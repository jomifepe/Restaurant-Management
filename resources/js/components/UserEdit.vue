<template>
	<v-form ref="form" v-if="user">
		<v-text-field box name="email" ref="email" v-model="email" label="E-mail" prepend-icon="alternate_email" 
			:disabled="!isUserManager" :readonly="!isUserManager" v-validate="'required|email'"
			:rules="(!errors.first('email')) ? [true] : [errors.first('email')]"></v-text-field>

		<v-text-field box name="name" ref="name" v-model="name" label="Name" prepend-icon="fas fa-signature"
			v-validate="{required: true, regex:/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/}"
			:rules="(!errors.first('name')) ? [true] : [errors.first('name')]"></v-text-field>

		<v-text-field box name="username" ref="username" v-model="username" label="Username"
			v-validate="'required|max:20'" prepend-icon="person"
			:rules="(!errors.first('username')) ? [true] : [errors.first('username')]"></v-text-field>

		<v-checkbox label="Change current password" v-model="changePassword"></v-checkbox>

		<v-flex xs12 fluid  v-if="changePassword" class="white rounded mb-3" transition="scale-transition">
				<v-text-field box name="currentPassword" 
					ref="currentPassword"
					v-model="currentPassword" 
					label="Current password"
					prepend-icon="vpn_key"
					hint="The current password of this account"
					:append-icon="showCurrentPassword ? 'visibility_off' : 'visibility'"
					:type="showCurrentPassword ? 'text' : 'password'"
					@click:append="showCurrentPassword = !showCurrentPassword"
					v-validate="'required|max:20'"
					:rules="(!errors.first('currentPassword')) ? [true] : [errors.first('currentPassword')]">
				</v-text-field>

				<v-text-field box name="newPassword" 
					ref="newPassword"
					v-model="newPassword" label="New password" 
					prepend-icon="vpn_key"
					:append-icon="showNewPassword ? 'visibility_off' : 'visibility'" 
					:type="showNewPassword ? 'text' : 'password'"
					@click:append="showNewPassword = !showNewPassword" counter
					v-validate="'required|max:20'"
					:rules="(!errors.first('newPassword')) ? [true] : [errors.first('newPassword')]">
				</v-text-field>

				<v-text-field box name="repeatPassword" 
					ref="repeatPassword"
					v-model="repeatPassword" 
					label="Repeat password"
					prepend-icon="vpn_key"
					:append-icon="showRepeatPassword ? 'visibility_off' : 'visibility'"
					:type="showRepeatPassword ? 'text' : 'password'"
					@click:append="showRepeatPassword = !showRepeatPassword" counter
					v-validate="'required|max:20|confirmed:newPassword'"
					:rules="(!errors.first('repeatPassword')) ? [true] : [errors.first('repeatPassword')]">
				</v-text-field>
		</v-flex>
		
		<v-layout justify-end align-end>
			<v-btn :disabled="formErrors" color="primary" @click="submit" large>Submit</v-btn>
		</v-layout>
	</v-form>
</template>

<script type="text/javascript">
	import axios from 'axios'
	import {toasts} from '../mixin.js';

    export default {
		props: ['user'],
		mixins: [toasts],
		data: () => ({
			name: '',
			email: '',
			username: '',
			currentPassword: '',
			newPassword: '',
			repeatPassword: '',
			changePassword: false,
			showCurrentPassword: false,
			showNewPassword: false,
			showRepeatPassword: false
		}),
		computed: {
			isUserManager() {
				return this.user.type === 'manager';
			},
			formErrors() {
				if (this.errors.any() || 
					(this.changePassword && (!this.currentPassword.length ||
					!this.newPassword.length || !this.repeatPassword.length))) {
					return true;
				}
				return false;
			}
		},
		watch: {
			changePassword() {
				if (!this.changePassword) {
					this.resetPasswordForm();
				}
			}
		},
	    methods: {
			fillUser() {
				this.name = this.user.name;
				this.email = this.user.email;
				this.username = this.user.username;
			},
            submit() {
                this.$validator.validate().then((result) => {
					if (result) {
						this.submitUser().then(() => {
							this.showSuccessToast('Successfully updated user');
						});
					}
                });
			},
			submitUser() {
				return new Promise(resolve => {
					let user = this.user;
					user.name = this.name;
					user.username = this.username;
					if (this.isUserManager) {
						user.email = this.email;
					}
					if (this.changePassword) {
						user.password = this.newPassword;
						user.current_password = this.currentPassword;
					}

					axios.put(`/users/${user.id}`, user)
						.then(response => {
							if (response.status === 200) {
								this.$store.commit("setUser", user);
								resolve('success');
							} else {
								this.showErrorToast('Failed to update user');
							}
						})
						.catch(error => {
							if (error.response.data) {
								this.showErrorToast(error.response.data.message);
							} else {
								this.showErrorLog(`Failed to update user`, error);
							}
						})
				});
			},
			resetPasswordForm() {
				this.currentPassword = '';
				this.newPassword = '';
				this.repeatPassword = '';
			},
		},
		mounted() {
			this.fillUser();
		}
	}
</script>

<style scoped>	

</style>