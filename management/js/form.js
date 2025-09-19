 async function initForm(type, id) {
        const isTeacher = (type === 'teacher');
        const formTitle = (id ? 'Edit ' : 'Add ') + (isTeacher ? 'Teacher Information' : 'Student Information');

        const formTemplate = `
           <div class="card">
                    <div class="card-header">
                        <h5 class="modal-title" style="color: #7a7a7a;">${formTitle}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-origami nav-contact " style="font-size:1.2em">
                            <li class="nav active"><a href="#${type}_personal_contact_tab" data-toggle="tab"><i class="fas fa-user-circle" style="color: #0080ef;"></i> Personal Contact </a></li>
                            <li class="nav"><a href="#${type}_bio_tab" data-toggle="tab"><i class="fas fa-address-card" style="color: #0fa22ea6;"></i> Biography </a></li>
                            <li class="nav"><a href="#${type}_favorite_tab" data-toggle="tab"><i class="fas fa-heart" style="color: #e42c2c;"></i> Favorite </a></li>
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
                                                        <i class="fas fa-camera-retro" style="color: #6c757d;"></i> Profile Picture 
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
                                                                
                                                                <a href="#" onclick="removeImage('${type}_image_profile_preview', '${type}_image_profile');" class="remove-btn">Remove</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="${type}_image_profile_current" id="${type}_image_profile_current" value="">
                                                </div>
                                                <div class="form-group g-4 row mt-3">
                                                    <div class="col-sm-12">
                                                        <label for="${type}_gender" class="control-label">เพศ </label>
                                                        <input type="text" name="${type}_gender" id="${type}_gender" class="form-control" list="gender-list">
                                                        <datalist id="gender-list"></datalist>
                                                    </div>
                                                </div>
                                               <div class="form-group g-4 row">
                                                <div class="col-sm-12">
                                                    <label for="${type}_birth_date" class="control-label">วันเกิด </label>
                                                    <div class="input-group" style="width:100%;">
                                                        <input type="date" name="${type}_birth_date" id="${type}_birth_date" class="form-control">
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
                                                        <label for="${type}_idcard" class="control-label">เลขบัตรประชาชน </label>
                                                        <input type="text" name="${type}_idcard" id="${type}_idcard" class="form-control">
                                                    </div>
                                                    <div class="form-group g-4 col-sm-6">
                                                        <label for="${type}_passport" class="control-label">เลขหนังสือเดินทาง </label>
                                                        <input type="text" name="${type}_passport" id="${type}_passport" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-box mt-4">
                                        <div class="row">
                                            <div class="col-sm-6 text-center">
                                                <label for="${type}_card_front" class="control-label">บัตรประชาชน (ด้านหน้า) 🪪</label>
                                                <div class="preview-uploads preview-uploads-card">
                                                    <div class="image-placeholder">
                                                        <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                                        <h5 class="text-muted mt-2">Drag and drop an image<br>or click to upload</h5>
                                                        <input name="${type}_card_front" id="${type}_card_front" type="file" onchange="readURL(this, '${type}_card_front_preview');" class="d-none" accept="image/*">
                                                    </div>
                                                    <div class="image-preview" style="display: none;">
                                                        <img id="${type}_card_front_preview" src="" alt="Front Name Card Preview">
                                                        <div class="image-actions">
                                                            
                                                            <a  onclick="removeImage('${type}_card_front_preview', '${type}_card_front');" class="remove-btn">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="${type}_card_front_current" id="${type}_card_front_current" value="">
                                            </div>
                                            <div class="col-sm-6 text-center">
                                                <label for="${type}_card_back" class="control-label">บัตรประชาชน (ด้านหลัง)💳</label>
                                                <div class="preview-uploads preview-uploads-card">
                                                    <div class="image-placeholder">
                                                        <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                                        <h5 class="text-muted mt-2">Drag and drop an image<br>or click to upload</h5>
                                                        <input name="${type}_card_back" id="${type}_card_back" type="file" onchange="readURL(this, '${type}_card_back_preview');" class="d-none" accept="image/*">
                                                    </div>
                                                    <div class="image-preview" style="display: none;">
                                                        <img id="${type}_card_back_preview" src="" alt="Back Name Card Preview">
                                                        <div class="image-actions">
                                                            
                                                            <a  onclick="removeImage('${type}_card_back_preview', '${type}_card_back');" class="remove-btn">Remove</a>
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
                                                    <input type="email" name="${type}_email" id="${type}_email" class="form-control" style="padding: 2rem 1rem;">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_mobile" class="control-label"><i class="fas fa-phone-alt" style="color: #2c85d2ff; margin-right: 5px;"></i> เบอร์มือถือ </label>
                                                    <input type="text" name="${type}_mobile" id="${type}_mobile" class="form-control" style="padding: 2rem 1rem;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_facebook" class="control-label"><i class="fab fa-facebook" style="color: #4267B2; margin-right: 5px;"></i> Facebook </label>
                                                    <input type="text" name="${type}_facebook" id="${type}_facebook" class="form-control" style="padding: 2rem 1rem;">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_line" class="control-label"><i class="fab fa-line" style="color: #00B900; margin-right: 5px;"></i> Line ID </label>
                                                    <input type="text" name="${type}_line" id="${type}_line" class="form-control" style="padding: 2rem 1rem;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_ig" class="control-label"><i class="fab fa-instagram" style="color: #E1306C; margin-right: 5px;"></i> Instagram </label>
                                                    <input type="text" name="${type}_ig" id="${type}_ig" class="form-control" style="padding: 2rem 1rem;">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="${type}_address" class="control-label"><i class="fas fa-map-marker-alt" style="color: #ff9801; margin-right: 5px;"></i> ที่อยู่</label>
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
                                            <label for="${type}_bio" class="control-label"><i class="fas fa-book-open" style="color: #0fa22ea6; margin-right: 5px;"></i> Biography </label>
                                            <textarea name="${type}_bio" id="${type}_bio" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_education" class="control-label"><i class="fas fa-graduation-cap" style="color: #0080ef; margin-right: 5px;"></i> Education </label>
                                            <textarea name="${type}_education" id="${type}_education" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 3.5em;">
                                            <label for="${type}_experience" class="control-label"><i class="fas fa-briefcase" style="color: #ea6523; margin-right: 5px;"></i> Experience </label>
                                            <textarea name="${type}_experience" id="${type}_experience" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                <label for="${type}_company" class="control-label"><i class="fas fa-building" style="color: #6c757d; margin-right: 5px;"></i> Workplace/School </label>
                                                <input type="text" name="${type}_company" id="${type}_company" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_position" class="control-label"><i class="fas fa-user-tag" style="color: #6c757d; margin-right: 5px;"></i> Position </label>
                                                <input type="text" name="${type}_position" id="${type}_position" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_company_detail" class="control-label"><i class="fas fa-info-circle" style="color: #6c757d; margin-right: 5px;"></i> Company Detail </label>
                                            <textarea name="${type}_company_detail" id="${type}_company_detail" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_company_url" class="control-label"><i class="fas fa-link" style="color: #6c757d; margin-right: 5px;"></i> Company URL </label>
                                            <input type="url" name="${type}_company_url" id="${type}_company_url" class="form-control" style="padding: 2rem 1rem;">
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6" style="width:100%;">
                                                <label for="${type}_company_logo" class="control-label"><i class="fas fa-image" style="color: #6c757d; margin-right: 5px;"></i> Company Logo</label>
                                                <div class="preview-uploads">
                                                    <input type="file" class="file-input" name="${type}_company_logo" id="${type}_company_logo" accept="image/*" onchange="readURL(this, '${type}_company_logo_preview')">
                                                    <div class="image-placeholder">
                                                        <i class="fas fa-image"></i>
                                                        <p>Click to add logo</p>
                                                    </div>
                                                    <div class="image-preview" style="display:none;">
                                                        <img id="${type}_company_logo_preview" src="#" alt="Company Logo Preview" />
                                                    </div>
                                                    <input type="hidden" id="${type}_company_logo_current" name="${type}_company_logo_current">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6" style="width:100%;">
                                                <label for="${type}_company_photos" class="control-label"><i class="fas fa-images" style="color: #6c757d; margin-right: 5px;"></i> Company Photos</label>
                                                <div class="file-gallery-wrapper">
                                                    <div id="company-photos-fields" class="row"></div>
                                                </div>
                                                <button type="button" class="btn btn-default mt-2" onclick="addCompanyPhotoField('', '${type}', true)"><i class="fas fa-plus"></i> Add Photo</button>
                                            </div>
                                        </div>
                                        <div class="form-group g-4 row" style="margin-bottom:4.5em;">
                                            <div class="col-sm-6">
                                                <label for="${type}_religion" class="control-label"><i class="fas fa-hand-holding-heart" style="color: #ecc379; margin-right: 5px;"></i> Religion </label>
                                                <input type="text" name="${type}_religion" id="${type}_religion" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_bloodgroup" class="control-label"><i class="" style="color: #6c757d; margin-right: 5px;"></i> Blood Group </label>
                                                <input type="text" name="${type}_bloodgroup" id="${type}_bloodgroup" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_attach_document" class="control-label"><i class="fas fa-paperclip" style="color: #ff9900; margin-right: 5px;"></i> Other Attachments</label>
                                            
                                            <div class="file-gallery-wrapper">
                                                <div id="attach-document-fields" class="row"></div>
                                            </div>
                                            
                                            <button type="button" class="btn btn-default mt-2" onclick="addDocumentField('', '${type}', true)"><i class="fas fa-plus"></i> Add Document</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="${type}_favorite_tab">
                                <form id="frm_${type}_favorite" class="mt-3">
                                    <div class="form-box">
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                <label for="${type}_hobby" class="control-label"><i class="fas fa-palette" style="color: #19ff00; margin-right: 5px;"></i> Hobby </label>
                                                <input type="text" name="${type}_hobby" id="${type}_hobby" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_music" class="control-label"><i class="fas fa-music" style="color: #0080ef; margin-right: 5px;"></i> Favorite Music </label>
                                                <input type="text" name="${type}_music" id="${type}_music" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                        </div>
                                        <div class="form-group g-4 row">
                                            <div class="col-sm-6">
                                                    <label for="${type}_drink" class="control-label"><i class="fas fa-glass-cheers" style="color: #a60efd; margin-right: 5px;"></i> Favorite Drink </label>
                                                    <input type="text" name="${type}_drink" id="${type}_drink" class="form-control" style="padding: 2rem 1rem;">
                                                </div>
                                            <div class="col-sm-6">
                                                <label for="${type}_movie" class="control-label"><i class="fas fa-film" style="color: #a23131; margin-right: 5px;"></i> Favorite Movies </label>
                                                <input type="text" name="${type}_movie" id="${type}_movie" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                           
                                        </div>
                                        <div class="form-group g-4 row">
                                             <div class="col-sm-6">
                                                <label for="${type}_goal" class="control-label"><i class="fas fa-bullseye" style="color: #ff9900; margin-right: 5px;"></i> Life Goal </label>
                                                <input type="text" name="${type}_goal" id="${type}_goal" class="form-control" style="padding: 2rem 1rem;">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="${type}_setup_tab">
                                <form id="frm_${type}_setup" class="mt-3">
                                    <div class="form-box">
                                        <div class="form-group">
                                            <label for="${type}_username" class="control-label"><i class="fas fa-user" style="color: #6c757d; margin-right: 5px;"></i> Username *</label>
                                            <input type="text" name="${type}_username" id="${type}_username" class="form-control required-field-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_password" class="control-label"><i class="fas fa-lock" style="color: #6c757d; margin-right: 5px;"></i> Password </label>
                                            <input type="password" name="${type}_password" id="${type}_password" class="form-control">
                                            <small class="text-muted">Fill in only if you want to change the password</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="${type}_confirm_password" class="control-label"><i class="fas fa-lock" style="color: #6c757d; margin-right: 5px;"></i> Confirm Password </label>
                                            <input type="password" name="${type}_confirm_password" id="${type}_confirm_password" class="form-control">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right" style="padding: 1em;">
                        <button type="button" class="btn btn-white" onclick="window.location.href = '/classroom/management/'">Close</button>
                        <button type="button" class="btn btn-primary" id="saveBtn" style="    background-color: #ff9900;">Save</button>
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

// Helper Functions
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
// แก้ไขฟังก์ชัน removeDocumentField
function removeDocumentField(docId, fileId, type) {
    if (fileId) {
        // If fileId exists, it means the file is already saved in the database
        // Send an AJAX request to delete the file from the server
        $.ajax({
            url: 'actions/fetch.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'deleteFile',
                type: type, // Pass the type (e.g., 'teacher')
                file_id: fileId
            },
            success: function(response) {
                if (response.status === 'success') {
                    $(`#${docId}`).remove(); // Remove the field from the UI
                    swal("Success!", "File removed successfully.", "success");
                } else {
                    swal("Error!", response.message, "error");
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                swal("Error!", "Failed to remove file from server.", "error");
            }
        });
    } else {
        // If there's no fileId, it's a new file not yet saved to the database.
        // Just remove the field from the UI.
        $(`#${docId}`).remove();
        console.log(`Removed new document field with ID: ${docId}`);
    }
}


