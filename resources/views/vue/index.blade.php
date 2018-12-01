@extends('master')

@section('title', 'Restaurant Management')
@section('content')

<div class="alert mt-4" :class="alertClass" v-if="showMessage">
	<button type="button" class="close" aria-label="Close" @click="closeAlertMessage">
        <span aria-hidden="true">&times;</span>
    </button>
	<strong>@{{ alertMessage }}</strong>
</div>

<navigation v-if="isUserLoggedIn"></navigation>

<div class="mt-4 mb-4">
	<login v-if="showLoginForm"
		   @login-successful="onLoginSuccessful"
           @login-failed="onLoginFailed"></login>
</div>

<div class="mt-4 mb-4">
	<register v-if="showRegisterForm" @register-cancel="onHideRegisterForm"></register>
	<menu-list></menu-list>
</div>

@endsection
@section('pagescript') 
<script src="js/general.js"></script>
<script src="https://unpkg.com/vuex"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
@stop
