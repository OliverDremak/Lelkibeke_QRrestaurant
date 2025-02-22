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
            // Client-side password hashing
            console.log(email, password)
            //const hashedPassword = CryptoJS.SHA256(password).toString();
            const { token, user } = await $fetch<AuthResponse>('http://localhost:8000/api/login', {
              method: 'POST',
              body: { 
                email,
                password : password
              }
            })
            
            this.token = token
            this.user = user
            localStorage.setItem('token', token)
          } catch (error: any) {
            this.error = error.response?.data?.message || 'Login failed'
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
          const { token, user } = await $fetch<AuthResponse>('http://localhost:8000/api/register', {
            method: 'POST',
            body: { name, email, password }
          })
          
          this.token = token
          this.user = user
          console.log(token)
          localStorage.setItem('token', token)
        } catch (error: any) {
          this.error = error.response?.data?.message || 'Registration failed'
        } finally {
          this.loading = false
        }
      },
  
      logout() {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        window.location.reload()
      },
  
      async checkAuth() {
        const token = localStorage.getItem('token')
        if (token) {
          try {
            // Add user type assertion
            const user = await $fetch<User>('/api/auth/me', {
              headers: { Authorization: `Bearer ${token}` }
            })
            
            this.token = token
            this.user = user
          } catch {
            this.logout()
          }
        }
      }
    }
  })