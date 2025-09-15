 async function initForm(type, id) {
        const isTeacher = (type === 'teacher');
        const formTitle = (id ? 'Edit ' : 'Add ') + (isTeacher ? 'Teacher Information' : 'Student Information');

        const formTemplate = `
           <div class="card">
                    <div class="card-header">
                        <h5 class="modal-title">${formTitle}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-origami nav-contact " style="font-size:1.2em">
                            <li class="nav active"><a href="#${type}_personal_contact_tab" data-toggle="tab"><i class="fas fa-user-circle" style="color: #007bff;"></i> Personal Contact </a></li>
                            <li class="nav"><a href="#${type}_bio_tab" data-toggle="tab"><i class="fas fa-address-card" style="color: #6c757d;"></i> Biography </a></li>
                            <li class="nav"><a href="#${type}_favorite_tab" data-toggle="tab"><i class="fas fa-heart" style="color: #ff9800;"></i> Favorite </a></li>
                            <li class="nav"><a href="#${type}_setup_tab" data-toggle="tab"><i class="fas fa-cog" style="color: #6c757d;"></i> Login Setup </a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="${type}_personal_contact_tab">
                                <form id="frm_${type}" action="save_${type}" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id" id="${type}_id" value="${id || ''}">
                                    <input type="hidden" name="type" value="${type}">

                                    <div class="form-box">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group g-4 text-center mt-3">
                                                    <label for="${type}_image_profile" class="control-label">
                                                        <i class="fas fa-camera-retro" style="color: #6c757d;"></i> Profile Picture 📸
                                                    </label>
                                                    <div class="preview-uploads preview-uploads-logo">
                                                        <div class="image-placeholder">
                                                            <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                                            <h5 class="text-muted mt-2">Drag and drop an image here<br>or click to upload</h5>
                                                            <input name="${type}_image_profile" id="${type}_image_profile" type="file" onchange="readURL(this, '${type}_image_profile_preview');" class="d-none" accept="image/*">
                                                        </div>
                                                        <div class="image-preview" style="display: none;">
                                                            <img id="${type}_image_profile_preview" src="" alt="Image Preview">
                                                            <div class="image-actions">
                                                                <a href="#" onclick="previewImage('${type}_image_profile_preview');" class="preview-btn">View</a>
                                                                <a href="#" onclick="removeImage('${type}_image_profile_preview', '${type}_image_profile');" class="remove-btn">Remove</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="${type}_image_profile_current" id="${type}_image_profile_current" value="">
                                                </div>
                                                <div class="form-group g-4 row mt-3">
                                                    <div class="col-sm-12">
                                                        <label for="${type}_gender" class="control-label">เพศ 🚻</label>
                                                        <input type="text" name="${type}_gender" id="${type}_gender" class="form-control" list="gender-list">
                                                        <datalist id="gender-list"></datalist>
                                                    </div>
                                                </div>
                                                <div class="form-group g-4 row">
                                                    <div class="col-sm-12">
                                                        <label for="${type}_birth_date" class="control-label">วันเกิด 🎂</label>
                                                        <div class="input-group">
                                                            <input type="text" name="${type}_birth_date" id="${type}_birth_date" class="form-control datepicker">
                                                            <span class="input-group-addon"><i class="fas fa-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="form-group g-4 col-sm-12">
                                                        <label for="${type}_perfix" class="control-label">คำนำหน้า </label>
                                                        <input type="text" name="${type}_perfix" id="${type}_perfix" class="form-control" list="prefix-list">
                                                        <datalist id="prefix-list"></datalist>
                                                    </div>
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_firstname_en" class="control-label">Firstname (EN) *</label>
                                                        <input type="text" name="${type}_firstname_en" id="${type}_firstname_en" class="form-control" required>
                                                    </div>
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_lastname_en" class="control-label">Lastname (EN) *</label>
                                                        <input type="text" name="${type}_lastname_en" id="${type}_lastname_en" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_firstname_th" class="control-label">ชื่อจริง (TH)</label>
                                                        <input type="text" name="${type}_firstname_th" id="${type}_firstname_th" class="form-control">
                                                    </div>
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_lastname_th" class="control-label">นามสกุล (TH)</label>
                                                        <input type="text" name="${type}_lastname_th" id="${type}_lastname_th" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_nickname_th" class="control-label">ชื่อเล่น (TH) </label>
                                                        <input type="text" name="${type}_nickname_th" id="${type}_nickname_th" class="form-control">
                                                    </div>
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_nickname_en" class="control-label">Nickname (EN) </label>
                                                        <input type="text" name="${type}_nickname_en" id="${type}_nickname_en" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_idcard" class="control-label">เลขบัตรประชาชน 🆔</label>
                                                        <input type="text" name="${type}_idcard" id="${type}_idcard" class="form-control">
                                                    </div>
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_passport" class="control-label">เลขหนังสือเดินทาง 🛂</label>
                                                        <input type="text" name="${type}_passport" id="${type}_passport" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-box mt-4">
                                        <div class="row">
                                            <div class="col-sm-6 text-center">
                                                <label for="${type}_card_front" class="control-label">บัตรประชาชน (ด้านหน้า) 💳</label>
                                                <div class="preview-uploads preview-uploads-card">
                                                    <div class="image-placeholder">
                                                        <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                                        <h5 class="text-muted mt-2">Drag and drop an image<br>or click to upload</h5>
                                                        <input name="${type}_card_front" id="${type}_card_front" type="file" onchange="readURL(this, '${type}_card_front_preview');" class="d-none" accept="image/*">
                                                    </div>
                                                    <div class="image-preview" style="display: none;">
                                                        <img id="${type}_card_front_preview" src="" alt="Front Name Card Preview">
                                                        <div class="image-actions">
                                                            <a href="#" onclick="previewImage('${type}_card_front_preview');" class="preview-btn">View</a>
                                                            <a href="#" onclick="removeImage('${type}_card_front_preview', '${type}_card_front');" class="remove-btn">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="${type}_card_front_current" id="${type}_card_front_current" value="">
                                            </div>
                                            <div class="col-sm-6 text-center">
                                                <label for="${type}_card_back" class="control-label">บัตรประชาชน (ด้านหลัง) 🪪</label>
                                                <div class="preview-uploads preview-uploads-card">
                                                    <div class="image-placeholder">
                                                        <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                                        <h5 class="text-muted mt-2">Drag and drop an image<br>or click to upload</h5>
                                                        <input name="${type}_card_back" id="${type}_card_back" type="file" onchange="readURL(this, '${type}_card_back_preview');" class="d-none" accept="image/*">
                                                    </div>
                                                    <div class="image-preview" style="display: none;">
                                                        <img id="${type}_card_back_preview" src="" alt="Back Name Card Preview">
                                                        <div class="image-actions">
                                                            <a href="#" onclick="previewImage('${type}_card_back_preview');" class="preview-btn">View</a>
                                                            <a href="#" onclick="removeImage('${type}_card_back_preview', '${type}_card_back');" class="remove-btn">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="${type}_card_back_current" id="${type}_card_back_current" value="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-box mt-4">
                                        <h6 class="control-label">ช่องทางการติดต่อ </h6>
                                        <hr class="mt-2 mb-4">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_email" class="control-label"><i class="fas fa-envelope" style="color: #da4636ff; margin-right: 5px;"></i> Email </label>
                                                    <input type="email" name="${type}_email" id="${type}_email" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_mobile" class="control-label"><i class="fas fa-phone-alt" style="color: #2c85d2ff; margin-right: 5px;"></i> เบอร์มือถือ </label>
                                                    <input type="text" name="${type}_mobile" id="${type}_mobile" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_facebook" class="control-label"><i class="fab fa-facebook" style="color: #4267B2; margin-right: 5px;"></i> Facebook </label>
                                                    <input type="text" name="${type}_facebook" id="${type}_facebook" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_line" class="control-label"><i class="fab fa-line" style="color: #00B900; margin-right: 5px;"></i> Line ID </label>
                                                    <input type="text" name="${type}_line" id="${type}_line" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_ig" class="control-label"><i class="fab fa-instagram" style="color: #E1306C; margin-right: 5px;"></i> Instagram </label>
                                                    <input type="text" name="${type}_ig" id="${type}_ig" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_address" class="control-label"><i class="fas fa-map-marker-alt" style="color: #ff9800; margin-right: 5px;"></i> ที่อยู่</label>
                                                    <textarea name="${type}_address" id="${type}_address" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="${type}_bio_tab">
                                <form id="frm_${type}_bio" class="mt-3">
                                    <div class="form-box">
                                        <div class="form-group">
                                            <label for="${type}_bio" class="control-label"><i class="fas fa-book-open" style="color: #ff9800; margin-right: 5px;"></i> Biography </label>
                                            <textarea name="${type}_bio" id="${type}_bio" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_education" class="control-label"><i class="fas fa-graduation-cap" style="color: #ff9800; margin-right: 5px;"></i> Education </label>
                                            <textarea name="${type}_education" id="${type}_education" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_experience" class="control-label"><i class="fas fa-briefcase" style="color: #ff9800; margin-right: 5px;"></i> Experience </label>
                                            <textarea name="${type}_experience" id="${type}_experience" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                <label for="${type}_company" class="control-label"><i class="fas fa-building" style="color: #ff9800; margin-right: 5px;"></i> Workplace/School </label>
                                                <input type="text" name="${type}_company" id="${type}_company" class="form-control">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_position" class="control-label"><i class="fas fa-user-tag" style="color: #ff9800; margin-right: 5px;"></i> Position </label>
                                                <input type="text" name="${type}_position" id="${type}_position" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                <label for="${type}_religion" class="control-label"><i class="fas fa-hand-holding-heart" style="color: #ff9800; margin-right: 5px;"></i> Religion </label>
                                                <input type="text" name="${type}_religion" id="${type}_religion" class="form-control">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_bloodgroup" class="control-label"><i class="fas fa-tint" style="color: #ff9800; margin-right: 5px;"></i> Blood Group </label>
                                                <input type="text" name="${type}_bloodgroup" id="${type}_bloodgroup" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_attach_document" class="control-label"><i class="fas fa-paperclip" style="color: #ff9800; margin-right: 5px;"></i> Other Attachments </label>
                                            <div id="attach-document-fields"></div>
                                            <button type="button" class="btn btn-default mt-2" onclick="addDocumentField('', '${type}', true)"><i class="fas fa-plus"></i> Add Document</button>
                                        </div>
                                        <input type="hidden" name="${type}_attach_document_current[]">
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="${type}_favorite_tab">
                                <form id="frm_${type}_favorite" class="mt-3">
                                    <div class="form-box">
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                <label for="${type}_hobby" class="control-label"><i class="fas fa-palette" style="color: #ff9800; margin-right: 5px;"></i> Hobby </label>
                                                <input type="text" name="${type}_hobby" id="${type}_hobby" class="form-control">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_music" class="control-label"><i class="fas fa-music" style="color: #ff9800; margin-right: 5px;"></i> Favorite Music </label>
                                                <input type="text" name="${type}_music" id="${type}_music" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                <label for="${type}_movie" class="control-label"><i class="fas fa-film" style="color: #ff9800; margin-right: 5px;"></i> Favorite Movies </label>
                                                <input type="text" name="${type}_movie" id="${type}_movie" class="form-control">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_goal" class="control-label"><i class="fas fa-bullseye" style="color: #ff9800; margin-right: 5px;"></i> Life Goal </label>
                                                <input type="text" name="${type}_goal" id="${type}_goal" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="${type}_setup_tab">
                                <form id="frm_${type}_setup" class="mt-3">
                                    <div class="form-box">
                                        <div class="form-group">
                                            <label for="${type}_username" class="control-label"><i class="fas fa-user" style="color: #ff9800; margin-right: 5px;"></i> Username *</label>
                                            <input type="text" name="${type}_username" id="${type}_username" class="form-control required-field-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_password" class="control-label"><i class="fas fa-lock" style="color: #ff9800; margin-right: 5px;"></i> Password </label>
                                            <input type="password" name="${type}_password" id="${type}_password" class="form-control">
                                            <small class="text-muted">Fill in only if you want to change the password</small>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right" style="padding: 1em;">
                        <button type="button" class="btn btn-white" onclick="window.history.back()">Close</button>
                        <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </div>
        `;

        $("#form-container").html(formTemplate);

       // สร้าง datalist สำหรับคำนำหน้าชื่อ
        const prefixMap = getPrefixMap();
        const prefixList = $('#prefix-list');
        for (const key in prefixMap) {
            prefixList.append(`<option value="${prefixMap[key]}">`);
        }

        // สร้าง datalist สำหรับเพศ
        const genderMap = getGenderMap();
        const genderList = $('#gender-list');
        for (const key in genderMap) {
            genderList.append(`<option value="${genderMap[key]}">`);
        }

        // ✨ แก้ไขใหม่: เพิ่มโค้ดสำหรับแสดง dropdown ทั้งหมดเมื่อคลิก
        $(`#${type}_perfix, #${type}_gender`).on('focus', function() {
            // Clear any text that might be in the input to show all options
            $(this).attr('placeholder', $(this).val()); // Save current value as placeholder
            $(this).val('');
        }).on('blur', function() {
            // Restore the original value from the placeholder if no new option was selected
            if (!$(this).val()) {
                $(this).val($(this).attr('placeholder'));
            }
        });

        

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        if (id) {
            await loadData(type, id);
        }

        $('#saveBtn').on('click', function () {
            handleFormSubmission(type, id);
        });

        // Add orange border to required fields after the form is rendered
        $('input[required], textarea[required], select[required]').on('input', function() {
            if ($(this).val()) {
                $(this).removeClass('required-field-input-invalid');
                $(this).addClass('required-field-input');
            } else {
                $(this).addClass('required-field-input-invalid');
            }
        });

        $('input[required], textarea[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('required-field-input-invalid');
            }
        });
    }

