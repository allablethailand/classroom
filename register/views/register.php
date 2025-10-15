<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register • ORIGAMI PLATFORM</title>
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/fancybox/source/jquery.fancybox.css">
<link rel="stylesheet" href="/dist/dropify/dist/css/dropify.min.css">
<link rel="stylesheet" href="/classroom/register/css/register.css?v=<?php echo time(); ?>">
<script src="/dist/fontawesome-5.11.2/js/all.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>"></script>
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/jquery-ui.min.js"></script>
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/dist/dropify/dist/js/dropify.min.js"></script>
<script src="/dist/fancybox/source/jquery.fancybox.js"></script>
<script src="/classroom/register/js/register.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
</head>
<body>
<input type="hidden" id="classroomCode" value="<?php echo $classroomCode; ?>">
<input type="hidden" id="channel" value="<?php echo $channel; ?>">
<div class="container-fluid registration-container">
    <div class="row" style="height: 100vh;">
        <div class="col-sm-9 left-section">
            <div class="event-details">
                <div class="detail-item">
                    <div class="poster-content">
                        <div class="poster-container">
                            <div class="poster-bg"></div>
                            <div class="poster-img">
                                <img onerror="this.src='/images/training.jpg'">
                            </div>
                        </div>
                        <div class="poster-text">
                            <h3 style="margin-top: 15px;" class="classroom-name"></h3>
                        </div>
                        <h5 class="classroom-date"></h5>
                        <h5 class="classroom-location"></h5>
                    </div>
                    <div class="detail-value classroom-information"></div>
                    <div class="detail-value contact-us"></div>
                    <div class="text-center" style="margin-bottom: 35px;">
                        <img src="/images/ogm_logo.png" style="height: 100px;">
                        <h5 style="margin-bottom: 25px;">Powered by Origami</h5>
                        <div>Copyright © 2020, Allable Co.,Ltd. All Rights Reserved.</div>
                    </div>
                    <div class="origami-events-notice"><label data-lang="consent_notice"></label>
                        <p data-lang="consent_paragraph"></p>
                        <label>Origami Form</label>
                        <p data-lang="consent_footer"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3 right-section" id="registration-form">
            <div class="container-header">
                <div class="container-header-bg"></div>
                <div class="container-header-logo">
                    <img onerror="this.src='/images/ogm_logo.png'">
                </div>
            </div>
            <div class="form-section">
                <h4 class="form-title">
                    <span data-lang="registration_form"></span>
                </h4>
                <form id="registrationForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group text-center input-15 hidden" style="margin-top:20px;">
                                 <p><label class="register-form" style="margin-top:10px;color:#888;" data-lang="upload_image"></label></p>
                                <div class="profile-upload">
                                    <img id="profilePreview" src="/images/profile-default.jpg" onerror="this.src='/images/profile-default.jpg'" alt="Profile Picture">
                                    <span class="camera-icon">
                                        <i class="fa fa-camera"></i>
                                    </span>
                                </div>
                                <input type="file" id="student_image_profile" name="student_image_profile" accept="image/*" style="display:none;">
                                <p style="margin: 15px auto;"> 
                                    <button type="button" class="btn btn-primary" id="viewProfile" style="font-size:12px;"><i class="fas fa-search-plus"></i> <span data-lang="preview"></span></button>
                                    <button type="button" class="btn btn-warning" id="removeProfile" style="font-size:12px;"><i class="fas fa-trash-alt"></i> <span data-lang="remove"></span></button>
                                </p>
                                <input type="hidden" id="ex_student_image_profile" name="ex_student_image_profile">
                            </div>
                            <div class="form-group form-input input-17 hidden">
                                <label class="register-form" for="student_idcard" data-lang="idcard"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control " id="student_idcard" name="student_idcard" autocomplete="off" maxlength="13">
                                    <span class="input-group-addon"><i class="fas fa-address-card"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-18 hidden">
                                <label class="register-form" for="student_passport" data-lang="passport"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="student_passport" name="student_passport" autocomplete="off">
                                    <span class="input-group-addon"><i class="fas fa-passport"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-24 hidden">
                                <label class="register-form" for="student_passport_expire" data-lang="passport_expire"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control datepicker-past" id="student_passport_expire" name="student_passport_expire" autocomplete="off">
                                    <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-16 hidden">
                                <label class="register-form" for="studentstudent_perfix_gender" data-lang="prefix"></label>
                                <select class="form-control" id="student_perfix" name="student_perfix">
                                    <option value=""></option>
                                    <option value="Mr." data-lang="mr"></option>
                                    <option value="Mrs." data-lang="mrs"></option>
                                    <option value="Miss" data-lang="miss"></option>
                                    <option value="Other" data-lang="other"></option>
                                </select>
                                <input type="text" class="form-control prefix-other hidden" id="student_perfix_other" name="student_perfix_other" style="margin-top: 10px;">
                            </div>
                            <div class="form-group form-input input-1 hidden">
                                <label class="register-form" for="student_firstname_en" data-lang="firstname_en"></label>
                                <input type="text" class="form-control" id="student_firstname_en" name="student_firstname_en" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-2 hidden">
                                <label class="register-form" for="student_lastname_en" data-lang="lastname_en"></label>
                                <input type="text" class="form-control" id="student_lastname_en" name="student_lastname_en" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-5 hidden">
                                <label class="register-form" for="student_firstname_th" data-lang="firstname_th"></label>
                                <input type="text" class="form-control" id="student_firstname_th" name="student_firstname_th" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-6 hidden">
                                <label class="register-form" for="student_lastname_th" data-lang="lastname_th"></label>
                                <input type="text" class="form-control" id="student_lastname_th" name="student_lastname_th" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-3 hidden">
                                <label class="register-form" for="student_nickname_en" data-lang="nickname_en"></label>
                                 <input type="text" class="form-control" id="student_nickname_en" name="student_nickname_en" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-4 hidden">
                                <label class="register-form" for="student_nickname_th" data-lang="nickname_th"></label>
                                 <input type="text" class="form-control" id="student_nickname_th" name="student_nickname_th" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-7 hidden">
                                <label class="register-form" for="student_gender" data-lang="gender"></label>
                                <select class="form-control" id="student_gender" name="student_gender">
                                    <option value=""></option>
                                    <option value="M" data-lang="male"></option>
                                    <option value="F" data-lang="female"></option>
                                    <option value="O" data-lang="other"></option>
                                </select>
                            </div>
                            <div class="form-group form-input input-14 hidden">
                                <label class="register-form" for="student_birth_date" data-lang="birthday"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control datepicker" id="student_birth_date" name="student_birth_date" autocomplete="off">
                                    <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-23 hidden">
                                <label class="register-form" for="student_nationality" data-lang="nationality"></label>
                                <select class="form-control" id="student_nationality" name="student_nationality"></select>
                            </div>
                            <div class="form-group form-input input-8 hidden">
                                <label class="register-form" for="student_email" data-lang="email"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="student_email" name="student_email" autocomplete="off">
                                    <span class="input-group-addon"><i class="fas fa-envelope-open-text"></i></span>
                                </div>
                                <div style="font-size: 11px; color: #888888; margin-top: 10px;">example@origami.life</div>
                            </div>
                            <div class="form-group form-input input-9 hidden">
                                <label class="register-form" for="student_mobile" data-lang="mobile"></label>
                                <input type="tel" class="form-control" id="student_mobile" name="student_mobile" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-10 hidden">
                                <label class="register-form" for="student_company" data-lang="company"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="student_company" name="student_company" autocomplete="off">
                                    <span class="input-group-addon"><i class="fas fa-building"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-11 hidden">
                                <label class="register-form" for="student_position" data-lang="position"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="student_position" name="student_position" autocomplete="off">
                                    <span class="input-group-addon"><i class="fas fa-briefcase"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-25 hidden">
                                <label class="register-form" for="student_reference" data-lang="reference"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="student_reference" name="student_reference" autocomplete="off">
                                    <span class="input-group-addon"><i class="fas fa-user-friends"></i></span>
                                </div>
                            </div>
                            <div class="form-group form-input input-19 hidden">
                                <label class="register-form" for="copy_of_idcard"><i class="fas fa-paperclip"></i> <span data-lang="copy_of_idcard"></span></label>
                                <p class="text-orange" data-lang="support_upload" style="margin: 10px auto;"></p>
                                <input type="file" class="form-control input-file" id="copy_of_idcard" name="copy_of_idcard" accept="image/*,.pdf">
                                <input type="hidden" id="existing_copy_of_idcard" name="existing_copy_of_idcard">
                            </div>
                            <div class="form-group form-input input-22 hidden">
                                <label class="register-form" for="copy_of_passport"><i class="fas fa-paperclip"></i> <span data-lang="copy_of_passport"></span></label>
                                <p class="text-orange" data-lang="support_upload" style="margin: 10px auto;"></p>
                                <input type="file" class="form-control input-file" id="copy_of_passport" name="copy_of_passport" accept="image/*,.pdf">
                                <input type="hidden" id="existing_copy_of_passport" name="existing_copy_of_passport">
                            </div>
                            <div class="form-group form-input input-20 hidden">
                                <label class="register-form" for="work_certificate"><i class="fas fa-paperclip"></i> <span data-lang="work_certificate"></span></label>
                                <p class="text-orange" data-lang="support_upload" style="margin: 10px auto;"></p>
                                <input type="file" class="form-control input-file" id="work_certificate" name="work_certificate" accept="image/*,.pdf">
                                <input type="hidden" id="existing_work_certificate" name="existing_work_certificate">
                            </div>
                            <div class="form-group form-input input-21 hidden">
                                <label class="register-form" for="company_certificate"><i class="fas fa-paperclip"></i> <span data-lang="company_certificate"></span></label>
                                <p class="text-orange" data-lang="support_upload" style="margin: 10px auto;"></p>
                                <input type="file" class="form-control input-file" id="company_certificate" name="company_certificate" accept="image/*,.pdf">
                                <input type="hidden" id="existing_company_certificate" name="existing_company_certificate">
                            </div>
                            <div class="form-group form-input input-12 hidden">
                                <label class="register-form" for="student_username" data-lang="username"></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="student_username" name="student_username" autocomplete="off" maxlength="20">
                                    <span class="input-group-addon"><i class="fas fa-user-lock"></i></span>
                                </div>
                                <div data-lang="username_info" style="font-size: 11px; color: #888888; margin-top: 10px;"></div>
                            </div>
                            <div class="form-group form-input input-13 hidden">
                                <label class="register-form" for="student_password" data-lang="password"></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="student_password" name="student_password" autocomplete="off" maxlength="20">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" id="togglePassword">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>
                                <div data-lang="password_info" style="font-size: 11px; color: #888888; margin-top: 10px;"></div>
                            </div>
                            <div class="form-group form-input payment hidden">
                                <label class="register-form"><i class="fas fa-paperclip"></i> <span data-lang="payment_title"></span></label>
                                <p class="text-orange" data-lang="payment_upload" style="margin: 10px auto;"></p>
                                <input name="payment_slip" id="payment_slip" type="file" class="dropify" data-allowed-file-extensions='["jpg", "jpeg", "png"]' data-max-file-size="20M"  data-height="155" data-default-file="">
                                <input name="ex_payment_slip" id="ex_payment_slip" type="hidden">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-container"></div>
                        </div>
                    </div>
                    <div class="form-group input-consent hidden">
                        <div class="checkbox">
                            <input type="checkbox" id="agree" name="agree" style="margin-left: 0;">
                            <label class="checkbox-label" for="agree" style="margin-bottom: -5px !important;"></label>
                            <span><span data-lang="i_accept"></span> <a class="open-term" style="color: #667eea; cursor:pointer;" data-lang="policy"></a></span>
                        </div>
                    </div>
                    <div class="before-login hidden">
                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-register"><span data-lang="register"></span></button>
                            <p class="text-center" style="margin: 15px auto;"><span data-lang="already"></span> <b><a class="login" style="color:#FF9900; cursor:pointer;" data-lang="login"></a></b></p>
                        </div>
                    </div>
                    <div class="after-login hidden">
                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-register"><span data-lang="save"></span></button>
                            <p class="text-center" style="margin: 15px auto;"><a class="logout" style="color:#FF9900; cursor:pointer;" data-lang="logout"></a></b></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="form-progress">
                <div class="progress">
                    <div id="progress_bar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="language-menu fx fx-right">
    <div lang="en" store-translate="EN" data-lang="eng">EN</div>
    <div lang="th" store-translate="TH" data-lang="thai">TH</div>
</div>
<button class="scroll-to-form-btn" id="scrollToFormBtn">
    <i class="fa fa-arrow-down"></i> <span data-lang="register"></span>
</button>
<div class="modal fade systemModal" role="dialog" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
</body>
</html>