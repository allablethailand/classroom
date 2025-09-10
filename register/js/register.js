let classroom_id;
let tenant_key = '';
let currentLang = "th";
const translations = {
    en: {
        eng: "English", thai: "Thai", register: "Register", infomation: "Details",
        contact_us: "Contact Information", registration_form: "Registration Form",
        firstname: "First Name (EN)", lastname: "Last Name (EN)", nickname: "Nickname",
        gender: "Gender", male: "Male", female: "Female", other: "Prefer not to specify",
        email: "Email", mobile: "Mobile Number", company: "Company", position: "Position",
        view_on_map: "View on Map", i_accept: "I accept", policy: "Terms and Conditions & Privacy Policy",
        username: "Username", password: "Password",
        password_info: "Password must be 4–20 characters, using only English letters or numbers.",
        username_info: "Username must be 8–20 characters, consisting of English letters or numbers only. (By default, your registered mobile number will be used)",
        close: "Close", accept: "Accept", already: "Already have an account?", login: "Log in", registered: "Successfully registered.",
        registered_success: "Your registration has been submitted successfully.",
        however: "However, your account is currently pending approval from our administrator.",
        once: "Once your registration is reviewed and approved, you will receive a notification via email.",
        registered_login: "You can now log in to the system using your registered username and password.",
        please_login: "Please proceed by clicking the \"Login\" button below."
    },
    th: {
        eng: "อังกฤษ", thai: "ไทย", register: "ลงทะเบียน", infomation: "รายละเอียด",
        contact_us: "ข้อมูลการติดต่อ", registration_form: "แบบฟอร์มลงทะเบียน",
        firstname: "ชื่อ (ภาษาอังกฤษ)", lastname: "นามสกุล (ภาษาอังกฤษ)", nickname: "ชื่อเล่น",
        gender: "เพศ", male: "ชาย", female: "หญิง", other: "ไม่ประสงค์ระบุ",
        email: "อีเมล", mobile: "หมายเลขโทรศัพท์มือถือ", company: "บริษัท", position: "ตำแหน่ง",
        view_on_map: "ดูบนแผนที่", i_accept: "ข้าพเจ้ายอมรับ", policy: "ข้อกำหนดและเงื่อนไข รวมถึงนโยบายความเป็นส่วนตัว",
        username: "ชื่อผู้ใช้", password: "รหัสผ่าน",
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
$(function() {
    const classroomCode = $("#classroomCode").val();
    function initForm() {
        const $form = $('#registrationForm');
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
        $form.on('submit', function(e) {
            e.preventDefault();
            if (!validateForm($form)) return;
            const $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true);
            const fd = new FormData(this);
            fd.append('classroom_id', classroom_id);
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
                    swal({ type: 'error', title: "Error", text: "Server error, please try again.", timer: 2500 });
                }
            });
        });
        const validators = {
            email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            mobile: /^[0-9-+\s()]{10,}$/
        };
        $('#email, #mobile').on('blur', function() {
            const val = $(this).val();
            const type = $(this).attr('id');
            if (val && !validators[type].test(val)) {
                $(this).addClass('has-error');
                const msg = type === 'email' ? 'Invalid email format' : 'The phone number format is incorrect.';
                swal({ type: 'warning', title: "Warning...", text: msg, showConfirmButton: false, timer: 2500 });
            } else {
                $(this).removeClass('has-error');
                if (type === 'mobile' && !$("#username").val()) $("#username").val(val);
            }
        });
        $(".fixed-character").on("input", function() {
            const clean = $(this).val().replace(/[^a-zA-Z0-9]/g, "");
            $(this).val(clean);
        });
    }
    function validateForm($form) {
        let isValid = true;
        let errorMessage = '';
        $form.find('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('has-error');
                const label = $('label[for="' + $(this).attr('id') + '"]').text();
                errorMessage += `Please fill in ${label}\n`;
            } else $(this).removeClass('has-error');
        });
        if (!$('#agree').is(':checked')) {
            isValid = false;
            errorMessage += 'Please accept the terms and conditions.\n';
        }
        if (!isValid) {
            swal({ type: 'warning', title: "Warning...", text: errorMessage, showConfirmButton: false, timer: 2500 });
        }
        return isValid;
    }
    function handleRegisterResponse(result) {
        $(".loader").removeClass("active");
        if (!result.status) {
            swal({ type: 'warning', title: "Duplicate Data", text: result.message, timer: 2500 });
            return;
        }
        const auto_approve = result.auto_approve;
        $(".systemModal .modal-header").html(`<h5 class="modal-title" data-lang="registered"></h5>`);
        if (auto_approve == 0) {
            $(".systemModal .modal-body").html(`
                <div class="text-center">
                    <img class="comp-logo" onerror="this.src='/images/origami-academy.png'" style="height: 100px;">
                    <h5 class="text-success" data-lang="registered_success"></h5>
                    <p data-lang="registered_login"></p>
                    <p data-lang="please_login"></p>
                </div>
            `);
            $(".systemModal .modal-footer").html(`
                <div class="text-center">
                    <a href="/${tenant_key}" class="btn btn-warning" data-lang="login"></a>
                    <button type="button" class="btn btn-default" data-lang="close" data-dismiss="modal"></button>
                </div>
            `);
        } else {
            $(".systemModal .modal-body").html(`
                <div class="text-center">
                    <img class="comp-logo" onerror="this.src='/images/origami-academy.png'" style="height: 100px;">
                    <h5 class="text-success" data-lang="registered_success"></h5>
                    <p data-lang="however"></p>
                    <p data-lang="once"></p>
                </div>
            `);
            $(".systemModal .modal-footer").html(`
                <div class="text-center">
                    <button type="button" class="btn btn-default" data-lang="close" data-dismiss="modal"></button>
                </div>
            `);
        }
        $(".systemModal").modal();
        toggleLanguage(currentLang);
    }
    $.post('/classroom/register/actions/register.php', { action: "verifyClassroom", classroomCode }, (response) => {
        if (!response.status) return window.location.href = '/';
        classroom_id = response.classroom_data.classroom_id;
        tenant_key = response.classroom_data.tenant_key;
        initForm();
        initTemplate(response.classroom_data);
        $(".login").attr("href", "/" + tenant_key);
    }, 'json').fail(() => window.location.href = '/');
    function toggleLanguage(lang) {
        currentLang = lang;
        $("[data-lang]").each(function() {
            const key = $(this).attr("data-lang");
            $(this).text(translations[currentLang][key] || key);
        });
        $(".language-menu div").removeClass("lang-active");
        $(".language-menu div[store-translate='" + lang.toUpperCase() + "']").addClass("lang-active");
    }
    $(".language-menu div").click(function() {
        const lang = $(this).attr("store-translate");
        if (lang) toggleLanguage(lang.toLowerCase());
    });
    toggleLanguage(currentLang);
    $(".open-term").click(function() {
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
    $(document).on('click', '#togglePassword', function() {
        const $input = $('#password');
        const isPassword = $input.attr('type') === 'password';
        $input.attr('type', isPassword ? 'text' : 'password');
        $(this).html(`<i class="fa fa-${isPassword ? 'eye-slash' : 'eye'}"></i>`);
    });
});
function initTemplate(data) {
    $(".poster-content img").attr("src", data.classroom_poster || '');
    $(".poster-content .classroom-name").text(data.classroom_name || '-');
    const $location = $(".poster-content h5.classroom-location");
    if (data.classroom_type === 'online') {
        $location.html(`Online at ${data.classroom_place || '-'}`);
    } else {
        let mapHtml = '';
        if (data.classroom_place) {
            const [lat, lng] = data.classroom_place.split(',').map(c => c.trim());
            if (lat && lng) {
                mapHtml = `<a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="btn btn-sm btn-warning" data-lang="view_on_map"></a>`;
            }
        }
        $location.html(`<i class="fas fa-map-marker-alt"></i> ${data.classroom_source || '-'} <p style="margin-top:10px;">${mapHtml}</p>`);
    }
    $("h5.classroom-date").html(`<i class="fa fa-calendar"></i> ${data.classroom_start_date} ${data.classroom_start_time} - ${data.classroom_end_date} ${data.classroom_end_time}`);
    $(".classroom-information").html(data.classroom_information || '-');
    $(".contact-us").html(data.contact_us || '-');
    $(".comp-logo").attr("src", data.comp_logo || '');
}