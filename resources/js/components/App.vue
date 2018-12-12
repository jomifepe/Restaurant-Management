<template>
    <router-view></router-view>
</template>

<script>
    export default {
        name: "App",
        created() {
            this.$store.commit('loadTokenAndUserFromSession');
        }
    }
</script>

<style scoped>
</style>