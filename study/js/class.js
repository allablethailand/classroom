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
      classroom_id: classroom_id
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
  const initial = course.instructor ? course.instructor.charAt(0) : '';
  const courseCover = course.course_cover ? `<img src="${course.course_cover}" alt="${course.course_name}" style="width:100%; height:auto; margin-bottom: 1rem;">` : '';
  return `
    <div class="row" onclick="loadClass('${course.classroom_id}')"
      <div class="row">
        <div class="container-menu" style="margin-top: 10px; padding: 2rem;">
          <div class="header-menu">
            <span class="title-menu" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; overflow: hidden;">${course.classroom_name}</span>
          </div>
          <div class="usage-menu">
            <div class="progress-section">
              <div class="progress-header-flex">
                <span class="progress-text">${course.classroom_information}</span>
              </div>
              <div class="progress-header-flex">
                <span class="progress-text-end"></span>
              </div>
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
    container.insertAdjacentHTML("beforeend", renderClassCard(course, classroom_id));
  });
}
function renderClassCard(course, classroom_id) {
  const courseCover = course.course_cover ? `<img src="${course.course_cover}" alt="${course.course_name}" style="width:100%; height:auto; margin-bottom: 1rem;">` : '';
  return `
    <div class="row" onclick="redirectCurreculum('${course.course_id}', '${course.course_type}', ${classroom_id})">
      <div class="container-menu" style="margin-top: 10px; padding: 2rem;">
        <div class="header-menu">
          <span class="title-menu" style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; overflow: hidden;">${course.course_name.trim()} </span>
          <i class="fas fa-chevron-right"></i>
        </div>
        <div class="usage-menu">
          <div class="progress-section">
            <div class="progress-header-flex">${courseCover}</div>
          </div>
        </div>
      </div>
    </div>
  `;
}
function redirectCurreculum(course_id, course_type, classroom_id) {
  let new_path = course_type + "_" + course_id;
  let url = `/classroom/study/redirect.php?id=${window.btoa(new_path)}&cid=${window.btoa(classroom_id)}`;
  window.open(url, '_blank');
}