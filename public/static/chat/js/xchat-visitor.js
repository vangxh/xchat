(function() {
    // xchat
    var xchat = new XChat('visitor');
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
    // 接收消息
    xchat.on('getMessage', function(res) {
        // 机器人自动回复
        res.robot && (this.data.to.robot = 1);
        vm.chat.data.push(res);
        vm.scrollBottom();
        // 通知父窗口新消息
        parent.postMessage({
            type: 'MSG'
        }, this.conf.cors);
    });
    // 建立链接
    xchat.on('onConnect', function(res) {
        var self = this;
        // 无客服在线
        if (res.id == 0) {
            if (!this.data.to.id) {
                // 随机选择一客服
                var keys = Object.keys(this.data.kefu);
                if (keys.length > 0) {
                    this.data.to = this.data.kefu[keys[Math.floor(Math.random() * keys.length)]];
                } else {
                    this.data.to = res;
                    vm.noticeBox.splice(0, 0, '暂无客服，你可以通过机器人留言链接留言');
                }
            }
        } else {
            this.data.to = res;
            if (this.data.kefu[res.id]) {
                this.data.to.name = this.data.kefu[res.id].name;
                this.data.to.avatar = this.data.kefu[res.id].avatar;
            }
            // 表示转移客服的连接
            if (res.case == 1) {

            }
        }
        // 缓存客服id，用于在线时优先连接
        if (this.data.local.id != this.data.to.id) {
            this.data.local.id = this.data.to.id;
            this.cache(this.data.mine.id, this.data.local);
        }
        // 初始化连接
        if (!vm.conf.connect) {
            vm.conf.connect = true;
            // 默认展示
            if (!self.util.isEmpty(vm.app.chat_show)) {
                for (var key in vm.app.chat_show) {
                    // 0为分组
                    if (key != 0) {
                        vm.app.chat_show[key].name = vm.app.chat_show[0][key].name;
                    }
                }
                delete vm.app.chat_show[0];
                vm.chat.data.push({
                    type:'show',
                    name: self.data.to.name,
                    avatar: self.data.to.avatar,
                    data: vm.app.chat_show
                });
            }
            // 自动欢迎
            vm.app.chat_auto.forEach(function(vo) {
                vo.name = self.data.to.name;
                vo.avatar = self.data.to.avatar;
                vm.chat.data.push(vo);
            });
            vm.scrollBottom();
        }
    });
    // 通用消息
    xchat.on('onMessage', function(res) {
        if (res.type == 'notify') {
            vm.noticeBox.splice(0, 0, res.msg);
        }
    });
    // 连接终断
    xchat.on('onClose', function(msg) {
        vm.notice = msg;
        // 注意这里设置为0，用于区分初始化连接中或连接已关闭
        vm.conf.connect = 0;
    });
    // 客服上下线
    // xchat.on('onStatus', function(res) {
    //     var to = this.data.to;
    //     // 若是当前客服
    //     if (res.id == to.id && !to.robot) {
    //         var msg = res.status == 'online' ? '上线' : '离开';
    //         vm.noticeBox.splice(0, 0, '客服已'+ msg);
    //     }
    // });

    // vue事例化
    const app = Vue.createApp({
        data() {
            return {
                uploadImgUrl: '/xchat/upload_img',
                uploadFileUrl: '/xchat/upload_file',
                app: {},                // 应用全局数据
                mine: {},
                chat: {                 // 当前聊天列表
                    data: [],           // 数据
                    total: 0,           // 数量
                    page: 1,            // 当前页码
                    limit: 10           // 每页数目
                },
                content: '',
                notice: '',             // 通知提示
                noticeBox: [],          // 通知容器
                conf: {
                    scrollMore: false,
                    scrollLoad: false,
                    dbclick: 0,         // 模拟双击
                    connect: false,     // 是否已连接
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
                    xchat.data.tipBox = self.$el.querySelector('.xchat-body');
                    // 访客初始化
                    xchat.initVisitor('/xchat/initVisitor', function(data) {
                        var chatlog = xchat.getChatlog();
                        // 消息长度，更多消息显示
                        self.chat.data = chatlog.slice(-self.chat.limit);
                        self.chat.total = self.chat.data.length;
                        self.mine = data.mine;
                        self.app = data.app;
                        parent.postMessage({
                            type: 'INIT',
                            data: {
                                name: data.app.name,
                                avatar: data.app.avatar
                            }
                        }, res.cors);
                        // 访客socket连接
                        xchat.connectVisitor((res.protocol == 'https:' ? 'wss' : 'ws') +'://'+ res.host +'/ws')
                        self.scrollBottom();
                    });
                }, false);
            })
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
            send(content) {
                if (this.conf.connect) {
                    xchat.sendMessage(content === undefined ? this.content : content);
                } else {
                    xchat.tip(this.conf.connect === false ? '连接中，请稍后重试！' : '连接已关闭，请刷新重试！');
                }
            },
            scrollBottom() {
                this.$nextTick(() => {
                    var xchatBody = this.$el.querySelector('.xchat-body');
                    xchatBody.scrollTop = xchatBody.scrollHeight;
                })
            },
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
            // 删除通知
            noticeDel() {
                this.noticeBox.shift();
            },
            // 快捷回复
            fastReply(data) {
                if (this.conf.connect) {
                    var self = this, to = xchat.data.to, time = (new Date().getTime()).toFixed(0)/1000;
                    self.chat.data.push({
                        name: self.mine.name,
                        avatar: self.mine.avatar,
                        content: data.name,
                        time: time,
                        mine: true
                    });
                    self.scrollBottom();
                    setTimeout(function() {
                        self.chat.data.push({
                            name: to.name,
                            avatar: to.avatar,
                            content: data.content,
                            time: time + 1000
                        });
                        self.scrollBottom();
                    }, 1000);
                } else {
                    xchat.tip(this.conf.connect === false ? '连接中，请稍后重试！' : '连接已关闭，请刷新重试！');
                }
            }
        },
        computed: {
            chatList() {
                var self = this, push = [], time = 0;
                self.chat.data.forEach(function(item, i) {
                    push[i] = Object.assign({}, item);
                    push[i].time = xchat.util.timeFormat(item.time, true);
                    if (item.time - time > 300) {
                        time = item.time;
                        push[i].show = true;
                    }
                });
                return push;
            }
        }
    });
    // 编辑器组件
    app.component('xchat-editor', xchat.component('editor'));
    // 表情组件
    app.component('xchat-face', xchat.component('face'));
    // 绑定dom
    const vm = app.mount('#app');
}())
