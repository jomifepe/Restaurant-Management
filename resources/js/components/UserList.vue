<template>
    <div>
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
                        <img :src="props.item.photo_url" alt="Avatar">
                    </v-avatar>
                </td>
                <td class="text-xs-left">{{ props.item.name }}</td>
                <td class="text-xs-left">{{ props.item.username }}</td>
                <td class="text-xs-left">
                    <v-chip :color="typeColor(props.item)" text-color="white">
                        {{ props.item.type }}
                    </v-chip>
                </td>
                <td class="text-xs-left">{{ props.item.email }}</td>
                <td class="text-xs-left">{{ props.item.blockedStr }}</td>
                <td class="justify-center layout px-0" v-if="user.id !== props.item.id">

                    <div v-if="!props.item.blocked">
                        <v-icon large color="red darken-2" dark right @click.prevent="blockUser(props.item)">lock</v-icon>
                    </div>
                    <div v-else>
                        <v-icon large color="green darken-2" dark right @click.prevent="blockUser(props.item)">lock_open</v-icon>
                    </div>

                    <v-icon large color="yellow darken-2" @click.prevent="editItem(props.item)"> edit </v-icon>


                    <div v-if="props.item.deleted_at != null">
                        <v-icon large color="green darken-2" dark right @click.prevent="restoreUser(props.item)">undo</v-icon>
                    </div>
                    <div v-else>
                        <v-icon large color="red darken-2" @click.prevent="deleteItem(props.item)"> delete </v-icon>
                    </div>
                </td>
            </template>
            <template slot="no-data">
                <v-btn color="primary" @click="getUsers">Reset</v-btn>
            </template>
        </v-data-table>

        <v-dialog v-model="showEdit" v-if="showEdit">
            <v-card>
                <v-card-title>
                    <span class="headline">Edit User</span>
                </v-card-title>
                <v-card-text>
                    <v-avatar class="ma-1" slot="activator" size="100px" >
                        <img :src="this.userToEdit.photo_url" alt="Avatar">
                    </v-avatar>
                </v-card-text>
                <UserEdit class="ma-3" :user="Object.assign({}, this.userToEdit)" @onUpdateUserList="close(), getUsers()" @onClose="close()"></UserEdit>
            </v-card>
        </v-dialog>

    </div>
</template>

<script>
    import {toasts} from '../mixin';
    import UserEdit from './UserEdit';
    import Errors from './Errors';
    export default {
        name: "UserList",
        components: {
            UserEdit,
            Errors},
        mixins: [toasts],
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
                    {text: 'Photo', value: 'photo_url '},
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
                    console.log(response.data);
                    this.users = response.data.data;
                    this.loadingTableEffect = false;
                }).catch(error => {
                    this.noData = true;
                    this.loadingTableEffect=false;
                });
            },
            blockUser(user){
                axios.put('user/' + user.id).then(response => {
                    console.log(response.data);
                    this.showSuccessToast(`User ${response.data} successfuly`);
                    this.getUsers();
                }).catch(error => {
                    this.showErrorToast(`Problem ${response.data} user`);
                });
            },
            typeColor(item){
                switch(item.type) {
                    case 'manager': return 'red';
                        break;
                    case 'waiter': return 'primary';
                        break;
                    case 'cook': return 'orange';
                        break;
                    case 'cashier': return 'teal';
                        break;
                }
            },
            editItem (item) {
                this.userToEdit = item;
                console.log(this.userToEdit);
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
                if(this.editedItem.photo_url != null) {
                    form.append('photo_url', this.editedItem.photo_url);  /** HAS IMAGE ? -> ADD TO FORM**/
                }

                console.log(this.editedItem);
                axios.post('users', form).then(response => {
                    console.log(response.data.data);
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
            console.log(this.$route.name);
            this.getUsers()
        },
    }
</script>

<style scoped>

</style>


switch(1)

case 1:
case 0: