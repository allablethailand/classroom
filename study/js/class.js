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

function renderCurriculum(courses) {
  const container = document.querySelector(".course-curreculum");
  container.innerHTML = "";
  courses.forEach((course) => {
    container.insertAdjacentHTML("beforeend", renderCourseCard(course));
  });
}

// ${course.classroom_id}

function renderCourseCard(course) {
  const initial = course.instructor ? course.instructor.charAt(0) : "";
  const courseCover = course.course_cover
    ? `<img src="${course.course_cover}" alt="${course.course_name}" class="transparent-bg" style="width: 50px; height: 50px; border-radius: 100%;">`
    : "";

  const courseCoverGT = ` <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="" style="width: 50px; height: 50px; border-radius: 100%;" >`
  return `
    
<div class="row" onclick="loadClass('${course.classroom_id}')">
        <a href="classinfo" class="container-menu" style="margin-top: 10px; padding: 2rem;">
        <div class="flex-box-container">
          <div class="header-menu">
            <div class="img-banner">
               ${courseCoverGT}
            </div>
            <div class="class-menu">
            <span class="title-menu" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; overflow: hidden;">${course.classroom_name}</span>
              <div class="progress-section">
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
        </a>
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
                onerror="this.src='/images/origami-academy-logo.png'; this.style.width='30px'; this.style.height='30px'; this.style.objectFit='scale-down';" 
                alt="${name.trim()}" 
                style="width: 30px; height: 30px; border-radius: 100%; object-fit: fill; border: 3px solid orange;">
          </div>`;
      });
    } else {
      // Show only first 3
      namesArray.slice(0, maxVisible).forEach(name => {
        instructorsHtml += `
          <div class="member-avatar avatar-orange" title="${name.trim()}">
            <img src="" 
                onerror="this.src='/images/origami-academy-logo.png'; this.style.width='30px'; this.style.height='30px'; this.style.objectFit='scale-down';" 
                alt="${name.trim()}" 
                style="width: 30px; height: 30px; border-radius: 100%; object-fit: fill; border: 3px solid orange;">
          </div>`;
      });

      // Add count +N for the remaining
      const remainingCount = namesArray.length - maxVisible;
      instructorsHtml += `
        <div class="member-avatar avatar-orange" title="and ${remainingCount} more">
          <div class="avatar-counter" style="margin-left: 1rem; width: 30px; height: 30px; border-radius: 100%; background-color: #f80; color: white; display: flex; justify-content: center; align-items: center; font-weight: bold;">
            +${remainingCount}
          </div>
        </div>`;
    }
  } else {

    instructorsHtml += `
      <div class="member-avatar avatar-orange" title="more" style="display: flex;">
        <img src="/${courseInstr}" alt="Instructor" class="instructor-photo" onerror="this.src='/images/origami-academy-logo.png'" style="border: 3px solid orange;" />
      </div>`;
  }

  // <div class="avatar-counter" style="width: 30px; height: 30px; border-radius: 100%; background-color: #f80; color: white; display: flex; justify-content: center; align-items: center; font-weight: bold;">
  // </div>


  return `
    <div class="row" onclick="loadClass('${course.classroom_id}')">
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
                <div class="location-info" style="margin-left: 0.5rem;">
                  สถานที่: ${courseLoca}
                </div>
                 <div class="time-schedule-class" style="margin-left: 0.5rem;">
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

// function renderCurriculum(courses) {
//   const safeText = (text) => text ? text : "ไม่ระบุ";
//   const courseCover = course.course_cover
//     ? `<img src="/${course.course_cover}" alt="${course.course_name}" style="width: 70px; height: 70px; margin-bottom: 1rem; border-radius: 10px;" onerror="this.src='/images/training.jpg'">`
//     : "";

//   let instructorsHtml = "";
//   const courseLoca = safeText(course.course_location);

//   return `<div class="row" onclick="redirectCurreculum('${course.course_id}', '${course.course_type}', '${course.classroom_id}')">
//     <div class="academy-option-table el-row">
//     <div class="academy-length-table el-col el-col-24 el-col-xs-12 el-col-lg-6">
//       <div class="el-row" style="margin-left: -20px; margin-right: -20px;">
//         <div class="el-col el-col-24 el-col-xs-24 el-col-lg-2 el-col-lg-offset-1" style="padding-left: 20px; padding-right: 20px;">
//           <label class="label-input">Show</label>
//         </div>
//         <div class="form-inline-input el-col el-col-24 el-col-lg-8" style="padding-left: 20px; padding-right: 20px; margin-bottom: 10px;">
//           <div style="width: 100%;">
//             <div>
//               <select id="select-2-23" class="form-control select2-hidden-accessible" style="width: 100%;" data-select2-id="select-2-23" tabindex="-1" aria-hidden="true">
//                 <option value="10" data-select2-id="2">10</option>
//                 <option value="25">25</option>
//                 <option value="50">50</option>
//                 <option value="100">100</option>
//               </select>
//               <span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="1" style="width: 100%;">
//                 <span class="selection">
//                   <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-select-2-23-container">
//                     <span class="select2-selection__rendered" id="select2-select-2-23-container" role="textbox" aria-readonly="true" title="10">
//                       <span lang="en">10</span>
//                     </span>
//                     <span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span>
//                   </span>
//                 </span>
//                 <span class="dropdown-wrapper" aria-hidden="true"></span>
//               </span>
//             </div>
//           </div>
//         </div>
//       </div>
//     </div>

