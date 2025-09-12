$(document).ready(function () {
    load_nextclass();
});

function view_group(class_id) {
    $.ajax({
        url: "/classroom/study/actions/group.php",
        type: "POST",
        data: {
            action: "view_group",
            class_gen_id: class_id,
        },
        dataType: "JSON",
        success: function (result) {
            $.redirect("group", { classroom_group: result }, "post");
            // $.redirect("detail",{classroom_id: result.classroom_id}, 'post');
            //    console.log(result);
        },
        error: function (xhr, status, error) {
            console.error("Error loading management data:", error);
        },
    });
}

function load_nextclass() {
    $.ajax({
        url: "/classroom/study/actions/menu.php", // your PHP file
        type: "POST", // send JSON
        data: {
            action: "getUpcomingClass",
        },
        success: function (response) {
            console.log("HTML", response);

            const nextClass = response.soon_class;
            const otherClass = response.other_classes;

            if (nextClass) {
                getUpcomingClass(nextClass, otherClass);
                startCountdown(`${nextClass.date_start}T${nextClass.time_start}`);
            }
        },
        error: function (xhr, status, error) {
            console.error("Failed to load schedule:", error);
        },
    });
}

function getUpcomingClass(soonClass, otherClasses) {
    let timeSchedule;
    if (!soonClass.time_end || soonClass.time_end === "") {
        timeSchedule = soonClass.time_start;
    } else {
        timeSchedule = soonClass.time_start + " - " + soonClass.time_end;
    }

    // Format date like Tuesday, September 9, 2025
    let options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    let newDate = new Date(soonClass.date_start).toLocaleDateString(
        "en-US",
        options
    );

    let html = "";
    if (soonClass) {
        html += `
          <div class="container-menu" style="margin-top: 10px;">
            <div class="header-menu">
                <span class="title-menu">${soonClass.schedule_name}</span>
                <span class="progress-text-end">
                    <span class="label label-default pill pill-icon-before">${soonClass.stamp_in_status}</span>
                    </span>
            </div>
            <div class="usage-menu">
                <div class="progress-section">
                <div class="progress-header-flex">
                    <span class="progress-text">
                    ${newDate}     
                    </span>
                    <span class="progress-text">${timeSchedule}</span>
                </div>
                <div class="progress-header-flex">
                <span id="countdown" class="progress-text-bottom"></span>       
                </div>
                </div>
            </div>
        </div>`;
    }


    if (otherClasses && otherClasses.length > 0) {
        otherClasses.forEach((cls) => {
            html += `<div class="row">
            <div class="container-menu" style="margin-top: 10px;">
              <div class="header-menu">
                <span class="title-menu">${cls.schedule_name}</span>
                <span class="subtitle-menu">${cls.date_start}</span>
              </div>
              <div class="usage-menu">
                <div class="progress-section">
                  <div class="progress-header-flex">
                    <span class="progress-text">${cls.time_start} - ${cls.time_end || ""
                }</span>
                  </div>
                  <div class="progress-header-flex">
                    <span class="progress-text-end">
                      <span class="label label-default pill pill-icon-before">No status</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>`;
        });
    }
    $("#upcomingClass").html(html);
}
function startCountdown(targetStartTime) {
    const countdownElem = document.getElementById("countdown");
    if (!countdownElem) return;

    function updateCountdown() {
        const now = new Date();
        let diffMs = new Date(targetStartTime) - now;

        if (diffMs <= 0) {
            countdownElem.textContent = "Class is starting now or already started";
            clearInterval(interval);
            return;
        }

        const diffSeconds = Math.floor(diffMs / 1000);
        const diffMinutes = Math.floor(diffSeconds / 60);
        const diffHours = Math.floor(diffMinutes / 60);

        let timeText = "";
        if (diffHours > 0) {
            timeText = `Start in ${diffHours} hour${diffHours > 1 ? "s" : ""}`;
        } else if (diffMinutes > 0) {
            timeText = `Start in ${diffMinutes} minute${diffMinutes > 1 ? "s" : ""}`;
        } else {
            timeText = `Start in ${diffSeconds} second${diffSeconds > 1 ? "s" : ""}`;
        }

        countdownElem.textContent = timeText;
    }

    updateCountdown(); // initial call immediately
    const interval = setInterval(updateCountdown, 1000);
}
