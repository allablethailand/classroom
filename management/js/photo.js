function buildPhotoPage() {
    var contentUrl = '/classroom/management/actions/photo.php';
    $.ajax({
        url: contentUrl,
        type: 'GET',
        dataType: 'html',
        beforeSend: function() {
            $(".content-container").html('<div class="text-center" style="padding: 50px;"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading...</div>');
        }
    })
    .done(function(data) {
        $(".content-container").html(data);
        
    
    })
    .fail(function() {
        $(".content-container").html('<div class="alert alert-danger">ไม่สามารถโหลดเนื้อหา **เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition** ได้</div>');
    });
}

$(document).ready(function() {
    $('a.get-management[data-page="photo"]').on('click', function(e) {
        e.preventDefault(); 
        buildPhotoPage();
        
       
    });
});

