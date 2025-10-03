<?php
    include_once(__DIR__ . "/../../connect_sqli.php");
    include_once(__DIR__ . "/../function.php");
    $evId = '';
    if (isset($_GET['evId'])) {
        $evId = $_GET['evId'];
    } elseif (isset($_GET['liff_state'])) {
        parse_str(ltrim($_GET['liff_state'], '?'), $params);
        $evId = isset($params['evId']) ? $params['evId'] : '';
    }
    $event = getEventByCode($evId);
    $event_id = $event['event_id'];
    $event_code = $event['event_code'];
    $line_oa = $event['line_oa'];
    $Liff = select_data("line_liff_id","line_token","where line_token_id = '{$line_oa}'");
    $liffId = $Liff[0]['line_liff_id'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Connecting LINE...</title>
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>
<body>
<h3>กำลังเชื่อมบัญชี LINE กับ Event...</h3>
<script>
    const liffId = "<?php echo $liffId; ?>";
    const evId = "<?php echo htmlspecialchars($evId); ?>";
    const event_id = "<?php echo htmlspecialchars($event_id); ?>";
    liff.init({ liffId }).then(() => {
        if (!liff.isLoggedIn()) {
            liff.login({ redirectUri: window.location.href });
            return;
        }
        const ctx = liff.getContext();
        console.log("LIFF Context:", ctx);
        if (ctx.type === "external" || ctx.type === "none") {
            alert("❌ กรุณาเปิดจากแชทของ LINE OA เท่านั้น — ไม่สามารถส่งข้อความได้");
            return;
        }
        liff.getProfile().then(profile => {
            const userId = profile.userId;
            const msg = `Origami AI`;
            fetch("/lib/line/actions/save_user_event.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    userId: userId,
                    event_id: event_id
                })
            }).then(res => res.json()).then(data => {
                console.log("✅ บันทึกแล้ว:", data);
            }).catch(err => {
                console.error("❌ บันทกล้มเหลว", err);
            });
            liff.sendMessages([
                {
                    type: 'text',
                    text: msg
                }
            ]).then(() => {
                liff.closeWindow();
            }).catch(err => {
                alert("❌ ส่งข้อความล้มเหลว: " + err.message);
                liff.closeWindow();
            });
        }).catch(err => {
            alert("❌ ไม่สามารถดึงโปรไฟล์ได้: " + err.message);
            liff.closeWindow();
        });
    }).catch(err => {
        alert("❌ LIFF Init Failed: " + err.message);
        liff.closeWindow();
    });
</script>
</body>
</html>