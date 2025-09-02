function buildCoursePage() {
    $(".content-container").html(getCoursetTemplate());
    buildCourse();
}
function getCoursetTemplate() {
    return `
        <table class="table table-border" id="tb_course">
            <thead>
                <tr>
                    <th style="width: 100px;"></th>
                    <th lang="en">Course</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
let tb_course;
function buildCourse() {
    if ($.fn.DataTable.isDataTable('#tb_course')) {
        $('#tb_course').DataTable().ajax.reload(null, false);
    } else {
		tb_course = $('#tb_course').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/course.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildCourse";
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
                "data": "course_cover",
                "render": function (data,type,row,meta) {	
					return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);" onerror="this.src='/images/training.jpg'">
                    `;
                }
            },{ 
                "targets": 1,
                "data": "course_name",
                "render": function (data,type,row,meta) {	
					return `
                        <p><b>${data}</b></p>
                        <div class="text-grey"><i class="fas fa-bookmark"></i> ${(row['course_type'] == 'course') ? 'Course' : 'Learning Map'}</div>
                    `;
                }
            },{ 
                "targets": 2,
                "data": "date_create",
            },{ 
                "targets": 3,
                "data": "emp_create",
            },{ 
                "targets": 4,
                "data": "course_id",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-red btn-circle" onclick="delCourse(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            }]
        });
        $('div#tb_course_filter.dataTables_filter label input').remove();
        $('div#tb_course_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="newCourse('')"><i class="fas fa-plus"></i> <span lang="en">Course</span></button>
        `;
        $('div#tb_course_filter.dataTables_filter input').hide();
        $('div#tb_course_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_course.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_course.search('').draw();
                buildCourse();
            }
        });
    }
}
function newCourse() {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">New Course</h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <table class="table table-border" id="tb_course_master" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 100px;"></th>
                    <th lang="en">Course</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table> 
    `);
    $('.systemModal').on('shown.bs.modal', function () {
        buildCourseMaster();
        if ($.fn.DataTable.isDataTable('#tb_course_master')) {
            $('#tb_course_master').DataTable().columns.adjust();
        }
    });
}
let tb_course_master;
function buildCourseMaster() {
    if ($.fn.DataTable.isDataTable('#tb_course_master')) {
        $('#tb_course_master').DataTable().ajax.reload(null, false);
    } else {
		tb_course_master = $('#tb_course_master').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/course.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildCourseMaster";
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
			"order": [[1,'asc']],
			"columns": [{ 
                "targets": 0,
                "data": "course_cover",
                "orderable": false,
                "render": function (data,type,row,meta) {	
					return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);" onerror="this.src='/images/training.jpg'">
                    `;
                }
            },{ 
                "targets": 1,
                "data": "course_name",
                "render": function (data,type,row,meta) {	
					return `
                        <p><b>${data}</b></p>
                        <div class="text-grey"><i class="fas fa-bookmark"></i> ${(row['course_type'] == 'course') ? 'Course' : 'Learning Map'}</div>
                    `;
                }
            },{ 
                "targets": 2,
                "data": "course_id",
                "render": function (data,type,row,meta) {
                    let course_type = row['course_type'];	
					return `
                        <button type="button" class="btn btn-orange" lang="en" onclick="addToClassroom(${data},'${course_type}')">Add to Classroom</button>
                    `;
                }
            }]
        });
        $('div#tb_course_master_filter.dataTables_filter label input').remove();
        $('div#tb_course_master_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;">
        `;
        $('div#tb_course_master_filter.dataTables_filter input').hide();
        $('div#tb_course_master_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_course_master.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_course_master.search('').draw();
                buildCourseMaster();
            }
        });
    }
}
function addToClassroom(course_id, course_type) {
    event.stopPropagation();
    swal({
        html: true,
        title: window.lang.translate("Confirm Action"),
        text: "Do you want to add this course to the classroom?",
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Yes, Add"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/course.php",
                type: "POST",
                data: {
                    action:'addToClassroom',
                    classroom_id: classroom_id,
                    course_id: course_id,
                    course_type: course_type
                },
                dataType: "JSON",
                success: function(result){
                    if(result.status === true){			
                        swal({
                            type: 'success',
                            title: "Added Successfully",
                            text: "The course has been added to the classroom.",
                            showConfirmButton: false,
                            timer: 1500
                        });							
                        buildCourse();
                        buildCourseMaster();
                    } else {
                        swal({
                            type: 'error',
                            title: "Error",
                            text: "Something went wrong while adding the course.",
                            timer: 2000
                        });
                    }
                }
            });
        } else {
            swal.close();
        }
    }); 
}
function delCourse(course_id) {
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
                url: "/classroom/management/actions/course.php",
                type: "POST",
                data: {
                    action:'delCourse',
                    course_id: course_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildCourse();
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