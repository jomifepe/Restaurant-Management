<template>
    <v-alert dismissible v-model="this.$store.state.alertShown" :type="this.$store.state.alertType">
        {{ this.$store.state.alertMessage }}
    </v-alert>
</template>

<script>
    export default {
        name: "AlertMessage"
    }
</script>

<style scoped>

</style>