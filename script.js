const marketplaceRoot = document.querySelector('#productsGrid');
const adminAccessApp = document.querySelector('#adminAccessApp');
const authApp = document.querySelector('#authApp');
const buyerDashboard = document.querySelector('#buyerDashboard');
const adminAssociationDashboard = document.querySelector('#adminAssociationDashboard');
const merchantDashboard = document.querySelector('#merchantDashboard');
const superadminDashboard = document.querySelector('#superadminDashboard');
const paymentApp = document.querySelector('#paymentApp');
const buyerCartStorageKey = 'foodloopBuyerCart';
const adminCartStorageKey = 'foodloopAdminAssociationCart';
const checkoutStorageKey = 'foodloopCheckoutCart';
const checkoutReturnKey = 'foodloopCheckoutReturnPage';
const superadminRoleKey = 'role';
const ADMIN_CODE = 'FOODLOOP-ADMIN-2026';
const currentUserStorageKey = 'currentUser';
const usersStorageKey = 'foodloopUsers';
const defaultUsers = [
    { email: 'test@user.com', password: '1234', role: 'acheteur' },
    { email: 'admin@assoc.com', password: '1234', role: 'admin_association' },
    { email: 'shop@store.com', password: '1234', role: 'commerce' }
];
const roleRoutes = {
    acheteur: 'buyer_dashboard.html',
    admin_association: 'admin_association_dashboard.html',
    commerce: 'merchant_dashboard.html',
    superadmin: 'superadmin_dashboard.html'
};

function getStoredUsers() {
    const storedUsers = loadStoredArray(usersStorageKey);
    if (storedUsers.length === 0) {
        persistStoredArray(usersStorageKey, defaultUsers);
        return [...defaultUsers];
    }

    return storedUsers;
}

function getCurrentUser() {
    try {
        const raw = window.localStorage.getItem(currentUserStorageKey);
        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : null;
    } catch (error) {
        return null;
    }
}

function setCurrentUser(user) {
    window.localStorage.setItem(currentUserStorageKey, JSON.stringify(user));
}

function getRouteForRole(role) {
    return roleRoutes[role] || 'login_signup.html';
}

function redirectToRoleHome(role) {
    window.location.href = getRouteForRole(role);
}

function requireAuthenticatedUser() {
    const user = getCurrentUser();
    if (!user) {
        window.location.href = 'login_signup.html';
        return null;
    }

    return user;
}

function requireRole(expectedRole) {
    const user = requireAuthenticatedUser();
    if (!user) {
        return null;
    }

    if (user.role !== expectedRole) {
        redirectToRoleHome(user.role);
        return null;
    }

    return user;
}

function loadStoredArray(storageKey) {
    try {
        const raw = window.localStorage.getItem(storageKey);
        if (!raw) {
            return [];
        }

        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
        return [];
    }
}

function persistStoredArray(storageKey, value) {
    window.localStorage.setItem(storageKey, JSON.stringify(value));
}

function setCheckoutState(cartItems, returnPage) {
    persistStoredArray(checkoutStorageKey, cartItems);
    window.localStorage.setItem(checkoutReturnKey, returnPage);
}

if (adminAccessApp) {
    const adminAccessForm = document.querySelector('#adminAccessForm');
    const adminCodeInput = document.querySelector('#adminCodeInput');
    const adminAccessMessage = document.querySelector('#adminAccessMessage');

    adminAccessForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const submittedCode = adminCodeInput instanceof HTMLInputElement ? adminCodeInput.value.trim() : '';

        if (submittedCode === ADMIN_CODE) {
            const superadminUser = {
                email: 'superadmin@foodloop.com',
                password: ADMIN_CODE,
                role: 'superadmin'
            };
            window.localStorage.setItem(superadminRoleKey, 'superadmin');
            setCurrentUser(superadminUser);
            adminAccessMessage.textContent = '';
            adminAccessApp.classList.add('is-redirecting');
            window.setTimeout(() => {
                window.location.href = 'superadmin_dashboard.html';
            }, 180);
            return;
        }

        adminAccessMessage.textContent = 'Code incorrect';
    });
}

if (marketplaceRoot) {
    const isLoggedIn = getCurrentUser() !== null;
    const products = [
        {
            id: 1,
            title: 'Panier fruits & legumes',
            description: 'Un melange frais de fruits et legumes de saison a recuperer avant la fermeture.',
            location: 'Tunis Centre',
            zone: 'Tunis',
            type: 'Epicerie',
            distance: 2,
            image: 'https://images.unsplash.com/photo-1765480953875-a7338f896e91?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 2,
            title: 'Viennoiseries du matin',
            description: 'Croissants, pains au chocolat et mini brioches disponibles en lot du jour.',
            location: 'La Marsa',
            zone: 'La Marsa',
            type: 'Boulangerie',
            distance: 5,
            image: 'https://images.unsplash.com/photo-1774043132154-8934327dee3a?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 3,
            title: 'Repas chauds solidaires',
            description: 'Des portions cuisinees pretes a etre redistribuees rapidement a proximite.',
            location: 'Sfax Medina',
            zone: 'Sfax',
            type: 'Restauration',
            distance: 8,
            image: 'https://images.unsplash.com/photo-1568897798550-91c8caffe391?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 4,
            title: 'Produits frais du soir',
            description: 'Selection de yaourts, salades et desserts a courte duree de vie.',
            location: 'Sousse Ville',
            zone: 'Sousse',
            type: 'Frais',
            distance: 3,
            image: 'https://images.unsplash.com/photo-1612383277710-67896ecf4c69?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 5,
            title: 'Pains invendus artisanaux',
            description: 'Pain complet, baguettes et fougasses encore disponibles pour reservation.',
            location: 'Nabeul',
            zone: 'Nabeul',
            type: 'Boulangerie',
            distance: 10,
            image: 'https://images.unsplash.com/photo-1774043132154-8934327dee3a?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 6,
            title: 'Box repas vegetarienne',
            description: 'Portions pretes a emporter avec legumes rotis, riz et sauce maison.',
            location: 'Ariana',
            zone: 'Ariana',
            type: 'Restauration',
            distance: 4,
            image: 'https://images.unsplash.com/photo-1568897798550-91c8caffe391?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 7,
            title: 'Corbeille petit dejeuner',
            description: 'Fruits coupes, yaourts et mini cakes pour une recuperation rapide.',
            location: 'Bizerte',
            zone: 'Bizerte',
            type: 'Frais',
            distance: 12,
            image: 'https://images.unsplash.com/photo-1612383277710-67896ecf4c69?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 8,
            title: 'Lot epicerie locale',
            description: 'Conserves, biscuits et produits secs approchant leur date optimale.',
            location: 'Monastir',
            zone: 'Monastir',
            type: 'Epicerie',
            distance: 6,
            image: 'https://images.unsplash.com/photo-1584093092919-3d551a9c5055?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        }
    ];

    const state = {
        visibleCount: 6,
        selectedProduct: null
    };

    const searchInput = document.querySelector('#searchInput');
    const zoneFilter = document.querySelector('#zoneFilter');
    const typeFilter = document.querySelector('#typeFilter');
    const distanceFilter = document.querySelector('#distanceFilter');
    const productsGrid = document.querySelector('#productsGrid');
    const resultsCount = document.querySelector('#resultsCount');
    const loadMoreButton = document.querySelector('#loadMoreButton');
    const reservationModal = document.querySelector('#reservationModal');
    const modalTitle = document.querySelector('#modalTitle');
    const modalDescription = document.querySelector('#modalDescription');
    const modalActionButton = document.querySelector('#modalActionButton');
    const authRequiredModal = document.querySelector('#authRequiredModal');

    function loadProducts() {
        populateFilterOptions();
        renderProducts(filterProducts());
    }

    function renderProducts(filteredProducts) {
        const visibleProducts = filteredProducts.slice(0, state.visibleCount);
        productsGrid.innerHTML = '';

        if (visibleProducts.length === 0) {
            productsGrid.innerHTML = '<div class="empty-state">Aucun produit ne correspond a votre recherche pour le moment.</div>';
            resultsCount.textContent = '0 resultat';
            loadMoreButton.hidden = true;
            return;
        }

        visibleProducts.forEach((product) => {
            const article = document.createElement('article');
            article.className = 'product-card';
            article.innerHTML = `
                <img class="product-image" src="${product.image}" alt="${product.title}">
                <div class="product-content">
                    <div class="product-tags">
                        <span>${product.type}</span>
                        <span class="distance-pill">${product.distance} km</span>
                    </div>
                    <h3 class="product-title">${product.title}</h3>
                    <p class="product-description">${product.description}</p>
                    <p class="product-location">Lieu: ${product.location}</p>
                    <button class="reserve-button" type="button" data-product-id="${product.id}">Reserver</button>
                </div>
            `;

            productsGrid.appendChild(article);
        });

        const totalText = filteredProducts.length > 1 ? 'resultats' : 'resultat';
        resultsCount.textContent = `${filteredProducts.length} ${totalText}`;
        loadMoreButton.hidden = filteredProducts.length <= state.visibleCount;
    }

    function filterProducts() {
        const keyword = searchInput.value.trim().toLowerCase();
        const selectedZone = zoneFilter.value;
        const selectedType = typeFilter.value;
        const selectedDistance = distanceFilter.value === '' ? null : Number(distanceFilter.value);

        return products.filter((product) => {
            const matchesKeyword =
                product.title.toLowerCase().includes(keyword) ||
                product.description.toLowerCase().includes(keyword) ||
                product.location.toLowerCase().includes(keyword) ||
                product.type.toLowerCase().includes(keyword);

            const matchesZone = selectedZone === '' || product.zone === selectedZone;
            const matchesType = selectedType === '' || product.type === selectedType;
            const matchesDistance = selectedDistance === null || product.distance <= selectedDistance;

            return matchesKeyword && matchesZone && matchesType && matchesDistance;
        });
    }

    function populateFilterOptions() {
        const zones = [...new Set(products.map((product) => product.zone))];
        const types = [...new Set(products.map((product) => product.type))];

        zones.forEach((zone) => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            zoneFilter.appendChild(option);
        });

        types.forEach((type) => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            typeFilter.appendChild(option);
        });
    }

    function openReservationModal(productId) {
        const product = products.find((item) => item.id === Number(productId));
        if (!product) {
            return;
        }

        state.selectedProduct = product;
        modalTitle.textContent = product.title;
        modalDescription.textContent = `${product.description} Retrait prevu a ${product.location}.`;
        reservationModal.classList.add('is-open');
        reservationModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        console.log(`Reservation simulee pour: ${product.title}`);
    }

    function closeReservationModal() {
        reservationModal.classList.remove('is-open');
        reservationModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function openAuthRequiredModal() {
        if (!authRequiredModal) {
            return;
        }

        authRequiredModal.classList.add('is-open');
        authRequiredModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeAuthRequiredModal() {
        if (!authRequiredModal) {
            return;
        }

        authRequiredModal.classList.remove('is-open');
        authRequiredModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function refreshProducts() {
        state.visibleCount = 6;
        renderProducts(filterProducts());
    }

    searchInput.addEventListener('input', refreshProducts);
    zoneFilter.addEventListener('change', refreshProducts);
    typeFilter.addEventListener('change', refreshProducts);
    distanceFilter.addEventListener('change', refreshProducts);

    productsGrid.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('[data-product-id]')) {
            if (isLoggedIn === false) {
                openAuthRequiredModal();
                return;
            }
            openReservationModal(target.dataset.productId);
        }
    });

    loadMoreButton.addEventListener('click', () => {
        state.visibleCount += 3;
        renderProducts(filterProducts());
    });

    reservationModal?.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.dataset.closeModal === 'true') {
            closeReservationModal();
        }
    });

    modalActionButton?.addEventListener('click', () => {
        if (state.selectedProduct !== null) {
            console.log(`Confirmation de reservation pour: ${state.selectedProduct.title}`);
        }
        closeReservationModal();
    });

    authRequiredModal?.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-auth-modal') === 'true') {
            closeAuthRequiredModal();
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && reservationModal?.classList.contains('is-open')) {
            closeReservationModal();
        }
        if (event.key === 'Escape' && authRequiredModal?.classList.contains('is-open')) {
            closeAuthRequiredModal();
        }
    });

    loadProducts();
}

