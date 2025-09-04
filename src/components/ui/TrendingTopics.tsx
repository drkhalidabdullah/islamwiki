import React from 'react';
import { Link } from 'react-router-dom';

interface TrendingTopic {
  id: number;
  title: string;
  discussion_count: number;
  url: string;
}

interface TrendingTopicsProps {
  topics: TrendingTopic[];
  maxItems?: number;
  className?: string;
}

const TrendingTopics: React.FC<TrendingTopicsProps> = ({ 
  topics, 
  maxItems = 5, 
  className = '' 
}) => {
  const displayTopics = topics.slice(0, maxItems);

  return (
    <div className={`space-y-3 ${className}`}>
      {displayTopics.map((topic, index) => (
        <div key={topic.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
          <div className="flex items-center space-x-3">
            <div className="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <span className="text-green-600 font-bold text-sm">{index + 1}</span>
            </div>
            <div className="min-w-0 flex-1">
              <Link 
                to={topic.url}
                className="text-sm font-medium text-gray-900 hover:text-green-600 transition-colors line-clamp-2"
              >
                {topic.title}
              </Link>
              <p className="text-xs text-gray-500 mt-1">
                {topic.discussion_count} discussion{topic.discussion_count !== 1 ? 's' : ''}
              </p>
            </div>
          </div>
          <div className="flex-shrink-0">
            <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>
      ))}
      
      {topics.length > maxItems && (
        <div className="text-center pt-2">
          <Link 
            to="/discussions"
            className="text-sm text-green-600 hover:text-green-700 font-medium"
          >
            View all trending topics
          </Link>
        </div>
      )}
    </div>
  );
};

export default TrendingTopics; 