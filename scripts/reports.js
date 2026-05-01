// ============================================================
// REPORTS PAGE — Live Data + Charts
// ============================================================

(function () {

  // ── CSS variable helpers ──────────────────────────────────
  const rootStyle = getComputedStyle(document.documentElement);
  const cssVar = (name) => rootStyle.getPropertyValue(name).trim();

  const COLOR_PRIMARY   = cssVar('--primary');     // #FF7043
  const COLOR_SECONDARY = cssVar('--secondary');   // #0b486f
  const COLOR_SUCCESS   = cssVar('--success');     // #2ecc71
  const COLOR_WARNING   = cssVar('--warning');     // #f39c12
  const COLOR_DANGER    = cssVar('--danger');      // #e74c3c

  // Rich multi-color palette for route slices (cycles if more routes added)
  const ROUTE_PALETTE = [
    COLOR_PRIMARY,
    COLOR_SECONDARY,
    COLOR_SUCCESS,
    COLOR_WARNING,
    COLOR_DANGER,
    '#9b59b6',
    '#1abc9c',
  ];

  // ── Chart instances ───────────────────────────────────────
  let revenueChart    = null;
  let tripsRouteChart = null;

  // ── Number formatter ─────────────────────────────────────
  function fmt(n) {
    return Number(n).toLocaleString('en-PH');
  }

  // ── Populate summary cards ────────────────────────────────
  function populateSummary(summary) {
    const passEl  = document.getElementById('rpt-passengers');
    const revEl   = document.getElementById('rpt-revenue');
    const tripsEl = document.getElementById('rpt-trips');

    if (passEl)  passEl.textContent  = fmt(summary.totalPassengers);
    if (revEl)   revEl.textContent   = '₱ ' + fmt(summary.totalRevenue.toFixed(2));
    if (tripsEl) tripsEl.textContent = fmt(summary.totalTrips);
  }

  // ── Revenue by Route — Horizontal Bar Chart ───────────────
  function renderRevenueChart(data) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    if (revenueChart) revenueChart.destroy();

    // Shorten long route names for readability
    const shortLabels = data.labels.map(l =>
      l.length > 24 ? l.substring(0, 22) + '…' : l
    );

    // Gradient fill: one solid colour per bar using palette index
    const bgColors = data.labels.map((_, i) =>
      ROUTE_PALETTE[i % ROUTE_PALETTE.length] + 'cc'
    );
    const borderColors = data.labels.map((_, i) =>
      ROUTE_PALETTE[i % ROUTE_PALETTE.length]
    );

    revenueChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: shortLabels,
        datasets: [{
          label: 'Revenue (₱)',
          data: data.values,
          backgroundColor: bgColors,
          borderColor: borderColors,
          borderWidth: 1.5,
          borderRadius: 6,
          borderSkipped: false,
        }]
      },
      options: {
        indexAxis: 'y',                   // ← horizontal bar
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
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
              // Show full label in tooltip
              title: (items) => data.labels[items[0].dataIndex],
              label: (ctx)   => ` ₱ ${fmt(ctx.parsed.x.toFixed(2))}`
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: { color: cssVar('--border'), lineWidth: 1 },
            ticks: {
              color: cssVar('--text-muted'),
              font: { family: "'DM Mono', monospace", size: 11 },
              callback: (v) => '₱' + fmt(v)
            }
          },
          y: {
            grid: { display: false },
            ticks: {
              color: cssVar('--text-main'),
              font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' }
            }
          }
        }
      }
    });
  }

  // ── Trips per Route — Pie Chart ───────────────────────────
  function renderTripsRouteChart(data) {
    const ctx = document.getElementById('tripsRouteChart');
    if (!ctx) return;
    if (tripsRouteChart) tripsRouteChart.destroy();

    const bgColors    = data.labels.map((_, i) => ROUTE_PALETTE[i % ROUTE_PALETTE.length] + 'cc');
    const hoverColors = data.labels.map((_, i) => ROUTE_PALETTE[i % ROUTE_PALETTE.length] + 'ee');

    tripsRouteChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: data.labels,
        datasets: [{
          data: data.values,
          backgroundColor: bgColors,
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
              padding: 14,
              font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
              color: cssVar('--text-main'),
              // Shorten long labels in legend
              generateLabels: (chart) => {
                const ds = chart.data.datasets[0];
                return chart.data.labels.map((label, i) => ({
                  text: label.length > 20 ? label.substring(0, 18) + '…' : label,
                  fillStyle: ds.backgroundColor[i],
                  strokeStyle: ds.borderColor,
                  lineWidth: ds.borderWidth,
                  hidden: false,
                  index: i,
                }));
              }
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
              title: (items) => data.labels[items[0].dataIndex],
              label: (ctx) => {
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                const pct   = ((ctx.parsed / total) * 100).toFixed(1);
                return ` ${ctx.parsed} trip${ctx.parsed !== 1 ? 's' : ''} (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }

  // ── Main fetch ────────────────────────────────────────────
  function loadReports() {
    $.ajax({
      url: '../api/reports.php',
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        populateSummary(data.summary);
        renderRevenueChart(data.revenueByRoute);
        renderTripsRouteChart(data.tripsPerRoute);
      },
      error: function (err) {
        console.error('Reports API error:', err);
        ['rpt-passengers', 'rpt-revenue', 'rpt-trips'].forEach(function (id) {
          const el = document.getElementById(id);
          if (el) el.textContent = 'N/A';
        });
      }
    });
  }

  // ── Run only on reports page ──────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('revenueChart') || document.getElementById('tripsRouteChart')) {
      loadReports();
    }
  });

})();
