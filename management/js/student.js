function buildStudentPage() {
    $(".content-container").html(getStudentTemplate());
    buildStudent();
}

function getStudentTemplate() {
    return `
        <table class="table table-border" id="tb_student">
            <thead>
                <tr>
                    <th></th>
                    <th lang="en">Student Name</th>
                    <th lang="en">Nickname</th>
                    <th lang="en">Company</th>
                    <th lang="en">Position</th>
                    <th lang="en">Group</th>
                    <th lang="en">Birthday</th>
                    <th lang="en">Age</th>
                    <th lang="en">Email</th>
                    <th lang="en">Mobile</th>
                    <th lang="en">Create</th>
                    <th><span lang="en">Action</span></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}

let tb_student;
function buildStudent() {
    if ($.fn.DataTable.isDataTable('#tb_student')) {
        $('#tb_student').DataTable().ajax.reload(null, false);
    } else {
        tb_student = $('#tb_student').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
            "ajax": {
                "url": "/classroom/management/actions/student.php",
                "type": "POST",
                "data": function (data) {
                    data.action = "buildStudent";
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
            "order": [[9,'desc']],
            "columns": [{ 
                "targets": 0,
                "data": "student_image_profile",
                "render": function (data,type,row,meta) {
                    let student_gender = row['student_gender'];
                    var img_error = {
                        'M': '/images/default.png',
                        'F': '/images/female.png',
                        'O': '/images/icon-01.png'
                    }[student_gender] || '/images/default.png'; 
                    return `
                        <div class="avatar" style="border:3px solid #FFFFFF;">
                            <img src="${data}" onerror="this.src='${img_error}'">
                        </div>
                    `;
                }
            },{ 
                "targets": 1,
                "data": "student_firstname_en",
                "render": function (data,type,row,meta) {  
                    let student_firstname_en = row['student_firstname_en'];
                    let student_lastname_en = row['student_lastname_en'];
                    let student_firstname_th = row['student_firstname_th'];
                    let student_lastname_th = row['student_lastname_th'];
                    return `
                        <p><b>${student_firstname_en} ${student_lastname_en}</b></p>
                        <div>${student_firstname_th} ${student_lastname_th}</div>
                    `;
                }
            },{ 
                "targets": 2,
                "data": "student_nickname_en",
                "render": function (data,type,row,meta) {
                    let student_nickname_en = row['student_nickname_en'];  
                    let student_nickname_th = row['student_nickname_th'];  
                    return `${(student_nickname_en) ? student_nickname_en : ''} ${(student_nickname_th) ? `(${student_nickname_th})` : ``}`;
                }
            },{ 
                "targets": 3,
                "data": "student_company"
            },{ 
                "targets": 4,
                "data": "student_position"
            },{ 
                "targets": 5,
                "data": "group_name",
                "render": function (data,type,row,meta) {  
                    let group_id = row['group_id'];
                    return `
                        ${(data) ? data : ''}
                    `;
                }
            },{ 
                "targets": 6,
                "data": "student_birth_date",
                "render": function (data,type,row,meta) {  
                    return `
                        ${( (data && data !== '0000/00/00')) ? `${data || '-'}` : ''}
                    `;
                }
            },{ 
                "targets": 7,
                "data": "student_age",
                "render": function (data,type,row,meta) {  
                    return `
                        ${(data) ? data : ''}
                    `;
                }
            },{ 
                "targets": 8,
                "data": "student_mobile"
            },{ 
                "targets": 9,
                "data": "student_email"
            },{ 
                "targets": 10,
                "data": "register_date"
            },{
                // เพิ่มคอลัมน์ Action
                "targets": 11,
                "data": "student_id",
                "orderable": false,
                "render": function (data, type, row, meta) {
                    return `
                        <button class="btn btn-warning btn-circle" onclick="manageStudent('${data}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-circle" onclick="deleteStudent('${data}')"><i class="fas fa-trash-alt"></i></button>
                    `;
                }
            }]
        });
        $('div#tb_student_filter.dataTables_filter label input').remove();
        $('div#tb_student_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;">
             <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageStudent('')"><i class="fas fa-plus"></i> <span lang="en">Student</span></button>
        `;
        $('div#tb_student_filter.dataTables_filter input').hide();
        $('div#tb_student_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_student.search(val).draw();   
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_student.search('').draw();
                buildStudent();
            }
        });
    }
}

