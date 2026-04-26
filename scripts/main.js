// ============================================================
// DATE/TIME CLOCK
// ============================================================
function updateClock() {
  const now = new Date();
  const opts = { weekday:'short', year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
  const topbarDateEl = document.getElementById('topbarDate');
  if (topbarDateEl) topbarDateEl.textContent = now.toLocaleString('en-PH', opts);
}
setInterval(updateClock, 1000);
updateClock();

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
function applySidebarState() {
  const sidebar = document.querySelector('.sidebar-container');
  if (!sidebar) return;

  const isMobile = window.innerWidth <= 991.98;
  const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';

  if (isMobile) {
    document.body.classList.remove('sidebar-collapsed');  
    sidebar.classList.remove('collapsed');
    document.body.classList.toggle('sidebar-open', !collapsed);
    return;
  }

  document.body.classList.remove('sidebar-open');
  document.body.classList.toggle('sidebar-collapsed', collapsed);
  sidebar.classList.toggle('collapsed', collapsed);
}

function toggleSidebar() {
  const isMobile = window.innerWidth <= 991.98;

  if (isMobile) {
    const isOpen = document.body.classList.contains('sidebar-open');
    localStorage.setItem('sidebarCollapsed', String(isOpen));
  } else {
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
    localStorage.setItem('sidebarCollapsed', String(!isCollapsed));
  }

  applySidebarState();
}

window.addEventListener('resize', applySidebarState);

function setActiveSidebarItem() {
  const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
  if (!navItems.length) return;

  const currentPage = (window.location.pathname.split('/').pop() || 'dashboard.html').toLowerCase();

  navItems.forEach((item) => {
    const itemPage = (item.getAttribute('href') || '').split('/').pop().toLowerCase();
    const isActive = itemPage === currentPage;

    item.classList.toggle('active', isActive);

    if (isActive) {
      item.setAttribute('aria-current', 'page');
    } else {
      item.removeAttribute('aria-current');
    }
  });
}

// ============================================================
// FETCH SIDEBAR
// ============================================================

function loadSidebar() {
  fetch('includes/sidebar.html')
    .then((res) => {
      if (!res.ok) throw new Error('Network response was not ok');
      return res.text();
    })
    .then((html) => {
      const placeholder = document.getElementById('sidebar-placeholder');
      if (!placeholder) return;
      placeholder.innerHTML = html;
      setActiveSidebarItem();
      // Re-apply sidebar state now that the sidebar exists
      applySidebarState();
    })
    .catch((err) => console.error('Failed to load sidebar:', err));
}

function loadTopbar() {
  fetch('includes/topbar.html')
    .then((res) => {
      if (!res.ok) throw new Error('Network response was not ok');
      return res.text();
    })
    .then((html) => {
      const placeholder = document.getElementById('topbar-placeholder');
      if (!placeholder) return;
      placeholder.innerHTML = html;
      // Populate the clock now that topbar was injected
      updateClock();
      // Set topbar page name dynamically from document.title or URL
      const pageSpan = placeholder.querySelector('.page span');
      let pageText = (document.title || '').trim();
      if (!pageText) {
        const path = window.location.pathname.split('/').pop() || '';
        pageText = path.replace('.html', '').replace(/[-_]/g, ' ');
        pageText = pageText ? pageText.charAt(0).toUpperCase() + pageText.slice(1) : 'Dashboard';
      }
      if (pageSpan) pageSpan.textContent = pageText;
      // Re-apply sidebar state in case topbar layout depends on sidebar width
      applySidebarState();
    })
    .catch((err) => console.error('Failed to load topbar:', err));
}

document.addEventListener('DOMContentLoaded', () => {
  loadSidebar();
  loadTopbar();
});

// ─── DATA ────────────────────────────────────────────────────────────────

let tripsData = []; // empty at first

function loadTrips() {
    $.ajax({
        url: "../api/trips.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            tripsData = data;

            populateRouteFilter();
            renderTable(tripsData);
        },
        error: function(err) {
            console.error("Failed to fetch trips:", err);
        }
    });
    console.log("Fetching trips...");
}


// ─── RENDER TABLE ────────────────────────────────────────────────────────

function renderTable(data) {
    if (data.length === 0) {
        $('#tripsTableBody').html(`
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size:1.5rem"></i>
                    <div class="mt-2">No trips match the selected filters.</div>
                </td>
            </tr>`);
        return;
    }

    const rows = data.map(function(t) {
        const totalPax    = t.paxStudent + t.paxRegular + t.paxSenior;
        const statusClass = t.status.toLowerCase();
        return `
            <tr>
                <td class="fw-mono" style="color:var(--primary)">${t.id}</td>
                <td>${t.driver}</td>
                <td>${t.vehicle}</td>
                <td>${t.route}</td>
                <td><span class="status-badge ${statusClass}">${t.status}</span></td>
                <td class="fw-bold">${totalPax}</td>
                <td class="fw-mono">${t.fare}</td>
                <td>
                    <button class="btn btn-tbl btn-tbl-view btn-view-trip" data-id="${t.id}">
                        <i class="bi bi-eye-fill"></i> View
                    </button>
                </td>
            </tr>`;
    }).join('');

    $('#tripsTableBody').html(rows);
}


// ─── FILTERS ─────────────────────────────────────────────────────────────

