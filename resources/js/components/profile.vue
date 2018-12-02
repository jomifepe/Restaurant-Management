<template>
    <!--<div>
        <div class="alert alert-success" v-if="showSuccess">             
            <button type="button" class="close-btn" v-on:click="showSuccess=false">&times;</button>
            <strong>{{ successMessage }}</strong>
        </div>
        <user-edit :user="profileUser"  @user-saved="savedUser" @user-canceled="cancelEdit"></user-edit>
    </div>-->

    <div class="container">
        <div class="span3 well">
            <center>
                <a href="#aboutModal" data-toggle="modal" data-target="#myModal"><img :src="profileUser.photo_url" name="aboutme" width="140" height="140" class="img-circle"></a>
                <h3>{{profileUser.name}}</h3>
                <em>{{profileUser.type}}</em>
            </center>
        </div>
        <user-edit :user="profileUser"  @user-saved="savedUser" @user-canceled="cancelEdit"></user-edit>
    </div>
</template>

<script type="text/javascript">    
    import userEdit from './userEdit.vue';

    export default {
        components: {
            'user-edit': userEdit, 
        },
        data: function(){
            return { 
                profileUser: null,
                successMessage: "",
                showSuccess: false
            }
        },
        methods: {
            getInformationFromLoggedUser() {
                this.profileUser = this.$store.state.user;
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