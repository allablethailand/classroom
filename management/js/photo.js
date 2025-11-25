// js/photo.js ที่แก้ไขแล้ว
function buildPhotoPage() {
    // **✅ ดึง classroom_id จาก Hidden Field ในหน้าแม่**
    var classroomId = $('#classroom_id').val(); 
    var contentUrl = '/classroom/management/actions/photo.php';
    
    $.ajax({
        url: contentUrl,
        type: 'POST', // **เปลี่ยนเป็น POST เพื่อส่ง classroom_id ได้ง่าย**
        data: { classroom_id: classroomId }, // **✅ ส่ง classroom_id ไปด้วย**
        dataType: 'html',
        beforeSend: function() {
            $(".content-container").html('<div class="text-center" style="padding: 50px;"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading...</div>');
        }
    })
    .done(function(data) {
        $(".content-container").html(data);
        
        // **✅ ตั้งค่า Hidden Field ในฟอร์มย่อย (photo.php) ให้ถูกต้องอีกครั้ง**
        $('#form_classroom_id').val(classroomId); 
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