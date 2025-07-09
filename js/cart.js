// Cart functionality
$(document).ready(function() {
    // Handle add to cart buttons
    $('.add-to-cart').on('click', function(e) {
        e.preventDefault();
        
        if (!isUserLoggedIn()) {
            showAlert('Please login to add items to cart', 'warning');
            return;
        }
        
        const productId = $(this).data('product-id');
        const quantity = $(this).closest('.product-card, .product-detail').find('.quantity-input').val() || 1;
        
        addToCart(productId, quantity);
    });
    
    // Handle cart quantity updates
    $('.cart-quantity').on('change', function() {
        const productId = $(this).data('product-id');
        const quantity = $(this).val();
        updateCartQuantity(productId, quantity);
    });
    
    // Handle remove from cart
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        removeFromCart(productId);
    });
    
    // Handle clear cart
    $('#clear-cart').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to clear your cart?')) {
            clearCart();
        }
    });
    
    // Handle coupon application
    $('#apply-coupon-btn').on('click', function() {
        const couponCode = $('#coupon-code').val().trim();
        if (couponCode) {
            applyCoupon(couponCode);
        } else {
            showAlert('Please enter a coupon code', 'warning');
        }
    });
    
    // Handle coupon removal
    $('#remove-coupon').on('click', function(e) {
        e.preventDefault();
        removeCoupon();
    });
    
    // Auto-save cart on quantity change
    $('.cart-quantity').on('input', debounce(function() {
        const productId = $(this).data('product-id');
        const quantity = $(this).val();
        updateCartQuantity(productId, quantity, false); // Don't show success message
    }, 1000));
});

// Add item to cart
function addToCart(productId, quantity = 1) {
    const $btn = $(`.add-to-cart[data-product-id="${productId}"]`);
    const originalText = $btn.text();
    
    // Show loading state
    $btn.prop('disabled', true).html('<span class="loading"></span> Adding...');
    
    $.post('ajax/add-to-cart.php', {
        product_id: productId,
        quantity: quantity
    }, function(response) {
        if (response.success) {
            showAlert(response.message, 'success');
            updateCartCount();
            updateCartDisplay();
            
            // Show added animation
            $btn.html('✓ Added').addClass('btn-success');
            setTimeout(() => {
                $btn.text(originalText).removeClass('btn-success').prop('disabled', false);
            }, 2000);
        } else {
            showAlert(response.message, 'error');
            $btn.text(originalText).prop('disabled', false);
        }
    }).fail(function() {
        showAlert('Failed to add item to cart', 'error');
        $btn.text(originalText).prop('disabled', false);
    });
}

// Update cart item quantity
function updateCartQuantity(productId, quantity, showMessage = true) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    $.post('ajax/update-cart-quantity.php', {
        product_id: productId,
        quantity: quantity
    }, function(response) {
        if (response.success) {
            if (showMessage) {
                showAlert('Cart updated', 'success');
            }
            updateCartDisplay();
            updateCartTotals();
        } else {
            showAlert(response.message, 'error');
            // Revert quantity
            $(`.cart-quantity[data-product-id="${productId}"]`).val(response.old_quantity || 1);
        }
    });
}

// Remove item from cart
function removeFromCart(productId) {
    if (!confirm('Remove this item from cart?')) {
        return;
    }
    
    $.post('ajax/remove-from-cart.php', {
        product_id: productId
    }, function(response) {
        if (response.success) {
            showAlert(response.message, 'success');
            $(`.cart-item[data-product-id="${productId}"]`).fadeOut(300, function() {
                $(this).remove();
                updateCartTotals();
                
                // Check if cart is empty
                if ($('.cart-item').length === 0) {
                    showEmptyCart();
                }
            });
            updateCartCount();
        } else {
            showAlert(response.message, 'error');
        }
    });
}

// Clear entire cart
function clearCart() {
    $.post('ajax/clear-cart.php', function(response) {
        if (response.success) {
            showAlert('Cart cleared', 'success');
            $('.cart-items').fadeOut(300, function() {
                showEmptyCart();
            });
            updateCartCount();
        } else {
            showAlert(response.message, 'error');
        }
    });
}

