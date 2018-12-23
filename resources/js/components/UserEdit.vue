<template>
	<v-form ref="form" v-model="valid" lazy-validation>
		<input class="mt-3 mb-3" v-if="this.isManager()" type="file" name="photo_url" @change="onFileSelected">
		<v-text-field solo v-model="userToEdit.email"
					  label="E-mail"
					  :validate-on-blur="validateOnBlur"
					  :clearable="false"
					  :disabled="!this.isManager()"
					  :readonly="!this.isManager()">
		</v-text-field>
		<v-text-field name="Name"
					  v-model="userToEdit.name"
					  :rules="nameRules"
					  :validate-on-blur="validateOnBlur"
					  label="Name"></v-text-field>
		<v-text-field name="username"
					  v-model="userToEdit.username"
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
		<v-card-actions>
			<v-btn :disabled="!valid" color="primary" @click="submit" large>Submit</v-btn>
			<v-btn color="blue darken-1" v-if="this.$route.name === 'users'" flat @click="close">Cancel</v-btn>
		</v-card-actions>
	</v-form>
</template>

<script type="text/javascript">

    import { requiredIf, minLength, sameAs } from 'vuelidate/lib/validators'
    import axios from 'axios'
    import {util} from '../mixin';
    import {toasts} from '../mixin';

    export default {
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