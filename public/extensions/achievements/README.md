# Achievement System Extension

A comprehensive award/badge/achievement/goals system with Islamic learning focus for the IslamWiki platform.

## Features

### Core Features
- **Level System**: Progressive leveling with XP and scaling requirements
- **Achievement Categories**: Organized by Islamic learning, community, content creation, etc.
- **Achievement Types**: Badges, achievements, awards, goals, and milestones
- **Rarity System**: Common, uncommon, rare, epic, and legendary achievements
- **Progress Tracking**: Real-time progress tracking for incomplete achievements
- **Notifications**: Popup notifications for achievements and level ups
- **Leaderboard**: Competitive ranking system
- **Statistics**: Detailed analytics and progress tracking

### Islamic Learning Focus
- **Quran Reading**: Track verses read and memorized
- **Hadith Study**: Monitor hadith learning progress
- **Islamic History**: Track historical knowledge acquisition
- **Tajweed Lessons**: Monitor pronunciation learning
- **Fiqh Study**: Track Islamic jurisprudence learning
- **Sunnah Practice**: Monitor daily practice implementation
- **Surah Memorization**: Track Quran memorization progress

### Admin Features
- **Achievement Management**: Create, edit, and delete achievements
- **Category Management**: Organize achievements by categories
- **Type Management**: Define different achievement types
- **System Settings**: Configure XP multipliers, level scaling, etc.
- **User Management**: Reset user achievements and levels
- **Analytics**: View system-wide statistics and usage

### User Features
- **Achievement Page**: View all achievements with filtering
- **Level Display**: Show current level and progress
- **Recent Achievements**: Quick access to recent unlocks
- **Statistics**: Personal achievement statistics
- **Leaderboard**: Compare with other users
- **Notifications**: Real-time achievement notifications

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- IslamWiki platform with extension system

### Step 1: Run Installation Script
```bash
php scripts/install_achievements.php
```

### Step 2: Enable Extension
1. Go to Admin Panel > System Settings > Extensions
2. Find "Achievement System" in the list
3. Click "Enable"
4. Configure settings as needed

### Step 3: Test Installation
```bash
php scripts/test_achievements.php
```

## Configuration

### System Settings
- **Enable Achievement System**: Master switch for the entire system
- **XP Multiplier**: Global multiplier for XP rewards (default: 1.0)
- **Points Multiplier**: Global multiplier for points rewards (default: 1.0)
- **Enable Notifications**: Show achievement notifications (default: true)
- **Enable Level System**: Enable level progression (default: true)
- **Maximum Level**: Highest level users can reach (default: 100)
- **Base XP per Level**: XP required for level 2 (default: 100)
- **Level Scaling Factor**: How much XP increases per level (default: 1.2)

### Achievement Categories
- **Islamic Learning**: Quran, Hadith, Islamic History, etc.
- **Community**: Social interactions, friend making, etc.
- **Content Creation**: Articles, wiki pages, comments, etc.
- **Wiki Mastery**: Wiki editing, moderation, expertise
- **Social Engagement**: Likes, comments, discussions
- **Special Events**: Seasonal and special occasion achievements

### Achievement Types
- **Badge**: Visual badges for completing tasks
- **Achievement**: Milestone achievements for reaching goals
- **Award**: Special recognition for outstanding contributions
- **Goal**: Personal objectives to work towards
- **Milestone**: Significant milestones in user journey

## Usage

### For Users

#### Viewing Achievements
1. Go to `/pages/user/achievements.php`
2. Use filters to find specific achievements
3. Click on achievements for detailed information
4. View your level and progress

#### Understanding Levels
- Start at Level 1 with 0 XP
- Gain XP by completing activities
- Level up when you reach the required XP
- Higher levels require more XP (scaling factor)

#### Earning Achievements
- Achievements are automatically unlocked based on activities
- Some achievements require specific levels
- Progress is tracked in real-time
- Notifications appear when achievements are unlocked

### For Administrators

#### Managing Achievements
1. Go to `/pages/admin/achievements.php`
2. Create new achievements with requirements
3. Edit existing achievements
4. Delete unwanted achievements
5. Organize by categories and types

