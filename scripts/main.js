// ============================================================
// DATE/TIME CLOCK
// ============================================================
function updateClock() {
  const now = new Date();
  const opts = { weekday:'short', year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
  document.getElementById('topbarDate').textContent = now.toLocaleString('en-PH', opts);
}
setInterval(updateClock, 1000);
updat

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
document.addEventListener('DOMContentLoaded', applySidebarState);


// ====