let classroom_group;
let management_template;
let join_template;
let tb_staff;
let table_join_user;

$(document).ready(function() {
    // initializeClassroomManagement();
    $('#toggleStudent').click(function (e) {
        e.stopPropagation();
        var $btn = $(this);
        $.ajax({
            url: '/classroom/study/actions/group.php?action=toggle_student',
            type: "GET",
            success: function(data) {
               var $icon = $btn.find('[data-fa-i2svg]');
                if ($icon.attr('data-icon') === 'address-book') {
                    $icon.removeClass('fa-address-book').addClass('fa-th-large') // replace 'fa-other-icon' with your off icon class
                        .attr('data-icon', 'fa-th-large'); // without 'fa-' prefix
                } else {
                    $icon.removeClass('fa-th-large').addClass('fa-address-book')
                        .attr('data-icon', 'address-book');
                }

                var students = JSON.parse(data);
                var html = '<div class="student-list">';

                if (students.length > 0) {
                    students.forEach(function(row) {
                        // Set default image if empty
                        var student_pic = row.student_image_profile ? row.student_image_profile : '../../../images/default.png';
                        var group_logo = row.group_logo ? row.group_logo : '../../../images/default.png';
                        var border_color = row.group_color ? row.group_color : '#ff8c00';

                        

                        html += '<a href="studentinfo?id=' + encodeURIComponent(row.student_id) + '" class="student-card">';
                        html += '<div class="student-avatar" style="border-color:' + border_color + ';">';
                        html += '<img src="' + student_pic + '" alt="Student Avatar" ';
                        html += 'onerror="this.src=\'../../../images/default.png\'">';
                        html += '</div>';
                        html += '<div class="student-info">';
                        html += '<p class="student-id-display"><span style="margin-right:.5em;">ID:&nbsp;</span> ' + row.student_id + '</p>';
                        html += '<h4 class="student-name">&nbsp;' + (row.student_firstname_th + ' ' + row.student_lastname_th) + '</h4>';
                        html += '<p class="student-details highlight-text"><i class="fas fa-graduation-cap"></i> &nbsp;' + (row.classroom_name ? row.classroom_name : '-') + '</p>';
                        html += '<p class="student-details highlight-text group-name-container"><img src="' + group_logo + '" alt="Group Logo"> ' + (row.group_name ? row.group_name : '-') + '</p>';
                        html += '<p class="student-details"><i class="fas fa-building"></i> &nbsp;' + (row.student_company ? row.student_company : '-') + '</p>';
                        html += '<p class="student-details"><i class="fas fa-briefcase"></i> &nbsp;' + (row.student_position ? row.student_position : '-') + '</p>';
                        html += '<p class="student-details"><i class="fas fa-phone"></i> &nbsp;' + (row.student_mobile ? row.student_mobile : '-') + '</p>';
                        html += '<p class="student-details"><i class="fas fa-envelope"></i> &nbsp;' + (row.student_email ? row.student_email : '-') + '</p>';
                        html += '</div></a>';
                    });
                } else {
                    html += "<p style='text-align: center; color: #888;'>ไม่พบข้อมูลนักเรียนในกลุ่มนี้</p>";
                }

                html += '</div>';

                // Insert the generated HTML into the page
                $('#menu').html(html).toggle();
                $('#rowData').toggle();
            },
            error: function() {
                alert('Failed to load data.');
            }
        });
    });

});





// console.log(classroom_group);
// function view_group(group_id){
// 	event.stopPropagation();
//     console.log("GROUP 1")
// 	$.redirect("../views/student.php", {group_id_id: group_id, need_manage: 'view'}, 'post', '_self');
// }

