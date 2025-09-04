import React, { forwardRef } from 'react';
import { clsx } from 'clsx';

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  variant?: 'default' | 'success' | 'error';
}

const Input = forwardRef<HTMLInputElement, InputProps>(({
  label,
  error,
  helperText,
  leftIcon,
  rightIcon,
  variant = 'default',
  className,
  id,
  ...props
}, ref) => {
  const inputId = id || `input-${Math.random().toString(36).substr(2, 9)}`;
  
  const baseClasses = 'block w-full px-3 py-2 border rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-0 transition-colors duration-200';
  
  const variantClasses = {
    default: 'border-gray-300 focus:border-green-500 focus:ring-green-500',
    success: 'border-green-300 focus:border-green-500 focus:ring-green-500',
    error: 'border-red-300 focus:border-red-500 focus:ring-red-500'
  };
  
  const inputClasses = clsx(
    baseClasses,
    variantClasses[variant],
    leftIcon && 'pl-10',
    rightIcon && 'pr-10',
    className
  );
  
  const labelClasses = clsx(
    'block text-sm font-medium text-gray-700 mb-1',
    error && 'text-red-600'
  );
  
  const helperClasses = clsx(
    'mt-1 text-sm',
    error ? 'text-red-600' : 'text-gray-500'
  );
  
  return (
    <div className="w-full">
      {label && (
        <label htmlFor={inputId} className={labelClasses}>
          {label}
        </label>
      )}
      
      <div className="relative">
        {leftIcon && (
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <div className="h-5 w-5 text-gray-400">
              {leftIcon}
            </div>
          </div>
        )}
        
        <input
          ref={ref}
          id={inputId}
          className={inputClasses}
          {...props}
        />
        
        {rightIcon && (
          <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <div className="h-5 w-5 text-gray-400">
              {rightIcon}
            </div>
          </div>
        )}
      </div>
      
      {(error || helperText) && (
        <p className={helperClasses}>
          {error || helperText}
        </p>
      )}
    </div>
  );
});

Input.displayName = 'Input';

export default Input; 