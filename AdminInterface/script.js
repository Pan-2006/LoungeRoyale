const pages = document.querySelectorAll(".screen-page");
const SESSION_KEY = "royale_session";
const BOOKINGS_KEY = "royale_crud_bookings";
const CLIENTS_KEY = "royale_crud_clients";
const DB_NAME = "thelounge_royale_db";
let db;

function showPage(pageName) {
  pages.forEach((page) => page.classList.remove("active"));
  document.getElementById(`page-${pageName}`).classList.add("active");
  sessionStorage.setItem("royale_current_page", pageName);
  window.scrollTo(0, 0);
}

document.addEventListener("click", (event) => {
  const target = event.target.closest("[data-page]");
  if (!target) return;

  if (target.classList.contains("nav-logout")) {
    sessionStorage.removeItem(SESSION_KEY);
  }

  showPage(target.dataset.page);
});

function read(key) {
  return JSON.parse(localStorage.getItem(key) || "[]");
}

function write(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
  saveDatabaseCopy(key, value);
}

function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, 1);

    request.onupgradeneeded = (event) => {
      const database = event.target.result;

      if (!database.objectStoreNames.contains("records")) {
        database.createObjectStore("records", { keyPath: "name" });
      }
    };

    request.onsuccess = () => {
      db = request.result;
      resolve(db);
    };

    request.onerror = () => reject(request.error);
  });
}

function saveDatabaseCopy(name, value) {
  if (!db) return;

  const transaction = db.transaction("records", "readwrite");
  transaction.objectStore("records").put({
    name,
    value,
    updatedAt: new Date().toISOString()
  });
}

function seedCrud() {
  if (!localStorage.getItem(BOOKINGS_KEY)) {
    write(BOOKINGS_KEY, [
      {
        id: crypto.randomUUID(),
        name: "Kian",
        service: "Deluxe Royale 1",
        date: "2026-04-29"
      },
      {
        id: crypto.randomUUID(),
        name: "Jisoo",
        service: "Deluxe Royale 2",
        date: "2026-05-01"
      }
    ]);
  }

  if (!localStorage.getItem(CLIENTS_KEY)) {
    write(CLIENTS_KEY, [
      {
        id: crypto.randomUUID(),
        name: "Edith",
        email: "hello@reallygreatsite.com"
      },
      {
        id: crypto.randomUUID(),
        name: "Louisa Mae",
        email: "hello@reallygreatsite.com"
      }
    ]);
  }
}

document.getElementById("registerForm").addEventListener("submit", (event) => {
  event.preventDefault();

  const employee = {
    name: document.getElementById("regName").value,
    age: document.getElementById("regAge").value,
    address: document.getElementById("regAddress").value,
    email: document.getElementById("regEmail").value,
    password: document.getElementById("regPassword").value
  };

  localStorage.setItem("royale_employee", JSON.stringify(employee));

  sessionStorage.setItem(SESSION_KEY, JSON.stringify({
    email: employee.email,
    name: employee.name
  }));

  showPage("dashboard");
});

document.getElementById("signinForm").addEventListener("submit", (event) => {
  event.preventDefault();

  sessionStorage.setItem(SESSION_KEY, JSON.stringify({
    email: document.getElementById("loginEmail").value,
    name: "Admin"
  }));

  showPage("dashboard");
});

document.getElementById("bookingCrud").addEventListener("submit", (event) => {
  event.preventDefault();

  const id = document.getElementById("bookingId").value || crypto.randomUUID();
  const bookings = read(BOOKINGS_KEY);

  const nextBooking = {
    id,
    name: document.getElementById("bookingName").value,
    service: document.getElementById("bookingService").value,
    date: document.getElementById("bookingDate").value
  };

  const updatedBookings = bookings.some((booking) => booking.id === id)
    ? bookings.map((booking) => booking.id === id ? nextBooking : booking)
    : [...bookings, nextBooking];

  write(BOOKINGS_KEY, updatedBookings);

  event.target.reset();
  document.getElementById("bookingId").value = "";
  renderCrud();
});

