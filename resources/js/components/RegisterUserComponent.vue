<template>
	<div class="jumbotron mt-4 mb-4">
		<h2>Login</h2>
		<form @submit.prevent="handleSignUp">
			<div class="form-group">
				<label for="inputName">Name</label>
				<input v-model.trim="user.name" type="text" class="form-control" id="inputName"
					   aria-describedby="nameHelp" placeholder="Enter name">
			</div>
			<div class="form-group">
				<label for="inputUsername">Username</label>
				<input v-model.trim="user.username" type="text" class="form-control" id="inputUsername"
					   aria-describedby="usernameHelp" placeholder="Enter username">
			</div>
			<div class="form-group">
				<label for="inputEmail">Email address</label>
				<input v-model.trim="user.email" type="email" class="form-control" id="inputEmail"
					   aria-describedby="emailHelp" placeholder="Enter email">
			</div>
			<div class="form-group">
				<label for="inputPassword">Password</label>
				<input v-model="user.password" type="password" class="form-control" id="inputPassword" placeholder="Password">
			</div>
			<div class="form-group">
				<label v-model="confirmPassword" for="inputRetypePassword">Retype Password</label>
				<input type="password" class="form-control" id="inputRetypePassword" placeholder="Retype Password">
			</div>
			<div class="form-group">
				<label for="inputEmployeeType">Employee type</label>
				<select v-model="user.type" class="form-control" id="inputEmployeeType">
					<option selected>Waiter</option>
					<option>Cook</option>
					<option>Cashier</option>
					<option>Manager</option>
				</select>
			</div>
			<button type="submit" class="btn btn-primary">Register</button>
			<button type="submit" class="btn btn-secondary" @click.prevent="registerCancel">Cancel</button>
		</form>
	</div>
</template>

<script type="text/javascript">
    export default {
		data() {
            return {
                user: {
                    name: null,
                    username: null,
                    email: null,
                    password: null,
					type: null,
                    photo: null,
                },
                confirmPassword: null
            }
		},
        methods: {
            handleSignUp() {
			    if (this.user.password !== this.confirmPassword) {
			        console.log("Password don't match");
				}

                axios.post('register', this.user)
                    .then(response => {
                        console.log(response);
                    })
                    .catch(error => {
                        console.log(error);
                    })
			},
            registerCancel(){
                this.$emit("register-cancel");
			}
		},
        mounted() {
        }
    }
</script>
