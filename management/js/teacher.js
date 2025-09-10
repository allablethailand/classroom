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
                    <th lang="en">Name</th>
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
                        <button class="btn btn-warning btn-circle" onclick="manageTeacher('${data}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-circle" onclick="deleteTeacher('${data}')"><i class="fas fa-trash-alt"></i></button>
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
                        <i class="fas fa-upload me-2"></i>
                        <span id="file-label">เลือกรูปโปรไฟล์</span>
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
                            <input type="text" class="form-control" id="teacher_birth_date" name="teacher_birth_date">
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
                            <label class="form-label">ที่อยู่ <span class="text-danger">*</span></label>
                            <input type="hidden" id="teacher_address" name="teacher_address">
                            <div class="row">
                                <div class="col-md-4 mb-3" style="padding-bottom:10px;">
                                    <input type="text" class="form-control" id="teacher_address_house_no" placeholder="บ้านเลขที่">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-8 mb-3" style="padding-bottom:10px;">
                                    <input type="text" class="form-control" id="teacher_address_road" placeholder="ถนน">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3" style="padding-bottom:10px;">
                                    <input type="text" class="form-control zipcode-search" id="teacher_address_subdistrict" placeholder="ตำบล / แขวง">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3" style="padding-bottom:10px;">
                                    <input type="text" class="form-control zipcode-search" id="teacher_address_district" placeholder="อำเภอ / เขต">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3" style="padding-bottom:10px;">
                                    <input type="text" class="form-control zipcode-search" id="teacher_address_province" placeholder="จังหวัด">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3" style="padding-bottom:10px;">
                                    <input type="text" class="form-control zipcode-search" id="teacher_address_zipcode" placeholder="รหัสไปรษณีย์" maxlength="5">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="address-invalid-feedback" style="display: none;"></div>
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
                            <label class="form-label">ประวัติการศึกษา</label>
                            <div class="education-form mb-3">
                                <h6 class="text-muted">วุฒิปริญญาโท</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control education-input" data-level="master" data-field="school" placeholder="ชื่อมหาวิทยาลัย/สถาบัน">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control education-input" data-level="master" data-field="major" placeholder="คณะ/สาขาวิชา">
                                    </div>
                                </div>
                            </div>
                            <div class="education-form mb-3">
                                <h6 class="text-muted">วุฒิปริญญาตรี</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control education-input" data-level="bachelor" data-field="school" placeholder="ชื่อมหาวิทยาลัย/สถาบัน">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control education-input" data-level="bachelor" data-field="major" placeholder="คณะ/สาขาวิชา">
                                    </div>
                                </div>
                            </div>
                            <div class="education-form mb-3">
                                <h6 class="text-muted">วุฒิระดับมัธยมศึกษา</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control education-input" data-level="highschool" data-field="school" placeholder="ชื่อโรงเรียน">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control education-input" data-level="highschool" data-field="major" placeholder="สายวิชา (เช่น วิทย์-คณิต)">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="teacher_education" name="teacher_education">
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
                            <input type="file" class="form-control" id="teacher_attach_document" name="teacher_attach_document[]" multiple>
                            <small class="form-text text-muted">แนบเอกสารเพิ่มเติม เช่น วุฒิการศึกษา (pdf, docx)</small>
                            <div class="file-preview-container mt-2" id="document-preview-container"></div>
                            <input type="hidden" name="teacher_attach_document_current" id="teacher_attach_document_current">
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

    // ตั้งค่า Event Listener สำหรับการอัปโหลดไฟล์
    $('#teacher_image_profile').on('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'เลือกรูปโปรไฟล์';
        $('#file-label').text(fileName);

        // แสดงรูปภาพตัวอย่าง
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#profile-img').attr('src', event.target.result).show();
                $('#upload-icon-overlay').hide();
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // 🆕 Event Listener สำหรับการกรอกที่อยู่
    setupAddressAutocomplete();

    // 🆕 เปลี่ยน input type="date" เป็น type="text" และใช้ jQuery UI Datepicker
    $('#teacher_birth_date').datepicker({
        dateFormat: 'yy-mm-dd', // กำหนดรูปแบบวันที่เป็น ปี-เดือน-วัน
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0" // ให้เลือกปีได้ 100 ปีย้อนหลัง
    });
}