// --- JAVASCRIPT CODE ---

// เพิ่มฟังก์ชันสำหรับสร้าง dropdown คำนำหน้าชื่อ
function getPrefixMap() {
    return {
        '0': 'ไม่ระบุ (N/A)',
        '1': 'นาย (Mr.)',
        '2': 'นาง (Mrs.)',
        '3': 'นางสาว (Ms.)',
        '4': 'เด็กชาย (Master)',
        '5': 'เด็กหญิง (Miss)'
    };
}

// เพิ่มฟังก์ชันสำหรับสร้าง object map ของเพศ
function getGenderMap() {
    return {
        'M': 'ชาย (Male)',
        'F': 'หญิง (Female)',
        'N': 'ไม่ระบุ (N/A)'
    };
}

// Helper Functions (Same as yours, but adding new ones)
function readURL(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $(`#${previewId}`).attr('src', e.target.result);
            $(`#${previewId}`).closest('.image-preview').show();
            $(`#${previewId}`).closest('.preview-uploads').find('.image-placeholder').hide();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function showFilePreview(inputName, filePath) {
    if (filePath) {
        $(`#${inputName}_preview`).attr('src', filePath);
        $(`#${inputName}_preview`).closest('.image-preview').show();
        $(`#${inputName}_preview`).closest('.preview-uploads').find('.image-placeholder').hide();
        $(`#${inputName}_current`).val(filePath);
    }
}

function removeImage(previewId, inputName) {
    $(`#${previewId}`).attr('src', '');
    $(`#${previewId}`).closest('.image-preview').hide();
    $(`#${previewId}`).closest('.preview-uploads').find('.image-placeholder').show();
    $(`#${inputName}`).val('');
    $(`#${inputName}_current`).val('');
}

// **New/Modified Function for Attach Documents**
function addDocumentField(docUrl = '', type, isNew = true) {
    const documentContainer = $(`#attach-document-fields`);
    const docId = `doc-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const fileName = docUrl ? docUrl.substring(docUrl.lastIndexOf('/') + 1) : '';

    const newFieldHtml = `
        <div class="input-group mb-2 document-field" id="${docId}">
            ${docUrl ? `
                <a href="${docUrl}" target="_blank" class="form-control btn btn-link text-left">
                    <i class="fas fa-file document-file-icon"></i>
                    <span class="document-file-link">${fileName}</span>
                </a>
                <input type="hidden" name="${type}_attach_document_current[]" value="${docUrl}">
            ` : `
                <input type="file" class="form-control" name="${type}_attach_document[]" multiple>
            `}
            <div class="input-group-append">
                <button class="btn btn-danger" type="button" style="margin-top:5px" onclick="removeDocumentField('${docId}', '${docUrl}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    documentContainer.append(newFieldHtml);
}

function removeDocumentField(docId, docUrl) {
    // If a document URL is provided, we need to handle the deletion logic on the server
    // For now, we'll just remove the element from the DOM
    $(`#${docId}`).remove();

    // You can add a new hidden input here to tell the server which file to delete if needed
    // Example: <input type="hidden" name="docs_to_delete[]" value="${docUrl}">
    console.log(`Removed document field with ID: ${docId}, URL: ${docUrl}`);
}

function loadData(type, id) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'actions/fetch.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetchData',
                type: type,
                id: id
            },
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    console.log('Data loaded:', data);



                    const prefixMap = getPrefixMap();
                    const perfixValue = data[`${type}_perfix`];
                    const perfixText = prefixMap[perfixValue] || '';
                    $(`#${type}_perfix`).val(perfixText);

                      // แก้ไข: เพิ่มการแปลงค่าเพศจากฐานข้อมูลมาแสดงผลในฟอร์ม
                    const genderMap = getGenderMap();
                    const genderValue = data[`${type}_gender`];
                    const genderText = genderMap[genderValue] || '';
                    $(`#${type}_gender`).val(genderText);

               

                    // Personal Information Tab
                    $(`#${type}_id`).val(data[`${type}_id`]);
                    // $(`#${type}_perfix`).val(data[`${type}_perfix_text`] || '');
                    $(`#${type}_firstname_th`).val(data[`${type}_firstname_th`]);
                    $(`#${type}_lastname_th`).val(data[`${type}_lastname_th`]);
                    $(`#${type}_firstname_en`).val(data[`${type}_firstname_en`]);
                    $(`#${type}_lastname_en`).val(data[`${type}_lastname_en`]);
                    $(`#${type}_nickname_th`).val(data[`${type}_nickname_th`]);
                    $(`#${type}_nickname_en`).val(data[`${type}_nickname_en`]);
                    $(`#${type}_idcard`).val(data[`${type}_idcard`]);
                    $(`#${type}_passport`).val(data[`${type}_passport`]);
                    // $(`#${type}_gender`).val(data[`${type}_gender_text`] || '');

                    // ✨ แก้ไขใหม่: จัดการการแสดงผลวันเกิด
                    if (data[`${type}_birth_date`] && data[`${type}_birth_date`] !== '0000-00-00') {
                        const dateParts = data[`${type}_birth_date`].split('-');
                        const formattedDate = `${dateParts[0]}-${dateParts[1]}-${dateParts[2]}`;
                        $(`#${type}_birth_date`).val(formattedDate);
                    } else {
                        $(`#${type}_birth_date`).val('');
                    }

                    showFilePreview(`${type}_image_profile`, data[`${type}_image_profile`]);
                    showFilePreview(`${type}_card_front`, data[`${type}_card_front`]);
                    showFilePreview(`${type}_card_back`, data[`${type}_card_back`]);

                    // Contact fields
                    $(`#${type}_email`).val(data[`${type}_email`]);
                    $(`#${type}_mobile`).val(data[`${type}_mobile`]);
                    $(`#${type}_facebook`).val(data[`${type}_facebook`]);
                    $(`#${type}_line`).val(data[`${type}_line`]);
                    $(`#${type}_ig`).val(data[`${type}_ig`]);
                    $(`#${type}_address`).val(data[`${type}_address`]);

                    // Bio Tab
                    $(`#${type}_bio`).val(data[`${type}_bio`]);
                    $(`#${type}_education`).val(data[`${type}_education`]);
                    $(`#${type}_experience`).val(data[`${type}_experience`]);
                    $(`#${type}_company`).val(data[`${type}_company`]);
                    $(`#${type}_position`).val(data[`${type}_position`]);
                    $(`#${type}_religion`).val(data[`${type}_religion`]);
                    $(`#${type}_bloodgroup`).val(data[`${type}_bloodgroup`]);

                    // Favorite Tab
                    $(`#${type}_hobby`).val(data[`${type}_hobby`]);
                    $(`#${type}_music`).val(data[`${type}_music`]);
                    $(`#${type}_movie`).val(data[`${type}_movie`]);
                    $(`#${type}_goal`).val(data[`${type}_goal`]);

                    // Attach Documents
                    const documentContainer = $(`#attach-document-fields`);
                    documentContainer.empty();
                    if (data[`${type}_attach_document`]) {
                        const documents = data[`${type}_attach_document`].split('|').filter(Boolean);
                        console.log('Documents to load:', documents);
                        documents.forEach(docUrl => {
                            addDocumentField(docUrl, type, false);
                        });
                    }

                    // Setup Tab
                    $(`#${type}_username`).val(data[`${type}_username`]);

                    resolve(data);
                } else {
                    console.error('Error loading data:', response.message);
                    swal("Error!", response.message, "error");
                    reject();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                swal("Error!", "Failed to fetch data.", "error");
                reject();
            }
        });
    });
}

