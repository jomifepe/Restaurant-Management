<template>
    <v-container>
		<v-layout>
			<v-flex xs12>
                <v-toolbar flat color="white">
                    <v-toolbar-title>Users List</v-toolbar-title>
                    <v-divider class="mx-2" inset vertical> </v-divider>
                    <v-spacer></v-spacer>

                    <v-btn slot="activator" color="primary" dark class="mb-2">New User</v-btn>

                    <v-text-field v-model="filter" class="mx-3 rounded-text-field" flat small
                                label="Search" prepend-inner-icon="search" solo-inverted>
                    </v-text-field>



                    <v-dialog v-model="dialog" max-width="500px">
                        <v-btn slot="activator" color="primary" dark class="mb-2">New User</v-btn>
                        <v-card>
                            <v-card-title>
                                <span class="headline">{{ formTitle }}</span>
                            </v-card-title>
                            <form id="form" @submit.prevent="validateBeforeSubmit">
                                <v-card-text>
                                    <div v-if="hasValidationErrors" v-for="(value, key, index) in validationErrors">
                                        <errors :msg="value"></errors>
                                    </div>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field v-model="editedItem.name" v-validate="'required|alpha_spaces'" name="name" type="text" label="Full Name"></v-text-field>
                                                <span style="color:red">{{ errors.first('name') }}</span>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field v-model="editedItem.username" v-validate="'required|alpha_spaces'" name="username" type="text" label="Username"></v-text-field>
                                                <span style="color:red">{{ errors.first('username') }}</span>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field v-model="editedItem.email" v-validate="'required|email'" name="email" type="text" label="Email"></v-text-field>
                                                <span style="color:red">{{ errors.first('email') }}</span>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-select v-model="editedItem.type" v-validate="'required'" name="type" type="text"
                                                        :items="['manager', 'cook', 'cashier', 'waiter']"
                                                        label="Type"
                                                        required>
                                                </v-select>
                                                <span style="color:red">{{ errors.first('type') }}</span>
                                            </v-flex>

                                            <v-flex xs12 sm6 md4>
                                                <v-text-field v-model="editedItem.password" v-validate="'required'" name="password" type="password"  label="Password"></v-text-field>
                                                <span style="color:red">{{ errors.first('password') }}</span>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <input type="file" name="photo_url" @change="onFileSelected">
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn>
                                    <v-btn color="blue darken-1" flat type="submit" >Save</v-btn>
                                </v-card-actions>
                            </form>
                        </v-card>
                    </v-dialog>

                </v-toolbar>
                <v-data-table :headers="headers" :items="users" :loading="loadingTableEffect" class="elevation-1" :search="filter">
                    <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                    <template slot="items" slot-scope="props">
                        <td class="text-xs-left">
                            <v-avatar class="ma-1"  slot="activator" size="100px" >
                                <img :src="props.item.photo_src" alt="Avatar">
                            </v-avatar>
                        </td>
                        <td class="text-xs-left">{{ props.item.name }}</td>
                        <td class="text-xs-left">{{ props.item.username }}</td>
                        <td class="text-xs-left">
                            <v-chip :color="getUserAppearence(props.item).color" text-color="white">
                                {{ props.item.type }}
                            </v-chip>
                        </td>
                        <td class="text-xs-left">{{ props.item.email }}</td>
                        <td class="text-xs-left">{{ props.item.blockedStr }}</td>
                        <td class="justify-center px-0 text-md-center dt-actions" v-if="user.id !== props.item.id">
                            <v-tooltip top v-if="!props.item.blocked">
                                <v-btn icon slot="activator" @click.prevent="blockUser(props.item)">
                                    <v-icon color="red darken-2" dark>lock</v-icon>
                                </v-btn>
                                <span>Block user</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-btn icon slot="activator" @click.prevent="blockUser(props.item)">
                                    <v-icon color="green darken-2" dark>lock_open</v-icon>
                                </v-btn>
                                <span>Unblock user</span>
                            </v-tooltip>
                            <v-tooltip top>
                                <v-btn icon slot="activator" @click.prevent="editItem(props.item)">
                                    <v-icon color="yellow darken-2">edit</v-icon>
                                </v-btn>
                                <span>Edit user</span>
                            </v-tooltip>
                            <v-tooltip top v-if="props.item.deleted_at != null">
                                <v-btn icon slot="activator" @click.prevent="restoreUser(props.item)">
                                    <v-icon color="green darken-2" dark>undo</v-icon>
                                </v-btn>
                                <span>Restore user</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-btn icon slot="activator" @click.prevent="deleteItem(props.item)">
                                    <v-icon color="red darken-2">delete</v-icon>
                                </v-btn>
                                <span>Delete user</span>
                            </v-tooltip>
                        </td>
                    </template>
                    <template slot="no-data">
                        <v-btn color="primary" @click="getUsers">Reset</v-btn>
                    </template>
                </v-data-table>

                <v-dialog v-model="showEdit" v-if="showEdit" :width="800">
                    <v-card class="pb-4" >
                        <v-card-title>
                            <span class="headline">Edit User</span>
                        </v-card-title>
                        <v-card-text>
                            <v-avatar class="ma-1" slot="activator" size="100px" >
                                <img :src="this.userToEdit.photo_src" alt="Avatar">
                            </v-avatar>
                        </v-card-text>
                        <UserEdit class="ml-4 mr-4" :user="Object.assign({}, this.userToEdit)" @onUpdateUserList="close(), getUsers()" @onClose="close()"></UserEdit>
                    </v-card>
                </v-dialog>
			</v-flex>
		</v-layout>
    </v-container>
