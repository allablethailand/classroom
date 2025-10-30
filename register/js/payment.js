function renderTimelineStatus(payment_status, payment_status_date, payment_status_remark) {
    payment_status = parseInt(payment_status);
    const baseStyle = "margin: 0; color: #555555; font-size: 12px;";
    const templates = {
        0: {
            color: "#FF9900",
            icon: "fa fa-clock-o",
            lang: "waiting_approve"
        },
        1: {
            color: "#28a745",
            icon: "fas fa-check",
            lang: "confirm_approve",
            dateIcon: "far fa-calendar-check"
        },
        2: {
            color: "#fb9678",
            icon: "fas fa-times",
            lang: "reject_approve",
            dateIcon: "far fa-calendar-times"
        }
    };
    const t = templates[payment_status];
    if (!t) return "";
    return `
        <div class="timeline-item">
            <div class="timeline-icon" style="background: ${t.color};">
                <i class="${t.icon}"></i>
            </div>
            <div class="timeline-content">
                <h5 style="margin: 0 0 10px 0; color: ${t.color};" data-lang="${t.lang}"></h5>
                ${payment_status_date ? `<p style="${baseStyle}"><i class="${t.dateIcon}"></i> ${payment_status_date}</p>` : ""}
                ${payment_status_remark ? `<p style="${baseStyle}">${payment_status_remark}</p>` : ""}
            </div>
        </div>
    `;
}
function renderTickets(ticket_data, ticket_selected) {
    let html = '';
    ticket_data.forEach(ticket => {
        const isSelected = (ticket_selected == ticket.ticket_id) ? 0 : 1;
        const isDefault = ticket.ticket_default == 0;
        html += `
            <div class="col-sm-12">
                <label class="ticket-option-card ${isSelected == 0 ? 'selected' : ''}" data-id="${ticket.ticket_id}">
                    <input type="radio" name="ticket_id" value="${ticket.ticket_id}" ${isDefault ? 'checked' : ''}>
                    <div class="ticket-card-inner">
                        <div class="ticket-icon-wrapper">
                            <i class="glyphicon glyphicon-tag"></i>
                        </div>
                        <div class="ticket-price-section">
                            <span class="ticket-price-amount">${ticket.ticket_price}</span>
                            <span class="ticket-price-currency">${ticket.currency_code}</span>
                        </div>
                        <div class="ticket-type-label" data-lang="${ticket.ticket_type}" style="margin-left: 60px;"></div>
                        <div class="ticket-check-icon"><i class="glyphicon glyphicon-ok"></i></div>
                    </div>
                    ${isDefault ? `
                        <div class="ticket-recommended-badge">
                            <i class="glyphicon glyphicon-star"></i>
                            <span data-lang="recommend"></span>
                        </div>
                    ` : ''}
                </label>
            </div>
        `;
    });
    $("#ticket-list").html(html);
    $(".ticket-option-card").on("click", function() {
        $(".ticket-option-card").removeClass("selected");
        $(this).addClass("selected");
        $(this).find('input[type="radio"]').prop('checked', true);
    });
}
function initializeDropify() {
    try {
        $('.dropify').dropify({
            tpl: {
                wrap: '<div class="dropify-wrapper"></div>',
                loader: '<div class="dropify-loader"></div>',
                message: '<div class="dropify-message"><span class="file-icon" /> <p>{{ default }}</p></div>',
                preview: `
                    <div class="dropify-preview">
                        <span class="dropify-render"></span>
                        <div class="dropify-infos">
                            <div class="dropify-infos-inner">
                                <p class="dropify-infos-message">{{ replace }}</p>
                            </div>
                        </div>
                    </div>`,
                filename: '<p class="dropify-filename"><span class="file-icon"></span> <span class="dropify-filename-inner"></span></p>',
                clearButton: `
                    <div>
                        <button type="button" class="dropify-clear dropify-view">Preview</button>
                        <button type="button" class="dropify-clear dropify-remove">{{ remove }}</button>
                    </div>`,
                errorLine: '<p class="dropify-error">{{ error }}</p>',
                errorsContainer: '<div class="dropify-errors-container"><ul></ul></div>'
            }
        });
        $('.dropify-view').on('click', function(event) {
            event.stopPropagation();
            event.preventDefault();
            const img = $(this).closest('.dropify-wrapper').find('.dropify-render img').attr('src');
            if (img && $.fancybox) {
                $.fancybox({ href: img });
            }
        });
        $('.dropify').on('dropify.beforeClear', function(event, element) {
            $('#ex_payment_slip').val('');
        });
    } catch (error) {
        console.error('Error initializing Dropify:', error);
    }
}
function buildPaymentContainer() {
    $.ajax({
        url: "/classroom/register/actions/register.php",
        type: "POST",
        data: {
            action: 'buildPayment',
            classroom_id: classroom_id
        },
        dataType: "json",
        success: function(result) {
            let ticket_data = result.ticket_data;
            let payment_data = result.payment_data;
            let payment_methods = result.payment_methods || [];
            const {
                payment_status,
                payment_attach_file,
                payment_date,
                payment_status_date,
                payment_status_remark
            } = payment_data;
            const hasPayment = !!payment_date;
            const color = hasPayment ? '#28a745' : '#FF9900';
            const icon = hasPayment ? 'fas fa-check' : 'fa fa-clock-o';
            const langKey = hasPayment ? 'proof_payment' : 'wait_payment';
            let paymentMethodHTML = '';
            if (payment_methods.length > 0) {
                const firstMethod = payment_methods[0];
                paymentMethodHTML = `
                    <div class="card-header">
                        <p><i class="fa fa-credit-card"></i>
                            <span data-lang="payment_method"></span>
                        </p>
                    </div>
                    ${payment_methods.length > 1 ? `
                        <div class="text-right" style="margin-bottom: 15px;">
                            <a id="btnChoosePayment">
                                <i class="fa fa-exchange"></i> <span data-lang="change_payment_method"></span>
                            </a>
                        </div>
                    ` : ``}
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="method-container">
                                <div class="payment-method-card selected" data-id="${firstMethod.method_id}">
                                    <div class="cover">
                                        <img src="${firstMethod.method_cover || '/images/payment-default.png'}" alt="${firstMethod.method_type}">
                                    </div>
                                    <div class="info">
                                        <h5>${(currentLang == 'en') ? firstMethod.bank_name_en : firstMethod.bank_name_th || firstMethod.method_type}</h5>
                                        <p class="account">${firstMethod.account_name || ''}</p>
                                        <p class="number">${firstMethod.account_number || ''}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="method_id" id="method_id" value="${firstMethod.method_id}">
                `;
                if (payment_methods.length > 1) {
                    paymentMethodHTML += `
                        <div class="modal fade" id="paymentMethodModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        <h5 class="modal-title"><i class="fa fa-credit-card"></i> <span data-lang="choose_payment_method"></span></h5>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            ${payment_methods.map(method => `
                                                <div class="col-sm-6">
                                                    <div class="payment-method-card selectable" data-id="${method.method_id}">
                                                        <div class="cover">
                                                            <img src="${method.method_cover || '/images/payment-default.png'}" alt="${method.method_type}">
                                                        </div>
                                                        <div class="info">
                                                            <h5>${(currentLang == 'en') ? method.bank_name_en : method.bank_name_th || method.method_type}</h5>
                                                            <p class="account">${method.account_name || ''}</p>
                                                            <p class="number">${method.account_number || ''}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }
            $(document).on('click', '#btnChoosePayment', function() {
                $('#paymentMethodModal').modal('show');
            });
            $(document).on('click', '.payment-method-card.selectable', function() {
                const $card = $(this);
                const selectedId = $card.data('id');
                const selectedMethod = payment_methods.find(m => m.method_id == selectedId);
                const newCardHTML = `
                    <div class="payment-method-card selected" data-id="${selectedMethod.method_id}">
                        <div class="cover">
                            <img src="${selectedMethod.method_cover || '/images/payment-default.png'}" alt="${selectedMethod.method_type}">
                        </div>
                        <div class="info">
                            <h5>${(currentLang == 'en') ? selectedMethod.bank_name_en : selectedMethod.bank_name_th || selectedMethod.method_type}</h5>
                            <p class="account">${selectedMethod.account_name || ''}</p>
                            <p class="number">${selectedMethod.account_number || ''}</p>
                        </div>
                    </div>
                `;
                const container = $('.method-container');
                container.html(newCardHTML);
                $('#method_id').val(selectedMethod.method_id);
                $('#selected_method_id').val(selectedMethod.method_id);
                $('#bank_name').val(selectedMethod.bank_name_th || selectedMethod.bank_name_en || '');
                $('#account_name').val(selectedMethod.account_name || '');
                $('#account_number').val(selectedMethod.account_number || '');
                $('#paymentMethodModal').modal('hide');
            });
            const payment_html = `
                <div class="row" id="ticket-list"></div>
                ${paymentMethodHTML}
                <div class="card-header">
                    <p><i class="fa fa-upload"></i> <span data-lang="proof_of_payment"></span></p>
                </div>
                <input name="payment_slip" id="payment_slip" type="file" class="dropify" data-allowed-file-extensions='["jpg", "jpeg", "png"]' data-max-file-size="25M" data-height="155" data-default-file="">
                <input name="ex_payment_slip" id="ex_payment_slip" type="hidden">
                <div class="timeline" id="paymentTimeline">
                    <div class="timeline-item">
                        <div class="timeline-icon" style="background: ${color};">
                            <i class="${icon}"></i>
                        </div>
                        <div class="timeline-content">
                            <h5 style="margin: 0 0 10px 0; color: ${color};" data-lang="${langKey}"></h5>
                            ${hasPayment ? `
                                <p style="margin: 0; color: #555; font-size: 12px;">
                                    <i class="far fa-calendar-check"></i> ${payment_date}
                                </p>
                                ${payment_status !== "1" ? `
                                    <button type="button" class="btn btn-red" data-lang="cancel_payment" style="font-size: 12px; margin-top: 15px;" onclick="cancelPayment();"></button> 
                                ` : ``}
                            ` : ''}
                        </div>
                    </div>
                    ${hasPayment ? renderTimelineStatus(payment_status, payment_status_date, payment_status_remark) : ''}
                </div>
                <div style="margin-top: 30px;">
                    ${(payment_status !== "1") ? `
                        <button type="submit" class="btn btn-payment" id="submitPaymentBtn">
                            <span data-lang="send"></span>
                        </button>` : ''}
                    <p class="text-center" style="margin: 15px auto;">
                        <a class="logout" style="color:#FF9900; cursor:pointer;" data-lang="logout"></a>
                    </p>
                </div>
            `;
            $("#paymentForm").html(payment_html);
            $(".logout").attr("href", "/actions/logout.php");
            renderTickets(ticket_data, result.ticket_selected);
            if (payment_attach_file) {
                $("#payment_slip").attr("data-default-file", payment_attach_file);
                $("#ex_payment_slip").val(payment_attach_file);
            }
            initializeDropify();
            if(payment_status == "1") {
                $(".dropify-remove").attr("disabled", true);
            }
            $("#submitPaymentBtn").on("click", handlePaymentSubmit);
            toggleLanguage(currentLang);
            $(".payment-method-card").on("click", function() {
                $(".payment-method-card").removeClass("selected");
                $(this).addClass("selected");
                $("#method_id").val($(this).data("id"));
            });
            const firstMethod = $(".payment-method-card").first();
            if (firstMethod.length) {
                firstMethod.addClass("selected");
                $("#selected_method_id").val(firstMethod.data("id"));
            }
        }
    });
}
function handlePaymentSubmit(e) {
    e.preventDefault();
    const selectedTicket = $('input[name="ticket_id"]:checked').val();
    if (!selectedTicket) return showWarning('Please select ticket type.', 'กรุณาเลือกประเภทตั๋ว');
    const paymentSlip = $('#payment_slip')[0].files[0];
    const exPaymentSlip = $('#ex_payment_slip').val();
    if (!paymentSlip && !exPaymentSlip) return showWarning('Please attach proof of payment.', 'กรุณาแนบหลักฐานการชำระเงิน');
    const fd = new FormData($('#paymentForm')[0]);
    if (typeof classroom_id !== 'undefined') fd.append('classroom_id', classroom_id);
    const lang = (typeof currentLang !== 'undefined') ? currentLang : 'th';
    fd.append('currentLang', lang);
    $(".btn-payment").prop('disabled', true);
    $.ajax({
        url: '/classroom/register/actions/register.php?action=savePayment',
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "JSON",
        success: function() {
            const msg = (lang === 'en') ? "Send successfully" : "ส่งหลักฐานการชำระเงินเรียบร้อย";
            swal({ title: msg, type: 'success', confirmButtonColor: '#41a85f' }, () => location.reload());
            $(".btn-payment").prop('disabled', false);
        },
        error: function() {
            $(".btn-payment").prop('disabled', false);
            showWarning("Save failed, please try again.", "บันทึกไม่สำเร็จ กรุณาลองอีกครั้ง");
        }
    });
}
function cancelPayment() {
    const msg = currentLang === 'en' ? "Cancel payment?" : "ต้องการยกเลิกการชำระเงิน?";
    const confmsg = currentLang === 'en' ? "Yes" : "ใช่";
    const canfmsg = currentLang === 'en' ? "Cancel" : "ยกเลิก";
    swal({
        title: msg,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: confmsg,
        cancelButtonText: canfmsg,
        closeOnConfirm: false,
        closeOnCancel: true
    }, function(isConfirm) {
        if (!isConfirm) return;
        $.ajax({
            url: "/classroom/register/actions/register.php",
            type: "POST",
            data: {
                action: 'cancelPayment',
                classroom_id: classroom_id,
                currentLang: currentLang
            },
            dataType: "json",
            success: function(result) {
                if (!result || !result.status) {
                    const titlemsg = currentLang === 'en' ? "Unable to cancel payment" : "ไม่สามารถยกเลิกการชำระเงินได้";
                    swal({
                        title: titlemsg,
                        text: result && result.message ? result.message : '',
                        type: 'warning',
                        confirmButtonColor: '#FF9900'
                    }, function() {
                        location.reload();
                    });
                } else {
                    const msg = currentLang === 'en' ? "Cancel successfully" : "ยกเลิกการชำระเงินสำเร็จ";
                    swal({
                        title: msg,
                        type: 'success',
                        confirmButtonColor: '#41a85f'
                    }, function() {
                        location.reload();
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                swal({
                    title: 'Error',
                    text: 'Cannot connect to server',
                    type: 'error'
                });
            }
        });
    });
}
function showWarning(enMsg, thMsg) {
    const msg = currentLang === 'en' ? 'Warning' : 'คำเตือน';
    const text = currentLang === 'en' ? enMsg : thMsg;
    swal({ type: 'warning', title: msg, text: text, confirmButtonColor: '#FF9900' });
}