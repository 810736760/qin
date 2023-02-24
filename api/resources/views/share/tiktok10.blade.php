<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0, maximum-scale=1.0,user-scalable=no,minimal-ui">
    <title>{{$data['title']}}</title>
    <script src="https://statics.lmmobi.com/js/jquery-1.10.2.min.js"></script>
    <script>
        //Part1
        !function (w, d, t) {
            w.TiktokAnalyticsObject = t;
            var ttq = w[t] = w[t] || [];
            ttq.methods = ["page", "track", "identify", "instances", "debug", "on", "off", "once", "ready", "alias", "group", "enableCookie", "disableCookie"], ttq.setAndDefer = function (t, e) {
                t[e] = function () {
                    t.push([e].concat(Array.prototype.slice.call(arguments, 0)))
                }
            };
            for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
            ttq.instance = function (t) {
                for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]);
                return e
            }, ttq.load = function (e, n) {
                var i = "https://analytics.tiktok.com/i18n/pixel/events.js";
                ttq._i = ttq._i || {}, ttq._i[e] = [], ttq._i[e]._u = i, ttq._t = ttq._t || {}, ttq._t[e] = +new Date, ttq._o = ttq._o || {}, ttq._o[e] = n || {};
                var o = document.createElement("script");
                o.type = "text/javascript", o.async = !0, o.src = i + "?sdkid=" + e + "&lib=" + t;
                var a = document.getElementsByTagName("script")[0];
                a.parentNode.insertBefore(o, a)
            };

            // 需配置pixel ID
            ttq.load('{{ $data['pixel_id'] }}');
            ttq.page();
        }(window, document, 'ttq');
    </script>

    <script>
        window.onload = function () {
            document.addEventListener('touchstart', function (event) {
                if (event.touches.length > 1) {
                    event.preventDefault();
                }
            });
            var lastTouchEnd = 0;
            document.addEventListener('touchend', function (event) {
                var now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            document.addEventListener('gesturestart', function (event) {
                event.preventDefault();
            });
        }
    </script>
    <style>
        html {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-size: 100% 100%;
        }

        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background: url("https://statics.kwmobi.com/landing-page/tiktok.jpeg") no-repeat;
            background-size: 100% 100%;
        }

        #box {
            width: 100%;
            max-width: 550px;
            height: 100vh;
            /*background-color: #cef4f2;*/
            margin: 0 auto;
            overflow-y: auto;
            position: relative;
            -webkit-overflow-scrolling: touch;
        }


        #app {
            width: 100%;
            max-width: 550px;
            height: 100vh;
            /*background-color: #f9f5ec;*/
            margin: 0 auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }


        #head img {
            width: 90px;
            border-radius: 10px;
            position: absolute;
            left: 10px;
            top: 15px;
        }

        #head .title {
            position: absolute;
            left: 120px;
            top: 10px;
            width: calc(100% - 120px);
            color: rgb(182, 138, 50);
            font-size: 0.9rem;
            height: 60%;
            line-height: normal;
            word-wrap: break-word
        }

        .style_5 {
            background: #efeff2;
        }

        #head .tag {
            width: calc(100% - 120px);
            position: absolute;
            left: 120px;
            bottom: 20px;
            color: rgb(182, 138, 50);
        }

        #head .tag span {
            padding: 5px 8px;
            width: 30%;
            border-radius: 15px;
            border: 2px solid rgb(182, 138, 50);
            margin-right: 10px;
            font-size: 0.5em;
        }

        #content {
            padding: 0 5%;
        }

        #content p {
            text-indent: 2rem;
            line-height: 1.8rem;
            font-size: {{$data['p_size']}}em;
        }

        #content h3 {
            /*    font-size: 0.58666667rem;*/
            /*color: #000;*/
            /*text-align: justify;*/
            /*font-family: Microsoft YaHei;*/
            /*line-height: 0.96rem;*/
            /*font-weight: 400;*/
            /*margin: 0;*/
            /*padding: 0;*/
            color: #333;
            font-size: {{$data['p_size'] * 1.2}}em;
            font-weight: bold;
            letter-spacing: 0;
            line-height: 1.5;
            text-align: center;
        }

        #novel-name {
            font-size: {{$data['p_size'] * 1.2}}em;
        }

        #modal-title {
            font-size: {{$data['p_size'] * 1.2}}em;
        }

        .copy {
            width: 100%;
            height: auto;
            padding-top: 5px;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            text-align: center;
        }

        .copy .desc {
            width: 100%;
            color: black;
            font-weight: bold;
            text-align: center;
        }

        .app_name {
            width: 100%;
            text-align: center;
        }

        #xsName {
            background-color: #F6F6F6;
            color: #bf2c24;
            font-weight: bold;
            font-size: 30px;
        }

        @keyframes mymove {

            0% {
                top: 10px;
                opacity: 0;
            }

            100% {
                top: 0px;
                opacity: 1;
            }

        }

        .copy .red {
            color: #bf2c24;
        }

        .subscribe {
            width: 100%;
            height: 90px;
            background: #a5dffc;
            position: relative;
            margin-top: 25px;
            z-index: 2;
        }

        .subscribe .info {
            width: 100%;
            height: 32px;
            position: absolute;
            left: 0;
            top: calc(50% - 16px);
        }

        .subscribe .info img {
            width: 32px;
            height: 32px;
            line-height: 32px;
            border-radius: 50%;
            position: absolute;
            left: 5%;
            top: 0;
        }

        .subscribe .info div {
            height: 32px;
            line-height: 32px;
            position: absolute;
            left: 15%;
            top: 0;
            font-size: 1.3rem;
            color: #2a2a2a;
        }

        .subscribe #subscribe {
            height: 40px;
            line-height: 40px;
            padding: 0 20px;
            border-radius: 45px;
            text-align: center;
            position: absolute;
            right: 2%;
            top: 25px;
            background: #6fb3f2;
            font-size: 1.3rem;
            color: white;
            cursor: pointer;
        }

        #task {
            position: absolute;
            left: 0;
            top: 0;
            z-index: 8;
            width: 100%;
            height: 100%;
            background: rgba(51, 51, 51, .5);
            display: none;
        }

        #task .pop {
            width: 60%;
            padding: 0 5%;
            height: auto;
            text-align: center;
            background: white;
            border-radius: 8px;
            position: absolute;
            left: 15%;
            top: calc(50% - 138px);
        }

        #task .pop svg {
            width: 20%;
        }

        #task .pop .title {
            font-size: 1.4rem;
            margin-top: -9%;
        }

        #task .pop .img {
            padding-top: 20px;
        }

        #task .pop .img .sh {
            padding: 20px;
            font-weight: 700;
            font-size: 18px;
            color: #1F2833;
        }

        #task .pop .info {
            font-size: 1rem;
            margin: 10px 0;
        }

        #task .pop .but {
            width: 100%;
            height: 80px;
            position: relative;
        }

        #task .pop .but #confirm {
            width: 30%;
            position: absolute;
            right: 8%;
            top: 25px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            color: #0cd934;
            cursor: pointer;
            background: #FE7474;
            border: 1px solid #FE7474;
            border-radius: 26px;
            font-weight: 400;
            font-size: 16px;
        }

        #task .pop .but #openWechat {
            width: 75%;
            height: 45px;
            line-height: 45px;
            background: #d8a83d;
            border-radius: 45px;
            position: absolute;
            left: 12.5%;
            top: calc(50% - 25.5px);
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .message {
            color: #babbbb;
            font-size: 1.3rem;
            text-align: center;
            padding-top: 20px;
        }

        .bt {
            width: 90%;
            margin: 0 auto;
            padding-top: 8px;
        }

        .foot {
            width: 90%;
            padding: 5px 5% 8px 5%;
            height: auto;
        }

        .foot img {
            width: 100%;
        }

        .foot div {
            text-align: center;
        }

        .zdColor {
            color: #e03327 !important;
            border-color: #e03327 !important;
        }

        .dlvColor {
            color: #6a7f68 !important;
            border-color: #6a7f68 !important;
        }

        .d_lanColor {
            color: #8f9da0 !important;
            border-color: #8f9da0 !important;
        }

        .d_huiColor {
            color: #b0b0b0 !important;
            border-color: #b0b0b0 !important;
        }

        #goChannel {
            width: 80%;
            height: 45px;
            line-height: 45px;
            border-radius: 45px;
            background: rgb(222, 193, 135);
            border: none;
            cursor: pointer;
            margin: 25px auto 5px;
            text-align: center;
            color: white;
            font-size: 1.5rem;
        }

        #task_w {
            position: absolute;
            left: 0;
            top: 0;
            z-index: 8;
            width: 100%;
            height: 100%;
            background: rgba(51, 51, 51, .5);
            display: none;
        }

        #task_w .pop {
            width: 90%;
            padding: 0 0%;
            height: auto;
            text-align: center;
            background: white;
            border-radius: 8px;
            position: absolute;
            left: 5%;
            top: calc(50% - 138px);
        }

        #task_w .pop .info {
            width: 100%;
            padding-top: 5px;
        }

        #task_w .pop .info .t {
            padding: 3px 0;
        }

        #task_w .pop .info .f {
            font-size: 10px;
            padding: 3px 0;
        }


        #task_w .pop .info img {
            width: 100%;
            padding-top: 5px;
        }

        #task_w .pop .but {
            width: 100%;
            height: 80px;
            position: relative;
        }

        #cancel {
            border-radius: 26px;
            width: 39%;
            height: 38px;
            line-height: 38px;
            position: absolute;
            left: 5%;
            top: calc(50% - 25.5px);
            text-align: center;
            border: 1px solid #909090;
            color: #909090;
            cursor: pointer;
            font-weight: 400;
            font-size: 15px;
        }

        #goF {
            width: 39%;
            height: 38px;
            line-height: 38px;
            position: absolute;
            right: 5%;
            top: calc(50% - 25.5px);
            color: white;
            cursor: pointer;
            background: #FE7474;
            border: 1px solid #FE7474;
            font-weight: 400;
            font-size: 15px;
            border-radius: 26px;
        }

        .v-card {
            width: 90%;
            margin: 10px 5%;
            padding-top: 5px;
            text-align: center;
            background-color: #fff;
            color: rgba(0, 0, 0, .87);
            border-radius: 5px;
            box-shadow: 0 3px 1px -2px rgb(0 0 0 / 20%), 0 2px 2px 0 rgb(0 0 0 / 14%), 0 1px 5px 0 rgb(0 0 0 / 12%);
        }

        .v-card .subtitle-2 {
            font-size: .875rem !important;
            font-weight: 500;
            letter-spacing: .0071428571em !important;
            line-height: 1.375rem;
        }

        .v-card .error {
            background-color: #ff5252 !important;
            border-color: #ff5252 !important;
            color: white;
            height: 36px;
            min-width: 64px;
            padding: 0 16px;
            -ms-flex-align: center;
            align-items: center;
            border-radius: 4px;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-flex: 0;
            -ms-flex: 0 0 auto;
            flex: 0 0 auto;
            font-weight: 500;
            letter-spacing: .0892857143em;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            max-width: 100%;
            outline: 0;
            position: relative;
            text-decoration: none;
            text-indent: .0892857143em;
            text-transform: uppercase;
            -webkit-transition-duration: .28s;
            transition-duration: .28s;
            -webkit-transition-property: opacity, -webkit-box-shadow, -webkit-transform;
            transition-property: box-shadow, transform, opacity, -webkit-box-shadow, -webkit-transform;
            -webkit-transition-timing-function: cubic-bezier(.4, 0, .2, 1);
            transition-timing-function: cubic-bezier(.4, 0, .2, 1);
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            vertical-align: middle;
            white-space: nowrap;
            border-style: none;
            margin-bottom: 15px;
        }

        /*#app {*/
        /*    background: url("https://statics.kwmobi.com/landing-page/sh-bg.png");*/
        /*}*/

        /* 皮肤1 淡紫色*/
        .style_1 {
            background: #EFE8F2;
        }

        /* 皮肤2 淡绿色*/
        .style_2 {
            background: #EFF2E5;
        }

        /* 皮肤3 土黄色*/
        .style_3 {
            background: #FFE7C1;
        }

        /* 皮肤4 淡黄色*/
        .style_4 {
            background: #FFF6DA;
        }

        #img {
            width: 100%;
            height: 230px;
        }

        #openW img {
            width: 100%;
            height: auto;
            position: absolute;
            left: 0;
            top: 0;
        }

        #nextNovel {
            width: 80%;
            height: 45px;
            line-height: 45px;
            border-radius: 45px;
            background: {{$data['btn_bg']?:'rgba(255, 185, 25, 1)'}};
            border: none;
            cursor: pointer;
            margin: 25px auto 15px;
            text-align: center;
            color: #fff;
            font-size: 1.3rem;
            font-weight: 600;
        }


        /* 悬浮加上此类名 */
        .next_novel_float {
            animation: an_breathe_115 500ms linear alternate infinite;
            color: #fff;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0 auto;
            width: max-content !important;
            padding: 0 100px;
        }

        /* 悬浮加上此类名 */
        .company_name_float {
            height: 90px;
        }

        @keyframes an_breathe_115 {
            from {
                transform: scale(1) translateY(0);
            }

            to {
                transform: scale(1.15) translateY(0);
            }
        }

        .footer-icp {
            /*font-family: Arial, sans-serif;*/
            color: rgb(51, 51, 51);
            /*font-size: 0.32rem;*/
            letter-spacing: 0;
            line-height: 1.5;
            text-align: center;
            white-space: pre-line
        }
    </style>
