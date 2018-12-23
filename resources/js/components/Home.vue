<template>
    <h5 class="mt-4">Hello from Home</h5>
</template>

<script>
    export default {
        name: "Home",
        mounted() {
            this.$store.commit('setPanelTitle', 'Home');
        }
    }
</script>

<style scoped>

</style>