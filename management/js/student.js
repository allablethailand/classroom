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
                "targets": 11,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 12,
                "data": "student_firstname_en",
                "visible": false,
            },{ 
                "targets": 13,
                "data": "student_lastname_en",
                "visible": false,
            },{ 
                "targets": 14,
                "data": "student_firstname_th",
                "visible": false,
            },{ 
                "targets": 15,
                "data": "student_lastname_th",
                "visible": false,
            },{ 
                "targets": 16,
                "data": "student_nickname_en",
                "visible": false,
            },{ 
                "targets": 17,
                "data": "student_nickname_th",
                "visible": false,
            },{ 
                "targets": 18,
                "data": "student_mobile",
                "visible": false,
            },{ 
                "targets": 19,
                "data": "student_email",
                "visible": false,
            },{ 
                "targets": 20,
                "data": "student_idcard",
                "visible": false,
            },{ 
                "targets": 21,
                "data": "student_passport",
                "visible": false,
            },{ 
                "targets": 22,
                "data": "student_company",
                "visible": false,
            },{ 
                "targets": 23,
                "data": "student_position",
                "visible": false,
            }]
        });
        $('div#tb_student_filter.dataTables_filter label input').remove();
        $('div#tb_student_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;">
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