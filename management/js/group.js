function buildGroupPage() {
    $(".content-container").html(getGroupTemplate());
    buildGroup();
}
function getGroupTemplate() {
    return `
        <table class="table table-border" id="tb_group">
            <thead>
                <tr>
                    <th style="width: 100px;"></th>
                    <th lang="en">Group</th>
                    <th lang="en">Color</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th lang="en">Student</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
let tb_group;
function buildGroup() {
    if ($.fn.DataTable.isDataTable('#tb_group')) {
        $('#tb_group').DataTable().ajax.reload(null, false);
    } else {
		tb_group = $('#tb_group').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/group.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildGroup";
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
                "data": "group_logo",
                "render": function (data,type,row,meta) {	
					return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);" onerror="this.src='/images/noimage.jpg'">
                    `;
                }
            },{ 
                "targets": 1,
                "data": "group_name",
                "render": function (data,type,row,meta) {	
					return `
                        <p><b>${data}</b></p>
                        ${(row['group_description']) ? `<div class="text-grey">${row['group_description']}</div>` : ``}
                    `;
                }
            },{ 
                "targets": 2,
                "data": "group_color",
                "render": function (data,type,row,meta) {	
					return `
                        <i class="fas fa-square fa-2x" style="color: ${data};"></i>
                    `;
                }
            },{ 
                "targets": 3,
                "data": "date_create",
            },{ 
                "targets": 4,
                "data": "emp_create",
            },{ 
                "targets": 5,
                "data": "group_student",
                "className": "text-right",
            },{ 
                "targets": 6,
                "data": "group_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-info btn-circle" onclick="configGroup(${data})"><i class="fas fa-user-cog"></i></button> 
                            <button type="button" class="btn btn-orange btn-circle" onclick="manageGroup(${data})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delGroup(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            }]
        });
        $('div#tb_group_filter.dataTables_filter label input').remove();
        $('div#tb_group_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageGroup('')"><i class="fas fa-plus"></i> <span lang="en">Group</span></button>
        `;
        $('div#tb_group_filter.dataTables_filter input').hide();
        $('div#tb_group_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_group.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_group.search('').draw();
                buildGroup();
            }
        });
    }
}
function configGroup(group_id) {
    $.redirect("group",{classroom_id: classroom_id, group_id: group_id},'post','_blank');
}
function manageGroup(group_id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Group Management</h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="saveGroup();">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <form id="group_form">
            <input type="hidden" id="group_id" name="group_id" value="${group_id}">
            <p class="text-center" lang="en">Group Logo</p>
            <div id="AvatarFileUpload" class="selected-image-holder">
                <img src="/images/noimage.jpg" class="image-profile" alt="AvatarInput" onerror="this.src='/images/noimage.jpg'">
                <div class="avatar-selector">
                    <a href="#" class="avatar-selector-btn"><i class="fas fa-camera-retro"></i></a>
                    <input type="file" accept="image/*" name="group_logo" id="group_logo" autocomplete="off">
                </div>
            </div>
            <p class="text-center text-orange" style="margin-top:15px;">
                <i class="fas fa-lightbulb"></i> <span lang="en">Only image files are allowed. Please upload a file like .jpg, .png, or .gif.</span>
            </p>
            <p><span lang="en">Group Name</span> <code>*</code></p>
            <input type="text" id="group_name" name="group_name" class="form-control require_obj" autocomplete="off">
            <p style="margin: 10px auto;"><span lang="en">Group Color</span> <code>*</code></p>
            <input type="color" id="group_color" name="group_color" value="#FF9900">
            <p style="margin: 10px auto;"><i class="fas fa-align-left"></i> <span lang="en">Description</span></p>
            <textarea class="form-control" id="group_description" name="group_description" oninput="autoResize(this)" style="overflow: hidden; min-height: 75px; font-size: 12px !important;"></textarea>
        </div>
    `);
    initAvatarUpload();
    if(group_id) {
        $.ajax({
            url: '/classroom/management/actions/group.php',
            type: "POST",
            data: {
                action: 'groupData',
                group_id: group_id
            },
            dataType: "JSON",
            success: function (result) {
                if (result.status) {
                    const data = result.group_data;
                    $('#group_name').val(data.group_name);
                    $('#group_description').val(data.group_description);
                    $('#group_color').val(data.group_color);
                    if (data.group_logo) {
                        $('#AvatarFileUpload img').attr('src', data.group_logo);
                    }
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
function initAvatarUpload() {
    const avatarUpload = document.getElementById('AvatarFileUpload');
    if (!avatarUpload) return;
    const imageViewer = avatarUpload.querySelector('.selected-image-holder>img');
    const imageSelector = avatarUpload.querySelector('.avatar-selector-btn');
    const imageInput = avatarUpload.querySelector('input[name="group_logo"]');
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
function saveGroup() {
    var err = 0;
	$.each($(".require_obj"), function(){    
		if(!$(this).val()) {
			++err;
		}              
	});
	if(err > 0) {
		swal({type: 'warning', title: "Warning...", text: 'Please input all item completely.', showConfirmButton: false, timer: 2500,showConfirmButton: false});
		return;
	}
    const form = document.getElementById("group_form");
	const fd = new FormData(form);
    fd.append('classroom_id', classroom_id);
    $.ajax({
		url: "/classroom/management/actions/group.php?action=saveGroup",
		type: "POST",
		data: fd,
		processData: false,
		contentType: false,
		dataType: "JSON",
		success: function(result) {
			$(".loader").removeClass("active");
			if(result.status === true) {
				swal({type: 'success', title: "Successfully", text: "", showConfirmButton: false, timer: 2500});
                buildGroup();
				$(".systemModal").modal("hide");
			} else {
                swal({type: 'error',title: "Sorry...",text: "Something went wrong! Please try again later.",timer: 2500});
            }
        }
    });
}
function delGroup(group_id) {
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
                url: "/classroom/management/actions/group.php",
                type: "POST",
                data: {
                    action:'delGroup',
                    group_id: group_id
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