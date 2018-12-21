import UserNavigation from './components/UserNavigation.vue';
import AdminNavigation from './components/AdminNavigation.vue';
import Home from './components/Home.vue';
import ItemMenu from './components/ItemMenu.vue';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard';
import Profile from './components/Profile.vue';
import Meals from './components/Meals.vue';
import RestaurantManagement from './components/RestaurantManagement.vue';
import AdminItemMenu from './components/AdminItemMenu.vue';
import MealOrders from './components/MealOrders.vue';
import UserList from './components/UserList.vue';

export default [
    { path: '/', component: UserNavigation,
        children: [
            { path: '', component: Home, name: 'home' },
            { path: 'menu', component: ItemMenu, name: 'menu' },
            { path: 'login', component: Login, name: 'login'},
        ]
    },
    { path: '/admin', component: AdminNavigation,
        children: [
            { path: '', component: Dashboard, name: 'dashboard' },
            { path: 'profile', component: Profile, name: 'profile' },
            { path: 'meals', component: Meals, name: 'meals',
                children: [
                    { path: ':mealId/orders', component: MealOrders, name: 'meal.orders' },
                ]},
            { path: 'menu', component: AdminItemMenu, name: 'worker.menu' },
            { path: 'menu/meal/:mealId', component: AdminItemMenu, name: 'menu.meal.orders' },
            { path: 'restaurantManagement', component: RestaurantManagement, name: 'restaurantManagement'},
            { path: 'users', component: UserList, name:'users'}
        ]
    }
]