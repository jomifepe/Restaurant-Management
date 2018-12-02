<template>
    <div class="card">
        <img class="card-img-top img-fluid" :src="item.photo_url" :alt="item.name"/>
        <div class="card-body">
            <h5 class="card-title">
                {{ item.name }}
                <small class="text-muted">{{ item.type }}</small>
            </h5>
            <p class="group inner list-group-item-text" v-if="showDescription">
                {{ item.description }}
            </p>
            <div class="col-xs-12 col-md-6">
                <button type="button" class="btn btn-info btn-sm"
                        @click.prevent="toggleDescription">Description</button>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="col-xs-12 col-md-6">
                {{ item.price }}€
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            item: null
        },
        data() {
            return {
                showDescription: false
            }
        },
        methods: {
            toggleDescription() {
                this.showDescription = !this.showDescription;
            },
        }
    }
</script>

<style scoped>

</style>