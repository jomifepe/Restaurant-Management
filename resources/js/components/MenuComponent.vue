<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="card-columns">
                <div class="card" v-for="item in items" :key="item.id">  
                    <img class="card-img-top img-fluid" :src="item.photo_url" :alt="item.name" />
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ item.name }}
                            <small class="text-muted">{{ item.type }}</small>
                        </h5>
                    </div>
                    <div class="card-footer text-muted">
                        <p class="text-right">
                            {{ item.price }}€
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
		data() {
            return {
			    items: []
            }
		},
        methods: {
            getItems() {
                axios.get("items")
                .then(response => {
					this.items = response.data.data;
                });
            },
		},
        mounted() {
            this.getItems();
        }
    }
</script>