function populateRouteFilter() {
    const uniqueRoutes = [...new Set(tripsData.map(t => t.route))];
    uniqueRoutes.forEach(function(r) {
        $('#filterRoute').append(`<option value="${r}">${r}</option>`);
    });
}

function applyFilters() {
    const status = $('#filterStatus').val();
    const route  = $('#filterRoute').val();
    const result = tripsData.filter(function(t) {
        return (!status || t.status === status) && (!route || t.route === route);
    });
    renderTable(result);
}


// ─── VIEW SWITCHING ──────────────────────────────────────────────────────

function showList() {
    $('#view-detail').removeClass('active');
    $('#view-list').addClass('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showDetail(tripId) {

    const t = tripsData.find(t => t.id === tripId);
    if (!t) return;

    // Header (same as before)
    $('#td-title').text('Trip ' + t.id);
    $('#td-subtitle').text(t.route + ' · ' + t.status);

    // Driver Info (same)
    $('#td-driver-info').html(`
        <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">${t.driver}</span></div>
        <div class="detail-row"><span class="detail-label">License</span><span class="detail-value fw-mono">${t.license}</span></div>
        <div class="detail-row"><span class="detail-label">Contact</span><span class="detail-value">${t.contact}</span></div>
        <div class="detail-row"><span class="detail-label">Status</span><span class="status-badge ${t.status.toLowerCase()}">${t.status}</span></div>
    `);

    // Vehicle Info (same)
    const plate = t.vehicle.split(' ').slice(0, 2).join(' ');
    const type  = t.vehicle.match(/\(([^)]+)\)/)?.[1] || 'N/A';

    $('#td-vehicle-info').html(`
        <div class="detail-row"><span class="detail-label">Plate No.</span><span class="detail-value fw-mono">${plate}</span></div>
        <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value">${type}</span></div>
        <div class="detail-row"><span class="detail-label">Route</span><span class="detail-value">${t.route}</span></div>
    `);

    // Fare Summary
    const totalPax = t.paxStudent + t.paxRegular + t.paxSenior;
    const totalFareValue = Number.parseFloat(String(t.fare).replace(/[^\d.]/g, '')) || 0;
    const averageFare = totalPax > 0 ? totalFareValue / totalPax : 0;

    $('#td-fare-info').html(`
        <div class="fare-summary-total">
            <span class="fare-summary-label">Total Fare Collected</span>
            <span class="fare-summary-value">${t.fare}</span>
        </div>
        <div class="detail-row"><span class="detail-label">Student Passengers</span><span class="detail-value">${t.paxStudent}</span></div>
        <div class="detail-row"><span class="detail-label">Regular Passengers</span><span class="detail-value">${t.paxRegular}</span></div>
        <div class="detail-row"><span class="detail-label">Senior Passengers</span><span class="detail-value">${t.paxSenior}</span></div>
    `);

    // Show loading state for timeline
    $('#td-timeline').html('<div class="text-center p-3">Loading timeline...</div>');
    $('#td-breakdown').html('<div class="text-center p-3">Loading breakdown...</div>');

    // ─── AJAX CALL ─────────────────────────────────────
    $.ajax({
        url: "../api/trip-details.php",
        method: "GET",
        data: { id: tripId },
        dataType: "json",
        success: function(res) {

            // ─── TIMELINE ───────────────────────────────
            const timelineRows = res.timeline.map(function(e) {
                const isBoard = e.action === 'Board';
                return `
                    <div class="timeline-item">
                        <div class="timeline-dot ${isBoard ? 'board' : 'drop'}">${isBoard ? '↑' : '↓'}</div>
                        <div class="timeline-body">
                            <div class="timeline-time">${e.time}</div>
                            <div class="timeline-event">
                                ${isBoard ? '🟠 Boarded' : '🔵 Dropped off'} — ${e.qty} ${e.type}
                            </div>
                            <div class="timeline-stop">
                                <i class="bi bi-geo-alt-fill"></i> ${e.stop}
                            </div>
                        </div>
                    </div>`;
            }).join('');

            $('#td-timeline').html('<div class="timeline">' + timelineRows + '</div>');


            // ─── BREAKDOWN ──────────────────────────────
            const b = res.breakdown;
            const total = (b.Student + b.Regular + b.Senior) || 1;
            const pct = n => ((n / total) * 100).toFixed(1);

            $('#td-breakdown').html(`
                <div class="p-3">
                    <div class="mini-stat-card mb-3">
                        Student: ${b.Student} (${pct(b.Student)}%)
                    </div>
                    <div class="mini-stat-card mb-3">
                        Regular: ${b.Regular} (${pct(b.Regular)}%)
                    </div>
                    <div class="mini-stat-card">
                        Senior: ${b.Senior} (${pct(b.Senior)}%)
                    </div>
                </div>
            `);
        },
        error: function(err) {
            console.error(err);
            $('#td-timeline').html('<div class="text-danger p-3">Failed to load timeline</div>');
        }
    });

    // Switch view
    $('#view-list').removeClass('active');
    $('#view-detail').addClass('active');
}


// ─── INIT ────────────────────────────────────────────────────────────────

$(function() {
    loadTrips(); // instead of renderTable(tripsData)

    $('#filterStatus, #filterRoute').on('change', applyFilters);

    $(document).on('click', '.btn-view-trip', function() {
        showDetail($(this).data('id'));
    });

    $('#btnBack').on('click', showList);
});
