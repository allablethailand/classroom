let classroom_id;
let tenant_key = '';
let currentLang = "th";
let consent_status = 'N';
let channel_id = '';
const translations = {
    en: {
        eng: "English", thai: "Thai", register: "Register", infomation: "Details",
        contact_us: "Contact Information", registration_form: "Registration Form",
        idcard: "ID Card", passport: "Passport", prefix: "Prefix",
        mr: "Mr.", mrs: "Mrs.", miss: "Miss", other: "Other",
        male: "Male", female: "Female",
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
        please_login: "Please proceed by clicking the \"Login\" button below.",
        accept_register: "Accept and register",
        copy_of_idcard: "Copy of ID card",
        copy_of_passport: "Passport",
        work_certificate: "Work certificate",
        company_certificate: "Company Certificate (for business owners)",
        support_upload: "Supports only image files or PDF.",
    },
    th: {
        eng: "อังกฤษ", thai: "ไทย", register: "ลงทะเบียน", infomation: "รายละเอียด",
        contact_us: "ข้อมูลการติดต่อ", registration_form: "แบบฟอร์มลงทะเบียน",
        idcard: "รหัสประจำตัวประชาชน", passport: "พาสปอร์ต", prefix: "คำนำหน้าชื่อ",
        mr: "นาย", mrs: "นาง", miss: "นางสาว", other: "อื่นๆ",
        male: "ชาย", female: "หญิง",
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
        please_login: "กรุณาดำเนินการต่อโดยคลิกปุ่ม \"เข้าสู่ระบบ\" ด้านล่าง",
        accept_register: "ยอมรับและลงทะเบียน",
        copy_of_idcard: "สำเนาบัตรประชาชน",
        copy_of_passport: "หนังสือเดินทาง",
        work_certificate: "หนังสือรับรองการทำงาน",
        company_certificate: "หนังสือรับรองของบริษัท (สำหรับเจ้าของกิจการ)",
        support_upload: "รองรับไฟล์รูปภาพหรือ pdf เท่านั้น",
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
                    confirmButtonColor: '#FF9900'
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
            swal({ 
                type: 'warning', 
                title: "Warning...", 
                text: msg, 
                confirmButtonColor: '#FF9900'
            });
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
    const channel = $("#channel").val();
    $.post('/classroom/register/actions/register.php', { 
        action: "verifyClassroom", 
        classroomCode: classroomCode, 
        channel: channel
    }, (response) => {
        if (!response.status) return window.location.href = '/';
        classroom_id = response.classroom_data.classroom_id;
        channel_id = response.channel_id;
        tenant_key = response.classroom_data.tenant_key;
        response.register_template.forEach(value => $(".input-" + value).removeClass("hidden"));
        response.register_require.forEach(value => {
            const $group = $(".input-" + value);
            $group.find("input, select, textarea").addClass("require");
            $group.find("input[type=file]").addClass("require");
            $group.find("label").addClass("required-field");
        });
        initTemplate(response.classroom_data);
        initForm(response.form_data);
        consent_status = response.consent_status;
        if(consent_status == 'Y') {
            $(".input-consent").removeClass("hidden");
        }
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
            if ($field.attr("id") === "student_idcard") {
                const val = $field.val().trim();
                if (!isValidThaiID(val)) {
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $field;
                    $field.addClass("has-error");
                    $group.find("label").addClass("has-error-text");
                } else {
                    $field.removeClass("has-error");
                    $group.find("label").removeClass("has-error-text");
                }
                return;
            }
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
        $(".form-container .form-group").each(function() {
            const $group = $(this);
            const $question = $group.find("[name^='q_']");
            if ($question.length === 0) return;
            const required = $group.find("[data-required='1']").length > 0 || $group.find(".require").length > 0;
            if (!required) return;
            if ($question.is(":radio") || $question.is(":checkbox")) {
                const name = $question.attr("name");
                const $checked = $(`[name='${name}']:checked`);
                if ($checked.length === 0) {
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = $group;
                    $group.find(".question_text").addClass("has-error-text");
                } else {
                    $group.removeClass("has-error");
                    const $other = $checked.filter("[value='other']");
                    if ($other.length > 0) {
                        const targetBox = $("#other_box_" + $other.data("qid"));
                        const $otherInput = targetBox.find("input[type='text']");
                        if ($otherInput.length && $otherInput.val().trim() === "") {
                            isValid = false;
                            if (!firstInvalidField) firstInvalidField = $otherInput;
                            $otherInput.addClass("has-error");
                        } else {
                            $otherInput.removeClass("has-error");
                        }
                    }
                }
            } else {
                $question.each(function() {
                    if ($(this).val().trim() === "") {
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = $(this);
                        $(this).addClass("has-error");
                    } else {
                        $(this).removeClass("has-error");
                    }
                });
            }
        });
        if (!isValid) {
            if (firstInvalidField) {
                $('html, body').animate({ scrollTop: firstInvalidField.offset().top - 100 }, 300);
                firstInvalidField.focus();
            }
            swal({
                type: 'warning',
                title: 'Warning',
                text: 'Please fill in all required fields. ' + errorMessage,
                confirmButtonColor: '#FF9900'
            });
            return false;
        }
        if (!$('#agree').is(':checked') && consent_status == "Y") {
            $("#agree").prop("checked", true);
            $(".systemModal").modal();
            $(".systemModal .modal-header").html('<h5 class="modal-title" data-lang="policy"></h5>');
            $(".systemModal .modal-footer").html(`
                <button type="button" class="btn btn-warning" data-lang="accept_register" onclick="saveRegister();"></button>
                <button type="button" class="btn" data-lang="close" data-dismiss="modal"></button>
            `);
            $.post('/classroom/register/actions/register.php', { action: "loadTerm", classroom_id }, (res) => {
                $(".systemModal .modal-body").html(res.classroom_consent || '-');
            }, 'json');
            return;
        }
        saveRegister();
    });
    $(".input-file").change(function() {
        var file = this.files[0];
        if (file) {
            var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
            if (allowedTypes.indexOf(file.type) === -1) {
                swal({
                    type: 'warning',
                    title: "Warning",
                    text: "Please select only an image or PDF file.",
                    confirmButtonColor: '#FF9900'
                });
                $(this).val('');
            }
        }
    });
    $("#student_idcard").on("blur", function() {
        var $this = $(this);
        var id = $this.val().trim();
        if (id.length === 0) return;
        if (!isValidThaiID(id)) {
            $this.addClass("has-error");
            swal({
                type: 'warning',
                title: 'Warning',
                text: 'Invalid ID card number',
                confirmButtonColor: '#FF9900'
            });
            setTimeout(function() {
                $this.focus();
            }, 0);
        } else {
            $this.removeClass("has-error");
        }
    });
    $("#student_perfix").on("change", function() {
        if($(this).val() == "Other") {
            $(".prefix-other").removeClass("hidden");
        } else {
            $(".prefix-other").addClass("hidden");
        }
    });
});
function isValidThaiID(id) {
    if (!/^\d{13}$/.test(id)) return false;
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(id.charAt(i)) * (13 - i);
    }
    let checkDigit = (11 - (sum % 11)) % 10;
    return checkDigit === parseInt(id.charAt(12));
}
function saveRegister() {
    const $form = $('#registrationForm');
    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);
    const fd = new FormData($form[0]);
    fd.append('classroom_id', classroom_id);
    const dialCode = $(".iti__selected-dial-code").html();
    fd.append('dialCode', dialCode);
    fd.append('channel_id', channel_id);
    $.ajax({
        url: '/classroom/register/actions/register.php?action=saveRegister',
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: handleRegisterResponse,
        error: () => {
            $(".systemModal").modal("hide");
            $btn.prop('disabled', false);
            swal({
                type: 'warning',
                title: "Warning",
                text: "Save faild, please try again.",
                confirmButtonColor: '#FF9900'
            });
        }
    });
}
function handleRegisterResponse(result) {
    $(".loader").removeClass("active");
    $(".systemModal").modal("hide");
    if (!result.status) {
        $(".btn-register").prop('disabled', false);
        swal({ 
            type: 'warning', 
            title: "Duplicate Data", 
            text: result.message, 
            confirmButtonColor: '#FF9900'
        });
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
function initForm(form_data) {
    if(!form_data) return;
    let html = '';
    form_data.forEach(q => {
        html += `<div class="form-group">
            <input type="hidden" name="question_id[]" value="${q.question_id}">
            <input type="hidden" name="question_type[]" value="${q.question_type}">
        `;
        html += `<label class="${q.has_required == 1 ? 'required-field' : ''} question_text">${q.question_text}</label>`;
        let requiredAttr = q.has_required == 1 ? 'data-required="1"' : '';
        if(q.has_options == 1) {
            if(q.question_type === "radio" || q.question_type === "multiple_choice") {
                q.option_item.forEach(opt => {
                    html += `
                        <div>
                            <input type="radio" id="q_${q.question_id}_opt_${opt.choice_id}" name="q_${q.question_id}" value="${opt.choice_id}" class="option-input" data-qid="${q.question_id}" ${requiredAttr}>
                            <label for="q_${q.question_id}_opt_${opt.choice_id}" class="radio-label">${opt.choice_text}</label>
                        </div>
                    `;
                });
            } else if(q.question_type === "checkbox") {
                q.option_item.forEach(opt => {
                    html += `
                        <div>
                            <input type="checkbox" id="q_${q.question_id}_opt_${opt.choice_id}" name="q_${q.question_id}[]" value="${opt.choice_id}" class="option-input" data-qid="${q.question_id}" ${requiredAttr}>
                            <label for="q_${q.question_id}_opt_${opt.choice_id}" class="checkbox-label">${opt.choice_text}</label>
                        </div>
                    `;
                });
            }
            if(q.has_other_option == 1) {
                const inputType = (q.question_type === 'checkbox' ? 'checkbox' : 'radio');
                const inputName = `q_${q.question_id}${inputType === 'checkbox' ? '[]' : ''}`;
                html += `
                    <div>
                        <input type="${inputType}" id="q_${q.question_id}_other" name="${inputName}" value="other" class="option-input other-input" data-qid="${q.question_id}" ${requiredAttr}>
                        <label for="q_${q.question_id}_other" class="${inputType==='checkbox' ? 'checkbox-label':'radio-label'}"><span data-lang="other">อื่นๆ</span></label>
                    </div>
                    <div id="other_box_${q.question_id}" style="display:none; margin-top:5px;">
                        <input type="text" class="form-control" name="q_${q.question_id}_other">
                    </div>
                `;
            }
        } else {
            if(q.question_type === "short_answer") {
                html += `<textarea name="q_${q.question_id}" class="form-control" ${requiredAttr}></textarea>`;
            }
        }
        html += `</div>`;
    });
    document.querySelector(".form-container").innerHTML = html;
    if(typeof toggleLanguage === "function") toggleLanguage(currentLang);
    document.querySelectorAll('.option-input').forEach(input => {
        input.addEventListener('change', function() {
            const qid = this.dataset.qid;
            const otherBox = document.getElementById(`other_box_${qid}`);
            const otherInput = otherBox ? otherBox.querySelector("input") : null;
            if(this.type === "radio") {
                if(this.value === "other" && this.checked) {
                    otherBox.style.display = "block";
                    if(otherInput) otherInput.setAttribute("data-required","1");
                } else {
                    if(otherBox) otherBox.style.display = "none";
                    if(otherInput) otherInput.removeAttribute("data-required");
                }
            } else if(this.type === "checkbox") {
                if(this.value === "other") {
                    otherBox.style.display = this.checked ? "block" : "none";
                    if(otherInput) {
                        if(this.checked) otherInput.setAttribute("data-required","1");
                        else otherInput.removeAttribute("data-required");
                    }
                }
            }
        });
    });
}
function autoResize(textarea) {
    textarea.style.height = 'auto'; 
    textarea.style.height = textarea.scrollHeight + 'px'; 
}
function initTemplate(data) {
    let bg = (data.classroom_bg != '') ? data.classroom_bg : "/images/ogm_bg.png";
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