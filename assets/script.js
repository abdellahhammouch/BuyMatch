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