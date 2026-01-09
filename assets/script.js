function toggleMobileMenu(){
  const menu = document.getElementById("mobileMenu");
  if(!menu) return;
  const isOpen = menu.classList.contains("show");
  if(isOpen) {
    menu.classList.remove("show");
  } else {
    menu.classList.add("show");
  }
}

function toggleProfile() {
  const dropdown = document.getElementById('profileDropdown');
  if(!dropdown) return;
  dropdown.classList.toggle('show');
}

document.addEventListener('click', (e) => {
  const profileTrigger = document.querySelector('.profile-trigger');
  const dropdown = document.getElementById('profileDropdown');
  if (profileTrigger && dropdown && 
      !profileTrigger.contains(e.target) && 
      !dropdown.contains(e.target)) {
    dropdown.classList.remove('show');
  }
});

function filterMatches(){
  const q = (document.getElementById("q")?.value || "").toLowerCase();
  const city = (document.getElementById("city")?.value || "").toLowerCase();

  document.querySelectorAll("[data-match-card]").forEach(card => {
    const text = (card.getAttribute("data-text") || "").toLowerCase();
    const cardCity = (card.getAttribute("data-city") || "").toLowerCase();

    const okQ = !q || text.includes(q);
    const okCity = !city || cardCity === city;
    card.style.display = (okQ && okCity) ? "block" : "none";
  });
}

function clampQty(){
  const qty = document.getElementById("qty");
  if(!qty) return;
  let v = parseInt(qty.value || "1", 10);
  if (Number.isNaN(v)) v = 1;
  if (v < 1) v = 1;
  if (v > 4) v = 4;
  qty.value = String(v);
}

function openModal(id){
  const m = document.getElementById(id);
  if(!m) return;
  m.style.display = "flex";
}
function closeModal(id){
  const m = document.getElementById(id);
  if(!m) return;
  m.style.display = "none";
}

document.addEventListener("DOMContentLoaded", () => {

  const q = document.getElementById("q");
  const city = document.getElementById("city");
  if(q) q.addEventListener("input", filterMatches);
  if(city) city.addEventListener("change", filterMatches);

  const qty = document.getElementById("qty");
  if(qty) qty.addEventListener("input", clampQty);
  
  const input = document.getElementById("photoInput");
  const img = document.getElementById("avatarPreview");
  const circle = document.getElementById("avatarCircle");

  if (input && img && circle) {
    input.addEventListener("change", (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;

      if (!file.type.startsWith("image/")) {
        alert("Veuillez choisir une image (png/jpg/webp).");
        input.value = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = () => {
        img.src = reader.result;
        img.style.display = "block";

        const icon = circle.querySelector("i");
        if (icon) icon.style.display = "none";
      };
      reader.readAsDataURL(file);
    });
  }
  
  const toggleBtn = document.getElementById("togglePassword");
  const passInput = document.getElementById("password");
  const icon = document.getElementById("toggleIcon");

  if (toggleBtn && passInput && icon) {
    toggleBtn.addEventListener("click", () => {
      const isPassword = passInput.type === "password";
      passInput.type = isPassword ? "text" : "password";
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    });
  }
});

function showTicketModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'flex';
  }
}

function closeTicketModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'none';
  }
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('ticket-modal')) {
    e.target.style.display = 'none';
  }
});

const input = document.getElementById("photoInput");
  const preview = document.getElementById("photoPreview");

  if (input && preview) {
    input.addEventListener("change", function () {
      const file = this.files && this.files[0];
      if (!file) return;

      const url = URL.createObjectURL(file);
      preview.src = url;
      preview.style.display = "block";
    });
  }

  document.addEventListener("click", function (e) {
  const dd = document.getElementById("profileDropdown");
  const trigger = document.querySelector(".profile-trigger");
  if (!dd || !trigger) return;

  if (!dd.contains(e.target) && !trigger.contains(e.target)) {
    dd.classList.remove("open");
  }
});


document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-open-modal]").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-open-modal");
      const modal = document.getElementById(id);
      if (modal) modal.style.display = "block";
    });
  });

  document.querySelectorAll("[data-close-modal]").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-close-modal");
      const modal = document.getElementById(id);
      if (modal) modal.style.display = "none";
    });
  });
});


