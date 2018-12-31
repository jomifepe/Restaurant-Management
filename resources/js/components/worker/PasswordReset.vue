<template>
	<v-app>
		<v-content>
		<v-container fluid fill-height>
			<v-layout align-center justify-center>
			<v-flex xs12 sm8 md8>
				<v-card class="elevation-12">
				<v-toolbar dark color="blue-grey">
					<v-toolbar-title>Password reset</v-toolbar-title>
					<v-spacer></v-spacer>
				</v-toolbar>
				<v-card-text>
					<v-form ref="form" v-model="valid">
						<v-text-field v-model="email" ref="email" prepend-icon="fas fa-at" 
							name="email" label="Email" id="email" 
							type="text" v-validate="'required|email'"
							:rules="(!errors.first('email')) ? [true] : [errors.first('email')]">
						</v-text-field>
						<v-text-field v-model="newPassword" ref="newPassword" prepend-icon="fas fa-lock" 
							name="newPassword" label="New password" id="newPassword" v-validate="'required|min:6|max:20'"
							:append-icon="showNewPassword ? 'visibility_off' : 'visibility'"
							:type="showNewPassword ? 'text' : 'password'"
							@click:append="showNewPassword = !showNewPassword" counter
							:rules="(!errors.first('newPassword')) ? [true] : [errors.first('newPassword')]">
						</v-text-field>
						<v-text-field v-model="passwordRepeat" ref="passwordRepeat" prepend-icon="fas fa-lock"
							name="passwordRepeat" label="Repeat password" id="passwordRepeat" 
							v-validate="'required|max:20|confirmed:newPassword'"
							:append-icon="showPasswordRepeat ? 'visibility_off' : 'visibility'"
							:type="showPasswordRepeat ? 'text' : 'password'"
							@click:append="showPasswordRepeat = !showPasswordRepeat" counter
							:rules="(!errors.first('passwordRepeat')) ? [true] : [errors.first('passwordRepeat')]">
						</v-text-field>
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn @click="clear">Clear</v-btn>
					<v-btn color="primary" :disabled="!valid" @click="submit">Submit</v-btn>
				</v-card-actions>
				</v-card>
			</v-flex>
			</v-layout>
		</v-container>
		</v-content>
	</v-app>
</template>

<script>
	export default {
		name: "PasswordReset",
		data: () => ({
			valid: true,
			token: null,
			email: '',
			newPassword: '',
			passwordRepeat: '',
			showNewPassword: false,
			showPasswordRepeat: false
		}),
		methods: {
			submit() {
				if (this.$refs.form.validate() && this.token.length) {
					this.confirmEmail().then(() => this.resetPassword())
				}
			},
			confirmEmail() {
				return new Promise(resolve => {
					axios.get(`/api/users/email/${this.email}`)
					.then(response => {
						if (response.status === 200) {
							resolve();
						} else if (response.status === 404) {
							console.log(response);
							this.$toasted.error('Couldn\'t find a user with the provided email', { 
								icon: 'error', 
								position: 'bottom-center', 
								duration : 3000
							});
						}
					})
					.catch(error => {
						this.$toasted.error('Couldn\'t find a user with the provided email', { 
							icon: 'error', 
							position: 'bottom-center', 
							duration : 3000
						});
					})
				})
			},
			resetPassword() {
				let form = new FormData();
				form.append('token', this.token);
				form.append('email', this.email);
				form.append('password', this.newPassword);
				form.append('password_confirmation', this.passwordRepeat);

				axios.post('/password/reset', form)
					.then(response => {
						console.log(response);
						if (response.status === 200) {
							this.$toasted.success('Password successfully changed', { 
								icon: 'check_circle', 
								position: 'bottom-center', 
								duration : 2000,
								onComplete: () => window.location.href = '/#/login',
								action: {
									text: 'Ok',
									onClick : (e, toastObject) => {
										window.location.href = '/#/login';
									}
								}
							});
						} else {
							this.$toasted.error('Failed to change password, your email may be incorrect', { 
								icon: 'error', 
								position: 'bottom-center', 
								duration : 3000
							});
						}
					})
					.catch(error => {
						this.$toasted.error('Failed to change password, your email may be incorrect', { 
							icon: 'error', 
							position: 'bottom-center', 
							duration : 3000
						});
						console.log(error);
					})
			},
			clear() {
				this.$refs.form.reset();
			},
			loadTokenFromURL(url) {
				this.token = url.substr(url.lastIndexOf('/') + 1);
			}
		},
		created() {
			this.loadTokenFromURL(window.location.pathname);
		}
	}
</script>

<style scoped>

</style>