</head>

<body>
<div id="box">
    <div id="app" class="style_{{ $data['bg_color'] }}">
        <!-- 需配置图片 -->
        <img id="img" src="{{ $data['banner'] }}" alt="">
        <div id="content">
            <h4 id="novel-name"></h4>
            <div id="book-content">
                {!! $data['content'] !!}
            </div>
        </div>
        <div id="nextNovel" class="next_novel_float"
             onclick="showTask()">{{$data['btn_content'] ?:'Next Chapter'}}</div>
        <div id="task">
            <div class="pop">
                <div class="img">
                    <img src="" id="azs" class="logo">
                    <div class="sh"></div>
                </div>
                <div class="but">
                    <div id="cancel" onclick="cancel()">Cancel</div>
                    <div onclick="go()" id="goF">Read Now</div>
                </div>
            </div>
        </div>
        {{--        <div id="company-name" class="company_name_float "--}}
        {{--             style="margin-top: 5px;margin-bottom: 10px;text-align: center">--}}
        {{--            @if(($data['bottom']?? '') != '')--}}
        {{--                <img id="img" src="{{ $data['bottom'] }}" alt="">--}}
        {{--            @else--}}
        {{--            @endif--}}
        {{--            {{$data['copyright']}}--}}
        {{--        </div>--}}

        <div style="padding-bottom: 70px">
            @if(($data['bottom']?? '') != '')
                <img id="img" src="{{ $data['bottom'] }}" alt="" style="background-size: 100% auto;height: 180px;">
            @else
            @endif
            <div class="footer-icp">{{$data['copyright']}}</div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript">
    // 小说名称
    $("#novel-name").html("{{$data['title']}}")


    function showTask() {
        $("#task").show()
    }

    function cancel() {
        $("#task").hide()
    }

    var u_1 = navigator.userAgent;
    var isIos = IsIos();
    let redirect_url = {!! $data['cta']  !!} // 跳转对应的googlepaly 或者appstore
    let redirect = '';

    if (isIos) {
        redirect = redirect_url['ios'];
        $("#azs").attr('src', 'https://statics.kwmobi.com/landing-page/apple.png')
        $(".sh").html('App Store')

    } else {
        redirect = redirect_url['android'];
        $("#azs").attr('src', 'https://statics.kwmobi.com/google.jpg')
        $(".sh").html('Google Play')
    }
    redirect = redirect || redirect_url['android'] || redirect_url['ios']


    function go() {
        js()
    }

    function js() {
        ttq.track('Download')
        request(redirect_url['api'] || '')
        statBtnClick({{$data['id']}})

    }

    function IsIos() {
        let deviceType = navigator.userAgent; //获取userAgent信息
        return !!deviceType.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    }

    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] === variable) {
                return pair[1];
            }
        }
        return '';
    }

    function getFbCli() {
        let cookieString = document.cookie;

        let cookieArr = cookieString.split("; ")

        let ttclid
        for (let i = 0; i < cookieArr.length; i++) {
            let cookieKey = cookieArr[i].split("=")[0];
            if (cookieKey == 'ttclid') {
                ttclid = cookieArr[i].split("=")[1]
            }
        }
        if (!ttclid) {
            ttclid = getQueryVariable('ttclid')
        }
        return ttclid
    }

    function clipboard(randomString) {
        let input = document.createElement("input");
        input.setAttribute("value", `${randomString}`);
        document.body.appendChild(input);
        input.select();
        document.execCommand("Copy");
        document.body.removeChild(input);
    };

    function request(api) {
        var rs = getFbCli()
        var web_sign = '{{$data['web_sign']}}'
        clipboard(web_sign)
        return $.ajax({
            type: 'post',
            url: api + '/web_link_attribution',
            datatype: 'json',
            data: {
                ip: '{{$data['ip']}}',
                link_id: "{!! $data['link_id']??''  !!}",
                nonce: "{!! $data['code']??''  !!}",
                user_agent: navigator.userAgent,
                platform: "{!! $data['package']??''  !!}",
                pixel_id: "{!! $data['pixel_id']??''  !!}",
                book_id: "{!! $data['book_id']??''  !!}",
                chapter_id: "{!! $data['chapter_id']??''  !!}",
                web_sign,
                middle_page_url: document.location.href,
                ttclid: rs,
            },
            success: function (r) {
                window.location.href = redirect
            },
            error: function () {
                window.location.href = redirect
            }
        })
    }


    function statBtnClick(code) {
        $.ajax({
            type: 'post',
            datatype: 'json',
            url: document.location.protocol + "//" + document.location.host + "/share/click",
            data: {
                code,
            },
            success: function (r) {

            },
            error: function () {
            }
        })
    }

</script>

</html>
