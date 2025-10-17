function buildPricingPage() {
    $(".content-container").html(getPricingTemplate());
    buildPricing();
}
function getPricingTemplate() {
    return `
        <table class="table table-border" id="tb_pricing">
            <thead>
                <tr>
                    <th lang="en">Publish</th>
                    <th lang="en">Default Price</th>
                    <th lang="en">Type</th>
                    <th lang="en">Price</th>
                    <th lang="en">Quantity</th>
                    <th lang="en">Start</th>
                    <th lang="en">End</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;
}
let tb_pricing;
function buildPricing() {
    if ($.fn.DataTable.isDataTable('#tb_pricing')) {
        $('#tb_pricing').DataTable().ajax.reload(null, false);
    } else {
		tb_pricing = $('#tb_pricing').DataTable({
            "processing": true,
        	"serverSide": true,
			"lengthMenu": [[50,100, 150,200,250,300, -1], [50,100, 150,200,250,300, "All"]],
			"ajax": {
				"url": "/classroom/management/actions/pricing.php",
				"type": "POST",
				"data": function (data) {
                    data.action = "buildPricing";
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
			"order": [[0,'asc'], [1,'asc'], [2,'desc'],  [3,'asc']],
			"columns": [{ 
                "targets": 0,
                "data": "is_public",
                "render": function (data,type,row,meta) {	
					let ticket_id = row['ticket_id'];
					return `
                        <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchPrice(${ticket_id}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>
                    `;
                }
            },{ 
                "targets": 1,
                "data": "ticket_default",
                "render": function (data,type,row,meta) {	
					let ticket_id = row['ticket_id'];
					let is_public = row['is_public'];
					return `
                        ${(is_public == 0) ? `
                            <a class="text-${(data == 0) ? 'green' : 'grey'}" onclick="switchDefault(${ticket_id}, ${data});"><i class="fas fa-toggle-${(data == 0) ? 'on' : 'off'} fa-2x"></i></a>    
                        ` : ``}
                    `;
                }
            },{ 
                "targets": 2,
                "data": "ticket_type",
                "render": function (data,type,row,meta) {	
                    let description = row['description'];
					switch(data) {
                        case 'normal':
                            method_html = `<span class="label label-warning">Normal Price</span>`;
                            break;
                        case 'early':
                            method_html = `<span class="label label-danger">Early Bird</span>`;
                            break;
                    }
                    return `
                        ${method_html}
                        ${(description) ? `<p style="margin-top: 10px;"><small>${description}</small></p>` : ``}
                    `;
                }
            },{ 
                "targets": 3,
                "data": "ticket_price",
                "className": "text-right"
            },{ 
                "targets": 4,
                "data": "ticket_quota",
                "className": "text-right"
            },{ 
                "targets": 5,
                "data": "start_sale"
            },{ 
                "targets": 6,
                "data": "end_sale"
            },{ 
                "targets": 7,
                "data": "ticket_id",
                "className": "text-center",
                "render": function (data,type,row,meta) {	
					return `
                        <div class="nowarp">
                            <button type="button" class="btn btn-orange btn-circle" onclick="managePrice(${data})"><i class="fas fa-pencil-alt"></i></button> 
                            <button type="button" class="btn btn-red btn-circle" onclick="delPrice(${data})"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                }
            }]
        });
        $('div#tb_pricing_filter.dataTables_filter label input').remove();
        $('div#tb_pricing_filter.dataTables_filter label span').remove();
        var template = `
            <input type="search" class="form-control input-sm search-datatable" placeholder="" autocomplete="off" style="margin-bottom:0px !important;"> 
            <button type="button" class="btn btn-green" style="font-size:12px;" onclick="managePrice('')"><i class="fas fa-plus"></i> <span lang="en">Price</span></button>
        `;
        $('div#tb_pricing_filter.dataTables_filter input').hide();
        $('div#tb_pricing_filter.dataTables_filter label').append(template);
        var searchDataTable = $.fn.dataTable.util.throttle(function (val) {
            if(typeof val != 'undefined') {
                tb_pricing.search(val).draw();	
            } 
        },1000);
        $('.search-datatable').on('keyup',function(e) {
            if(e.keyCode === 13) {
                $('.dataTables_processing.panel').css('top','5%');
                val = e.target.value.trim().replace(/ /g, "");
                searchDataTable(val);
            }
            if(e.target.value == '') {
                tb_pricing.search('').draw();
                buildPricing();
            }
        });
    }
}
function switchPrice(ticket_id,option) {
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
		title: window.lang.translate(`${topic} Pricing?`),
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
                url: "/classroom/management/actions/pricing.php",
                type: "POST",
                data: {
                    action:'switchPrice',
                    classroom_id: classroom_id,
                    ticket_id: ticket_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildPricing();
                }
            });
		}else{
			swal.close();
		}
	});
}
function switchDefault(ticket_id,option) {
    if(option == 0){
		var message = 'Remove from Default';
		var topic = 'Yes';
		var type_color = 'error';
		var button_color = '#FF6666';
	}else{
		var message = 'Set to Default';
		var topic = 'Yes';
		var type_color = 'info';
		var button_color = '#5bc0de';
	}
	event.stopPropagation();
	swal({ 
		html:true,
		title: window.lang.translate(`${topic} Pricing?`),
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
                url: "/classroom/management/actions/pricing.php",
                type: "POST",
                data: {
                    action:'switchDefault',
                    classroom_id: classroom_id,
                    ticket_id: ticket_id,
                    option: option
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                    buildPricing();
                }
            });
		}else{
			swal.close();
		}
	});
}
function delPrice(ticket_id) {
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
                url: "/classroom/management/actions/pricing.php",
                type: "POST",
                data: {
                    action:'delPrice',
                    ticket_id: ticket_id
                },
                dataType: "JSON",
                type: 'POST',
                success: function(result){
                    if(result.status === true){			
                        swal({type: 'success',title: "Successfully",text: "", showConfirmButton: false,timer: 1500});							
                        buildPricing();
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
function managePrice(ticket_id) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title" lang="en">Pricing Management</h5>    
    `);
    $(".systemModal .modal-body").html(`
        <form id="ticket_form">
            <input type="hidden" name="ticket_id" value="${ticket_id}">
            <p><span lang="en">Price Type</span> <code>*</code></p>
            <div class="checkbox checkbox-warning">
                <input class="ticket_type styled" id="normal" name="ticket_type" type="radio" value="normal" checked>
                <label for="normal"><span lang="en">Normal Price</span></label>
            </div>
            <div class="checkbox checkbox-warning">
                <input class="ticket_type styled" id="early" name="ticket_type" type="radio" value="early">
                <label for="early"><span lang="en">Early Bird</span></label>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <p><span lang="en">Pricing</span> <code>*</code></p>
                    <input type="text" id="ticket_price" name="ticket_price" class="form-control require_obj" autocomplete="off" style="text-align:right;" onkeypress="return isNumberKeyEvent(event);" onClick="this.select();">
                </div>
                <div class="col-sm-6">
                    <p><span lang="en">Quantity</span> <code>*</code></p>
                    <input type="text" id="ticket_quota" name="ticket_quota" class="form-control require_obj" autocomplete="off" style="text-align:right;" onkeypress="return isNumberKeyEvent(event);" onClick="this.select();">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <p><span lang="en">Start Sale</span> <code>*</code></p>
                    <input type="text" id="start_sale" name="start_sale" class="form-control datepicker require_obj" autocomplete="off">
                </div>
                <div class="col-sm-6">
                    <p><span lang="en">End Sale</span> <code>*</code></p>
                    <input type="text" id="end_sale" name="end_sale" class="form-control datepicker require_obj" autocomplete="off">
                </div>
            </div>
            <p><span lang="en">Description</span></p>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
    `);
    $(".systemModal .modal-footer").html(`
        <button type="button" class="btn btn-orange" lang="en" onclick="savePrice()">Save</button> 
        <button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Close</button>
    `);
    $('.datepicker').datepicker({
        dateFormat: 'yy/mm/dd', 
        changeMonth: true,
        changeYear: true,
        autoclose: true
    });
    $("#start_sale, #end_sale").on("change", function(){
        let start = $("#start_sale").val();
        let end = $("#end_sale").val();
        if(start && end){
            let startDate = new Date(start);
            let endDate = new Date(end);
            if(startDate > endDate){
                $("#start_sale").val(end);
            }
        }
    });
    if(ticket_id) {
        $.ajax({
            url: '/classroom/management/actions/pricing.php',
            type: "POST",
            data: {
                action: 'priceData',
                ticket_id: ticket_id
            },
            dataType: "JSON",
            success: function (result) {
                if (result.status) {
                    const data = result.price_data;
                    setDatePickerValue("#start_sale", data.start_sale);
                    setDatePickerValue("#end_sale", data.end_sale);
                    $("#ticket_price").val(data.ticket_price);
                    $("#ticket_quota").val(data.ticket_quota);
                    $("#description").val(data.description);
                    $("#"+data.ticket_type).prop("checked", true);
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
function savePrice() {
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
    const form = document.getElementById("ticket_form");
	const fd = new FormData(form);
    fd.append('classroom_id', classroom_id);
    $.ajax({
		url: "/classroom/management/actions/pricing.php?action=savePrice",
		type: "POST",
		data: fd,
		processData: false,
		contentType: false,
		dataType: "JSON",
		success: function(result) {
			$(".loader").removeClass("active");
			if(result.status === true) {
				swal({type: 'success', title: "Successfully", text: "", showConfirmButton: false, timer: 2500});
                buildPricing();
				$(".systemModal").modal("hide");
			} else {
                swal({type: 'error',title: "Sorry...",text: "Something went wrong! Please try again later.",timer: 2500});
            }
        }
    });
}