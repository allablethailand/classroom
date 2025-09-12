<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register • ORIGAMI PLATFORM</title>
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/classroom/register/css/register.css?v=<?php echo time(); ?>">
<script src="/dist/fontawesome-5.11.2/js/all.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>"></script>
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/jquery-ui.min.js"></script>
<script src="/classroom/register/js/register.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
</head>
<body>
<input type="hidden" id="classroomCode" value="<?php echo $classroomCode; ?>">
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
                        <img src="/images/origami-academy.png" style="height: 100px;">
                        <h5 style="margin-bottom: 25px;">Powered by Origami</h5>
                        <div>Copyright © 2020, Allable Co.,Ltd. All Rights Reserved.</div>
                    </div>
                    <div class="origami-events-notice"><label>Consent Notice</label>
                        This form has been created by the form owner. Any information you submit will be sent directly to the form owner. 
                        Allable is not responsible for the privacy practices or actions of third-party form owners. Please avoid submitting personal, sensitive, 
                        or confidential information, and never share your password.
                        <br><br> <label>Origami Form</label>
                        Please do not provide personal or sensitive information.
                        Thank you for your understanding!
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
                                <div class="profile-upload" style="position:relative; display:inline-block;">
                                    <img id="profilePreview" src="/images/profile-default.jpg" onerror="this.src='/images/profile-default.jpg'" class="img-circle" style="width:120px;height:120px;object-fit:cover;border:2px solid #ddd; cursor:pointer;">
                                    <span for="student_image_profile" class="camera-icon" style="position:absolute; bottom:5px; right:5px; background:#fff; border-radius:50%; padding:6px; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,0.2);"><i class="fa fa-camera"></i></span>
                                    <button type="button" id="removeProfile" style="display:none; position:absolute; top:5px; right:5px; background:#f44336; color:#fff; border:none; border-radius:50%; width:25px; height:25px; line-height:0px; font-size:20px; cursor:pointer;">&times;</button>
                                </div>
                                <input type="file" id="student_image_profile" name="student_image_profile" accept="image/*" style="display:none;">
                                <p><label style="margin-top:10px;color:#888;" data-lang="upload_image"></label></p>
                            </div>
                            <div class="form-group form-input input-17 hidden">
                                <label for="student_idcard" data-lang="idcard"></label>
                                <input type="text" class="form-control " id="student_idcard" name="student_idcard" autocomplete="off" maxlength="13">
                            </div>
                            <div class="form-group form-input input-18 hidden">
                                <label for="student_passport" data-lang="passport"></label>
                                <input type="text" class="form-control" id="student_passport" name="student_passport" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-16 hidden">
                                <label for="studentstudent_perfix_gender" data-lang="prefix"></label>
                                <select class="form-control" id="student_perfix" name="student_perfix">
                                    <option value=""></option>
                                    <option value="Mr." data-lang="mr"></option>
                                    <option value="Mrs." data-lang="mrs"></option>
                                    <option value="Miss" data-lang="miss"></option>
                                    <option value="Other" data-lang="other"></option>
                                </select>
                            </div>
                            <div class="form-group form-input input-1 hidden">
                                <label for="student_firstname_en" data-lang="firstname_en"></label>
                                <input type="text" class="form-control" id="student_firstname_en" name="student_firstname_en" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-2 hidden">
                                <label for="student_lastname_en" data-lang="lastname_en"></label>
                                <input type="text" class="form-control" id="student_lastname_en" name="student_lastname_en" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-5 hidden">
                                <label for="student_firstname_th" data-lang="firstname_th"></label>
                                <input type="text" class="form-control" id="student_firstname_th" name="student_firstname_th" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-6 hidden">
                                <label for="student_lastname_th" data-lang="lastname_th"></label>
                                <input type="text" class="form-control" id="student_lastname_th" name="student_lastname_th" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-3 hidden">
                                <label for="student_nickname_en" data-lang="nickname_en"></label>
                                 <input type="text" class="form-control" id="student_nickname_en" name="student_nickname_en" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-4 hidden">
                                <label for="student_nickname_th" data-lang="nickname_th"></label>
                                 <input type="text" class="form-control" id="student_nickname_th" name="student_nickname_th" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-7 hidden">
                                <label for="student_gender" data-lang="gender"></label>
                                <select class="form-control" id="student_gender" name="student_gender">
                                    <option value=""></option>
                                    <option value="M" data-lang="male"></option>
                                    <option value="F" data-lang="female"></option>
                                    <option value="O" data-lang="other"></option>
                                </select>
                            </div>
                            <div class="form-group form-input input-14 hidden">
                                <label for="student_birth_date" data-lang="birthday"></label>
                                <input type="text" class="form-control datepicker" id="student_birth_date" name="student_birth_date" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-8 hidden">
                                <label for="student_email" data-lang="email"></label>
                                <input type="text" class="form-control" id="student_email" name="student_email" autocomplete="off">
                                <div style="font-size: 11px; color: #888888; margin-top: 10px;">example@origami.life</div>
                            </div>
                            <div class="form-group form-input input-9 hidden">
                                <label for="student_mobile" data-lang="mobile"></label>
                                <input type="tel" class="form-control" id="student_mobile" name="student_mobile" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-10 hidden">
                                <label for="student_company" data-lang="company"></label>
                                <input type="text" class="form-control" id="student_company" name="student_company" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-11 hidden">
                                <label for="student_position" data-lang="position"></label>
                                <input type="text" class="form-control" id="student_position" name="student_position" autocomplete="off">
                            </div>
                            <div class="form-group form-input input-12 hidden">
                                <label for="student_username" data-lang="username"></label>
                                <input type="text" class="form-control" id="student_username" name="student_username" autocomplete="off" maxlength="20">
                                <div data-lang="username_info" style="font-size: 11px; color: #888888; margin-top: 10px;"></div>
                            </div>
                            <div class="form-group form-input input-13 hidden">
                                <label for="student_password" data-lang="password"></label>
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
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="agree" name="agree" style="margin-left: 0;">
                            <span style="margin-left: 20px;"><span data-lang="i_accept"></span> <a class="open-term" style="color: #667eea; cursor:pointer;" data-lang="policy"></a></span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-register"><span data-lang="register"></span></button>
                        <p class="text-center" style="margin: 15px auto;"><span data-lang="already"></span> <b><a class="login" style="color:#FF9900; cursor:pointer;" data-lang="login"></a></b></p>
                    </div>
                </form>
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