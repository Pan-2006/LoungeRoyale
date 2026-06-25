/* ============================================================
   THE LOUNGE ROYALE — Customer Interface JavaScript
   LocalStorage DB · SessionStorage session · SOAP XML log
   ============================================================ */

'use strict';

// -- Constants -------------------------------------------------
const DB_KEY      = 'loungeRoyaleCanvaFaithfulDB';
const SESSION_KEY = 'loungeRoyaleCanvaFaithfulSession';
const SALON_OPEN  = '09:00';
const SALON_CLOSE = '20:00';

// -- Service groups definition ---------------------------------
const serviceGroups = {
    hand: {
        label:    'Hand Services',
        design:   'assets/references/appointment_HandServices_customer.png',
        services: [
            'Classic Manicure',
            'Orly Breathable Manicure',
            'Coucou Gel Manicure',
            'Orly Gel Manicure',
            'Royale Signature hand Spa with Classic Manicure',
            'Royale Signature hand Spa with Orly Breathable Manicure',
            'Royale Signature hand Spa with Coucou Gel Manicure',
            'Hand Paraffin Wax',
            'Gel Manicure Removal'
        ],
    },
    foot: {
        label:    'Foot Services',
        design:   'assets/references/appointment_FootServices_customer.png',
        services: [
            'Classic Pedicure',
            'Orly Breathable Pedicure',
            'Coucou Gel Pedicure',
            'Royale Signature foot Spa with Classic Pedicure',
            'Royale Signature foot Spa with Orly Breathable Pedicure',
            'Royale Signature foot Spa with Coucou Gel Pedicure',
            'Foot Paraffin Wax',
            'Gel Pedicure Removal'
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
            'Nail Art | Design/Stamp per Nail',
            'Nail Art | Stone per Nail',
        ],
    },
    deluxe: {
        label:    'Royale Deluxe Packages',
        design:   'assets/references/appointment_DeluxePackage_customer.png',
        services: [
            'Deluxe Royale 1',
            'Deluxe Royale 2',
            'Deluxe Royale 3',
            'Deluxe Royale 4',
            'Deluxe Royale 5',
            'Deluxe Royale 6',
            'Deluxe Royale 7',
            'Deluxe Royale 8'
        ],
    },
};

const $  = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

let activeService = 'hand';
let toastTimer;

function readDB() {
    try {
        const db = JSON.parse(localStorage.getItem(DB_KEY));
        if (db && Array.isArray(db.users) && Array.isArray(db.appointments) && Array.isArray(db.soapLogs)) return db;
    } catch (_) { }
    return { users: [], appointments: [], soapLogs: [] };
}

function writeDB(db) { localStorage.setItem(DB_KEY, JSON.stringify(db)); }

function currentUser() {
    const email = sessionStorage.getItem(SESSION_KEY);
    return email ? (readDB().users.find((u) => u.email === email) || null) : null;
}

function todayValue() { return new Date().toISOString().split('T')[0]; }
function isValidEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }

function showToast(message) {
    const el = $('#toast');
    if(!el) return;
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

function updateLoginState() {
    if (currentUser()) {
        document.body.classList.add('is-logged-in');
    } else {
        document.body.classList.remove('is-logged-in');
    }
    updateStatusBanner(); // Triggers the banner update whenever auth state changes
}

function route(page = 'home', serviceKey, updateHash = true) {
    if (page === 'logout') {
        sessionStorage.removeItem(SESSION_KEY);
        updateLoginState();
        showToast('You have been signed out.');
        page = 'home';
    }

    if (['profile', 'appointments', 'service'].includes(page) && !currentUser()) {
        showToast('Please sign in first.');
        page = 'signin';
    }

    if (!document.querySelector(`[data-page="${page}"]`)) page = 'home';
    $$('.screen-page').forEach((s) => s.classList.toggle('active', s.dataset.page === page));

    if (page === 'service')  openService(serviceKey || activeService);
    if (page === 'profile')  renderProfile();

    if (updateHash) history.replaceState(null, '', `#${page}`);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

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
    db.soapLogs.push({ id: crypto.randomUUID(), action, createdAt: new Date().toISOString(), soap: soapEnvelope(action, appt) });
}

function openService(key) {
    activeService = serviceGroups[key] ? key : 'hand';
    const group   = serviceGroups[activeService];
    const img  = $('#serviceDesign');
    img.src    = group.design;
    img.alt    = `${group.label} booking page`;

    const form = $('#bookingForm');
    form.reset();
    form.elements.id.value   = '';
    if(form.elements.date) form.elements.date.min   = todayValue();

    form.elements.service.innerHTML =
        '<option value="" disabled selected>— Choose a service —</option>' +
        group.services.map((s) => `<option value="${s}">${s}</option>`).join('');

    const user = currentUser();
    if (user) {
        form.elements.name.value  = user.name;
        form.elements.email.value = user.email;
    }
    setMessage('#bookingMessage', '');
}

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
    if (time < SALON_OPEN || time > SALON_CLOSE) return 'Please choose a time between 9:00 AM and 8:00 PM.';

    const duplicate = readDB().appointments.some(
        (a) => a.id !== editingId && a.date === date && a.time === time && a.status !== 'Cancelled'
    );
    return duplicate ? 'That date and time slot is already booked.' : '';
}

