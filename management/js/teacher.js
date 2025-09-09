function buildTeacherPage() {
    $(".content-container").html(getTeacherTemplate());
    buildTeacher();
}
function getTeacherTemplate() {
    return `
        <table class="table table-border" id="tb_teacher">
            <thead>
                <tr>
                    <th><span lang="en">No.</span></th>
                    <th lang="en">Teacher</th>
                    <th lang="en">Position</th>
                    <th lang="en">Company</th>
                    <th lang="en">Job Position</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th><span lang="en">Action</span></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
let tb_teacher;
function buildTeacher() {
    if ($.fn.DataTable.isDataTable('#tb_teacher')) {
        $('#tb_teacher').DataTable().ajax.reload(null, false);
    } else {
        tb_teacher = $('#tb_teacher').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
            "ajax": {
                "url": "/classroom/management/actions/teacher.php",
                "type": "POST",
                "data": function (data) {
                    data.action = "buildTeacher";
                    data.classroom_id = classroom_id;
                }
            },
            "language": default_language,
            "responsive": true,
            "searchDelay": 1000,
            "deferRender": false,
            "drawCallback": function( settings ) {
                var lang = new Lang();
                lang.dynamic('th', '/js/langpack/th.json?v='+Date.now());
                lang.init({
                    defaultLang: 'en'
                });
            },
            "order": [[0,'asc']],
             "columns": [
            {
                // แก้ไขตรงนี้: ให้ data ชี้ไปที่ 'teacher_id'
                "data": "teacher_id",
                "render": function (data, type, row, meta) {
                    return data; // แสดงค่า teacher_id
                }
            },
            {
                "data": "teacher_name",
                "render": function (data, type, row, meta) {
                    return data;
                }
            },
            {
                "data": "teacher_job_position",
                "render": function (data, type, row, meta) {
                    return data;
                }
            },
            {
                "data": "teacher_company",
                "render": function (data, type, row, meta) {
                    return data;
                }
            },
            {
                "data": "teacher_position",
                "render": function (data, type, row, meta) {
                    return data;
                }
            },
            {
                "data": "date_create",
                "render": function (data, type, row, meta) {
                    return data;
                }
            },
            {
                "data": "emp_create",
                "render": function (data, type, row, meta) {
                    return data;
                }
            },
            {
                "data": "teacher_id",
                "render": function (data, type, row, meta) {
                    return `
                        <button class="btn btn-warning btn-sm" onclick="manageTeacher('${data}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="deleteTeacher('${data}')"><i class="fas fa-trash-alt"></i></button>
                    `;
                }
            }
        ]
        });
        $('div#tb_teacher_filter.dataTables_filter label input').remove();
        $('div#tb_teacher_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageTeacher('')"><i class="fas fa-plus"></i> <span lang="en">Teacher</span></button>
        `;
        $('div#tb_teacher_filter.dataTables_filter input').hide();
        $('div#tb_teacher_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_teacher.search(val).draw();   
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_teacher.search('').draw();
                buildTeacher();
            }
        });
    }
}


// ต้องเพิ่ม Library SweetAlert2 เข้าไปในหน้า HTML ของคุณก่อน
// ตัวอย่าง: <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

