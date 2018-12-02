<nav class="navbar navbar-expand-md navbar-dark bg-dark">
	<a class="navbar-brand" href="#">
		<strong>RM</strong>
	</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarNav">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="nav-link" v-if="!isUserLoggedIn" @click.prevent="toggleRegisterForm">Register</a>
			</li>
		</ul>
		<ul class="navbar-nav">
			<li class="nav-item">
				{{--<router-link to="/login">Login</router-link>--}}
				<a class="nav-link" v-if="!isUserLoggedIn" @click.prevent="toggleLoginForm">Login</a>
			</li>
            <logout v-if="isUserLoggedIn" @logout-successul="onLogoutSuccessful"></logout>
		</ul>
	</div>
</nav>