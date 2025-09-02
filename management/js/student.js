function buildStudentPage() {
    $(".content-container").html(getStudentTemplate());
    buildStudent();
}
function getStudentTemplate() {
    return `
        <div class="filter">
            <a class="toggleFilter" title="Click to show or hide filter"><i class="fas fa-sliders-h"></i></a>
            <label class="countFilter">0</label>
            <div class="row">
                <div class="col-md-5ths col-sm-4 col-xs-12">
                    <p style="margin:10px auto;">
                        <i class="far fa-calendar"></i>
                        <span lang="en">Register Date</span>
                    </p>
                    <input type="text" id="filter_date" class="form-control filter-object" placeholder="All">
                </div>
                <div class="col-md-5ths col-sm-4 col-xs-12">
                    <p style="margin:10px auto;">
                        <i class="fas fa-cubes"></i>
                        <span lang="en">Group</span>
                    </p>
                    <select class="form-control" id="filter_group"></select>
                </div>
            </div>
        </div>
        <div class="rows">
            <div class="columns">
                <div class="cards el-classroom el-classroomlead" el="lead">
                    <span lang="en">Lead</span> <span class="count countlead">0</span>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomfollow" el="follow">
                    <span lang="en">Follow</span> <span class="count countrefollow">0</span>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomregister" el="register">
                    <span lang="en">Register</span> <span class="count countregister">0</span>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomwaiting active" el="waiting">
                    <span lang="en">Waiting Approve</span> <span class="count countwaiting">0</span>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomapprove" el="approve">
                    <span lang="en">Approve</span> <span class="count countapprove">0</span>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroompayment" el="payment">
                    <span lang="en">Payment</span> <span class="count countpayment">0</span>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomnotapprove last-item" el="notapprove">
                    <div class="inner">
                        <span lang="en">Not approved</span> <span class="count countnotapprove">0</span>
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomnotpayment last-item" el="notpayment" style="margin-left:0px;">
                    <div class="inner">
                        <span lang="en">Not payment</span> <span class="count countnotpayment">0</span>
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="cards el-classroom el-classroomcancel last-item" el="cancel" style="margin-left:0px;">
                    <div class="inner">
                        <span lang="en">Cancel</span> <span class="count countcancel">0</span>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="filter_status" value="waiting">
        <table class="table table-border" id="tb_student">
            <thead>
                <tr>
                    <th></th>
                    <th style="width:50px;" class="text-center">
                        <div class="checkbox checkbox-warning object_trainee">
                            <input class="styled student_all" id="student_all" type="checkbox">
                            <label for="student_all"></label>
                        </div>
                    </th>
                    <th lang="en">Register Date</th>
                    <th lang="en">Student</th>
                    <th lang="en">Group</th>
                    <th lang="en">Lead</th>
                    <th lang="en">Approve</th>
                    <th lang="en">Payment</th>
                    <th lang="en">Remark</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
$(document).on("click", ".el-classroom", function(){
    var el = $(this).attr("el");
    $(".el-classroom").removeClass("active");
    $(this).addClass("active");
    $("#filter_status").val(el);
    buildStudent();
});
let tb_student;
function buildStudent() {
    if ($.fn.DataTable.isDataTable('#tb_student')) {
        $('#tb_student').DataTable().ajax.reload(null, false);
    } else {
		tb_student = $('#tb_student').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50, 100, 150, 250, 500, 1000, -1], [50, 100, 150, 250, 500, 1000, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/student.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildStudent";
                    data.classroom_id = classroom_id;
                    data.filter_status = $("#filter_status").val();
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
			"order": [[2,'desc']],
			"columns": [{ 
                "targets": 0,
                "data": "join_id",
                "visible": false,
            },{ 
                "targets": 1,
                "data": "student_firstname_en",
                "render": function (data,type,row,meta) {
                    var {
						student_firstname_en, student_lastname_en, student_firstname_th, student_lastname_th,
						student_nickname_en, student_nickname_th, student_gender = 'O', student_idcard,
						student_passport, student_image_profile, student_email, student_mobile,
						student_birth_date, student_age, student_username, student_password, group_color
					} = row;
                    var student_gender = {
						'M': '<div class="text-info"><i class="fas fa-male"></i> <span lang="en">Male</span></div>',
						'F': '<div class="text-danger"><i class="fas fa-female"></i> <span lang="en">Female</span></div>',
						'O': '<div class="text-grey"><i class="fas fa-venus-mars"></i> <span lang="en">Other</span></div>'
					}[student_gender];
                    var img_error = {
						'M': '/images/default.png',
						'F': '/images/female.png',
						'O': '/images/icon-01.png'
					}[student_gender] || '/images/default.png';
					return `
						<div class="profile">
							<div class="image" style="border-color:${group_color};">
								<img src="${student_image_profile}" onerror="this.src='${img_error}'" alt="Profile Image">
							</div>
							${(student_firstname_en || student_lastname_en) ? `
							<div class="text-orange text-center" style="margin-bottom:10px;">
								<b>${student_firstname_en || ''} ${student_lastname_en || ''}</b>
							</div>` : ''}
							${(student_firstname_th || student_lastname_th) ? `
							<div class="text-orange text-center" style="margin-bottom:10px;">
								<b>${student_firstname_th || ''} ${student_lastname_th || ''}</b>
							</div>` : ''}
							<div class="text-center" style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap; font-weight: 600;">
								${student_nickname_en ? `<span><i class="fas fa-user-tag"></i> ${student_nickname_en}</span>` : ''}
								${student_nickname_th ? `<span><i class="fas fa-user-tag"></i> ${student_nickname_th}</span>` : ''}
								${student_gender || ''}
							</div>
						<div style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
							<i class="fas fa-phone-volume"></i>
							<span>${student_mobile || ''}</span>
						</div>
						<div style="font-size:11px; display:flex; gap:6px; word-break: break-all;">
							<i class="fas fa-envelope-open-text"></i>
							<span>${student_email || ''}</span>
						</div>
						${( (student_birth_date && student_birth_date !== '0000/00/00') || student_age ) ? `
							<div style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
								<i class="fas fa-birthday-cake"></i>
								<span>${student_birth_date || ''}</span>
								<span lang="en" style="margin-left:6px;">Age</span> <span>${student_age || ''}</span>
							</div>` : ''}
						${(student_username || password_original) ? `
							<div style="font-size:11px; margin-top:10px;">
							${student_username ? `
								<div style="display:flex; gap:6px; word-break: break-all;">
									<i class="fas fa-user-circle"></i>
									<span class="copy-text" data-copy="${student_username}">
									${student_username}
									<a href="javascript:void(0);" onclick="copyToClipboard('${student_username}')">
										<i class="far fa-copy"></i>
									</a>
									</span>
								</div>` : ''}
							${student_password ? `
								<div style="display:flex; gap:6px; word-break: break-all;">
									<i class="fas fa-unlock-alt"></i>
									<span class="copy-text" data-copy="${student_password}">
									${student_password}
									<a href="javascript:void(0);" onclick="copyToClipboard('${student_password}')">
										<i class="far fa-copy"></i>
									</a>
									</span>
								</div>` : ''}
							</div>` : ''}
						</div>
					`;
                }
            },{ 
                "targets": 2,
                "data": "register_date",
                "render": function (data,type,row,meta) {	
                    let register_by = row['register_by'];
					var val = data.split(' ');
					return `
                        <div><i class="far fa-calendar"></i> ${val[0]}</div>
                        <div><i class="far fa-clock"></i> ${val[1]}</div>
                        <div class="text-grey" style="font-size:10px; margin-top:15px;">
							<i class="fas fa-sign-in-alt"></i> ${(register_by == 0) ? 'Student Register' : 'Invite From Staff'}
						</div>
                    `;
                }
            },{ 
                "targets": 3,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 4,
                "data": "group_name",
            },{ 
                "targets": 5,
                "data": "invite_status",
                "render": function (data,type,row,meta) {	
                    let invite_status = row['invite_status'];
                    let invite_date = row['invite_date'];
                    let invite_by = row['invite_by'];
                    let join_id = row['join_id'];
                    let mockup = "";
                    if(invite_status == 0) {
                        mockup += `
                            <div class="nowrap">
                                <button type="button" class="btn btn-circle btn-green" onclick="confirmStudent(${join_id}, 'Y')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-circle btn-red" onclick="confirmStudent(${join_id}, 'N')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        mockup += `
                            <button type="button" class="btn btn-circle btn-white" onclick="confirmStudent(${join_id}, 'W')">
                                <i class="fas fa-sort-amount-down-alt"></i>
                            </button>
                            <button type="button" class="btn btn-circle btn-${invite_status == 1 ? "red" : "green"}" onclick="confirmStudent(${join_id}, '${invite_status == 1 ? "N" : "Y"}')">
                                <i class="fas fa-${invite_status == 1 ? "times" : "check"}"></i>
                            </button>
                        `;
                        const isConfirmed = invite_status == 1;
						const text_class = isConfirmed ? "text-green" : "text-red";
						const text_display = isConfirmed ? "Confirmed" : "Cancel";
						const text_icon = isConfirmed ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
						mockup += `
							<ul class="list-group mt-2 text-xs" style="margin-top:15px; width:125px;">
								<li class="list-group-item border-0 p-0" style="padding:0; border:none;">
									<p class="${text_class} line-clamp1">
										${text_icon} <span lang="en">${text_display}</span>
									</p>
									<div class="${text_class} line-clamp1">
										<i class="far fa-user-circle"></i> ${invite_by}
									</div>
									<div class="${text_class} nowrap">
										<i class="far fa-calendar-check"></i> ${invite_date}
									</div>
								</li>
							</ul>
						`;
                    }
					return mockup;
                }
            },{ 
                "targets": 6,
                "data": "approve_status",
                "render": function (data,type,row,meta) {	
					let approve_status = row['approve_status'];
                    let approve_date = row['approve_date'];
                    let approve_by = row['approve_by'];
                    let join_id = row['join_id'];
                    let invite_status = row['invite_status'];
                    let mockup = "";
                    if(invite_status == 1) {
                        if(approve_status == 0) {
                            mockup += `
                                <div class="nowrap">
                                    <button type="button" class="btn btn-circle btn-green" onclick="approveStudent(${join_id}, 'Y')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-circle btn-red" onclick="approveStudent(${join_id}, 'N')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            mockup += `
                                <button type="button" class="btn btn-circle btn-white" onclick="approveStudent(${join_id}, 'W')">
                                    <i class="fas fa-sort-amount-down-alt"></i>
                                </button>
                                <button type="button" class="btn btn-circle btn-${approve_status == 1 ? "red" : "green"}" onclick="approveStudent(${join_id}, '${approve_status == 1 ? "N" : "Y"}')">
                                    <i class="fas fa-${approve_status == 1 ? "times" : "check"}"></i>
                                </button>
                            `;
                            const isConfirmed = approve_status == 1;
                            const text_class = isConfirmed ? "text-green" : "text-red";
                            const text_display = isConfirmed ? "Approve" : "Not Approve";
                            const text_icon = isConfirmed ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
                            mockup += `
                                <ul class="list-group mt-2 text-xs" style="margin-top:15px; width:125px;">
                                    <li class="list-group-item border-0 p-0" style="padding:0; border:none;">
                                        <p class="${text_class} line-clamp1">
                                            ${text_icon} <span lang="en">${text_display}</span>
                                        </p>
                                        <div class="${text_class} line-clamp1">
                                            <i class="far fa-user-circle"></i> ${approve_by}
                                        </div>
                                        <div class="${text_class} nowrap">
                                            <i class="far fa-calendar-check"></i> ${approve_date}
                                        </div>
                                    </li>
                                </ul>
                            `;
                        }
                    }
					return mockup;
                }
            },{ 
                "targets": 7,
                "data": "payment_status",
                "render": function (data,type,row,meta) {	
					let payment_status = row['payment_status'];
                    let payment_status_date = row['payment_status_date'];
                    let payment_status_by = row['payment_status_by'];
                    let join_id = row['join_id'];
                    let approve_status = row['approve_status'];
                    let mockup = "";
                    if(approve_status == 1) {
                        if(payment_status == 0) {
                            mockup += `
                                <div class="nowrap">
                                    <button type="button" class="btn btn-circle btn-green" onclick="paymentStudent(${join_id}, 'Y')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-circle btn-red" onclick="paymentStudent(${join_id}, 'N')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            mockup += `
                                <button type="button" class="btn btn-circle btn-white" onclick="paymentStudent(${join_id}, 'W')">
                                    <i class="fas fa-sort-amount-down-alt"></i>
                                </button>
                                <button type="button" class="btn btn-circle btn-${payment_status == 1 ? "red" : "green"}" onclick="paymentStudent(${join_id}, '${payment_status == 1 ? "N" : "Y"}')">
                                    <i class="fas fa-${payment_status == 1 ? "times" : "check"}"></i>
                                </button>
                            `;
                            const isConfirmed = payment_status == 1;
                            const text_class = isConfirmed ? "text-green" : "text-red";
                            const text_display = isConfirmed ? "Paymented" : "Not Paymented";
                            const text_icon = isConfirmed ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
                            mockup += `
                                <ul class="list-group mt-2 text-xs" style="margin-top:15px; width:125px;">
                                    <li class="list-group-item border-0 p-0" style="padding:0; border:none;">
                                        <p class="${text_class} line-clamp1">
                                            ${text_icon} <span lang="en">${text_display}</span>
                                        </p>
                                        <div class="${text_class} line-clamp1">
                                            <i class="far fa-user-circle"></i> ${payment_status_by}
                                        </div>
                                        <div class="${text_class} nowrap">
                                            <i class="far fa-calendar-check"></i> ${payment_status_date}
                                        </div>
                                    </li>
                                </ul>
                            `;
                        }
                    }
					return mockup;
                }
            },{ 
                "targets": 8,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 9,
                "data": "join_id",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-red btn-circle" onclick="delStudent(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            },{ 
                "targets": 10,
                "data": "student_firstname_en",
                "visible": false,
            },{ 
                "targets": 11,
                "data": "student_lastname_en",
                "visible": false,
            },{ 
                "targets": 12,
                "data": "student_firstname_th",
                "visible": false,
            },{ 
                "targets": 13,
                "data": "student_lastname_th",
                "visible": false,
            },{ 
                "targets": 14,
                "data": "student_nickname_en",
                "visible": false,
            },{ 
                "targets": 15,
                "data": "student_nickname_th",
                "visible": false,
            },{ 
                "targets": 16,
                "data": "student_mobile",
                "visible": false,
            },{ 
                "targets": 17,
                "data": "student_email",
                "visible": false,
            },{ 
                "targets": 18,
                "data": "student_idcard",
                "visible": false,
            },{ 
                "targets": 19,
                "data": "student_passport",
                "visible": false,
            }]
        });
        $('div#tb_student_filter.dataTables_filter label input').remove();
        $('div#tb_student_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green hidden" style="font-size:12px;" onclick="manageStudent('')"><i class="fas fa-plus"></i> <span lang="en">Student</span></button> 
            <button type="button" class="btn btn-white text-green" style="font-size:12px;" onclick="importStudent('')"><i class="fas fa-file-excel"></i> <span lang="en">Import</span></button> 
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
function confirmStudent(join_id, option) {
    event.stopPropagation();
    var title = ``;
    if(option == "Y") {
        title = `Move to Waiting approve station`;
    } else if(option == "N") {
        title = `Move to Cancel station`;
    } else if(option == "W") {
        title = `Move to Lead station`;
    }
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: title,
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                data: {
                    action: 'confirmStudent',
                    join_id: join_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){	
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                        buildStudent();
                    }else{
                        swal({type: 'error',title: "Sorry...",text: "Something went wrong!",timer: 3000});
                    }
                }
            });
        } else {
            swal.close();
        }
    });
}
function approveStudent(join_id, option) {
    event.stopPropagation();
    var title = ``;
    if(option == "Y") {
        title = `Move to Payment station`;
    } else if(option == "N") {
        title = `Move to Not Approve station`;
    } else if(option == "W") {
        title = `Move to Waiting approve station`;
    }
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: title,
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                data: {
                    action: 'approveStudent',
                    join_id: join_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){	
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                        buildStudent();
                    } else {
                        swal({type: 'error',title: "Sorry...",text: "Something went wrong!",timer: 3000});
                    }
                }
            });
        } else {
            swal.close();
        }
    });
}
function paymentStudent(join_id, option) {
    event.stopPropagation();
    var title = ``;
    if(option == "Y") {
        title = `Confirm Payment`;
    } else if(option == "N") {
        title = `Move to Not Payment station`;
    } else if(option == "W") {
        title = `Move to Approve station`;
    }
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: title,
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                data: {
                    action: 'paymentStudent',
                    join_id: join_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){	
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                        buildStudent();
                    }else{
                        swal({type: 'error',title: "Sorry...",text: "Something went wrong!",timer: 3000});
                    }
                }
            });
        } else {
            swal.close();
        }
    });
}
function importStudent() {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Import Student</h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="saveImport();">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <form id="import_form">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h5 lang="en">Download Import Template</h5>
					<a class="btn btn-white btn-lg text-green" href="/classroom/export/StudentsTemplate.xlsx" target="_blank"><i class="fas fa-download"></i> <span lang="en">Download</span></a>
				</div>
				<div class="col-sm-12">
					<div style="border:2px dotted #00C292; padding:25px; border-radius:15px; margin:25px auto;">
						<h4 style="margin-top:10px;">
							<i class="fas fa-upload"></i> <span lang="en">Choose excel file for import Students</span>
						</h4>
						<input type="file" name="excel_file" id="excel_file" accept=".xlsx">
						<p style="margin:15px auto;"><i class="fas fa-file-excel"></i> <span lang="en">File upload</span> <code>.xlsx only.</code></p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<h5 class="text-orange"><i class="fas fa-info-circle"></i> <span lang="en">How to import data</span></h5>
					<p>1. <span lang="en">Download sample files from the link</span> <a href="/classroom/export/StudentsTemplate.xlsx" target="_blank" class="text-orange"><u class="text-green"><b>Download</b></u></a></p>
					<p>2. <span lang="en">Choose the file you want to import.</span></p>
					<p>3. <span lang="en">Press the <b class="text-orange">Import Data</b> button to import the data.</span></p>
				</div>
			</div>
		</form> 
    `);
    $("#excel_file").change(function(){
		const file = this.files[0];
		if (file) {
			const fileName = file.name;
			const fileExtension = fileName.split('.').pop().toLowerCase();
			if (fileExtension !== 'xlsx') {
				swal({
					type: 'warning',
					title: "Warning...",
					text: 'Only .xlsx files are allowed!',
					showConfirmButton: true,
					confirmButtonText: window.lang.translate("Ok"),
					confirmButtonColor: '#FBC02D',
				});
				$(this).val(''); 
			}
		}
	});
}
function saveImport() {
    if($("#excel_file").val() == "") {
        swal({type: 'warning',title: "Warning...",text: 'Please choose files to import.',showConfirmButton: false,timer: 2000});
    } else {
        $(".loader").addClass("active");
        var excel_file = new FormData(document.getElementById("import_form"));
        excel_file.append('classroom_id', classroom_id);
		$.ajax({
			url: "/classroom/management/actions/student.php?action=saveImport",
			type: "POST",
			data: excel_file,
			processData: false,
			contentType: false,
			dataType: "JSON",
			type: 'POST',
			success: function(result){
				$(".loader").removeClass("active");
                if(result.status === true){	
                    $(".systemModal").modal("hide");
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                    $(".el-classroomlead").click();
                    buildStudent();
                }else{
                    swal({type: 'error',title: "Sorry...",text: "Something went wrong!",timer: 2000});
                }
            }
        });
    }
}
function delStudent(join_id) {
    event.stopPropagation();
    swal({
        html:true,
        title: window.lang.translate("Are you sure?"),
        text: 'Do you really want to delete these records? </br> This process cannot be undone.',
        type: "error",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Delete"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF6666',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/student.php",
                type: "POST",
                data: {
                    action:'delStudent',
                    join_id: join_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildGroup();
                    }else{
                        swal({type: 'error',title: "Sorry...",text: "Something went wrong!",timer: 2000});
                    }
                }
            });
        } else {
            swal.close();
        }
    });
}
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
        }).catch(err => {
            console.error('Copy failed', err);
        });
    } else {
        const textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.style.position = "fixed"; 
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand("copy");
        } catch (err) {
            console.error("Fallback copy failed", err);
        }
        document.body.removeChild(textarea);
    }
}