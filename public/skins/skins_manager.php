<?php
/**
 * Skins Manager
 * Handles skin loading, switching, and management
 */

class SkinsManager {
    private $skins_dir;
    private $current_skin;
    private $user_id;
    
    public function __construct() {
        $this->skins_dir = __DIR__;
        $this->user_id = $_SESSION['user_id'] ?? null;
        $this->loadCurrentSkin();
    }
    
    private function loadCurrentSkin() {
        // Check if user has a custom skin preference
        if ($this->user_id && get_system_setting('allow_skin_selection', true)) {
            $user_skin = $this->getUserSkin();
            if ($user_skin) {
                $this->current_skin = $user_skin;
                return;
            }
        }
        
        // Use default skin
        $this->current_skin = get_system_setting('default_skin', 'bismillah');
    }
    
    private function getUserSkin() {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT s.name 
            FROM user_skin_preferences usp 
            JOIN skins s ON usp.skin_id = s.id 
            WHERE usp.user_id = ? AND s.is_active = 1
        ");
        $stmt->execute([$this->user_id]);
        $result = $stmt->fetch();
        
        return $result ? $result['name'] : null;
    }
    
    public function getCurrentSkin() {
        return $this->current_skin;
    }
    
    public function loadSkinAssets() {
        $skin_path = $this->skins_dir . '/' . $this->current_skin;
        
        // Load CSS
        $css_file = $skin_path . '/assets/css/' . $this->current_skin . '.css';
        if (file_exists($css_file)) {
            echo '<link rel="stylesheet" href="/skins/' . $this->current_skin . '/assets/css/' . $this->current_skin . '.css?v=' . time() . '">';
        }
        
        // Load JS
        $js_file = $skin_path . '/assets/js/' . $this->current_skin . '.js';
        if (file_exists($js_file)) {
            echo '<script src="/skins/' . $this->current_skin . '/assets/js/' . $this->current_skin . '.js?v=' . time() . '"></script>';
        }
    }
    
    public function getAllSkins() {
        global $pdo;
        
        $stmt = $pdo->query("
            SELECT * FROM skins 
            WHERE is_active = 1 
            ORDER BY is_default DESC, display_name ASC
        ");
        return $stmt->fetchAll();
    }
    
    public function getSkin($name) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM skins WHERE name = ? AND is_active = 1");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    public function setUserSkin($skin_name) {
        if (!$this->user_id || !get_system_setting('allow_skin_selection', true)) {
            return false;
        }
        
        $skin = $this->getSkin($skin_name);
        if (!$skin) {
            return false;
        }
        
        global $pdo;
        
        // Remove existing preference
        $stmt = $pdo->prepare("DELETE FROM user_skin_preferences WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        
        // Add new preference
        $stmt = $pdo->prepare("
            INSERT INTO user_skin_preferences (user_id, skin_id) 
            VALUES (?, ?)
        ");
        return $stmt->execute([$this->user_id, $skin['id']]);
    }
    
    public function getSkinPreview($skin_name) {
        $skin_path = $this->skins_dir . '/' . $skin_name;
        
        // Check for preview.png in the skin root directory
        $preview_file = $skin_path . '/preview.png';
        if (file_exists($preview_file)) {
            return '/skins/' . $skin_name . '/preview.png';
        }
        
        // Check for preview.png in assets/images directory
        $preview_file = $skin_path . '/assets/images/preview.png';
        if (file_exists($preview_file)) {
            return '/skins/' . $skin_name . '/assets/images/preview.png';
        }
        
        return null;
    }
    
    public function getSkinInfo($skin_name) {
        $skin_path = $this->skins_dir . '/' . $skin_name;
        $info_file = $skin_path . '/skin.json';
        
        if (file_exists($info_file)) {
            $info = json_decode(file_get_contents($info_file), true);
            return $info;
        }
        
        return null;
    }
}

// Global skins manager instance
$skins_manager = new SkinsManager();
