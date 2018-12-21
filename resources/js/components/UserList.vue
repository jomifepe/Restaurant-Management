<template>
    <div>
        <v-toolbar flat color="white">
            <v-toolbar-title>Users List</v-toolbar-title>
            <v-divider class="mx-2" inset vertical> </v-divider>
            <v-spacer></v-spacer>
            <!--<v-dialog v-model="dialog" max-width="500px">
                <v-btn slot="activator" color="primary" dark class="mb-2">New User</v-btn>
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
                                <v-flex xs12 sm6 md4>
                                    <v-text-field v-model="editedItem.name" label="Full Name"></v-text-field>
                                </v-flex>
                                <v-flex xs12 sm6 md4>
                                    <v-text-field v-model="editedItem.username" label="Username"></v-text-field>
                                </v-flex>
                                <v-flex xs12 sm6 md4>
                                    <v-text-field v-model="editedItem.email" label="Email"></v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" flat @click="close">Cancel</v-btn>
                        <v-btn color="blue darken-1" flat @click="save">Save</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>-->
        </v-toolbar>
        <v-data-table :headers="headers" :items="users" :loading="loadingTableEffect" class="elevation-1">
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
        <UserEdit v-if="showEdit" :user="userToEdit"></UserEdit>
    </div>
</template>

<script>
    import {toasts} from '../mixin';
    import {util} from '../mixin';
    import UserEdit from "./UserEdit";
    export default {
        name: "UserList",
        components: {UserEdit},
        mixins: [toasts],
        data: () => ({
            loadingTableEffect: true,
            dialog: false,
            showEdit: false,
            userToEdit: null,
            headers: [
                { text: 'Photo', value: 'photo_url '},
                { text: 'Full Name', align: 'left', value: 'name'},
                { text: 'Username', value: 'username' },
                { text: 'Type', value: 'type' },
                { text: 'Email', value: 'email' },
                { text: 'Blocked', value: 'blocked'},
                { text: 'Action', value: ''}
            ],
            users: [],
            editedIndex: -1,
            editedItem: {
                name: '',
                username: '',
                email: '',
            },
            defaultItem: {
                name: '',
                username: '',
                email: '',
            }
        }),
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
                this.showEdit = true;
                this.dialog = true;
                this.userToEdit = iterationCopy(item);

                function iterationCopy(src) {
                    let target = {};
                    for (let prop in src) {
                        if (src.hasOwnProperty(prop)) {
                            target[prop] = src[prop];
                        }
                    }
                    return target;
                }

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
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1
                }, 300)
            },
            save () {
                if (this.editedIndex > -1) {
                    Object.assign(this.users[this.editedIndex], this.editedItem)
                } else {
                    this.users.push(this.editedItem)
                }
                this.close()
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