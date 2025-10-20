function switchTab(content) {
    $(".register-nav").removeClass("active");
    switchTabs(content)
    const url = new URL(window.location.href);
    url.search = '?' + content;
    history.replaceState(null, '', url);
}
function switchTabs(fragment) {
    switch(fragment) {
        case 'register':
            $(".register-container").removeClass("hidden");
            $(".payment-container").addClass("hidden");
            $(".for-register").addClass("active");
            $("#scrollToFormBtn span").attr('data-lang', 'register');
            break;
        case 'payment':
            $(".register-container").addClass("hidden");
            $(".for-payment").addClass("active");
            $(".payment-container").removeClass("hidden");
            buildPaymentContainer();
            $("#scrollToFormBtn span").attr('data-lang', 'send');
            break;
    }
    toggleLanguage(currentLang);
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
function toggleLanguage(lang) {
    currentLang = lang;
    $("[data-lang]").each(function () {
        const key = $(this).attr("data-lang");
        $(this).text(translations[currentLang][key] || key);
    });
    $(".language-menu div").removeClass("lang-active");
    $(".language-menu div[store-translate='" + lang.toUpperCase() + "']").addClass("lang-active");
}
function autoResize(textarea) {
    textarea.style.height = 'auto'; 
    textarea.style.height = textarea.scrollHeight + 'px'; 
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
function showSuccessModal(lang, tenant_url, message_success) {
    const $modal = $(".systemModal");
    if ($modal.length === 0) {
        location.reload();
        return;
    }
    $modal.modal('show');
    $modal.find(".modal-header").html(`<h5 class="modal-title" data-lang="registered"></h5>`);
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