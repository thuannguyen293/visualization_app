<table>
    @if(isset($title))
        <tr>
            <td>{{$title}}</td>
        </tr>
    @endif
    @if(isset($header))
        <thead>
        <tr>
            @foreach($header as $h)
                <th>{{$h}}</th>
            @endforeach
        </tr>
        </thead>
    @endif
    @if(isset($body))
        <tbody>
        @foreach($body as $v)
            <tr>
                @foreach($v as $data)
                <td>{{$data}}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    @endif
</table>