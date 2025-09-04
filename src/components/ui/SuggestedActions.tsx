import React from 'react';
import { Link } from 'react-router-dom';

interface SuggestedAction {
  action: string;
  description: string;
  priority: 'high' | 'medium' | 'low';
  url?: string;
}

interface SuggestedActionsProps {
  actions: SuggestedAction[];
  maxItems?: number;
  className?: string;
}

const SuggestedActions: React.FC<SuggestedActionsProps> = ({ 
  actions, 
  maxItems = 5, 
  className = '' 
}) => {
  const getPriorityColor = (priority: SuggestedAction['priority']) => {
    switch (priority) {
      case 'high':
        return 'border-red-200 bg-red-50';
      case 'medium':
        return 'border-yellow-200 bg-yellow-50';
      case 'low':
        return 'border-blue-200 bg-blue-50';
      default:
        return 'border-gray-200 bg-gray-50';
    }
  };

  const getPriorityIcon = (priority: SuggestedAction['priority']) => {
    switch (priority) {
      case 'high':
        return (
          <svg className="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        );
      case 'medium':
        return (
          <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      case 'low':
        return (
          <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      default:
        return (
          <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
    }
  };

  const getPriorityText = (priority: SuggestedAction['priority']) => {
    switch (priority) {
      case 'high':
        return 'High Priority';
      case 'medium':
        return 'Medium Priority';
      case 'low':
        return 'Low Priority';
      default:
        return 'Priority';
    }
  };

  const displayActions = actions.slice(0, maxItems);

  return (
    <div className={`space-y-3 ${className}`}>
      {displayActions.map((action, index) => (
        <div 
          key={index} 
          className={`p-4 rounded-lg border ${getPriorityColor(action.priority)} transition-all duration-200 hover:shadow-sm`}
        >
          <div className="flex items-start space-x-3">
            <div className="flex-shrink-0 mt-0.5">
              {getPriorityIcon(action.priority)}
            </div>
            <div className="flex-1 min-w-0">
              <div className="flex items-center space-x-2 mb-1">
                <h4 className="text-sm font-medium text-gray-900">{action.action}</h4>
                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                  action.priority === 'high' ? 'bg-red-100 text-red-800' :
                  action.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-blue-100 text-blue-800'
                }`}>
                  {getPriorityText(action.priority)}
                </span>
              </div>
              <p className="text-sm text-gray-600 mb-3">{action.description}</p>
              {action.url ? (
                <Link
                  to={action.url}
                  className="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-700 transition-colors"
                >
                  Take Action
                  <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              ) : (
                <button className="inline-flex items-center text-sm font-medium text-gray-500 cursor-not-allowed">
                  Coming Soon
                </button>
              )}
            </div>
          </div>
        </div>
      ))}
      
      {actions.length > maxItems && (
        <div className="text-center pt-2">
          <button className="text-sm text-green-600 hover:text-green-700 font-medium">
            View all {actions.length} suggestions
          </button>
        </div>
      )}
    </div>
  );
};

export default SuggestedActions; 