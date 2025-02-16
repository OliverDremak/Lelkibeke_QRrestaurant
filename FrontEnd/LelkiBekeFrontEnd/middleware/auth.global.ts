import { useAuthStore } from "~/stores/auth";

export default defineNuxtRouteMiddleware((to) => {
    const auth = useAuthStore()
    
    // Auto-check auth state on first load
    if (typeof window !== 'undefined' && !auth.user) {
      auth.checkAuth()
    }
  
    // Redirect to login if not authenticated
    if (to.meta.requiresAuth && !auth.user) {
      return navigateTo('/auth')
    }
  
    // Redirect to home if already authenticated
    if (to.path === '/auth' && auth.user) {
      return navigateTo('/')
    }
  })
