import React from 'react';
import { Link } from 'react-router-dom';

interface ActivityItem {
  id: number;
  type: 'login' | 'content_created' | 'content_edited' | 'comment_made' | 'profile_updated' | 'settings_changed';
  description: string;
  timestamp: string;
  related_content?: {
    id: number;
    title: string;
    url: string;
  };
}

interface ActivityTimelineProps {
  activities: ActivityItem[];
  maxItems?: number;
}

const ActivityTimeline: React.FC<ActivityTimelineProps> = ({ activities, maxItems = 5 }) => {
  const getActivityIcon = (type: ActivityItem['type']) => {
    switch (type) {
      case 'login':
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
        );
      case 'content_created':
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        );
      case 'content_edited':
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        );
      case 'comment_made':
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        );
      case 'profile_updated':
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        );
      case 'settings_changed':
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        );
      default:
        return (
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
    }
  };

  const getActivityColor = (type: ActivityItem['type']) => {
    switch (type) {
      case 'login':
        return 'bg-blue-100 text-blue-600';
      case 'content_created':
        return 'bg-green-100 text-green-600';
      case 'content_edited':
        return 'bg-yellow-100 text-yellow-600';
      case 'comment_made':
        return 'bg-purple-100 text-purple-600';
      case 'profile_updated':
        return 'bg-indigo-100 text-indigo-600';
      case 'settings_changed':
        return 'bg-gray-100 text-gray-600';
      default:
        return 'bg-gray-100 text-gray-600';
    }
  };

  const formatTimestamp = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
    
    if (diffInHours < 1) return 'Just now';
    if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString();
  };

  const displayActivities = activities.slice(0, maxItems);

  return (
    <div className="space-y-4">
      {displayActivities.map((activity) => (
        <div key={activity.id} className="flex items-start space-x-3">
          <div className={`flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${getActivityColor(activity.type)}`}>
            {getActivityIcon(activity.type)}
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm text-gray-900">
              {activity.description}
              {activity.related_content && (
                <Link 
                  to={activity.related_content.url}
                  className="ml-1 text-green-600 hover:text-green-700 font-medium"
                >
                  &ldquo;{activity.related_content.title}&rdquo;
                </Link>
              )}
            </p>
            <p className="text-xs text-gray-500 mt-1">
              {formatTimestamp(activity.timestamp)}
            </p>
          </div>
        </div>
      ))}
      
      {activities.length > maxItems && (
        <div className="text-center pt-2">
          <button className="text-sm text-green-600 hover:text-green-700 font-medium">
            View all {activities.length} activities
          </button>
        </div>
      )}
    </div>
  );
};

export default ActivityTimeline; 