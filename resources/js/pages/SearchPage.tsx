import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import Button from '../components/ui/Button';

interface SearchResult {
  id: number;
  type: 'article' | 'user' | 'category';
  title: string;
  description: string;
  url: string;
  score: number;
  metadata?: {
    author?: string;
    date?: string;
    tags?: string[];
    category?: string;
  };
}

const SearchPage: React.FC = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [query, setQuery] = useState(searchParams.get('q') || '');
  const [searchType, setSearchType] = useState<'all' | 'article' | 'user' | 'category'>('all');
  const [results, setResults] = useState<SearchResult[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [hasSearched, setHasSearched] = useState(false);

  // Mock search results for development
  const mockResults: SearchResult[] = [
    {
      id: 1,
      type: 'article',
      title: 'Understanding Islamic Law (Shariah)',
      description: 'A comprehensive guide to Islamic jurisprudence and legal principles...',
      url: '/articles/understanding-islamic-law',
      score: 0.95,
      metadata: {
        author: 'Dr. Ahmed Hassan',
        date: '2025-01-27',
        tags: ['islamic-law', 'shariah', 'jurisprudence'],
        category: 'Islamic Law'
      }
    },
    {
      id: 2,
      type: 'user',
      title: 'Dr. Ahmed Hassan',
      description: 'Islamic scholar and expert in Islamic law with 20+ years of experience...',
      url: '/user/dr-ahmed-hassan',
      score: 0.87,
      metadata: {
        author: 'Verified Scholar',
        date: '2025-01-27',
        tags: ['scholar', 'islamic-law', 'expert'],
        category: 'Islamic Scholars'
      }
    },
    {
      id: 3,
      type: 'article',
      title: 'The Five Pillars of Islam',
      description: 'Essential practices that form the foundation of Islamic faith...',
      url: '/articles/five-pillars-islam',
      score: 0.92,
      metadata: {
        author: 'Ustadha Fatima Ali',
        date: '2025-01-26',
        tags: ['five-pillars', 'islam', 'faith', 'practices'],
        category: 'Islamic Beliefs'
      }
    },
    {
      id: 4,
      type: 'category',
      title: 'Islamic History',
      description: 'Explore the rich history of Islamic civilization, from the Prophet Muhammad to modern times...',
      url: '/category/islamic-history',
      score: 0.89,
      metadata: {
        author: 'System',
        date: '2025-01-25',
        tags: ['history', 'civilization', 'prophet-muhammad'],
        category: 'Islamic History'
      }
    }
  ];

  useEffect(() => {
    if (query.trim()) {
      performSearch();
    }
  }, [query, searchType]);

  const performSearch = async () => {
    if (!query.trim()) return;

    setIsLoading(true);
    setHasSearched(true);

    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Filter results based on search type
      let filteredResults = mockResults;
      if (searchType !== 'all') {
        filteredResults = mockResults.filter(result => result.type === searchType);
      }
      
      // Filter by query
      filteredResults = filteredResults.filter(result =>
        result.title.toLowerCase().includes(query.toLowerCase()) ||
        result.description.toLowerCase().includes(query.toLowerCase()) ||
        result.metadata?.tags?.some(tag => tag.toLowerCase().includes(query.toLowerCase()))
      );

      setResults(filteredResults);
    } catch (error) {
      console.error('Search error:', error);
      setResults([]);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (query.trim()) {
      setSearchParams({ q: query.trim() });
      performSearch();
    }
  };

  const handleClearSearch = () => {
    setQuery('');
    setSearchParams({});
    setResults([]);
    setHasSearched(false);
  };

  const getResultIcon = (type: string) => {
    switch (type) {
      case 'article':
        return (
          <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        );
      case 'user':
        return (
          <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        );
      case 'category':
        return (
          <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2m14 0V5a2 2 0 00-2-2H5a2 2 0 00-2 2v2" />
          </svg>
        );
      default:
        return (
          <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        );
    }
  };

  const getResultTypeLabel = (type: string) => {
    switch (type) {
      case 'article':
        return 'Article';
      case 'user':
        return 'User';
      case 'category':
        return 'Category';
      default:
        return 'Result';
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Search</h1>
          <p className="text-gray-600 mt-2">Find articles, users, and categories</p>
        </div>

        {/* Search Form */}
        <Card className="mb-8">
          <div className="p-6">
            <form onSubmit={handleSearch} className="space-y-4">
              <div className="flex flex-col sm:flex-row gap-4">
                <div className="flex-1">
                  <Input
                    type="text"
                    placeholder="Search for articles, users, categories..."
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    className="w-full"
                  />
                </div>
                <div className="w-full sm:w-48">
                  <select
                    value={searchType}
                    onChange={(e) => setSearchType(e.target.value as any)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                    <option value="all">All Types</option>
                    <option value="article">Articles</option>
                    <option value="user">Users</option>
                    <option value="category">Categories</option>
                  </select>
                </div>
                <Button type="submit" variant="primary" loading={isLoading}>
                  {isLoading ? 'Searching...' : 'Search'}
                </Button>
                {hasSearched && (
                  <Button type="button" variant="outline" onClick={handleClearSearch}>
                    Clear
                  </Button>
                )}
              </div>
            </form>
          </div>
        </Card>

        {/* Search Results */}
        {hasSearched && (
          <div className="space-y-4">
            <div className="flex justify-between items-center">
              <h2 className="text-xl font-semibold text-gray-900">
                Search Results
                {query && (
                  <span className="text-gray-600 font-normal ml-2">
                    for "{query}"
                  </span>
                )}
              </h2>
              {results.length > 0 && (
                <p className="text-sm text-gray-600">
                  {results.length} result{results.length !== 1 ? 's' : ''} found
                </p>
              )}
            </div>

            {isLoading ? (
              <div className="flex items-center justify-center h-32">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
              </div>
            ) : results.length > 0 ? (
              <div className="space-y-4">
                {results.map((result) => (
                  <Card key={`${result.type}-${result.id}`} className="hover:shadow-md transition-shadow">
                    <div className="p-6">
                      <div className="flex items-start space-x-4">
                        <div className="flex-shrink-0 mt-1">
                          {getResultIcon(result.type)}
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center space-x-2 mb-2">
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                              {getResultTypeLabel(result.type)}
                            </span>
                            <span className="text-sm text-gray-500">
                              Score: {(result.score * 100).toFixed(0)}%
                            </span>
                          </div>
                          
                          <h3 className="text-lg font-semibold text-gray-900 mb-2">
                            <a 
                              href={result.url} 
                              className="hover:text-green-600 transition-colors"
                            >
                              {result.title}
                            </a>
                          </h3>
                          
                          <p className="text-gray-600 mb-3">
                            {result.description}
                          </p>
                          
                          {result.metadata && (
                            <div className="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                              {result.metadata.author && (
                                <span className="flex items-center">
                                  <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                  </svg>
                                  {result.metadata.author}
                                </span>
                              )}
                              
                              {result.metadata.date && (
                                <span className="flex items-center">
                                  <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                  </svg>
                                  {new Date(result.metadata.date).toLocaleDateString()}
                                </span>
                              )}
                              
                              {result.metadata.category && (
                                <span className="flex items-center">
                                  <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                  </svg>
                                  {result.metadata.category}
                                </span>
                              )}
                            </div>
                          )}
                          
                          {result.metadata?.tags && result.metadata.tags.length > 0 && (
                            <div className="mt-3 flex flex-wrap gap-2">
                              {result.metadata.tags.map((tag, index) => (
                                <span
                                  key={index}
                                  className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                  #{tag}
                                </span>
                              ))}
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  </Card>
                ))}
              </div>
            ) : (
              <Card>
                <div className="p-12 text-center">
                  <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <h3 className="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                  <p className="mt-1 text-sm text-gray-500">
                    Try adjusting your search terms or browse our categories instead.
                  </p>
                  <div className="mt-6">
                    <Button variant="primary" onClick={() => setSearchType('all')}>
                      Browse All Content
                    </Button>
                  </div>
                </div>
              </Card>
            )}
          </div>
        )}

        {/* Search Tips */}
        {!hasSearched && (
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Search Tips</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">For Articles</h4>
                  <ul className="text-sm text-gray-600 space-y-1">
                    <li>• Use specific keywords like "halal", "prayer", "fasting"</li>
                    <li>• Search by author name</li>
                    <li>• Use category names like "Islamic Law" or "Islamic History"</li>
                  </ul>
                </div>
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">For Users</h4>
                  <ul className="text-sm text-gray-600 space-y-1">
                    <li>• Search by username or real name</li>
                    <li>• Look for scholars and experts</li>
                    <li>• Find users by location or specialization</li>
                  </ul>
                </div>
              </div>
            </div>
          </Card>
        )}
      </div>
    </div>
  );
};

export default SearchPage; 