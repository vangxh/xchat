# xchat

基于thinkphp6 + vue3 + workerman WEB客服聊天demo

坏境要求：php、mysql、redis

# 安装说明：

1、创建xchat数据库，导入xchat.sql

2、配置nginx

```
server {
    listen 80;
    # 你的域名或虚拟host或ip
    server_name chat.me;

    root d:/CmdTool/project/xchat/public; # 你的xhcat路径
    index index.php;

    # 静态文件
    location ~ ^/(?:static|upload)/ {
        concat on;
        concat_max_files 20;
        expires 10d;
    }

    location / {
        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php?s=$1 last;
        }
    }

    location ~ \.php$ {
        fastcgi_param   SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass    127.0.0.1:9000;
        include         fastcgi_params;
    }

    # socket
    location ~ ^/ws$ {
        proxy_pass http://127.0.0.1:8011;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_redirect off;
    }
}
```

3、windows下运行

    cd xchat

    chat.bat

4、linux下运行

    cd xchat

    php chat.php start -d

5、打开浏览器查看

    // 访客端
    http://chat.me

    // 客服端
    http://chat.me/index/kefu
