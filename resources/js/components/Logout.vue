<template>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
           data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            {{ getCurrentUserFirstName() }}
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" v-on:click.prevent="logout">Logout</a>
        </div>
    </li>
</template>

<script type="text/javascript">
    export default {
        data: function(){
            return {
            }
        },
        methods: {
            logout() {
                this.showMessage = false;
                axios.post('logout')
                    .then(response => {
                        this.$store.commit('clearUserAndToken');
                        this.$emit('logout-successul');
                    })
                    .catch(error => {
                        this.$store.commit('clearUserAndToken');
                        console.log(`Failed to logout, But local credentials were discarded: \n${error}`);
                        this.$emit('logout-failed');
                    })
            },
            getCurrentUserFirstName() {
                return this.$store.state.user.name.split(" ")[0];
            }
        }
    }
</script>