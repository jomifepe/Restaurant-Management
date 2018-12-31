<template>
    <v-app v-if="user">
        <v-navigation-drawer id="nav-menu" v-model="drawer" :mini-variant="mini" :clipped="clippedNavDrawer" hide-overlay
                stateless app width="250">
            <v-list class="nav-list-toolbar" dense>
                <v-list-tile v-if="mini" @click.stop="mini = !mini" title="Expand menu">
                    <v-list-tile-action>
                        <v-icon>chevron_right</v-icon>
                    </v-list-tile-action>
                </v-list-tile>

                <v-list-tile avatar tag="div">
                    <v-list-tile-avatar>
                        <img :src="user.photo_src" alt="Worker photo">
                    </v-list-tile-avatar>

                    <v-list-tile-content>
                        <v-list-tile-title>{{ userFirstAndLastName(user.name) }}</v-list-tile-title>
                    </v-list-tile-content>

                    <v-list-tile-action>
                        <v-btn icon @click.stop="mini = !mini">
                            <v-icon>chevron_left</v-icon>
                        </v-btn>
                    </v-list-tile-action>
                </v-list-tile>
            </v-list>

            <v-list class="pt-0" dense>
                <v-divider light class="pb-3"></v-divider>
                <v-list-tile v-for="item in menuItems" :key="item.title" :to="item.target"
                    v-if="item.visible === true ? true : item.visible.includes(user.type)">
                        <v-list-tile-action>
                            <v-icon>{{ item.icon }}</v-icon>
                        </v-list-tile-action>

                        <v-list-tile-content>
                            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                        </v-list-tile-content>
                </v-list-tile>
            </v-list>
        </v-navigation-drawer>
        <v-navigation-drawer id="nav-notifications" v-model="rightDrawer" :width="400" temporary right app>
            <v-toolbar flat>
                <v-toolbar-title>Notifications</v-toolbar-title>
                <v-dialog v-if="user.type !== 'manager'" v-model="dialog" width="500">
                    <v-btn icon slot="activator" title="Message all managers">
                        <v-icon>message</v-icon>
                    </v-btn>
                    <v-card>
                        <v-card-title class="headline grey lighten-2" xs12 primary-title>
                            New message to all managers
                        </v-card-title>
                        <v-card-text>
                            <v-flex xs12>
                                <v-form ref="form" v-model="msgFormValid">
                                    <v-text-field v-model="titleNotManager" single-line name="msg-title"
                                        ref="msg-title" label="Description/Subject" maxlength="20" counter="20"
                                        :rules="[v => !!v || 'This field is required', v => v.length <= 25 || 'Max 20 characters']">
                                    </v-text-field>
                                    <v-textarea class="mt-3" v-model="textNotManager" box auto-grow
                                        name="msg-content" ref="msg-content" label="Message" maxlength="200"
                                        counter="200" :rules="[v => !!v || 'This field is required', v => v.length <= 200 || 'Max 200 characters']">
                                    </v-textarea>
                                </v-form>
                            </v-flex>
                        </v-card-text>
                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" flat @click="cancelDialog()">Cancel</v-btn>
                            <v-btn color="primary" :disabled="!msgFormValid" flat
                                @click="sendNotificationToManagers()">Send</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-toolbar>
            <v-divider></v-divider>
            <v-card class="elevation-0">
                <v-list three-line>
                    <template v-for="(item, index) in notifications">
                        <v-flex xs12 :key="index">
                            <v-list-tile @click="redirectToItemLocation(item.forward)">
                                <v-list-tile-content>
                                    <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                                    <v-list-tile-sub-title>{{ item.text }}</v-list-tile-sub-title>
                                </v-list-tile-content>

                                <v-list-tile-action>
                                    <v-list-tile-action-text>{{ item.action }}</v-list-tile-action-text>
                                </v-list-tile-action>

                                <v-btn @click="dismissNotification(index)" icon>
                                    <v-icon>close</v-icon>
                                </v-btn>
                            </v-list-tile>
                            <v-divider v-if="index + 1 < notifications.length"></v-divider>
                        </v-flex>
                    </template>
                </v-list>
            </v-card>
        </v-navigation-drawer>
        <v-toolbar id="toolbar-main" :clipped-left="clippedToolbar" color="blue-grey darken-1" dark dense app>
            <v-toolbar-side-icon @click.stop="drawer = !drawer" title="Toggle menu"></v-toolbar-side-icon>
            <v-toolbar-title>
                {{ this.$store.state.panelTitle }}
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-progress-circular v-if="$store.state.progressBarShown" color="white"
                class="mr-3" :value="$store.state.progressBarValue" 
                :indeterminate="$store.state.progressBarIndeterminate"></v-progress-circular>

            <v-toolbar-items>
                <v-menu :left="true" :nudge-width="100">
                    <v-toolbar-title slot="activator">
                        <span>{{ userFirstName(user.name) }}</span>
                        <v-icon>arrow_drop_down</v-icon>
                    </v-toolbar-title>
                    <v-list>
                        <v-list-tile @click="performControledLogout">
                            <v-list-tile-action>
                                <v-icon>exit_to_app</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>Logout</v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                    </v-list>
                </v-menu>
            </v-toolbar-items>
             <v-badge right color="red">
                <span v-if="notifications.length > 0" slot="badge">{{ notifications.length }}</span>
                <v-icon class="ml-2" @click.stop="toggleNotificationDrawer" title="Toggle right menu">fas fa-bell</v-icon>
             </v-badge>
        </v-toolbar>
        <v-dialog v-model="logoutEndShiftDialog" max-width="350">
            <v-card>
                <v-card-text class="subheading">
                    Your shift is currently active, do you want to end it before logging out?
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn flat @click="logoutEndShiftDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn flat color="teal" @click="logout">
                        No
                    </v-btn>
                    <v-btn flat color="red" @click="logoutEndShiftDialog = false; loggedOut = true">
                        Yes
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-content>
            <v-container fluid>
                <v-flex xs12>
                    <router-view></router-view>
                </v-flex>
            </v-container>
        </v-content>
        <v-footer height="auto" app inset>
            <WorkerInfo :user="user" @onClearNotifications="clearNotifications" 
                :logOutFlag="loggedOut" @onShiftEndLogout="logout"></WorkerInfo>
        </v-footer>
    </v-app>
