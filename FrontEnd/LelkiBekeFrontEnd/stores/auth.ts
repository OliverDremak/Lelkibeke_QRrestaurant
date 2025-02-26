import { defineStore } from "pinia";
import type User from "~/types/User";
import type AuthResponse from "~/types/AuthResponse";
import type AuthState from "~/types/AuthState";

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
      user: null,
      token: null,
      loading: false,
      error: null
    }),
    actions: {
      async login(email: string, password: string) {
          this.loading = true
          this.error = null
          try {
            const response = await $fetch<AuthResponse>('https://bgs.jedlik.eu/innerpeace/backend/api/login', {
              method: 'POST',
              body: { 
                email,
                password
              }
            })
            
            console.log('Login API response:', response) // Debug full response
            
            // Make sure we're storing the token from the correct response structure
            if (response.token) {
              this.token = response.token
              this.user = response.user
              
              // Normalize and store role consistently
              const userRole = response.user?.role?.toLowerCase().trim() || null
              console.log('Normalized user role:', userRole)
              
              // Use consistent keys across the application
              localStorage.setItem('auth_token', response.token) // Changed from 'token' to 'auth_token'
              localStorage.setItem('user', JSON.stringify(response.user))
              localStorage.setItem('user_role', userRole || '')
              
              console.log('Auth data stored in localStorage:', {
                token: !!response.token,
                user: !!response.user,
                role: userRole
              })
              return true
            } else {
              console.error('No token in response:', response)
              throw new Error('No token received from server')
            }
          } catch (error: any) {
            console.error('Login error:', error) // Debug log
            
            // Handle different error formats
            if (error.response?.data?.message) {
              this.error = error.response.data.message
            } else if (typeof error.data === 'object' && error.data?.message) {
              this.error = error.data.message
            } else if (typeof error.message === 'string') {
              this.error = error.message
            } else {
              this.error = 'Login failed'
            }
            
            // Translate common error messages
            if (this.error === 'Hibás e-mail vagy jelszó') {
              this.error = 'Invalid email or password'
            }
            
            return false
          } finally {
            this.loading = false
          }
      },
  
      async register(name: string, email: string, password: string) {
        this.loading = true
        this.error = null
        try {
          // Add type assertion here as well
          //const hashedPassword = VueCryptojs.CryptoJS.SHA256(password).toString();
          const response = await $fetch<AuthResponse>('https://bgs.jedlik.eu/innerpeace/backend/api/register', {
            method: 'POST',
            body: { name, email, password }
          })
          
          if (response.token) {
            this.token = response.token
            this.user = response.user
            localStorage.setItem('token', response.token)
            console.log('Token stored after registration:', response.token)
            return true
          }
        } catch (error: any) {
          console.error('Registration error:', error)
          this.error = error.response?.data?.message || 'Registration failed'
          return false
        } finally {
          this.loading = false
        }
      },
  
      logout() {
        console.log("Auth store logout called");
        this.token = null;
        this.user = null;
        
        // Clear all auth-related storage consistently
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('user_role');
        localStorage.removeItem('token');  // Also clear any legacy keys
        
        console.log('Auth store: Logged out, all auth data cleared');
        
        // Don't use window.location.reload() as it interrupts navigation
        // Instead, let the component handle navigation after this method completes
      },
  
      async checkAuth() {
        const token = localStorage.getItem('auth_token') // Changed from 'token' to 'auth_token'
        const userStr = localStorage.getItem('user')
        
        if (token && userStr) {
          try {
            // First try to load user from localStorage
            this.token = token
            this.user = JSON.parse(userStr)
            
            // Ensure user_role is also set
            if (this.user?.role) {
              localStorage.setItem('user_role', this.user.role.toLowerCase().trim())
            }
            
            // Optionally verify with backend
            // const user = await $fetch<User>('/api/auth/me', {
            //   headers: { Authorization: `Bearer ${token}` }
            // })
            // this.user = user
          } catch {
            this.logout()
          }
        }
      }
    },
    getters: {
      // Add a getter for user role
      userRole: (state) => state.user?.role || null
    }
  })