function adminShowSection(name) {
  const sections = ["demandesSection", "usersSection", "statsSection"];

  sections.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.style.display = "none";
  });

  const target = document.getElementById(name + "Section");
  if (target) target.style.display = "block";

  const links = document.querySelectorAll(".sidebar-link");
  links.forEach((a) => a.classList.remove("active"));

  links.forEach((a) => {
    const onclick = a.getAttribute("onclick") || "";
    if (onclick.includes("adminShowSection('" + name + "')")) {
      a.classList.add("active");
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("demandesSection")) {
    adminShowSection("demandes");
  }
});

// Tabs admin: Demandes / Utilisateurs / Stats
document.addEventListener("DOMContentLoaded", () => {
  const links = document.querySelectorAll(".sidebar-link");
  const sections = {
    demandes: document.getElementById("demandesSection"),
    users: document.getElementById("usersSection"),
    stats: document.getElementById("statsSection"),
  };

  function showSection(name) {
    Object.keys(sections).forEach((k) => {
      if (sections[k]) sections[k].style.display = "none";
    });
    if (sections[name]) sections[name].style.display = "block";

    links.forEach((a) => a.classList.remove("active"));
    links.forEach((a) => {
      if (a.dataset.section === name) a.classList.add("active");
    });
  }

  links.forEach((a) => {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      showSection(a.dataset.section);
    });
  });

  // default
  showSection("demandes");
});

// Stats organisateur: show/hide (détails)
function showOrgStats(orgId) {
  const empty = document.getElementById("orgStatsEmpty");
  if (empty) empty.style.display = "none";

  const all = document.querySelectorAll(".org-stats-card");
  all.forEach((el) => (el.style.display = "none"));

  const target = document.getElementById("orgStats" + orgId);
  if (target) target.style.display = "block";
}


// OPEN / CLOSE MODAL (BuyMatch)
document.addEventListener("click", function (e) {
  // Ouvrir
  const openBtn = e.target.closest("[data-open-modal]");
  if (openBtn) {
    const id = openBtn.getAttribute("data-open-modal");
    const modal = document.getElementById(id);
    if (modal) modal.style.display = "block";
    return;
  }

  // Fermer
  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) {
    const id = closeBtn.getAttribute("data-close-modal");
    const modal = document.getElementById(id);
    if (modal) modal.style.display = "none";
    return;
  }
});

// Fermer avec la touche ESC
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    document.querySelectorAll(".bm-modal").forEach(function (m) {
      m.style.display = "none";
    });
  }
});

document.addEventListener("click", function (e) {
  const link = e.target.closest("[data-tab-link]");
  if (!link) return;

  e.preventDefault();
  const tab = link.getAttribute("data-tab-link");

  const tabMatchs = document.getElementById("tab-matchs");
  const tabHistory = document.getElementById("tab-history");

  if (!tabMatchs || !tabHistory) return;

  // show/hide
  if (tab === "history") {
    tabMatchs.style.display = "none";
    tabHistory.style.display = "block";
  } else {
    tabMatchs.style.display = "block";
    tabHistory.style.display = "none";
  }

  // active class in navbar
  document.querySelectorAll("[data-tab-link]").forEach(a => a.classList.remove("active"));
  link.classList.add("active");
});

document.addEventListener("DOMContentLoaded", function () {
  const params = new URLSearchParams(window.location.search);
  const openId = params.get("open"); // match id

  if (openId) {
    const modal = document.getElementById("matchModal-" + openId);
    if (modal) modal.style.display = "block";
  }
});

document.addEventListener("DOMContentLoaded", function () {
  // switch sections admin
  const links = document.querySelectorAll(".sidebar-link");
  const demandes = document.getElementById("demandesSection");
  const users = document.getElementById("usersSection");
  const stats = document.getElementById("statsSection");

  function show(section) {
    if (demandes) demandes.style.display = (section === "demandes") ? "" : "none";
    if (users) users.style.display = (section === "users") ? "" : "none";
    if (stats) stats.style.display = (section === "stats") ? "" : "none";

    links.forEach(l => l.classList.remove("active"));
    links.forEach(l => {
      if (l.getAttribute("data-section") === section) l.classList.add("active");
    });
  }

  links.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      show(this.getAttribute("data-section"));
    });
  });

  const params = new URLSearchParams(window.location.search);
  const section = params.get("section") || "demandes";
  show(section);
});


 const stars = document.querySelectorAll('.rating i');
const input1 = document.getElementById('stars');

stars.forEach(star => {
  star.addEventListener('click', () => {
    const value = star.getAttribute('data-value');
    input1.value = value;

    stars.forEach(s => {
      s.classList.toggle(
        'active',
        s.getAttribute('data-value') <= value
      );
    });
  });
});


