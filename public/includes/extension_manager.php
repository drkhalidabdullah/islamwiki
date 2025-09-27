<?php
/**
 * Extension Manager
 * Handles loading and managing extensions
 */

class ExtensionManager {
    private $extensions = [];
    private $extensions_dir;
    
    public function __construct() {
        $this->extensions_dir = __DIR__ . '/../extensions';
        $this->loadExtensions();
    }
    
    private function loadExtensions() {
        if (!is_dir($this->extensions_dir)) {
            return;
        }
        
        $extension_dirs = scandir($this->extensions_dir);
        foreach ($extension_dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $extension_path = $this->extensions_dir . '/' . $dir . '/extension.php';
            if (file_exists($extension_path)) {
                require_once $extension_path;
                
                // Get extension class name (assuming it follows the pattern)
                $class_name = ucfirst($dir) . 'Extension';
                if (class_exists($class_name)) {
                    $this->extensions[$dir] = new $class_name();
                }
            }
        }
    }
    
    public function getExtensions() {
        return $this->extensions;
    }
    
    public function getExtension($name) {
        return $this->extensions[$name] ?? null;
    }
    
    public function renderExtensions() {
        foreach ($this->extensions as $extension) {
            if ($extension->enabled) {
                $extension->render();
            }
        }
    }
    
    public function loadExtensionAssets() {
        foreach ($this->extensions as $extension) {
            if ($extension->enabled) {
                $extension->loadAssets();
            }
        }
    }
    
    public function loadExtensionScripts() {
        foreach ($this->extensions as $extension) {
            if ($extension->enabled && method_exists($extension, 'loadScripts')) {
                $extension->loadScripts();
            }
        }
    }
    
    public function getEnabledExtensions() {
        return array_filter($this->extensions, function($extension) {
            return $extension->enabled;
        });
    }
    
    public function getExtensionSettings() {
        $settings = [];
        foreach ($this->extensions as $name => $extension) {
            // Read the current enabled state from database settings
            // Use the extension's own setting key pattern
            $setting_key = $this->getExtensionSettingKey($name);
            $enabled = get_system_setting($setting_key, $extension->enabled);
            
            $settings[$name] = [
                'name' => $extension->name,
                'version' => $extension->version,
                'description' => $extension->description,
                'enabled' => $enabled,
                'settings_form' => method_exists($extension, 'getSettingsForm') ? $extension->getSettingsForm() : ''
            ];
        }
        return $settings;
    }
    
    /**
     * Get the correct setting key for an extension's enabled state
     */
    public function getExtensionSettingKey($extension_name) {
        // Map extension names to their actual setting keys
        $setting_keys = [
            'achievements' => 'achievements_enabled',
            'seo' => 'seo_enabled',
            'newsbar' => 'newsbar_enabled'
        ];
        
        return $setting_keys[$extension_name] ?? 'extension_' . $extension_name . '_enabled';
    }
}

// Global extension manager instance
$extension_manager = new ExtensionManager();

