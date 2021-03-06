<?php /*a:1:{s:55:"D:\CmdTool\project\worker\xchat\view\xchat\service.html";i:1620047506;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>XChat客服</title>
    <link rel="stylesheet" href="/static/chat/css/base.css" />
    <link rel="stylesheet" href="/static/chat/css/xchat.css" />
    <link rel="stylesheet" href="/static/chat/css/xchat-service.css" />
</head>
<body id="app" v-cloak>
    <div class="xchat" ref="xchat">
        <div class="xchat-left">
            <div class="tab">
                <a href="javascript:;" @click="this.conf.visitor = 0" :class="{on:conf.visitor == 0}">在线访客</a>
                <a href="javascript:;" @click="tabHistory" :class="{on:conf.visitor == 1}">历史访客</a>
            </div>
            <div class="content">
                <ol v-show="conf.visitor == 0">
                    <template v-if="xchat.util.isEmpty(visitorList)">
                        <dt>暂无</dt>
                    </template>
                    <template v-else>
                        <dd v-for="(vo, key) in visitorList" @click="onChat(key, 0)" @touchend="onChat(key, 0)" :class="{on:key == to.id && conf.visitor == 0}">
                            <img :src="vo.avatar" class="avatar" :class="vo.status" />
                            <div class="user">
                                <span class="time">{{vo.time}}</span><span class="name">{{vo.name}}</span>
                                <div class="say" v-html="vo.content"></div>
                                <i v-show="vo.chat" class="badge">{{vo.chat}}</i>
                                <i class="del" @click="removeVisitor($event, key)">×</i>
                            </div>
                        </dd>
                    </template>
                </ol>
                <ol v-show="conf.visitor == 1">
                    <template v-if="historyList.length == 0">
                        <dt>暂无</dt>
                    </template>
                    <template v-else>
                        <dd v-for="(vo, key) in historyList" @click="onChat(key, 1)" @touchend="onChat(key, 1)" :class="{on:vo.id == to.id && conf.visitor == 1}">
                            <img :src="vo.avatar" class="avatar" :class="{offline:vo.status != 'online'}" />
                            <div class="user">
                                <span class="name">{{vo.name}}</span>
                                <div class="say">{{vo.time}}</div>
                                <i class="del" @click="removeHistory($event, key)">×</i>
                            </div>
                        </dd>
                    </template>
                </ol>
            </div>
            <div class="foot">
                <div class="page">
                    <xchat-page v-show="conf.visitor == 0" :total="visitor.total" limit="10" unit="人" @change="getVisitor"></xchat-page>
                    <xchat-page v-show="conf.visitor == 1" :total="visitorHistory.total" :limit="visitorHistory.limit" unit="人" @change="getVisitorHistory"></xchat-page>
                </div>
                <div class="set">
                    <a href="javascript:;" @click="pmWelcome">自动欢迎</a>
                    <a href="javascript:;" @click="pmDefault">默认展示</a>
                    <a href="javascript:;" @click="pmReply1">个人快捷回复</a>
                    <a href="javascript:;" @click="pmReply0">公共快捷回复</a>
                </div>
            </div>
        </div>
        <div class="xchat-center">
            <div class="xchat-box">
                <div class="xchat-top">
                    <template v-if="to.status == 'online'">
                        <a href="javascript:;" @click="conf.chatChange = !conf.chatChange">转接</a>
                        <a href="javascript:;" @click="chatStop">结束</a>
                    </template>
                    <span v-else-if="to.id">离线中...</span>
                    <span v-else>待接入...</span>
                </div>
                <div class="xchat-body" @scroll="scroll">
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
                                        <div class="say" v-html="vo.content"></div>
                                    </div>
                                    <div class="space"></div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="xchat-foot">
                    <xchat-editor
                        v-model="content"
                        @send="send"
                        @chatlog="getChatlog(1, true)"
                        :limit="300"
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
        <div v-show="conf.chatlog == 1" class="xchat-chatlog">
            <div class="title"><a href="javascript:;" @click="closeChatlog">×</a>消息记录</div>
            <div class="search"></div>
            <div class="content">
                <ul>
                    <li v-for="vo in chatlogList" :class="{mine:vo.id == mine.id}">
                        <span>{{vo.name}}</span>{{vo.time}}
                        <div v-html="vo.message"></div>
                    </li>
                </ul>
                <xchat-page :total="chatlog.total" :limit="chatlog.limit" pageShow="7" unit="条" @change="getChatlog"></xchat-page>
            </div>
        </div>
        <div v-else class="xchat-right">
            <template v-if="to.id">
                <ol class="kefu" v-if="conf.chatChange">
                    <dt>客服列表</dt>
                    <dd>
                        <ul>
                            <li v-for="vo in kefu">
                                <button class="btn" :disabled="vo.status != 'online'" @click="chatChange(vo.id)">转接</button>
                                <img :src="vo.avatar" /><span>{{vo.name}}<i>{{vo.status == 'online' ? '在线' : '离线'}}</i></span>
                            </li>
                        </ul>
                        <div><a href="javascript:;" class="btn cancel" @click="conf.chatChange = 0">取消</a></div>
                    </dd>
                </ol>
                <ol class="visitor" v-if="to.id">
                    <dt>访客信息</dt>
                    <dd>
                        <div><label>城市</label>{{to.city || '未知'}}</div>
                        <div>
                            <label>来源</label>
                            <a v-if="to.refer" :href="to.refer" :title="to.refer" target="_blank">{{to.refer}}</a>
                            <span v-else>直接访问</span>
                        </div>
                        <div><label>最新访问</label>{{xchat.util.timeFormat(to.time)}}</div>
                        <template v-if="to.num">
                            <div><label>首次访问</label>{{xchat.util.timeFormat(to.ctime)}}</div>
                            <div><label>访问次数</label>{{to.num}}次</div>
                        </template>
                    </dd>
                </ol>
            </template>
            <ol class="fastreply">
                <dt>快捷回复</dt>
                <dt class="extra"><xchat-search placeholder="输入关键词检索" :content="searchResult" @onpaste="replyCopy" @onchange="fastReplySearch"></xchat-search></dt>
                <dd>
                    <ul>
                        <li v-if="app.chat_fast && app.chat_fast.length" v-for="(item, k) in app.chat_fast"
                            @mouseenter="this.conf.fastReplyHover = k"
                            @mouseleave="this.conf.fastReplyHover = -1"
                            :class="{on: conf.fastReplyHover == k}">
                            <div @click="replyCopy(item.content)" v-html="item.content"></div>
                        </li>
                        <li v-else class="empty">暂无</li>
                    </ul>
                </dd>
            </ol>
        </div>
    </div>

    <script src="https://cdn.staticfile.org/vue/3.0.11/vue.global.prod.js"></script>
    <script src="http://chat.blog.me/static/chat/js/xchat-core.js"></script>
    <script src="/static/chat/js/xchat-service.js"></script>
</body>
</html>
