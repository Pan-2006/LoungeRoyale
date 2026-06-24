const pages = document.querySelectorAll(".screen-page");
const USERS = "royale_users";
const BOOKINGS = "royale_bookings";
const CLIENTS = "royale_clients";
const SESSION = "royale_session";
const DB_NAME = "thelounge_royale_db";

const SERVICES = [
  "Classic Manicure",
  "Orly Breathable Manicure",
  "Coucou Gel Manicure",
  "Orly Gel Manicure",
  "Royale Signature Hand Spa with Classic Manicure",
  "Royale Signature Hand Spa with Orly Breathable Manicure",
  "Royale Signature Hand Spa with Coucou Gel Manicure",
  "Royale Signature Hand Spa with Orly Gel Polish",
  "Hand Paraffin Wax",
  "Gel Manicure Removal",
  "Gel Extension with Classic Polish",
  "Gel Extension with Orly Breathable Polish",
  "Gel Extension with Coucou Gel Polish",
  "Gel Extension with Orly Gel Polish",
  "Nail Extension Removal",
  "Nail Extension Removal (for non-Lounge Royale Polish)",
  "Classic Pedicure",
  "Orly Breathable Pedicure",
  "Coucou Gel Pedicure",
  "Orly Gel Pedicure",
  "Royale Signature Foot Spa with Classic Pedicure",
  "Royale Signature Foot Spa with Orly Breathable Pedicure",
  "Royale Signature Foot Spa with Coucou Gel Pedicure",
  "Royale Signature Foot Spa with Orly Gel Pedicure",
  "Gel Manicure Removal (for non-Lounge Royale Polish)",
  "Kiddie Manicure",
  "Kiddie Pedicure",
  "Kiddie Hand Spa with Manicure",
  "Kiddie Foot Spa with Pedicure",
  "Ear Candling",
  "Nail Art - Design/Stamp per nail",
  "Nail Art - Stone per nail",
  "Deluxe Royale 1",
  "Deluxe Royale 2",
  "Deluxe Royale 3",
  "Deluxe Royale 4",
  "Deluxe Royale 5",
  "Deluxe Royale 6",
  "Deluxe Royale 7",
  "Deluxe Royale 8",
  "Eyebrow Wax",
  "Underarm Wax",
  "Arm Wax",
  "Half Leg Wax",
  "Full Leg Wax",
  "Upper/Lower Lip Wax",
  "Brazilian Wax"
];

let db;

function read(key) {
  return JSON.parse(localStorage.getItem(key) || "[]");
}

function write(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
  saveDb(key, value);
}

function user() {
  return JSON.parse(sessionStorage.getItem(SESSION) || "null");
}

function esc(value) {
  return String(value ?? "").replace(/[&<>"']/g, function (match) {
    return {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;"
    }[match];
  });
}

function showPage(name) {
  pages.forEach(function (page) {
    page.classList.remove("active");
  });

  const selectedPage = document.getElementById("page-" + name);

  if (selectedPage) {
    selectedPage.classList.add("active");
    sessionStorage.setItem("royale_current_page", name);
    window.scrollTo(0, 0);
  }
}

function toast(message) {
  const toastBox = document.getElementById("toast");
  if (!toastBox) return;

  toastBox.textContent = message;
  toastBox.classList.add("show");

  setTimeout(function () {
    toastBox.classList.remove("show");
  }, 2200);
}

document.addEventListener("click", function (event) {
  const target = event.target.closest("[data-page]");
  if (!target) return;

  if (target.classList.contains("nav-logout")) {
    sessionStorage.removeItem(SESSION);
    renderAll();
    showPage("home");
    toast("Logged out.");
    return;
  }

  showPage(target.dataset.page);
});

function openDb() {
  return new Promise(function (resolve) {
    const request = indexedDB.open(DB_NAME, 1);

    request.onupgradeneeded = function (event) {
      const database = event.target.result;

      if (!database.objectStoreNames.contains("records")) {
        database.createObjectStore("records", { keyPath: "name" });
      }
    };

    request.onsuccess = function () {
      db = request.result;
      resolve();
    };

    request.onerror = function () {
      resolve();
    };
  });
}

