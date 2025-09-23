-- Comprehensive Achievement System Expansion
-- Version: 0.0.0.22
-- Description: Massive expansion of achievements with 100+ achievements covering all platform features

-- First, let's add new categories for more specific achievement types
INSERT INTO `achievement_categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
('Daily Activities', 'daily-activities', 'Achievements for daily engagement and consistent participation', 'fas fa-calendar-day', '#1abc9c', 7),
('Learning Paths', 'learning-paths', 'Achievements for completing structured learning journeys', 'fas fa-route', '#34495e', 8),
('Social Features', 'social-features', 'Achievements for using social platform features', 'fas fa-share-alt', '#e91e63', 9),
('Content Quality', 'content-quality', 'Achievements for high-quality content creation and curation', 'fas fa-gem', '#f39c12', 10),
('Platform Mastery', 'platform-mastery', 'Achievements for becoming proficient with platform features', 'fas fa-cogs', '#95a5a6', 11),
('Special Occasions', 'special-occasions', 'Achievements for special events and milestones', 'fas fa-gift', '#e67e22', 12),
('Seasonal Events', 'seasonal-events', 'Achievements for seasonal and holiday activities', 'fas fa-snowflake', '#3498db', 13),
('Progressive Series', 'progressive-series', 'Achievements that build upon each other in series', 'fas fa-layer-group', '#8e44ad', 14),
('Expertise Areas', 'expertise-areas', 'Achievements for becoming expert in specific Islamic topics', 'fas fa-microscope', '#16a085', 15),
('Community Leadership', 'community-leadership', 'Achievements for leadership and mentorship roles', 'fas fa-crown', '#f1c40f', 16);

-- Add new achievement types for different reward structures
INSERT INTO `achievement_types` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
('Streak', 'streak', 'Achievements for maintaining consistent activity streaks', 'fas fa-fire', '#e74c3c', 6),
('Collection', 'collection', 'Achievements for collecting or completing sets of items', 'fas fa-layer-group', '#9b59b6', 7),
('Challenge', 'challenge', 'Achievements for completing specific challenges or tasks', 'fas fa-flag-checkered', '#2ecc71', 8),
('Series', 'series', 'Achievements that are part of a progressive series', 'fas fa-list-ol', '#34495e', 9),
('Seasonal', 'seasonal', 'Achievements available only during specific seasons or events', 'fas fa-calendar-alt', '#e67e22', 10),
('Hidden', 'hidden', 'Secret achievements that are discovered through exploration', 'fas fa-eye-slash', '#7f8c8d', 11);

-- Now let's add a comprehensive set of achievements
INSERT INTO `achievements` (`name`, `slug`, `description`, `long_description`, `category_id`, `type_id`, `icon`, `color`, `rarity`, `points`, `xp_reward`, `level_requirement`) VALUES

-- DAILY ACTIVITIES ACHIEVEMENTS
('Early Bird', 'early-bird', 'Log in before 6 AM', 'You start your day with dedication and purpose, following the Sunnah of early rising.', 7, 6, 'fas fa-sun', '#f39c12', 'uncommon', 25, 100, 1),
('Night Owl', 'night-owl', 'Log in after 10 PM', 'You end your day with reflection and learning, following the tradition of night prayers.', 7, 6, 'fas fa-moon', '#8e44ad', 'uncommon', 25, 100, 1),
('Daily Devotee', 'daily-devotee', 'Log in for 7 consecutive days', 'Your consistent daily engagement shows true dedication to your Islamic learning journey.', 7, 6, 'fas fa-calendar-check', '#27ae60', 'rare', 75, 300, 2),
('Weekly Warrior', 'weekly-warrior', 'Log in for 30 consecutive days', 'A month of consistent daily engagement - you are truly committed to your growth.', 7, 6, 'fas fa-calendar-week', '#e74c3c', 'epic', 200, 600, 5),
('Monthly Master', 'monthly-master', 'Log in for 100 consecutive days', 'Over three months of daily dedication - you are a true master of consistency.', 7, 6, 'fas fa-calendar-alt', '#9b59b6', 'legendary', 500, 1500, 10),
('First Login', 'first-login', 'Complete your first login', 'Welcome to your Islamic learning journey! Every great journey begins with a single step.', 7, 1, 'fas fa-door-open', '#3498db', 'common', 10, 50, 0),
('Weekend Warrior', 'weekend-warrior', 'Log in on 5 consecutive weekends', 'You maintain your learning even during weekends, showing true dedication.', 7, 6, 'fas fa-calendar-week', '#e67e22', 'uncommon', 40, 150, 2),
('Holiday Hero', 'holiday-hero', 'Log in on 3 major Islamic holidays', 'You celebrate Islamic holidays by continuing your learning journey.', 7, 6, 'fas fa-star', '#f1c40f', 'rare', 60, 250, 3),

-- LEARNING PATHS ACHIEVEMENTS
('Quran Journey I', 'quran-journey-1', 'Complete the first 5 Surahs study', 'You have begun your journey through the Quran, the most important book in Islam.', 8, 9, 'fas fa-book-open', '#2ecc71', 'common', 30, 120, 1),
('Quran Journey II', 'quran-journey-2', 'Complete the first 10 Surahs study', 'Your understanding of the Quran is deepening with each Surah you study.', 8, 9, 'fas fa-book-open', '#27ae60', 'uncommon', 60, 250, 2),
('Quran Journey III', 'quran-journey-3', 'Complete the first 20 Surahs study', 'You have covered significant portions of the Quran, gaining comprehensive knowledge.', 8, 9, 'fas fa-book-open', '#16a085', 'rare', 120, 400, 4),
('Hadith Collection I', 'hadith-collection-1', 'Study 25 Sahih Bukhari hadiths', 'You are learning from the most authentic collection of hadiths.', 8, 9, 'fas fa-quote-left', '#8e44ad', 'uncommon', 50, 200, 2),
('Hadith Collection II', 'hadith-collection-2', 'Study 50 Sahih Muslim hadiths', 'You are expanding your knowledge with another authentic hadith collection.', 8, 9, 'fas fa-quote-left', '#9b59b6', 'rare', 100, 350, 4),
('Hadith Collection III', 'hadith-collection-3', 'Study 100 authentic hadiths', 'You have gained extensive knowledge from the sayings of Prophet Muhammad (PBUH).', 8, 9, 'fas fa-quote-left', '#8e44ad', 'epic', 200, 600, 7),
('Fiqh Fundamentals', 'fiqh-fundamentals', 'Complete basic Fiqh course', 'You have learned the fundamental principles of Islamic jurisprudence.', 8, 2, 'fas fa-balance-scale', '#16a085', 'uncommon', 75, 300, 3),
('Aqeedah Master', 'aqeedah-master', 'Complete Aqeedah course', 'You have mastered the fundamental beliefs and creed of Islam.', 8, 2, 'fas fa-heart', '#e74c3c', 'rare', 100, 400, 4),
('Seerah Scholar', 'seerah-scholar', 'Complete Prophet\'s biography course', 'You have learned the life and teachings of Prophet Muhammad (PBUH).', 8, 2, 'fas fa-user-circle', '#f39c12', 'rare', 90, 350, 4),
('Tafseer Explorer', 'tafseer-explorer', 'Complete Quranic interpretation course', 'You have learned how to properly interpret and understand the Quran.', 8, 2, 'fas fa-search', '#3498db', 'rare', 85, 325, 4),

-- SOCIAL FEATURES ACHIEVEMENTS
('Profile Perfect', 'profile-perfect', 'Complete your profile with all information', 'You have created a complete profile, helping others get to know you better.', 9, 1, 'fas fa-user-check', '#3498db', 'common', 20, 100, 0),
('Photo Pioneer', 'photo-pioneer', 'Upload your first profile photo', 'You have shared your first photo with the community.', 9, 1, 'fas fa-camera', '#e91e63', 'common', 15, 75, 0),
('Status Starter', 'status-starter', 'Post your first status update', 'You have shared your first thoughts with the community.', 9, 1, 'fas fa-comment', '#2ecc71', 'common', 10, 50, 0),
('Message Master', 'message-master', 'Send 100 messages', 'You are an active communicator in our community.', 9, 2, 'fas fa-envelope', '#8e44ad', 'uncommon', 50, 200, 2),
('Group Creator', 'group-creator', 'Create your first group', 'You have taken the initiative to create a community space.', 9, 2, 'fas fa-users', '#e67e22', 'uncommon', 40, 150, 2),
('Event Organizer', 'event-organizer', 'Create your first event', 'You are bringing people together for meaningful activities.', 9, 2, 'fas fa-calendar-plus', '#f39c12', 'uncommon', 45, 175, 2),
('Share Champion', 'share-champion', 'Share 50 pieces of content', 'You are actively sharing valuable content with the community.', 9, 2, 'fas fa-share', '#16a085', 'uncommon', 60, 250, 3),
('Tag Team', 'tag-team', 'Tag 25 people in posts', 'You are helping others stay connected and engaged.', 9, 2, 'fas fa-tag', '#e91e63', 'uncommon', 35, 140, 2),
('Notification Ninja', 'notification-ninja', 'Respond to 100 notifications', 'You are always engaged and responsive to community interactions.', 9, 2, 'fas fa-bell', '#9b59b6', 'rare', 80, 300, 4),
('Social Butterfly Pro', 'social-butterfly-pro', 'Make 50 friends', 'You have built a strong network of connections in our community.', 9, 3, 'fas fa-user-friends', '#e74c3c', 'rare', 100, 400, 5),

-- CONTENT QUALITY ACHIEVEMENTS
('Grammar Guru', 'grammar-guru', 'Write 20 posts with perfect grammar', 'Your attention to language quality makes our community more professional.', 10, 2, 'fas fa-spell-check', '#27ae60', 'uncommon', 60, 250, 3),
('Citation Scholar', 'citation-scholar', 'Include 25 proper citations in your content', 'You are contributing to the academic integrity of our knowledge base.', 10, 2, 'fas fa-quote-right', '#8e44ad', 'uncommon', 70, 280, 3),
('Fact Checker', 'fact-checker', 'Verify 50 facts in community content', 'You are helping maintain the accuracy and reliability of our information.', 10, 3, 'fas fa-check-double', '#16a085', 'rare', 90, 350, 4),
('Editor\'s Choice', 'editors-choice', 'Have your content featured by editors', 'Your content has been recognized for its exceptional quality.', 10, 3, 'fas fa-star', '#f39c12', 'rare', 120, 450, 5),
('Community Favorite', 'community-favorite', 'Receive 100 likes on a single post', 'Your content has resonated strongly with the community.', 10, 3, 'fas fa-heart', '#e91e63', 'rare', 100, 400, 5),
('Educational Contributor', 'educational-contributor', 'Create 50 educational articles', 'You are a valuable contributor to our educational content.', 10, 3, 'fas fa-graduation-cap', '#2ecc71', 'rare', 150, 500, 6),
('Research Master', 'research-master', 'Write 20 research-based articles', 'Your thorough research contributes significantly to our knowledge base.', 10, 3, 'fas fa-microscope', '#34495e', 'rare', 130, 450, 6),
('Translation Hero', 'translation-hero', 'Translate 10 articles to help non-Arabic speakers', 'You are breaking language barriers and making knowledge accessible to all.', 10, 3, 'fas fa-language', '#e67e22', 'rare', 110, 400, 5),
('Visual Storyteller', 'visual-storyteller', 'Create 25 posts with images or videos', 'You are using multimedia to enhance your content and engage the community.', 10, 2, 'fas fa-images', '#9b59b6', 'uncommon', 80, 300, 4),
('Content Curator', 'content-curator', 'Organize 20 pieces of content into collections', 'You are helping organize and categorize our knowledge for better access.', 10, 2, 'fas fa-folder', '#95a5a6', 'uncommon', 70, 250, 3),

-- PLATFORM MASTERY ACHIEVEMENTS
('Search Savant', 'search-savant', 'Use advanced search features 50 times', 'You have mastered the art of finding information efficiently on our platform.', 11, 2, 'fas fa-search-plus', '#3498db', 'uncommon', 40, 150, 2),
('Settings Specialist', 'settings-specialist', 'Customize all your account settings', 'You have personalized your experience to match your preferences perfectly.', 11, 1, 'fas fa-cog', '#95a5a6', 'common', 25, 100, 1),
('Keyboard Master', 'keyboard-master', 'Use 20 keyboard shortcuts', 'You are navigating the platform like a pro with keyboard shortcuts.', 11, 2, 'fas fa-keyboard', '#2c3e50', 'uncommon', 50, 200, 2),
('Mobile Maven', 'mobile-maven', 'Use the platform on mobile for 30 days', 'You are comfortable using our platform on any device.', 11, 2, 'fas fa-mobile-alt', '#e74c3c', 'uncommon', 60, 250, 3),
('Feature Explorer', 'feature-explorer', 'Use 15 different platform features', 'You have explored and utilized the full range of our platform capabilities.', 11, 2, 'fas fa-compass', '#8e44ad', 'uncommon', 70, 280, 3),
('Help Helper', 'help-helper', 'Help 10 other users with platform questions', 'You are sharing your platform knowledge to help others succeed.', 11, 3, 'fas fa-question-circle', '#16a085', 'rare', 80, 300, 4),
('Bug Hunter', 'bug-hunter', 'Report 5 bugs or issues', 'You are helping improve our platform by reporting issues you encounter.', 11, 2, 'fas fa-bug', '#e67e22', 'uncommon', 45, 175, 2),
('Feedback Champion', 'feedback-champion', 'Provide 20 pieces of constructive feedback', 'Your feedback is helping us improve the platform for everyone.', 11, 2, 'fas fa-comment-dots', '#f39c12', 'uncommon', 55, 220, 3),
('Tutorial Teacher', 'tutorial-teacher', 'Create 5 tutorial posts', 'You are teaching others how to use platform features effectively.', 11, 3, 'fas fa-chalkboard-teacher', '#9b59b6', 'rare', 90, 350, 4),
('Platform Pioneer', 'platform-pioneer', 'Be among the first 100 users to use a new feature', 'You are an early adopter, always ready to try new features.', 11, 3, 'fas fa-rocket', '#e74c3c', 'rare', 100, 400, 5),

-- SPECIAL OCCASIONS ACHIEVEMENTS
('Birthday Blessing', 'birthday-blessing', 'Log in on your birthday', 'May Allah bless you with another year of growth and learning.', 12, 5, 'fas fa-birthday-cake', '#e91e63', 'uncommon', 50, 200, 1),
('Anniversary Ace', 'anniversary-ace', 'Celebrate your 1-year platform anniversary', 'Congratulations on one year of dedicated learning and growth!', 12, 5, 'fas fa-calendar', '#f39c12', 'rare', 150, 500, 5),
('Milestone Master', 'milestone-master', 'Reach 1000 total points', 'You have accumulated significant achievements and recognition.', 12, 5, 'fas fa-trophy', '#e74c3c', 'epic', 200, 600, 8),
('Level Legend', 'level-legend', 'Reach level 25', 'You have achieved a high level of expertise and engagement.', 12, 5, 'fas fa-star', '#f1c40f', 'epic', 250, 750, 10),
('Achievement Ace', 'achievement-ace', 'Earn 50 achievements', 'You have mastered the art of achievement hunting!', 12, 5, 'fas fa-medal', '#9b59b6', 'epic', 300, 800, 8),
('Point Powerhouse', 'point-powerhouse', 'Earn 5000 total points', 'Your consistent engagement has earned you massive recognition.', 12, 5, 'fas fa-gem', '#e67e22', 'legendary', 500, 1200, 12),
('Streak Supreme', 'streak-supreme', 'Maintain a 365-day login streak', 'One full year of daily dedication - you are truly exceptional!', 12, 5, 'fas fa-fire', '#e74c3c', 'legendary', 1000, 2000, 15),
('Community Champion', 'community-champion', 'Be recognized as a top community member', 'Your contributions have made you a recognized leader in our community.', 12, 5, 'fas fa-crown', '#f1c40f', 'legendary', 400, 1000, 10),

-- SEASONAL EVENTS ACHIEVEMENTS
('Ramadan Ready', 'ramadan-ready', 'Be active during the entire month of Ramadan', 'You have used the blessed month of Ramadan for spiritual growth and learning.', 13, 10, 'fas fa-moon', '#2ecc71', 'epic', 300, 800, 6),
('Eid Celebrator', 'eid-celebrator', 'Participate in Eid activities', 'You have celebrated the joyous occasion of Eid with the community.', 13, 10, 'fas fa-star', '#f39c12', 'rare', 100, 400, 3),
('Hajj Helper', 'hajj-helper', 'Help others during Hajj season', 'You have supported others during the sacred pilgrimage season.', 13, 10, 'fas fa-kaaba', '#8e44ad', 'rare', 120, 450, 4),
('Umrah Supporter', 'umrah-supporter', 'Support Umrah pilgrims', 'You have helped those performing the lesser pilgrimage.', 13, 10, 'fas fa-mosque', '#16a085', 'uncommon', 80, 300, 3),
('Laylat al-Qadr Seeker', 'laylat-al-qadr-seeker', 'Be active on Laylat al-Qadr', 'You have sought the blessings of the Night of Power.', 13, 10, 'fas fa-moon', '#9b59b6', 'epic', 200, 600, 5),
('Ashura Scholar', 'ashura-scholar', 'Learn about the Day of Ashura', 'You have gained knowledge about this important day in Islamic history.', 13, 10, 'fas fa-book', '#e74c3c', 'uncommon', 60, 250, 2),
('Mawlid Celebrator', 'mawlid-celebrator', 'Celebrate the Prophet\'s birthday', 'You have honored the birth of Prophet Muhammad (PBUH).', 13, 10, 'fas fa-birthday-cake', '#f39c12', 'uncommon', 70, 280, 2),
('Winter Warrior', 'winter-warrior', 'Stay active during winter months', 'You have maintained your learning even during the challenging winter season.', 13, 10, 'fas fa-snowflake', '#3498db', 'uncommon', 50, 200, 2),
('Summer Scholar', 'summer-scholar', 'Use summer break for intensive learning', 'You have used your summer time productively for Islamic learning.', 13, 10, 'fas fa-sun', '#e67e22', 'uncommon', 60, 250, 2),

-- PROGRESSIVE SERIES ACHIEVEMENTS
('Friend Finder I', 'friend-finder-1', 'Make your first 5 friends', 'You are beginning to build your social network.', 14, 9, 'fas fa-user-plus', '#3498db', 'common', 25, 100, 1),
('Friend Finder II', 'friend-finder-2', 'Make 15 friends', 'Your social network is growing steadily.', 14, 9, 'fas fa-users', '#2980b9', 'uncommon', 50, 200, 2),
('Friend Finder III', 'friend-finder-3', 'Make 30 friends', 'You have built a strong social network.', 14, 9, 'fas fa-user-friends', '#1f618d', 'rare', 100, 400, 4),
('Content Creator I', 'content-creator-1', 'Create your first 5 posts', 'You are beginning your journey as a content creator.', 14, 9, 'fas fa-edit', '#e74c3c', 'common', 30, 120, 1),
('Content Creator II', 'content-creator-2', 'Create 25 posts', 'You are becoming a regular content contributor.', 14, 9, 'fas fa-pen', '#c0392b', 'uncommon', 75, 300, 3),
('Content Creator III', 'content-creator-3', 'Create 100 posts', 'You are a prolific content creator.', 14, 9, 'fas fa-feather', '#a93226', 'rare', 200, 600, 6),
('Wiki Warrior I', 'wiki-warrior-1', 'Make 10 wiki edits', 'You are beginning to contribute to our knowledge base.', 14, 9, 'fas fa-edit', '#f39c12', 'common', 20, 80, 1),
('Wiki Warrior II', 'wiki-warrior-2', 'Make 50 wiki edits', 'You are actively improving our wiki content.', 14, 9, 'fas fa-wikipedia-w', '#e67e22', 'uncommon', 60, 250, 3),
('Wiki Warrior III', 'wiki-warrior-3', 'Make 200 wiki edits', 'You are a dedicated wiki contributor.', 14, 9, 'fas fa-book', '#d35400', 'rare', 150, 500, 6),
('Learning Leader I', 'learning-leader-1', 'Complete 10 learning activities', 'You are establishing a consistent learning routine.', 14, 9, 'fas fa-graduation-cap', '#2ecc71', 'common', 40, 160, 1),
('Learning Leader II', 'learning-leader-2', 'Complete 50 learning activities', 'You are maintaining a strong learning commitment.', 14, 9, 'fas fa-book-reader', '#27ae60', 'uncommon', 100, 400, 3),
('Learning Leader III', 'learning-leader-3', 'Complete 200 learning activities', 'You are a true learning champion.', 14, 9, 'fas fa-trophy', '#16a085', 'rare', 250, 800, 7),

-- EXPERTISE AREAS ACHIEVEMENTS
('Quran Expert', 'quran-expert', 'Become expert in Quranic studies', 'You have achieved deep knowledge and understanding of the Quran.', 15, 3, 'fas fa-book-quran', '#2ecc71', 'epic', 200, 600, 8),
('Hadith Expert', 'hadith-expert', 'Become expert in Hadith studies', 'You have mastered the science of Hadith and its authentication.', 15, 3, 'fas fa-quote-left', '#8e44ad', 'epic', 200, 600, 8),
('Fiqh Expert', 'fiqh-expert', 'Become expert in Islamic jurisprudence', 'You have gained deep understanding of Islamic law and its applications.', 15, 3, 'fas fa-balance-scale', '#16a085', 'epic', 200, 600, 8),
('Aqeedah Expert', 'aqeedah-expert', 'Become expert in Islamic creed', 'You have mastered the fundamental beliefs and theology of Islam.', 15, 3, 'fas fa-heart', '#e74c3c', 'epic', 200, 600, 8),
('Seerah Expert', 'seerah-expert', 'Become expert in Prophet\'s biography', 'You have gained comprehensive knowledge of the Prophet\'s life and teachings.', 15, 3, 'fas fa-user-circle', '#f39c12', 'epic', 200, 600, 8),
('Tafseer Expert', 'tafseer-expert', 'Become expert in Quranic interpretation', 'You have mastered the art and science of Quranic exegesis.', 15, 3, 'fas fa-search', '#3498db', 'epic', 200, 600, 8),
('Arabic Scholar', 'arabic-scholar', 'Become proficient in Arabic language', 'You have gained strong command of the Arabic language for Islamic studies.', 15, 3, 'fas fa-language', '#e67e22', 'epic', 200, 600, 8),
('Islamic History Expert', 'islamic-history-expert', 'Become expert in Islamic history', 'You have gained comprehensive knowledge of Islamic civilization and history.', 15, 3, 'fas fa-landmark', '#8e44ad', 'epic', 200, 600, 8),
('Comparative Religion Expert', 'comparative-religion-expert', 'Become expert in comparative religion', 'You have gained knowledge of other religions for better understanding of Islam.', 15, 3, 'fas fa-globe', '#16a085', 'epic', 200, 600, 8),
('Islamic Art Expert', 'islamic-art-expert', 'Become expert in Islamic art and culture', 'You have gained appreciation and knowledge of Islamic artistic traditions.', 15, 3, 'fas fa-palette', '#9b59b6', 'epic', 200, 600, 8),

-- COMMUNITY LEADERSHIP ACHIEVEMENTS
('Mentor', 'mentor', 'Mentor 5 new users', 'You are helping new members succeed in their learning journey.', 16, 3, 'fas fa-hands-helping', '#16a085', 'rare', 100, 400, 5),
('Moderator', 'moderator', 'Help moderate community discussions', 'You are helping maintain a positive and respectful community environment.', 16, 3, 'fas fa-shield-alt', '#e74c3c', 'rare', 120, 450, 6),
('Event Coordinator', 'event-coordinator', 'Organize 5 community events', 'You are bringing the community together through meaningful events.', 16, 3, 'fas fa-calendar-plus', '#f39c12', 'rare', 130, 500, 6),
('Discussion Leader', 'discussion-leader', 'Lead 20 meaningful discussions', 'You are facilitating important conversations and knowledge sharing.', 16, 3, 'fas fa-comments', '#8e44ad', 'rare', 110, 420, 5),
('Knowledge Keeper', 'knowledge-keeper', 'Maintain and update 10 wiki pages', 'You are preserving and improving our collective knowledge base.', 16, 3, 'fas fa-book', '#2ecc71', 'rare', 140, 550, 6),
('Community Builder', 'community-builder', 'Help build a thriving community section', 'You are contributing to the growth and vitality of our community.', 16, 3, 'fas fa-users', '#3498db', 'rare', 150, 600, 7),
('Ambassador', 'ambassador', 'Represent the community in external activities', 'You are representing our community values and knowledge externally.', 16, 3, 'fas fa-flag', '#e67e22', 'epic', 200, 700, 8),
('Visionary', 'visionary', 'Propose and implement community improvements', 'You are shaping the future of our community with your innovative ideas.', 16, 3, 'fas fa-lightbulb', '#f1c40f', 'epic', 250, 800, 9),
('Legend', 'legend', 'Be recognized as a community legend', 'Your contributions have made you a legendary figure in our community.', 16, 3, 'fas fa-crown', '#9b59b6', 'legendary', 500, 1500, 12);

-- Add requirements for the new achievements
INSERT INTO `achievement_requirements` (`achievement_id`, `requirement_type`, `requirement_value`, `requirement_operator`) VALUES
-- Daily Activities Requirements
(29, 'login_time', '{"hour": 6, "before": true}', '>='),
(30, 'login_time', '{"hour": 22, "after": true}', '>='),
(31, 'login_streak', '{"days": 7}', '>='),
(32, 'login_streak', '{"days": 30}', '>='),
(33, 'login_streak', '{"days": 100}', '>='),
(34, 'login_count', '{"count": 1}', '>='),
(35, 'weekend_login_streak', '{"weekends": 5}', '>='),
(36, 'holiday_login', '{"holidays": 3}', '>='),

-- Learning Paths Requirements
(37, 'course_completion', '{"course": "quran_surahs", "count": 5}', '>='),
(38, 'course_completion', '{"course": "quran_surahs", "count": 10}', '>='),
(39, 'course_completion', '{"course": "quran_surahs", "count": 20}', '>='),
(40, 'hadith_study', '{"collection": "bukhari", "count": 25}', '>='),
(41, 'hadith_study', '{"collection": "muslim", "count": 50}', '>='),
(42, 'hadith_study', '{"collection": "authentic", "count": 100}', '>='),
(43, 'course_completion', '{"course": "fiqh_basics", "count": 1}', '>='),
(44, 'course_completion', '{"course": "aqeedah", "count": 1}', '>='),
(45, 'course_completion', '{"course": "seerah", "count": 1}', '>='),
(46, 'course_completion', '{"course": "tafseer", "count": 1}', '>='),

-- Social Features Requirements
(47, 'profile_completion', '{"percentage": 100}', '>='),
(48, 'photo_upload', '{"count": 1}', '>='),
(49, 'status_post', '{"count": 1}', '>='),
(50, 'message_sent', '{"count": 100}', '>='),
(51, 'group_created', '{"count": 1}', '>='),
(52, 'event_created', '{"count": 1}', '>='),
(53, 'content_shared', '{"count": 50}', '>='),
(54, 'user_tagged', '{"count": 25}', '>='),
(55, 'notification_response', '{"count": 100}', '>='),
(56, 'friend_count', '{"count": 50}', '>='),

-- Content Quality Requirements
(57, 'grammar_perfect_posts', '{"count": 20}', '>='),
(58, 'citations_included', '{"count": 25}', '>='),
(59, 'facts_verified', '{"count": 50}', '>='),
(60, 'editor_featured', '{"count": 1}', '>='),
(61, 'single_post_likes', '{"count": 100}', '>='),
(62, 'educational_articles', '{"count": 50}', '>='),
(63, 'research_articles', '{"count": 20}', '>='),
(64, 'translations', '{"count": 10}', '>='),
(65, 'multimedia_posts', '{"count": 25}', '>='),
(66, 'content_collections', '{"count": 20}', '>='),

-- Platform Mastery Requirements
(67, 'advanced_search_usage', '{"count": 50}', '>='),
(68, 'settings_customized', '{"percentage": 100}', '>='),
(69, 'keyboard_shortcuts', '{"count": 20}', '>='),
(70, 'mobile_usage_days', '{"days": 30}', '>='),
(71, 'features_used', '{"count": 15}', '>='),
(72, 'help_provided', '{"count": 10}', '>='),
(73, 'bugs_reported', '{"count": 5}', '>='),
(74, 'feedback_provided', '{"count": 20}', '>='),
(75, 'tutorials_created', '{"count": 5}', '>='),
(76, 'early_feature_adoption', '{"count": 1}', '>='),

-- Special Occasions Requirements
(77, 'birthday_login', '{"count": 1}', '>='),
(78, 'anniversary_login', '{"years": 1}', '>='),
(79, 'total_points', '{"points": 1000}', '>='),
(80, 'user_level', '{"level": 25}', '>='),
(81, 'achievements_earned', '{"count": 50}', '>='),
(82, 'total_points', '{"points": 5000}', '>='),
(83, 'login_streak', '{"days": 365}', '>='),
(84, 'community_recognition', '{"type": "top_member"}', '='),

-- Seasonal Events Requirements
(85, 'ramadan_activity', '{"days": 30}', '>='),
(86, 'eid_participation', '{"count": 1}', '>='),
(87, 'hajj_help', '{"count": 1}', '>='),
(88, 'umrah_support', '{"count": 1}', '>='),
(89, 'laylat_al_qadr', '{"count": 1}', '>='),
(90, 'ashura_learning', '{"count": 1}', '>='),
(91, 'mawlid_celebration', '{"count": 1}', '>='),
(92, 'winter_activity', '{"months": 3}', '>='),
(93, 'summer_learning', '{"months": 3}', '>='),

-- Progressive Series Requirements
(94, 'friend_count', '{"count": 5}', '>='),
(95, 'friend_count', '{"count": 15}', '>='),
(96, 'friend_count', '{"count": 30}', '>='),
(97, 'content_created', '{"count": 5}', '>='),
(98, 'content_created', '{"count": 25}', '>='),
(99, 'content_created', '{"count": 100}', '>='),
(100, 'wiki_edits', '{"count": 10}', '>='),
(101, 'wiki_edits', '{"count": 50}', '>='),
(102, 'wiki_edits', '{"count": 200}', '>='),
(103, 'learning_activities', '{"count": 10}', '>='),
(104, 'learning_activities', '{"count": 50}', '>='),
(105, 'learning_activities', '{"count": 200}', '>='),

-- Expertise Areas Requirements
(106, 'expertise_area', '{"area": "quran", "level": "expert"}', '='),
(107, 'expertise_area', '{"area": "hadith", "level": "expert"}', '='),
(108, 'expertise_area', '{"area": "fiqh", "level": "expert"}', '='),
(109, 'expertise_area', '{"area": "aqeedah", "level": "expert"}', '='),
(110, 'expertise_area', '{"area": "seerah", "level": "expert"}', '='),
(111, 'expertise_area', '{"area": "tafseer", "level": "expert"}', '='),
(112, 'expertise_area', '{"area": "arabic", "level": "expert"}', '='),
(113, 'expertise_area', '{"area": "islamic_history", "level": "expert"}', '='),
(114, 'expertise_area', '{"area": "comparative_religion", "level": "expert"}', '='),
(115, 'expertise_area', '{"area": "islamic_art", "level": "expert"}', '='),

-- Community Leadership Requirements
(116, 'users_mentored', '{"count": 5}', '>='),
(117, 'moderation_actions', '{"count": 50}', '>='),
(118, 'events_organized', '{"count": 5}', '>='),
(119, 'discussions_led', '{"count": 20}', '>='),
(120, 'wiki_pages_maintained', '{"count": 10}', '>='),
(121, 'community_sections_built', '{"count": 1}', '>='),
(122, 'external_representation', '{"count": 1}', '>='),
(123, 'community_improvements', '{"count": 1}', '>='),
(124, 'legendary_status', '{"type": "community_legend"}', '=');
