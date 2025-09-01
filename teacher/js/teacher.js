$(document).ready(function() {
    buildTeacher();
});
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
				"url": "/classroom/teacher/actions/teacher.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildTeacher";
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
			"order": [[0,'desc'], [2,'asc']],
			"columns": [{ 
                "targets": 0,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 1,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 2,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 3,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 4,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 5,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 6,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 7,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 8,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            },{ 
                "targets": 9,
                "render": function (data, type, row, meta) {
                    return ``;
                }
            }]
        });
        $('div#tb_teacher_filter.dataTables_filter label input').remove();
        $('div#tb_teacher_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageTeacher('')"><i class="fas fa-plus"></i> <span lang="en">New teacher</span></button>
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

}