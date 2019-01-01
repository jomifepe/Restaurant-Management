<template>
    <v-container grid-list-md fluid>
        <pending-invoices-list></pending-invoices-list>
        <v-btn v-if="user.type==='manager' && !otherInvoices" block color="info" @click="showAllInvoices()">Get Other Invoices</v-btn>
        <not-pending-invoices-list v-if="user.type==='cashier' || otherInvoices" @onClose="closeOtherInvoices"></not-pending-invoices-list>
    <v-flex xs12 id="InvoiceDetails" class="mt-5">
            <router-view></router-view>
    </v-flex>
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
            user(){
                return this.$store.state.user;
            }
        },
        mounted() {
 
        },
        methods: {
            closeOtherInvoices(){
                this.otherInvoices=false;
            },
            showAllInvoices(){
                this.otherInvoices= true;
            },
            
        }
    }
</script>

<style scoped>

</style>