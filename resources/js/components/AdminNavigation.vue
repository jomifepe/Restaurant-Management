<template>
    <v-app v-if="user">
        <v-navigation-drawer
                v-model="drawer"
                :mini-variant="mini"
                :clipped="clippedNavDrawer"
                hide-overlay
                stateless
                app>
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
                        <v-list-tile-title>{{ userFirstAndLastName }}</v-list-tile-title>
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

                <v-list-tile v-for="item in items"
                    :key="item.title"
                    :to="item.target"
                    v-if="item.visible === true ? true : item.visible.includes(userType)">
                        <v-list-tile-action>
                            <v-icon>{{ item.icon }}</v-icon>
                        </v-list-tile-action>

                        <v-list-tile-content>
                            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                        </v-list-tile-content>
                </v-list-tile>
            </v-list>
        </v-navigation-drawer>
        <v-toolbar :clipped-left="clippedToolbar" color="blue-grey darken-1" dark dense app>
            <v-toolbar-side-icon @click.stop="drawer = !drawer" title="Toggle menu"></v-toolbar-side-icon>
            <v-toolbar-title>{{ this.$store.state.panelTitle }}</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items class="hidden-sm-and-down">
                <v-menu :nudge-width="100">
                    <v-toolbar-title slot="activator">
                        <span>{{ userFirstName }}</span>
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
        </v-toolbar>
        <v-content>
            <v-container fluid>
                <v-flex xs12>
                    <router-view></router-view>
                </v-flex>
            </v-container>
        </v-content>
        <v-footer height="auto" app>
            <WorkerInfo></WorkerInfo>
        </v-footer>
    </v-app>


</template>

<script>
    import WorkerInfo from './WorkerInfo';
    import axios from 'axios';

    export default {
        name: "AdminNavigation",
        components: {
            WorkerInfo
        },
        data() {
            return {
                clippedNavDrawer: false,
                clippedToolbar: false,
                drawer: true,
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
                        visible: true }
                ],
                mini: true,
                right: null
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
        },
        computed: {
            user() {
                return this.$store.state.user;
            },
            userFirstName() {
                return this.$store.state.user.name.split(" ")[0];
            },
            userFirstAndLastName() {
                let parts = this.$store.state.user.name.split(" ");
                if (parts.length > 1) {
                    return `${parts[0]} ${parts[parts.length - 1]}`;
                }
                return this.$store.state.user.name;
            },
            userType() {
                return this.$store.state.user.type;
            }
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>