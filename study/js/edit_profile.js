var studentId;
let pendingFileUploadData = null;
let swl_title = "TEST";

$(document).ready(function() {
    // initializeClassroomManagement();
    studentId = $("#student_id").val();
    loadProfile();

    // Handle Save Action (for text data only)
    $("#saveBtn").on("click", function (e) {
        e.preventDefault();

        const formData = new FormData($("#editProfileForm")[0]);
        formData.append('action', 'saveProfile');
        
        for (const pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $.ajax({
            url: "/classroom/study/actions/edit_profile.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function (response) {
                console.log("RES", response);
                if (response.status === true) {
                    swal({ title: "บันทึกสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
                } else {
                    swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
                }
            },error: function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้", type: "error" });
            }
        });
    });
});


function loadProfile() {
  $.ajax({
    url: "/classroom/study/actions/edit_profile.php",
    data: {
      action: "loadProfile",
      student_id: studentId,
    },
    dataType: "JSON",
    type: "POST",
    success: function (result) {
        console.log(result);
        if (result.status === true) {
                $('#student_id').val(result.student_info.student_id);
                $('#firstname').val(result.student_info.student_firstname_th);
                $('#lastname').val(result.student_info.student_lastname_th);
                $('#bio').val(result.student_info.student_bio);
                $('#email').val(result.student_info.student_email);
                $('#mobile').val(result.student_info.student_mobile);
                $('#birth_date').val(result.student_info.student_birth_date);
                $('#gender').val(result.student_info.student_gender);
                $('#line').val(result.student_info.student_line);
                $('#instagram').val(result.student_info.student_instagram);
                $('#facebook').val(result.student_info.student_facebook);
                $('#hobby').val(result.student_info.student_hobby);
                $('#student_music').val(result.student_info.student_music);
                $('#student_drink').val(result.student_info.student_drink);
                $('#student_movie').val(result.student_info.student_movie);
                $('#goal').val(result.student_info.student_goal);
                $('#allergy').val(result.student_info.student_allergy);
                $('#company').val(result.student_info.student_company);
                $('#position').val(result.student_info.student_position);
                $('#company_url').val(result.student_info.student_company_url);
                $('#company_detail').val(result.student_info.student_company_detail);
                
                // Border Color IN CSS AND Apply styles dynamically to the main profile image
                let profileBorderColor = result.student_info.group_color || '#ff8c00';
                const mainProfileImage = document.querySelector('.profile-image-item.is-main img');
                if (mainProfileImage) {
                    mainProfileImage.style.borderColor = profileBorderColor;
                    mainProfileImage.style.boxShadow = '0 0 10px rgba(255, 140, 0, 0.5)';
                }

                let stud_id = result.student_info.student_id;

                console.log("STD_IMAGES_ID:", result.student_images);

                // Student Image
                $('#imageGallery').empty();

                let galleryHtml = '';
                let std_images = result.student_images || [];
                std_images.forEach(function(image, index) {


                // Select the image element based on data-file-index
                    let imgSelector = `.profile-image-item[data-file-index="${index}"] img.profile-image`;
                    const isMainClass = image.file_status == 1 ? 'is-main' : '';
                    galleryHtml += `
                        <div class="profile-image-item" 
                            data-file-id="${image.file_id}" 
                            data-file-path="${image.file_path}" 
                            data-file-index="${index}">
                            <div class="image-wrapper">
                                <img src="${image.file_path}" 
                                    alt="Profile Image ${index + 1}" 
                                    class="profile-image ${isMainClass}" 
                                    onerror="this.src='/images/default.png'"/>
                            </div>
                            <div class="image-overlay">
                                <div class="overlay-actions">
                                <button type="button" class="overlay-btn btn-delete-image" onclick="deleteProfileImage('${image.file_id}', '${image.file_order}', '${stud_id}')" title="ลบรูปภาพ">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <button type="button" class="overlay-btn btn-replace-image" onclick="replaceNewProfileImage('${image.file_id}', 'replace-file-${index}', '${stud_id}')" title="เปลี่ยนรูปภาพ">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                ${isMainClass ? '' : `
                                    <button type="button" class="overlay-btn btn-set-main" onclick="setProfileImageToMain('${image.file_id}', '${image.file_order}', '${stud_id}')" title="ตั้งเป็นรูปหลัก">
                                        <i class="fas fa-star"></i>
                                    </button>
                                `}
                                </div>
                            </div>
                            <input type="file" id="replace-file-${index}" data-file-type="profile_image" class="file-input-handler" style="display:none;" accept="image/*" />
                        </div>`;
                });

                if (std_images.length < 4) {
                    galleryHtml += `
                        <div class="profile-image-item profile-image-placeholder">
                            <div class="image-wrapper">
                                <label for="add-file" style="cursor:pointer;">
                                    <i class="fas fa-plus"></i>
                                </label>
                                <input type="file" name="profile_image[]" id="add-file" class="file-input-handler" data-file-type="profile_image" style="display:none;"  accept="image/*" />
                            </div>
                        </div>`;
                }

                $('#imageGallery').html(galleryHtml);

                // Company Logo
                let logoHtml = `<div class="col-md-3 mb-4 company-logo-item" data-file-id="0" data-file-index="logo">
                    <div class="circle-logo-container">`;

                if (result.student_info.student_company_logo && result.student_info.student_company_logo.trim() !== '') {
                    logoHtml += `
                        <img src="${result.student_info.student_company_logo}" alt="Company Logo" class="company-logo img-thumbnail circle-logo" onerror="this.src='/images/default.png'">
                        <div class="image-overlay1">
                            <div class="overlay-actions">
                                <button type="button" class="overlay-btn btn-delete-image-logo" onclick="deleteCompanyLogo(${stud_id})" title="ลบโลโก้">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <label for="replace-company-logo" class="overlay-btn" title="เปลี่ยนโลโก้">
                                    <i class="fas fa-exchange-alt"></i>
                                </label>
                            </div>
                        </div>
                        <input type="file" name="company_logo" id="replace-company-logo" class="file-input-handler d-none" data-file-type="company_logo" accept="image/*" />`;
                } else {
                    logoHtml += `<div class="company-add-placeholder logo-placeholder">
                            <div class="circle-logo-container">
                                <i class="fas fa-plus-circle fa-2x text-muted"></i>
                                <span class="text-muted">เพิ่มโลโก้บริษัท</span>
                                <input name="company_logo" type="file" class="file-input-handler d-none" data-file-type="company_logo" id="add-company-logo" />
                                <label for="add-company-logo" class="stretched-link"></label>
                            </div>
                        </div>`;
                }

                logoHtml += `</div></div>`;

                // Insert into container
                $('#company-logo-container').html(logoHtml);

                // Company Image
                $('#company-photos-container').empty();
                let companyImages = result.company_images || [];
                let companyHtml = '';

                companyImages.forEach(function(image, index) {
                    companyHtml += `
                        <div class="col-md-3 mb-4 company-image-item" data-file-id="${image.file_id}" data-file-index="${index}">
                            <div class="image-wrapper">
                                <img src="${image.file_path}" alt="Company Photo" class="company-image img-thumbnail" onerror="this.src='/images/default.png'" />
                            </div>
                            <div class="image-overlay1">
                                <div class="overlay-actions">
                                    <button type="button" class="overlay-btn btn-delete-image-company" onclick="deleteCompanyBanner('${image.file_id}', '${stud_id}')" title="ลบรูปภาพ">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                });

                companyHtml += `<div class="col-md-3 mb-4 company-image-item" data-file-index="${companyImages.length}">
                                    <div class="image-wrapper company-add-placeholder">
                                            <i class="fas fa-plus-circle fa-2x text-muted"></i>
                                            <span class="text-muted">เพิ่มรูปภาพ</span>
                                            <input name="company_banner" type="file" class="file-input-handler d-none" data-file-type="company_photo" id="add-company-file">
                                            <label for="add-company-file" class="stretched-link"></label>
                                    </div>
                                </div>`

                $('#company-photos-container').html(companyHtml);
        } else {
            swal({type: 'error',title: "Error!",text: "Failed to load profile:",timer: 2000});
        }
    },
    error: function (xhr, status, error) {
        alert("AJAX error while loading profile.");
        console.error("Failed to load courses:", error);
    },
  });
}

function replaceNewProfileImage(fileId, inputId, studentId){
    const fileInput = document.getElementById(inputId);
    fileInput.click();

    fileInput.onchange = function() {
        if (fileInput.files.length > 0) {
            let formData = new FormData();
            formData.append("action", "updateImageProfile");
            formData.append("student_id", studentId);
            formData.append("file_id", fileId);
            formData.append("new_img_file", fileInput.files[0]); // append the actual file object

            $.ajax({
                url: "/classroom/study/actions/edit_profile.php",
                data: formData,
                processData: false, // crucial for sending FormData
                contentType: false, // crucial for sending FormData
                dataType: "JSON",
                type: "POST",
                success: function(result) {
                    if (result.status == true) {
                        // handle success
                    } else {
                        swal({ title: "เกิดข้อผิดพลาด", text: result.message, type: "error" });
                    }
                },
                error: function(xhr, status, error) {
                    swal({ title: "ERROR!", text: "AJAX error while loading profile.", type: "error" });
                    console.error("Failed to load profile:", error);
                }
            });
        }
    };
}


function setProfileImageToMain(fileId, file_order, studentId){
    $.ajax({
        url: "/classroom/study/actions/edit_profile.php",
        data: {
            action: "setMainImage",
            student_id: studentId,
            file_id: fileId,
            file_img_idx: file_order,
        },
        dataType: "JSON",
        type: "POST",
        success: function (result) {
            
        }, error: function (xhr, status, error) {
            swal({ title: "ERROR!", text: "AJAX error while loading profile.", type: "error" });
            console.error("Failed to load courses:", error);
        }
    });
}


function deleteProfileImage(fileId, file_order, studentId){
    swal({
        html:true,
        title: "ต้องการลบรูปปกบริษัทใช่หรือไม่",
        text: swl_title,
        type: 'warning',
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Confirm"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FBC02D',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
                $.ajax({
                    url: "/classroom/study/actions/edit_profile.php",
                    data: {
                        action: "deleteProfileImg",
                        student_id: studentId,
                        file_id: fileId,
                        file_img_idx: file_order,
                    },
                    dataType: "JSON",
                    type: "POST",
                    success: function (result) {

                    }, error: function (xhr, status, error) {
                        swal({ title: "ERROR!", text: "AJAX error while loading profile.", type: "error" });
                        console.error("Failed to load courses:", error);
                    }
                });
            }
        }
    )
        
}

function deleteCompanyBanner(fileId, studentId){
    swal({
        html:true,
        title: "ต้องการลบรูปปกบริษัทใช่หรือไม่",
        text: swl_title,
        type: 'warning',
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Confirm"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FBC02D',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
                $.ajax({
                    url: "/classroom/study/actions/edit_profile.php",
                    data: {
                        action: "deleteBanner",
                        student_id: studentId,
                        file_id: fileId,
                        file_img_idx: file_order,
                    },
                    dataType: "JSON",
                    type: "POST",
                    success: function (result) {

                    }, error: function (xhr, status, error) {
                        swal({ title: "ERROR!", text: "AJAX error while loading profile.", type: "error" });
                        console.error("Failed to load courses:", error);
                    }
                });
            }
        }
    ) 
}


function deleteCompanyLogo(studentId){
    swal({
        html:true,
        title: "ต้องการลบรูปโลโก้บริษัทใช่หรือไม่",
        text: swl_title,
        type: 'warning',
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Confirm"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FBC02D',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/study/actions/edit_profile.php",
                data: {
                    action: "deleteCompLogo",
                    student_id: studentId,
                },
                dataType: "JSON",
                type: "POST",
                success: function (result) {

                }, error: function (xhr, status, error) {
                    swal({ title: "ERROR!", text: "AJAX error while loading profile.", type: "error" });
                    console.error("Failed to load courses:", error);
                }
            });
        }
    });
   
}


