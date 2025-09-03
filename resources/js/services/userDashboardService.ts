export interface UserDashboardData {
  user_statistics: {
    profile_views: number;
    content_created: number;
    content_edited: number;
    comments_made: number;
    likes_received: number;
    followers_count: number;
    following_count: number;
    last_login: string;
    account_age_days: number;
    email_verified: boolean;
  };
  recent_activity: Array<{
    id: number;
    type: 'login' | 'content_created' | 'content_edited' | 'comment_made' | 'profile_updated' | 'settings_changed';
    description: string;
    timestamp: string;
    related_content?: {
      id: number;
      title: string;
      url: string;
    };
  }>;
  content_overview: {
    total_articles: number;
    total_pages: number;
    total_comments: number;
    draft_count: number;
    published_count: number;
    pending_review: number;
    last_content_created?: string;
    most_viewed_content?: {
      id: number;
      title: string;
      views: number;
      url: string;
    };
  };
  community_stats: {
    total_users: number;
    online_users: number;
    new_users_today: number;
    active_discussions: number;
    trending_topics: Array<{
      id: number;
      title: string;
      discussion_count: number;
      url: string;
    }>;
  };
  quick_insights: {
    weekly_activity_trend: 'increasing' | 'decreasing' | 'stable';
    engagement_score: number;
    content_quality_score: number;
    community_contribution: number;
    suggested_actions: Array<{
      action: string;
      description: string;
      priority: 'high' | 'medium' | 'low';
      url?: string;
    }>;
  };
  system_info: {
    platform_version: string;
    last_maintenance: string;
    upcoming_features: Array<{
      title: string;
      description: string;
      estimated_release: string;
    }>;
    announcements: Array<{
      id: number;
      title: string;
      content: string;
      priority: 'info' | 'warning' | 'important';
      timestamp: string;
    }>;
  };
}

export const userDashboardService = {
  async getUserDashboardData(): Promise<UserDashboardData> {
    try {
      const response = await fetch('/api/user/dashboard');
      if (!response.ok) {
        throw new Error('Failed to fetch user dashboard data');
      }
      const data = await response.json();
      if (!data.success) {
        throw new Error(data.error || 'Failed to fetch user dashboard data');
      }
      return data.data;
    } catch (error) {
      // Return mock data for now until backend is implemented
      return this.getMockDashboardData();
    }
  },

  getMockDashboardData(): UserDashboardData {
    const now = new Date();
    const lastWeek = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    return {
      user_statistics: {
        profile_views: 42,
        content_created: 8,
        content_edited: 15,
        comments_made: 23,
        likes_received: 67,
        followers_count: 12,
        following_count: 8,
        last_login: now.toISOString(),
        account_age_days: 45,
        email_verified: true
      },
      recent_activity: [
        {
          id: 1,
          type: 'login',
          description: 'Logged in successfully',
          timestamp: now.toISOString()
        },
        {
          id: 2,
          type: 'content_created',
          description: 'Created article "Introduction to Islamic Finance"',
          timestamp: lastWeek.toISOString(),
          related_content: {
            id: 1,
            title: 'Introduction to Islamic Finance',
            url: '/articles/introduction-to-islamic-finance'
          }
        },
        {
          id: 3,
          type: 'comment_made',
          description: 'Commented on "Five Pillars of Islam"',
          timestamp: new Date(now.getTime() - 2 * 24 * 60 * 60 * 1000).toISOString(),
          related_content: {
            id: 2,
            title: 'Five Pillars of Islam',
            url: '/articles/five-pillars-of-islam'
          }
        },
        {
          id: 4,
          type: 'profile_updated',
          description: 'Updated profile information',
          timestamp: new Date(now.getTime() - 5 * 24 * 60 * 60 * 1000).toISOString()
        }
      ],
      content_overview: {
        total_articles: 8,
        total_pages: 12,
        total_comments: 23,
        draft_count: 3,
        published_count: 5,
        pending_review: 0,
        last_content_created: lastWeek.toISOString(),
        most_viewed_content: {
          id: 1,
          title: 'Introduction to Islamic Finance',
          views: 156,
          url: '/articles/introduction-to-islamic-finance'
        }
      },
      community_stats: {
        total_users: 1247,
        online_users: 89,
        new_users_today: 12,
        active_discussions: 23,
        trending_topics: [
          {
            id: 1,
            title: 'Islamic Banking Principles',
            discussion_count: 45,
            url: '/discussions/islamic-banking-principles'
          },
          {
            id: 2,
            title: 'Ramadan Preparation Guide',
            discussion_count: 38,
            url: '/discussions/ramadan-preparation-guide'
          },
          {
            id: 3,
            title: 'Quran Study Methods',
            discussion_count: 32,
            url: '/discussions/quran-study-methods'
          }
        ]
      },
      quick_insights: {
        weekly_activity_trend: 'increasing',
        engagement_score: 78,
        content_quality_score: 85,
        community_contribution: 72,
        suggested_actions: [
          {
            action: 'Complete Profile',
            description: 'Add bio and profile picture to increase visibility',
            priority: 'high',
            url: '/settings/profile'
          },
          {
            action: 'Join Discussion',
            description: 'Participate in trending topic about Islamic Banking',
            priority: 'medium',
            url: '/discussions/islamic-banking-principles'
          },
          {
            action: 'Create Content',
            description: 'Share your knowledge about Islamic practices',
            priority: 'medium',
            url: '/content/create'
          }
        ]
      },
      system_info: {
        platform_version: 'v0.0.5.1',
        last_maintenance: new Date(now.getTime() - 3 * 24 * 60 * 60 * 1000).toISOString(),
        upcoming_features: [
          {
            title: 'Advanced Search',
            description: 'Enhanced search with filters and saved searches',
            estimated_release: 'v0.1.0'
          },
          {
            title: 'Mobile App',
            description: 'Native mobile application for iOS and Android',
            estimated_release: 'v0.2.0'
          },
          {
            title: 'Learning Paths',
            description: 'Structured learning courses and progress tracking',
            estimated_release: 'v0.3.0'
          }
        ],
        announcements: [
          {
            id: 1,
            title: 'New Dashboard Features',
            content: 'We\'ve added comprehensive user statistics and insights to your dashboard!',
            priority: 'info',
            timestamp: new Date(now.getTime() - 1 * 24 * 60 * 60 * 1000).toISOString()
          },
          {
            id: 2,
            title: 'Community Guidelines Updated',
            content: 'Please review our updated community guidelines for better collaboration.',
            priority: 'important',
            timestamp: new Date(now.getTime() - 2 * 24 * 60 * 60 * 1000).toISOString()
          }
        ]
      }
    };
  }
}; 