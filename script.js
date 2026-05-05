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
const checkoutStorageKey = 'foodloopCheckoutCartV2';
const legacyCheckoutStorageKey = 'foodloopCheckoutCart';
const checkoutReturnKey = 'foodloopCheckoutReturnPage';
const annonceStockOverridesKey = 'foodloopAnnonceStockOverrides';
const reservationEventStorageKey = 'foodloopReservationEvent';
const superadminRoleKey = 'role';
const currentUserStorageKey = 'currentUser';
const apiEndpoints = {
    signup: 'signup.php',
    login: 'login.php',
    authStatus: 'auth_status.php',
    adminAccess: 'admin_access.php',
    superadmin: 'superadmin.php',
    reserve: 'reserve.php',
    payment: 'payment.php',
    createProduct: 'create_product.php',
    annonces: 'annonces.php'
};
const pageRoutes = {
    home: 'index.php?route=home',
    login: 'index.php?route=login',
    buyer: 'index.php?route=dashboard',
    association: 'index.php?route=association',
    business: 'index.php?route=business',
    admin: 'index.php?route=admin',
    adminAccess: 'index.php?route=admin-access',
    payment: 'index.php?route=payment',
    logout: 'index.php?route=logout'
};
const roleRoutes = {
    acheteur: pageRoutes.buyer,
    admin_association: pageRoutes.association,
    commerce: pageRoutes.business,
    superadmin: pageRoutes.admin
};

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

function getUserDisplayName(user, fallback = 'Client FoodLoop') {
    if (user && typeof user.name === 'string' && user.name.trim() !== '') {
        return user.name.trim();
    }

    if (user && typeof user.email === 'string' && user.email.trim() !== '') {
        return user.email.trim().split('@')[0];
    }

    return fallback;
}

function getUserInitials(name, fallback = 'U') {
    const normalizedName = String(name || '').trim();
    if (normalizedName === '') {
        return fallback;
    }

    const parts = normalizedName.split(/\s+/).filter(Boolean);
    if (parts.length === 1) {
        return parts[0].charAt(0).toUpperCase();
    }

    return `${parts[0].charAt(0)}${parts[1].charAt(0)}`.toUpperCase();
}

function getRouteForRole(role) {
    return roleRoutes[role] || pageRoutes.login;
}

function redirectToRoleHome(role) {
    window.location.href = getRouteForRole(role);
}

