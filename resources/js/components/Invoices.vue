<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>Invoices</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-text-field
                        v-if="user.type ==='manager'"
                        v-model="search"
                        append-icon="search"
                        label="Search"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-toolbar>
                <v-data-table :headers="myInvoicesHeaders"
                              :items="invoices"
                              :pagination.sync="pagination"
                              :loading="loading"
                              :search="search"
                              class="elevation-1"
                >

                    <template slot="items" slot-scope="props">
                         <tr @click="props.expanded = !props.expanded">
                            <td>{{ props.item.id }}</td>
                            <td>{{props.item.table_number}}</td>
                            <td>{{ props.item.responsible_waiter_name}}</td>
                            <td :class="getStateColor(props.item.state)">
                                <strong>{{ props.item.state }}</strong>
                            </td>
                            <td>{{ props.item.date }}</td>
                            <td>{{ props.item.total_price }}€</td>
                        </tr>
                    </template>
                    <v-alert slot="no-results" :value="true" color="error" icon="warning">
                        Your search for "{{ search }}" found no results.
                    </v-alert>
                     <template slot="expand" slot-scope="props">
                                <v-dialog v-model="dialog" persistent max-width="600px">
                                <v-btn flat small v-if="props.item.state ==='pending'" slot="activator">Fill Client Information</v-btn>
                                <v-card>
                                    <v-card-title>
                                    <span class="headline">User Information</span>
                                    </v-card-title>
                                    <v-form ref="form" v-model="valid" >
                                    <v-card-text>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                        <v-flex xs12>
                                            <v-text-field  v-model="nif" label="NIF*" :rules="nifRules" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12>
                                            <v-text-field v-model="name" label="Full Name*" :rules="nameRules" required></v-text-field>
                                        </v-flex>  
                                        </v-layout>
                                    </v-container>
                                    <small>*indicates required field</small>
                                    </v-card-text>
                                    <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" flat @click="close()">Close</v-btn>
                                    <v-btn :disabled="!valid" color="blue darken-1" flat @click="submit(props.item)">Close Invoice</v-btn>
                                    </v-card-actions>
                                    </v-form>
                                </v-card>
                                </v-dialog>
                                <v-btn flat small @click="showInvoiceDetails(props.item.id)">
                                 Details
                                </v-btn>
                                <v-btn @click="exportToPdf(props.item)" icon>
                                 <v-icon>print</v-icon>
                                </v-btn>
                            </v-card-text>
                        </v-card>
                    </template>
                </v-data-table>
            </v-flex>
        </v-layout>
        <v-flex xs12 id="InvoiceDetails" class="mt-5">
            <router-view></router-view>
        </v-flex>
    </v-container>
</template>

<script>
    import axios from 'axios';
    import jsPDF from 'jspdf';
    import autoTable from 'jspdf-autotable';
    import InvoiceDetails from './InvoiceDetails';
    import {toasts} from '../mixin';

    export default {
        name: "Invoices",
        mixins: [toasts],
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
                    { text: 'State', value: 'state' },
                    { text: 'Date', value: 'date'},
                    { text: 'Total Price', value: 'total_price' }
                ],
                search: '',            
                dialog: false,
                totalInvoices: 0,
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
            }
        },
        computed: {
            user(){
                return this.$store.state.user;
            }
        },
        watch: {
            pagination: {
                handler () {
                    this.getDataFromApi()
                        .then(data => {
                            this.invoices = data.items;
                            this.totalInvoices = data.total;
                        })
                },
                deep: true
            }
        },
        mounted() {
           // this.getDataFromApi()
           //     .then(data => {
           //         this.invoices = data.items;
           //         this.totalInvoices= data.total
           //     })
           this.loadInvoices();
           if(this.user.type==='manager'){
               this.search='pending';
           }
        },
        methods: {
            sendNotificationToManager(meal){
                let message =
                {
                    'title' : "Meal Paid",
                    'text' : `The meal ${meal.id} in table ${meal.table_number} is now paid`
                }
                this.$socket.emit('to_all_managers', message);
            },
            close(){
                this.dialog = false;
                this.$refs.form.reset();
            },
            getMeal(id){
                return axios.get(`invoices/${id}/meal`);
            },
            updateMeal(meal){
                return axios.patch(`meals/${meal.id}`, meal);
            },
            submit(invoice){
                if (this.$refs.form.validate()) {
                    invoice.nif= this.nif;
                    invoice.name= this.name;
                    invoice.state = 'paid';
                    var meal = null;

                    axios.patch(`/invoices/${invoice.id}`, invoice)
                        .then(response => {
                            if (response.status === 200) {
                                this.getMeal(invoice.id)
                                    .then(response => {
                                        meal = response.data.data;
                                        meal.state = 'paid';
                                        this.updateMeal(meal)
                                            .then(responseMealUpdate => {
                                                this.showSuccessToast('Meal edited');
                                                this.sendNotificationToManager(meal);
                                            }).catch(error => {
                                            if (error.response.data) {
                                                this.showErrorToast(error.response.data.message);
                                            } else {
                                                this.showErrorLog(`Failed to update meal`, error);
                                            }
                                        });
                                    }).catch(error => {
                                            if (error.response.data) {
                                                this.showErrorToast(error.response.data.message);
                                            } else {
                                                this.showErrorLog(`Failed to get the invoice meal`, error);
                                            }
                                        });
                                this.showSuccessToast('Invoice edited');
                            }
                        }).catch(error => {
                        if (error.response.data) {
                            this.showErrorToast(error.response.data.message);
                        } else {
                            this.showErrorLog(`Failed to update invoice`, error);
                        }
                    });
                
                
                }
                this.dialog = false
            },
            showInvoiceDetails(id){
                this.$router.push({ name: 'invoices.details', params: { invoiceId: id }});
            },
            getDataFromApi (){
                this.loading = true;
                return new Promise((resolve, reject) => {
                    const { sortBy, descending, page, rowsPerPage } = this.pagination;

                    let items;

                    this.loadInvoices()
                        .then(data => {
                            items= data.data;
                            const total = items.length; 
                            
                            
                            if (rowsPerPage > 0) {
                                items = items.slice((page - 1) * rowsPerPage, page * rowsPerPage)
                            }

                            setTimeout(() => {

                                this.loading = false;
                                resolve({
                                    items,
                                    total
                                })
                            }, 1000)
                        });
                });
                
            },
            getStateColor(state) {
                return state === 'pending' ? 'yellow--text' : 'green--text';
            },
            loadInvoices() {
                this.loading = true;
                axios.get(`/invoices/details`)
                    .then(response => {
                       // console.log(response);
                       if(response.status=== 200){
                        this.loading= false;
                        this.invoices = response.data;
                       }
                        
                    }).catch(error => {
                        this.loadingTableEffect=false;
                });
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

        }
    }
</script>

<style scoped>

</style>