//     <div class="academy-filter-table el-col el-col-24 el-col-xs-36 el-col-lg-18">
//       <div class="el-row" style="margin-left: -10px; margin-right: -10px;">
        
//         <div class="el-col el-col-24 el-col-xs-24 el-col-lg-6" style="padding-left: 10px; padding-right: 10px; margin-bottom: 10px;">
//           <div class="el-date-editor el-range-editor el-input__inner el-date-editor--daterange">
//             <i class="el-input__icon el-range__icon el-icon-date"></i>
//             <input autocomplete="off" name="" class="el-range-input" fdprocessedid="xody3i">
//             <span class="el-range-separator">-</span>
//             <input autocomplete="off" name="" class="el-range-input" fdprocessedid="x16dnr">
//             <i class="el-input__icon el-range__close-icon"></i>
//           </div>
//         </div>

//         <div class="el-col el-col-24 el-col-xs-24 el-col-lg-4" style="padding-left: 10px; padding-right: 10px; margin-bottom: 10px;">
//           <div>
//             <select id="select-2-29" class="form-control select2-hidden-accessible" style="width: 100%;" data-select2-id="select-2-29" tabindex="-1" aria-hidden="true">
//               <option value="all" data-select2-id="4">All Course Level</option>
//             </select>
//             <span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="3" style="width: 100%;">
//               <span class="selection">
//                 <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-select-2-29-container">
//                   <span class="select2-selection__rendered" id="select2-select-2-29-container" role="textbox" aria-readonly="true" title="All Course Level">
//                     <span lang="en">All Course Level</span>
//                   </span>
//                   <span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span>
//                 </span>
//               </span>
//               <span class="dropdown-wrapper" aria-hidden="true"></span>
//             </span>
//           </div>
//         </div>

//         <div class="el-col el-col-24 el-col-xs-24 el-col-lg-4" style="padding-left: 10px; padding-right: 10px; margin-bottom: 10px;">
//           <div>
//             <select id="select-2-31" class="form-control select2-hidden-accessible" style="width: 100%;" data-select2-id="select-2-31" tabindex="-1" aria-hidden="true">
//               <option value="all" data-select2-id="6">All Categories</option>
//             </select>
//             <span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="5" style="width: 100%;">
//               <span class="selection">
//                 <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-select-2-31-container">
//                   <span class="select2-selection__rendered" id="select2-select-2-31-container" role="textbox" aria-readonly="true" title="All Categories">
//                     <span lang="en">All Categories</span>
//                   </span>
//                   <span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span>
//                 </span>
//               </span>
//               <span class="dropdown-wrapper" aria-hidden="true"></span>
//             </span>
//           </div>
//         </div>

//         <div class="el-col el-col-24 el-col-xs-24 el-col-lg-4" style="padding-left: 10px; padding-right: 10px; margin-bottom: 10px;">
//           <div>
//             <select id="select-2-33" class="form-control select2-hidden-accessible" style="width: 100%;" data-select2-id="select-2-33" tabindex="-1" aria-hidden="true">
//               <option value="all" data-select2-id="8">All Type</option>
//               <option value="1">Course</option>
//               <option value="2">Learning Map</option>
//               <option value="3">OA Course</option>
//             </select>
//             <span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="7" style="width: 100%;">
//               <span class="selection">
//                 <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-select-2-33-container">
//                   <span class="select2-selection__rendered" id="select2-select-2-33-container" role="textbox" aria-readonly="true" title="All Type">
//                     <span lang="en">All Type</span>
//                   </span>
//                   <span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span>
//                 </span>
//               </span>
//               <span class="dropdown-wrapper" aria-hidden="true"></span>
//             </span>
//           </div>
//         </div>

//         <div class="el-col el-col-24 el-col-xs-24 el-col-lg-6" style="padding-left: 10px; padding-right: 10px; margin-bottom: 10px;">
//           <input type="text" placeholder="Search" class="form-control" fdprocessedid="eel6a">
//         </div>

//       </div>
//     </div>
//   </div>
//   </div>`;
// }


function redirectCurreculum(course_id, course_type, classroom_id) {
  let new_path = course_type + "_" + course_id;
  let url = `/classroom/study/redirect.php?id=${window.btoa(
    new_path
  )}&cid=${window.btoa(classroom_id)}`;
  window.open(url, "_self");
}
