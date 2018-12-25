{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
        .system-message{padding: 20px;position:fixed;top:50%;left:50%;-webkit-transform: translate(-50%,-50%);-moz-transform: translate(-50%,-50%);transform:translate(-50%,-50%);}
        .system-message img{width:100px;height:100px;display: block;margin: 0 auto;}
        .system-message .jump{ padding-top: 10px; color: #aaa}
        .system-message .jump a{ color: #333;color: #aaa}
        .system-message .success,.system-message .error{ line-height: 1.8em; font-size: 24px; text-align: center;}
        .system-message .success{color:#79b926}
        .system-message .error{color:#de4c4c}

        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display: none; }
    </style>
</head>
<body>

    <div class="system-message">
        <?php switch ($code) {?>
            <?php case 1:?>
             <img src='../../public/static/success.png' />
            <p class="success"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
            <?php case 0:?>
             <img src='../../public/static/error.png' />
            <p class="error"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
        <?php } ?>
        <p class="detail"></p>
        <p class="jump">
            页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </p>

    </div>
    <script type="text/javascript">
        //<?php echo($wait);?>
        //(function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        //})();
    </script>
</body>
</html>
