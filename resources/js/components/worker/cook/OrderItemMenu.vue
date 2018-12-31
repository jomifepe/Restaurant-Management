<template>
    <div>
        <v-toolbar light tabs>
            <v-text-field append-icon="mic"
                          class="mx-3"
                          flat
                          label="Search"
                          prepend-inner-icon="search"
                          solo-inverted></v-text-field>

            <v-tabs slot="extension"
                    v-model="tab"
                    centered
                    color="transparent"
                    slider-color="white">
                <v-tab v-for="(item, i) in tabs" :key="i">
                    {{ item }}
                </v-tab>
            </v-tabs>
        </v-toolbar>

        <v-tabs-items v-model="tab">
            <v-tab-item v-for="(itemFromType, i) in items" :key="i">
                <v-layout row wrap>
                    <v-flex xs3 v-for="item in itemFromType" :key="item.id">
                        <v-card>
                            <v-img :src="item.photo_url"
                                aspect-ratio="1.75"
                            ></v-img>
                            <v-card-title><h4>{{ item.name }}</h4></v-card-title>
                            <v-divider></v-divider>
                            <v-list dense>
                                <v-list-tile>
                                    <v-list-tile-content>Type:</v-list-tile-content>
                                    <v-list-tile-content class="align-end">{{ item.type }}</v-list-tile-content>
                                </v-list-tile>
                                <v-list-tile>
                                    <v-list-tile-content>Price:</v-list-tile-content>
                                    <v-list-tile-content class="align-end">{{ item.price }}€</v-list-tile-content>
                                </v-list-tile>
                            </v-list>
                        </v-card>
                    </v-flex>
                </v-layout>
            </v-tab-item>
        </v-tabs-items>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "OrderItemMenu",
        props: ['meal'],
        data: () => ({
            tab: null,
            tabs: ['dish', 'drink'],
            items: [],
            rowsPerPageItems: [4, 8, 12],
            pagination: {
                rowsPerPage: 4
            },
        }),
        methods: {
            getItems() {
                axios.get(`/items/type/dish`)
                    .then(response => {
                        this.items.push(response.data.data);
                        axios.get(`/items/type/drink`)
                            .then(response => this.items.push(response.data.data))
                            .catch(error => {
                                console.log(error);
                            })
                    })
                    .catch(error => {
                        console.log(error);
                    })
                
            }
        },
        mounted() {
            this.getItems();
        }
    }
</script>

<style scoped>

</style>