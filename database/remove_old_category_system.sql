-- Remove Old Category System
-- This migration removes the old content_categories system and category_id field
-- in favor of the new wiki_categories system with [[Category:Name]] syntax

-- 1. Remove foreign key constraint from wiki_articles.category_id
ALTER TABLE wiki_articles DROP FOREIGN KEY IF EXISTS wiki_articles_ibfk_1;

-- 2. Remove category_id column from wiki_articles
ALTER TABLE wiki_articles DROP COLUMN IF EXISTS category_id;

-- 3. Drop the old content_categories table
DROP TABLE IF EXISTS content_categories;

-- 4. Remove any indexes related to the old category system
ALTER TABLE wiki_articles DROP INDEX IF EXISTS idx_category_id;
ALTER TABLE wiki_articles DROP INDEX IF EXISTS idx_search_category_status;

-- 5. Update any articles that might have old category references
-- (This is handled by the new wiki category system automatically)
