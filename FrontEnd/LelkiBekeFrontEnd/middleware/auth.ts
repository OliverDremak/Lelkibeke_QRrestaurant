import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuthStore();
  
  // Protected routes
  const protectedRoutes = ['/profile'];
  
  if (protectedRoutes.includes(to.path) && !auth.token) {
    return navigateTo('/auth');
  }
});
