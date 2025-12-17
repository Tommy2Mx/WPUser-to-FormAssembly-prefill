<?php
/**
 * Plugin Name: WP User To FormAssembly Prefill
 * Plugin URI: https://github.com/Tommy2Mx
 * Description: Automatically passes logged-in WordPress user data as URL parameters to embedded FormAssembly forms.
 * Version: 1.0.0
 * Author: Tom G
 * Author URI: https://github.com/Tommy2Mx
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: WP-User-to-FormAssembly-Prefill
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FormAssembly_User_Prefill {
    
    private $option_name = 'formassembly_user_prefill_settings';
    private $version = '1.0.0';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('the_content', array($this, 'modify_formassembly_iframes'), 20);
        add_filter('widget_text', array($this, 'modify_formassembly_iframes'), 20);
    }
    
    public function add_admin_menu() {
        add_options_page(
            'FormAssembly User Prefill Settings',
            'WP User To FormAssembly Prefill',
            'manage_options',
            'formassembly-user-prefill',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('formassembly_user_prefill_group', $this->option_name, array($this, 'sanitize_settings'));
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        if (isset($input['parameters'])) {
            $sanitized['parameters'] = sanitize_textarea_field($input['parameters']);
        }
        return $sanitized;
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>FormAssembly User Prefill Settings</h1>
            <p>Configure which WordPress user data should be passed to your FormAssembly forms as URL parameters.</p>
            
            <form method="post" action="options.php">
                <?php settings_fields('formassembly_user_prefill_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="parameters">URL Parameters</label>
                        </th>
                        <td>
                            <textarea name="<?php echo esc_attr($this->option_name); ?>[parameters]" id="parameters" rows="10" cols="50" class="large-text code"><?php 
                                $options = get_option($this->option_name);
                                echo isset($options['parameters']) ? esc_textarea($options['parameters']) : '';
                            ?></textarea>
                            
                            <p class="description">
                                <strong>Format:</strong> Add one parameter per line in format: <code>parameter_name=user_field</code>
                            </p>
                            
                            <p class="description">
                                <strong>Available user fields:</strong><br>
                                <code>ID</code> - WordPress user ID<br>
                                <code>user_email</code> - Email address<br>
                                <code>user_login</code> - Username<br>
                                <code>display_name</code> - Display name<br>
                                <code>first_name</code> - First name<br>
                                <code>last_name</code> - Last name<br>
                                <em>You can also use any custom user meta field key</em>
                            </p>
                            
                            <p class="description">
                                <strong>Examples:</strong><br>
                                <code>email=user_email</code> - Passes user's email as "email" parameter<br>
                                <code>sfid=ID</code> - Passes WordPress user ID as "sfid" parameter<br>
                                <code>firstname=first_name</code> - Passes user's first name<br>
                                <code>lastname=last_name</code> - Passes user's last name
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>How It Works</h2>
            <p>This plugin automatically detects FormAssembly iframes in your content and appends the configured parameters to the iframe src URL.</p>
            
            <p><strong>Example:</strong></p>
            <p>If your iframe src is:<br>
            <code>https://example.tfaforms.net/123</code></p>
            
            <p>And you configure:<br>
            <code>email=user_email</code></p>
            
            <p>It will become:<br>
            <code>https://example.tfaforms.net/123?email=user@example.com</code></p>
            
            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>Parameters are only added when a user is logged in</li>
                <li>Non-logged-in users will see the form without parameters</li>
                <li>Works with FormAssembly plugin shortcodes that generate iframes</li>
                <li>Works with manually embedded FormAssembly iframes</li>
            </ul>
            
            <hr>
            
            <h2>Need Help?</h2>
            <p>Visit the <a href="https://github.com/Tommy2Mx/WPUser-to-FormAssembly-prefill" target="_blank">GitHub repository</a> for documentation and support.</p>
        </div>
        
        <style>
            .wrap h2 { margin-top: 20px; }
            .wrap code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; }
        </style>
        <?php
    }
    
    public function modify_formassembly_iframes($content) {
        // Only process if user is logged in
        if (!is_user_logged_in()) {
            return $content;
        }
        
        // Check if content contains iframe with formassembly or tfaforms
        if (stripos($content, 'iframe') === false || 
            (stripos($content, 'tfaforms') === false && stripos($content, 'formassembly') === false)) {
            return $content;
        }
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Get settings
        $options = get_option($this->option_name);
        if (empty($options['parameters'])) {
            return $content;
        }
        
        // Parse parameter mappings
        $params = array();
        $lines = explode("\n", $options['parameters']);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || substr($line, 0, 1) === '#') {
                continue;
            }
            
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            
            $param_name = trim($parts[0]);
            $user_field = trim($parts[1]);
            
            // Get user field value
            $value = $this->get_user_field_value($current_user, $user_field);
            
            if ($value !== null && $value !== '') {
                $params[$param_name] = $value;
            }
        }
        
        // If no params to add, return original content
        if (empty($params)) {
            return $content;
        }
        
        // Build query string
        $query_string = http_build_query($params);
        
        // Find and modify FormAssembly iframes
        // Pattern matches iframes with src containing tfaforms or formassembly
        $content = preg_replace_callback(
            '/<iframe([^>]*?)src=["\']([^"\']*(?:tfaforms|formassembly)[^"\']*)["\']([^>]*?)>/i',
            function($matches) use ($query_string) {
                $before_attrs = $matches[1];
                $url = $matches[2];
                $after_attrs = $matches[3];
                
                // Add parameters to URL
                $separator = (strpos($url, '?') !== false) ? '&' : '?';
                $new_url = $url . $separator . $query_string;
                
                return '<iframe' . $before_attrs . 'src="' . esc_url($new_url) . '"' . $after_attrs . '>';
            },
            $content
        );
        
        return $content;
    }
    
    private function get_user_field_value($user, $field) {
        switch ($field) {
            case 'ID':
                return $user->ID;
            case 'user_email':
                return $user->user_email;
            case 'user_login':
                return $user->user_login;
            case 'display_name':
                return $user->display_name;
            case 'first_name':
                return get_user_meta($user->ID, 'first_name', true);
            case 'last_name':
                return get_user_meta($user->ID, 'last_name', true);
            default:
                // Try to get as custom user meta field
                $value = get_user_meta($user->ID, $field, true);
                return $value !== false ? $value : null;
        }
    }
}

// Initialize the plugin
new FormAssembly_User_Prefill();