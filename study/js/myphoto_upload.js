// Configuration
const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];
const MAX_FILES = 50; // จำกัดให้ user อัปโหลดครั้งละไม่เกิน 50 ไฟล์

let selectedFiles = [];
let currentPage = 1;
let isLoading = false;
let hasMore = true;

let gameState = {
    classroom_id: null,
    student_id: null,
    member_id: null,
};

$(document).ready(function() {
    getPermission();
});

function getPermission() {
    $(".loader").addClass("active");
    $.ajax({
        url: "/classroom/study/actions/myphoto_upload.php",
        type: "POST",
        data: {
            action: 'getPermission',
            classroom_data: $("#classroom_data").val()
        },
        dataType: "JSON",
        success: function(result) {
            $(".loader").removeClass("active");
            if(result.status == true) {
                gameState.classroom_id = result.classroom_id;
                gameState.student_id = result.student_id;
                gameState.member_id = result.member_id;
                
                $("#classroom_id").val(result.classroom_id);
                $("#student_id").val(result.student_id);
                
                // Setup event header
                setupEvent(result.classroom_data);
                
                // Build upload UI
                buildUploadUI();
                
                // Load existing photos
                loadUserPhotos();
            } else {
                nonePermissionAlert();
            }
        },
        error: function(xhr, status, error) {
            $(".loader").removeClass("active");
            console.error('Get permission error:', error);
            nonePermissionAlert();
        }
    });
}

function setupEvent(classroom_data) {
    $(".classroom-title").text(classroom_data.classroom_name || "-");
    $(".classroom-location").text(classroom_data.classroom_location || "-");
    $(".classroom-time").text(
        (classroom_data.classroom_date || "") + " " + (classroom_data.classroom_time || "")
    );
    $(document).attr("title", classroom_data.classroom_name + " • ORIGAMI SYSTEM");
    
    if (classroom_data.classroom_poster) {
        const posterUrl = classroom_data.classroom_poster;
        const $poster = $('#posterPreload');
        $poster.off('load error');
        $poster.attr('src', posterUrl).on('load', function () {
            $('.event-header')[0].style.setProperty('--event-bg', `url("${posterUrl}")`);
        });
    }
}

