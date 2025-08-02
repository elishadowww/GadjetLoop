<?php
session_start();
require_once('../includes/config.php');
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

$success = '';
$error = '';

// Handle address actions
if ($_POST) {
    if (isset($_POST['add_address'])) {
        $type = sanitizeInput($_POST['type']);
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $company = sanitizeInput($_POST['company']);
        $address_line_1 = sanitizeInput($_POST['address_line_1']);
        $address_line_2 = sanitizeInput($_POST['address_line_2']);
        $city = sanitizeInput($_POST['city']);
        $state = sanitizeInput($_POST['state']);
        $zip_code = sanitizeInput($_POST['zip_code']);
        $country = sanitizeInput($_POST['country']);
        $phone = sanitizeInput($_POST['phone']);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        if (empty($first_name) || empty($last_name) || empty($address_line_1) || empty($city) || empty($country)) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                // If this is set as default, remove default from other addresses
                if ($is_default) {
                    $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND type = ?");
                    $stmt->execute([$user_id, $type]);
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO user_addresses (user_id, type, first_name, last_name, company, address_line_1, 
                                               address_line_2, city, state, zip_code, country, phone, is_default, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$user_id, $type, $first_name, $last_name, $company, $address_line_1, 
                               $address_line_2, $city, $state, $zip_code, $country, $phone, $is_default]);
                
                $success = 'Address added successfully';
            } catch (PDOException $e) {
                $error = 'Failed to add address';
            }
        }
    }
    
    if (isset($_POST['update_address'])) {
        $address_id = intval($_POST['address_id']);
        $type = sanitizeInput($_POST['type']);
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $company = sanitizeInput($_POST['company']);
        $address_line_1 = sanitizeInput($_POST['address_line_1']);
        $address_line_2 = sanitizeInput($_POST['address_line_2']);
        $city = sanitizeInput($_POST['city']);
        $state = sanitizeInput($_POST['state']);
        $zip_code = sanitizeInput($_POST['zip_code']);
        $country = sanitizeInput($_POST['country']);
        $phone = sanitizeInput($_POST['phone']);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        try {
            // If this is set as default, remove default from other addresses
            if ($is_default) {
                $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND type = ? AND id != ?");
                $stmt->execute([$user_id, $type, $address_id]);
            }
            
            $stmt = $pdo->prepare("
                UPDATE user_addresses SET 
                type = ?, first_name = ?, last_name = ?, company = ?, address_line_1 = ?, 
                address_line_2 = ?, city = ?, state = ?, zip_code = ?, country = ?, phone = ?, 
                is_default = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$type, $first_name, $last_name, $company, $address_line_1, 
                           $address_line_2, $city, $state, $zip_code, $country, $phone, 
                           $is_default, $address_id, $user_id]);
            
            $success = 'Address updated successfully';
        } catch (PDOException $e) {
            $error = 'Failed to update address';
        }
    }
    
    if (isset($_POST['delete_address'])) {
        $address_id = intval($_POST['address_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$address_id, $user_id]);
            $success = 'Address deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete address';
        }
    }
    
    if (isset($_POST['set_default'])) {
        $address_id = intval($_POST['address_id']);
        $type = sanitizeInput($_POST['type']);
        try {
            // Remove default from all addresses of this type
            $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND type = ?");
            $stmt->execute([$user_id, $type]);
            
            // Set this address as default
            $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$address_id, $user_id]);
            
            $success = 'Default address updated';
        } catch (PDOException $e) {
            $error = 'Failed to update default address';
        }
    }
}

