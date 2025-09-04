import React from 'react';
import { clsx } from 'clsx';

export interface CardProps {
  children: React.ReactNode;
  className?: string;
  padding?: 'none' | 'sm' | 'md' | 'lg';
  shadow?: 'none' | 'sm' | 'md' | 'lg';
  border?: boolean;
  hover?: boolean;
}

const Card: React.FC<CardProps> = ({
  children,
  className,
  padding = 'md',
  shadow = 'sm',
  border = true,
  hover = false
}) => {
  const paddingClasses = {
    none: '',
    sm: 'p-3',
    md: 'p-6',
    lg: 'p-8'
  };
  
  const shadowClasses = {
    none: '',
    sm: 'shadow-sm',
    md: 'shadow-md',
    lg: 'shadow-lg'
  };
  
  const borderClasses = border ? 'border border-gray-200' : '';
  const hoverClasses = hover ? 'hover:shadow-md transition-shadow duration-200' : '';
  
  const classes = clsx(
    'bg-white rounded-lg',
    paddingClasses[padding],
    shadowClasses[shadow],
    borderClasses,
    hoverClasses,
    className
  );
  
  return (
    <div className={classes}>
      {children}
    </div>
  );
};

export default Card; 