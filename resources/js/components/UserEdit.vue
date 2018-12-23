<template>
	<v-form ref="form" v-if="user">
        <input class="mt-3 mb-3" v-if="this.isManager()" type="file" name="photo_url" @change="onFileSelected">

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

    import { requiredIf, minLength, sameAs } from 'vuelidate/lib/validators'
    import axios from 'axios'
    import {util} from '../mixin';
    import {toasts} from '../mixin';

    export default {
		props: ['user'],
		mixins: [toasts],
		data: () => ({
            userToEdit: this.user,
            hasNewPhoto: false,
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
        props: ['user'],
        mixins: [util, toasts],
        data() {
            return {
                userToEdit: this.user,
				hasNewPhoto: false,
                currentPassword: "",
                newPassword: "",
                repeatPassword: "",

                valid: true,
                showCurrentPassword: false,
                showNewPassword: false,
                showRepeatPassword: false,
                validateOnBlur: false,
                nameRules: [
                    v => /^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/.test(v) || 'Name is invalid'
                ],
                usernameRules: [
                    v => v.length <= 20 || 'Name must have less than 20 characters'
                ],
                newPasswordRules: [
                    v => (this.currentPassword.length === 0 && v.length === 0) ||
                        (this.currentPassword.length > 0 && v.length > 0) || 'This field is required'
                ],
                repeatPasswordRules: [
                    v => (this.newPassword.length === 0 && v.length === 0) ||
                        (this.newPassword.length > 0 && v.length > 0) || 'This field is required',
                    v => v === this.newPassword || 'Passwords don\'t match',
                ]
            }
        },
        // validations: {
        //     // name: {
        //     //     isNameValid
        // 	// },
        //     password: {
        //         required: requiredIf(function() {
        //             return this.currentPassword.length > 0
        //         }),
        //         minLength: minLength(3)
        //     },
        //     repeatPassword: {
        //         required: requiredIf(function() {
        //             return this.password.length > 0
        //         }),
        //         sameAsPassword: sameAs('password')
        //     }
        // },
        methods: {
            submit () {
                if (this.$refs.form.validate()) {
                    if(typeof this.userToEdit.photo_url  === 'string' || this.hasNewPhoto){
                        console.log('with photo');
                        let form = new FormData;
                        form.append('email', this.userToEdit.email);
                        form.append('name', this.userToEdit.name);
                        form.append('username', this.userToEdit.username);
                        form.append('newPassword', this.newPassword);
                        form.append('photo_url', this.userToEdit.photo_url);  /** HAS IMAGE ? -> ADD TO FORM**/

                        console.log(this.userToEdit.photo_url);
                        axios.post('users/update/'+this.userToEdit.id, form).then(response =>{
							this.showSuccessToast('User edited');
                            this.userToEdit = response.data.data;
                            this.$store.commit("setUser", this.userToEdit);
							//this.$store.state.user = response.data.data;
                            //this.$store.commit("setUser", this.userToEdit);
						}).catch(error => {
                            this.showErrorToast('Problem editing user');
						});


                    }else {
                        console.log('without photo');
                        axios.put(`/users/${this.userToEdit.id}`, this.userToEdit)
                            .then(response => {
                                if (response.status === 200) {
                                    this.userToEdit = response.data.data;
                                    this.showSuccessToast('User edited');
                                    // Object.assign(this.user, response.data.data);
                                    if (this.userToEdit.id === this.$store.state.user.id) {
                                        this.$store.commit("setUser", this.userToEdit);
                                    } else {
                                        this.$emit('onUpdateUserList');
                                    }
                                }
                            }).catch(error => {
                            this.showErrorToast('Problem editing user');
						});
                    }
                }
                this.clear();
            },
            onFileSelected(event){
                this.userToEdit.photo_url = event.target.files[0];
                this.hasNewPhoto = true;
            },
            close(){
                this.$emit('onClose');
            },
            clear(){
                this.hasNewPhoto = false;
                this.userToEdit.photo_url;
			},
        },
    }
</script>

<style scoped>	

</style>