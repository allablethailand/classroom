function buildConsentPage() {
    $(".content-container").html(getConsentTemplate());
    initializeConsentEditor();
    buildConsentData();
}
function getConsentTemplate() {
    return `
        <form id="consent_form">
            <textarea name="classroom_consent" id="classroom_consent" class="form-control"></textarea>
        </div>
        <br>
        <div class="text-right">
            <button type="button" class="btn btn-orange btn-save" lang="en" onclick="saveConsent();">Save</button> 
        </div>
    `;
}
function initializeConsentEditor() {
    try {
        const editorElement = $('#classroom_consent');
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
    if (!consentText) {
        ++err;
    }
	if(err > 0) {
		swal({
			type: 'warning',
			title: "Warning",
			text: "Please input all item completely.",
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
			swal({type: 'success', title: "Successfully", text: "", showConfirmButton: false, timer: 1500});
			buildConsentData();
		}
	});
}
function buildConsentData() {
    if (!classroom_id) return;
    $.ajax({
        url: "/classroom/management/actions/consent.php",
        type: "POST",
        data: { 
            action: 'buildConsentData', 
            classroom_id: classroom_id 
        },
        dataType: "JSON",
        success: function(result) {
            if (result && result.classroom_consent) {
                $('#classroom_consent').editable("setHTML", result.classroom_consent, true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading management data:', error);
        }
    });
}