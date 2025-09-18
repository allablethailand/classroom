let classroom_id = '';
let group_id = '';
$(document).ready(function() {
    classroom_id = $("#classroom_id").val();
    group_id = $("#group_id").val();
    if(!classroom_id || !group_id) {
        window.close();
    }
    getGroupData();
    $(".content-container").html(getGroupTemplate());
    buildStudent();
});
function getGroupTemplate() {
    return `
        <table class="table table-border" id="tb_student">
            <thead>
                <tr>
                    <th></th>
                    <th lang="en">Student</th>
                    <th lang="en">Nickname</th>
                    <th lang="en">Company</th>
                    <th lang="en">Position</th>
                    <th lang="en">Email</th>
                    <th lang="en">Mobile</th>
                    <th lang="en">Register</th>
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
                    data.group_id = group_id;
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
			"order": [[4,'desc']],
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
                    let student_firstname_en = row["student_firstname_en"] || "";
                    let student_lastname_en  = row["student_lastname_en"]  || "";
                    let student_firstname_th = row["student_firstname_th"] || "";
                    let student_lastname_th  = row["student_lastname_th"]  || "";
                    let fullNameTh = (student_firstname_th + " " + student_lastname_th).trim();
                    let fullNameEn = (student_firstname_en + " " + student_lastname_en).trim();
                    let displayName = "-";
                    if(fullNameTh && fullNameEn){
                        displayName = fullNameTh + " (" + fullNameEn + ")";
                    } else if(fullNameTh){
                        displayName = fullNameTh;
                    } else if(fullNameEn){
                        displayName = fullNameEn;
                    }
                    return displayName;
                }
            },{ 
                "targets": 2,
                "data": "student_nickname_en",
                "render": function (data,type,row,meta) {	
					let student_nickname_en = row['student_nickname_en'] || "";
                    let student_nickname_th = row['student_nickname_th'] || "";
                    let displayNickname = "-";
                    if(student_nickname_en && student_nickname_th){
                        displayNickname = student_nickname_en + " (" + student_nickname_th + ")";
                    } else if(student_nickname_th){
                        student_nickname_th = student_nickname_th;
                    } else if(student_nickname_en){
                        displayNickname = student_nickname_en;
                    }
                    return displayNickname;
                }
            },{ 
                "targets": 3,
                "data": "student_company",
                "render": function (data,type,row,meta) {	
					return data || "-";
                }
            },{ 
                "targets": 4,
                "data": "student_position",
                "render": function (data,type,row,meta) {	
					return data || "-";
                }
            },{ 
                "targets": 5,
                "data": "student_email",
                "render": function (data,type,row,meta) {	
					return data || "-";
                }
            },{ 
                "targets": 6,
                "data": "student_mobile",
                "render": function (data,type,row,meta) {	
					return data || "-";
                }
            },{ 
                "targets": 7,
                "data": "register_date",
            },{ 
                "targets": 8,
                "data": "student_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
					return `
                        <button type="button" class="btn btn-circle btn-red" onclick="removeFromGroup(${data});"><i class="fas fa-trash-alt"></i></button>
                    `;
                }
            },{ 
                "targets": 4,
                "data": "student_firstname_en",
                "visible": false,
            },{ 
                "targets": 5,
                "data": "student_lastname_en",
                "visible": false,
            },{ 
                "targets": 6,
                "data": "student_firstname_th",
                "visible": false,
            },{ 
                "targets": 7,
                "data": "student_lastname_th",
                "visible": false,
            },{ 
                "targets": 8,
                "data": "student_nickname_en",
                "visible": false,
            },{ 
                "targets": 9,
                "data": "student_nickname_th",
                "visible": false,
            },{ 
                "targets": 10,
                "data": "student_email",
                "visible": false,
            },{ 
                "targets": 11,
                "data": "student_mobile",
                "visible": false,
            },{ 
                "targets": 12,
                "data": "student_company",
                "visible": false,
            },{ 
                "targets": 13,
                "data": "student_position",
                "visible": false,
            }]
        });
        $('div#tb_student_filter.dataTables_filter label input').remove();
        $('div#tb_student_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;">
             <button type="button" class="btn btn-green" style="font-size:12px;" onclick="joinStudent()"><i class="fas fa-plus"></i> <span lang="en">Join Student</span></button>
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
function removeFromGroup(student_id) {
    event.stopPropagation();
    swal({
        html:true,
        title: window.lang.translate("Are you sure?"),
        text: 'Do you want to remove these records from group?',
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
                    action:'removeFromGroup',
                    classroom_id: classroom_id,
                    student_id: student_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildStudent();
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
function joinStudent() {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Join Student</h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <p style="margin: 10px auto;"><i class="fas fa-cubes"></i> <span lang="en">Group</span></p>
        <div style="margin-bottom: 15px;">
            <select class="form-control" id="group_selected" onchange="buildJoin();" style="widtd: 50px;"></select>
        </div>
        <table class="table table-border" id="tb_join">
            <thead>
                <tr>
                    <th></th>
                    <th lang="en">Student</th>
                    <th lang="en">Group</th>
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
    `);
    buildJoin();
    buildGroupSelect();
}
function buildGroupSelect() {
    $("#group_selected").select2({
        theme: "bootstrap",
        placeholder: "Without Group",
        minimumInputLength: -1,
        allowClear: true,
        ajax: {
            url: "/classroom/management/actions/student.php",
            dataType: 'json',
            delay: 250,
            cache: false,
            data: function(params) {
                return {
                    term: params.term,
                    page: params.page || 1,
                    action: 'buildGroupSelect',
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
}
let tb_join;
function buildJoin() {
    if ($.fn.DataTable.isDataTable('#tb_join')) {
        $('#tb_join').DataTable().ajax.reload(null, false);
    } else {
		tb_join = $('#tb_join').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/student.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildStudent";
                    data.classroom_id = classroom_id;
                    data.build_type = 'join';
                    data.group_selected = $("#group_selected").val();
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
			"order": [[1, "asc"]],
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
                    let student_firstname_en = row["student_firstname_en"] || "";
                    let student_lastname_en  = row["student_lastname_en"]  || "";
                    let student_firstname_th = row["student_firstname_th"] || "";
                    let student_lastname_th  = row["student_lastname_th"]  || "";
                    let fullNameTh = (student_firstname_th + " " + student_lastname_th).trim();
                    let fullNameEn = (student_firstname_en + " " + student_lastname_en).trim();
                    let displayName = "-";
                    if(fullNameTh && fullNameEn){
                        displayName = fullNameTh + " (" + fullNameEn + ")";
                    } else if(fullNameTh){
                        displayName = fullNameTh;
                    } else if(fullNameEn){
                        displayName = fullNameEn;
                    }
                    let student_nickname_en = row['student_nickname_en'] || "";
                    let student_nickname_th = row['student_nickname_th'] || "";
                    let displayNickname = "-";
                    if(student_nickname_en && student_nickname_th){
                        displayNickname = student_nickname_en + " (" + student_nickname_th + ")";
                    } else if(student_nickname_th){
                        student_nickname_th = student_nickname_th;
                    } else if(student_nickname_en){
                        displayNickname = student_nickname_en;
                    }
                    let student_email = row['student_email'];
                    let student_mobile = row['student_mobile'];
                    let student_company = row['student_company'];
                    let student_position = row['student_position'];
                    return `
                        <p><b>${displayName}</b></p>
                        <div><small>${displayNickname}</small></div>
                        <div><small><i class="fas fa-envelope-open-text"></i> ${student_email || "-"}</small></div>
                        <div><small><i class="fas fa-phone-volume"></i> ${student_mobile || "-"}</small></div>
                        <div><small><i class="fas fa-building"></i> ${student_company || "-"}</small></div>
                        <div><small><i class="fas fa-briefcase"></i> ${student_position || "-"}</small></div>
                    `;
                }
            },{ 
                "targets": 2,
                "data": "group_name",
            },{ 
                "targets": 3,
                "data": "student_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
					return `
                        <button type="button" class="btn btn-orange" lang="en" onclick="addToGroup(${data});">Add to Group</button>
                    `;
                }
            },{ 
                "targets": 4,
                "data": "student_firstname_en",
                "visible": false,
            },{ 
                "targets": 5,
                "data": "student_lastname_en",
                "visible": false,
            },{ 
                "targets": 6,
                "data": "student_firstname_th",
                "visible": false,
            },{ 
                "targets": 7,
                "data": "student_lastname_th",
                "visible": false,
            },{ 
                "targets": 8,
                "data": "student_nickname_en",
                "visible": false,
            },{ 
                "targets": 9,
                "data": "student_nickname_th",
                "visible": false,
            },{ 
                "targets": 10,
                "data": "student_email",
                "visible": false,
            },{ 
                "targets": 11,
                "data": "student_mobile",
                "visible": false,
            },{ 
                "targets": 12,
                "data": "student_company",
                "visible": false,
            },{ 
                "targets": 13,
                "data": "student_position",
                "visible": false,
            }]
        });
        $('div#tb_join_filter.dataTables_filter label input').remove();
        $('div#tb_join_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;">
        `;
        $('div#tb_join_filter.dataTables_filter input').hide();
        $('div#tb_join_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_join.search(val).draw();   
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_join.search('').draw();
                buildJoin();
            }
        });
    }
}
function addToGroup(student_id) {
    $.ajax({
        url: '/classroom/management/actions/student.php',
        type: "POST",
        data: {
            action: 'addToGroup',
            classroom_id: classroom_id,
            group_id: group_id,
            student_id: student_id
        },
        dataType: "JSON",
        success: function (result) {
            buildJoin();
            buildStudent();
        }
    });
}
function getGroupData() {
    $.ajax({
        url: '/classroom/management/actions/group.php',
        type: "POST",
        data: {
            action: 'groupData',
            group_id: group_id
        },
        dataType: "JSON",
        success: function (result) {
            let data = result.group_data;
            let group_logo = data.group_logo;
            let group_name = data.group_name;
            let group_color = data.group_color;
            let infoHtml = `
                <img src="${group_logo}" onerror="this.src='/images/noimage.jpg'" alt="Group Logo" id="group_logo" style="height: 50px;"> <span class="label label-warning label-title" style="background-color: ${group_color};">${group_name}</span>
            `;
            $(".group-info").html(infoHtml);
        }
    });
}