function requireAuthenticatedUser() {
    const user = getCurrentUser();
    if (!user) {
        window.location.href = pageRoutes.login;
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

function normalizeCheckoutItem(item, user = getCurrentUser()) {
    if (!item || typeof item !== 'object' || Array.isArray(item)) {
        return null;
    }

    const reservationId = Number(item.reservation_id ?? item.reservationId ?? 0);
    const quantity = Number(item.quantity) > 0 ? Number(item.quantity) : 1;
    const priceValue = parsePaymentPrice(item.price);
    const normalized = attachOwnership({
        ...item,
        reservation_id: Number.isFinite(reservationId) ? reservationId : 0,
        quantity,
        price: item.price ?? '',
    }, user);

    if (!Number.isInteger(normalized.reservation_id) || normalized.reservation_id <= 0) {
        return null;
    }

    if (!Number.isFinite(priceValue) || priceValue <= 0) {
        return null;
    }

    return normalized;
}

function getUserStorageSuffix(user = getCurrentUser()) {
    if (!user) {
        return 'guest';
    }

    const role = String(user.role || 'guest').trim().toLowerCase() || 'guest';
    const id = user.id === null || user.id === undefined || user.id === '' ? 'no-id' : String(user.id).trim();
    const email = String(user.email || '').trim().toLowerCase() || 'no-email';
    return `${role}:${id}:${email}`;
}

function getScopedStorageKey(baseKey, user = getCurrentUser()) {
    return `${baseKey}:${getUserStorageSuffix(user)}`;
}

function loadScopedArray(baseKey, fallback = [], user = getCurrentUser()) {
    const value = loadStoredArray(getScopedStorageKey(baseKey, user));
    return value.length === 0 ? [...fallback] : value;
}

function persistScopedArray(baseKey, value, user = getCurrentUser()) {
    persistStoredArray(getScopedStorageKey(baseKey, user), value);
}

function loadScopedObject(baseKey, fallback = {}, user = getCurrentUser()) {
    try {
        const raw = window.localStorage.getItem(getScopedStorageKey(baseKey, user));
        if (!raw) {
            return { ...fallback };
        }

        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? { ...fallback, ...parsed } : { ...fallback };
    } catch (error) {
        return { ...fallback };
    }
}

function persistScopedObject(baseKey, value, user = getCurrentUser()) {
    window.localStorage.setItem(getScopedStorageKey(baseKey, user), JSON.stringify(value));
}

function loadGlobalObject(storageKey, fallback = {}) {
    try {
        const raw = window.localStorage.getItem(storageKey);
        if (!raw) {
            return { ...fallback };
        }

        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? { ...fallback, ...parsed } : { ...fallback };
    } catch (error) {
        return { ...fallback };
    }
}

function persistGlobalObject(storageKey, value) {
    window.localStorage.setItem(storageKey, JSON.stringify(value));
}

function getUserOwnership(user = getCurrentUser()) {
    if (!user) {
        return null;
    }

    return {
        ownerRole: String(user.role || '').trim().toLowerCase(),
        ownerId: user.id === null || user.id === undefined || user.id === '' ? null : String(user.id).trim(),
        ownerEmail: String(user.email || '').trim().toLowerCase() || null
    };
}

function attachOwnership(record, user = getCurrentUser()) {
    if (!record || typeof record !== 'object') {
        return record;
    }

    const ownership = getUserOwnership(user);
    return ownership ? { ...record, ...ownership } : { ...record };
}

function recordBelongsToUser(record, user = getCurrentUser()) {
    if (!record || typeof record !== 'object' || Array.isArray(record) || !user) {
        return false;
    }

    const userRole = String(user.role || '').trim().toLowerCase();
    const userId = user.id === null || user.id === undefined || user.id === '' ? null : String(user.id).trim();
    const userEmail = String(user.email || '').trim().toLowerCase() || null;
    const recordRole = String(record.ownerRole || '').trim().toLowerCase();
    const recordId = record.ownerId === null || record.ownerId === undefined || record.ownerId === '' ? null : String(record.ownerId).trim();
    const recordEmail = String(record.ownerEmail || '').trim().toLowerCase() || null;

    if (recordRole === '' || recordRole !== userRole) {
        return false;
    }

    if (recordEmail && userEmail) {
        return recordEmail === userEmail;
    }

    if (recordId && userId) {
        return recordId === userId;
    }

    return false;
}

function loadOwnedScopedArray(baseKey, fallback = [], user = getCurrentUser()) {
    const scopedItems = loadScopedArray(baseKey, fallback, user);
    return scopedItems.filter((item) => recordBelongsToUser(item, user));
}

function setCheckoutState(cartItems, returnPage) {
    const currentUser = getCurrentUser();
    const normalizedCartItems = Array.isArray(cartItems)
        ? cartItems.map((item) => normalizeCheckoutItem(item, currentUser)).filter(Boolean)
        : [];
    persistStoredArray(checkoutStorageKey, normalizedCartItems);
    window.localStorage.removeItem(legacyCheckoutStorageKey);
    window.localStorage.setItem(checkoutReturnKey, returnPage);
}

async function postToPhpEndpoint(endpoint, formData) {
    const response = await window.fetch(endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    });

    let payload = null;

    try {
        payload = await response.json();
    } catch (error) {
        payload = null;
    }

    if (response.status === 401) {
        clearApplicationState();
    }

    if (!response.ok || !payload || payload.status !== 'success') {
        const message = payload && payload.message ? payload.message : 'Une erreur est survenue.';
        throw new Error(message);
    }

    return payload;
}

async function getFromPhpEndpoint(endpoint) {
    const response = await window.fetch(endpoint, {
        method: 'GET',
        credentials: 'same-origin'
    });

    let payload = null;

    try {
        payload = await response.json();
    } catch (error) {
        payload = null;
    }

    if (response.status === 401) {
        clearApplicationState();
    }

    if (!response.ok || !payload || payload.status !== 'success') {
        const message = payload && payload.message ? payload.message : 'Une erreur est survenue.';
        throw new Error(message);
    }

    return payload;
}

async function fetchAuthenticatedUser() {
    const response = await window.fetch(apiEndpoints.authStatus, {
        method: 'GET',
        credentials: 'same-origin'
    });

    let payload = null;

    try {
        payload = await response.json();
    } catch (error) {
        payload = null;
    }

    if (response.status === 401) {
        clearApplicationState();
        return null;
    }

    if (!response.ok || !payload || payload.status !== 'success' || !payload.user || typeof payload.user !== 'object') {
        return null;
    }

    const normalizedUser = {
        id: payload.user.id,
        role: payload.user.role,
        email: payload.user.email || '',
        name: payload.user.name || ''
    };
    setCurrentUser(normalizedUser);
    return normalizedUser;
}

function clearApplicationState() {
    window.localStorage.removeItem(currentUserStorageKey);
    window.localStorage.removeItem(superadminRoleKey);
    window.localStorage.removeItem(checkoutStorageKey);
    window.localStorage.removeItem(legacyCheckoutStorageKey);
    window.localStorage.removeItem(checkoutReturnKey);
}

function appendItemsToUserHistory(user, baseKey, items, extra = {}) {
    if (!user || !Array.isArray(items) || items.length === 0) {
        return;
    }

    const existingHistory = loadOwnedScopedArray(baseKey, [], user);
    const normalizedItems = items.map((item) => attachOwnership({
        id: item.id,
        title: item.title,
        description: item.description || '',
        location: item.location || '',
        price: item.price || '',
        pickupTime: item.pickupTime || '',
        quantity: Number(item.quantity) > 0 ? Number(item.quantity) : 1,
        addedAt: new Date().toISOString(),
        ...extra
    }, user));

    persistScopedArray(baseKey, [...normalizedItems, ...existingHistory], user);
}

function appendItemsToUserReservations(user, baseKey, items, extra = {}) {
    if (!user || !Array.isArray(items) || items.length === 0) {
        return;
    }

    const existingReservations = loadOwnedScopedArray(baseKey, [], user);
    const normalizedItems = items.map((item) => attachOwnership({
        id: item.id,
        title: item.title,
        location: item.location || '',
        price: item.price || '',
        pickupTime: item.pickupTime || '',
        quantity: Number(item.quantity) > 0 ? Number(item.quantity) : 1,
        reservedAt: new Date().toISOString(),
        ...extra
    }, user));

    persistScopedArray(baseKey, [...normalizedItems, ...existingReservations], user);
}

function prependUserNotification(user, baseKey, notification) {
    if (!user || !notification) {
        return;
    }

    const notifications = loadOwnedScopedArray(baseKey, [], user);
    persistScopedArray(baseKey, [attachOwnership({ id: Date.now(), ...notification }, user), ...notifications], user);
}

function clearScopedCart(baseKey, user = getCurrentUser()) {
    window.localStorage.removeItem(getScopedStorageKey(baseKey, user));
}

function finalizeSuccessfulCheckout(user, items, returnPage) {
    if (!user || !Array.isArray(items) || items.length === 0) {
        return;
    }

    if (returnPage === pageRoutes.association) {
        appendItemsToUserReservations(user, 'foodloopAdminAssociationReservations', items, { status: 'Reserved' });
        appendItemsToUserHistory(user, 'foodloopAdminAssociationHistory', items, { status: 'Paid' });
        prependUserNotification(user, 'foodloopAdminAssociationNotifications', {
            title: 'Reservation confirmee',
            message: `${items.length} lot(s) ont ete ajoutes a votre historique.`
        });
        clearScopedCart(adminCartStorageKey, user);
        return;
    }

    appendItemsToUserReservations(user, 'foodloopBuyerReservations', items, { status: 'Reserved' });
    appendItemsToUserHistory(user, 'foodloopBuyerHistory', items, { status: 'Paid' });
    prependUserNotification(user, 'foodloopBuyerNotifications', {
        title: 'Reservation confirmee',
        message: `${items.length} produit(s) ont ete ajoutes a votre historique.`
    });
    clearScopedCart(buyerCartStorageKey, user);
}

function getOracleAnnonceId(productId) {
    if (productId >= 101 && productId <= 199) {
        return productId - 100;
    }

    return productId;
}

function getStoredAnnonceStockOverrides() {
    return loadGlobalObject(annonceStockOverridesKey, {});
}

function getCategoryImage(category) {
    const normalized = String(category || '').trim().toLowerCase();

    if (normalized.includes('boulangerie')) {
        return 'https://images.unsplash.com/photo-1774043132154-8934327dee3a?auto=format&fit=crop&fm=jpg&q=80&w=1200';
    }

    if (normalized.includes('plat') || normalized.includes('restauration') || normalized.includes('repas')) {
        return 'https://images.unsplash.com/photo-1568897798550-91c8caffe391?auto=format&fit=crop&fm=jpg&q=80&w=1200';
    }

    if (normalized.includes('lait') || normalized.includes('frais')) {
        return 'https://images.unsplash.com/photo-1612383277710-67896ecf4c69?auto=format&fit=crop&fm=jpg&q=80&w=1200';
    }

    if (normalized.includes('epicerie') || normalized.includes('fruit') || normalized.includes('legume')) {
        return 'https://images.unsplash.com/photo-1765480953875-a7338f896e91?auto=format&fit=crop&fm=jpg&q=80&w=1200';
    }

    return 'https://images.unsplash.com/photo-1584093092919-3d551a9c5055?auto=format&fit=crop&fm=jpg&q=80&w=1200';
}

function formatPrice(price) {
    const normalized = Number(price);
    return `${(Number.isFinite(normalized) ? normalized : 0).toFixed(2)} DT`;
}

function getStoredAnnonceStock(annonceId, fallbackQuantity = null) {
    const overrides = getStoredAnnonceStockOverrides();
    const key = String(annonceId);
    if (!Object.prototype.hasOwnProperty.call(overrides, key)) {
        return fallbackQuantity;
    }

    const quantity = Number(overrides[key]);
    return Number.isFinite(quantity) ? quantity : fallbackQuantity;
}

function setStoredAnnonceStock(annonceId, quantity) {
    const overrides = getStoredAnnonceStockOverrides();
    overrides[String(annonceId)] = Number(quantity);
    persistGlobalObject(annonceStockOverridesKey, overrides);
}

function applyStoredStockToProducts(products) {
    if (!Array.isArray(products)) {
        return;
    }

    products.forEach((product) => {
        if (!product || typeof product !== 'object') {
            return;
        }

        const annonceId = getOracleAnnonceId(Number(product.id));
        const fallbackQuantity = Object.prototype.hasOwnProperty.call(product, 'stock')
            ? product.stock
            : (Object.prototype.hasOwnProperty.call(product, 'quantity') ? product.quantity : null);
        const storedStock = getStoredAnnonceStock(annonceId, fallbackQuantity);
        if (Number.isFinite(storedStock)) {
            if (Object.prototype.hasOwnProperty.call(product, 'stock')) {
                product.stock = Math.max(0, Number(storedStock));
            }
            if (Object.prototype.hasOwnProperty.call(product, 'quantity')) {
                product.quantity = Math.max(0, Number(storedStock));
            }
        }
    });
}

function updateProductStockInView(products, reservedItems) {
    if (!Array.isArray(products) || !Array.isArray(reservedItems)) {
        return;
    }

    reservedItems.forEach((item) => {
        const annonceId = Number(item.annonce_id || getOracleAnnonceId(Number(item.id)));
        const remainingQuantity = Number(item.quantite_restante);
        if (!Number.isFinite(annonceId) || !Number.isFinite(remainingQuantity)) {
            return;
        }

        setStoredAnnonceStock(annonceId, remainingQuantity);
        const matchingProduct = products.find((product) => getOracleAnnonceId(Number(product.id)) === annonceId);
        if (matchingProduct) {
            if (Object.prototype.hasOwnProperty.call(matchingProduct, 'stock')) {
                matchingProduct.stock = Math.max(0, remainingQuantity);
            }
            if (Object.prototype.hasOwnProperty.call(matchingProduct, 'quantity')) {
                matchingProduct.quantity = Math.max(0, remainingQuantity);
            }
        }
    });
}

async function syncProductsWithBackendAvailability(products) {
    if (!Array.isArray(products) || products.length === 0) {
        return;
    }

    const annonceIds = [...new Set(products.map((product) => getOracleAnnonceId(Number(product.id))).filter((id) => Number.isInteger(id) && id > 0))];
    if (annonceIds.length === 0) {
        return;
    }

    const payload = await getFromPhpEndpoint(`${apiEndpoints.annonces}?ids=${encodeURIComponent(annonceIds.join(','))}`);
    const snapshots = new Map((payload.items || []).map((item) => [Number(item.annonce_id), item]));

    products.forEach((product) => {
        const annonceId = getOracleAnnonceId(Number(product.id));
        const snapshot = snapshots.get(annonceId);

        if (!snapshot) {
            if (Object.prototype.hasOwnProperty.call(product, 'stock')) {
                product.stock = 0;
            }
            product.isReservable = false;
            product.backendStatus = 'missing';
            return;
        }

        const quantity = Math.max(0, Number(snapshot.quantity) || 0);
        const statusCode = String(snapshot.status_code || '').trim().toLowerCase();

        if (Object.prototype.hasOwnProperty.call(product, 'stock')) {
            product.stock = quantity;
        }

        if (typeof snapshot.title === 'string' && snapshot.title.trim() !== '') {
            product.title = snapshot.title.trim();
        }

        if (typeof snapshot.description === 'string' && snapshot.description.trim() !== '') {
            product.description = snapshot.description.trim();
        }

        if (typeof snapshot.location === 'string' && snapshot.location.trim() !== '') {
            product.location = snapshot.location.trim();
        }

        product.backendStatus = statusCode;
        product.isReservable = ['available', 'priority_access'].includes(statusCode) && quantity > 0;
        setStoredAnnonceStock(annonceId, quantity);
    });
}

async function loadFeedProducts() {
    const payload = await getFromPhpEndpoint(`${apiEndpoints.annonces}?feed=1`);
    const items = Array.isArray(payload.items) ? payload.items : [];

    return items.map((item, index) => {
        const category = String(item.category || item.type || 'Produit');
        const zone = String(item.zone || item.city || item.location || 'Zone');
        const city = String(item.city || item.location || zone);

        return {
            id: Number(item.id),
            title: String(item.title || 'Annonce FoodLoop'),
            description: String(item.description || ''),
            location: String(item.location || city),
            zone,
            category,
            distance: index + 1,
            price: formatPrice(item.price),
            pickupTime: String(item.pickup_time || 'Horaire a confirmer'),
            stock: Math.max(0, Number(item.stock) || 0),
            image: getCategoryImage(category),
            isReservable: ['available', 'priority_access'].includes(String(item.status_code || '').toLowerCase()) && Number(item.stock) > 0,
            backendStatus: String(item.status_code || '').toLowerCase(),
            unit: String(item.unit || 'unite')
        };
    });
}

async function loadMerchantAnnouncements() {
    const payload = await getFromPhpEndpoint(`${apiEndpoints.annonces}?owner_feed=1`);
    const items = Array.isArray(payload.items) ? payload.items : [];

    return items.map((item) => attachOwnership({
        id: Number(item.id),
        title: String(item.title || 'Annonce FoodLoop'),
        description: String(item.description || ''),
        foodType: String(item.food_type || item.category || 'Produit'),
        quantity: Math.max(0, Number(item.quantity) || 0),
        unit: String(item.unit || 'unite'),
        location: String(item.location || ''),
        status: String(item.status_code || 'available'),
        price: formatPrice(item.price),
        pickupTime: String(item.pickup_time || 'Horaire a confirmer'),
        paymentMethod: 'N/A'
    }, getCurrentUser()));
}

async function showDesktopNotification(title, body) {
    if (typeof window === 'undefined' || typeof window.Notification === 'undefined') {
        return;
    }

    let permission = window.Notification.permission;
    if (permission === 'default') {
        permission = await window.Notification.requestPermission();
    }

    if (permission === 'granted') {
        new window.Notification(title, { body });
    }
}

function broadcastReservationEvent(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return;
    }

    const firstItem = items[0];
    const payload = {
        id: Date.now(),
        count: items.length,
        title: firstItem && firstItem.title ? String(firstItem.title) : 'Reservation',
        timestamp: new Date().toISOString()
    };
    window.localStorage.setItem(reservationEventStorageKey, JSON.stringify(payload));
}

