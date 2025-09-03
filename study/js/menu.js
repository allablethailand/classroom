$(document).ready(function(){
});


function view_group(class_id)
{
	$.ajax({
        url: "/classroom/study/actions/group.php",
        type: 'POST',
        data: {
            action:'view_group',
            class_gen_id: class_id
        },
        dataType: "JSON",
        success: function(result){
            $.redirect("group",{classroom_group: result}, 'post');
            // $.redirect("detail",{classroom_id: result.classroom_id}, 'post');
        //    console.log(result);
        },
        error: function(xhr, status, error) {
            console.error('Error loading management data:', error);
        }
    })


    console.log("HELLO!");
}