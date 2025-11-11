let classroom_group;
let management_template;
let join_template;
let tb_staff;
let table_join_user;
var start;
var end;
var default_date;

// const badge = document.querySelector('.filter-tab-present .badge-count');
// badge.textContent = '0'

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

function capitalizeFirstLetter(str) {
  if (!str) return ''; // Handle empty string
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function load_history(){
    $.ajax({
		url: "/classroom/study/actions/history.php",
		data: {
			action:'fetch_history',
			// classroom_id: classroom_id
		},
		dataType: "JSON",
		type: 'POST',
		success: function(result){
            console.log("hello",result);
            if(result.length === 0){
                $('#ordersContainer').html('<p style="margin-top: 5rem; text-align:center;">No history found.</p>');
                return;
            }
            var html = '';
            result.forEach(function(order){
                // Format date from yyyy-mm-dd to dd/mm/yy
                var originalDate = order.olh_date_part || order.trn_date || '';
                // var formattedDate = '';
                // if(originalDate){
                //     var d = new Date(originalDate);
                //     var day = ('0' + d.getDate()).slice(-2);
                //     var month = ('0' + (d.getMonth()+1)).slice(-2);
                //     var year = d.getFullYear().toString().slice(-2);
                //     formattedDate = day + '/' + month + '/' + year;
                // }

                // Determine order type and icon
                var dataType = order.trn_type_description === 'inhouse' || order.trn_type_description === 'both' ? 'current' : 'waiting';
                
                // LATE, PRESENT, EARLY, MISSED
                
                var iconHtml = '<img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 60px; height: 60px; border-radius: 100%;">'

                    // : '<i class="fa-solid fa-motorcycle"></i>';

                // <span class="order-detail-item"><i class="fast fa-solid fa-box"></i> 500 pieces</span>
                            
                // Construct order card HTML
                html += `
                <div class="order-card" data-type="${dataType}">
                    <div class="order-icon">${iconHtml}</div>
                    <div class="order-info">
                        <span class="order-status status-${dataType}">${dataType === 'current' ? 'Current Order' : 'Awaiting Rider'}</span>
                        <h3 class="order-title">${order.trn_subject || 'N/A'}</h3>
                        <span class="order-number"><i class="fas fa-building"></i>: ${capitalizeFirstLetter(order.trn_type_description) || '-'}</span>
                        <div class="order-details">
                            <span class="order-detail-item"><i class="fas fa-regular fa-clock"></i> ${order.olh_time_part || '-'}</span>
                            <span class="order-detail-item"><i class="fas fa-regular fa-calendar"></i> ${originalDate}</span>
                        </div>
                    </div>
                </div>`;
            });
            $('#ordersContainer').html(html);
        },
        error: function(){
            $('#ordersContainer').html('<p style="text-align: center;">Error loading history.</p>');
        }
	});
}


 function switchMainTab(element, tab) {
            // Remove active class from all main tabs
            document.querySelectorAll('.main-tab').forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            element.classList.add('active');

            // You can add logic here to load different data based on tab
            console.log('Switched to:', tab);
        }

    
        function switchFilterTab(element, filter) {
            const tabsContainer = document.querySelector('.filter-tabs');
            const allTab = tabsContainer.querySelector('button[onclick*="all"]');
            const tabs = tabsContainer.querySelectorAll('button');
            const cards = document.querySelectorAll('.order-card');

            if (filter === 'all') {
                // Activate only the 'All' tab, deactivate others
                tabs.forEach(tab => tab.classList.remove('active'));
                element.classList.add('active');

                // Show all cards
                cards.forEach(card => card.style.display = 'block');
            } else {
                // Toggle clicked non-'all' tab active state
                if (element.classList.contains('active')) {
                    element.classList.remove('active');
                } else {
                    element.classList.add('active');
                }

                // If any non-'all' tab is active, deactivate 'All'
                const anyActive = [...tabs].some(tab => tab !== allTab && tab.classList.contains('active'));
                if (anyActive) {
                    allTab.classList.remove('active');
                } else {
                    // If none active, activate 'All'
                    allTab.classList.add('active');
                }

                // Show cards matching any active filters (or all if none)
                const activeFilters = [...tabs]
                    .filter(tab => tab !== allTab && tab.classList.contains('active'))
                    .map(tab => tab.getAttribute('onclick').match(/'(\w+)'/)[1]);

                cards.forEach(card => {
                    if (activeFilters.length === 0) {
                        card.style.display = 'block';
                    } else {
                        const cardType = card.getAttribute('data-type');
                        card.style.display = activeFilters.includes(cardType) ? 'block' : 'none';
                    }
                });
            }
        }

        // Add click animation to order cards
        document.querySelectorAll('.order-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-2px)';
                }, 100);
            });
        });


        // Attach event listeners to filters:
        document.querySelectorAll('.filter-tag').forEach(element => {
            const removeBtn = element.querySelector('.remove-btn');
            // Set remove button visibility initially
            removeBtn.style.display = element.classList.contains('active') ? 'inline' : 'none';

            element.addEventListener('click', () => {
                // Remove active class from all, hide all remove buttons
                document.querySelectorAll('.filter-tag').forEach(e => {
                    e.classList.remove('active');
                    e.querySelector('.remove-btn').style.display = 'none';
                });

                element.classList.add('active');
                removeBtn.style.display = 'inline';

                // Apply filter and update date pickers
                applyFilter(element.getAttribute('data-filter'));
            });

            removeBtn.addEventListener('click', (event) => {
                event.stopPropagation(); // Prevent triggering element click
                element.classList.remove('active');
                removeBtn.style.display = 'none';

                // Clear filters and daterange inputs
                applyFilter('all');
            });
        });



$(document).ready(function () {

    

    load_history();
    // Dropdown change event
    $('select[name="classroom_id"]').on('change', function () {
        var classroomId = $(this).val();

        // Send AJAX POST request with selected classroom_id
        $.ajax({
            url: '/classroom/study/actions/history.php', // Your backend endpoint
            type: 'POST',
            data: {
                action: 'fetch_history',
                // classroom_id: classroomId,
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


        $('#searchDateRange').on('click', function() {
            var startDate = $('#start_date').val(); // e.g., '15/10/2025'
            var endDate = $('#end_date').val(); // e.g., '17/10/2025'

            // Basic validation can be added here to ensure both dates are selected

            $.ajax({
                url: '/classroom/study/actions/history.php', // Your backend endpoint
                type: 'POST',
                data: {
                    action: 'searchDatePeriod',
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    // Handle response - e.g., update UI with fetched data
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error fetching data:', error);
                }
            });
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