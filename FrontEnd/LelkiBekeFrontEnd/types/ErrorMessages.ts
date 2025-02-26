const errorTranslations: Record<string, string> = {
  'Hibás e-mail vagy jelszó': 'Invalid email or password',
  'The email field is required': 'Email is required',
  'The password field is required': 'Password is required',
  'A user with that email already exists': 'A user with that email already exists',
  'Server error': 'Server error, please try again later'
};

export const translateError = (error: string): string => {
  return errorTranslations[error] || error;
};

export default errorTranslations;