// --- ฟังก์ชันหลักสำหรับเปิด Modal และสร้างฟอร์ม ---
function manageStudent(student_id){
     let classroom_id = $("#classroom_id").val();
    // console.log(classroom_id);
    
    
    // window.location.href = `/classroom/management/form?type=teacher&id=${teacher_id}`;
    $.redirect(`form?type=student&id=${student_id}`,{classroom_id: classroom_id},'post','_self');
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
                    $('#student_address_subdistrict').val(data.subdistrict);
                    $('#student_address_district').val(data.district);
                    $('#student_address_province').val(data.province);
                    $('#student_address_zipcode').val(data.zipcode);
                }
            } catch (error) {
                console.error("Autocomplete failed:", error);
            }
        }
    }, 500);

    // ดักจับการเปลี่ยนแปลงในช่อง ตำบล, อำเภอ, จังหวัด และ รหัสไปรษณีย์
    $('#student_address_subdistrict, #student_address_district, #student_address_province, #student_address_zipcode').on('input', handleSearch);
}

 
let selectedStudentFiles = [];
let currentStudentFiles = [];

// Function สำหรับดึงข้อมูลเพื่อแก้ไข
function fetchStudentData(student_id) {
    $.ajax({
        url: "/classroom/management/actions/student.php",
        type: "POST",
        data: {
            action: "getStudentData",
            student_id: student_id
        },
        dataType: 'json',
        success: function(response) {
            if (response) {
                $('#student_id').val(response.student_id);
                const perfix_map = ['นาย', 'นาง', 'นางสาว'];
                $('#student_perfix').val(perfix_map[parseInt(response.student_perfix)]);
                $('#student_firstname_th').val(response.student_firstname_th);
                $('#student_lastname_th').val(response.student_lastname_th);
                $('#student_firstname_en').val(response.student_firstname_en);
                $('#student_lastname_en').val(response.student_lastname_en);
                $('#student_nickname_th').val(response.student_nickname_th);
                $('#student_nickname_en').val(response.student_nickname_en);
                $('#student_idcard').val(response.student_idcard);
                $('#student_passport').val(response.student_passport);
                $('#student_birth_date').val(response.student_birth_date);
                $('#student_mobile').val(response.student_mobile);
                $('#student_email').val(response.student_email);
                $('#student_gender').val(response.student_gender);
                $('#student_company').val(response.student_company);
                $('#student_position').val(response.student_position);
                $('#student_experience').val(response.student_experience);
                $('#student_username').val(response.student_username);
                $('#student_bio').val(response.student_bio);
                $('#student_password_key').val('');
                
                // 1. ไม่ต้องแสดงรหัสผ่านเมื่อดึงข้อมูลมา
                $('#student_password').val('');

                // จัดการที่อยู่
                if (response.student_address) {
                    const addressParts = response.student_address.split(',').map(part => part.trim());
                    $('#student_address_house_no').val(addressParts[0] || '');
                    $('#student_address_road').val(addressParts[1] || '');
                    $('#student_address_subdistrict').val(addressParts[2] || '');
                    $('#student_address_district').val(addressParts[3] || '');
                    $('#student_address_province').val(addressParts[4] || '');
                    $('#student_address_zipcode').val(addressParts[5] || '');
                }

                // จัดการประวัติการศึกษา
                if (response.student_education) {
                    try {
                        const educationData = JSON.parse(response.student_education);
                        $.each(educationData, function(level, data) {
                            $(`.education-input[data-level="${level}"][data-field="school"]`).val(data.school);
                            $(`.education-input[data-level="${level}"][data-field="major"]`).val(data.major);
                        });
                    } catch (e) {
                        console.error("Failed to parse education JSON:", e);
                    }
                }

                // จัดการไฟล์รูปภาพและเอกสารแนบ
                if (response.student_image_profile) {
                    showProfilePreview(response.student_image_profile);
                }
                if (response.student_card_front) {
                    showCardPreview(response.student_card_front, '#current-card-front');
                }
                if (response.student_card_back) {
                    showCardPreview(response.student_card_back, '#current-card-back');
                }
                
                // ✅ แก้ไข: กำหนดค่า currentStudentFiles จาก response โดยตรง
                if (response.student_attach_document && Array.isArray(response.student_attach_document)) {
                    currentStudentFiles = response.student_attach_document;
                    displaySelectedFiles1(); // เรียกใช้ฟังก์ชันแสดงผล
                    $('#student_attach_document_current').val(response.student_attach_document.join('|'));
                } else {
                    currentStudentFiles = [];
                    displaySelectedFiles1();
                    $('#student_attach_document_current').val('');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            swal({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'เกิดข้อผิดพลาดในการดึงข้อมูลนักเรียน'
            });
        }
    });
}

function saveStudent() {
    // เพิ่มการตรวจสอบความถูกต้องของข้อมูลก่อนส่ง
    if (!validateForm()) {
        return;
    }

    const formData = new FormData($("#studentForm")[0]);
    formData.append('action', 'saveStudent');

    // 3. แปลงข้อมูลการศึกษาให้อยู่ในรูปแบบ JSON string
    const education = {};
    $('.education-input').each(function() {
        const level = $(this).data('level');
        const field = $(this).data('field');
        if (!education[level]) {
            education[level] = {};
        }
        education[level][field] = $(this).val();
    });
    formData.append('student_education', JSON.stringify(education));

    const address = [
        $('#student_address_house_no').val(),
        $('#student_address_road').val(),
        $('#student_address_subdistrict').val(),
        $('#student_address_district').val(),
        $('#student_address_province').val(),
        $('#student_address_zipcode').val()
    ].join(', ');
    formData.append('student_address', address);

    // 2. เพิ่มไฟล์ที่เลือกใหม่เข้าไปใน FormData
    selectedStudentFiles.forEach(file => {
        formData.append('student_attach_document[]', file);
    });
    
    // ส่งข้อมูลไปยังเซิร์ฟเวอร์
    $.ajax({
        url: "/classroom/management/actions/student.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                swal({
                    icon: 'success',
                    title: 'บันทึกข้อมูลสำเร็จ',
                    text: response.message || 'บันทึกข้อมูลเรียบร้อย'
                });
                setTimeout(() => {
                    $(".systemModal").modal('hide');
                    if (window.tb_student) {
                        window.tb_student.ajax.reload(null, false);
                    }
                }, 2000);
            } else {
                swal({
                    icon: 'error',
                    title: 'บันทึกข้อมูลไม่สำเร็จ',
                    text: response.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            swal({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'เกิดข้อผิดพลาดในการส่งข้อมูล'
            });
        }
    });
}

// ฟังก์ชันสำหรับตรวจสอบความถูกต้องของฟอร์ม
function validateForm() {
    let isValid = true;

    // ล้างสถานะ invalid เดิมทั้งหมด
    $('#studentForm .form-control').removeClass('is-invalid');
    $('#address-invalid-feedback').hide();

    // ข้อมูลส่วนตัว
    const perfix = $('#student_perfix').val();
    if (!perfix) {
        $('#student_perfix').addClass('is-invalid');
        isValid = false;
    }

    const firstnameEn = $('#student_firstname_en').val();
    if (!firstnameEn) {
        $('#student_firstname_en').addClass('is-invalid');
        isValid = false;
    }

    const lastnameEn = $('#student_lastname_en').val();
    if (!lastnameEn) {
        $('#student_lastname_en').addClass('is-invalid');
        isValid = false;
    }

    const idcard = $('#student_idcard').val();
    if (!idcard || idcard.length !== 13) {
        $('#student_idcard').addClass('is-invalid');
        isValid = false;
    }

    const mobile = $('#student_mobile').val();
    if (!mobile || mobile.length !== 10) {
        $('#student_mobile').addClass('is-invalid');
        isValid = false;
    }

    const email = $('#student_email').val();
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
        $('#student_email').addClass('is-invalid');
        isValid = false;
    }

    const gender = $('#student_gender').val();
    if (!gender) {
        $('#student_gender').addClass('is-invalid');
        isValid = false;
    }
    
    // ที่อยู่
    const addressFields = [
        'student_address_house_no',
        'student_address_subdistrict',
        'student_address_district',
        'student_address_province',
        'student_address_zipcode'
    ];
    let addressValid = true;
    addressFields.forEach(id => {
        if (!$('#' + id).val()) {
            $('#' + id).addClass('is-invalid');
            addressValid = false;
        }
    });
    if (!addressValid) {
        $('#address-invalid-feedback').show();
        isValid = false;
    }

    // ข้อมูลผู้ใช้และระบบ
    const username = $('#student_username').val();
    const password = $('#student_password').val();
    const passwordKey = $('#student_password_key').val();

    if (!username) {
        $('#student_username').addClass('is-invalid');
        isValid = false;
    }

    if (!password || password.length < 6) {
        $('#student_password').addClass('is-invalid');
        isValid = false;
    }

    if (password !== passwordKey) {
        $('#student_password_key').addClass('is-invalid');
        isValid = false;
    }

    return isValid;
}

