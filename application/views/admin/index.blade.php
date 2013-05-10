@section('content')
<form method="post">
	<h2>مدیریت</h2>
	<p>گذرواژه:
		<input type="password" class="eng" name="key" />
	</p>
	<p><input type="submit" name="submit" class="btn" value="تایید" /></p>
</form>
@endsection

@include('main')
