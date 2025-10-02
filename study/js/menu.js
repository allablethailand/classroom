$(document).ready(function () {
    loadAndRenderClasses();
});

// Utility to format date like Tuesday, September 9, 2025
function formatDate(dateStr) {
  let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateStr).toLocaleDateString('en-US', options);
}

// Utility to remove seconds from time string HH:mm:ss -> HH:mm
function removeSeconds(time) {
  return time ? time.split(':').slice(0, 2).join(':') : time;
}

// Render an array of class data to the given containerId with optional label
function renderClasses(classes, containerId, label = '') {
  let container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';

   // Check if any class object is null or falsy, then exit early
  if (!classes || classes.some(cls => cls == null)) {
    console.warn("Received null class data, skipping render.");
    return;
  }

  if (label) {
    container.innerHTML += `
      <div class="row">
        <div class="head-flex-menu">
          <p class="menu-title">${label}</p>
          <a href="schedule" class="menu-title-button">
            <svg viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage"> <g id="Icon-Set-Filled" sketch:type="MSLayerGroup" transform="translate(-310.000000, -1089.000000)" fill="currentColor"> <path d="M332.535,1105.88 L326.879,1111.54 C326.488,1111.93 325.855,1111.93 325.465,1111.54 C325.074,1111.15 325.074,1110.51 325.465,1110.12 L329.586,1106 L319,1106 C318.447,1106 318,1105.55 318,1105 C318,1104.45 318.447,1104 319,1104 L329.586,1104 L325.465,1099.88 C325.074,1099.49 325.074,1098.86 325.465,1098.46 C325.855,1098.07 326.488,1098.07 326.879,1098.46 L332.535,1104.12 C332.775,1104.36 332.85,1104.69 332.795,1105 C332.85,1105.31 332.775,1105.64 332.535,1105.88 L332.535,1105.88 Z M326,1089 C317.163,1089 310,1096.16 310,1105 C310,1113.84 317.163,1121 326,1121 C334.837,1121 342,1113.84 342,1105 C342,1096.16 334.837,1089 326,1089 L326,1089 Z" id="arrow-right-circle" sketch:type="MSShapeGroup"> </path> </g> </g> </g></svg>
          </a>
        </div>
      </div>
    `;
  }

  classes.forEach(cls => {
    let timeSchedule = cls.time_end 
      ? `${removeSeconds(cls.time_start)} - ${removeSeconds(cls.time_end)}`
      : removeSeconds(cls.time_start);

    container.innerHTML += `
      <div class="row">
        <a href="schedule?course=${cls.id || cls.trn_id}" >
          <div class="container-menu" style="margin-top: 10px;">
            <div class="header-menu">
              <span class="title-menu">${cls.schedule_name}</span>
              <span id="countdown-${cls.id || cls.trn_id}" class="subtitle-menu">
            </span>
            </div>
            <div class="usage-menu">
              <div class="progress-section">
                <div class="progress-header-flex">
                  <span class="progress-text">${formatDate(cls.date_start)}</span>
                  <span class="progress-text">${timeSchedule}</span>
                </div>
                <div class="progress-header-flex">
                  ${cls.countdown ? `<span id="countdown-${cls.id || cls.trn_id}" class="progress-text-bottom">${cls.countdown}</span>` : ''}
                </div>
              </div>
            </div>

                <div class="button-menu">
                  <div class="progress-section">
                    <div class="progress-header-reverse">
                      <span class="progress-text-bottom">Topic: ${cls.topic_name ?? 'Education'}</span>
                      <button class="btn-origami">View Detail</button>
                    </div>
                  </div>
                </div>
          </div>
        </a>
      </div>
    `;
  });
}

// Countdown helper for a class start time with element id
function startCountdownForClass(classInfo, elementId) {
  const countdownElem = document.getElementById(elementId);
  if (!countdownElem) return;

  function updateCountdown() {
    const now = new Date();
    const startTime = new Date(`${classInfo.date_start}T${classInfo.time_start}`).getTime();
    const diffMs = startTime - now.getTime();
    console.log(`Current time: ${classInfo.date_start}`);
    
   // When the countdown reaches zero or past
    if (diffMs <= 0) {
        if (classInfo.time_end) {
        const endTime = new Date(`${classInfo.date_start}T${classInfo.time_end}`).getTime();
        if (now.getTime() < endTime) {
            countdownElem.innerHTML = '<span style="color: #ff8c5a;">Ongoing</span>';
            return; // Keep showing ongoing status, no need to clear interval
        }
        }
        countdownElem.textContent = "Class has ended";
        clearInterval(interval);
        return;
    }

    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);

    let timeText = "";
    if (diffHours > 0) {
      timeText = `Start in <span style="color: #ff8c5a;">&nbsp;${diffHours}&nbsp;</span> hour${diffHours > 1 ? "s" : ""}`;
    } else if (diffMinutes > 0) {
      timeText = `Start in <span style="color: #ff8c5a;">&nbsp;${diffMinutes}&nbsp;</span> minute${diffMinutes > 1 ? "s" : ""}`;
    } else {
      timeText = `Start in <span style="color: #ff8c5a;">&nbsp;${diffSeconds}&nbsp;</span> second${diffSeconds !== 1 ? "s" : ""}`;
    }

    countdownElem.innerHTML = timeText;
  }

  updateCountdown();
  let interval = setInterval(updateCountdown, 3000);
}

function initializeCountdownsForAllClasses(classSchedule) {
  const allClasses = [
    ...(classSchedule.ongoing_class || []),
    ...(classSchedule.starting_soon_class || []),
    ...(classSchedule.other_upcoming_class || []),
  ];
  allClasses.forEach(cls => {
    const countdownId = `countdown-${cls.id || cls.trn_id}`;
    startCountdownForClass(cls, countdownId);
  });
}

// Main function to load classes and render all sections
function loadAndRenderClasses() {
  $.ajax({
    url: "/classroom/study/actions/menu.php",
    type: "POST",
    data: {
      action: "getUpcomingClass"
    },
    success: function (response) {
     let classSchedule  = JSON.parse(response);

      renderClasses(classSchedule.ongoing_class, "ongoing-class-container", "Upcoming Class");
      renderClasses(classSchedule.starting_soon_class, "starting-soon-class-container");
      renderClasses(classSchedule.other_upcoming_class, "other-upcoming-class-container");
      renderClasses(classSchedule.overdue_class, "overdue-class-container");

      initializeCountdownsForAllClasses(classSchedule);
    },
    error: function (xhr, status, error) {
      console.error("Failed to load schedule:", error);
    }
  });
}
