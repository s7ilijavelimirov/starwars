/**
 * Glavni frontend JavaScript - Sa specifičnim rešenjem za loop animacije
 */

document.addEventListener('DOMContentLoaded', function () {
  // Inicijalizacija samo animacija teksta u slideru
  initSliderTextAnimations();

  // Ostale inicijalizacije
  initHeaderScroll();
  initBackToTop();
});
document.addEventListener('DOMContentLoaded', function () {
  const searchToggle = document.getElementById('searchToggle');
  const searchDropdown = document.getElementById('searchDropdown');
  const searchInput = document.getElementById('searchInput');
  const searchClose = document.getElementById('searchClose');

  // Otvori search
  searchToggle.addEventListener('click', function (e) {
    e.stopPropagation();
    searchDropdown.style.display = 'block';
    setTimeout(() => {
      searchDropdown.classList.add('show');
      searchInput.focus();
    }, 10);
  });

  // Zatvori search
  function closeSearch() {
    searchDropdown.classList.remove('show');
    setTimeout(() => {
      searchDropdown.style.display = 'none';
    }, 300);
  }

  searchClose.addEventListener('click', closeSearch);

  // Zatvori klikom izvan
  document.addEventListener('click', function (e) {
    if (!searchDropdown.contains(e.target) && !searchToggle.contains(e.target)) {
      closeSearch();
    }
  });

  // Zatvori sa Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && searchDropdown.classList.contains('show')) {
      closeSearch();
    }
  });
});
/**
 * Inicijalizuje animacije teksta u Bootstrap slideru
 * Sa posebnim rešenjem za ponavljanje animacije kod povratka na prvi slajd
 */
