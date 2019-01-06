<template>
    <v-flex xs12> 
        <v-toolbar flat :color="isUserManager ? 'gray' : 'amber'">
            <v-toolbar-title>
                {{ isUserManager ? 'All Invoices' : 'Pending Invoices'}}
                <span class="body-1">(click to reveal invoice actions)</span>
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="search" class="mr-2"
                label="Search" single-line hide-details></v-text-field>
            <v-dialog v-if="isUserManager" v-model="filterDialog" max-width="450">
                <v-btn v-if="isUserManager" slot="activator" color="primary" fab dark>
                    <v-icon>filter_list</v-icon>
                </v-btn>
                <v-card>
                    <v-card-title>
                        <span class="headline">Filters</span>
                    </v-card-title>
                    <v-card-text class="pr-5 pb-3 pl-5 pt-3">
                        <v-select :items="stateFilterList" v-model="stateFilter" chips
                            label="Meal state" multiple>
                        </v-select>
                        <v-select v-if="this.isUserManager" :items="waiterFilterList" v-model="waiterFilter"
                            label="Responsible waiter" multiple item-text="name" item-value="id">
                            <template slot="selection" slot-scope="{ item, index }">
                                <v-chip v-if="index === 0">
                                    <span>{{ item.name }}</span>
                                </v-chip>
                                <span v-if="index === 1" class="grey--text caption">
                                    (+{{ waiterFilter.length - 1 }} others)
                                </span>
                            </template>
                        </v-select>
                        <v-menu v-if="this.isUserManager" :close-on-content-click="false"
                            v-model="filterDatePickerMenu" :nudge-right="40" lazy
                            transition="scale-transition" offset-y full-width min-width="290px">
                            <v-text-field slot="activator" v-model="filterDate" clearable
                                label="Date" prepend-icon="event" readonly>
                            </v-text-field>
                            <v-date-picker v-model="filterDate" @input="filterDatePickerMenu = false">
                            </v-date-picker>
                        </v-menu>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn flat @click="filterDialog = false">
                            Close
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-toolbar>
        <v-data-table :headers="myInvoicesHeaders" :items="isUserManager ? filteredInvoices : invoices"
            :pagination.sync="pagination" :loading="loading" :search="search" class="elevation-1">
            <template slot="items" slot-scope="props">
                <tr class="clickable" @click="props.expanded = !props.expanded">
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
                <v-dialog v-model="dialog" persistent max-width="600px">
                    <v-btn flat small v-if="props.item.state ==='pending'" slot="activator">Fill Client Information</v-btn>
                    <v-card>
                        <v-card-title>
                            <span class="headline">User Information</span>
                        </v-card-title>
                        
                        <v-card-text>
                            <v-container grid-list-md>
                                <v-form ref="form" v-model="valid" >
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-text-field ref="nif" v-model="nif" label="NIF*" :rules="nifRules" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12>
                                            <v-text-field ref="name" v-model="name" label="Full Name*" :rules="nameRules" required></v-text-field>
                                        </v-flex>  
                                    </v-layout>
                                </v-form>
                            </v-container>
                            <small>*indicates required field</small>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue darken-1" flat @click="close()">Close</v-btn>
                                <v-btn :disabled="!valid" color="blue darken-1" flat @click="submit(props.item)">
                                    Close Invoice
                                </v-btn>
                            </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-btn flat small v-if="user.type==='manager'" @click="declareAsNotPaid(props.item)">
                    Declare as not paid
                </v-btn>
                <v-btn flat small @click="showInvoiceDetails(props.item.id)">
                    Details
                </v-btn>
            </template>
        </v-data-table> 
    </v-flex>
</template>

