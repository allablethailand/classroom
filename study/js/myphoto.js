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