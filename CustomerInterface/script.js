const DB_KEY = 'loungeRoyaleCustomerDB';
const SESSION_KEY = 'loungeRoyaleSession';

const serviceGroups = {
    hand: {
        label: 'Hand Services',
        image: 'assets/references/appointment_HandServices_customer.png',
        services: ['Classic Manicure', 'Gel Manicure', 'Soft Gel Extension', 'Nail Art Add-on']
    },
    foot: {
        label: 'Foot Services',
        image: 'assets/references/appointment_FootServices_customer.png',
        services: ['Classic Pedicure', 'Gel Pedicure', 'Foot Spa', 'Callus Care']
    },
    wax: {
        label: 'Wax Services',
        image: 'assets/references/appointment_WaxServices_customer.png',
        services: ['Eyebrow Wax', 'Underarm Wax', 'Half Leg Wax', 'Full Leg Wax']
    },
    eyelash: {
        label: 'Eyelash Services',
        image: 'assets/references/appointment_EyelashServices_customer.png',
        services: ['Classic Lash Extension', 'Hybrid Lash Extension', 'Volume Lash Extension', 'Lash Lift']
    },
    kiddie: {
        label: 'Kiddie & Other',
        image: 'assets/references/appointment_Kiddie&Other_customer.png',
        services: ['Kiddie Manicure', 'Kiddie Pedicure', 'Polish Change', 'Nail Removal']
    },
    deluxe: {
        label: 'Deluxe Package',
        image: 'assets/references/appointment_DeluxePackage_customer.png',
        services: ['Royale Hand & Foot Package', 'Deluxe Spa Package', 'Lash and Nail Package', 'Full Beauty Package']
    }
};

let activeServiceKey = 'hand';
let toastTimer;

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

function readDB() {
    const fallback = { users: [], appointments: [], xmlLogs: [] };
    try {
        return JSON.parse(localStorage.getItem(DB_KEY)) || fallback;
    } catch (error) {
        return fallback;
    }
}

function writeDB(db) {
    localStorage.setItem(DB_KEY, JSON.stringify(db));
}

function currentSession() {
    try {
        return JSON.parse(sessionStorage.getItem(SESSION_KEY));
    } catch (error) {
        return null;
    }
}

function setSession(email) {
    sessionStorage.setItem(SESSION_KEY, JSON.stringify({ email, startedAt: new Date().toISOString() }));
}

function getCurrentUser() {
    const session = currentSession();
    if (!session) return null;
    return readDB().users.find((user) => user.email === session.email) || null;
}

function showToast(message) {
    const toast = $('#toast');
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
}

function routeTo(page, serviceKey) {
    const targetPage = page === 'service' ? 'servicePage' : page;
    $$('.page').forEach((section) => section.classList.toggle('active-page', section.dataset.page === targetPage));
    $$('.nav-links a').forEach((link) => link.classList.toggle('active', link.dataset.route === page || (page === 'service' && link.dataset.route === 'appointments')));

    if (page === 'profile' && !getCurrentUser()) {
        showToast('Please sign in before viewing your profile.');
        routeTo('auth');
        return;
    }

    if (page === 'service') {
        openService(serviceKey || activeServiceKey);
    }

    updateHeader();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateHeader() {
    const user = getCurrentUser();
    const authButton = $('#authButton');
    authButton.textContent = user ? 'LOG OUT' : 'LOGIN/REGISTER';
    authButton.dataset.route = user ? 'logout' : 'auth';
    authButton.href = user ? '#logout' : '#auth';
}

function closeMobileMenu() {
    $('.menu-toggle').classList.remove('active');
    $('.nav-menu').classList.remove('active');
}

function buildServiceCards() {
    const grid = $('#serviceGrid');
    grid.innerHTML = Object.entries(serviceGroups).map(([key, group]) => `
        <button class="service-card" type="button" data-service-key="${key}">
            <img src="${group.image}" alt="${group.label}">
            <span>${group.label}</span>
        </button>
    `).join('');

    grid.addEventListener('click', (event) => {
        const card = event.target.closest('.service-card');
        if (!card) return;
        if (!getCurrentUser()) {
            showToast('Please sign in first so your booking can be saved.');
            routeTo('auth');
            return;
        }
        routeTo('service', card.dataset.serviceKey);
    });
}

function openService(key) {
    activeServiceKey = serviceGroups[key] ? key : 'hand';
    const group = serviceGroups[activeServiceKey];
    $('#serviceEyebrow').textContent = group.label.toUpperCase();
    $('#serviceTitle').textContent = `Book ${group.label}`;
    $('#serviceSelect').innerHTML = group.services.map((service) => `<option value="${service}">${service}</option>`).join('');
    $('#bookingForm').reset();
    $('#editingId').value = '';
    $('#bookingSubmit').textContent = 'CREATE BOOKING';
    $('#cancelEdit').classList.add('hidden');
    renderBookings();
}

function requireUser() {
    const user = getCurrentUser();
    if (!user) {
        showToast('Please sign in first.');
        routeTo('auth');
        return null;
    }
    return user;
}

function xmlEscape(value) {
    return String(value || '').replace(/[<>&'"]/g, (char) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;' }[char]));
}

