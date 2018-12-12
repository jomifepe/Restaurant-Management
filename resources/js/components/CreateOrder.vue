<template>
    <v-dialog v-model="dialog" max-width="600px">
        <v-btn slot="activator" color="primary" dark>Create new order for meal {{ meal.id }}</v-btn>
        <v-card>
            <v-card-title class="headline">New meal</v-card-title>
            <v-card-text>
                <v-flex xs12>

                </v-flex>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat @click="dialog = false">Cancel</v-btn>
                <v-btn flat color="primary" @click="startMeal">Start</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        name: "CreateOrder",
        props: {
            meal: null
        }
    }
</script>

<style scoped>

</style>