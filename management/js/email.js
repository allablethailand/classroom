function buildEmailPage() {
    $(".content-container").html(buildEmailTemplate());
    buildEmail();
}
function buildEmailTemplate() {
    return `
        <table class="table table-border" id="tb_email">
            <thead>
                <tr>
                    <th></th>
                    <th lang="en" style="width: 75px;">Send Status</th>
                    <th lang="en">Subject</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
var tb_email;
function buildEmail() {
    const $table = $('#tb_email');
    if ($.fn.DataTable.isDataTable($table)) {
        $table.DataTable().ajax.reload(null, false);
        return;
    }
    tb_email = $table.DataTable({
        processing: true,
        lengthMenu: [[50, 100, 250, 500, 1000, -1], [50, 100, 250, 500, 1000, "All"]],
        ajax: {
            url: "/classroom/management/actions/email.php",
            type: "POST",
            data: d => {
                d.action = "buildEmail";
                d.classroom_id = classroom_id;
            }
        },
        language: default_language,
        responsive: true,
        searchDelay: 1000,
        deferRender: false,
        order: [[0, 'asc']],
        columns: [{
            data: "mail_template_id",
            visible: false
        },{
            data: "mail_sending",
            className: "text-center",
            render: (data, type, row) => {
                return `
                    <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchEmail(${row['mail_template_id']}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>
                `;
            }
        },{
            data: "mail_subject",
            render: (data, type, row) => {
                const {
                    mail_reason, mail_reference, date_create,
                    mail_template_id, emp_name, mail_name
                } = row;
                const icon = mail_reference != 0 ? '<i class="fas fa-star text-orange"></i>' : '<i class="fas fa-mail-bulk"></i>';
                return `
                    <div class="row">
                        <div class="col-sm-10">
                            <p><b>${icon} ${data}</b></p>
                            <div class="text-grey" style="font-size:11px;">
                                <i class="fas fa-tags"></i> ${mail_name}
                            </div>
                            ${mail_reason ? `
                                <p class="text-grey" style="font-size:11px;">${mail_reason}</p>
                            ` : ''}
                            <div class="text-grey" style="font-size:11px;">
                                <i class="far fa-calendar"></i> ${date_create}
                            </div>
                            <div class="text-grey" style="font-size:11px;">
                                <i class="far fa-user-circle"></i> ${emp_name}
                            </div>
                        </div>
                        <div class="col-sm-2 text-right">
                            <div class="nowrap">
                                <button type="button" class="btn btn-info btn-circle" onclick="previewTemplated(${mail_template_id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-orange btn-circle" onclick="manageEmailTemplate(${mail_template_id})">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                ${mail_reference == 0 ? `
                                    <button type="button" class="btn btn-red btn-circle" onclick="delTemplate(${mail_template_id})">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }
        }],
        drawCallback: () => {
            const lang = new Lang();
            lang.dynamic('th', `/js/langpack/th.json?v=${Date.now()}`);
            lang.init({ defaultLang: 'en' });
        }
    });
    const $filter = $('div#tb_email_filter.dataTables_filter');
    $filter.find('label input, label span').remove();
    const customFilterHTML = `<input type="search" class="form-control search-datatable" placeholder="Search..." autocomplete="off" style="height:30px;"> `;
    $filter.find('label').append(customFilterHTML);
    const searchDataTable = $.fn.dataTable.util.throttle((val) => {
        if (val !== undefined) {
            tb_email.search(val).draw();
        }
    }, 1000);
    $('.search-datatable').on('keyup', function (e) {
        const val = $(this).val().trim().replace(/ /g, "");
        if (e.keyCode === 13) {
            $('.dataTables_processing.panel').css('top', '5%');
            searchDataTable(val);
        } else if (!val) {
            tb_email.search('').draw();
            buildEmail();
        }
    });
}
function switchEmail(template_id, option) {
    if(option == 0){
		var message = 'Turn Off';
		var topic = 'Turn Off';
		var type_color = 'error';
		var button_color = '#FF6666';
	}else{
		var message = 'Turn On';
		var topic = 'Turn On';
		var type_color = 'info';
		var button_color = '#5bc0de';
	}
	event.stopPropagation();
	swal({ 
		html:true,
		title: window.lang.translate(`${topic} email template?`),
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
                url: "/classroom/management/actions/email.php",
                type: "POST",
                data: {
                    action:'switchEmail',
                    classroom_id: classroom_id,
                    template_id: template_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildEmail();
                }
            });
		}else{
			swal.close();
		}
	});
}
function restoreTemplate(template_id, event) {
    if (event) event.stopPropagation();
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: "Are you sure to do this?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: window.lang.translate("Yes"),
        cancelButtonText: window.lang.translate("Cancel"),
        confirmButtonColor: "#FF9900",
        cancelButtonColor: "#CCCCCC",
        showLoaderOnConfirm: true,
        closeOnConfirm: false
    }, function (isConfirm) {
        if (!isConfirm) return swal.close();
        $.ajax({
            url: "/classroom/management/actions/email.php",
            type: "POST",
            dataType: "JSON",
            data: {
                action: "restoreTemplate",
                template_id: template_id,
                classroom_id: classroom_id
            },
            success: function (result) {
                handleResponse(result);
                if (result.status === true) {
                    swal({
                        type: "success",
                        title: "Successfully",
                        text: "",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    manageEmailTemplate(template_id);
                } else {
                    swal({
                        type: "error",
                        title: "Sorry...",
                        text: "Something went wrong!",
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function () {
                swal({
                    type: "error",
                    title: "Error",
                    text: "AJAX request failed!",
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    });
}
function manageEmailTemplate(template_id) {
	$(".modal-template").modal();
    $(".modal-template .modal-title").html(`
        <button type="button" class="btn btn-white restore-template hidden" onclick="restoreTemplate(${template_id});" style="font-size:12px;">
            <i class="fas fa-tools"></i> <span lang="en">Restore to default template</span>
        </button>  
    `);
    const masterData = [
        { key: 0, label: "Origami Academy Logo",text: "{{origamiAcademyLogo}}", },
        { key: 1, label: "Academy Logo",text: "{{academyLogo}}", },
        { key: 2, label: "Academy Name", text: "{{academyName}}", },
        { key: 3, label: "Academy Start", text: "{{academyStart}}", },
        { key: 4, label: "Academy End", text: "{{academyEnd}}", },
        { key: 5, label: "Location Name", text: "{{academyLocationName}}", },
        { key: 6, label: "Academy Information", text: "{{academyInfomation}}", },
        { key: 7, label: "Contact Us", text: "{{academyContactUs}}", },
        { key: 8, label: "Login Link", text: "tenantLink", },
    ];
    const studentData = [
        { key: 9, label: "Name", text: "{{studentName}}" },
        { key: 10, label: "Profile", text: "{{studentAvatar}}" },
        { key: 11, label: "Email", text: "{{studentEmail}}" },
        { key: 12, label: "Mobile", text: "{{studentTel}}" },
        { key: 13, label: "Company", text: "{{studentCompany}}" },
        { key: 14, label: "Position", text: "{{studentPosition}}" },
        { key: 15, label: "Username", text: "{{studentUsername}}" },
        { key: 16, label: "Password", text: "{{studentPassword}}" },
    ];
    function generateListClassroom(data) {
        return data.map(item => `
            <span class="label label-default" style="padding: .2em .6em .3em; border-radius:5px; margin: 5px; display: inline-block; font-size:10px;">
                <i class="fas fa-circle"></i> <span lang="en">${item.label}</span>
                <a class="badge copy-${item.key}" onclick="copyKey(${item.key})" data-clipboard-text="${item.text}">
                    <i class="fas fa-copy"></i> <span class="notofication-share"><i class="fas fa-check"></i> <label lang="en">Copied</label></span>
                </a>
            </span>
        `).join('');
    }
    function generateListItems(data) {
        return data.map(item => `
            <li class="list-group-item">
                <i class="fas fa-circle"></i> <span lang="en">${item.label}</span>
                <a class="badge copy-${item.key}" onclick="copyKey(${item.key})" data-clipboard-text="${item.text}">
                    <i class="fas fa-copy"></i> <span class="notofication-share"><i class="fas fa-check"></i> <label lang="en">Copied</label></span>
                </a>
            </li>
        `).join('');
    }
	$(".modal-template .modal-body").html(`
		<input type="hidden" id="template_id" value="${template_id}">
        <div class="row">
            <div class="col-xs-3">
                <div class="panel panel-default" style="font-size:10px;">
                    <div class="panel-heading" role="tab" id="question1">
                        <h5 class="panel-title">
                            <a class="collapsed" data-toggle="collapse" data-parent="#faq" href="#answer1" aria-expanded="false" aria-controls="answer1">
                                <i class="fas fa-user-circle"></i> <span lang="en">Guest Data</span>
                            </a>
                        </h5>
                    </div>
                    <div id="answer1" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="question1">
                        <ul class="list-group">
                            ${generateListItems(studentData)}
                        </ul>
                    </div>
                </div>
                <div class="for-follower"></div>
            </div>
			<div class="col-xs-9">
                <p style="margin:10px auto;"><b><i class="fas fa-pencil-alt"></i> <span lang="en">Subject</span></b> <code>*</code></p>
                <input type="text" class="form-control require_obj" id="template_subject">
                <div style="margin:15px auto;">
                    ${generateListClassroom(masterData)}
                </div>
				<p style="margin:10px auto;"><b><i class="fas fa-align-left"></i> <span lang="en">Template</span></b> <code>*</code></p>
				<textarea class="require_obj" id="template" lang="en" style="width:100%;"></textarea>
			</div>
		</div>
	`);
	$(".modal-template .modal-footer").html(`
		<button type="button" class="btn btn-info" style="font-size:12px;" lang="en" onclick="previewTemplate();">Preview</button> 
		<button type="button" class="btn btn-orange" style="font-size:12px;" lang="en" onclick="saveTemplate();">Save</button> 
		<button type="button" class="btn btn-white" style="font-size:12px;" lang="en" onclick="closeTemplate()">Cancel</button> 
	`);
	$("#template").editable({
		inlineMode: false,
		buttons: ['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'fontFamily', 'fontSize', 'color', 'formatBlock', 'blockStyle', 'inlineStyle', 'align', 'insertOrderedList', 'insertUnorderedList', 'outdent', 'indent', 'selectAll', 'createLink', 'insertImage', 'table', 'undo', 'redo', 'insertHorizontalRule', 'uploadFile', 'fullscreen', 'html'],
		minHeight: 700,
		imageUploadURL: 'upload_image.php',
        imageDeleteURL: 'delete_image.php'
	});	
	$("a[href='http://editor.froala.com']").parent().remove();
	$.ajax({
		url: "/classroom/management/actions/email.php",
		type: "POST",
		data: {
			action: 'dataTemplate',
			template_id: template_id
		},
		dataType: "JSON",
		type: 'POST',
		success: function(result) {
            handleResponse(result);
			var template_data = result.template_data;
			var mail_subject = template_data.mail_subject;
			var mail_description = template_data.mail_description;
			var mail_reference = template_data.mail_reference;
			$("#template_subject").val(mail_subject);
			if(mail_description) {
				$('#template').editable("setHTML", mail_description, true);
			}
			if(mail_reference != 0) {
				$(".restore-template").removeClass("hidden");
			}
		}
	});
}
function copyKey(rows) {
    new ClipboardJS('.copy-'+rows);
    var val = $('.copy-'+rows).attr('data-clipboard-text');
    $(".copy-"+rows+" .notofication-share").addClass("active");
    setTimeout(function() { 
        $(".copy-"+rows+" .notofication-share").removeClass("active");
    }, 1500);
}
function previewTemplate() {
	var template = $("#template").val();
	$.ajax({
		url: "/classroom/management/actions/email.php",
		type: "POST",
		data: {
			action: 'previewTemplate',
			classroom_id: classroom_id,
			template: template
		},
		dataType: "JSON",
		type: 'POST',
		success: function(result){
            handleResponse(result);
			var template = result.template;
			$(".systemModal").modal();
			$(".systemModal .modal-body").html(template);
			$(".systemModal .modal-header").html(`
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>    
            `);
			$(".systemModal .modal-footer").html(`
                <button type="button" class="btn btn-white" style="font-size:12px;" lang="en" data-dismiss="modal">Close</button> 
            `);
		}
	});
}
function previewTemplated(template_id) {
	$.ajax({
		url: "/classroom/management/actions/email.php",
		type: "POST",
		data: {
			action: 'previewTemplated',
			template_id: template_id,
			classroom_id: classroom_id
		},
		dataType: "JSON",
		type: 'POST',
		success: function(result){
            handleResponse(result);
			var template = result.template;
			$(".systemModal").modal();
			$(".systemModal .modal-body").html(template);
			$(".systemModal .modal-header").html(`
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>    
            `);
			$(".systemModal .modal-footer").html(`
                <button type="button" class="btn btn-white" style="font-size:12px;" lang="en" data-dismiss="modal">Close</button> 
            `);
		}
	});
}
function closeTemplate() {
	event.stopPropagation();
    swal({
        html:true,
        title: window.lang.translate("Are you sure?"),
        text: 'Do you want to cancel this action?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate("Ok"),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FBC02D',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
			$(".modal-template").modal("hide");
			$(".modal-preview").modal("hide");
			swal.close();
		}
	});
}
function saveTemplate() {
    var template_id = $("#template_id").val().trim();
    var template = $("#template").val().trim();
    var template_subject = $("#template_subject").val().trim();
    var err = 0;
    $(".require_obj").each(function () {
        if (!$(this).val().trim()) {
            err++;
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });
    if (err > 0) {
        swal({
            type: 'warning',
            title: "Warning...",
            text: 'Please fill in all required fields.',
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }
    $(".loader").addClass("active");
    $.ajax({
        url: "/classroom/management/actions/email.php",
        type: "POST",
        dataType: "JSON",
        data: {
            action: 'saveTemplate',
            classroom_id: classroom_id,
            template_id: template_id,
            template: template,
            template_subject: template_subject
        },
        success: function (result) {
            $(".loader").removeClass("active");
            handleResponse(result);
            if (result.status) {
                swal({
                    type: 'success',
                    title: "Saved successfully",
                    text: "",
                    showConfirmButton: false,
                    timer: 1500
                });
                buildEmail();
                $(".modal-template").modal("hide");
            } else {
                swal({
                    type: 'error',
                    title: "Error",
                    text: result.message || "Unable to save template.",
                    showConfirmButton: true
                });
            }
        },
        error: function (xhr, status, error) {
            $(".loader").removeClass("active");
            swal({
                type: 'error',
                title: "Ajax Error",
                text: error,
                showConfirmButton: true
            });
        }
    });
}
function delTemplate(template_id) {
	event.stopPropagation();
    swal({
        html: true,
        title: window.lang.translate("Are you sure?"),
        text: 'Are you sure to do this?',
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        confirmButtonText: window.lang.translate('Yes'),
        cancelButtonText: window.lang.translate("Cancel"),	
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#CCCCCC',
        showLoaderOnConfirm: true,
    },
    function(isConfirm){
        if (isConfirm) {
            $.ajax({
                url: "/classroom/management/actions/email.php",
                type: "POST",
                data: {
                    action: 'delTemplate',
                    template_id: template_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    handleResponse(result);
                    if(result.status === true){	
						swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});
						buildEmail();
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