var activeCategory = 'alles';
var searchQuery = '';

function updateVisibleCards() {
    document.querySelectorAll('.card').forEach(function (card) {
        var cardCategory = card.dataset.category.toLowerCase();
        var cardName = card.dataset.name.toLowerCase();
        var matchesCategory = activeCategory === 'alles' || activeCategory === cardCategory;
        var matchesSearch = searchQuery === '' || cardName.includes(searchQuery);
        card.classList.toggle('hidden', !matchesCategory || !matchesSearch);
    });
}

function updateClearFiltersVisibility() {
    var clearFilters = document.querySelector('.clear-filters');
    if (clearFilters) {
        clearFilters.hidden = activeCategory === 'alles' && searchQuery === '';
    }
}

document.querySelectorAll('.filter').forEach(function (filter) {
    filter.addEventListener('click', function () {
        document.querySelectorAll('.filter').forEach(function (button) {
            button.classList.remove('active');
        });
        filter.classList.add('active');

        activeCategory = filter.textContent.trim().toLowerCase();
        updateVisibleCards();
        updateClearFiltersVisibility();
    });
});

var search = document.querySelector('.search input');
if (search) {
    search.addEventListener('input', function () {
        searchQuery = search.value.toLowerCase().trim();
        updateVisibleCards();
        updateClearFiltersVisibility();
    });
}

var clearFilters = document.querySelector('.clear-filters');
if (clearFilters) {
    clearFilters.addEventListener('click', function () {
        activeCategory = 'alles';
        searchQuery = '';
        document.querySelector('.filter').classList.add('active');
        document.querySelectorAll('.filter:not(:first-child)').forEach(function (filter) {
            filter.classList.remove('active');
        });
        if (search) {
            search.value = '';
        }
        updateVisibleCards();
        updateClearFiltersVisibility();
    });
}

var modal = document.querySelector('.product-modal');
var modalImage = document.querySelector('#modal-image');
var modalCategory = document.querySelector('#modal-category');
var modalName = document.querySelector('#modal-name');
var modalDescription = document.querySelector('#modal-description');
var modalPrice = document.querySelector('#modal-price');
var modalStock = document.querySelector('#modal-stock');

document.querySelectorAll('.product-image-button').forEach(function (button) {
    button.addEventListener('click', function () {
        var card = button.closest('.card');
        var stock = Number(card.dataset.stock);

        modalImage.src = card.dataset.image;
        modalImage.alt = card.dataset.name;
        modalCategory.textContent = card.dataset.category;
        modalName.textContent = card.dataset.name;
        modalDescription.textContent = card.dataset.description;
        modalPrice.textContent = card.dataset.price;
        modalStock.textContent = stock > 0
            ? 'Op voorraad - nog ' + stock + ' beschikbaar'
            : 'Tijdelijk uitverkocht';
        modalStock.classList.toggle('out-of-stock', stock < 1);
        modal.hidden = false;
        document.body.classList.add('modal-open');
    });
});

document.querySelectorAll('[data-modal-close]').forEach(function (element) {
    element.addEventListener('click', function () {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
    });
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && modal && !modal.hidden) {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
    }
});

updateClearFiltersVisibility();