function saveDb(name, value) {
  if (!db) return;

  db.transaction("records", "readwrite")
    .objectStore("records")
    .put({
      name: name,
      value: value,
      updatedAt: new Date().toISOString()
    });
}

function seed() {
  if (!localStorage.getItem(USERS)) {
    write(USERS, [
      {
        id: "admin-1",
        name: "Regina Lourine Cruz",
        age: "19",
        address: "Pasig City",
        email: "admin@royale.com",
        password: "admin123!",
        phone: "+639 913 456 7890"
      }
    ]);
  }

  if (!localStorage.getItem(BOOKINGS)) {
    write(BOOKINGS, [
      {
        id: crypto.randomUUID(),
        date: "2026-04-29",
        time: "15:00",
        name: "Kian",
        email: "hello@reallygreatsite.com",
        service: "Classic Manicure",
        status: "Completed"
      },
      {
        id: crypto.randomUUID(),
        date: "2026-05-01",
        time: "17:00",
        name: "Jisoo",
        email: "hello@reallygreatsite.com",
        service: "Deluxe Royale 2",
        status: "Pending"
      }
    ]);
  }

  if (!localStorage.getItem(CLIENTS)) {
    write(CLIENTS, [
      {
        id: crypto.randomUUID(),
        name: "Edith",
        email: "edith@example.com",
        appointments: 3
      },
      {
        id: crypto.randomUUID(),
        name: "Louisa Mae",
        email: "louisa@example.com",
        appointments: 2
      },
      {
        id: crypto.randomUUID(),
        name: "Jae Sun",
        email: "jaesun@example.com",
        appointments: 1
      }
    ]);
  }

  migrateData();
}

function migrateData() {
  write(
    BOOKINGS,
    read(BOOKINGS).map(function (booking) {
      if (booking.status === "Not Yet") {
        return { ...booking, status: "Cancelled" };
      }

      return booking;
    })
  );

  write(
    USERS,
    read(USERS).map(function (account) {
      return {
        ...account,
        phone:
          account.phone && !account.phone.includes("XXX")
            ? account.phone
            : "+639 913 456 7890"
      };
    })
  );
}

function renderServiceOptions(selected) {
  const bookingService = document.getElementById("bookingService");
  if (!bookingService) return;

  bookingService.innerHTML = SERVICES.map(function (service) {
    return `<option value="${esc(service)}" ${
      service === selected ? "selected" : ""
    }>${esc(service)}</option>`;
  }).join("");
}

const registerForm = document.getElementById("registerForm");
if (registerForm) {
  registerForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("regEmail").value.trim().toLowerCase();
    const age = Number(document.getElementById("regAge").value);
    const users = read(USERS);

    if (age < 18) {
      toast("Employee must be 18 or older.");
      return;
    }

    if (!validEmail(email)) {
      toast("Please enter a valid email.");
      return;
    }

    if (users.some(function (account) { return account.email === email; })) {
      toast("Email already exists. Please sign in.");
      showPage("signin");
      return;
    }

    const account = {
      id: crypto.randomUUID(),
      name: document.getElementById("regName").value.trim(),
      age: age,
      address: document.getElementById("regAddress").value.trim(),
      email: email,
      password: document.getElementById("regPassword").value,
      phone: "+639 913 456 7890"
    };

    write(USERS, [...users, account]);
    sessionStorage.setItem(SESSION, JSON.stringify(account));

    event.target.reset();
    renderAll();
    showPage("dashboard");
    toast("Account created.");
  });
}

