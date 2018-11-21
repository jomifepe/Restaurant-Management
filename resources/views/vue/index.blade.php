@extends('master')

@section('title', 'Restaurant Management')
@section('content')

<div class="mt-5 mb-5">
	<login-user></login-user>
</div>

<div class="mt-5 mb-5">
	<menu-list></menu-list>
</div>

@endsection
@section('pagescript') 
<script src="js/general.js"></script>
<script src="https://unpkg.com/vuex"></script>
@stop