</template>

<script>
    import WorkerInfo from "./WorkerInfo";
    import axios from "axios";
    import moment from 'moment';
    import {toasts, helper} from '../../mixin.js';

    export default {
        name: "AdminNavigation",
        mixins: [toasts, helper],
        components: {
            WorkerInfo
        },
        data: () => ({
            notifications: [],
            dialog: false,
            titleNotManager: '',
            textNotManager: '',
            clippedNavDrawer: false,
            clippedToolbar: false,
            drawer: true,
            rightDrawer: false,
            msgFormValid: true,
            menuItems: [
                {
                    title: 'Dashboard',
                    icon: 'dashboard',
                    target: '/admin/dashboard',
                    visible: true
                },
                {
                    title: 'Profile',
                    icon: 'person',
                    target: '/admin/profile',
                    visible: true
                },
                {
                    title: 'Users',
                    icon: 'people',
                    target: '/admin/users',
                    visible: ['manager']
                },
                {
                    title: 'Meals',
                    icon: 'restaurant',
                    target: '/admin/meals',
                    visible: ['waiter', 'manager']
                },
                {
                    title: 'Menu',
                    icon: 'fastfood',
                    target: '/admin/menu',
                    visible: true
                },
                {
                    title: 'Tables',
                    icon: 'fas fa-chair',
                    target: '/admin/tables',
                    visible: ['manager']
                },
                {
                    title: 'Orders',
                    icon: 'event_note',
                    target: '/admin/orders',
                    visible: ['cook', 'manager']
                },
                {
                    title: "Invoices",
                    icon: "monetization_on",
                    target: "/admin/invoices",
                    visible: ['cashier', 'manager']
                },
            ],
            mini: true,
            right: null,
            loggedOut: false,
            logoutEndShiftDialog: false
        }),
        sockets: {
            connect(){
                console.log('socket connected (socket ID = '+ this.$socket.id +')');
            },
            confirmation_sent_to_manager(data){
                this.showTopRightSuccessToast('Message sent', 1000);
            },
            /* everyone */
            user_enter(data) {
                this.showTopRightToast(data, 'arrow_forward', 2000);
            },
            user_exit(data) {
                this.showTopRightToast(data, 'arrow_back', 2000);
            },
            /* waiters */
            order_prepared_waiter(order) {
                this.addNotification(`Order ${order.id} is prepared`,
                    ` ${order.item_name} from table ${order.meal_table_number} is ready for pick up`,
                    `/admin/meals/${order.meal_id}/orders`);
            },
            /* cooks */
            order_prepared_cook(data) {
                this.addNotification("Order send confirmation", data, false);
            },
            order_received() {
                this.addNotification("New Order", "New order arrived!", "/admin/orders");
            },
            /* cashiers */
            pending_invoice_received(){
                this.addNotification("New Invoice", "New invoice generated", "/admin/invoices");
            },
            /* managers */
            message_from_worker(data){
                this.showTopRightInfoToast('Your got a new message from a worker', 2000);
                this.addNotification(
                    `Message from ${data.sender.type}`,
                    `${this.userFirstAndLastName(data.sender.name)} says:\n ${data.text}`, false);
            },
        },
        computed: {
            user() {
                return this.$store.state.user;
            }
        },
        methods: {
            cancelDialog(){
                this.dialog = false;
                this.titleNotManager='';
                this.textNotManager='';

            },
            sendNotificationToManagers() {
                if (this.$refs.form.validate()) {
                    let content = {
                        'sender': this.user,
                        'title': this.titleNotManager,
                        'text': this.textNotManager
                    }

                    this.$socket.emit('to_all_managers', content);
                    this.titleNotManager='';
                    this.textNotManager='';
                    this.rightDrawer = false;
                }
            },
            clearNotifications() {
                this.notifications = [];
            },
            dismissNotification(index) {
                this.notifications.splice(index, 1);
            },
            addNotification(title, data, forward = true) {
                let object = {
                    title: title,
                    text: data,
                    action: moment().format("HH:mm"),
                    forward: forward
                };
                this.notifications.push(object);
            },
            logout() {
                axios.post('logout')
                    .then(response => {
                        if (response.status === 200) {
                            this.$store.commit('clearUserAndToken');
                            this.$router.push("/");
                        }
                    })
                    .catch(error => {
                        this.$store.commit('clearUserAndToken');
                        console.log(`Failed to logout, But local credentials were discarded: \n${error}`);
                        this.$router.push("/");
                    })
            },
            performControledLogout() {
                if (!!this.user.shift_active) {
                    this.logoutEndShiftDialog = true;
                } else {
                    this.logout();
                }
            },
            toggleNotificationDrawer() {
                if (!!this.user.shift_active) {
                    this.rightDrawer = !this.rightDrawer;
                }
            },
            redirectToItemLocation(notificationRoute) {
                if (notificationRoute) {
                    this.$router.push(notificationRoute)
                }
            }
        }
    }
</script>

<style scoped>
    #nav-notifications {
        z-index: 60;
    }
    #nav-menu {
        z-index: 50;
    }
    #toolbar-main {
        z-index: 40;
    }
    .nav-list-toolbar {
        padding-top: 0;
        padding-bottom: 0;
    }
    .v-divider {
        margin: 0;
    }
</style>