// --- JAVASCRIPT CODE ---

async function initForm(type, id) {
    const isTeacher = (type === 'teacher');
    const formTitle = (id ? 'แก้ไข' : 'เพิ่ม') + ' ' + (isTeacher ? 'ข้อมูลครู' : 'ข้อมูลนักเรียน');

    const formTemplate = `
        <div class="card">
            <div class="card-header">
                <h5 class="modal-title">${formTitle}</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-origami nav-contact">
                    <li class="nav active"><a href="#${type}_personal_tab" data-toggle="tab">ข้อมูลส่วนตัว</a></li>
                    <li class="nav"><a href="#${type}_contact_tab" data-toggle="tab">ข้อมูลติดต่อ</a></li>
                    <li class="nav"><a href="#${type}_bio_tab" data-toggle="tab">ประวัติส่วนตัว</a></li>
                    <li class="nav"><a href="#${type}_setup_tab" data-toggle="tab">ข้อมูลเข้าสู่ระบบ</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade in active" id="${type}_personal_tab">
                        <form id="frm_${type}" action="save_${type}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="${type}_id" value="${id || ''}">
                            <input type="hidden" name="type" value="${type}">

                            <div class="form-group row mt-3">
                                <div class="col-sm-12 text-center">
                                    <label for="${type}_image_profile" class="control-label">รูปโปรไฟล์</label>
                                    <div class="preview-uploads preview-uploads-logo">
                                        <div class="image-placeholder">
                                            <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                            <h5 class="text-muted mt-2">ลากและวางรูปภาพที่นี่<br>หรือคลิกเพื่ออัปโหลด</h5>
                                            <input name="${type}_image_profile" id="${type}_image_profile" type="file" onchange="readURL(this, '${type}_image_profile_preview');" class="d-none" accept="image/*">
                                        </div>
                                        <div class="image-preview" style="display: none;">
                                            <img id="${type}_image_profile_preview" src="" alt="Image Preview">
                                            <div class="image-actions">
                                                <a href="#" onclick="previewImage('${type}_image_profile_preview');" class="preview-btn">ดูรูป</a>
                                                <a href="#" onclick="removeImage('${type}_image_profile_preview', '${type}_image_profile');" class="remove-btn">ลบรูป</a>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="${type}_image_profile_current" id="${type}_image_profile_current" value="">
                                </div>
                            </div>

                            <div class="form-group row mt-3">
                                <div class="col-sm-3">
                                    <label for="${type}_perfix" class="control-label">คำนำหน้า *</label>
                                    <select name="${type}_perfix" id="${type}_perfix" class="form-control" required>
                                        <option value="">- เลือก -</option>
                                        <option value="นาย">นาย</option>
                                        <option value="นาง">นาง</option>
                                        <option value="นางสาว">นางสาว</option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label for="${type}_firstname_th" class="control-label required-field">ชื่อ (TH) *</label>
                                    <input type="text" name="${type}_firstname_th" id="${type}_firstname_th" class="form-control" required>
                                </div>
                                <div class="col-sm-5">
                                    <label for="${type}_lastname_th" class="control-label required-field">นามสกุล (TH) *</label>
                                    <input type="text" name="${type}_lastname_th" id="${type}_lastname_th" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_firstname_en" class="control-label">Firstname (EN)</label>
                                    <input type="text" name="${type}_firstname_en" id="${type}_firstname_en" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_lastname_en" class="control-label">Lastname (EN)</label>
                                    <input type="text" name="${type}_lastname_en" id="${type}_lastname_en" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_nickname_th" class="control-label">ชื่อเล่น (TH)</label>
                                    <input type="text" name="${type}_nickname_th" id="${type}_nickname_th" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_nickname_en" class="control-label">Nickname (EN)</label>
                                    <input type="text" name="${type}_nickname_en" id="${type}_nickname_en" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_idcard" class="control-label">เลขบัตรประชาชน</label>
                                    <input type="text" name="${type}_idcard" id="${type}_idcard" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_passport" class="control-label">เลขหนังสือเดินทาง</label>
                                    <input type="text" name="${type}_passport" id="${type}_passport" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_gender" class="control-label">เพศ</label>
                                    <select name="${type}_gender" id="${type}_gender" class="form-control">
                                        <option value="">- เลือก -</option>
                                        <option value="ชาย">ชาย</option>
                                        <option value="หญิง">หญิง</option>
                                        <option value="อื่นๆ">อื่นๆ</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_birth_date" class="control-label">วันเกิด</label>
                                    <div class="input-group">
                                        <input type="text" name="${type}_birth_date" id="${type}_birth_date" class="form-control datepicker">
                                        <span class="input-group-addon"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mt-4">
                                <div class="col-sm-6 text-center">
                                    <label for="${type}_card_front" class="control-label">บัตร ปชช. (ด้านหน้า)</label>
                                    <div class="preview-uploads">
                                        <div class="image-placeholder">
                                            <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                            <h5 class="text-muted mt-2">ลากและวางรูปภาพ<br>หรือคลิกเพื่ออัปโหลด</h5>
                                            <input name="${type}_card_front" id="${type}_card_front" type="file" onchange="readURL(this, '${type}_card_front_preview');" class="d-none" accept="image/*">
                                        </div>
                                        <div class="image-preview" style="display: none;">
                                            <img id="${type}_card_front_preview" src="" alt="Front Name Card Preview">
                                            <div class="image-actions">
                                                <a href="#" onclick="previewImage('${type}_card_front_preview');" class="preview-btn">ดูรูป</a>
                                                <a href="#" onclick="removeImage('${type}_card_front_preview', '${type}_card_front');" class="remove-btn">ลบรูป</a>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="${type}_card_front_current" id="${type}_card_front_current" value="">
                                </div>
                                <div class="col-sm-6 text-center">
                                    <label for="${type}_card_back" class="control-label">บัตร ปชช. (ด้านหลัง)</label>
                                    <div class="preview-uploads">
                                        <div class="image-placeholder">
                                            <span class="fa fa-cloud-upload-alt fa-3x text-muted"></span>
                                            <h5 class="text-muted mt-2">ลากและวางรูปภาพ<br>หรือคลิกเพื่ออัปโหลด</h5>
                                            <input name="${type}_card_back" id="${type}_card_back" type="file" onchange="readURL(this, '${type}_card_back_preview');" class="d-none" accept="image/*">
                                        </div>
                                        <div class="image-preview" style="display: none;">
                                            <img id="${type}_card_back_preview" src="" alt="Back Name Card Preview">
                                            <div class="image-actions">
                                                <a href="#" onclick="previewImage('${type}_card_back_preview');" class="preview-btn">ดูรูป</a>
                                                <a href="#" onclick="removeImage('${type}_card_back_preview', '${type}_card_back');" class="remove-btn">ลบรูป</a>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="${type}_card_back_current" id="${type}_card_back_current" value="">
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="${type}_contact_tab">
                        <form id="frm_${type}_contact" class="mt-3">
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_email" class="control-label">อีเมล</label>
                                    <input type="email" name="${type}_email" id="${type}_email" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_mobile" class="control-label">เบอร์โทรศัพท์</label>
                                    <input type="text" name="${type}_mobile" id="${type}_mobile" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_facebook" class="control-label">Facebook</label>
                                    <input type="text" name="${type}_facebook" id="${type}_facebook" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_line" class="control-label">Line ID</label>
                                    <input type="text" name="${type}_line" id="${type}_line" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="${type}_ig" class="control-label">Instagram</label>
                                    <input type="text" name="${type}_ig" id="${type}_ig" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="${type}_address" class="control-label">ที่อยู่</label>
                                    <textarea name="${type}_address" id="${type}_address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="${type}_bio_tab">
                        <form id="frm_${type}_bio" class="mt-3">
                            <div class="form-group">
                                <label for="${type}_bio" class="control-label">ประวัติส่วนตัว</label>
                                <textarea name="${type}_bio" id="${type}_bio" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="${type}_education" class="control-label">การศึกษา</label>
                                <textarea name="${type}_education" id="${type}_education" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="${type}_experience" class="control-label">ประสบการณ์</label>
                                <textarea name="${type}_experience" id="${type}_experience" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="${type}_attach_document" class="control-label">เอกสารแนบอื่นๆ</label>
                                <div id="attach-document-fields">
                                    </div>
                                <button type="button" class="btn btn-default mt-2" onclick="addDocumentField('', '${type}', true)"><i class="fas fa-plus"></i> เพิ่มเอกสาร</button>
                            </div>
                            <input type="hidden" name="${type}_attach_document_current" id="${type}_attach_document_current" value="">
                        </form>
                    </div>

                    <div class="tab-pane fade" id="${type}_setup_tab">
                        <form id="frm_${type}_setup" class="mt-3">
                            <div class="form-group">
                                <label for="${type}_username" class="control-label required-field">Username *</label>
                                <input type="text" name="${type}_username" id="${type}_username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="${type}_password" class="control-label">Password</label>
                                <input type="password" name="${type}_password" id="${type}_password" class="form-control">
                                <small class="text-muted">กรอกเมื่อต้องการเปลี่ยนรหัสผ่านเท่านั้น</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="button" class="btn btn-white" onclick="window.history.back()">ปิด</button>
                <button type="button" class="btn btn-primary" id="saveBtn">บันทึก</button>
            </div>
        </div>
    `;

    $("#form-container").html(formTemplate);

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
                    
                    // Personal Information Tab
                    $(`#${type}_id`).val(data[`${type}_id`]);
                    $(`#${type}_perfix`).val(data[`${type}_perfix`]);
                    $(`#${type}_firstname_th`).val(data[`${type}_firstname_th`]);
                    $(`#${type}_lastname_th`).val(data[`${type}_lastname_th`]);
                    $(`#${type}_firstname_en`).val(data[`${type}_firstname_en`]);
                    $(`#${type}_lastname_en`).val(data[`${type}_lastname_en`]);
                    $(`#${type}_nickname_th`).val(data[`${type}_nickname_th`]);
                    $(`#${type}_nickname_en`).val(data[`${type}_nickname_en`]);
                    $(`#${type}_idcard`).val(data[`${type}_idcard`]);
                    $(`#${type}_passport`).val(data[`${type}_passport`]);
                    $(`#${type}_gender`).val(data[`${type}_gender`]);
                    $(`#${type}_birth_date`).val(data[`${type}_birth_date`]);

                    showFilePreview(`${type}_image_profile`, data[`${type}_image_profile`]);
                    showFilePreview(`${type}_card_front`, data[`${type}_card_front`]);
                    showFilePreview(`${type}_card_back`, data[`${type}_card_back`]);

                    // Contact Tab
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

                    // Attach Documents
                    if (data[`${type}_attach_document`]) {
                        try {
                            const documents = JSON.parse(data[`${type}_attach_document`]);
                            const documentContainer = $(`#attach-document-fields`);
                            documentContainer.empty();
                            documents.forEach(docUrl => {
                                addDocumentField(docUrl, type, false);
                            });
                        } catch (e) {
                            console.error("Error parsing attach documents:", e);
                        }
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

function handleFormSubmission(type, id) {
    const $form = $(`#frm_${type}`);
    const formData = new FormData();
    let isValid = true;
    let errorMessage = "";

    // Append data from each form tab
    $(`#frm_${type}, #frm_${type}_contact, #frm_${type}_bio, #frm_${type}_setup`).each(function() {
        const formFields = $(this).serializeArray();
        formFields.forEach(field => {
            formData.append(field.name, field.value);
        });
    });

    // **แก้ไข: เพิ่มการดึง classroom_id และส่งไปใน formData**
    const classroom_id = $('#classroom_id').val();
    formData.append('classroom_id', classroom_id);

    // Append file data
    $(`input[type="file"]`).each(function() {
        if (this.files.length > 0) {
            if (this.id.includes('_attach_document')) {
                for (let i = 0; i < this.files.length; i++) {
                    formData.append(this.name + '[]', this.files[i]);
                }
            } else {
                 formData.append(this.name, this.files[0]);
            }
        }
    });

    // Append hidden current file paths
    $(`input[type="hidden"][name$="_current"]`).each(function() {
        if (this.id.includes('_attach_document')) {
            // Append each document URL individually
            if (this.value) {
                // **แก้ไข: แยก URL ที่เป็น array ออกจากกันก่อนส่ง**
                const docs = this.value.split(',');
                docs.forEach(doc => {
                    formData.append(this.name + '[]', doc.trim());
                });
            }
        } else {
            formData.append(this.name, this.value);
        }
    });

    // Append static data
    formData.append('action', 'saveData');
    formData.append('type', type);
    formData.append('id', id);

    // Validate form fields
    $form.find("[required]").each(function () {
        if (!$(this).val() || $(this).val().trim() === "") {
            isValid = false;
            errorMessage = "กรุณากรอกข้อมูลในช่องที่จำเป็นให้ครบถ้วน";
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });

    if (!isValid) {
        swal("แจ้งเตือน", errorMessage, "warning");
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
                swal("สำเร็จ!", response.message, "success");
                setTimeout(() => {
                    const newId = response.id || id;
                    if (newId) {
                         // Redirect to the same form with the updated ID
                         window.location.href = `?type=${type}&id=${newId}`;
                    } else {
                         window.location.reload();
                    }
                }, 1500);
            } else {
                swal("ผิดพลาด!", response.message, "error");
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
            swal("ผิดพลาด!", "ไม่สามารถบันทึกข้อมูลได้", "error");
        }
    });
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

// Initial call
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('type');
    const formId = urlParams.get('id');

    if (formType) {
        initForm(formType, formId);
    } else {
        // Handle case where no type is provided
        $("#form-container").html("<h3>ไม่พบประเภทข้อมูลที่ต้องการแสดง</h3>");
    }
});