function createSoapEnvelope(action, appointment) {
    return `<?xml version="1.0" encoding="UTF-8"?>\n<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:lr="https://loungeroyale.local/customer">\n  <soap:Body>\n    <lr:${action}>\n      <lr:appointment id="${xmlEscape(appointment.id)}">\n        <lr:customerEmail>${xmlEscape(appointment.email)}</lr:customerEmail>\n        <lr:category>${xmlEscape(appointment.category)}</lr:category>\n        <lr:service>${xmlEscape(appointment.service)}</lr:service>\n        <lr:date>${xmlEscape(appointment.date)}</lr:date>\n        <lr:time>${xmlEscape(appointment.time)}</lr:time>\n        <lr:notes>${xmlEscape(appointment.notes)}</lr:notes>\n      </lr:appointment>\n    </lr:${action}>\n  </soap:Body>\n</soap:Envelope>`;
}

function saveXmlLog(action, appointment, db = readDB()) {
    db.xmlLogs.push({ id: crypto.randomUUID(), action, createdAt: new Date().toISOString(), soap: createSoapEnvelope(action, appointment) });
    writeDB(db);
}

function renderBookings() {
    const user = getCurrentUser();
    const list = $('#bookingList');
    const profileList = $('#profileBookings');
    const db = readDB();
    const bookings = user ? db.appointments.filter((item) => item.email === user.email) : [];

    const html = bookings.length ? bookings.map((item) => `
        <article class="booking-item">
            <strong>${item.service}</strong>
            <span>${item.category} • ${item.date} at ${item.time}</span>
            <span>${item.notes || 'No notes added.'}</span>
            <div class="booking-actions">
                <button type="button" data-edit-id="${item.id}">Edit</button>
                <button type="button" data-delete-id="${item.id}">Delete</button>
            </div>
        </article>
    `).join('') : '<p>No appointments yet.</p>';

    if (list) list.innerHTML = html;
    if (profileList) profileList.innerHTML = html;
}

function fillProfile() {
    const user = getCurrentUser();
    if (!user) return;
    const form = $('#profileForm');
    form.name.value = user.name;
    form.email.value = user.email;
    form.phone.value = user.phone;
    $('#profileGreeting').textContent = `${user.name}'s Profile`;
    renderBookings();
}

function handleRegister(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const email = form.email.value.trim().toLowerCase();
    const db = readDB();

    if (db.users.some((user) => user.email === email)) {
        showToast('This email is already registered.');
        return;
    }

    db.users.push({
        id: crypto.randomUUID(),
        name: form.name.value.trim(),
        email,
        phone: form.phone.value.trim(),
        password: form.password.value,
        role: 'customer',
        createdAt: new Date().toISOString()
    });
    writeDB(db);
    setSession(email);
    showToast('Registration complete. You are signed in.');
    form.reset();
    routeTo('profile');
    fillProfile();
}