function buildUploadUI() {
    const uploadHTML = `
        <div class="container-fluid" style="padding: 20px;">
            <div class="row">
                <div class="col-md-12">
                    <!-- Upload Section -->
                    <div class="upload-section text-center"
                        style="border:2px dashed #ccc; padding:40px 30px; border-radius:8px; cursor:pointer; background:#fafafa; transition:all 0.3s; margin-bottom:30px;"
                        id="uploadDropZone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:48px; color:#667eea; margin-bottom:15px;"></i>
                        <h4 style="margin:10px 0; color:#333;">อัปโหลดรูปภาพของคุณ</h4>
                        <p style="color:#999; margin:5px 0;">คลิกหรือลากไฟล์มาวางที่นี่ (สูงสุด ${MAX_FILES} ไฟล์)</p>
                        <p style="color:#999; margin:5px 0; font-size:12px;">รองรับ: JPG, JPEG, PNG, BMP (สูงสุด 100MB ต่อไฟล์)</p>
                        <input type="file" id="uploadInput" multiple style="display:none;" accept="image/jpeg,image/jpg,image/png,image/bmp">
                        <button class="btn btn-primary btn-upload-select" style="margin-top:15px; padding:10px 30px; border-radius:25px;">
                            <i class="fas fa-file-image"></i> เลือกไฟล์
                        </button>
                        
                        <!-- Pending Files Preview -->
                        <div class="pending-files" style="margin-top:20px; display:none;">
                            <div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:15px;">
                                <h5 style="margin:0 0 10px 0; color:#333;">
                                    <i class="fas fa-list"></i> ไฟล์ที่เลือก:
                                </h5>
                                <ul class="file-list" style="text-align:left; max-height:150px; overflow-y:auto; margin:10px 0; padding-left:20px; list-style:none;">
                                </ul>
                                <button class="btn btn-success btn-upload-start" style="margin-top:10px; padding:10px 30px; border-radius:25px;">
                                    <i class="fas fa-upload"></i> อัปโหลด (<span class="file-count">0</span>) ไฟล์
                                </button>
                                <button class="btn btn-danger btn-clear-files" style="margin-top:10px; padding:10px 30px; border-radius:25px; margin-left:10px;">
                                    <i class="fas fa-times"></i> ยกเลิก
                                </button>
                                <div class="upload-status text-info" style="margin-top:15px; display:none; font-weight:600;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- My Photos Section -->
                    <div class="my-photos-section">
                        <h3 style="margin-bottom:20px;">
                            <i class="fas fa-images"></i> รูปภาพของฉัน
                            <span class="badge badge-primary" style="font-size:14px; margin-left:10px;">
                                <span class="my-photos-count">0</span> รูป
                            </span>
                        </h3>
                        
                        <div id="myPhotosGrid" class="row">
                            <!-- Photos will be loaded here -->
                        </div>
                        
                        <div id="loading-area" class="text-center" style="display:none; padding:20px;">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                            <p style="margin-top:10px; color:#666;">กำลังโหลดรูปภาพ...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(uploadHTML);
    bindUploadEvents();
}

function bindUploadEvents() {
    // Click to select files
    $(document).on("click", ".btn-upload-select", function () {
        $("#uploadInput").click();
    });
    
    $(document).on("change", "#uploadInput", function () {
        addFilesToList(this.files);
    });
    
    // Drag & Drop
    $(document).on("dragover", "#uploadDropZone", function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).css("background", "#f0f4ff");
    });
    
    $(document).on("dragleave", "#uploadDropZone", function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).css("background", "#fafafa");
    });
    
    $(document).on("drop", "#uploadDropZone", function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).css("background", "#fafafa");
        addFilesToList(e.originalEvent.dataTransfer.files);
    });
    
    // Clear files
    $(document).on("click", ".btn-clear-files", function() {
        selectedFiles = [];
        renderFileList();
    });
    
    // Start upload
    $(document).on("click", ".btn-upload-start", function() {
        startUpload();
    });
}

function addFilesToList(files) {
    let rejected = [];
    
    for (let i = 0; i < files.length; i++) {
        let file = files[i];
        
        if (selectedFiles.length >= MAX_FILES) {
            alert(`สามารถอัปโหลดได้สูงสุด ${MAX_FILES} ไฟล์`);
            break;
        }
        
        let fileName = file.name;
        let ext = fileName.split('.').pop().toLowerCase();
        
        if (!ALLOWED_EXTENSIONS.includes(ext)) {
            rejected.push(`${fileName}: ประเภทไฟล์ไม่ถูกต้อง`);
            continue;
        }
        
        if (file.size > MAX_FILE_SIZE) {
            let sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            rejected.push(`${fileName}: ขนาดไฟล์ใหญ่เกินไป (${sizeMB}MB)`);
            continue;
        }
        
        if (!file.type.startsWith('image/')) {
            rejected.push(`${fileName}: ไม่ใช่ไฟล์รูปภาพ`);
            continue;
        }
        
        selectedFiles.push(file);
    }
    
    if (rejected.length > 0) {
        swal({
            type: 'warning',
            title: "มีไฟล์บางส่วนไม่สามารถเลือกได้",
            html: true,
            text: rejected.join('<br>'),
            confirmButtonText: 'ตรวจสอบแล้ว'
        });
    }
    
    renderFileList();
}

function renderFileList() {
    if (selectedFiles.length === 0) {
        $(".pending-files").hide();
        return;
    }
    
    let html = "";
    selectedFiles.forEach((f, idx) => {
        html += `<li style="padding:5px 0; border-bottom:1px solid #eee;">
            <i class="fa fa-image text-primary"></i> ${idx + 1}. ${f.name} 
            <small style="color:#999;">(${(f.size / (1024 * 1024)).toFixed(2)} MB)</small>
        </li>`;
    });
    
    $(".file-list").html(html);
    $(".file-count").text(selectedFiles.length);
    $(".pending-files").show();
}

async function startUpload() {
    if (selectedFiles.length === 0) {
        swal({
            type: 'warning',
            title: "ไม่มีไฟล์",
            text: "กรุณาเลือกไฟล์ที่ต้องการอัปโหลด",
            timer: 2000
        });
        return;
    }
    
    $(".loader").addClass("active");
    $(".upload-status").show().html('<i class="fas fa-spinner fa-spin"></i> กำลังเตรียมอัปโหลด...');
    
    const batchSize = 10;
    let totalFiles = selectedFiles.length;
    let uploadedFiles = 0;
    let allErrors = [];
    let totalSuccess = 0;
    
    for (let b = 0; b < totalFiles; b += batchSize) {
        const batchFiles = selectedFiles.slice(b, b + batchSize);
        const formData = new FormData();
        
        for (let i = 0; i < batchFiles.length; i++) {
            formData.append("files[]", batchFiles[i]);
        }
        
        formData.append("action", "userUploadImages");
        formData.append("event_id", gameState.event_id);
        formData.append("register_id", gameState.register_id);
        
        const batchNum = Math.floor(b / batchSize) + 1;
        const totalBatches = Math.ceil(totalFiles / batchSize);
        
        $(".upload-status").html(`
            <i class="fas fa-spinner fa-spin"></i> 
            กำลังอัปโหลดชุดที่ ${batchNum}/${totalBatches} 
            (${uploadedFiles}/${totalFiles} ไฟล์)
        `);
        
        try {
            const result = await $.ajax({
                url: "/classroom/study/actions/myphoto_upload.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 120000
            });
            
            uploadedFiles += batchFiles.length;
            totalSuccess += result.success_count || 0;
            
            if (result.errors && result.errors.length > 0) {
                allErrors = allErrors.concat(result.errors);
            }
            
        } catch (error) {
            console.error('Upload error:', error);
            allErrors.push(`ชุดที่ ${batchNum} อัปโหลดล้มเหลว`);
            uploadedFiles += batchFiles.length;
        }
        
        const percent = Math.round((uploadedFiles / totalFiles) * 100);
        $(".upload-status").html(`
            <i class="fas fa-spinner fa-spin"></i> 
            กำลังอัปโหลด ${percent}% 
            (สำเร็จ ${totalSuccess} ไฟล์)
        `);
    }
    
    $(".loader").removeClass("active");
    $(".upload-status").hide();
    
    if (totalSuccess === totalFiles) {
        swal({
            type: 'success',
            title: "อัปโหลดสำเร็จ",
            html: true,
            text: `<div style="font-size:16px;">
                        อัปโหลดสำเร็จ <strong>${totalSuccess} / ${totalFiles}</strong> ไฟล์!<br>
                       <small style="color:#28a745;">รูปภาพจะถูกประมวลผลในพื้นหลัง</small>
                   </div>`,
            confirmButtonText: 'ตกลง'
        });
    } else if (totalSuccess > 0) {
        swal({
            type: 'warning',
            title: "อัปโหลดบางส่วน",
            html: true,
            text: `<div style="font-size:15px;">
                       ✅ อัปโหลดสำเร็จ: <strong>${totalSuccess}</strong> ไฟล์<br>
                       ❌ ล้มเหลว: <strong>${totalFiles - totalSuccess}</strong> ไฟล์
                   </div>`,
            confirmButtonText: 'ตรวจสอบแล้ว'
        });
    } else {
        swal({
            type: 'error',
            title: "อัปโหลดล้มเหลว",
            text: "ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองใหม่อีกครั้ง",
            confirmButtonText: 'ตกลง'
        });
    }
    
    selectedFiles = [];
    renderFileList();
    
    if (totalSuccess > 0) {
        loadUserPhotos();
    }
}

function loadUserPhotos() {
    $('#loading-area').show();
    
    $.ajax({
        url: "/classroom/study/actions/myphoto_upload.php",
        method: "POST",
        dataType: "json",
        data: {
            action: "getUserPhotos",
            event_id: gameState.event_id,
            register_id: gameState.register_id
        },
        success: function(res) {
            $('#loading-area').hide();
            
            if (res.status && res.photos) {
                renderUserPhotos(res.photos);
                $('.my-photos-count').text(res.photos.length);
                $('.images-counter').text(res.photos.length);
            } else {
                $('#myPhotosGrid').html(`
                    <div class="col-md-12 text-center" style="padding:40px;">
                        <i class="fas fa-images fa-3x text-muted"></i>
                        <p class="text-muted" style="margin-top:15px;">ยังไม่มีรูปภาพ</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#loading-area').hide();
            swal({
                type: 'error',
                title: "เกิดข้อผิดพลาด",
                text: "ไม่สามารถโหลดรูปภาพได้",
                timer: 2000
            });
        }
    });
}

