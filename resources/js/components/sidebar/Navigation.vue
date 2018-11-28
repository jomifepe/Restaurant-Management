<template>

    <sidebar-menu :menu="menu"></sidebar-menu>
</template>


<script>
    module.exports = {
        data() {
            return {
                menu: [
                    {
                        href: '/',
                        title: 'Dashboard',
                        icon: 'fa fa-user'
                    },
                    {
                        href: '#',
                        title: 'Charts',
                        icon: 'fa fa-chart-area'
                    },
                ]
            }
        }
    }
</script>