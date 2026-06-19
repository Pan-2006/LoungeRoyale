/* ============================================================
   THE LOUNGE ROYALE — Customer Interface JavaScript
   LocalStorage DB · SessionStorage session · SOAP XML log
   ============================================================ */

'use strict';

// ── Constants ─────────────────────────────────────────────────
const DB_KEY      = 'loungeRoyaleCanvaFaithfulDB';
const SESSION_KEY = 'loungeRoyaleCanvaFaithfulSession';
const SALON_OPEN  = '09:00';
const SALON_CLOSE = '20:00';

// ── Service groups definition ─────────────────────────────────
const serviceGroups = {
    hand: {
        label:    'Hand Services',
        design:   'assets/references/appointment_HandServices_customer.png',
        services: [
            'Classic Manicure',
            'Only Breathable Manicure',
            'Overlay Gel Manicure',
            'Royal Signature Hand Spa',
            'Hand Paraffin Wax',
        ],
    },
    foot: {
        label:    'Foot Services',
        design:   'assets/references/appointment_FootServices_customer.png',
        services: [
            'Classic Pedicure',
            'Only Breathable Pedicure',
            'Classic Gel Pedicure',
            'Royal Signature Foot Spa',
            'Foot Paraffin Wax',
        ],
    },
    wax: {
        label:    'Wax Services',
        design:   'assets/references/appointment_WaxServices_customer.png',
        services: [
            'Eyebrow Wax',
            'Underarm Wax',
            'Arm Wax',
            'Half Leg Wax',
            'Full Leg Wax',
            'Upper/Lower Lip Wax',
            'Brazilian Wax',
        ],
    },
    eyelash: {
        label:    'Eyelash Services',
        design:   'assets/references/appointment_EyelashServices_customer.png',
        services: [
            'Eyelash Perming',
            'Eyelash Extension',
            'Eyelash Retouch',
            'Eyelash Removal',
        ],
    },
    kiddie: {
        label:    'Kiddie and Other Services',
        design:   'assets/references/appointment_Kiddie&Other_customer.png',
        services: [
            'Kiddie Manicure',
            'Kiddie Pedicure',
            'Kiddie Hand Spa with Manicure',
            'Kiddie Foot Spa with Pedicure',
            'Ear Candling',
            'Nail Art',
        ],
    },
    deluxe: {
        label:    'Royale Deluxe Packages',
        design:   'assets/references/appointment_DeluxePackage_customer.png',
        services: [
            'Classic Royale 1',
            'Only Breathable Royale',
            'Deluxe Royale 1',
            'Deluxe Royale 4',
            'Deluxe Royale 5',
        ],
    },
};

// ── DOM helpers ───────────────────────────────────────────────
const $  = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

let activeService = 'hand';
let toastTimer;

// ── LocalStorage DB helpers ───────────────────────────────────
function readDB() {
    try {
        const db = JSON.parse(localStorage.getItem(DB_KEY));
        if (db && Array.isArray(db.users) && Array.isArray(db.appointments) && Array.isArray(db.soapLogs)) {
            return db;
        }
    } catch (_) { /* ignore */ }
    return { users: [], appointments: [], soapLogs: [] };
}

function writeDB(db) {
    localStorage.setItem(DB_KEY, JSON.stringify(db));
}

// ── Session helpers ───────────────────────────────────────────
function currentUser() {
    const email = sessionStorage.getItem(SESSION_KEY);
    return email ? (readDB().users.find((u) => u.email === email) || null) : null;
}

