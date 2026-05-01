// ============================================================
// DASHBOARD — Live Data + Charts
// ============================================================

(function () {

  // ── CSS variable helpers ──────────────────────────────────
  const rootStyle = getComputedStyle(document.documentElement);
  const cssVar = (name) => rootStyle.getPropertyValue(name).trim();

  const COLOR_PRIMARY   = cssVar('--primary');     // #FF7043  boarding / regular
  const COLOR_SECONDARY = cssVar('--secondary');   // #0b486f  drop-off / student
  const COLOR_SUCCESS   = cssVar('--success');     // #2ecc71  senior

  // ── Chart instances (held for potential refresh) ──────────
  let hourlyChart = null;
  let typeChart   = null;

  // ── Number formatter ─────────────────────────────────────
  function fmt(n) {
    return Number(n).toLocaleString('en-PH');
  }

  // ── Populate stat cards ───────────────────────────────────
  function populateStats(stats) {
    const tripsEl      = document.getElementById('stat-trips');
    const passEl       = document.getElementById('stat-passengers');
    const revEl        = document.getElementById('stat-revenue');
    const activeEl     = document.getElementById('stat-active');
    const tripsChgEl   = document.getElementById('stat-trips-change');
    const passChgEl    = document.getElementById('stat-passengers-change');
    const revChgEl     = document.getElementById('stat-revenue-change');

    if (tripsEl)  tripsEl.textContent  = fmt(stats.tripsToday);
    if (passEl)   passEl.textContent   = fmt(stats.totalPassengers);
    if (revEl)    revEl.textContent    = '₱ ' + fmt(stats.totalRevenue.toFixed(2));
    if (activeEl) activeEl.textContent = fmt(stats.activeTrips);

    // Replace loading text with a friendly note
    const note = '<i class="bi bi-database-fill"></i> From database';
    if (tripsChgEl)  tripsChgEl.innerHTML = note;
    if (passChgEl)   passChgEl.innerHTML  = note;
    if (revChgEl)    revChgEl.innerHTML   = note;
  }

  // ── Populate passenger mini-cards ────────────────────────
  function populateBreakdownCards(breakdown) {
    const studentEl = document.getElementById('pax-student');
    const regularEl = document.getElementById('pax-regular');
    const seniorEl  = document.getElementById('pax-senior');

    if (studentEl) studentEl.textContent = fmt(breakdown.Student);
    if (regularEl) regularEl.textContent = fmt(breakdown.Regular);
    if (seniorEl)  seniorEl.textContent  = fmt(breakdown.Senior);
  }

  // ── Hourly Flow Bar Chart ─────────────────────────────────
  function renderHourlyChart(hourly) {
    const ctx = document.getElementById('hourlyChart');
    if (!ctx) return;

    if (hourlyChart) hourlyChart.destroy();

    hourlyChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: hourly.labels,
        datasets: [
          {
            label: 'Boarding',
            data: hourly.board,
            backgroundColor: COLOR_PRIMARY + 'cc',    // 80% opacity
            borderColor: COLOR_PRIMARY,
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
          },
          {
            label: 'Drop-off',
            data: hourly.drop,
            backgroundColor: COLOR_SECONDARY + 'cc',
            borderColor: COLOR_SECONDARY,
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: {
            position: 'top',
            align: 'end',
            labels: {
              boxWidth: 12,
              boxHeight: 12,
              borderRadius: 4,
              useBorderRadius: true,
              font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
              color: cssVar('--text-muted'),
            }
          },
          tooltip: {
            backgroundColor: '#fff',
            titleColor: cssVar('--text-main'),
            bodyColor: cssVar('--text-muted'),
            borderColor: cssVar('--border'),
            borderWidth: 1,
            padding: 12,
            cornerRadius: 10,
            titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' },
            bodyFont:  { family: "'Plus Jakarta Sans', sans-serif" },
            callbacks: {
              label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y} pax`
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: cssVar('--text-muted'),
              font: { family: "'DM Mono', monospace", size: 11 }
            }
          },
          y: {
            beginAtZero: true,
            grid: { color: cssVar('--border'), lineWidth: 1 },
            ticks: {
              color: cssVar('--text-muted'),
              font: { family: "'DM Mono', monospace", size: 11 },
              stepSize: 1,
              precision: 0,
            }
          }
        }
      }
    });
  }

  // ── Passenger Breakdown Pie Chart ─────────────────────────
  function renderTypeChart(breakdown) {
    const ctx = document.getElementById('typeChart');
    if (!ctx) return;

    if (typeChart) typeChart.destroy();

    const labels = ['Student', 'Regular', 'Senior'];
    const values = [breakdown.Student, breakdown.Regular, breakdown.Senior];
    const colors = [COLOR_SECONDARY, COLOR_PRIMARY, COLOR_SUCCESS];

    // Paler versions for hover
    const hoverColors = [
      COLOR_SECONDARY + 'dd',
      COLOR_PRIMARY   + 'dd',
      COLOR_SUCCESS   + 'dd',
    ];

    typeChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors.map(c => c + 'cc'),
          hoverBackgroundColor: hoverColors,
          borderColor: '#fff',
          borderWidth: 3,
          hoverOffset: 8,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              boxHeight: 12,
              borderRadius: 4,
              useBorderRadius: true,
              padding: 16,
              font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
              color: cssVar('--text-main'),
            }
          },
          tooltip: {
            backgroundColor: '#fff',
            titleColor: cssVar('--text-main'),
            bodyColor: cssVar('--text-muted'),
            borderColor: cssVar('--border'),
            borderWidth: 1,
            padding: 12,
            cornerRadius: 10,
            titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' },
            bodyFont:  { family: "'Plus Jakarta Sans', sans-serif" },
            callbacks: {
              label: (ctx) => {
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                const pct   = ((ctx.parsed / total) * 100).toFixed(1);
                return ` ${ctx.label}: ${ctx.parsed} pax (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }

  // ── Main fetch ────────────────────────────────────────────
  function loadDashboard() {
    $.ajax({
      url: '../api/dashboard.php',
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        populateStats(data.stats);
        populateBreakdownCards(data.breakdown);
        renderHourlyChart(data.hourly);
        renderTypeChart(data.breakdown);
      },
      error: function (err) {
        console.error('Dashboard API error:', err);
        // Show fallback text on cards
        ['stat-trips','stat-passengers','stat-revenue','stat-active',
         'pax-student','pax-regular','pax-senior'].forEach(function(id) {
          const el = document.getElementById(id);
          if (el) el.textContent = 'N/A';
        });
      }
    });
  }

  // ── Run only on dashboard page ────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('hourlyChart') || document.getElementById('typeChart')) {
      loadDashboard();
    }
  });

})();
