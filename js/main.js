$(document).ready(function() {
    // Initialize page
    initializePage();
    
    // Update cart count on page load
    updateCartCount();
    
    // Handle search form
    $('.search-form').on('submit', function(e) {
        const searchTerm = $(this).find('input[name="search"]').val().trim();
        if (!searchTerm) {
            e.preventDefault();
            showAlert('Please enter a search term', 'warning');
        }
    });
    
    // Handle dropdown menus
    $('.dropdown').on('mouseenter', function() {
        $(this).find('.dropdown-content').fadeIn(200);
    }).on('mouseleave', function() {
        $(this).find('.dropdown-content').fadeOut(200);
    });
    
    // Handle mobile menu toggle
    $('.mobile-menu-toggle').on('click', function() {
        $('.nav-menu').toggleClass('active');
    });
    
    // Handle form validation
    $('form').on('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });
    
    // Handle file uploads with drag and drop
    initializeDragAndDrop();
    
    // Handle AJAX forms
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        handleAjaxForm(this);
    });
    
    // Handle wishlist buttons
    $('.wishlist-btn').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const $btn = $(this);
        toggleWishlist(productId, $btn);
    });
    
    // Handle product quick view
    $('.quick-view-btn').on('click', function(e) {
        e.preventDefault();
        showQuickView($(this).data('product-id'));
    });
    
    // Handle add to cart buttons (event delegation to prevent double binding)
    $(document).off('click', '.add-to-cart').on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        if (!isUserLoggedIn()) {
            showAlert('Please login to add items to cart', 'warning');
            return;
        }
        const productId = $(this).data('product-id');
        const quantity = 1; // Default quantity
        addToCart(productId, quantity);
    });
    
    // Handle rating stars
    $('.rating-stars').on('click', '.star', function() {
        const rating = $(this).data('rating');
        setRating($(this).closest('.rating-stars'), rating);
    });
    
    // Handle image zoom
    $('.product-image img').on('mouseenter', function() {
        $(this).addClass('zoomed');
    }).on('mouseleave', function() {
        $(this).removeClass('zoomed');
    });
    
    // Handle infinite scroll for products
    if ($('.product-grid').length) {
        initializeInfiniteScroll();
    }
    
    // Handle price range slider
    if ($('#price-range').length) {
        initializePriceRange();
    }
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});

// Initialize page-specific functionality
function initializePage() {
    const page = $('body').data('page');
    
    switch(page) {
        case 'home':
            initializeHomePage();
            break;
        case 'products':
            initializeProductsPage();
            break;
        case 'product-detail':
            initializeProductDetailPage();
            break;
        case 'cart':
            initializeCartPage();
            break;
        case 'checkout':
            initializeCheckoutPage();
            break;
        case 'admin':
            initializeAdminPage();
            break;
    }
}

// Home page initialization
function initializeHomePage() {
    // Initialize hero slider if exists
    if ($('.hero-slider').length) {
        $('.hero-slider').slick({
            autoplay: true,
            autoplaySpeed: 5000,
            dots: true,
            arrows: false,
            fade: true
        });
    }
    
    // Initialize category carousel
    if ($('.category-carousel').length) {
        $('.category-carousel').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });
    }
}

// Products page initialization
function initializeProductsPage() {
    // Handle filter changes
    $('.filter-form input, .filter-form select').on('change', function() {
        applyFilters();
    });
    
    // Handle sort changes
    $('#sort-select').on('change', function() {
        applyFilters();
    });
    
    // Handle view toggle
    $('.view-toggle').on('click', 'button', function() {
        const view = $(this).data('view');
        toggleProductView(view);
    });
}

// Product detail page initialization
function initializeProductDetailPage() {
    // Initialize image gallery
    initializeImageGallery();
    
    // Handle quantity changes
    $('.quantity-controls').on('click', '.qty-btn', function() {
        const input = $(this).siblings('input');
        const currentVal = parseInt(input.val()) || 1;
        const isIncrease = $(this).hasClass('qty-increase');
        const max = parseInt(input.attr('max')) || 999;
        
        if (isIncrease && currentVal < max) {
            input.val(currentVal + 1);
        } else if (!isIncrease && currentVal > 1) {
            input.val(currentVal - 1);
        }
    });
    
    // Handle review submission
    $('#review-form').on('submit', function(e) {
        e.preventDefault();
        submitReview();
    });
}

