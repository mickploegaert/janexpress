var cartItems = document.querySelector('#cart-items');
var emptyCart = document.querySelector('#empty-cart');
var cartSummary = document.querySelector('#cart-summary');
var cartSubtotal = document.querySelector('#cart-subtotal');
var cartTotal = document.querySelector('#cart-total');
var confirmation = document.querySelector('#order-confirmation');
var confirmOrder = document.querySelector('#confirm-order');
var activeCard = null;
var modalCart = document.querySelector('.modal-cart');
var cartMessage = document.querySelector('#cart-message');
var messageTimer;

function addToCart(card) {
    var cart = getCart();
    var productId = Number(card.dataset.id);
    var product = cart.find(function (item) {
        return item.id === productId;
    });

    if (product) {
        product.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: card.dataset.name,
            price: Number(card.dataset.priceValue),
            image: card.dataset.image,
            quantity: 1
        });
    }

    saveCart(cart);
    showCartMessage();
}

function showCartMessage() {
    if (!cartMessage) {
        return;
    }

    clearTimeout(messageTimer);
    cartMessage.hidden = false;
    messageTimer = setTimeout(function () {
        cartMessage.hidden = true;
    }, 2500);
}

function getCart() {
    return JSON.parse(localStorage.getItem('janexpress-cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('janexpress-cart', JSON.stringify(cart));
}

function showPrice(price) {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

function showCart() {
    var cart = getCart();
    var total = 0;

    cartItems.innerHTML = '';

    cart.forEach(function (item, index) {
        total += item.price * item.quantity;

        cartItems.innerHTML +=
            '<article class="cart-item">' +
            '<img class="cart-item-image" src="' + item.image + '" alt="' + item.name + '">' +
            '<div><h2>' + item.name + '</h2>' +
            '<div class="cart-item-price">' + showPrice(item.price) + ' per stuk</div>' +
            '<button class="remove-item" data-remove="' + index + '">Verwijderen</button></div>' +
            '<div class="quantity">' +
            '<button data-decrease="' + index + '">−</button>' +
            '<span>' + item.quantity + '</span>' +
            '<button data-increase="' + index + '">+</button>' +
            '</div></article>';
    });

    emptyCart.hidden = cart.length > 0;
    cartSummary.hidden = cart.length === 0;
    cartSubtotal.textContent = showPrice(total);
    cartTotal.textContent = showPrice(total);
}

if (cartItems) {
    cartItems.addEventListener('click', function (event) {
        var button = event.target;
        var index = button.dataset.remove || button.dataset.decrease || button.dataset.increase;
        var cart = getCart();

        if (index === undefined) {
            return;
        }

        if (button.dataset.remove) {
            cart.splice(index, 1);
        }

        if (button.dataset.decrease) {
            cart[index].quantity -= 1;
            if (cart[index].quantity === 0) {
                cart.splice(index, 1);
            }
        }

        if (button.dataset.increase) {
            cart[index].quantity += 1;
        }

        saveCart(cart);
        showCart();
    });

    showCart();

    confirmation.addEventListener('change', function () {
        confirmOrder.disabled = !confirmation.checked;
    });
}

document.querySelectorAll('.add').forEach(function (button) {
    button.addEventListener('click', function () {
        addToCart(button.closest('.card'));
    });
});

document.querySelectorAll('.product-image-button').forEach(function (button) {
    button.addEventListener('click', function () {
        activeCard = button.closest('.card');
        modalCart.disabled = Number(activeCard.dataset.stock) < 1;
    });
});

if (modalCart) {
    modalCart.addEventListener('click', function () {
        if (activeCard) {
            addToCart(activeCard);
        }
    });
}