const signinForm = document.getElementById("signinForm");
if (signinForm) {
  signinForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("loginEmail").value.trim().toLowerCase();
    const password = document.getElementById("loginPassword").value;

    const account = read(USERS).find(function (item) {
      return item.email === email && item.password === password;
    });

    if (!account) {
      toast("Invalid email or password.");
      return;
    }

    sessionStorage.setItem(SESSION, JSON.stringify(account));

    event.target.reset();
    renderAll();
    showPage("dashboard");
    toast("Signed in.");
  });
}

const bookingForm = document.getElementById("bookingForm");
if (bookingForm) {
  bookingForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const rows = read(BOOKINGS);
    const id = document.getElementById("bookingId").value || crypto.randomUUID();

    const item = {
      id: id,
      date: document.getElementById("bookingDate").value,
      time: document.getElementById("bookingTime").value,
      name: document.getElementById("bookingName").value.trim(),
      email: document.getElementById("bookingEmail").value.trim().toLowerCase(),
      service: document.getElementById("bookingService").value,
      status: document.getElementById("bookingStatus").value
    };

    write(
      BOOKINGS,
      rows.some(function (booking) { return booking.id === id; })
        ? rows.map(function (booking) { return booking.id === id ? item : booking; })
        : [...rows, item]
    );

    event.target.reset();
    document.getElementById("bookingId").value = "";
    renderAll();
    toast("Booking saved.");
  });
}

const clientForm = document.getElementById("clientForm");
if (clientForm) {
  clientForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const rows = read(CLIENTS);
    const id = document.getElementById("clientId").value || crypto.randomUUID();
    const email = document.getElementById("clientEmail").value.trim().toLowerCase();
    const appointments = Number(document.getElementById("clientAppointments").value);

    if (!validEmail(email)) {
      toast("Please enter a valid client email.");
      return;
    }

    if (!Number.isInteger(appointments) || appointments < 0) {
      toast("Appointments must be 0 or higher.");
      return;
    }

    const item = {
      id: id,
      name: document.getElementById("clientName").value.trim(),
      email: email,
      appointments: appointments
    };

    write(
      CLIENTS,
      rows.some(function (client) { return client.id === id; })
        ? rows.map(function (client) { return client.id === id ? item : client; })
        : [...rows, item]
    );

    event.target.reset();
    document.getElementById("clientId").value = "";
    renderAll();
    toast("Client saved.");
  });
}

const profileForm = document.getElementById("profileForm");
if (profileForm) {
  profileForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const current = user() || read(USERS)[0];
    if (!current) return;

    const updated = {
      ...current,
      name: document.getElementById("editProfileName").value.trim(),
      email: document.getElementById("editProfileEmail").value.trim().toLowerCase(),
      phone: document.getElementById("editProfilePhone").value.trim()
    };

    write(
      USERS,
      read(USERS).map(function (account) {
        return account.id === updated.id ? updated : account;
      })
    );

    sessionStorage.setItem(SESSION, JSON.stringify(updated));
    renderAll();
    toast("Profile saved.");
  });
}

const bookingFilter = document.getElementById("bookingFilter");
if (bookingFilter) {
  bookingFilter.addEventListener("input", renderBookings);
}

function renderAll() {
  renderServiceOptions();
  renderDashboard();
  renderBookings();
  renderClients();
  renderStaff();
  renderProfile();

  localStorage.setItem("royale_soap_xml", buildSoap());
}

function renderDashboard() {
  const bookings = read(BOOKINGS);
  const clients = read(CLIENTS);
  const users = read(USERS);

  setText("statBookings", bookings.length);
  setText("statClients", clients.length);
  setText("statStaff", users.length);
  setText("statPending", bookings.filter(function (b) { return b.status === "Pending"; }).length);
  setText("statCompleted", bookings.filter(function (b) { return b.status === "Completed"; }).length);
  setText("statCancelled", bookings.filter(function (b) { return b.status === "Cancelled"; }).length);
  setText("statSales", "P" + (bookings.filter(function (b) { return b.status === "Completed"; }).length * 4000).toLocaleString());

  const todayRows = document.getElementById("todayRows");
  if (!todayRows) return;

  todayRows.innerHTML =
    bookings.slice(0, 4).map(function (booking) {
      return `
        <tr>
          <td>${esc(booking.name)}</td>
          <td>${fmtTime(booking.time)}</td>
          <td>${esc(booking.service)}</td>
          <td class="${statusClass(booking.status)}">${esc(booking.status)}</td>
        </tr>
      `;
    }).join("") || `<tr><td colspan="4">No appointments yet.</td></tr>`;
}