function manageTeacher(teacher_id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Teacher Management</h5> 
    `);
    
    // โค้ด HTML ของฟอร์มที่ถูกแก้ไข
    const formHtml = `
        <div class="container-fluid p-4">
            <form id="teacherForm" enctype="multipart/form-data">
                <input type="hidden" name="teacher_id" id="teacher_id">
                <input type="hidden" name="classroom_id" id="form_classroom_id"> 

                <div class="form-group mb-4 text-center">
                    <div class="profile-image-preview" id="current-profile-img" style="position: relative; width: 150px; height: 150px; margin: 0 auto 10px; border-radius: 50%; overflow: hidden; border: 2px solid #ddd; background-color: #f8f9fa;">
                        <img id="profile-img" src="" class="img-fluid" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                        <div id="upload-icon-overlay" class="d-flex align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; font-size: 2rem; color: #ced4da;">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <label for="teacher_image_profile" class="form-label d-block text-primary" style="cursor: pointer;">
                        <i class="fas fa-upload me-2"></i> เลือกรูปโปรไฟล์
                    </label>
                    <input type="file" class="d-none" id="teacher_image_profile" name="teacher_image_profile" accept="image/*">
                </div>
                
                <fieldset class="border p-3 mb-4 rounded">
                    <legend class="w-auto px-2 h5 text-primary">ข้อมูลส่วนตัว</legend>
                    <div class="row">
                        <div class="col-md-2 form-group mb-3">
                            <label for="teacher_perfix" class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                            <select class="form-control" id="teacher_perfix" name="teacher_perfix">
                                <option value="">เลือกคำนำหน้า</option>
                                <option value="นาย">นาย</option>
                                <option value="นาง">นาง</option>
                                <option value="นางสาว">นางสาว</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-5 form-group mb-3">
                            <label for="teacher_firstname_th" class="form-label">ชื่อ (ภาษาไทย)</label>
                            <input type="text" class="form-control" id="teacher_firstname_th" name="teacher_firstname_th">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-5 form-group mb-3">
                            <label for="teacher_lastname_th" class="form-label">นามสกุล (ภาษาไทย)</label>
                            <input type="text" class="form-control" id="teacher_lastname_th" name="teacher_lastname_th">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_firstname_en" class="form-label">ชื่อ (ภาษาอังกฤษ) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_firstname_en" name="teacher_firstname_en">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_lastname_en" class="form-label">นามสกุล (ภาษาอังกฤษ) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_lastname_en" name="teacher_lastname_en">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_nickname_th" class="form-label">ชื่อเล่น (ไทย)</label>
                            <input type="text" class="form-control" id="teacher_nickname_th" name="teacher_nickname_th">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_nickname_en" class="form-label">ชื่อเล่น (en)</label>
                            <input type="text" class="form-control" id="teacher_nickname_en" name="teacher_nickname_en">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_idcard" class="form-label">เลขบัตรประชาชน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_idcard" name="teacher_idcard" maxlength="13">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_passport" class="form-label">เลขที่หนังสือเดินทาง</label>
                            <input type="text" class="form-control" id="teacher_passport" name="teacher_passport">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_birth_date" class="form-label">วันเกิด</label>
                            <input type="date" class="form-control" id="teacher_birth_date" name="teacher_birth_date">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_mobile" class="form-label">เบอร์โทรศัพท์มือถือ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="teacher_mobile" name="teacher_mobile" maxlength="10">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="teacher_email" name="teacher_email">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="position_id" class="form-label">ตำแหน่งครู <span class="text-danger">*</span></label>
                            <select class="form-control" id="position_id" name="position_id">
                                <option value="">เลือกตำแหน่ง</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 form-group mb-3">
                            <label for="teacher_address" class="form-label">ที่อยู่ <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="teacher_address" name="teacher_address" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4 rounded">
                    <legend class="w-auto px-2 h5 text-primary">ข้อมูลการทำงานและการศึกษา</legend>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_company" class="form-label">บริษัท / องค์กร <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_company" name="teacher_company">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_position" class="form-label">ตำแหน่งงาน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_position" name="teacher_position">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 form-group mb-3">
                            <label for="teacher_education" class="form-label">ประวัติการศึกษา</label>
                            <textarea class="form-control" id="teacher_education" name="teacher_education" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 form-group mb-3">
                            <label for="teacher_experience" class="form-label">ประสบการณ์ทำงาน</label>
                            <textarea class="form-control" id="teacher_experience" name="teacher_experience" rows="4"></textarea>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4 rounded">
                    <legend class="w-auto px-2 h5 text-primary">เอกสารและรูปภาพ</legend>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_card_front" class="form-label">รูปนามบัตร (ด้านหน้า)</label>
                            <input type="file" class="form-control" id="teacher_card_front" name="teacher_card_front" accept="image/*">
                            <div class="current-file" id="current-card-front"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_card_back" class="form-label">รูปนามบัตร (ด้านหลัง)</label>
                            <input type="file" class="form-control" id="teacher_card_back" name="teacher_card_back" accept="image/*">
                            <div class="current-file" id="current-card-back"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 form-group mb-3">
                            <label for="teacher_attach_document" class="form-label">เอกสารแนบอื่นๆ</label>
                            <input type="file" class="form-control" id="teacher_attach_document" name="teacher_attach_document" multiple>
                            <small class="form-text text-muted">แนบเอกสารเพิ่มเติม เช่น วุฒิการศึกษา (pdf, docx)</small>
                            <div class="current-file" id="current-documents"></div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4 rounded">
                    <legend class="w-auto px-2 h5 text-primary">ข้อมูลผู้ใช้และระบบ</legend>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_username" class="form-label">ชื่อผู้ใช้งาน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="teacher_username" name="teacher_username">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="teacher_password" name="teacher_password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="teacher_password_key" class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="teacher_password_key" name="teacher_password_key">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group mb-4">
                    <label for="teacher_bio" class="form-label">ประวัติย่อ</label>
                    <textarea class="form-control" id="teacher_bio" name="teacher_bio" rows="5"></textarea>
                </div>
            </form>
        </div>
    `;

    $(".systemModal .modal-body").html(formHtml);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button> 
        <button type="button" class="btn btn-primary" id="saveBtn" lang="en">Save</button>
    `);
    
    const classroom_id = $('#classroom_id').val();
    if (classroom_id) {
        $('#form_classroom_id').val(classroom_id);
    }
    
    // ตั้งค่า Event Listener ที่ปุ่ม Save
    $("#saveBtn").on('click', saveTeacher);

    fetchPositions().then(() => {
        if (teacher_id) {
            $("#teacher_id").val(teacher_id);
            fetchTeacherData(teacher_id);
        }
    }).catch(error => {
        console.error("Failed to load positions:", error);
    });

    setupFilePreview();
}

