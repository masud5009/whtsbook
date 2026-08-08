<html>
	<head>
        <title>{{$userBs->website_title}} - Maintainance Mode</title>
		<!-- favicon -->
		<link rel="shortcut icon" href="{{asset('assets/front/img/'.$userBs->favicon)}}" type="image/x-icon">
		<!-- bootstrap css -->
		<link rel="stylesheet" href="{{asset('assets/front/css/bootstrap.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/tenant/css/503.css')}}">
	</head>
	<body>
		<div class="container">
			<div class="content">
				<div class="row">
					<div class="col-lg-4 offset-lg-4">
						<div class="maintain-img-wrapper">
							<img src="{{\App\Http\Helpers\Uploader::getImageUrl(Constant::WEBSITE_MAINTENANCE_IMAGE,$userBs->maintenance_img,$userBs)}}" alt="">
						</div>
					</div>
				</div>
				<div class="row mt-4">
					<div class="col-lg-6 offset-lg-3">
						 <h2 class="mb-4">{{__('Under Maintenance !')}}</h2>
						<p class="maintain-txt">
							{!! nl2br($userBs->maintenance_msg) !!}
						</p>
					</div>
				</div>
				
			</div>
		</div>
	</body>
</html>
