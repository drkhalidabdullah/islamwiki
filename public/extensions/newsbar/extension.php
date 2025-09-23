<?php
/**
 * News Bar Extension
 * Displays news updates at the top of pages
 */

class NewsBarExtension {
    public $name = 'News Bar';
    public $version = '0.0.1';
    public $description = 'Displays news updates at the top of pages';
    public $enabled = false;
    public $settings = [];
    
    public function __construct() {
        $this->enabled = function_exists('get_system_setting') ? get_system_setting('extension_newsbar_enabled', false) : false;
        $this->loadSettings();
    }
    
    private function loadSettings() {
        // Check if get_system_setting function exists (database connection available)
        if (function_exists('get_system_setting')) {
            $this->settings = [
                'position' => get_system_setting('extension_newsbar_position', 'top'),
                'animation_speed' => get_system_setting('extension_newsbar_animation_speed', '20'),
                'auto_pause' => get_system_setting('extension_newsbar_auto_pause', true),
                'show_controls' => get_system_setting('extension_newsbar_show_controls', true),
                'news_items' => get_system_setting('extension_newsbar_news_items', json_encode([
                    [
                        'time' => '2 hours ago',
                        'text' => 'New Islamic Wiki feature: Enhanced search with AI-powered suggestions'
                    ],
                    [
                        'time' => '5 hours ago',
                        'text' => 'Community milestone: 1,000+ articles published on Islamic topics'
                    ],
                    [
                        'time' => '1 day ago',
                        'text' => 'Ramadan 2024: Special collection of fasting and prayer articles now available'
                    ],
                    [
                        'time' => '2 days ago',
                        'text' => 'New editor tools: Improved article creation and editing experience'
                    ]
                ]))
            ];
        } else {
            // Default settings when database is not available
            $this->settings = [
                'position' => 'top',
                'animation_speed' => '20',
                'auto_pause' => true,
                'show_controls' => true,
                'news_items' => json_encode([
                    [
                        'time' => '2 hours ago',
                        'text' => 'New Islamic Wiki feature: Enhanced search with AI-powered suggestions'
                    ],
                    [
                        'time' => '5 hours ago',
                        'text' => 'Community milestone: 1,000+ articles published on Islamic topics'
                    ],
                    [
                        'time' => '1 day ago',
                        'text' => 'Ramadan 2024: Special collection of fasting and prayer articles now available'
                    ],
                    [
                        'time' => '2 days ago',
                        'text' => 'New editor tools: Improved article creation and editing experience'
                    ]
                ])
            ];
        }
    }
    
