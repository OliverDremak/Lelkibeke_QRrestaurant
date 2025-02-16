import { useCookie } from '#app';

const API_URL = 'http://127.0.0.1:8000';

export const useApi = () => {
    const token = useCookie('auth_token');
  
    const login = async (email: string, password: string) => {
      try {
        const response: any = await $fetch(`${API_URL}/login`, {
          method: 'POST',
          body: { email, password },
        });
        token.value = response.token;
        return response;
      } catch (error) {
        console.error('Login failed:', error);
        throw error;
      }
    };
  
    const register = async (name: string, email: string, password: string) => {
      try {
        const response: any = await $fetch(`${API_URL}/register`, {
          method: 'POST',
          body: { name, email, password },
        });
        return response;
      } catch (error) {
        console.error('Registration failed:', error);
        throw error;
      }
    };
  
    return { login, register };
  };
  