if (authApp) {
    const authTitle = document.querySelector('#authTitle');
    const authSubtitle = document.querySelector('#authSubtitle');
    const authStepIndicator = document.querySelector('#authStepIndicator');
    const authStepContent = document.querySelector('#authStepContent');
    const users = getStoredUsers();

    const authState = {
        step: 'login',
        role: 'acheteur',
        errorMessage: ''
    };

    const formSchemas = {
        acheteur: {
            title: 'Creer un compte',
            subtitle: 'Acheteur',
            fields: [
                { label: 'Prenom', name: 'prenom', type: 'text', placeholder: 'Votre prenom' },
                { label: 'Nom', name: 'nom', type: 'text', placeholder: 'Votre nom' },
                { label: 'Email', name: 'email', type: 'email', placeholder: 'vous@foodloop.com' },
                { label: 'Mot de passe', name: 'password', type: 'password', placeholder: 'Votre mot de passe' },
                { label: 'Numero de telephone', name: 'telephone', type: 'tel', placeholder: 'Votre numero' },
                { label: 'Adresse', name: 'adresse', type: 'text', placeholder: 'Votre adresse' }
            ]
        },
        commerce: {
            title: 'Creer un compte commerce',
            subtitle: 'Proprietaire commerce',
            fields: [
                { label: 'Email', name: 'email', type: 'email', placeholder: 'commerce@foodloop.com' },
                { label: 'Mot de passe', name: 'password', type: 'password', placeholder: 'Votre mot de passe' },
                { label: 'Nom du commerce', name: 'nomCommerce', type: 'text', placeholder: 'Nom du commerce' },
                {
                    label: 'Type de commerce',
                    name: 'typeCommerce',
                    type: 'select',
                    options: ['Restaurant', 'Patisserie', 'Boulangerie', 'Epicerie', 'Supermarche']
                },
                { label: 'Adresse', name: 'adresse', type: 'text', placeholder: 'Adresse du commerce' },
                { label: 'Business licence', name: 'licence', type: 'text', placeholder: 'Numero de licence' }
            ]
        },
        admin_association: {
            title: 'Creer un compte association',
            subtitle: 'Admin association',
            fields: [
                { label: "Nom d'association", name: 'association', type: 'text', placeholder: "Nom de l'association" },
                { label: 'Adresse', name: 'adresse', type: 'text', placeholder: "Adresse de l'association" },
                { label: 'Email association', name: 'email', type: 'email', placeholder: 'association@foodloop.com' },
                { label: 'Mot de passe', name: 'password', type: 'password', placeholder: 'Votre mot de passe' },
                { label: 'Numero de telephone organisation', name: 'telephone', type: 'tel', placeholder: 'Numero de telephone' }
            ]
        }
    };

    function updateHeader() {
        const content = {
            login: {
                title: 'Se connecter',
                subtitle: 'Connectez-vous ou creez un compte en quelques etapes.'
            },
            role: {
                title: "S'inscrire",
                subtitle: 'Selectionnez votre role pour afficher le formulaire adapte.'
            },
            form: {
                title: formSchemas[authState.role].title,
                subtitle: `Parcours ${formSchemas[authState.role].subtitle.toLowerCase()} pret pour une integration PHP + Oracle.`
            }
        };

        authTitle.textContent = content[authState.step].title;
        authSubtitle.textContent = content[authState.step].subtitle;
    }

    function renderStepIndicator() {
        const steps = ['login', 'role', 'form'];
        authStepIndicator.innerHTML = steps.map((step) => {
            const active = step === authState.step ? ' is-active' : '';
            return `<span class="auth-step-dot${active}"></span>`;
        }).join('');
    }

    function renderField(field) {
        if (field.type === 'select') {
            const options = field.options.map((option) => `<option value="${option}">${option}</option>`).join('');
            return `
                <label class="auth-field">
                    <span>${field.label}</span>
                    <select name="${field.name}">
                        ${options}
                    </select>
                </label>
            `;
        }

        return `
            <label class="auth-field">
                <span>${field.label}</span>
                <input type="${field.type}" name="${field.name}" placeholder="${field.placeholder}">
            </label>
        `;
    }

    function renderLoginStep() {
        return `
            <div class="auth-form-panel">
                <form class="auth-form-stack" data-auth-action="login">
                    <div class="auth-form-heading">
                        <strong>Connexion</strong>
                        <span>Accedez a votre espace FoodLoop.</span>
                    </div>
                    <label class="auth-field">
                        <span>Email</span>
                        <input type="email" name="email" placeholder="vous@foodloop.com">
                    </label>
                    <label class="auth-field">
                        <span>Mot de passe</span>
                        <input type="password" name="password" placeholder="Votre mot de passe">
                    </label>
                    ${authState.errorMessage ? `<p class="inline-error">${authState.errorMessage}</p>` : ''}
                    <button class="auth-primary-button" type="submit">Se connecter</button>
                </form>
                <div class="auth-link-row">
                    <span>Vous n'avez pas de compte ?</span>
                    <button class="auth-secondary-button" type="button" data-auth-nav="role">S'inscrire</button>
                </div>
            </div>
        `;
    }

    function renderRoleStep() {
        return `
            <div class="auth-form-panel">
                <form class="auth-form-stack" data-auth-action="role">
                    <div class="auth-form-heading">
                        <strong>Choisissez votre role</strong>
                        <span>Le formulaire suivant s'adaptera automatiquement.</span>
                    </div>
                    <fieldset class="role-group">
                        <legend>S'inscrire</legend>
                        <label class="role-option">
                            <input type="radio" name="role" value="acheteur" ${authState.role === 'acheteur' ? 'checked' : ''}>
                            <span>Acheteur</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="commerce" ${authState.role === 'commerce' ? 'checked' : ''}>
                            <span>Proprietaire commerce</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="admin_association" ${authState.role === 'admin_association' ? 'checked' : ''}>
                            <span>Admin association</span>
                        </label>
                    </fieldset>
                    <button class="auth-primary-button" type="submit">Continuer</button>
                </form>
                <div class="auth-link-row">
                    <span>Deja un compte ?</span>
                    <button class="auth-secondary-button" type="button" data-auth-nav="login">Se connecter</button>
                </div>
            </div>
        `;
    }

    function renderDynamicFormStep() {
        const schema = formSchemas[authState.role];
        const fields = schema.fields.map(renderField).join('');

        return `
            <div class="auth-form-panel">
                <form class="auth-form-stack" data-auth-action="create-account">
                    <div class="auth-form-heading">
                        <strong>${schema.title}</strong>
                        <span>${schema.subtitle}</span>
                    </div>
                    ${fields}
                    <button class="auth-primary-button" type="submit">Creer le compte</button>
                </form>
                <p class="auth-success-note">La soumission simulera une redirection vers le futur dashboard.</p>
            </div>
        `;
    }

    function renderCurrentStep() {
        if (authState.step === 'login') {
            return renderLoginStep();
        }

        if (authState.step === 'role') {
            return renderRoleStep();
        }

        return renderDynamicFormStep();
    }

    function renderAuthApp() {
        updateHeader();
        renderStepIndicator();
        authStepContent.innerHTML = renderCurrentStep();
    }

    authStepContent.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const nextStep = target.getAttribute('data-auth-nav');
        if (nextStep === 'login' || nextStep === 'role') {
            authState.step = nextStep;
            authState.errorMessage = '';
            renderAuthApp();
        }
    });

    authStepContent.addEventListener('change', (event) => {
        const target = event.target;
        if (target instanceof HTMLInputElement && target.name === 'role') {
            authState.role = target.value;
        }
    });

    authStepContent.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const action = form.getAttribute('data-auth-action');

        if (action === 'login') {
            const formData = new FormData(form);
            const email = String(formData.get('email') || '').trim().toLowerCase();
            const password = String(formData.get('password') || '');
            const matchedUser = users.find((user) => user.email.toLowerCase() === email && user.password === password);

            if (!matchedUser) {
                authState.errorMessage = 'Email ou mot de passe incorrect';
                renderAuthApp();
                return;
            }

            authState.errorMessage = '';
            setCurrentUser(matchedUser);
            if (matchedUser.role === 'superadmin') {
                window.localStorage.setItem(superadminRoleKey, 'superadmin');
            }
            redirectToRoleHome(matchedUser.role);
            return;
        }

        if (action === 'role') {
            const formData = new FormData(form);
            authState.role = String(formData.get('role') || 'acheteur');
            authState.step = 'form';
            renderAuthApp();
            return;
        }

        if (action === 'create-account') {
            const formData = new FormData(form);
            const email = String(formData.get('email') || '').trim().toLowerCase();
            const password = String(formData.get('password') || '');
            const newUser = {
                email,
                password,
                role: authState.role
            };

            const nextUsers = users.filter((user) => user.email.toLowerCase() !== email);
            nextUsers.push(newUser);
            persistStoredArray(usersStorageKey, nextUsers);
            setCurrentUser(newUser);
            authState.errorMessage = '';
            redirectToRoleHome(newUser.role);
        }
    });

    renderAuthApp();
}

