function buildConsentPage() {
    $(".content-container").html(getConsentTemplate());
    buildConsent();
}
function getConsentTemplate() {
    return `
        <table class="table table-border" id="tb_consent">
            <thead>
                <tr>
                    <th lang="en">Publish</th>
                    <th lang="en">Consent (ภาษาไทย)</th>
                    <th lang="en">Consent (English)</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
let tb_consent;
function buildConsent() {
    if ($.fn.DataTable.isDataTable('#tb_consent')) {
        $('#tb_consent').DataTable().ajax.reload(null, false);
    } else {
		tb_consent = $('#tb_consent').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/consent.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildConsent";
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
			"order": [[0,'asc']],
			"columns": [{ 
                "targets": 0,
                "data": "consent_use",
                "className": "text-center",
                "orderable": false,
                "render": function (data,type,row,meta) {	
                    let consent_id = row['consent_id'];
					return `
                        <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchConsent(${consent_id}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>
                    `;
                }
            },{ 
                "targets": 1,
                "data": "consent_body",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="consent-example">${data}</div>
                    `;
                }
            },{ 
                "targets": 2,
                "data": "consent_body_en",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="consent-example">${data}</div>
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
                "data": "consent_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-orange btn-circle" onclick="manageConsent(${data})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delConsent(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            }]
        });
        $('div#tb_consent_filter.dataTables_filter label input').remove();
        $('div#tb_consent_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="manageConsent('')"><i class="fas fa-plus"></i> <span lang="en">Consent</span></button>
        `;
        $('div#tb_consent_filter.dataTables_filter input').hide();
        $('div#tb_consent_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_consent.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_consent.search('').draw();
                buildConsent();
            }
        });
    }
}
function switchConsent(consent_id,option) {
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
		title: window.lang.translate(`${topic} Consent?`),
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
                url: "/classroom/management/actions/consent.php",
                type: "POST",
                data: {
                    action:'switchConsent',
                    classroom_id: classroom_id,
                    consent_id: consent_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildConsent();
                }
            });
		}else{
			swal.close();
		}
	});
}
function delConsent(consent_id) {
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
                url: "/classroom/management/actions/consent.php",
                type: "POST",
                data: {
                    action:'delConsent',
                    consent_id: consent_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildConsent();
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
function manageConsent(consent_id = '') {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Consent Management</h5>    
    `);
     $(".systemModal .modal-body").html(`
        <form id="consent_form">
            <input type="hidden" name="consent_id" value="${consent_id}">
            <p style="margin: 10px auto;"><b>ภาษาไทย</b></p>
            <textarea name="classroom_consent" id="classroom_consent" class="form-control"></textarea>
            <p style="margin: 10px auto;"><b>English</b></p>
            <textarea name="classroom_consent_en" id="classroom_consent_en" class="form-control"></textarea>
        </div>
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="saveConsent()">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    initializeConsentEditor();
    if(consent_id) {
        $.ajax({
            url: "/classroom/management/actions/consent.php",
            type: "POST",
            data: { 
                action: 'buildConsentData', 
                consent_id: consent_id 
            },
            dataType: "JSON",
            success: function(result) {
                if (result && result.consent_body) {
                    $('#classroom_consent').editable("setHTML", result.consent_body, true);
                    $('#classroom_consent_en').editable("setHTML", result.consent_body_en, true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading management data:', error);
            }
        });
    }
}
function initializeConsentEditor() {
    try {
        const editorElement = $('#classroom_consent, #classroom_consent_en');
        if (editorElement.length && typeof editorElement.editable === 'function') {
            editorElement.editable({
                theme: 'gray',
                inlineMode: false,
                buttons: [
                    'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript',
                    'fontFamily', 'fontSize', 'color', 'formatBlock', 'blockStyle', 'inlineStyle',
                    'align', 'insertOrderedList', 'insertUnorderedList', 'outdent', 'indent',
                    'selectAll', 'createLink', 'table', 'undo', 'redo',
                    'insertHorizontalRule', 'fullscreen', 'html'
                ],
                minHeight: 450,
            });
            $("a[href='http://editor.froala.com']").parent().remove();
        }
    } catch (error) {
        console.error('Error initializing editor:', error);
    }
}
function saveConsent() {
    var err = 0;
    const consentText = $("#classroom_consent").val().trim();
    const consentTextEn = $("#classroom_consent_en").val().trim();
    if (!consentText && !consentTextEn) {
        ++err;
    }
	if(err > 0) {
		swal({
			type: 'warning',
			title: "Warning",
			text: "Please input one item completely.",
			timer: 2500,
			showConfirmButton: false,
			allowOutsideClick: true
		});
		return;
	}
    $(".loader").addClass("active");
    var fd = new FormData();
    var fd = new FormData(document.getElementById("consent_form"));
        fd.append('classroom_id', classroom_id);
    $.ajax({
		url: "/classroom/management/actions/consent.php?action=saveConsent",
		type: "POST",
		data: fd,
		processData: false,
		contentType: false,
		dataType: "JSON",
		success: function(result){
			$(".loader").removeClass("active");
            if(result.status == false) {
                swal({type: 'warning', title: "Something went wrong", text: (result.message) ? result.message : "Please try again later", showConfirmButton: false, timer: 1500});
                return;
            }
            $(".systemModal").modal("hide");
			swal({type: 'success', title: "Successfully", text: "", showConfirmButton: false, timer: 1500});
			buildConsent();
		}
	});
}