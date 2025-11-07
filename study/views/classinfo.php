<?php

session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}

define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';
require_once $base_include . '/classroom/study/actions/student_func.php';

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$course_type = isset($_GET['course_type']) ? $_GET['course_type'] : null;


$course_detail = select_data("cc.course_type,
    otl.trn_id AS course_id,
    otl.trn_subject AS course_name,
    otl.picture_title AS course_cover,
    otl.trn_location AS course_location,
    otl.trn_longitude AS cousre_longitude,
    otl.trn_latitude AS course_location,
    otl.trn_purpose AS course_description,
    otl.trn_from_time AS course_timestart,
    otl.trn_to_time AS course_timeend,
    otl.trn_by AS course_instructor,
    DATE_FORMAT(otl.trn_date, '%d/%m/%Y') AS course_date,
    LENGTH(REPLACE(trn_by, ' ', '')) - LENGTH(REPLACE(REPLACE(trn_by, ' ', ''), ',', '')) + 1 AS trn_count_by",
    "classroom_course AS cc JOIN ot_training_list AS otl on cc.course_ref_id = otl.trn_id",
    "WHERE otl.trn_id = '{$course_id}' AND cc.status = 0");

$course_data = $course_detail[0];

$dateString = $course_data['course_date'];
// Create DateTime object from original format (d/m/Y)
$date = DateTime::createFromFormat('d/m/Y', $dateString);
$formattedDate = $date->format('F j, Y');

$trainers_array = explode(', ', $course_data['course_instructor']);
$course_file = select_data("*", "ot_training_file", "WHERE trn_id='58' and status_del = 0 ORDER BY trn_file_id ASC");

// var_dump($course_file);

// --- Initialization ---
$file_key_arr = [];          
$file_path_arr = [];    
$file_key_arr_script = []; 

function attach_ext($ext) {
    // Convert extension to lowercase for consistent matching
    $ext = strtolower($ext);

    // Default values
    $type_info = [
        'name' => 'General File',
        'color' => '#808080', // Grey
        'icon' => 'fa-file'   // Default file icon
    ];

    switch ($ext) {
        case 'doc':
        case 'docx':
            $type_info['name'] = 'Word Document';
            $type_info['color'] = '#2B579A'; // Microsoft Word Blue
            $type_info['icon'] = 'fa-file-word';
            break;
        case 'xls':
        case 'xlsx':
            $type_info['name'] = 'Excel Spreadsheet';
            $type_info['color'] = '#1E7145'; // Microsoft Excel Green
            $type_info['icon'] = 'fa-file-excel';
            break;
        case 'ppt':
        case 'pptx':
            $type_info['name'] = 'PowerPoint Presentation';
            $type_info['color'] = '#B7472A'; // Microsoft PowerPoint Orange/Red
            $type_info['icon'] = 'fa-file-powerpoint';
            break;
        case 'pdf':
            $type_info['name'] = 'PDF Document';
            $type_info['color'] = '#E62719'; // Adobe Red
            $type_info['icon'] = 'fa-file-pdf';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            $type_info['name'] = 'Image File';
            $type_info['color'] = '#4C8CD2'; // Blue
            $type_info['icon'] = 'fa-file-image';
            break;
        case 'zip':
        case 'rar':
            $type_info['name'] = 'Archive File';
            $type_info['color'] = '#FFD700'; // Gold
            $type_info['icon'] = 'fa-file-archive';
            break;
    }

    return $type_info;
}

if (!empty($course_file)) {
    // NOTE: It's safer to use prepared statements, but adapting your original query structure for now.
    foreach ($course_file as $row_file) {
        $file_info = pathinfo($row_file['trn_file_path']);
        $file_ext = strtolower($file_info['extension']);
        $type_info = attach_ext($file_ext);
        
        $script = array(
            "type_name" => $type_info['name'],
            "base_color" => $type_info['color'],
            "icon_class" => $type_info['icon'],
            
            // Existing fields
            "filename" => $row_file['trn_file_name'], // trn_file_name often contains the full name including extension, check your DB
            "full_filename" => $row_file['trn_file_name'],
            "caption" => ($row_file['trn_file_name'] ? $row_file['trn_file_name'] : ''),
            "downloadUrl" => GetUrl($row_file['trn_file_path']),
            "url" => "lib/tmp_del_fake.php", // Assumed placeholder
            "key" => $row_file['trn_file_id'],
            "id" => $row_file['trn_file_id']
        );
        
        array_push($file_key_arr, $row_file['trn_file_id']);
        array_push($file_path_arr, GetUrl($row_file['trn_file_path'])); 
        array_push($file_key_arr_script, $script);
    }
}
// var_dump($course_data);

