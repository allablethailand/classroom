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
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="addTeacherOptions()"><i class="fas fa-plus"></i> <span lang="en">Teacher</span></button>
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

function manageTeacher(teacher_id) {
    let classroom_id = $("#classroom_id").val();
    $.redirect(`form?type=teacher&id=${teacher_id}`,{classroom_id: classroom_id},'post','_blank');
}

function deleteTeacher(teacher_id) {
    // เปลี่ยนจาก Swal.fire เป็น swal
    swal({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการลบข้อมูลครูท่านนี้ใช่ไหม? การกระทำนี้ไม่สามารถย้อนกลับได้",
        type: 'warning', // เปลี่ยน icon เป็น type
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        closeOnConfirm: false, // ปิดการปิด pop-up อัตโนมัติหลังกดปุ่ม
        closeOnCancel: true
    },
    // เปลี่ยน .then((result) => {...}) เป็น callback function
    function(isConfirm) {
        if (isConfirm) {
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
                        // เปลี่ยน Swal.fire เป็น swal
                        swal(
                            'ลบเรียบร้อย!',
                            response.message,
                            'success'
                        );
                        // Reload ตารางหลังจากลบข้อมูล
                        if (window.tb_teacher) {
                            window.tb_teacher.ajax.reload(null, false);
                        }
                    } else {
                        // เปลี่ยน Swal.fire เป็น swal
                        swal(
                            'เกิดข้อผิดพลาด!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    // เปลี่ยน Swal.fire เป็น swal
                    swal(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถลบข้อมูลได้',
                        'error'
                    );
                }
            });
        }
    });
}

// ฟังก์ชันใหม่สำหรับการแสดงตัวเลือกการเพิ่มครู
function addTeacherOptions() {
    // เปลี่ยน Swal.fire เป็น swal
    swal({
        title: 'เลือกประเภทครูที่ต้องการเพิ่ม',
        showCancelButton: true,
        showConfirmButton: false, // ใช้ showConfirmButton: false เพื่อซ่อนปุ่ม "OK"
        cancelButtonText: 'ยกเลิก',
        html: true, // เพิ่ม html: true เพื่อรองรับการแสดงผล HTML
        customClass: 'bottom-custumer-employee',
        text: `
            <div class="d-flex justify-content-around mt-3">
                <button id="add-employee" class="btn btn-info mx-2" style="width:80%;"><i class="fas fa-users" ></i> เพิ่มจาก Employee</button>
                <button id="add-customer" class="btn btn-primary mx-2" style="width:80%;"><i class="fas fa-user-tie" ></i> เพิ่มจาก Customer</button>
                <button id="add-manual" class="btn btn-success mx-2"><i class="fas fa-plus-circle"></i> กรอกข้อมูลเอง</button>
            </div>
        `,
        // เพิ่ม callback function เพื่อกำหนด Event listener หลังจาก pop-up เปิด
    }, function() {
        // Callback function นี้จะทำงานเมื่อ pop-up ปิด
        // เนื่องจาก SweetAlert เก่าไม่รองรับ didOpen, เราจะใช้ setTimeout แทน
    });
    // ใช้ setTimeout เพื่อให้แน่ใจว่า DOM ของ SweetAlert โหลดเสร็จแล้ว
    setTimeout(function() {
        $('#add-employee').on('click', function() {

            // console.log("hello");
            swal.close();
            showAddEmployeePopup();
        });

        $('#add-customer').on('click', function() {
            swal.close();
            showAddCustomerPopup();
        });

        $('#add-manual').on('click', function() {
            swal.close();
            let classroom_id = $("#classroom_id").val();
            $.redirect(`form?type=teacher&id=`,{classroom_id: classroom_id},'post','_blank');
        });
    }, 500); // กำหนดดีเลย์ประมาณ 500ms
}