// ── Utilities ─────────────────────────────────────────────────
function todayValue() {
    return new Date().toISOString().split('T')[0];
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ── Toast ─────────────────────────────────────────────────────
function showToast(message) {
    const el = $('#toast');
    el.textContent = message;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
}

function setMessage(id, message) {
    const el = $(id);
    if (!el) return;
    el.textContent = message;
    el.classList.toggle('show', Boolean(message));
}

// ── Router ────────────────────────────────────────────────────
function route(page = 'home', serviceKey, updateHash = true) {
    // Handle logout
    if (page === 'logout') {
        sessionStorage.removeItem(SESSION_KEY);
        showToast('You have been signed out.');
        page = 'home';
    }

    // Guard protected pages
    if (['profile', 'appointments', 'service'].includes(page) && !currentUser()) {
        showToast('Please sign in first.');
        page = 'signin';
    }

    // Fall back to home if page doesn't exist
    if (!document.querySelector(`[data-page="${page}"]`)) page = 'home';

    // Switch visible section
    $$('.screen-page').forEach((s) => s.classList.toggle('active', s.dataset.page === page));

    // Page-specific setup
    if (page === 'service')  openService(serviceKey || activeService);
    if (page === 'profile')  renderProfile();

    // Update URL hash
    if (updateHash) history.replaceState(null, '', `#${page}`);

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── SOAP XML helpers ──────────────────────────────────────────
function xmlEscape(value) {
    return String(value ?? '').replace(/[<>&'"]/g, (ch) => ({
        '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;',
    }[ch]));
}

function soapEnvelope(action, appt) {
    return (
        `<?xml version="1.0" encoding="UTF-8"?>\n` +
        `<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:lr="https://loungeroyale.local/customer">\n` +
        `  <soap:Body>\n` +
        `    <lr:${action}>\n` +
        `      <lr:appointment id="${xmlEscape(appt.id)}">\n` +
        `        <lr:customerEmail>${xmlEscape(appt.email)}</lr:customerEmail>\n` +
        `        <lr:customerName>${xmlEscape(appt.name)}</lr:customerName>\n` +
        `        <lr:category>${xmlEscape(appt.category)}</lr:category>\n` +
        `        <lr:service>${xmlEscape(appt.service)}</lr:service>\n` +
        `        <lr:date>${xmlEscape(appt.date)}</lr:date>\n` +
        `        <lr:time>${xmlEscape(appt.time)}</lr:time>\n` +
        `        <lr:notes>${xmlEscape(appt.notes)}</lr:notes>\n` +
        `        <lr:technician>${xmlEscape(appt.technician)}</lr:technician>\n` +
        `        <lr:status>${xmlEscape(appt.status)}</lr:status>\n` +
        `      </lr:appointment>\n` +
        `    </lr:${action}>\n` +
        `  </soap:Body>\n` +
        `</soap:Envelope>`
    );
}

function logSoap(db, action, appt) {
    db.soapLogs.push({
        id:        crypto.randomUUID(),
        action,
        createdAt: new Date().toISOString(),
        soap:      soapEnvelope(action, appt),
    });
}

// ── Open service booking page ─────────────────────────────────
function openService(key) {
    activeService = serviceGroups[key] ? key : 'hand';
    const group   = serviceGroups[activeService];

    // Swap Canva reference image
    const img  = $('#serviceDesign');
    img.src    = group.design;
    img.alt    = `${group.label} booking page`;

    // Reset form
    const form = $('#bookingForm');
    form.reset();
    form.elements.id.value   = '';
    form.elements.date.min   = todayValue();

    // Populate service select
    form.elements.service.innerHTML =
        '<option value="">— Choose a service —</option>' +
        group.services.map((s) => `<option value="${s}">${s}</option>`).join('');

    // Pre-fill name/email from session
    const user = currentUser();
    if (user) {
        form.elements.name.value  = user.name;
        form.elements.email.value = user.email;
    }

    setMessage('#bookingMessage', '');
}

// ── Booking validation ────────────────────────────────────────
function validateBooking(form, editingId) {
    const email = form.elements.email.value.trim().toLowerCase();
    const date  = form.elements.date.value;
    const time  = form.elements.time.value;

    if (!form.elements.name.value.trim())     return 'Please enter your name.';
    if (!isValidEmail(email))                 return 'Please enter a valid email address.';
    if (!form.elements.service.value)         return 'Please choose a service.';
    if (!date)                                return 'Please select a date.';
    if (date < todayValue())                  return 'Please choose today or a future date.';
    if (!time)                                return 'Please select a time.';
    if (time < SALON_OPEN || time > SALON_CLOSE)
        return 'Please choose a time between 9:00 AM and 8:00 PM.';

    const duplicate = readDB().appointments.some(
        (a) => a.id !== editingId && a.date === date && a.time === time && a.status !== 'Cancelled'
    );
    return duplicate ? 'That date and time slot is already booked.' : '';
}

// ── Sign-up handler ───────────────────────────────────────────
function handleSignup(event) {
    event.preventDefault();
    const form            = event.currentTarget;
    const name            = form.elements.name.value.trim();
    const email           = form.elements.email.value.trim().toLowerCase();
    const password        = form.elements.password.value;
    const confirmPassword = form.elements.confirmPassword.value;
    const db              = readDB();

    let error = '';
    if (!name)                                   error = 'Please enter your name.';
    else if (!isValidEmail(email))               error = 'Please enter a valid email address.';
    else if (password.length < 6)                error = 'Password must be at least 6 characters.';
    else if (password !== confirmPassword)       error = 'Passwords do not match.';
    else if (db.users.some((u) => u.email === email)) error = 'This email is already registered.';

    if (error) { setMessage('#signupMessage', error); return; }

    db.users.push({
        id:        crypto.randomUUID(),
        name,
        email,
        password,
        createdAt: new Date().toISOString(),
    });
    writeDB(db);
    sessionStorage.setItem(SESSION_KEY, email);
    form.reset();
    showToast('Account created. Welcome!');
    route('profile');
}

// ── Sign-in handler ───────────────────────────────────────────
function handleSignin(event) {
    event.preventDefault();
    const form  = event.currentTarget;
    const email = form.elements.email.value.trim().toLowerCase();
    const user  = readDB().users.find(
        (u) => u.email === email && u.password === form.elements.password.value
    );

    if (!user) { setMessage('#signinMessage', 'Incorrect email or password.'); return; }

    sessionStorage.setItem(SESSION_KEY, email);
    form.reset();
    showToast('Signed in successfully.');
    route('profile');
}

// ── Booking submit handler ────────────────────────────────────
function handleBooking(event) {
    event.preventDefault();
    const user = currentUser();
    if (!user) { route('signin'); return; }

    const form  = event.currentTarget;
    const id    = form.elements.id.value || crypto.randomUUID();
    const error = validateBooking(form, id);
    if (error) { setMessage('#bookingMessage', error); return; }

    const db    = readDB();
    const existing = db.appointments.findIndex((a) => a.id === id && a.email === user.email);

    const appt = {
        id,
        email:      user.email,
        name:       form.elements.name.value.trim(),
        category:   serviceGroups[activeService].label,
        service:    form.elements.service.value,
        date:       form.elements.date.value,
        time:       form.elements.time.value,
        notes:      form.elements.notes.value.trim(),
        technician: form.elements.technician.value || 'To Be Assigned',
        status:     'Confirmed',
        updatedAt:  new Date().toISOString(),
    };

    if (existing >= 0) {
        db.appointments[existing] = { ...db.appointments[existing], ...appt };
        logSoap(db, 'UpdateAppointment', appt);
        showToast('Appointment updated.');
    } else {
        appt.createdAt = new Date().toISOString();
        db.appointments.push(appt);
        logSoap(db, 'CreateAppointment', appt);
        showToast('Booking confirmed!');
    }

    writeDB(db);
    route('profile');
}

// ── Formatting helpers ────────────────────────────────────────
function formatDate(value) {
    return value
        ? new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
        : '';
}

function formatTime(value) {
    if (!value) return '';
    const [h, m] = value.split(':').map(Number);
    const d = new Date();
    d.setHours(h, m || 0, 0, 0);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function appointmentDateTime(appt) {
    return new Date(`${appt.date}T${appt.time || '00:00'}`);
}

// ── Profile render ────────────────────────────────────────────
function renderProfile() {
    const user = currentUser();
    if (!user) return;

    $('#profileName').textContent  = user.name  || 'CUSTOMER';
    $('#profileEmail').textContent = user.email || '';

    const upcoming = readDB().appointments
        .filter((a) => a.email === user.email)
        .sort((a, b) => appointmentDateTime(a) - appointmentDateTime(b))
        .filter((a) => appointmentDateTime(a) >= new Date() && a.status !== 'Cancelled')
        .slice(0, 5);

    $('#appointmentsTable').innerHTML = upcoming.length
        ? upcoming.map((a) =>
            `<div class="appointment-row" title="${xmlEscape(a.notes || 'No notes')}">` +
                `<span>${formatDate(a.date)}</span>` +
                `<span>${formatTime(a.time)}</span>` +
                `<span>${xmlEscape(a.service)}</span>` +
                `<span>${xmlEscape(a.technician)}</span>` +
                `<span>${xmlEscape(a.status)}</span>` +
                `<span>` +
                    `<button type="button" data-edit="${a.id}">Edit</button> ` +
                    `<button type="button" data-delete="${a.id}">Cancel</button>` +
                `</span>` +
            `</div>`
        ).join('')
        : '<div class="appointment-row"><span colspan="6">No upcoming appointments.</span></div>';
}

// ── Profile actions (edit / cancel) ──────────────────────────
function handleProfileAction(event) {
    const editBtn   = event.target.closest('[data-edit]');
    const deleteBtn = event.target.closest('[data-delete]');
    if (!editBtn && !deleteBtn) return;

    const user = currentUser();
    const db   = readDB();

    if (deleteBtn) {
        if (!confirm('Are you sure you want to cancel this appointment?')) return;
        const appt = db.appointments.find(
            (a) => a.id === deleteBtn.dataset.delete && a.email === user.email
        );
        if (appt) {
            appt.status    = 'Cancelled';
            appt.updatedAt = new Date().toISOString();
            logSoap(db, 'CancelAppointment', appt);
            writeDB(db);
            showToast('Appointment cancelled.');
            renderProfile();
        }
        return;
    }

    // Edit: pre-fill booking form
    const appt = db.appointments.find(
        (a) => a.id === editBtn.dataset.edit && a.email === user.email
    );
    if (!appt) return;

    const key = Object.keys(serviceGroups).find(
        (k) => serviceGroups[k].label === appt.category
    ) || 'hand';

    route('service', key);

    // Wait for openService to populate the select, then fill values
    requestAnimationFrame(() => {
        const form = $('#bookingForm');
        form.elements.id.value         = appt.id;
        form.elements.name.value       = appt.name;
        form.elements.email.value      = appt.email;
        form.elements.service.value    = appt.service;
        form.elements.date.value       = appt.date;
        form.elements.time.value       = appt.time;
        form.elements.technician.value = appt.technician;
        form.elements.notes.value      = appt.notes || '';
    });
}

// ── Interactive map (About page) ──────────────────────────────
function setupMap() {
    const map = $('#miniMap');
    const pin = $('#mapPin');
    if (!map || !pin) return;

    let mode = '', lastX = 0, lastY = 0, mapX = 0, mapY = 0;

    // Restore saved state
    try {
        const saved = JSON.parse(localStorage.getItem('loungeRoyaleMapState'));
        if (saved) {
            if (saved.x) map.style.setProperty('--pin-x', `${saved.x}%`);
            if (saved.y) map.style.setProperty('--pin-y', `${saved.y}%`);
            if (saved.mapX) { mapX = saved.mapX; map.style.setProperty('--map-x', `${mapX}px`); }
            if (saved.mapY) { mapY = saved.mapY; map.style.setProperty('--map-y', `${mapY}px`); }
        }
    } catch (_) { /* ignore */ }

    function movePin(clientX, clientY) {
        const rect = map.getBoundingClientRect();
        const x    = Math.max(5,  Math.min(95, ((clientX - rect.left)  / rect.width)  * 100));
        const y    = Math.max(12, Math.min(95, ((clientY - rect.top)   / rect.height) * 100));
        map.style.setProperty('--pin-x', `${x}%`);
        map.style.setProperty('--pin-y', `${y}%`);
        try {
            localStorage.setItem('loungeRoyaleMapState', JSON.stringify({ x, y, mapX, mapY }));
        } catch (_) { /* ignore */ }
    }

    map.addEventListener('pointerdown', (e) => {
        mode  = e.target === pin ? 'pin' : 'map';
        lastX = e.clientX;
        lastY = e.clientY;
        map.setPointerCapture(e.pointerId);
        if (mode === 'pin') movePin(e.clientX, e.clientY);
    });

    map.addEventListener('pointermove', (e) => {
        if (!mode) return;
        if (mode === 'pin') { movePin(e.clientX, e.clientY); return; }
        mapX += e.clientX - lastX;
        mapY += e.clientY - lastY;
        lastX  = e.clientX;
        lastY  = e.clientY;
        map.style.setProperty('--map-x', `${mapX}px`);
        map.style.setProperty('--map-y', `${mapY}px`);
    });

    map.addEventListener('pointerup',     () => { mode = ''; });
    map.addEventListener('pointercancel', () => { mode = ''; });
}

// ── Initialise ────────────────────────────────────────────────
function init() {
    setupMap();

    // Set date min on booking form
    const dateInput = $('#bookingForm').elements.date;
    if (dateInput) dateInput.min = todayValue();

    // Global click delegation for hotspots
    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-page]');
        if (!target) return;
        event.preventDefault();
        route(target.dataset.page, target.dataset.service);
    });

    // Form submissions
    $('#signupForm').addEventListener('submit', handleSignup);
    $('#signinForm').addEventListener('submit', handleSignin);
    $('#bookingForm').addEventListener('submit', handleBooking);

    // Profile table actions (edit / cancel)
    $('#appointmentsTable').addEventListener('click', handleProfileAction);

    // Read initial page from URL hash
    const initialPage = (location.hash || '').replace('#', '') || 'home';
    route(initialPage, undefined, false);
}

document.addEventListener('DOMContentLoaded', init);