<template>
    <v-flex xs12 class="mt-3"> 
        <v-toolbar flat color="green">
            <v-toolbar-title>
                Other Invoices
                <span class="body-1">(click to reveal invoice actions)</span>
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="search"
                label="Search" single-line hide-details></v-text-field>                   
        </v-toolbar>
        <v-data-table :headers="myInvoicesHeaders" :items="invoices" :pagination.sync="pagination"
            :loading="loading" :search="search" class="elevation-1">

        <template slot="items" slot-scope="props">
            <tr :class="{'newTableRecord': isSecondDateAfter(mountedTime, props.item.updated_at.date), 
                'clickable': true}" @click="props.expanded = !props.expanded">
                <td>{{ props.item.id }}</td>
                <td>{{props.item.table_number}}</td>
                <td>{{ props.item.responsible_waiter_name}}</td>
                <td>{{ props.item.date }}</td>
                <td>{{ props.item.total_price }}€</td>
                <td :class="getInvoiceStateTextColor(props.item.state)">
                    <strong>{{ props.item.state }}</strong>
                </td>
            </tr>
        </template>
        <v-alert slot="no-results" :value="true" color="error" icon="warning">
            Your search for "{{ search }}" found no results.
        </v-alert>
            <template slot="expand" slot-scope="props">
                <v-btn flat small @click="showInvoiceDetails(props.item.id)">
                    Details
                </v-btn>
                <v-btn @click="exportToPdf(props.item)" icon>
                    <v-icon>print</v-icon>
                </v-btn>
            </template>
        </v-data-table> 
    </v-flex>
</template>

<script>
    import InvoiceDetails from './InvoiceDetails';
    import {toasts, helper} from '../../../mixin';
    import jsPDF from 'jspdf';
    import autoTable from 'jspdf-autotable';
    import moment from 'moment';

    export default {
            name: "NotPendingInvoices",
            mixins: [toasts, helper],
            components: {
                InvoiceDetails
            },
            data(){
            return {
                invoices: [],
                myInvoicesHeaders: [
                    { text: 'Id', value: 'id' },
                    { text: 'Table number', value: 'table_number' },
                    { text: 'Waiter', value: 'responsible_waiter_name' },
                    { text: 'Date', value: 'date'},
                    { text: 'Total Price', value: 'total_price' },
                    { text: 'State', value: 'state' }
                ],
                search: '',            
                dialog: false,
                loading: true,
                pagination: {},
                valid: true,
                name: '',
                nameRules: [
                    v => !!v || 'Name is required',
                    v => /^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/.test(v) || 'Name must only contain letters and spaces'
                ],
                nif: '',
                nifRules: [
                    v => !!v || 'NIF is required',
                    v => /^\d{9}$/.test(v) || 'NIF must have 9 numbers'
                ],
                mountedTime: null
            }
        },
        sockets: {
            update_orders(){
                this.loadInvoices();
            },
        },
        computed: {
            user(){
                return this.$store.state.user;
            }
        },
        methods: {
            closeInvoices(){
                this.$emit('onClose');
            },
            close(){
                this.dialog = false;
                this.$refs.form.reset();
            },
            showInvoiceDetails(id){
                this.$router.push({ name: 'invoices.details', params: { invoiceId: id }});
            },
            loadInvoices() {
                this.loading = true;
                axios.get(`/invoices/notpending`)
                    .then(response => {
                       if(response.status=== 200){
                            this.invoices = response.data;
                       }
                    })
                    .catch(error => {
                        this.loadingTableEffect = false;
                    })
                    .finally(() => this.loading = false );
            },
            exportToPdf(invoice){
                let doc = new jsPDF('p','pt');
                let columns = [
                    {title: 'Id', dataKey: 'Id'},
                    {title: 'Price', dataKey: 'Price'},
                    {title: 'Waiter', dataKey: 'Waiter'},
                    {title: 'NIF', dataKey: 'NIF'},
                    {title: 'Name', dataKey: 'Name'},
                    {title: 'Date', dataKey: 'Date'},
                    

                ];
                let teste = [
                    {
                        'Id': invoice.id,
                        'Price': invoice.total_price,
                        'Waiter': invoice.responsible_waiter_name,
                        'NIF': invoice.nif,
                        'Name': invoice.name,
                        'Date' : invoice.date

                    }
                ]
                doc.autoTable(columns,teste);
                doc.save(`invoice${invoice.id}.pdf`);
            },
        },
        mounted() {
            this.loadInvoices();
            this.mountedTime = moment().format();
        }
    }
</script>

<style scoped>

</style>    