<template>
    <div class="text-xs-center">
        <div v-if="this.$store.state.user.type === 'manager'">
            <v-btn dark class="mb-2" color="primary" @click.prevent="showForm = true">
                New Item
            </v-btn>
        </div>
            <item-form :showDialog="showForm" :show@onGetItems="onGetItems()" 
                @onCloseForm="closeForm()"></item-form>
    </div>
</template>

<script>
    import ItemForm from './ItemForm';

    export default {
        name: "NewItem",
        components:{
            ItemForm
        },
        data () {
            return {
                showForm: false,
            }
        },
        methods:{
            closeForm(){
                this.showForm = false;
            },
            onGetItems(){
                this.$emit('onGetItems');
            }
        }
    }
</script>

<style scoped>

</style>