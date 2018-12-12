<template>
    <h5 class="mt-4">Hello from Dashboard</h5>
</template>

<script>
    export default {
        name: "Dashboard",
        mounted() {
            this.$store.commit('setPanelTitle', 'Dashboard');
        }
    }
</script>

<style scoped>

</style>