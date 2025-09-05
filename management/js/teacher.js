function buildTeacherPage() {
    $(".content-container").html(getTeacherTemplate());
    buildTeacher();
}
function getTeacherTemplate() {
    return `
        <table class="table table-border" id="tb_teacher">
            <thead>
                <tr>
                    <th></th>
                    <th lang="en">Teacher</th>
                    <th lang="en">Teacher Position</th>
                    <th lang="en">Company</th>
                    <th lang="en">Position</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th></th>
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
			"order": [[4,'desc']],
			"columns": [{ 
                "targets": 0,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 1,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 2,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 3,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 4,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 5,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 6,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 7,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            }]
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
function manageTeacher(teacher_id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Teacher Management</h5> 
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-white" lang="en" onclick="saveTeacher();">Close</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        
    `);
}