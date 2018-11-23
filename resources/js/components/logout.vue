<template>
    <div>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" v-on:click.prevent="logout">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script type="text/javascript">
    export default {
        data: function(){
            return {
                typeofmsg: "alert-success",
                showMessage: false,
            }
        },
        methods: {
            logout() {
                this.showMessage = false;
                axios.post('logout')
                    .then(response => {
                        this.$store.commit('clearUserAndToken');
                        this.typeofmsg = "alert-success";
                        //$emit('loggout', "User has logged out correctly");
                    })
                    .catch(error => {
                        this.$store.commit('clearUserAndToken');
                        this.typeofmsg = "alert-danger";
                        this.message = "Logout incorrect. But local credentials were discarded";
                        this.showMessage = true;
                        console.log(error);
                    })
            }
        }
    }
</script>