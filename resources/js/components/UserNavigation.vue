<template>
    <div>
        <template v-if="!this.$store.state.user">
            <div class="container">
                <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                    <router-link class="navbar-brand" to="/">RM</router-link>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mr-auto">
                            <router-link to="menu">Menu</router-link>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <router-link to="login">Login</router-link>
                            </li>
                        </ul>
                    </div>
                </nav>
                <router-view></router-view>
            </div>
        </template>
    </div>
</template>

<script>

    export default {
        name: "UserNavigation"
    }
</script>

<style scoped>

</style>