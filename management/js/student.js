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
                "data": "date_create"
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
            <button type="button" class="btn btn-green" onclick="addStudentOptions()"><i class="fas fa-plus"></i> Student</button>
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

// ฟังก์ชันสำหรับเลือกและบันทึกข้อมูลนักเรียน
function selectStudent(id, type) {
    const classroom_id = $("#classroom_id").val();

    // กำหนดว่า Modal ไหนที่จะถูกซ่อน
    const modalToHide = (type === 'employee') ? $('#employeeStudentModal') : $('#customerStudentModal');
    modalToHide.modal('hide');

    // เปลี่ยน Swal.fire เป็น swal (รุ่นเก่า)
    swal({
        title: 'ยืนยันการเพิ่มข้อมูล',
        text: `คุณต้องการเพิ่ม ${type} นี้เป็นนักเรียนใช่หรือไม่?`,
        type: 'info', // ใช้ 'info' แทน 'question' เพื่อให้รองรับ SweetAlert รุ่นเก่า
        showCancelButton: true,
        confirmButtonText: 'ใช่',
        cancelButtonText: 'ยกเลิก',
        closeOnConfirm: false,
        closeOnCancel: true,
        customClass: 'swal-high-zindex'
    },
    // ใช้ callback function แทน .then()
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                dataType: "json",
                data: {
                    action: "addStudentFromRef",
                    ref_id: id,
                    ref_type: type,
                    classroom_id: classroom_id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        swal('สำเร็จ', response.message, 'success');
                        
                        // Reload ตารางหลักนักเรียน
                        if (window.tb_student) {
                            window.tb_student.ajax.reload(null, false);
                        }
                        
                        // เปิด Modal ที่ถูกต้องกลับมาหลังจากดีเลย์สั้นๆ
                        setTimeout(() => {
                            if (type === 'employee') {
                                showAddEmployeeStudentPopup();
                            } else if (type === 'customer') {
                                showAddCustomerStudentPopup();
                            }
                        }, 500);
                        
                    } else {
                        // เปิด Modal กลับมาเมื่อเกิดข้อผิดพลาด
                        if (type === 'employee') {
                            showAddEmployeeStudentPopup();
                        } else if (type === 'customer') {
                            showAddCustomerStudentPopup();
                        }
                        swal('เกิดข้อผิดพลาด', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // เปิด Modal กลับมาเมื่อเกิดข้อผิดพลาดจากเซิร์ฟเวอร์
                    if (type === 'employee') {
                        showAddEmployeeStudentPopup();
                    } else if (type === 'customer') {
                        showAddCustomerStudentPopup();
                    }
                    swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }
    });
}

