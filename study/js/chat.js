$(document).ready(function () {

    $('.start-chat-btn').on('click', function(e){
        e.preventDefault();
        $('.chat-start-button').hide();
        $('.chat-text-header, #chatMessages, .chat-input-container, .disclaimer').show();

        var emp_id = $('#emp_id').val();
        var comp_id = $('#comp_id').val();
        
        const formData = new FormData();
        formData.append('action', 'create_chat');
        formData.append('emp_id', emp_id);
        formData.append('comp_id', comp_id);

        // console.log("FORMDATA", formData);

        $.ajax({
            url: "/classroom/study/actions/chat.php",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                // console.log("DATA", data);
                // console.log("DATA", data.status);

                if(data.status == true){
                    $("#chatmessageInput").attr('data-group', data.group_id);
                } else {
                    const messageDiv = $('<div class="message message-bot"></div>');
                    const plainText = htmlToPlainText(data.message || '');
                    const contentDiv = $('<div class="message-content"></div>').html(plainText.replace(/\n/g, '<br>'));
                    messageDiv.append(contentDiv);  // Content goes inside message
                    
                    $("#chatMessages").append(messageDiv);
                    
                    // Auto-scroll to bottom
                    $("#chatMessages")[0].scrollTop = $("#chatMessages")[0].scrollHeight;

                    $("#sendMessageButton").attr("disabled", true);
                    $("#sendMessageButton").css("background-color", "LightGrey"); 
                    $("#chatmessageInput").attr("disabled", true);
                    $("#chatmessageInput").attr("placeholder", "Service Unavaialble, Please contact Allable Team.");

                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });
    

    // SEND MESSAGE TO AI CHATBOT
    $("#sendMessageButton").on('click', function(e){
        e.preventDefault();
        var inputMessage = $("#chatmessageInput").val();
        let groupValue = $("#chatmessageInput").attr('data-group');
        
        if (inputMessage.trim() !== '') {    
            const dateObj = new Date(); // ใช้เวลาปัจจุบัน

            // ดึงและจัดรูปแบบวันที่
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            const year = String(dateObj.getFullYear()).slice(-2);

            // ดึงและจัดรูปแบบเวลา
            let hours = dateObj.getHours();
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;
            const formattedHour = String(hours).padStart(2, '0');

            // รวมเป็นข้อความเดียว
            const dateTimeFormat = `${month}/${day}/${year} at ${formattedHour}.${minutes} ${ampm}`;

            // console.log(groupValue);

            // Create user message container and content with jQuery
            const userMessageContainer = $('<div class="message message-user"></div>');
            let message = htmlToPlainText(inputMessage);
            const userMessageContent = $('<div class="message-content"></div>').html(`${message}<div class="message-time">${dateTimeFormat}</div>`);
            userMessageContainer.append(userMessageContent);
            $('#chatMessages').append(userMessageContainer);

            // Scroll to bottom and clear input
            $('#chatMessages')[0].scrollTop = $('#chatMessages')[0].scrollHeight;
            $('#chatmessageInput').val(''); 
            
            const botMessageContainer = $('<div class="message message-bot"></div>');
            const botAvatar = $('<img>', {
                src: '/helpdesk/images/ai-logo.png',
                alt: 'Bot Avatar',
                class: 'avatar',
                css: {
                    width: '40px',
                    height: '40px',
                    borderRadius: '50%',
                    marginRight: '10px'
                }
            });

            // Append avatar to bot message container
            botMessageContainer.append(botAvatar);

            // Create processing (typing) message element
            const typingIndicator = $('<div class="typing-indicator"><span></span><span></span><span></span></div>');
            typingIndicator.css('display', 'block');
            $('#chatMessages').append(typingIndicator);
            // console.log("MESSAGE PROCESS")

            setTimeout(function () {
                const formData = new FormData();
                formData.append('action', 'get_message_ai');
                formData.append('user_message', message);
                formData.append('group_id', groupValue);

                $.ajax({
                    url: "/classroom/study/actions/chat.php",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(data) {
                        
                        typingIndicator.css('display', 'none');
                        console.log("RESPONSE FROM AI", data);

                        // Format reply text
                        let htmlText = '';
                        if (data.reply) {
                            htmlText = htmlToPlainText(data.reply.replace(/\n/g, "<br>"));
                        }

                        // Format date/time
                        let dateTimeFormatBot = '';
                        if (data.date_time) {
                            let dateObjBot = new Date(data.date_time);
                            
                            // Date 
                            let monthBot = String(dateObjBot.getMonth() + 1).padStart(2, '0');
                            let dayBot = String(dateObjBot.getDate()).padStart(2, '0');
                            let yearBot = String(dateObjBot.getFullYear()).slice(-2);

                            // Time
                            let hoursBot = dateObjBot.getHours();
                            let minutesBot = String(dateObjBot.getMinutes()).padStart(2, '0');
                            let ampmBot = hoursBot >= 12 ? 'PM' : 'AM';

                            hoursBot = hoursBot % 12;
                            hoursBot = hoursBot ? hoursBot : 12;
                            let formattedHourBot = String(hoursBot).padStart(2, '0');

                            // DateTime
                            dateTimeFormatBot = `${monthBot}/${dayBot}/${yearBot} at ${formattedHourBot}.${minutesBot} ${ampmBot}`;
                        }

                        let urlLink = '';
                        if (data.url) {
                            urlLink = `<a style="padding: 1px 6px; color: #ff7e00;" href="${data.url}" target="_blank"><i class="fas fa-link"></i></a>`;
                        }
                        
                       const messageHtml = `
                            <div class="message message-bot">
                                <div class="message-wrapper">
                                    <div class="message-content">
                                        ${htmlText}
                                    </div>
                                    <div class="message-time">${dateTimeFormatBot}</div>
                                    <div class="message-actions">
                                        <i class="fa fa-files-o btn-coppy" title="Copy" style="color: #999; font-size: 14px; cursor: pointer;"></i>
                                        <i class="fa fa-rotate-right" title="Regenerate" style="color: #999; font-size: 14px; cursor: pointer;"></i>
                                        <i class="fa fa-thumbs-o-up btn-like" title="Good response" style="color: #999; font-size: 14px; cursor: pointer;"></i>
                                        <i class="fa fa-thumbs-o-down btn-dislike" title="Bad response" style="color: #999; font-size: 14px; cursor: pointer;"></i>
                                        ${urlLink}
                                    </div>
                                </div>
                            </div>
                        `;

                        $('#chatMessages').append(messageHtml);

                        // Copy button handler
                        const coppyBtn = $('#chatMessages .btn-coppy:last');
                        coppyBtn.off('click').on('click', function() {
                            const cleanText = htmlText ? htmlText.replace(/<br>/g, '\n').replace(/<[^>]*>/g, '') : '';
                            inputField.value = cleanText;
                            
                            coppyBtn.html('✅ Copied!');
                            setTimeout(() => {
                                coppyBtn.html('<i class="fa fa-files-o"></i>');
                            }, 2000);
                        });

                        // Like button handler
                        const likeBtn = $('#chatMessages .btn-like:last');
                        likeBtn.off('click').on('click', function() {
                            sendInterestAI(urlApi_chat, 'like_ai', data.chat_id, null, null)
                            .then(responseData => {
                                if (responseData.status) {
                                    likeBtn.html('<i class="fas fa-thumbs-up"></i>');
                                    likeBtn.closest('.message-actions').find('.btn-dislike').html('<i class="far fa-thumbs-down"></i>');
                                }
                            })
                            .catch(error => console.error('Caught outside:', error));
                        });

                        // Dislike button handler
                        const dislikeBtn = $('#chatMessages .btn-dislike:last');
                        dislikeBtn.off('click').on('click', function() {
                            sendInterestAI(urlApi_chat, 'not_like_ai', data.chat_id, null, null)
                            .then(responseData => {
                                if (responseData.status) {
                                    dislikeBtn.html('<i class="fas fa-thumbs-down"></i>');
                                    dislikeBtn.closest('.message-actions').find('.btn-like').html('<i class="far fa-thumbs-up"></i>');
                                }
                            })
                            .catch(error => console.error('Caught outside:', error));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        processingMessage.textContent = 'Error processing your message.';
                    }
                });
            }, 3000);
        } 
    })

    // SEND CHAT MESSAGE ON ENTER KEY
    $("#chatmessageInput").on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $("#sendMessageButton").click(); // ensure sendButton is the correct ID or selector for your button
        }
    });

    // OPEN CHAT HISTORY MODAL
    $("#chatHistoryButton").on('click', function(e){
        e.preventDefault();

        const formData = new FormData();
        formData.append('action', 'get_history');

        $.ajax({
            url: "/classroom/study/actions/chat.php",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.data_history) {
                    let tableHTML = `
                        <table id="chat-history-table" class="table table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Date created</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    $.each(data.data_history, function(_, item) {
                        tableHTML += `
                            <tr data-id="${item.group_id}">
                                <td>${item.subject}</td>
                                <td>${item.date_created}</td>
                            </tr>
                        `;
                    });

                    tableHTML += `</tbody></table>`;

                    modalContent.html(`
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h4 style="margin-top:0">
                                <i class="fas fa-comments"></i>
                                Chat History
                            </h4>
                            <div style="text-align:right; margin-bottom: 10px;">
                                <button id="close-modal-btn" style="
                                    padding: 8px 12px;
                                    border: none;
                                    background-color: #9b9b9b;
                                    color: white;
                                    border-radius: 5px;
                                    cursor: pointer;
                                ">Close</button>
                            </div>
                        </div>
                        ${tableHTML}
                    `);

                    $('#chat-history-table tbody tr').off('click').on('click', function() {
                        const groupId = $(this).data('id');
                        inputField.attr('data-group', groupId);

                        let detailsForm = new FormData();
                        detailsForm.append('action', 'history_inchat');
                        detailsForm.append('group_id', groupId);

                        $.ajax({
                            url: "/classroom/study/actions/chat.php",
                            method: 'POST',
                            data: detailsForm,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(messages) {
                                if (!messages.data || messages.data.length === 0) {
                                    // $('.chatHistoryModal').modal('hide');                    
                                    messagesContainer.empty();

                                    const botMessageContainer = $('<div>').css({
                                        display: 'flex',
                                        alignItems: 'center',
                                        marginBottom: '10px'
                                    });

                                    const botAvatar = $('<img>').attr('src', '/helpdesk/images/ai-logo.png').css({
                                        width: '40px',
                                        height: '40px',
                                        borderRadius: '50%',
                                        marginRight: '10px'
                                    });

                                    const botMessage = $('<div>').addClass('message bot resizable-text').text('Hello! How can we help?').css({
                                        padding: '8px',
                                        borderRadius: '10px',
                                        backgroundColor: 'rgb(255 250 245)',
                                        border: '1px solid #ff9a4c',
                                        maxWidth: '70%'
                                    });

                                    botMessageContainer.append(botAvatar, botMessage);
                                    messagesContainer.append(botMessageContainer);
                                    messagesContainer.scrollTop(messagesContainer[0].scrollHeight);

                                    inputField.attr('data-group', '');
                                    return;
                                }

                                // Fetch user avatar once
                                let userForm = new FormData();
                                userForm.append('action', 'get_user');

                                $.ajax({
                                    url: "/classroom/study/actions/chat.php",
                                    method: 'POST',
                                    data: userForm,
                                    processData: false,
                                    contentType: false,
                                    dataType: 'json',
                                    success: function(userData) {
                                        const userAvatarURL = '/' + userData.user_pic;
                                        const botAvatarURL = '/helpdesk/images/ai-logo.png';

                                        messagesContainer.empty();
                                        $('.chatHistoryModal').modal('hide');                    

                                        $.each(messages.data, function(_, msg) {
                                            let dateTimeFormat = '';
                                            if (msg.date_created) {
                                                const dateObj = new Date(msg.date_created);
                                                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                                                const day = String(dateObj.getDate()).padStart(2, '0');
                                                const year = String(dateObj.getFullYear()).slice(-2);
                                                let hours = dateObj.getHours();
                                                const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                                hours = hours % 12 || 12;
                                                const formattedHour = String(hours).padStart(2, '0');
                                                dateTimeFormat = `${month}/${day}/${year} at ${formattedHour}.${minutes} ${ampm}`;
                                            }

                                            let urlRef = '';
                                            if (msg.url) {
                                                urlRef = `<a style="padding: 1px 6px; color: #ff7e00;" href="${msg.url}" target="_blank"><i class="fas fa-link"></i></a>`;
                                            }

                                            let htmlInterest_like = msg.like === '1' ? `<i class="fas fa-thumbs-up"></i>` : `<i class="far fa-thumbs-up"></i>`;
                                            let htmlInterest_notlike = msg.not_like === '1' ? `<i class="fas fa-thumbs-down"></i>` : `<i class="far fa-thumbs-down"></i>`;

                                            const textMsg = (msg.message || '').replace(/\n/g, '<br>');
                                            const chatId = msg.chat_id;

                                            if (msg.key === 'R') {
                                                const messageContainer = $('<div>').css({
                                                    display: 'flex',
                                                    justifyContent: 'flex-end',
                                                    marginBottom: '10px'
                                                });
                                                const message = $('<div>').addClass('message user resizable-text').css({
                                                    padding: '8px',
                                                    borderRadius: '10px',
                                                    backgroundColor: '#d6ebf9',
                                                    maxWidth: '70%',
                                                    marginRight: '10px'
                                                });
                                                const mainText = $(`
                                                    <div>
                                                        ${textMsg}<br>
                                                        <div style="font-size: 9px;">${dateTimeFormat}</div>
                                                    </div>`);
                                                message.append(mainText);

                                                const avatar = $('<img>').attr('src', userAvatarURL).css({
                                                    width: '40px',
                                                    height: '40px',
                                                    borderRadius: '50%',
                                                    objectFit: 'contain',
                                                    border: '1px solid #f2f2f2'
                                                });

                                                messageContainer.append(message, avatar);
                                                messagesContainer.append(messageContainer);
                                            }

                                            if (msg.key === 'L') {
                                                const botContainer = $('<div>').css({
                                                    display: 'flex',
                                                    marginBottom: '10px'
                                                });

                                                const avatar = $('<img>').attr('src', botAvatarURL).css({
                                                    width: '40px',
                                                    height: '40px',
                                                    borderRadius: '50%',
                                                    marginRight: '10px'
                                                });

                                                const reply = $('<div>').addClass('message bot resizable-text').css({
                                                    padding: '8px',
                                                    borderRadius: '10px',
                                                    backgroundColor: 'rgb(255 250 245)',
                                                    border: '1px solid #ff9a4c',
                                                    maxWidth: '70%'
                                                });

                                                const replyHTML = `
                                                    <div>
                                                        ${textMsg}
                                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap;">
                                                            <div>
                                                                <button type="button" class="btn-coppy" style="border: none; background: none; color: #ff7e00;"><i class="far fa-clone"></i></button>
                                                                <button type="button" class="btn-like" style="border: none; background: none; color: #ff7e00;">${htmlInterest_like}</button>
                                                                <button type="button" class="btn-dislike" style="border: none; background: none; color: #ff7e00;">${htmlInterest_notlike}</button>
                                                                ${urlRef}
                                                            </div>
                                                            <div class="show-Interest"></div>
                                                        </div>
                                                        <div style="padding: 1px 6px; font-size: 10px;">
                                                            ${dateTimeFormat}
                                                        </div>
                                                    </div>
                                                `;

                                                reply.html(replyHTML);
                                                botContainer.append(avatar, reply);
                                                messagesContainer.append(botContainer);

                                                // Buttons event handlers
                                                reply.find('.btn-coppy').off('click').on('click', function() {
                                                    const cleanText = textMsg.replace(/<[^>]*>/g, '');
                                                    inputField.val(cleanText);
                                                    $(this).text('✅ Copied!');
                                                    setTimeout(() => {
                                                        $(this).html('<i class="far fa-clone"></i>');
                                                    }, 2000);
                                                });

                                                reply.find('.btn-like').off('click').on('click', function() {
                                                    sendInterestAI(urlApi_chat, 'like_ai', chatId, null, null)
                                                        .then(data => {
                                                            if (data.status) {
                                                                $(this).html('<i class="fas fa-thumbs-up"></i>');
                                                                reply.find('.btn-dislike').html('<i class="far fa-thumbs-down"></i>');
                                                            }
                                                        })
                                                        .catch(error => console.error('Caught outside:', error));
                                                });

                                                reply.find('.btn-dislike').off('click').on('click', function() {
                                                    sendInterestAI(urlApi_chat, 'not_like_ai', chatId, null, null)
                                                        .then(data => {
                                                            if (data.status) {
                                                                $(this).html('<i class="fas fa-thumbs-down"></i>');
                                                                reply.find('.btn-like').html('<i class="far fa-thumbs-up"></i>');
                                                            }
                                                        })
                                                        .catch(error => console.error('Caught outside:', error));
                                                });
                                            }
                                        });

                                        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Error loading user data:', error);
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading messages:', error);
                            }
                        });
                    });

                    $('#close-modal-btn').off('click').on('click', function() {
                        $('.chatHistoryModal').modal('hide');                    
                    });
                } else {

                    // Set modal content first
                    // $('.chatHistoryModal .modal-title').html('<p><p>!');
                    $('.chatHistoryModal  .modal-body').html('<p>No chat history found.</p>');
        
                    // Then show modal
                    $('.chatHistoryModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('.chatHistoryModal .modal-title').html('<p>Error!</p>');
                $('.chatHistoryModal  .modal-body').html('<p>Something Happend. No chat history found.</p>');
        
                    // Then show modal
                $('.chatHistoryModal').modal('show');
            }
        });
    });


    // CLEAR CHAT MESSAGE BUTTON
    $("#clearChatButton").on('click', function(e){
        e.preventDefault();
        $('#chatMessages').innerHTML = '';

        var emp_id = $('#emp_id').val();
        var comp_id = $('#comp_id').val();

        const formData = new FormData();
        formData.append('action', 'create_chat');
        formData.append('emp_id', emp_id);
        formData.append('comp_id', comp_id);

        $.ajax({
            url: "/classroom/study/actions/chat.php",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                console.log("DATA", data);
                console.log("DATA", data.status);

                if(data.status == true){
                    $("#chatmessageInput").attr('data-group', data.group_id);
                } else {
                    const messageDiv = $('<div class="message message-bot"></div>');
                    const plainText = htmlToPlainText(data.message || '');
                    const contentDiv = $('<div class="message-content"></div>').html(plainText.replace(/\n/g, '<br>'));
                    messageDiv.append(contentDiv);  // Content goes inside message
                    
                    $("#chatMessages").append(messageDiv);
                    
                    // Auto-scroll to bottom
                    $("#chatMessages")[0].scrollTop = $("#chatMessages")[0].scrollHeight;

                    $("#sendMessageButton").attr("disabled", true);
                    $("#sendMessageButton").css("background-color", "LightGrey"); 
                    $("#chatmessageInput").attr("disabled", true);
                    $("#chatmessageInput").attr("placeholder", "Service Unavaialble, Please contact Allable Team.");

                    
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });

});



});



//         // Create bot message container with processing animation
//         var botMessageContainer = document.createElement('div');
//         botMessageContainer.style.display = 'flex';
//         botMessageContainer.style.alignItems = 'center';
//         botMessageContainer.style.marginBottom = '10px';

//         var botAvatar = document.createElement('img');
//         botAvatar.src = '/helpdesk/images/ai-logo.png';
//         botAvatar.style.width = '40px';
//         botAvatar.style.height = '40px';
//         botAvatar.style.borderRadius = '50%';
//         botAvatar.style.marginRight = '10px';

//         var processingMessage = document.createElement('div');
//         processingMessage.classList.add('message', 'bot', 'resizable-text');
//         // processingMessage.style.padding = '8px';
//         // processingMessage.style.borderRadius = '10px';
//         // processingMessage.style.backgroundColor = '#f1f1f1';
//         // processingMessage.style.maxWidth = '70%';
//         processingMessage.style.padding = '8px';
//         processingMessage.style.borderRadius = '10px';
//         processingMessage.style.backgroundColor = 'rgb(255 250 245)';
//         processingMessage.style.border = '1px solid #ff9a4c';
//         processingMessage.style.maxWidth = '70%';

//         // Start animation
//         let dotCount = 0;
//         let processingText = 'Processing';
//         let dotInterval = setInterval(() => {
//             dotCount = (dotCount + 1) % 4; // 0-3 dots
//             processingMessage.textContent = processingText + '.'.repeat(dotCount);
//         }, 500);

//         botMessageContainer.appendChild(botAvatar);
//         botMessageContainer.appendChild(processingMessage);
//         messagesContainer.appendChild(botMessageContainer);
//         messagesContainer.scrollTop = messagesContainer.scrollHeight;

//         // Call API after delay
//         setTimeout(function () {
//             const formData = new FormData();
//             formData.append('action', 'get_message_ai');
//             formData.append('user_message', message);
//             formData.append('group_id', groupValue);
            
//             fetch(urlApi_chat, {
//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => {
//                 if (!response.ok) throw new Error('Network response was not ok');
//                 return response.json();
//             })
//             .then(data => {

//                 // console.log('data', data);

//                 clearInterval(dotInterval); // Stop

//                 let url = '';
//                 if (data.url) {
//                     url = `<a style="padding: 1px 6px; color: #ff7e00;" href="${data.url}" target="_blank"><i class="fas fa-link"></i></a>`;
//                 }

//                 let htmlText = '';
//                 if(data.reply){
//                     htmlText = data.reply.replace(/\n/g, "<br>");
//                 }

//                 let dateTimeFormatBot = '';
//                 if(data.date_time){
//                     let dateObjBot = new Date(data.date_time);

//                      // Date 
//                     let monthBot = String(dateObjBot.getMonth() + 1).padStart(2, '0');
//                     let dayBot = String(dateObjBot.getDate()).padStart(2, '0');
//                     let yearBot = String(dateObjBot.getFullYear()).slice(-2);

//                     // Time
//                     let hoursBot = dateObjBot.getHours();
//                     let minutesBot = String(dateObjBot.getMinutes()).padStart(2, '0');
//                     let ampmBot = hoursBot >= 12 ? 'PM' : 'AM';

//                     hoursBot = hoursBot % 12;
//                     hoursBot = hoursBot ? hoursBot : 12;
//                     let formattedHourBot = String(hoursBot).padStart(2, '0');

//                     // DateTime
//                     dateTimeFormatBot = `${monthBot}/${dayBot}/${yearBot} at ${formattedHourBot}.${minutesBot} ${ampmBot}`;
//                 }

//                 let htmlAction = '';
//                 if(data.chat_id){
//                     htmlAction = `
//                     <div class="message-time" style="font-size: 11px; color: #999; margin-top: 4px; padding-left: 5px;">
//                         ${dateTimeFormatBot}
//                     </div>
//                     <div class="message-actions" style="display: flex; align-items: center; gap: 8px; margin-top: 5px; padding-left: 5px;">
//                         <i class="fa fa-files-o btn-coppy" title="Copy" style="color: #999; font-size: 14px; cursor: pointer;"></i>
//                         <i class="fa fa-rotate-right" title="Regenerate" style="color: #999; font-size: 14px; cursor: pointer;"></i>
//                         <i class="fa fa-thumbs-o-up btn-like" title="Good response" style="color: #999; font-size: 14px; cursor: pointer;"></i>
//                         <i class="fa fa-thumbs-o-down btn-dislike" title="Bad response" style="color: #999; font-size: 14px; cursor: pointer;"></i>
//                         ${url}
//                     </div>
//                     `;
//                 }
                
//                 // processingMessage.innerHTML = htmlText + url;
//                 processingMessage.innerHTML = `
//                 <div style="padding: 12px 16px; border-radius: 18px; border-bottom-left-radius: 4px; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
//                     ${htmlText}
//                 </div>
//                 ${htmlAction}
//                 `;

//                 const coppyBtn = processingMessage.querySelector('.btn-coppy');
//                 const likeBtn = processingMessage.querySelector('.btn-like');
//                 const dislikeBtn = processingMessage.querySelector('.btn-dislike');
//                 // const showInterest = processingMessage.querySelector('.show-Interest');

//                 if(coppyBtn){

//                     coppyBtn.addEventListener('click', () => {

//                         const cleanText = htmlText ? htmlText.replace(/<[^>]*>/g, '') : '';
//                         inputField.value = cleanText;
                        
//                         coppyBtn.textContent = '✅ Copied!';
//                         setTimeout(() => {
//                             coppyBtn.innerHTML = '<i class="far fa-clone"></i>';
//                         }, 2000);

//                     });

//                 }

//                 if(likeBtn){

//                     likeBtn.addEventListener('click', () => {
//                         sendInterestAI(urlApi_chat, 'like_ai', data.chat_id, null, null)
//                         .then(data => {
//                             if(data.status){

//                                 likeBtn.innerHTML = '<i class="fas fa-thumbs-up"></i>';
//                                 dislikeBtn.innerHTML = '<i class="far fa-thumbs-down"></i>';


//                                 // showInterest.innerHTML = `<span 
//                                 // style="
//                                 // padding: 1px 6px;
//                                 // color: #ffffff;
//                                 // font-weight: 700;
//                                 // background-color: #4caf50;
//                                 // border-radius: 8px;
//                                 // ">
//                                 // Yes
//                                 // </span>`;
//                             }
//                         })
//                         .catch(error => {
//                             console.error('Caught outside:', error);
//                         });
//                     });

//                 }
                
//                 if(dislikeBtn){

//                     dislikeBtn.addEventListener('click', () => {
//                         sendInterestAI(urlApi_chat, 'not_like_ai', data.chat_id, null, null)
//                         .then(data => {
//                             if(data.status){

//                                 dislikeBtn.innerHTML = '<i class="fas fa-thumbs-down"></i>';
//                                 likeBtn.innerHTML = '<i class="far fa-thumbs-up"></i>';

//                                 // showInterest.innerHTML = `<span
//                                 // style="
//                                 // padding: 1px 6px;
//                                 // color: #ffffff;
//                                 // font-weight: 700;
//                                 // background-color: #9E9E9E;
//                                 // border-radius: 8px;
//                                 // "
//                                 // >
//                                 // No
//                                 // </span>`;
//                             }
//                         })
//                         .catch(error => {
//                             console.error('Caught outside:', error);
//                         });
//                     });

//                 }


//             })
//             .catch(error => {
//                 console.error('Error:', error);
//                 clearInterval(dotInterval); // Stop animation on error
//                 processingMessage.textContent = 'Error processing your message.';
//             });
//         }, 3000);

//     }
// });

// Handle Enter key as send

function htmlToPlainText(html) {
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    return tempDiv.innerText || tempDiv.textContent;
}

function scrollToBottom() {
    chatMessages.scrollTop(chatMessages[0].scrollHeight);
}