function handleSignin(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const email = form.email.value.trim().toLowerCase();
    const user = readDB().users.find((item) => item.email === email && item.password === form.password.value);

    if (!user) {
        showToast('Invalid email or password.');
        return;
    }

    setSession(email);
    showToast('Signed in successfully.');
    form.reset();
    routeTo('profile');
    fillProfile();
}

function handleProfileSave(event) {
    event.preventDefault();
    const user = requireUser();
    if (!user) return;

    const db = readDB();
    const record = db.users.find((item) => item.email === user.email);
    record.name = event.currentTarget.name.value.trim();
    record.phone = event.currentTarget.phone.value.trim();
    writeDB(db);
    showToast('Profile updated.');
    fillProfile();
}

function handleBookingSave(event) {
    event.preventDefault();
    const user = requireUser();
    if (!user) return;

    const form = event.currentTarget;
    const db = readDB();
    const editingId = $('#editingId').value;
    const appointment = {
        id: editingId || crypto.randomUUID(),
        email: user.email,
        customerName: user.name,
        category: serviceGroups[activeServiceKey].label,
        service: form.service.value,
        date: form.date.value,
        time: form.time.value,
        notes: form.notes.value.trim(),
        updatedAt: new Date().toISOString()
    };

    if (editingId) {
        const index = db.appointments.findIndex((item) => item.id === editingId && item.email === user.email);
        if (index >= 0) db.appointments[index] = { ...db.appointments[index], ...appointment };
        saveXmlLog('UpdateAppointment', appointment, db);
        showToast('Booking updated.');
    } else {
        appointment.createdAt = new Date().toISOString();
        db.appointments.push(appointment);
        saveXmlLog('CreateAppointment', appointment, db);
        showToast('Booking created.');
    }

    writeDB(db);
    openService(activeServiceKey);
    fillProfile();
}

function handleBookingAction(event) {
    const editButton = event.target.closest('[data-edit-id]');
    const deleteButton = event.target.closest('[data-delete-id]');
    const user = requireUser();
    if (!user || (!editButton && !deleteButton)) return;

    const db = readDB();

    if (editButton) {
        const booking = db.appointments.find((item) => item.id === editButton.dataset.editId && item.email === user.email);
        if (!booking) return;
        const key = Object.keys(serviceGroups).find((groupKey) => serviceGroups[groupKey].label === booking.category) || activeServiceKey;
        routeTo('service', key);
        $('#serviceEyebrow').textContent = booking.category.toUpperCase();
        $('#serviceTitle').textContent = `Edit ${booking.category}`;
        $('#editingId').value = booking.id;
        $('#bookingForm').service.value = booking.service;
        $('#bookingForm').date.value = booking.date;
        $('#bookingForm').time.value = booking.time;
        $('#bookingForm').notes.value = booking.notes;
        $('#bookingSubmit').textContent = 'UPDATE BOOKING';
        $('#cancelEdit').classList.remove('hidden');
        return;
    }

    const id = deleteButton.dataset.deleteId;
    const booking = db.appointments.find((item) => item.id === id && item.email === user.email);
    db.appointments = db.appointments.filter((item) => !(item.id === id && item.email === user.email));
    writeDB(db);
    if (booking) saveXmlLog('DeleteAppointment', booking, db);
    showToast('Booking deleted.');
    renderBookings();
}

function logout() {
    sessionStorage.removeItem(SESSION_KEY);
    updateHeader();
    showToast('Signed out.');
    routeTo('home');
}

