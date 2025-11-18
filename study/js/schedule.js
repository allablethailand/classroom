let allSessions = [];
let currentDate;

$(document).ready(function () {

   function isValidDate(d) {
    return d instanceof Date && !isNaN(d);
  }

  if (typeof dateRangeFromPHP !== 'undefined' && dateRangeFromPHP.trim() !== '') {
    let parsedDate = new Date(dateRangeFromPHP);
    if (isValidDate(parsedDate)) {
      currentDate = parsedDate;
    } else {
      console.warn("dateRangeFromPHP is invalid, falling back to current date.");
      currentDate = new Date();
    }
  } else {
    currentDate = new Date();
  }

  console.log("TODAY", currentDate);

  const $currentDateSpan = $("#current-date");
  const Buttonpos = $("#select-date-btn").offset();

  console.log(Buttonpos);

  $('#select-date-btn').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    // autoApply: false, // shows apply and cancel buttons
    locale: {
        format: 'YYYY-MM-DD',
        cancelLabel: 'Cancel',
			  applyLabel: 'Ok',
    },
    opens: 'left'
}, function(start, end, label) {
    currentDate = start.toDate();

    $('#hidden-date-input').val(start.format('YYYY-MM-DD'));
    updateDateDisplay();
});


$('#select-date-btn').on('cancel.daterangepicker', function(ev, picker) {
    // You can reset or handle cancel here if needed
    picker.hide();
});

  $("#select-date-btn").on("click", function() {
    $(this).data('daterangepicker').show();
});

  function formatDate(date) {
    const options = {
      weekday: "short",
      day: "numeric",
      month: "short",
      year: "numeric",
    };
    return date.toLocaleDateString("en-US", options);
  }

  function formatDateToBangkok(date) {
  const options = {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    timeZone: 'Asia/Bangkok',
  };
  
  const formatter = new Intl.DateTimeFormat('en-CA', options);
  return formatter.format(date);
}


  function updateDateDisplay() {

    $currentDateSpan.text(formatDate(currentDate));
    $("#datepicker").daterangepicker("setDate", currentDate);

    console.log("formDate:", currentDate);

    const sqlDate = formatDateToBangkok(currentDate);
    // const sqlDate = currentDate.toISOString().split("T")[0];
    // console.log("Load schedule for:", formatDateToBangkok(currentDate));

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
        // console.log("hello", result);
        if (result.status && Array.isArray(result.group_data)) {
          allSessions = result.group_data;
          const allInstructors = result.instructor || [];

          $("#scheduleContainer").show().empty();

          allSessions.forEach((session, key) => {
            const eventDate = session.date_start || "";
            const startTime = session.time_start || "";
            const endTime = session.time_end || "";
            const title = session.course_name || "";
            const category = session.course_category || "";
            const course_detail = session.course_detail || "ยังไม่ระบุ";

            // Filter instructors for this course/session by matching course_id or other identifiers
            const instructorsForSession = allInstructors.filter(
              (instr) => instr.course_id === session.course_id
            );

            let instructorsHtml = "";
            const maxVisible = 10;

            if (instructorsForSession.length <= maxVisible) {
              // Show all instructors if 3 or less
              instructorsForSession.forEach((instr) => {
                instructorsHtml += `
            <div class="member-avatar avatar-orange" title="${instr.coach_name}">
                <img src="/${instr.coach_image}" 
                    onerror="this.src='/images/default.png'; this.style.width='30px'; this.style.height='30px'; this.style.objectFit='scale-down';" 
                    alt="${instr.coach_name}" 
                    style="width: 30px; height: 30px; border-radius: 100%; object-fit: fill">
            </div>`;
              });
            } else {
              // Show first 3 instructors
              for (let i = 0; i < maxVisible; i++) {
                let instr = instructorsForSession[i];
                instructorsHtml += `
                <div class="member-avatar avatar-orange" title="${instr.coach_name}">
                    <img src="/${instr.coach_image}" 
                        onerror="this.src='/images/default.png'; this.style.width='30px'; this.style.height='30px'; this.style.objectFit='scale-down';" 
                        alt="${instr.coach_name}" 
                        style="width: 30px; height: 30px; border-radius: 100%; object-fit: fill">
                </div>`;
              }
              // Add a count indicator for remaining instructors
              const remainingCount = instructorsForSession.length - maxVisible;
              instructorsHtml += `
                <div class="member-avatar avatar-orange" title="and ${remainingCount} more">
                    <div class="avatar-counter" style="width: 30px; height: 30px; border-radius: 100%; background-color: #f80; color: white; display: flex; justify-content: center; align-items: center; font-weight: bold;">
                        +${remainingCount}
                    </div>
                </div>`;
            }

            const html = `
            <div class="schedule-container${
              key === allSessions.length - 1 ? " last" : ""
            }">
                <div class="schedule-item">
                    <div class="schedule-time">
                        <span class="schedule-time-text">${startTime}</span>
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
                                <p class="schedule-duration">${eventDate} • ${startTime}${endTime ? " - " + endTime : "" }</p>
                            </div>
                            <span class="schedule-badge badge-class">${category}</span>
                        </div>
                        <div class="schedule-footer">
                            <div class="member-avatars">
                                ${instructorsHtml}
                            </div>
                            <button type="button" class="btn btn-primary" style="background-color: #7936e4; border-radius: 15px;"
                                data-toggle="modal"
                                data-target="#scheduleModal"
                                data-index="${key}">
                                ไปยังคลาสเรียน
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
            $("#scheduleContainer").append(html);
          });
        } else {
          $("#scheduleContainer").hide();
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
$(document).on("click", '.btn.btn-primary[data-toggle="modal"]', function () {
  const index = $(this).data("index");
  if (allSessions && allSessions[index]) {
    const session = allSessions[index];

    $("#modalDetails span").text(session.schedule_name || "-");
    const start = session.time_start || "-";
    const end = session.time_end || "";
    $("#modalTime span").text(end ? start + " - " + end : start);
    $("#modalSpeakers span").text(session.coach_name || "ยังไม่ระบุ");

    // Store index on modal itself
    $("#scheduleModal").data("sessionIndex", index);

    $("#scheduleModal").modal("show");
  }
});

// Cancel modal on decline button click
$(document).on("click", ".decline-modal", function () {
  // Find closest modal to this button and hide it
  $(this).closest(".modal").modal("hide");

  swal({
    type: "error",
    title: "ปฏิเสธ",
    text: "คุณได้ปฏิเสธการเข้าร่วมอีเวนท์นี้",
  });
});

// Open second modal from first modal's "join" button
$(document).on("click", ".open-new-modal", function () {
  const firstModal = $(this).closest(".modal");
  // Get the index stored when opening the modal earlier
  const index = firstModal.data("sessionIndex");

  if (typeof index === "undefined" || !allSessions[index]) return;

  const session = allSessions[index];
  const start = session.time_start || "-";
  const end = session.time_end || "";

  // Hide first modal, then show second modal

  // Hide first modal, then show second modal linked by index
  firstModal.modal("hide");
  firstModal.one("hidden.bs.modal", function () {
    $("#modalTimeNew").text(end ? start + " - " + end : start);

    $("#newModal").modal("show");
  });
});

// Accept event on second modal
$(document).on("click", ".accept-event", function () {
  $(this).closest(".modal").modal("hide");

  swal({
    type: "success",
    title: "เข้าร่วมสำเร็จ",
    text: "คุณได้เข้าร่วมอีเว้นท์นี้เรียบร้อยแล้ว",
  });
});



function redirectCurreculum(course_id, course_type, classroom_id) {
  let new_path = course_type + "_" + course_id;
  let url = `/classroom/study/redirect.php?id=${window.btoa(
    new_path
  )}&cid=${window.btoa(classroom_id)}`;
  window.open(url, "_self");
}