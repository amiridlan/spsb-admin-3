// resources/js/routes/index.ts
import { RouteRecordRaw } from 'vue-router'

const publicRoutes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/Login.vue')
  }
]

// Protected admin routes
const adminRoutes: RouteRecordRaw[] = [
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/Register.vue'),
    meta: { requiredRoles: ['superadmin', 'admin'] }
  }
]

export default [...publicRoutes]
export { adminRoutes }
