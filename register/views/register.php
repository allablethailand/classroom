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
<script src="/classroom/register/js/register.js?v=<?php echo time(); ?>" type="text/javascript"></script>
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
                            <img onerror="this.src='/images/training.jpg'">
                        </div>
                        <div class="poster-text">
                            <h4 style="margin-top: 15px;" class="classroom-name"></h4>
                        </div>
                        <h5 class="classroom-date"></h5>
                        <h5 class="classroom-location"></h5>
                    </div>
                    <div class="detail-value classroom-information"></div>
                    <div class="detail-value contact-us"></div>
                    <div class="text-center hidden-xs" style="margin-bottom: 35px;">
                        <img src="/images/origami-academy.png" style="height: 100px;">
                        <h5 style="margin-bottom: 25px;">Powered by Origami</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3 right-section" id="registration-form">
            <div class="form-section">
                <div class="text-center">
                    <img class="comp-logo" onerror="this.src='/images/origami-academy.png'" style="height: 125px;">
                </div>
                <h4 class="form-title">
                    <span data-lang="registration_form"></span>
                </h4>
                <form id="registrationForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="firstName"><span data-lang="firstname_en"></span> <span style="color: red;">*</span></label>
                                <input type="text" class="form-control fixed-character" id="firstName" name="firstName" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="lastName"><span data-lang="lastname_en"></span> <span style="color: red;">*</span></label>
                                <input type="text" class="form-control fixed-character" id="lastName" name="lastName" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="firstName"><span data-lang="firstname_th"></span> <span style="color: red;">*</span></label>
                                <input type="text" class="form-control fixed-character" id="firstNameTH" name="firstNameTH" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="lastName"><span data-lang="lastname_th"></span> <span style="color: red;">*</span></label>
                                <input type="text" class="form-control fixed-character" id="lastNameTH" name="lastNameTH" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="gender"><span data-lang="nickname_en"></span> <span style="color: red;">*</span></label>
                                 <input type="text" class="form-control" id="nickname" name="nickname" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="gender"><span data-lang="nickname_th"></span> <span style="color: red;">*</span></label>
                                 <input type="text" class="form-control" id="nicknameTH" name="nicknameTH" autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="gender"><span data-lang="gender"></span> <span style="color: red;">*</span></label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value=""></option>
                                    <option value="M" data-lang="male"></option>
                                    <option value="F" data-lang="female"></option>
                                    <option value="O" data-lang="other"></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email"><span data-lang="email"></span> <span style="color: red;">*</span></label>
                        <div style="font-size: 11px; color: #888888; margin-bottom: 10px;">example@origami.life</div>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="phone"><span data-lang="mobile"></span> <span style="color: red;">*</span></label>
                        <input type="tel" class="form-control" id="mobile" name="mobile" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="organization"><span data-lang="company"></span> <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="organization" name="organization" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="position"><span data-lang="position"></span> <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="position" name="position" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="organization"><span data-lang="username"></span> <span style="color: red;">*</span></label>
                        <div data-lang="username_info" style="font-size: 11px; color: #888888; margin-bottom: 10px;"></div>
                        <input type="text" class="form-control fixed-character" id="username" name="username" autocomplete="off" required maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="password">
                            <span data-lang="password"></span> 
                            <span style="color: red;">*</span>
                        </label>
                        <div data-lang="password_info" style="font-size: 11px; color: #888888; margin-bottom: 10px;"></div>
                        <div class="input-group">
                            <input type="password" class="form-control fixed-character" id="password" name="password" autocomplete="off" required maxlength="20">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="togglePassword">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="agree" name="agree" required style="margin-left: 0;">
                            <span style="margin-left: 20px;"><span data-lang="i_accept"></span> <a class="open-term" style="color: #667eea; cursor:pointer;" data-lang="policy"></a></span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-register"><span data-lang="register"></span></button>
                        <p class="text-center" style="margin: 15px auto;"><span data-lang="already"></span> <b><a class="login" style="color:#FF9900; cursor:pointer;" data-lang="login"></a></b></p>
                    </div>
                </form>
            </div>
            <div class="text-center visible-xs" style="margin-bottom: 35px;">
                <img src="/images/origami-academy.png" style="height: 100px;">
                <h5 style="margin-bottom: 25px;">Powered by Origami</h5>
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