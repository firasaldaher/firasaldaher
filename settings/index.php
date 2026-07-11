<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Settings & Billing | Admin | 33° NORTH</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Settings & Billing</h1>
          <div class="page-subtitle">Manage salon preferences and subscription plans.</div>
        </div>
      </div>

      <div style="display: flex; flex-wrap: wrap; gap: 32px;">
        
        <!-- Left Column: Active Subscriptions & Add-ons -->
        <div style="flex: 2; min-width: 340px;">
          
          <h2 style="font-family: var(--font-head); font-size: 18px; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Active Subscriptions
          </h2>
          
          <!-- Hosting & Domain (Active) -->
          <div class="card-panel" style="display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--admin-success); margin-bottom: 24px; padding: 24px;">
            <div>
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <h3 style="font-size: 18px; margin: 0; color: var(--admin-text);">Hosting & Domain Name (3 Years)</h3>
                <span class="badge badge-success">Active</span>
              </div>
              <p style="font-size: 14px; color: var(--admin-text-muted); margin: 0;">Purchased: <strong style="color: var(--admin-text);">May 29, 2026</strong> &nbsp;&bull;&nbsp; Next renewal: <strong style="color: var(--admin-text);">May 29, 2029</strong></p>
            </div>
            <button class="btn btn-outline" style="font-size: 14px; padding: 8px 16px;" onclick="alert('Redirecting to hosting management...')">Manage</button>
          </div>

          <h2 style="font-family: var(--font-head); font-size: 18px; margin-top: 48px; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 12 12 12 8"></polyline><line x1="9" y1="11" x2="12" y2="8"></line><line x1="15" y1="11" x2="12" y2="8"></line></svg>
            Available Upgrades
          </h2>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
            
            <!-- Analytics -->
            <div class="card-panel" style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(245, 158, 11, 0.2);">
              <div style="flex-grow: 1; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                  <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: var(--admin-warning); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                  </div>
                  <div style="font-size: 20px; font-weight: 700;">$80<span style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);">/mo</span></div>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px;">Advanced Chart Analytics</h3>
                <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 0;">Unlock detailed visual charts, historical comparisons, and exportable reports.</p>
              </div>
              <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(4px);">
                <button class="btn" style="width: 100%; justify-content: center; background: rgba(245,158,11,0.1); color: var(--admin-warning); font-weight: 700;" onclick="window.location.href='checkout.php?service=Advanced Chart Analytics&price=80'">Subscribe</button>
              </div>
            </div>

            <!-- Support -->
            <div class="card-panel" style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(212, 175, 55, 0.2);">
              <div style="flex-grow: 1; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                  <div style="width: 48px; height: 48px; background: rgba(212, 175, 55, 0.1); color: var(--admin-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                  </div>
                  <div style="font-size: 20px; font-weight: 700;">$200<span style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);">/mo</span></div>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px;">Priority Technical Support</h3>
                <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 0;">24/7 priority access to our engineering team for instant resolutions.</p>
              </div>
              <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(4px);">
                <button class="btn" style="width: 100%; justify-content: center; background: rgba(212,175,55,0.1); color: var(--admin-primary-hover); font-weight: 700;" onclick="window.location.href='checkout.php?service=Priority Technical Support&price=200'">Subscribe</button>
              </div>
            </div>

            <!-- Security -->
            <div class="card-panel" style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(16, 185, 129, 0.2);">
              <div style="flex-grow: 1; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                  <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: var(--admin-success); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                  </div>
                  <div style="font-size: 20px; font-weight: 700;">$50<span style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);">/mo</span></div>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px;">Advanced Security Shield</h3>
                <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 0;">WAF, DDoS protection, and daily malware scans.</p>
              </div>
              <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(4px);">
                <button class="btn" style="width: 100%; justify-content: center; background: rgba(16,185,129,0.1); color: var(--admin-success); font-weight: 700;" onclick="window.location.href='checkout.php?service=Advanced Security Shield&price=50'">Subscribe</button>
              </div>
            </div>

            <!-- Backups -->
            <div class="card-panel" style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(59, 130, 246, 0.2);">
              <div style="flex-grow: 1; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                  <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                  </div>
                  <div style="font-size: 20px; font-weight: 700;">$30<span style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);">/mo</span></div>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px;">Automated Cloud Backups</h3>
                <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 0;">Daily database & file backups to secure cloud servers.</p>
              </div>
              <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(4px);">
                <button class="btn" style="width: 100%; justify-content: center; background: rgba(59,130,246,0.1); color: #3b82f6; font-weight: 700;" onclick="window.location.href='checkout.php?service=Automated Cloud Backups&price=30'">Subscribe</button>
              </div>
            </div>

            <!-- Marketing -->
            <div class="card-panel" style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(139, 92, 246, 0.2);">
              <div style="flex-grow: 1; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                  <div style="width: 48px; height: 48px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                  </div>
                  <div style="font-size: 20px; font-weight: 700;">$100<span style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);">/mo</span></div>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px;">SEO & Marketing Suite</h3>
                <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 0;">Automated email, SMS reminders, and SEO tools.</p>
              </div>
              <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(4px);">
                <button class="btn" style="width: 100%; justify-content: center; background: rgba(139,92,246,0.1); color: #8b5cf6; font-weight: 700;" onclick="window.location.href='checkout.php?service=SEO %26 Marketing Suite&price=100'">Subscribe</button>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Column: General Settings -->
        <div style="flex: 1; min-width: 320px;">
          <h2 style="font-family: var(--font-head); font-size: 18px; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            General Settings
          </h2>
          
          <div class="card-panel" style="padding: 32px;">
            <form onsubmit="event.preventDefault(); alert('Settings saved successfully!');">
              <div class="form-group" style="margin-bottom: 24px;">
                <label style="margin-bottom: 8px; color: var(--admin-text); font-weight: 700;">Salon Name</label>
                <input type="text" class="form-control" value="33° NORTH" style="font-size: 15px; padding: 14px 16px;" required>
              </div>
              <div class="form-group" style="margin-bottom: 24px;">
                <label style="margin-bottom: 8px; color: var(--admin-text); font-weight: 700;">Contact Email</label>
                <input type="email" class="form-control" value="info@33northlb.com" style="font-size: 15px; padding: 14px 16px;" required>
              </div>
              <div class="form-group" style="margin-bottom: 24px;">
                <label style="margin-bottom: 8px; color: var(--admin-text); font-weight: 700;">Currency</label>
                <select class="form-control" style="font-size: 15px; padding: 14px 16px; cursor: pointer;">
                  <option value="USD" selected>USD ($)</option>
                  <option value="LBP">LBP (ل.ل)</option>
                  <option value="EUR">EUR (€)</option>
                </select>
              </div>
              <div class="form-group" style="margin-bottom: 24px;">
                <label style="margin-bottom: 8px; color: var(--admin-text); font-weight: 700;">Timezone</label>
                <select class="form-control" style="font-size: 15px; padding: 14px 16px; cursor: pointer;">
                  <option value="Asia/Beirut" selected>Asia/Beirut (GMT+3)</option>
                  <option value="UTC">UTC</option>
                </select>
              </div>
              
              <hr style="border: none; border-top: 1px solid var(--admin-border); margin: 32px 0;">
              
              <h3 style="font-size: 16px; margin-bottom: 20px; font-weight: 700;">Notifications</h3>
              <label style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--admin-primary);">
                <span style="font-size: 15px; color: var(--admin-text);">Email me on new booking</span>
              </label>
              <label style="display: flex; align-items: center; gap: 12px; margin-bottom: 32px; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--admin-primary);">
                <span style="font-size: 15px; color: var(--admin-text);">Email me daily reports</span>
              </label>
              
              <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px; font-weight: 700;">Save Changes</button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
</body>
</html>
