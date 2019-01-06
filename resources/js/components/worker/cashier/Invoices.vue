<template>
    <v-container grid-list-md fluid>
        <v-layout row wrap>
            <pending-invoices-list></pending-invoices-list>
            <not-pending-invoices-list v-if="user.type === 'cashier'"></not-pending-invoices-list>
            <v-flex xs12 id="InvoiceDetails" class="mt-5">
                <router-view></router-view>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import PendingInvoicesList from './PendingInvoicesList';
    import NotPendingInvoicesList from './NotPendingInvoicesList';
    import {toasts} from '../../../mixin';

    export default {
        name: "Invoices",
        mixins: [toasts],
        components: {
            'pending-invoices-list' : PendingInvoicesList,
            'not-pending-invoices-list' : NotPendingInvoicesList
        },
        data: () => ({
            dialog: false,
            otherInvoices: false,
        }),
        computed: {
            user() {
                return this.$store.state.user;
            }
        }
    }
</script>

<style scoped>

</style>