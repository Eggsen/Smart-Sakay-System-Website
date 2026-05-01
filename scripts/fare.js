$(document).ready(function () {
    loadFarePolicies();
    loadGlobalDiscounts();
});

function loadFarePolicies() {
    $.ajax({
        url: "../api/fare-policy.php",
        method: "GET",
        dataType: "json",
        success: function (data) {
            renderFareCards(data);
            populateFareRouteFilter(data);
        },
        error: function (err) {
            console.error("Failed to load fare policies:", err);
            $('#fareMatrixContainer').html('<div class="col-12"><div class="text-danger">Error loading fares.</div></div>');
        }
    });
}

function populateFareRouteFilter(data) {
    const filter = $('#routeFilter');
    // Keep the first option ("-- All Routes --") and clear the rest
    filter.find('option:not(:first)').remove();

    data.forEach(function (route) {
        filter.append(`<option value="${route.id}">${route.name}</option>`);
    });

    filter.on('change', function () {
        const selectedId = $(this).val();
        if (!selectedId) {
            renderFareCards(data);
        } else {
            const filtered = data.filter(r => r.id == selectedId);
            renderFareCards(filtered);
        }
    });
}

function renderFareCards(data) {
    if (data.length === 0) {
        $('#fareMatrixContainer').html('<div class="col-12"><p class="text-muted">No fare policies found.</p></div>');
        return;
    }

    // Add row class if it's not there to enable grid layout like in routes.html
    if (!$('#fareMatrixContainer').hasClass('row')) {
        $('#fareMatrixContainer').addClass('row g-4');
    }

    const colClass = data.length === 1 ? 'col-xl-6 col-lg-8 col-md-10 col-12' : 'col-xl-4 col-md-6 col-12';

    const cards = data.map(route => {
        const stopsHTML = route.stops.map((stop, index) => {
            let fareBadge = '';
            if (stop.fare !== null) {
                fareBadge = `
                    <div class="text-end" style="font-size: 0.8rem;">
                        <span class="text-muted d-inline-block me-2" style="min-width: 65px;">Reg: <strong class="text-success">₱${parseFloat(stop.fare).toFixed(2)}</strong></span>
                        <span class="text-muted d-inline-block me-2" style="min-width: 65px;">Stu: <strong class="text-primary">₱${parseFloat(stop.student_fare).toFixed(2)}</strong></span>
                        <span class="text-muted d-inline-block" style="min-width: 65px;">Snr: <strong class="text-warning">₱${parseFloat(stop.senior_fare).toFixed(2)}</strong></span>
                    </div>
                `;
            } else if (index === 0) {
                fareBadge = `<span class="float-end text-muted small">Origin</span>`;
            } else {
                fareBadge = `<span class="float-end text-muted small">N/A</span>`;
            }

            return `
                <li class="stop-item d-flex justify-content-between align-items-center" style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <span class="stop-num me-2" style="background: var(--primary); color: white; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">${index + 1}</span>
                        ${stop.stop_name}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        ${fareBadge}
                        <button class="btn btn-tbl btn-tbl-edit btn-edit-fare" 
                                data-stop-id="${stop.stop_id}" 
                                data-route-id="${route.id}" 
                                data-stop-name="${stop.stop_name}" 
                                data-fare="${stop.fare || ''}" 
                                data-student="${stop.student_fare || ''}" 
                                data-senior="${stop.senior_fare || ''}" 
                                data-is-origin="${index === 0}"
                                title="Edit Stop">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </div>
                </li>
            `;
        }).join('');

        return `
        <div class="${colClass}">
            <div class="card route-card rounded-4 h-100 shadow-sm border-0">
                <div class="card-header route-card-header d-flex justify-content-between align-items-center py-3 px-3 rounded-top-4">
                    <span class="route-card-title fw-bold">
                        <i class="bi bi-signpost-2-fill me-2 text-primary"></i>${route.name}
                    </span>
                </div>
                <div class="card-body route-card-body d-flex flex-column p-4">
                    <div class="mb-3 d-flex justify-content-between align-items-center" style="font-size:0.85rem; color:#6c757d;">
                        <span><i class="bi bi-rulers me-1"></i>${route.distance} km</span>
                        <span><i class="bi bi-geo-alt-fill me-1"></i>${route.stops.length} stops</span>
                    </div>
                    <ul class="stop-list" style="list-style:none; padding:0; margin:0; font-size: 0.95rem;">
                        ${stopsHTML}
                    </ul>
                </div>
            </div>
        </div>
        `;
    }).join('');

    $('#fareMatrixContainer').html(cards);
}