// ** NEW: Function สำหรับดึงข้อมูลตำแหน่งจาก API **
async function fetchPositions() {
    try {
        const response = await $.ajax({
            url: "/classroom/management/actions/teacher.php",
            type: "POST",
            data: { action: "getPositions" },
            dataType: 'json'
        });
        
        if (response.status === 'success') {
            const dropdown = $("#position_id");
            dropdown.empty();
            dropdown.append('<option value="">เลือกตำแหน่ง</option>');
            response.data.forEach(pos => {
                dropdown.append(`<option value="${pos.position_id}">${pos.position_name_en}</option>`);
            });
        } else {
            console.error("Error fetching positions:", response.message);
        }
    } catch (error) {
        console.error("Server error while fetching positions:", error);
    }
}

// Function สำหรับดึงข้อมูลเพื่อแก้ไข
function fetchTeacherData(teacher_id) {
    $.ajax({
        url: "/classroom/management/actions/teacher.php",
        type: "POST",
        data: {
            action: "getTeacherData",
            teacher_id: teacher_id
        },
        dataType: 'json',
        success: function(response) {
            if (response) {
                // ... (โค้ดเดิม)
                $('#teacher_id').val(response.teacher_id);
                // แปลงค่าคำนำหน้าให้ถูกต้อง
                const perfix_map = ['นาย', 'นาง', 'นางสาว'];
                $('#teacher_perfix').val(perfix_map[parseInt(response.teacher_perfix)]);
                
                $('#teacher_firstname_th').val(response.teacher_firstname_th);
                $('#teacher_lastname_th').val(response.teacher_th);
                $('#teacher_firstname_en').val(response.teacher_firstname_en);
                $('#teacher_lastname_en').val(response.teacher_lastname_en);
                $('#teacher_nickname_th').val(response.teacher_nickname_th);
                $('#teacher_nickname_en').val(response.teacher_nickname_en);
                $('#teacher_idcard').val(response.teacher_idcard);
                $('#teacher_passport').val(response.teacher_passport);
                $('#teacher_birth_date').val(response.teacher_birth_date);
                $('#teacher_mobile').val(response.teacher_mobile);
                $('#teacher_address').val(response.teacher_address);
                $('#teacher_company').val(response.teacher_company);
                $('#teacher_education').val(response.teacher_education);
                $('#teacher_experience').val(response.teacher_experience);
                $('#teacher_username').val(response.teacher_username);
                $('#teacher_email').val(response.teacher_email);
                $('#teacher_bio').val(response.teacher_bio);
                $('#teacher_position').val(response.teacher_position);
                $('#position_id').val(response.position_id);

                if (response.teacher_image_profile) {
                    showProfilePreview(response.teacher_image_profile);
                }
                if (response.teacher_card_front) {
                    showCardPreview(response.teacher_card_front, '#current-card-front');
                }
                if (response.teacher_card_back) {
                    showCardPreview(response.teacher_card_back, '#current-card-back');
                }
            } else {
                    Swal.fire('ไม่พบข้อมูลครู', '', 'warning');
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            Swal.fire('เกิดข้อผิดพลาด', 'เกิดข้อผิดพลาดในการดึงข้อมูลครู', 'error');
        }
    });
}

function showProfilePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        $('#profile-img').attr('src', e.target.result).show();
        $('#upload-icon-overlay').hide();
    }
    if (file instanceof File) {
        reader.readAsDataURL(file);
    } else if (typeof file === 'string' && file.length > 0) {
        $('#profile-img').attr('src', file).show();
        $('#upload-icon-overlay').hide();
    } else {
        $('#profile-img').hide().attr('src', '');
        $('#upload-icon-overlay').show();
    }
}

