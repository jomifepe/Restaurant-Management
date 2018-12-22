<template>
		<v-form ref="form" v-model="valid" lazy-validation>
			<v-text-field solo v-model="currentUser.email"
						  label="E-mail"
						  :validate-on-blur="validateOnBlur"
						  :clearable="false"
						  this.disabled this.readonly></v-text-field>
			<v-text-field name="Name"
						  v-model="currentUser.name"
						  :rules="nameRules"
						  :validate-on-blur="validateOnBlur"
						  label="Name"></v-text-field>
			<v-text-field name="username"
						  v-model="currentUser.username"
						  :rules="usernameRules"
						  :validate-on-blur="validateOnBlur"
						  label="Username"></v-text-field>
			<v-text-field name="currentPassword"
						  v-model="currentPassword"
						  label="Current password"
						  :validate-on-blur="validateOnBlur"
						  :append-icon="showCurrentPassword ? 'visibility_off' : 'visibility'"
						  :type="showCurrentPassword ? 'text' : 'password'"
						  @click:append="showCurrentPassword = !showCurrentPassword"></v-text-field>
			<v-text-field name="newPassword"
						  v-model="newPassword"
						  :rules="newPasswordRules"
						  label="New password"
						  :validate-on-blur="validateOnBlur"
						  :append-icon="showNewPassword ? 'visibility_off' : 'visibility'"
						  :type="showNewPassword ? 'text' : 'password'"
						  @click:append="showNewPassword = !showNewPassword"
						  counter></v-text-field>
			<v-text-field name="repeatPassword"
						  v-model="repeatPassword"
						  :rules="repeatPasswordRules"
						  label="Repeat password"
						  :validate-on-blur="validateOnBlur"
						  :append-icon="showRepeatPassword ? 'visibility_off' : 'visibility'"
						  :type="showRepeatPassword ? 'text' : 'password'"
						  @click:append="showRepeatPassword = !showRepeatPassword"
						  counter></v-text-field>

			<v-btn :disabled="!valid" color="primary" @click="submit" large>Submit</v-btn>
		</v-form>
</template>

<script type="text/javascript">

    import { requiredIf, minLength, sameAs } from 'vuelidate/lib/validators'
    import axios from 'axios'

    export default {
		props: ['user'],
		data() {
            return {
                currentUser: this.user,
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
                    axios.put(`/users/${this.currentUser.id}`, this.currentUser)
                        .then(response => {
                            if (response.status === 200) {
                                this.currentUser = response.data.data;
                                // Object.assign(this.user, response.data.data);
								if(this.currentUser.id === this.$store.state.user.id) {
                                    this.$store.commit("setUser", this.currentUser);
                                }else{
									this.$emit('onUpdateUserList');
								}
                            }
                        });
                }
            }
        },
		computed:{
		    disabled(){
		        this.$store.state.user.type === 'manager' ? '':'disabled';
			},
            readonly(){
                this.$store.state.user.type === 'manager' ? '':'readonly';
			}
		}
	}
</script>

<style scoped>	

</style>