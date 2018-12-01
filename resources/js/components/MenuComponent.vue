<template>
    <!--<div class="col-xs-12 col-sm-6 col-md-12">
        <div class="row justify-content-center">
            <div class="card-columns">
                <div class="card" v-for="item in items" :key="item.id">
                    <img class="card-img-top img-fluid" :src="item.photo_url" :alt="item.name"/>
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
    </div>-->
    <div class="container">
        <div id="products" class="row view-group">
            <div class="item col-xs-4 col-lg-4" v-for="item in items" :key="item.id">
                <div class="thumbnail card">
                    <div class="img-event img-wrapper">
                        <img class="card-img-top img-fluid" :src="item.photo_url" :alt="item.name"/>
                    </div>
                    <div class="caption card-body">
                        <h4 class="group card-title inner list-group-item-heading">{{item.name}}</h4>
                        <h6 class="group card-title inner list-group-item-heading">{{item.type}}</h6>
                        <!-- <p class="group inner list-group-item-text" @click.prevent="toggleDescription">Show Description</p>-->
                        <p class="group inner list-group-item-text" v-if="showDescription">{{item.description}}</p>
                        <div class="row card-footer text-muted">
                            <div class="col-xs-12 col-md-6">
                                <p class="lead">{{item.price}}€</p>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <!--<a class="btn btn-success" href="#">Add to cart</a>-->
                                <button type="button" class="btn btn-info" @click.prevent="toggleDescription">Description</button>
                            </div>
                        </div>
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
                items: [],
                showDescription:false,
            }
        },
        methods: {
            getItems() {
                axios.get("items")
                    .then(response => {
                        this.items = response.data.data;
                    });
            },
            toggleDescription(){
                this.showDescription == true? this.showDescription=false : this.showDescription=true;
            }
        },
        mounted() {
            this.getItems();
        }
    }
</script>



<style>
    .thumbnail
    {
        margin-bottom: 20px;
        padding: 0px;
        -webkit-border-radius: 0px;
        -moz-border-radius: 0px;
        border-radius: 0px;
    }


    .item.list-group-item .list-group-image
    {
        margin-right: 10px;
    }
    .item.list-group-item .thumbnail
    {
        margin-bottom: 0px;
    }
    .item.list-group-item .caption
    {
        padding: 9px 9px 0px 9px;
    }
    .item.list-group-item img
    {
        float: left;
    }

    .img-wrapper {
        position: relative;
        padding-bottom: 100%;
        overflow: hidden;
        width: 100%;
    }
    .img-wrapper img {
        position: absolute;
        top:0;
        left:0;
        width:100%;
        height:100%;
    }

    .list-group-item-text
    {
        margin: 0 0 11px;
    }
</style>