    public function render() {
        if (!$this->enabled) return;
        
        // Don't show on maintenance mode unless user is logged in
        if (is_maintenance_mode() && !is_logged_in()) return;
        
        $news_items = is_string($this->settings['news_items']) ? 
            json_decode($this->settings['news_items'], true) : 
            $this->settings['news_items'];
        
        echo '<div class="newsbar">';
        echo '<div class="newsbar-content">';
        echo '<div class="newsbar-left">';
        echo '<div class="newsbar-label">';
        echo '<i class="iw iw-bullhorn"></i>';
        echo '<span>Latest News</span>';
        echo '</div>';
        echo '</div>';
        echo '<div class="newsbar-center">';
        echo '<div class="newsbar-ticker">';
        echo '<div class="newsbar-items">';
        
        foreach ($news_items as $item) {
            echo '<div class="newsbar-item">';
            echo '<span class="newsbar-time">' . htmlspecialchars($item['time']) . '</span>';
            echo '<span class="newsbar-text">' . htmlspecialchars($item['text']) . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="newsbar-right">';
        echo '<div class="newsbar-controls">';
        echo '<button class="newsbar-pause" onclick="toggleNewsbarPause()" title="Pause/Resume">';
        echo '<i class="iw iw-pause"></i>';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    public function loadAssets() {
        if (!$this->enabled) return;
        
        // Load CSS
        echo '<link rel="stylesheet" href="/extensions/newsbar/assets/css/newsbar.css">';
    }
    
    public function loadScripts() {
        if (!$this->enabled) return;
        
        // Load JS at the end of body
        echo '<script src="/extensions/newsbar/assets/js/newsbar.js"></script>';
    }
    
    public function getSettingsForm() {
        $news_items = is_string($this->settings['news_items']) ? 
            json_decode($this->settings['news_items'], true) : 
            $this->settings['news_items'];
        
        $form = '<div class="extension-settings">';
        $form .= '<h4>News Bar Settings</h4>';
        
        // Enable/Disable
        $form .= '<div class="form-group">';
        $form .= '<label>';
        $form .= '<input type="checkbox" name="extension_newsbar_enabled" value="1" ' . ($this->enabled ? 'checked' : '') . '> ';
        $form .= 'Enable News Bar';
        $form .= '</label>';
        $form .= '</div>';
        
        // Position
        $form .= '<div class="form-group">';
        $form .= '<label for="extension_newsbar_position">Position:</label>';
        $form .= '<select name="extension_newsbar_position" id="extension_newsbar_position">';
        $form .= '<option value="top" ' . ($this->settings['position'] === 'top' ? 'selected' : '') . '>Top</option>';
        $form .= '<option value="bottom" ' . ($this->settings['position'] === 'bottom' ? 'selected' : '') . '>Bottom</option>';
        $form .= '</select>';
        $form .= '</div>';
        
        // Animation Speed
        $form .= '<div class="form-group">';
        $form .= '<label for="extension_newsbar_animation_speed">Animation Speed (seconds):</label>';
        $form .= '<input type="number" name="extension_newsbar_animation_speed" id="extension_newsbar_animation_speed" value="' . $this->settings['animation_speed'] . '" min="5" max="60">';
        $form .= '</div>';
        
        // Show Controls
        $form .= '<div class="form-group">';
        $form .= '<label>';
        $form .= '<input type="checkbox" name="extension_newsbar_show_controls" value="1" ' . ($this->settings['show_controls'] ? 'checked' : '') . '> ';
        $form .= 'Show Controls';
        $form .= '</label>';
        $form .= '</div>';
        
        // News Items
        $form .= '<div class="form-group">';
        $form .= '<label>News Items:</label>';
        $form .= '<div id="news-items-container">';
        
        foreach ($news_items as $index => $item) {
            $form .= '<div class="news-item-row">';
            $form .= '<input type="text" name="news_item_time[]" placeholder="Time (e.g., 2 hours ago)" value="' . htmlspecialchars($item['time']) . '">';
            $form .= '<input type="text" name="news_item_text[]" placeholder="News text" value="' . htmlspecialchars($item['text']) . '">';
            $form .= '<button type="button" onclick="removeNewsItem(this)">Remove</button>';
            $form .= '</div>';
        }
        
        $form .= '</div>';
        $form .= '<button type="button" onclick="addNewsItem()">Add News Item</button>';
        $form .= '</div>';
        
        $form .= '</div>';
        
        return $form;
    }
    
    public function saveSettings($data) {
        $settings = [];
        
        // Basic settings
        $settings['extension_newsbar_enabled'] = isset($data['extension_newsbar_enabled']) ? 1 : 0;
        $settings['extension_newsbar_position'] = sanitize_input($data['extension_newsbar_position'] ?? 'top');
        $settings['extension_newsbar_animation_speed'] = (int)($data['extension_newsbar_animation_speed'] ?? 20);
        $settings['extension_newsbar_show_controls'] = isset($data['extension_newsbar_show_controls']) ? 1 : 0;
        
        // News items
        $news_items = [];
        if (isset($data['news_item_time']) && isset($data['news_item_text'])) {
            for ($i = 0; $i < count($data['news_item_time']); $i++) {
                if (!empty($data['news_item_time'][$i]) && !empty($data['news_item_text'][$i])) {
                    $news_items[] = [
                        'time' => sanitize_input($data['news_item_time'][$i]),
                        'text' => sanitize_input($data['news_item_text'][$i])
                    ];
                }
            }
        }
        
        $settings['extension_newsbar_news_items'] = json_encode($news_items);
        
        // Save to database
        $saved = 0;
        foreach ($settings as $key => $value) {
            $type = is_bool($value) || $value === 0 || $value === 1 ? 'boolean' : 'string';
            if (set_system_setting($key, $value, $type)) {
                $saved++;
            }
        }
        
        return $saved > 0;
    }
}

// Initialize the extension
$newsbar_extension = new NewsBarExtension();

