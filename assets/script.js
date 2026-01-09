// ===== Menu mobile =====
function toggleMobileMenu() {
  const menu = document.getElementById("mobileMenu");
  if (!menu) return;
  menu.classList.toggle("show");
}

// ===== Dropdown profil =====
function toggleProfile() {
  const dd = document.getElementById("profileDropdown");
  if (!dd) return;
  dd.classList.toggle("show"); // CSS utilise .show
}

// Fermer dropdown si click dehors
document.addEventListener("click", (e) => {
  const trigger = document.querySelector(".profile-trigger");
  const dd = document.getElementById("profileDropdown");
  if (!trigger || !dd) return;

  if (!trigger.contains(e.target) && !dd.contains(e.target)) {
    dd.classList.remove("show");
  }
});

// ===== Filtre matchs =====
function filterMatches() {
  const q = (document.getElementById("q")?.value || "").toLowerCase();
  const city = (document.getElementById("city")?.value || "").toLowerCase();

  document.querySelectorAll("[data-match-card]").forEach((card) => {
    const text = (card.getAttribute("data-text") || "").toLowerCase();
    const cardCity = (card.getAttribute("data-city") || "").toLowerCase();

    const okQ = !q || text.includes(q);
    const okCity = !city || cardCity === city;

    card.style.display = okQ && okCity ? "block" : "none";
  });
}

// ===== Clamp quantité achat (max 4) =====
function clampQty() {
  const qty = document.getElementById("qty");
  if (!qty) return;

  let v = parseInt(qty.value || "1", 10);
  if (Number.isNaN(v)) v = 1;
  if (v < 1) v = 1;
  if (v > 4) v = 4;
  qty.value = String(v);
}

// ===== Modals BuyMatch (data-open-modal / data-close-modal) =====
document.addEventListener("click", (e) => {
  const openBtn = e.target.closest("[data-open-modal]");
  if (openBtn) {
    const id = openBtn.getAttribute("data-open-modal");
    const modal = document.getElementById(id);
    if (modal) modal.style.display = "block";
    return;
  }

  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) {
    const id = closeBtn.getAttribute("data-close-modal");
    const modal = document.getElementById(id);
    if (modal) modal.style.display = "none";
    return;
  }
});

// ESC ferme tous les modals
document.addEventListener("keydown", (e) => {
  if (e.key !== "Escape") return;
  document.querySelectorAll(".bm-modal").forEach((m) => (m.style.display = "none"));
});

// ===== Tabs (Matchs / Historique) =====
document.addEventListener("click", (e) => {
  const link = e.target.closest("[data-tab-link]");
  if (!link) return;

  e.preventDefault();
  const tab = link.getAttribute("data-tab-link");

  const tabMatchs = document.getElementById("tab-matchs");
  const tabHistory = document.getElementById("tab-history");
  if (!tabMatchs || !tabHistory) return;

  if (tab === "history") {
    tabMatchs.style.display = "none";
    tabHistory.style.display = "block";
  } else {
    tabMatchs.style.display = "block";
    tabHistory.style.display = "none";
  }

  document.querySelectorAll("[data-tab-link]").forEach((a) => a.classList.remove("active"));
  link.classList.add("active");
});

// ===== DOMContentLoaded : hooks =====
document.addEventListener("DOMContentLoaded", () => {
  // filtre
  const q = document.getElementById("q");
  const city = document.getElementById("city");
  if (q) q.addEventListener("input", filterMatches);
  if (city) city.addEventListener("change", filterMatches);

  // qty
  const qty = document.getElementById("qty");
  if (qty) qty.addEventListener("input", clampQty);

  // open modal by URL ?open=ID
  const params = new URLSearchParams(window.location.search);
  const openId = params.get("open");
  if (openId) {
    const modal = document.getElementById("matchModal-" + openId);
    if (modal) modal.style.display = "block";
  }

  // preview image (profile)
  const input = document.getElementById("photoInput");
  const avatarPreview = document.getElementById("avatarPreview");
  const photoPreview = document.getElementById("photoPreview"); // si tu l’utilises ailleurs
  const circle = document.getElementById("avatarCircle");

  if (input) {
    input.addEventListener("change", (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;

      if (!file.type.startsWith("image/")) {
        alert("Veuillez choisir une image (png/jpg/webp).");
        input.value = "";
        return;
      }

      const url = URL.createObjectURL(file);

      if (avatarPreview) {
        avatarPreview.src = url;
        avatarPreview.style.display = "block";
        const icon = circle ? circle.querySelector("i") : null;
        if (icon) icon.style.display = "none";
      }

      if (photoPreview) {
        photoPreview.src = url;
        photoPreview.style.display = "block";
      }
    });
  }

  // toggle password (register/login)
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