?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Classroom Info • ORIGAMI SYSTEM</title>
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
    <script src="/classroom/study/js/classinfo.js?v=<?php echo time(); ?>"  type="text/javascript"></script>
    <script>
        var course_id = <?php echo json_encode($course_id); ?>;
        var course_type = <?php echo json_encode($course_type); ?>;
        var classroomId = <?php echo json_encode($class_id); ?>;
    </script>
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
                    <h2 class="text-2xl font-bold text-text-primary mb-2"><?= $course_data['course_name']; ?></h2>
                    <div class="flex items-center text-sm text-text-secondary mb-1">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span><?= $formattedDate; ?></span>
                    </div>
                    <div class="flex items-center text-sm text-text-secondary mb-3">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><?= $course_data['course_timestart']; ?> - <?= $course_data['course_timeend']; ?> </span>
                    </div>
                </div>
                <div class="status-success">
                    Class Information
                </div>
            </div>
            

            <?php if (!empty($course_data['course_location']) && $course_data['trn_type'] != 'inhouse'): ?>

            <!-- Location Section -->
            <div class="flex items-start space-x-3 mb-4">
                <svg class="w-5 h-5 text-primary mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
                <!-- IF there's location -->
                <div class="flex-1">
                    <p class="font-medium text-text-primary"><?= $course_data['course_location'] ?></p>
                    <p class="text-sm text-text-secondary">123 Business Plaza, San Francisco, CA 94105</p>
                    <button class="text-sm text-primary hover:text-primary-600 transition-smooth mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                        </svg>
                        Get Directions
                    </button>
                </div>
                <!-- End of location -->
            </div>
            <?php endif ?>

            <!-- Map Preview -->
            <div class="bg-primary-50 rounded-lg h-48 mb-4 flex items-center justify-center relative overflow-hidden">
                <img src=<?= $course_data['picture_title'] ?> 
                     alt="Map view of Conference Room A location in Downtown San Francisco" 
                     class="w-full h-full object-cover"
                     onerror="this.src='/images/training.jpg'">
                <div class="absolute inset-0 bg-primary bg-opacity-20 flex items-center justify-center">
                    <!-- <button class="bg-surface text-primary px-4 py-2 rounded-lg shadow-elevation-2 hover:shadow-elevation-3 transition-smooth">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        View Full Map
                    </button> -->
                </div>
            </div>

        </div>

        <!-- Event Description Card -->
        <div class="card mb-6">
            <h3 class="text-lg font-semibold text-text-primary mb-3">Description</h3>
            <p class="text-text-secondary leading-relaxed mb-4">
                <?= isset($course_data['course_description']) ? $course_data['course_description'] : ' ไม่พบข้อมูล ' ?>
            </p>
        </div>

        <!-- Attendees Card -->
        <div class="card mb-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Instructors ( <?= $course_data['trn_count_by'] ?> )</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- FOREACH DATA INSIDE TRN INSTRUCTOR -->
                    <?php foreach ($trainers_array as $index => $trainer_name) { ?>
                    <!-- Attendee 1 -->
                    <div class="flex items-center space-x-3">
                        <img src="" 
                            alt="Profile photo of Sarah Johnson, Marketing Director" 
                            class="w-10 h-10 rounded-full object-cover"
                            onerror="this.src='/images/logo_academy_169x150.png'; this.onerror=null;">
                        <div>
                            <p class="text-sm font-medium text-text-primary"><?= $trainer_name ?></p>
                            <!-- <p class="text-xs text-text-secondary">Marketing Dir.</p> -->
                        </div>
                    </div>
                    <!-- END FOR EACH -->
                    <?php } ?>
            </div>
            <button style="display: none;" class="text-sm text-primary hover:text-primary-600 transition-smooth mt-4 flex items-center">
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
            <h3 class="text-lg font-semibold text-text-primary mb-4">Attachments ( <?php echo count($file_key_arr_script) ?> ) </h3>
            <div class="space-y-3">
                <!-- Attachment 1 -->
                <?php foreach ($file_key_arr_script as $file) { ?>
                    
                    <div class="flex items-center space-x-3 p-3 bg-background rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-error-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-error-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-text-primary"> <?= htmlspecialchars($file['full_filename']) ?></p>
                            <p class="text-xs text-text-secondary"><?= htmlspecialchars($file['type_name']) ?></p>
                        </div>
                        <script>
                            
                            var downloadUrl = "<?php echo htmlspecialchars($file['downloadUrl'], ENT_QUOTES); ?>";
                            var fileNameUrl = "<?php echo htmlspecialchars($file['filename'], ENT_QUOTES); ?>";
                        </script>
                        <button class="text-primary hover:text-primary-600 transition-smooth" onclick="downloadFileAsBlob(downloadUrl, fileNameUrl)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </button>
                    </div>
               
                <?php } ?>

                
            </div>
        </div>

        <!-- 
            END IF THERE'S FILE ATTACHMENT 
         -->


        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-4 mb-20">

            <!-- redirect to academy -->
            <button class="btn-primary flex items-center justify-center space-x-2" onclick="redirectCurreculum(course_id, course_type, classroomId)">
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
        function downloadFileAsBlob(fileUrl, fileName = '') {

            if (!fileName) {
                console.error('❌ Invalid file URL.');
                alert('Invalid file URL.');
                return;
            }

            // console.log('fileUrl', fileUrl);
            // console.log('fileName', fileName);

            const proxyUrl = '<?php echo $base_path; ?>/lib/proxy.php?url=' + encodeURIComponent(fileUrl);
            fetch(proxyUrl)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.blob();
                })
                .then(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobUrl);
                    console.log('✅ Download successful.');
                })
                .catch(err => {
                    console.error('❌ Download failed:', err);
                    alert('Download failed.');
            });
        }

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
        });
    </script>

    <?php require_once('component/footer.php'); ?>
</body>
</html>