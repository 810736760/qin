<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['title']}}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.2/jquery.min.js"
            integrity="sha512-tWHlutFnuG0C6nQRlpvrEhE4QpkG1nn2MOUMWmUeRePl4e3Aki0VB6W1v3oLjFtd0hVOtRQ9PHpSfN6u6/QXkQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/mobile-detect@1.4.5/mobile-detect.min.js"></script>

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
<style>
    * {
        margin: 0;
        padding: 0;
    }

    .main {
        padding-bottom: 3rem;
        max-width: 600px;
        margin: 0 auto;
        position: relative;
    }

    .top-img-box {
        width: 100%;
        height: 20rem;
        position: relative;
        overflow: hidden;
    }

    .white-mask_1 {
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        position: absolute;
        z-index: 1;
        /* background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #fff); */
        background: linear-gradient(180deg, rgba(239, 232, 242, 0.08) 2%, rgba(239, 232, 242, 0.45) 31%, rgba(239, 232, 242, 0.70) 64%, rgba(239, 232, 242, 0.85) 83%, #EFE8F2);
    }


    .white-mask_2 {
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        position: absolute;
        z-index: 1;
        /* background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #fff); */
        background: linear-gradient(180deg, rgba(255, 231, 193, 0.08) 2%, rgba(255, 231, 193, 0.45) 31%, rgba(255, 231, 193, 0.70) 64%, rgba(239, 242, 229, 0.85) 83%, #EFF2E5);
    }

    .white-mask_3 {
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        position: absolute;
        z-index: 1;
        /* background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #fff); */
        background: linear-gradient(180deg, rgba(255, 231, 193, 0.08) 2%, rgba(255, 231, 193, 0.45) 31%, rgba(255, 231, 193, 0.70) 64%, rgba(255, 231, 193, 0.85) 83%, #FFE7C1);
    }

    .white-mask_4 {
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        position: absolute;
        z-index: 1;
        /* background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #fff); */
        background: linear-gradient(180deg, rgba(255, 246, 218, 0.08) 2%, rgba(255, 246, 218, 0.45) 31%, rgba(255, 246, 218, 0.70) 64%, rgba(255, 246, 218, 0.85) 83%, #FFF6DA);
    }

    .white-mask_5 {
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        position: absolute;
        z-index: 1;
        /* background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #fff); */
        background: linear-gradient(180deg, rgba(239, 239, 242, 0.08) 2%, rgba(239, 239, 242, 0.45) 31%, rgba(239, 239, 242, 0.70) 64%, rgba(239, 239, 242, 0.85) 83%, #efeff2);
    }


    .top-content {
        display: flex;
        align-items: center;
        position: absolute;
        left: 4.5rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
    }

    .top-content img {
        width: 9rem;
        height: 12rem;
        box-shadow: 0px 1.5px 2px 0px rgba(0, 0, 0, 0.30);
        border-radius: 10px;
    }

    .top-content .novel-name {
        font-size: 2.5rem;
        font-family: HelveticaNeue, HelveticaNeue-Medium;
        font-weight: 700;
        color: #000000;
        padding-right: 3rem;
        margin-left: 2rem;
    }

    .top-img {
        width: 100%;
    }

    .content {
        width: 100%;
        padding: 0 2rem;
        box-sizing: border-box;
        margin-bottom: 7rem;
    }

    .content p {
        margin-bottom: 20px;
        font-size: {{$data['p_size']*2.2}}rem;
        line-height: 3.4rem;
    }

    .top-nav {
        position: fixed;
        top: 0;
        width: 100%;
        height: 7rem;
        background-color: #fff;
        max-width: 600px;
        margin: 0 auto;
        display: none;
        font-size: 2.3rem;
        font-family: HelveticaNeue, HelveticaNeue-Bold;
        font-weight: 700;
        text-align: center;
        color: #333333;
        line-height: 7rem;
    }

    .chapter-content {
        font-size: 2rem;
        font-family: HelveticaNeue, HelveticaNeue-Medium;
        font-weight: 500;
        text-align: left;
        color: #444444;
    }

    .chapter-content h3 {
        font-size: {{$data['p_size']*2.56}}rem;
        font-family: HelveticaNeue, HelveticaNeue-Medium;
        font-weight: 600;
        text-align: center;
        color: #333333;
        margin-bottom: 2rem;
        padding-top: 2rem;
    }

    .jump-btn {
        width: 38.46rem;
        height: 5.9rem;
        background: {{$data['btn_bg']?:'rgba(255, 185, 25, 1)'}};
        border-radius: 3rem;
        margin: 3rem auto;
        font-size: 2.3rem;
        font-family: HelveticaNeue, HelveticaNeue-Medium;
        font-weight: 500;
        text-align: center;
        color: #ffffff;
        line-height: 5.9rem;
    }

    .jump-btn-float {
        width: 26.4rem;
        height: 6.13rem;
        background: {{$data['btn_bg']?:'#151d2f'}};
        border-radius: 24px;
        font-size: 2.4rem;
        font-family: HelveticaNeue, HelveticaNeue-Medium;
        font-weight: 500;
        text-align: center;
        color: #ffffff;
        line-height: 6.13rem;
        position: fixed;
        bottom: 6rem;
        left: 50%;
        transform: translateX(-50%);
        display: none;
    }

    .promptInfo {
        font-size: 1.8rem;
        font-family: HelveticaNeue, HelveticaNeue-HelveticaNeue;
        font-weight: normal;
        text-align: center;
        color: #8c8c8e;
        line-height: 1.8rem;
    }

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

    .style_5 {
        background: #efeff2;
    }

</style>

<body>
<script>
    const init = () => {
        const width = document.documentElement.clientWidth;

        function IsPC() {
            var userAgentInfo = navigator.userAgent;
            var Agents = ["Android", "iPhone",
                "SymbianOS", "Windows Phone",
                "iPad", "iPod"];
            var flag = true;
            for (var v = 0; v < Agents.length; v++) {
                if (userAgentInfo.indexOf(Agents[v]) > 0) {
                    flag = false;
                    break;
                }
            }
            return flag;
        }

        if (IsPC()) {
            document.documentElement.style.fontSize = `7.8px`;
        } else {
            document.documentElement.style.fontSize = `${width / 50}px`;
        }
    }
    init();
    window.addEventListener('resize', init);
    window.addEventListener('orientationchange', init);
</script>
<div class="main style_{{ $data['bg_color'] }}">
    @if($data['top_title'])
        <div class="top-nav"> {!! $data['top_title'] !!}</div>
    @else
    @endif
    <div class="top-img-box">
        <div class="white-mask_{{ $data['bg_color'] }}"></div>
        <img class="top-img img" src="{{$data['banner']}}" alt=""/>
        <div class="top-content">
            <img class="img" src="{{$data['bottom']}}" alt="">
            <div class="novel-name">
                {!! $data['title'] !!}
            </div>
        </div>
    </div>
    <div class="content">
        <div class="chapter-content">
            {!! $data['content'] !!}
        </div>
    </div>
    <p class="promptInfo">{{$data['copyright']}}</p>
    <div class="jump-btn" onclick="jumpLink()">
        {{$data['btn_content'] ?:'Read More'}}
    </div>
    <div class="jump-btn-float" onclick="jumpLink()">
        {{$data['btn_content'] ?:'Read More'}}
    </div>
</div>
<script>
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
    window.addEventListener('scroll', () => {
        const {top, bottom} = document.querySelector(".jump-btn").getBoundingClientRect();
        const topBoxEle = document.querySelector(".top-img-box")
        const topBox = topBoxEle.getBoundingClientRect().top
        const topBoxHeight = topBoxEle.clientHeight
        const clientHeight = document.documentElement.clientHeight;
        if ((bottom > 0) && (top < clientHeight)) {
            $(".jump-btn-float").fadeOut()
        } else {
            $(".jump-btn-float").fadeIn()
        }
        if (topBox < 0 && Math.abs(topBox) >= topBoxHeight) {
            $(".top-nav").fadeIn()
        } else {
            $(".top-nav").fadeOut()
        }

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
    });

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
    console.log(redirect)

    let timer = null;

    function delay(callback, ms) {
        if (timer) return
        timer = setTimeout(() => {
            callback(redirect_url['api'] || '')
            timer = null
        }, ms);
    }

    let ip = '{{$data['ip']}}'

    function jumpLink() {
        delay(statClick(redirect_url['api'] || '', 'click'), 3000);
        statBtnClick({{$data['id']}})
        fbq('track', 'SubmitApplication', {}, {'eventID': 3});
    }


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
        let url = api + '/web_link_attribution'

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
</script>
</body>


</html>
