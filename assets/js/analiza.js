(function () {
  function collectPerformanceData() {
    const timing = performance.timing;
    const pageLoadTime = timing.loadEventEnd - timing.navigationStart;
    const domContentLoadedTime = timing.domContentLoadedEventEnd - timing.navigationStart;

    const resources = performance.getEntriesByType('resource').map(res => ({
      name: res.name,
      type: res.initiatorType,
      size: res.transferSize,
      duration: res.duration.toFixed(2)
    }));

    return {
      timings: {
        pageLoad: pageLoadTime,
        domContentLoaded: domContentLoadedTime
      },
      resources: resources
    };
  }

  function displayPerformanceData() {
    const data = collectPerformanceData();

    console.log('%c🚀 Performanse sajta', 'color: #00aaff; font-size: 16px; font-weight: bold;');
    console.log(`%c⏱️ Učitavanje stranice: ${data.timings.pageLoad}ms`, 'color: green;');
    console.log(`%c📄 DOM učitan: ${data.timings.domContentLoaded}ms`, 'color: orange;');

    console.log('%c📦 Resursi:', 'color: #9933cc; font-weight: bold;');
    console.table(data.resources);
  }

  // Sačekaj da se sve učita pa onda pokreni analizu
  window.addEventListener('load', function () {
    setTimeout(displayPerformanceData, 3000); // pauza 3 sekunde posle učitavanja
  });
})();
