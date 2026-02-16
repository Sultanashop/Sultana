// Gestion du panier
let cart = [];
let cartCount = 0;

// Base de données fictive d'utilisateurs
const fakeUsers = [
    { name: "Ahmed Benali", email: "ahmed.benali@email.com", password: "password123" },
    { name: "Fatima Alami", email: "fatima.alami@email.com", password: "azerty456" },
    { name: "Mohammed Karim", email: "mohammed.karim@email.com", password: "qwerty789" },
    { name: "Amina Said", email: "amina.said@email.com", password: "sultan123" },
    { name: "Youssef Mansour", email: "youssef.mansour@email.com", password: "luxury456" }
];

// Base de données de produits
const products = [
    { name: "Caftan Royal", price: "100 €", category: "caftan", description: "Caftan traditionnel marocain" },
    { name: "Abaya Élégante", price: "1 800 €", category: "abaya", description: "Abaya moderne et élégante" },
    { name: "Djellaba Luxe", price: "1 200 €", category: "djellaba", description: "Djellaba de luxe" },
    { name: "Thobe Traditionnel", price: "950 €", category: "thobe", description: "Thobe traditionnel arabe" }
];

// Gestion des utilisateurs
let users = JSON.parse(localStorage.getItem('users')) || [...fakeUsers];
let currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateAccountButton();
    
    // Gestion des produits
    document.querySelectorAll('.btn-add-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('h3').textContent;
            const productPrice = productCard.querySelector('.price').textContent;
            
            addToCart(productName, productPrice);
        });
    });
    
    // Navigation smooth
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Fonctions du panier
function addToCart(name, price) {
    const existingItem = cart.find(item => item.name === name);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            name: name,
            price: price,
            quantity: 1
        });
    }
    
    updateCartCount();
    showNotification(`${name} ajouté au panier!`);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartCount();
    updateCartDisplay();
}

function updateCartCount() {
    cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const cartElement = document.querySelector('.cart-count');
    if (cartElement) {
        cartElement.textContent = cartCount;
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background-color: #ffd700;
        color: #000000;
        padding: 1rem 1.5rem;
        border-radius: 4px;
        font-weight: 600;
        z-index: 3000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Fonctions d'authentification
function showLogin() {
    document.getElementById('authModal').style.display = 'block';
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
}

function showRegister() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
}

function closeModal() {
    document.getElementById('authModal').style.display = 'none';
}

function handleLogin(event) {
    event.preventDefault();
    
    const email = event.target.querySelector('input[type="email"]').value;
    const password = event.target.querySelector('input[type="password"]').value;
    
    const user = users.find(u => u.email === email && u.password === password);
    
    if (user) {
        currentUser = user;
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        updateAccountButton();
        closeModal();
        showNotification(`Bienvenue ${user.name}!`);
        event.target.reset();
    } else {
        showNotification('Email ou mot de passe incorrect');
    }
}

function handleRegister(event) {
    event.preventDefault();
    
    const name = event.target.querySelector('input[type="text"]').value;
    const email = event.target.querySelector('input[type="email"]').value;
    const password = event.target.querySelector('input[type="password"]').value;
    const confirmPassword = event.target.querySelectorAll('input[type="password"]')[1].value;
    
    if (password !== confirmPassword) {
        showNotification('Les mots de passe ne correspondent pas');
        return;
    }
    
    if (users.find(u => u.email === email)) {
        showNotification('Cet email est déjà utilisé');
        return;
    }
    
    const newUser = {
        name: name,
        email: email,
        password: password,
        createdAt: new Date().toISOString()
    };
    
    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));
    
    showNotification('Inscription réussie! Vous pouvez maintenant vous connecter.');
    showLogin();
    event.target.reset();
}

function updateAccountButton() {
    const accountButton = document.querySelector('.btn-account');
    
    if (currentUser) {
        accountButton.textContent = currentUser.name;
        accountButton.onclick = function() {
            if (confirm('Voulez-vous vous déconnecter?')) {
                logout();
            }
        };
    } else {
        accountButton.textContent = 'Compte';
        accountButton.onclick = showLogin;
    }
}

function logout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    updateAccountButton();
    showNotification('Vous êtes déconnecté');
}

