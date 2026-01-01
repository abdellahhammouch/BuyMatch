// assets/script.js
(function () {
  const toggle = document.querySelector("[data-nav-toggle]");
  const nav = document.querySelector("[data-nav]");
  if (toggle && nav) {
    toggle.addEventListener("click", () => nav.classList.toggle("open"));
  }

  // Filtre simple sur la page matchs.php
  const search = document.querySelector("[data-search]");
  const cards = document.querySelectorAll("[data-match-card]");

  if (search && cards.length) {
    search.addEventListener("input", () => {
      const q = search.value.trim().toLowerCase();
      cards.forEach((c) => {
        const text = (c.getAttribute("data-search-text") || "").toLowerCase();
        c.style.display = text.includes(q) ? "" : "none";
      });
    });
  }
})();
