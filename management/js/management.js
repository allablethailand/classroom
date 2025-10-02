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
                var classroom_mode = data['classroom_mode'];
                if(classroom_mode == 'online') {
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
			"order": [[9,'desc']],
			"columns": [{ 
                "targets": 0,
                "data": "classroom_poster",
                "className": "dt-click",
                "render": function (data,type,row,meta) {	
					return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);" onerror="this.src='/images/training.jpg'">
                    `;
                }
            },{ 
                "targets": 1,
                "data": "classroom_name",
                "className": "dt-click",
                "render": function (data,type,row,meta) {	
					return `
                        <b>${data}</b>
                    `;
                }
            },{ 
                "targets": 2,
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
                "render": function (data,type,row,meta) {	
					return (data == 'online') ? `<span class="label label-success">Online</span>` : `<span class="label label-warning">Onsite</span>`;
                }
            },{ 
                "targets": 5,
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
            },{ 
                "targets": 8,
                "data": "classroom_promote",
                "className": "dt-click",
                "render": function (data,type,row,meta) {	
                    let classroom_id = row['classroom_id'];
					return `
                        <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchClassroom(${classroom_id}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>
                    `;
                }
            },{ 
                "targets": 9,
                "data": "classroom_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
                    let classroom_name = row['classroom_name'];
                    let classroom_link = row['classroom_link'];
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-info btn-circle" onclick="showQRCode('${classroom_link}', '${classroom_name}')" title="Show QR Code"><i class="fas fa-qrcode"></i></button> 
                            <button type="button" class="btn btn-primary btn-circle" onclick="viewLink('${data}')" title="Show QR Code"><i class="fa fa-link"></i></button> 
                            <button type="button" class="btn btn-orange btn-circle" onclick="manageClassroom(${data})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delClassroom(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
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
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageClassroom('')"><i class="fas fa-plus"></i> <span lang="en">Academy</span></button>
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
function switchClassroom(classroom_id,option) {
    if(option == 0){
        var message = 'Hide';
		var topic = 'Hidden on login page';
        var type_color = 'error';
		var button_color = '#FF6666';
	} else {
		var message = 'Show';
		var topic = 'Show on login page';
		var type_color = 'info';
		var button_color = '#5bc0de';
	}
	event.stopPropagation();
	swal({ 
		html:true,
		title: window.lang.translate(`${topic}?`),
		text: ``,
		type: type_color,
		showCancelButton: true,
		closeOnConfirm: false,
		confirmButtonText: window.lang.translate(message),
		cancelButtonText: window.lang.translate("Cancel"),	
		confirmButtonColor: button_color,
		cancelButtonColor: '#CCCCCC',
		showLoaderOnConfirm: true,
	},
	function(isConfirm){
		if (isConfirm) {
			$.ajax({
                url: "/classroom/management/actions/management.php",
                type: "POST",
                data: {
                    action:'switchClassroom',
                    classroom_id: classroom_id,
                    classroom_promote: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildClassroom();
                }
            });
		}else{
			swal.close();
		}
	});
}
function showQRCode(classroom_link, classroom_name) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Classroom QR Code</h5>
    `);
    $(".systemModal .modal-body").html(`
        <h4 class="text-center">${classroom_name}</h4>
        <br>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 text-center">
                <div id="qrcode"></div>
            </div>
        </div>
    `);
    $(".systemModal .modal-footer").html(`
        <a id="download" class="btn btn-orange" href="#" download="qrcode.png" style="font-size:12px;">Download QR Code</a>
        <a style="font-size:10px;" class="btn btn-white share-link copy-4 copy-qr2" onclick="copyLink(4)">
            <i class="fas fa-link"></i> <span lang="en">Copy</span>
            <span class="notofication-share"><i class="fas fa-check"></i> <label lang="en">Copy Link</label></span>
        </a>
        <button type="button" class="btn btn-white" data-dismiss="modal" style="font-size:12px;">Close</button>
    `);
    var text = classroom_link;
    $('#qrcode').empty(); 
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: text,
        width: 1024,
        height: 1024,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    $('#download').off('click').on('click', function(e){
        e.preventDefault();
        html2canvas($(".systemModal .modal-body")[0]).then(function(canvas) {
            var dataUrl = canvas.toDataURL('image/png');
            var link = document.createElement('a');
            link.href = dataUrl;
            link.download = classroom_name.replace(/\s+/g, '_') + '.png';
            link.click();
        });
    });
    $(".copy-qr2").attr("data-clipboard-text", classroom_link);
    new ClipboardJS('.copy-qr2');
}
function manageClassroom(classroom_id) {
    $.redirect("detail",{classroom_id: classroom_id},'post','_blank');
}
function delClassroom(classroom_id) {
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
                url: "/classroom/management/actions/management.php",
                type: "POST",
                data: {
                    action:'delClassroom',
                    classroom_id: classroom_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildClassroom();
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
$(window).on('storage', function(e) {
	if (e.originalEvent.key === 'reloadManagement') {
		buildClassroom();
	}
});
function viewLink(classroom_id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title"></h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $.ajax({
        url: "/classroom/management/actions/management.php",
        type: "POST",
        data: {
            action:'viewLink',
            classroom_id: classroom_id
        },
        dataType: "JSON",
        type: 'POST',
        success: function(result){
            if(result.status === true){			
                let classroom_name = result.classroom_name;
                let login_url = result.login_url;
                let register_url = result.register_url;
                $(".modal-title").html(classroom_name);
                var panels = [];
                var links = [
                    { icon: 'fa-sign-in-alt text-orange', label: 'Login', key: 'login_url', copyId: 1 },
                    { icon: 'fa-notes-medical text-orange', label: 'Register', key: 'register_url', copyId: 2 },
                ];
                $.each(links, function(i, item) {
                    var value = result[item.key];
                    if (value) {
                        panels.push(`
                            <div class="col-sm-6 col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-body text-center">
                                        <i class="fa ${item.icon} fa-4x"></i>
                                        <p lang="en" style="margin-top:15px;">${item.label}</p>
                                        <input type="hidden" class="form-control input-sm m-b-sm" readonly value="${value}">
                                        <a class="btn btn-xs btn-orange" href="${value}" target="_blank">Go to</a> 
                                        <a class="btn btn-xs btn-white text-grey share-link copy-${item.copyId}" 
                                        onclick="copyLink(${item.copyId})"
                                        data-clipboard-text="${value}">
                                        <i class="fa fa-link"></i> <span lang="en">Copy</span>
                                        <span class="notofication-share"><i class="fa fa-check"></i> 
                                        <label lang="en">Copy Link</label></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                });
                var html = `<div class="row">${panels.join('')}</div>`;
                $(".systemModal .modal-body").html(html);
            } else {
                swal({type: 'error',title: "Sorry...",text: "Something went wrong!",timer: 2000});
            }
        }
    });
}
function copyLink(rows) {
    new ClipboardJS('.copy-'+rows);
    $(".copy-"+rows+" .notofication-share").addClass("active");
    setTimeout(function() { 
        $(".copy-"+rows+" .notofication-share").removeClass("active");
    }, 2000);
}