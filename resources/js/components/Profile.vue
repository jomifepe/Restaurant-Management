<template>
    <v-container grid-list-md>
        <v-layout row>
            <v-list id="user-card" class="blue-grey darken-3 rounded elevation-2" two-line>
                <v-list-tile avatar>
                    <v-list-tile-avatar size="100" color="grey lighten-4">
                        <img class="bordered" :src="user.photo_url" alt="Worker photo">
                    </v-list-tile-avatar>

                    <v-list-tile-content class="ml-4">
                        <v-list-tile-title class="white--text">
                            {{ user.name }}
                        </v-list-tile-title>
                        <v-list-tile-sub-title>
                            <v-chip small class="no-margin-left" :color="getUserAppearence(user).color" 
                                text-color="white">
                                {{ user.type }}
                            </v-chip>
                        </v-list-tile-sub-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list>
            <v-flex xs12 class="white elevation-2 rounded mt-3">
                <v-container>
                    <UserEdit :user="Object.assign({}, user)"></UserEdit>
                </v-container>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script type="text/javascript">
    import UserEdit from './UserEdit.vue';
    import {helper} from '../mixin.js';

    export default {
        mixins: [helper],
        components: {
            UserEdit
        },
        computed: {
            user() {
                return this.$store.state.user;
            }
        },
        mounted() {
            this.$store.commit('setPanelTitle', 'Profile');
        }
    }
</script>

<style scoped>
    #user-card {
        width: 100%;
    }
</style>