// Apply coupon code
function applyCoupon(couponCode) {
    const $btn = $('#apply-coupon-btn');
    const originalText = $btn.text();
    
    $btn.prop('disabled', true).html('<span class="loading"></span> Applying...');
    
    $.post('ajax/apply-coupon.php', {
        coupon_code: couponCode
    }, function(response) {
        if (response.success) {
            showAlert(response.message, 'success');
            updateCartTotals();
            showCouponApplied(response.coupon);
        } else {
            showAlert(response.message, 'error');
        }
    }).always(function() {
        $btn.prop('disabled', false).text(originalText);
    });
}

// Remove applied coupon
function removeCoupon() {
    $.post('ajax/remove-coupon.php', function(response) {
        if (response.success) {
            showAlert('Coupon removed', 'success');
            updateCartTotals();
            hideCouponApplied();
        } else {
            showAlert(response.message, 'error');
        }
    });
}

// Update cart display
function updateCartDisplay() {
    $.get('ajax/get-cart-items.php', function(data) {
        if (data.success && data.items.length > 0) {
            let cartHtml = '';
            data.items.forEach(item => {
                cartHtml += generateCartItemHtml(item);
            });
            $('.cart-items').html(cartHtml);
            updateCartTotals();
        } else {
            showEmptyCart();
        }
    });
}

// Generate cart item HTML
function generateCartItemHtml(item) {
    const salePrice = item.discount_percentage > 0 ? item.sale_price : item.price;
    const originalPrice = item.discount_percentage > 0 ? item.price : null;
    
    return `
        <div class="cart-item" data-product-id="${item.product_id}">
            <div class="item-image">
                <img src="uploads/products/${item.main_image}" alt="${item.name}">
            </div>
            <div class="item-details">
                <h4>${item.name}</h4>
                <div class="item-price">
                    ${originalPrice ? `<span class="original-price">$${originalPrice}</span>` : ''}
                    <span class="current-price">$${salePrice}</span>
                </div>
            </div>
            <div class="item-quantity">
                <div class="quantity-controls">
                    <button type="button" class="qty-btn qty-decrease" data-product-id="${item.product_id}">-</button>
                    <input type="number" class="cart-quantity" data-product-id="${item.product_id}" 
                           value="${item.quantity}" min="1" max="${item.stock_quantity}">
                    <button type="button" class="qty-btn qty-increase" data-product-id="${item.product_id}">+</button>
                </div>
                <small class="stock-info">${item.stock_quantity} in stock</small>
            </div>
            <div class="item-total">
                $${(salePrice * item.quantity).toFixed(2)}
            </div>
            <div class="item-actions">
                <button type="button" class="remove-from-cart" data-product-id="${item.product_id}">
                    Remove
                </button>
            </div>
        </div>
    `;
}

// Update cart totals
function updateCartTotals() {
    $.get('ajax/get-cart-totals.php', function(data) {
        if (data.success) {
            $('#subtotal').text('$' + data.subtotal.toFixed(2));
            $('#tax-amount').text('$' + data.tax.toFixed(2));
            $('#shipping-amount').text(data.shipping > 0 ? '$' + data.shipping.toFixed(2) : 'Free');
            
            if (data.discount > 0) {
                $('#discount-row').show();
                $('#discount-amount').text('-$' + data.discount.toFixed(2));
            } else {
                $('#discount-row').hide();
            }
            
            $('#total-amount').text('$' + data.total.toFixed(2));
            
            // Update shipping message
            if (data.subtotal < 50 && data.subtotal > 0) {
                const remaining = (50 - data.subtotal).toFixed(2);
                $('#shipping-message').html(`Add $${remaining} more for free shipping!`).show();
            } else {
                $('#shipping-message').hide();
            }
        }
    });
}

// Show empty cart message
function showEmptyCart() {
    const emptyCartHtml = `
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="products.php" class="btn btn-primary">Start Shopping</a>
        </div>
    `;
    $('.cart-content').html(emptyCartHtml);
    $('#cart-count').text('0');
}

// Show applied coupon
function showCouponApplied(coupon) {
    const couponHtml = `
        <div class="applied-coupon">
            <span class="coupon-code">${coupon.code}</span>
            <span class="coupon-discount">-${coupon.discount_type === 'percentage' ? coupon.discount_value + '%' : '$' + coupon.discount_value}</span>
            <button type="button" id="remove-coupon" class="remove-coupon">×</button>
        </div>
    `;
    $('.coupon-section').append(couponHtml);
    $('#coupon-code').val('').prop('disabled', true);
    $('#apply-coupon-btn').prop('disabled', true);
}

// Hide applied coupon
function hideCouponApplied() {
    $('.applied-coupon').remove();
    $('#coupon-code').prop('disabled', false);
    $('#apply-coupon-btn').prop('disabled', false);
}

// Handle quantity control buttons
$(document).on('click', '.qty-btn', function() {
    const $input = $(this).siblings('.cart-quantity');
    const productId = $(this).data('product-id');
    const currentVal = parseInt($input.val()) || 1;
    const max = parseInt($input.attr('max')) || 999;
    const isIncrease = $(this).hasClass('qty-increase');
    
    let newVal = currentVal;
    
    if (isIncrease && currentVal < max) {
        newVal = currentVal + 1;
    } else if (!isIncrease && currentVal > 1) {
        newVal = currentVal - 1;
    }
    
    if (newVal !== currentVal) {
        $input.val(newVal);
        updateCartQuantity(productId, newVal);
    }
});

// Mini cart functionality for header
function updateMiniCart() {
    $.get('ajax/get-cart-items.php', function(data) {
        if (data.success) {
            let miniCartHtml = '';
            let total = 0;
            
            if (data.items.length > 0) {
                data.items.forEach(item => {
                    const itemTotal = item.sale_price * item.quantity;
                    total += itemTotal;
                    
                    miniCartHtml += `
                        <div class="mini-cart-item">
                            <img src="uploads/products/${item.main_image}" alt="${item.name}">
                            <div class="item-info">
                                <h5>${item.name}</h5>
                                <span class="quantity">${item.quantity} × $${item.sale_price}</span>
                            </div>
                            <button class="remove-mini-item" data-product-id="${item.product_id}">×</button>
                        </div>
                    `;
                });
                
                miniCartHtml += `
                    <div class="mini-cart-footer">
                        <div class="mini-cart-total">Total: $${total.toFixed(2)}</div>
                        <div class="mini-cart-actions">
                            <a href="cart.php" class="btn btn-outline btn-sm">View Cart</a>
                            <a href="checkout.php" class="btn btn-primary btn-sm">Checkout</a>
                        </div>
                    </div>
                `;
            } else {
                miniCartHtml = '<div class="mini-cart-empty">Your cart is empty</div>';
            }
            
            $('.mini-cart-content').html(miniCartHtml);
        }
    });
}

// Handle mini cart item removal
$(document).on('click', '.remove-mini-item', function(e) {
    e.preventDefault();
    const productId = $(this).data('product-id');
    removeFromCart(productId);
});

// Show mini cart on hover
$('.cart-icon').on('mouseenter', function() {
    updateMiniCart();
    $('.mini-cart').fadeIn(200);
}).on('mouseleave', function() {
    setTimeout(() => {
        if (!$('.mini-cart:hover').length) {
            $('.mini-cart').fadeOut(200);
        }
    }, 300);
});

$('.mini-cart').on('mouseleave', function() {
    $(this).fadeOut(200);
});

// Save cart to localStorage for guest users
function saveGuestCart() {
    if (!isUserLoggedIn()) {
        const cartData = [];
        $('.cart-item').each(function() {
            const productId = $(this).data('product-id');
            const quantity = $(this).find('.cart-quantity').val();
            cartData.push({ product_id: productId, quantity: quantity });
        });
        localStorage.setItem('guest_cart', JSON.stringify(cartData));
    }
}

// Load guest cart
function loadGuestCart() {
    if (!isUserLoggedIn()) {
        const guestCart = localStorage.getItem('guest_cart');
        if (guestCart) {
            const cartData = JSON.parse(guestCart);
            // Process guest cart data
            cartData.forEach(item => {
                // Add items to display (this would need server-side support)
            });
        }
    }
}

// Transfer guest cart to user account on login
function transferGuestCart() {
    const guestCart = localStorage.getItem('guest_cart');
    if (guestCart && isUserLoggedIn()) {
        $.post('ajax/transfer-guest-cart.php', {
            cart_data: guestCart
        }, function(response) {
            if (response.success) {
                localStorage.removeItem('guest_cart');
                updateCartCount();
                updateCartDisplay();
            }
        });
    }
}