  let allSessions = [];

$(document).ready(function () {
  let currentDate = new Date();

  const $currentDateSpan = $("#current-date");

  function formatDate(date) {
    const options = {
      weekday: "short",
      day: "numeric",
      month: "short",
      year: "numeric",
    };
    return date.toLocaleDateString("en-US", options);
  }

  function updateDateDisplay() {
    $currentDateSpan.text(formatDate(currentDate));

    console.log("formDate:", currentDate);

    const sqlDate = currentDate.toISOString().split("T")[0];
    console.log("Load schedule for:", currentDate.toISOString().split("T")[0]);

    //  Fetch Data Schedule
    $.ajax({
      url: "/classroom/study/actions/schedule.php",
      type: "POST",
      data: {
        action: "fetch_schedules",
        date_range: sqlDate,
      },
      dataType: "JSON",
      success: function (result) {
        console.log(result);
        if (result.status ==  true && result.group_data) {
            allSessions = result.group_data;
            const sessions = result.group_data;

            $('#scheduleContainer').show();
            $('#scheduleContainer').empty();

             sessions.forEach((item, index) => {
            // Access properties using keys, e.g.:
            // item.schedule_name, item.topic_name, item.date_start, item.time_start, item.time_end
            // Example of creating HTML output:

            const eventDate = item.date_start || '';
            const startTime = item.time_start || '';
            const endTime = item.time_end || '';
            const title = item.schedule_name || '';
            const topic = item.topic_name || 'ยังไม่ระบุ'; // Means 'Not specified'

            // Create a container or append to an existing element, like:
            const html = `
                <div class="schedule-container${index === sessions.length - 1 ? ' last' : ''}">
                <div class="schedule-item">
                    <div class="schedule-time">
                    <span class="schedule-time-text">${startTime}</span>
                    <span class="schedule-time-bottom">${endTime}</span>
                    </div>
                    <div class="schedule-timeline">
                    <div class="timeline-dot timeline-dot-purple"></div>
                    <div class="timeline-line"></div>
                    </div>
                    <div class="schedule-content schedule-content-purple">
                    <div class="schedule-header">
                        <div>
                        <h3 class="schedule-title" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            ${title}
                        </h3>
                        <p class="schedule-duration">${eventDate} • ${startTime}${endTime ? ' - ' + endTime : ''}</p>
                        </div>
                        <span class="schedule-badge badge-class">${topic}</span>
                    </div>
                    <div class="schedule-footer">
                        <div class="member-avatars">
                        <div class="member-avatar avatar-purple"><span>👤</span></div>
                        <div class="member-avatar avatar-teal"><span>👤</span></div>
                        <div class="member-avatar avatar-orange"><span>👤</span></div>
                        </div>
                        <button type="button" class="btn btn-primary" style="background-color: #7936e4; border-radius: 15px;"
  data-toggle="modal"
  data-target="#scheduleModal"
  data-index="${index}">
  รายละเอียด
</button>
                    </div>
                    </div>
                </div>
                </div>`;

            // Append the HTML to some container in your page
            $('#scheduleContainer').append(html);
            });
        //   load_table();
        } else {
            allSessions = [];
            $('#scheduleContainer').hide();
        }
      },
      error: function (xhr, status, error) {
        swal({
          type: "error",
          title: "Error!",
          text: "Something went wrong: " + error,
        });
      },
    });


     // Send the session data to PHP backend to generate HTML
    // $.ajax({
    //   url: "/classroom/study/actions/schedule.php", // your PHP file
    //   type: "POST",
    //   contentType: "application/json", // send JSON
    //   data: JSON.stringify({
    //     action: "fetch_mydata",
    //     sessions: sessions,
    //     date: sqlDate,
    //   }),
    //   success: function (html) {
    //     // On success, inject returned HTML in your container
    //     $("#scheduleContainer").html(html);
    //   },
    //   error: function (xhr, status, error) {
    //     console.error("Failed to load schedule:", error);
    //     $("#scheduleContainer").html(
    //       '<p class="text-center">ไม่พบข้อมูลวันดังกล่าว</p>'
    //     );
    //   },
    // });
  }

  

  $("#prev-day").on("click", function () {
    currentDate.setDate(currentDate.getDate() - 1);
    updateDateDisplay();
  });

  $("#next-day").on("click", function () {
    currentDate.setDate(currentDate.getDate() + 1);
    updateDateDisplay();
  });

  

  // Cancel First Modal

  updateDateDisplay();
});


// Handle click on "รายละเอียด" buttons dynamically
$(document).on('click', '.btn.btn-primary[data-toggle="modal"]', function () {
  const index = $(this).data('index');
  if (allSessions && allSessions[index]) {
    const session = allSessions[index];

    $('#modalDetails span').text(session.schedule_name || '-');
    const start = session.time_start || '-';
    const end = session.time_end || '';
    $('#modalTime span').text(end ? start + ' - ' + end : start);
    $('#modalSpeakers span').text(session.session_speaker || 'ยังไม่ระบุ');

 // Store index on modal itself
    $('#scheduleModal').data('sessionIndex', index);

    $('#scheduleModal').modal('show');
  }
});

 // Cancel modal on decline button click 
    $(document).on('click', '.decline-modal', function() {
        // Find closest modal to this button and hide it
        $(this).closest('.modal').modal('hide');

        swal({
            type: 'error',
            title: 'ปฏิเสธ',
            text: 'คุณได้ปฏิเสธการเข้าร่วมอีเวนท์นี้',
        });
    });

    // Open second modal from first modal's "join" button
    $(document).on('click', '.open-new-modal', function() {
        const firstModal = $(this).closest('.modal');
        // Get the index stored when opening the modal earlier
        const index = firstModal.data('sessionIndex');

        if (typeof index === 'undefined' || !allSessions[index]) return;

        const session = allSessions[index];
        const start = session.time_start || '-';
        const end = session.time_end || '';

        // Hide first modal, then show second modal

        // Hide first modal, then show second modal linked by index
        firstModal.modal('hide');
        firstModal.one('hidden.bs.modal', function() {
            $('#modalTimeNew').text(end ? start + ' - ' + end : start);

            $('#newModal').modal('show');
        });
    });

    // Accept event on second modal
    $(document).on('click', '.accept-event', function() {
        $(this).closest('.modal').modal('hide');

        swal({
            type: 'success',
            title: 'เข้าร่วมสำเร็จ',
            text: 'คุณได้เข้าร่วมอีเว้นท์นี้เรียบร้อยแล้ว',
        });
    });

