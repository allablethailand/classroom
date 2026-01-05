var student_id ;

$(document).ready(function() {
    $('#btn-send-profile').on('click', function(e) {
        e.preventDefault();
        student_id = $('#student_id').val();

        $.ajax({
            url: "/classroom/study/actions/myphoto.php",
            data: {
                action: "fetchToAI",
                student_id: student_id
            },
            dataType: "JSON",
            type: "POST",
            success: function (result) {
            console.log(result);
                if (Array.isArray(result)) {
                    renderCourses(result);
                }
            },
            error: function (xhr, status, error) {
                console.error("Failed to load courses:", error);
            },
        });
  });

//   DOWNLOAD FN
//  function downloadFileAsBlob(fileUrl, fileName = 'downloaded-file.png') {
// 		// console.log('fileUrl', fileUrl);
// 		const proxyUrl = '<?php echo $base_path; ?>/lib/proxy.php?url=' + encodeURIComponent(fileUrl);
		
// 		fetch(proxyUrl)
// 		.then(res => {
// 			if (!res.ok) throw new Error('Network response was not ok');
// 			return res.blob();
// 		})
// 		.then(blob => {
// 			const blobUrl = URL.createObjectURL(blob);
// 			const a = document.createElement('a');
// 			a.href = blobUrl;
// 			a.download = fileName;
// 			document.body.appendChild(a);
// 			a.click();
// 			document.body.removeChild(a);
// 			URL.revokeObjectURL(blobUrl);
// 			console.log('✅ Download successful.');
// 		})
// 		.catch(err => {
// 			console.error('❌ Download failed:', err);
// 			alert('Download failed.');
// 		});
// 	}


  $('#btn-new-img').on('click', function(e) {
        e.preventDefault();
        
        const fileInput = $('#photo-ref-send')[0];
        if (fileInput.files.length === 0) {
            alert('Please select an image first.');
            return;
        }

        const formData = new FormData();
        student_id = $('#student_id').val();
        formData.append('image', fileInput.files[0]);
        formData.append('action', 'saveToProfile');
        formData.append('student_id', student_id);


        $.ajax({
            url: "/classroom/study/actions/myphoto.php",
            data: formData,
            dataType: "JSON",
            type: "POST",
            processData: false,
            contentType: false,
            success: function (result) {
            console.log(result);
                if (Array.isArray(result)) {
                    renderCourses(result);
                }
            },
            error: function (xhr, status, error) {
                console.error("Failed to load courses:", error);
            },
        });
  });
});

function displayResults(matchedPhotos, similarities, matchesData) {
    const container = $(".avatar-container");
    container.html("");
    container.append('<p class="text-center"><img class="logo" src="/images/origami_event.png"></p>');
    const row = $('<div class="gallery-grid"></div>');
    matchedPhotos.forEach((path, index) => {
        const match = matchesData[index];
        const bbox = match.bbox;
        const thumbnailPath = addThumbnailSuffix(path);

        const col = $(`
            <div class="gallery-item">
                <div class="thumbnail face-item" style="display:flex; flex-direction:column;">
                    <div class="img-containers" style=" overflow:hidden; position:relative;">
                        <img src="${thumbnailPath}" style="width:100%; height:100%; object-fit:contain; border-radius:8px;">
                        <div class="image-overlay">
                            <button class="overlay-btn preview-btn btn-view" data-src="${path}">Preview</button>
                            <a class="overlay-btn download-btn" href="${path}" download>Download</a>
                        </div>
                    </div>
                </div>
            </div>
        `);
        row.append(col);
    });
    container.append($('<div class="gallery-container"></div>').append(row));
    container.append('<div class="text-center" style="margin-top:15px;"><button class="btn btn-primary upload-again">Find Again</button></div>');   
    const ImageModalHTML = $('<div id="imageModal" class="modal"><span class="close-modal">&times;</span><div class="modal-content"><img class="modal-image" id="modalImg" src="" alt="Preview"></div></div>');

    $('body').append(ImageModalHTML);

    // PREVIEW MODAL
    $(".btn-view").click(function(){
        const src = $(this).data("src");
        $("#modalImg").attr("src", src);
        $("#imageModal").addClass("active");  // Use addClass to match removeClass
    });

    // CLOSE MODAL
    $(document).on('click', '.close-modal', function(e) {
        e.preventDefault();  // Prevent default navigation
        $("#imageModal").removeClass("active");
    });

    // DOWNLOAD IMAGE
    $(document).on('click', '.download-btn', function(e) {
        e.preventDefault();  // Prevent default navigation
        const src = $(this).attr('href');  // Use href directly
        const filename = src.split('/').pop();
        
        // Create temporary link to trigger download
        const $tempLink = $('<a>', {
            href: src,
            download: filename,
            css: { display: 'none' }
        });
        
        $('body').append($tempLink);
        $tempLink[0].click();
        $tempLink.remove();
    });

    $(".upload-again").click(()=>{
        container.html(avatar_container);
    });
}