function renderUserPhotos(photos) {
    if (photos.length === 0) {
        $('#myPhotosGrid').html(`
            <div class="col-md-12 text-center" style="padding:40px;">
                <i class="fas fa-images fa-3x text-muted"></i>
                <p class="text-muted" style="margin-top:15px;">ยังไม่มีรูปภาพ</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    photos.forEach(photo => {
        const statusBadge = photo.queue_status === 'completed' 
            ? '<span class="badge badge-success"><i class="fa fa-check"></i> เสร็จสิ้น</span>'
            : photo.queue_status === 'processing'
            ? '<span class="badge badge-info"><i class="fa fa-spinner fa-spin"></i> กำลังประมวลผล</span>'
            : photo.queue_status === 'pending'
            ? '<span class="badge badge-warning"><i class="fa fa-clock"></i> รอดำเนินการ</span>'
            : '<span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i> ข้อผิดพลาด</span>';
        
        html += `
            <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2" style="margin-bottom:20px;">
                <div class="photo-card" style="border:1px solid #e0e0e0; border-radius:8px; padding:10px; background:white; position:relative;">
                    <img src="${photo.thumb}" 
                         style="width:100%; height:150px; object-fit:cover; border-radius:6px; cursor:pointer;"
                         class="view-photo"
                         data-full="${photo.full}" onerror="this.src='/images/ogm_event_logo.jpg'">
                    
                    <div style="margin-top:10px; text-align:center;">
                        ${statusBadge}
                    </div>
                    
                    <div style="margin-top:10px; text-align:center;">
                        <small style="color:#999; display:block; margin-bottom:5px;">${photo.date_create}</small>
                        <button class="btn btn-danger btn-xs delete-photo" data-photo-id="${photo.photo_id}" style="border-radius:15px;">
                            <i class="fa fa-trash"></i> ลบ
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#myPhotosGrid').html(html);
}

// View photo in modal
$(document).on('click', '.view-photo', function() {
    const fullImg = $(this).data('full');
    
    swal({
        title: "รูปภาพ",
        html: true,
        text: `<img src="${fullImg}" style="max-width:100%; max-height:70vh; border-radius:8px;">`,
        confirmButtonText: 'ปิด',
        customClass: 'swal-wide'
    });
});

// Delete photo
$(document).on('click', '.delete-photo', function() {
    const photo_id = $(this).data('photo-id');
    
    swal({
        title: "ยืนยันการลบ",
        text: "คุณต้องการลบรูปภาพนี้ใช่หรือไม่?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#dc3545',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/study/actions/myphoto_upload.php",
                method: "POST",
                data: {
                    action: "deleteUserPhoto",
                    photo_id: photo_id,
                    register_id: gameState.register_id
                },
                dataType: "json",
                success: function(res) {
                    if (res.status) {
                        swal({
                            type: 'success',
                            title: "ลบสำเร็จ",
                            text: "",
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadUserPhotos();
                    } else {
                        swal({
                            type: 'error',
                            title: "เกิดข้อผิดพลาด",
                            text: res.message || "ไม่สามารถลบรูปภาพได้",
                            timer: 2000
                        });
                    }
                }
            });
        }
    });
});

function nonePermissionAlert() {
    swal({
        html: true,
        title: "ข้อผิดพลาด",
        text: "เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง",
        type: "error",
        confirmButtonText: "ตกลง",
        confirmButtonColor: "#F27474"
    }, function () {
        window.location.reload();
    });
}

// Add custom CSS for wide modal
$('<style>')
    .text(`
        .swal-wide {
            width: 90% !important;
            max-width: 1200px !important;
        }
        
        .loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loader.active {
            display: flex;
        }
        
        .loader::after {
            content: "";
            width: 50px;
            height: 50px;
            border: 5px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `)
    .appendTo('head');

// Add loader element
if (!$('.loader').length) {
    $('body').append('<div class="loader"></div>');
}

function closeGallery() {
    $(".loader").addClass("active");

    $.ajax({
		url: "/classroom/study/actions/myphoto_album.php",
		data: {
			action: 'closeGallery',
		},
		dataType: "JSON",
		type: 'POST',
		success: function(result){
            $(".loader").removeClass("active");
            if(result.status == true) {
                var classroom_id = result.classroom_id;
                window.location = "/classroom/study/myphoto";
            } else {
                nonePermissionAlert();
            }
        }
    }); 
}