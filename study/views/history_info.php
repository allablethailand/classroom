<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Order Info</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .app-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }
        
        /* Mobile: Full screen with padding */
        @media (max-width: 767px) {
            .app-container {
                padding: 0;
            }
            .content-wrapper {
                padding: 15px;
            }
        }
        
        /* Desktop: Card style with shadow */
        @media (min-width: 768px) {
            body {
                padding: 40px 20px;
            }
            .app-container {
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.08);
                min-height: auto;
            }
            .content-wrapper {
                padding: 30px 40px;
            }
        }
        
        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 15px;
            border-bottom: 1px solid #e8ecef;
        }
        @media (min-width: 768px) {
            .header {
                padding: 25px 40px;
            }
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #6b7280;
            font-size: 16px;
            font-weight: 500;
        }
        .back-btn {
            color: #6b7280;
            cursor: pointer;
            transition: color 0.2s;
        }
        .back-btn:hover {
            color: #374151;
        }
        .bell-icon {
            color: #374151;
            font-size: 20px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .bell-icon:hover {
            color: #4a9fd8;
        }
        
        /* Status Section */
        .status-section {
            text-align: left;
            margin-bottom: 30px;
        }
        .status-icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #4a9fd8 0%, #3d8bc7 100%);
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .status-icon-wrapper i {
            color: white;
            font-size: 32px;
        }
        .status-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }
        .status-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }
        .order-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 14px;
        }
        .order-badges span {
            display: flex;
            align-items: center;
        }
        .badge-separator {
            margin: 0 4px;
        }
        .order-info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .order-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .order-info-item i {
            color: #9ca3af;
        }
        .success-alert {
            background: #f0f9ff;
            border: 1px solid #e0f2fe;
            padding: 14px 18px;
            border-radius: 10px;
            color: #0c4a6e;
            font-size: 14px;
        }
        
        /* Section Title */
        .section-header {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
            margin-top: 35px;
        }
        
        /* Order Items */
        .order-items-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .order-item-card {
            display: flex;
            gap: 15px;
            padding: 16px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .order-item-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-color: #d1d5db;
        }
        .item-thumbnail {
            width: 70px;
            height: 70px;
            background: #f3f4f6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }
        .item-placeholder {
            background: linear-gradient(135deg, #7cb342 0%, #689f38 100%);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .item-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .item-title {
            font-size: 15px;
            color: #111827;
            font-weight: 500;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .item-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .item-quantity {
            color: #6b7280;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .item-quantity i {
            color: #9ca3af;
        }
        .not-found-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fef2f2;
            color: #dc2626;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .not-found-label i {
            font-size: 11px;
        }
        
        /* Rider Section */
        .rider-section {
            margin-top: 35px;
            padding-top: 30px;
            border-top: 2px solid #f3f4f6;
        }
        .rider-details-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .rider-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .rider-detail-row:last-child {
            border-bottom: none;
        }
        .rider-label {
            color: #6b7280;
            font-size: 15px;
            font-weight: 400;
        }
        .rider-value {
            color: #111827;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .copy-btn {
            color: #9ca3af;
            cursor: pointer;
            font-size: 17px;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            color: #4a9fd8;
            transform: scale(1.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .status-title {
                font-size: 24px;
            }
            .order-badges {
                font-size: 13px;
            }
            .item-thumbnail {
                width: 60px;
                height: 60px;
            }
            .item-title {
                font-size: 14px;
            }
        }
        
        /* Toast notification */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #111827;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .toast-notification.show {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <i class="fa fa-chevron-left back-btn"></i>
                <span>Completed Order Info</span>
            </div>
            <i class="fa fa-bell-o bell-icon"></i>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Status Section -->
            <div class="status-section">
                <div class="status-icon-wrapper">
                    <i class="fa fa-check"></i>
                </div>
                
                <div class="status-header">
                    <div class="status-title">Completed</div>
                    <div class="order-badges">
                        <span>Order #1234</span>
                        <span class="badge-separator">•</span>
                        <span>Shoprite Okota</span>
                    </div>
                </div>
                
                <div class="order-info-row">
                    <div class="order-info-item">
                        <i class="fa fa-shopping-bag"></i>
                        <span>500 pieces</span>
                    </div>
                    <div class="order-info-item">
                        <i class="fa fa-clock-o"></i>
                        <span>12:55 am</span>
                    </div>
                    <div class="order-info-item">
                        <i class="fa fa-calendar"></i>
                        <span>12-07-2024</span>
                    </div>
                </div>

                <div class="success-alert">
                    This order has been completed successfully.
                </div>
            </div>

            <!-- Order Details -->
            <div class="section-header">Order Details (3/4)</div>

            <div class="order-items-list">
                <div class="order-item-card">
                    <div class="item-thumbnail">
                        <div class="item-placeholder">MILO</div>
                    </div>
                    <div class="item-content">
                        <div class="item-title">
                            Cardberry cocoa & sugar blue with icing and colored
                        </div>
                        <div class="item-meta">
                            <div class="item-quantity">
                                <i class="fa fa-shopping-bag"></i>
                                <span>12 pieces</span>
                            </div>
                            <div class="not-found-label">
                                <i class="fa fa-times-circle"></i>
                                <span>Not Found</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-item-card">
                    <div class="item-thumbnail">
                        <div class="item-placeholder">MILO</div>
                    </div>
                    <div class="item-content">
                        <div class="item-title">
                            Cardberry cocoa & sugar blue with icing and colored
                        </div>
                        <div class="item-meta">
                            <div class="item-quantity">
                                <i class="fa fa-shopping-bag"></i>
                                <span>12 pieces</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-item-card">
                    <div class="item-thumbnail">
                        <div class="item-placeholder">MILO</div>
                    </div>
                    <div class="item-content">
                        <div class="item-title">
                            Cardberry cocoa & sugar blue with icing and colored
                        </div>
                        <div class="item-meta">
                            <div class="item-quantity">
                                <i class="fa fa-shopping-bag"></i>
                                <span>12 pieces</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-item-card">
                    <div class="item-thumbnail">
                        <div class="item-placeholder">MILO</div>
                    </div>
                    <div class="item-content">
                        <div class="item-title">
                            Cardberry cocoa & sugar blue with icing and colored
                        </div>
                        <div class="item-meta">
                            <div class="item-quantity">
                                <i class="fa fa-shopping-bag"></i>
                                <span>12 pieces</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rider Details -->
            <div class="rider-section">
                <div class="section-header">Rider Details</div>
                
                <div class="rider-details-list">
                    <div class="rider-detail-row">
                        <div class="rider-label">Rider's Name</div>
                        <div class="rider-value">Oladimeji Adedoyin</div>
                    </div>
                    
                    <div class="rider-detail-row">
                        <div class="rider-label">Rider's Phone no</div>
                        <div class="rider-value">
                            <span>08034221323</span>
                            <i class="fa fa-copy copy-btn" onclick="copyPhoneNumber()"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast-notification"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script>
        function copyPhoneNumber() {
            const phoneNumber = '08034221323';
            
            // Modern clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(phoneNumber).then(function() {
                    showToast('Phone number copied!');
                }).catch(function() {
                    fallbackCopy(phoneNumber);
                });
            } else {
                fallbackCopy(phoneNumber);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showToast('Phone number copied!');
            } catch (err) {
                showToast('Failed to copy');
            }
            
            document.body.removeChild(textarea);
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            
            setTimeout(function() {
                toast.classList.remove('show');
            }, 2500);
        }
    </script>
</body>
</html> 