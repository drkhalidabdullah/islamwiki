import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { userDashboardService, UserDashboardData } from '../services/userDashboardService';
import Card from '../components/ui/Card';
import Button from '../components/ui/Button';
import StatCard from '../components/ui/StatCard';
import ActivityTimeline from '../components/ui/ActivityTimeline';
import ProgressBar from '../components/ui/ProgressBar';
import TrendingTopics from '../components/ui/TrendingTopics';
import SuggestedActions from '../components/ui/SuggestedActions';

const DashboardPage: React.FC = () => {
  const { user } = useAuthStore();
  const [dashboardData, setDashboardData] = useState<UserDashboardData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setIsLoading(true);
        const data = await userDashboardService.getUserDashboardData();
        setDashboardData(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to load dashboard data');
      } finally {
        setIsLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading your dashboard...</p>
        </div>
      </div>
    );
  }

  if (error || !dashboardData) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-red-600 text-6xl mb-4">⚠️</div>
          <h2 className="text-xl font-semibold text-gray-900 mb-2">Dashboard Unavailable</h2>
          <p className="text-gray-600 mb-4">{error || 'Unable to load dashboard data'}</p>
          <Button onClick={() => window.location.reload()} variant="primary">
            Try Again
          </Button>
        </div>
      </div>
    );
  }

  const formatLastLogin = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
    
    if (diffInHours < 1) return 'Just now';
    if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString();
  };

  const getEngagementColor = (score: number) => {
    if (score >= 80) return 'green';
    if (score >= 60) return 'yellow';
    return 'red';
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Welcome back, {user?.first_name ? user.first_name : user?.username}!</h1>
                              <p className="text-lg text-gray-600 mt-1">
                  Here&apos;s what&apos;s happening with your account and the community
                </p>
            </div>
            <div className="flex items-center space-x-3">
              <Link to="/content/create">
                <Button variant="primary" size="lg">
                  <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Create Content
                </Button>
              </Link>
              <Link to="/settings">
                <Button variant="outline" size="lg">
                  <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Settings
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Statistics Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard
            title="Profile Views"
            value={dashboardData.user_statistics.profile_views}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            }
            change={{ value: 12, isPositive: true, period: 'last week' }}
          />
          <StatCard
            title="Content Created"
            value={dashboardData.user_statistics.content_created}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            }
            change={{ value: 8, isPositive: true, period: 'last week' }}
          />
          <StatCard
            title="Likes Received"
            value={dashboardData.user_statistics.likes_received}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            }
            change={{ value: 15, isPositive: true, period: 'last week' }}
          />
          <StatCard
            title="Followers"
            value={dashboardData.user_statistics.followers_count}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            }
            change={{ value: 3, isPositive: true, period: 'last week' }}
          />
        </div>

        {/* Main Dashboard Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Left Column - Main Content */}
          <div className="lg:col-span-2 space-y-8">
            {/* Content Overview */}
            <Card>
              <div className="p-6">
                <div className="flex items-center justify-between mb-6">
                  <h2 className="text-xl font-semibold text-gray-900">Content Overview</h2>
                  <Link to="/content" className="text-green-600 hover:text-green-700 text-sm font-medium">
                    View All →
                  </Link>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                  <div className="text-center p-4 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{dashboardData.content_overview.total_articles}</div>
                    <div className="text-sm text-gray-600">Articles</div>
                  </div>
                  <div className="text-center p-4 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{dashboardData.content_overview.published_count}</div>
                    <div className="text-sm text-gray-600">Published</div>
                  </div>
                  <div className="text-center p-4 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{dashboardData.content_overview.draft_count}</div>
                    <div className="text-sm text-gray-600">Drafts</div>
                  </div>
                  <div className="text-center p-4 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-900">{dashboardData.content_overview.total_comments}</div>
                    <div className="text-sm text-gray-600">Comments</div>
                  </div>
                </div>
                {dashboardData.content_overview.most_viewed_content && (
                  <div className="bg-green-50 p-4 rounded-lg">
                    <h4 className="font-medium text-green-900 mb-2">Most Viewed Content</h4>
                    <Link 
                      to={dashboardData.content_overview.most_viewed_content.url}
                      className="text-green-700 hover:text-green-800 font-medium"
                    >
                      {dashboardData.content_overview.most_viewed_content.title}
                    </Link>
                    <p className="text-sm text-green-600 mt-1">
                      {dashboardData.content_overview.most_viewed_content.views} views
                    </p>
                  </div>
                )}
              </div>
            </Card>

            {/* Recent Activity */}
            <Card>
              <div className="p-6">
                <div className="flex items-center justify-between mb-6">
                  <h2 className="text-xl font-semibold text-gray-900">Recent Activity</h2>
                  <Link to="/activity" className="text-green-600 hover:text-green-700 text-sm font-medium">
                    View All →
                  </Link>
                </div>
                <ActivityTimeline activities={dashboardData.recent_activity} maxItems={6} />
              </div>
            </Card>

            {/* Community Stats */}
            <Card>
              <div className="p-6">
                <h2 className="text-xl font-semibold text-gray-900 mb-6">Community Overview</h2>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                  <div className="text-center">
                    <div className="text-2xl font-bold text-gray-900">{dashboardData.community_stats.total_users.toLocaleString()}</div>
                    <div className="text-sm text-gray-600">Total Users</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-green-600">{dashboardData.community_stats.online_users}</div>
                    <div className="text-sm text-gray-600">Online Now</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-blue-600">{dashboardData.community_stats.new_users_today}</div>
                    <div className="text-sm text-gray-600">New Today</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold text-purple-600">{dashboardData.community_stats.active_discussions}</div>
                    <div className="text-sm text-gray-600">Active Discussions</div>
                  </div>
                </div>
                <div className="bg-blue-50 p-4 rounded-lg">
                  <h4 className="font-medium text-blue-900 mb-3">Trending Topics</h4>
                  <TrendingTopics topics={dashboardData.community_stats.trending_topics} maxItems={3} />
                </div>
              </div>
            </Card>
          </div>

          {/* Right Column - Sidebar */}
          <div className="space-y-8">
            {/* Quick Insights */}
            <Card>
              <div className="p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Insights</h2>
                <div className="space-y-4">
                  <div>
                    <div className="flex justify-between items-center mb-2">
                      <span className="text-sm font-medium text-gray-700">Engagement Score</span>
                      <span className="text-sm text-gray-500">{dashboardData.quick_insights.engagement_score}/100</span>
                    </div>
                    <ProgressBar 
                      value={dashboardData.quick_insights.engagement_score} 
                      max={100} 
                      color={getEngagementColor(dashboardData.quick_insights.engagement_score) as any}
                    />
                  </div>
                  <div>
                    <div className="flex justify-between items-center mb-2">
                      <span className="text-sm font-medium text-gray-700">Content Quality</span>
                      <span className="text-sm text-gray-500">{dashboardData.quick_insights.content_quality_score}/100</span>
                    </div>
                    <ProgressBar 
                      value={dashboardData.quick_insights.content_quality_score} 
                      max={100} 
                      color="green"
                    />
                  </div>
                  <div>
                    <div className="flex justify-between items-center mb-2">
                      <span className="text-sm font-medium text-gray-700">Community Contribution</span>
                      <span className="text-sm text-gray-500">{dashboardData.quick_insights.community_contribution}/100</span>
                    </div>
                    <ProgressBar 
                      value={dashboardData.quick_insights.community_contribution} 
                      max={100} 
                      color="blue"
                    />
                  </div>
                </div>
                <div className="mt-4 p-3 bg-green-50 rounded-lg">
                  <div className="flex items-center">
                    <svg className="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span className="text-sm text-green-800 font-medium">
                      Activity trend: {dashboardData.quick_insights.weekly_activity_trend}
                    </span>
                  </div>
                </div>
              </div>
            </Card>

            {/* Suggested Actions */}
            <Card>
              <div className="p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Suggested Actions</h2>
                <SuggestedActions actions={dashboardData.quick_insights.suggested_actions} maxItems={3} />
              </div>
            </Card>

            {/* Account Status */}
            <Card>
              <div className="p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Account Status</h2>
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Account Age</span>
                    <span className="text-sm font-medium text-gray-900">{dashboardData.user_statistics.account_age_days} days</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Last Login</span>
                    <span className="text-sm font-medium text-gray-900">{formatLastLogin(dashboardData.user_statistics.last_login)}</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Email Verified</span>
                    <span className={`text-sm font-medium ${dashboardData.user_statistics.email_verified ? 'text-green-600' : 'text-red-600'}`}>
                      {dashboardData.user_statistics.email_verified ? '✓ Verified' : '✗ Not Verified'}
                    </span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Platform Version</span>
                    <span className="text-sm font-medium text-gray-900">{dashboardData.system_info.platform_version}</span>
                  </div>
                </div>
              </div>
            </Card>

            {/* System Announcements */}
            {dashboardData.system_info.announcements.length > 0 && (
              <Card>
                <div className="p-6">
                  <h2 className="text-lg font-semibold text-gray-900 mb-4">Announcements</h2>
                  <div className="space-y-3">
                    {dashboardData.system_info.announcements.slice(0, 2).map((announcement) => (
                      <div key={announcement.id} className={`p-3 rounded-lg border ${
                        announcement.priority === 'important' ? 'border-red-200 bg-red-50' :
                        announcement.priority === 'warning' ? 'border-yellow-200 bg-yellow-50' :
                        'border-blue-200 bg-blue-50'
                      }`}>
                        <h4 className="font-medium text-gray-900 mb-1">{announcement.title}</h4>
                        <p className="text-sm text-gray-600">{announcement.content}</p>
                        <p className="text-xs text-gray-500 mt-2">
                          {new Date(announcement.timestamp).toLocaleDateString()}
                        </p>
                      </div>
                    ))}
                  </div>
                </div>
              </Card>
            )}
          </div>
        </div>

        {/* Footer Actions */}
        <div className="mt-12 text-center">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Ready to contribute?</h3>
            <p className="text-gray-600 mb-6">
              Share your knowledge, join discussions, and help build the IslamWiki community.
            </p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Link to="/content/create">
                <Button variant="primary" size="lg">
                  <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Create New Content
                </Button>
              </Link>
              <Link to="/discussions">
                <Button variant="outline" size="lg">
                  <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  Join Discussions
                </Button>
              </Link>
              <Link to="/search">
                <Button variant="outline" size="lg">
                  <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  Explore Content
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default DashboardPage; 