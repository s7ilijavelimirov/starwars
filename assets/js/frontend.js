/**
 * Optimizovani JavaScript za Star Wars temu
 * Uklonjena debugging podrška, dodate sigurnosne provere
 */
document.addEventListener('DOMContentLoaded', function () {
  // Scroll efekti za header - sa proverom da li element postoji
  initHeaderScroll();

  // Debug mod - samo ako je potreban
  if (window.location.search.includes('debug=true')) {
    analyzeSitePerformance();
  }

  // Inicijalizuj sve komponente
  initImageModals();
  initBackToTop();
});

/**
 * Inicijalizacija scroll efekta za header
 * Sa sigurnosnim proverama elemenata
 */
function initHeaderScroll() {
  const navbar = document.getElementById('navbar');

  // Sigurnosna provera - izađi ako element ne postoji
  if (!navbar) return;

  const body = document.body;

  // Optimizovana funkcija za scroll
  function checkScroll() {
    // Koristi konstantu umesto ponovnog računanja
    const scrolled = window.scrollY > 50;

    // Optimizacija - proveri trenutno stanje pre promene DOM-a
    const hasScrolledClass = navbar.classList.contains('scrolled');

    if (scrolled && !hasScrolledClass) {
      navbar.classList.add('scrolled');
      body.classList.add('header-scrolled');
    } else if (!scrolled && hasScrolledClass) {
      navbar.classList.remove('scrolled');
      body.classList.remove('header-scrolled');
    }
  }

  // Inicijalno stanje - proverimo odmah
  checkScroll();

  // Throttling za scroll event
  let ticking = false;
  window.addEventListener('scroll', function () {
    if (!ticking) {
      window.requestAnimationFrame(function () {
        checkScroll();
        ticking = false;
      });
      ticking = true;
    }
  }, { passive: true });
}

/**
 * Funkcija za inicijalizaciju modala za slike (npr. tabele veličina)
 */
function initImageModals() {
  // Elementi za modal
  const modal = document.getElementById('myModal');
  const img = document.getElementById('myImg');

  // Proveri da li elementi postoje
  if (!modal || !img) return;

  const modalImg = document.getElementById('img01');
  const captionText = document.getElementById('caption');
  const closeBtn = document.querySelector('.close');

  // Event listener za otvaranje modala
  img.onclick = function () {
    modal.style.display = "block";
    if (modalImg) modalImg.src = this.src;
    if (captionText) captionText.innerHTML = this.alt;
  }

  // Event listener za zatvaranje modala
  if (closeBtn) {
    closeBtn.onclick = function () {
      modal.style.display = "none";
    }
  }

  // Zatvaranje na klik izvan slike
  modal.onclick = function (e) {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  }
}

/**
 * Inicijalizacija back-to-top dugmeta
 */
function initBackToTop() {
  const backToTopBtn = document.querySelector('.back-to-top-btn');

  if (!backToTopBtn) return;

  // Kada se skroluje više od 300px, prikaži dugme
  window.addEventListener('scroll', function () {
    if (window.scrollY > 300) {
      backToTopBtn.classList.add('show');
    } else {
      backToTopBtn.classList.remove('show');
    }
  }, { passive: true });

  // Scroll na vrh kada se klikne dugme
  backToTopBtn.addEventListener('click', function (e) {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}