</template>

<script>
    import {toasts, helper} from '../mixin';
    import UserEdit from './UserEdit';
    import Errors from './Errors';
    export default {
        name: "UserList",
        components: { 
            UserEdit, 
            Errors
        },
        mixins: [toasts, helper],
        data: function() {
            return {
                loadingTableEffect: true,
                dialog: false,
                showEdit: false,
                filter: '',
                hasValidationErrors: false,
                validationErrors: [],
                userToEdit: null,
                headers: [
                    {text: 'Photo', value: 'photo_src '},
                    {text: 'Full Name', align: 'left', value: 'name'},
                    {text: 'Username', value: 'username'},
                    {text: 'Type', value: 'type'},
                    {text: 'Email', value: 'email'},
                    {text: 'Blocked', value: 'blocked'},
                    {text: 'Action', value: ''}
                ],
                users: [],
                editedIndex: -1,
                editedItem: {
                    name: '',
                    username: '',
                    email: '',
                    type: '',
                    photo_url: null,

                },
                defaultItem: {
                    name: '',
                    username: '',
                    email: '',
                }
            }
        },
        methods: {
            getUsers () {
                this.loadingTableEffect = true;
                axios.get('users/all').then(response => {
                    this.users = response.data.data;
                    this.loadingTableEffect = false;
                }).catch(error => {
                    this.noData = true;
                    this.loadingTableEffect=false;
                });
            },
            blockUser(user){
                axios.put('user/' + user.id).then(response => {
                    this.showSuccessToast(`User ${response.data} successfuly`);
                    this.getUsers();
                }).catch(error => {
                    this.showErrorToast(`Problem ${response.data} user`);
                });
            },
            editItem (item) {
                this.userToEdit = item;
                this.showEdit = true;

            },
            deleteItem (item) {
                axios.delete('users/' + item.id).then(response => {
                    this.showSuccessToast('User deleted successfuly');
                    this.getUsers();
                }).catch(error =>{
                    this.showErrorToast('Problem deleting user');
                });
            },
            restoreUser(item){
                axios.put('user/restore/' + item.id).then(response => {
                    this.showSuccessToast('User restored successfuly');
                    this.getUsers();
                }).catch(error =>{
                    this.showErrorToast('Problem restoring user');
                });
            },
            close () {
                this.dialog = false;
                this.showEdit = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1
                }, 300)
            },
            save () {
                let form = new FormData;
                form.append('name', this.editedItem.name);
                form.append('username', this.editedItem.username);
                form.append('email', this.editedItem.email);
                form.append('password', this.editedItem.password);
                form.append('type', this.editedItem.type);
                if (this.editedItem.photo_url != null) {
                    form.append('photo_url', this.editedItem.photo_url);  /** HAS IMAGE ? -> ADD TO FORM**/
                }

                axios.post('users', form).then(response => {
                    this.getUsers();
                    this.close()
                }).catch(error => {
                    this.hasErrors(error.response.data.errors);
                });
            },
            onFileSelected(event){
                this.editedItem.photo_url = event.target.files[0];
            },
            hasErrors(errors){
                this.validationErrors = errors;
                this.hasValidationErrors = true;
                setTimeout(() => (this.hasValidationErrors = false), 6000)
            },
            validateBeforeSubmit() {
                this.$validator.validateAll().then((result) => {
                    if (!result) {
                        alert('Correct the errors!');
                    }else{
                        this.save();
                        return;
                    }
                });
                this.close();
            },
        },
        computed: {
            formTitle () {
                return this.editedIndex === -1 ? 'New User' : 'Edit User'
            },
            user(){
                return this.$store.state.user;
            },
        },

        watch: {
            dialog (val) {
                val || this.close()
            }
        },

        created () {
            this.getUsers()
        },
    }
</script>

<style scoped>

</style>


switch(1)

case 1:
case 0: