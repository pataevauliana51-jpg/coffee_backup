let cart = [];

function loadCart() {
    var saved = localStorage.getItem('cart');
    if (saved) {
        cart = JSON.parse(saved);
    }
    updateCartCount();
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    var count = cart.reduce(function(sum, item) {
        return sum + item.quantity;
    }, 0);
    var badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

function sortCartByName() {
    cart.sort(function(a, b) {
        return a.name.localeCompare(b.name);
    });
    saveCart();
    renderCart();
}

function sortByPrice() {
    cart.sort(function(a, b) {
        return a.price - b.price;
    });
    saveCart();
    renderCart();
}

function addToCart(name, price, image, size) {
    var key = name + size;
    var existing = cart.find(function(item) {
        return item.key === key;
    });
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({
            key: key,
            name: name,
            price: price,
            image: image,
            size: size,
            quantity: 1
        });
    }
    saveCart();
    alert('✅ "' + name + '" добавлен в корзину!');
}

function removeFromCart(key) {
    cart = cart.filter(function(item) {
        return item.key !== key;
    });
    saveCart();
    renderCart();
}

function changeQuantity(key, delta) {
    var item = cart.find(function(i) {
        return i.key === key;
    });
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            removeFromCart(key);
            return;
        }
        saveCart();
        renderCart();
    }
}

function clearCart() {
    if (confirm('Очистить корзину?')) {
        cart = [];
        saveCart();
        renderCart();
    }
}

function getTotal() {
    return cart.reduce(function(sum, item) {
        return sum + item.price * item.quantity;
    }, 0);
}

function renderCart() {
    var container = document.getElementById('cart-items');
    var totalEl = document.getElementById('cart-total');
    var emptyEl = document.getElementById('cart-empty');
    var formEl = document.getElementById('order-form');

    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '';
        if (emptyEl) emptyEl.style.display = 'block';
        if (totalEl) totalEl.textContent = '0 ₽';
        if (formEl) formEl.style.display = 'none';
        return;
    }

    if (emptyEl) emptyEl.style.display = 'none';
    if (formEl) formEl.style.display = 'block';

    var html = '';
    cart.forEach(function(item) {
        html += '<div class="cart-item" data-key="' + item.key + '">';
        html += '<div class="cart-item-image"><img src="' + item.image + '" alt="' + item.name + '"></div>';
        html += '<div class="cart-item-info">';
        html += '<div class="cart-item-name">' + item.name + '</div>';
        if (item.size) {
            html += '<div class="cart-item-size">' + item.size + '</div>';
        }
        html += '<div class="cart-item-price">' + item.price + ' ₽</div>';
        html += '</div>';
        html += '<div class="cart-item-actions">';
        html += '<button onclick="changeQuantity(\'' + item.key + '\', -1)">−</button>';
        html += '<span class="cart-item-qty">' + item.quantity + '</span>';
        html += '<button onclick="changeQuantity(\'' + item.key + '\', 1)">+</button>';
        html += '<button class="cart-item-remove" onclick="removeFromCart(\'' + item.key + '\')">✕</button>';
        html += '</div>';
        html += '</div>';
    });

    container.innerHTML = html;
    if (totalEl) totalEl.textContent = getTotal() + ' ₽';
}

function submitOrder() {
    if (cart.length === 0) {
        alert('Корзина пуста!');
        return;
    }

    var form = document.getElementById('order-form');
    var name = document.getElementById('order-name').value.trim();
    var phone = document.getElementById('order-phone').value.trim();
    var address = document.getElementById('order-address').value.trim();
    var comment = document.getElementById('order-comment').value.trim();

    if (!name || !phone || !address) {
        alert('Заполните все обязательные поля!');
        return;
    }

    var orderData = {
        customer: {
            name: name,
            phone: phone,
            address: address,
            comment: comment
        },
        items: cart,
        total: getTotal(),
        date: new Date().toLocaleString()
    };

    var orders = JSON.parse(localStorage.getItem('orders') || '[]');
    orders.push(orderData);
    localStorage.setItem('orders', JSON.stringify(orders));

    cart = [];
    saveCart();
    renderCart();

    alert('✅ Заказ оформлен! Спасибо!');
    form.reset();
    window.location.href = 'orders.html';
}

document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    if (document.getElementById('cart-items')) {
        renderCart();
    }
});
