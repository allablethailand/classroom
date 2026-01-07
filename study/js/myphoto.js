var student_id ;
let classroom_id = 2;

const appState = {
    // PAGE
    currentView: 'galleryView',
    previewSrc: '',
    hasImage: false,
    showDetectButton: false,
    isUploading: false,
    useProfileToDetect: false,

    // Look for old profile photo
    hasOldEmbedding: false,
    oldProfilePhoto: '',
    oldProfilePhotoKey: 0,
    isLoading: false,

    // UPLOAD & DETECT VARIABLE
    matchesData: [
        { bbox: [0.1, 0.15, 0.35, 0.45] },
        { bbox: [0.2, 0.1, 0.4, 0.4] },
        { bbox: [0.15, 0.2, 0.38, 0.48] },
        { bbox: [0.12, 0.18, 0.36, 0.46] }
    ],

    // SELECT PHOTO VARIABLE
    currentPhotoId: null,
    currentPhotoName: '',
    currentReportPhoto: null,
    maxSelectPhoto: 10,
    selectedPhotos: {},
    isPhotoDownloading: false,

    // PHOTO DOWNLOAD VARIABLE
    isDownloading: false,
    downloadProgress: 0,
    downloadTotal: 0,

    // REPORT PHOTO
    showReportModal: false,
    reportReason: '',
    isReporting: false,
    modalZIndex: 1050,
    imageLimit: 10,
    selectedFile: null,
    showInstructionModal: false
};


function switchView(view) {
    appState.currentView = view;
    document.querySelectorAll('.myphoto-page-content').forEach(v => {
        v.classList.add('hidden');
    });

    document.getElementById(view).classList.remove('hidden');
}

function showView(view) {
    appState.currentView = view;

    const views = [
        'galleryView',
        'findMyPhotoView',
        'uploadView',
        'resultsView'
    ];

    views.forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    document.getElementById(view)?.classList.remove('hidden');
}

//  else if($action == 'generate_params_scan') {
//             $event_id = $data->event_id;
//             $register_id = $data->register_id;
//             if ($event_id && $register_id) {
//                 $concatMD5params = md5($event_id . ']C' . $register_id);
//                 echo json_encode(['status' => true, 'data' => $concatMD5params]);
//                 exit();
//             }
//             echo json_encode(['status' => false, 'err_message' => 'Error: Unable to start scanner.']);
//             exit();


    // กรณี REPORT ส่ง $student_id ลง DB ด้วย
    // $student_id = (isset($_SESSION['student_id'])) ? $_SESSION['student_id'] : '';

function useSameUploadedImage() {

}

function triggerMobileGallery() {

}

function triggerCamera() {

}


async function goToPhotoUpload() {
    try {
        let student_id = $('#student_code_id').val();

        let result = await $.ajax({
            url: "/classroom/study/actions/myphoto.php",
            data: { action: "get_student_embedparams", student_id: student_id },
            dataType: "JSON",
            type: "POST"
        });

        if (result && result.status === true && result.data) {
            let url = `/classroom/study/myphoto_upload?${result.data}`;
            window.location.replace(url);
        } else {
            console.error("Invalid response:", result);
            alert("Failed to generate upload parameters.");
        }

    } catch (error) {
        console.error("Failed to get params:", error);
        alert("Unable to prepare photo upload.");
    }
}


async function goToPhotoAlbum() {
    try {
        let student_id = $('#student_code_id').val();

        let result = await $.ajax({
            url: "/classroom/study/actions/myphoto.php",
            data: { action: "generate_classroom_params", student_id: student_id },
            dataType: "JSON",
            type: "POST"
        });

        if (result && result.status === true && result.data) {
            let url = `/classroom/study/myphoto_album?${encodeURIComponent(result.data)}`;
            window.location.replace(url);
        } else {
            console.error("Invalid response:", result);
            alert("Failed to generate upload parameters.");
        }
        
    } catch (error) {
        console.error("Failed to get params:", error);
        alert("Unable to prepare photo upload.");
    }
}

function openInstructionModal() {
    // Show instructions modal
    alert('วิธีการค้นหา: อัปโหลดภาพใบหน้าที่ชัดเจน');
}

$(document).ready(function() {
    // ส่งรูปภาพ PROFILE ไปยัง AI เพื่อค้นหา
    $('#btn-send-profile').on('click', function(e) {
        e.preventDefault();
        student_id = $('#student_id').val();

        $.ajax({
            url: "/classroom/study/actions/myphoto.php",
            data: {
                action: "fetchToAI",
                student_id: student_id
            },
            dataType: "JSON",
            type: "POST",
            success: function (result) {
            console.log(result);
                if (Array.isArray(result)) {
                    renderCourses(result);
                }
            },
            error: function (xhr, status, error) {
                console.error("Failed to load courses:", error);
            },
        });
    });


     $('#btn-send-profile').on('click', function(e) {
        e.preventDefault();
        student_id = $('#student_id').val();

        $.ajax({
            url: "/classroom/study/actions/myphoto.php",
            data: {
                action: "fetchToAI",
                student_id: student_id
            },
            dataType: "JSON",
            type: "POST",
            success: function (result) {
            console.log(result);
                if (Array.isArray(result)) {
                    renderCourses(result);
                }
            },
            error: function (xhr, status, error) {
                console.error("Failed to load courses:", error);
            },
        });
    });


});





