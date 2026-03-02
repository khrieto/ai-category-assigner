<?php
/**
 * Plugin Name: AI Category Assigner
 * Plugin URI:  https://github.com/khrieto/ai-category-assigner
 * Description: Automatically assign categories to your wordpress posts using AI (OpenAI or Pollinations). Supports the most cost‑effective models: GPT‑4o Mini, GPT‑4.1 Mini, and Gemini Fast (Google Gemini 2.5 Flash Lite).
 * Version:     2.0
 * Author:      Khrieto Moirangthem
 * Author URI:  https://github.com/khrieto
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-category-assigner
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AICategoryAssigner {
    
    private $openai_key;
    private $pollinations_key;
    private $categories;
    private $model;
    private $assign_mode;
    
    // Allowed models with provider and display name
    private $allowed_models = array(
        'gpt-4o-mini' => array(
            'provider' => 'openai',
            'name'     => 'GPT‑4o Mini (OpenAI) – Fast & Cost‑Effective'
        ),
        'gpt-4.1-mini' => array(
            'provider' => 'openai',
            'name'     => 'GPT‑4.1 Mini (OpenAI) – Latest Mini Model'
        ),
        'gemini-fast' => array(
            'provider' => 'pollinations',
            'name'     => 'Gemini Fast (Google Gemini 2.5 Flash Lite) – Fast & Free Credits'
        )
    );
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_assign_categories_ai', array($this, 'assign_categories_ajax'));
        add_action('wp_ajax_get_all_posts', array($this, 'get_all_posts_ajax'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook != 'toplevel_page_ai-category-assigner') {
            return;
        }
        wp_enqueue_script('jquery');
    }
    
    public function add_admin_menu() {
        // Changed from add_submenu_page to add_menu_page for top-level menu
        add_menu_page(
            'AI Category Assigner',           // Page title
            'AI Category Assigner',           // Menu title
            'manage_options',                  // Capability required
            'ai-category-assigner',            // Menu slug
            array($this, 'admin_page_html'),   // Function to display the page
            'dashicons-tagcloud',              // Icon (using tag cloud icon - appropriate for categories)
            80                                  // Position (lower number = higher up)
        );
    }
    
    public function init_settings() {
        register_setting('ai_category_assigner', 'openai_api_key');
        register_setting('ai_category_assigner', 'pollinations_api_key');
        register_setting('ai_category_assigner', 'target_categories');
        register_setting('ai_category_assigner', 'ai_model');
        register_setting('ai_category_assigner', 'assign_mode');
        
        $this->openai_key       = get_option('openai_api_key');
        $this->pollinations_key = get_option('pollinations_api_key');
        $this->categories       = get_option('target_categories');
        $this->model            = get_option('ai_model', 'gpt-4o-mini');
        $this->assign_mode      = get_option('assign_mode', 'all');
    }
    
    public function admin_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['settings-updated'])) {
            add_settings_error('ai_category_messages', 'ai_category_message', 'Settings Saved', 'updated');
        }
        
        settings_errors('ai_category_messages');
        ?>
        <div class="wrap">
            <h1>🚀 AI Category Assigner</h1>
            
            <!-- Cost‑effectiveness note -->
            <div class="notice notice-info">
                <p><strong>💡 Why these models?</strong> We've hand‑picked the most effective and affordable AI models for categorization. Using larger models would be overkill – these three give you the best balance of speed, accuracy, and cost.</p>
            </div>
            
            <!-- Settings Form -->
            <div class="card" style="max-width: 800px;">
                <h2>⚙️ Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('ai_category_assigner'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="openai_api_key">OpenAI API Key</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="openai_api_key" 
                                       name="openai_api_key" 
                                       value="<?php echo esc_attr($this->openai_key); ?>" 
                                       class="regular-text" />
                                <p class="description">Required only if you use an OpenAI model. <a href="https://platform.openai.com/api-keys" target="_blank">Get your OpenAI API key</a></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pollinations_api_key">Pollinations.ai API Key</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="pollinations_api_key" 
                                       name="pollinations_api_key" 
                                       value="<?php echo esc_attr($this->pollinations_key); ?>" 
                                       class="regular-text" />
                                <p class="description">Required only if you use the Gemini Fast model. <a href="https://enter.pollinations.ai" target="_blank">Get your Pollinations API key (free credits included)</a></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ai_model">AI Model</label>
                            </th>
                            <td>
                                <select id="ai_model" name="ai_model" class="regular-text">
                                    <?php foreach ($this->allowed_models as $model_value => $model_info): ?>
                                        <option value="<?php echo esc_attr($model_value); ?>" 
                                                <?php selected($this->model, $model_value); ?>>
                                            <?php echo esc_html($model_info['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Choose the AI model that fits your needs. The API key for the corresponding provider must be entered above.</p>
                                <p class="description" style="color: #d63638; font-weight: bold; margin-top: 8px;">
                                    ⚠️ Important: GPT models (OpenAI) require the OpenAI API key; Gemini Fast (Pollinations) requires the Pollinations API key.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="assign_mode">Assignment Mode</label>
                            </th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="assign_mode" value="all" <?php checked($this->assign_mode, 'all'); ?>>
                                        <strong>All posts</strong> – Assign categories to every post, replacing existing categories.
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="assign_mode" value="uncategorized" <?php checked($this->assign_mode, 'uncategorized'); ?>>
                                        <strong>Only uncategorized posts</strong> – Only assign categories to posts that currently have no categories OR only the default "Uncategorized" category. Posts with other categories will be skipped.
                                    </label>
                                </fieldset>
                                <p class="description">Choose whether to process all posts or only those without meaningful categories.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="target_categories">Target Categories</label>
                            </th>
                            <td>
                                <textarea 
                                    id="target_categories" 
                                    name="target_categories" 
                                    rows="5" 
                                    class="large-text" 
                                    placeholder="Enter categories separated by commas&#10;Example: Technology, Business, Health, Lifestyle, Education"><?php echo esc_textarea($this->categories); ?></textarea>
                                <p class="description">Enter the categories you want to assign (comma‑separated). <strong>Case‑insensitive matching</strong> – "technology", "Technology", and "TECHNOLOGY" will all match.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Settings'); ?>
                </form>
            </div>
            
            <!-- Posts Table and Controls -->
            <div class="card" style="max-width: 1200px;">
                <h2>📝 Posts Management</h2>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 15px 0;">
                    <h3 style="margin-top: 0;">How to Use:</h3>
                    <ol>
                        <li>Save your settings with the required API key and target categories. Ensure you use the correct API key for your chosen model (OpenAI for GPT models, Pollinations for Gemini Fast).</li>
                        <li>Click "Load All Posts" to see all your published posts.</li>
                        <li>Click "Assign Categories to All Posts" to process ALL posts (or only uncategorized ones, depending on your selected mode).</li>
                        <li>The system processes posts in batches of 10 to avoid API limits.</li>
                    </ol>
                    <p style="color: #d63638; font-weight: bold; margin-top: 10px; padding: 8px; background: #ffe0e0; border-radius: 4px;">
                        ⚠️ <strong>WARNING:</strong> Clicking "Assign Categories" will permanently modify your posts. 
                        Existing categories will be replaced based on your selected mode. This action CANNOT be undone automatically. 
                        Consider backing up your database before proceeding with large batches.
                    </p>
                </div>
                
                <div style="margin: 20px 0;">
                    <button type="button" id="load-all-posts" class="button button-primary">📥 Load All Posts</button>
                    <button type="button" id="assign-all-categories" class="button button-secondary" disabled>🤖 Assign Categories to All Posts</button>
                    <span id="loading-spinner" style="display: none;">🔄 Processing... <span class="spinner is-active" style="float: none; margin: 0 5px;"></span></span>
                </div>
                
                <div id="model-info" style="background: #f0f0f1; padding: 12px; border-left: 4px solid #0073aa; margin: 15px 0; display: none; border-radius: 4px;">
                    <strong>🤖 Current Model:</strong> <span id="current-model-name"></span> | 
                    <strong>📦 Processing:</strong> All posts in batches of 10
                </div>
                
                <!-- Progress Bar -->
                <div id="progress-container" style="display: none; margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span id="progress-status">Processing posts...</span>
                        <span id="progress-percentage">0%</span>
                    </div>
                    <div style="background: #f0f0f1; border-radius: 10px; height: 20px;">
                        <div id="progress-bar" style="background: #0073aa; height: 100%; width: 0%; border-radius: 10px; transition: width 0.3s;"></div>
                    </div>
                    <div id="batch-info" style="margin-top: 5px; font-size: 12px; color: #666;"></div>
                </div>
                
                <div id="posts-table-container" style="display: none;">
                    <div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong>📊 Total Posts Loaded:</strong> <span id="total-posts-count">0</span>
                    </div>
                    <table class="wp-list-table widefat fixed striped" id="posts-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="40%">Title</th>
                                <th width="30%">Current Categories</th>
                                <th width="25%">AI Assigned Category</th>
                            </tr>
                        </thead>
                        <tbody id="posts-table-body">
                            <!-- Posts will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <div id="results-message" style="margin-top: 20px; padding: 10px; border-radius: 4px;"></div>
            </div>
            
            <style>
                .category-pill {
                    display: inline-block;
                    background: #e0e0e0;
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 12px;
                    margin: 2px;
                }
                .ai-category {
                    font-weight: bold;
                    color: #0073aa;
                }
                .success { background: #f0fff0; color: green; border-left: 4px solid #46b450; padding: 10px; }
                .error   { background: #fff0f0; color: red; border-left: 4px solid #dc3232; padding: 10px; }
                .warning { background: #fff9e0; color: orange; border-left: 4px solid #ffb900; padding: 10px; }
                .info    { background: #f0f6ff; color: #0073aa; border-left: 4px solid #0073aa; padding: 10px; }
                #progress-bar { transition: width 0.5s ease-in-out; }
            </style>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                let allPosts = [];
                let currentBatch = 0;
                const batchSize = 10;
                
                // Model names for display (sync with PHP)
                const modelNames = {
                    'gpt-4o-mini': 'GPT‑4o Mini (OpenAI) – Fast & Cost‑Effective',
                    'gpt-4.1-mini': 'GPT‑4.1 Mini (OpenAI) – Latest Mini Model',
                    'gemini-fast': 'Gemini Fast (Google Gemini 2.5 Flash Lite) – Fast & Free Credits'
                };
                
                updateModelInfo();
                $('#ai_model').on('change', updateModelInfo);
                
                $('#load-all-posts').on('click', loadAllPosts);
                $('#assign-all-categories').on('click', assignAllCategories);
                
                function updateModelInfo() {
                    var selectedModel = $('#ai_model').val();
                    var modelName = modelNames[selectedModel] || selectedModel;
                    $('#current-model-name').text(modelName);
                    $('#model-info').show();
                }
                
                function loadAllPosts() {
                    $('#loading-spinner').show();
                    $('#load-all-posts').prop('disabled', true);
                    showMessage('🔄 Loading all posts...', 'info');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_all_posts',
                            nonce: '<?php echo wp_create_nonce("get_all_posts_nonce"); ?>'
                        },
                        success: function(response) {
                            $('#loading-spinner').hide();
                            $('#load-all-posts').prop('disabled', false);
                            
                            if (response.success) {
                                allPosts = response.data.posts;
                                $('#posts-table-body').html(response.data.html);
                                $('#total-posts-count').text(response.data.total);
                                $('#posts-table-container').show();
                                $('#assign-all-categories').prop('disabled', false);
                                showMessage('✅ Successfully loaded all ' + response.data.total + ' posts!', 'success');
                            } else {
                                showMessage('❌ Error loading posts: ' + response.data, 'error');
                            }
                        },
                        error: function() {
                            $('#loading-spinner').hide();
                            $('#load-all-posts').prop('disabled', false);
                            showMessage('❌ AJAX error: Could not load posts', 'error');
                        }
                    });
                }
                
                function assignAllCategories() {
                    if (allPosts.length === 0) {
                        showMessage('❌ No posts loaded. Please click "Load All Posts" first.', 'error');
                        return;
                    }
                    
                    var selectedModel = $('#ai_model').val();
                    var modelName = modelNames[selectedModel] || selectedModel;
                    
                    // Enhanced irreversible warning
                    if (!confirm(`⚠️ IRREVERSIBLE ACTION ⚠️\n\nThis will permanently modify your posts:\n\n• ${allPosts.length} posts will be processed\n• Using: ${modelName}\n• Mode: ${$('input[name="assign_mode"]:checked').next('strong').text() || 'All posts'}\n\n✅ Existing categories WILL BE REPLACED\n✅ This action CANNOT be undone automatically\n✅ Consider backing up your database first\n\nAre you ABSOLUTELY SURE you want to continue?`)) {
                        return;
                    }
                    
                    // Second confirmation for extra safety
                    if (!confirm('FINAL WARNING: This will permanently change your post categories. There is NO UNDO. Click OK to proceed or Cancel to abort.')) {
                        return;
                    }
                    
                    $('#loading-spinner').show();
                    $('#assign-all-categories').prop('disabled', true);
                    $('#progress-container').show();
                    
                    currentBatch = 0;
                    processBatch();
                }
                
                function processBatch() {
                    const startIndex = currentBatch * batchSize;
                    const endIndex = Math.min(startIndex + batchSize, allPosts.length);
                    const batchPosts = allPosts.slice(startIndex, endIndex);
                    
                    if (batchPosts.length === 0) {
                        $('#loading-spinner').hide();
                        $('#assign-all-categories').prop('disabled', false);
                        showMessage('🎉 All posts have been processed successfully!', 'success');
                        return;
                    }
                    
                    const progress = Math.round((startIndex / allPosts.length) * 100);
                    updateProgress(progress, currentBatch + 1, Math.ceil(allPosts.length / batchSize));
                    
                    showMessage(`🔄 Processing batch ${currentBatch + 1} of ${Math.ceil(allPosts.length / batchSize)}...`, 'info');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'assign_categories_ai',
                            post_ids: batchPosts.map(post => post.ID),
                            nonce: '<?php echo wp_create_nonce("assign_categories_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                response.data.results.forEach(function(result) {
                                    if (result.success) {
                                        $(`#post-${result.post_id} .ai-category`).html(
                                            '<span class="category-pill" style="background: #e0f7e0; color: #2e7d32;">' + result.assigned_category + '</span>'
                                        );
                                    } else {
                                        $(`#post-${result.post_id} .ai-category`).html(
                                            '<span style="color: #d32f2f;">❌ ' + result.error + '</span>'
                                        );
                                    }
                                });
                                
                                currentBatch++;
                                
                                // Update progress after batch completion
                                const processed = Math.min(currentBatch * batchSize, allPosts.length);
                                const newProgress = Math.round((processed / allPosts.length) * 100);
                                updateProgress(newProgress, currentBatch, Math.ceil(allPosts.length / batchSize));
                                
                                setTimeout(processBatch, 500);
                            } else {
                                $('#loading-spinner').hide();
                                $('#assign-all-categories').prop('disabled', false);
                                showMessage('❌ Error: ' + response.data, 'error');
                            }
                        },
                        error: function() {
                            $('#loading-spinner').hide();
                            $('#assign-all-categories').prop('disabled', false);
                            showMessage('❌ AJAX request failed. Please try again.', 'error');
                        }
                    });
                }
                
                function updateProgress(percentage, currentBatch, totalBatches) {
                    $('#progress-bar').css('width', percentage + '%');
                    $('#progress-percentage').text(percentage + '%');
                    $('#progress-status').text(`Processing: ${currentBatch}/${totalBatches} batches`);
                    $('#batch-info').text(`Batch ${currentBatch} of ${totalBatches} (${Math.ceil(allPosts.length / batchSize)} total batches)`);
                }
                
                function showMessage(message, type) {
                    $('#results-message').removeClass('success error warning info').addClass(type).html(message);
                }
            });
            </script>
        </div>
        <?php
    }
    
    public function get_all_posts_ajax() {
        check_ajax_referer('get_all_posts_nonce', 'nonce');
        
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $total_posts = count($posts);
        
        $html = '';
        foreach ($posts as $post) {
            $categories = wp_get_post_categories($post->ID, array('fields' => 'names'));
            $categories_html = '';
            
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $categories_html .= '<span class="category-pill">' . esc_html($category) . '</span>';
                }
            } else {
                $categories_html = '<em style="color: #999;">No categories</em>';
            }
            
            $html .= '
            <tr id="post-' . $post->ID . '">
                <td>' . $post->ID . '</td>
                <td><strong>' . esc_html($post->post_title) . '</strong></td>
                <td>' . $categories_html . '</td>
                <td class="ai-category">-</td>
            </tr>';
        }
        
        wp_send_json_success(array(
            'html' => $html,
            'total' => $total_posts,
            'posts' => $posts
        ));
    }
    
    public function assign_categories_ajax() {
        check_ajax_referer('assign_categories_nonce', 'nonce');
        
        $model = get_option('ai_model', 'gpt-4o-mini');
        
        // Determine provider and check API key
        if (!isset($this->allowed_models[$model])) {
            wp_send_json_error('Selected model is not allowed.');
        }
        
        $provider = $this->allowed_models[$model]['provider'];
        
        if ($provider === 'openai') {
            $api_key = get_option('openai_api_key');
            if (empty($api_key)) {
                wp_send_json_error('OpenAI API key not set. Please save your settings first.');
            }
        } else { // pollinations
            $api_key = get_option('pollinations_api_key');
            if (empty($api_key)) {
                wp_send_json_error('Pollinations API key not set. Please save your settings first.');
            }
        }
        
        $categories = get_option('target_categories');
        if (empty($categories)) {
            wp_send_json_error('Target categories not set. Please save your settings first.');
        }
        
        // Get assignment mode
        $assign_mode = get_option('assign_mode', 'all');
        
        if (isset($_POST['post_ids']) && is_array($_POST['post_ids'])) {
            $post_ids = array_map('intval', $_POST['post_ids']);
            $posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'numberposts' => -1,
                'post__in' => $post_ids,
                'orderby' => 'post__in'
            ));
        } else {
            $posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'numberposts' => 10,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
        }
        
        if (empty($posts)) {
            wp_send_json_error('No posts found to process.');
        }
        
        // Filter posts based on assignment mode
        if ($assign_mode === 'uncategorized') {
            $default_category_id = get_option('default_category'); // Get default Uncategorized category ID
            $filtered_posts = array();
            foreach ($posts as $post) {
                $post_categories = wp_get_post_categories($post->ID);
                // Include if no categories OR only the default category
                if (empty($post_categories) || (count($post_categories) === 1 && $post_categories[0] == $default_category_id)) {
                    $filtered_posts[] = $post;
                }
            }
            $posts = $filtered_posts;
        }
        
        if (empty($posts)) {
            wp_send_json_success(array(
                'message' => 'No uncategorized posts to process.',
                'results' => array(),
                'model_used' => $model,
                'processed_count' => 0
            ));
        }
        
        $results = array();
        $success_count = 0;
        
        foreach ($posts as $post) {
            $result = $this->assign_category_to_post($post, $api_key, $categories, $model, $provider);
            $results[] = $result;
            if ($result['success']) {
                $success_count++;
            }
        }
        
        $message = "Processed " . count($posts) . " posts. Success: {$success_count}, Failed: " . (count($posts) - $success_count);
        wp_send_json_success(array(
            'message' => $message,
            'results' => $results,
            'model_used' => $model,
            'processed_count' => count($posts)
        ));
    }
    
    private function assign_category_to_post($post, $api_key, $categories, $model, $provider) {
        $title = $post->post_title;
        
        $prompt = $this->build_prompt($title, $categories);
        
        if ($provider === 'openai') {
            $assigned_category = $this->call_openai_api($api_key, $prompt, $model);
        } else {
            $assigned_category = $this->call_pollinations_api($api_key, $prompt, $model);
        }
        
        if (is_wp_error($assigned_category)) {
            return array(
                'post_id' => $post->ID,
                'title' => $title,
                'success' => false,
                'error' => $assigned_category->get_error_message(),
                'assigned_category' => null,
                'model' => $model
            );
        }
        
        $category_id = $this->get_category_id_by_name_case_insensitive($assigned_category);
        
        if (!$category_id) {
            return array(
                'post_id' => $post->ID,
                'title' => $title,
                'success' => false,
                'error' => "Category '{$assigned_category}' not found in WordPress. Please make sure this category exists.",
                'assigned_category' => $assigned_category,
                'model' => $model
            );
        }
        
        $result = wp_set_post_categories($post->ID, array($category_id), false);
        
        return array(
            'post_id' => $post->ID,
            'title' => $title,
            'success' => !is_wp_error($result) && $result,
            'assigned_category' => $assigned_category,
            'category_id' => $category_id,
            'model' => $model
        );
    }
    
    private function build_prompt($title, $categories) {
        return "Analyze the following blog post title and assign it the most relevant category from the provided list. 

CATEGORIES TO CHOOSE FROM: {$categories}

BLOG POST TITLE: \"{$title}\"

INSTRUCTIONS:
- Choose only ONE category that best matches the content
- Return ONLY the category name, nothing else
- If no category fits well, choose the closest match
- Do not add any explanations or additional text

RESPONSE FORMAT: Just the category name";
    }
    
    private function call_openai_api($api_key, $prompt, $model) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        );
        
        $body = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a content categorization assistant. Your task is to assign the most relevant category to blog post titles. Always respond with only the category name and nothing else.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 50,
            'temperature' => 0.3
        );
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            return new WP_Error('openai_error', 'OpenAI API error (' . $response_code . '): ' . $error_message);
        }
        
        $category = trim($data['choices'][0]['message']['content']);
        $category = trim($category, '"\' ');
        
        return $category;
    }
    
    private function call_pollinations_api($api_key, $prompt, $model) {
        $url = 'https://gen.pollinations.ai/v1/chat/completions';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        );
        
        $body = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a content categorization assistant. Your task is to assign the most relevant category to blog post titles. Always respond with only the category name and nothing else.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 50,
            'temperature' => 0.3,
            'stream' => false
        );
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            return new WP_Error('pollinations_error', 'Pollinations API error (' . $response_code . '): ' . $error_message);
        }
        
        $category = trim($data['choices'][0]['message']['content']);
        $category = trim($category, '"\' ');
        
        return $category;
    }
    
    /**
     * Case‑insensitive category matching
     */
    private function get_category_id_by_name_case_insensitive($category_name) {
        // First try exact match
        $category = get_term_by('name', $category_name, 'category');
        if ($category) {
            return $category->term_id;
        }
        
        // Try case‑insensitive search by getting all categories and comparing
        $all_categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false,
        ));
        
        foreach ($all_categories as $cat) {
            if (strtolower($cat->name) === strtolower($category_name)) {
                return $cat->term_id;
            }
        }
        
        // Try partial match
        foreach ($all_categories as $cat) {
            if (strpos(strtolower($cat->name), strtolower($category_name)) !== false || 
                strpos(strtolower($category_name), strtolower($cat->name)) !== false) {
                return $cat->term_id;
            }
        }
        
        return false;
    }
}

new AICategoryAssigner();
