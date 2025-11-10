let classroom_group;
let management_template;
let join_template;
let tb_staff;
let table_join_user;

$(document).ready(function() {
    // initializeClassroomManagement();
    $('#toggleViewList').click(function (e) {
        e.stopPropagation();
        var $btn = $(this);
        $.ajax({
            url: '/classroom/study/actions/group.php?action=toggleMember',
            type: "GET",
            dataType: "json",
            success: function(data) {

                var $icon = $btn.find('[data-fa-i2svg]');
                if ($icon.attr('data-icon') === 'address-book') {
                    $icon.removeClass('fa-address-book').addClass('fa-th-large') // replace 'fa-other-icon' with your off icon class
                        .attr('data-icon', 'fa-th-large'); // without 'fa-' prefix
                } else {
                    $icon.removeClass('fa-th-large').addClass('fa-address-book')
                        .attr('data-icon', 'address-book');
                }

                var students = Object.values(data.student_data || {});
                var teachers = Object.values(data.teacher_data || {});


                var html = '<div class="student-list">';

                if (students.length > 0) {
                    students.forEach(function(row) {
                        // Set default image if empty
                        var student_pic = row.student_image_profile ? row.student_image_profile : '../../../images/default.png';
                        var group_logo = row.group_logo ? row.group_logo : '../../../images/academy_logo.png';
                        var border_color = row.group_color ? row.group_color : '#ff8c00';


                        html += '<a href="studentinfo?id=' + encodeURIComponent(row.student_id) + '" class="student-card">';
                        html += '<div class="student-avatar" style="border-color:' + border_color + ';">';
                        html += '<img src="' + student_pic + '" alt="Student Avatar" onerror="this.src=\'../../../images/default.png\'"> ';
                        html += '</div>';
                        html += '<div class="student-info">';
                        html += '<p class="student-id-display"><span style="margin-right:.5em;">ID:&nbsp;</span> ' + row.student_id + '</p>';
                        html += '<h4 class="student-name">&nbsp;' + (row.student_firstname_th + ' ' + row.student_lastname_th) + '</h4>';
                        html += '<p class="student-details highlight-text"><i class="fas fa-graduation-cap"></i> &nbsp;' + (row.classroom_name ? row.classroom_name : '-') + '</p>';
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

                var teacherHtml = '<div class="teacher-list">';

                if (teachers.length > 0) {
                    teachers.forEach(function(row) {
                        var teacher_pic = row.teacher_image_profile ? row.teacher_image_profile : '../../../images/default.png';
                        var position = row.position_name_th ? row.position_name_th : '-';
                        var fullName = (row.teacher_firstname_th || '') + ' ' + (row.teacher_lastname_th || '');
                        var email = row.teacher_email ? row.teacher_email : '-';
                        var mobile = row.teacher_mobile ? row.teacher_mobile : '-';

                        teacherHtml += '<a href="teacherinfo?id=' + encodeURIComponent(row.teacher_id) + '" class="student-card">';
                        teacherHtml += '<div class="student-avatar">';
                        teacherHtml += '<img src="' + teacher_pic + '" alt="Teacher Avatar" onerror="this.src=\'../../../images/default_teacher.png\'">';
                        teacherHtml += '</div>';
                        teacherHtml += '<div class="student-info" style="margin-left: 10px; color: black;">';
                        teacherHtml += '<h4 class="teacher-name">' + fullName + '</h4>';
                        teacherHtml += '<div class="group-head-name">';
                        teacherHtml += '</div>';
                        teacherHtml += '<p class="group-description">ข้อมูลติดต่อ</p>';
                        teacherHtml += '<p><i class="fas fa-phone"></i> ' + mobile + '</p>';
                        teacherHtml += '<p><i class="fas fa-envelope"></i> ' + email + '</p>';
                        teacherHtml += '</div>';
                        teacherHtml += '</a>';
                    });
                } else {
                    teacherHtml = `
                    <div class="empty-state">
                        <i class="fas fa-users empty-icon"></i>
                        <h3>ยังไม่พบข้อมูลอาจารย์ในขณะนี้</h3>
                        <p>กรุณาตรวจสอบใหม่อีกครั้ง</p>
                    </div>
                    `;
                }

                teacherHtml += '</div>';

                // Insert the generated teacher HTML into #rowTeacher
                $('#rowTeacher').toggle();
                $('#menuTeacher').html(teacherHtml).toggle();

                // Insert the generated HTML into the page
                $('#rowData').toggle();
                $('#menu').html(html).toggle();

            },
            error: function() {
                alert('Failed to load data.');
            }
        });
    });

});


// function cleanData(arr) {
//     return arr.map(item => {
//         let cleaned = {};
//         for (const key in item) {
//             if (isNaN(key)) { // keep only non-numeric keys
//                 cleaned[key] = item[key];
//             }
//         }
//         return cleaned;
//     });
// }

