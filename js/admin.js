// Admin Panel JavaScript
$(document).ready(function() {
    // Initialize admin functionality
    initializeAdmin();
    
    // Handle sidebar toggle for mobile
    $('.sidebar-toggle').on('click', function() {
        $('.admin-sidebar').toggleClass('active');
    });
    
    // Handle bulk actions
    $('#select-all').on('change', function() {
        $('.item-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActions();
    });
    
    $('.item-checkbox').on('change', function() {
        updateBulkActions();
        updateSelectAll();
    });
    
    // Handle bulk action form
    $('#bulk-action-form').on('submit', function(e) {
        const action = $('#bulk-action').val();
        const selected = $('.item-checkbox:checked').length;
        
        if (!action) {
            e.preventDefault();
            showAlert('Please select an action', 'warning');
            return;
        }
        
        if (selected === 0) {
            e.preventDefault();
            showAlert('Please select at least one item', 'warning');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selected} item(s)?`)) {
            e.preventDefault();
        }
    });
    
    // Handle status updates
    $('.status-select').on('change', function() {
        const id = $(this).data('id');
        const status = $(this).val();
        const type = $(this).data('type');
        
        updateStatus(type, id, status);
    });
    
    // Handle quick actions
    $('.quick-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        handleQuickAction(action, type, id);
    });
    
    // Handle file uploads
    initializeFileUploads();
    
    // Handle data tables
    if ($('.data-table').length) {
        initializeDataTables();
    }
    
    // Handle charts
    if ($('#analytics-chart').length) {
        initializeAnalyticsChart();
    }
    
    // Auto-refresh notifications
    setInterval(refreshNotifications, 30000); // Every 30 seconds
    
    // Handle search
    $('#admin-search').on('keyup', debounce(function() {
        const query = $(this).val();
        if (query.length >= 3) {
            performAdminSearch(query);
        }
    }, 500));
});

// Initialize admin functionality
function initializeAdmin() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                xhr.setRequestHeader("X-CSRFToken", getCsrfToken());
            }
        }
    });
    
    // Initialize tooltips
    $('[data-tooltip]').each(function() {
        $(this).attr('title', $(this).data('tooltip'));
    });
    
    // Initialize confirmation dialogs
    $('.confirm-action').on('click', function(e) {
        const message = $(this).data('confirm') || 'Are you sure?';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
}

// Update bulk actions visibility
function updateBulkActions() {
    const selected = $('.item-checkbox:checked').length;
    const $bulkActions = $('.bulk-actions');
    
    if (selected > 0) {
        $bulkActions.show();
        $('.selected-count').text(selected);
    } else {
        $bulkActions.hide();
    }
}

// Update select all checkbox
function updateSelectAll() {
    const total = $('.item-checkbox').length;
    const checked = $('.item-checkbox:checked').length;
    
    $('#select-all').prop('indeterminate', checked > 0 && checked < total);
    $('#select-all').prop('checked', checked === total);
}

// Update status
function updateStatus(type, id, status) {
    $.post('ajax/update-status.php', {
        type: type,
        id: id,
        status: status
    }, function(response) {
        if (response.success) {
            showAlert(response.message, 'success');
            // Update status badge if exists
            const $badge = $(`.status-badge[data-id="${id}"]`);
            if ($badge.length) {
                $badge.removeClass().addClass(`status-badge status-${status}`).text(status);
            }
        } else {
            showAlert(response.message, 'error');
        }
    }).fail(function() {
        showAlert('Failed to update status', 'error');
    });
}

// Handle quick actions
function handleQuickAction(action, type, id) {
    let confirmMessage = '';
    
    switch(action) {
        case 'delete':
            confirmMessage = 'Are you sure you want to delete this item?';
            break;
        case 'activate':
            confirmMessage = 'Are you sure you want to activate this item?';
            break;
        case 'deactivate':
            confirmMessage = 'Are you sure you want to deactivate this item?';
            break;
        default:
            confirmMessage = 'Are you sure you want to perform this action?';
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    $.post('ajax/quick-action.php', {
        action: action,
        type: type,
        id: id
    }, function(response) {
        if (response.success) {
            showAlert(response.message, 'success');
            
            if (action === 'delete') {
                // Remove row from table
                $(`tr[data-id="${id}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                // Refresh page or update UI
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        } else {
            showAlert(response.message, 'error');
        }
    }).fail(function() {
        showAlert('Action failed', 'error');
    });
}

// Initialize file uploads
function initializeFileUploads() {
    // Drag and drop
    $('.file-upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    }).on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        handleFileUpload(files, $(this));
    });
    
    // Click to upload
    $('.file-upload-area').on('click', function() {
        $(this).find('.file-upload-input').click();
    });
    
    $('.file-upload-input').on('change', function() {
        const files = this.files;
        const $uploadArea = $(this).closest('.file-upload-area');
        handleFileUpload(files, $uploadArea);
    });
    
    // Remove uploaded files
    $(document).on('click', '.preview-remove', function() {
        $(this).closest('.preview-item').remove();
    });
}

// Handle file upload
function handleFileUpload(files, $uploadArea) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    Array.from(files).forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            showAlert('Only JPEG, PNG, and GIF files are allowed', 'error');
            return;
        }
        
        if (file.size > maxSize) {
            showAlert('File size must be less than 5MB', 'error');
            return;
        }
        
        // Create preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = `
                <div class="preview-item">
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="preview-remove">×</button>
                    <input type="hidden" name="uploaded_images[]" value="${file.name}">
                </div>
            `;
            $uploadArea.siblings('.image-preview').append(preview);
        };
        reader.readAsDataURL(file);
        
        // Upload file
        uploadFile(file, $uploadArea);
    });
}

// Upload file to server
function uploadFile(file, $uploadArea) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('upload_type', $uploadArea.data('upload-type') || 'general');
    
    $.ajax({
        url: 'ajax/upload-file.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // File uploaded successfully
                console.log('File uploaded:', response.filename);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            showAlert('Upload failed', 'error');
        }
    });
}

// Initialize data tables
function initializeDataTables() {
    $('.data-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        columnDefs: [
            {
                targets: 'no-sort',
                orderable: false
            }
        ]
    });
}

// Initialize analytics chart
function initializeAnalyticsChart() {
    const ctx = document.getElementById('analytics-chart').getContext('2d');
    
    // Get chart data from server
    $.get('ajax/get-analytics-data.php', function(data) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Sales',
                    data: data.sales,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Orders',
                    data: data.orders,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
}

// Refresh notifications
function refreshNotifications() {
    $.get('ajax/get-notifications.php', function(data) {
        if (data.count > 0) {
            $('.notification-count').text(data.count).show();
        } else {
            $('.notification-count').hide();
        }
        
        // Update notification list
        $('.notification-list').html(data.html);
    });
}

// Perform admin search
function performAdminSearch(query) {
    $.get('ajax/admin-search.php', { q: query }, function(data) {
        // Show search results
        showSearchResults(data.results);
    });
}

// Show search results
function showSearchResults(results) {
    let html = '<div class="search-results">';
    
    if (results.length === 0) {
        html += '<p>No results found</p>';
    } else {
        results.forEach(result => {
            html += `
                <div class="search-result-item">
                    <h4><a href="${result.url}">${result.title}</a></h4>
                    <p>${result.description}</p>
                    <small>${result.type}</small>
                </div>
            `;
        });
    }
    
    html += '</div>';
    
    // Show results in dropdown or modal
    $('.search-results-container').html(html).show();
}

// Get CSRF token
function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

// Export data
function exportData(type, format) {
    const params = new URLSearchParams();
    // Add filters from the form
    $('.filter-form input, .filter-form select').each(function() {
        if ($(this).val()) {
            params.append($(this).attr('name'), $(this).val());
        }
    });
    if (type === 'users' && format === 'csv') {
        window.location.href = '../ajax/export-users.php?' + params.toString();
    } else {
        window.location.href = 'ajax/export-data.php?' + params.toString();
    }
}

// Import data
function importData(type) {
    const $modal = $('#import-modal');
    $modal.find('.import-type').text(type);
    $modal.find('input[name="import_type"]').val(type);
    $modal.modal('show');
}

// Handle import form
$('#import-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'ajax/import-data.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                $('#import-modal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            showAlert('Import failed', 'error');
        }
    });
});

// Backup database
function backupDatabase() {
    if (!confirm('Are you sure you want to create a database backup?')) {
        return;
    }
    
    $.post('ajax/backup-database.php', function(response) {
        if (response.success) {
            showAlert('Backup created successfully', 'success');
            // Download backup file
            window.location.href = response.download_url;
        } else {
            showAlert(response.message, 'error');
        }
    });
}

// Utility functions
function showAlert(message, type = 'info') {
    const alertClass = 'alert-' + type;
    const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.admin-content').prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format numbers
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('Copied to clipboard', 'success');
    }, function() {
        showAlert('Failed to copy', 'error');
    });
}