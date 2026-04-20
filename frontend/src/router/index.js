import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: () => import('@/views/DashboardView.vue') },
      { path: 'assets', name: 'assets', component: () => import('@/views/assets/AssetList.vue') },
      { path: 'assets/create', name: 'assets.create', component: () => import('@/views/assets/AssetForm.vue') },
      { path: 'assets/:id/edit', name: 'assets.edit', component: () => import('@/views/assets/AssetForm.vue') },
      { path: 'assets/:id', name: 'assets.show', component: () => import('@/views/assets/AssetShow.vue') },
      { path: 'work-orders', name: 'work-orders', component: () => import('@/views/workorders/WorkOrderList.vue') },
      { path: 'work-orders/create', name: 'work-orders.create', component: () => import('@/views/workorders/WorkOrderForm.vue') },
      { path: 'work-orders/:id', name: 'work-orders.show', component: () => import('@/views/workorders/WorkOrderShow.vue') },
      { path: 'work-orders/:id/edit', name: 'work-orders.edit', component: () => import('@/views/workorders/WorkOrderForm.vue') },
      { path: 'locations', name: 'locations', component: () => import('@/views/locations/LocationList.vue') },
      { path: 'areas', name: 'areas', component: () => import('@/views/areas/AreaList.vue') },
      { path: 'contractors', name: 'contractors', component: () => import('@/views/contractors/ContractorList.vue') },
      { path: 'parts', name: 'parts', component: () => import('@/views/parts/PartList.vue') },
      { path: 'meters', name: 'meters', component: () => import('@/views/meters/MeterList.vue') },
      { path: 'users', name: 'users', component: () => import('@/views/users/UserList.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login' }
  }
  if (to.meta.guest && auth.isLoggedIn) {
    return { name: 'dashboard' }
  }
})

export default router
