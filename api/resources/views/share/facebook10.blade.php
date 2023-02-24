<html lang="en" style="font-size:82.8px !important">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta version="1.0">
    <title>{{$data['title']}}</title>
    <style>
        .mybanner div,
        div.mybanner, .footer_btn {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            border: 0;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
            -webkit-text-size-adjust: none;
            font-size: 0.37333333rem;
            font-family: PingFang SC, Helvetica Neue, Helvetica, Hiragino Sans GB,
            Microsoft YaHei, '\5FAE\8F6F\96C5\9ED1', Arial, sans-serif;
            line-height: 1.5;
            outline: none;
        }

        div.mybanner, .footer_btn {
            height: 100%;
            background-size: cover;
            background-position: 50%;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
        }

        div.mybanner .mdl-content--2Svir {
            height: 100%;
        }

        body,
        html {
            padding: 0;
            margin: 0;
            background: #fff;
        }

        .page-loding {
            text-align: center;
            padding: 15px 0;
            font-size: 12px;
        }

        .main {
            max-width: 7.5rem;
            margin: 0 auto;
            box-shadow: 0px 0px 16px 3px #b0b0b0;
        }

        /* 皮肤1 淡紫色*/
        .style_1 {
            background: #EFE8F2;
        }

        .style_1 .content p.red_words {
            color: #C95850;
        }

        .style_1 .content .continue_btn {
            background: #FF9119;
        }

        .style_1 .footer_btn {
            background-image: url(http://statics.tmtmz.cn/img/png/style1_bg.png);
            background-size: 100% auto;
        }

        /* 皮肤2 淡绿色*/
        .style_2 {
            background: #EFF2E5;
        }

        .style_5 {
            background: #efeff2;
        }
        .style_2 .content p.red_words {
            color: #FC9E24;
        }

        .style_2 .content .btn {
            background: #77A347;
        }

        .style_2 .footer_btn {
            background-image: url(http://statics.tmtmz.cn/img/png/style2_bg.png);
            background-size: 100% auto;
        }

        /* 皮肤3 土黄色*/
        .style_3 {
            background: #FFE7C1;
        }

        .style_3 .content p.red_words {
            color: #FF5B5B;
        }

        .style_3 .content .btn {
            background: #B68A32;
        }

        .style_3 .footer_btn {
            background-image: url(http://statics.tmtmz.cn/img/png/style3_bg.png);
            background-size: 100% auto;
        }

        /* 皮肤4 淡黄色*/
        .style_4 {
            background: #FFF6DA;
        }

        .style_4 .content p.red_words {
            color: #FF6933;
        }

        .style_4 .content .btn {
            background: #FF7D4D;
        }

        .menu {
            padding: 0.26666667rem 0.4rem;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
            color: #999;
            font-size: 0.32rem;
        }

        .menu a {
            text-align: center;
            text-decoration: none;
            color: #999;
        }

        .title {
            padding: 0.38rem 0 0.2rem;
        }

        .title h1 {
            font-size: 0.58666667rem;
            color: #000;
            text-align: justify;
            font-family: Microsoft YaHei;
            line-height: 0.96rem;
            font-weight: 400;
            margin: 0;
            padding: 0;
        }

        .banner {
            /*padding: 0.26666667rem 0.4rem 0;*/
        }

        .banner img {
            width: 100%;
            /*border-radius: 0.13333333rem;*/
        }

        .banner-top {
            margin-bottom: 0.4rem;
        }

        .banner-top img {
            width: 100%;
            vertical-align: middle;
        }

        .content {
            padding: 0 0.4rem;
        }

        {{--.content p {--}}
        {{--    font-size: {{$data['p_size']}}em;--}}
        {{--    color: #4E4650;--}}
        {{--    line-height: 0.7rem;--}}
        {{--    text-align: justify;--}}
        {{--    margin: 0.2rem auto 0;--}}
        {{--    text-indent: 2em;--}}
        {{--    font-family: Microsoft YaHei;--}}
        {{--}--}}
        .content p {
            font-size: {{$data['p_size']}}em;
            /*overflow-x: hidden;*/
            word-break: break-word;
            /*   font-size: 0.4rem;*/
            /*text-indent: 2em;*/
            /*margin-top: 0.3rem;*/
            /*line-height: 1.5;*/
            color: #333;
            /*font-size: 0.5866666666666667rem;*/
            /*letter-spacing: 0.02666666666666667rem;*/
            line-height: 2;
            text-align: left;
        }

        .content h3 {
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

        .content p.red_words {
            color: #C95850;
            width: 100%;
            line-height: 0.68rem;
            font-size: 1.4em;
            font-weight: bold;
            text-align: center;
        }

        .content .btn {
            width: 7rem;
            height: 0.8rem;
            background: {{$data['btn_bg']?:'rgba(255, 185, 25, 1)'}};
            font-size: 0.36rem;
            color: #fff;
            line-height: 0.8rem;
            text-align: center;
            margin-top: 0.4rem;
            border-radius: 2.66667rem;
        }

        .next_btn {
            position: fixed;
            z-index: 10;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            background-color: #E6454A;
        }

        .footer_btn {
            margin-top: 0.4rem;
            background-image: url(https://statics.tmtmz.cn/img/png/vlEgqLxKGEDDnNy7X5jcuw8LdaX6CCrYHMNxMhv1.jpeg);
            background-size: 100% auto;
            height: 2rem;
        }

        .btn_jump {
            margin: auto;
            cursor: pointer;
        }

        .footer-icp {
            /*font-family: Arial, sans-serif;*/
            color: rgb(51, 51, 51);
            font-size: 0.32rem;
            letter-spacing: 0;
            line-height: 1.5;
            text-align: center;
            white-space: pre-line
        }

        .mybanner div,
        div.mybanner {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            border: 0;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
            -webkit-text-size-adjust: none;
            font-size: .37333333rem;
            font-family: PingFang SC, Helvetica Neue, Helvetica, Hiragino Sans GB, Microsoft YaHei, "\5FAE\8F6F\96C5\9ED1", Arial, sans-serif;
            line-height: 1.5;
            outline: none
        }

        div.mybanner {
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
        }

        div.mybanner .mdl-content--2Svir {
            height: 100%
        }

        /* 选择浏览器打开 */
        .btn_open_wx {
            background: url(https://statics.tmtmz.cn/img/png/10721590152236_.pic_hd.jpg) rgba(0, 0, 0, 0.5) no-repeat;
            background-size: 100% auto;
            position: fixed;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100vh;

        }

        /* 继续阅读弹窗样式 */
        .next_wrap {
            background: rgba(0, 0, 0, 0.5) no-repeat;
            position: fixed;
            width: 100%;
            height: 100vh;
            top: 0;
            display: none;
        }

        .black_sec {
            background: rgba(0, 0, 0, 0.5) no-repeat;
            position: fixed;
            width: 100%;
            height: 100vh;
        }

        .white_sec {
            width: 4.68rem;
            height: 3.94rem;
            background: rgba(255, 255, 255, 1);
            border-radius: 0.11rem;
            position: relative;
            top: 50%;
            left: 50%;
            position: relative;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-sizing: border-box;
        }

        .white_sec img {
            width: 2.1rem;
            height: 2.11rem;
            margin: 0 auto;
            display: inherit;
        }

        .close_jump_read {
            height: 0.56rem;
            width: 100%;
            display: flex;
            justify-content: flex-end;

        }

        .close_jump {
            border-radius: 0.28rem;
            border: 2px solid #FFFFFF;
            font-size: 0.3rem;
            color: #999999;
            margin-right: 0.2rem;

        }

        .btn_continue {
            width: 4.68rem;
            height: 0.8rem;
            background: linear-gradient(180deg, rgba(253, 189, 36, 1) 0%, rgba(251, 132, 27, 1) 100%);
            border-radius: 0 0 0.1rem 0.1rem;
            font-size: 0.32rem;
            /*font-family: PingFangSC-Medium, PingFang SC;*/
            font-weight: 500;
            color: rgba(255, 255, 255, 1);
            line-height: 0.45rem;
            position: absolute;
            bottom: 0;
            left: 0;
            line-height: 0.8rem;
            text-align: center;
        }

        @keyframes an_breathe_115 {
            from {
                transform: scale(1) translateY(0);
            }
            to {
                transform: scale(1.15) translateY(0);
            }
        }

        .callApp_fl_btn.an {
            animation: an_breathe_115 500ms linear alternate infinite;
            color: #fff;
        }

        .callApp_fl_btn {
            z-index: 999
        }

        .callApp_fl_btn {
            position: fixed;
            bottom: 0.25rem;
            left: 0;
            right: 0;
            margin: 0 auto;
            width: 6rem;
            height: 0.8rem;
            background: {{$data['btn_bg']?:'rgba(255, 185, 25, 1)'}};
            z-index: 9;
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: horizontal;
            -webkit-box-direction: normal;
            -webkit-flex-direction: row;
            -ms-flex-direction: row;
            flex-direction: row;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            border-radius: 1rem;
            padding: 0 0.3rem 0 0.38rem;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            text-align: center;
            border: 1px solid{{$data['btn_bg']?:'rgba(255, 185, 25, 1)'}};
            color: #fff;
        }

        .callUp_btn_txt {
            font-size: .34rem;
            margin: 0 auto;
            font-weight: 400;
        }

        /* 继续阅读弹窗样式 */
    </style>
    <script src="https://statics.lmmobi.com/js/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mobile-detect@1.4.5/mobile-detect.min.js"></script>

    <script>
        var resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize'

        function widthProportion() {
            var doc = document.body || document.documentElement
            var p = window.innerWidth
            // console.log(p)
            if (p > 750) {
                return 100
            } else {
                return p / 7.5
            }
        }

        function changePage() {
            document
                .getElementsByTagName('html')[0]
                .setAttribute(
                    'style',
                    'font-size:' + widthProportion() + 'px !important'
                )
        }

        changePage()
        window.addEventListener(resizeEvt, changePage, false)
    </script>


@if($data['from']==1 && $data['pixel_id'] )
    <!-- Meta Pixel Code -->
        <script>
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $data['pixel_id'] }}');
            fbq('track', 'PageView');
            fbq('track', 'ViewContent', {}, {'eventID': 1});
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{$data['pixel_id']}}&ev=PageView&noscript=1"
            />
        </noscript>
        <!-- End Meta Pixel Code -->
    @else
    @endif

</head>

<body style="font-size: 12px;overflow-x: hidden">
<div class="main style_{{ $data['bg_color'] }}">
    <div class="mybanner"
         style=' background-image: url("{{ $data['banner'] }}"); background-size: 100% auto;height: 3.5rem; border-radius: 0.2rem'>
    </div>
    <div class="content">
        <div>
            {!! $data['content'] !!}
            @if($data['description'] != '')
                <p class="red_words">
                    {{$data['description']}}
                </p>
            @else
            @endif


        </div>

    </div>
    <div style="padding-bottom: 1.1rem">
        @if(($data['bottom']?? '') != '')
            <div class="mybanner"
                 style=' background-image: url("{{ $data['bottom'] }}"); background-size: 100% auto;height: 1.8rem;'>
            </div>
        @else
        @endif

        <div class="footer-icp">{{$data['copyright']}}</div>
    </div>
    {{--    <div class="content">--}}
    {{--        <div class="next_btn btn btn_jump">{{$data['btn_content'] ?:'下一章'}}</div>--}}
    {{--    </div>--}}

    <div class="callApp_fl_btn  an  btn_jump">
        <div class="callUp_btn_txt">{{$data['btn_content'] ?:'下一章'}}</div>
    </div>

</div>
<script>

    (function () {
        function getClientHeight() {
            let clientHeight = 0;
            if (document.body.clientHeight && document.documentElement.clientHeight) {
                clientHeight = (document.body.clientHeight < document.documentElement.clientHeight) ? document.body.clientHeight : document.documentElement.clientHeight;
            } else {
                clientHeight = (document.body.clientHeight > document.documentElement.clientHeight) ? document.body.clientHeight : document.documentElement.clientHeight;
            }
            return clientHeight;
        }

        let uptoBottom = 0
        window.addEventListener("scroll", function () {
            //窗口高度
            var windowHeight = getClientHeight();
            //滚动高度
            var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            //页面高度
            var documentHeight = document.documentElement.scrollHeight || document.body.scrollHeight;

            if (windowHeight + scrollTop >= documentHeight) {
                uptoBottom++
                if (uptoBottom === 1) {
                    fbq('track', 'StartTrial', {}, {'eventID': 2});
                }
            }
        })
        Array.prototype.contains = function (needle) {
            for (i in this) {
                if (this[i].indexOf(needle) > 0) return i;
            }
            return -1;
        };
        let deviceType = navigator.userAgent; //获取userAgent信息
        let md = new MobileDetect(deviceType); //初始化mobile-detect
        let os = md.os(); //获取系统
        let model = "";
        if (os == "iOS") {
            //ios系统的处理
            os = md.os() + md.version("iPhone"); // 系统版本
            model = md.mobile(); // 手机型号
        } else if (os == "AndroidOS") {
            //Android系统的处理
            os = md.os() + md.version("Android");
            let deviceInfo = deviceType.split(";");
            if (deviceType.includes("Build/")) {
                let i = deviceInfo.contains("Build/");
                if (i > -1) {
                    model = deviceInfo[i].substring(
                        0,
                        deviceInfo[i].indexOf("Build/")
                    );
                }
            } else {
                model = deviceInfo[deviceInfo.length - 1].split(`)`)[0]
            }
        }
        let redirect_url = {!! $data['cta']  !!}// 跳转对应的googlepaly 或者appstore
        // let isAndroid = deviceType.indexOf("Android") > -1 || deviceType.indexOf("Adr") > -1;
        let isiOS = !!deviceType.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        let redirect = redirect_url['android'];
        if (isiOS) {
            redirect = redirect_url['ios'];
        }

        redirect = redirect || redirect_url['android'] || redirect_url['ios']
        if ({{$data['use_adjust']}}) {
            let campaign = getQueryString('campaign')
            let adgroup = getQueryString('adgroup')
            if (campaign !== "{" + "{campaign.name}" + "}") {
                redirect += '&campaign=' + campaign
            }
            if (adgroup !== "{" + "{adset.name}" + "}") {
                redirect += '&adgroup=' + adgroup
            }
        }

        let timer = null;

        function delay(callback, ms) {
            if (timer) return
            timer = setTimeout(() => {
                callback(redirect_url['api'] || '')
                timer = null
            }, ms);
        }

        let ip = '{{$data['ip']}}'

        $('.btn_jump').click(() => {
            delay(statClick(redirect_url['api'] || '', 'click'), 3000);
            statBtnClick({{$data['id']}})
            fbq('track', 'SubmitApplication', {}, {'eventID': 3});

        })

        function clipboard(randomString) {
            let input = document.createElement("input");
            input.setAttribute("value", randomString);
            document.body.appendChild(input);
            input.select();
            document.execCommand("Copy");
            document.body.removeChild(input);
        };

        let pollingTimer = null
        let startTime = 0 // 初始开始请求时间
        // 开始准备轮询
        function handlerData() {
            startTime = new Date().getTime() // 获取触发轮询时的时间
            inquireData() // 调用轮询接口,开始进行轮询
        }

        function inquireData() {
            const reload = () => {
                clearTimeout(pollingTimer) // 清除定时器
                // 超过30分钟则停止轮询
                if (new Date().getTime() - startTime > 1440 * 60 * 1000) {
                    clearTimeout(pollingTimer)
                    return
                }
                // 3s一次, 轮询中
                pollingTimer = setTimeout(() => {
                    inquireData() // 调用轮询
                }, 3000)
            }
            statClick(redirect_url['api'] || '', reload)
        }

        handlerData()
        function getQueryString(name) {
            var url = encodeURI(window.location.search);
            var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
            var r = url.substr(1).match(reg);
            if (r != null) {
                return decodeURI(r[2]);
            }
            return null;
        }


        function statClick(api, callback) {
            if (!api) {
                return
            }
            var web_sign = '{{$data['web_sign']}}'
            if (callback === 'click') {
                clipboard(web_sign)
            }
            var cookieString = document.cookie;
            var cookieArr = cookieString.split("; ")
            let fbc = ''
            let fbp = ''
            for (let i = 0; i < cookieArr.length; i++) {
                let cookieKey = cookieArr[i].split("=")[0];
                if (cookieKey == '_fbc') {
                    fbc = cookieArr[i].split("=")[1];
                    // url = url + '&fbc=' + cookieArr[i].split("=")[1];
                } else if (cookieKey == '_fbp') {
                    fbp = cookieArr[i].split("=")[1]
                    // url = url + '&fbp=' + cookieArr[i].split("=")[1];
                }
            }
            let ad_id = getQueryString('ad_id')
            let data = {
                link_id: "{!! $data['link_id']??''  !!}",
                phone_model: model + os,
                platform: "{!! $data['package']??''  !!}",
                pixel_id: "{!! $data['pixel_id']??''  !!}",
                book_id: "{!! $data['book_id']??''  !!}",
                chapter_id: "{!! $data['chapter_id']??''  !!}",
                fbp: fbp,
                fbc: fbc,
                ua: navigator.userAgent,
                web_sign,
                nonce: "{!! $data['code']??''  !!}",
                ip,
                middle_page_url: document.location.href,
                ad_id
            }
            url = api + '/web_link_attribution'
            return $.ajax({
                type: 'post',
                datatype: 'json',
                url,
                data,
                success: function (res) {
                    if (callback === 'click') { // todo 改动
                        window.location.href = redirect;
                    } else {
                        callback()
                    }
                },
                error: function () {
                    if (callback === 'click') { // todo 改动
                        window.location.href = redirect;
                    } else {
                        callback()

                    }
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
    })()
</script>

</body>

</html>
