<?php /*a:1:{s:53:"D:\CmdTool\project\worker\xchat\view\xchat\reply.html";i:1619700875;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>XChat客服-通用设置</title>
    <link rel="stylesheet" href="/static/chat/css/layui.css" />
    <link rel="stylesheet" href="/static/chat/css/base.css" />
    <link rel="stylesheet" href="/static/chat/css/xchat.css" />
    <style>
        html,body {
            height: 100%;
        }
        body {
            padding: 8px;
        }
        .btn {
            padding: 1px 8px 2px;
            background-color: #00a0d8;
            color: #fff;
            font-size: 12px;
        }
        .btn.cancel, .btn[disabled] {
            background-color: #ddd;
            color: #777;
        }
        .btn:hover {
            opacity: .7;
            color: #fff;
        }
        .xchat-face {
            border: 1px #d5d5d5 solid;
            width: 266px;
            padding: 8px !important;
        }
        table button {
            margin-right: 4px;
        }
        table a {
            color: #00a0d8;
        }
        .form {
            width: 220px;
            margin: 0 auto;
        }
        .form p, .form2 p {
            margin: 8px 0;
        }
        .form .foot {
            text-align: right;
            padding-right: 4px;
        }
        .form2 label {
            display: inline-block;
            width: 80px;
            text-align: right;
        }
    </style>
</head>
<body id="app" v-cloak>
    <template v-if="type == 1">
        <button @click="replyAdd" class="btn">添加欢迎</button>
        <table class="layui-table">
            <thead>
                <tr>
                    <td width="60">NO</td>
                    <td>内容</td>
                    <td width="60">排序</td>
                    <td width="120">操作</td>
                </tr>
            </thead>
            <tr v-for="(vo, key) in data">
                <td>{{key+1}}</td>
                <td v-html="vo.content"></td>
                <td>{{vo.sort}}</td>
                <td>
                    <button class="btn" @click="replyEdit(vo.id, key)">编辑</button>
                    <button class="btn" @click="replyDel(vo.id, key)">删除</button>
                </td>
            </tr>
        </table>
    </template>
    <template v-else-if="pid">
        <button @click="replyBack" class="btn" style="margin-right: 8px;">返回</button>
        <button @click="replyAdd" class="btn">添加条目</button>
        <table class="layui-table">
            <thead>
                <tr>
                    <td width="40">NO</td>
                    <td>关键词</td>
                    <td width="60">排序</td>
                    <td width="120">操作</td>
                </tr>
            </thead>
            <tr v-for="(vo, key) in list">
                <td>{{key+1}}</td>
                <td>{{vo.name}}</td>
                <td>{{vo.sort}}</td>
                <td>
                    <button class="btn" @click="replyEdit(vo.id, key)">编辑</button>
                    <button class="btn" @click="replyDel(vo.id, key)">删除</button>
                </td>
            </tr>
        </table>
    </template>
    <template v-else>
        <button @click="replyAdd" class="btn">添加分组</button>
        <table class="layui-table">
            <thead>
                <tr>
                    <td width="40">NO</td>
                    <td>分组</td>
                    <td width="60">排序</td>
                    <td width="120">操作</td>
                </tr>
            </thead>
            <tr v-for="(vo, key) in data">
                <td>{{key+1}}</td>
                <td><a href="javascript:;" @click="reply(vo.id)">{{vo.name}}</a></td>
                <td>{{vo.sort}}</td>
                <td>
                    <button class="btn" @click="replyEdit(vo.id, key)">编辑</button>
                    <button class="btn" @click="replyDel(vo.id, key)">删除</button>
                </td>
            </tr>
        </table>
    </template>
<script>
    var conf = {
        type: "<?php echo htmlentities($type); ?>"
    }
</script>
<script src="https://cdn.staticfile.org/vue/3.0.11/vue.global.prod.js"></script>
<script src="/static/chat/js/xchat-user.js"></script>
</body>
</html>