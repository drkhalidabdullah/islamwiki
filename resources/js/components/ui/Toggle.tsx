import React from 'react';
import { cn } from '../../utils/cn';

interface ToggleProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  disabled?: boolean;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
  label?: string;
  description?: string;
}

const Toggle: React.FC<ToggleProps> = ({
  checked,
  onChange,
  disabled = false,
  size = 'md',
  className,
  label,
  description
}) => {
  const sizeClasses = {
    sm: 'w-9 h-5',
    md: 'w-11 h-6',
    lg: 'w-14 h-7'
  };

  const thumbSizeClasses = {
    sm: 'w-3 h-3',
    md: 'w-5 h-5',
    lg: 'w-6 h-6'
  };

  const thumbTranslateClasses = {
    sm: 'translate-x-4',
    md: 'translate-x-5',
    lg: 'translate-x-7'
  };

  return (
    <div className={cn('flex items-center justify-between', className)}>
      {(label || description) && (
        <div className="flex-1 mr-4">
          {label && (
            <p className="text-sm font-medium text-gray-700">{label}</p>
          )}
          {description && (
            <p className="text-sm text-gray-500">{description}</p>
          )}
        </div>
      )}
      
      <label className={cn(
        'relative inline-flex items-center cursor-pointer',
        disabled && 'cursor-not-allowed opacity-50'
      )}>
        <input
          type="checkbox"
          checked={checked}
          onChange={(e) => onChange(e.target.checked)}
          disabled={disabled}
          className="sr-only peer"
        />
        <div className={cn(
          'bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer transition-colors duration-200',
          checked ? 'bg-green-600' : 'bg-gray-200',
          sizeClasses[size]
        )}>
          <div className={cn(
            'bg-white border border-gray-300 rounded-full transition-transform duration-200 ease-in-out',
            thumbSizeClasses[size],
            checked ? thumbTranslateClasses[size] : 'translate-x-0'
          )} />
        </div>
      </label>
    </div>
  );
};

export default Toggle; 