function handleSignup(event) {
    event.preventDefault();
    const form     = event.currentTarget;
    const name     = form.elements.name.value.trim();
    const email    = form.elements.email.value.trim().toLowerCase();
    const password = form.elements.password.value;
    const db       = readDB();

    let error = '';
    if (!name)                                        error = 'Please enter your name.';
    else if (!isValidEmail(email))                    error = 'Please enter a valid email address.';
    else if (password.length < 6)                     error = 'Password must be at least 6 characters.';
    else if (db.users.some((u) => u.email === email)) error = 'This email is already registered.';

    if (error) { setMessage('#signupMessage', error); return; }

    db.users.push({ id: crypto.randomUUID(), name, email, password, createdAt: new Date().toISOString() });
    writeDB(db);
    sessionStorage.setItem(SESSION_KEY, email);
    updateLoginState();
    form.reset();
    showToast('Account created. Welcome!');
    route('profile');
}

function handleSignin(event) {
    event.preventDefault();
    const form  = event.currentTarget;
    const email = form.elements.email.value.trim().toLowerCase();
    const user  = readDB().users.find((u) => u.email === email && u.password === form.elements.password.value);

    if (!user) { setMessage('#signinMessage', 'Incorrect email or password.'); return; }

    sessionStorage.setItem(SESSION_KEY, email);
    updateLoginState();
    form.reset();
    showToast('Signed in successfully.');
    route('profile');
}