function showCardPreview(file, targetId) {
    const reader = new FileReader();
    reader.onload = function(e) {
        $(targetId).html(`<img src="${e.target.result}" class="img-thumbnail mt-2" style="max-height: 200px;" alt="Image Preview">`);
    }
    if (file instanceof File) {
        reader.readAsDataURL(file);
    } else if (typeof file === 'string' && file.length > 0) {
        $(targetId).html(`<img src="${file}" class="img-thumbnail mt-2" style="max-height: 200px;" alt="Current Image">`);
    } else {
        $(targetId).html('');
    }
}

function setupFilePreview() {
    $('#teacher_image_profile').on('change', function() {
        showProfilePreview(this.files[0]);
    });

    $('#teacher_card_front').on('change', function() {
        showCardPreview(this.files[0], '#current-card-front');
    });

    $('#teacher_card_back').on('change', function() {
        showCardPreview(this.files[0], '#current-card-back');
    });
}
// 🆕 Function สำหรับตรวจสอบรูปแบบอีเมล
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// 🆕 Function สำหรับตรวจสอบรูปแบบเลขบัตรประชาชน
function isValidIdCard(idcard) {
    if (!/^\d{13}$/.test(idcard)) return false;
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(idcard.charAt(i)) * (13 - i);
    }
    const lastDigit = parseInt(idcard.charAt(12));
    return (11 - (sum % 11)) % 10 === lastDigit;
}

// 🆕 Function สำหรับตรวจสอบรูปแบบเบอร์โทรศัพท์มือถือ
function isValidMobile(mobile) {
    const mobileRegex = /^0[6,8,9]{1}[0-9]{8}$/;
    return mobileRegex.test(mobile);
}

