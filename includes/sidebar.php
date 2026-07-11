<?php 
$admin_dir = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') ? '' : '../'; 
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="admin-sidebar" id="sidebar">
  <a href="<?php echo $admin_dir; ?>../index.php" class="sidebar-brand">
    33<span>°</span>NORTH
  </a>
  <nav class="sidebar-nav">
    <a href="<?php echo $admin_dir ? $admin_dir : './'; ?>" class="nav-item <?php echo ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
      Dashboard
    </a>
    <a href="<?php echo $admin_dir; ?>pos/" class="nav-item <?php echo ($current_dir === 'pos') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
      POS (Point of Sale)
    </a>
    <a href="<?php echo $admin_dir; ?>orders/" class="nav-item <?php echo ($current_dir === 'orders') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
      Orders History
    </a>
    <a href="<?php echo $admin_dir; ?>products/" class="nav-item <?php echo ($current_dir === 'products') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
      Products / Menu
    </a>
    <a href="<?php echo $admin_dir; ?>inventory/" class="nav-item <?php echo ($current_dir === 'inventory') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
      Inventory
    </a>
    <a href="<?php echo $admin_dir; ?>accounting/" class="nav-item <?php echo ($current_dir === 'accounting') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
      Accounting
    </a>
    <a href="<?php echo $admin_dir; ?>reports/" class="nav-item <?php echo ($current_dir === 'reports') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
      Reports
    </a>
    <a href="<?php echo $admin_dir; ?>staff/" class="nav-item <?php echo ($current_dir === 'staff') ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
      Staff
    </a>
  </nav>
  <div style="padding: 24px; margin-top: auto;">
    <a href="<?php echo $admin_dir; ?>logout.php" class="btn btn-outline" style="width: 100%; justify-content: center; border: 1px solid var(--admin-border); color: var(--admin-danger);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      Logout
    </a>
  </div>
</aside>