// Function for form validation
function validateForm(type) {
    let isValid = true;
    let firstInvalidField = null;

    // Define the list of required fields and their validation rules
    const fieldsToValidate = [
        // Personal Contact Tab
        { id: `${type}_perfix`, tab: `${type}_personal_contact_tab`, message: 'Please select a prefix.' },
        { id: `${type}_firstname_en`, tab: `${type}_personal_contact_tab`, message: 'Please enter a first name in English.', pattern: /^[A-Za-z\s]+$/ },
        { id: `${type}_lastname_en`, tab: `${type}_personal_contact_tab`, message: 'Please enter a last name in English.', pattern: /^[A-Za-z\s]+$/ },
        { id: `${type}_idcard`, tab: `${type}_personal_contact_tab`, message: 'ID card number must be 13 digits.', pattern: /^[0-9]{13}$/ },
        { id: `${type}_passport`, tab: `${type}_personal_contact_tab`, message: 'Please enter a passport number.', optional: true }, // Optional but still checked
        { id: `${type}_gender`, tab: `${type}_personal_contact_tab`, message: 'Please select a gender.' },
        { id: `${type}_birth_date`, tab: `${type}_personal_contact_tab`, message: 'Please select a date of birth.' },
        { id: `${type}_email`, tab: `${type}_personal_contact_tab`, message: 'Please enter a valid email address.', pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ },
        { id: `${type}_mobile`, tab: `${type}_personal_contact_tab`, message: 'Please enter a mobile number.', optional: true }, // Assuming mobile is not always required
        { id: `${type}_address`, tab: `${type}_personal_contact_tab`, message: 'Please enter an address.' },
        
        // Biography Tab
        { id: `${type}_bio`, tab: `${type}_bio_tab`, message: 'Please enter a biography.' },
        { id: `${type}_education`, tab: `${type}_bio_tab`, message: 'Please enter your education history.' },
        { id: `${type}_experience`, tab: `${type}_bio_tab`, message: 'Please enter your experience.' },
        { id: `${type}_company`, tab: `${type}_bio_tab`, message: 'Please enter your workplace or school.' },
        { id: `${type}_position`, tab: `${type}_bio_tab`, message: 'Please enter your position.' },

        // Login Setup Tab
        { id: `${type}_username`, tab: `${type}_setup_tab`, message: 'Username must be at least 6 characters.', pattern: /^.{6,}$/ },
        { id: `${type}_password`, tab: `${type}_setup_tab`, message: 'Password must be at least 6 characters.', pattern: /^.{6,}$/, conditional: true } // Conditional field, required only when creating new user
    ];

    // Reset all previous error states
    $(`.form-control`).removeClass('required-field-input-invalid');
    $('.alert-danger').remove();

    // Loop through each field to perform validation
    for (const field of fieldsToValidate) {
        const $input = $(`#${field.id}`);
        const value = $input.val().trim();
        let currentFieldIsValid = true;

        // Check for required fields
        if (!field.optional && value === '') {
            currentFieldIsValid = false;
        }

        // Check for specific patterns (e.g., English alphabet, numbers)
        if (value !== '' && field.pattern && !field.pattern.test(value)) {
            currentFieldIsValid = false;
        }

        // Handle specific conditions
        if (field.id === `${type}_password` && ($(`#${type}_id`).val() && value === '')) {
            currentFieldIsValid = true; // Don't require password on edit if not changed
        }
        
        // If a field is not valid, mark it and break the loop
        if (!currentFieldIsValid) {
            isValid = false;
            $input.addClass('required-field-input-invalid');
            if (!firstInvalidField) {
                firstInvalidField = { input: $input, tab: field.tab, message: field.message };
            }
        } else {
            $input.removeClass('required-field-input-invalid');
        }
    }

    if (!isValid) {
        // Show an error message at the top of the form
        $('#form-container .card-header').after(`
            <div class="alert alert-danger mx-3" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <strong>Validation Error!</strong> Please fill in all required fields and correct any formatting errors.
            </div>
        `);
        
        // Switch to the correct tab and focus on the first invalid field
        $(`a[href="#${firstInvalidField.tab}"]`).tab('show');
        firstInvalidField.input.focus();
        
        // Add a tooltip or message near the input
        if (firstInvalidField.message) {
             console.log(`Validation Error: ${firstInvalidField.message}`);
             // Note: For a more advanced UI, you would add a tooltip here.
        }
    }

    return isValid;
}