// Event Delegation for Edit Button
$(document).on('click', '.btn-edit-fare', function() {
    const btn = $(this);
    const stopId = btn.data('stop-id');
    const routeId = btn.data('route-id');
    const stopName = btn.data('stop-name');
    const fare = btn.data('fare');
    const student = btn.data('student');
    const senior = btn.data('senior');
    const isOrigin = btn.data('is-origin');

    $('#editStopId').val(stopId);
    $('#editRouteId').val(routeId);
    $('#editStopName').val(stopName);
    
    if (isOrigin) {
        $('#fareInputsContainer').hide();
        $('#editRegularFare').val('');
        $('#editStudentFare').val('');
        $('#editSeniorFare').val('');
    } else {
        $('#fareInputsContainer').show();
        $('#editRegularFare').val(fare);
        $('#editStudentFare').val(student);
        $('#editSeniorFare').val(senior);
    }
    
    const modal = new bootstrap.Modal(document.getElementById('fareEditModal'));
    modal.show();
});

function saveFarePolicy() {
    const stopId = $('#editStopId').val();
    const routeId = $('#editRouteId').val();
    const stopName = $('#editStopName').val();
    const regularFare = $('#editRegularFare').val();
    const studentFare = $('#editStudentFare').val();
    const seniorFare = $('#editSeniorFare').val();

    if (!stopName) {
        alert("Stop name is required.");
        return;
    }

    $.ajax({
        url: "../api/update-fare.php",
        method: "POST",
        data: {
            stop_id: stopId,
            route_id: routeId,
            stop_name: stopName,
            regular_fare: regularFare,
            student_fare: studentFare,
            senior_fare: seniorFare
        },
        success: function(res) {
            bootstrap.Modal.getInstance(document.getElementById('fareEditModal')).hide();
            loadFarePolicies();
        },
        error: function(err) {
            console.error("Failed to update fare:", err);
            alert("Error updating fare.");
        }
    });
}

// Global Discount Handlers
function loadGlobalDiscounts() {
    $.ajax({
        url: "../api/get-discount.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            $('.fare-type-card.student .fare-type-value').text(data.student_discount + '%');
            $('.fare-type-card.senior .fare-type-value').text(data.senior_discount + '%');
            $('.fare-type-card.regular .fare-type-value').text('0%'); // Regular has 0% discount
            
            // Pre-fill modal
            $('#editStudentDiscount').val(data.student_discount);
            $('#editSeniorDiscount').val(data.senior_discount);
        },
        error: function(err) {
            console.error("Failed to load discounts:", err);
        }
    });
}

function openFareModal() {
    const modal = new bootstrap.Modal(document.getElementById('globalDiscountModal'));
    modal.show();
}

function saveGlobalDiscount() {
    const studentPct = $('#editStudentDiscount').val();
    const seniorPct = $('#editSeniorDiscount').val();

    if (studentPct === '' || seniorPct === '') {
        alert("Please enter both discount percentages.");
        return;
    }

    $.ajax({
        url: "../api/update-discount.php",
        method: "POST",
        data: {
            student_discount: studentPct,
            senior_discount: seniorPct
        },
        success: function(res) {
            bootstrap.Modal.getInstance(document.getElementById('globalDiscountModal')).hide();
            // Reload all data to reflect new fares
            loadFarePolicies();
            loadGlobalDiscounts();
        },
        error: function(err) {
            console.error("Failed to update discounts:", err);
            alert("Error updating global discounts.");
        }
    });
}