// Gestion du panier (affichage)
function showCart() {
    if (cart.length === 0) {
        showNotification('Votre panier est vide');
        return;
    }
    
    let cartHTML = '<div class="cart-modal"><h3>Votre Panier</h3><div class="cart-items">';
    let total = 0;
    
    cart.forEach((item, index) => {
        const itemPrice = parseFloat(item.price.replace('€', '').replace(' ', ''));
        const itemTotal = itemPrice * item.quantity;
        total += itemTotal;
        
        cartHTML += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>${item.price} x ${item.quantity}</p>
                </div>
                <div class="cart-item-actions">
                    <button onclick="removeFromCart(${index})" class="btn-remove">Supprimer</button>
                </div>
            </div>
        `;
    });
    
    cartHTML += `</div><div class="cart-total"><strong>Total: ${total.toFixed(2)} €</strong></div>`;
    cartHTML += '<button class="btn-primary" onclick="checkout()">Passer la commande</button></div>';
    
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = cartHTML;
    modal.style.display = 'block';
    
    modal.onclick = function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    };
    
    document.body.appendChild(modal);
}

function checkout() {
    if (!currentUser) {
        showNotification('Veuillez vous connecter pour passer une commande');
        showLogin();
        return;
    }
    
    if (cart.length === 0) {
        showNotification('Votre panier est vide');
        return;
    }
    
    // Simulation de commande
    const order = {
        user: currentUser,
        items: cart,
        total: cart.reduce((sum, item) => {
            const price = parseFloat(item.price.replace('€', '').replace(' ', ''));
            return sum + (price * item.quantity);
        }, 0),
        date: new Date().toISOString()
    };
    
    // Sauvegarder la commande (simulation)
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    orders.push(order);
    localStorage.setItem('orders', JSON.stringify(orders));
    
    showNotification('Commande passée avec succès! Merci pour votre achat.');
    cart = [];
    updateCartCount();
    
    // Fermer le modal du panier
    document.querySelector('.cart-modal').parentElement.remove();
}

// Styles additionnels pour les notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .cart-modal {
        background-color: #1a1a1a;
        border: 1px solid #ffd700;
        border-radius: 8px;
        padding: 2rem;
        max-width: 500px;
        margin: 10% auto;
        position: relative;
    }
    
    .cart-modal h3 {
        color: #ffd700;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .cart-items {
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: 1rem;
    }
    
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #333;
    }
    
    .cart-item-info h4 {
        color: #ffffff;
        margin-bottom: 0.5rem;
    }
    
    .cart-item-info p {
        color: #cccccc;
    }
    
    .btn-remove {
        background-color: #dc3545;
        color: #ffffff;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .btn-remove:hover {
        background-color: #c82333;
    }
    
    .cart-total {
        text-align: right;
        margin: 1rem 0;
        padding: 1rem;
        border-top: 1px solid #333;
    }
    
    .cart-total strong {
        color: #ffd700;
        font-size: 1.2rem;
    }
`;
document.head.appendChild(style);

// Gestion du clic sur le bouton panier
document.querySelector('.btn-cart').addEventListener('click', showCart);

// Fonction de recherche
function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput.value.toLowerCase().trim();

    if (searchTerm === '') {
        showNotification('Veuillez entrer un terme de recherche');
        return;
    }

    const filteredProducts = products.filter(product => 
        product.name.toLowerCase().includes(searchTerm) ||
        product.category.toLowerCase().includes(searchTerm) ||
        product.description.toLowerCase().includes(searchTerm)
    );

    if (filteredProducts.length === 0) {
        showNotification('Aucun produit trouvé pour: ' + searchTerm);
        return;
    }

    displaySearchResults(filteredProducts, searchTerm);
}

function displaySearchResults(results, searchTerm) {
    const productsSection = document.getElementById('products');
    const productGrid = productsSection.querySelector('.product-grid');

    // Vider la grille actuelle
    productGrid.innerHTML = '';

    // Créer les cartes de produits pour les résultats
    results.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <div class="product-image">
                <img src="https://via.placeholder.com/300x400/1a1a1a/ffd700?text=${encodeURIComponent(product.name)}" alt="${product.name}">
                <div class="product-overlay">
                    <button class="btn-add-cart" onclick="addToCart('${product.name}', '${product.price}')">Ajouter au panier</button>
                </div>
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="price">${product.price}</p>
            </div>
        `;
        productGrid.appendChild(productCard);
    });

    // Mettre à jour le titre
    const sectionTitle = productsSection.querySelector('.section-title');
    sectionTitle.textContent = `Résultats pour "${searchTerm}" (${results.length} produit${results.length > 1 ? 's' : ''})`;

    // Faire défiler vers la section produits
    productsSection.scrollIntoView({ behavior: 'smooth' });

    showNotification(`${results.length} produit${results.length > 1 ? 's' : ''} trouvé${results.length > 1 ? 's' : ''}`);
}

// Gestion de la recherche avec la touche Entrée
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
});
