<?phsession_start();
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if($_SERVER['HTTP_HOST'] == 'localhost'){
        $request_uri = $_SERVER['REQUEST_URI'];
        $exl_path = explode('/',$request_uri);
        if(!file_exists($base_include."/dashboard.php")){
            $base_path .= "/".$exl_path[1];
        }
        $base_include .= "/".$exl_path[1];
    }
    define('BASE_PATH', $base_path);
    define('BASE_INCLUDE', $base_include);
    require_once $base_include.'/lib/connect_sqli.php';
    include_once(__DIR__ . "/config.php");
    include_once(__DIR__ . "/LINEHelper.php");
    $code = isset($_GET['code']) ? trim($_GET['code']) : '';
    $state = isset($_GET['state']) ? trim($_GET['state']) : '';
    // if (!$code || !$state) {
    //     redirectToRegister();
    // }
    // list($event_id, $exhibitor_id, $member_id) = parseState($state);
    // if (!$event_id) {
    //     redirectToRegister();
    // }
    // $event = getEventData($event_id);
    // if (!$event) {
    //     redirectToRegister();
    // }
    // $event_code = $event['event_code'];
    // $comp_id = $event['comp_id'];
    // $register_url = LINEHelper::getRegisterUrl($event_code);
    // $line_oa = $event['line_oa'];
    // if (!$line_oa) {
    //     redirectTo($register_url);
    // }
    // $token_data = getLineToken($line_oa);
    // if (!$token_data) {
    //     redirectTo($register_url);
    // }
    // $result = LINEHelper::getAccessToken($code, $token_data['line_client_id'], $token_data['line_client_secret']);
    // $access_token = isset($result['access_token']) ? $result['access_token'] : '';
    // if (!$access_token) {
    //     redirectTo($register_url);
    // }
    // $profile = LINEHelper::getLineProfile($access_token);
    // if (!isset($profile['userId'])) {
    //     redirectTo($register_url);
    // }
    // $userId = escape_string($profile['userId']);
    // $displayName = isset($profile['displayName']) ? escape_string($profile['displayName']) : '';
    // $pictureUrl = isset($profile['pictureUrl']) ? escape_string($profile['pictureUrl']) : '';
    // $statusMessage = isset($profile['statusMessage']) ? escape_string($profile['statusMessage']) : '';
    // $line_id = upsertLineLogin($userId, $displayName, $pictureUrl, $statusMessage);
    // if (!$line_id) {
    //     redirectTo($register_url);
    // }
    // $member_id = isset($member_id) ? $member_id : '';
    // $member_id = upsertMember($event_id, $line_id, $comp_id, $pictureUrl, $member_id);
    // createConnectionIfNotExist($line_id, $event_id, $member_id);
    // $allow = false;
    // if(isset($member_id)) {
    //     $allow = true;
    // }
    // $member = select_data("*","event_members","where line_login_id = '{$line_id}' and status = 0 and comp_id = '{$comp_id}' and member_id in (SELECT user_id FROM event_register WHERE event_id = '{$event_id}' and status = 0)");
    // if(empty($member)) {
    //     $allow = false;
    // } else {
    //     $register = select_data("*","event_register","where user_id = '{$member[0]['member_id']}' and status = 0");
    //     if(empty($register)) {
    //         $allow = false;
    //     } else {
    //         $allow = true;
    //     }
    // }
    // if ($allow === false) {
    //     $register_url_with_member = $register_url . '&member_id=' . urlencode($member_id);
    //     redirectTo($register_url_with_member);
    //     exit;
    // }
    // header("Location: /events/user/profile.php?token=" . LINE_REGISTER_TOKEN . "&event_id={$event_code}&id=" . base64_encode($member_id));
    // exit;
?>