function registerReservationRealtimeHandlers(onUpdate) {
    window.addEventListener('storage', (event) => {
        if (event.key === annonceStockOverridesKey && typeof onUpdate === 'function') {
            onUpdate();
            return;
        }

        if (event.key !== reservationEventStorageKey || !event.newValue) {
            return;
        }

        try {
            const payload = JSON.parse(event.newValue);
            if (typeof onUpdate === 'function') {
                onUpdate();
            }
            showDesktopNotification(
                'Nouvelle reservation FoodLoop',
                payload && payload.count > 1
                    ? `${payload.count} reservations viennent d'etre enregistrees.`
                    : `${payload.title || 'Une reservation'} vient d'etre enregistree.`
            );
        } catch (error) {
            if (typeof onUpdate === 'function') {
                onUpdate();
            }
        }
    });
}

async function createReservationsFromCart(items) {
    const createdReservations = [];

    for (const item of items) {
        const formData = new FormData();
        const annonceId = getOracleAnnonceId(Number(item.id));
        const requestedQuantity = Number(item.quantity) > 0 ? Number(item.quantity) : 1;
        formData.append('annonce_id', String(annonceId));
        formData.append('quantite', String(requestedQuantity));

        const payload = await postToPhpEndpoint(apiEndpoints.reserve, formData);
        createdReservations.push({
            ...item,
            annonce_id: annonceId,
            quantity: requestedQuantity,
            reservation_id: payload.reservation_id,
            quantite_restante: Number(payload.quantite_restante)
        });
    }

    return createdReservations;
}

async function processPaymentsForCart(items, method, extraFields = {}) {
    const results = [];

    for (const item of items) {
        const normalizedReservationId = Number(item && item.reservation_id);
        const normalizedAmount = (Number(item && item.quantity) > 0 ? Number(item.quantity) : 1) * parsePaymentPrice(item && item.price);

        if (!Number.isInteger(normalizedReservationId) || normalizedReservationId <= 0) {
            throw new Error('Cette session de paiement est invalide. Retournez au dashboard et refaites la reservation.');
        }

        if (!Number.isFinite(normalizedAmount) || normalizedAmount <= 0) {
            throw new Error('Montant de paiement invalide. Retournez au dashboard et refaites la reservation.');
        }

        const formData = new FormData();
        formData.append('reservation_id', String(normalizedReservationId));
        formData.append('montant', String(normalizedAmount));
        formData.append('method', method);

        Object.entries(extraFields).forEach(([key, value]) => {
            formData.append(key, value);
        });

        const payload = await postToPhpEndpoint(apiEndpoints.payment, formData);
        results.push(payload);
    }

    return results;
}