// แก้ไข HTML ในฟังก์ชัน addDocumentField เพื่อรองรับการแสดงผล Preview และโครงสร้างใหม่
function addDocumentField(docUrl = '', type, fileId = null) {
    const documentContainer = $(`#attach-document-fields`);
    const docId = `doc-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const fileName = docUrl ? docUrl.substring(docUrl.lastIndexOf('/') + 1) : '';
    const fileExtension = fileName.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension);
    const isPdf = fileExtension === 'pdf';

    let previewContent = '';
    if (docUrl) {
        if (isImage) {
            previewContent = `<img src="${docUrl}" class="img-fluid document-preview" alt="Attachment Preview">`;
        } else if (isPdf) {
            // Using iframe to display PDF preview
            previewContent = `<iframe src="${docUrl}" class="document-preview-iframe" style="width:100%; height:200px; border:none;"></iframe>`;
        } else {
            previewContent = `<a href="${docUrl}" target="_blank" class="document-file-link"><i class="fas fa-file document-file-icon"></i> <span class="document-file-name">${fileName}</span></a>`;
        }
    } else {
        previewContent = `<div class="image-placeholder"><i class="fas fa-file-upload"></i> <p>Click to add file</p></div><input type="file" class="file-input" name="${type}_attach_document[]" accept="image/*,.pdf" multiple>`;
    }

    const newFieldHtml = `
        <div class="col-6 col-md-2 mb-3">
            <div class="file-item-box" id="${docId}" data-file-id="${fileId}">
                <div class="file-preview-container">
                    ${previewContent}
                    ${docUrl ? `<input type="hidden" name="${type}_attach_document_current[]" value="${docUrl}">` : ''}
                </div>
                <div class="file-actions">
                    <button class="btn btn-danger btn-sm" type="button" onclick="removeDocumentField('${docId}', ${fileId}, '${type}')">
                        <i class="fas fa-trash"></i>
                    </button>
                    ${docUrl ? `<a href="${docUrl}" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>` : ''}
                </div>
            </div>
        </div>
    `;

    documentContainer.append(newFieldHtml);
}

// เพิ่มฟังก์ชันสำหรับจัดการการแสดงตัวอย่างไฟล์ที่เลือก
$(document).on('change', 'input[name$="_attach_document[]"]', function(event) {
    const file = event.target.files[0];
    if (file) {
        const docId = $(this).closest('.file-item-box').attr('id');
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension);
        const isPdf = fileExtension === 'pdf';
        
        const previewUrl = URL.createObjectURL(file);
        
        let previewHtml = '';
        if (isImage) {
            previewHtml = `<img src="${previewUrl}" class="img-fluid document-preview" alt="Attachment Preview">`;
        } else if (isPdf) {
            previewHtml = `<iframe src="${previewUrl}" class="document-preview-iframe" style="width:100%; height:200px; border:none;"></iframe>`;
        } else {
            previewHtml = `<a href="#" class="document-file-link"><i class="fas fa-file document-file-icon"></i> <span class="document-file-name">${file.name}</span></a>`;
        }
        
        $(`#${docId} .file-preview-container`).html(previewHtml);

        const newFileInput = document.createElement('input');
        newFileInput.type = 'file';
        newFileInput.name = event.target.name;
        newFileInput.style.display = 'none';
        newFileInput.files = event.target.files;
        $(`#${docId} .file-preview-container`).append(newFileInput);

        $(`#${docId} .file-actions a.btn`).remove();
        $(`#${docId} .file-actions`).append(`<a href="${previewUrl}" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>`);
        
    }
});