<script>
    import InvoiceDetails from './InvoiceDetails';
    import {toasts, helper} from '../../../mixin';
    import moment from 'moment';

    export default {
        name: "PendingInvoices",
        mixins: [toasts, helper],
        components: {
            InvoiceDetails
        },
        data: () => ({
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
            filterDialog: false,
            stateFilterList: ['pending', 'paid', 'not paid'],
            stateFilter: ['pending'],
            waiterFilterList: [],
            waiterFilter: [],
            filterDate: '',
            filterDatePickerMenu : false
        }),
        computed: {
            user() {
                return this.$store.state.user;
            },
            filteredInvoices() {
                return this.invoices.filter(invoice => {
                    let hasState = this.stateFilter.includes(invoice.state);
                    let hasWaiter = this.waiterFilter.length == 0 ||
                        this.waiterFilter.includes(invoice.responsible_waiter_id);
                    let filterDateFormatted = moment(this.filterDate).format('YYYY-MM-DD');
                    let invoiceDateFormatted = moment(invoice.date).format('YYYY-MM-DD');
                    let isOnDate = !this.filterDate || filterDateFormatted === invoiceDateFormatted;
                    return hasState && hasWaiter && isOnDate;
                });
            },
        },
        watch: {
            dialog() {
                if (this.dialog) {
                    this.$refs.form.reset();
                    this.$nextTick(function(){
                        this.$refs.nif.focus();
                    });
                }
            }
        },
        sockets: {
            pending_invoice_received(user) {
                this.loadInvoices();
            },
            meal_paid_notify(meal){
                this.showTopRightInfoToast("Invoice paid table(" + meal.table_number +")")
                this.loadInvoices();
            }
        },
        methods: {
            saveOrder(order){
                return axios.patch(`/orders/${order.id}`, order);
            },
            getAllNotDelOrdersFormAMeal(meal){
                return axios.get(`/orders/meal/${meal.id}/notDeliveredItems`);
            },
            changeAllNotDeliveredOrdersFormAMealToNotDelivered(meal){
                let orders= null;
                var promises= [];
                this.getAllNotDelOrdersFormAMeal(meal)
                    .then(response => {
                        orders = response.data;
                        orders.forEach(order => {
                            order.state = 'not delivered';
                            promises.push(this.saveOrder(order));

                        })
                        axios.all(promises)
                            .then(axios.spread((...responses) => {
                                // responses.forEach(res => console.log('Success'))
                                // console.log('submitted all axios calls');
                                this.$socket.emit('update_not_pending');
                            }))
                    })
                    .catch(error => {
                        this.showErrorLog('Failed to change orders', error);
                    })
            },
            declareAsNotPaid(invoice){
                this.changeInvoice(invoice,'not paid');
            },
            changeInvoice(invoice, state){
                invoice.state = state;
                var meal = null;
                axios.patch(`/invoices/${invoice.id}`, invoice)
                    .then(response => {
                        if (response.status === 200) {
                            this.getMeal(invoice.id)
                                .then(response => {
                                    meal = response.data.data;
                                    meal.state = state;
                                    this.updateMeal(meal)
                                        .then(responseMealUpdate => {
                                            this.showSuccessToast('Meal edited');
                                            this.sendNotificationToManager(meal, state);
                                            if(state === 'not paid'){
                                                this.changeAllNotDeliveredOrdersFormAMealToNotDelivered(meal);
                                            }
                                            // console.log("loading");
                                            this.$socket.emit('meal_paid', meal);
                                            this.loadInvoices();
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
            },
            close(){
                this.dialog = false;
                this.$refs.form.reset();
            },
            sendNotificationToManager(meal, state){
                let message =
                {
                    'sender' : this.user,
                    'title' : "Meal Paid",
                    'text' : `The meal ${meal.id} in table ${meal.table_number} is now paid`
                }
                this.$socket.emit('to_all_managers', message);
            },
            submit(invoice){
                if (this.$refs.form.validate()) {
                    invoice.nif= this.nif;
                    invoice.name= this.name;
                    this.changeInvoice(invoice, 'paid');
                }
                this.dialog = false
             },
            showInvoiceDetails(id){
                this.$router.push({ name: 'invoices.details', params: { invoiceId: id }});
            },
            getMeal(id){
                return axios.get(`invoices/${id}/meal`);
            },
            updateMeal(meal){
                return axios.patch(`meals/${meal.id}`, meal);
            },
            loadInvoices() {
                this.loading = true;
                if (this.isUserManager) {
                    axios.get(`/invoices/details`)
                        .then(response => {
                            if (response.status === 200) {
                                this.invoices = response.data;
                            }
                        })
                        .catch(error => {
                            this.showErrorLog('Failed to load invoices', error);
                        })
                        .finally(() => this.loading = false);
                } else {
                    axios.get(`/invoices/pending`)
                        .then(response => {
                            if (response.status === 200) {
                                this.invoices = response.data;
                            }
                        })
                        .catch(error => {
                            this.showErrorLog('Failed to load pending invoices', error);
                        })
                        .finally(() => this.loading = false);
                }
            },
            loadWaiters() {
                axios.get('/users/all/waiter')
                    .then(response => {
                        if (response.status === 200) {
                            this.waiterFilterList = response.data.data;
                        } else {
                            this.showErrorToast('Failed to get all waiters to populate filters');
                        }
                    })
                    .catch(error => {
                        this.showErrorLog('Failed to get all waiters to populate filters', error);
                    })
            },
        },
        mounted() {
            this.loadInvoices();
            if (this.isUserManager) {
                this.loadWaiters();
            }
        }
    }
</script>

<style scoped>

</style>