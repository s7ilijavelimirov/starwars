/**
 * Minimalna JavaScript za scroll efekat headera
 * Bez Bootstrap zavisnosti, samo za scroll animaciju
 */
document.addEventListener('DOMContentLoaded', function() {
    // Scroll efekti za header
    initHeaderScroll();
    
    // Analiza performansi sajta
    if (window.location.search.includes('debug=true')) {
      analyzeSitePerformance();
    }
  });
  
  /**
   * Inicijalizacija scroll efekta za header
   */
  function initHeaderScroll() {
    const navbar = document.getElementById('navbar');
    const body = document.body;
    
    if (!navbar) return;
    
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
    window.addEventListener('scroll', function() {
      if (!ticking) {
        window.requestAnimationFrame(function() {
          checkScroll();
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });
  }
  
  /**
   * Analiza performansi i učitavanja Bootstrap resursa
   */
  function analyzeSitePerformance() {
    console.group('%c📊 Analiza performansi sajta', 'font-size: 16px; font-weight: bold; color: #ffe81f; background: #000;');
    
    // 1. Analiza Bootstrap resursa
    console.group('%c📦 Bootstrap resursi', 'font-weight: bold;');
    const bootstrapJS = Array.from(document.querySelectorAll('script[src*="bootstrap"]'));
    const bootstrapCSS = Array.from(document.querySelectorAll('link[rel="stylesheet"][href*="bootstrap"], link[rel="preload"][href*="bootstrap"]'));
    
    console.log(`JavaScript resursi (${bootstrapJS.length}):`);
    bootstrapJS.forEach(script => {
      console.log(`- ${script.src.split('/').pop()} (${script.async ? 'async' : 'sync'}, ${script.defer ? 'defer' : 'no-defer'})`);
    });
    
    console.log(`CSS resursi (${bootstrapCSS.length}):`);
    bootstrapCSS.forEach(link => {
      console.log(`- ${link.href.split('/').pop()} (${link.rel})`);
    });
    
    // Proveri za potencijalno dupliranje
    if (bootstrapJS.length > 1) {
      console.warn('⚠️ Detektovano više Bootstrap JS resursa. Moguće dupliranje.');
    }
    if (bootstrapCSS.length > 1) {
      console.warn('⚠️ Detektovano više Bootstrap CSS resursa. Moguće dupliranje.');
    }
    console.groupEnd();
    
    // 2. Analiza performansi učitavanja
    console.group('%c⚡ Performanse učitavanja', 'font-weight: bold;');
    if (window.performance && window.performance.getEntriesByType) {
      const resources = window.performance.getEntriesByType('resource');
      const bootstrapResources = resources.filter(res => 
        res.name.includes('bootstrap')
      );
      
      if (bootstrapResources.length > 0) {
        let totalSize = 0;
        let totalTime = 0;
        
        bootstrapResources.forEach(res => {
          totalSize += res.transferSize || 0;
          totalTime += res.duration || 0;
          
          console.log(`${res.name.split('/').pop()}:`);
          console.log(`  - Veličina: ${formatBytes(res.transferSize || 0)}`);
          console.log(`  - Vreme učitavanja: ${Math.round(res.duration)}ms`);
        });
        
        console.log(`Ukupna veličina Bootstrap resursa: ${formatBytes(totalSize)}`);
        console.log(`Prosečno vreme učitavanja: ${Math.round(totalTime / bootstrapResources.length)}ms`);
      } else {
        console.log('Nisu pronađeni Bootstrap resursi u Performance API.');
      }
      
      // 3. Analiza učitavanja stranice
      const pageLoadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
      console.log(`Ukupno vreme učitavanja stranice: ${pageLoadTime}ms`);
      
      // FCP (First Contentful Paint)
      const paintEntries = window.performance.getEntriesByType('paint');
      const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
      const firstContentfulPaint = paintEntries.find(entry => entry.name === 'first-contentful-paint');
      
      if (firstPaint) {
        console.log(`First Paint: ${Math.round(firstPaint.startTime)}ms`);
      }
      if (firstContentfulPaint) {
        console.log(`First Contentful Paint: ${Math.round(firstContentfulPaint.startTime)}ms`);
      }
    } else {
      console.log('Performance API nije dostupan za analizu učitavanja.');
    }
    console.groupEnd();
    
    // 3. Analiza CSS Bootstrap klasa
    console.group('%c🔢 Analiza CSS Bootstrap klasa', 'font-weight: bold;');
    
    // Prikupi sve Bootstrap klase korišćene na stranici
    const allElements = document.querySelectorAll('*');
    const bootstrapClassMap = {};
    const bootstrapClassPatterns = [
      /^container/, /^row/, /^col/, /^d-/, /^m[trblxy]?-/, /^p[trblxy]?-/,
      /^bg-/, /^text-/, /^align-/, /^float-/, /^position-/, /^fixed-/,
      /^w-/, /^h-/, /^flex/, /^justify-/, /^align-/, /^order-/,
      /^form-/, /^btn/, /^nav/, /^card/, /^table/, /^alert/,
      /^badge/, /^modal/, /^carousel/, /^spinner/, /^toast/,
      /^border/, /^rounded/, /^shadow/, /^invisible/, /^visible/
    ];
    
    // Za svaki element, proveri Bootstrap klase
    allElements.forEach(element => {
      if (element.classList && element.classList.length) {
        // Konvertuj classList u niz
        const classes = Array.from(element.classList);
        
        // Filtriraj samo Bootstrap klase
        classes.forEach(className => {
          if (bootstrapClassPatterns.some(pattern => pattern.test(className))) {
            if (!bootstrapClassMap[className]) {
              bootstrapClassMap[className] = {
                count: 0,
                elements: []
              };
            }
            bootstrapClassMap[className].count++;
            bootstrapClassMap[className].elements.push(element);
          }
        });
      }
    });
    
    // Sortiraj klase po učestalosti
    const sortedClasses = Object.keys(bootstrapClassMap).sort((a, b) => 
      bootstrapClassMap[b].count - bootstrapClassMap[a].count
    );
    
    if (sortedClasses.length > 0) {
      console.group('Top 15 najčešće korišćenih Bootstrap klasa:');
      sortedClasses.slice(0, 15).forEach((className, index) => {
        console.log(`${index+1}. %c${className}%c - korišćena ${bootstrapClassMap[className].count} puta`, 
            'font-weight: bold; color: #ffe81f;', 'font-weight: normal;');
      });
      console.groupEnd();
      
      // Grupiši klase po vrsti
      const classBuckets = {
        'Layout': ['container', 'row', 'col'],
        'Flexbox': ['d-flex', 'flex', 'justify-content', 'align-items'],
        'Spacing': ['m', 'p', 'mt', 'mb', 'ms', 'me', 'pt', 'pb', 'ps', 'pe'],
        'Display': ['d-none', 'd-block', 'd-inline', 'd-inline-block'],
        'Components': ['navbar', 'nav', 'carousel', 'modal', 'offcanvas', 'dropdown']
      };
      
      // Analiza klasa po vrsti
      console.group('Kategorije korišćenih Bootstrap klasa:');
      for (const [category, prefixes] of Object.entries(classBuckets)) {
        const matches = sortedClasses.filter(cls => 
          prefixes.some(prefix => cls.startsWith(prefix))
        );
        if (matches.length > 0) {
          console.log(`%c${category}:%c ${matches.length} klasa`, 'font-weight: bold; color: #ffe81f;', 'font-weight: normal;');
        }
      }
      console.groupEnd();
    } else {
      console.log('Nisu pronađene Bootstrap klase na stranici.');
    }
    console.groupEnd();
    
    // 4. Preporuke za optimizaciju
    console.group('%c🚀 Preporuke za optimizaciju', 'font-weight: bold;');
    
    // Ukupan broj detektovanih klasa
    const uniqueClassesCount = sortedClasses.length;
    const totalClassesUsage = sortedClasses.reduce((total, cls) => total + bootstrapClassMap[cls].count, 0);
    
    console.log(`Broj jedinstvenih Bootstrap klasa: ${uniqueClassesCount}`);
    console.log(`Ukupno korišćenje Bootstrap klasa: ${totalClassesUsage}`);
    
    // Performanse preporuke
    console.log('%cOpšte preporuke:', 'font-weight: bold;');
    console.log('✓ Koristi defer za non-critical JavaScript (bootstrap.bundle.min.js)');
    console.log('✓ Preload kritične resurse (CSS, fontovi, pozadinske slike)');
    console.log('✓ Koristi font-display: swap za bolje učitavanje fontova');
    console.log('✓ Optimizuj slike korišćenjem WebP formata i lazy loading-a');
    
    console.log('%cBootstrap preporuke:', 'font-weight: bold;');
    console.log('✓ Koristi samo potrebne Bootstrap komponente (selektivni import)');
    console.log('✓ Implementiraj PurgeCSS za uklanjanje nekorišćenih klasa');
    console.log('✓ Razmotri custom Bootstrap build sa samo potrebnim komponentama');
    
    if (window.jQuery) {
      console.log('⚠️ Detektovan jQuery. Bootstrap 5 ne zahteva jQuery, razmotri njegovo uklanjanje.');
    }
    
    console.groupEnd();
    console.groupEnd();
    
    // Pomoćne funkcije
    function formatBytes(bytes, decimals = 2) {
      if (bytes === 0) return '0 Bytes';
      
      const k = 1024;
      const dm = decimals < 0 ? 0 : decimals;
      const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
      
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
  }