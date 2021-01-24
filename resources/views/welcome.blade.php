{{--<table border="1">--}}
{{--    <tr>--}}
{{--        <td>Title</td>--}}
{{--        <td>Shop</td>--}}
{{--        <td>Weight</td>--}}
{{--        <td>Price</td>--}}
{{--    </tr>--}}
{{--    @foreach(\App\Classes\Parser::getGrechka() as $grechka)--}}
{{--        <tr>--}}
{{--            <td><a href="{{$grechka['src']}}">{{$grechka['title']}}</a></td>--}}
{{--            <td>{{$grechka['shop']}}</td>--}}
{{--            <td>{{$grechka['weight_per_one']}}</td>--}}
{{--            <td>{{$grechka['price_per_one']}}</td>--}}
{{--        </tr>--}}
{{--    @endforeach--}}
{{--</table>--}}

<?php
use \App\Classes\SmartParser;

$parser = new SmartParser();

$parser->get_grechka();

?>