function initSliderTextAnimations() {
  const heroSlider = document.getElementById('heroSlider');
  if (!heroSlider) return;

  // Globalno stanje za praćenje slajdova
  let currentSlideIndex = 0;
  let previousSlideIndex = 0;
  let totalSlides = 0;
  let firstSlideAnimated = false;

  // Dobavljamo sve slajdove
  const allSlides = heroSlider.querySelectorAll('.carousel-item');
  totalSlides = allSlides.length;

  // Postavljamo inicijalni indeks
  allSlides.forEach((slide, index) => {
    if (slide.classList.contains('active')) {
      currentSlideIndex = index;
    }
  });

  // Resetujemo SVE slajdove na početku - važno za rešavanje problema sa prvim slajdom
  allSlides.forEach((slide) => {
    const content = slide.querySelector('.caption-content');
    const title = slide.querySelector('.hero-title');
    const subtitle = slide.querySelector('.hero-subtitle');
    const button = slide.querySelector('.hero-button-wrapper');

    if (content) content.style.opacity = '0';
    if (title) title.style.opacity = '0';
    if (subtitle) subtitle.style.opacity = '0';
    if (button) button.style.opacity = '0';
  });

  /**
   * Resetuje animacije na određenom slajdu
   */
  function resetSlideAnimations(slideIndex) {
    if (slideIndex < 0 || slideIndex >= totalSlides) return;

    const slide = allSlides[slideIndex];
    if (!slide) return;

    const content = slide.querySelector('.caption-content');
    const title = slide.querySelector('.hero-title');
    const subtitle = slide.querySelector('.hero-subtitle');
    const button = slide.querySelector('.hero-button-wrapper');

    // Odmah resetujemo sve elemente
    if (content) {
      content.style.opacity = '0';
      content.style.animationName = 'none';
    }
    if (title) title.style.opacity = '0';
    if (subtitle) subtitle.style.opacity = '0';
    if (button) button.style.opacity = '0';
  }

  /**
   * Animira tekstualne elemente određenog slajda
   */
  function animateSlideContent(slideIndex) {
    if (slideIndex < 0 || slideIndex >= totalSlides) return;

    const slide = allSlides[slideIndex];
    if (!slide) return;

    // Poseban slučaj za prvi slajd u drugom krugu
    const isFirstSlideAgain = (slideIndex === 0 && firstSlideAnimated);

    // Pronađimo elemente
    const content = slide.querySelector('.caption-content');
    const title = slide.querySelector('.hero-title');
    const subtitle = slide.querySelector('.hero-subtitle');
    const button = slide.querySelector('.hero-button-wrapper');
    const animationType = slide.getAttribute('data-animation');

    // Poseban slučaj: ako je prvi slajd opet, prvo ga resetujemo
    if (isFirstSlideAgain) {
      if (content) {
        content.style.opacity = '0';
        content.style.animationName = 'none';
      }
      if (title) title.style.opacity = '0';
      if (subtitle) subtitle.style.opacity = '0';
      if (button) button.style.opacity = '0';

      // Sačekaj reflow browsersa da registruje reset
      void slide.offsetWidth;
    }

    // Zatim primenimo animacije
    setTimeout(() => {
      if (content) {
        content.style.opacity = '1';

        // Primeni odgovarajuću animaciju
        if (animationType) {
          switch (animationType) {
            case 'fade-enter':
              content.style.animationName = 'fadeInUp';
              break;
            case 'slide-right-enter':
              content.style.animationName = 'slideInRight';
              break;
            case 'slide-left-enter':
              content.style.animationName = 'slideInLeft';
              break;
            default:
              content.style.animationName = 'fadeInUp';
          }
        }
      }

      if (title) title.style.opacity = '1';
      if (subtitle) subtitle.style.opacity = '1';
      if (button) button.style.opacity = '1';

      // Označi da je prvi slajd animiran ako je ovo prvi slajd
      if (slideIndex === 0) {
        firstSlideAnimated = true;
      }
    }, isFirstSlideAgain ? 50 : 20); // Malo duži delay za prvi slajd u ponovljenom ciklusu
  }

  // Prvi slide događaj: resetujemo sve slajdove
  heroSlider.addEventListener('slide.bs.carousel', function (event) {
    // Pratimo indekse slajdova
    previousSlideIndex = currentSlideIndex;

    // Ako imamo event indekse, koristimo njih
    if (typeof event.from !== 'undefined' && typeof event.to !== 'undefined') {
      previousSlideIndex = event.from;
      currentSlideIndex = event.to;
    } else {
      // Inače, pretpostavljamo da idemo na sledeći slajd
      currentSlideIndex = (previousSlideIndex + 1) % totalSlides;
    }

    // Posebna obrada za slučaj kad se vraćamo na prvi slajd
    if (currentSlideIndex === 0 && previousSlideIndex === totalSlides - 1) {
      // Ovo je povratak sa poslednjeg na prvi slajd
      // Resetujemo prvi slajd eksplicitno pre animacije
      resetSlideAnimations(0);
    } else {
      // Normalno resetovanje prethodnog slajda
      resetSlideAnimations(previousSlideIndex);
    }
  });

  // Drugi slide događaj: animiramo novi aktivni slajd
  heroSlider.addEventListener('slid.bs.carousel', function (event) {
    // Ažuriramo indeks ako je dostupan
    if (typeof event.to !== 'undefined') {
      currentSlideIndex = event.to;
    }

    // Animiramo novi slajd
    animateSlideContent(currentSlideIndex);
  });

  // Hvatanje promene slajda po tranziciji za dodatnu sigurnost
  heroSlider.addEventListener('transitionend', function (e) {
    // Samo reagujemo na carousel-item tranzicije
    if (e.target.classList.contains('carousel-item') && e.propertyName === 'opacity') {
      // Ako je ovo završetak prikazivanja slajda
      if (e.target.classList.contains('active')) {
        // Pronađi indeks ovog slajda
        let slideIndex = Array.from(allSlides).indexOf(e.target);

        // Samo ako ne odgovara trenutnom indeksu
        if (slideIndex !== -1 && slideIndex !== currentSlideIndex) {
          currentSlideIndex = slideIndex;
          animateSlideContent(currentSlideIndex);
        }
      }
    }
  });

  // Animiraj prvi slajd nakon učitavanja
  setTimeout(() => {
    animateSlideContent(currentSlideIndex);
  }, 100);
}

/**
 * Inicijalizacija scroll efekta za header
 */
function initHeaderScroll() {
  const navbar = document.getElementById('navbar');
  if (!navbar) return;

  const body = document.body;

  function checkScroll() {
    const scrolled = window.scrollY > 50;
    const hasScrolledClass = navbar.classList.contains('scrolled');

    if (scrolled && !hasScrolledClass) {
      navbar.classList.add('scrolled');
      body.classList.add('header-scrolled');
    } else if (!scrolled && hasScrolledClass) {
      navbar.classList.remove('scrolled');
      body.classList.remove('header-scrolled');
    }
  }

  checkScroll();

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
 * Inicijalizacija back-to-top dugmeta
 */
function initBackToTop() {
  const backToTopBtn = document.querySelector('.back-to-top-btn');
  if (!backToTopBtn) return;

  window.addEventListener('scroll', function () {
    if (window.scrollY > 300) {
      backToTopBtn.classList.add('show');
    } else {
      backToTopBtn.classList.remove('show');
    }
  }, { passive: true });

  backToTopBtn.addEventListener('click', function (e) {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}
