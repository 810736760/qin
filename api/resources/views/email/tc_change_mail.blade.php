@component('vendor.mail.markdown.table')
    <table style=" border:1px solid">
        <thead>
        <th style=" border:1px solid">
            期数
        </th>
        <th style=" border:1px solid">
            变更用户
        </th>
        <th style=" border:1px solid">
            学校信息
        </th>
        <th style=" border:1px solid">
            周几
        </th>
        <th style=" border:1px solid">
            内容
        </th>
        <th style=" border:1px solid">
            时间
        </th>
        </thead>
        @foreach ($content as $key => $item)
            <tr>
                <td style=" border:1px solid">{{$item['term_name']??''}}</td>
                <td style=" border:1px solid">{{$item['teacher_name']??''}}({{$item['tel']??''}})</td>
                <td style=" border:1px solid">{{$item['school_name']??''}}({{$item['class_name']??''}})</td>
                <td style=" border:1px solid">{{$item['date']??''}}</td>
                <td style=" border:1px solid"> {!! nl2br($item['content']??'') !!}</td>
                <td style=" border:1px solid">{{$item['updated_at']??''}}</td>
            </tr>
        @endforeach
    </table>
@endcomponent
