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
                              class="elevation-1"
                >

                    <template slot="items" slot-scope="props">
                        <tr>
                            <td>{{ props.item.item_name }}</td>
                            <td>{{ props.item.quantity }}</td>
                            <td>{{ props.item.unit_price}}</td>
                            <td>{{ props.item.sub_total_price }}</td>
                        </tr>
                    </template>
				</v-data-table>
		</v-list>
        <v-btn block color="info" :to="'/admin/invoices'" >Close</v-btn>
        </v-card>
    </v-flex>
</template>

<script>
     export default {
        name: "InvoiceDetails",
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
            },
            pagination: {
                handler () {
                    this.getDataFromApi()
                        .then(data => {
                            this.invoiceItems = data.items;
                            this.totalInvoiceItems = data.total;
                         })
                },
                deep: true
            }
        },
        methods: {
             getDataFromApi (){
                this.loading = true;
                return new Promise((resolve, reject) => {
                    const { sortBy, descending, page, rowsPerPage } = this.pagination;

                    let items;

                    this.loadInvoiceItems()
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



                })
            },
            loadInvoiceItems(id){
                return axios.get(`/invoices/${this.$route.params.invoiceId}/items`);
            },
            loadInvoiceFromRouterId() {
               if (this.$route.params.invoiceId) {
                    
                    this.progressBar = true;
                    axios.get(`/invoices/${this.$route.params.invoiceId}`)
                        .then(response => {
                            console.log(response);
                            if (response.status === 200) {
                                this.invoice = response.data.data;    
                             //   this.$store.commit('setPanelTitle', `Details of Invoice #${this.invoice.id}`);
                             this.getDataFromApi()
                            .then(data => {
                                this.invoiceItems = data.items;
                                this.totalInvoiceItems = data.total;
                                })
                                
                            } else {
                                this.progressBar = false;
                                this.$toasted.show('Failed to get orders associated to the selected meal', {
                                    icon : 'error',
                                    position: "bottom-center",
                                    duration : 3000
                                });
                            }
                        })
                        .catch(error => {
                            this.progressBar = false;
                            console.log(error);
                            this.$toasted.show('Failed to get meal identified by the given id', {
                                icon : 'error',
                                position: "bottom-center",
                                duration : 3000
                            });
                        })
                }
            },
            closeDetails(){
                this.$router.pop({ name:'invoices.details'});
            },
        },
        mounted() {
            this.loadInvoiceFromRouterId(),
            this.getDataFromApi()
                .then(data => {
                    this.invoiceItems = data.items;
                    this.totalInvoiceItems = data.total;
            })
        }
    }
</script>

<style scoped>

</style>