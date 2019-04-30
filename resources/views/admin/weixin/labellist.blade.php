<style>
	no-padding {
		padding: 0 !important;
	}
	.box-body {
		border-top-left-radius: 0;
		border-top-right-radius: 0;
		border-bottom-right-radius: 3px;
		border-bottom-left-radius: 3px;
		padding: 10px;
		background-color:#fff;
	}

	.table-responsive {
		width: 100%;
		margin-bottom: 15px;
		overflow-y: hidden;
		-ms-overflow-style: -ms-autohiding-scrollbar;
		border: 1px solid #ddd;
	}
	.table-responsive {
		min-height: .01%;
		overflow-x: auto;
	}
	* {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}

	div {
		display: block;
	}
	body {
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;
		font-weight: 400;
		overflow-x: hidden;
		overflow-y: auto;
	}
	body {
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
		font-size: 14px;
		line-height: 1.42857143;
		color: #333;
		background-color: #fff;
	}
	html {
		font-size: 10px;
		-webkit-tap-highlight-color: rgba(0,0,0,0);
	}
	html {
		font-family: sans-serif;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
	}
	.box-header:before, .box-body:before, .box-footer:before, .box-header:after, .box-body:after, .box-footer:after {
		content: " ";
		display: table;
	}
	:after, :before {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	.box-header:after, .box-body:after, .box-footer:after {
		clear: both;
	}
	.box-header:before, .box-body:before, .box-footer:before, .box-header:after, .box-body:after, .box-footer:after {
		content: " ";
		display: table;
	}
	:after, :before {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
</style>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>标签展示</title>
</head>
<body>
<div class="box-body table-responsive no-padding content">
	<div class="baBody">
	<div class="bbD">
		<table class="table table-hover">

		<thead>
			<tr>
				<td></td>
				<td>id</td>
				<td>name</td>
			</tr>
		</thead>
		<tbody>
			@foreach ($labelInfo as $v)
			<tr>
				<td><input type="checkbox"  name="box" class="check"></td>
				<td>{{$v['id']}}</td>
				<td>{{$v['name']}}</td>
			</tr>
				@endforeach
		</tbody>

	</table>
	</div>
</div>
</div>
</body>
<ml>

	<script src="/js/jquery-3.1.1.min.js"></script>
	<link rel="stylesheet" href="/layui/css/layui.css">
	<script src="/layui/layui.js"></script>
	<script>
		// $(function(){
		//    $('#sel').change(function(){
		//        var sel=$('#sel').val();
		//
		//        $.ajax({
        //            type: 'POST',
        //            dataType: 'json',
        //            url: 'LabelList',
        //            data: {sel: sel},
        //            success: function (msg) {
        //                // if (msg.status == 0) {
        //                //     layer.msg(msg.msg)
        //                // } else {
        //                //     layer.msg(msg.msg)
        //                // }
        //                // console.log(msg)
        //            }
		// 	   })
		//    })
		// })
	</script>
</ml>