function parsePaymentPrice(value) {
    return Number.parseFloat(String(value).replace(' DT', '').replace(',', '.')) || 0;
}

if (adminAccessApp) {
    const adminAccessForm = document.querySelector('#adminAccessForm');
    const adminCodeInput = document.querySelector('#adminCodeInput');
    const adminAccessMessage = document.querySelector('#adminAccessMessage');

    adminAccessForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submittedCode = adminCodeInput instanceof HTMLInputElement ? adminCodeInput.value.trim() : '';

        if (submittedCode === '') {
            adminAccessMessage.textContent = 'Code incorrect';
            return;
        }

        try {
            const formData = new FormData();
            formData.append('adminCode', submittedCode);
            const payload = await postToPhpEndpoint(apiEndpoints.adminAccess, formData);

            const superadminUser = {
                id: 1,
                email: 'superadmin@foodloop.local',
                role: payload.role || 'superadmin',
                name: payload.name || 'Superadmin FoodLoop'
            };
            window.localStorage.setItem(superadminRoleKey, 'superadmin');
            setCurrentUser(superadminUser);
            adminAccessMessage.textContent = '';
            adminAccessApp.classList.add('is-redirecting');
            window.setTimeout(() => {
                window.location.href = pageRoutes.admin;
            }, 180);
        } catch (error) {
            adminAccessMessage.textContent = error instanceof Error ? error.message : 'Code incorrect';
        }
    });
}

if (marketplaceRoot) {
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
        modalActionButton.textContent = 'Reserver';
        reservationModal.classList.add('is-open');
        reservationModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
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

    productsGrid.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('[data-product-id]')) {
            const currentUser = await fetchAuthenticatedUser();

            if (!currentUser || typeof currentUser.role !== 'string' || currentUser.role.trim() === '') {
                closeReservationModal();
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

    const authState = {
        step: 'login',
        role: 'acheteur',
        errorMessage: '',
        successMessage: '',
        isSubmitting: false
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
                subtitle: ''
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
                    ${authState.successMessage ? `<p class="inline-success">${authState.successMessage}</p>` : ''}
                    <button class="auth-primary-button" type="submit" ${authState.isSubmitting ? 'disabled' : ''}>${authState.isSubmitting ? 'Connexion...' : 'Se connecter'}</button>
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
                        ${schema.subtitle ? `<span>${schema.subtitle}</span>` : ''}
                    </div>
                    ${fields}
                    ${authState.errorMessage ? `<p class="inline-error">${authState.errorMessage}</p>` : ''}
                    ${authState.successMessage ? `<p class="inline-success">${authState.successMessage}</p>` : ''}
                    <button class="auth-primary-button" type="submit" ${authState.isSubmitting ? 'disabled' : ''}>${authState.isSubmitting ? 'Creation...' : 'Creer le compte'}</button>
                </form>
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
            authState.successMessage = '';
            renderAuthApp();
        }
    });

    authStepContent.addEventListener('change', (event) => {
        const target = event.target;
        if (target instanceof HTMLInputElement && target.name === 'role') {
            authState.role = target.value;
        }
    });

    authStepContent.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const action = form.getAttribute('data-auth-action');

        if (action === 'login') {
            const formData = new FormData(form);
            const email = String(formData.get('email') || '').trim().toLowerCase();
            authState.isSubmitting = true;
            authState.errorMessage = '';
            authState.successMessage = '';
            renderAuthApp();

            try {
                const payload = await postToPhpEndpoint(apiEndpoints.login, formData);

                const connectedUser = {
                    id: payload.user_id,
                    email,
                    role: payload.role,
                    name: payload.name || email
                };
                setCurrentUser(connectedUser);
                authState.isSubmitting = false;
                authState.successMessage = '';
                redirectToRoleHome(payload.role);
            } catch (error) {
                authState.isSubmitting = false;
                authState.errorMessage = error instanceof Error ? error.message : 'Email ou mot de passe incorrect';
                renderAuthApp();
            }
            return;
        }

        if (action === 'role') {
            const formData = new FormData(form);
            authState.role = String(formData.get('role') || 'acheteur');
            authState.step = 'form';
            authState.errorMessage = '';
            authState.successMessage = '';
            renderAuthApp();
            return;
        }

        if (action === 'create-account') {
            const localFormData = new FormData(form);
            const signupFormData = new FormData();
            const email = String(localFormData.get('email') || '').trim().toLowerCase();

            signupFormData.append('email', email);
            signupFormData.append('password', String(localFormData.get('password') || ''));
            signupFormData.append('role', authState.role);

            if (authState.role === 'acheteur') {
                signupFormData.append('prenom', String(localFormData.get('prenom') || '').trim());
                signupFormData.append('nom', String(localFormData.get('nom') || '').trim());
                signupFormData.append('telephone', String(localFormData.get('telephone') || '').trim());
                signupFormData.append('adresse', String(localFormData.get('adresse') || '').trim());
            }

            if (authState.role === 'commerce') {
                const commerceName = String(localFormData.get('nomCommerce') || '').trim();
                signupFormData.append('prenom', 'Commerce');
                signupFormData.append('nom', commerceName || 'FoodLoop');
                signupFormData.append('telephone', '00000000');
                signupFormData.append('adresse', String(localFormData.get('adresse') || '').trim());
                signupFormData.append('nom_organisation', commerceName);
                signupFormData.append('business_license', String(localFormData.get('licence') || '').trim());
            }

            if (authState.role === 'admin_association') {
                const associationName = String(localFormData.get('association') || '').trim();
                signupFormData.append('prenom', 'Admin');
                signupFormData.append('nom', associationName || 'Association');
                signupFormData.append('telephone', String(localFormData.get('telephone') || '').trim());
                signupFormData.append('adresse', String(localFormData.get('adresse') || '').trim());
                signupFormData.append('nom_organisation', associationName);
            }

            authState.isSubmitting = true;
            authState.errorMessage = '';
            authState.successMessage = '';
            renderAuthApp();

            try {
                await postToPhpEndpoint(apiEndpoints.signup, signupFormData);
                authState.successMessage = 'Compte cree avec succes. Connectez-vous maintenant.';

                authState.isSubmitting = false;
                authState.errorMessage = '';
                authState.step = 'login';
                renderAuthApp();
            } catch (error) {
                authState.isSubmitting = false;
                authState.errorMessage = error instanceof Error ? error.message : 'Inscription impossible.';
                renderAuthApp();
            }
        }
    });

    renderAuthApp();
}

