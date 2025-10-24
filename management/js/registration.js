function buildRegistrationPage() {
    $(".content-container").html(getRegistrationTemplate());
    var start = moment().startOf('year');
    var end = moment().endOf('year');
    var quarter = moment().quarter();
    $('#filter_date').daterangepicker({
        startDate: start,
        endDate: end,
		showDropdowns: true,
		autoUpdateInput: false,
		opens: 'right',
		locale: {
			cancelLabel: 'Show all',
			applyLabel: 'Ok',
			format: 'DD/MM/YYYY',
		},
        ranges: {
            'Today': [moment()],
            'This week': [moment().startOf('week'), moment().endOf('week')],
			'This month': [moment().startOf('month'), moment().endOf('month')],
			'This quarter': [moment().quarter(quarter).startOf('quarter'), moment().quarter(quarter).endOf('quarter')],
			'This year': [moment().startOf('year'), moment().endOf('year')],
			'Last week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
			'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
			'Last quarter': [moment().subtract(1, 'quarter').startOf('quarter'), moment().subtract(1, 'quarter').endOf('quarter')],
			'Last year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
			'30 days ago': [moment().subtract(29, 'days'), moment()],
			'60 days ago': [moment().subtract(59, 'days'), moment()],
			'90 days ago': [moment().subtract(89, 'days'), moment()],
			'120 days ago': [moment().subtract(119, 'days'), moment()],
		}
	}, cb);
    $('#filter_date').on('hide.daterangepicker hideCalendar.daterangepicker ', function(ev, picker) {
		var st = picker.startDate.format('DD/MM/YYYY');
        var ed = picker.endDate.format('DD/MM/YYYY');
        if(st == ed) {
            var dt = st;
        } else {
            var dt = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
        }
		$(this).val(dt);
	});
	$('#filter_date').on('cancel.daterangepicker', function(ev, picker) {
		$(this).val('');
		buildRegistration();
	});
	$('#filter_date').on('apply.daterangepicker', function(ev, picker) {
		var st = picker.startDate.format('DD/MM/YYYY');
        var ed = picker.endDate.format('DD/MM/YYYY');
        if(st == ed) {
            var dt = st;
        } else {
            var dt = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
        }
		$(this).val(dt);
		buildRegistration();
	});	
    buildRegistration();
    buildChannelSelected();
}
function cb(start, end) {
	var st = start;
	var ed = end;
	if(st == ed) {
		var dt = st;
	} else {
		var dt = start + ' - ' + end;
	}
	$('#filter_date').val(dt);
};
function getRegistrationTemplate() {
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
                        <span lang="en">Channel</span>
                    </p>
                    <select class="form-control" id="filter_channel" onchange="buildRegistration();"></select>
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
                <div class="cards el-classroom el-classroompay" el="pay">
                    <span lang="en">Pay</span> <span class="count countpay">0</span>
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
        <table class="table table-border" id="tb_registration">
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
                    <th lang="en">Attach Files</th>
                    <th lang="en">Reference Person</th>
                    <th lang="en">Channel</th>
                    <th lang="en">Lead</th>
                    <th lang="en">Approve</th>
                    <th lang="en">Pay</th>
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
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
$(document).on("click", ".el-classroom", function() {
    var el = $(this).attr("el");
    $(".el-classroom").removeClass("active");
    $(this).addClass("active");
    $("#filter_status").val(el);
    buildRegistration();
});
let tb_registration;
function buildRegistration() {
    if ($.fn.DataTable.isDataTable('#tb_registration')) {
        $('#tb_registration').DataTable().ajax.reload(null, false);
    } else {
		tb_registration = $('#tb_registration').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50, 100, 150, 250, 500, 1000, -1], [50, 100, 150, 250, 500, 1000, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/registration.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildRegistration";
                    data.classroom_id = classroom_id;
                    data.filter_status = $("#filter_status").val();
                    data.filter_date = $("#filter_date").val();
                    data.filter_channel = $("#filter_channel").val();
				}
			},
			"language": default_language,
			"responsive": true,
			"searchDelay": 1000,
			"deferRender": false,
			"drawCallback": function(settings) {
				var lang = new Lang();
				lang.dynamic('th', '/js/langpack/th.json?v='+Date.now());
				lang.init({
					defaultLang: 'en'
				});
                buildSummaryRegistration();
			},
			"order": [[2,'desc']],
			"columns": [{ 
                "targets": 0,
                "data": "join_id",
                "visible": false,
            },{ 
                "targets": 1,
                "data": "join_id",
                "visible": false,
                "render": function (data,type,row,meta) {
					return ``;
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
                "data": "student_firstname_en",
                "render": function (data,type,row,meta) {	
					var {
                        student_perfix, student_perfix_th, student_firstname_en, student_lastname_en, student_firstname_th, student_lastname_th, student_nickname_en, student_nickname_th, student_gender = 'O', student_idcard, student_passport, student_passport_expire, student_image_profile, student_email, student_mobile, student_company, student_position, student_username, student_password, student_birth_date, student_age, nationality_name
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
							<div class="image">
								<img src="${student_image_profile}" onerror="this.src='${img_error}'" alt="Profile Image">
							</div>
							${(student_firstname_en || student_lastname_en) ? `
                                <div class="text-orange text-center" style="margin-bottom:10px;">
                                    <b>${student_perfix || ''} ${student_firstname_en || ''} ${student_lastname_en || ''}</b>
                                </div>` : ''}
							${(student_firstname_th || student_lastname_th) ? `
                                <div class="text-orange text-center" style="margin-bottom:10px;">
                                    <b>${student_perfix_th || ''} ${(student_firstname_th || '')} ${(student_lastname_th || '')}</b>
                                </div>` : ''}
							<div class="text-center" style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap; font-weight: 600;">
								${student_nickname_en ? `<span><i class="fas fa-user-tag"></i> ${student_nickname_en}</span>` : ''}
								${student_nickname_th ? `<span><i class="fas fa-user-tag"></i> ${student_nickname_th}</span>` : ''}
								${student_gender || ''}
							</div>
                            ${ (student_idcard) ? `
                                <div class="hidden" style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                    <i class="fas fa-address-card"></i>
                                    <span>${student_idcard || '-'}</span>
                                </div>` : ''}
                            ${ (student_passport) ? `
                                <div class="hidden" style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                    <i class="fas fa-passport"></i>
                                    <span>${student_passport || '-'}</span>
                                </div>` : ''}
                            ${ (student_passport_expire && student_passport_expire !== '0000/00/00') ? `
                                <div class="hidden" style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>${student_passport_expire || '-'}</span>
                                </div>` : ''}
                            ${ (nationality_name) ? `
                                <div class="hidden" style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                    <i class="fas fa-globe"></i>
                                    <span>${nationality_name || '-'}</span>
                                </div>` : ''}
                            <div style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                <i class="fas fa-building"></i>
                                <span>${student_company || '-'}</span>
                            </div>
                            <div class="hidden" style="font-size:11px; display:flex; gap:6px; word-break: break-all;">
                                <i class="fas fa-briefcase"></i>
                                <span>${student_position || '-'}</span>
                            </div>
                            <div style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                <i class="fas fa-phone-volume"></i>
                                <span>${student_mobile || '-'}</span>
                            </div>
                            <div style="font-size:11px; display:flex; gap:6px; word-break: break-all;">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>${student_email || '-'}</span>
                            </div>
                            ${( (student_birth_date && student_birth_date !== '0000/00/00') || student_age ) ? `
                                <div style="font-size:11px; margin-top:10px; display:flex; gap:6px; word-break: break-all;">
                                    <i class="fas fa-birthday-cake"></i>
                                    <span>${student_birth_date || '-'}</span>
                                    <span lang="en" style="margin-left:6px;">Age</span> <span>${student_age || ''}</span>
                                </div>` : ''}
						    ${(student_username || student_password) ? `
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
                "targets": 4,
                "render": function (data,type,row,meta) {	
                    var {
                        copy_of_idcard, copy_of_passport, work_certificate, company_certificate
					} = row;
                    return `
                        ${(copy_of_idcard) ? `
                            <div><a onclick="viewFile('${copy_of_idcard}', 'Copy of ID card')"><i class="fas fa-link"></i> <span lang="en">Copy of ID card</span></a></div>
                            ` : ``} 
                        ${(copy_of_passport) ? `
                            <div><a onclick="viewFile('${copy_of_idcard}', 'Copy of Passport')"><i class="fas fa-link"></i> <span lang="en">Copy of Passport</span></a></div>
                            ` : ``} 
                        ${(work_certificate) ? `
                            <div><a onclick="viewFile('${copy_of_idcard}', 'Work certificate')"><i class="fas fa-link"></i> <span lang="en">Work certificate</span></a></div>
                            ` : ``} 
                        ${(company_certificate) ? `
                            <div><a onclick="viewFile('${copy_of_idcard}', 'Company Certificate (for business owners)')"><i class="fas fa-link"></i> <span lang="en">Company Certificate (for business owners)</span></a></div>
                            ` : ``} 
                    `;
                }
            },{ 
                "targets": 5,
                "data": "student_reference",
                "render": function (data,type,row,meta) {	
                    let student_reference = row['student_reference'] || "";
                    return student_reference;
                }
            },{ 
                "targets": 6,
                "data": "channel_name",
                "render": function (data,type,row,meta) {	
                    let channel_name = row['channel_name'] || "";
                    return channel_name;
                }
            },{ 
                "targets": 7,
                "data": "invite_status",
                "render": function (data,type,row,meta) {	
                    let invite_status = row['invite_status'] || "-";
                    let invite_date = row['invite_date'] || "-";
                    let invite_by = row['invite_by'] || "-";
                    let join_id = row['join_id'] || "-";
                    let mockup = "";
                    if(invite_status == 0) {
                        mockup += `
                            <div class="nowarp">
                                <button type="button" class="btn btn-circle btn-green" onclick="confirmRegistration(${join_id}, 'Y')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-circle btn-red" onclick="confirmRegistration(${join_id}, 'N')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        mockup += `
                            <button type="button" class="btn btn-circle btn-white" onclick="confirmRegistration(${join_id}, 'W')">
                                <i class="fas fa-sort-amount-down-alt"></i>
                            </button>
                            <button type="button" class="btn btn-circle btn-${invite_status == 1 ? "red" : "green"}" onclick="confirmRegistration(${join_id}, '${invite_status == 1 ? "N" : "Y"}')">
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
									<div class="${text_class} nowarp">
										<i class="far fa-calendar-check"></i> ${invite_date}
									</div>
								</li>
							</ul>
						`;
                    }
					return mockup;
                }
            },{ 
                "targets": 8,
                "data": "approve_status",
                "render": function (data,type,row,meta) {	
					let approve_status = row['approve_status'] || "-";
                    let approve_date = row['approve_date'] || "-";
                    let approve_by = row['approve_by'] || "-";
                    let join_id = row['join_id'] || "-";
                    let invite_status = row['invite_status'] || "-";
                    let student_id = row['student_id'] || "";
                    let mockup = "";
                    if(invite_status == 1) {
                        if(approve_status == 0) {
                            mockup += `
                                <div class="nowarp">
                                    <button type="button" class="btn btn-circle btn-green" onclick="approveRegistration(${join_id}, ${student_id}, 'Y')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-circle btn-red" onclick="approveRegistration(${join_id}, ${student_id}, 'N')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            mockup += `
                                <button type="button" class="btn btn-circle btn-white" onclick="approveRegistration(${join_id} ,${student_id}, 'W')">
                                    <i class="fas fa-sort-amount-down-alt"></i>
                                </button>
                                <button type="button" class="btn btn-circle btn-${approve_status == 1 ? "red" : "green"}" onclick="approveRegistration(${join_id}, ${student_id}, '${approve_status == 1 ? "N" : "Y"}')">
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
                                        <div class="${text_class} nowarp">
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
                "targets": 9,
                "data": "payment_date",
                "render": function (data,type,row,meta) {	
					let payment_date = row['payment_date'] || "";
                    let payment_attach_file = row['payment_attach_file'] || "";
                    let payment_by = row['payment_by'] || 0;
                    let mockup = "";
                    if(payment_date) {
                        mockup += `
                            <button type="button" class="btn btn-orange" onclick="viewPayment('${payment_attach_file}');">View</button>
                            <p class="text-green" style="margin-top: 15px;"><i class="fas fa-check"></i> <span lang="en">Attachment included</span></p>
                            <div class="text-green nowarp"><i class="far fa-user-circle"></i> ${payment_by}</div>
                            <div class="text-green nowarp"><i class="far fa-calendar-check"></i> ${payment_date}</div>
                        `;
                    } else {
                        mockup += `<p class="text-grey nowarp"><i class="fas fa-hourglass-half"></i> <span lang="en">Waiting for payment</span></p>`;
                    }
					return mockup;
                }
            },{ 
                "targets": 10,
                "data": "payment_status",
                "render": function (data,type,row,meta) {	
					let payment_status = row['payment_status'] || "-";
                    let payment_status_date = row['payment_status_date'] || "-";
                    let payment_status_by = row['payment_status_by'] || "-";
                    let join_id = row['join_id'] || "-";
                    let approve_status = row['approve_status'] || "-";
                    let student_id = row['student_id'] || "";
                    let mockup = "";
                    if(approve_status == 1) {
                        if(payment_status == 0) {
                            mockup += `
                                <div class="nowarp">
                                    <button type="button" class="btn btn-circle btn-green" onclick="paymentRegistration(${join_id}, ${student_id}, 'Y')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-circle btn-red" onclick="paymentRegistration(${join_id}, ${student_id}, 'N')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            mockup += `
                                <button type="button" class="btn btn-circle btn-white" onclick="paymentRegistration(${join_id}, ${student_id}, 'W')">
                                    <i class="fas fa-sort-amount-down-alt"></i>
                                </button>
                                <button type="button" class="btn btn-circle btn-${payment_status == 1 ? "red" : "green"}" onclick="paymentRegistration(${join_id}, ${student_id}, '${payment_status == 1 ? "N" : "Y"}')">
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
                                        <div class="${text_class} nowarp">
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
                "targets": 11,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 12,
                "data": "join_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-warning btn-circle" onclick="manageRegistration(${row['student_id']})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delRegistration(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            },{ 
                "targets": 13,
                "data": "student_firstname_en",
                "visible": false,
            },{ 
                "targets": 14,
                "data": "student_lastname_en",
                "visible": false,
            },{ 
                "targets": 15,
                "data": "student_firstname_th",
                "visible": false,
            },{ 
                "targets": 16,
                "data": "student_lastname_th",
                "visible": false,
            },{ 
                "targets": 17,
                "data": "student_nickname_en",
                "visible": false,
            },{ 
                "targets": 18,
                "data": "student_nickname_th",
                "visible": false,
            },{ 
                "targets": 19,
                "data": "student_mobile",
                "visible": false,
            },{ 
                "targets": 20,
                "data": "student_email",
                "visible": false,
            },{ 
                "targets": 21,
                "data": "student_idcard",
                "visible": false,
            },{ 
                "targets": 22,
                "data": "student_passport",
                "visible": false,
            },{ 
                "targets": 23,
                "data": "student_company",
                "visible": false,
            },{ 
                "targets": 24,
                "data": "student_position",
                "visible": false,
            }]
        });
        $('div#tb_registration_filter.dataTables_filter label input').remove();
        $('div#tb_registration_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;">
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageRegistration('')"><i class="fas fa-plus"></i> <span lang="en">Student</span></button> 
            <button type="button" class="btn btn-white text-green hidden" style="font-size:12px;" onclick="importStudent('')"><i class="fas fa-file-excel"></i> <span lang="en">Import</span></button> 
            <button type="button" class="btn btn-white text-green" style="font-size:12px;" onclick="exportStudent('')"><i class="fas fa-file-excel"></i> <span lang="en">Export</span></button> 
        `;
        $('div#tb_registration_filter.dataTables_filter input').hide();
        $('div#tb_registration_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_registration.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_registration.search('').draw();
                buildRegistration();
            }
        });
    }
}
function viewPayment(fileUrl) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`<h5 class="modal-title" lang="en">View Payment</h5>`);
    const ext = fileUrl.split('.').pop().toLowerCase();
    let content = '';
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
        content = `<img src="${fileUrl}" alt="Preview" class="img-fluid" style="max-width:100%; height:auto;">`;
    } else if (ext === 'pdf') {
        const gviewUrl = "https://docs.google.com/gview?embedded=true&url=" + encodeURIComponent(fileUrl);
        content = `<iframe src="${gviewUrl}" width="100%" height="500px" style="border:none;"></iframe>`;
    }
    $(".systemModal .modal-body").html(content);
    $(".systemModal .modal-footer").html(`
        <div class="text-center">
            <button type="button" class="btn btn-default" data-dismiss="modal" lang="en">Close</button>
        </div>
    `);
}
function exportStudent() {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Student Export (Excel)</h5>    
    `);
    $(".systemModal .modal-body").html(`
        <h5 lang="en">Student Status</h5>
        <p lang="en">Please choose status to export data.</p>
        <div class="form-input">
            <div class="row">
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-info">
                        <input class="styled" id="status_all" type="checkbox" value="all">
                        <label for="status_all"><span lang="en">All</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-warning">
                        <input class="styled" id="status_lead" name="status_id[]" type="checkbox" value="lead">
                        <label for="status_lead"><span lang="en">Lead</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-primary">
                        <input class="styled" id="status_register" type="checkbox" value="register" checked>
                        <label for="status_register"><span lang="en">Register</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-warning" style="margin-left: 20px;">
                        <input class="styled sub-register" id="status_wait" name="status_id[]" type="checkbox" value="wait" checked>
                        <label for="status_wait"><span lang="en">Waiting Approve</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-success" style="margin-left: 20px;">
                        <input class="styled sub-register" id="status_approve" name="status_id[]" type="checkbox" value="approve" checked>
                        <label for="status_approve"><span lang="en">Approve</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-success" style="margin-left: 20px;">
                        <input class="styled sub-register" id="status_payment" name="status_id[]" type="checkbox" value="payment" checked>
                        <label for="status_payment"><span lang="en">Payment</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-danger">
                        <input class="styled" id="status_cancels" type="checkbox" value="cancels">
                        <label for="status_cancels"><span lang="en">Cancel Status</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-left: 20px; margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-danger">
                        <input class="styled sub-cancels" id="status_notapprove" name="status_id[]" type="checkbox" value="notapprove">
                        <label for="status_notapprove"><span lang="en">Not Approve</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-left: 20px; margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-danger">
                        <input class="styled sub-cancels" id="status_notpayment" name="status_id[]" type="checkbox" value="notpayment">
                        <label for="status_notpayment"><span lang="en">Not Payment</span></label>
                    </div>
                </div>
                <div class="col-sm-12" style="margin-left: 20px; margin-top: 10px; margin-bottom: 10px;">
                    <div class="checkbox checkbox-danger">
                        <input class="styled sub-cancels" id="status_cancel" name="status_id[]" type="checkbox" value="cancel">
                        <label for="status_cancel"><span lang="en">Cancel</span></label>
                    </div>
                </div>
            </div>
        </div>
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-green" lang="en" onclick="exportToExcel();">Export</button>
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $('#status_all').on('change', function() {
        if ($(this).is(':checked')) {
            $('input[name="status_id[]"]').prop('checked', true);
        } else {
            $('input[name="status_id[]"]').prop('checked', true);
        }
    });
    $('#status_register').on('change', function() {
        $('.sub-register').prop('checked', $(this).is(':checked'));
        updateAllCheckbox();
    });
    $('#status_cancels').on('change', function() {
        $('.sub-cancels').prop('checked', $(this).is(':checked'));
        updateAllCheckbox();
    });
    $('.sub-register').on('change', function() {
        updateParentCheckbox('#status_register', '.sub-register');
        updateAllCheckbox();
    });
    $('.sub-cancels').on('change', function() {
        updateParentCheckbox('#status_cancels', '.sub-cancels');
        updateAllCheckbox();
    });
    $('#status_lead').on('change', function() {
        updateAllCheckbox();
    });
    function updateParentCheckbox(parentId, childrenClass) {
        const total = $(childrenClass).length;
        const checked = $(childrenClass + ':checked').length;
        if (checked === total) {
            $(parentId).prop('checked', true);
            $(parentId).prop('indeterminate', false);
        } else if (checked === 0) {
            $(parentId).prop('checked', false);
            $(parentId).prop('indeterminate', false);
        } else {
            $(parentId).prop('indeterminate', true);
        }
    }
    function updateAllCheckbox() {
        const total = $('input[name="status_id[]"]').length;
        const checked = $('input[name="status_id[]"]:checked').length;
        if (checked === total) {
            $('#status_all').prop('checked', true);
            $('#status_all').prop('indeterminate', false);
        } else if (checked === 0) {
            $('#status_all').prop('checked', false);
            $('#status_all').prop('indeterminate', false);
        } else {
            $('#status_all').prop('indeterminate', true);
        }
    }
    updateParentCheckbox('#status_register', '.sub-register');
    updateParentCheckbox('#status_cancels', '.sub-cancels');
    updateAllCheckbox();
}
function exportToExcel() {
    var status_id = [];
	$('input[name="status_id[]"]:checked').each(function() {
		status_id.push(this.value); 
	});
    if(status_id.length == 0) {
		swal({type: 'warning',title: "Warning...",text: 'Please select at least 1 status.',showConfirmButton: false,timer: 2000,});
	} else {
		$.redirect("/classroom/management/export/registration.php",{ 
			'classroom_id': classroom_id,
			'status_id': status_id,
            'filter_date': $("#filter_date").val(),
            'filter_channel': $("#filter_channel").val()
		},'post','_blank');
	}
}
function viewFile(fileUrl, fileTitle) {
    $(".previewModal").modal();
    $(".previewModal .modal-header").html(`<h5 class="modal-title" lang="en">${(fileTitle || '')}</h5>`);
    const ext = fileUrl.split('.').pop().toLowerCase();
    let content = '';
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
        content = `<img src="${fileUrl}" alt="Preview" class="img-fluid" style="max-width:100%; height:auto;">`;
    } else if (ext === 'pdf') {
        const gviewUrl = "https://docs.google.com/gview?embedded=true&url=" + encodeURIComponent(fileUrl);
        content = `
            <iframe src="${gviewUrl}" width="100%" height="500px" style="border:none;"></iframe>
        `;
    }
    $(".previewModal .modal-body").html(content);
    $(".previewModal .modal-footer").html(`
        <div class="text-center">
            <button type="button" class="btn btn-default" data-dismiss="modal" lang="en">Close</button>
        </div>
    `);
}
function manageRegistration(student_id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Student Management</h5>    
    `);
    $(".systemModal .modal-body").html(`
        <form id="registrationForm">
            <input type="hidden" name="student_id" value="${student_id}">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group input-15 text-center" style="margin-top:20px;">
                        <div class="profile-upload" style="position:relative; display:inline-block;">
                            <img id="profilePreview" src="/images/profile-default.jpg" onerror="this.src='/images/profile-default.jpg'" class="img-circle" style="width:120px;height:120px;object-fit:cover;border:2px solid #ddd; cursor:pointer;">
                            <span for="student_image_profile" class="camera-icon" style="position:absolute; bottom:5px; right:5px; background:#fff; border-radius:50%; padding:6px; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="fa fa-camera"></i></span>
                            <button type="button" id="removeProfile" style="display:none; position:absolute; top:5px; right:5px; background:#f44336; color:#fff; border:none; border-radius:50%; width:25px; height:25px; line-height:10px;  cursor:pointer;">&times;</button>
                        </div>
                        <input type="file" id="student_image_profile" name="student_image_profile" accept="image/*" style="display:none;">
                        <input type="hidden" id="ex_student_image_profile" name="ex_student_image_profile">
                        <p><label class="register-form" style="margin-top:10px;color:#888;" lang="en">Upload Image</label></p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group form-input input-17">
                        <label class="register-form" for="student_idcard" lang="en">ID Card</label>
                        <div class="input-group">
                            <input type="text" class="form-control " id="student_idcard" name="student_idcard" autocomplete="off" maxlength="13">
                            <span class="input-group-addon"><i class="fas fa-address-card"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-18">
                        <label class="register-form" for="student_passport" lang="en">Passport</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="student_passport" name="student_passport" autocomplete="off">
                            <span class="input-group-addon"><i class="fas fa-passport"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-24">
                        <label class="register-form" for="student_passport_expire" lang="en">Passport Expire</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-past" id="student_passport_expire" name="student_passport_expire" autocomplete="off">
                            <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-16">
                        <label class="register-form" for="studentstudent_perfix_gender" lang="en">Prefix</label>
                        <select class="form-control" id="student_perfix" name="student_perfix">
                            <option value=""></option>
                            <option value="Mr.">Mr.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Miss">Miss</option>
                            <option value="Other">Other</option>
                        </select>
                        <input type="text" class="form-control prefix-other hidden" id="student_perfix_other" name="student_perfix_other" style="margin-top: 10px;">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-1">
                        <label class="register-form" for="student_firstname_en" lang="en">Firstname (EN)</label>
                        <input type="text" class="form-control" id="student_firstname_en" name="student_firstname_en" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-2">
                        <label class="register-form" for="student_lastname_en" lang="en">Lastname (EN)</label>
                        <input type="text" class="form-control" id="student_lastname_en" name="student_lastname_en" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-5">
                        <label class="register-form" for="student_firstname_th" lang="en">Firstname (TH)</label>
                        <input type="text" class="form-control" id="student_firstname_th" name="student_firstname_th" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-6">
                        <label class="register-form" for="student_lastname_th" lang="en">Lastname (TH)</label>
                        <input type="text" class="form-control" id="student_lastname_th" name="student_lastname_th" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-3">
                        <label class="register-form" for="student_nickname_en" lang="en">Nickname (EN)</label>
                        <input type="text" class="form-control" id="student_nickname_en" name="student_nickname_en" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-4">
                        <label class="register-form" for="student_nickname_th" lang="en">Nickname (TH)</label>
                        <input type="text" class="form-control" id="student_nickname_th" name="student_nickname_th" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-7">
                        <label class="register-form" for="student_gender">Gender</label>
                        <select class="form-control" id="student_gender" name="student_gender">
                            <option value=""></option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-14">
                        <label class="register-form" for="student_birth_date" lang="en">Birthday</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker" id="student_birth_date" name="student_birth_date" autocomplete="off">
                            <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-23">
                        <label class="register-form" for="student_nationality" lang="en">Nationality</label>
                        <select class="form-control" id="student_nationality" name="student_nationality"></select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-8">
                        <label class="register-form" for="student_email" lang="en">Email</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="student_email" name="student_email" autocomplete="off">
                            <span class="input-group-addon"><i class="fas fa-envelope-open-text"></i></span>
                        </div>
                        <div style="font-size: 11px; color: #888888; margin-top: 10px;">example@origami.life</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-9">
                        <label class="register-form" for="student_mobile" lang="en">Mobile</label>
                        <input type="tel" class="form-control" id="student_mobile" name="student_mobile" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-10">
                        <label class="register-form" for="student_company" lang="en">Company</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="student_company" name="student_company" autocomplete="off">
                            <span class="input-group-addon"><i class="fas fa-building"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-11">
                        <label class="register-form" for="student_position" lang="en">Position</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="student_position" name="student_position" autocomplete="off">
                            <span class="input-group-addon"><i class="fas fa-briefcase"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-25">
                        <label class="register-form" for="student_reference" lang="en">Reference Person</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="student_reference" name="student_reference" autocomplete="off">
                            <span class="input-group-addon"><i class="fas fa-user-friends"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group form-input input-19">
                        <label class="register-form" for="copy_of_idcard"><i class="fas fa-paperclip"></i> <span lang="en">Copy of ID card</span></label>
                        <p class="text-orange" style="margin: 10px auto;" lang="en">Supports image or PDF files with a size not exceeding 20 MB only.</p>
                        <input type="file" class="form-control input-file" id="copy_of_idcard" name="copy_of_idcard" accept="image/*,.pdf">
                        <input type="hidden" id="existing_copy_of_idcard" name="existing_copy_of_idcard">
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group form-input input-22">
                        <label class="register-form" for="copy_of_passport"><i class="fas fa-paperclip"></i> <span lang="en">Copy of Passport</span></label>
                        <p class="text-orange" style="margin: 10px auto;" lang="en">Supports image or PDF files with a size not exceeding 20 MB only.</p>
                        <input type="file" class="form-control input-file" id="copy_of_passport" name="copy_of_passport" accept="image/*,.pdf">
                        <input type="hidden" id="existing_copy_of_passport" name="existing_copy_of_passport">
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group form-input input-20">
                        <label class="register-form" for="work_certificate"><i class="fas fa-paperclip"></i> <span lang="en">Work certificate</span></label>
                        <p class="text-orange" style="margin: 10px auto;" lang="en">Supports image or PDF files with a size not exceeding 20 MB only.</p>
                        <input type="file" class="form-control input-file" id="work_certificate" name="work_certificate" accept="image/*,.pdf">
                        <input type="hidden" id="existing_work_certificate" name="existing_work_certificate">
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group form-input input-21">
                        <label class="register-form" for="company_certificate"><i class="fas fa-paperclip"></i> <span lang="en">Company Certificate (for business owners)</span></label>
                        <p class="text-orange" style="margin: 10px auto;" lang="en">Supports image or PDF files with a size not exceeding 20 MB only.</p>
                        <input type="file" class="form-control input-file" id="company_certificate" name="company_certificate" accept="image/*,.pdf">
                        <input type="hidden" id="existing_company_certificate" name="existing_company_certificate">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-12">
                        <label class="register-form" for="student_username" lang="en">Username</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="student_username" name="student_username" autocomplete="off" maxlength="20">
                            <span class="input-group-addon"><i class="fas fa-user-lock"></i></span>
                        </div>
                        <div style="font-size: 11px; color: #888888; margin-top: 10px;" lang="en">Username must be 8–20 characters, consisting of English letters or numbers only. (By default, your registered mobile number will be used)</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group form-input input-13">
                        <label class="register-form" for="student_password" lang="en">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="student_password" name="student_password" autocomplete="off" maxlength="20">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="togglePassword">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </span>
                        </div>
                        <div style="font-size: 11px; color: #888888; margin-top: 10px;" lang="en">Password must be 4–20 characters, using only English letters or numbers.</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-container"></div>
                </div>
            </div>
        </form>
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="saveRegister()">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".loader").addClass("active");
    $.post('/classroom/management/actions/registration.php', { 
        action: "dataRegistration", 
        classroom_id: classroom_id, 
        student_id: student_id
    }, (response) => {
        $(".loader").removeClass("active");
        response.register_template.forEach(value => $(".input-" + value).removeClass("hidden"));
        response.register_require.forEach(value => {
            const $group = $(".input-" + value);
            $group.find("input, select, textarea").addClass("require");
            $group.find("input[type=file]").addClass("require");
            $group.find("label").addClass("required-field");
        });
        initForm(response.form_data);
        consent_status = response.consent_status;
        if(consent_status == 'Y') {
            $(".input-consent").removeClass("hidden");
        }
        if (response.register_template.includes("23")) {
            let nationality = response.nationality;
            $("#student_nationality").append($('<option>', { 
                value: nationality.nationality_id, 
                text: nationality.nationality_name 
            }));
        }
        is_logged_in = response.is_logged_in;
        if (response.is_logged_in && response.student_data) {
            $(".after-login").removeClass("hidden");
            const data = response.student_data;
            if (data.student_id) {
                $("input[name='student_id']").val(data.student_id);
                if ($("input[name='student_id']").length === 0) {
                    $("form").append('<input type="hidden" name="student_id" value="' + data.student_id + '">');
                }
            }
            if (data.student_firstname_en) $("#student_firstname_en").val(data.student_firstname_en);
            if (data.student_lastname_en) $("#student_lastname_en").val(data.student_lastname_en);
            if (data.student_nickname_en) $("#student_nickname_en").val(data.student_nickname_en);
            if (data.student_firstname_th) $("#student_firstname_th").val(data.student_firstname_th);
            if (data.student_lastname_th) $("#student_lastname_th").val(data.student_lastname_th);
            if (data.student_nickname_th) $("#student_nickname_th").val(data.student_nickname_th);
            if (data.student_email) $("#student_email").val(data.student_email);
            if (data.student_mobile) $("#student_mobile").val(data.student_mobile);
            if (data.student_company) $("#student_company").val(data.student_company);
            if (data.student_position) $("#student_position").val(data.student_position);
            if (data.student_username) $("#student_username").val(data.student_username);
            if (data.student_birth_date) $("#student_birth_date").val(data.student_birth_date);
            if (data.student_idcard) $("#student_idcard").val(data.student_idcard);
            if (data.student_passport) $("#student_passport").val(data.student_passport);
            if (data.student_passport_expire) $("#student_passport_expire").val(data.student_passport_expire);
            if (data.student_password) $("#student_password").val(data.student_password);
            if (data.student_reference) $("#student_reference").val(data.student_reference);
            if (data.dial_code) {
                $("#dialCode").val(data.dial_code);
                $("select[name='dialCode']").val(data.dial_code);
            }
            if (data.student_gender) {
                $("#student_gender").val(data.student_gender);
                $("select[name='student_gender']").val(data.student_gender).trigger('change');
            }
            if (data.student_perfix) {
                $("#student_perfix").val(data.student_perfix);
                $("select[name='student_perfix']").val(data.student_perfix).trigger('change');
            }
            if (data.student_perfix_other) {
                $("#student_perfix_other").val(data.student_perfix_other);
                $(".input-perfix-other").removeClass("hidden");
            }
            if (data.student_nationality) {
                $("#student_nationality").val(data.student_nationality);
                $("select[name='student_nationality']").val(data.student_nationality);
            }
            if (data.student_image_profile) {
                const $imgPreview = $("#profilePreview");
                $imgPreview.attr('src', data.student_image_profile);
                $("#removeProfile").show();
                $("#ex_student_image_profile").val(data.student_image_profile);
            }
            if (data.copy_of_idcard) {
                showDocumentPreview('copy_of_idcard', data.copy_of_idcard, 'ID Card');
                $("#existing_copy_of_idcard").val(data.copy_of_idcard);
            }
            if (data.copy_of_passport) {
                showDocumentPreview('copy_of_passport', data.copy_of_passport, 'Passport');
                $("#existing_copy_of_passport").val(data.copy_of_passport);
            }
            if (data.work_certificate) {
                showDocumentPreview('work_certificate', data.work_certificate, 'Work Certificate');
                $("#existing_work_certificate").val(data.work_certificate);
            }
            if (data.company_certificate) {
                showDocumentPreview('company_certificate', data.company_certificate, 'Company Certificate');
                $("#existing_company_certificate").val(data.company_certificate);
            }
            $(".page-title, h1").append(' <span class="badge badge-info" data-lang="update_mode">Update Mode</span>');
            $(".input-password").addClass("hidden");
            $("input[name='student_password']").removeClass("require");
        } 
    }, 'json').fail(() => '');
    const defaultProfile = "/images/default-profile.png";
    $(".profile-upload img, .profile-upload .camera-icon").on("click", function() {
        $("#student_image_profile").click();
    });
    $("#student_image_profile").on("change", function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            if (!file.type.startsWith("image/")) {
                swal({
                    type: 'warning',
                    title: 'Warning',
                    text: 'Please select a valid image file.',
                    confirmButtonColor: '#FF9900'
                });
                $(this).val("");
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#profilePreview").attr("src", e.target.result);
                $("#removeProfile").show();
                $("#ex_student_image_profile").val(e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });
    $("#removeProfile").on("click", function() {
        $("#ex_student_image_profile").val("");
        $("#profilePreview").attr("src", "/images/profile-default.jpg");
        $(this).hide();
    });
    $("#removeProfile").on("click", function () {
        $("#student_image_profile").val("");
        $("#student_image_profile").val("");
        $("#profilePreview").attr("src", defaultProfile);
        $(this).hide();
    });
    $('.datepicker').datepicker({
        dateFormat: 'yy/mm/dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        autoclose: true,
        maxDate: 0 
    });
    $('.datepicker-past').datepicker({
        dateFormat: 'yy/mm/dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "-20:+20",
        autoclose: true,
    });
    $(document).on("input", "#student_idcard, #student_mobile", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
    });
    $(document).on("input", "[id$='_en']", function () {
        this.value = this.value.replace(/[^A-Za-z\s]/g, "");
    });
    $(document).on("input", "[id$='_th']", function () {
        this.value = this.value.replace(/[^ก-๙\s]/g, "");
    });
    $("#student_username").on("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9]/g, "");
    });
    $("#student_password").on("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9!@#$%^&*()_\+\-=\[\]{};:'",.<>\/?]/g, "");
    });
    $("#togglePassword").on("click", function () {
        const $pwd = $("#student_password");
        const type = $pwd.attr("type") === "password" ? "text" : "password";
        $pwd.attr("type", type);
        $(this).html(`<i class="fa fa-${type === 'text' ? 'eye-slash' : 'eye'}"></i>`);
    });
    const validators = {
        student_email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        student_mobile: /^[0-9]{9,}$/
    };
    $('#student_email, #student_mobile').on('blur', function () {
        let val = $(this).val().trim();
        const type = $(this).attr('id');
        if (!validators[type]) return;
        if (val && !validators[type].test(val)) {
            $(this).addClass('has-error');
            const msg = type === 'student_email' ? 'Invalid email format' : 'The phone number format is incorrect.';
            swal({ 
                type: 'warning', 
                title: "Warning...", 
                text: msg, 
                confirmButtonColor: '#FF9900'
            });
        } else {
            $(this).removeClass('has-error');
            if(type === 'student_email') {
                verifyDuplicateData(val, 'email', 'student_email');
            }
            if(type === 'student_mobile') {
                verifyDuplicateData(val, 'mobile', 'student_mobile');
            }
        }
    });
    const input = document.querySelector("#student_mobile");
    const iti = window.intlTelInput(input, {
        initialCountry: "th",
        separateDialCode: true,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });
}
function showDocumentPreview(fieldName, fileUrl, label) {
    const $input = $("input[name='" + fieldName + "']");
    const $existingInput = $("input[name='existing_" + fieldName + "']");
    let $container = $input.siblings('.document-preview-list-' + fieldName);
    if ($container.length === 0) {
        $container = $('<ul class="list-group document-preview-list-' + fieldName + ' mt-2"></ul>');
        $input.after($container);
    }
    const fileExt = fileUrl.split('.').pop().toLowerCase();
    let $item = $('<li class="list-group-item d-flex justify-content-between align-items-center"></li>');
    let previewContent = '';
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
        previewContent = `
            <div class="text-center">
                <img src="${fileUrl}" class="img-thumbnail" style="max-width: 80px; margin-right:10px;">
            </div>
        `;
    } else {
        previewContent = `
            <div class="text-center">
                <i class="fa fa-file-pdf-o fa-5x text-danger"></i>
            </div>
        `;
    }
    $item.append(previewContent);
    const $btnGroup = $('<div class="text-center" style="margin-top: 15px;"></div>');
    const $viewBtn = $(`<button type="button" class="btn btn-primary" onclick="viewFile('${fileUrl}')" lang="en">View</button>`);
    const $deleteBtn = $('<button type="button" class="btn btn-warning" style="margin-left: 15px;" lang="en">Delete</button>');
    $deleteBtn.on('click', function() {
        const msg = "Are you sure?";
        const text = "This file will be removed from the list.";
        const confmsg = "Yes, delete it!";
        const canfmsg = "Cancel";
        swal({
            title: msg,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: confmsg,
            cancelButtonText: canfmsg,
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {
                $item.remove();
                $existingInput.val('');
            }
        });
    });
    $btnGroup.append($viewBtn).append($deleteBtn);
    $item.append($btnGroup);
    $container.append($item);
}
function initForm(form_data) {
    if(!form_data) return;
    let html = '';
    form_data.forEach(q => {
        const answerChoice = q.answer_choice_id;
        const answerText = q.answer_text;
        const answerOther = q.answer_other_text;
        html += `<div class="form-group">
            <input type="hidden" name="question_id[]" value="${q.question_id}">
            <input type="hidden" name="question_type[]" value="${q.question_type}">
            <hr>
        `;
        html += `<label class="${q.has_required == 1 ? 'required-field' : ''} question_text"><i class="fas fa-question-circle"></i> ${q.question_text}</label>`;
        let requiredAttr = q.has_required == 1 ? 'data-required="1"' : '';
        if(q.has_options == 1) {
            if(q.question_type === "radio" || q.question_type === "multiple_choice") {
                q.option_item.forEach(opt => {
                    const checked = (answerChoice == opt.choice_id) ? 'checked' : '';
                    html += `
                        <div>
                            <input type="radio" id="q_${q.question_id}_opt_${opt.choice_id}" name="q_${q.question_id}" value="${opt.choice_id}" class="option-input" data-qid="${q.question_id}" ${requiredAttr} ${checked}>
                            <label for="q_${q.question_id}_opt_${opt.choice_id}" class="radio-label">${opt.choice_text}</label>
                        </div>
                    `;
                });
            } else if(q.question_type === "checkbox") {
                q.option_item.forEach(opt => {
                    const checked = (Array.isArray(answerChoice) && answerChoice.includes(opt.choice_id)) ? 'checked' : '';
                    html += `
                        <div>
                            <input type="checkbox" id="q_${q.question_id}_opt_${opt.choice_id}" name="q_${q.question_id}[]" value="${opt.choice_id}" class="option-input" data-qid="${q.question_id}" ${requiredAttr} ${checked}>
                            <label for="q_${q.question_id}_opt_${opt.choice_id}" class="checkbox-label">${opt.choice_text}</label>
                        </div>
                    `;
                });
            }
            if(q.has_other_option == 1) {
                const inputType = (q.question_type === 'checkbox' ? 'checkbox' : 'radio');
                const inputName = `q_${q.question_id}_other`;
                const otherChecked = (answerOther && answerOther !== '') ? 'checked' : '';
                const otherDisplay = otherChecked ? 'block' : 'none';
                html += `
                    <div>
                        <input type="${inputType}" id="q_${q.question_id}_other" name="${inputName}" value="other" class="option-input other-input" data-qid="${q.question_id}" ${requiredAttr} ${otherChecked}>
                        <label for="q_${q.question_id}_other" class="${inputType==='checkbox' ? 'checkbox-label':'radio-label'}"><span data-lang="other">อื่นๆ</span></label>
                    </div>
                    <div id="other_box_${q.question_id}" style="display:${otherDisplay}; margin-top:5px;">
                        <input type="text" class="form-control" name="q_${q.question_id}_other" value="${answerOther || ''}">
                    </div>
                `;
            }
        } else {
            if(q.question_type === "short_answer") {
                html += `<textarea name="q_${q.question_id}" class="form-control" ${requiredAttr} onclick="autoResize(this);" onkeyup="autoResize(this);">${answerText || ''}</textarea>`;
            }
        }
        html += `</div>`;
    });
    document.querySelector(".form-container").innerHTML = html;
    document.querySelectorAll('.option-input').forEach(input => {
        input.addEventListener('change', function() {
            const qid = this.dataset.qid;
            const otherBox = document.getElementById(`other_box_${qid}`);
            const otherInput = otherBox ? otherBox.querySelector("input") : null;
            if(this.type === "radio") {
                if(this.value === "other" && this.checked) {
                    otherBox.style.display = "block";
                    if(otherInput) otherInput.setAttribute("data-required","1");
                } else {
                    if(otherBox) otherBox.style.display = "none";
                    if(otherInput) otherInput.removeAttribute("data-required");
                }
            } else if(this.type === "checkbox") {
                if(this.value === "other") {
                    otherBox.style.display = this.checked ? "block" : "none";
                    if(otherInput) {
                        if(this.checked) otherInput.setAttribute("data-required","1");
                        else otherInput.removeAttribute("data-required");
                    }
                }
            }
        });
    });
}
function confirmRegistration(join_id, option) {
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
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/registration.php",
                type: "POST",
                data: {
                    action: 'confirmRegistration',
                    join_id: join_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result) {
                    if(result.status === true) {	
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                        buildRegistration();
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
function approveRegistration(join_id, student_id, option) {
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
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/registration.php",
                type: "POST",
                data: {
                    action: 'approveRegistration',
                    join_id: join_id,
                    option: option,
                    classroom_id: classroom_id,
                    student_id: student_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result) {
                    if(result.status === true) {	
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                        buildRegistration();
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
function paymentRegistration(join_id, student_id, option) {
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
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/registration.php",
                type: "POST",
                data: {
                    action: 'paymentRegistration',
                    join_id: join_id,
                    option: option,
                    classroom_id: classroom_id,
                    student_id: student_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result) {
                    if(result.status === true) {	
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
                        buildRegistration();
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
function saveRegister() {
    let isValid = true;
    let firstInvalidField = null;
    $(".require").each(function(){
        const $field = $(this);
        const $group = $field.closest(".form-group");
        if ($group.hasClass("hidden")) return;
        let val = $field.val();
        if ($field.attr("id") === "student_idcard") {
            const val = $field.val().trim();
            if (!isValidThaiID(val)) {
                isValid = false;
                if (!firstInvalidField) firstInvalidField = $field;
                $field.addClass("has-error");
                $group.find("label").addClass("has-error-text");
            } else {
                $field.removeClass("has-error");
                $group.find("label").removeClass("has-error-text");
            }
            return;
        }
        if ($field.attr("type") === "file") {
            if ($field[0].files.length === 0) {
                isValid = false;
                if (!firstInvalidField) firstInvalidField = $field;
                $group.find(".profile-upload").css("border", "2px solid orange");
            } else {
                $group.find(".profile-upload").css("border", "2px solid #ddd");
            }
        } else if (!$field.is(":checkbox") && !$field.is(":radio")) {
            if (!val || val.trim() === "") {
                $field.addClass("has-error");
                if (!firstInvalidField) firstInvalidField = $field;
                isValid = false;
            } else {
                $field.removeClass("has-error");
            }
        } else if ($field.is(":checkbox") || $field.is(":radio")) {
            const name = $field.attr("name");
            if ($(`[name='${name}']:checked`).length === 0) {
                $field.addClass("has-error");
                if (!firstInvalidField) firstInvalidField = $field;
                isValid = false;
            } else {
                $field.removeClass("has-error");
            }
        }
    });
    let errorMessage = '';
    const $username = $("#student_username");
    if (!$username.closest(".form-group").hasClass("hidden")) {
        const usernameVal = $username.val().trim();
        if ($username.hasClass("require") || usernameVal !== "") {
            if (usernameVal.length < 4 || usernameVal.length > 20) {
                $username.addClass("has-error");
                if (!firstInvalidField) firstInvalidField = $username;
                isValid = false;
                errorMessage + 'Username';
            } else {
                $username.removeClass("has-error");
            }
        } else {
            $username.removeClass("has-error");
        }
    }
    const $password = $("#student_password");
    if (!$password.closest(".form-group").hasClass("hidden")) {
        const passwordVal = $password.val().trim();
        if ($password.hasClass("require") || passwordVal !== "") {
            if (passwordVal.length < 4 || passwordVal.length > 20) {
                $password.addClass("has-error");
                if (!firstInvalidField) firstInvalidField = $password;
                isValid = false;
                errorMessage + ' Password';
            } else {
                $password.removeClass("has-error");
            }
        } else {
            $password.removeClass("has-error");
        }
    }
    if(errorMessage) {
        errorMessage += 'must be 4–20 characters if provided. ';
    }
    $(".form-container .form-group").each(function() {
        const $group = $(this);
        const $question = $group.find("[name^='q_']");
        if ($question.length === 0) return;
        const required = $group.find("[data-required='1']").length > 0 || $group.find(".require").length > 0;
        if (!required) return;
        if ($question.is(":radio") || $question.is(":checkbox")) {
            const name = $question.attr("name");
            const $checked = $(`[name='${name}']:checked`);
            if ($checked.length === 0) {
                isValid = false;
                if (!firstInvalidField) firstInvalidField = $group;
                $group.find(".question_text").addClass("has-error-text");
            } else {
                $group.removeClass("has-error");
                const $other = $checked.filter("[value='other']");
                if ($other.length > 0) {
                    const targetBox = $("#other_box_" + $other.data("qid"));
                    const $otherInput = targetBox.find("input[type='text']");
                    if ($otherInput.length && $otherInput.val().trim() === "") {
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $otherInput;
                        $otherInput.addClass("has-error");
                    } else {
                        $otherInput.removeClass("has-error");
                    }
                }
            }
        } else {
            $question.each(function() {
                if ($(this).val().trim() === "") {
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $(this);
                    $(this).addClass("has-error");
                } else {
                    $(this).removeClass("has-error");
                }
            });
        }
    });
    if (!isValid) {
        if (firstInvalidField) {
            $('html, body').animate({ scrollTop: firstInvalidField.offset().top - 100 }, 300);
            firstInvalidField.focus();
        }
        swal({
            type: 'warning',
            title: 'Warning',
            text: 'Please fill in all required fields. ' + errorMessage,
            confirmButtonColor: '#FF9900'
        });
        return false;
    }
    const $form = $('#registrationForm');
    if ($form.length === 0) {
        console.error('Registration form not found');
        return;
    }
    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);
    if ($(".loader").length > 0) {
        $(".loader").addClass("active");
    }
    const fd = new FormData($form[0]);
    if (typeof classroom_id !== 'undefined' && classroom_id) {
        fd.append('classroom_id', classroom_id);
    }
    const $dialCode = $(".iti__selected-dial-code");
    if ($dialCode.length > 0) {
        const dialCode = $dialCode.text().trim();
        fd.append('dialCode', dialCode);
    }
    $.ajax({
        url: '/classroom/management/actions/registration.php?action=saveRegister',
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: function(result) {
            handleRegisterResponse(result);
        },
        error: function(xhr, status, error) {
            console.error('Register error:', status, error);
            $(".loader").removeClass("active");
            $btn.prop('disabled', false);
            const titlemsg = "Warning";
            const msg = "Save failed, please try again.";
            if (typeof swal === 'function') {
                swal({
                    type: 'warning',
                    title: titlemsg,
                    text: msg,
                    confirmButtonColor: '#FF9900'
                });
            } else {
                alert(msg);
            }
        }
    });
}
function isValidThaiID(id) {
    if (!/^\d{13}$/.test(id)) return false;
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(id.charAt(i)) * (13 - i);
    }
    let checkDigit = (11 - (sum % 11)) % 10;
    return checkDigit === parseInt(id.charAt(12));
}
function handleRegisterResponse(result) {
    $(".loader").removeClass("active");
    swal({
        title: 'Saved successfully',
        text: '',
        type: 'success',
        confirmButtonColor: '#41a85f'
    },function() {
        $(".systemModal").modal("hide");
        buildRegistration();
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
					<a class="btn btn-white btn-lg text-green" href="/classroom/export/StudentsTemplate.php?classroom_id=${classroom_id}" target="_blank"><i class="fas fa-download"></i> <span lang="en">Download</span></a>
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
					<p>1. <span lang="en">Download sample files from the link</span> <a href="/classroom/export/StudentsTemplate.php?classroom_id=${classroom_id}" target="_blank" class="text-orange"><u class="text-green"><b>Download</b></u></a></p>
					<p>2. <span lang="en">Choose the file you want to import.</span></p>
					<p>3. <span lang="en">Press the <b class="text-orange">Import Data</b> button to import the data.</span></p>
				</div>
			</div>
		</form> 
    `);
    $("#excel_file").change(function() {
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
			url: "/classroom/management/actions/registration.php?action=saveImport",
			type: "POST",
			data: excel_file,
			processData: false,
			contentType: false,
			dataType: "JSON",
			type: 'POST',
			success: function(result) {
				$(".loader").removeClass("active");
                if(result.status === true) {	
                    $(".systemModal").modal("hide");
                    swal({type: 'success',title: "Successfully",text: result.message, showConfirmButton: false,timer: 1500});
                    $(".el-classroomlead").click();
                    buildRegistration();
                } else {
                    swal({type: 'error',title: "Sorry...",text: result.message,timer: 2000});
                }
            }
        });
    }
}
function delRegistration(join_id) {
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
    function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/registration.php",
                type: "POST",
                data: {
                    action:'delRegistration',
                    join_id: join_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result) {
                    if(result.status === true) {			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildRegistration();
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
function autoResize(textarea) {
    textarea.style.height = 'auto'; 
    textarea.style.height = textarea.scrollHeight + 'px'; 
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
function buildSummaryRegistration() {
    $.ajax({
        url: "/classroom/management/actions/registration.php",
        type: "POST",
        data: {
            action: 'buildSummaryRegistration',
            classroom_id: classroom_id,
            filter_date: $("#filter_date").val(),
            filter_channel: $("#filter_channel").val(),
        },
        dataType: "JSON",
        success: function(result) {
            if (result.status === true) {			
                $.each(result.summary_data, function(status, value) {
                    $(".count" + status).text(value);
                });
            } else {
                swal({
                    type: 'error',
                    title: "Sorry...",
                    text: "Something went wrong!",
                    timer: 2000
                });
            }
        }
    });
}
function buildChannelSelected() {
    try {
        $("#filter_channel").select2({
            theme: "bootstrap",
            placeholder: "All Channel",
            minimumInputLength: -1,
            allowClear: true,
            ajax: {
                url: "/classroom/management/actions/registration.php",
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                        action: 'buildChannel',
                        classroom_id: classroom_id
                    };
                },
                processResults: function(data, params) {
                    const page = params.page || 1;
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.col,
                                code: item.code,
                                desc: item.desc,
                            };
                        }),
                        pagination: {
                            more: (page * 10) <= (data[0] ? data[0].total_count : 0)
                        }
                    };
                },
            },
            templateSelection: function(data) {
                return data.text;
            },
        });
    } catch (error) {
        console.error('Error building department dropdown:', error);
    }
}