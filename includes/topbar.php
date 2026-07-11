<!-- admin/includes/topbar.html -->
<header class="admin-topbar">
  <div class="topbar-left">
    <button class="mobile-toggle" id="mobileToggle">
      <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
  </div>
  <div class="topbar-right" style="display: flex; align-items: center; gap: 20px;">
    <!-- Calendar Icon -->
    <a href="<?php echo (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '' : '../'; ?>appointments/?view=calendar" title="Calendar" style="color: var(--admin-text-muted); display: flex; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='var(--admin-primary)'" onmouseout="this.style.color='var(--admin-text-muted)'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
    </a>

    <!-- Messages Icon -->
    <a href="<?php echo (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '' : '../'; ?>messages/" title="Messages" style="color: var(--admin-text-muted); position: relative; display: flex; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='var(--admin-primary)'" onmouseout="this.style.color='var(--admin-text-muted)'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <span style="position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: var(--admin-danger); border-radius: 50%;"></span>
    </a>
    
    <!-- Push Notifications Icon -->
    <a href="<?php echo (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '' : '../'; ?>notifications/" title="Send Push Notification" style="color: var(--admin-text-muted); display: flex; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='var(--admin-primary)'" onmouseout="this.style.color='var(--admin-text-muted)'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
    </a>
    
    <!-- Settings Icon -->
    <a href="<?php echo (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '' : '../'; ?>settings/" title="Settings" style="color: var(--admin-text-muted); display: flex; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='var(--admin-primary)'" onmouseout="this.style.color='var(--admin-text-muted)'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
    </a>

    <div class="user-profile" style="border-left: 1px solid var(--admin-border); padding-left: 20px; margin-left: 4px;">
      <div class="avatar">A</div>
      <span>Admin</span>
    </div>
  </div>
</header>

