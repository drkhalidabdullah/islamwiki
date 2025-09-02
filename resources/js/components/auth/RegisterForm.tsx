import React, { useState } from 'react';
import Button from '../ui/Button';
import Input from '../ui/Input';

const RegisterForm: React.FC = () => {
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    first_name: '',
    last_name: ''
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isLoading, setIsLoading] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.username.trim()) {
      newErrors.username = 'Username is required';
    } else if (formData.username.length < 3) {
      newErrors.username = 'Username must be at least 3 characters';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }

    if (!formData.password) {
      newErrors.password = 'Password is required';
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters';
    }
    
    if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match';
    }

    if (!formData.first_name.trim()) {
      newErrors.first_name = 'First name is required';
    }

    if (!formData.last_name.trim()) {
      newErrors.last_name = 'Last name is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }
    
    setIsLoading(true);
    
    try {
      // TODO: Implement actual registration API call
      console.log('Registration data:', formData);
      
      // Simulate API call delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // TODO: Handle successful registration
      console.log('Registration successful!');
      
    } catch (error) {
      if (error instanceof Error) {
        setErrors({ general: error.message });
      } else {
        setErrors({ general: 'An unexpected error occurred' });
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
      <h2 className="text-2xl font-bold text-center text-gray-800 mb-6">
        Create Your Account
          </h2>

      {errors.general && (
        <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
          {errors.general}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
              <Input
                label="First Name"
                type="text"
            name="first_name"
                value={formData.first_name}
            onChange={handleChange}
            error={errors.first_name}
                placeholder="Enter your first name"
                required
              />
          
              <Input
                label="Last Name"
                type="text"
            name="last_name"
                value={formData.last_name}
            onChange={handleChange}
            error={errors.last_name}
                placeholder="Enter your last name"
                required
              />
          </div>

            <Input
              label="Username"
              type="text"
          name="username"
              value={formData.username}
          onChange={handleChange}
          error={errors.username}
              placeholder="Choose a username"
              required
            />

            <Input
          label="Email"
              type="email"
          name="email"
              value={formData.email}
          onChange={handleChange}
          error={errors.email}
          placeholder="Enter your email"
              required
            />

            <Input
              label="Password"
              type="password"
          name="password"
              value={formData.password}
          onChange={handleChange}
          error={errors.password}
          placeholder="Create a password"
              required
            />

            <Input
              label="Confirm Password"
              type="password"
          name="password_confirmation"
              value={formData.password_confirmation}
          onChange={handleChange}
          error={errors.password_confirmation}
              placeholder="Confirm your password"
                required
              />

          <Button
            type="submit"
            variant="primary"
            size="lg"
          loading={isLoading}
            className="w-full"
          >
            {isLoading ? 'Creating Account...' : 'Create Account'}
          </Button>
        </form>

      <div className="mt-4 text-center">
        <p className="text-sm text-gray-600">
            Already have an account?{' '}
          <a href="/login" className="text-blue-600 hover:text-blue-800 font-medium">
            Sign in here
          </a>
        </p>
      </div>
    </div>
  );
}; 

export default RegisterForm; 