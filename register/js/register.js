let classroom_id;
let tenant_key = '';
let currentLang = "th";
const translations = {
    en: {
        eng: "English", thai: "Thai", register: "Register", infomation: "Details",
        contact_us: "Contact Information", registration_form: "Registration Form",
        idcard: "ID Card", passport: "Passport", prefix: "Prefix",
        mr: "Mr.", mrs: "Mrs.", miss: "Miss", other: "Other",
        firstname_en: "First Name (EN)", lastname_en: "Last Name (EN)",
        firstname_th: "First Name (TH)", lastname_th: "Last Name (TH)",
        nickname_en: "Nickname (EN)", nickname_th: "Nickname (TH)",
        gender: "Gender", birthday: "Birthday", upload_image: "Upload Image",
        email: "Email", mobile: "Mobile Number", company: "Company", position: "Position",
        username: "Username", password: "Password",
        i_accept: "I accept", policy: "Terms and Conditions & Privacy Policy",
        password_info: "Password must be 4–20 characters, using only English letters or numbers.",
        username_info: "Username must be 8–20 characters, consisting of English letters or numbers only. (By default, your registered mobile number will be used)",
        close: "Close", accept: "Accept", already: "Already have an account?", login: "Log in",
        registered: "Successfully registered.", registered_success: "Your registration has been submitted successfully.",
        however: "However, your account is currently pending approval from our administrator.",
        once: "Once your registration is reviewed and approved, you will receive a notification via email.",
        registered_login: "You can now log in to the system using your registered username and password.",
        please_login: "Please proceed by clicking the \"Login\" button below."
    },
    th: {
        eng: "อังกฤษ", thai: "ไทย", register: "ลงทะเบียน", infomation: "รายละเอียด",
        contact_us: "ข้อมูลการติดต่อ", registration_form: "แบบฟอร์มลงทะเบียน",
        idcard: "รหัสประจำตัวประชาชน", passport: "พาสปอร์ต", prefix: "คำนำหน้าชื่อ",
        mr: "นาย", mrs: "นาง", miss: "นางสาว", other: "อื่นๆ",
        firstname_en: "ชื่อ (ภาษาอังกฤษ)", lastname_en: "นามสกุล (ภาษาอังกฤษ)",
        firstname_th: "ชื่อ (ภาษาไทย)", lastname_th: "นามสกุล (ภาษาไทย)",
        nickname_en: "ชื่อเล่น (ภาษาอังกฤษ)", nickname_th: "ชื่อเล่น (ภาษาไทย)",
        gender: "เพศ", birthday: "วันเกิด", upload_image: "อัพโหดลรูปภาพ",
        email: "อีเมล", mobile: "หมายเลขโทรศัพท์มือถือ", company: "บริษัท", position: "ตำแหน่ง",
        username: "ชื่อผู้ใช้", password: "รหัสผ่าน",
        i_accept: "ข้าพเจ้ายอมรับ", policy: "ข้อกำหนดและเงื่อนไข รวมถึงนโยบายความเป็นส่วนตัว",
        password_info: "รหัสผ่านต้องมีความยาว 4–20 ตัวอักษร และใช้ได้เฉพาะตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น",
        username_info: "ชื่อผู้ใช้ต้องมีความยาว 8–20 ตัวอักษร และประกอบด้วยตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น (ค่าเริ่มต้นคือหมายเลขโทรศัพท์มือถือที่ท่านกรอกไว้)",
        close: "ปิด", accept: "ยอมรับ", already: "มีบัญชีผู้ใช้อยู่แล้ว?", login: "เข้าสู่ระบบ",
        registered: "ลงทะเบียนสำเร็จ", registered_success: "การลงทะเบียนของคุณเรียบร้อยแล้ว",
        however: "อย่างไรก็ตาม บัญชีของคุณกำลังรอการอนุมัติจากผู้ดูแลระบบของเรา",
        once: "เมื่อการลงทะเบียนของคุณได้รับการตรวจสอบและอนุมัติแล้ว คุณจะได้รับการแจ้งเตือนทางอีเมล",
        registered_login: "คุณสามารถเข้าสู่ระบบโดยใช้ชื่อผู้ใช้และรหัสผ่านที่คุณลงทะเบียนไว้ได้แล้ว",
        please_login: "กรุณาดำเนินการต่อโดยคลิกปุ่ม \"เข้าสู่ระบบ\" ด้านล่าง"
    }
};
$(document).ready(function () {
    function toggleScrollBtn() {
        if ($(window).width() > 767) return $('#scrollToFormBtn').hide();
        const formOffset = $('#registration-form').offset().top;
        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        $('#scrollToFormBtn').toggle(scrollTop + windowHeight < formOffset + 100);
    }
    $(window).on('scroll resize', toggleScrollBtn);
    toggleScrollBtn();
    $('#scrollToFormBtn').click(() => {
        $('html, body').animate({ scrollTop: $('#registration-form').offset().top - 20 }, 500);
    });
    const defaultProfile = "/images/default-profile.png";
    $(".profile-upload img, .profile-upload .camera-icon").on("click", function() {
        $("#student_image_profile").click();
    });
    $("#student_image_profile").on("change", function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            if (!file.type.startsWith("image/")) {
                swal({
                    type: 'warning',
                    title: 'Warning',
                    text: 'Please select a valid image file.',
                    showConfirmButton: false,
                });
                $(this).val("");
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#profilePreview").attr("src", e.target.result);
                $("#removeProfile").show();
            }
            reader.readAsDataURL(file);
        }
    });
    $("#removeProfile").on("click", function() {
        $("#student_image_profile").val("");
        $("#profilePreview").attr("src", "/images/profile-default.jpg");
        $(this).hide();
    });
    $("#removeProfile").on("click", function () {
        $("#student_image_profile").val("");
        $("#profilePreview").attr("src", defaultProfile);
        $(this).hide();
    });
    $('.datepicker').datepicker({
        dateFormat: 'yy/mm/dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        autoclose: true
    });
    $("#student_idcard, #student_mobile").on("keypress", function (e) {
        if (e.which < 48 || e.which > 57) e.preventDefault();
    });
    $(document).on("input", "[id$='_en']", function () {
        this.value = this.value.replace(/[^A-Za-z\s]/g, "");
    });
    $(document).on("input", "[id$='_th']", function () {
        this.value = this.value.replace(/[^ก-๙\s]/g, "");
    });
    $("#student_username").on("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9]/g, "");
    });
    $("#student_password").on("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9!@#$%^&*()_\+\-=\[\]{};:'",.<>\/?]/g, "");
    });
    $("#togglePassword").on("click", function () {
        const $pwd = $("#student_password");
        const type = $pwd.attr("type") === "password" ? "text" : "password";
        $pwd.attr("type", type);
        $(this).html(`<i class="fa fa-${type === 'text' ? 'eye-slash' : 'eye'}"></i>`);
    });
    const validators = {
        student_email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        student_mobile: /^[0-9]{9,}$/
    };
    $('#student_email, #student_mobile').on('blur', function () {
        let val = $(this).val().trim();
        const type = $(this).attr('id');
        if (!validators[type]) return;
        if (val && !validators[type].test(val)) {
            $(this).addClass('has-error');
            const msg = type === 'student_email'
                ? 'Invalid email format'
                : 'The phone number format is incorrect.';
            swal({ type: 'warning', title: "Warning...", text: msg, showConfirmButton: false, timer: 2500 });
        } else {
            $(this).removeClass('has-error');
            if (type === 'student_mobile') {
                let usernameMobile = val;
                if (val.startsWith("0")) {
                    val = val.replace(/^0/, "");
                    $(this).val(val);
                } else if (val.length > 0) {
                    usernameMobile = "0" + val;
                }
                $("#student_username").val(val ? usernameMobile : "");
            }
        }
    });
    const input = document.querySelector("#student_mobile");
    const iti = window.intlTelInput(input, {
        initialCountry: "th",
        separateDialCode: true,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });
    const classroomCode = $("#classroomCode").val();
    $.post('/classroom/register/actions/register.php', { action: "verifyClassroom", classroomCode }, (response) => {
        if (!response.status) return window.location.href = '/';
        classroom_id = response.classroom_data.classroom_id;
        tenant_key = response.classroom_data.tenant_key;
        response.register_template.forEach(value => $(".input-" + value).removeClass("hidden"));
        response.register_require.forEach(value => {
            const $group = $(".input-" + value);
            $group.find("input, select, textarea").addClass("require");
            $group.find("input[type=file]").addClass("require");
            if ($group.find("label .require-mark").length === 0) {
                $group.find("label").append(' <span class="require-mark" style="color:orange">*</span>');
            }
        });
        initTemplate(response.classroom_data);
        $(".login").attr("href", "/" + tenant_key);
    }, 'json').fail(() => window.location.href = '/');
    $(".language-menu div").click(function () {
        const lang = $(this).attr("store-translate");
        if (lang) toggleLanguage(lang.toLowerCase());
    });
    toggleLanguage(currentLang);
    $(".open-term").click(function () {
        $(".systemModal").modal();
        $(".systemModal .modal-header").html('<h5 class="modal-title" data-lang="policy"></h5>');
        $(".systemModal .modal-footer").html(`
            <button type="button" class="btn btn-warning accept-term" data-lang="accept"></button>
            <button type="button" class="btn" data-lang="close" data-dismiss="modal"></button>
        `);
        $.post('/classroom/register/actions/register.php', { action: "loadTerm", classroom_id }, (res) => {
            $(".systemModal .modal-body").html(res.classroom_consent || '-');
        }, 'json');
    });
    $(document).on("click", ".accept-term", () => {
        $("#agree").prop("checked", true);
        $(".systemModal").modal("hide");
    });
    $(document).on('shown.bs.modal', '.modal', () => toggleLanguage(currentLang));
    $(".btn-register").click(function(e) {
        e.preventDefault();
        let isValid = true;
        let firstInvalidField = null;
        $(".require").each(function(){
            const $field = $(this);
            const $group = $field.closest(".form-group");
            if ($group.hasClass("hidden")) return;
            let val = $field.val();
            if ($field.attr("type") === "file") {
                if ($field[0].files.length === 0) {
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $field;
                    $group.find(".profile-upload").css("border", "2px solid orange");
                } else {
                    $group.find(".profile-upload").css("border", "2px solid #ddd");
                }
            } else if (!$field.is(":checkbox") && !$field.is(":radio")) {
                if (!val || val.trim() === "") {
                    $field.addClass("has-error");
                    if (!firstInvalidField) firstInvalidField = $field;
                    isValid = false;
                } else {
                    $field.removeClass("has-error");
                }
            } else if ($field.is(":checkbox") || $field.is(":radio")) {
                const name = $field.attr("name");
                if ($(`[name='${name}']:checked`).length === 0) {
                    $field.addClass("has-error");
                    if (!firstInvalidField) firstInvalidField = $field;
                    isValid = false;
                } else {
                    $field.removeClass("has-error");
                }
            }
        });
        let errorMessage = '';
        const $username = $("#student_username");
        if (!$username.closest(".form-group").hasClass("hidden")) {
            const usernameVal = $username.val().trim();
            if ($username.hasClass("require") || usernameVal !== "") {
                if (usernameVal.length < 4 || usernameVal.length > 20) {
                    $username.addClass("has-error");
                    if (!firstInvalidField) firstInvalidField = $username;
                    isValid = false;
                    errorMessage + 'Username';
                } else {
                    $username.removeClass("has-error");
                }
            } else {
                $username.removeClass("has-error");
            }
        }
        const $password = $("#student_password");
        if (!$password.closest(".form-group").hasClass("hidden")) {
            const passwordVal = $password.val().trim();
            if ($password.hasClass("require") || passwordVal !== "") {
                if (passwordVal.length < 4 || passwordVal.length > 20) {
                    $password.addClass("has-error");
                    if (!firstInvalidField) firstInvalidField = $password;
                    isValid = false;
                    errorMessage + ' Password';
                } else {
                    $password.removeClass("has-error");
                }
            } else {
                $password.removeClass("has-error");
            }
        }
        if(errorMessage) {
            errorMessage += 'must be 4–20 characters if provided. ';
        }
        if (!isValid) {
            if (firstInvalidField) {
                $('html, body').animate({ scrollTop: firstInvalidField.offset().top - 100 }, 300);
                firstInvalidField.focus();
            }
            swal({
                type: 'warning',
                title: 'Warning',
                text: 'Please fill in all required fields. ' + errorMessage,
                showConfirmButton: false,
                timer: 2500
            });
            return false;
        }
        if (!$('#agree').is(':checked')) {
             swal({
                type: 'warning',
                title: 'Warning',
                text: 'Please accept the terms and conditions.',
                showConfirmButton: false,
                timer: 2500
            });
            return false;
        }
        saveRegister();
    });
});
function saveRegister() {
    const $form = $('#registrationForm');
    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);
    const fd = new FormData($form[0]);
    fd.append('classroom_id', classroom_id);
    const dialCode = $(".iti__selected-dial-code").html();
    fd.append('dialCode', dialCode);
    $.ajax({
        url: '/classroom/register/actions/register.php?action=saveRegister',
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: handleRegisterResponse,
        error: () => {
            $btn.prop('disabled', false);
            swal({
                type: 'warning',
                title: "Warning",
                text: "Save faild, please try again.",
                showConfirmButton: false,
                timer: 2500
            });
        }
    });
}
function handleRegisterResponse(result) {
    $(".loader").removeClass("active");
    if (!result.status) {
        swal({ type: 'warning', title: "Duplicate Data", text: result.message, timer: 2500, showConfirmButton: false, });
        return;
    }
    $(".systemModal .modal-header").html(`<h5 class="modal-title" data-lang="registered"></h5>`);
    $(".systemModal .modal-body").html(`
        <div class="text-center">
            <img src="/images/origami-academy.png" onerror="this.src='/images/origami-academy.png'" style="height: 100px;">
            <h5 class="text-success" data-lang="registered_success"></h5>
            <p data-lang="however"></p>
            <p data-lang="once"></p>
        </div>
    `);
    $(".systemModal .modal-footer").html(`
        <div class="text-center">
            <button type="button" class="btn btn-default close-register" data-lang="close"></button>
        </div>
    `);
    $(".systemModal").modal();
    toggleLanguage(currentLang);
    $(".close-register").off("click").on("click", function() {
        location.reload();
    });
}
function initTemplate(data) {
    let bg = (data.classroom_bg != '') ? data.classroom_bg : "/images/bg.jpg";
    $(".poster-bg").css("background-image",`url(${bg})`);
    $(".poster-img img").attr("src", data.classroom_poster || "/images/training.jpg");
    $(".container-header-bg").css("background-image",`url(${bg})`);
    $(".container-header-logo img").attr("src", data.comp_logo);
    const sheet = document.styleSheets[0];
    const rule = `
        .poster-container::before {
            background: url("${data.classroom_poster}") no-repeat center center fixed;
        }
    `;
    sheet.insertRule(rule, sheet.cssRules.length);
    $('.poster-container').css({
        '-webkit-background-size': 'cover',
        '-moz-background-size': 'cover',
        '-o-background-size': 'cover',
        'background-size': 'cover',
    });
    $(".classroom-name").text(data.classroom_name || '');
    const $location = $(".poster-content h5.classroom-location");
    if (data.classroom_type === 'online') {
        $location.html(`Online at ${data.classroom_place || ''}`);
    } else {
        $location.html(`<i class="fas fa-map-marker-alt"></i> ${data.classroom_source || '-'}`);
    }
    $("h5.classroom-date").html(`<i class="far fa-calendar"></i> ${data.classroom_start_date} ${data.classroom_start_time} - ${data.classroom_end_date} ${data.classroom_end_time}`);
    $(".classroom-information").html(data.classroom_information || '');
    $(".contact-us").html(data.contact_us || '');
    $(".comp-logo").attr("src", data.comp_logo || '');
}
function toggleLanguage(lang) {
    currentLang = lang;
    $("[data-lang]").each(function () {
        const key = $(this).attr("data-lang");
        $(this).text(translations[currentLang][key] || key);
    });
    $(".language-menu div").removeClass("lang-active");
    $(".language-menu div[store-translate='" + lang.toUpperCase() + "']").addClass("lang-active");
}