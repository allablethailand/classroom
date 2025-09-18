function buildPaymentPage() {
    $(".content-container").html(getPaymentTemplate());
    buildPayment();
}
function getPaymentTemplate() {
    return `
        <table class="table table-border" id="tb_payment">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th lang="en">Payment</th>
                    <th lang="en">Method</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
let tb_payment;
function buildPayment() {
    if ($.fn.DataTable.isDataTable('#tb_payment')) {
        $('#tb_payment').DataTable().ajax.reload(null, false);
    } else {
        tb_payment = $('#tb_payment').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
            "ajax": {
                "url": "/classroom/setup/actions/payment.php",
                "type": "POST",
                "data": function (data) {
                    data.action = "buildPayment";
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
                    let id = row['id'];
					return `
                        <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchPayment(${id}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>
                    `;
                }
            },{ 
                "targets": 1,
                "data": "method_cover",
                "render": function (data, type, row, meta) {
                    return `
                        <img src="${data}" style="width: 100px; border-radius: 5px; border: 3px solid #FFFFFF; box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.16);" onerror="this.src='/images/noimage.jpg'">
                    `;
                }
            },{ 
                "targets": 2,
                "data": "method_name",
                "render": function (data, type, row, meta) {
                    let method_type = row["method_type"];
                    let method_html = ``;
                    switch(method_type) {
                        case 'bank':
                            method_html = `<span class="label label-warning">Bank Transfer</span>`;
                            break;
                        case 'promptpay':
                            method_html = `<span class="label label-info">PromptPay</span>`;
                            break;
                        case 'gateway':
                            method_html = `<span class="label label-danger">Payment Gateway</span>`;
                            break;
                    }
                    return `
                        <p><b>${data}</b></p>
                        ${method_html}
                    `;
                }
            },{ 
                "targets": 3,
                "render": function (data, type, row, meta) {
                    let method_type = row["method_type"];
                    let account_name = row['account_name'] || '';
                    let account_number = row['account_number'] || '';
                    let bank_code = row['bank_code'] || '';
                    let bank_name = row['bank_name'] || '';
                    let api_url = row['api_url'] || '';
                    let api_key = row['api_key'] || '';
                    let api_secret = row['api_secret'] || '';
                    let html = '';
                    switch (method_type) {
                        case 'promptpay':
                            html = `
                                <div>
                                    <b>PromptPay:</b> ${account_number}<br>
                                    <b>Account Number:</b> ${account_name}
                                </div>
                            `;
                            break;
                        case 'bank':
                            html = `
                                <div>
                                    <b>${bank_name}</b><br>
                                    <b>Account Number:</b> ${account_number}<br>
                                    <b>Account Name:</b> ${account_name}
                                </div>
                            `;
                            break;

                        case 'gateway':
                            html = `
                                <div>
                                    <b>Gateway API</b><br>
                                    <b>URL:</b> ${api_url}<br>
                                    <b>Key:</b> ${api_key ? api_key.substr(0,6)+'***' : ''}
                                </div>
                            `;
                            break;
                        default:
                            html = `<span class="text-muted">Not Data</span>`;
                    }
                    return html;
                }
            },{ 
                "targets": 4,
                "data": "date_create"
            },{ 
                "targets": 5,
                "data": "emp_create"
            },{ 
                "targets": 6,
                "data": "id",
                "render": function (data, type, row, meta) {
                    return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-orange btn-circle" onclick="managePayment(${data})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delPayment(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            }]
        });
        $('div#tb_payment_filter.dataTables_filter label input').remove();
        $('div#tb_payment_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="managePayment('')"><i class="fas fa-plus"></i> <span lang="en">Payment</span></button>
        `;
        $('div#tb_payment_filter.dataTables_filter input').hide();
        $('div#tb_payment_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_payment.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_payment.search('').draw();
                buildPayment();
            }
        });
    }
}
function managePayment(id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Payment Management</h5>    
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="savePayment();">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $(".systemModal .modal-body").html(`
        <form id="payment_form">
            <input type="hidden" id="id" name="id" value="${id}">
            <p class="text-center" lang="en">Payment Logo</p>
            <div id="AvatarFileUpload" class="selected-image-holder">
                <img src="/images/noimage.jpg" class="image-profile" alt="AvatarInput" onerror="this.src='/images/noimage.jpg'">
                <div class="avatar-selector">
                    <a href="#" class="avatar-selector-btn"><i class="fas fa-camera-retro"></i></a>
                    <input type="file" accept="image/*" name="method_cover" id="method_cover" autocomplete="off">
                </div>
            </div>
            <p class="text-center text-orange" style="margin-top:15px;">
                <i class="fas fa-lightbulb"></i> <span lang="en">Only image files are allowed. Please upload a file like .jpg, .png, or .gif.</span>
            </p>
            <p><span lang="en">Payment Name</span> <code>*</code></p>
            <input type="text" id="method_name" name="method_name" class="form-control require_obj" autocomplete="off">
            <p><span lang="en">Payment Type</span> <code>*</code></p>
            <div class="checkbox checkbox-warning">
                <input class="method_type styled" id="bank" name="method_type" type="radio" value="bank" checked>
                <label for="bank"><span lang="en">Bank Transfer</span></label>
            </div>
            <div class="checkbox checkbox-warning">
                <input class="method_type styled" id="promptpay" name="method_type" type="radio" value="promptpay">
                <label for="promptpay"><span lang="en">PromptPay</span></label>
            </div>
            <div class="checkbox checkbox-warning">
                <input class="method_type styled" id="gateway" name="method_type" type="radio" value="gateway">
                <label for="gateway"><span lang="en">Payment Gateway</span></label>
            </div>
            <div class="method for-bank">
                <p><span lang="en">Bank</span> <code>*</code></p>
                <select class="form-control" id="bank_id" name="bank_id"></select>
                <p><span lang="en">Account No</span> <code>*</code></p>
                <input type="text" id="account_number" name="account_number" class="form-control require_obj" autocomplete="off">
                <p><span lang="en">Account Name</span> <code>*</code></p>
                <input type="text" id="account_name" name="account_name" class="form-control require_obj" autocomplete="off">
            </div>
            <div class="method for-promptpay hidden">
                <p><span lang="en">Promptpay Number</span> <code>*</code></p>
                <input type="text" id="account_number" name="account_number" class="form-control require_obj" autocomplete="off">
                <p><span lang="en">Account Name</span> <code>*</code></p>
                <input type="text" id="account_name" name="account_name" class="form-control require_obj" autocomplete="off">
            </div>
            <div class="method for-gateway hidden">
                <p><span lang="en">URL</span> <code>*</code></p>
                <input type="text" id="api_url" name="api_url" class="form-control require_obj" autocomplete="off">
                <p><span lang="en">Client Key</span> <code>*</code></p>
                <input type="text" id="api_key" name="api_key" class="form-control require_obj" autocomplete="off">
                <p><span lang="en">Secret Key</span> <code>*</code></p>
                <input type="text" id="api_secret" name="api_secret" class="form-control require_obj" autocomplete="off">
            </div>
        </form>
    `);
    $(".method_type").click(function() {
        $(".method").addClass("hidden");
        $(".for-"+$(this).val()).removeClass("hidden");
    });
    buildBank();
    initAvatarUpload();
    if(id) {
        $.ajax({
            url: '/classroom/setup/actions/payment.php',
            type: "POST",
            data: {
                action: 'paymentData',
                id: id
            },
            dataType: "JSON",
            success: function (result) {
                if (result.status) {
                    const data = result.payment_data;
                    $('#method_name').val(data.method_name);
                    if (data.method_cover) {
                        $('#AvatarFileUpload img').attr('src', data.method_cover);
                    }
                    $("#"+data.method_type).prop("checked", true);
                    $(".method").addClass("hidden");
                    $(".for-"+data.method_type).removeClass("hidden");
                    $('#account_name').val(data.account_name);
                    $('#account_number').val(data.account_number);
                    if(data.bank_name) {
                        $('#bank_id').append($('<option>', { 
                            value: data.bank_id, 
                            text: data.bank_name 
                        }));
                    }
                    $('#api_url').val(data.api_url);
                    $('#api_key').val(data.api_key);
                    $('#api_secret').val(data.api_secret);
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
function savePayment() {
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
    $(".method.hidden :input").prop("disabled", true);
    $(".method:not(.hidden) :input").prop("disabled", false);
    const form = document.getElementById("payment_form");
    const fd = new FormData(form);
    $.ajax({
        url: "/classroom/setup/actions/payment.php?action=savePayment",
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
                buildPayment();
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
function initAvatarUpload() {
    const avatarUpload = document.getElementById('AvatarFileUpload');
    if (!avatarUpload) return;
    const imageViewer = avatarUpload.querySelector('.selected-image-holder>img');
    const imageSelector = avatarUpload.querySelector('.avatar-selector-btn');
    const imageInput = avatarUpload.querySelector('input[name="method_cover"]');
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
function delPayment(id) {
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
                url: "/classroom/setup/actions/payment.php",
                type: "POST",
                data: {
                    action:'delPayment',
                    id: id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildPayment();
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
function switchPayment(id,option) {
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
		title: window.lang.translate(`${topic} Payment?`),
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
                url: "/classroom/setup/actions/payment.php",
                type: "POST",
                data: {
                    action:'switchPayment',
                    id: id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildPayment();
                }
            });
		}else{
			swal.close();
		}
	});
}
function buildBank() {
    try {
        $("#bank_id").select2({
            theme: "bootstrap",
            placeholder: "Choose bank",
            minimumInputLength: -1,
            allowClear: true,
            ajax: {
                url: "/classroom/setup/actions/payment.php",
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                        action: 'buildBank'
                    };
                },
                processResults: function(data, params) {
                    const page = params.page || 1;
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.col,
                                code: item.code,
                                desc: item.desc,
                            };
                        }),
                        pagination: {
                            more: (page * 10) <= (data[0] ? data[0].total_count : 0)
                        }
                    };
                },
            },
            templateSelection: function(data) {
                return data.text;
            },
        });
    } catch (error) {
        console.error('Error building department dropdown:', error);
    }
}