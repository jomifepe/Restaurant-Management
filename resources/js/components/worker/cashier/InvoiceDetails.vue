<template>
    <v-flex v-if="invoice">
        <v-card>
            <v-card-title>
                <h4>Invoice #{{invoice.id }}</h4>
            </v-card-title>
            <v-divider></v-divider>
            	<v-list dense>
			<v-list-tile>
				<v-list-tile-content>NIF:</v-list-tile-content>
				<v-list-tile-content v-if="invoice.state==='paid'" class="align-end">{{ invoice.nif }}</v-list-tile-content>
                <v-list-tile-content v-if="invoice.state!=='paid'" class="align-end">---</v-list-tile-content>
            </v-list-tile>
            <v-list-tile>
				<v-list-tile-content>Name:</v-list-tile-content>
				<v-list-tile-content v-if="invoice.state==='paid'" class="align-end">{{ invoice.name }}</v-list-tile-content>
                <v-list-tile-content v-if="invoice.state!=='paid'" class="align-end">---</v-list-tile-content>
			</v-list-tile>
            <v-list-tile>
				<v-list-tile-content>Date:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ invoice.date }}</v-list-tile-content>
			</v-list-tile>
			<v-list-tile>
				<v-list-tile-content>Table Number:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ invoice.table_number }}</v-list-tile-content>
			</v-list-tile>
			<v-list-tile>
				<v-list-tile-content>Waiter Name:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ invoice.responsible_waiter_name }}</v-list-tile-content>
			</v-list-tile>	
            <v-list-tile>
				<v-list-tile-content>Total Price:</v-list-tile-content>
				<v-list-tile-content class="align-end">{{ invoice.total_price }}€</v-list-tile-content>
			</v-list-tile>
            <v-divider></v-divider>
            <v-list-tile>
            <v-list-tile-content>Items:</v-list-tile-content>
            </v-list-tile>
				<v-data-table :headers="headers"
                              :items="invoiceItems"
                              :pagination.sync="pagination"
                              :total-items="totalInvoiceItems"
                              :loading="loading"
                              class="elevation-1">

                    <template slot="items" slot-scope="props">
                        <tr>
                            <td>{{ props.item.item_name }}</td>
                            <td>{{ props.item.quantity }}</td>
                            <td>{{ props.item.unit_price }}€</td>
                            <td>{{ props.item.sub_total_price }}€</td>
                        </tr>
                    </template>
				</v-data-table>
		</v-list>
        <v-btn block color="info" :to="'/admin/invoices'" >Close</v-btn>
        </v-card>
    </v-flex>
</template>

<script>
    import {toasts, helper} from '../../../mixin';

    export default {
        name: "InvoiceDetails",
        mixins: [toasts, helper],
        data: () => ({
            invoice : null,
            invoiceItems: [],
            totalInvoiceItems: 0,
            loading: true,
            pagination: {},
            headers: [
                { text: 'Name', value: 'name' },
                { text: 'Quantity', value: 'quantity' },
                { text: 'Unit Price', value: 'unit_price' },
                { text: 'Sub Total Price', value: 'sub_total_price' }
            ],
        }),
        computed: {
        },
        watch: {
            $route(to, from) {
                this.loadInvoiceFromRouterId();
            }
        },
        methods: {
            loadInvoiceItems() {
                return new Promise(resolve => {
                    this.loading = true;
                    axios.get(`/invoices/${this.$route.params.invoiceId}/items`)
                        .then(response => {
                            if (response.status === 200) {
                                this.invoiceItems = response.data;
                                this.totalInvoiceItems = this.invoice.length;
                            } else {
                                this.showErrorToast('Failed to get invoices');
                            }
                        })
                        .catch(error => {
                            this.showErrorLog('Failed to get invoices', error);
                        })
                        .finally(() => {
                            this.loading = false;
                            resolve();
                        });
                })
            },
            loadInvoiceFromRouterId() {
                return new Promise(resolve => {
                    if (this.$route.params.invoiceId) {
                        this.progressBar = true;
                        axios.get(`/invoices/${this.$route.params.invoiceId}`)
                            .then(response => {
                                if (response.status === 200) {
                                    this.invoice = response.data.data;
                                    // this.$store.commit('setPanelTitle', `Details of Invoice #${this.invoice.id}`);
                                } else {
                                    this.showErrorToast('Failed to get invoice identified by the given id');
                                }
                            })
                            .catch(error => {
                                this.showErrorLog('Failed to get invoice identified by the given id', error);
                            })
                            .finally(() => {
                                this.progressBar = false;
                                resolve();
                            })
                    } else {
                        resolve();
                    }
                })
            },
            closeDetails(){
                this.$router.pop({ name:'invoices.details'});
            },
        },
        mounted() {
            this.loadInvoiceFromRouterId()
                .then(() => this.loadInvoiceItems());
        }
    }
</script>

<style scoped>

</style>