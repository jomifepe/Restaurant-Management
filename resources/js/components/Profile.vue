<template>
    <!--<div>
        <div class="alert alert-success" v-if="showSuccess">             
            <button type="button" class="close-btn" v-on:click="showSuccess=false">&times;</button>
            <strong>{{ successMessage }}</strong>
        </div>
        <user-edit :user="profileUser"  @user-saved="savedUser" @user-canceled="cancelEdit"></user-edit>
    </div>-->


        <div class="container mt-4" v-if="profileUser != null">
            <div class="span3 well">
                <div class="text-center">
                    <a href="#aboutModal" data-toggle="modal" data-target="#myModal">
                        <img :src="profileUser.photo_url" name="aboutme" width="140" height="140" class="img-circle" />
                    </a>
                    <h3>{{ profileUser.name }}</h3>
                    <em>{{ profileUser.type }}</em>
                </div>
            </div>
            <user-edit :user="profileUser" @user-saved="savedUser" @user-canceled="cancelEdit"></user-edit>
        </div>
</template>

<script type="text/javascript">    
    import UserEdit from './UserEdit.vue';

    export default {
        components: {
            'user-edit': UserEdit,
        },
        data: function() {
            return {
                profileUser: null,
                successMessage: "",
                showSuccess: false
            }
        },
        methods: {
            getInformationFromLoggedUser() {
                this.profileUser = this.$store.state.user;
                this.showProfile = true;
            },
            savedUser: function(){
                this.showSuccess = true;
                this.successMessage = "User's Profile Updated";
            },
            cancelEdit: function(){
                this.showSuccess = false;
            },            
        },
        mounted() {
            this.getInformationFromLoggedUser();
        }
    }
</script>