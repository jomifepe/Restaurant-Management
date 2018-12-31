<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12> 
                <v-toolbar flat color="yellow">
                    <v-toolbar-title>Pending Invoices</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-text-field
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
    import InvoiceDetails from './InvoiceDetails';
    import {toasts} from '../../../mixin';

    export default {
            name: "PendingInvoices",
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
            mounted() {
                this.loadInvoices();

            },
            methods: {
                submit(invoice){
                    
                },
                showInvoiceDetails(id){
                    this.$router.push({ name: 'invoices.details', params: { invoiceId: id }});
                },
                 loadInvoices() {
                 this.loading = true;
                     axios.get(`/invoices/pending`)
                         .then(response => {
                         if(response.status=== 200){
                             this.loading= false;
                             this.invoices = response.data;
                            }
                        }).catch(error => {
                            this.loadingTableEffect=false;
                    });
                },
                getStateColor(state) {
                return state === 'pending' ? 'yellow--text' : 'green--text';
            },
                
            }
    }
</script>

<style scoped>

</style>