#### Managing Categories
1. Create achievement categories
2. Set colors and icons
3. Organize by sort order
4. Enable/disable categories

#### System Configuration
1. Go to Admin Panel > System Settings > Extensions
2. Find "Achievement System"
3. Configure all system settings
4. Save changes

## API Reference

### User API Endpoints

#### Get User Level
```
GET /api/achievements.php?action=get_user_level
```

#### Get User Achievements
```
GET /api/achievements.php?action=get_achievements
GET /api/achievements.php?action=get_achievements&completed_only=true
```

#### Get Achievement Details
```
GET /api/achievements.php?action=get_achievement_details&id={achievement_id}
```

#### Get Notifications
```
GET /api/achievements.php?action=get_notifications&limit=10
```

#### Mark Notification as Read
```
POST /api/achievements.php?action=mark_notification_read
Content-Type: application/json
{"notification_id": 123}
```

#### Get Leaderboard
```
GET /api/achievements.php?action=get_leaderboard&limit=10&category_id=1
```

#### Get Achievement Statistics
```
GET /api/achievements.php?action=get_achievement_stats
```

#### Award XP
```
POST /api/achievements.php?action=award_xp
Content-Type: application/json
{
  "xp_amount": 50,
  "activity_type": "article_create",
  "activity_data": {"article_id": 123}
}
```

#### Award Points
```
POST /api/achievements.php?action=award_points
Content-Type: application/json
{
  "points_amount": 10,
  "activity_type": "article_create",
  "activity_data": {"article_id": 123}
}
```

### Admin API Endpoints

#### Get All Achievements
```
GET /api/admin/achievements.php?action=get_all_achievements
```

#### Create Achievement
```
POST /api/admin/achievements.php?action=create_achievement
Content-Type: application/json
{
  "name": "First Article",
  "slug": "first-article",
  "description": "Create your first article",
  "category_id": 1,
  "type_id": 1,
  "points": 10,
  "xp_reward": 50
}
```

#### Update Achievement
```
POST /api/admin/achievements.php?action=update_achievement
Content-Type: application/json
{
  "id": 1,
  "name": "Updated Name",
  "points": 20
}
```

#### Delete Achievement
```
POST /api/admin/achievements.php?action=delete_achievement
Content-Type: application/json
{"id": 1}
```

## Integration

### Automatic Activity Tracking

The system automatically tracks various user activities:

#### Content Creation
- Article creation and editing
- Wiki page creation and editing
- Comment writing
- Discussion creation

#### Social Activities
- Friend making
- Liking content
- Receiving likes
- Mentions

#### Islamic Learning
- Quran reading
- Hadith study
- Islamic history reading
- Tajweed lessons
- Fiqh study
- Sunnah practice
- Surah memorization

#### Wiki Activities
- Page visits
- Page edits
- Moderation actions
- Expertise development

### Manual Integration

#### Award XP
```php
award_achievement_xp($user_id, 50, 'custom_activity', ['data' => 'value']);
```

#### Award Points
```php
award_achievement_points($user_id, 10, 'custom_activity', ['data' => 'value']);
```

#### Check Achievements
```php
check_user_achievements($user_id);
```

#### Track Islamic Learning
```php
track_islamic_learning($user_id, 'quran_reading', ['verses' => 10]);
```

#### Track Content Creation
```php
track_content_creation($user_id, 'article', $article_id);
```

#### Track Social Activity
```php
track_social_activity($user_id, 'like_given', $content_id, 'article');
```

#### Track Wiki Activity
```php
track_wiki_activity($user_id, 'page_edited', $page_id);
```

## Database Schema

### Core Tables

#### achievement_categories
- `id`: Primary key
- `name`: Category name
- `slug`: URL-friendly identifier
- `description`: Category description
- `icon`: Font Awesome icon class
- `color`: Hex color code
- `sort_order`: Display order
- `is_active`: Active status

#### achievement_types
- `id`: Primary key
- `name`: Type name
- `slug`: URL-friendly identifier
- `description`: Type description
- `icon`: Font Awesome icon class
- `color`: Hex color code
- `sort_order`: Display order
- `is_active`: Active status

