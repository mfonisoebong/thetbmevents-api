<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h1>Invoice</h1>
<h3>Items bought:</h3>

<ul>
    @foreach($cartItems as $item)
        <li>
            <p>{{$item['name']}}</p>
            <span>({{$item['quantity']}})</span>: <b>{{$item['amount'] }}</b>
        </li>
    @endforeach
</ul>

<h4>Total: {{$total}}</h4>

<p>Signed: TBM</p>

</body>
</html>
