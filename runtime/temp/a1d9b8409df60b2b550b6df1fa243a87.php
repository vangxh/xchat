<?php /*a:1:{s:55:"D:\CmdTool\project\worker\xchat\view\xchat\visitor.html";i:1620047495;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>XChat客服</title>
    <link rel="stylesheet" href="/static/chat/css/base.css" />
    <link rel="stylesheet" href="/static/chat/css/xchat.css" />
    <link rel="stylesheet" href="/static/chat/css/xchat-visitor.css" />
</head>
<body id="app" v-cloak>
    <div class="xchat" ref="xchat">
        <div class="xchat-main">
            <div class="xchat-body" ref="xchat-body" @scroll="scroll">
                <div class="xchat-more" v-if="chat.total >= 10">
                    <img src="/static/chat/img/loading.gif" v-if="conf.scrollMore" />
                    <a href="javascript:;" v-else @click="scrollMore">查看更多消息</a>
                </div>
                <template v-for="(vo, i) in chatList">
                    <div class="line" :class="vo.mine ? 'right' : 'left'">
                        <div class="split" v-if="vo.split">
                            <hr />
                            <span>以下是新消息</span>
                        </div>
                        <div class="time" v-if="vo.show">{{vo.time}}</div>
                        <div class="user">
                            <template v-if="vo.mine">
                                <div class="space"></div>
                                <div class="msg">
                                    <div class="name">{{vo.name}}</div>
                                    <i v-if="vo._fail">!</i>
                                    <div class="say" v-html="vo.content"></div>
                                </div>
                                <div class="face"><img :src="vo.avatar" /></div>
                            </template>
                            <template v-else>
                                <div class="face"><img :src="vo.avatar" /></div>
                                <div class="msg">
                                    <div class="name">{{vo.name}}</div>
                                    <div class="say" v-if="vo.type == 'show'">
                                        <ol v-for="vo2 in vo.data">
                                            <dt>{{vo2.name}}</dt>
                                            <dd v-for="(vo3, key) in vo2._">
                                                <a href="javascript:;" @click="fastReply(vo3)">{{key+1}}. {{vo3.name}}</a>
                                            </dd>
                                        </ol>
                                    </div>
                                    <div class="say" v-else v-html="vo.content"></div>
                                </div>
                                <div class="space"></div>
                            </template>
                        </div>
                    </div>
                </template>
                <div class="notice" v-if="notice != ''">{{notice}}</div>
            </div>
            <div class="xchat-foot">
                <xchat-editor
                    v-model="content"
                    @send="send"
                    :upload_img_url="uploadImgUrl"
                    :upload_file_url="uploadFileUrl"></xchat-editor>
            </div>
        </div>
        <transition-group name="animate" tag="ul" class="xchat-notice" v-if="noticeBox.length">
            <li v-for="msg in noticeBox" :key="msg">
                <a href="javascript:;" @click="noticeDel">×</a>{{msg}}
            </li>
        </transition-group>
    </div>

    <script src="https://cdn.staticfile.org/vue/3.0.11/vue.global.prod.js"></script>
    <script src="http://chat.blog.me/static/chat/js/xchat-core.js"></script>
    <script src="/static/chat/js/xchat-visitor.js"></script>
</body>
</html>