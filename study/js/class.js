$(document).ready(function () {
  loadAlumni();
});
function loadAlumni() {
  $.ajax({
    url: "/classroom/study/actions/class.php",
    data: {
      action: "loadAlumni",
    },
    dataType: "JSON",
    type: "GET",
    success: function (result) {
      console.log(result);
      if (Array.isArray(result)) {
        renderCourses(result);
      }
    },
    error: function (xhr, status, error) {
      console.error("Failed to load courses:", error);
    },
  });
}
function loadClass(classroom_id) {
  $.ajax({
    url: "/classroom/study/actions/class.php",
    data: {
      action: "loadClass",
      classroom_id: classroom_id,
    },
    dataType: "JSON",
    type: "POST",
    success: function (result) {
      if (Array.isArray(result)) {
        renderClass(result, classroom_id);
      } else {
        console.error("Expected an array of courses but got:", result);
      }
    },
    error: function (xhr, status, error) {
      console.log(error);
      console.error("Failed to load courses:", error);
    },
  });
}
function loadCourses(courses) {
  const container = document.querySelector(".course-class");
  container.innerHTML = "";
  courses.forEach((course) => {
    console.log("hello");
    container.insertAdjacentHTML("beforeend", renderCourseCard(course));
  });
}
function renderCourses(courses) {
  const container = document.querySelector(".course-class");
  container.innerHTML = "";
  courses.forEach((course) => {
    container.insertAdjacentHTML("beforeend", renderCourseCard(course));
  });
}

function renderCourseCard(course) {
  const initial = course.instructor ? course.instructor.charAt(0) : "";
  const courseCover = course.course_cover
    ? `<img src="${course.course_cover}" alt="${course.course_name}" class="transparent-bg" style="width: 50px; height: 50px; border-radius: 100%;">`
    : "";

  const courseCoverGT = ` <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="" style="width: 50px; height: 50px; border-radius: 100%;" >`
  return `
    <div class="row" onclick="loadClass('${course.classroom_id}')"
      <div class="row">
        <div class="container-menu" style="margin-top: 10px; padding: 2rem;">
        <div class="flex-box-container">
          <div class="header-menu">
            <div class="img-banner">
               ${courseCoverGT}
            </div>
            <div class="class-menu">
            <span class="title-menu" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; overflow: hidden;">${course.classroom_name}</span>
              <div class="progress-section">
                <div class="progress-header">
                  <span class="progress-text" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;">${course.classroom_information}</span>
                </div>
                <div class="progress-header-flex">
                  <span class="progress-text-end"></span>
                </div>
              </div>
            </div>
          </div>
          <div class="next-icon-box">
            <i class="fas fa-chevron-right"></i>
            </div>
        </div>
        </div>
      </div>
    </div>
  `;
}


function renderClass(courses, classroom_id) {
  console.log(courses);
  const container = document.querySelector(".course-class");
  container.innerHTML = "";
  courses.forEach((course) => {
    container.insertAdjacentHTML(
      "beforeend",
      renderClassCard(course, classroom_id)
    );
  });
}


function renderClassCard(course, classroom_id) {
  const safeText = (text) => text ? text : "ไม่ระบุ";
  const courseCover = course.course_cover
    ? `<img src="/${course.course_cover}" alt="${course.course_name}" style="width: 70px; height: 70px; margin-bottom: 1rem; border-radius: 10px;" onerror="this.src='/images/training.jpg'">`
    : "";

  let instructorsHtml = "";

  const courseLoca = safeText(course.course_location);
  const courseStart = safeText(course.course_timestart);
  const courseEnd = course.course_timeend;
  const displayTime = courseEnd ? `${courseStart} - ${courseEnd}` : courseStart || "ไม่ระบุ";
  const courseInstr = safeText(course.course_instructor);
  const courseName = safeText(course.course_name);
  const courseDate = safeText(course.course_date);

  if (course.trn_count_by != null && course.trn_count_by > 1) {

    let cleaned = courseInstr.replace(/\s*,\s*/g, ',');
    let namesArray = cleaned.split(',');
    // Count the elements
    let namesCount = namesArray.length;
    // Loop to get each name
    namesArray.forEach(name => {
      console.log(name.trim());
    });


    let maxVisible = 3;

    if (namesArray.length <= maxVisible) {
      // Show all if 3 or less
      namesArray.forEach(name => {
        instructorsHtml += `
          <div class="member-avatar avatar-orange" title="${name.trim()}">
            <img src="" 
                onerror="this.src='/images/default.png'; this.style.width='30px'; this.style.height='30px'; this.style.objectFit='scale-down';" 
                alt="${name.trim()}" 
                style="width: 30px; height: 30px; border-radius: 100%; object-fit: fill; border: 3px solid red;">
          </div>`;
      });
    } else {
      // Show only first 3
      namesArray.slice(0, maxVisible).forEach(name => {
        instructorsHtml += `
          <div class="member-avatar avatar-orange" title="${name.trim()}">
            <img src="" 
                onerror="this.src='/images/default.png'; this.style.width='30px'; this.style.height='30px'; this.style.objectFit='scale-down';" 
                alt="${name.trim()}" 
                style="width: 30px; height: 30px; border-radius: 100%; object-fit: fill">
          </div>`;
      });

      // Add count +N for the remaining
      const remainingCount = namesArray.length - maxVisible;
      instructorsHtml += `
        <div class="member-avatar avatar-orange" title="and ${remainingCount} more">
          <div class="avatar-counter" style="width: 30px; height: 30px; border-radius: 100%; background-color: #f80; color: white; display: flex; justify-content: center; align-items: center; font-weight: bold; border: 3px solid red;">
            +${remainingCount}
          </div>
        </div>`;
    }
  } else {

    instructorsHtml += `
      <div class="member-avatar avatar-orange" title="more">
        <div class="avatar-counter" style="width: 30px; height: 30px; border-radius: 100%; background-color: #f80; color: white; display: flex; justify-content: center; align-items: center; font-weight: bold;">
          <img src="/${courseInstr}" alt="Instructor" class="instructor-photo" onerror="this.src='/images/default.png'" style="border: 3px solid red;" />
        </div>
      </div>`;
  }


  return `
    <div class="row" onclick="redirectCurreculum('${course.course_id}', '${course.course_type
    }', ${classroom_id})">
      <div class="container-menu" style="margin-top: 10px; padding: 2rem;">

        <div class="flex-box-container">
          <div class="header-menu">
            <div class="small-img-banner">
               ${courseCover}
            </div>
            <div class="class-menu">
            <span class="title-menu-sec" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;">${course.course_name}</span>
              <div class="progress-section">
                 <div>
                  <div class="instructor-name">
                    <b>ผู้สอน:</b> ${courseInstr}
                  </div>
                  <div class="instructor-info" style="margin-left: 0.5rem;">
                  ${instructorsHtml}
                  </div>
                </div>
                <div class="location-info">
                  สถานที่: โรงเรียน 1
                </div>
                 <div class="time-schedule-class">
                  <span class="small-text-gray">${courseDate}</span>
                  <span class="small-text-gray">| ${displayTime}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="next-icon-box">
            <i class="fas fa-chevron-right"></i>
            </div>
        </div>

      </div>
    </div>
  `;
}
function redirectCurreculum(course_id, course_type, classroom_id) {
  let new_path = course_type + "_" + course_id;
  let url = `/classroom/study/redirect.php?id=${window.btoa(
    new_path
  )}&cid=${window.btoa(classroom_id)}`;
  window.open(url, "_blank");
}
