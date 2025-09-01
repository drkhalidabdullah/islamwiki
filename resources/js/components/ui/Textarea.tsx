import React from 'react';

interface TextareaProps {
  value: string;
  onChange: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
  placeholder?: string;
  rows?: number;
  className?: string;
  disabled?: boolean;
  required?: boolean;
  name?: string;
  id?: string;
}

const Textarea: React.FC<TextareaProps> = ({
  value,
  onChange,
  placeholder,
  rows = 4,
  className = '',
  disabled = false,
  required = false,
  name,
  id
}) => {
  return (
    <textarea
      value={value}
      onChange={onChange}
      placeholder={placeholder}
      rows={rows}
      name={name}
      id={id}
      required={required}
      disabled={disabled}
      className={`
        w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
        placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500
        disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed
        ${className}
      `}
    />
  );
};

export default Textarea; 