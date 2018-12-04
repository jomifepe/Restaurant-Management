@extends('master')
@section('title', 'Restaurant Management')
@section('content')

<alert-message :classtype="alertClass"
               :show="showMessage"
               :message="alertMessage"
               @close-message="onCloseAlertMessage"></alert-message>

<navigation v-if="isUserLoggedIn"></navigation>
<login-form v-if="showLoginForm"
            @login-successful="onLoginSuccessful"
            @login-failed="onLoginFailed"></login-form>
<register v-if="showRegisterForm"
          @register-successful="onRegisterSuccessful"
          @register-failed="onRegisterFailed"
          @register-cancel="onHideRegisterForm"></register>

<router-view></router-view>


@endsection
@section('pagescript') 
<script src="js/general.js"></script>
<script src="https://unpkg.com/vuex"></script>
<script src="https://cdn.jsdelivr.net/vuejs-paginator/2.0.0/vuejs-paginator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vuejs-paginator/2.0.0/vuejs-paginator.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
@stop
