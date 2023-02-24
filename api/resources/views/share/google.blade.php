<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"></meta>
    <!-- ################# META TAGS ########################## -->
    <!-- This tag defines the width and height of your ad: -->
    <meta name="ad.size" content="width=320, height=480"/>

    <!-- This tag makes sure the ad will render properly on mobile: -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ###################################################### -->


    <!-- ################# STYLE / PRESENTATION TAGS ########## -->

    <!-- This is a link to a google font - you can add these to include a font-style that will work across all browsers! -->
    <!-- Browse potential fonts you can include in your AdWords Ad at: https://www.google.com/fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,700' rel='stylesheet' type='text/css'>


    <!-- ################# INTERACTION TAGS ########## -->

    <!-- This script enables the use of jQuery in your ad: -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- ############################################# -->

    <style>
        * {
            padding: 0px;
            margin: 0px;
            font-family: "Helvetica", "Verdana", "Sans-Serif";
        }

        .ad {
            width: 320px; /* Sets the width for the ad */
            height: 480px; /* Sets the height for the ad */
            overflow: hidden; /* Makes it so anything inside the ad doesn't spill outside of the ad */
            position: relative; /* Makes it easier for us to position elements inside the ad */
            background-color: {{$data['main_bg']?:'#d3d0b7'}} ; /* Sets a background color for the entire ad */
            color: #3A3A3A; /* Sets a basic font and outline color for the entire ad */
            padding: 20px;
            line-height: 35px;
            box-sizing: border-box;
        }

        *, input, button {
            font-family: "Roboto Slab";
        }

        .book-box {
            overflow: hidden;
            height: 310px;
        }

        .last {
            display: none;
        }

        .chapter-options .all {
            display: flex;
            justify-content: space-between;
        }

        .all-box {
            display: none;
        }

        .chapter-options span {
            box-sizing: border-box;
            padding: 0 20px;
            border-radius: 5px;
            width: 130px;
            text-align: center;
            background-color: {{$data['btn_bg']?:'#1d0904'}};
            color: #fff;
            cursor: pointer;
        }

        .chapter-options .next span,
        .chapter-options .last span {
            text-align: center;
            display: inline-block;
            width: 100%;
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

        .content p {
            clear: both; /* 清除左右浮动 */
            word-wrap: break-word; /* IE */
            line-height: 31px;
        }

        h3 {
            line-height: 31px;
        }
    </style>
</head>
<body>

<!-- your ad goes inside here -->
<div class="ad">
    <div class="book-box">
        <div class="content">
            @if($data['banner_path'] != '')
                <div class="mybanner"
                     style=' background-image: url("img/{{$data['banner_path']}}"); background-size: 100% auto;height: 124px;'>
                </div>
            @else
            @endif
            {!! $data['content'] !!}
        </div>
    </div>
    <div class="chapter-options">
        <div class="all-box">
            <div class="all">
                <span onclick="previousChapter()">{{$data['pre_text']?:'Previous'}}</span>
                <span class="next-chapter material-icons" onclick="nextChapter()">{{$data['next_text']?:'Next'}}</span>
            </div>
        </div>
        <div class="next">
            <span class="next-chapter" onclick="nextChapter()">{{$data['next_text']?:'Next'}}</span>
        </div>
        <div class="last">
            <span onclick="previousChapter()">{{$data['pre_text']?:'Previous'}}</span>
        </div>
    </div>
</div>
</body>

<script type="application/javascript">
    $(document).ready(function () {

        let num = 0;
        let offsetTop = $(".content").height();
        previousChapter = function () {
            num += 310;
            $(".content").css({
                transform: `translateY(${num}px)`,
            });
            if (num === 0) {
                $("h3").show();
            }
            handle();
        };
        nextChapter = function () {
            if (num !== 0 && $(".content").position().top + -310 <= -offsetTop) {
                //ExitApi.exit();
            } else {
                num -= 310;
                $(".content").css({
                    transform: `translateY(${num}px)`,
                });
                if (num !== 0) {
                    $("h3", '.mybanner').hide();
                }
                handle();
            }
        };

        handle = function () {
            if (num === 0) {
                $(".all-box").hide();
                $(".last").hide();
                $(".next").show();
            } else {
                $(".all-box").show();
                $(".next").hide();
                $(".last").hide();
            }
        };
    });
</script>
</html>
