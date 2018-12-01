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
	<menu-component></menu-component>
</div>

@endsection
@section('pagescript') 
<script src="js/general.js"></script>
<script src="https://unpkg.com/vuex"></script>
<script src="https://cdn.jsdelivr.net/vuejs-paginator/2.0.0/vuejs-paginator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vuejs-paginator/2.0.0/vuejs-paginator.js"></script>
@stop