function handleBooking(event) {
    event.preventDefault();
    const user = currentUser();
    if (!user) { route('signin'); return; }

    const form  = event.currentTarget;
    const id    = form.elements.id.value || crypto.randomUUID();
    const error = validateBooking(form, id);
    if (error) { setMessage('#bookingMessage', error); return; }

    const db = readDB();
    const existing = db.appointments.findIndex((a) => a.id === id && a.email === user.email);

    const appt = {
        id, email: user.email, name: form.elements.name.value.trim(),
        category: serviceGroups[activeService].label, service: form.elements.service.value,
        date: form.elements.date.value, time: form.elements.time.value,
        notes: form.elements.notes.value.trim(), technician: form.elements.technician.value || 'To Be Assigned',
        status: 'Confirmed', updatedAt: new Date().toISOString(),
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
    updateStatusBanner(); // Triggers the banner update right after a new booking is saved
    route('profile');
}

function formatDate(value) {
    return value ? new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '';
}

function formatTime(value) {
    if (!value) return '';
    const [h, m] = value.split(':').map(Number);
    const d = new Date();
    d.setHours(h, m || 0, 0, 0);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function appointmentDateTime(appt) { return new Date(`${appt.date}T${appt.time || '00:00'}`); }

// -- Update Status Banner (Header) -----------------------------
function updateStatusBanner() {
    const banner = document.getElementById('userStatusBanner');
    if (!banner) return;

    const user = currentUser();
    if (!user) {
        banner.innerHTML = '';
        return;
    }

    // Find the closest upcoming appointment for this user
    const upcoming = readDB().appointments
        .filter((a) => a.email === user.email && appointmentDateTime(a) >= new Date() && a.status !== 'Cancelled')
        .sort((a, b) => appointmentDateTime(a) - appointmentDateTime(b))[0]; // Get the first one

    // 1. Always show the name
    let html = `<div class="status-name">HI, ${user.name.split(' ')[0]}!</div>`;
    
    // 2. If they have an appointment, add the details below it
    if (upcoming) {
        html += `<div class="status-appt">Next Appt: ${formatDate(upcoming.date)} @ ${formatTime(upcoming.time)}</div>`;
    }

    banner.innerHTML = html;
}

function renderProfile() {
    const user = currentUser();
    if (!user) return;

    // Fill name and email into the cover block
    const nameEl  = $('#profileName');
    const emailEl = $('#profileEmail');
    if (nameEl)  nameEl.textContent  = user.name.toUpperCase();
    if (emailEl) emailEl.textContent = user.email;

    // Fetch all non-cancelled appointments for this user
    const appointments = readDB().appointments
        .filter((a) => a.email === user.email && a.status !== 'Cancelled')
        .sort((a, b) => appointmentDateTime(a) - appointmentDateTime(b));

    const table = $('#appointmentsTable');
    if (!table) return;

    const header =
        '<div class="appt-table-head">'
        + '<span>Date</span><span>Time</span><span>Service</span>'
        + '<span>Technician</span><span>Status</span>'
        + '</div>';

    if (appointments.length === 0) {
        table.innerHTML = header + '<div class="appt-empty">No appointments yet.</div>';
        return;
    }

    table.innerHTML = header + appointments.map((a) =>
        `<div class="appt-row" title="${xmlEscape(a.notes || '')}">`
        + `<span>${formatDate(a.date)}</span>`
        + `<span>${formatTime(a.time)}</span>`
        + `<span>${xmlEscape(a.service)}</span>`
        + `<span>${xmlEscape(a.technician)}</span>`
        + `<span>${xmlEscape(a.status)}</span>`
        + '</div>'
    ).join('');
}

function handleProfileAction(event) {
    const editBtn   = event.target.closest('[data-edit]');
    const deleteBtn = event.target.closest('[data-delete]');
    if (!editBtn && !deleteBtn) return;

    const user = currentUser();
    const db   = readDB();

    if (deleteBtn) {
        if (!confirm('Are you sure you want to cancel this appointment?')) return;
        const appt = db.appointments.find((a) => a.id === deleteBtn.dataset.delete && a.email === user.email);
        if (appt) {
            appt.status = 'Cancelled'; appt.updatedAt = new Date().toISOString();
            logSoap(db, 'CancelAppointment', appt); 
            writeDB(db); 
            showToast('Appointment cancelled.'); 
            renderProfile();
            updateStatusBanner(); // Triggers the banner update if the next appointment was cancelled
        }
        return;
    }

    const appt = db.appointments.find((a) => a.id === editBtn.dataset.edit && a.email === user.email);
    if (!appt) return;

    const key = Object.keys(serviceGroups).find((k) => serviceGroups[k].label === appt.category) || 'hand';
    route('service', key);

    requestAnimationFrame(() => {
        const form = $('#bookingForm');
        form.elements.id.value = appt.id; form.elements.name.value = appt.name;
        form.elements.email.value = appt.email; form.elements.service.value = appt.service;
        form.elements.date.value = appt.date; form.elements.time.value = appt.time;
        form.elements.technician.value = appt.technician; form.elements.notes.value = appt.notes || '';
    });
}

function init() {
    updateLoginState(); // This automatically checks currentUser() and updates the banner

    const dateInput = $('#bookingForm')?.elements?.date;
    if (dateInput) dateInput.min = todayValue();

    document.addEventListener('click', (event) => {
        const target = event.target.closest('button[data-page], a[data-page]');
        if (!target) return;
        event.preventDefault();
        route(target.dataset.page, target.dataset.service);
    });

    $('#signupForm')?.addEventListener('submit', handleSignup);
    $('#signinForm')?.addEventListener('submit', handleSignin);
    $('#bookingForm')?.addEventListener('submit', handleBooking);
    $('#appointmentsTable')?.addEventListener('click', handleProfileAction);

    const initialPage = (location.hash || '').replace('#', '') || 'home';
    route(initialPage, undefined, false);
}

window.addEventListener('hashchange', () => {
    const page = (location.hash || '').replace('#', '') || 'home';
    route(page, undefined, false);
});
document.addEventListener('DOMContentLoaded', init);