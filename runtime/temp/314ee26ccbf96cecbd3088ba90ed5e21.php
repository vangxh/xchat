<?php /*a:1:{s:53:"D:\CmdTool\project\worker\xchat\view\index\index.html";i:1620052994;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>XChat携信客服-DEMO</title>
    <link rel="stylesheet" href="/static/chat/css/base.css" />
	<style>
		body {
			background-color: #aaa;
		}
	</style>
</head>
<body>
    <script src="http://chat.me/static/chat/js/xchat.js?uid=<?php echo htmlentities($uid); ?>"></script>
	<script>
		XChat.init({
			name: '访客',
			open: 1
		})
	</script>
</body>
</html>