// Cart page initialization
function initializeCartPage() {
    // Handle quantity updates
    $('.cart-quantity').on('change', function() {
        updateCartItemQuantity($(this).data('product-id'), $(this).val());
    });
    
    // Handle item removal
    $('.remove-item').on('click', function(e) {
        e.preventDefault();
        removeCartItem($(this).data('product-id'));
    });
    
    // Handle coupon application
    $('#apply-coupon').on('click', function() {
        applyCoupon($('#coupon-code').val());
    });
}

// Checkout page initialization
function initializeCheckoutPage() {
    // Handle payment method changes
    $('input[name="payment_method"]').on('change', function() {
        togglePaymentFields($(this).val());
    });
    
    // Handle address form toggle
    $('#different-billing').on('change', function() {
        $('#billing-address').toggle($(this).is(':checked'));
    });
    
    // Validate checkout form
    $('#checkout-form').on('submit', function(e) {
        if (!validateCheckoutForm()) {
            e.preventDefault();
        }
    });
}

// Admin page initialization
function initializeAdminPage() {
    // Initialize data tables
    if ($('.data-table').length) {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }
    
    // Initialize charts
    if ($('#sales-chart').length) {
        initializeSalesChart();
    }
    
    // Handle bulk actions
    $('#bulk-action-form').on('submit', function(e) {
        const action = $('#bulk-action').val();
        const selected = $('.item-checkbox:checked').length;
        
        if (!action || selected === 0) {
            e.preventDefault();
            showAlert('Please select an action and at least one item', 'warning');
        } else if (!confirm('Are you sure you want to perform this action?')) {
            e.preventDefault();
        }
    });
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const $form = $(form);
    
    // Clear previous errors
    $form.find('.error-message').remove();
    $form.find('.form-control').removeClass('error');
    
    // Validate required fields
    $form.find('[required]').each(function() {
        const $field = $(this);
        const value = $field.val().trim();
        
        if (!value) {
            showFieldError($field, 'This field is required');
            isValid = false;
        }
    });
    
    // Validate email fields
    $form.find('input[type="email"]').each(function() {
        const $field = $(this);
        const value = $field.val().trim();
        
        if (value && !isValidEmail(value)) {
            showFieldError($field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Validate password fields
    $form.find('input[name="password"]').each(function() {
        const $field = $(this);
        const value = $field.val();
        
        if (value && value.length < 6) {
            showFieldError($field, 'Password must be at least 6 characters long');
            isValid = false;
        }
    });
    
    // Validate password confirmation
    const $password = $form.find('input[name="password"]');
    const $confirmPassword = $form.find('input[name="confirm_password"]');
    
    if ($password.length && $confirmPassword.length) {
        if ($password.val() !== $confirmPassword.val()) {
            showFieldError($confirmPassword, 'Passwords do not match');
            isValid = false;
        }
    }
    
    return isValid;
}

// Show field error
function showFieldError($field, message) {
    $field.addClass('error');
    $field.after('<div class="error-message">' + message + '</div>');
}

// Validate email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Handle AJAX forms
function handleAjaxForm(form) {
    const $form = $(form);
    const url = $form.attr('action');
    const method = $form.attr('method') || 'POST';
    const formData = new FormData(form);
    
    // Show loading state
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.text();
    $submitBtn.prop('disabled', true).html('<span class="loading"></span> Processing...');
    
    $.ajax({
        url: url,
        method: method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                }
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'error');
        },
        complete: function() {
            $submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertClass = 'alert-' + type;
    const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('main').prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

// Update cart count
function updateCartCount() {
    $.get('ajax/get-cart-count.php', function(data) {
        $('#cart-count').text(data.count || 0);
    }).fail(function() {
        $('#cart-count').text('0');
    });
}

// Toggle wishlist
function toggleWishlist(productId, $btn) {
    if (!isUserLoggedIn()) {
        showAlert('Please login to add items to wishlist', 'warning');
        return;
    }
    
    // Show loading state
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('...');
    
    $.post('ajax/toggle-wishlist.php', {
        product_id: productId
    }, function(response) {
        if (response.success) {
            // Update button appearance
            if (response.in_wishlist) {
                $btn.addClass('active').html('♥');
            } else {
                $btn.removeClass('active').html('♡');
            }
            showAlert(response.message, 'success');
        } else {
            showAlert(response.message, 'error');
        }
    }).fail(function() {
        showAlert('Failed to update wishlist', 'error');
    }).always(function() {
        // Restore button state
        $btn.prop('disabled', false);
        if (!$btn.hasClass('active') && $btn.html() === '...') {
            $btn.html('♡');
        }
    });
}

// Check if user is logged in
function isUserLoggedIn() {
    return $('body').hasClass('logged-in');
}

// Initialize drag and drop file upload
function initializeDragAndDrop() {
    $('.file-drop-zone').on('dragover', function(e) {
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
    
    $('.file-input').on('change', function() {
        const files = this.files;
        const $dropZone = $(this).closest('.file-drop-zone');
        handleFileUpload(files, $dropZone);
    });
}

// Handle file upload
function handleFileUpload(files, $dropZone) {
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
                <div class="file-preview">
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-file">×</button>
                </div>
            `;
            $dropZone.find('.file-previews').append(preview);
        };
        reader.readAsDataURL(file);
    });
}

// Initialize image gallery
function initializeImageGallery() {
    $('.product-images .thumbnail').on('click', function() {
        const newSrc = $(this).data('full-image');
        $('.main-image img').attr('src', newSrc);
        $('.thumbnail').removeClass('active');
        $(this).addClass('active');
    });
    
    // Initialize image slider if multiple images
    if ($('.product-images .thumbnail').length > 1) {
        $('.product-images').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            vertical: true,
            verticalSwiping: true,
            arrows: true,
            focusOnSelect: true
        });
    }
}

// Apply product filters
function applyFilters() {
    const formData = $('.filter-form').serialize();
    const currentUrl = new URL(window.location);
    
    // Update URL parameters
    const params = new URLSearchParams(formData);
    params.forEach((value, key) => {
        if (value) {
            currentUrl.searchParams.set(key, value);
        } else {
            currentUrl.searchParams.delete(key);
        }
    });
    
    // Reload page with new parameters
    window.location.href = currentUrl.toString();
}

// Toggle product view (grid/list)
function toggleProductView(view) {
    $('.view-toggle button').removeClass('active');
    $(`.view-toggle button[data-view="${view}"]`).addClass('active');
    
    $('.product-grid').removeClass('grid-view list-view').addClass(view + '-view');
    
    // Save preference
    localStorage.setItem('product-view', view);
}

// Initialize infinite scroll
function initializeInfiniteScroll() {
    let loading = false;
    let page = 2;
    
    $(window).on('scroll', function() {
        if (loading) return;
        
        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();
        
        if (scrollTop + windowHeight >= documentHeight - 100) {
            loading = true;
            loadMoreProducts(page++);
        }
    });
}

// Load more products
function loadMoreProducts(page) {
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('page', page);
    
    $.get('ajax/load-products.php?' + currentParams.toString(), function(data) {
        if (data.products && data.products.length > 0) {
            $('.product-grid').append(data.products);
            loading = false;
        } else {
            // No more products
            $(window).off('scroll');
        }
    });
}

// Initialize price range slider
function initializePriceRange() {
    const $slider = $('#price-range');
    const min = parseInt($slider.data('min')) || 0;
    const max = parseInt($slider.data('max')) || 1000;
    const currentMin = parseInt($slider.data('current-min')) || min;
    const currentMax = parseInt($slider.data('current-max')) || max;
    
    $slider.slider({
        range: true,
        min: min,
        max: max,
        values: [currentMin, currentMax],
        slide: function(event, ui) {
            $('#price-min').val(ui.values[0]);
            $('#price-max').val(ui.values[1]);
            $('#price-display').text('$' + ui.values[0] + ' - $' + ui.values[1]);
        },
        stop: function(event, ui) {
            applyFilters();
        }
    });
    
    $('#price-display').text('$' + currentMin + ' - $' + currentMax);
}

// Utility functions
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
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