@section('content')
<div class="container">
<div class="hero-unit">
	<h1>کتابخانه</h1>
	<p>برای مدیریت کتاب‌ها در مسجد، مدرسه یا محله شما</p>
</div>

<div class="row">
	<div class="span3">
		<h3>کتابداری</h3>
		<p>
			<ul>
				<li>چاپ لیست‌ها و برچسب‌ها</li>
				<li>محاسبه جریمه دیرکرد</li>
			</ul>
		</p>
	</div>
	<div class="span3">
		<h3>سایت کتابخانه</h3>
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

<p class="download">
	<a id="download" class="btn btn-large btn-primary" href="http://bayanbox.ir/id/7042369005653032383">دریافت «کتابخانه»</a>
	<a id="guide" class="btn btn-large" href="guide">آشنایی با «کتابخانه»</a>
</p>
<hr>

<h3>کتابخانه‌ها</h3>
<div class="libraries row">
	@foreach ($libraries as $library)
		<div class='span5'>
			<img class='tiny' src='{{ $library->image ? "files/". $library->image : "" }}'>
			<p class='title'><a href='{{ $library->slug }}'>{{ $library->title }}</a></p>
			<p class='subtitle'>{{ $library->description }}</p>
			<!-- <span class="books">{{ $library->books }} کتاب</span> -->
			<!-- <span class="users">{{ $library->users }} عضو</span> -->
		</div>
	@endforeach
</div>
</div>
@endsection

@include('main')
