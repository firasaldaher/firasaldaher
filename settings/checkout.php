<?php
require_once __DIR__ . '/../includes/auth.php';

$serviceName = $_GET['service'] ?? 'Premium Service';
$servicePrice = $_GET['price'] ?? '0';

// Handle submission securely
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt'])) {
    
    $uploadDir = __DIR__ . '/../../uploads/receipts/';
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        // Create an .htaccess file to block PHP execution in the upload directory
        file_put_contents($uploadDir . '.htaccess', "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\nOptions -Indexes");
    }

    $file = $_FILES['receipt'];
    
    // 1. Check for basic upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "File upload error code: " . $file['error'];
    } else {
        // 2. Validate File Size (Max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $errorMsg = "File is too large. Maximum size is 5MB.";
        } else {
            // 3. Validate MIME Type securely using finfo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                $errorMsg = "Invalid file format. Only JPG, PNG, and PDF are allowed.";
            } else {
                // 4. Validate Extension (Double check)
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                
                if (!in_array($ext, $allowedExtensions, true)) {
                    $errorMsg = "Invalid file extension.";
                } else {
                    // 5. Generate secure random filename to prevent malicious naming and collisions
                    $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
                    $destination = $uploadDir . $newFileName;
                    
                    // 6. Move the uploaded file securely
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Success! Trigger toast in JS and redirect
                        echo "<script>
                            document.addEventListener('DOMContentLoaded', () => {
                                showToast('Receipt uploaded securely! Verification takes 2-48 hours. Once confirmed, the service will be activated.', 'success');
                                setTimeout(() => {
                                    window.location.href = 'index.php';
                                }, 4000);
                            });
                        </script>";
                        $success = true;
                    } else {
                        $errorMsg = "Failed to save the uploaded file to the server.";
                    }
                }
            }
        }
    }
    
    // Show error toast if any validation failed
    if (isset($errorMsg)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('" . addslashes($errorMsg) . "', 'error');
            });
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Checkout | Admin | Caraway</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  
  <style>
    .checkout-container {
      max-width: 600px;
      margin: 0 auto;
    }
    .whish-logo {
      display: inline-block;
      background: #E63946;
      color: #fff;
      font-weight: 800;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 13px;
      letter-spacing: 0.5px;
    }
    .upload-area {
      border: 2px dashed var(--admin-border);
      padding: 20px;
      text-align: center;
      border-radius: 12px;
      background: #f8f9fa;
      cursor: pointer;
      transition: all 0.2s;
    }
    .upload-area:hover {
      border-color: var(--admin-primary);
      background: rgba(212, 175, 55, 0.05);
    }
    .upload-icon {
      color: var(--admin-primary);
      margin-bottom: 8px;
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content" style="padding: 16px 32px;">
      <div class="page-header" style="max-width: 600px; margin: 0 auto 16px auto;">
        <div>
          <a href="index.php" style="color: var(--admin-text-muted); text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px;">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Settings
          </a>
          <h1 class="page-title" style="font-size: 22px;">Complete Your Activation</h1>
          <div class="page-subtitle" style="margin-top: 2px;">Secure payment via Whish Money</div>
        </div>
      </div>

      <div class="checkout-container" style="max-width: 900px; margin: 0 auto;">
        
        <!-- Order Summary (Full Width Top) -->
        <div class="card-panel" style="padding: 16px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid var(--admin-primary);">
          <div>
            <div style="font-size: 12px; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Service to Activate</div>
            <h2 style="font-size: 16px; margin: 0;"><?php echo htmlspecialchars($serviceName); ?></h2>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 12px; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Total Amount</div>
            <div style="font-family: var(--font-head); font-size: 20px; font-weight: 700; color: var(--admin-primary);">$<?php echo htmlspecialchars($servicePrice); ?></div>
          </div>
        </div>

        <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: stretch;">
          
          <!-- Payment Instructions (Left Column) -->
          <div class="card-panel" style="padding: 24px; flex: 1; min-width: 300px; display: flex; flex-direction: column;">
            <h3 style="font-family: var(--font-head); font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
              <span style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: var(--admin-text); color: #fff; border-radius: 50%; font-size: 13px;">1</span>
              Transfer Payment
            </h3>
            
            <p style="font-size: 14px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 24px; flex-grow: 1;">
              Please transfer the exact total amount <strong>($<?php echo htmlspecialchars($servicePrice); ?>)</strong> using <span class="whish-logo">WHISH MONEY</span> to the following number:
            </p>

            <div style="background: rgba(212, 175, 55, 0.05); border: 1px dashed var(--admin-primary); padding: 20px; border-radius: 8px; text-align: center; margin-top: auto;">
              <div style="font-size: 12px; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Whish Money Number</div>
              <div style="font-family: var(--font-head); font-size: 28px; font-weight: 800; color: var(--admin-text); letter-spacing: 1px;">+961 81 340 801</div>
            </div>
          </div>

          <!-- Verification Form (Right Column) -->
          <div class="card-panel" style="padding: 24px; flex: 1; min-width: 300px; display: flex; flex-direction: column;">
            <h3 style="font-family: var(--font-head); font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
              <span style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: var(--admin-text); color: #fff; border-radius: 50%; font-size: 13px;">2</span>
              Verify Payment
            </h3>

            <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 16px; flex-grow: 1;">
              Upload a screenshot of the receipt so our team can verify the transaction.
            </p>

            <form method="POST" enctype="multipart/form-data" style="margin-bottom: 0; margin-top: auto;">
              
              <label for="receipt" style="display: block; margin-bottom: 16px;">
                <div class="upload-area" id="uploadBox" style="padding: 24px;">
                  <svg class="upload-icon" viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" stroke-width="1.5" fill="none" style="margin-bottom: 12px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                  <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px; color: var(--admin-text);">Click to Upload Receipt</div>
                  <div style="font-size: 12px; color: var(--admin-text-muted);" id="fileName">Supports JPG, PNG, PDF</div>
                </div>
                <input type="file" name="receipt" id="receipt" style="display: none;" accept="image/*,.pdf" required>
              </label>

              <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px; font-weight: 700;">Submit for Verification</button>
            </form>
          </div>

        </div>

      </div>

    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
  <script>
    document.getElementById('receipt').addEventListener('change', function(e) {
      if(e.target.files.length > 0) {
        document.getElementById('fileName').innerText = "Selected: " + e.target.files[0].name;
        document.getElementById('uploadBox').style.borderColor = "var(--admin-success)";
        document.getElementById('uploadBox').style.background = "rgba(16,185,129,0.05)";
        document.querySelector('.upload-icon').style.color = "var(--admin-success)";
      }
    });
  </script>
</body>
</html>
