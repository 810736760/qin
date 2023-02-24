@component('vendor.mail.markdown.table')

    @foreach ($content as $key => $item)
        @if($key)
           <hr style="margin-top: 10px"/>
        @endif
        <table style="border:1px">
            <tr>
                <td>广告组</td>
                <td><a href="{{$item['set_url']}}">{{$item['set']??''}}</a></td>
            </tr>
            <tr>
                <td>广告系列</td>
                <td><a href="{{$item['campaign_url']}}">{{$item['campaign']??''}}</a></td>
            </tr>
            <tr>
                <td>广告账户</td>
                <td><a href="{{$item['account_url']}}">{{$item['account']??''}}</a></td>
            </tr>
            <tr>
                <td>触发成效</td>
                <td>{{$item['insights']??''}}</td>
            </tr>
            <tr>
                <td>操作</td>
                <td>{{$item['opr']??''}}</td>
            </tr>
            <tr>
                <td>触发时间</td>
                <td>{{$item['time']??''}}</td>
            </tr>
        </table>
    @endforeach

@endcomponent