// ฟังก์ชันสำหรับเพิ่มช่องอัปโหลดรูปภาพบริษัท
function addCompanyPhotoField(photoUrl = '', type, fileId = null) {
    const photoContainer = $(`#company-photos-fields`);
    const photoId = `photo-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

    let previewContent = '';
    let fileInputHtml = '';
    
    // หากมี URL รูปภาพอยู่แล้ว
    if (photoUrl) {
        previewContent = `<img src="${photoUrl}" class="img-fluid document-preview" alt="Company Photo Preview">`;
        fileInputHtml = `<input type="hidden" name="${type}_company_photos_current[]" value="${photoUrl}">`;
    } else {
        // หากเป็นการเพิ่มรูปภาพใหม่
        previewContent = `<div class="image-placeholder"><i class="fas fa-file-image"></i> <p>Click to add photo</p></div>`;
        fileInputHtml = `<input type="file" class="file-input" name="${type}_company_photos[]" accept="image/*">`;
    }

    const newFieldHtml = `
        <div class="col-6 col-md-2 mb-3">
            <div class="file-item-box" id="${photoId}" data-file-id="${fileId}">
                <div class="file-preview-container">
                    ${previewContent}
                </div>
                ${fileInputHtml}
                <div class="file-actions">
                    <button class="btn btn-danger btn-sm" type="button" onclick="removeCompanyPhotoField('${photoId}', ${fileId}, '${type}')">
                        <i class="fas fa-trash"></i>
                    </button>
                    ${photoUrl ? `<a href="${photoUrl}" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>` : ''}
                </div>
            </div>
        </div>
    `;

    const newField = $(newFieldHtml);
    photoContainer.append(newField);

    // ✨ เพิ่มโค้ดส่วนนี้เพื่อจัดการพรีวิวรูปภาพ
    const fileInput = newField.find('.file-input');
    const previewContainer = newField.find('.file-preview-container');

    previewContainer.on('click', function() {
        if (!photoUrl) {
            fileInput.trigger('click');
        }
    });

    fileInput.on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.html(`<img src="${e.target.result}" class="img-fluid document-preview" alt="Company Photo Preview">`);
            };
            reader.readAsDataURL(file);
        }
    });
}

// ฟังก์ชันสำหรับลบรูปภาพบริษัท
function removeCompanyPhotoField(photoId, fileId, type) {
    if (fileId) {
        $.ajax({
            url: 'actions/fetch.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'deleteFile',
                type: type,
                file_id: fileId,
                file_type: 'company_photos' // ระบุ file_type ใหม่
            },
            success: function(response) {
                if (response.status === 'success') {
                    $(`#${photoId}`).remove();
                    swal("Success!", "Company photo removed successfully.", "success");
                } else {
                    swal("Error!", response.message, "error");
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                swal("Error!", "Failed to remove company photo from server.", "error");
            }
        });
    } else {
        $(`#${photoId}`).remove();
        console.log(`Removed new company photo field with ID: ${photoId}`);
    }
}

