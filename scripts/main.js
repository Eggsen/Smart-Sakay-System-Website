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

// Note: initialize page-specific behavior below in a single block to avoid
// duplicate global handlers that run on other pages.

// ============================================================
// DRIVERS-PAGE DATA
// ============================================================

let driversData = [];

function loadDrivers() {
    $.ajax({
        url: "../api/drivers.php",
        method: "GET",
        dataType: "json",
        success: function (data) {
            driversData = data;
            populateDriverStatusFilter();
            renderDrivers(data);
        },
        error: function (err) {
            console.error("Failed to fetch drivers:", err);
        }
    });
}

function renderDrivers(data) {
    if (data.length === 0) {
        $('#driversTableBody').html(`
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    No drivers found.
                </td>
            </tr>
        `);
        return;
    }

    const rows = data.map((d, index) => {
        return `
            <tr class="text-center"  data-id="${d.id}">
                <td>${index + 1}</td>
                <td>${d.name}</td>
                <td class="fw-mono">${d.license}</td>
                <td>${d.contact}</td>
                <td>
                    <span class="status-badge ${d.status.toLowerCase().replace(' ', '-')}">
                        ${d.status}
                    </span>
                </td>
                <td>
                    <button class="btn btn-tbl btn-tbl-edit">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-tbl btn-tbl-delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    $('#driversTableBody').html(rows);
}

function populateDriverStatusFilter() {
    const select = $('#filterDriverStatus');
    if (!select.length) return;
    // Clear custom options except the first "All Status"
    select.find('option').not(':first').remove();
    const unique = [...new Set(driversData.map(d => d.status))];
    unique.forEach(s => {
        if (s == null) return;
        select.append(`<option value="${s}">${s}</option>`);
    });
}

function applyDriverFilters() {
    const q = ($('#driverSearchInput').val() || '').toString().trim().toLowerCase();
    const status = ($('#filterDriverStatus').val() || '').toString();

    const filtered = driversData.filter(d => {
        const matchesStatus = !status || d.status === status;
        const idStr = (d.id || '').toString().toLowerCase();
        const nameStr = (d.name || '').toString().toLowerCase();
        const matchesQuery = !q || idStr.includes(q) || nameStr.includes(q);
        return matchesStatus && matchesQuery;
    });

    renderDrivers(filtered);
}

// drivers initialization moved to unified init block

// ============================================================
// VEHICLES-PAGE DATA
// ============================================================

let vehiclesData = [];

function loadVehicles() {
    $.ajax({
        url: "../api/vehicles.php",
        method: "GET",
        dataType: "json",

        success: function (data) {
            vehiclesData = data;
            populateVehicleFilters();
            renderVehicles(data);
        },

        error: function (err) {
            console.error("Failed to fetch vehicles:", err);
        }
    });
}

// Render vehicles as table

function renderVehicles(data) {

    if (data.length === 0) {
        $('#vehiclesTableBody').html(`
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    No vehicles found.
                </td>
            </tr>
        `);
        return;
    }

    const rows = data.map((v, index) => {
        return `
            <tr class="text-center" data-id="${v.id}">
                <td>${index + 1}</td>
                <td class="fw-mono">${v.plate}</td>
                <td>${v.type}</td>
                <td>${v.capacity}</td>
                <td>${v.color || '-'}</td>
                <td>
                    <span class="status-badge ${v.status.toLowerCase().replace(' ', '-')}">
                        ${v.status}
                    </span>
                </td>
                <td class="fw-mono">${v.created_at || '-'}</td>
            </tr>
        `;
    }).join('');

    $('#vehiclesTableBody').html(rows);
}

// vehicles initialization moved to unified init block

function populateVehicleFilters() {
    const statusSel = $('#filterVehicleStatus');
    const typeSel = $('#filterVehicleType');
    if (!statusSel.length && !typeSel.length) return;

    // Populate unique statuses (keep existing first option)
    if (statusSel.length) {
        const existing = statusSel.find('option').map((i,el) => $(el).text()).get();
        const uniqueStatus = [...new Set(vehiclesData.map(v => v.status).filter(s => s))];
        uniqueStatus.forEach(s => {
            if (existing.includes(s)) return;
            statusSel.append(`<option value="${s}">${s}</option>`);
        });
    }

    // Populate unique types
    if (typeSel.length) {
        typeSel.find('option').not(':first').remove();
        const uniqueTypes = [...new Set(vehiclesData.map(v => v.type).filter(t => t))];
        uniqueTypes.forEach(t => {
            typeSel.append(`<option value="${t}">${t}</option>`);
        });
    }
}

function applyVehicleFilters() {
    const q = ($('#vehicleSearchInput').val() || '').toString().trim().toLowerCase();
    const status = ($('#filterVehicleStatus').val() || '').toString();
    const type = ($('#filterVehicleType').val() || '').toString();

    const filtered = vehiclesData.filter(v => {
        const matchesStatus = !status || (v.status === status);
        const matchesType = !type || (v.type === type);
        const idStr = (v.id || '').toString().toLowerCase();
        const plateStr = (v.plate || '').toString().toLowerCase();
        const matchesQuery = !q || idStr.includes(q) || plateStr.includes(q);
        return matchesStatus && matchesType && matchesQuery;
    });

    renderVehicles(filtered);
}

// DRIVER MODAL CREATE - SAVE - DELETE (CRUD)

// ADD DRIVER
$('#btnAddDriver').on('click', function () {

    $('#driverEditId').val('');
    $('#driverName').val('');
    $('#driverLicense').val('');
    $('#driverContact').val('');
    $('#driverStatus').val('Active');

    $('#driverModalTitle').text('Add Driver');

    $('#driverModal').modal('show');
});

// EDIT DRIVER handler delegated in unified init block below

// SAVE DRIVER
function saveDriver() {
    const id = $('#driverEditId').val();
    const name = $('#driverName').val().trim();
    const license = $('#driverLicense').val().trim();
    const contact = $('#driverContact').val().trim();
    const status = $('#driverStatus').val();

    // Simple validation
    if (!name || !license || !contact) {
        alert("Please fill all fields.");
        return;
    }

    $.ajax({
        url: "../api/save-driver.php",
        method: "POST",
        data: {
            id: id,
            name: name,
            license: license,
            contact: contact,
            status: status
        },
        success: function (res) {
            alert(res);

            // Close modal
            const modalEl = document.getElementById('driverModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            loadDrivers(); // reload table
        },
        error: function (err) {
            console.error(err);
        }
    });
}

// DELETE DRIVER
function confirmDelete(type, id, label) {

    $('#deleteMsg').text(`Delete "${label}"? This action cannot be undone.`);

    $('#deleteConfirmBtn').off('click');

    $('#deleteConfirmBtn').on('click', function () {

        let url = "";

        // Decide API based on type
        if (type === 'driver') url = "../api/delete-driver.php";
        if (type === 'vehicle') url = "../api/delete-vehicle.php";
        if (type === 'route') url = "../api/delete-route.php";

        $.ajax({
            url: url,
            method: "POST",
            data: { id: id },

            success: function (res) {

                // Reload correct UI
                if (type === 'driver') loadDrivers();
                if (type === 'vehicle') loadVehicles();
                if (type === 'route') loadRoutes();

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();

                // Show success message
                showToast(label + " deleted.");
            },

            error: function (err) {
                console.error(err);
                showToast("Delete failed.");
            }
        });

    });

    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// DELETE BTN delegated in unified init block below

// ============================================================
// ROUTES & STOPS-PAGE DATA
// ============================================================

let routesData = [];

function loadRoutes() {
    $.ajax({
        url: "../api/routes.php",
        method: "GET",
        dataType: "json",

        success: function (data) {
            routesData = data;
            renderRoutes(data);
        }
    });
}

function openRouteModal() {

    // Reset form
    $('#routeModalTitle').text("Add Route");
    $('#routeEditId').val('');
    $('#routeName').val('');
    $('#routeStops').val('');
    $('#routeDistance').val('');

    new bootstrap.Modal(document.getElementById('routeModal')).show();
}

function renderRoutes(data) {

    if (data.length === 0) {
        $('#routesContainer').html(`<p class="text-muted">No routes found.</p>`);
        return;
    }

    const cards = data.map(route => {

        // Generate stops list
        const stopsHTML = route.stops.map((stopName, index) => {
            return `
                <li class="stop-item">
                    <span class="stop-num">${index + 1}</span>
                    ${stopName}
                </li>
            `;
        }).join('');

        return `
        <div class="col-xl-4 col-md-6">
            <div class="card route-card rounded-4">

                <div class="card-header route-card-header d-flex justify-content-between align-items-center py-3 px-3 rounded-top-4">
                    <span class="route-card-title">
                        <i class="bi bi-signpost-2-fill me-2"></i>${route.name}
                    </span>

                    <div class="d-flex gap-1">
                        <button class="btn-tbl btn-tbl-edit" data-id="${route.id}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="btn-tbl btn-tbl-delete" data-id="${route.id}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body route-card-body d-flex flex-column">

                    <div class="mb-3" style="font-size:0.77rem;color:var(--text-muted)">
                        <i class="bi bi-rulers me-1"></i>${route.distance} km &nbsp;|&nbsp;
                        <i class="bi bi-geo-alt-fill me-1"></i>${route.stops.length} stops
                    </div>

                    <ul class="stop-list">
                        ${stopsHTML}
                    </ul>

                </div>
            </div>
        </div>
        `;
    }).join('');

    $('#routesContainer').html(cards);
}

// routes initialization moved to unified init block

// ROUTES MODAL CREATE - SAVE - DELETE (CRUD)

// Save route
function saveRoute() {

    const id = $('#routeEditId').val();
    const name = $('#routeName').val();
    const stops = $('#routeStops').val();
    const distance = $('#routeDistance').val();

    if (!name || !stops || !distance) {
        alert("Please fill all fields");
        return;
    }

    const url = id ? "../api/update-route.php" : "../api/add-route.php";

    $.ajax({
        url: url,
        method: "POST",
        data: {
            id: id,
            name: name,
            stops: stops,
            distance: distance
        },

        success: function (res) {

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('routeModal')).hide();

            // Reload routes
            loadRoutes();

            console.log(res);
        },

        error: function (err) {
            console.error(err);
        }
    });
}

// Edit/Delete route handlers delegated in unified init block below

// Unified page init: only attach handlers and load data for the present page
document.addEventListener('DOMContentLoaded', function () {
    // Trips page
    if ($('#tripsTableBody').length || $('#filterStatus').length) {
        loadTrips();
        $('#filterStatus, #filterRoute').on('change', applyFilters);
        if ($('#tripsTableBody').length) {
            $('#tripsTableBody').on('click', '.btn-view-trip', function () {
                showDetail($(this).data('id'));
            });
        }
        $('#btnBack').on('click', showList);
    }

    // Drivers page
    if ($('#driversTableBody').length) {
        loadDrivers();
        $('#driverSearchInput').on('input', applyDriverFilters);
        $('#filterDriverStatus').on('change', applyDriverFilters);

        // edit driver (delegated)
        $('#driversTableBody').on('click', '.btn-tbl-edit', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const driver = driversData.find(d => d.id == id);
            if (!driver) return;
            $('#driverEditId').val(driver.id);
            $('#driverName').val(driver.name);
            $('#driverLicense').val(driver.license);
            $('#driverContact').val(driver.contact);
            $('#driverStatus').val(driver.status);
            $('#driverModalTitle').text('Edit Driver');
            const modal = new bootstrap.Modal(document.getElementById('driverModal'));
            modal.show();
        });

        // delete driver (delegated)
        $('#driversTableBody').on('click', '.btn-tbl-delete', function () {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const name = row.find('td:eq(1)').text();
            confirmDelete('driver', id, name);
        });
    }

    // Vehicles page
    if ($('#vehiclesTableBody').length) {
        loadVehicles();
        $('#vehicleSearchInput').on('input', applyVehicleFilters);
        $('#filterVehicleStatus, #filterVehicleType').on('change', applyVehicleFilters);
    }

    // Routes page
    if ($('#routesContainer').length) {
        loadRoutes();

        // edit route (delegated)
        $('#routesContainer').on('click', '.btn-tbl-edit', function () {
            const id = $(this).data('id');
            const route = routesData.find(r => r.id == id);
            if (!route) return;
            $('#routeModalTitle').text('Edit Route');
            $('#routeEditId').val(route.id);
            $('#routeName').val(route.name);
            $('#routeDistance').val(route.distance);
            $('#routeStops').val(route.stops.join(', '));
            new bootstrap.Modal(document.getElementById('routeModal')).show();
        });

        // delete route (delegated)
        $('#routesContainer').on('click', '.btn-tbl-delete', function () {
            const id = $(this).data('id');
            confirmDelete('route', id, 'Route');
        });
    }
});