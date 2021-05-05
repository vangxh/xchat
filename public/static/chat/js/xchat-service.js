(function() {
    // xchat
    var xchat = window.xchat = new XChat('service');
    // 移动
    xchat.data.tipDoc = parent.document;
    xchat.move(parent.XChat.box.querySelector('.xchat-head'));
    xchat.data.tipDoc = null;
    // 发送消息
    xchat.on('sendMessage', function(res) {
        vm.chat.data.push(res);
        vm.scrollBottom();
    });
    // 消息发送超时
    xchat.on('sendMessageFail', function(res) {
        // 聊天发送失败
        if (res.type == 'CHAT') {
            var len = vm.chat.data.length;
            while (len--) {
                if (vm.chat.data[len].time == res.data.mine.time) {
                    vm.chat.data[len]._fail = true;
                    res.data.mine._fail = true;
                    break;
                }
            }
        }
    });
    // 收取消息
    xchat.on('getMessage', function(res) {
        if (res.id == vm.to.id) {
            if (!vm.visitor.data[res.id]) {
                vm.visitor.total += 1;
            }
            vm.chat.data.push(res);
            vm.scrollBottom();
        } else {
            if (vm.visitor.data[res.id]) {
                if (vm.visitor.data[res.id].chat) {
                    vm.visitor.data[res.id].chat += 1;
                } else {
                    vm.visitor.data[res.id].chat = 1;
                }
            } else {
                vm.visitor.data[res.id] = {
                    id: res.id,
                    name: res.name,
                    avatar: res.avatar,
                    status: 'offline',
                    time: res.time,
                    chat: 1
                };
                vm.visitor.total += 1;
            }
        }
        // 通知父窗口新消息
        parent.postMessage({
            type: 'MSG'
        }, this.conf.cors);
    });
    // 接到访客连接
    xchat.on('onConnect', function(res) {
        if (vm.visitor.data[res.id]) {
            vm.visitor.data[res.id].time = res.time;
            vm.visitor.data[res.id].status = res.status;
        } else {
            vm.visitor.data[res.id] = res;
            vm.visitor.total += 1;
        }
        // 历史访客
        var key = this.util.isExist(vm.visitorHistory.data, res.id);
        key === false || (vm.visitorHistory.data[key].status = res.status);
    });
    // 用户上下线
    xchat.on('onStatus', function(res) {
        // 客服
        if (res.kefu) {
            vm.kefu[res.id] && (vm.kefu[res.id].status = res.status);
        } else {
            if (vm.visitor.data[res.id]) {
                if (res.status != 'online' && !vm.visitor.data[res.id].chat) {
                    vm.visitor.total--;
                    delete vm.visitor.data[res.id];
                } else {
                    vm.visitor.data[res.id].status = res.status;
                }
            };
            var key = this.util.isExist(vm.visitorHistory.data, res.id);
            key === false || (vm.visitorHistory.data[key].status = res.status);
        }
    });
    // 通用消息
    xchat.on('onMessage', function(res) {
        if (res.type == 'transfer') {
            this.confirm(this.data.kefu[res.data.uid].name +'向你转移访客：'+ res.data.name, function() {
                this.transferVisitor(res.data.id, res.data.uid);
                this.notify(res.data.uid, vm.mine.name +'接受了您的申请', 'transferAccept');
                vm.visitor.data[res.data.id] = {
                    id: res.data.id,
                    name: res.data.name,
                    avatar: res.data.avatar,
                    status: res.data.status,
                    time: res.data.time
                }
            });
        } else if (res.type == 'notify') {
            vm.noticeBox.splice(0, 0, res.msg);
            if (res.data == 'transferAccept') {
                // 移除在线访客
                delete vm.visitor.data[vm.to.id];
                // 转移成功
                vm.clear();
            }
        }
    });

    // vue事例化
    const app = Vue.createApp({
        data() {
            return {
                uploadImgUrl: '/xchat/upload_img',
                uploadFileUrl: '/xchat/upload_file',
                app: {},                // 应用全局信息
                to: {},                 // 访客信息
                mine: {},               // 当前登录客服信息
                kefu: {},               // 客服列表
                chat: {                 // 当前聊天列表
                    data: [],           // 数据
                    total: 0,           // 数量
                    page: 1,            // 当前页码
                    limit: 10           // 每页数目
                },
                visitor: {              // 在线访客列表
                    data: {},
                    total: 0,
                    limit: 10,
                    page: 1
                },
                visitorHistory: {       // 历史访客
                    data: []
                },
                chatlog: {},            // 消息记录
                content: '',            // 编辑器内容
                noticeBox: [],          // 消息通知列表
                searchResult: [],       // 快捷回复检索结果
                // 通基础配置 简易数据 显示、隐藏状态等
                conf: {
                    chatlog: 0,         // 消息记录
                    visitor: 0,         // 访客(0为在线，1为历史)
                    visitorPage: 1,     // 在线访客页码
                    chatChange: 0,      // 客服是否转移
                    scrollMore: false,  // 是否加载中
                    scrollLoad: false,  // scroll顶部
                    dbclick: 0,         // 模拟双击
                    fastReplyHover: -1,  // hover事件
                }
            }
        },
        beforeCreate() {
            var self = this;
            // 挂载xchat
            self.xchat = xchat;
            self.$nextTick(() => {
                window.addEventListener('message', function(res) {
                    res = res.data;
                    // 设置父窗口初始化数据
                    xchat.conf = res;
                    // 设置默认弹层容器
                    xchat.data.tipBox = self.$el.querySelector('.xchat-center');
                    // 客服初始化
                    xchat.initService('/xchat/initService', function(data) {
                        self.mine = data.mine;
                        self.visitor = data.visitor;
                        self.kefu = data.kefu;
                        self.app = data.app;
                        parent.postMessage({
                            type: 'INIT',
                            data: {
                                name: data.app.name,
                                avatar: data.app.avatar
                            }
                        }, res.cors);
                        // 客服socket连接
                        xchat.connectService((location.protocol == 'https:' ? 'wss' : 'ws') +'://'+ res.host +'/ws')
                        self.scrollBottom();
                    });
                }, false);
            });
        },
        mounted() {
            var self = this, timer;
            // 图片双击显示大图
            this.$el.querySelector('.xchat-body').addEventListener('click', function(e) {
                if (e.path[1] && e.path[1].className == 'say' && e.path[0].className != 'emoj') {
                    self.conf.dbclick++;
                    timer = setTimeout(function() {
                        self.conf.dbclick--
                        self.conf.dbclick < 0 && (self.conf.dbclick = 0);
                    }, 300);
                    if (self.conf.dbclick == 2) {
                        clearTimeout(timer);
                        self.conf.dbclick = 0;
                        window.open(e.target.src)
                    }
                }
            });
        },
        methods: {
            // 发送消息
            send() {
                xchat.sendMessage(this.content);
            },
            // 滚动到底部
            scrollBottom() {
                this.$nextTick(() => {
                    var xchatBody = this.$el.querySelector('.xchat-body');
                    xchatBody.scrollTop = xchatBody.scrollHeight;
                })
            },
            // 滚动到顶部加载更多消息
            scrollMore() {
                var self = this, chatlog = xchat.getChatlog();
                self.conf.scrollMore = true;
                if (chatlog.length > 0) {
                    var xchatBody = this.$el.querySelector('.xchat-body'),
                        height = xchatBody.scrollHeight;
                    chatlog = chatlog.slice(-this.chat.limit*(this.chat.page + 1), -this.chat.limit*this.chat.page);
                    setTimeout(function() {
                        self.chat.total = chatlog.length;
                        self.chat.page++;
                        self.chat.data = chatlog.concat(self.chat.data);
                        self.conf.scrollMore = false;
                        self.$nextTick(() => {
                            xchatBody.scrollTop = xchatBody.scrollHeight - height;
                        });
                    }, 200);
                }
            },
            // scroll事件
            scroll(e) {
                var self = this;
                if (e.target.scrollTop == 0) {
                    if (self.conf.scrollLoad) {
                        var node = e.target.querySelector('.xchat-more')
                        node && node.firstChild.click();
                        self.conf.scrollLoad = false;
                    } else {
                        setTimeout(function() {
                            e.target.scrollTop = 5;
                            self.conf.scrollLoad = true;
                        }, 100);
                    }
                }
            },
            // 左侧点击聊天
            onChat(key, type) {
                var chatlog;
                if (this.to.id != key || type == 0 && this.to.num || type == 1 && !this.to.num) {
                    if (this.to.id != key) {
                        chatlog = xchat.getChatlog(key);
                        xchat.data.to = {
                            type: 'kefu',
                            id: key,
                            kefu: 0
                        }
                        this.chat.data = chatlog.slice(-this.chat.limit);
                        this.chat.total = this.chat.data.length;
                        this.scrollBottom();
                        // 消息记录是否打开
                        this.conf.chatlog && this.closeChatlog();
                    }
                    if (type == 1) {
                        this.to = this.visitorHistory.data[key];
                    } else {
                        this.to = this.visitor.data[key];
                        this.visitor.data[key].chat = 0;
                    }
                }
            },
            // 移除会话
            removeVisitor(e, id) {
                // 当前会话
                if (this.to.id == id) {
                    this.clear();
                }
                delete this.visitor.data[id];
                this.visitor.total -= 1;
                e.stopPropagation();
            },
            // 移除历史访客
            removeHistory(e, key) {
                this.visitorHistory.splice(key, 1);
                this.visitorHistory.total -= 1;
                e.stopPropagation();
            },
            // 删除通知
            noticeDel() {
                this.noticeBox.shift();
            },
            // 历史访客
            tabHistory() {
                this.conf.visitor = 1;
                if (!this.visitorHistory.data.length) {
                    this.getVisitorHistory(1);
                }
            },
            // 分页查询历史访客
            getVisitorHistory(page) {
                var self = this;
                self.xchat.tip('请求中', self.$el.querySelector('.xchat-left .content'), function() {
                    self.xchat.ajax('/xchat/getHistory?p='+ page, function(res) {
                        if (res.code) {
                            self.visitorHistory = res.data;
                        } else {
                            xchat.tip(res.msg);
                        }
                    });
                    return true;
                });
            },
            // 分页查询在线访客
            getVisitor(page) {
                this.visitor.page = page;
            },
            // 结束会话
            chatStop() {
                var self = this;
                xchat.confirm('确认终断访客会话吗？', function() {
                    xchat.stopSocket(self.to.id);
                    self.clear();
                });
            },
            // 转移会话
            chatChange(id) {
                var self = this;
                this.xchat.confirm('确认转接吗？', function() {
                    xchat.transferKefu(id, self.to);
                    xchat.tip('转接申请已发送');
                });
            },
            // 快捷回复展开、收起
            dropup(key, val) {
                this.app.chat_fast[key].drop = !val;
            },
            // 快捷回复 复制
            replyCopy(content) {
                this.content = content;
            },
            fastReplySearch(val, key) {
                var self = this;
                xchat.ajax('/xchat/match_search?key='+ val +'&type='+ key, function(res) {
                    if (res.code) {
                        self.searchResult = res.data;
                    } else {
                        xchat.tip(res.msg);
                    }
                });
            },
            // 历史记录
            getChatlog(page, tof) {
                var self = this;
                if (this.to.id) {
                    if (this.conf.chatlog == 1 && tof) {
                        this.closeChatlog();
                    } else {
                        if (tof) {
                            self.conf.chatlog = 1;
                            parent.postMessage({
                                type: 'RESIZE',
                                size: 80
                            }, xchat.conf.cors);
                        }
                        xchat.tip('加载中', self.$el.querySelector('.xchat-chatlog .content'), function(complete) {
                            xchat.ajax('/xchat/chatlog?id='+ self.to.id +'&p='+ page, function(res) {
                                self.chatlog = res;
                                complete = true;
                            });
                        })
                    }
                } else {
                    xchat.tip('未选择用户');
                }
            },
            // 关闭消息记录
            closeChatlog() {
                var self = this;
                parent.postMessage({
                    type: 'RESIZE',
                    size: -80
                }, xchat.conf.cors);
                setTimeout(function() {
                    self.conf.chatlog = 0;
                    self.chatlog = {};
                }, 80);
            },
            // 清除当前会话信息
            clear() {
                this.to = {};
                this.chat = {
                    data: [],
                    total: 0,
                    page: 1,
                    limit: 10
                };
                this.conf.chatChange = 0;
                xchat.data.to = {};
            },
            // 自动欢迎管理
            pmWelcome() {
                xchat.pop({
                    type: 'iframe',
                    area: ['80%', '80%'],
                    title: '自动欢迎',
                    url: '/xchat/reply?type=1',
                    onClose: function() {
                        xchat.data.tipDoc = null;
                    }
                }, this.$el);
            },
            // 默认展示管理
            pmDefault() {
                xchat.pop({
                    type: 'iframe',
                    area: ['80%', '80%'],
                    title: '默认展示',
                    url: '/xchat/reply?type=0',
                    onClose: function() {
                        xchat.data.tipDoc = null;
                    }
                }, this.$el);
            },
            // 公共快捷回复管理
            pmReply0() {
                xchat.pop({
                    type: 'iframe',
                    area: ['80%', '80%'],
                    title: '公共快捷回复',
                    url: '/xchat/match?type=0',
                    onClose: function() {
                        xchat.data.tipDoc = null;
                    }
                }, this.$el);
            },
            // 个人快捷回复管理
            pmReply1() {
                xchat.pop({
                    type: 'iframe',
                    area: ['80%', '80%'],
                    title: '个人快捷回复',
                    url: '/xchat/match?type=1',
                    onClose: function() {
                        xchat.data.tipDoc = null;
                    }
                }, this.$el);
            }
        },
        computed: {
            // 聊天列表
            chatList() {
                var push = [], time = 0;
                this.chat.data.forEach(function(item, i) {
                    push[i] = Object.assign({}, item);
                    push[i].time = xchat.util.timeFormat(item.time, true);
                    if (item.time - time > 300) {
                        time = item.time;
                        push[i].show = true;
                    }
                });
                return push;
            },
            // 在线访客
            visitorList() {
                var self = this, push = {}, item, keys = Object.keys(self.visitor.data);
                keys = keys.splice((self.visitor.page-1)*self.visitor.limit, self.visitor.limit);
                keys.forEach(function(key) {
                    item = Object.assign({}, self.visitor.data[key]);
                    item.time = xchat.util.timeFormat(item.time, 'hh:ii');
                    push[key] = item;
                });
                return push;
            },
            // 历史访客
            historyList() {
                var push = [], temp;
                this.visitorHistory.data.forEach(function(item) {
                    temp = Object.assign({}, item);
                    temp.time = xchat.util.timeFormat(temp.time);
                    temp.ctime = xchat.util.timeFormat(temp.ctime);
                    push.push(temp);
                });
                return push;
            },
            // 历史记录
            chatlogList() {
                var push = [];
                if (this.chatlog.data) {
                    this.chatlog.data.forEach(function(item, i) {
                        push[i] = Object.assign({}, item);
                        push[i].time = xchat.util.timeFormat(item.time, true);
                    });
                }
                return push;
            }
        }
    });
    // 编辑器组件
    app.component('xchat-editor', xchat.component('editor'));
    // 表情组件
    app.component('xchat-face', xchat.component('face'));
    app.component('xchat-page', {
        data () {
            return {
                index: this.page,
                limit: this.limit,
                total: this.total,
                pageShow: this.pageShow,
                unit: this.unit
            }
        },
        props : {
            // 页面中的可见页码，其他的以...替代, 必须是奇数
            pageShow: {
                type: Number,
                default: 5
            },
            // 当前页码
            page: {
                type: Number,
                default: 1
            },
            // 每页显示条数
            limit: {
                type: Number,
                default: 10
            },
            // 总记录数
            total: {
                type: Number,
                default: 0
            },
            // 单位描述
            unit: {
                type: String,
                default: ''
            }
        },
        methods : {
            prev() {
                if (this.index > 1) {
                    this.go(this.index - 1)
                }
            },
            next() {
                if (this.index < this.pages) {
                    this.go(this.index + 1)
                }
            },
            first() {
                if (this.index !== 1) {
                    this.go(1)
                }
            },
            last() {
                if (this.index != this.pages) {
                    this.go(this.pages)
                }
            },
            go(page) {
                if (this.index !== page) {
                    this.index = page
                    this.$emit('change', this.index)
                }
            }
        },
        computed : {
            // 计算总页码
            pages() {
                return Math.ceil(this.total / this.limit)
            },
            // 计算页码，当count等变化时自动计算
            pagers () {
                const array = []
                const pageShow = this.pageShow
                const pageCount = this.pages
                let current = this.index
                const _offset = (pageShow - 1) / 2
                const offset = {
                    start : current - _offset,
                    end   : current + _offset
                }
                if (offset.start < 1) {
                    offset.end = offset.end + (1 - offset.start)
                    offset.start = 1
                }
                if (offset.end > pageCount) {
                    offset.start = offset.start - (offset.end - pageCount)
                    offset.end = pageCount
                }
                if (offset.start < 1) offset.start = 1

                for (let i = offset.start; i <= offset.end; i++) {
                    array.push(i)
                }
                return array
            }
        },
        watch : {
            page(val) {
                this.index = val || 1
            },
            limit(val) {
                this.limit = val || 10
            },
            total(val) {
                this.total = val || 0
            }
        },
        template: `<div class="xchat-page">
                    <a href="javascript:;" v-if="pages > pageShow && index > pageShow/2+1" @click="first">«</a>
                    <a href="javascript:;"
                        :class="{'page-curr' : index === pager}"
                        v-for="pager in pagers" @click="go(pager)">{{ pager }}
                    </a>
                    <a href="javascript:;" v-if="pages > pageShow && pages - index > pageShow/2" @click="last">»</a>
                    <span class="sum">共{{total}}{{unit}}</span>
                </div>`
    });
    // 检索
    app.component('xchat-search', {
        data() {
            return {
                timer: null,
                val: '',
                key: 0,
                drop: false,
                option: ['个人','通用'],
                result: [],
                show: false
            }
        },
        props: {
            placeholder: {
                type: String,
                default: ''
            },
            content: [String, Array]
        },
        watch: {
            content(val) {
                this.result = val;
                this.show = this.val == '' ? false : true;
            }
        },
        mounted() {
            var self = this;
            document.onclick = function() {
                self.drop = false;
            }
        },
        methods: {
            change(e) {
                var self = this, val = e.target.value.replace(/^\s+|\s+$/, '');
                if (val !== '' && self.val != val) {
                    self.timer && clearTimeout(self.timer);
                    self.timer = setTimeout(function() {
                        self.$emit('onchange', val, self.key);
                    }, 200);
                } else if (val === '') {
                    this.show = false;
                }
                self.val = val;
            },
            blur() {
                var self = this;
                setTimeout(function() {
                    self.show = false;
                }, 150);
            },
            focus() {
                this.val == '' || (this.show = true);
            },
            paste(val) {
                this.$emit('onpaste', val);
                this.show = false;
            },
            select(key) {
                this.key = key;
            }
        },
        template: `<div class="xchat-search">
                    <input type="text"
                        :placeholder="placeholder"
                        @blur="blur"
                        @focus="focus"
                        @input="change" />
                    <div class="result" v-show="show">
                        <div v-if="result.length"
                            v-for="item in result"
                            @click="paste(item.content)"
                            v-html="item.content">
                        </div>
                        <div v-else class="empty">无</div>
                    </div>
                    <div class="select">
                        <div class="show"
                            @mousedown="this.drop = false"
                            @mouseup="this.drop = true"
                            @click="$event.stopPropagation()"
                        >{{option[key]}}</div>
                        <div class="drop" :class="{on:drop}">
                            <div v-for="(val,key) in option" @click="select(key)">
                                {{val}}
                            </div>
                        </div>
                    </div>
                </div>`
    });
    // 绑定dom
    const vm = app.mount('#app');
}())
