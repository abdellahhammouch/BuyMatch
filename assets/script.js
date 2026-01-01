// Mobile menu
function toggleMobileMenu(){
  const menu = document.getElementById("mobileMenu");
  if(!menu) return;
  const isOpen = menu.style.display === "block";
  menu.style.display = isOpen ? "none" : "block";
}

// Simple filters for matchs.html
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

// Quantity limit (max 4)
function clampQty(){
  const qty = document.getElementById("qty");
  if(!qty) return;
  let v = parseInt(qty.value || "1", 10);
  if (Number.isNaN(v)) v = 1;
  if (v < 1) v = 1;
  if (v > 4) v = 4;
  qty.value = String(v);
}

// Basic "modal" (optional)
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
  // auto bind
  const q = document.getElementById("q");
  const city = document.getElementById("city");
  if(q) q.addEventListener("input", filterMatches);
  if(city) city.addEventListener("change", filterMatches);

  const qty = document.getElementById("qty");
  if(qty) qty.addEventListener("input", clampQty);
});
