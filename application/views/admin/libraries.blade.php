@section('content')
<h2>کتاب‌خانه‌ها</h2>
@foreach ($libraries as $library)
	<form method='post'>
		<p>{{ $library->title }}</p>
		<input type='hidden' name='library_id' value='{{ $library->id }}'>
		<input type='text' class='eng' name='slug' placeholder='slug' value='{{ $library->slug }}'>
		<input type='password' class='eng' name='key' placeholder='key'>
		<input type='submit' class="btn" value='ارسال'>
	</form>
@endforeach
@endsection

@include('main')