function renderBookings() {
  const bookingRows = document.getElementById("bookingRows");
  const filter = document.getElementById("bookingFilter");
  if (!bookingRows) return;

  const query = filter ? filter.value.toLowerCase() : "";

  const rows = read(BOOKINGS).filter(function (booking) {
    return Object.values(booking).join(" ").toLowerCase().includes(query);
  });

  bookingRows.innerHTML =
    rows.map(function (booking) {
      return `
        <tr>
          <td>${fmtDate(booking.date)}</td>
          <td>${fmtTime(booking.time)}</td>
          <td>${esc(booking.name)}</td>
          <td><u>${esc(booking.email)}</u></td>
          <td>${esc(booking.service)}</td>
          <td>
            <select onchange="changeStatus('${booking.id}', this.value)">
              <option ${sel(booking.status, "Completed")}>Completed</option>
              <option ${sel(booking.status, "Pending")}>Pending</option>
              <option ${sel(booking.status, "Cancelled")}>Cancelled</option>
            </select>
          </td>
          <td>
            <div class="row-actions">
              <button onclick="editBooking('${booking.id}')">Update</button>
              <button onclick="deleteBooking('${booking.id}')">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join("") || `<tr><td colspan="7">No bookings found.</td></tr>`;
}

function editBooking(id) {
  const booking = read(BOOKINGS).find(function (item) {
    return item.id === id;
  });

  if (!booking) return;

  document.getElementById("bookingId").value = booking.id;
  document.getElementById("bookingDate").value = booking.date;
  document.getElementById("bookingTime").value = booking.time;
  document.getElementById("bookingName").value = booking.name;
  document.getElementById("bookingEmail").value = booking.email;
  renderServiceOptions(booking.service);
  document.getElementById("bookingStatus").value = booking.status;

  showPage("bookings");
}

function deleteBooking(id) {
  write(
    BOOKINGS,
    read(BOOKINGS).filter(function (booking) {
      return booking.id !== id;
    })
  );

  renderAll();
}

function changeStatus(id, status) {
  write(
    BOOKINGS,
    read(BOOKINGS).map(function (booking) {
      return booking.id === id ? { ...booking, status: status } : booking;
    })
  );

  renderAll();
}

function renderClients() {
  const clientRows = document.getElementById("clientRows");
  if (!clientRows) return;

  clientRows.innerHTML =
    read(CLIENTS).map(function (client) {
      return `
        <tr>
          <td>${esc(client.name)}</td>
          <td><u>${esc(client.email)}</u></td>
          <td><u>${client.appointments}</u></td>
          <td>
            <div class="row-actions">
              <button onclick="editClient('${client.id}')">Update</button>
              <button onclick="deleteClient('${client.id}')">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join("") || `<tr><td colspan="4">No clients yet.</td></tr>`;
}

function editClient(id) {
  const client = read(CLIENTS).find(function (item) {
    return item.id === id;
  });

  if (!client) return;

  document.getElementById("clientId").value = client.id;
  document.getElementById("clientName").value = client.name;
  document.getElementById("clientEmail").value = client.email;
  document.getElementById("clientAppointments").value = client.appointments;

  showPage("clients");
}

function deleteClient(id) {
  write(
    CLIENTS,
    read(CLIENTS).filter(function (client) {
      return client.id !== id;
    })
  );

  renderAll();
}

function renderStaff() {
  const staffCards = document.getElementById("staffCards");
  const scheduleRows = document.getElementById("scheduleRows");

  if (!staffCards || !scheduleRows) return;

  const current = user();
  const allUsers = read(USERS);
  const users = current
    ? [current, ...allUsers.filter(function (account) { return account.id !== current.id; })]
    : allUsers;

  staffCards.innerHTML =
    users.map(function (account) {
      return `
        <article>
          <h2>${esc(account.name)}</h2>
          <p>
            Age: ${esc(account.age || "")}<br>
            Address: ${esc(account.address || "")}<br>
            Contact No.: ${esc(account.phone || "+639 913 456 7890")}<br>
            Email:<br>${esc(account.email)}
          </p>
        </article>
      `;
    }).join("") || `<article><h2>No staff yet</h2><p>Register an employee account to add staff.</p></article>`;

  const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
  const rowCount = Math.max(3, Math.ceil(users.length / 2));

  scheduleRows.innerHTML = Array.from({ length: rowCount }, function (_, rowIndex) {
    return `
      <tr>
        ${days.map(function (day, columnIndex) {
          const name = day === "Monday"
            ? "Closed"
            : esc(users[(rowIndex + columnIndex) % users.length]?.name || "");

          return `<td>${name}</td>`;
        }).join("")}
      </tr>
    `;
  }).join("");
}

function renderProfile() {
  const account = user() || read(USERS)[0];
  if (!account) return;

  setText("profileName", account.name.toUpperCase());
  setText("profileEmail", account.email);
  setText("profilePhone", account.phone || "+639 913 456 7890");

  const editName = document.getElementById("editProfileName");
  const editEmail = document.getElementById("editProfileEmail");
  const editPhone = document.getElementById("editProfilePhone");

  if (editName) editName.value = account.name;
  if (editEmail) editEmail.value = account.email;
  if (editPhone) editPhone.value = account.phone || "+639 913 456 7890";
}

function buildXml() {
  return `<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE salon SYSTEM "salon.dtd">
<salon>
  <name>The Lounge Royale</name>
  <staff>${read(USERS).map(function (account) {
    return `
    <employee id="${account.id}">
      <name>${esc(account.name)}</name>
      <email>${esc(account.email)}</email>
    </employee>`;
  }).join("")}
  </staff>
  <clients>${read(CLIENTS).map(function (client) {
    return `
    <client id="${client.id}">
      <name>${esc(client.name)}</name>
      <email>${esc(client.email)}</email>
      <appointments>${client.appointments}</appointments>
    </client>`;
  }).join("")}
  </clients>
  <bookings>${read(BOOKINGS).map(function (booking) {
    return `
    <booking id="${booking.id}" status="${booking.status}">
      <client>${esc(booking.name)}</client>
      <service>${esc(booking.service)}</service>
      <date>${booking.date}</date>
    </booking>`;
  }).join("")}
  </bookings>
</salon>`;
}

function buildDtd() {
  return `<!ELEMENT salon (name, staff, clients, bookings)>
<!ELEMENT name (#PCDATA)>
<!ELEMENT staff (employee*)>
<!ELEMENT employee (name, email)>
<!ATTLIST employee id CDATA #REQUIRED>
<!ELEMENT email (#PCDATA)>
<!ELEMENT clients (client*)>
<!ELEMENT client (name, email, appointments)>
<!ATTLIST client id CDATA #REQUIRED>
<!ELEMENT appointments (#PCDATA)>
<!ELEMENT bookings (booking*)>
<!ELEMENT booking (client, service, date)>
<!ATTLIST booking id CDATA #REQUIRED status (Completed|Pending|Cancelled) #REQUIRED>
<!ELEMENT service (#PCDATA)>
<!ELEMENT date (#PCDATA)>`;
}

function buildSoap() {
  return `<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
${buildXml().split("\n").map(function (line) {
    return "    " + line;
  }).join("\n")}
  </soap:Body>
</soap:Envelope>`;
}

function download(name, text, type) {
  const link = document.createElement("a");

  link.href = URL.createObjectURL(
    new Blob([text], { type: type || "text/xml" })
  );

  link.download = name;
  link.click();

  URL.revokeObjectURL(link.href);
}

function showXmlPreview(title, text) {
  const preview = document.getElementById("xmlPreview");
  const previewTitle = document.getElementById("xmlPreviewTitle");
  const previewCode = document.getElementById("xmlPreviewCode");

  if (!preview || !previewTitle || !previewCode) return;

  previewTitle.textContent = title;
  previewCode.textContent = text;
  preview.classList.add("show");
  preview.setAttribute("aria-hidden", "false");
}

function exportPreview(title, fileName, text, type) {
  showXmlPreview(title, text);
  download(fileName, text, type);
  toast(title + " generated and downloaded.");
}

const downloadXml = document.getElementById("downloadXml");
if (downloadXml) {
  downloadXml.onclick = function () {
    exportPreview("XML Data Preview", "salon-data.xml", buildXml());
  };
}

const downloadDtd = document.getElementById("downloadDtd");
if (downloadDtd) {
  downloadDtd.onclick = function () {
    exportPreview("DTD Preview", "salon.dtd", buildDtd(), "text/plain");
  };
}

const downloadSoap = document.getElementById("downloadSoap");
if (downloadSoap) {
  downloadSoap.onclick = function () {
    exportPreview("SOAP XML Preview", "soap-request.xml", buildSoap());
  };
}

const closeXmlPreview = document.getElementById("closeXmlPreview");
if (closeXmlPreview) {
  closeXmlPreview.onclick = function () {
    const preview = document.getElementById("xmlPreview");
    preview.classList.remove("show");
    preview.setAttribute("aria-hidden", "true");
  };
}

const xmlPreview = document.getElementById("xmlPreview");
if (xmlPreview) {
  xmlPreview.addEventListener("click", function (event) {
    if (event.target === xmlPreview && closeXmlPreview) {
      closeXmlPreview.click();
    }
  });
}

function makeMapMovable() {
  const map = document.getElementById("movableMap");
  const bar = document.getElementById("mapBar");

  if (!map || !bar) return;

  let startX = 0;
  let startY = 0;
  let startLeft = 0;
  let startTop = 0;
  let moving = false;

  bar.addEventListener("pointerdown", function (event) {
    moving = true;
    startX = event.clientX;
    startY = event.clientY;
    startLeft = map.offsetLeft;
    startTop = map.offsetTop;
    bar.setPointerCapture(event.pointerId);
  });

  bar.addEventListener("pointermove", function (event) {
    if (!moving) return;

    map.style.left = startLeft + event.clientX - startX + "px";
    map.style.top = startTop + event.clientY - startY + "px";
  });

  bar.addEventListener("pointerup", function () {
    moving = false;
  });
}

function fmtDate(value) {
  if (!value) return "";

  const parts = value.split("-");
  return Number(parts[1]) + "/" + Number(parts[2]) + "/" + parts[0];
}

function fmtTime(value) {
  if (!value) return "";

  let parts = value.split(":");
  let hour = Number(parts[0]);
  let minute = parts[1];

  return (hour % 12 || 12) + ":" + minute + (hour >= 12 ? "PM" : "AM");
}

function statusClass(status) {
  if (status === "Completed") return "status-completed";
  if (status === "Pending") return "status-pending";
  return "status-cancelled";
}

function sel(a, b) {
  return a === b ? "selected" : "";
}

function validEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

function setText(id, value) {
  const element = document.getElementById(id);
  if (element) element.textContent = value;
}

seed();

openDb().then(function () {
  saveDb(USERS, read(USERS));
  saveDb(BOOKINGS, read(BOOKINGS));
  saveDb(CLIENTS, read(CLIENTS));
});

renderServiceOptions();
renderAll();
makeMapMovable();

const savedPage = sessionStorage.getItem("royale_current_page") || "home";

if (document.getElementById("page-" + savedPage)) {
  showPage(savedPage);
} else {
  showPage("home");
}