function setupMap() {
    const canvas = $('#mapCanvas');
    const pin = $('#mapPin');
    const coords = $('#mapCoords');
    let dragging = null;
    let startX = 0;
    let startY = 0;
    let mapX = 0;
    let mapY = 0;

    function setPin(clientX, clientY) {
        const rect = canvas.getBoundingClientRect();
        const x = Math.max(6, Math.min(94, ((clientX - rect.left) / rect.width) * 100));
        const y = Math.max(12, Math.min(94, ((clientY - rect.top) / rect.height) * 100));
        canvas.style.setProperty('--pin-x', `${x}%`);
        canvas.style.setProperty('--pin-y', `${y}%`);
        coords.textContent = `Pin: ${Math.round(x)}%, ${Math.round(y)}%`;
        localStorage.setItem('loungeRoyaleMap', JSON.stringify({ x, y, mapX, mapY }));
    }

    function setMap(dx, dy) {
        mapX += dx;
        mapY += dy;
        canvas.style.setProperty('--map-x', `${mapX}px`);
        canvas.style.setProperty('--map-y', `${mapY}px`);
    }

    canvas.addEventListener('pointerdown', (event) => {
        dragging = event.target === pin ? 'pin' : 'map';
        startX = event.clientX;
        startY = event.clientY;
        canvas.setPointerCapture(event.pointerId);
        if (dragging === 'pin') setPin(event.clientX, event.clientY);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!dragging) return;
        if (dragging === 'pin') {
            setPin(event.clientX, event.clientY);
        } else {
            setMap(event.clientX - startX, event.clientY - startY);
            startX = event.clientX;
            startY = event.clientY;
        }
    });

    canvas.addEventListener('pointerup', () => { dragging = null; });
    canvas.addEventListener('pointercancel', () => { dragging = null; });

    $('#resetMap').addEventListener('click', () => {
        mapX = 0;
        mapY = 0;
        canvas.style.setProperty('--map-x', '0px');
        canvas.style.setProperty('--map-y', '0px');
        canvas.style.setProperty('--pin-x', '50%');
        canvas.style.setProperty('--pin-y', '50%');
        coords.textContent = 'Pin: 50%, 50%';
        localStorage.removeItem('loungeRoyaleMap');
    });

    const saved = JSON.parse(localStorage.getItem('loungeRoyaleMap') || 'null');
    if (saved) {
        mapX = saved.mapX || 0;
        mapY = saved.mapY || 0;
        canvas.style.setProperty('--map-x', `${mapX}px`);
        canvas.style.setProperty('--map-y', `${mapY}px`);
        canvas.style.setProperty('--pin-x', `${saved.x}%`);
        canvas.style.setProperty('--pin-y', `${saved.y}%`);
        coords.textContent = `Pin: ${Math.round(saved.x)}%, ${Math.round(saved.y)}%`;
    }
}

function init() {
    $('.menu-toggle').addEventListener('click', () => {
        $('.menu-toggle').classList.toggle('active');
        $('.nav-menu').classList.toggle('active');
    });

    document.addEventListener('click', (event) => {
        const routeLink = event.target.closest('[data-route]');
        if (!routeLink) return;
        event.preventDefault();
        closeMobileMenu();
        const route = routeLink.dataset.route;
        if (route === 'logout') {
            logout();
            return;
        }
        routeTo(route);
        if (route === 'profile') fillProfile();
    });

    $$('[data-auth-mode]').forEach((button) => {
        button.addEventListener('click', () => $('#authShell').classList.toggle('register-mode', button.dataset.authMode === 'register'));
    });

    $('#registerForm').addEventListener('submit', handleRegister);
    $('#signinForm').addEventListener('submit', handleSignin);
    $('#profileForm').addEventListener('submit', handleProfileSave);
    $('#logoutButton').addEventListener('click', logout);
    $('#bookingForm').addEventListener('submit', handleBookingSave);
    $('#cancelEdit').addEventListener('click', () => openService(activeServiceKey));
    $('#bookingList').addEventListener('click', handleBookingAction);
    $('#profileBookings').addEventListener('click', handleBookingAction);

    buildServiceCards();
    setupMap();
    updateHeader();
    fillProfile();

    const hash = location.hash.replace('#', '');
    if (hash && hash !== 'logout') routeTo(hash);

    window.addEventListener('scroll', () => {
        if (window.innerWidth > 768) {
            const ribbon = document.querySelector('.gold-ribbon');
            if (ribbon) ribbon.style.transform = `translateY(${window.pageYOffset * 0.15}px)`;
        }
    });
}

document.addEventListener('DOMContentLoaded', init);




