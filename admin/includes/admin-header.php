<header class="admin-header">
    <div class="admin-header-content">
        <div class="admin-header-left">
            <div class="admin-logo">
                <a href="dashboard.php">
                  <h2>GadgetLoop Admin</h2>
                </a>
            </div>
        </div>
        
        <div class="admin-header-center">
            <div class="admin-search">
                <input type="text" placeholder="Search..." id="admin-search">
                <button type="button">🔍</button>
            </div>
        </div>

        <div class="admin-header-right">
            <div class="admin-user-menu">
                <button type="button" class="user-menu-btn" id="user-menu-btn">
                    <img src="../uploads/profiles/admin-avatar.jpg" alt="Admin" class="user-avatar">
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="dropdown-arrow">▼</span>
                </button>
                <div class="user-menu-dropdown" id="user-menu-dropdown">
                    <a href="settings.php">⚙️ Settings</a>
                    <a href="../logout.php">🚪 Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
$(document).ready(function() {
    // Toggle notification dropdown
    $('#notification-btn').on('click', function(e) {
        e.stopPropagation();
        $('#notification-dropdown').toggle();
        $('#user-menu-dropdown').hide();
    });
    
    // Toggle user menu dropdown
    $('#user-menu-btn').on('click', function(e) {
        e.stopPropagation();
        $('#user-menu-dropdown').toggle();
        $('#notification-dropdown').hide();
    });
    
    // Close dropdowns when clicking outside
    $(document).on('click', function() {
        $('.notification-dropdown, .user-menu-dropdown').hide();
    });
    
    // Mark all notifications as read
    $('.mark-all-read').on('click', function() {
        $('.notification-item').removeClass('unread');
        $('.notification-count').text('0').hide();
    });
    
    // Admin search functionality
    $('#admin-search').on('keypress', function(e) {
        if (e.which === 13) {
            const query = $(this).val();
            if (query.trim()) {
                window.location.href = 'search.php?q=' + encodeURIComponent(query);
            }
        }
    });
});
</script>