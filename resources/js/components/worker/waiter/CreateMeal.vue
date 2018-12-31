<template>
    <v-dialog v-model="dialog" max-width="600px">
        <v-btn slot="activator" color="primary" dark>Create new meal</v-btn>
        <v-card>
            <v-card-title class="headline">New meal</v-card-title>
            <v-card-text>
                <v-flex xs12>
                    <v-form ref="form">
                        <v-text-field v-model="tableNumber" box ref="tableNumber"  label="Table number" 
                            type="number" name="tableNumber" :error-messages="errorMessages"
                            :rules="[() => !!tableNumber || 'This field is required']" required>
                        </v-text-field>
                    </v-form>
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
    import axios from 'axios';
    import moment from 'moment';

    export default {
        name: "CreateMeal",
        data: () => ({
            dialog: false,
            tableNumber: null,
            errorMessages: ''
        }),
        methods: {
            startMeal() {
                if (this.tableNumber == null) {
                    return;
                }

                if (this.$refs.form.validate()) {
                    let meal = {
                        table_number: this.tableNumber,
                        state: 'active',
                        start: moment().format('YYYY-MM-DD HH:mm:ss'),
                        responsible_waiter_id: this.$store.state.user.id
                    };
                    axios.post('/meals', meal)
                        .then(response => {
                            console.log(response);
                            if (response.status === 201) {
                                this.dialog = false;
                                this.$emit('onCreate', this.tableNumber);
                            }
                        })
                        .catch(error => {
                            if (error.response.status === 422) {
                                this.errorMessages = error.response.data.message;
                            } else {
                                console.log(error);
                                this.errorMessages = 'Invalid table number';
                            }
                        })
                }
            }
        }
    }
</script>

<style scoped>

</style>