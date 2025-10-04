let classroom_id;
let tenant_key = '';
let currentLang = "th";
let consent_status = 'N';
let channel_id = '';
let is_logged_in = false;
let line_client_id;
let is_result = false;
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
        registered: "Successfully registered.", 
        accept_register: "Accept and register",
        copy_of_idcard: "Copy of ID card",
        copy_of_passport: "Passport",
        work_certificate: "Work certificate",
        company_certificate: "Company Certificate (for business owners)",
        support_upload: "Supports image or PDF files with a size not exceeding 20 MB only.",
        nationality: "Nationality",
        save: "Save",
        logout: "Logout",
        passport_expire: "Passport Expiry Date",
        view: "View",
        delete: "Delete",
        file_preview: "File Preview",
        consent_notice: "Consent Notice",
        consent_paragraph: "This form has been created by the form owner. Any information you submit will be sent directly to the form owner. Allable is not responsible for the privacy practices or actions of third-party form owners. Please avoid submitting personal, sensitive, or confidential information, and never share your password.",
        consent_footer: "Please do not provide personal or sensitive information. Thank you for your understanding!",
        reference: "Person Who Referred You",
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
        registered: "ลงทะเบียนสำเร็จ",
        accept_register: "ยอมรับและลงทะเบียน",
        copy_of_idcard: "สำเนาบัตรประชาชน",
        copy_of_passport: "หนังสือเดินทาง",
        work_certificate: "หนังสือรับรองการทำงาน",
        company_certificate: "หนังสือรับรองของบริษัท (สำหรับเจ้าของกิจการ)",
        support_upload: "รองรับไฟล์รูปภาพหรือ pdf ที่มีขนาดไม่เกิน 20 MB เท่านั้น",
        nationality: "สัญชาติ",
        save: "บันทึกข้อมูล",
        logout: "ออกจากระบบ",
        passport_expire: "วันหมดอายุของพาสปอร์ต",
        view: "ดู",
        delete: "ลบ",
        file_preview: "ตัวอย่างไฟล์",
        consent_notice: "หนังสือแจ้งเพื่อขอความยินยอม",
        consent_paragraph: "แบบฟอร์มนี้ถูกสร้างขึ้นโดยเจ้าของฟอร์ม ข้อมูลใด ๆ ที่คุณส่ง จะถูกส่งไปยังเจ้าของฟอร์มโดยตรง Allable จะไม่รับผิดชอบต่อการปฏิบัติด้านความเป็นส่วนตัวหรือการดำเนินการใด ๆ ของเจ้าของฟอร์มภายนอก โปรดหลีกเลี่ยงการส่งข้อมูลส่วนบุคคล ข้อมูลที่อ่อนไหว หรือข้อมูลลับ และอย่าเปิดเผยรหัสผ่านของคุณโดยเด็ดขาด",
        consent_footer: "กรุณางดให้ข้อมูลส่วนบุคคลหรือข้อมูลที่อ่อนไหว ขอบคุณในความเข้าใจของท่าน",
        reference: "ผู้แนะนำให้มาสมัคร",
    }
};
$(document).ready(function () {
    $("input").attr("autocomplete", "off");
    line_client_id = $("#line_client_id").val();
    is_result = $("#is_result").val();
    if(is_result) {
        showSuccessModal(currentLang);
    }
    $('#registrationForm').on('input change', 'input, textarea, select', updateProgressBar);
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
                if(currentLang == 'en') {
                    swal({
                        type: 'warning',
                        title: 'Warning',
                        text: 'Please select a valid image file.',
                        confirmButtonColor: '#FF9900'
                    });
                } else {
                    swal({
                        type: 'warning',
                        title: 'คำเตือน',
                        text: 'กรุณาเลือกไฟล์รูปภาพที่ถูกต้อง.',
                        confirmButtonColor: '#FF9900'
                    });
                }
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
        autoclose: true,
        maxDate: 0 
    });
    $('.datepicker-past').datepicker({
        dateFormat: 'yy/mm/dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "-20:+20",
        autoclose: true,
    });
    $(document).on("input", "#student_idcard, #student_mobile", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
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
            if(currentLang == 'en') {
                const msg = type === 'student_email' ? 'Invalid email format' : 'The phone number format is incorrect.';
                swal({ 
                    type: 'warning', 
                    title: "Warning...", 
                    text: msg, 
                    confirmButtonColor: '#FF9900'
                });
            } else {    
                const msg = type === 'student_email' ? 'รูปแบบอีเมลไม่ถูกต้อง' : 'รูปแบบหมายเลขโทรศัพท์ไม่ถูกต้อง';
                swal({ 
                    type: 'warning', 
                    title: "คำเตือน...", 
                    text: msg, 
                    confirmButtonColor: '#FF9900'
                }); 
            }
        } else {
            $(this).removeClass('has-error');
            if(type === 'student_email') {
                verifyDuplicateData(val, 'email', 'student_email');
            }
            if(type === 'student_mobile') {
                verifyDuplicateData(val, 'mobile', 'student_mobile');
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
        if (response.register_template.includes("23")) {
            let nationality = response.nationality;
            $("#student_nationality").append($('<option>', { 
                value: nationality.nationality_id, 
                text: nationality.nationality_name 
            }));
        }
        is_logged_in = response.is_logged_in;
        if (response.is_logged_in && response.student_data) {
            $(".after-login").removeClass("hidden");
            const data = response.student_data;
            if (data.student_id) {
                $("input[name='student_id']").val(data.student_id);
                if ($("input[name='student_id']").length === 0) {
                    $("form").append('<input type="hidden" name="student_id" value="' + data.student_id + '">');
                }
            }
            if (data.student_firstname_en) $("#student_firstname_en").val(data.student_firstname_en);
            if (data.student_lastname_en) $("#student_lastname_en").val(data.student_lastname_en);
            if (data.student_nickname_en) $("#student_nickname_en").val(data.student_nickname_en);
            if (data.student_firstname_th) $("#student_firstname_th").val(data.student_firstname_th);
            if (data.student_lastname_th) $("#student_lastname_th").val(data.student_lastname_th);
            if (data.student_nickname_th) $("#student_nickname_th").val(data.student_nickname_th);
            if (data.student_email) $("#student_email").val(data.student_email);
            if (data.student_mobile) $("#student_mobile").val(data.student_mobile);
            if (data.student_company) $("#student_company").val(data.student_company);
            if (data.student_position) $("#student_position").val(data.student_position);
            if (data.student_username) $("#student_username").val(data.student_username);
            if (data.student_birth_date) $("#student_birth_date").val(data.student_birth_date);
            if (data.student_idcard) $("#student_idcard").val(data.student_idcard);
            if (data.student_passport) $("#student_passport").val(data.student_passport);
            if (data.student_passport_expire) $("#student_passport_expire").val(data.student_passport_expire);
            if (data.student_password) $("#student_password").val(data.student_password);
            if (data.student_reference) $("#student_reference").val(data.student_reference);
            if (data.dial_code) {
                $("#dialCode").val(data.dial_code);
                $("select[name='dialCode']").val(data.dial_code);
            }
            if (data.student_gender) {
                $("#student_gender").val(data.student_gender);
                $("select[name='student_gender']").val(data.student_gender).trigger('change');
            }
            if (data.student_perfix) {
                $("#student_perfix").val(data.student_perfix);
                $("select[name='student_perfix']").val(data.student_perfix).trigger('change');
            }
            if (data.student_perfix_other) {
                $("#student_perfix_other").val(data.student_perfix_other);
                $(".input-perfix-other").removeClass("hidden");
            }
            if (data.student_nationality) {
                $("#student_nationality").val(data.student_nationality);
                $("select[name='student_nationality']").val(data.student_nationality);
            }
            if (data.student_image_profile) {
                const $imgPreview = $("#preview_student_image_profile");
                if ($imgPreview.length) {
                    $imgPreview.attr('src', data.student_image_profile).removeClass('hidden').show();
                } else {
                    $("input[name='student_image_profile']").after(
                        '<img id="preview_student_image_profile" src="' + data.student_image_profile + '" class="img-thumbnail mt-2" style="max-width: 200px;">'
                    );
                }
            }
            if (data.copy_of_idcard) {
                showDocumentPreview('copy_of_idcard', data.copy_of_idcard, 'ID Card');
                $("#existing_copy_of_idcard").val(data.copy_of_idcard);
            }
            if (data.copy_of_passport) {
                showDocumentPreview('copy_of_passport', data.copy_of_passport, 'Passport');
                $("#existing_copy_of_passport").val(data.copy_of_passport);
            }
            if (data.work_certificate) {
                showDocumentPreview('work_certificate', data.work_certificate, 'Work Certificate');
                $("#existing_work_certificate").val(data.work_certificate);
            }
            if (data.company_certificate) {
                showDocumentPreview('company_certificate', data.company_certificate, 'Company Certificate');
                $("#existing_company_certificate").val(data.company_certificate);
            }
            $(".page-title, h1").append(' <span class="badge badge-info" data-lang="update_mode">Update Mode</span>');
            $(".input-password").addClass("hidden");
            $("input[name='student_password']").removeClass("require");
            $(".logout").attr("href", "/actions/logout.php");
        } else {
            $(".before-login").removeClass("hidden");
            $(".login").attr("href", "/" + tenant_key);
        }
        updateProgressBar();
    }, 'json').fail(() => window.location.href = '/');
    function showDocumentPreview(fieldName, fileUrl, label) {
        const $input = $("input[name='" + fieldName + "']");
        const $existingInput = $("input[name='existing_" + fieldName + "']");
        let $container = $input.siblings('.document-preview-list-' + fieldName);
        if ($container.length === 0) {
            $container = $('<ul class="list-group document-preview-list-' + fieldName + ' mt-2"></ul>');
            $input.after($container);
        }
        const fileExt = fileUrl.split('.').pop().toLowerCase();
        let $item = $('<li class="list-group-item d-flex justify-content-between align-items-center"></li>');
        let previewContent = '';
        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
            previewContent = `
                <div class="text-center">
                    <img src="${fileUrl}" class="img-thumbnail" style="max-width: 80px; margin-right:10px;">
                </div>
            `;
        } else {
            previewContent = `
                <div class="text-center">
                    <i class="fa fa-file-pdf-o fa-5x text-danger"></i>
                </div>
            `;
        }
        $item.append(previewContent);
        const $btnGroup = $('<div class="text-center" style="margin-top: 15px;"></div>');
        const $viewBtn = $(`<button type="button" class="btn btn-primary" onclick="viewFile('${fileUrl}')" data-lang="view"></button>`);
        const $deleteBtn = $('<button type="button" class="btn btn-warning" data-lang="delete" style="margin-left: 15px;"></button>');
        $deleteBtn.on('click', function() {
            const msg = currentLang === 'en' ? "Are you sure?" : "คุณแน่ใจหรือไม่?";
            const text = currentLang === 'en' ? "This file will be removed from the list." : "ไฟล์นี้จะถูกลบออกจากรายการ";
            const confmsg = currentLang === 'en' ? "Yes, delete it!" : "ใช่, ลบเลย!";
            const canfmsg = currentLang === 'en' ? "Cancel" : "ยกเลิก";
            swal({
                title: msg,
                text: text,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: confmsg,
                cancelButtonText: canfmsg,
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {
                    $item.remove();
                    $existingInput.val('');
                }
            });
        });
        $btnGroup.append($viewBtn).append($deleteBtn);
        $item.append($btnGroup);
        $container.append($item);
        toggleLanguage(currentLang);
    }
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
        $.post('/classroom/register/actions/register.php', { 
            action: "loadTerm", 
            classroom_id 
        }, (res) => {
            $(".systemModal .modal-body").html(res.classroom_consent || '-');
        }, 'json');
    });
    $(document).on('click', '.accept-term', function() {
        $("#agree").prop('checked', true);
        $(".systemModal").modal('hide');
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
            if(currentLang == 'en') {
                swal({
                    type: 'warning',
                    title: 'Warning',
                    text: 'Please fill in all required fields. ' + errorMessage,
                    confirmButtonColor: '#FF9900'
                });
            } else {
                swal({
                    type: 'warning',
                    title: 'คำเตือน',
                    text: 'กรุณากรอกข้อมูลในฟิลด์ที่จำเป็นทั้งหมด ' + errorMessage,
                    confirmButtonColor: '#FF9900'
                });
            }
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
            var maxSize = 20 * 1024 * 1024; 
            const titlemsg = currentLang === 'en' ? "Warning" : "คำเตือน";
            if (allowedTypes.indexOf(file.type) === -1) {
                const msg = currentLang === 'en' ? "Please select only an image or PDF file." : "กรุณาเลือกไฟล์ภาพหรือ PDF เท่านั้น.";
                swal({
                    type: 'warning',
                    title: titlemsg,
                    text: msg,
                    confirmButtonColor: '#FF9900'
                });
                $(this).val('');
                return;
            }
            if (file.size > maxSize) {
                const msg = currentLang === 'en' ? "File size must not exceed 20 MB." : "ขนาดไฟล์ต้องไม่เกิน 20 MB.";
                swal({
                    type: 'warning',
                    title: titlemsg,
                    text: msg,
                    confirmButtonColor: '#FF9900'
                });
                $(this).val('');
                return;
            }
        }
    });
    $("#student_idcard").on("blur", function() {
        var $this = $(this);
        var id = $this.val().trim();
        if (id.length === 0) return;
        if (!isValidThaiID(id)) {
            $this.addClass("has-error");
            const titlemsg = currentLang === 'en' ? "Warning" : "คำเตือน";
            const msg = currentLang === 'en' ? "Invalid ID card number" : "หมายเลขบัตรประชาชนไม่ถูกต้อง";
            swal({
                type: 'warning',
                title: titlemsg,
                text: msg,
                confirmButtonColor: '#FF9900'
            });
            setTimeout(function() {
                $this.focus();
            }, 0);
        } else {
            $this.removeClass("has-error");
            verifyDuplicateData(id, 'idcard', 'student_idcard');
        }
    });
    $("#student_perfix").on("change", function() {
        if($(this).val() == "Other") {
            $(".prefix-other").removeClass("hidden");
        } else {
            $(".prefix-other").addClass("hidden");
        }
    });
    buildNationality();
});
function verifyDuplicateData(value, type, fieldId) {
    $.ajax({
        url: "/classroom/register/actions/register.php",
        type: "POST",
        data: {
            action:'verifyDuplicateData',
            verify_val: value,
            verify_type: type,
            currentLang: currentLang
        },
        dataType: "JSON",
        type: 'POST',
        success: function(result){
            if (!result.status) {
                $("#" + fieldId).addClass("has-error");
                const titlemsg = currentLang === 'en' ? "Warning" : "คำเตือน";
                swal({
                    type: 'warning',
                    title: titlemsg,
                    text: result.message,
                    confirmButtonColor: '#FF9900'
                });
            } else {
                $("#" + fieldId).removeClass("has-error");
                if(type == 'mobile') {
                    let usernameMobile = value;
                    if (value.startsWith("0")) {
                        value = value.replace(/^0/, "");
                        $(this).val(value);
                    } else if (value.length > 0) {
                        usernameMobile = "0" + value;
                    }
                    $("#student_username").val(value ? usernameMobile : "");
                }
            }
        }
    });
}
function viewFile(fileUrl) {
    $(".systemModal").modal();
    $(".systemModal .modal-header").html(`<h5 class="modal-title" data-lang="file_preview"></h5>`);
    const ext = fileUrl.split('.').pop().toLowerCase();
    let content = '';
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
        content = `<img src="${fileUrl}" alt="Preview" class="img-fluid" style="max-width:100%; height:auto;">`;
    } else if (ext === 'pdf') {
        const gviewUrl = "https://docs.google.com/gview?embedded=true&url=" + encodeURIComponent(fileUrl);
        content = `
            <iframe src="${gviewUrl}" 
                width="100%" 
                height="500px" 
                style="border:none;">
            </iframe>
        `;
    }
    $(".systemModal .modal-body").html(content);
    $(".systemModal .modal-footer").html(`
        <div class="text-center">
            <button type="button" class="btn btn-default" data-lang="close" data-dismiss="modal">Close</button>
        </div>
    `);
}
function buildNationality() {
    try {
        $("#student_nationality").select2({
            theme: "bootstrap",
            placeholder: "",
            minimumInputLength: -1,
            allowClear: true,
            ajax: {
                url: "/classroom/register/actions/register.php",
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                        action: 'buildNationality'
                    };
                },
                processResults: function(data, params) {
                    const page = params.page || 1;
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.col,
                                code: item.code,
                                desc: item.desc,
                            };
                        }),
                        pagination: {
                            more: (page * 10) <= (data[0] ? data[0].total_count : 0)
                        }
                    };
                },
            },
            templateSelection: function(data) {
                return data.text;
            },
        });
    } catch (error) {
        console.error('Error building department dropdown:', error);
    }
}
function isValidThaiID(id) {
    if (!/^\d{13}$/.test(id)) return false;
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(id.charAt(i)) * (13 - i);
    }
    let checkDigit = (11 - (sum % 11)) % 10;
    return checkDigit === parseInt(id.charAt(12));
}
function autoSaveRegister() {
    const $form = $('#registrationForm');
    const fd = new FormData($form[0]);
    if (typeof classroom_id !== 'undefined' && classroom_id) {
        fd.append('classroom_id', classroom_id);
    }
    const $dialCode = $(".iti__selected-dial-code");
    if ($dialCode.length > 0) {
        const dialCode = $dialCode.text().trim();
        fd.append('dialCode', dialCode);
    }
    if (typeof channel_id !== 'undefined' && channel_id) {
        fd.append('channel_id', channel_id);
    }
    if (typeof line_client_id !== 'undefined' && line_client_id) {
        fd.append('line_client_id', line_client_id);
    }
    const lang = (typeof currentLang !== 'undefined' && currentLang) ? currentLang : 'th';
    fd.append('currentLang', lang);
    $.ajax({
        url: '/classroom/register/actions/register.php?action=saveRegister',
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: function(result) {
        },
        error: function(xhr, status, error) {
            console.error('Register error:', status, error);
            $(".loader").removeClass("active");
            $(".systemModal").modal("hide");
            $btn.prop('disabled', false);
            const titlemsg = (lang === 'en') ? "Warning" : "คำเตือน";
            const msg = (lang === 'en') ? "Save failed, please try again." : "บันทึกไม่สำเร็จ กรุณาลองอีกครั้ง";
            if (typeof swal === 'function') {
                swal({
                    type: 'warning',
                    title: titlemsg,
                    text: msg,
                    confirmButtonColor: '#FF9900'
                });
            } else {
                alert(msg);
            }
        }
    });
}
function saveRegister() {
    const $form = $('#registrationForm');
    if ($form.length === 0) {
        console.error('Registration form not found');
        return;
    }
    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);
    if ($(".loader").length > 0) {
        $(".loader").addClass("active");
    }
    const fd = new FormData($form[0]);
    if (typeof classroom_id !== 'undefined' && classroom_id) {
        fd.append('classroom_id', classroom_id);
    }
    const $dialCode = $(".iti__selected-dial-code");
    if ($dialCode.length > 0) {
        const dialCode = $dialCode.text().trim();
        fd.append('dialCode', dialCode);
    }
    if (typeof channel_id !== 'undefined' && channel_id) {
        fd.append('channel_id', channel_id);
    }
    if (typeof line_client_id !== 'undefined' && line_client_id) {
        fd.append('line_client_id', line_client_id);
    }
    const lang = (typeof currentLang !== 'undefined' && currentLang) ? currentLang : 'th';
    fd.append('currentLang', lang);
    $.ajax({
        url: '/classroom/register/actions/register.php?action=saveRegister',
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: function(result) {
            handleRegisterResponse(result);
        },
        error: function(xhr, status, error) {
            console.error('Register error:', status, error);
            $(".loader").removeClass("active");
            $(".systemModal").modal("hide");
            $btn.prop('disabled', false);
            const titlemsg = (lang === 'en') ? "Warning" : "คำเตือน";
            const msg = (lang === 'en') ? "Save failed, please try again." : "บันทึกไม่สำเร็จ กรุณาลองอีกครั้ง";
            if (typeof swal === 'function') {
                swal({
                    type: 'warning',
                    title: titlemsg,
                    text: msg,
                    confirmButtonColor: '#FF9900'
                });
            } else {
                alert(msg);
            }
        }
    });
}
function handleRegisterResponse(result) {
    $(".loader").removeClass("active");
    $(".systemModal").modal("hide");
    if (!result || typeof result !== 'object') {
        console.error('Invalid response:', result);
        const lang = (typeof currentLang !== 'undefined' && currentLang) ? currentLang : 'th';
        const msg = (lang === 'en') ? "Invalid response from server" : "ข้อมูลจากเซิร์ฟเวอร์ไม่ถูกต้อง";
        if (typeof swal === 'function') {
            swal({
                type: 'error',
                title: 'Error',
                text: msg,
                confirmButtonColor: '#FF0000'
            });
        } else {
            alert(msg);
        }
        return;
    }
    const lang = (typeof currentLang !== 'undefined' && currentLang) ? currentLang : 'th';
    if (!result.status) {
        $(".btn-register").prop('disabled', false);
        const titlemsg = (lang === 'en') ? "Warning" : "คำเตือน";
        const message = result.message || ((lang === 'en') ? "Registration failed" : "การลงทะเบียนล้มเหลว");
        if (typeof swal === 'function') {
            swal({
                type: 'warning',
                title: titlemsg,
                text: message,
                confirmButtonColor: '#FF9900'
            });
        } else {
            alert(message);
        }
        return;
    }
    const isLoggedIn = (typeof is_logged_in !== 'undefined') ? is_logged_in : false;
    if (isLoggedIn) {
        const msg = (lang === 'en') ? "Saved successfully" : "บันทึกข้อมูลเรียบร้อย";
        if (typeof swal === 'function') {
            swal({
                title: msg,
                text: '',
                type: 'success',
                confirmButtonColor: '#41a85f'
            },function() {
                location.reload();
            });
        }
        $(".btn-register").prop('disabled', false);
    } else {
        if(line_client_id) {
            handleLineLogin(result);
        } else {
            showSuccessModal(lang, result.tenant_url, result.message_success);
        }
    }
}
function showSuccessModal(lang, tenant_url, message_success) {
    const $modal = $(".systemModal");
    if ($modal.length === 0) {
        console.error('System modal not found');
        location.reload();
        return;
    }
    $modal.modal('show');
    $modal.find(".modal-header").html(`
        <h5 class="modal-title" data-lang="registered"></h5>
    `);
    $modal.find(".modal-body").html(message_success);
    $modal.find(".modal-footer").html(`
        <div class="text-center w-100">
            <a href="${tenant_url}" class="btn btn-primary" data-lang="close"></a>
        </div>
    `);
    toggleLanguage(lang);
    $modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
        location.reload();
    });
}
function handleLineLogin(result) {
    const lineClientId = result.line_client_id || '';
    if (lineClientId && lineClientId !== '') {
        const classroomKey = $("#classroomCode").val() || '';
        const studentId = result.student_id || '';
        if (classroomKey && studentId) {
            try {
                const stateData = `cid=${classroomKey}&stu=${studentId}&lid=${lineClientId}`;
                const state = btoa(encodeURIComponent(stateData));
                window.location.href = `/classroom/lib/line/login.php?state=${state}`;
            } catch (error) {
                console.error('Error encoding LINE state:', error);
            }
        }
    }
}
function initForm(form_data) {
    if(!form_data) return;
    let html = '';
    form_data.forEach(q => {
        const answerChoice = q.answer_choice_id;
        const answerText = q.answer_text;
        const answerOther = q.answer_other_text;
        html += `<div class="form-group">
            <input type="hidden" name="question_id[]" value="${q.question_id}">
            <input type="hidden" name="question_type[]" value="${q.question_type}">
            <hr>
        `;
        html += `<label class="${q.has_required == 1 ? 'required-field' : ''} question_text"><i class="fas fa-question-circle"></i> ${q.question_text}</label>`;
        let requiredAttr = q.has_required == 1 ? 'data-required="1"' : '';
        if(q.has_options == 1) {
            if(q.question_type === "radio" || q.question_type === "multiple_choice") {
                q.option_item.forEach(opt => {
                    const checked = (answerChoice == opt.choice_id) ? 'checked' : '';
                    html += `
                        <div>
                            <input type="radio" id="q_${q.question_id}_opt_${opt.choice_id}" name="q_${q.question_id}" value="${opt.choice_id}" class="option-input" data-qid="${q.question_id}" ${requiredAttr} ${checked}>
                            <label for="q_${q.question_id}_opt_${opt.choice_id}" class="radio-label">${opt.choice_text}</label>
                        </div>
                    `;
                });
            } else if(q.question_type === "checkbox") {
                q.option_item.forEach(opt => {
                    const checked = (Array.isArray(answerChoice) && answerChoice.includes(opt.choice_id)) ? 'checked' : '';
                    html += `
                        <div>
                            <input type="checkbox" id="q_${q.question_id}_opt_${opt.choice_id}" name="q_${q.question_id}[]" value="${opt.choice_id}" class="option-input" data-qid="${q.question_id}" ${requiredAttr} ${checked}>
                            <label for="q_${q.question_id}_opt_${opt.choice_id}" class="checkbox-label">${opt.choice_text}</label>
                        </div>
                    `;
                });
            }
            if(q.has_other_option == 1) {
                const inputType = (q.question_type === 'checkbox' ? 'checkbox' : 'radio');
                const inputName = `q_${q.question_id}_other`;
                const otherChecked = (answerOther && answerOther !== '') ? 'checked' : '';
                const otherDisplay = otherChecked ? 'block' : 'none';
                html += `
                    <div>
                        <input type="${inputType}" id="q_${q.question_id}_other" name="${inputName}" value="other" class="option-input other-input" data-qid="${q.question_id}" ${requiredAttr} ${otherChecked}>
                        <label for="q_${q.question_id}_other" class="${inputType==='checkbox' ? 'checkbox-label':'radio-label'}"><span data-lang="other">อื่นๆ</span></label>
                    </div>
                    <div id="other_box_${q.question_id}" style="display:${otherDisplay}; margin-top:5px;">
                        <input type="text" class="form-control" name="q_${q.question_id}_other" value="${answerOther || ''}">
                    </div>
                `;
            }
        } else {
            if(q.question_type === "short_answer") {
                html += `<textarea name="q_${q.question_id}" class="form-control" ${requiredAttr} onclick="autoResize(this);" onkeyup="autoResize(this);">${answerText || ''}</textarea>`;
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
    $(document).attr("title", `${data.classroom_name} • ORIGAMI PLATFORM`);
    $(".poster-bg").css("background-image",`url(/images/ogm_bg.png)`);
    $(".poster-img img").attr("src", data.classroom_poster || "/images/training.jpg");
    $(".container-header-bg").css("background-image",`url(/images/ogm_bg.png)`);
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
    $("h5.classroom-date").html(`<i class="far fa-calendar"></i> ${data.classroom_start_date} - ${data.classroom_end_date} At ${data.classroom_start_time}-${data.classroom_end_time}`);
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
function calculateFormCompletion() {
    const fieldStatus = {};
    $('#registrationForm').find('input:visible, textarea:visible, select:visible').each(function() {
        const $el = $(this);
        const name = $el.attr('name');
        if(!name) return;
        if(fieldStatus[name]) return;
        let filled = false;
        const type = $el.attr('type');
        if($el.is('textarea') || type === 'text' || type === 'tel' || type === 'email' || type === 'password') {
            filled = isInputValid($el);
        } else if(type === 'file') {
            filled = $el.val() || ($(`#existing_${$el.attr('id')}`).length && $(`#existing_${$el.attr('id')}`).val());
        } else if(type === 'checkbox' || type === 'radio') {
            if($(`input[name="${name}"]:checked`).length) filled = true;
            const otherEl = $(`input[name="${name}_other"]`);
            const otherChecked = $(`input[name="${name}"][value="other"]`).is(':checked');
            if(otherEl.length && otherChecked && otherEl.val().trim() !== '') filled = true;
        } else if($el.is('select')) {
            filled = $el.val() && $el.val() !== '';
        }
        fieldStatus[name] = filled;
    });
    const totalFields = Object.keys(fieldStatus).length;
    const filledFields = Object.values(fieldStatus).filter(v => v).length;
    return totalFields ? Math.round((filledFields / totalFields) * 100) : 0;
}
function isInputValid($el) {
    const val = ($el.val() || '').trim();
    if (!val) return false;
    const id = $el.attr('id') || '';
    if (id.endsWith('_en')) {
        return /^[A-Za-z\s'-]+$/.test(val);
    } else if (id.endsWith('_th')) {
        return /^[ก-๙ะ-๏\s]+$/.test(val);
    } else if (id === 'student_username') {
        return /^[A-Za-z0-9]+$/.test(val);
    } else if (id === 'student_password') {
        return /^[A-Za-z0-9!@#$%^&*()_\+\-=\[\]{};:'",.<>\/?\\|`~]+$/.test(val);
    }
    return true;
}
function updateProgressBar() {
    const percent = calculateFormCompletion();
    $('#progress_bar').css('width', percent + '%').text(percent + '%');
    if(is_logged_in) {
        autoSaveRegister();
    }
}