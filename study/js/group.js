$(document).ready(function(){

});

function view_group(group_id){
	event.stopPropagation();
    console.log("GROUP 1")
	$.redirect("../views/student.php", {group_id_id: group_id, need_manage: 'view'}, 'post', '_self');
}