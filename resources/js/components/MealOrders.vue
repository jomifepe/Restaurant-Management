<template>
    <v-flex v-if="meal != null">
        <v-toolbar flat color="gray">
            <v-toolbar-title>Orders for table {{ meal.table_number }} (meal #{{ meal.id }})</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn v-if="meal.state === 'active'" color="success">Order new item</v-btn>
        </v-toolbar>
        <v-data-iterator row wrap :items="items"
                         :rows-per-page-items="rowsPerPageItems"
                         :pagination.sync="pagination"
                         content-tag="v-layout">
            <v-flex slot="item" slot-scope="props"
                    xs12 sm6 md4 lg3>
                <v-card>
                    <v-img :src="getItemPhotoUrl(props.item.photo_url)"
                           aspect-ratio="1.75"
                    ></v-img>
                    <v-card-title><h4>{{ props.item.name }}</h4></v-card-title>
                    <v-divider></v-divider>
                    <v-list dense>
                        <v-list-tile>
                            <v-list-tile-content>Type:</v-list-tile-content>
                            <v-list-tile-content class="align-end">{{ props.item.type }}</v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>Price:</v-list-tile-content>
                            <v-list-tile-content class="align-end">{{ props.item.price }}€</v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>Order time:</v-list-tile-content>
                            <v-list-tile-content class="align-end">{{ props.item.order_created_at }}</v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>Order end:</v-list-tile-content>
                            <v-list-tile-content class="align-end">{{ props.item.order_end }}</v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>Order state:</v-list-tile-content>
                            <v-list-tile-content class="align-end">{{ props.item.order_state }}</v-list-tile-content>
                        </v-list-tile>
                    </v-list>
                </v-card>
            </v-flex>
        </v-data-iterator>
    </v-flex>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "MealOrdersPreview",
        props: {
            meal: null
        },
        data: () => ({
            items: [],
            rowsPerPageItems: [4, 8, 12],
            pagination: {
                rowsPerPage: 4
            },
        }),
        watch: {
            meal() {
                this.loadMealItems();
            }
        },
        methods: {
            loadMealItems() {
                axios.get(`/meals/${this.meal.id}/items`)
                    .then(response => {
                        if (response.status === 200) {
                            console.log(response);
                            this.items = response.data;
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            getItemPhotoUrl(filename) {
                return `/storage/items/${filename}`;
            }
        },
        mounted() {
            this.loadMealItems();
        }
    }
</script>

<style scoped>

</style>