if (buyerDashboard && requireRole('acheteur')) {
    const cartStorageKey = 'foodloopBuyerCart';
    const buyerNotificationsKey = 'foodloopBuyerNotifications';
    const buyerReservationsKey = 'foodloopBuyerReservations';
    const buyerHistoryKey = 'foodloopBuyerHistory';
    const currentBuyerUser = getCurrentUser();
    const buyer = {
        name: getUserDisplayName(currentBuyerUser, 'Acheteur FoodLoop'),
        zone: 'Tunis Centre',
        preferredCategories: ['Epicerie', 'Frais', 'Boulangerie']
    };

    const dashboardProducts = [];

    applyStoredStockToProducts(dashboardProducts);

    const dashboardState = {
        currentView: 'profile',
        cart: loadStoredCart(),
        notifications: loadOwnedScopedArray(buyerNotificationsKey, [], currentBuyerUser),
        reservations: loadOwnedScopedArray(buyerReservationsKey, [], currentBuyerUser),
        history: loadOwnedScopedArray(buyerHistoryKey, [], currentBuyerUser)
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
    const buyerNameElement = document.querySelector('#buyerName');
    const buyerZoneElement = document.querySelector('#buyerZone');
    const buyerAvatarElement = document.querySelector('.profile-avatar');

    if (buyerNameElement) {
        buyerNameElement.textContent = buyer.name;
    }

    if (buyerZoneElement) {
        buyerZoneElement.textContent = buyer.zone;
    }

    if (buyerAvatarElement) {
        buyerAvatarElement.textContent = getUserInitials(buyer.name, 'A');
    }

    function loadStoredCart() {
        return loadOwnedScopedArray(cartStorageKey, [], currentBuyerUser);
    }

    function persistCart() {
        persistScopedArray(cartStorageKey, dashboardState.cart, currentBuyerUser);
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
            const isReservable = product.isReservable !== false;
            const matchesTerm =
                term === '' ||
                product.title.toLowerCase().includes(term) ||
                product.description.toLowerCase().includes(term) ||
                product.location.toLowerCase().includes(term) ||
                product.category.toLowerCase().includes(term);

            const matchesZone = zone === '' || product.zone === zone;
            const matchesCategory = category === '' || product.category === category;
            const matchesDistance = distance === null || product.distance <= distance;

            return isReservable && matchesTerm && matchesZone && matchesCategory && matchesDistance;
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
                    <p class="inline-note">Distance: ${product.distance} km</p>
                    <button class="dashboard-primary-button" type="button" data-reserve-product="${product.id}" ${product.isReservable === false || product.stock <= 0 ? 'disabled' : ''}>${product.isReservable === false || product.stock <= 0 ? 'Indisponible' : 'Reserver'}</button>
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
        const reserved = [...dashboardState.cart, ...dashboardState.reservations];

        if (reserved.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucune reservation active pour le moment.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="reservation-list">${reserved.map((product) => `
            <article class="reservation-card">
                <h3>${product.title}</h3>
                <p>${product.location} · ${product.price}</p>
                <p class="reservation-meta">Quantite: ${product.quantity || 1}</p>
                <p class="pickup-time">Pickup time: ${product.pickupTime}</p>
                <p>${product.status || 'Reservation en attente'}</p>
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
        const historyItems = dashboardState.history;
        if (historyItems.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Votre historique est encore vide.</div>';
            return;
        }

        contentRoot.innerHTML = `<div class="history-list">${historyItems.map((product) => `
            <article class="history-card">
                <h3>${product.title}</h3>
                <p>${product.description}</p>
                <p>${product.location} · ${product.price}</p>
                <p class="reservation-meta">Quantite: ${product.quantity || 1}</p>
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

    registerReservationRealtimeHandlers(() => {
        applyStoredStockToProducts(dashboardProducts);
        renderCurrentView();
    });

    function addToCart(productId) {
        if (!dashboardState.cart.some((item) => item.id === productId)) {
            const product = dashboardProducts.find((item) => item.id === productId);
            if (!product || product.stock <= 0 || product.isReservable === false) {
                return;
            }
            dashboardState.cart.push(attachOwnership({
                ...product,
                stock: product.stock,
                isReservable: product.isReservable !== false
            }, currentBuyerUser));
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
            document.querySelector('#confirmReservationButton')?.addEventListener('click', async () => {
                try {
                    const reservableCartItems = dashboardState.cart.filter((item) => item.isReservable !== false && Number(item.stock) > 0);
                    if (reservableCartItems.length === 0) {
                        throw new Error('Aucun produit du panier n est encore reservable.');
                    }

                    const reservedItems = await createReservationsFromCart(reservableCartItems.map((item) => ({
                        ...item,
                        quantity: 1
                    })));
                    updateProductStockInView(dashboardProducts, reservedItems);
                    broadcastReservationEvent(reservedItems);
                    await showDesktopNotification(
                        'Nouvelle reservation FoodLoop',
                        `${reservedItems.length} reservation(s) enregistree(s).`
                    );
                    setCheckoutState(reservedItems, pageRoutes.buyer);
                    window.location.href = pageRoutes.payment;
                } catch (error) {
                    panelContent.insertAdjacentHTML('beforeend', `<p class="inline-error">${error instanceof Error ? error.message : 'Reservation impossible.'}</p>`);
                }
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
        clearApplicationState();
        window.location.href = pageRoutes.logout;
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-panel') === 'true') {
            closePanel();
        }
    });

    updateBadges();
    renderCurrentView();
    loadFeedProducts()
        .then((items) => {
            dashboardProducts.splice(0, dashboardProducts.length, ...items);
            zoneSelect.innerHTML = '<option value="">Toutes</option>';
            categorySelect.innerHTML = '<option value="">Toutes</option>';
            populateDashboardFilters();
            renderCurrentView();
        })
        .catch(() => {
            renderCurrentView();
        });
}

if (adminAssociationDashboard && requireRole('admin_association')) {
    const userRole = 'admin_association';
    const currentAssociationUser = getCurrentUser();
    const adminNotificationsKey = 'foodloopAdminAssociationNotifications';
    const adminReservationsKey = 'foodloopAdminAssociationReservations';
    const adminHistoryKey = 'foodloopAdminAssociationHistory';
    let cart = loadOwnedScopedArray(adminCartStorageKey, [], currentAssociationUser);
    const products = [];

    const adminState = {
        currentView: 'products',
        notifications: loadOwnedScopedArray(adminNotificationsKey, [], currentAssociationUser),
        reservations: loadOwnedScopedArray(adminReservationsKey, [], currentAssociationUser),
        history: loadOwnedScopedArray(adminHistoryKey, [], currentAssociationUser)
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
    const adminAssociationNameElement = document.querySelector('#adminAssociationName');
    const adminAssociationAvatarElement = document.querySelector('#adminAssociationDashboard .profile-avatar');
    const adminAssociationDisplayName = getUserDisplayName(currentAssociationUser, 'Association FoodLoop');

    if (adminAssociationNameElement) {
        adminAssociationNameElement.textContent = adminAssociationDisplayName;
    }

    if (adminAssociationAvatarElement) {
        adminAssociationAvatarElement.textContent = getUserInitials(adminAssociationDisplayName, 'AA');
    }

    function persistCart() {
        persistScopedArray(adminCartStorageKey, cart, currentAssociationUser);
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
            const isReservable = product.isReservable !== false;
            const matchesTerm =
                term === '' ||
                product.title.toLowerCase().includes(term) ||
                product.description.toLowerCase().includes(term) ||
                product.location.toLowerCase().includes(term) ||
                product.category.toLowerCase().includes(term);

            const matchesZone = zone === '' || product.zone === zone;
            const matchesCategory = category === '' || product.category === category;
            const matchesDistance = distance === null || product.distance <= distance;

            return isReservable && matchesTerm && matchesZone && matchesCategory && matchesDistance;
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
                        <button class="dashboard-primary-button" type="button" data-reserve-product="${product.id}" ${product.isReservable === false || product.stock <= 0 ? 'disabled' : ''}>${product.isReservable === false || product.stock <= 0 ? 'Indisponible' : 'Reserver'}</button>
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
            return `
                <article class="reservation-card">
                    <h3>${entry.title}</h3>
                    <p>${entry.location} · ${entry.price}</p>
                    <p class="reservation-meta">Quantite reservee: ${entry.quantity}</p>
                    <p class="pickup-time">Pickup time: ${entry.pickupTime}</p>
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
            return `
                <article class="history-card">
                    <h3>${entry.title}</h3>
                    <p>${entry.description || ''}</p>
                    <p>${entry.location} · ${entry.price}</p>
                    <p class="reservation-meta">Quantite retiree: ${entry.quantity}</p>
                    <p class="pickup-time">Pickup time: ${entry.pickupTime}</p>
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
        if (!product || product.isReservable === false || product.stock <= 0) {
            return;
        }

        const existingItem = cart.find((item) => item.id === productId);
        if (existingItem) {
            existingItem.quantity = quantity;
            existingItem.stock = product.stock;
            existingItem.isReservable = product.isReservable !== false;
        } else {
            cart.push(attachOwnership({
                id: product.id,
                title: product.title,
                quantity,
                price: product.price,
                pickupTime: product.pickupTime,
                location: product.location,
                stock: product.stock,
                isReservable: product.isReservable !== false
            }, currentAssociationUser));
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

    registerReservationRealtimeHandlers(() => {
        applyStoredStockToProducts(products);
        renderCurrentView();
    });

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

        document.querySelector('#confirmAdminReservationButton')?.addEventListener('click', async () => {
            try {
                const reservableCartItems = cart.filter((item) => item.isReservable !== false && Number(item.stock) > 0);
                if (reservableCartItems.length === 0) {
                    throw new Error('Aucun produit du panier n est encore reservable.');
                }

                const reservedItems = await createReservationsFromCart(reservableCartItems);
                updateProductStockInView(products, reservedItems);
                broadcastReservationEvent(reservedItems);
                await showDesktopNotification(
                    'Nouvelle reservation FoodLoop',
                    `${reservedItems.length} reservation(s) enregistree(s).`
                );
                setCheckoutState(reservedItems, pageRoutes.association);
                window.location.href = pageRoutes.payment;
            } catch (error) {
                panelContent.insertAdjacentHTML('beforeend', `<p class="inline-error">${error instanceof Error ? error.message : 'Reservation impossible.'}</p>`);
            }
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
        clearApplicationState();
        window.location.href = pageRoutes.logout;
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-admin-panel') === 'true') {
            closePanel();
        }
    });

    updateCart();
    renderCurrentView();
    loadFeedProducts()
        .then((items) => {
            products.splice(0, products.length, ...items);
            zoneSelect.innerHTML = '<option value="">Toutes</option>';
            categorySelect.innerHTML = '<option value="">Toutes</option>';
            populateDashboardFilters();
            renderCurrentView();
        })
        .catch(() => {
            renderCurrentView();
        });
}

if (merchantDashboard && requireRole('commerce')) {
    const currentMerchantUser = getCurrentUser();
    const merchantProductsKey = 'foodloopMerchantProducts';
    const merchantHistoryKey = 'foodloopMerchantHistory';
    const merchantReservationsKey = 'foodloopMerchantReservations';
    const merchantNotificationsKey = 'foodloopMerchantNotifications';
    const products = [];
    const history = loadOwnedScopedArray(merchantHistoryKey, [], currentMerchantUser);
    const reservations = loadOwnedScopedArray(merchantReservationsKey, [], currentMerchantUser);

    const merchantState = {
        currentView: 'publish',
        notifications: loadOwnedScopedArray(merchantNotificationsKey, [], currentMerchantUser),
        lastPublishMessage: '',
        lastPublishError: ''
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
    const merchantNameElement = document.querySelector('#merchantDashboard .profile-card strong');
    const merchantAvatarElement = document.querySelector('#merchantDashboard .profile-avatar');
    const merchantDisplayName = getUserDisplayName(currentMerchantUser, 'Commerce FoodLoop');

    if (merchantNameElement) {
        merchantNameElement.textContent = merchantDisplayName;
    }

    if (merchantAvatarElement) {
        merchantAvatarElement.textContent = getUserInitials(merchantDisplayName, 'CF');
    }

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

    function persistMerchantState() {
        persistScopedArray(merchantHistoryKey, history, currentMerchantUser);
        persistScopedArray(merchantReservationsKey, reservations, currentMerchantUser);
        persistScopedArray(merchantNotificationsKey, merchantState.notifications, currentMerchantUser);
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
                    <p>Prix: ${product.price}</p>
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
                        <label class="merchant-field">
                            <span>Prix</span>
                            <input type="number" name="price" min="0" step="0.01" placeholder="3.50" required>
                        </label>
                    </div>
                    <div class="merchant-submit-row">
                        <button class="dashboard-primary-button" type="submit">Publier</button>
                    </div>
                    ${merchantState.lastPublishMessage ? `<p class="inline-success">${merchantState.lastPublishMessage}</p>` : ''}
                    ${merchantState.lastPublishError ? `<p class="inline-error">${merchantState.lastPublishError}</p>` : ''}
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

    async function publishProduct(formData) {
        const quantity = Number(formData.get('quantity'));
        const title = String(formData.get('title')).trim();
        const description = String(formData.get('description')).trim();
        const foodType = String(formData.get('foodType')).trim();
        const unit = String(formData.get('unit')).trim();
        const location = String(formData.get('location')).trim();
        const submittedPrice = Number(formData.get('price'));
        const normalizedPrice = Number.isFinite(submittedPrice) ? submittedPrice : 0;
        const pickupHour = String(17 + Math.min(quantity, 3)).padStart(2, '0');
        const pickupTime = `${pickupHour}:30`;

        const phpFormData = new FormData();
        phpFormData.append('titre', title);
        phpFormData.append('description', description);
        phpFormData.append('type', foodType);
        phpFormData.append('quantite', String(quantity));
        phpFormData.append('unite', unit);
        phpFormData.append('localisation', location);
        phpFormData.append('prix', normalizedPrice.toFixed(2));
        phpFormData.append('pickup_time', `${new Date().toISOString().slice(0, 10)} ${pickupTime}:00`);

        await postToPhpEndpoint(apiEndpoints.createProduct, phpFormData);
        const freshProducts = await loadMerchantAnnouncements();
        products.splice(0, products.length, ...freshProducts);
        merchantState.lastPublishMessage = `Annonce publiee: ${title}`;
        merchantState.lastPublishError = '';
        merchantState.notifications.unshift(attachOwnership({
            id: Date.now() + 1,
            title: 'Annonce publiee',
            message: `${title} a ete ajoutee a vos annonces actives.`
        }, currentMerchantUser));
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
        persistMerchantState();
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
            merchantState.notifications.unshift(attachOwnership({
                id: Date.now() + 2,
                title: 'Pickup confirme',
                message: `${movedProduct.title} a ete marquee comme retiree.`
            }, currentMerchantUser));
            persistMerchantState();
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

    registerReservationRealtimeHandlers(() => {
        loadMerchantAnnouncements()
            .then((items) => {
                products.splice(0, products.length, ...items);
                renderCurrentView();
            })
            .catch(() => {
                renderCurrentView();
            });
    });

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
            merchantState.currentView = link.getAttribute('data-merchant-view') || 'publish';
            renderCurrentView();
        });
    });

    contentRoot.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.id !== 'merchantPublishForm') {
            return;
        }

        try {
            await publishProduct(new FormData(form));
            form.reset();
        } catch (error) {
            merchantState.lastPublishMessage = '';
            merchantState.lastPublishError = error instanceof Error ? error.message : 'Publication impossible.';
        }
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
        clearApplicationState();
        window.location.href = pageRoutes.logout;
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-merchant-panel') === 'true') {
            closePanel();
        }
    });

    updateNotifications();
    loadMerchantAnnouncements()
        .then((items) => {
            products.splice(0, products.length, ...items);
            renderCurrentView();
        })
        .catch(() => {
            renderCurrentView();
        });
    renderCurrentView();
}

if (superadminDashboard && requireRole('superadmin')) {
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

    const adminState = {
        currentView: 'global',
        notifications: [],
        data: {
            summary: {
                buyers_active: 0,
                commerces_active: 0,
                associations_active: 0,
                reservations_total: 0
            },
            buyers: [],
            commerces: [],
            associations: [],
            reservations_by_status: [],
            account_mix: [],
            reports: [],
            system: {
                database: 'Oracle FOODLOOP',
                accounts_loaded: 0,
                suspended_accounts: 0,
                queued_notifications: 0,
                scheduled_pickups: 0,
                reports_total: 0,
                moderation_table: ''
            }
        }
    };

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
        const value = String(status || '').toLowerCase();
        if (value.includes('suspend')) {
            return ' is-suspended';
        }
        if (value.includes('actif') || value.includes('valide')) {
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
                description: 'Consultez les comptes et reservations reelles issus de la base Oracle.'
            },
            buyers: {
                kicker: 'Gestion roles',
                title: 'Gestion acheteurs',
                description: 'Liste Oracle des acheteurs avec suspension et suppression reelles.'
            },
            commerces: {
                kicker: 'Gestion roles',
                title: 'Gestion commerces',
                description: 'Liste Oracle des proprietaires commerce avec moderation reelle.'
            },
            associations: {
                kicker: 'Gestion roles',
                title: 'Gestion associations',
                description: 'Liste Oracle des admins association avec moderation reelle.'
            },
            reports: {
                kicker: 'Moderation',
                title: 'Signalements',
                description: 'Rapports Oracle, consultations et volumes consolides en temps reel.'
            },
            system: {
                kicker: 'Infrastructure',
                title: 'Systeme',
                description: 'Vue systeme reliee a la session superadmin et aux actions Oracle.'
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

    async function loadSuperadminData() {
        const payload = await getFromPhpEndpoint(apiEndpoints.superadmin);
        adminState.data = {
            summary: payload.summary || adminState.data.summary,
            buyers: Array.isArray(payload.buyers) ? payload.buyers : [],
            commerces: Array.isArray(payload.commerces) ? payload.commerces : [],
            associations: Array.isArray(payload.associations) ? payload.associations : [],
            reservations_by_status: Array.isArray(payload.reservations_by_status) ? payload.reservations_by_status : [],
            account_mix: Array.isArray(payload.account_mix) ? payload.account_mix : [],
            reports: Array.isArray(payload.reports) ? payload.reports : [],
            system: payload.system && typeof payload.system === 'object' ? payload.system : adminState.data.system
        };
    }

    async function runSuperadminAction(action, accountType, userId, label) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('account_type', accountType);
        formData.append('user_id', String(userId));

        const payload = await postToPhpEndpoint(apiEndpoints.superadmin, formData);
        adminState.data = {
            summary: payload.summary || adminState.data.summary,
            buyers: Array.isArray(payload.buyers) ? payload.buyers : adminState.data.buyers,
            commerces: Array.isArray(payload.commerces) ? payload.commerces : adminState.data.commerces,
            associations: Array.isArray(payload.associations) ? payload.associations : adminState.data.associations,
            reservations_by_status: Array.isArray(payload.reservations_by_status) ? payload.reservations_by_status : adminState.data.reservations_by_status,
            account_mix: Array.isArray(payload.account_mix) ? payload.account_mix : adminState.data.account_mix,
            reports: Array.isArray(payload.reports) ? payload.reports : adminState.data.reports,
            system: payload.system && typeof payload.system === 'object' ? payload.system : adminState.data.system
        };
        adminState.notifications.unshift({
            id: Date.now(),
            title: 'Action superadmin',
            message: `${label} applique avec succes sur Oracle.`
        });
        updateNotifications();
        renderCurrentView();
    }

    function renderGlobalView() {
        const summary = adminState.data.summary;
        contentRoot.innerHTML = `
            <div class="superadmin-stats-grid">
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Acheteurs actifs</p>
                    <strong>${summary.buyers_active}</strong>
                    <p>Comptes acheteurs actifs dans Oracle.</p>
                </article>
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Commerces actifs</p>
                    <strong>${summary.commerces_active}</strong>
                    <p>Comptes commerce actifs dans Oracle.</p>
                </article>
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Associations actives</p>
                    <strong>${summary.associations_active}</strong>
                    <p>Admins association actifs dans Oracle.</p>
                </article>
                <article class="superadmin-stat-card">
                    <p class="dashboard-kicker">Reservations total</p>
                    <strong>${summary.reservations_total}</strong>
                    <p>Volume total de reservations en base Oracle.</p>
                </article>
            </div>
            <div class="admin-chart-grid">
                <article class="merchant-chart-card">
                    <h3>Repartition comptes</h3>
                    <div id="superadminUsageChart" class="chart-surface"></div>
                </article>
                <article class="merchant-chart-card">
                    <h3>Reservations par statut</h3>
                    <div id="superadminReservationsChart" class="chart-surface"></div>
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

    function actionButtons(item, accountType) {
        const suspendAction = item.status === 'Suspendu' ? 'Activer' : 'Suspendre';
        const suspendAttr = item.status === 'Suspendu' ? 'data-unsuspend-account' : 'data-suspend-account';
        return `
            <div class="superadmin-actions">
                <button class="admin-action-button admin-action-secondary" type="button" ${suspendAttr}="${accountType}:${item.id}">${suspendAction}</button>
                <button class="admin-action-button admin-action-danger" type="button" data-delete-account="${accountType}:${item.id}">Supprimer</button>
            </div>
        `;
    }

    function renderBuyersView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Nom</span>
                <span>Email</span>
                <span>Actions</span>
            </div>
            ${adminState.data.buyers.map((buyer) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${buyer.name}</strong>
                        ${statusPill(buyer.status)}
                    </div>
                    <span>${buyer.email}</span>
                    ${actionButtons(buyer, 'buyer')}
                </div>
            `).join('')}
        `;
        contentRoot.innerHTML = `<div class="superadmin-table-grid">${renderRoleTable('Liste Oracle des acheteurs', rows)}</div>`;
    }

    function renderCommercesView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Nom</span>
                <span>Email / Type</span>
                <span>Actions</span>
            </div>
            ${adminState.data.commerces.map((commerce) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${commerce.name}</strong>
                        ${statusPill(commerce.status)}
                    </div>
                    <div class="superadmin-table-meta">
                        <span>${commerce.email}</span>
                        <span>${commerce.type}</span>
                    </div>
                    ${actionButtons(commerce, 'commerce')}
                </div>
            `).join('')}
        `;
        contentRoot.innerHTML = `<div class="superadmin-table-grid">${renderRoleTable('Liste Oracle des commerces', rows)}</div>`;
    }

    function renderAssociationsView() {
        const rows = `
            <div class="superadmin-table-head">
                <span>Nom</span>
                <span>Email / Statut</span>
                <span>Actions</span>
            </div>
            ${adminState.data.associations.map((association) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${association.name}</strong>
                    </div>
                    <div class="superadmin-table-meta">
                        <span>${association.email}</span>
                        ${statusPill(association.status)}
                    </div>
                    ${actionButtons(association, 'association')}
                </div>
            `).join('')}
        `;
        contentRoot.innerHTML = `<div class="superadmin-table-grid superadmin-table-grid-wide">${renderRoleTable('Liste Oracle des associations', rows)}</div>`;
    }

    function renderReportsView() {
        if (adminState.data.reports.length === 0) {
            contentRoot.innerHTML = '<div class="dashboard-empty">Aucun rapport Oracle n est disponible pour le moment.</div>';
            return;
        }

        const rows = `
            <div class="superadmin-table-head">
                <span>Rapport</span>
                <span>Periode / consultations</span>
                <span>Impact</span>
            </div>
            ${adminState.data.reports.map((report) => `
                <div class="superadmin-table-row">
                    <div class="superadmin-table-meta">
                        <strong>${report.type}</strong>
                        <span>Genere le ${report.created_at}</span>
                    </div>
                    <div class="superadmin-table-meta">
                        <span>${report.period}</span>
                        <span>Commerces: ${report.commerce_views} · Superadmin: ${report.superadmin_views}</span>
                    </div>
                    <div class="superadmin-table-meta">
                        <span>${report.food_saved_kg.toFixed(2)} kg sauves</span>
                        <span>${report.total_reservations} reservations · ${report.total_distributions} distributions</span>
                    </div>
                </div>
            `).join('')}
        `;

        contentRoot.innerHTML = `<div class="superadmin-table-grid superadmin-table-grid-wide">${renderRoleTable('Rapports Oracle', rows)}</div>`;
    }

    function renderSystemView() {
        const system = adminState.data.system || {};
        contentRoot.innerHTML = `
            <div class="system-grid">
                <article class="system-card">
                    <h3>Etat superadmin</h3>
                    <div class="monitoring-list">
                        <div class="monitoring-item">
                            <strong>Source</strong>
                            <p>${system.database || 'Oracle FOODLOOP'}</p>
                        </div>
                        <div class="monitoring-item">
                            <strong>Comptes charges</strong>
                            <p>${system.accounts_loaded ?? 0}</p>
                        </div>
                        <div class="monitoring-item">
                            <strong>Comptes suspendus</strong>
                            <p>${system.suspended_accounts ?? 0}</p>
                        </div>
                        <div class="monitoring-item">
                            <strong>Pickups en attente</strong>
                            <p>${system.scheduled_pickups ?? 0}</p>
                        </div>
                        <div class="monitoring-item">
                            <strong>Notifications en file</strong>
                            <p>${system.queued_notifications ?? 0}</p>
                        </div>
                        <div class="monitoring-item">
                            <strong>Moderation</strong>
                            <p>${system.moderation_table || 'Non disponible'}</p>
                        </div>
                    </div>
                </article>
                <article class="system-card">
                    <h3>Logs d actions</h3>
                    <div class="system-log-list">
                        ${adminState.notifications.map((log) => `
                            <div class="system-log-item">
                                <strong>${log.title}</strong>
                                <p>${log.message}</p>
                            </div>
                        `).join('') || '<div class="system-log-item"><strong>Aucun log</strong><p>Aucune action superadmin sur cette session.</p></div>'}
                    </div>
                </article>
            </div>
        `;
    }

    function renderCharts() {
        if (typeof window.CanvasJS === 'undefined') {
            return;
        }

        const usageChart = new window.CanvasJS.Chart('superadminUsageChart', {
            animationEnabled: true,
            backgroundColor: 'transparent',
            data: [{
                type: 'pie',
                startAngle: 220,
                indexLabel: '{label}: {y}',
                dataPoints: adminState.data.account_mix.map((item, index) => ({
                    label: item.label,
                    y: item.value,
                    color: ['#9AB17A', '#C3CC9B', '#E4DFB5'][index % 3]
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
                dataPoints: adminState.data.reservations_by_status.map((item) => ({
                    label: item.label,
                    y: item.value
                }))
            }]
        });

        usageChart.render();
        reservationsChart.render();
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

    contentRoot.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const deleteToken = target.getAttribute('data-delete-account');
        const suspendToken = target.getAttribute('data-suspend-account');
        const unsuspendToken = target.getAttribute('data-unsuspend-account');

        try {
            if (deleteToken) {
                const [accountType, rawId] = deleteToken.split(':');
                await runSuperadminAction('delete', accountType, Number(rawId), 'Suppression');
            }

            if (suspendToken) {
                const [accountType, rawId] = suspendToken.split(':');
                await runSuperadminAction('suspend', accountType, Number(rawId), 'Suspension');
            }

            if (unsuspendToken) {
                const [accountType, rawId] = unsuspendToken.split(':');
                await runSuperadminAction('unsuspend', accountType, Number(rawId), 'Reactivation');
            }
        } catch (error) {
            openPanel('Erreur superadmin', `<p class="inline-error">${error instanceof Error ? error.message : 'Action impossible.'}</p>`);
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
            `).join('') || '<div class="dashboard-empty">Aucune notification pour le moment.</div>'
        );
    });

    profileButton.addEventListener('click', () => {
        openPanel(
            'Profil superadmin',
            `
                <article class="panel-item">
                    <strong>Superadmin FoodLoop</strong>
                    <p>Session PHP superadmin avec gestion Oracle des comptes.</p>
                </article>
            `
        );
    });

    logoutButton.addEventListener('click', () => {
        clearApplicationState();
        window.location.href = pageRoutes.logout;
    });

    panel.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.getAttribute('data-close-superadmin-panel') === 'true') {
            closePanel();
        }
    });

    contentRoot.innerHTML = '<div class="dashboard-empty">Chargement des donnees Oracle...</div>';
    loadSuperadminData()
        .then(() => {
            updateNotifications();
            renderCurrentView();
        })
        .catch((error) => {
            contentRoot.innerHTML = `<div class="dashboard-empty">${error instanceof Error ? error.message : 'Chargement impossible.'}</div>`;
        });
}

if (paymentApp && requireAuthenticatedUser()) {
    const currentUser = requireAuthenticatedUser();
    const fallbackBuyerDashboard = currentUser ? getRouteForRole(currentUser.role) : pageRoutes.login;
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

    function loadPaymentCart() {
        try {
            const raw = window.localStorage.getItem(checkoutStorageKey) || window.localStorage.getItem(legacyCheckoutStorageKey);
            if (!raw) {
                return [];
            }
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return [];
            }

            const normalizedItems = parsed
                .filter((item) => recordBelongsToUser(item, currentUser))
                .map((item) => normalizeCheckoutItem(item, currentUser))
                .filter(Boolean);

            persistStoredArray(checkoutStorageKey, normalizedItems);
            window.localStorage.removeItem(legacyCheckoutStorageKey);
            return normalizedItems;
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
            (async () => {
                try {
                    const paymentResults = await processPaymentsForCart(paymentState.cart, 'onsite', {
                        receiver_name: currentUser && currentUser.email ? currentUser.email : 'Client FoodLoop'
                    });
                    paymentState.method = 'pickup';
                    paymentState.pickupCode = paymentResults.map((item) => item.pickup_code).filter(Boolean).join(' / ');
                    paymentState.step = 'pickup';
                    finalizeSuccessfulCheckout(currentUser, paymentState.cart, returnPage);
                    window.localStorage.removeItem(checkoutStorageKey);
                    window.localStorage.removeItem(checkoutReturnKey);
                    renderPaymentStep();
                } catch (error) {
                    paymentContent.innerHTML = `<div class="payment-panel"><p class="inline-error">${error instanceof Error ? error.message : 'Paiement impossible.'}</p></div>`;
                }
            })();
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
            (async () => {
                const cardFormData = new FormData(form);

                try {
                    const paymentResults = await processPaymentsForCart(paymentState.cart, 'card', {
                        card_holder: String(cardFormData.get('owner') || ''),
                        card_number: String(cardFormData.get('cardNumber') || ''),
                        card_expiry: String(cardFormData.get('cardExpiry') || '12/30'),
                        card_cvc: String(cardFormData.get('cvv') || '')
                    });
                    paymentState.method = 'card';
                    paymentState.transactionId = paymentResults.map((item) => `PAY-${item.payment_id}`).join(' / ') || generateTransactionId();
                    paymentState.step = 'receipt';
                    finalizeSuccessfulCheckout(currentUser, paymentState.cart, returnPage);
                    window.localStorage.removeItem(checkoutStorageKey);
                    window.localStorage.removeItem(checkoutReturnKey);
                    renderPaymentStep();
                } catch (error) {
                    paymentContent.insertAdjacentHTML('beforeend', `<p class="inline-error">${error instanceof Error ? error.message : 'Paiement impossible.'}</p>`);
                }
            })();
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
