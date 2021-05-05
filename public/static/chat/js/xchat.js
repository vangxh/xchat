(function() {
    var __URL__ = Array.apply(null, document.scripts).pop().getAttribute('src').split('?');
    var XChat = function() {
        this.data = {
            msg: 0      // 当窗口隐藏时新消息数量
        }
        // 对外配置 [type[客服|访客],gid[客服分组],token,uid,name,avatar,open,icon,text,success-回调]
        this.conf = null;
    }
    // 解析用户及URL参数
    XChat.prototype.parse = function(conf) {
        var self = this, url = __URL__;
        // 设置用户配置
        self.conf = conf || {};
        // url参数
        url[1] = url[1].split(/=|&/);
        url[1].some(function(val, i) {
            if (i % 2 == 0) {
                self.conf[val] = url[1][i + 1];
            }
        });
        url = url[0].match(/^(https?:)?\/\/([^\/]+)/i);
        // 用于跨域
        self.conf.cors = location.protocol +'//'+ location.host;
        self.conf.protocol = url[1] || location.protocol;
        self.conf.host = url[2];
        self.conf.path = url[0];
        self.conf.refer = document.referrer || '';
        self.conf.gid = self.conf.gid || 0;
    }
    XChat.prototype.css = function(data) {
        data = JSON.stringify(data)
                    .replace(/","/g, ';')
                    .replace(/"|,/g, '')
                    .replace(/^\{|\}$/g, '')
                    .replace(/:\{/g, '{');
        if (document.getElementById('xchat-style')) {
            document.getElementById('xchat-style').cssText = data
        } else {
            var node = document.createElement('style');
            node.id = 'xchat-style';
            node.innerHTML = data;
            document.head.appendChild(node);
        }
    }
    // 启动
    XChat.prototype.run = function(url) {
        var self = this;
        self.box = document.createElement('div');
        self.box.className = 'xchat';
        self.box.innerHTML = [
            '<div class="xchat-main"></div>',
            '<a href="javascript:;" class="xchat-start">',
                '<span class="xchat-msg"></span>',
                '<span class="xchat-tip">',
                    self.conf.icon || '<svg t="1618456235819" class="icon" viewBox="0 0 1081 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2096" width="32" height="32"><path d="M989.316566 152.342676h-60.373265v-60.373264A92.169851 92.169851 0 0 0 836.974694 0.000805h-744.603599A91.968607 91.968607 0 0 0 0 91.768167v440.121101a94.584781 94.584781 0 0 0 94.584781 94.383537H100.622108v-40.248843h-6.037327A54.335938 54.335938 0 0 1 40.248843 531.889268V91.768167A51.518519 51.518519 0 0 1 91.767362 40.249648h744.603599a51.719763 51.719763 0 0 1 51.719764 51.719764v60.373264h-643.981491a91.968607 91.968607 0 0 0-91.767363 91.767363v457.025614a77.479023 77.479023 0 0 0 77.479024 77.479023h37.431424v238.474396a6.842303 6.842303 0 0 0 12.275897 4.226129l179.912329-228.412185a37.431424 37.431424 0 0 1 29.381656-14.28834h514.983948a77.680267 77.680267 0 0 0 77.680268-77.680267V244.512527a92.169851 92.169851 0 0 0-92.169851-92.169851z m51.921007 548.792977a37.23018 37.23018 0 0 1-37.23018 37.23018h-515.185192a77.479023 77.479023 0 0 0-60.373265 29.5829L319.777059 905.599777a6.842303 6.842303 0 0 1-12.275897-4.226129v-163.007815h-77.680267a37.23018 37.23018 0 0 1-37.23018-37.23018V244.110039a51.518519 51.518519 0 0 1 51.518519-51.518519h744.603599a51.921008 51.921008 0 0 1 51.921008 51.719763z" p-id="2097"></path><path d="M349.561203 323.199016h252.96398v40.248843H349.561203zM349.561203 453.404024h523.234962v40.248843h-523.234962zM349.561203 573.54682h296.633974v40.248844H349.561203z" p-id="2098"></path></svg>',
                    self.conf.text || '携信客服',
                '</span>',
            '</a>'
        ].join('');
        // 插入指定容器
        self.conf.rel
            ? document.getElementById(self.conf.rel).appendChild(self.box)
            : document.body.appendChild(self.box);
        // 事件
        self.box.children[1].onclick = function() {
            var that = this;
            if (!self.box.children[0].children[0]) {
                self.box.children[0].innerHTML = [
                    '<div class="xchat-head"><span></span><a href="javascript:;">×</a></div>',
                    '<iframe class="xchat-body" frameborder="0" allowtransparency="true" width="100%" src="'+ url +'"></iframe>'
                ].join('');
                // 接收子页面数据
                window.addEventListener('message', function(res) {
                    res = res.data;
                    if (res.type == 'INIT') {
                        self.box.children[0].children[0].children[0].innerHTML = '<img src="'+ res.data.avatar +'" />'+ res.data.name
                    } else if (res.type == 'MSG') {
                        if (self.box.children[0].style.display == 'none') {
                            self.data.msg++;
                            that.children[0].textContent = self.data.msg;
                            that.children[0].style.display = 'inherit';
                        }
                    } else if (res.type == 'RESIZE') {
                        self.box.style.width = self.box.offsetWidth + res.size +'px';
                    }
                }, false)
                // 向子页面发送数据
                self.box.children[0].children[1].onload = function() {
                    this.style.background = 'none';
                    self.box.children[0].removeAttribute('style');
                    this.contentWindow.postMessage(Object.assign({}, self.conf), self.conf.protocol +'//'+ self.conf.host)
                }
                // 事件
                self.box.children[0].children[0].children[1].onclick = function() {
                    self.box.children[0].style.display = 'none';
                    self.box.children[1].style.display = 'inherit';
                    // 主容器大小处理
                    self.toggle();
                }
            } else {
                self.box.children[0].removeAttribute('style');
            }
            that.style.display = 'none';
            that.children[0].style.display = 'none';
            self.data.msg = 0;
            // 主容器大小处理
            self.toggle();
        };
        // 触发事件
        (self.conf.open == 1 || self.conf.type == 'kefu' && self.conf.open != 0) && self.box.children[1].click();
    };
    // 显示与隐藏
    XChat.prototype.toggle = function() {
        var width = null, height = null;
        if (this.box.children[0].style.display != 'none') {
            if (this.conf.type == 'kefu') {
                width = '720px';
                height = '476px';
            } else {
                width = '320px';
                height = '480px';
            }
        }
        this.box.style.width = width;
        this.box.style.height = height;
    };
    // 启动入口
    XChat.prototype.init = function(conf) {
        var self = this;
        // 解析参数
        self.parse(conf);
        // 执行
        self.run(self.conf.path +'/xchat/'+ (self.conf.type == 'kefu' ? 'service' : 'visitor'));
        // 回调
        typeof self.conf.success == 'function' && self.conf.success.call(self.box);
        // 设置样式
        self.css({
            '.xchat': self.conf.type == 'kefu' ? {
                'position': 'absolute',
                'top': '60px',
                'left': '0px',
                'right': '0px',
                'margin': 'auto'
            } : {
                'position': 'absolute',
                'top': '48px',
                'right': '0px'
            },
            '.xchat .xchat-main': {
                'height': '100%',
                'display': 'flex',
                'flex-direction': 'column',
                'position': 'relative'
            },
            '.xchat .xchat-head': {
                'height': '40px',
                'background-color': '#008000',
                'padding': '6px 8px',
                'color': '#fff',
                'cursor': 'move',
                'position': 'relative',
                'flex-shrink': '0',
                'border-top-left-radius': '5px',
                'border-top-right-radius': '5px'
            },
            '.xchat .xchat-head span': {
                'display': 'inline-block',
                'cursor' : 'default'
            },
            '.xchat .xchat-head span img': {
                'width': '28px',
                'height': '28px',
                'border-radius': '50%',
                'vertical-align': 'middle',
                'margin-right': '8px'
            },
            '.xchat .xchat-head a': {
                'position': 'absolute',
                'top': '0px',
                'right': '8px',
                'font-size': '26px',
                'text-align': 'center',
                'text-decoration': 'none',
                'color': '#dfdfdf',
                'font-weight': '300'
            },
            '.xchat .xchat-head a:hover': {
                'color': '#fff'
            },
            '.xchat .xchat-body': {
                'overflow': 'hidden',
                'flex-grow': '1',
                'background': 'url('+ self.conf.path +'/static/chat/img/loading.gif) center center no-repeat'
            },
            '.xchat .xchat-start': {
                'position': 'fixed',
                'right': '20px',
                'bottom': '25%',
                'text-decoration': 'none',
                'font-size': '14px',
                'color': '#000'
            },
            '.xchat .xchat-start .xchat-msg': {
                'position': 'absolute',
                'padding': '0 4px',
                'height': '18px',
                'left': '-2px',
                'top': '-2px',
                'min-width': '18px',
                'line-height': '18px',
                'border-radius': '10px',
                'background-color': '#f00',
                'color': '#fff',
                'text-align': 'center',
                'font-size': '10px',
                'display': 'none'
            },
            '.xchat .xchat-start .xchat-tip svg': {
                'width': '28px',
                'height': '28px',
                'margin-right': '4px',
                'vertical-align': 'middle'
            },
            '.xchat .xchat-start:hover .xchat-tip svg': {
                'opacity': '0.5'
            }
        });
    };

    // 事例化
    var xchat = window.XChat = new XChat();
    // 自动执行
    setTimeout(function() {
        xchat.conf || xchat.init();
    }, 200);
}());