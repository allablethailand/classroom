let classroom_group;
let management_template;
let join_template;
let tb_staff;
let table_join_user;
var start;
var end;
var default_date;

function applyFilter(filter) {
    let startDate, endDate;
    const now = moment();

    switch(filter) {
        case 'today':
            startDate = now.clone().startOf('day');
            endDate = now.clone().endOf('day');
            break;
        case 'lastmonth':
            startDate = now.clone().subtract(1, 'month').startOf('month');
            endDate = now.clone().subtract(1, 'month').endOf('month');
            break;
        case 'lastweek':
            startDate = now.clone().subtract(1, 'week').startOf('week');
            endDate = now.clone().subtract(1, 'week').endOf('week');
            break;
        case 'thismonth':
            startDate = now.clone().startOf('month');
            endDate = now.clone().endOf('month');
            break;
        case 'thisweek':
            startDate = now.clone().startOf('week');
            endDate = now.clone().endOf('week');
            break;
        case 'all':
            // Clear the date pickers and filters
            $('#start_date').val('');
            $('#end_date').val('');
            return;
        default:
            return;
    }

    // Update the date picker inputs
    $('#start_date').data('daterangepicker').setStartDate(startDate);
    $('#end_date').data('daterangepicker').setStartDate(endDate);

    $('#start_date').val(startDate.format('DD/MM/YYYY'));
    $('#end_date').val(endDate.format('DD/MM/YYYY'));
}