document.getElementById("clientCrud").addEventListener("submit", (event) => {
  event.preventDefault();

  const id = document.getElementById("clientId").value || crypto.randomUUID();
  const clients = read(CLIENTS_KEY);

  const nextClient = {
    id,
    name: document.getElementById("clientName").value,
    email: document.getElementById("clientEmail").value
  };

  const updatedClients = clients.some((client) => client.id === id)
    ? clients.map((client) => client.id === id ? nextClient : client)
    : [...clients, nextClient];

  write(CLIENTS_KEY, updatedClients);

  event.target.reset();
  document.getElementById("clientId").value = "";
  renderCrud();
});

function renderCrud() {
  const bookings = read(BOOKINGS_KEY);
  const clients = read(CLIENTS_KEY);

  document.getElementById("bookingRows").innerHTML = bookings.map((booking) => `
    <tr>
      <td>${booking.name} - ${booking.service} - ${booking.date}</td>
      <td>
        <button onclick="editBooking('${booking.id}')">Update</button>
        <button onclick="deleteBooking('${booking.id}')">Delete</button>
      </td>
    </tr>
  `).join("");

  document.getElementById("clientRows").innerHTML = clients.map((client) => `
    <tr>
      <td>${client.name} - ${client.email}</td>
      <td>
        <button onclick="editClient('${client.id}')">Update</button>
        <button onclick="deleteClient('${client.id}')">Delete</button>
      </td>
    </tr>
  `).join("");

  localStorage.setItem("royale_soap_xml", buildSoap(bookings, clients));
}

function editBooking(id) {
  const booking = read(BOOKINGS_KEY).find((item) => item.id === id);

  document.getElementById("bookingId").value = booking.id;
  document.getElementById("bookingName").value = booking.name;
  document.getElementById("bookingService").value = booking.service;
  document.getElementById("bookingDate").value = booking.date;
}

function deleteBooking(id) {
  const bookings = read(BOOKINGS_KEY).filter((booking) => booking.id !== id);
  write(BOOKINGS_KEY, bookings);
  renderCrud();
}

function editClient(id) {
  const client = read(CLIENTS_KEY).find((item) => item.id === id);

  document.getElementById("clientId").value = client.id;
  document.getElementById("clientName").value = client.name;
  document.getElementById("clientEmail").value = client.email;
}

function deleteClient(id) {
  const clients = read(CLIENTS_KEY).filter((client) => client.id !== id);
  write(CLIENTS_KEY, clients);
  renderCrud();
}

function buildSoap(bookings, clients) {
  return `<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <session>${sessionStorage.getItem(SESSION_KEY) ? "active" : "guest"}</session>
  </soap:Header>
  <soap:Body>
    <salon>
      <name>The Lounge Royale</name>
      <clients>
${clients.map((client) => `        <client id="${client.id}"><name>${client.name}</name><email>${client.email}</email></client>`).join("\n")}
      </clients>
      <bookings>
${bookings.map((booking) => `        <booking id="${booking.id}"><client>${booking.name}</client><service>${booking.service}</service><date>${booking.date}</date></booking>`).join("\n")}
      </bookings>
    </salon>
  </soap:Body>
</soap:Envelope>`;
}

function makeMapMovable() {
  const map = document.getElementById("movableMap");
  const bar = document.getElementById("mapBar");

  let startX = 0;
  let startY = 0;
  let left = 0;
  let top = 0;
  let moving = false;

  bar.addEventListener("pointerdown", (event) => {
    moving = true;
    startX = event.clientX;
    startY = event.clientY;
    left = map.offsetLeft;
    top = map.offsetTop;
    bar.setPointerCapture(event.pointerId);
  });

  bar.addEventListener("pointermove", (event) => {
    if (!moving) return;

    map.style.left = `${left + event.clientX - startX}px`;
    map.style.top = `${top + event.clientY - startY}px`;
  });

  bar.addEventListener("pointerup", () => {
    moving = false;
  });
}

seedCrud();

openDatabase().then(() => {
  saveDatabaseCopy(BOOKINGS_KEY, read(BOOKINGS_KEY));
  saveDatabaseCopy(CLIENTS_KEY, read(CLIENTS_KEY));
  renderCrud();
  makeMapMovable();
  showPage(sessionStorage.getItem("royale_current_page") || "home");
});