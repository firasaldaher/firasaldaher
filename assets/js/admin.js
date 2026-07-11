// ============================================================
// 33° NORTH — Admin JS
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  // Mobile Sidebar Toggle
  const initSidebarToggle = () => {
    const toggleBtn = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
      });
    }
  };

  // Setup active links based on current URL path
  const setupActiveLinks = () => {
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
      const href = item.getAttribute('href');
      // Simple exact or startsWith matching
      if (currentPath.includes(href) && href !== '../index.html') {
        item.classList.add('active');
      } else if (currentPath.endsWith('/admin/') && href.includes('index.html')) {
        item.classList.add('active');
      }
    });
  };

  // Allow time for includes to load before initializing JS that relies on them
  setTimeout(() => {
    initSidebarToggle();
    setupActiveLinks();
  }, 300);
});

// ============================================================
// Professional Toast & Confirm System
// ============================================================
window.showToast = (message, type = 'success') => {
  let container = document.getElementById('admin-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'admin-toast-container';
    container.className = 'admin-toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `admin-toast ${type} admin-toast-${type}`;
  
  let iconHtml = '<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
  if (type === 'error') {
    iconHtml = '<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
  }

  toast.innerHTML = `
    <div class="admin-toast-icon">${iconHtml}</div>
    <div class="admin-toast-msg">${message}</div>
  `;
  container.appendChild(toast);

  // Trigger animation
  setTimeout(() => toast.classList.add('show'), 10);

  // Remove after 3s
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
};

// Override window.alert
window.alert = (msg) => {
  showToast(msg, 'success');
};

// Custom Confirm
window.customConfirm = (msg, callback) => {
  let overlay = document.getElementById('admin-modal-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'admin-modal-overlay';
    overlay.className = 'admin-modal-overlay';
    overlay.innerHTML = `
      <div class="admin-modal">
        <div class="admin-modal-icon">
          <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <h3 class="admin-modal-title">Are you sure?</h3>
        <p class="admin-modal-text">${msg}</p>
        <div class="admin-modal-actions">
          <button class="btn btn-outline" id="admin-modal-cancel">Cancel</button>
          <button class="btn" style="background: var(--admin-danger); color: #fff;" id="admin-modal-confirm">Yes, proceed</button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);
  }

  const textEl = overlay.querySelector('.admin-modal-text');
  textEl.innerText = msg;
  
  overlay.classList.add('show');

  const btnCancel = document.getElementById('admin-modal-cancel');
  const btnConfirm = document.getElementById('admin-modal-confirm');

  const newCancel = btnCancel.cloneNode(true);
  const newConfirm = btnConfirm.cloneNode(true);
  btnCancel.replaceWith(newCancel);
  btnConfirm.replaceWith(newConfirm);

  newCancel.addEventListener('click', () => {
    overlay.classList.remove('show');
  });

  newConfirm.addEventListener('click', () => {
    overlay.classList.remove('show');
    if (callback) callback();
  });
};

// Intercept forms using onsubmit="return confirm('...')"
document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll('form[onsubmit*="confirm"]');
  forms.forEach(form => {
    const originalOnsubmit = form.getAttribute('onsubmit');
    const msgMatch = originalOnsubmit.match(/confirm\(['"](.*?)['"]\)/);
    if (msgMatch && msgMatch[1]) {
      const msg = msgMatch[1];
      form.removeAttribute('onsubmit');
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        customConfirm(msg, () => {
          form.submit();
        });
      });
    }
  });
});
