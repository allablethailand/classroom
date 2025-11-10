function buildPositionPage() {
    $(".content-container").html(getPositionTemplate());
    buildPosition();
}
function getPositionTemplate() {
    return `
        <table class="table table-border" id="tb_position">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
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
let tb_position;
function buildPosition() {
    if ($.fn.DataTable.isDataTable('#tb_position')) {
        $('#tb_position').DataTable().ajax.reload(null, false);
    } else {
        tb_position = $('#tb_position').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
            "ajax": {
                "url": "/classroom/setup/actions/position.php",
                "type": "POST",
                "data": function (data) {
                    data.action = "buildPosition";
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
            "order": [[0,'asc'], [4, 'desc']],
            "columns": [{ 
                "targets": 0,
                "data": "is_active",
                "className": "text-center",
                "render": function (data, type, row, meta) {
                    let id = row['position_id'];
					return `
                        <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchPosition(${id}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>
                    `;
                }
            },{ 
                "targets": 1,
                "data": "position_cover",
                "render": function (data, type, row, meta) {
                    return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);" onerror="this.src='/images/noimage.jpg'">
                    `;
                }
            },{ 
                "targets": 2,
                "data": "position_name_en",
                "render": function (data, type, row, meta) {
                    let position_name_en = row["position_name_en"];
                    let position_name_th = row['position_name_th'];
                    let position_description = row['position_description'];
                    let html = `
                        <p><b>${position_name_en}</b></p>
                        <div>${position_name_th}</div>
                        ${(position_description) ? `
                            <div>${position_description}</div>
                            ` : ``}
                    `;
                    return html;
                }
            },{ 
                "targets": 3,
                "data": "date_create"
            },{ 
                "targets": 4,
                "data": "emp_create"
            },{ 
                "targets": 5,
                "data": "position_id",
                "render": function (data, type, row, meta) {
                    return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-orange btn-circle" onclick="managePosition(${data})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delPPosition(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            }]
        });
        $('div#tb_position_filter.dataTables_filter label input').remove();
        $('div#tb_position_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="managePosition('')"><i class="fas fa-plus"></i> <span lang="en">Position</span></button>
        `;
        $('div#tb_position_filter.dataTables_filter input').hide();
        $('div#tb_position_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_position.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_position.search('').draw();
                buildPosition();
            }
        });
    }
}
function managePosition(id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Position Management</h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="savePosition();">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <form id="position_form">
            <input type="hidden" id="position_id" name="position_id" value="${id}">
            <p class="text-center" lang="en">Position Logo</p>
            <div id="AvatarFileUpload" class="selected-image-holder">
                <img src="/images/noimage.jpg" class="image-profile" alt="AvatarInput" onerror="this.src='/images/noimage.jpg'">
                <div class="avatar-selector">
                    <a href="#" class="avatar-selector-btn"><i class="fas fa-camera-retro"></i></a>
                    <input type="file" accept="image/*" name="position_cover" id="position_cover">
                </div>
            </div>
            <p class="text-center text-orange" style="margin-top:15px;">
                <i class="fas fa-lightbulb"></i> <span lang="en">Only image files are allowed. Please upload a file like .jpg, .png, or .gif.</span>
            </p>
            <p><span lang="en">Position Name</span> (EN) <code>*</code></p>
            <input type="text" id="position_name_en" name="position_name_en" class="form-control require_obj" autocomplete="off">
            <p><span lang="en">Position Name</span> (TH) <code>*</code></p>
            <input type="text" id="position_name_th" name="position_name_th" class="form-control require_obj" autocomplete="off">
            <p><span lang="en">Description</span></p>
            <textarea class="form-control" id="position_description" name="position_description"></textarea>
        </form>
    `);
    initPositionUpload();
    if(id) {
        $.ajax({
            url: '/classroom/setup/actions/position.php',
            type: "POST",
            data: {
                action: 'positionData',
                id: id
            },
            dataType: "JSON",
            success: function (result) {
                if (result.status) {
                    const data = result.position_data;
                    $('#method_name').val(data.method_name);
                    if (data.position_cover) {
                        $('#AvatarFileUpload img').attr('src', data.position_cover);
                    }
                    $('#position_name_en').val(data.position_name_en);
                    $('#position_name_th').val(data.position_name_th);
                    $('#position_description').val(data.position_description);
                } else {
                    swal({
                        type: 'warning',
                        title: "Warning...",
                        text: 'Failed to load AI data.',
                        timer: 2500
                    });
                }
            }
        });
    }
}
function savePosition() {
    var err = 0;
    $(".require_obj:visible").each(function() {
        if (!$(this).val()) {
            ++err;
        }
    });
    if (err > 0) {
        swal({
            type: 'warning',
            title: "Warning...",
            text: 'Please input all item completely.',
            showConfirmButton: false,
            timer: 2500
        });
        return;
    }
    const form = document.getElementById("position_form");
    const fd = new FormData(form);
    $.ajax({
        url: "/classroom/setup/actions/position.php?action=savePosition",
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: function(result) {
            $(".loader").removeClass("active");
            if (result.status === true) {
                swal({
                    type: 'success',
                    title: "Successfully",
                    text: "",
                    showConfirmButton: false,
                    timer: 2500
                });
                buildPosition();
                $(".systemModal").modal("hide");
            } else {
                swal({
                    type: 'error',
                    title: "Sorry...",
                    text: "Something went wrong! Please try again later.",
                    timer: 2500
                });
            }
        }
    });
}
function initPositionUpload() {
    const avatarUpload = document.getElementById('AvatarFileUpload');
    if (!avatarUpload) return;
    const imageViewer = avatarUpload.querySelector('.selected-image-holder>img');
    const imageSelector = avatarUpload.querySelector('.avatar-selector-btn');
    const imageInput = avatarUpload.querySelector('input[name="position_cover"]');
    imageSelector?.addEventListener('click', e => {
        e.preventDefault();
        imageInput?.click();
    });
    imageInput?.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            swal({type: 'warning', title: "Warning...", text: 'Only image files are allowed. Please upload a file like .jpg, .png, or .gif.', showConfirmButton: false, timer: 2500,showConfirmButton: false});
            imageInput.value = ''; 
            return;
        }
        const reader = new FileReader();
        reader.onload = () => imageViewer.src = reader.result;
        reader.readAsDataURL(file);
    });
}
function delPosition(id) {
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
                url: "/classroom/setup/actions/position.php",
                type: "POST",
                data: {
                    action:'delPosition',
                    id: id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildPosition();
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
function switchPosition(id,option) {
    if(option == 0){
		var message = 'Unpublish';
		var topic = 'Unpublish';
		var type_color = 'error';
		var button_color = '#FF6666';
	}else{
		var message = 'Publish';
		var topic = 'Publish';
		var type_color = 'info';
		var button_color = '#5bc0de';
	}
	event.stopPropagation();
	swal({ 
		html:true,
		title: window.lang.translate(`${topic} Position?`),
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
                url: "/classroom/setup/actions/position.php",
                type: "POST",
                data: {
                    action:'switchPosition',
                    id: id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildPosition();
                }
            });
		} else {
			swal.close();
		}
	});
}