// ✨ แก้ไข: ฟังก์ชัน loadData เพื่อรองรับการดึงรูปโปรไฟล์และไฟล์แนบจากตารางอื่น
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

                    const genderMap = getGenderMap();
                    const genderValue = data[`${type}_gender`];
                    const genderText = genderMap[genderValue] || '';
                    $(`#${type}_gender`).val(genderText);

                    // Personal Information Tab
                    $(`#${type}_id`).val(data[`${type}_id`]);
                    $(`#${type}_firstname_th`).val(data[`${type}_firstname_th`]);
                    $(`#${type}_lastname_th`).val(data[`${type}_lastname_th`]);
                    $(`#${type}_firstname_en`).val(data[`${type}_firstname_en`]);
                    $(`#${type}_lastname_en`).val(data[`${type}_lastname_en`]);
                    $(`#${type}_nickname_th`).val(data[`${type}_nickname_th`]);
                    $(`#${type}_nickname_en`).val(data[`${type}_nickname_en`]);
                    $(`#${type}_idcard`).val(data[`${type}_idcard`]);
                    $(`#${type}_passport`).val(data[`${type}_passport`]);

                    // ✨ แก้ไข: จัดการการแสดงผลวันเกิด
                    if (data[`${type}_birth_date`] && data[`${type}_birth_date`] !== '0000-00-00') {
                        const dateParts = data[`${type}_birth_date`].split('-');
                        const formattedDate = `${dateParts[0]}-${dateParts[1]}-${dateParts[2]}`;
                        $(`#${type}_birth_date`).val(formattedDate);
                    } else {
                        $(`#${type}_birth_date`).val('');
                    }

                    // ✨ แก้ไข: ดึงรูปโปรไฟล์จากตารางใหม่
                    if (data.all_profile_images && data.all_profile_images.length > 0) {
                        // ดึงเฉพาะรูปหลัก (index 0) ที่มี file_status = 1
                        const mainProfile = data.all_profile_images.find(img => img.file_status === '1');
                        if (mainProfile) {
                            showFilePreview(`${type}_image_profile`, mainProfile.file_path);
                        } else {
                            // ถ้าไม่มีรูปหลัก ให้ดึงรูปแรก
                            showFilePreview(`${type}_image_profile`, data.all_profile_images[0].file_path);
                        }
                    } else {
                        removeImage(`${type}_image_profile_preview`, `${type}_image_profile`);
                    }

                    // ดึงรูปบัตรประชาชนตามเดิม
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
                    // ✨ เพิ่มโค้ดใหม่ตรงนี้
