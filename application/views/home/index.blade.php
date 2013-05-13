@section('content')
<div class="hero-unit">
	<h1>کتاب‌خانه</h1>
	<p>برای مدیریت کتاب‌ها در مسجد، مدرسه یا محله‌ی شما</p>
</div>

<div class="row">
	<div class="span3">
		<h3>کتاب‌داری</h3>
		<p>
			<ul>
				<li>چاپ لیست‌ها و برچسب‌ها</li>
				<li>محاسبه جریمه دیرکرد</li>
			</ul>
		</p>
	</div>
	<div class="span3">
		<h3>سایت کتاب‌خانه</h3>
		<p>
			<ul>
				<li>جستجو در کتاب‌ها</li>
				<li>نمایش کتاب‌های امانت داده‌شده</li>
			</ul>
		</p>
	</div>
	<div class="span3">
		<h3>رقابت سالم</h3>
		<p>
			<ul>
				<li>طراحی سوال از روی کتاب‌ها</li>
				<li><a href="http://reghaabat.ir">بیشتر بدانید...</a></li>
			</ul>
		</p>
	</div>
</div>

<p class="download"><a id="download" class="btn btn-large btn-primary" href="http://bayanbox.ir/id/3919373541372850975?download">دریافت نرم‌افزار</a></p>

<h3>کتاب‌خانه‌ها</h3>
<div class="libraries row">
	@foreach ($libraries as $library)
		<div class='span5'>
			<img class='tiny' src='{{ $library->image ? "files/". $library->image : "" }}'>
			<p class='title'><a href='{{ $library->slug }}'>{{ $library->title }}</a></p>
			<p class='subtitle'>{{ $library->books }} کتاب، {{ $library->users }} عضو</p>
		</div>
	@endforeach
</div>
@endsection

@include('main')
