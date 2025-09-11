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
        renderCourses(result);  // clear naming confusion
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
        console.log(result);
        if (Array.isArray(result)) {
            renderClass(result);
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
  const container = document.querySelector(".course-class"); // replace appropriate container

  container.innerHTML = ""; // clear before adding

  courses.forEach((course) => {
    console.log("hello");
    container.insertAdjacentHTML("beforeend", renderCourseCard(course));
  });
}

function renderCourses(courses) {
  const container = document.querySelector(".course-class"); // your container selector
  container.innerHTML = "";

  courses.forEach((course) => {
    container.insertAdjacentHTML("beforeend", renderCourseCard(course));
  });
}

function renderCourseCard(course) {
  // Get first character of instructor name for initial fallback
  const initial = course.instructor ? course.instructor.charAt(0) : '';

  const courseCover = course.course_cover 
    ? `<img src="${course.course_cover}" alt="${course.course_name}" style="width:100%; height:auto; margin-bottom: 1rem;">` 
    : '';

  return `
    <div class="row" onclick="loadClass('${course.classroom_id}')"
      <div class="row">
        <div class="container-menu" style="margin-top: 10px; padding: 2rem;">
          <div class="header-menu">
            <span class="title-menu" style=" display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 1;
                overflow: hidden;">
                ${course.classroom_name}</span>
          </div>

          <div class="usage-menu">
            <div class="progress-section">
              <div class="progress-header-flex">
                <span class="progress-text">
                 ${course.classroom_information}
                </span>
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


function renderClass(courses) {
  const container = document.querySelector(".course-class"); // your container selector
  container.innerHTML = "";

  courses.forEach((course) => {
    container.insertAdjacentHTML("beforeend", renderClassCard(course));
  });
}

function renderClassCard(course) {
  const courseCover = course.course_cover 
    ? `<img src="${course.course_cover}" alt="${course.course_name}" style="width:100%; height:auto; margin-bottom: 1rem;">` 
    : '';

   

  return `
    <div class="row" onclick="redirectCurreculum('${course.course_id}', '${course.course_type}')">
      <div class="container-menu" style="margin-top: 10px; padding: 2rem;">
        <div class="header-menu">
          <span class="title-menu" style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; overflow: hidden;">
            ${course.course_name.trim()}
          </span>
        </div>

        <div class="usage-menu">
          <div class="progress-section">
            <div class="progress-header-flex">
              ${courseCover}
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

function redirectCurreculum(course_id, course_type) {
    let new_path = course_id + "_" + course_type;
    let url = `/academy/redirect.php?id=${window.btoa(new_path)}`;
    window.location.href = url; // Use this to actually redirect the page
}