// Company fields
$(`#${type}_company_detail`).val(data[`${type}_company_detail`]);
$(`#${type}_company_url`).val(data[`${type}_company_url`]);

// ดึงและแสดงผลโลโก้บริษัท
showFilePreview(`${type}_company_logo`, data[`${type}_company_logo`]);

// ดึงและแสดงผลรูปภาพบริษัทหลายรูป
const companyPhotosContainer = $(`#company-photos-fields`);
companyPhotosContainer.empty();
if (data.company_photos && data.company_photos.length > 0) {
    data.company_photos.forEach(photo => {
        addCompanyPhotoField(photo.file_path, type, photo.file_id);
    });
}

                    // Favorite Tab
                    $(`#${type}_hobby`).val(data[`${type}_hobby`]);
                    $(`#${type}_music`).val(data[`${type}_music`]);
                    $(`#${type}_drink`).val(data[`${type}_drink`]);
                    $(`#${type}_movie`).val(data[`${type}_movie`]);
                    $(`#${type}_goal`).val(data[`${type}_goal`]);

                    // ✨ แก้ไข: ดึงไฟล์แนบจากตารางใหม่ (แก้ชื่อ key)
                    const documentContainer = $(`#attach-document-fields`);
                    documentContainer.empty();
                    if (data.attached_documents && data.attached_documents.length > 0) {
                        data.attached_documents.forEach(doc => {
                            addDocumentField(doc.file_path, type, doc.file_id, false); // ส่ง file_id ไปด้วย
                        });
                    }

                    // Setup Tab
                    $(`#${type}_username`).val(data[`${type}_username`]);

                    resolve(data);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
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
        { id: `${type}_passport`, tab: `${type}_personal_contact_tab`, message: 'Please enter a passport number.', optional: true },
        { id: `${type}_gender`, tab: `${type}_personal_contact_tab`, message: 'Please select a gender.' },
        { id: `${type}_birth_date`, tab: `${type}_personal_contact_tab`, message: 'Please select a date of birth.' },
        { id: `${type}_email`, tab: `${type}_personal_contact_tab`, message: 'Please enter a valid email address.', pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ },
        { id: `${type}_mobile`, tab: `${type}_personal_contact_tab`, message: 'Please enter a mobile number.', optional: true },
        { id: `${type}_address`, tab: `${type}_personal_contact_tab`, message: 'Please enter an address.' },
        
        // Biography Tab
        { id: `${type}_bio`, tab: `${type}_bio_tab`, message: 'Please enter a biography.' },
        { id: `${type}_education`, tab: `${type}_bio_tab`, message: 'Please enter your education history.' },
        { id: `${type}_experience`, tab: `${type}_bio_tab`, message: 'Please enter your experience.' },
        { id: `${type}_company`, tab: `${type}_bio_tab`, message: 'Please enter your workplace or school.' },
        { id: `${type}_position`, tab: `${type}_bio_tab`, message: 'Please enter your position.' },

        // Login Setup Tab
        { id: `${type}_username`, tab: `${type}_setup_tab`, message: 'Username must be at least 6 characters.', pattern: /^.{6,}$/ },
        { id: `${type}_password`, tab: `${type}_setup_tab`, message: 'Password must be at least 6 characters.', pattern: /^.{6,}$/, conditional: true },
        { id: `${type}_confirm_password`, tab: `${type}_setup_tab`, message: 'Passwords do not match.', conditional: true }
    ];

    $(`.form-control`).removeClass('required-field-input-invalid');
    $('.alert-danger').remove();

    for (const field of fieldsToValidate) {
        const $input = $(`#${field.id}`);
        const value = $input.val().trim();
        let currentFieldIsValid = true;
        const currentId = $(`#${type}_id`).val();
        
        // Check for required fields (optional fields are skipped)
        if (!field.optional && value === '') {
            currentFieldIsValid = false;
        }

        // Check against patterns
        if (value !== '' && field.pattern && !field.pattern.test(value)) {
            currentFieldIsValid = false;
        }

        // Password validation logic
        if (field.id === `${type}_password`) {
            // If it's an update and no password is entered, it's valid
            if (currentId && value === '') {
                currentFieldIsValid = true;
            } else if (value === '') {
                // If it's a new entry and no password is provided, it's invalid
                currentFieldIsValid = false;
            }
        }

        // Confirm Password validation
        if (field.id === `${type}_confirm_password`) {
            const passwordValue = $(`#${type}_password`).val().trim();
            if (passwordValue !== '' && value !== passwordValue) {
                currentFieldIsValid = false;
            } else if (passwordValue !== '' && value === '') {
                currentFieldIsValid = false;
            }
        }

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
        $('#form-container .card-header').after(`
            <div class="alert alert-danger mx-3" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <strong>Validation Error!</strong> Please fill in all required fields and correct any formatting errors.
            </div>
        `);
        
        $(`a[href="#${firstInvalidField.tab}"]`).tab('show');
        firstInvalidField.input.focus();
        
        if (firstInvalidField.message) {
             console.log(`Validation Error: ${firstInvalidField.message}`);
        }
    }

    return isValid;
}

