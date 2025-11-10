$(document).ready(function() {

    $(".notification").on("click", function () {
        var isExpanded = $(this).attr("aria-expanded") === "true";
        var $menu = $(this).next(".main-notification");
        if (!isExpanded) {
        if (!$menu.data("loaded")) {
            $.ajax({
                url: "/actions/header.php",
                async: true,
                type: "POST",
                data: { 
                    action: 'getNotification' 
                },
                dataType: "JSON",
                beforeSend: function() {
                    $menu.html(`<li class="no-result text-center">
                        <div class=text-grey" style="margin: 25px auto;">
                            <i class="fas fa-spinner fa-pulse fa-3x"></i>
                            <div>Loading</div>
                        </div>
                    </li>`);
                },
                success: function(result) {
                    if (result.status) {
                        if ((result.notification_data).length > 0) {
                            let html = `
                                <li class="text-center">
                                    <a onclick="readNotification();">
                                        <i class="fa fa-check-circle"></i> Read all
                                    </a>
                                </li>
                                <li class="divider"></li>
                            `;
                            $.each(result.notification_data, function(index, row) {
                                const decodedRequest = JSON.parse(row.noti_request);
                                const link = decodedRequest.link || '';
                                const id = decodedRequest.id || '';
                                const bgColor = row.color 
                                    ? `background: linear-gradient(210deg, rgba(255, 255, 255, 0.15) 6.62%, ${row.color} 63.09%), #FFFFFF;`
                                    : 'background: linear-gradient(210deg, rgba(255, 255, 255, 0.15) 6.62%, rgb(255 216 129 / 66%) 63.09%), #FFFFFF;';
                                const disabledClass = row.noti_read_datetime != null ? ' disabled ' : '';
                                const avatarHtml = `
                                    <div class="img-circle" style="vertical-align: middle;padding:1px;display:inline-block; position: relative;  width: 50px;  height: 50px;  overflow: hidden !important;">
                                        <img width="50" src="${row.emp_pic}" onerror="this.src='/images/default.png'">
                                    </div>
                                `;
                                const elapsed = row.noti_datetime;
                                let notificationTxt = row.notification_txt;
                                let description = row.description;
                                const statusSvg = row.noti_read_datetime == null ? `
                                    <div class="status-noti">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <circle cx="5" cy="5" r="5" fill="#FFA930"></circle>
                                        </svg>
                                    </div>
                                ` : '';
                                html += `
                                    <li>
                                        <div class="sub-noti-modal${disabledClass}" style="${bgColor}" onclick="SendLink('${link}', this, '${id}', '${row.noti_module_name}');">
                                            <div style="padding: 10px;">
                                                <input type="hidden" name="noti-id" value="${row.noti_id}" />
                                                <div class="column1">
                                                    ${avatarHtml}
                                                    <span style="margin: -12px 36px; position: absolute; display: flex;">
                                                        <svg width="21" height="21" viewBox="0 0 103 103" fill="none">
                                                            <circle cx="51.5" cy="51.5" r="51.5" fill="#FFA930"/>
                                                            <path d="M42.7216 27.9446C42.7216 22.8539 46.6518 18.7273 51.5 18.7273C56.3483 18.7273 60.2785 22.8539 60.2785 27.9446C60.2785 33.0351 56.3483 37.1619 51.5 37.1619C46.6518 37.1619 42.7216 33.0351 42.7216 27.9446ZM73.7664 28.1204C72.2428 26.5205 69.7724 26.5205 68.2489 28.1204L57.687 39.2102H45.313L34.7512 28.1204C33.2276 26.5205 30.7572 26.5205 29.2337 28.1204C27.71 29.7202 27.71 32.314 29.2337 33.9138L40.7709 46.0277V80.1761C40.7709 82.4386 42.5176 84.2727 44.6724 84.2727H46.6231C48.7779 84.2727 50.5247 82.4386 50.5247 80.1761V65.8381H52.4754V80.1761C52.4754 82.4386 54.2222 84.2727 56.3769 84.2727H58.3277C60.4824 84.2727 62.2292 82.4386 62.2292 80.1761V46.0277L73.7664 33.9137C75.29 32.3139 75.29 29.7202 73.7664 28.1204Z" fill="white"/>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="column2">
                                                    <div class="text-action">
                                                        ${row.noti_response} ${row.noti_module_name}
                                                    </div>
                                                    <div class="text-subject">
                                                        ${notificationTxt}
                                                    </div>
                                                    <div class="text-descript">
                                                        ${description}
                                                    </div>
                                                </div>
                                                <div class="column3">
                                                    <div class="time-noti" style="margin-bottom: 10px;">
                                                        <i class="fa fa-clock-o"></i>&nbsp;${elapsed}
                                                    </div>
                                                    ${statusSvg}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                `;
                            });
                            html += `
                                <li class="divider"></li>
                                <li class="text-center view-all">
                                    <a onclick="viewAllNotifications();">
                                        <i class="fa fa-list"></i> View all
                                    </a>
                                </li>
                            `;
                            $menu.html(html);
                            $menu.data("loaded", true);
                        } else {
                            $menu.html(`
                                <li class="no-result text-center">
                                    <div class=text-grey" style="margin: 25px auto;">
                                        <i class="fas fa-bell fa-3x"></i>
                                        <div>No results found</div>
                                    </div>
                                </li>
                            `);
                        }
                    } else {
                        $menu.html(`
                            <li class="no-result text-center">
                                <div class=text-grey" style="margin: 25px auto;">
                                    <i class="fas fa-bell fa-3x"></i>
                                    <div>No results found</div>
                                </div>
                            </li>
                        `);
                    }
                },
                error: function() {
                    $menu.html(`
                        <li class="no-result text-center">
                            <div class=text-red" style="margin: 25px auto;">
                                <i class="fas fa-exclamation fa-3x"></i>
                                <div>Failed to load.</div>
                            </div>
                        </li>
                    `);
                }
            });
        }
        }
       });
})



function update_notiStatus_read() {
	$('.for-notification').addClass('hidden');
	if ($('.noti-toggle.dropdown').hasClass("open")) {	
	} else {
		$.ajax({
			url: "/classroom/study/actions/header.php", 
			type: "POST",
			data: {
				action: "ReadNotifications"
			},
			success: function(result) {}
		});
	}
}


function readNotification() {
    $('.status-noti').addClass('hidden');
    $('.sub-notifications').addClass('disabled');
    $.ajax({
        url: "/lib/notification_action.php", 
        type: "POST",
        data: {
            action: "ReadDetailAll"
        },
        success: function(result) {}
    });
}
