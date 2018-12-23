<template>
    <div>
        <span> HELLO FROM DASHBOARD</span>

        <Invoices></Invoices>
        <Meals></Meals>

    </div>
</template>

<script>
    import Invoices from "./Invoices";
    import Meals from "./Meals"
    export default {
        name: "Dashboard",
        components: {Invoices, Meals}
    }
</script>

<style scoped>

</style>