// ✨ แก้ไข: ฟังก์ชัน handleFormSubmission เพื่อรองรับการส่งรูปโปรไฟล์และไฟล์แนบ
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

    // Convert birth date format from YYYY/MM/DD back to YYYY-MM-DD
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
        formData.set(`${type}_birth_date`, '');
    }

    // Append image files (except attached documents and company photos)
$(`input[type="file"]:not([name*="_attach_document"]):not([name*="_company_photos"])`).each(function() {
    if (this.files.length > 0) {
        formData.append(this.name, this.files[0]);
    }
});

// ✨ เพิ่มโค้ดใหม่ตรงนี้
// Append multiple company photos
$(`input[name="${type}_company_photos[]"]`).each(function() {
    if (this.files.length > 0) {
        for (let i = 0; i < this.files.length; i++) {
            formData.append(this.name, this.files[i]);
        }
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

    // ✨ เพิ่มโค้ดใหม่ตรงนี้
// Append company photos that are already saved and not removed
$(`input[name="${type}_company_photos_current[]"]`).each(function() {
    formData.append(this.name, this.value);
});

    // Convert prefix text to numeric value
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

    // Convert gender text to abbreviation
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
                    window.location.href='/classroom/management/';
                    // if (newId) {
                    //     window.location.href = `?type=${type}&id=${newId}`;
                    // } else {
                    //     window.location.reload();
                    // }
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