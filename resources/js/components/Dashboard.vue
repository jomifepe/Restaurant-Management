<template>
    <v-container grid-list-md>
        <v-layout>
            <v-flex xs12>
                <h4 class="display-1 font-weight-regular">Welcome back, {{ userFirstName(user.name) }}</h4>
                <Invoices v-if="isUserManager"></Invoices>
                <Meals v-if="isUserManager"></Meals>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import Invoices from "./Invoices";
    import Meals from "./Meals"
    import {helper} from "../mixin"

    export default {
        name: "Dashboard",
        mixins: [helper],
        components: {Invoices, Meals},
        computed: {
            user() {
                return this.$store.state.user;
            }
        }
    }
</script>

<style scoped>

</style>