/**
 * API Configuration
 * 
 * This file contains configuration for API endpoints
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

// API base URL - adjust this based on your environment
export const API_BASE_URL = process.env.NODE_ENV === 'production' 
  ? '' // Use relative URLs in production
  : 'http://localhost:8000'; // Use full URL in development

// API endpoints
export const API_ENDPOINTS = {
  // Database management
  DATABASE_OVERVIEW: '/admin/api/database/overview',
  DATABASE_HEALTH: '/admin/api/database/health',
  DATABASE_MIGRATIONS_STATUS: '/admin/api/database/migrations/status',
  DATABASE_MIGRATIONS_RUN: '/admin/api/database/migrations/run',
  DATABASE_MIGRATIONS_ROLLBACK: '/admin/api/database/migrations/rollback',
  DATABASE_QUERY: '/admin/api/database/query',
  DATABASE_QUERY_LOG: '/admin/api/database/query-log',
  
  // User management
  USERS: '/admin/api/users',
  USER_PROFILE: '/admin/api/users/profile',
  
  // Content management
  ARTICLES: '/admin/api/content/articles',
  CATEGORIES: '/admin/api/content/categories',
  
  // System
  SYSTEM_HEALTH: '/admin/api/system/health',
  SYSTEM_STATS: '/admin/api/system/stats',
};

// Helper function to build full API URLs
export const buildApiUrl = (endpoint: string): string => {
  return `${API_BASE_URL}${endpoint}`;
}; 