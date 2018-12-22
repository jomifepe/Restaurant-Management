<template>
    <v-app v-if="user">
        <v-navigation-drawer v-model="drawer" :mini-variant="mini" :clipped="clippedNavDrawer" hide-overlay
                stateless app style="z-index: 50">
            <v-list class="pa-1">
                <v-list-tile v-if="mini" @click.stop="mini = !mini" title="Expand menu">
                    <v-list-tile-action>
                        <v-icon>chevron_right</v-icon>
                    </v-list-tile-action>
                </v-list-tile>

                <v-list-tile avatar tag="div">
                    <v-list-tile-avatar>
                        <img :src="user.photo_url" alt="Worker photo">
                    </v-list-tile-avatar>

                    <v-list-tile-content>
                        <v-list-tile-title>{{ userFirstAndLastName(user) }}</v-list-tile-title>
                    </v-list-tile-content>

                    <v-list-tile-action>
                        <v-btn icon @click.stop="mini = !mini">
                            <v-icon>chevron_left</v-icon>
                        </v-btn>
                    </v-list-tile-action>
                </v-list-tile>
            </v-list>

            <v-list class="pt-0" dense>
                <v-divider light></v-divider>
                <v-list-tile v-for="item in items" :key="item.title" :to="item.target"
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
        <v-navigation-drawer v-model="rightDrawer" temporary right app style="z-index: 50">
            <v-toolbar flat>
                <v-list>
                    <v-list-tile>
                        <v-list-tile-title class="title">
                            Notifications
                        </v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-toolbar>
            <v-divider></v-divider>
        </v-navigation-drawer>
        <v-toolbar :clipped-left="clippedToolbar" color="blue-grey darken-1" dark dense app style="z-index: 40">
            <v-toolbar-side-icon @click.stop="drawer = !drawer" title="Toggle menu"></v-toolbar-side-icon>
            <v-toolbar-title>
                {{ this.$store.state.panelTitle }}
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-progress-circular v-if="$store.state.progressBarShown" color="white"
                class="mr-3" :value="$store.state.progressBarValue" 
                :indeterminate="$store.state.progressBarIndeterminate"></v-progress-circular>

            <v-toolbar-items class="hidden-sm-and-down">
                <v-menu :left="true" :nudge-width="100">
                    <v-toolbar-title slot="activator">
                        <span>{{ userFirstName(user) }}</span>
                        <v-icon>arrow_drop_down</v-icon>
                    </v-toolbar-title>
                    <v-list>
                        <v-list-tile @click="logout">
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
            <v-toolbar-side-icon @click.stop="rightDrawer = !rightDrawer" title="Toggle right menu"></v-toolbar-side-icon>
        </v-toolbar>
        <v-content>
            <v-container fluid>
                <v-flex xs12>
                    <router-view></router-view>
                </v-flex>
            </v-container>
        </v-content>
        <v-footer height="auto" app>
            <WorkerInfo :user="user"></WorkerInfo>
        </v-footer>
    </v-app>
</template>

<script>
    import WorkerInfo from './WorkerInfo';
    import axios from 'axios';
    import {helper} from '../mixin.js';

    export default {
        name: "AdminNavigation",
        mixins: [helper],
        components: {
            WorkerInfo
        },
        data: () => ({
            clippedNavDrawer: false,
            clippedToolbar: false,
            drawer: true,
            rightDrawer: false,
            items: [
                {
                    title: 'Home',
                    icon: 'dashboard',
                    target: '/admin',
                    visible: true },
                {
                    title: 'Profile',
                    icon: 'person',
                    target: '/admin/profile',
                    visible: true },
                {
                    title: 'Meals',
                    icon: 'restaurant',
                    target: '/admin/meals',
                    visible: ['waiter', 'manager'] },
                {
                    title: 'Menu',
                    icon: 'fas fa-list-ul',
                    target: '/admin/menu',
                    visible: true },
                {
                    title: 'Management',
                    icon: 'build',
                    target: '/admin/restaurantManagement',
                    visible: ['manager']},
                {
                    title: 'Orders',
                    icon: 'restaurant',
                    target: '/admin/orders',
                    visible: ['cook']},
            ],
            mini: true,
            right: null
        }),
        sockets: {
            order_prepared(){
              console.log("the order is prepared");
            },
            user_enter(data){
               console.log('Data recieved from the server when started shift = '+data+')');
            },
            user_exit(data){
                console.log('Data recieved from the server when ended shift = '+data+')');
            },
        },
        computed: {
            user() {
                return this.$store.state.user;
            }
        },
        methods: {
            logout() {
                axios.post('logout')
                    .then(response => {
                        this.$store.commit('clearUserAndToken');
                        this.$router.push("/");
                    })
                    .catch(error => {
                        this.$store.commit('clearUserAndToken');
                        console.log(`Failed to logout, But local credentials were discarded: \n${error}`);
                    })
            },
        }
    }
</script>

<style scoped>

</style>