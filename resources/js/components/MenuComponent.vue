<template>
    <div class="col-md-12 mt-4 mb-4">
        <div class="row justify-content-center">
            <div class="card-columns">
                <template v-for="item in items">
                    <menu-card class="animated fadeIn" :item="item"></menu-card>
                </template>
            </div>
        </div>

        <!--<div class="container mt-4">-->
            <!--<div id="products" class="row view-group">-->
                <!--<div class="item col-xs-3 col-lg-3" v-for="item in items" :key="item.id">-->
                    <!--<div class="thumbnail card">-->
                        <!--<div class="img-event img-wrapper">-->
                            <!--<img class="card-img-top img-fluid" :src="item.photo_url" :alt="item.name"/>-->
                        <!--</div>-->
                        <!--<div class="caption card-body">-->
                            <!--<h4 class="group card-title inner list-group-item-heading">{{item.name}}</h4>-->
                            <!--<h6 class="group card-title inner list-group-item-heading">{{item.type}}</h6>-->
                            <!--&lt;!&ndash; <p class="group inner list-group-item-text" @click.prevent="toggleDescription">Show Description</p>&ndash;&gt;-->
                            <!--<p class="group inner list-group-item-text" v-if="showDescription">{{item.description}}</p>-->
                            <!--<div class="row card-footer text-muted">-->
                                <!--<div class="col-xs-12 col-md-6">-->
                                    <!--<p class="lead">{{item.price}}€</p>-->
                                <!--</div>-->
                                <!--<div class="col-xs-12 col-md-6">-->
                                    <!--&lt;!&ndash;<a class="btn btn-success" href="#">Add to cart</a>&ndash;&gt;-->
                                    <!--<button type="button" class="btn btn-info" @click.prevent="toggleDescription">Description</button>-->
                                <!--</div>-->
                            <!--</div>-->
                        <!--</div>-->
                    <!--</div>-->
                <!--</div>-->
            <!--</div>-->
        <!--</div>-->
        <div class="align-content-center">
            <div class="align-content-center">
                <button class="btn btn-primary"
                        @click.prevent="fetchPaginationItems(pagination.first_page_url)"
                        :disabled="!pagination.prev_page_url">
                    <i class="fas fa-angle-double-left"></i>
                </button>
                <button class="btn btn-primary"
                        @click.prevent="fetchPaginationItems(pagination.prev_page_url)"
                        :disabled="!pagination.prev_page_url">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-primary"
                        @click.prevent="fetchPaginationItems(pagination.next_page_url)"
                        :disabled="!pagination.next_page_url">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button class="btn btn-primary"
                        @click.prevent="fetchPaginationItems(pagination.last_page_url)"
                        :disabled="!pagination.last_page_url">
                    <i class="fas fa-angle-double-right"></i>
                </button>
                <div>
                    <span>Page {{pagination.current_page}} of {{pagination.last_page}} </span>
                </div>
                <div>
                    <span> From {{pagination.from}} of {{pagination.to}} of total {{pagination.total}}</span>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import MenuCard from './MenuCard';

    export default {
        components: {
            'menu-card': MenuCard
        },
        data() {
            return {
                items: [],
                url: 'items',
                pagination: {},

            }
        },
        methods: {
            getItems() {
                axios.get(this.url)
                    .then(response => {
                        this.items = response.data.data;
                        this.makePagination(response.data);
                    });
            },
            makePagination(data) {
                let pagination = {
                    first_page_url:data.links.first,
                    last_page_url: data.links.last,
                    next_page_url: data.links.next,
                    prev_page_url: data.links.prev,
                    current_page: data.meta.current_page,
                    last_page: data.meta.last_page,
                    from: data.meta.from,
                    to: data.meta.to,
                    total: data.meta.total,
                };
                this.pagination=pagination;
            },
            fetchPaginationItems(url){
                this.url = url;
                this.getItems();
            }
        },
        mounted() {
            this.getItems();
        }
    }
</script>



<style>
    /*.thumbnail*/
    /*{*/
        /*margin-bottom: 20px;*/
        /*padding: 0px;*/
        /*-webkit-border-radius: 0px;*/
        /*-moz-border-radius: 0px;*/
        /*border-radius: 0px;*/
    /*}*/


    /*.item.list-group-item .list-group-image*/
    /*{*/
        /*margin-right: 10px;*/
    /*}*/
    /*.item.list-group-item .thumbnail*/
    /*{*/
        /*margin-bottom: 0px;*/
    /*}*/
    /*.item.list-group-item .caption*/
    /*{*/
        /*padding: 9px 9px 0px 9px;*/
    /*}*/
    /*.item.list-group-item img*/
    /*{*/
        /*float: left;*/
    /*}*/

    /*.img-wrapper {*/
        /*position: relative;*/
        /*padding-bottom: 100%;*/
        /*overflow: hidden;*/
        /*width: 100%;*/
    /*}*/
    /*.img-wrapper img {*/
        /*position: absolute;*/
        /*top:0;*/
        /*left:0;*/
        /*width:100%;*/
        /*height:100%;*/
    /*}*/

    /*.list-group-item-text*/
    /*{*/
        /*margin: 0 0 11px;*/
    /*}*/
</style>
