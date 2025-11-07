<?php
// บรรทัดแรกสุดของไฟล์
session_start();
// โค้ดส่วนอื่นๆ ของหน้าจะเริ่มที่นี่
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Classroom • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <link rel="stylesheet" href="/dist/css/select2.min.css">
    <link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
    <link rel="stylesheet" href="/dist/css/jquery-ui.css">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/classinfo.css?v=<?php echo time(); ?>">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/menu.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>
</head>

<body class="bg-background min-h-screen">

    <?php require_once("component/header.php") ?>
    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-6">
        <h1 class="heading-1" data-lang="classroomdetail">รายละเอียดคลาส</h1>
            <div class="divider-1">
                <span></span>
            </div>
        <!-- Event Header Card -->
        <div class="card mb-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-text-primary mb-2">Team Strategy Meeting</h2>
                    <div class="flex items-center text-sm text-text-secondary mb-1">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>January 2, Sunday</span>
                    </div>
                    <div class="flex items-center text-sm text-text-secondary mb-3">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>9:00 AM - 10:30 AM PDT</span>
                    </div>
                </div>
                <div class="status-success">
                    Class Information
                </div>
            </div>
            
            <!-- Location Section -->
            <div class="flex items-start space-x-3 mb-4">
                <svg class="w-5 h-5 text-primary mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-text-primary">Conference Room A, Downtown Office</p>
                    <p class="text-sm text-text-secondary">123 Business Plaza, San Francisco, CA 94105</p>
                    <button class="text-sm text-primary hover:text-primary-600 transition-smooth mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                        </svg>
                        Get Directions
                    </button>
                </div>
            </div>

            <!-- Map Preview -->
            <div class="bg-primary-50 rounded-lg h-48 mb-4 flex items-center justify-center relative overflow-hidden">
                <img src="https://images.unsplash.com/photo-1693625700727-b548ca8f5679" 
                     alt="Map view of Conference Room A location in Downtown San Francisco" 
                     class="w-full h-full object-cover"
                     onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                <div class="absolute inset-0 bg-primary bg-opacity-20 flex items-center justify-center">
                    <button class="bg-surface text-primary px-4 py-2 rounded-lg shadow-elevation-2 hover:shadow-elevation-3 transition-smooth">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        View Full Map
                    </button>
                </div>
            </div>
        </div>

        <!-- Event Description Card -->
        <div class="card mb-6">
            <h3 class="text-lg font-semibold text-text-primary mb-3">Description</h3>
            <p class="text-text-secondary leading-relaxed mb-4">
                Join us for our quarterly team strategy meeting where we'll discuss upcoming projects, review Q4 performance metrics, and align on our goals for the new year. This session will cover market analysis, resource allocation, and strategic initiatives for the next quarter.
            </p>
            <p class="text-text-secondary leading-relaxed">
                Please bring your project reports and be prepared to discuss your team's priorities. We'll also be introducing new collaboration tools and processes to improve our workflow efficiency.
            </p>
        </div>

        <!-- Attendees Card -->
        <div class="card mb-6">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Instructors (8)</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Attendee 1 -->
                <div class="flex items-center space-x-3">
                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_180276e9a-1762274703516.png" 
                         alt="Profile photo of Sarah Johnson, Marketing Director" 
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                    <div>
                        <p class="text-sm font-medium text-text-primary">Sarah Johnson</p>
                        <p class="text-xs text-text-secondary">Marketing Dir.</p>
                    </div>
                </div>
                
                <!-- Attendee 2 -->
                <div class="flex items-center space-x-3">
                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_154695753-1762273284727.png" 
                         alt="Profile photo of Michael Chen, Product Manager" 
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                    <div>
                        <p class="text-sm font-medium text-text-primary">Michael Chen</p>
                        <p class="text-xs text-text-secondary">Product Mgr.</p>
                    </div>
                </div>
                
                <!-- Attendee 3 -->
                <div class="flex items-center space-x-3">
                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1333f08e0-1762274062613.png" 
                         alt="Profile photo of Emily Rodriguez, UX Designer" 
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                    <div>
                        <p class="text-sm font-medium text-text-primary">Emily Rodriguez</p>
                        <p class="text-xs text-text-secondary">UX Designer</p>
                    </div>
                </div>
                
                <!-- Attendee 4 -->
                <div class="flex items-center space-x-3">
                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1c6498fc9-1762249010546.png" 
                         alt="Profile photo of David Thompson, Engineering Lead" 
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                    <div>
                        <p class="text-sm font-medium text-text-primary">David Thompson</p>
                        <p class="text-xs text-text-secondary">Eng. Lead</p>
                    </div>
                </div>
            </div>
            
            <button class="text-sm text-primary hover:text-primary-600 transition-smooth mt-4 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                View All Attendees
            </button>
        </div>

        <!-- 
            IF THERE'S FILE ATTACHMENT 
         -->
        <!-- Attachments Card -->
        <div class="card mb-6">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Attachments</h3>
            <div class="space-y-3">
                <!-- Attachment 1 -->
                <div class="flex items-center space-x-3 p-3 bg-background rounded-lg">
                    <div class="flex-shrink-0 w-10 h-10 bg-error-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-error-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">Q4 Performance Report.pdf</p>
                        <p class="text-xs text-text-secondary">2.4 MB • Added 2 hours ago</p>
                    </div>
                    <button class="text-primary hover:text-primary-600 transition-smooth">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Attachment 2 -->
                <div class="flex items-center space-x-3 p-3 bg-background rounded-lg">
                    <div class="flex-shrink-0 w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">Meeting Agenda.docx</p>
                        <p class="text-xs text-text-secondary">1.2 MB • Added yesterday</p>
                    </div>
                    <button class="text-primary hover:text-primary-600 transition-smooth">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- 
            END IF THERE'S FILE ATTACHMENT 
         -->


        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-4 mb-20">
            <button class="btn-primary flex items-center justify-center space-x-2" onclick="window.location.href='create_event.html'">
                <i class="fas fa-external-link-alt w-5 h-5"></i>
                <!-- <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg> -->
                <span>Join this class</span>
            </button>
            
            <button class="bg-surface border border-border text-text-primary px-4 py-2 rounded-md font-medium transition-smooth hover:bg-background flex items-center justify-center space-x-2">
                <i class="fas fa-bell w-5 h-5"></i>
                <!-- <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg> -->
                <span>Notify Me</span>
            </button>
        </div>
    </main>
    <!-- JavaScript for Modal and Interactions -->
    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function deleteEvent() {
            // Simulate event deletion
            alert('Event deleted successfully!');
            closeDeleteModal();
            window.location.href = 'event_dashboard.html';
        }

        // Add event listener to delete button
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButton = document.querySelector('.bg-error-50');
            if (deleteButton) {
                deleteButton.addEventListener('click', openDeleteModal);
            }

            // Close modal when clicking outside
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });
        });
    </script>
    <script id="dhws-dataInjector" src="../public/dhws-data-injector.js"></script>

    <?php require_once('component/footer.php'); ?>
</body>
</html>