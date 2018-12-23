<template>
    <v-container grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-toolbar flat color="gray">
                    <v-toolbar-title>Paid invoices</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-data-table :headers="myInvoicesHeaders"
                              :items="invoices"
                              :pagination.sync="pagination"
                              :total-items="totalInvoices"
                              :loading="loading"
                              class="elevation-1"
                >

                    <template slot="items" slot-scope="props">
                        <tr>
                            <td>{{ props.item.id }}</td>
                            <td>{{ props.item.name }}</td>
                            <td> <v-btn @click="exportToPdf(props.item)" icon>
                                    <v-icon>print</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-flex>
        </v-layout>
    </v-container>
</template>
<script>
    import axios from 'axios';
    import jsPDF from 'jspdf';
    import autoTable from 'jspdf-autotable';

    export default {
        name: "Invoices",
        data(){
           return {
                invoices: [],
                myInvoicesHeaders: [
                    { text: 'Id', value: 'id' },
                    { text: 'Name', value: 'name'},
                    { text: 'Actions', value: 'actions' },
                ],
                totalInvoices: 0,
                loading: true,
                pagination: {},
           }
        },
        mounted() {
            this.getDataFromApi()
                .then(data => {
                    this.invoices = data.items;
                    this.totalInvoices= data.total
                })
        },
        methods: {
            exportToPdf(invoice){
                //let pdfName = 'test'; 
                //var doc = new jsPDF('p','pt');
                //doc.text("Hello World", 10, 10);
                //doc.save(pdfName + '.pdf');
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
            loadInvoices() {
                return axios.get(`/invoices/paid`);
            },

        }
    }

</script>

<style scoped>

</style>