#### achievements
- `id`: Primary key
- `name`: Achievement name
- `slug`: URL-friendly identifier
- `description`: Short description
- `long_description`: Detailed description
- `category_id`: Foreign key to categories
- `type_id`: Foreign key to types
- `icon`: Font Awesome icon class
- `color`: Hex color code
- `rarity`: Common, uncommon, rare, epic, legendary
- `points`: Points reward
- `xp_reward`: XP reward
- `level_requirement`: Minimum level required
- `is_active`: Active status
- `is_hidden`: Hidden status
- `sort_order`: Display order

#### achievement_requirements
- `id`: Primary key
- `achievement_id`: Foreign key to achievements
- `requirement_type`: Type of requirement
- `requirement_value`: JSON requirement data
- `requirement_operator`: Comparison operator
- `sort_order`: Evaluation order

#### user_achievements
- `id`: Primary key
- `user_id`: Foreign key to users
- `achievement_id`: Foreign key to achievements
- `progress`: Completion percentage (0-100)
- `is_completed`: Completion status
- `completed_at`: Completion timestamp
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

#### user_levels
- `id`: Primary key
- `user_id`: Foreign key to users
- `level`: Current level
- `total_xp`: Total XP earned
- `current_level_xp`: XP in current level
- `xp_to_next_level`: XP needed for next level
- `total_achievements`: Total achievements earned
- `total_points`: Total points earned

#### user_activity_log
- `id`: Primary key
- `user_id`: Foreign key to users
- `activity_type`: Type of activity
- `activity_data`: JSON activity data
- `xp_earned`: XP earned from activity
- `points_earned`: Points earned from activity
- `created_at`: Activity timestamp

#### achievement_notifications
- `id`: Primary key
- `user_id`: Foreign key to users
- `achievement_id`: Foreign key to achievements
- `notification_type`: Type of notification
- `title`: Notification title
- `message`: Notification message
- `is_read`: Read status
- `created_at`: Creation timestamp

## Customization

### Adding New Achievement Categories

1. Go to Admin Panel > Achievements
2. Click "Create Category"
3. Fill in category details
4. Set color and icon
5. Save category

### Adding New Achievement Types

1. Go to Admin Panel > Achievements
2. Click "Create Type"
3. Fill in type details
4. Set color and icon
5. Save type

### Creating Custom Achievements

1. Go to Admin Panel > Achievements
2. Click "Create Achievement"
3. Fill in achievement details
4. Set requirements
5. Configure rewards
6. Save achievement

### Customizing XP and Points

1. Go to Admin Panel > System Settings > Extensions
2. Find "Achievement System"
3. Adjust multipliers
4. Configure level scaling
5. Save settings

## Troubleshooting

### Common Issues

#### Achievements Not Unlocking
- Check if system is enabled
- Verify achievement requirements
- Ensure user has required level
- Check activity tracking

#### Notifications Not Showing
- Verify notifications are enabled
- Check browser console for errors
- Ensure JavaScript is loaded
- Check notification permissions

#### Level Not Updating
- Verify XP is being awarded
- Check level scaling settings
- Ensure user level record exists
- Check for database errors

#### API Errors
- Verify authentication
- Check API endpoint URLs
- Ensure proper permissions
- Check request format

### Debug Mode

Enable debug mode by setting:
```php
set_system_setting('achievements_debug', true);
```

This will log all achievement-related activities to the error log.

## Support

### Getting Help
- Check the troubleshooting section
- Review the API documentation
- Check the database schema
- Enable debug mode for detailed logging

### Reporting Issues
- Include error messages
- Provide steps to reproduce
- Include system information
- Check debug logs

### Feature Requests
- Describe the desired feature
- Explain the use case
- Provide implementation suggestions
- Consider Islamic learning focus

## License

This extension is part of the IslamWiki platform and follows the same license terms.

## Version History

### v1.0.0
- Initial release
- Complete achievement system
- Islamic learning focus
- Admin management interface
- User achievement page
- API endpoints
- Automatic activity tracking
- Notifications system
- Leaderboard
- Statistics and analytics