// Function สำหรับบันทึกข้อมูล
function saveTeacher() {
    const form = $("#teacherForm");
    
    // ล้างข้อความและขอบสีแดงเดิมทั้งหมด
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    let errors = {};
    let firstErrorField = null;

    // ตรวจสอบข้อมูลในช่องที่กำหนด
    const requiredFields = {
        teacher_perfix: "กรุณาเลือกคำนำหน้า",
        teacher_firstname_en: "กรุณากรอกชื่อ (ภาษาอังกฤษ)",
        teacher_lastname_en: "กรุณากรอกนามสกุล (ภาษาอังกฤษ)",
        teacher_idcard: "กรุณากรอกเลขบัตรประชาชน",
        teacher_mobile: "กรุณากรอกเบอร์โทรศัพท์มือถือ",
        teacher_email: "กรุณากรอกอีเมล",
        teacher_address: "กรุณากรอกที่อยู่",
        teacher_company: "กรุณากรอกชื่อบริษัท / องค์กร",
        teacher_position: "กรุณากรอกตำแหน่งงาน",
        teacher_username: "กรุณากรอกชื่อผู้ใช้งาน",
        position_id: "กรุณาเลือกตำแหน่งครู",
    };

    for (const fieldId in requiredFields) {
        const value = $(`#${fieldId}`).val();
        if (!value) {
            errors[fieldId] = requiredFields[fieldId];
            if (!firstErrorField) {
                firstErrorField = $(`#${fieldId}`);
            }
        }
    }

    const teacher_id = $("#teacher_id").val();
    const password = $("#teacher_password").val();
    const password_key = $("#teacher_password_key").val();
    const email = $("#teacher_email").val();
    const idCard = $("#teacher_idcard").val();
    const mobile = $("#teacher_mobile").val();

    // 🆕 ตรวจสอบรหัสผ่าน (เฉพาะตอนสร้างใหม่)
    if (teacher_id === "" && (!password || !password_key)) {
        errors['teacher_password'] = "กรุณากรอกรหัสผ่าน";
        errors['teacher_password_key'] = "กรุณายืนยันรหัสผ่าน";
        if (!firstErrorField) firstErrorField = $('#teacher_password');
    } else if (password && password !== password_key) {
        errors['teacher_password'] = "รหัสผ่านไม่ตรงกัน";
        errors['teacher_password_key'] = "รหัสผ่านไม่ตรงกัน";
        if (!firstErrorField) firstErrorField = $('#teacher_password');
    }

    // 🆕 ตรวจสอบรูปแบบอีเมล
    if (email && !isValidEmail(email)) {
        errors['teacher_email'] = "รูปแบบอีเมลไม่ถูกต้อง";
        if (!firstErrorField) firstErrorField = $('#teacher_email');
    }

    // 🆕 ตรวจสอบรูปแบบเบอร์โทรศัพท์
    if (mobile && !isValidMobile(mobile)) {
        errors['teacher_mobile'] = "รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง (ตัวอย่าง: 0812345678)";
        if (!firstErrorField) firstErrorField = $('#teacher_mobile');
    }

    // 🆕 ตรวจสอบรูปแบบเลขบัตรประชาชน
    if (idCard && !isValidIdCard(idCard)) {
        errors['teacher_idcard'] = "รูปแบบเลขบัตรประชาชนไม่ถูกต้อง";
        if (!firstErrorField) firstErrorField = $('#teacher_idcard');
    }

    // ถ้ามีข้อผิดพลาด
    if (Object.keys(errors).length > 0) {
        for (const fieldId in errors) {
            $(`#${fieldId}`).addClass('is-invalid');
            $(`#${fieldId}`).next('.invalid-feedback').text(errors[fieldId]);
        }
        
        // เลื่อนไปที่ช่องแรกที่มีข้อผิดพลาด
        if (firstErrorField) {
            $(".systemModal .modal-body").animate({
                scrollTop: firstErrorField.offset().top - $(".systemModal .modal-body").offset().top + $(".systemModal .modal-body").scrollTop() - 20
            }, 500);
        }
        return;
    }

    // ถ้าไม่มีข้อผิดพลาด ให้บันทึกข้อมูล
    const formData = new FormData($("#teacherForm")[0]);
    formData.append('action', 'saveTeacher');
    
    $.ajax({
        url: "/classroom/management/actions/teacher.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกเรียบร้อย!',
                    text: response.message || 'บันทึกข้อมูลเรียบร้อยแล้ว'
                });
                
                setTimeout(() => {
                    $(".systemModal").modal('hide');
                    if (window.tb_teacher) {
                        window.tb_teacher.ajax.reload(null, false);
                    }
                }, 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + response.message,
                });
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
            });
        }
    });
}
function deleteTeacher(teacher_id) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการลบข้อมูลครูท่านนี้ใช่ไหม? การกระทำนี้ไม่สามารถย้อนกลับได้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/classroom/management/actions/teacher.php",
                type: "POST",
                data: {
                    action: "deleteTeacher",
                    teacher_id: teacher_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire(
                            'ลบเรียบร้อย!',
                            response.message,
                            'success'
                        );
                        // Reload ตารางหลังจากลบข้อมูล
                        if (window.tb_teacher) {
                            window.tb_teacher.ajax.reload(null, false);
                        }
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            'ไม่สามารถลบข้อมูลครูได้: ' + response.message,
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                        'error'
                    );
                }
            });
        }
    });
}