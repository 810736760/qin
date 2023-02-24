@component('vendor.mail.markdown.table')

    @foreach ($content as $key => $item)
        @if($key)
           <hr style="margin-top: 10px"/>
        @endif
        <table style="border:1px">
            <tr>
                <td>类型</td>
                <td>{{$item['title']}}</td>
            </tr>
            <tr>
                <td>详细</td>
                <td>{{$item['detail']}}</td>
            </tr>
            <tr>
                <td>操作</td>
                <td>{{$item['extra']}}</td>
            </tr>
            <tr>
                <td>广告账户</td>
                <td><a href="{{$item['account_url']}}">{{$item['account_name']??''}}</a></td>
            </tr>
            <tr>
                <td>广告系列</td>
                <td><a href="{{$item['curl']}}">{{$item['cname']??''}}</a></td>
            </tr>

        </table>
    @endforeach

@endcomponent
