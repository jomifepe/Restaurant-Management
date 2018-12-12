<template>
    <v-flex xs12 class="ma-3" v-if="profileUser != null">
        <v-avatar size="140"
                color="grey lighten-4">
            <img :src="profileUser.photo_url" alt="Worker photo">
        </v-avatar>
        <h3>{{ profileUser.name }}</h3>
        <em>{{ profileUser.type }}</em>
        <UserEdit :user="Object.assign({}, profileUser)"></UserEdit>
    </v-flex>
</template>

<script type="text/javascript">    
    import UserEdit from './UserEdit.vue';

    export default {
        components: {
            UserEdit,
        },
        data: function() {
            return {
                profileUser: null,
            }
        },
        methods: {
            getInformationFromLoggedUser() {
                this.profileUser = this.$store.state.user;
            }
        },
        mounted() {
            this.$store.commit('setPanelTitle', 'Profile');
            this.getInformationFromLoggedUser();
        }
    }
</script>