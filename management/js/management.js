$(document).ready(function() {
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
		buildClassroom();
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
		buildClassroom();
	});	
    $(".filter-select").change(function() {
        buildClassroom();
    });
    buildClassroom();
});
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
let tb_classroom;
function buildClassroom() {
    if ($.fn.DataTable.isDataTable('#tb_classroom')) {
        $('#tb_classroom').DataTable().ajax.reload(null, false);
    } else {
		tb_classroom = $('#tb_classroom').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/management.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildClassroom";
                    data.filter_date = $('#filter_date').val();
                    data.filter_mode = $('#filter_mode').val();
				}
			},
			"language": default_language,
			"responsive": true,
			"searchDelay": 1000,
			"deferRender": false,
            "createdRow": function(row,data,dataIndex,meta) {
                var classroom_type = data['classroom_type'];
                if(classroom_type == 'online') {
                    $(row).addClass('tr-green');
                } else {
                    $(row).addClass('tr-orange');
                }
			},
			"drawCallback": function( settings ) {
				var lang = new Lang();
				lang.dynamic('th', '/js/langpack/th.json?v='+Date.now());
				lang.init({
					defaultLang: 'en'
				});
			},
			"order": [[6,'desc']],
			"columns": [{ 
                "targets": 0,
<<<<<<< HEAD
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 1,
=======
                "data": "classroom_poster",
                "className": "dt-click",
                "render": function (data,type,row,meta) {	
					return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);">
                    `;
                }
            },{ 
                "targets": 1,
                "data": "classroom_name",
                "className": "dt-click",
>>>>>>> c82db78991ceb63babbe6f0d1ecc2be69f040a54
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 2,
<<<<<<< HEAD
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
=======
                "data": "classroom_date",
                "className": "dt-click"
            },{ 
                "targets": 3,
                "data": "classroom_student",
                "className": "text-right dt-click"
            },{ 
                "targets": 4,
                "data": "classroom_mode",
                "className": "dt-click",
>>>>>>> c82db78991ceb63babbe6f0d1ecc2be69f040a54
                "render": function (data,type,row,meta) {	
					return ``;
                }
            },{ 
                "targets": 5,
<<<<<<< HEAD
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
=======
                "data": "classroom_register",
                "className": "text-right dt-click"
            },{ 
                "targets": 6,
                "data": "date_create",
                "className": "dt-click"
            },{ 
                "targets": 7,
                "data": "emp_create",
                "className": "dt-click"
>>>>>>> c82db78991ceb63babbe6f0d1ecc2be69f040a54
            },{ 
                "targets": 8,
                "render": function (data,type,row,meta) {	
					return ``;
                }
            }]
        });
        $('#tb_classroom tbody').on('click', 'tr td.dt-click', function () {
            var row = tb_classroom.row(this).data();
            manageClassroom(row['classroom_id']);
        });
        $('div#tb_classroom_filter.dataTables_filter label input').remove();
        $('div#tb_classroom_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageClassroom('')"><i class="fas fa-plus"></i> <span lang="en">Classroom</span></button>
        `;
        $('div#tb_classroom_filter.dataTables_filter input').hide();
        $('div#tb_classroom_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_classroom.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_classroom.search('').draw();
                buildClassroom();
            }
        });
    }
}
function manageClassroom(classroom_id) {
    $.redirect("detail",{classroom_id: classroom_id},'post','_blank');
}