// 2. ฟังก์ชันสำหรับแสดงไฟล์ที่เลือกไว้และไฟล์เดิม
function displaySelectedFiles1() {
    const previewContainer1 = $('#document-preview-container');
    previewContainer1.empty();
    
    // แสดงไฟล์เดิมที่อยู่ในฐานข้อมูล
    currentStudentFiles.forEach((path, index) => {
        const filename = path.split('/').pop();
        const fileItem = $(`<div class="d-flex align-items-center mb-1"><span class="me-2">${filename}</span> <button type="button" class="btn btn-danger btn-sm delete-file" data-type="current" data-index="${index}">&times;</button></div>`);
        previewContainer1.append(fileItem);
    });

    // แสดงไฟล์ใหม่ที่เลือกไว้
    selectedStudentFiles.forEach((file, index) => {
        const fileItem = $(`<div class="d-flex align-items-center mb-1"><span class="me-2">${file.name}</span> <button type="button" class="btn btn-danger btn-sm delete-file" data-type="new" data-index="${index}">&times;</button></div>`);
        previewContainer1.append(fileItem);
    });

    // เพิ่ม event listener ให้กับปุ่มลบ
    previewContainer1.off('click', '.delete-file');
    previewContainer1.on('click', '.delete-file', function() {
        const type = $(this).data('type');
        const index = $(this).data('index');
        if (type === 'current') {
            currentStudentFiles.splice(index, 1);
        } else {
            selectedStudentFiles.splice(index, 1);
        }
        displaySelectedFiles1();
    });
}

