import UserNavigation from './components/UserNavigation.vue';
import AdminNavigation from './components/AdminNavigation.vue';
import Home from './components/Home.vue';
import ItemMenu from './components/ItemMenu.vue';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';
import Profile from './components/Profile.vue';
import Meals from './components/Meals.vue';
import Orders from './components/Orders.vue';
import Tables from './components/Tables.vue';
import AdminItemMenu from './components/AdminItemMenu.vue';
import MealOrders from './components/MealOrders.vue';
import UserList from './components/UserList.vue';
import Invoices from './components/Invoices.vue';
import InvoiceDetails from './components/InvoiceDetails.vue';


export default [
    {
        path: '/',
        component: UserNavigation,
        children: [
            {
                path: '',
                component: Home,
                name: 'home'
            },
            {
                path: 'menu',
                component: ItemMenu,
                name: 'menu'
            },
            {
                path: 'login',
                component: Login,
                name: 'login'
            },
        ]
    },
    {
        path: '/admin',
        component: AdminNavigation,
        meta: {
            requiresAuth: true,
            allowed: true
        },
        children: [
            {
                path: '',
                component: Dashboard,
                name: 'dashboard',
                meta: {
                    allowed: true
                }
            },
            {
                path: 'profile',
                component: Profile,
                name: 'profile',
                meta: {
                    allowed: true
                }
            },
            {
                path: 'meals',
                component: Meals,
                name: 'meals',
                meta: {
                    allowed: ['waiter', 'cook', 'manager']
                },
                children: [{
                    path: ':mealId/orders',
                    component: MealOrders,
                    name: 'meal.orders',
                    meta: {
                        allowed: ['waiter', 'cook', 'manager']
                    }
                }]
            },
            {
                path: 'menu',
                component: AdminItemMenu,
                name: 'worker.menu',
                meta: {
                    allowed: true
                }
            },
            {
                path: 'menu/meal/:mealId',
                component: AdminItemMenu,
                name: 'menu.meal.orders',
                meta: {
                    allowed: ['waiter', 'manager']
                }
            },
            {
                path: 'orders',
                component: Orders,
                name: 'orders',
                meta: {
                    allowed: ['cook', 'manager']
                }
            },
            {
                path: 'tables',
                component: Tables,
                name: 'tables',
                meta: {
                    allowed: ['manager']
                }
            },
            {
                path: 'invoices',
                component: Invoices,
                name: 'pending.invoices',
                meta: {
                    allowed: ['cashier', 'manager']
                },
                children: [
                    {
                        path: ':invoiceId/details',
                        component: InvoiceDetails,
                        name: 'invoices.details',
                        meta: {
                            allowed: ['cashier', 'manager']
                        }
                    }
                ]
            },
            {
                path: 'users',
                component: UserList,
                name: 'users',
                meta: {
                    allowed: ['manager']
                }
            },
            {
                path: '*',
                redirect: {
                    name: 'dashboard'
                }
            }
        ]
    },
    {
        path: '*',
        redirect: {
            name: 'home'
        }
    }
]