// ฟังก์ชันสำหรับแสดง Pop-up และตาราง Employee
function showAddEmployeePopup() {
    // HTML string for the modal component
    const employeeModalHtml = `
        <div class="modal fade" id="employeeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">เพิ่มครูจาก Employee</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tb_employees">
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
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Append the modal HTML to the body
    $('body').append(employeeModalHtml);

    // Get the modal element
    const employeeModal = $('#employeeModal');

    // Show the modal
    employeeModal.modal('show');

    // Handle modal events
    employeeModal.on('shown.bs.modal', function() {
        // Initialize DataTables inside the modal
        const employeeTable = $('#tb_employees').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/classroom/management/actions/teacher.php",
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

        // Add event listener for the "Add" button
        employeeTable.on('click', '.add-from-emp', function() {
            const ref_id = $(this).data('id');
            const ref_type = $(this).data('type');
            selectPerson(ref_id, ref_type);
        });
    });

    // Clean up the modal element from the DOM when it's closed
    employeeModal.on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// ฟังก์ชันสำหรับแสดง Pop-up และตาราง Customer
function showAddCustomerPopup() {
    // HTML string for the modal component
    const customerModalHtml = `
        <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">เพิ่มครูจาก Customer</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tb_customers">
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

    // Append the modal HTML to the body
    $('body').append(customerModalHtml);

    // Get the modal element
    const customerModal = $('#customerModal');

    // Show the modal
    customerModal.modal('show');

    // Handle modal events
    customerModal.on('shown.bs.modal', function() {
        // Initialize DataTables inside the modal
        const customerTable = $('#tb_customers').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/classroom/management/actions/teacher.php",
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

        // Add event listener for the "Add" button
        customerTable.on('click', '.add-from-cus', function() {
            const ref_id = $(this).data('id');
            const ref_type = $(this).data('type');
            selectPerson(ref_id, ref_type);
        });
    });

    // Clean up the modal element from the DOM when it's closed
    customerModal.on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// ฟังก์ชันใหม่ที่ใช้แทนที่ Event handler เก่า
// ฟังก์ชันใหม่ที่ใช้แทนที่ Event handler เก่า
function selectPerson(id, type) {
    const classroom_id = $("#classroom_id").val();

    // Determine which modal to hide
    const modalToHide = (type === 'employee') ? $('#employeeModal') : $('#customerModal');
    modalToHide.modal('hide');

    swal({
        title: 'ยืนยันการเพิ่มข้อมูล',
        text: `คุณต้องการเพิ่ม ${type} นี้เป็นครูใช่หรือไม่?`,
        type: 'info',
        showCancelButton: true,
        confirmButtonText: 'ใช่',
        cancelButtonText: 'ยกเลิก',
        closeOnConfirm: false,
        closeOnCancel: true,
        customClass: 'swal-high-zindex'
    },
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/teacher.php",
                type: "POST",
                dataType: "json",
                data: {
                    action: "addTeacherFromRef",
                    ref_id: id,
                    ref_type: type,
                    classroom_id: classroom_id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        swal('สำเร็จ', response.message, 'success');
                        
                        // Reload the main teacher table
                        if (window.tb_teacher) {
                             window.tb_teacher.ajax.reload(null, false);
                        }
                        
                        // Re-open the correct modal after a short delay
                        setTimeout(() => {
                            if (type === 'employee') {
                                showAddEmployeePopup();
                            } else if (type === 'customer') {
                                showAddCustomerPopup();
                            }
                        }, 500);
                        
                    } else {
                        // Re-open the modal on error
                        if (type === 'employee') {
                            showAddEmployeePopup();
                        } else if (type === 'customer') {
                            showAddCustomerPopup();
                        }
                        swal('เกิดข้อผิดพลาด', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // Re-open the modal on server error
                    if (type === 'employee') {
                        showAddEmployeePopup();
                    } else if (type === 'customer') {
                        showAddCustomerPopup();
                    }
                    swal('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }
    });
}