$(document).ready(function () {
    // Dropdown change event
    $('select[name="classroom_id"]').on('change', function () {
        var classroomId = $(this).val();

        // Send AJAX POST request with selected classroom_id
        $.ajax({
            url: '/classroom/study/actions/history.php', // Your backend endpoint
            type: 'POST',
            data: {
                action: 'fetch_history',
                classroom_id: classroomId,
            },
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    // Process the returned alumni data
                    updateAlumniDisplay(result.data);
                } else {
                    alert('Failed to fetch alumni data');
                }
            },
            error: function () {
                alert('Error sending request');
            }
        });
    });


    $('#bottomModal').on('shown.bs.modal', function () {
        var today = moment();
        // Initialize start date picker
        $('#start_date').daterangepicker({
            singleDatePicker: true,
            startDate: today,
            showDropdowns: true,
            autoUpdateInput: true,
            opens: 'left',
            drops: 'up',
            locale: {
                cancelLabel: 'Clear',
                applyLabel: 'Ok',
                format: 'DD/MM/YYYY',
            },
        }, function(startSelected) {
            $('#start_date').val(startSelected.format('DD/MM/YYYY'));
            // Update the minimum date for end_date picker
            $('#end_date').data('daterangepicker').minDate = startSelected;
            var endPicker = $('#end_date').data('daterangepicker');
            if (endPicker.startDate.isBefore(startSelected, 'day')) {
                $('#end_date').val('');
                endPicker.setStartDate(startSelected);
            }
        });

        // Initialize end date picker
        $('#end_date').daterangepicker({
            singleDatePicker: true,
            startDate: today,
            minDate: today, // set initial minimum date as start date
            showDropdowns: true,
            autoUpdateInput: true,
            opens: 'left',
            drops: 'up',
            locale: {
                cancelLabel: 'Clear',
                applyLabel: 'Ok',
                format: 'DD/MM/YYYY',
            },
        }, function(endSelected) {
            $('#end_date').val(endSelected.format('DD/MM/YYYY'));
        });
    });



    // $('#toggleStudent').click(function (e) {
    //     e.stopPropagation();
    //     var $btn = $(this);
    //     $.ajax({
    //         url: '/classroom/study/actions/group.php?action=toggle_student',
    //         type: "GET",
    //         success: function(data) {
    //            var $icon = $btn.find('[data-fa-i2svg]');
    //             if ($icon.attr('data-icon') === 'address-book') {
    //                 $icon.removeClass('fa-address-book').addClass('fa-th-large') // replace 'fa-other-icon' with your off icon class
    //                     .attr('data-icon', 'fa-th-large'); // without 'fa-' prefix
    //             } else {
    //                 $icon.removeClass('fa-th-large').addClass('fa-address-book')
    //                     .attr('data-icon', 'address-book');
    //             }

    //             var students = JSON.parse(data);
    //             var html = '<div class="student-list">';

    //             if (students.length > 0) {
    //                 students.forEach(function(row) {
    //                     // Set default image if empty
    //                     var student_pic = row.student_image_profile ? row.student_image_profile : '../../../images/default.png';
    //                     var group_logo = row.group_logo ? row.group_logo : '../../../images/default.png';
    //                     var border_color = row.group_color ? row.group_color : '#ff8c00';



    //                     html += '<a href="studentinfo?id=' + encodeURIComponent(row.student_id) + '" class="student-card">';
    //                     html += '<div class="student-avatar" style="border-color:' + border_color + ';">';
    //                     html += '<img src="' + student_pic + '" alt="Student Avatar" ';
    //                     html += 'onerror="this.src=\'../../../images/default.png\'">';
    //                     html += '</div>';
    //                     html += '<div class="student-info">';
    //                     html += '<p class="student-id-display"><span style="margin-right:.5em;">ID:&nbsp;</span> ' + row.student_id + '</p>';
    //                     html += '<h4 class="student-name">&nbsp;' + (row.student_firstname_th + ' ' + row.student_lastname_th) + '</h4>';
    //                     html += '<p class="student-details highlight-text"><i class="fas fa-graduation-cap"></i> &nbsp;' + (row.classroom_name ? row.classroom_name : '-') + '</p>';
    //                     html += '<p class="student-details highlight-text group-name-container"><img src="' + group_logo + '" alt="Group Logo"> ' + (row.group_name ? row.group_name : '-') + '</p>';
    //                     html += '<p class="student-details"><i class="fas fa-building"></i> &nbsp;' + (row.student_company ? row.student_company : '-') + '</p>';
    //                     html += '<p class="student-details"><i class="fas fa-briefcase"></i> &nbsp;' + (row.student_position ? row.student_position : '-') + '</p>';
    //                     html += '<p class="student-details"><i class="fas fa-phone"></i> &nbsp;' + (row.student_mobile ? row.student_mobile : '-') + '</p>';
    //                     html += '<p class="student-details"><i class="fas fa-envelope"></i> &nbsp;' + (row.student_email ? row.student_email : '-') + '</p>';
    //                     html += '</div></a>';
    //                 });
    //             } else {
    //                 html += "<p style='text-align: center; color: #888;'>ไม่พบข้อมูลนักเรียนในกลุ่มนี้</p>";
    //             }

    //             html += '</div>';

    //             // Insert the generated HTML into the page
    //             $('#menu').html(html).toggle();
    //             $('#rowData').toggle();
    //         },
    //         error: function() {
    //             alert('Failed to load data.');
    //         }
    //     });
    // });

    // function fetchStudentHistory() {
    //     $.ajax({
    //         url: '/classroom/study/actions/group.php?action=fetch_student_history',
    //         type: "GET",
    //         success: function(data) {
    //             var students = JSON.parse(data);
    //             var html = '<div class="student-list">';

    // $('#toggleStudent').click(function (e) {
    //     e.stopPropagation();
    //     var $btn = $(this);
    //     $.ajax({
    //         url: '/classroom/study/actions/history.php',
    //         type: "GET",
    //         success: function (data) {

    //         }
    //     });
    // });

});




// $classroom_id = $_POST['classroom_id'];
//         $table = "SELECT 
//             course.course_id,
//             (
//                 CASE
//                     WHEN course.course_type = 'course' then c.trn_subject
//                     ELSE l.learning_map_name
//                 END
//             ) as course_name,
//             course.course_type,
//             (
//                 CASE
//                     WHEN course.course_type = 'course' then c.picture_title
//                     ELSE l.learning_map_pic
//                 END
//             ) as course_cover,
//             date_format(course.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
//             CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
//             course.course_ref_id
//         FROM 
//             classroom_course course
//         LEFT JOIN 
//             ot_training_list c on c.trn_id = course.course_ref_id and course.course_type = 'course'
//         LEFT JOIN 
//             ot_learning_map_list l on l.learning_map_id = course.course_ref_id and course.course_type = 'learning_map'