// resources/js/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import routes, { adminRoutes } from '@/routes/index'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Dynamically add admin routes after authentication
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.initialized) {
    await authStore.checkAuth()
  }

  const user = authStore.user

  // Add admin routes if user is superadmin or admin
  if (user && ['superadmin', 'admin'].includes(user.role)) {
    adminRoutes.forEach(route => {
      if (!router.hasRoute(route.name as string)) {
        router.addRoute(route)
      }
    })
  }

  next()
})

export default router
