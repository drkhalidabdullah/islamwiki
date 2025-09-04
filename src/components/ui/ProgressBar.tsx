import React from 'react';

interface ProgressBarProps {
  value: number;
  max: number;
  label?: string;
  showPercentage?: boolean;
  size?: 'sm' | 'md' | 'lg';
  color?: 'green' | 'blue' | 'yellow' | 'red' | 'purple';
  className?: string;
}

const ProgressBar: React.FC<ProgressBarProps> = ({
  value,
  max,
  label,
  showPercentage = true,
  size = 'md',
  color = 'green',
  className = ''
}) => {
  const percentage = Math.min((value / max) * 100, 100);
  
  const getSizeClasses = () => {
    switch (size) {
      case 'sm':
        return 'h-2';
      case 'md':
        return 'h-3';
      case 'lg':
        return 'h-4';
      default:
        return 'h-3';
    }
  };

  const getColorClasses = () => {
    switch (color) {
      case 'green':
        return 'bg-green-500';
      case 'blue':
        return 'bg-blue-500';
      case 'yellow':
        return 'bg-yellow-500';
      case 'red':
        return 'bg-red-500';
      case 'purple':
        return 'bg-purple-500';
      default:
        return 'bg-green-500';
    }
  };

  const getBackgroundColorClasses = () => {
    switch (color) {
      case 'green':
        return 'bg-green-100';
      case 'blue':
        return 'bg-blue-100';
      case 'yellow':
        return 'bg-yellow-100';
      case 'red':
        return 'bg-red-100';
      case 'purple':
        return 'bg-purple-100';
      default:
        return 'bg-green-100';
    }
  };

  return (
    <div className={className}>
      {label && (
        <div className="flex justify-between items-center mb-2">
          <span className="text-sm font-medium text-gray-700">{label}</span>
          {showPercentage && (
            <span className="text-sm text-gray-500">{Math.round(percentage)}%</span>
          )}
        </div>
      )}
      <div className={`w-full ${getBackgroundColorClasses()} rounded-full overflow-hidden`}>
        <div
          className={`${getSizeClasses()} ${getColorClasses()} rounded-full transition-all duration-300 ease-out`}
          style={{ width: `${percentage}%` }}
        />
      </div>
    </div>
  );
};

export default ProgressBar; 