// ฟังก์ชันสำหรับแสดง Pop-up และตาราง Employee เพื่อเพิ่มเป็นนักเรียน
function showAddEmployeeStudentPopup() {
    // HTML string สำหรับ Modal component
    const employeeStudentModalHtml = `
        <div class="modal fade" id="employeeStudentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">เพิ่มนักเรียนจาก Employee</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tb_employees_student">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ชื่อ - นามสกุล</th>
                                        <th>เบอร์โทร</th>
                                        <th>อีเมล</th>
                                        <th><i class="fas fa-check-circle"></i></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // เพิ่ม Modal HTML ลงใน body
    $('body').append(employeeStudentModalHtml);

    // รับ element ของ Modal
    const employeeStudentModal = $('#employeeStudentModal');

    // แสดง Modal
    employeeStudentModal.modal('show');

    // จัดการ events ของ Modal
    employeeStudentModal.on('shown.bs.modal', function() {
        // Initialize DataTables ภายใน Modal
        const employeeStudentTable = $('#tb_employees_student').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/classroom/management/actions/student.php",
                "type": "POST",
                "data": { action: "getEmployees" }
            },
            "columns": [
                { "data": "emp_id" },
                { "data": "full_name" },
                { "data": "tel_office" },
                { "data": "email" },
                {
                    "data": "emp_id",
                    "render": function(data, type, row) {
                        return `<button class="btn btn-success btn-circle add-from-emp" data-id="${data}" data-type="employee"><i class="fas fa-check"></i></button>`;
                    }
                }
            ],
            "language": default_language,
            "responsive": true,
            "deferRender": true
        });

        // เพิ่ม Event listener สำหรับปุ่ม "เพิ่ม"
        employeeStudentTable.on('click', '.add-from-emp', function() {
            const ref_id = $(this).data('id');
            const ref_type = $(this).data('type');
            selectStudent(ref_id, ref_type);
        });
    });

    // ล้าง element Modal ออกจาก DOM เมื่อปิด
    employeeStudentModal.on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// ฟังก์ชันสำหรับแสดง Pop-up และตาราง Customer เพื่อเพิ่มเป็นนักเรียน
function showAddCustomerStudentPopup() {
    // HTML string สำหรับ Modal component
    const customerStudentModalHtml = `
        <div class="modal fade" id="customerStudentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">เพิ่มนักเรียนจาก Customer</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tb_customers_student">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ชื่อ</th>
                                        <th>เบอร์โทร</th>
                                        <th>อีเมล</th>
                                        <th><i class="fas fa-check-circle"></i></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // เพิ่ม Modal HTML ลงใน body
    $('body').append(customerStudentModalHtml);

    // รับ element ของ Modal
    const customerStudentModal = $('#customerStudentModal');

    // แสดง Modal
    customerStudentModal.modal('show');

    // จัดการ events ของ Modal
    customerStudentModal.on('shown.bs.modal', function() {
        // Initialize DataTables ภายใน Modal
        const customerStudentTable = $('#tb_customers_student').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/classroom/management/actions/student.php",
                "type": "POST",
                "data": { action: "getCustomers" }
            },
            "columns": [
                { "data": "cus_id" },
                { "data": "cus_name_th" },
                { "data": "cus_tel_no" },
                { "data": "cus_email" },
                {
                    "data": "cus_id",
                    "render": function(data, type, row) {
                        return `<button class="btn btn-success btn-circle add-from-cus" data-id="${data}" data-type="customer"><i class="fas fa-check"></i></button>`;
                    }
                }
            ],
            "language": default_language,
            "responsive": true,
            "deferRender": true
        });

        // เพิ่ม Event listener สำหรับปุ่ม "เพิ่ม"
        customerStudentTable.on('click', '.add-from-cus', function() {
            const ref_id = $(this).data('id');
            const ref_type = $(this).data('type');
            selectStudent(ref_id, ref_type);
        });
    });

    // ล้าง element Modal ออกจาก DOM เมื่อปิด
    customerStudentModal.on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// ฟังก์ชันใหม่สำหรับการแสดงตัวเลือกการเพิ่มนักเรียน
function addStudentOptions() {
    swal({
        title: 'เลือกประเภทนักเรียนที่ต้องการเพิ่ม',
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonText: 'ยกเลิก',
        html: true,
        customClass: 'bottom-custumer-employee',
        text: `
            <div class="d-flex justify-content-around mt-3">
                <button id="add-employee-student" class="btn btn-info mx-2" style="width:80%;"><i class="fas fa-users" ></i> เพิ่มจาก Employee</button>
                <button id="add-customer-student" class="btn btn-primary mx-2" style="width:80%;"><i class="fas fa-user-tie" ></i> เพิ่มจาก Customer</button>
                <button id="add-manual-student" class="btn btn-success mx-2"><i class="fas fa-plus-circle"></i> กรอกข้อมูลเอง</button>
            </div>
        `
    });

    setTimeout(function() {
        $('#add-employee-student').on('click', function() {
            swal.close();
            showAddEmployeeStudentPopup();
        });

        $('#add-customer-student').on('click', function() {
            swal.close();
            showAddCustomerStudentPopup();
        });

        $('#add-manual-student').on('click', function() {
            swal.close();
            let classroom_id = $("#classroom_id").val();
            $.redirect(`form?type=student&id=`,{classroom_id: classroom_id},'post','_self');
        });
    }, 500); 
}

// --- ฟังก์ชันลบนักเรียน ---
function deleteStudent(student_id) {
    // เปลี่ยน Swal.fire เป็น swal (รุ่นเก่า)
    swal({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการลบข้อมูลนักเรียนท่านนี้ใช่ไหม? การกระทำนี้ไม่สามารถย้อนกลับได้",
        type: 'warning', // เปลี่ยน icon เป็น type
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    // ใช้ callback function แทน .then()
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                data: {
                    action: "deleteStudent",
                    student_id: student_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') { // เปลี่ยน response.success เป็น response.status === 'success' เพื่อให้เหมือนฝั่งครู
                        swal(
                            'ลบเรียบร้อย!',
                            response.message,
                            'success'
                        );
                        if (window.tb_student) {
                            window.tb_student.ajax.reload(null, false);
                        }
                    } else {
                        swal(
                            'เกิดข้อผิดพลาด!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    swal(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถลบข้อมูลนักเรียนได้',
                        'error'
                    );
                }
            });
        }
    });
}