// Get user addresses
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, type, created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// Group addresses by type
$shipping_addresses = array_filter($addresses, function($addr) { return $addr['type'] === 'shipping'; });
$billing_addresses = array_filter($addresses, function($addr) { return $addr['type'] === 'billing'; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Addresses - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
    <style>
        .addresses-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .address-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tab-btn {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .addresses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .address-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .address-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .address-card.default {
            border-color: #28a745;
            background: #f8fff9;
        }
        
        .default-badge {
            position: absolute;
            top: -8px;
            right: 15px;
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .address-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .address-type {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .address-details {
            color: #666;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .address-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .add-address-card {
            background: #f8f9fa;
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 3rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-address-card:hover {
            background: #e3f2fd;
            border-color: #0056b3;
        }
        
        .add-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        
        .address-form {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: none;
        }
        
        .address-form.show {
            display: block;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .addresses-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .address-tabs {
                flex-direction: column;
                gap: 0;
            }
            
            .tab-btn {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #e9ecef;
            }
        }
    </style>
</head>
<body data-page="addresses" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="member-layout">
                <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>My Addresses</h1>
                        <p>Manage your shipping and billing addresses</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <div class="addresses-tabs">
                        <button class="tab-btn active" data-tab="shipping">Shipping Addresses</button>
                        <button class="tab-btn" data-tab="billing">Billing Addresses</button>
                    </div>
                    
                    <!-- Shipping Addresses Tab -->
                    <div class="tab-content active" id="shipping">
                        <div class="addresses-grid">
                            <?php if (empty($shipping_addresses)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">📍</div>
                                    <h3>No shipping addresses</h3>
                                    <p>Add a shipping address to make checkout faster</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($shipping_addresses as $address): ?>
                                <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                    <?php if ($address['is_default']): ?>
                                        <span class="default-badge">Default</span>
                                    <?php endif; ?>
                                    
                                    <div class="address-header">
                                        <div>
                                            <div class="address-name"><?php echo htmlspecialchars($address['first_name'] . ' ' . $address['last_name']); ?></div>
                                            <div class="address-type">Shipping Address</div>
                                        </div>
                                    </div>
                                    
                                    <div class="address-details">
                                        <?php if ($address['company']): ?>
                                            <?php echo htmlspecialchars($address['company']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['address_line_1']); ?><br>
                                        <?php if ($address['address_line_2']): ?>
                                            <?php echo htmlspecialchars($address['address_line_2']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['zip_code']); ?><br>
                                        <?php echo htmlspecialchars($address['country']); ?>
                                        <?php if ($address['phone']): ?>
                                            <br>Phone: <?php echo htmlspecialchars($address['phone']); ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="address-actions">
                                        <button class="btn btn-outline btn-sm" onclick="editAddress(<?php echo htmlspecialchars(json_encode($address)); ?>)">Edit</button>
                                        <?php if (!$address['is_default']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                <input type="hidden" name="type" value="<?php echo $address['type']; ?>">
                                                <button type="submit" name="set_default" class="btn btn-primary btn-sm">Set Default</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                            <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                            <button type="submit" name="delete_address" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <div class="add-address-card" onclick="showAddressForm('shipping')">
                                <div class="add-icon">+</div>
                                <h3>Add Shipping Address</h3>
                                <p>Add a new shipping address</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Billing Addresses Tab -->
                    <div class="tab-content" id="billing">
                        <div class="addresses-grid">
                            <?php if (empty($billing_addresses)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">💳</div>
                                    <h3>No billing addresses</h3>
                                    <p>Add a billing address for payment processing</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($billing_addresses as $address): ?>
                                <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                    <?php if ($address['is_default']): ?>
                                        <span class="default-badge">Default</span>
                                    <?php endif; ?>
                                    
                                    <div class="address-header">
                                        <div>
                                            <div class="address-name"><?php echo htmlspecialchars($address['first_name'] . ' ' . $address['last_name']); ?></div>
                                            <div class="address-type">Billing Address</div>
                                        </div>
                                    </div>
                                    
                                    <div class="address-details">
                                        <?php if ($address['company']): ?>
                                            <?php echo htmlspecialchars($address['company']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['address_line_1']); ?><br>
                                        <?php if ($address['address_line_2']): ?>
                                            <?php echo htmlspecialchars($address['address_line_2']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['zip_code']); ?><br>
                                        <?php echo htmlspecialchars($address['country']); ?>
                                        <?php if ($address['phone']): ?>
                                            <br>Phone: <?php echo htmlspecialchars($address['phone']); ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="address-actions">
                                        <button class="btn btn-outline btn-sm" onclick="editAddress(<?php echo htmlspecialchars(json_encode($address)); ?>)">Edit</button>
                                        <?php if (!$address['is_default']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                <input type="hidden" name="type" value="<?php echo $address['type']; ?>">
                                                <button type="submit" name="set_default" class="btn btn-primary btn-sm">Set Default</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                            <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                            <button type="submit" name="delete_address" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <div class="add-address-card" onclick="showAddressForm('billing')">
                                <div class="add-icon">+</div>
                                <h3>Add Billing Address</h3>
                                <p>Add a new billing address</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Form -->
                    <div class="address-form" id="address-form">
                        <h3 id="form-title">Add Address</h3>
                        <form method="POST" id="address-form-element">
                            <input type="hidden" name="address_id" id="address-id">
                            
                            <div class="form-group">
                                <label for="type">Address Type *</label>
                                <select id="type" name="type" class="form-control" required>
                                    <option value="shipping">Shipping Address</option>
                                    <option value="billing">Billing Address</option>
                                </select>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="company">Company (Optional)</label>
                                <input type="text" id="company" name="company" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="address_line_1">Address Line 1 *</label>
                                <input type="text" id="address_line_1" name="address_line_1" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address_line_2">Address Line 2 (Optional)</label>
                                <input type="text" id="address_line_2" name="address_line_2" class="form-control">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="state">State/Province *</label>
                                    <input type="text" id="state" name="state" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="zip_code">ZIP/Postal Code *</label>
                                    <input type="text" id="zip_code" name="zip_code" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="country">Country *</label>
                                    <select id="country" name="country" class="form-control" required>
                                        <option value="">Select Country</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="AU">Australia</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="IT">Italy</option>
                                        <option value="ES">Spain</option>
                                        <option value="NL">Netherlands</option>
                                        <option value="BE">Belgium</option>
                                        <option value="CH">Switzerland</option>
                                        <option value="AT">Austria</option>
                                        <option value="SE">Sweden</option>
                                        <option value="NO">Norway</option>
                                        <option value="DK">Denmark</option>
                                        <option value="FI">Finland</option>
                                        <option value="JP">Japan</option>
                                        <option value="KR">South Korea</option>
                                        <option value="SG">Singapore</option>
                                        <option value="HK">Hong Kong</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="BR">Brazil</option>
                                        <option value="MX">Mexico</option>
                                        <option value="IN">India</option>
                                        <option value="CN">China</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_default" name="is_default">
                                    <label for="is_default">Set as default address</label>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" name="add_address" id="submit-btn" class="btn btn-primary">Add Address</button>
                                <button type="button" class="btn btn-outline" onclick="hideAddressForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.tab-btn').on('click', function() {
                const tab = $(this).data('tab');
                
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                $('.tab-content').removeClass('active');
                $('#' + tab).addClass('active');
            });
        });
        
        function showAddressForm(type) {
            $('#address-form').addClass('show');
            $('#form-title').text('Add ' + (type === 'shipping' ? 'Shipping' : 'Billing') + ' Address');
            $('#type').val(type);
            $('#submit-btn').text('Add Address').attr('name', 'add_address');
            $('#address-id').val('');
            
            // Clear form
            $('#address-form-element')[0].reset();
            $('#type').val(type);
            
            // Scroll to form
            $('html, body').animate({
                scrollTop: $('#address-form').offset().top - 100
            }, 500);
        }
        
        function editAddress(address) {
            $('#address-form').addClass('show');
            $('#form-title').text('Edit ' + (address.type === 'shipping' ? 'Shipping' : 'Billing') + ' Address');
            $('#submit-btn').text('Update Address').attr('name', 'update_address');
            
            // Fill form with address data
            $('#address-id').val(address.id);
            $('#type').val(address.type);
            $('#first_name').val(address.first_name);
            $('#last_name').val(address.last_name);
            $('#company').val(address.company || '');
            $('#address_line_1').val(address.address_line_1);
            $('#address_line_2').val(address.address_line_2 || '');
            $('#city').val(address.city);
            $('#state').val(address.state);
            $('#zip_code').val(address.zip_code);
            $('#country').val(address.country);
            $('#phone').val(address.phone || '');
            $('#is_default').prop('checked', address.is_default == 1);
            
            // Scroll to form
            $('html, body').animate({
                scrollTop: $('#address-form').offset().top - 100
            }, 500);
        }
        
        function hideAddressForm() {
            $('#address-form').removeClass('show');
        }
    </script>
</body>
</html>