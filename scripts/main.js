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

// FETCH SIDEBAR
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


// ====