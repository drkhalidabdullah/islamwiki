# System Architecture Documentation

## Overview

IslamWiki is a modern, social Islamic knowledge platform built with PHP and MySQL. This document provides a comprehensive overview of the system architecture, including the admin system, feature toggle system, permission system, and overall platform design.

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture Layers](#architecture-layers)
3. [Admin System Architecture](#admin-system-architecture)
4. [Feature Toggle System](#feature-toggle-system)
5. [Permission System](#permission-system)
6. [Database Architecture](#database-architecture)
7. [API Architecture](#api-architecture)
8. [Frontend Architecture](#frontend-architecture)
9. [Header Dashboard System](#header-dashboard-system)
10. [Courses System Architecture](#courses-system-architecture)
11. [Security Architecture](#security-architecture)
12. [Performance Considerations](#performance-considerations)

## System Overview

### Core Components
- **Web Application**: PHP-based web application
- **Database**: MySQL database for data persistence
- **Frontend**: HTML, CSS, JavaScript with responsive design
- **Admin System**: Comprehensive admin interface
- **Feature Toggles**: Configurable feature system
- **Permission System**: Role-based access control
- **Courses System**: Integrated educational content management
- **Wiki System**: Collaborative knowledge platform

### Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome
- **Charts**: Chart.js for analytics

## Architecture Layers

### Presentation Layer
- **User Interface**: Responsive web interface
- **Admin Interface**: Comprehensive admin dashboard
- **Mobile Support**: Mobile-optimized design
- **Accessibility**: WCAG compliant design

### Application Layer
- **Core Functions**: Utility and helper functions
- **Feature Toggles**: Configurable feature system
- **Permission System**: Role-based access control
- **Admin Functions**: Admin-specific functionality

### Data Layer
- **Database**: MySQL database
- **Caching**: Application-level caching
- **File Storage**: File upload and management
- **Session Management**: User session handling

### Security Layer
- **Authentication**: User authentication system
- **Authorization**: Permission-based access control
- **Input Validation**: Comprehensive input sanitization
- **API Security**: Secure API endpoints

## Admin System Architecture

### Core Components

#### Admin Dashboard
- **Main Interface**: Unified admin dashboard
- **System Statistics**: Real-time system metrics
- **Health Monitoring**: System health indicators
- **Quick Actions**: Common admin tasks

#### System Settings
- **Configuration Management**: System-wide settings
- **Feature Toggles**: Enable/disable features
- **Tab Persistence**: Maintains user context
- **Form Processing**: Secure form handling

#### User Management
- **User Accounts**: User account management
- **Role Assignment**: User role management
- **Permission Control**: Granular permission management
- **Activity Monitoring**: User activity tracking

#### Maintenance Control
- **Maintenance Mode**: System maintenance control
- **Health Monitoring**: Real-time system health
- **Cache Management**: System cache control
- **Database Optimization**: Database maintenance

### Data Flow

```
Admin Request → Authentication → Authorization → Processing → Response
```

### Security Model
- **Admin Access**: Admin privilege verification
- **Permission Checks**: Granular permission verification
- **API Protection**: Secure API endpoints
- **Input Validation**: Comprehensive input sanitization

## Feature Toggle System

### Architecture

#### Database Layer
- **Settings Storage**: Key-value pair storage
- **Type System**: Boolean, string, integer, JSON types
- **Default Values**: Sensible defaults for all features

#### Application Layer
- **Setting Retrieval**: `get_system_setting()` function
- **Caching**: Settings cached for performance
- **Type Conversion**: Proper type handling

#### UI Layer
- **Conditional Rendering**: UI elements show/hide based on settings
- **Admin Interface**: Toggle switches for configuration
- **User Feedback**: Clear messaging when features disabled

### Available Features

#### Core Features
- **User Registration**: Configurable registration system
- **Comments System**: Toggleable comment functionality
- **Wiki System**: Configurable wiki access
- **Social Features**: Toggleable social interactions
- **Analytics**: Configurable analytics tracking
- **Notifications**: Toggleable notification system

#### Implementation Pattern
```php
$feature_enabled = get_system_setting('feature_name', true);
if ($feature_enabled) {
    // Feature functionality
} else {
    // Alternative behavior or message
}
```

### API Integration
- **Protection Pattern**: All APIs check relevant settings
- **Error Handling**: Graceful degradation when features disabled
- **User Experience**: Clear feedback when features unavailable

## Permission System

### Architecture

#### Database Schema
- **Roles Table**: User role definitions
- **Permissions Table**: Available system permissions
- **Role Permissions Table**: Role-permission mappings
- **User Roles Table**: User-role assignments

#### Core Functions
- **has_role()**: Check user role membership
- **has_permission()**: Check user permissions
- **is_admin()**: Check admin privileges
- **require_admin()**: Require admin access

#### Permission Categories
- **Admin Permissions**: System administration
- **Wiki Permissions**: Wiki functionality
- **Content Permissions**: Content management
- **Social Permissions**: Social features

### Role Hierarchy

#### Administrator
- **Access Level**: Full system access
- **Permissions**: 20 comprehensive permissions
- **Use Case**: System administrators

#### Scholar
- **Access Level**: Research and content creation
- **Permissions**: 8 focused permissions
- **Use Case**: Academic researchers

#### Editor
- **Access Level**: Content editing and management
- **Permissions**: 7 content permissions
- **Use Case**: Content editors

#### Other Roles
- **Content Reviewer**: Content moderation
- **Moderator**: Community moderation
- **Contributor**: Basic content contribution
- **User**: Regular user functionality
- **Guest**: Minimal access

## Database Architecture

### Core Tables

#### User Management
- **users**: User account information
- **user_roles**: User-role assignments
- **roles**: Role definitions
- **role_permissions**: Role-permission mappings

#### Content Management
- **articles**: Wiki articles
- **posts**: User posts
- **comments**: User comments
- **categories**: Content categories

#### System Management
- **system_settings**: System configuration
- **sessions**: User sessions
- **logs**: System logs
- **analytics**: Analytics data

### Relationships

#### User-Role Relationship
```
users (1) ←→ (M) user_roles (M) ←→ (1) roles
```

#### Role-Permission Relationship
```
roles (1) ←→ (M) role_permissions (M) ←→ (1) permissions
```

#### Content Relationships
```
users (1) ←→ (M) articles
users (1) ←→ (M) posts
articles (1) ←→ (M) comments
```

### Indexing Strategy
- **Primary Keys**: Auto-incrementing integers
- **Foreign Keys**: Referential integrity
- **Unique Constraints**: Prevent duplicates
- **Composite Indexes**: Optimize queries

## API Architecture

### RESTful Design
- **Resource-Based URLs**: Clear resource identification
- **HTTP Methods**: Proper HTTP method usage
- **Status Codes**: Appropriate HTTP status codes
- **JSON Responses**: Consistent JSON format

### Security Model
- **Authentication**: Session-based authentication
- **Authorization**: Permission-based access control
- **Input Validation**: Comprehensive input sanitization
- **Rate Limiting**: Protection against abuse

### API Endpoints

#### Admin APIs
- **User Management**: `/admin/manage_users`
- **Permission Management**: `/admin/manage_permissions`
- **System Settings**: `/admin/system_settings`
- **Analytics**: `/admin/analytics`

#### Feature APIs
- **Comments**: `/api/ajax/add_comment.php`
- **Social**: `/api/ajax/send_message.php`
- **Analytics**: `/api/ajax/track_search.php`
- **Notifications**: `/api/ajax/get_notifications.php`

### Error Handling
- **Consistent Format**: Standardized error responses
- **HTTP Status Codes**: Appropriate status codes
- **Error Messages**: Clear error descriptions
- **Logging**: Comprehensive error logging

## Frontend Architecture

### Responsive Design
- **Mobile-First**: Mobile-optimized design
- **Breakpoints**: Responsive breakpoints
- **Flexible Layouts**: CSS Grid and Flexbox
- **Touch-Friendly**: Touch-optimized interfaces

### Component Structure
- **Header**: Navigation and user controls
- **Main Content**: Primary content area
- **Sidebar**: Secondary content and tools
- **Footer**: Site information and links

### JavaScript Architecture
- **Modular Design**: Modular JavaScript structure
- **Event Handling**: Proper event management
- **AJAX Integration**: Seamless AJAX functionality
- **Error Handling**: Client-side error handling

### CSS Architecture
- **Component-Based**: Component-based CSS
- **Utility Classes**: Reusable utility classes
- **Responsive Design**: Mobile-responsive design
- **Performance**: Optimized CSS delivery

## Header Dashboard System

### Overview
The Header Dashboard System is a modern, responsive navigation system that provides centralized access to all platform features. It consists of a fixed header component with integrated search, creation tools, user management, and sidebar controls.

### Components

#### Header Dashboard
- **Fixed Positioning**: Always visible at the top of the page
- **Search Integration**: Centralized search functionality
- **Create Button**: Dropdown menu for content creation
- **User Menu**: Profile and settings access
- **Utility Icons**: Messages and notifications
- **Responsive Design**: Adapts to all screen sizes

#### Sidebar Management
- **Left Sidebar Toggle**: Hamburger menu for navigation sidebar
- **Right Sidebar**: Friends sidebar with profile pictures
- **State Persistence**: User preferences saved in localStorage
- **Mobile Optimization**: Different behavior for mobile devices
- **Z-Index Management**: Proper layering of UI elements

### Technical Implementation

#### CSS Architecture
- **Modular Styles**: Separate CSS files for each component
- **Responsive Design**: Mobile-first approach with media queries
- **Z-Index Hierarchy**: Proper stacking context management
- **Animation System**: Smooth transitions and hover effects

#### JavaScript State Management
- **localStorage Integration**: Persistent user preferences
- **Event Handling**: Proper event delegation and cleanup
- **Mobile Detection**: Screen size-based behavior
- **State Synchronization**: Consistent state across components

#### PHP Backend
- **API Endpoints**: RESTful APIs for dynamic content
- **Session Management**: User state handling
- **Database Integration**: Efficient data queries
- **Security**: Input validation and sanitization

### User Experience

#### Desktop Experience
- **Full Feature Set**: All features accessible from header
- **Sidebar Controls**: Toggle sidebars as needed
- **Quick Actions**: Fast access to common tasks
- **Visual Feedback**: Clear indication of current state

#### Mobile Experience
- **Touch Optimization**: Large touch targets
- **Gesture Support**: Swipe and tap interactions
- **Responsive Layout**: Optimized for small screens
- **Performance**: Smooth animations on mobile

### Performance Considerations

#### Loading Performance
- **Lazy Loading**: Components loaded as needed
- **Minimal JavaScript**: Optimized code size
- **Efficient CSS**: Streamlined stylesheets
- **Caching**: Browser and server-side caching

#### Runtime Performance
- **Event Optimization**: Efficient event handling
- **Memory Management**: Proper cleanup of resources
- **State Updates**: Minimal DOM manipulation
- **Animation Performance**: Hardware-accelerated animations

## Security Architecture

### Authentication
- **Session Management**: Secure session handling
- **Password Security**: Secure password storage
- **Login Protection**: Brute force protection
- **Session Timeout**: Automatic session expiration

### Authorization
- **Role-Based Access**: Role-based access control
- **Permission Checks**: Granular permission verification
- **API Security**: Secure API endpoints
- **Page Protection**: Page-level access control

### Input Validation
- **Server-Side**: Comprehensive server-side validation
- **Client-Side**: Client-side validation
- **Sanitization**: Input sanitization
- **SQL Injection**: SQL injection prevention

### Data Protection
- **Encryption**: Sensitive data encryption
- **Secure Storage**: Secure data storage
- **Access Logging**: Comprehensive access logging
- **Audit Trail**: Complete audit trail

## Courses System Architecture

### Overview
The courses system is fully integrated into the wiki platform, providing a unified content management system for educational content. Courses are implemented as special wiki articles with course-specific metadata and functionality.

### Core Components

#### Course Articles
- **Course Type**: Special `course` type in wiki_articles table
- **Course Metadata**: JSON metadata storage for course information
- **Namespace**: Course namespace for organized content structure
- **Categories**: Course categories integrated as wiki content categories

#### Lesson Articles
- **Lesson Type**: Special `lesson` type in wiki_articles table
- **Parent Relationship**: Lessons linked to parent courses via parent_course_id
- **Lesson Metadata**: Lesson-specific fields (type, duration, sort order)
- **Content**: Rich lesson content using wiki editor

#### Progress Tracking
- **User Progress**: wiki_course_progress table for user progress tracking
- **Completions**: wiki_course_completions table for course completion records
- **Statistics**: Real-time course statistics and analytics
- **Achievements**: Course completion tracking and badges

### Database Schema

#### Wiki Articles Extension
```sql
-- Course-specific fields added to wiki_articles table
course_type ENUM('course', 'lesson', 'regular') DEFAULT 'regular'
course_metadata JSON NULL
parent_course_id BIGINT UNSIGNED NULL
lesson_type ENUM('text', 'video', 'audio', 'quiz', 'assignment') DEFAULT 'text'
lesson_duration INT DEFAULT 0
lesson_sort_order INT DEFAULT 0
difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner'
estimated_duration INT DEFAULT 0
thumbnail_url VARCHAR(500) NULL
is_course_featured BOOLEAN DEFAULT FALSE
```

#### Progress Tracking Tables
```sql
-- User course progress
wiki_course_progress (
    user_id, course_article_id, current_lesson_id,
    progress_percentage, time_spent, started_at, last_accessed_at
)

-- Course completions
wiki_course_completions (
    user_id, course_article_id, completed_at,
    completion_percentage, time_spent, is_completed
)
```

### URL Structure

#### Course URLs
- **Course Index**: `/wiki/Courses` - Complete course catalog
- **Course Overview**: `/wiki/Course:Course Name` - Individual course pages
- **Course Lessons**: `/wiki/Course:Course Name/Lesson Name` - Individual lessons

#### URL Routing
- **Course Namespace**: Special handling for Course namespace articles
- **Lesson Navigation**: Hierarchical URL structure for lessons
- **Backward Compatibility**: 301 redirects from old course URLs

### Content Management

#### Course Creation
- **Wiki Editor**: Same rich text editor for all content
- **Course Metadata**: Course-specific fields and settings
- **Category Assignment**: Course categories for organization
- **Difficulty Levels**: Beginner, intermediate, advanced classifications

#### Lesson Management
- **Parent-Child Relationship**: Lessons linked to parent courses
- **Sort Order**: Lesson ordering within courses
- **Lesson Types**: Text, video, audio, quiz, assignment support
- **Duration Tracking**: Estimated lesson durations

### User Experience

#### Course Navigation
- **Course Overview**: Comprehensive course information and lesson lists
- **Lesson Navigation**: Previous/Next lesson navigation
- **Progress Tracking**: Visual progress indicators and completion status
- **Course Statistics**: Student counts, completion rates, time tracking

#### Progress Visualization
- **Progress Bars**: Visual progress indicators for courses and lessons
- **Completion Status**: Clear indicators for completed, current, and locked lessons
- **Achievement Badges**: Visual recognition for course completion
- **Statistics Display**: Real-time course statistics and user progress

### Integration Benefits

#### Content Reusability
- **Cross-linking**: Easy linking between course lessons and related wiki articles
- **Content Sharing**: Course lessons can be referenced from multiple courses
- **Search Integration**: Course content discoverable through wiki search
- **Unified Editing**: Same interface for all content creation and editing

#### SEO Benefits
- **Searchable Content**: Course content now part of searchable wiki knowledge base
- **Clean URLs**: SEO-friendly URLs for better search engine indexing
- **Content Discovery**: Enhanced content discovery through unified system
- **Internal Linking**: Better internal linking structure

### Technical Implementation

#### Course Handlers
- **Course Article Handler**: `public/modules/wiki/course_article.php`
- **Course Lesson Handler**: `public/modules/wiki/course_lesson.php`
- **Specialized Routing**: Enhanced .htaccess routing for course namespace

#### Data Migration
- **Complete Migration**: All existing course data migrated to wiki system
- **Progress Preservation**: User progress and completion data maintained
- **Category Integration**: Course categories integrated as wiki content categories
- **Metadata Preservation**: All course metadata preserved in new system

### Security & Performance

#### Security
- **Access Control**: Same permission system as wiki articles
- **Content Validation**: Comprehensive input validation and sanitization
- **Progress Security**: Secure progress tracking and user data protection

#### Performance
- **Unified Caching**: Better caching strategies for all content
- **Database Optimization**: More efficient queries and data structure
- **Content Delivery**: Faster content delivery through unified system
- **Reduced Complexity**: Single system reduces maintenance overhead

## Performance Considerations

### Database Optimization
- **Query Optimization**: Optimized database queries
- **Indexing Strategy**: Strategic database indexing
- **Connection Pooling**: Efficient connection management
- **Caching**: Database query caching

### Application Performance
- **Code Optimization**: Optimized PHP code
- **Memory Management**: Efficient memory usage
- **Caching**: Application-level caching
- **Session Management**: Efficient session handling

### Frontend Performance
- **Asset Optimization**: Optimized CSS and JavaScript
- **Image Optimization**: Optimized image delivery
- **CDN Integration**: Content delivery network
- **Caching Strategy**: Browser caching strategy

### Scalability
- **Horizontal Scaling**: Load balancing support
- **Database Scaling**: Database scaling strategies
- **Caching Layers**: Multiple caching layers
- **Performance Monitoring**: Real-time performance monitoring

## Deployment Architecture

### Server Requirements
- **PHP**: PHP 7.4 or higher
- **MySQL**: MySQL 5.7 or higher
- **Web Server**: Apache or Nginx
- **SSL**: SSL certificate for HTTPS

### File Structure
```
/var/www/html/
├── public/                 # Web-accessible files
│   ├── pages/             # Page templates
│   ├── api/               # API endpoints
│   ├── assets/            # Static assets
│   └── includes/          # Include files
├── database/              # Database migrations
├── docs/                  # Documentation
└── uploads/               # User uploads
```

### Configuration
- **Environment Variables**: Environment-specific configuration
- **Database Configuration**: Database connection settings
- **Security Settings**: Security configuration
- **Feature Flags**: Feature toggle configuration

## Monitoring and Maintenance

### System Monitoring
- **Health Checks**: Real-time system health monitoring
- **Performance Metrics**: Performance monitoring
- **Error Tracking**: Error monitoring and logging
- **User Activity**: User activity tracking

### Maintenance Tasks
- **Database Maintenance**: Regular database optimization
- **Cache Management**: Cache clearing and optimization
- **Log Management**: Log rotation and cleanup
- **Security Updates**: Regular security updates

### Backup Strategy
- **Database Backups**: Regular database backups
- **File Backups**: File system backups
- **Configuration Backups**: Configuration backups
- **Disaster Recovery**: Disaster recovery procedures

---

This architecture documentation provides a comprehensive overview of the IslamWiki system. For additional technical details or implementation questions, please refer to the specific component documentation or contact the development team.
