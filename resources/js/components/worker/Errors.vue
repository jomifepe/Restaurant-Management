<template>
    <v-alert :value="true" type="error">
        {{msg}}
    </v-alert>
</template>

<script>
    export default {
        name: "Errors",
        props:['msg'],
    }
</script>

<style scoped>

</style>