// ฟังก์ชันสำหรับตั้งค่า preview ไฟล์
function setupFilePreview1() {
    $('#student_image_profile').on('change', function() {
        showProfilePreview(this.files[0]);
    });
    $('#student_card_front').on('change', function() {
        showCardPreview(this.files[0], '#current-card-front');
    });
    $('#student_card_back').on('change', function() {
        showCardPreview(this.files[0], '#current-card-back');
    });
    // เพิ่ม event listener สำหรับช่องอัปโหลดเอกสาร
    $('#student_attach_document').on('change', function() {
        handleMultipleFileSelection1(this.files);
    });
}

// ฟังก์ชันสำหรับจัดการการเลือกไฟล์หลายไฟล์
function handleMultipleFileSelection1(files) {
    for (let i = 0; i < files.length; i++) {
        selectedStudentFiles.push(files[i]);
    }
    displaySelectedFiles1();
    $('#student_attach_document').val('');
}

// ฟังก์ชันแสดงตัวอย่างรูปโปรไฟล์
function showProfilePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        $('#profile-img').attr('src', e.target.result).show();
        $('#upload-icon-overlay').hide();
    };
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

// ฟังก์ชันแสดงตัวอย่างรูปนามบัตร
function showCardPreview(file, targetId) {
    const reader = new FileReader();
    reader.onload = function(e) {
        $(targetId).html(`<img src="${e.target.result}" class="img-thumbnail mt-2" style="max-height: 200px;" alt="Image Preview">`);
    };
    if (file instanceof File) {
        reader.readAsDataURL(file);
    } else if (typeof file === 'string' && file.length > 0) {
        $(targetId).html(`<img src="${file}" class="img-thumbnail mt-2" style="max-height: 200px;" alt="Current Image">`);
    } else {
        $(targetId).html('');
    }
}
// --- ฟังก์ชันลบนักเรียน ---
function deleteStudent(student_id) {
    swal({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการลบข้อมูลนักเรียนท่านนี้ใช่ไหม? การกระทำนี้ไม่สามารถย้อนกลับได้",
        icon: 'warning',
        buttons: ["ยกเลิก", "ใช่, ลบเลย!"],
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                data: {
                    action: "deleteStudent",
                    student_id: student_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal(
                            'ลบเรียบร้อย!',
                            response.message || 'ลบข้อมูลนักเรียนเรียบร้อยแล้ว',
                            'success'
                        );
                        if (window.tb_student) {
                            window.tb_student.ajax.reload(null, false);
                        }
                    } else {
                        swal(
                            'เกิดข้อผิดพลาด!',
                            'ไม่สามารถลบข้อมูลนักเรียนได้: ' + (response.message || 'ไม่ทราบสาเหตุ'),
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    swal(
                        'เกิดข้อผิดพลาด!',
                        'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                        'error'
                    );
                }
            });
        }
    });
}