function handleFormSubmission(type, id) {

    if (!validateForm(type)) {
        return; // Stop the function if validation fails
    }
    const $form = $(`#frm_${type}`);
    const formData = new FormData();
    let isValid = true;
    let errorMessage = "";

    // Append data from each form tab
    $(`#frm_${type}, #frm_${type}_bio, #frm_${type}_favorite, #frm_${type}_setup`).each(function() {
        const formFields = $(this).serializeArray();
        formFields.forEach(field => {
            formData.append(field.name, field.value);
        });
    });

    const classroom_id = $('#classroom_id').val();
    formData.append('classroom_id', classroom_id);

    // ✨ แก้ไขใหม่: แปลง format วันเกิดจาก YYYY/MM/DD กลับเป็น YYYY-MM-DD
    const birthDateValue = $(`#${type}_birth_date`).val();
    if (birthDateValue) {
        const dateParts = birthDateValue.split('-');
        if (dateParts.length === 3) {
            const formattedDate = `${dateParts[0]}-${dateParts[1]}-${dateParts[2]}`;
            formData.set(`${type}_birth_date`, formattedDate);
        } else {
            console.warn('Invalid date format:', birthDateValue);
        }
    } else {
        formData.set(`${type}_birth_date`, ''); // ส่งค่าว่างไปถ้าไม่มีข้อมูล
    }

    // Append single file data
    $(`input[type="file"]:not([name*="_attach_document"])`).each(function() {
        if (this.files.length > 0) {
            formData.append(this.name, this.files[0]);
        }
    });

    // Append multiple attached documents
    $(`input[name="${type}_attach_document[]"]`).each(function() {
        if (this.files.length > 0) {
            for (let i = 0; i < this.files.length; i++) {
                formData.append(this.name, this.files[i]);
            }
        }
    });

    // Append hidden current file paths
    $(`input[type="hidden"][name$="_current"]`).each(function() {
        formData.append(this.name, this.value);
    });

    // Append attached documents that are already saved and not removed
    $(`input[name="${type}_attach_document_current[]"]`).each(function() {
        formData.append(this.name, this.value);
    });

     // เพิ่มการแปลงคำนำหน้าจากข้อความเป็นตัวเลข
    const reversePrefixMap = {};
    const prefixMap = getPrefixMap();
    for (const key in prefixMap) {
        reversePrefixMap[prefixMap[key]] = key;
    }

    const perfixValue = formData.get(`${type}_perfix`);
    if (perfixValue) {
        const newPerfixValue = reversePrefixMap[perfixValue] || '0';
        formData.set(`${type}_perfix`, newPerfixValue);
        console.log(`แปลงคำนำหน้า: '${perfixValue}' -> '${newPerfixValue}'`);
    }

    // แก้ไข: เพิ่มการแปลงค่าเพศจากข้อความเป็นตัวย่อ
    const reverseGenderMap = {};
    const genderMap = getGenderMap();
    for (const key in genderMap) {
        reverseGenderMap[genderMap[key]] = key;
    }

    const genderValue = formData.get(`${type}_gender`);
    if (genderValue) {
        const newGenderValue = reverseGenderMap[genderValue] || 'N';
        formData.set(`${type}_gender`, newGenderValue);
        console.log(`แปลงเพศ: '${genderValue}' -> '${newGenderValue}'`);
    }


   
    // Append static data
    formData.append('action', 'saveData');
    formData.append('type', type);
    formData.append('id', id);

    // Validate form fields
    $form.find("[required]").each(function () {
        if (!$(this).val() || $(this).val().trim() === "") {
            isValid = false;
            errorMessage = "Please fill in all required fields.";
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });

    if (!isValid) {
        swal("Warning", errorMessage, "warning");
        return;
    }

    $.ajax({
        url: 'actions/fetch.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                swal("Success!", response.message, "success");
                setTimeout(() => {
                    const newId = response.id || id;
                    if (newId) {
                        window.location.href = `?type=${type}&id=${newId}`;
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                swal("Error!", response.message, "error");
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
            swal("Error!", "Failed to save data.", "error");
        }
    });
}

// Initial call
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('type');
    const formId = urlParams.get('id');

    if (formType) {
        initForm(formType, formId);
    } else {
        $("#form-container").html("<h3>ไม่พบประเภทข้อมูลที่ต้องการแสดง</h3>");
    }
});