<template>
	<v-form ref="form" v-if="userToEdit">
        <input class="mt-3 mb-3" v-if="this.isManager()" type="file" name="photo_url" @change="onFileSelected">

        <v-text-field box name="email" ref="email" v-model="email" label="E-mail" prepend-icon="alternate_email"
			:disabled="!this.isManager()" :readonly="!this.isManager()" v-validate="'required|email'"
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
			<v-btn color="primary" @click="close()" large>Cancel</v-btn>
		</v-layout>
	</v-form>
</template>

<script type="text/javascript">

    import axios from 'axios'
    import {util} from '../mixin';
    import {toasts} from '../mixin';

    export default {
		props:['user'],
		mixins: [util, toasts],
		data: () => ({
            userToEdit: null,
            hasNewPhoto: false,
			name: '',
			email: '',
			username: '',
			photo: null,
			currentPassword: '',
			newPassword: '',
			repeatPassword: '',
			changePassword: false,
			showCurrentPassword: false,
			showNewPassword: false,
			showRepeatPassword: false
		}),

	    methods: {
            fillUser() {
                this.name = this.userToEdit.name;
                this.email = this.userToEdit.email;
                this.username = this.userToEdit.username;
            },
            submit () {
                if (this.$refs.form.validate()) {

                    let user = this.userToEdit;
                    user.name = this.name;
                    user.username = this.username;
                    if (this.isManager()) {
                        user.email = this.email;
                    }
                    if (this.changePassword) {
                        user.password = this.newPassword;
                        user.current_password = this.currentPassword;
                    }


                    if(this.hasNewPhoto){
                        console.log('with photo');
                        let form = new FormData;
                        if (this.isManager()) {
                            form.append('email', user.email);
                        }
                        form.append('name', user.name);
                        form.append('username', user.username);

                        if (this.changePassword) {
                            form.append('password', user.password);
                            form.append('current_password', user.current_password);
                        }
                        form.append('photo_url', this.photo);  /** HAS IMAGE ? -> ADD TO FORM**/


                        axios.post('users/update/'+user.id, form).then(response =>{
                            this.userToEdit = response.data.data;
                            console.log(this.userToEdit);
                            if (this.userToEdit.id === this.$store.state.user.id) {
                                this.$store.commit("setUser", this.userToEdit);
                            }
                            this.showSuccessToast('User edited');
                            this.$emit('onUpdateUserList');
                        }).catch(error => {
                            if (error.response.data) {
                                this.showErrorToast(error.response.data.message);
                            } else {
                                this.showErrorLog(`Failed to update user`, error);
                            }
                        });
                    }else {
                        console.log('without photo');
                        axios.put(`/users/${user.id}`, user)
                            .then(response => {
                                if (response.status === 200) {
                                    this.userToEdit = response.data.data;
                                    this.showSuccessToast('User edited');
                                    if (this.userToEdit.id === this.$store.state.user.id) {
                                        this.$store.commit("setUser", this.userToEdit);
                                    }
                                }
                                this.$emit('onUpdateUserList');
                            }).catch(error => {
                            if (error.response.data) {
                                this.showErrorToast(error.response.data.message);
                            } else {
                                this.showErrorLog(`Failed to update user`, error);
                            }
                        });
                    }
                }
                this.clear();
            },
            onFileSelected(event){
                this.photo = event.target.files[0];
                this.hasNewPhoto = true;
            },
            close(){
                this.$emit('onClose');
            },
            clear(){
                this.hasNewPhoto = false;
				this.photo = null;
            },
            resetPasswordForm() {
                this.currentPassword = '';
                this.newPassword = '';
                this.repeatPassword = '';
            },
        },
		mounted() {
		    console.log('Mounted');
		    this.userToEdit = Object.assign({}, this.user);
		    console.log(this.userToEdit);
			this.fillUser();
		},
        computed: {
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
	}
</script>

<style scoped>	

</style>