(function() {
    var xchat = parent.xchat, app, xchatEditor;
    app = Vue.createApp({
        data() {
            return {
                auth: xchat.data.mine.role ? true : false,
                type: conf.type,
                data: [],
                pid: 0,
                list: []
            }
        },
        mounted() {
            var self = this;
            xchat.ajax('/xchat/reply?type='+ this.type, function(res) {
                if (res.code) {
                    self.data = res.data;
                } else {
                    xchat.tip(res.msg);
                }
            });
        },
        methods: {
            pop(obj) {
                var self = this, content;
                xchat.data.tipDoc = document;
                if (self.type == 1) {
                    content =  [
                        '<xchat-editor v-model="data.content" @send="send"></xchat-editor>',
                        '<input type="text" v-model="data.sort" placeholder="排序" style="border:none;border-bottom:1px #d5d5d5 solid;float:left;width:80px;height:24px;margin-top:-26px;z-index:99;position:relative;" />'
                    ].join('');
                } else {
                    if (self.pid > 0) {
                        content =  [
                            '<div class="form2">',
                                '<p><label>关键词：</label><input type="text" name="name" v-model="data.name" /></p>',
                                '<p><label>排序：</label><input type="text" v-model="data.sort" /></p>',
                                '<p><label>内容：</label><span style="vertical-align:top;width:300px;display:inline-block;"><xchat-editor ref="editor" v-model="data.content" :focus="focus" @send="send"></xchat-editor></span></p>',
                            '</div>'
                        ].join('');
                    } else {
                        content =  [
                            '<div class="form">',
                                '<p><label>分组：</label><input type="text" name="name" v-model="data.name" placeholder="组名" /></p>',
                                '<p><label>排序：</label><input type="text" v-model="data.sort" placeholder="排序" /></p>',
                                '<p class="foot"><button class="btn" @click="send">提交</button></p>',
                            '</div>'
                        ].join('');
                    }
                }
                xchat.pop({
                    title: obj.title,
                    area: obj.area || ['60%', '60%'],
                    content: content,
                    success: function(node) {
                        var app = Vue.createApp({
                            data() {
                                return {
                                    data: Object.assign({}, obj.data),
                                    focus: false
                                }
                            },
                            methods: {
                                send() {
                                    var tof = false, that = this;
                                    for (var k in obj.data) {
                                        if (obj.required[k] && that.data[k] === '') {
                                            k == 'content'
                                                ? that.$refs.editor.$refs.editor.focus()
                                                : that.$el.querySelector('input[name="name"]').focus();
                                            xchat.tip('不许为空');
                                            return;
                                        }
                                        if (obj.data[k] !== that.data[k]) {
                                            tof = true;
                                            break;
                                        }
                                    }

                                    if (!tof) {
                                        return xchat.tip('表单未更新');
                                    }
                                    xchat.ajax(obj.url, {
                                        url: obj.url,
                                        type: 'POST',
                                        data: that.data,
                                        success: function(res) {
                                            if (res.code) {
                                                node.close();
                                                if (obj.key !== undefined) {
                                                    if (self.pid > 0) {
                                                        self.list[obj.key] = Object.assign(self.list[obj.key], that.data);
                                                    } else {
                                                        self.data[obj.key] = Object.assign(self.data[obj.key], that.data);
                                                    }
                                                } else {
                                                    if (self.pid > 0) {
                                                        self.list.push(Object.assign({id: res.data}, that.data));
                                                    } else {
                                                        self.data.push(Object.assign({id: res.data}, that.data));
                                                    }
                                                }
                                                that.data.content = '';
                                            }
                                            xchat.tip(res.msg);
                                        }
                                    });
                                }
                            }
                        });
                        app.component('xchat-editor', xchatEditor);
                        app.component('xchat-face', xchat.component('face'));
                        app.mount(node.querySelector('.xchat-pop-body'));
                    }
                }, document.body);
            },
            reply(id) {
                var self = this;
                xchat.ajax('/xchat/reply?type=0&pid='+ id, function(res) {
                    if (res.code) {
                        self.list = res.data;
                        self.pid = id;
                    } else {
                        xchat.tip(res.msg);
                    }
                });
            },
            replyBack() {
                this.pid = 0;
            },
            replyAdd() {
                var self = this;
                if (self.type == 1) {
                    this.pop({
                        title: '添加欢迎语',
                        required: {
                            content: true
                        },
                        data: {
                            content: '',
                            sort: ''
                        },
                        url: '/xchat/reply_add?type=1'
                    });
                } else {
                    if (this.pid > 0) {
                        this.pop({
                            title: '添加词条',
                            area: ['80%', '80%'],
                            required: {
                                name: true,
                                content: true
                            },
                            data: {
                                name: '',
                                sort: '',
                                pid: this.pid
                            },
                            url: '/xchat/reply_add?type=0'
                        });
                    } else {
                        this.pop({
                            title: '添加分组',
                            required: {
                                name: true
                            },
                            data: {
                                name: '',
                                sort: '',
                                pid: 0
                            },
                            url: '/xchat/reply_add?type=0'
                        });
                    }
                }
            },
            replyEdit(id, key) {
                if (this.type == 1) {
                    this.pop({
                        title: '编辑欢迎语',
                        required: {
                            content: true
                        },
                        data: {
                            content: this.data[key].content,
                            sort: this.data[key].sort,
                        },
                        url: '/xchat/reply_edit?id='+ id,
                        key: key
                    });
                } else {
                    if (this.pid > 0) {
                        this.pop({
                            title: '编辑问题',
                            area: ['80%', '80%'],
                            required: {
                                name: true,
                                content: true
                            },
                            data: {
                                name: this.list[key].name,
                                content: this.list[key].content,
                                sort: this.list[key].sort
                            },
                            url: '/xchat/reply_edit?id='+ id,
                            key: key
                        });
                    } else {
                        this.pop({
                            title: '编辑分组',
                            required: {
                                name: true
                            },
                            data: {
                                name: this.data[key].name,
                                sort: this.data[key].sort
                            },
                            url: '/xchat/reply_edit?id='+ id,
                            key: key
                        });
                    }
                }
            },
            replyDel(id, key) {
                var self = this;
                xchat.confirm('确认删除吗', function() {
                    xchat.ajax('/xchat/reply_del?id='+ id, function(res) {
                        if (res.code) {
                            self.pid > 0
                                ? self.list.splice(key, 1)
                                : self.data.splice(key, 1);
                        }
                        xchat.tip(res.msg);
                    });
                });
            }
        }
    });
    // 编辑器 重写
    xchatEditor = xchat.component('editor', window);
    xchatEditor.methods.send = function() {
        var self = this;
        if (xchat.util.length(self.modelValue, true) > self.limit) {
            xchat.tip('最多允许'+ self.limit +'字');
        } else {
            this.$emit('send');
        }
        this.focus && this.$el.querySelector('.content').focus();
    };
    xchatEditor.template = `
        <div class="xchat-editor">
            <div class="xchat-editor-tool">
                <span class="face" title="选择表情" @click="face"></span>
                <keep-alive><component :is="component" @faceClick="face($event)" @faceInsert="faceInsert($event)"></component></keep-alive>
            </div>
            <textarea class="filter"></textarea>
            <div class="xchat-editor-main">
                <div class="content" contenteditable="true" v-html="content" ref="editor"
                    @keydown.enter="inputWrap"
                    @input="input"
                    @focus="lock=true"
                    @keyup="keyup"
                    @click="click"
                    @blur="lock=false"
                    @paste="paste">
                </div>
            </div>
            <div class="xchat-editor-foot">
                <button class="btn" @click="send">提交</button>
            </div>
        </div>`;
    const vm = app.mount('#app')
})();