if (buyerDashboard && requireRole('acheteur')) {
    const cartStorageKey = 'foodloopBuyerCart';
    const buyer = {
        name: 'Amal Ben Ali',
        zone: 'Tunis Centre',
        preferredCategories: ['Epicerie', 'Frais', 'Boulangerie']
    };

    const dashboardProducts = [
        {
            id: 1,
            title: 'Panier fruits & legumes',
            description: 'Selection fraiche de saison pour une reservation rapide et solidaire.',
            location: 'Tunis Centre',
            zone: 'Tunis',
            category: 'Epicerie',
            distance: 2,
            price: '3.90 DT',
            pickupTime: '17:30 - 19:00',
            image: 'https://images.unsplash.com/photo-1765480953875-a7338f896e91?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 2,
            title: 'Viennoiseries du matin',
            description: 'Lot gourmand de croissants et mini brioches a petit prix.',
            location: 'La Marsa',
            zone: 'La Marsa',
            category: 'Boulangerie',
            distance: 5,
            price: '2.50 DT',
            pickupTime: '18:00 - 19:30',
            image: 'https://images.unsplash.com/photo-1774043132154-8934327dee3a?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 3,
            title: 'Repas chauds solidaires',
            description: 'Portions cuisinees disponibles pour recuperation avant fermeture.',
            location: 'Sfax Medina',
            zone: 'Sfax',
            category: 'Restauration',
            distance: 8,
            price: '4.20 DT',
            pickupTime: '12:30 - 14:00',
            image: 'https://images.unsplash.com/photo-1568897798550-91c8caffe391?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 4,
            title: 'Produits frais du soir',
            description: 'Yaourts, desserts et salades a tres petit prix.',
            location: 'Sousse Ville',
            zone: 'Sousse',
            category: 'Frais',
            distance: 3,
            price: '2.10 DT',
            pickupTime: '19:00 - 20:00',
            image: 'https://images.unsplash.com/photo-1612383277710-67896ecf4c69?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 5,
            title: 'Lot epicerie locale',
            description: 'Produits secs et conserves proches de la date optimale.',
            location: 'Monastir',
            zone: 'Monastir',
            category: 'Epicerie',
            distance: 6,
            price: '3.10 DT',
            pickupTime: '16:00 - 18:30',
            image: 'https://images.unsplash.com/photo-1584093092919-3d551a9c5055?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 6,
            title: 'Box repas vegetarienne',
            description: 'Legumes rotis, riz et sauce maison dans une box prete a retirer.',
            location: 'Ariana',
            zone: 'Ariana',
            category: 'Restauration',
            distance: 4,
            price: '4.50 DT',
            pickupTime: '13:00 - 15:00',
            image: 'https://images.unsplash.com/photo-1568897798550-91c8caffe391?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        }
    ];

    const dashboardState = {
        currentView: 'profile',
        cart: loadStoredCart(),
        notifications: [
            { id: 1, title: 'Reservation confirmee', message: 'Votre panier fruits & legumes est confirme pour 18h00.' },
            { id: 2, title: 'Rappel pickup', message: 'N oubliez pas de recuperer votre lot de viennoiseries avant 19h.' },
            { id: 3, title: 'Message admin', message: 'Un nouveau point de collecte est disponible a Tunis Centre.' }
        ],
        reservations: [1, 4],
        history: [2]
    };

    const dashboardSearch = document.querySelector('#dashboardSearch');
    const feedSearch = document.querySelector('#feedSearch');
    const zoneSelect = document.querySelector('#dashboardZone');
    const categorySelect = document.querySelector('#dashboardCategory');
    const distanceSelect = document.querySelector('#dashboardDistance');
    const contentRoot = document.querySelector('#dashboardContent');
    const viewTitle = document.querySelector('#viewTitle');
    const viewDescription = document.querySelector('#viewDescription');
    const viewKicker = document.querySelector('#viewKicker');
    const cartCount = document.querySelector('#cartCount');
    const notificationCount = document.querySelector('#notificationCount');
    const cartButton = document.querySelector('#cartButton');
    const notificationsButton = document.querySelector('#notificationsButton');
    const logoutButton = document.querySelector('#logoutButton');
    const panel = document.querySelector('#dashboardPanel');
    const panelTitle = document.querySelector('#panelTitle');
    const panelContent = document.querySelector('#panelContent');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    function loadStoredCart() {
        try {
            const raw = window.localStorage.getItem(cartStorageKey);
            if (!raw) {
                return [];
            }
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function persistCart() {
        window.localStorage.setItem(cartStorageKey, JSON.stringify(dashboardState.cart));
    }

    function populateDashboardFilters() {
        const zones = [...new Set(dashboardProducts.map((product) => product.zone))];
        const categories = [...new Set(dashboardProducts.map((product) => product.category))];

        zones.forEach((zone) => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            zoneSelect.appendChild(option);
        });

        categories.forEach((category) => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });
    }

    function filterProducts(sourceProducts = dashboardProducts) {
        const term = `${dashboardSearch.value} ${feedSearch.value}`.trim().toLowerCase();
        const zone = zoneSelect.value;
        const category = categorySelect.value;
        const distance = distanceSelect.value === '' ? null : Number(distanceSelect.value);

        return sourceProducts.filter((product) => {
            const matchesTerm =
                term === '' ||
                product.title.toLowerCase().includes(term) ||
                product.description.toLowerCase().includes(term) ||
                product.location.toLowerCase().includes(term) ||
                product.category.toLowerCase().includes(term);

            const matchesZone = zone === '' || product.zone === zone;
            const matchesCategory = category === '' || product.category === category;
            const matchesDistance = distance === null || product.distance <= distance;

            return matchesTerm && matchesZone && matchesCategory && matchesDistance;
        });
    }

    function productCardMarkup(product) {
        return `
            <article class="dashboard-product-card">
                <img class="dashboard-product-image" src="${product.image}" alt="${product.title}">
                <div class="dashboard-product-body">
                    <div class="dashboard-meta-row">
                        <span>${product.category}</span>
                        <span>${product.distance} km</span>
                    </div>
                    <h3>${product.title}</h3>
                    <p>${product.description}</p>
                    <div class="dashboard-meta-row">
                        <span>Lieu: ${product.location}</span>
                        <span class="price-tag">${product.price}</span>
                    </div>
                    <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                    <button class="dashboard-primary-button" type="button" data-reserve-product="${product.id}">Reserver</button>
                </div>
            </article>
        `;
    }

    function renderProducts(productsToRender) {
        if (productsToRender.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucun produit ne correspond aux filtres actuels.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="dashboard-product-grid">${productsToRender.map(productCardMarkup).join('')}</div>`;
    }

    function renderProfile() {
        contentRoot.innerHTML = `
            <div class="dashboard-summary-grid">
                <article class="dashboard-info-card">
                    <h3>Profil acheteur</h3>
                    <p><strong>${buyer.name}</strong></p>
                    <p>Zone principale: ${buyer.zone}</p>
                    <p>Preferences: ${buyer.preferredCategories.join(', ')}</p>
                </article>
                <article class="dashboard-info-card">
                    <h3>Activite rapide</h3>
                    <p>Reservations en cours: ${dashboardState.reservations.length}</p>
                    <p>Produits sauvegardes dans le panier: ${dashboardState.cart.length}</p>
                    <p>Notifications non lues: ${dashboardState.notifications.length}</p>
                </article>
            </div>
        `;
    }

    function renderReservations() {
        const reserved = dashboardProducts.filter((product) =>
            dashboardState.reservations.includes(product.id) || dashboardState.cart.some((item) => item.id === product.id)
        );

        if (reserved.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucune reservation active pour le moment.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="reservation-list">${reserved.map((product) => `
            <article class="reservation-card">
                <h3>${product.title}</h3>
                <p>${product.location} · ${product.price}</p>
                <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                <p>Retrait conseille sous ${product.distance} km.</p>
            </article>
        `).join('')}</div>`;
    }

    function renderRecommendations() {
        const recommended = filterProducts(dashboardProducts.filter((product) => buyer.preferredCategories.includes(product.category)));
        renderProducts(recommended);
    }

    function renderNearby() {
        const nearby = filterProducts(dashboardProducts.filter((product) => product.distance <= 5));
        renderProducts(nearby);
    }

    function renderHistory() {
        const historyItems = dashboardProducts.filter((product) => dashboardState.history.includes(product.id));
        if (historyItems.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Votre historique est encore vide.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="history-list">${historyItems.map((product) => `
            <article class="history-card">
                <h3>${product.title}</h3>
                <p>${product.description}</p>
                <p>${product.location} · ${product.price}</p>
                <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
            </article>
        `).join('')}</div>`;
    }

    function updateHeaderForView() {
        const copy = {
            profile: {
                kicker: 'Vue profil',
                title: 'Bienvenue dans votre espace acheteur',
                description: 'Consultez votre profil, vos preferences et votre activite recente.'
            },
            reservations: {
                kicker: 'Reservations',
                title: 'Vos reservations en cours',
                description: 'Suivez vos paniers confirmes et les produits ajoutes recemment.'
            },
            recommendations: {
                kicker: 'Recommandations',
                title: 'Suggestions pensees pour votre zone',
                description: 'Une selection proche de vos preferences et de votre localisation.'
            },
            nearby: {
                kicker: 'Produits proches',
                title: 'Les produits les plus proches',
                description: 'Visualisez les annonces les plus accessibles autour de vous.'
            },
            history: {
                kicker: 'Historique',
                title: 'Votre historique de reservations',
                description: 'Retrouvez les produits deja recuperes via la plateforme.'
            }
        };

        viewKicker.textContent = copy[dashboardState.currentView].kicker;
        viewTitle.textContent = copy[dashboardState.currentView].title;
        viewDescription.textContent = copy[dashboardState.currentView].description;
    }

    function updateBadges() {
        cartCount.textContent = String(dashboardState.cart.length);
        notificationCount.textContent = String(dashboardState.notifications.length);
    }

    function updateActiveSidebar() {
        sidebarLinks.forEach((link) => {
            link.classList.toggle('is-active', link.getAttribute('data-view') === dashboardState.currentView);
        });
    }

    function toggleFeedControls() {
        const feedControls = document.querySelector('#feedControls');
        const show = ['recommendations', 'nearby'].includes(dashboardState.currentView);
        feedControls.style.display = show ? 'grid' : 'none';
    }

    function renderCurrentView() {
        updateHeaderForView();
        updateActiveSidebar();
        toggleFeedControls();

        switch (dashboardState.currentView) {
            case 'profile':
                renderProfile();
                break;
            case 'reservations':
                renderReservations();
                break;
            case 'recommendations':
                renderRecommendations();
                break;
            case 'nearby':
                renderNearby();
                break;
            case 'history':
                renderHistory();
                break;
            default:
                renderProfile();
        }
    }

    function addToCart(productId) {
        if (!dashboardState.cart.some((item) => item.id === productId)) {
            const product = dashboardProducts.find((item) => item.id === productId);
            if (!product) {
                return;
            }
            dashboardState.cart.push(product);
            persistCart();
            updateBadges();
        }
    }

    function openPanel(title, items, itemRenderer) {
        panelTitle.textContent = title;
        if (items.length === 0) {
            panelContent.innerHTML = '<div class="dashboard-empty">Aucun element pour le moment.</div>';
        } else {
            panelContent.innerHTML = items.map(itemRenderer).join('');
        }
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
    }

    function closePanel() {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    dashboardSearch.addEventListener('input', () => {
        if (['recommendations', 'nearby'].includes(dashboardState.currentView)) {
            renderCurrentView();
        }
    });

    feedSearch.addEventListener('input', renderCurrentView);
    zoneSelect.addEventListener('change', renderCurrentView);
    categorySelect.addEventListener('change', renderCurrentView);
    distanceSelect.addEventListener('change', renderCurrentView);

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
            dashboardState.currentView = link.getAttribute('data-view') || 'profile';
            renderCurrentView();
        });
    });

    contentRoot.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('[data-reserve-product]')) {
            const productId = Number(target.getAttribute('data-reserve-product'));
            addToCart(productId);
        }
    });

    cartButton.addEventListener('click', () => {
        const items = dashboardState.cart;
        openPanel('Panier', items, (product) => `
            <article class="panel-item">
                <strong>${product.title}</strong>
                <p>${product.location} · ${product.price}</p>
                <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
            </article>
        `);

        if (items.length > 0) {
            panelContent.insertAdjacentHTML('beforeend', `
                <div class="panel-item-actions">
                    <button id="confirmReservationButton" class="panel-confirm-button" type="button">Confirmer réservation</button>
                </div>
            `);
            document.querySelector('#confirmReservationButton')?.addEventListener('click', () => {
                setCheckoutState(dashboardState.cart, 'buyer_dashboard.html');
                window.location.href = 'payment.html';
            });
        }
    });

    notificationsButton.addEventListener('click', () => {
        openPanel('Notifications', dashboardState.notifications, (notification) => `
            <article class="panel-item">
                <strong>${notification.title}</strong>
                <p>${notification.message}</p>
            </article>
        `);
    });

    logoutButton.addEventListener('click', () => {
        window.localStorage.clear();
        window.location.href = 'homepage.html';
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-panel') === 'true') {
            closePanel();
        }
    });

    populateDashboardFilters();
    updateBadges();
    renderCurrentView();
}

if (adminAssociationDashboard && requireRole('admin_association')) {
    const userRole = 'admin_association';
    let cart = loadStoredArray(adminCartStorageKey);
    const products = [
        {
            id: 101,
            title: 'Lots fruits solidaires',
            description: 'Caisses de fruits de saison prevues pour une distribution associative rapide.',
            location: 'Tunis Centre',
            zone: 'Tunis',
            category: 'Epicerie',
            distance: 2,
            price: '2.90 DT',
            pickupTime: '17:00 - 19:30',
            stock: 12,
            image: 'https://images.unsplash.com/photo-1765480953875-a7338f896e91?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 102,
            title: 'Plateaux repas families',
            description: 'Portions cuisinees pretes a etre reparties en plusieurs kits de distribution.',
            location: 'Ariana',
            zone: 'Ariana',
            category: 'Restauration',
            distance: 4,
            price: '4.60 DT',
            pickupTime: '12:30 - 14:30',
            stock: 18,
            image: 'https://images.unsplash.com/photo-1568897798550-91c8caffe391?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 103,
            title: 'Pains artisanaux invendus',
            description: 'Baguettes, pains complets et fougasses proposes avec remise association.',
            location: 'La Marsa',
            zone: 'La Marsa',
            category: 'Boulangerie',
            distance: 5,
            price: '1.90 DT',
            pickupTime: '18:00 - 20:00',
            stock: 20,
            image: 'https://images.unsplash.com/photo-1774043132154-8934327dee3a?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 104,
            title: 'Desserts frais associations',
            description: 'Lots de yaourts et desserts a retirer pour vos points de collecte.',
            location: 'Sousse Ville',
            zone: 'Sousse',
            category: 'Frais',
            distance: 3,
            price: '2.30 DT',
            pickupTime: '19:00 - 20:30',
            stock: 10,
            image: 'https://images.unsplash.com/photo-1612383277710-67896ecf4c69?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 105,
            title: 'Epicerie longue conservation',
            description: 'Pates, conserves et biscuits en lots dedies aux reseaux associatifs.',
            location: 'Monastir',
            zone: 'Monastir',
            category: 'Epicerie',
            distance: 6,
            price: '3.40 DT',
            pickupTime: '15:30 - 18:00',
            stock: 16,
            image: 'https://images.unsplash.com/photo-1584093092919-3d551a9c5055?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        },
        {
            id: 106,
            title: 'Offre partenaire grand volume',
            description: 'Lots premium avec reduction reservee aux associations partenaires.',
            location: 'Sfax Medina',
            zone: 'Sfax',
            category: 'Restauration',
            distance: 8,
            price: '3.80 DT',
            pickupTime: '11:30 - 13:30',
            stock: 24,
            image: 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&fm=jpg&q=80&w=1200'
        }
    ];

    const adminState = {
        currentView: 'products',
        notifications: [
            { id: 1, title: 'Offre reservee', message: 'Une remise de 15% est active sur les lots grand volume aujourd hui.' },
            { id: 2, title: 'Collecte prioritaire', message: 'Votre association dispose d un retrait prioritaire a Tunis Centre de 17h a 19h30.' }
        ],
        reservations: [
            { id: 102, quantity: 4 },
            { id: 104, quantity: 2 }
        ],
        history: [
            { id: 103, quantity: 6 },
            { id: 105, quantity: 3 }
        ]
    };

    const dashboardSearch = document.querySelector('#adminDashboardSearch');
    const feedSearch = document.querySelector('#adminFeedSearch');
    const zoneSelect = document.querySelector('#adminZone');
    const categorySelect = document.querySelector('#adminCategory');
    const distanceSelect = document.querySelector('#adminDistance');
    const contentRoot = document.querySelector('#adminDashboardContent');
    const viewTitle = document.querySelector('#adminViewTitle');
    const viewDescription = document.querySelector('#adminViewDescription');
    const viewKicker = document.querySelector('#adminViewKicker');
    const cartCount = document.querySelector('#adminCartCount');
    const notificationCount = document.querySelector('#adminNotificationCount');
    const cartButton = document.querySelector('#adminCartButton');
    const notificationsButton = document.querySelector('#adminNotificationsButton');
    const logoutButton = document.querySelector('#adminLogoutButton');
    const panel = document.querySelector('#adminDashboardPanel');
    const panelTitle = document.querySelector('#adminPanelTitle');
    const panelContent = document.querySelector('#adminPanelContent');
    const sidebarLinks = document.querySelectorAll('[data-admin-view]');
    const feedControls = document.querySelector('#adminFeedControls');

    function persistCart() {
        persistStoredArray(adminCartStorageKey, cart);
    }

    function populateDashboardFilters() {
        const zones = [...new Set(products.map((product) => product.zone))];
        const categories = [...new Set(products.map((product) => product.category))];

        zones.forEach((zone) => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            zoneSelect.appendChild(option);
        });

        categories.forEach((category) => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });
    }

    function parsePrice(value) {
        return Number.parseFloat(String(value).replace(' DT', '').replace(',', '.')) || 0;
    }

    function validateQuantity(productId, quantity) {
        const product = products.find((item) => item.id === productId);
        if (!product) {
            return { valid: false, message: 'Produit introuvable.' };
        }

        const normalizedQuantity = Number(quantity);
        if (!Number.isInteger(normalizedQuantity) || normalizedQuantity < 1) {
            return { valid: false, message: 'Choisissez une quantite valide.' };
        }

        if (normalizedQuantity > product.stock) {
            return { valid: false, message: `Quantite max: ${product.stock}.` };
        }

        return { valid: true, message: '' };
    }

    function filterProducts(sourceProducts = products) {
        const term = `${dashboardSearch.value} ${feedSearch.value}`.trim().toLowerCase();
        const zone = zoneSelect.value;
        const category = categorySelect.value;
        const distance = distanceSelect.value === '' ? null : Number(distanceSelect.value);

        return sourceProducts.filter((product) => {
            const matchesTerm =
                term === '' ||
                product.title.toLowerCase().includes(term) ||
                product.description.toLowerCase().includes(term) ||
                product.location.toLowerCase().includes(term) ||
                product.category.toLowerCase().includes(term);

            const matchesZone = zone === '' || product.zone === zone;
            const matchesCategory = category === '' || product.category === category;
            const matchesDistance = distance === null || product.distance <= distance;

            return matchesTerm && matchesZone && matchesCategory && matchesDistance;
        });
    }

    function productCardMarkup(product) {
        return `
            <article class="dashboard-product-card">
                <img class="dashboard-product-image" src="${product.image}" alt="${product.title}">
                <div class="dashboard-product-body">
                    <div class="dashboard-meta-row">
                        <span>${product.category}</span>
                        <span class="stock-pill">Stock: ${product.stock}</span>
                    </div>
                    <h3>${product.title}</h3>
                    <p>${product.description}</p>
                    <div class="dashboard-meta-row">
                        <span>Lieu: ${product.location}</span>
                        <span class="price-tag">${product.price}</span>
                    </div>
                    <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                    <div class="quantity-row">
                        <label class="quantity-field" for="quantity-${product.id}">
                            <span>Quantite</span>
                            <input id="quantity-${product.id}" type="number" min="1" max="${product.stock}" value="1" data-quantity-input="${product.id}">
                        </label>
                        <button class="dashboard-primary-button" type="button" data-reserve-product="${product.id}">Reserver</button>
                    </div>
                    <p class="inline-note">Distance: ${product.distance} km</p>
                    <p class="inline-error" data-quantity-error="${product.id}" hidden></p>
                </div>
            </article>
        `;
    }

    function renderProducts(productsToRender = filterProducts()) {
        if (productsToRender.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucun produit ne correspond aux filtres actuels.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="dashboard-product-grid">${productsToRender.map(productCardMarkup).join('')}</div>`;
    }

    function renderReservations() {
        if (adminState.reservations.length === 0 && cart.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucune reservation active pour le moment.</div>';
            return;
        }

        const reservationCards = adminState.reservations.map((entry) => {
            const product = products.find((item) => item.id === entry.id);
            if (!product) {
                return '';
            }

            return `
                <article class="reservation-card">
                    <h3>${product.title}</h3>
                    <p>${product.location} · ${product.price}</p>
                    <p class="reservation-meta">Quantite reservee: ${entry.quantity}</p>
                    <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                </article>
            `;
        }).join('');

        const cartCards = cart.map((item) => `
            <article class="reservation-card">
                <h3>${item.title}</h3>
                <p>${item.location} · ${item.price}</p>
                <p class="reservation-meta">Dans le panier: ${item.quantity}</p>
                <p class="pickup-time">Pickup time: ${item.pickupTime}</p>
            </article>
        `).join('');

        contentRoot.innerHTML = `<div class="reservation-list">${reservationCards}${cartCards}</div>`;
    }

    function renderRecommendations() {
        renderProducts(filterProducts(products.filter((product) => product.category !== 'Frais' || product.stock >= 10)));
    }

    function renderNearby() {
        renderProducts(filterProducts(products.filter((product) => product.distance <= 5)));
    }

    function renderHistory() {
        if (adminState.history.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Votre historique est encore vide.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="history-list">${adminState.history.map((entry) => {
            const product = products.find((item) => item.id === entry.id);
            if (!product) {
                return '';
            }

            return `
                <article class="history-card">
                    <h3>${product.title}</h3>
                    <p>${product.description}</p>
                    <p>${product.location} · ${product.price}</p>
                    <p class="reservation-meta">Quantite retiree: ${entry.quantity}</p>
                    <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                </article>
            `;
        }).join('')}</div>`;
    }

    function renderOffers() {
        contentRoot.innerHTML = `
            <div class="dashboard-summary-grid">
                <article class="dashboard-info-card">
                    <h3>Reduction partenaire</h3>
                    <p>Jusqu a 15% sur les lots a grand volume pour les associations verifiees.</p>
                </article>
                <article class="dashboard-info-card">
                    <h3>Collecte prioritaire</h3>
                    <p>Acces a des creneaux etendus sur Tunis Centre et Ariana pour les distributions du soir.</p>
                </article>
                <article class="dashboard-info-card">
                    <h3>Lots reserves</h3>
                    <p>Des paniers a stock renforce sont visibles en priorite pour faciliter les operations solidaires.</p>
                </article>
                <article class="dashboard-info-card">
                    <h3>Role actif</h3>
                    <p>Compte courant: ${userRole}</p>
                    <p>Produits disponibles aujourd hui: ${products.length}</p>
                </article>
            </div>
        `;
    }

    function updateHeaderForView() {
        const copy = {
            products: {
                kicker: 'Feed association',
                title: 'Produits disponibles pour votre association',
                description: 'Recherchez des lots, choisissez une quantite et preparez votre reservation.'
            },
            reservations: {
                kicker: 'Reservations',
                title: 'Vos reservations et paniers en attente',
                description: 'Suivez les lots deja reserves et ceux prets a etre confirmes.'
            },
            recommendations: {
                kicker: 'Recommandations',
                title: 'Suggestions solidaires pour votre structure',
                description: 'Une selection pertinente selon votre zone et vos besoins de redistribution.'
            },
            nearby: {
                kicker: 'Produits proches',
                title: 'Les offres les plus proches',
                description: 'Des produits accessibles rapidement pour vos collectes locales.'
            },
            history: {
                kicker: 'Historique',
                title: 'Historique des reservations',
                description: 'Retrouvez vos anciens retraits et volumes collectes.'
            },
            offers: {
                kicker: 'Offres exclusives',
                title: 'Avantages reserves aux associations',
                description: 'Remises, priorites de retrait et lots negocies pour les partenaires associatifs.'
            }
        };

        viewKicker.textContent = copy[adminState.currentView].kicker;
        viewTitle.textContent = copy[adminState.currentView].title;
        viewDescription.textContent = copy[adminState.currentView].description;
    }

    function updateActiveSidebar() {
        sidebarLinks.forEach((link) => {
            link.classList.toggle('is-active', link.getAttribute('data-admin-view') === adminState.currentView);
        });
    }

    function updateCart() {
        cartCount.textContent = String(cart.reduce((sum, item) => sum + item.quantity, 0));
        notificationCount.textContent = String(adminState.notifications.length);
        persistCart();
    }

    function showQuantityError(productId, message) {
        const errorNode = document.querySelector(`[data-quantity-error="${productId}"]`);
        if (!errorNode) {
            return;
        }

        errorNode.textContent = message;
        errorNode.hidden = message === '';
    }

    function addToCart(productId) {
        const input = document.querySelector(`[data-quantity-input="${productId}"]`);
        const quantity = input instanceof HTMLInputElement ? Number(input.value) : 1;
        const validation = validateQuantity(productId, quantity);

        showQuantityError(productId, validation.message);
        if (!validation.valid) {
            return;
        }

        const product = products.find((item) => item.id === productId);
        if (!product) {
            return;
        }

        const existingItem = cart.find((item) => item.id === productId);
        if (existingItem) {
            existingItem.quantity = quantity;
        } else {
            cart.push({
                id: product.id,
                title: product.title,
                quantity,
                price: product.price,
                pickupTime: product.pickupTime,
                location: product.location
            });
        }

        updateCart();
        showQuantityError(productId, '');
    }

    function openPanel(title, markup) {
        panelTitle.textContent = title;
        panelContent.innerHTML = markup;
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
    }

    function closePanel() {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    function renderCurrentView() {
        updateHeaderForView();
        updateActiveSidebar();
        feedControls.style.display = adminState.currentView === 'offers' ? 'none' : 'grid';

        switch (adminState.currentView) {
            case 'products':
                renderProducts();
                break;
            case 'reservations':
                renderReservations();
                break;
            case 'recommendations':
                renderRecommendations();
                break;
            case 'nearby':
                renderNearby();
                break;
            case 'history':
                renderHistory();
                break;
            case 'offers':
                renderOffers();
                break;
            default:
                renderProducts();
        }
    }

    dashboardSearch.addEventListener('input', renderCurrentView);
    feedSearch.addEventListener('input', renderCurrentView);
    zoneSelect.addEventListener('change', renderCurrentView);
    categorySelect.addEventListener('change', renderCurrentView);
    distanceSelect.addEventListener('change', renderCurrentView);

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
            adminState.currentView = link.getAttribute('data-admin-view') || 'products';
            renderCurrentView();
        });
    });

    contentRoot.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('[data-reserve-product]')) {
            addToCart(Number(target.getAttribute('data-reserve-product')));
        }
    });

    cartButton.addEventListener('click', () => {
        if (cart.length === 0) {
            openPanel('Panier association', '<div class="dashboard-empty">Votre panier est vide pour le moment.</div>');
            return;
        }

        const itemsMarkup = cart.map((item) => `
            <article class="panel-item">
                <strong>${item.title}</strong>
                <p class="cart-line">${item.location} · ${item.price}</p>
                <p class="cart-line">Quantite: ${item.quantity}</p>
                <p class="pickup-time">Pickup time: ${item.pickupTime}</p>
            </article>
        `).join('');

        openPanel('Panier association', `
            ${itemsMarkup}
            <div class="panel-item-actions">
                <button id="confirmAdminReservationButton" class="panel-confirm-button" type="button">Confirmer reservation</button>
            </div>
        `);

        document.querySelector('#confirmAdminReservationButton')?.addEventListener('click', () => {
            setCheckoutState(cart, 'admin_association_dashboard.html');
            window.location.href = 'payment.html';
        });
    });

    notificationsButton.addEventListener('click', () => {
        openPanel(
            'Notifications',
            adminState.notifications.map((notification) => `
                <article class="panel-item">
                    <strong>${notification.title}</strong>
                    <p>${notification.message}</p>
                </article>
            `).join('')
        );
    });

    logoutButton.addEventListener('click', () => {
        window.localStorage.clear();
        window.location.href = 'homepage.html';
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-admin-panel') === 'true') {
            closePanel();
        }
    });

    populateDashboardFilters();
    updateCart();
    renderCurrentView();
}

if (merchantDashboard && requireRole('commerce')) {
    const products = [
        {
            id: 201,
            title: 'Panier boulangerie du soir',
            description: 'Assortiment de pains et viennoiseries a recuperer avant fermeture.',
            foodType: 'Boulangerie',
            quantity: 10,
            unit: 'pcs',
            location: 'Tunis Centre',
            status: 'Reserved',
            price: '3.20 DT',
            pickupTime: '18:30',
            paymentMethod: 'Card'
        },
        {
            id: 202,
            title: 'Box legumes prets a cuisiner',
            description: 'Selection de legumes frais en lots rapides a reserver.',
            foodType: 'Fruits & legumes',
            quantity: 6,
            unit: 'box',
            location: 'La Marsa',
            status: 'Reserved',
            price: '4.10 DT',
            pickupTime: '19:10',
            paymentMethod: 'On-site'
        }
    ];

    const history = [
        {
            id: 190,
            title: 'Repas du midi invendus',
            description: 'Portions pretes a retirer en fin de service.',
            foodType: 'Restauration',
            quantity: 8,
            unit: 'pcs',
            location: 'Ariana',
            status: 'Picked-up',
            price: '5.50 DT',
            pickupTime: '18:30',
            paymentMethod: 'Card'
        }
    ];

    const reservations = [
        { id: 201, customer: 'Association El Amal', quantity: 10, status: 'Reserved' },
        { id: 202, customer: 'Collectif Nourrir', quantity: 5, status: 'Reserved' }
    ];

    const merchantState = {
        currentView: 'publish',
        notifications: [
            { id: 1, title: 'Nouvelle reservation', message: 'Association El Amal a reserve 10 pcs pour Panier boulangerie du soir.' },
            { id: 2, title: 'Rappel pickup', message: 'Le retrait de Box legumes prets a cuisiner est prevu a 19:10.' }
        ],
        lastPublishMessage: ''
    };

    const contentRoot = document.querySelector('#merchantDashboardContent');
    const viewTitle = document.querySelector('#merchantViewTitle');
    const viewDescription = document.querySelector('#merchantViewDescription');
    const viewKicker = document.querySelector('#merchantViewKicker');
    const notificationCount = document.querySelector('#merchantNotificationCount');
    const notificationsButton = document.querySelector('#merchantNotificationsButton');
    const logoutButton = document.querySelector('#merchantLogoutButton');
    const panel = document.querySelector('#merchantDashboardPanel');
    const panelTitle = document.querySelector('#merchantPanelTitle');
    const panelContent = document.querySelector('#merchantPanelContent');
    const sidebarLinks = document.querySelectorAll('[data-merchant-view]');

    function updateHeaderForView() {
        const copy = {
            publish: {
                kicker: 'Publication',
                title: "Publication d'une annonce",
                description: 'Ajoutez rapidement un nouveau lot avec les informations utiles pour les reservations.'
            },
            products: {
                kicker: 'Annonces actives',
                title: 'Tes annonces',
                description: 'Suivez les produits reserves, leur statut et confirmez les retraits.'
            },
            history: {
                kicker: 'Historique',
                title: 'Historique des annonces',
                description: 'Consultez les lots deja retires ainsi que le mode de paiement utilise.'
            },
            reports: {
                kicker: 'Reports',
                title: 'Reports & analytics',
                description: 'Visualisez vos ventes, tendances de reservation et repartition des paiements.'
            }
        };

        viewKicker.textContent = copy[merchantState.currentView].kicker;
        viewTitle.textContent = copy[merchantState.currentView].title;
        viewDescription.textContent = copy[merchantState.currentView].description;
    }

    function updateActiveSidebar() {
        sidebarLinks.forEach((link) => {
            link.classList.toggle('is-active', link.getAttribute('data-merchant-view') === merchantState.currentView);
        });
    }

    function openPanel(title, markup) {
        panelTitle.textContent = title;
        panelContent.innerHTML = markup;
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
    }

    function closePanel() {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    function updateNotifications() {
        notificationCount.textContent = String(merchantState.notifications.length);
    }

    function productCardMarkup(product) {
        const reservation = reservations.find((item) => item.id === product.id);
        const reservedQuantity = reservation ? reservation.quantity : product.quantity;

        return `
            <article class="reservation-card">
                <div class="merchant-status-row">
                    <h3>${product.title}</h3>
                    <span class="status-pill">${product.status}</span>
                </div>
                <div class="merchant-meta-grid">
                    <p>${product.description}</p>
                    <p>Type: ${product.foodType}</p>
                    <p>Quantite: ${reservedQuantity} ${product.unit}</p>
                    <p>Localisation: ${product.location}</p>
                    <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                </div>
                <button class="dashboard-primary-button" type="button" data-picked-up="${product.id}">Picked-up</button>
            </article>
        `;
    }

    function historyCardMarkup(product) {
        return `
            <article class="history-card">
                <div class="merchant-status-row">
                    <h3>${product.title}</h3>
                    <span class="status-pill is-picked">${product.status}</span>
                </div>
                <div class="merchant-meta-grid">
                    <p>Quantite: ${product.quantity} ${product.unit}</p>
                    <p>Price: ${product.price}</p>
                    <p>Picked-up at: ${product.pickupTime}</p>
                    <p>Payment: ${product.paymentMethod}</p>
                </div>
            </article>
        `;
    }

    function renderPublishView() {
        contentRoot.innerHTML = `
            <article class="merchant-form-card">
                <form id="merchantPublishForm" class="merchant-form">
                    <div class="merchant-form-grid">
                        <label class="merchant-field merchant-field-full">
                            <span>Titre</span>
                            <input type="text" name="title" placeholder="Ex: Panier du soir" required>
                        </label>
                        <label class="merchant-field merchant-field-full">
                            <span>Description</span>
                            <textarea name="description" placeholder="Decrivez le lot disponible..." required></textarea>
                        </label>
                        <label class="merchant-field">
                            <span>Type d'aliment</span>
                            <select name="foodType" required>
                                <option value="">Selectionner</option>
                                <option value="Boulangerie">Boulangerie</option>
                                <option value="Restauration">Restauration</option>
                                <option value="Fruits & legumes">Fruits & legumes</option>
                                <option value="Epicerie">Epicerie</option>
                                <option value="Frais">Frais</option>
                            </select>
                        </label>
                        <label class="merchant-field">
                            <span>Quantite</span>
                            <input type="number" name="quantity" min="1" placeholder="10" required>
                        </label>
                        <label class="merchant-field">
                            <span>Unite</span>
                            <select name="unit" required>
                                <option value="">Selectionner</option>
                                <option value="kg">kg</option>
                                <option value="pcs">pcs</option>
                                <option value="box">box</option>
                            </select>
                        </label>
                        <label class="merchant-field">
                            <span>Localisation</span>
                            <input type="text" name="location" placeholder="Tunis Centre" required>
                        </label>
                    </div>
                    <div class="merchant-submit-row">
                        <button class="dashboard-primary-button" type="submit">Publier</button>
                    </div>
                    ${merchantState.lastPublishMessage ? `<p class="inline-success">${merchantState.lastPublishMessage}</p>` : ''}
                </form>
            </article>
        `;
    }

    function renderProductsView() {
        if (products.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucune annonce active pour le moment.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="reservation-list">${products.map(productCardMarkup).join('')}</div>`;
    }

    function renderHistoryView() {
        if (history.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucun element dans l historique pour le moment.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="history-list">${history.map(historyCardMarkup).join('')}</div>`;
    }

    function renderReportsView() {
        contentRoot.innerHTML = `
            <div class="merchant-chart-grid">
                <article class="merchant-chart-card">
                    <h3>Sales per day</h3>
                    <div id="salesPerDayChart" class="chart-surface"></div>
                </article>
                <article class="merchant-chart-card">
                    <h3>Reservation trends</h3>
                    <div id="reservationTrendsChart" class="chart-surface"></div>
                </article>
                <article class="merchant-chart-card">
                    <h3>Payment methods</h3>
                    <div id="paymentMethodsChart" class="chart-surface"></div>
                </article>
                <article class="merchant-chart-card">
                    <h3>Stock vs sold analysis</h3>
                    <div id="stockVsSoldChart" class="chart-surface"></div>
                </article>
            </div>
        `;

        renderCharts();
    }

    function publishProduct(formData) {
        const quantity = Number(formData.get('quantity'));
        const id = Date.now();
        const product = {
            id,
            title: String(formData.get('title')).trim(),
            description: String(formData.get('description')).trim(),
            foodType: String(formData.get('foodType')).trim(),
            quantity,
            unit: String(formData.get('unit')).trim(),
            location: String(formData.get('location')).trim(),
            status: 'Reserved',
            price: `${(2 + quantity * 0.35).toFixed(2)} DT`,
            pickupTime: `${String(17 + Math.min(quantity, 3)).padStart(2, '0')}:30`,
            paymentMethod: Math.random() > 0.5 ? 'Card' : 'On-site'
        };

        const reservation = {
            id,
            customer: 'Reservation en attente',
            quantity,
            status: 'Reserved'
        };

        products.unshift(product);
        reservations.unshift(reservation);
        merchantState.lastPublishMessage = `Annonce publiee: ${product.title}`;
        merchantState.notifications.unshift({
            id: Date.now() + 1,
            title: 'Annonce publiee',
            message: `${product.title} a ete ajoutee a vos annonces actives.`
        });
        updateNotifications();
    }

    function moveToHistory(productId) {
        const productIndex = products.findIndex((item) => item.id === productId);
        if (productIndex === -1) {
            return;
        }

        const [product] = products.splice(productIndex, 1);
        product.status = 'Picked-up';
        history.unshift(product);
    }

    function markPickedUp(productId) {
        const reservation = reservations.find((item) => item.id === productId);
        if (reservation) {
            reservation.status = 'Picked-up';
        }

        moveToHistory(productId);

        const reservationIndex = reservations.findIndex((item) => item.id === productId);
        if (reservationIndex !== -1) {
            reservations.splice(reservationIndex, 1);
        }

        const movedProduct = history[0];
        if (movedProduct) {
            merchantState.notifications.unshift({
                id: Date.now() + 2,
                title: 'Pickup confirme',
                message: `${movedProduct.title} a ete marquee comme retiree.`
            });
            updateNotifications();
        }
    }

    function renderCharts() {
        if (typeof window.CanvasJS === 'undefined') {
            return;
        }

        const soldHistory = history.slice(0, 5);
        const paymentCardCount = history.filter((item) => item.paymentMethod === 'Card').length;
        const paymentOnSiteCount = history.filter((item) => item.paymentMethod === 'On-site').length;

        const salesPerDayChart = new window.CanvasJS.Chart('salesPerDayChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            axisY: { gridColor: 'rgba(154, 177, 122, 0.18)' },
            data: [{
                type: 'column',
                color: '#9AB17A',
                dataPoints: soldHistory.map((item, index) => ({
                    label: `Jour ${index + 1}`,
                    y: Number.parseFloat(String(item.price).replace(' DT', '')) || 0
                }))
            }]
        });

        const reservationTrendsChart = new window.CanvasJS.Chart('reservationTrendsChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            axisY: { gridColor: 'rgba(154, 177, 122, 0.18)' },
            data: [{
                type: 'line',
                color: '#C3CC9B',
                dataPoints: [7, 9, 8, 11, reservations.length + 6].map((value, index) => ({
                    label: `S${index + 1}`,
                    y: value
                }))
            }]
        });

        const paymentMethodsChart = new window.CanvasJS.Chart('paymentMethodsChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            data: [{
                type: 'pie',
                startAngle: 240,
                indexLabel: '{label}: {y}',
                dataPoints: [
                    { label: 'Card', y: paymentCardCount || 1, color: '#9AB17A' },
                    { label: 'On-site', y: paymentOnSiteCount || 1, color: '#E4DFB5' }
                ]
            }]
        });

        const stockVsSoldChart = new window.CanvasJS.Chart('stockVsSoldChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            axisY: { gridColor: 'rgba(154, 177, 122, 0.18)' },
            data: [
                {
                    type: 'column',
                    name: 'Stock',
                    showInLegend: true,
                    color: '#E4DFB5',
                    dataPoints: [
                        { label: 'Actif', y: products.reduce((sum, item) => sum + item.quantity, 0) },
                        { label: 'Vendu', y: history.reduce((sum, item) => sum + item.quantity, 0) }
                    ]
                },
                {
                    type: 'column',
                    name: 'Sold',
                    showInLegend: true,
                    color: '#9AB17A',
                    dataPoints: [
                        { label: 'Actif', y: Math.max(products.length - 1, 0) },
                        { label: 'Vendu', y: history.length }
                    ]
                }
            ]
        });

        salesPerDayChart.render();
        reservationTrendsChart.render();
        paymentMethodsChart.render();
        stockVsSoldChart.render();
    }

    function renderCurrentView() {
        updateHeaderForView();
        updateActiveSidebar();

        switch (merchantState.currentView) {
            case 'publish':
                renderPublishView();
                break;
            case 'products':
                renderProductsView();
                break;
            case 'history':
                renderHistoryView();
                break;
            case 'reports':
                renderReportsView();
                break;
            default:
                renderPublishView();
        }
    }

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
            merchantState.currentView = link.getAttribute('data-merchant-view') || 'publish';
            renderCurrentView();
        });
    });

    contentRoot.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.id !== 'merchantPublishForm') {
            return;
        }

        publishProduct(new FormData(form));
        form.reset();
        renderCurrentView();
    });

    contentRoot.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('[data-picked-up]')) {
            markPickedUp(Number(target.getAttribute('data-picked-up')));
            renderCurrentView();
        }
    });

    notificationsButton.addEventListener('click', () => {
        openPanel(
            'Notifications',
            merchantState.notifications.map((notification) => `
                <article class="panel-item">
                    <strong>${notification.title}</strong>
                    <p>${notification.message}</p>
                </article>
            `).join('')
        );
    });

    logoutButton.addEventListener('click', () => {
        window.localStorage.clear();
        window.location.href = 'homepage.html';
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-merchant-panel') === 'true') {
            closePanel();
        }
    });

    updateNotifications();
    renderCurrentView();
}

if (superadminDashboard && requireRole('superadmin')) {
    const buyers = [
        { id: 1, name: 'Ahmed Trabelsi', email: 'ahmed@foodloop.com', status: 'Actif' },
        { id: 2, name: 'Sarah Ben Amor', email: 'sarah@foodloop.com', status: 'Actif' },
        { id: 3, name: 'Youssef Jaziri', email: 'youssef@foodloop.com', status: 'Suspendu' }
    ];

    const commerces = [
        { id: 11, name: 'Bakery X', type: 'Boulangerie', status: 'Actif' },
        { id: 12, name: 'Green Shop', type: 'Epicerie', status: 'Actif' },
        { id: 13, name: 'Resto Medina', type: 'Restauration', status: 'Suspendu' }
    ];

    const associations = [
        { id: 21, name: 'Assoc El Amal', email: 'contact@elamal.org', status: 'Valide' },
        { id: 22, name: 'Solidarite Plus', email: 'hello@solidarite.org', status: 'Pending' },
        { id: 23, name: 'Food Care', email: 'team@foodcare.org', status: 'Suspendu' }
    ];

    const reports = [
        { id: 31, type: 'annonce', subject: 'Food X', reason: 'Spam', status: 'Ouvert' },
        { id: 32, type: 'acheteur', subject: 'Ahmed Trabelsi', reason: 'Abus', status: 'Ouvert' },
        { id: 33, type: 'association', subject: 'Solidarite Plus', reason: 'Documents manquants', status: 'Ouvert' }
    ];

    const adminState = {
        currentView: 'global',
        reservationsTotal: 248,
        notifications: [
            { id: 1, title: 'Signalement critique', message: 'Une annonce a ete signalee pour spam et attend moderation.' },
            { id: 2, title: 'Association en attente', message: 'Solidarite Plus attend une validation de compte.' },
            { id: 3, title: 'Systeme', message: 'Le monitoring indique un pic d activite sur les reservations.' }
        ],
        systemLogs: [
            { id: 1, title: 'Connexion superadmin', message: 'Session ouverte a 08:14 par superadmin principal.' },
            { id: 2, title: 'Suspension commerce', message: 'Resto Medina a ete suspendu suite a plusieurs signalements.' },
            { id: 3, title: 'Validation association', message: 'Assoc El Amal a ete validee apres verification des documents.' }
        ],
        liveMonitoring: [
            { id: 1, label: 'Uptime systeme', value: '99.98%' },
            { id: 2, label: 'Activite temps reel', value: '184 utilisateurs connectes' },
            { id: 3, label: 'Queue moderation', value: '3 signalements ouverts' }
        ]
    };

    const contentRoot = document.querySelector('#superadminDashboardContent');
    const viewTitle = document.querySelector('#superadminViewTitle');
    const viewDescription = document.querySelector('#superadminViewDescription');
    const viewKicker = document.querySelector('#superadminViewKicker');
    const notificationCount = document.querySelector('#superadminNotificationCount');
    const notificationsButton = document.querySelector('#superadminNotificationsButton');
    const profileButton = document.querySelector('#superadminProfileButton');
    const logoutButton = document.querySelector('#superadminLogoutButton');
    const panel = document.querySelector('#superadminDashboardPanel');
    const panelTitle = document.querySelector('#superadminPanelTitle');
    const panelContent = document.querySelector('#superadminPanelContent');
    const sidebarLinks = document.querySelectorAll('[data-superadmin-view]');

    function openPanel(title, markup) {
        panelTitle.textContent = title;
        panelContent.innerHTML = markup;
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
    }

    function closePanel() {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    function updateNotifications() {
        notificationCount.textContent = String(adminState.notifications.length);
    }

    function normalizeStatusClass(status) {
        const value = status.toLowerCase();
        if (value.includes('suspend')) {
            return ' is-suspended';
        }
        if (value.includes('pending')) {
            return ' is-pending';
        }
        if (value.includes('valide') || value.includes('resolu')) {
            return ' is-validated';
        }
        return '';
    }

    function statusPill(status) {
        return `<span class="admin-status-pill${normalizeStatusClass(status)}">${status}</span>`;
    }

    function updateHeaderForView() {
        const copy = {
            global: {
                kicker: 'Pilotage global',
                title: 'Dashboard global',
                description: 'Consultez les statistiques globales, les volumes de reservations et l usage systeme.'
            },
            buyers: {
                kicker: 'Gestion roles',
                title: 'Gestion acheteurs',
                description: 'Supprimez ou suspendez des comptes acheteurs selon les besoins de moderation.'
            },
            commerces: {
                kicker: 'Gestion roles',
                title: 'Gestion commerces',
                description: 'Gardez le controle sur les commerces actifs, suspendus ou a supprimer.'
            },
            associations: {
                kicker: 'Gestion roles',
                title: 'Gestion associations',
                description: 'Validez, suspendez ou supprimez les comptes associatifs selon leur statut.'
            },
            reports: {
                kicker: 'Moderation',
                title: 'Signalements',
                description: 'Traitez les signalements sur les annonces, acheteurs, commerces et associations.'
            },
            system: {
                kicker: 'Infrastructure',
                title: 'Systeme',
                description: 'Surveillez les logs, l activite admin et les indicateurs temps reel.'
            }
        };

        viewKicker.textContent = copy[adminState.currentView].kicker;
        viewTitle.textContent = copy[adminState.currentView].title;
        viewDescription.textContent = copy[adminState.currentView].description;
    }

    function updateActiveSidebar() {
        sidebarLinks.forEach((link) => {
            link.classList.toggle('is-active', link.getAttribute('data-superadmin-view') === adminState.currentView);
        });
    }

    function deleteBuyer(id) {
        const index = buyers.findIndex((item) => item.id === id);
        if (index === -1) {
            return;
        }

        const [buyer] = buyers.splice(index, 1);
        adminState.notifications.unshift({
            id: Date.now(),
            title: 'Acheteur supprime',
            message: `${buyer.name} a ete supprime du systeme.`
        });
        updateNotifications();
    }

    function deleteCommerce(id) {
        const index = commerces.findIndex((item) => item.id === id);
        if (index === -1) {
            return;
        }

        const [commerce] = commerces.splice(index, 1);
        adminState.notifications.unshift({
            id: Date.now() + 1,
            title: 'Commerce supprime',
            message: `${commerce.name} a ete supprime du systeme.`
        });
        updateNotifications();
    }

    function deleteAssociation(id) {
        const index = associations.findIndex((item) => item.id === id);
        if (index === -1) {
            return;
        }

        const [association] = associations.splice(index, 1);
        adminState.notifications.unshift({
            id: Date.now() + 2,
            title: 'Association supprimee',
            message: `${association.name} a ete supprimee du systeme.`
        });
        updateNotifications();
    }

    function deleteAnnouncement(id) {
        const index = reports.findIndex((item) => item.id === id && item.type === 'annonce');
        if (index === -1) {
            return;
        }

        const report = reports[index];
        report.status = 'Resolu';
        adminState.notifications.unshift({
            id: Date.now() + 3,
            title: 'Annonce supprimee',
            message: `L annonce ${report.subject} a ete supprimee apres signalement.`
        });
        updateNotifications();
    }

    function suspendAccount(collection, id) {
        const item = collection.find((entry) => entry.id === id);
        if (!item) {
            return;
        }

        item.status = 'Suspendu';
        adminState.notifications.unshift({
            id: Date.now() + 4,
            title: 'Compte suspendu',
            message: `${item.name || item.subject} a ete suspendu par le superadmin.`
        });
        updateNotifications();
    }

    function validateAssociation(id) {
        const association = associations.find((item) => item.id === id);
        if (!association) {
            return;
        }

        association.status = 'Valide';
        adminState.notifications.unshift({
            id: Date.now() + 5,
            title: 'Association validee',
            message: `${association.name} est maintenant validee sur la plateforme.`
        });
        updateNotifications();
    }

    function resolveReport(id) {
        const report = reports.find((item) => item.id === id);
        if (!report) {
            return;
        }

        report.status = 'Resolu';
        adminState.notifications.unshift({
            id: Date.now() + 6,
            title: 'Signalement resolu',
            message: `Le signalement ${report.subject} a ete marque comme resolu.`
        });
        updateNotifications();
    }

    function renderGlobalView() {
        contentRoot.innerHTML = `
            <div class="superadmin-stats-grid">
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Acheteurs actifs</p>
                    <strong>${buyers.filter((item) => item.status === 'Actif').length}</strong>
                    <p>Comptes clients actuellement operationnels.</p>
                </article>
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Commerces actifs</p>
                    <strong>${commerces.filter((item) => item.status === 'Actif').length}</strong>
                    <p>Commerces publies et visibles sur la plateforme.</p>
                </article>
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Associations actives</p>
                    <strong>${associations.filter((item) => item.status === 'Valide').length}</strong>
                    <p>Associations validees pouvant reserver des lots.</p>
                </article>
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Reservations total</p>
                    <strong>${adminState.reservationsTotal}</strong>
                    <p>Volume total de reservations traitees sur la periode.</p>
                </article>
            </div>
            <div class="admin-chart-grid">
                <article class="merchant-chart-card">
                    <h3>Activity trends</h3>
                    <div id="superadminActivityChart" class="chart-surface"></div>
                </article>
                <article class="merchant-chart-card">
                    <h3>Reservations volume</h3>
                    <div id="superadminReservationsChart" class="chart-surface"></div>
                </article>
                <article class="merchant-chart-card">
                    <h3>System usage</h3>
                    <div id="superadminUsageChart" class="chart-surface"></div>
                </article>
            </div>
        `;

        renderCharts();
    }

    function renderRoleTable(title, rowsMarkup) {
        return `
            <article class="superadmin-table-card">
                <h3>${title}</h3>
                <div class="superadmin-table">
                    ${rowsMarkup}
                </div>
            </article>
        `;
    }

    function renderBuyersView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Nom</span>
                <span>Email</span>
                <span>Actions</span>
            </div>
            ${buyers.map((buyer) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${buyer.name}</strong>
                        ${statusPill(buyer.status)}
                    </div>
                    <span>${buyer.email}</span>
                    <div class="superadmin-actions">
                        <button class="admin-action-button admin-action-secondary" type="button" data-suspend-buyer="${buyer.id}">Suspendre</button>
                        <button class="admin-action-button admin-action-danger" type="button" data-delete-buyer="${buyer.id}">Supprimer</button>
                    </div>
                </div>
            `).join('')}
        `;

        contentRoot.innerHTML = `<div class="superadmin-table-grid">${renderRoleTable('Liste des acheteurs', rows)}</div>`;
    }

    function renderCommercesView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Nom</span>
                <span>Type / Statut</span>
                <span>Actions</span>
            </div>
            ${commerces.map((commerce) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${commerce.name}</strong>
                        <span>${commerce.type}</span>
                    </div>
                    <div class="superadmin-table-meta">
                        ${statusPill(commerce.status)}
                    </div>
                    <div class="superadmin-actions">
                        <button class="admin-action-button admin-action-secondary" type="button" data-suspend-commerce="${commerce.id}">Suspendre</button>
                        <button class="admin-action-button admin-action-danger" type="button" data-delete-commerce="${commerce.id}">Supprimer</button>
                    </div>
                </div>
            `).join('')}
        `;

        contentRoot.innerHTML = `<div class="superadmin-table-grid">${renderRoleTable('Liste des commerces', rows)}</div>`;
    }

    function renderAssociationsView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Nom</span>
                <span>Email / Statut</span>
                <span>Actions</span>
            </div>
            ${associations.map((association) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${association.name}</strong>
                    </div>
                    <div class="superadmin-table-meta">
                        <span>${association.email}</span>
                        ${statusPill(association.status)}
                    </div>
                    <div class="superadmin-actions">
                        <button class="admin-action-button admin-action-secondary" type="button" data-suspend-association="${association.id}">Suspendre</button>
                        <button class="admin-action-button admin-action-danger" type="button" data-delete-association="${association.id}">Supprimer</button>
                    </div>
                </div>
            `).join('')}
        `;

        contentRoot.innerHTML = `<div class="superadmin-table-grid superadmin-table-grid-wide">${renderRoleTable('Liste des associations', rows)}</div>`;
    }

    function renderReportsView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Type / Objet</span>
                <span>Raison / Statut</span>
                <span>Actions</span>
            </div>
            ${reports.map((report) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${report.type}</strong>
                        <span>${report.subject}</span>
                    </div>
                    <div class="superadmin-table-meta">
                        <span>${report.reason}</span>
                        ${statusPill(report.status)}
                    </div>
                    <div class="superadmin-actions">
                        ${report.type === 'annonce'
                            ? `<button class="admin-action-button admin-action-danger" type="button" data-delete-announcement="${report.id}">Supprimer annonce</button>`
                            : report.type === 'acheteur'
                                ? `<button class="admin-action-button admin-action-secondary" type="button" data-ban-buyer="${report.id}">Ban acheteur</button>`
                                : `<button class="admin-action-button admin-action-secondary" type="button" data-resolve-report="${report.id}">Examiner</button>`
                        }
                        <button class="admin-action-button admin-action-primary" type="button" data-resolve-report="${report.id}">Resoudre</button>
                    </div>
                </div>
            `).join('')}
        `;

        contentRoot.innerHTML = `<div class="superadmin-table-grid superadmin-table-grid-wide">${renderRoleTable('Liste des signalements', rows)}</div>`;
    }

    function renderSystemView() {
        contentRoot.innerHTML = `
            <div class="system-grid">
                <article class="system-card">
                    <h3>Controle systeme</h3>
                    <div class="monitoring-list">
                        ${adminState.liveMonitoring.map((entry) => `
                            <div class="monitoring-item">
                                <strong>${entry.label}</strong>
                                <p>${entry.value}</p>
                            </div>
                        `).join('')}
                    </div>
                </article>
                <article class="system-card">
                    <h3>Logs systeme</h3>
                    <div class="system-log-list">
                        ${adminState.systemLogs.map((log) => `
                            <div class="system-log-item">
                                <strong>${log.title}</strong>
                                <p>${log.message}</p>
                            </div>
                        `).join('')}
                    </div>
                </article>
            </div>
        `;
    }

    function renderCharts() {
        if (typeof window.CanvasJS === 'undefined') {
            return;
        }

        const activityChart = new window.CanvasJS.Chart('superadminActivityChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            axisY: { gridColor: 'rgba(154, 177, 122, 0.18)' },
            data: [{
                type: 'line',
                color: '#9AB17A',
                dataPoints: [34, 41, 38, 47, 52, 49, 58].map((value, index) => ({
                    label: `J${index + 1}`,
                    y: value
                }))
            }]
        });

        const reservationsChart = new window.CanvasJS.Chart('superadminReservationsChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            axisY: { gridColor: 'rgba(154, 177, 122, 0.18)' },
            data: [{
                type: 'column',
                color: '#C3CC9B',
                dataPoints: [28, 36, 31, 42, 46].map((value, index) => ({
                    label: `S${index + 1}`,
                    y: value
                }))
            }]
        });

        const usageChart = new window.CanvasJS.Chart('superadminUsageChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            data: [{
                type: 'pie',
                startAngle: 220,
                indexLabel: '{label}: {y}',
                dataPoints: [
                    { label: 'Acheteurs', y: buyers.length, color: '#9AB17A' },
                    { label: 'Commerces', y: commerces.length, color: '#C3CC9B' },
                    { label: 'Associations', y: associations.length, color: '#E4DFB5' }
                ]
            }]
        });

        activityChart.render();
        reservationsChart.render();
        usageChart.render();
    }

    function renderCurrentView() {
        updateHeaderForView();
        updateActiveSidebar();

        switch (adminState.currentView) {
            case 'global':
                renderGlobalView();
                break;
            case 'buyers':
                renderBuyersView();
                break;
            case 'commerces':
                renderCommercesView();
                break;
            case 'associations':
                renderAssociationsView();
                break;
            case 'reports':
                renderReportsView();
                break;
            case 'system':
                renderSystemView();
                break;
            default:
                renderGlobalView();
        }
    }

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
            adminState.currentView = link.getAttribute('data-superadmin-view') || 'global';
            renderCurrentView();
        });
    });

    contentRoot.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('[data-delete-buyer]')) {
            deleteBuyer(Number(target.getAttribute('data-delete-buyer')));
            renderCurrentView();
        }

        if (target.matches('[data-suspend-buyer]')) {
            suspendAccount(buyers, Number(target.getAttribute('data-suspend-buyer')));
            renderCurrentView();
        }

        if (target.matches('[data-delete-commerce]')) {
            deleteCommerce(Number(target.getAttribute('data-delete-commerce')));
            renderCurrentView();
        }

        if (target.matches('[data-suspend-commerce]')) {
            suspendAccount(commerces, Number(target.getAttribute('data-suspend-commerce')));
            renderCurrentView();
        }

        if (target.matches('[data-delete-association]')) {
            deleteAssociation(Number(target.getAttribute('data-delete-association')));
            renderCurrentView();
        }

        if (target.matches('[data-suspend-association]')) {
            suspendAccount(associations, Number(target.getAttribute('data-suspend-association')));
            renderCurrentView();
        }

        if (target.matches('[data-validate-association]')) {
            validateAssociation(Number(target.getAttribute('data-validate-association')));
            renderCurrentView();
        }

        if (target.matches('[data-delete-announcement]')) {
            deleteAnnouncement(Number(target.getAttribute('data-delete-announcement')));
            renderCurrentView();
        }

        if (target.matches('[data-ban-buyer]')) {
            const reportId = Number(target.getAttribute('data-ban-buyer'));
            const report = reports.find((item) => item.id === reportId);
            const buyer = buyers.find((item) => item.name === report?.subject);
            if (buyer) {
                suspendAccount(buyers, buyer.id);
            }
            resolveReport(reportId);
            renderCurrentView();
        }

        if (target.matches('[data-resolve-report]')) {
            resolveReport(Number(target.getAttribute('data-resolve-report')));
            renderCurrentView();
        }
    });

    notificationsButton.addEventListener('click', () => {
        openPanel(
            'Notifications',
            adminState.notifications.map((notification) => `
                <article class="panel-item">
                    <strong>${notification.title}</strong>
                    <p>${notification.message}</p>
                </article>
            `).join('')
        );
    });

    profileButton.addEventListener('click', () => {
        openPanel(
            'Profil superadmin',
            `
                <article class="panel-item">
                    <strong>Superadmin principal</strong>
                    <p>Acces complet a la moderation, au monitoring et a la gestion multi-roles.</p>
                </article>
            `
        );
    });

    logoutButton.addEventListener('click', () => {
        window.localStorage.clear();
        window.location.href = 'homepage.html';
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-superadmin-panel') === 'true') {
            closePanel();
        }
    });

    updateNotifications();
    renderCurrentView();
}

if (paymentApp && requireAuthenticatedUser()) {
    const currentUser = requireAuthenticatedUser();
    const fallbackBuyerDashboard = currentUser ? getRouteForRole(currentUser.role) : 'login_signup.html';
    const paymentTitle = document.querySelector('#paymentTitle');
    const paymentSubtitle = document.querySelector('#paymentSubtitle');
    const paymentContent = document.querySelector('#paymentContent');
    const returnPage = window.localStorage.getItem(checkoutReturnKey) || fallbackBuyerDashboard;

    const paymentState = {
        step: 'choice',
        method: null,
        cart: loadPaymentCart(),
        total: 0,
        transactionId: '',
        pickupCode: ''
    };

    paymentState.total = paymentState.cart.reduce((sum, item) => {
        const quantity = Number(item.quantity) > 0 ? Number(item.quantity) : 1;
        return sum + (parsePaymentPrice(item.price) * quantity);
    }, 0);

    function parsePaymentPrice(value) {
        return Number.parseFloat(String(value).replace(' DT', '').replace(',', '.')) || 0;
    }

    function loadPaymentCart() {
        try {
            const raw = window.localStorage.getItem(checkoutStorageKey);
            if (!raw) {
                return [];
            }
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function formatTotal() {
        return `${paymentState.total.toFixed(2)} DT`;
    }

    function generateTransactionId() {
        return `TX-${Math.random().toString(36).slice(2, 8).toUpperCase()}`;
    }

    function generatePickupCode() {
        return `${Math.random().toString(36).slice(2, 6).toUpperCase()}-${Math.floor(1000 + Math.random() * 9000)}`;
    }

    function formatToday() {
        return new Date().toLocaleDateString('fr-FR');
    }

    function updatePaymentHeader() {
        const copy = {
            choice: {
                title: 'Confirmation de paiement',
                subtitle: 'Choisissez la methode qui vous convient pour finaliser votre reservation.'
            },
            card: {
                title: 'Paiement par carte',
                subtitle: 'Saisissez vos informations pour valider votre commande.'
            },
            receipt: {
                title: 'Paiement confirme',
                subtitle: 'Votre transaction a bien ete enregistree.'
            },
            pickup: {
                title: 'Paiement sur place',
                subtitle: 'Conservez ce code pour le montrer lors du retrait.'
            }
        };

        paymentTitle.textContent = copy[paymentState.step].title;
        paymentSubtitle.textContent = copy[paymentState.step].subtitle;
    }

    function renderChoiceStep() {
        return `
            <div class="payment-panel">
                <h2>Montant total: ${formatTotal()}</h2>
                <p class="payment-total">Choisissez votre methode :</p>
                <div class="payment-methods">
                    <button class="payment-method-button" type="button" data-payment-method="card">Carte bancaire</button>
                    <button class="payment-method-button" type="button" data-payment-method="pickup">Sur place</button>
                </div>
            </div>
        `;
    }

    function renderCardStep() {
        return `
            <div class="payment-panel">
                <h2>Paiement par carte</h2>
                <form class="payment-form" data-payment-action="submit-card">
                    <label class="payment-field">
                        <span>Numero de carte</span>
                        <input type="text" name="cardNumber" placeholder="0000 0000 0000 0000" required>
                    </label>
                    <label class="payment-field">
                        <span>Code CVV</span>
                        <input type="text" name="cvv" placeholder="123" required>
                    </label>
                    <label class="payment-field">
                        <span>Nom du proprietaire</span>
                        <input type="text" name="owner" placeholder="Nom complet" required>
                    </label>
                    <button class="payment-action-button" type="submit">Valider</button>
                </form>
            </div>
        `;
    }

    function renderReceiptStep() {
        return `
            <div class="payment-panel">
                <h2>Paiement confirme</h2>
                <div class="receipt-list">
                    <p><strong>Montant:</strong> ${formatTotal()}</p>
                    <p><strong>Date:</strong> ${formatToday()}</p>
                    <p><strong>ID transaction:</strong> ${paymentState.transactionId}</p>
                </div>
                <div class="receipt-code">${paymentState.transactionId}</div>
                <button class="payment-action-button" type="button" data-download-receipt="true">Telecharger recu</button>
            </div>
        `;
    }

    function renderPickupStep() {
        return `
            <div class="payment-panel">
                <h2>Paiement sur place</h2>
                <p class="payment-info-message">Presentez ce code lors du retrait de votre commande</p>
                <div class="pickup-code">${paymentState.pickupCode}</div>
                <p class="payment-code-note">A montrer au moment du pickup</p>
                <button class="payment-return-button" type="button" data-return-dashboard="true">Retour au dashboard</button>
            </div>
        `;
    }

    function renderPaymentStep() {
        updatePaymentHeader();

        switch (paymentState.step) {
            case 'choice':
                paymentContent.innerHTML = renderChoiceStep();
                break;
            case 'card':
                paymentContent.innerHTML = renderCardStep();
                break;
            case 'receipt':
                paymentContent.innerHTML = renderReceiptStep();
                break;
            case 'pickup':
                paymentContent.innerHTML = renderPickupStep();
                break;
            default:
                paymentContent.innerHTML = renderChoiceStep();
        }
    }

    paymentContent.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const method = target.getAttribute('data-payment-method');
        if (method === 'card') {
            paymentState.step = 'card';
            renderPaymentStep();
            return;
        }

        if (method === 'pickup') {
            paymentState.method = 'pickup';
            paymentState.pickupCode = generatePickupCode();
            paymentState.step = 'pickup';
            window.localStorage.removeItem(checkoutStorageKey);
            window.localStorage.removeItem(checkoutReturnKey);
            if (returnPage === 'admin_association_dashboard.html') {
                window.localStorage.removeItem(adminCartStorageKey);
            } else {
                window.localStorage.removeItem(buyerCartStorageKey);
            }
            renderPaymentStep();
            return;
        }

        if (target.getAttribute('data-return-dashboard') === 'true') {
            window.location.href = returnPage;
            return;
        }

        if (target.getAttribute('data-download-receipt') === 'true') {
            window.print();
        }
    });

    paymentContent.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.getAttribute('data-payment-action') === 'submit-card') {
            paymentState.method = 'card';
            paymentState.transactionId = generateTransactionId();
            paymentState.step = 'receipt';
            window.localStorage.removeItem(checkoutStorageKey);
            window.localStorage.removeItem(checkoutReturnKey);
            if (returnPage === 'admin_association_dashboard.html') {
                window.localStorage.removeItem(adminCartStorageKey);
            } else {
                window.localStorage.removeItem(buyerCartStorageKey);
            }
            renderPaymentStep();
        }
    });

    if (paymentState.cart.length === 0) {
        paymentTitle.textContent = 'Aucun produit a payer';
        paymentSubtitle.textContent = 'Retournez au dashboard pour ajouter une reservation au panier.';
        paymentContent.innerHTML = `
            <div class="payment-panel">
                <p class="payment-info-message">Votre panier est vide pour le moment.</p>
                <button class="payment-return-button" type="button" onclick="window.location.href='${returnPage}'">Retour au dashboard</button>
            </div>
        `;
    } else {
        renderPaymentStep();
    }
}