function setupAddressAutocomplete() {
    // ใช้ debounce เพื่อลดการเรียก API เมื่อผู้ใช้พิมพ์
    const debounce = (func, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                func.apply(null, args);
            }, delay);
        };
    };

    const handleSearch = debounce(async (event) => {
        const target = $(event.target);
        const term = target.val();

        if (term.length >= 3) {
            try {
                // ตัวอย่างการดึงข้อมูลจาก API ไปรษณีย์
                const result = await thailand.search(term);
                if (result.length > 0) {
                    const data = result[0];
                    $('#teacher_address_subdistrict').val(data.subdistrict);
                    $('#teacher_address_district').val(data.district);
                    $('#teacher_address_province').val(data.province);
                    $('#teacher_address_zipcode').val(data.zipcode);
                }
            } catch (error) {
                console.error("Autocomplete failed:", error);
            }
        }
    }, 500);

    // ดักจับการเปลี่ยนแปลงในช่อง ตำบล, อำเภอ, จังหวัด และ รหัสไปรษณีย์
    $('#teacher_address_subdistrict, #teacher_address_district, #teacher_address_province, #teacher_address_zipcode').on('input', handleSearch);
}


// ** NEW: Function สำหรับดึงข้อมูลตำแหน่งจาก API **
async function fetchPositions() {
    try {
        const response = await $.ajax({
            url: "/classroom/management/actions/teacher.php",
            type: "POST",
            data: {
                action: "getPositions"
            },
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
    // Function สำหรับดึงข้อมูลเพื่อแก้ไข
// Function สำหรับดึงข้อมูลเพื่อแก้ไข
let selectedFiles = [];
let currentFiles = [];

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
                $('#teacher_id').val(response.teacher_id);
                // แปลงค่าคำนำหน้าให้ถูกต้อง
                const perfix_map = ['นาย', 'นาง', 'นางสาว'];
                $('#teacher_perfix').val(perfix_map[parseInt(response.teacher_perfix)]);

                $('#teacher_firstname_th').val(response.teacher_firstname_th);
                $('#teacher_lastname_th').val(response.teacher_lastname_th);
                $('#teacher_firstname_en').val(response.teacher_firstname_en);
                $('#teacher_lastname_en').val(response.teacher_lastname_en);
                $('#teacher_nickname_th').val(response.teacher_nickname_th);
                $('#teacher_nickname_en').val(response.teacher_nickname_en);
                $('#teacher_idcard').val(response.teacher_idcard);
                $('#teacher_passport').val(response.teacher_passport);
                $('#teacher_birth_date').val(response.teacher_birth_date);
                $('#teacher_mobile').val(response.teacher_mobile);
                
                // ... (โค้ดเดิมส่วนอื่นๆ) ...
                
                $('#teacher_company').val(response.teacher_company);
                $('#teacher_experience').val(response.teacher_experience);
                $('#teacher_username').val(response.teacher_username);
                $('#teacher_email').val(response.teacher_email);
                $('#teacher_bio').val(response.teacher_bio);
                $('#teacher_position').val(response.teacher_position);
                $('#position_id').val(response.position_id);

                // **ส่วนนี้จะทำงานได้ทันที เพราะ URL ถูกจัดการมาแล้วจากฝั่ง PHP**
                if (response.teacher_image_profile) {
                    showProfilePreview(response.teacher_image_profile);
                }
                if (response.teacher_card_front) {
                    showCardPreview(response.teacher_card_front, '#current-card-front');
                }
                if (response.teacher_card_back) {
                    showCardPreview(response.teacher_card_back, '#current-card-back');
                }

                // 🆕 แสดงไฟล์เอกสารแนบเดิม
                if (response.teacher_attach_document) {
                    currentFiles = response.teacher_attach_document.split('|').filter(Boolean);
                    displayCurrentFiles(currentFiles, '#document-preview-container');
                    $('#teacher_attach_document_current').val(response.teacher_attach_document);
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

        // 🆕 เพิ่ม event listener สำหรับช่องอัปโหลดเอกสาร
        $('#teacher_attach_document').on('change', function() {
            handleMultipleFileSelection(this.files);
        });
    }

    // 🆕 ฟังก์ชันสำหรับจัดการการเลือกไฟล์หลายไฟล์
    function handleMultipleFileSelection(files) {
        // เพิ่มไฟล์ใหม่เข้าใน selectedFiles
        for (let i = 0; i < files.length; i++) {
            selectedFiles.push(files[i]);
        }
        displaySelectedFiles();
        // ล้างค่า input file เพื่อให้สามารถเลือกไฟล์เดิมได้อีกครั้ง
        $('#teacher_attach_document').val('');
    }

    // 🆕 ฟังก์ชันสำหรับแสดงไฟล์ที่เลือกไว้
    function displaySelectedFiles() {
        const previewContainer = $('#document-preview-container');
        previewContainer.empty();
        
        // แสดงไฟล์เดิมที่อยู่ในฐานข้อมูล
        currentFiles.forEach((path, index) => {
            const filename = path.split('/').pop();
            const fileItem = $(`<div class="d-flex align-items-center mb-1"><span class="me-2">${filename}</span> <button type="button" class="btn btn-danger btn-sm delete-file" data-type="current" data-index="${index}">&times;</button></div>`);
            previewContainer.append(fileItem);
        });

        // แสดงไฟล์ใหม่ที่เลือกไว้
        selectedFiles.forEach((file, index) => {
            const fileItem = $(`<div class="d-flex align-items-center mb-1"><span class="me-2">${file.name}</span> <button type="button" class="btn btn-danger btn-sm delete-file" data-type="new" data-index="${index}">&times;</button></div>`);
            previewContainer.append(fileItem);
        });

        // เพิ่ม event listener ให้กับปุ่มลบ
        previewContainer.off('click', '.delete-file');
        previewContainer.on('click', '.delete-file', function() {
            const type = $(this).data('type');
            const index = $(this).data('index');
            if (type === 'current') {
                currentFiles.splice(index, 1);
            } else {
                selectedFiles.splice(index, 1);
            }
            displaySelectedFiles();
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
        $('.invalid-feedback').text('').removeClass('text-danger');

        let errors = {};
        let firstErrorField = null;

        // 🆕 เตรียมข้อมูลที่อยู่จากช่องที่อยู่แยก
        const houseNo = $('#teacher_address_house_no').val();
        const road = $('#teacher_address_road').val();
        const subdistrict = $('#teacher_address_subdistrict').val();
        const district = $('#teacher_address_district').val();
        const province = $('#teacher_address_province').val();
        const zipcode = $('#teacher_address_zipcode').val();
        const fullAddress = [houseNo, road, subdistrict, district, province, zipcode].filter(part => part).join(', ');

        // 🆕 นำค่าที่อยู่เต็มไปใส่ใน hidden input ก่อนส่งฟอร์ม
        $('#teacher_address').val(fullAddress);

        // 🆕 รวมข้อมูลการศึกษาจากช่องใหม่
        const educationData = [];
        
        const masterSchool = $('.education-input[data-level="master"][data-field="school"]').val();
        const masterMajor = $('.education-input[data-level="master"][data-field="major"]').val();
        if (masterSchool) {
            let line = `ระดับปริญญาโท: ${masterSchool}`;
            if (masterMajor) {
                line += ` (${masterMajor})`;
            }
            educationData.push(line);
        }
        
        const bachelorSchool = $('.education-input[data-level="bachelor"][data-field="school"]').val();
        const bachelorMajor = $('.education-input[data-level="bachelor"][data-field="major"]').val();
        if (bachelorSchool) {
            let line = `ระดับปริญญาตรี: ${bachelorSchool}`;
            if (bachelorMajor) {
                line += ` (${bachelorMajor})`;
            }
            educationData.push(line);
        }
        
        const highschoolSchool = $('.education-input[data-level="highschool"][data-field="school"]').val();
        const highschoolMajor = $('.education-input[data-level="highschool"][data-field="major"]').val();
        if (highschoolSchool) {
            let line = `ระดับมัธยมศึกษา: ${highschoolSchool}`;
            if (highschoolMajor) {
                line += ` (${highschoolMajor})`;
            }
            educationData.push(line);
        }
        
        // ตั้งค่าค่าของ hidden input ด้วยข้อมูลที่รวมแล้ว
        $('#teacher_education').val(educationData.join('\n'));

        // ตรวจสอบข้อมูลในช่องที่กำหนด
        const requiredFields = {
            teacher_perfix: "กรุณาเลือกคำนำหน้า",
            teacher_firstname_en: "กรุณากรอกชื่อ (ภาษาอังกฤษ)",
            teacher_lastname_en: "กรุณากรอกนามสกุล (ภาษาอังกฤษ)",
            teacher_idcard: "กรุณากรอกเลขบัตรประชาชน",
            teacher_mobile: "กรุณากรอกเบอร์โทรศัพท์มือถือ",
            teacher_email: "กรุณากรอกอีเมล",
            teacher_company: "กรุณากรอกชื่อบริษัท / องค์กร",
            teacher_position: "กรุณากรอกตำแหน่งงาน",
            teacher_username: "กรุณากรอกชื่อผู้ใช้งาน",
            position_id: "กรุณาเลือกตำแหน่งครู",
        };

        // 🆕 แก้ไขการตรวจสอบที่อยู่ให้ถูกต้องตามแต่ละช่อง
        const addressFields = {
            teacher_address_house_no: "กรุณากรอกบ้านเลขที่",
            teacher_address_subdistrict: "กรุณากรอกตำบล / แขวง",
            teacher_address_district: "กรุณากรอกอำเภอ / เขต",
            teacher_address_province: "กรุณากรอกจังหวัด",
            teacher_address_zipcode: "กรุณากรอกรหัสไปรษณีย์",
        };

        // 🆕 วนลูปตรวจสอบช่องที่อยู่
        for (const fieldId in addressFields) {
            const value = $(`#${fieldId}`).val();
            if (!value) {
                errors[fieldId] = addressFields[fieldId];
                if (!firstErrorField) {
                    firstErrorField = $(`#${fieldId}`);
                }
            }
        }

        // 🆕 ตรวจสอบข้อมูลในช่องที่กำหนด
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
                $(`#${fieldId}`).next('.invalid-feedback').text(errors[fieldId]).addClass('text-danger');
            }

            // 🆕 แสดง Pop-up แจ้งเตือนเมื่อกรอกข้อมูลไม่ครบ
            const errorMessage = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
            Swal.fire({
                icon: 'warning',
                title: 'เกิดข้อผิดพลาด!',
                text: errorMessage,
                showCloseButton: true,
                confirmButtonText: 'ตกลง',
                customClass: {
                    popup: 'my-swal-popup'
                }
            });

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

        // 🆕 เพิ่มไฟล์เอกสารแนบที่เลือกไว้ลงใน formData
        selectedFiles.forEach(file => {
            formData.append('teacher_attach_document[]', file);
        });

        // 🆕 ส่งค่าไฟล์เดิมที่เหลืออยู่
        formData.append('teacher_attach_document_current', currentFiles.join('|'));


        $.ajax({
            url: "/classroom/management/actions/teacher.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // 🆕 แสดง Pop-up แจ้งเตือนเมื่อบันทึกสำเร็จ
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

    // ฟังก์ชันสำหรับแสดงไฟล์เดิม
    function displayCurrentFiles(files, containerId) {
        const container = $(containerId);
        container.empty();
        files.forEach((path, index) => {
            const filename = path.split('/').pop();
            const fileItem = $(`<div class="d-flex align-items-center mb-1"><span class="me-2">${filename}</span> <button type="button" class="btn btn-danger btn-sm delete-file" data-type="current" data-index="${index}">&times;</button></div>`);
            container.append(fileItem);
        });
        container.on('click', '.delete-file', function() {
            const index = $(this).data('index');
            files.splice(index, 1);
            $(this).parent().remove();
            // อัปเดต hidden input
            $('#teacher_attach_document_current').val(files.join('|'));
        });
    }

    // Call setup function on document ready
    $(document).ready(function() {
        setupFilePreview();
    });

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