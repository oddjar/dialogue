<?php
/**
 * Plugin Name: Dialogue 
 * Description: Create and embed a custom chat bot for OpenAI's Dialogue.
 * Version: 1.0
 * Author: Johnathon Williams
 * Author URI: https://glug.blog
 * License: GPL2
 * Plugin URI: https://glug.blog/dialogue
 */

// Add settings menu
function dialogue_add_settings_menu() {
    add_options_page('Dialogue Settings', 'Dialogue Settings', 'manage_options', 'dialogue-settings', 'dialogue_settings_page');
}
add_action('admin_menu', 'dialogue_add_settings_menu');

// Settings page content
function dialogue_settings_page() {
    ?>
    <div class="wrap">
    <h1>Dialogue Settings</h1>
    <form method="post" action="options.php">
    <?php
    settings_fields('dialogue-settings-group');
    do_settings_sections('dialogue-settings-group');
    ?>
    <table class="form-table">
    <tr valign="top">
    <th scope="row">OpenAI API Key</th>
    <td><input type="text" name="dialogue_openai_api_key" value="<?php echo esc_attr(get_option('dialogue_openai_api_key')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">OpenAI Model</th>
    <td>
    <select name="dialogue_openai_model">
    <option value="gpt-4" <?php selected(get_option('dialogue_openai_model'), 'gpt-4'); ?>>gpt-4</option>
    <option value="gpt-3.5-turbo" <?php selected(get_option('dialogue_openai_model'), 'gpt-3.5-turbo'); ?>>gpt-3.5-turbo</option>
    </select>
    </td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Result Background Color</th>
    <td><input type="text" class="color-picker" name="dialogue_result_background_color" value="<?php echo esc_attr(get_option('dialogue_result_background_color', '#E6EFFF')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Prompt Background Color</th>
    <td><input type="text" class="color-picker" name="dialogue_prompt_background_color" value="<?php echo esc_attr(get_option('dialogue_prompt_background_color', '#E9E9E9')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Font Color</th>
    <td><input type="text" class="color-picker" name="dialogue_font_color" value="<?php echo esc_attr(get_option('dialogue_font_color', '#000000')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Date and Time Font Color</th>
    <td><input type="text" class="color-picker" name="dialogue_date_and_time_font_color" value="<?php echo esc_attr(get_option('dialogue_date_and_time_font_color', '#999999')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Send Button Background Color</th>
    <td><input type="text" class="color-picker" name="dialogue_send_button_background_color" value="<?php echo esc_attr(get_option('dialogue_send_button_background_color', '#007bff')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Send Button Font Color</th>
    <td><input type="text" class="color-picker" name="dialog_send_button_font_color" value="<?php echo esc_attr(get_option('dialog_send_button_font_color', '#ffffff')); ?>" /></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Loading Font Color</th>
    <td><input type="text" class="color-picker" name="dialog_loading_font_color" value="<?php echo esc_attr(get_option('dialog_loading_font_color', '#ff0000')); ?>" /></td>
    </tr>
    </table>
    <?php submit_button(); ?>
    </form>
    </div>
    <script type="text/javascript">
    (function($) {
        $(document).ready(function() {
            $('.color-picker').wpColorPicker();
        });
    })(jQuery);
    </script>
    <?php
}

// Register and define settings
function dialogue_register_settings() {
    register_setting('dialogue-settings-group', 'dialogue_openai_api_key', 'dialogue_validate_openai_api_key');
    register_setting('dialogue-settings-group', 'dialogue_openai_model', 'dialogue_validate_openai_model');
    register_setting('dialogue-settings-group', 'dialogue_result_background_color', 'sanitize_hex_color');
    register_setting('dialogue-settings-group', 'dialogue_prompt_background_color', 'sanitize_hex_color');
    register_setting('dialogue-settings-group', 'dialogue_font_color', 'sanitize_hex_color');
    register_setting('dialogue-settings-group', 'dialogue_date_and_time_font_color', 'sanitize_hex_color');
    register_setting('dialogue-settings-group', 'dialogue_send_button_background_color', 'sanitize_hex_color');
    register_setting('dialogue-settings-group', 'dialog_send_button_font_color', 'sanitize_hex_color');
    register_setting('dialogue-settings-group', 'dialog_loading_font_color', 'sanitize_hex_color');
}
add_action('admin_init', 'dialogue_register_settings');

// Validate OpenAI API Key
function dialogue_validate_openai_api_key($input) {
    return trim($input);
}

// Validate OpenAI Model
function dialogue_validate_openai_model($input) {
    $allowed_models = array('gpt-4', 'gpt-3.5-turbo');
    return in_array($input, $allowed_models) ? $input : 'gpt-3.5-turbo';
}

// Shortcode
function dialogue_shortcode() {
    ob_start();
    ?>
    <div id="dialogue-container">
        <div id="dialogue-messages"></div>
        <div id="dialogue-input-container">
            <textarea id="dialogue-input" placeholder="Type your message here..."></textarea>
            <button id="dialogue-send" class="button">Send</button>
        </div>
    </div>
    <style>

    #dialogue-container {

    }

    #dialogue-input-container {

    }

    .dialogue-response, .dialogue-prompt {
        padding:15px 30px;
        border-radius:10px;
        margin-bottom:10px;
        font-family: 'Consolas', 'Source Code Pro', 'Anonymous Pro', 'Droid Sans Mono', 'Fira Code', monospace;
        color: <?php echo esc_attr(get_option('dialogue_font_color', '#000000')); ?>;
    }
    
    .dialogue-response {
        background-color: <?php echo esc_attr(get_option('dialogue_result_background_color', '#E6EFFF')); ?>;
    }
    
    .dialogue-prompt {
        background-color: <?php echo esc_attr(get_option('dialogue_prompt_background_color', '#E9E9E9')); ?>;
    }
    
    .dialogue-loading {
        color: <?php echo esc_attr(get_option('dialog_loading_font_color', '#ff0000')); ?>;
        margin-bottom: 10px;
    }

    .dialogue-timestamp {
        font-size: 0.8rem;
        color: <?php echo esc_attr(get_option('dialogue_date_and_time_font_color', '#999999')); ?>;
        text-align: right;
        margin-bottom: 25px;
        margin-top:-5px;
    }

    #dialogue-input {
        box-sizing: border-box;
        width:100%;
        padding:20px 30px;
        border-radius:10px;
        margin:0 auto;
        border:1px solid #ccc;
        min-height:200px;
        font-family: 'Consolas', 'Source Code Pro', 'Anonymous Pro', 'Droid Sans Mono', 'Fira Code', monospace;
        color: <?php echo esc_attr(get_option('dialogue_font_color', '#000000')); ?>;
    }

    #dialogue-send {
        display: inline-block;
        width: 100%;
        margin: 10px 0;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        background-color: <?php echo esc_attr(get_option('dialogue_send_button_background_color', '#007bff')); ?>;
        border: 1px solid <?php echo esc_attr(get_option('dialogue_send_button_background_color', '#007bff')); ?>;
        color: <?php echo esc_attr(get_option('dialog_send_button_font_color', '#ffffff')); ?>;
        text-decoration: none;
        white-space: nowrap;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    #dialogue-send-input:hover, #dialogue-send-input:focus {
        color: #fff;
        background-color: #0056b3;
        border-color: #004b9a;
    }

    #dialogue-send:focus, #dialogue-send.focus {
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    #dialogue-send:disabled, #dialogue-send.disabled {
        opacity: 0.65;
        box-shadow: none;
    }
    
    @keyframes fadeInOut {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .dialogue-loading-animation {
        animation: fadeInOut 1.5s infinite;
    }
    
    </style>
    <script>
    var dialogue_ajax_object = {ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>'};

    (function($) {
        $(document).ready(function() {
            var isLoading = false;
            var chatHistory = [];
            var md = window.markdownit();
            
            $('#dialogue-send').on('click', function() {
                if (!isLoading) {
                    sendMessage();
                }
            });
            
            $('#dialogue-input').on('keypress', function(e) {
                if (e.which === 13 && !isLoading) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            
            function sendMessage() {
                var prompt = $('#dialogue-input').val();
                if (prompt.trim() === '') return;

                var timestamp = new Date().toLocaleString();

                isLoading = true;
                $('#dialogue-input').val('');
                chatHistory.push({ role: 'user', content: prompt });
                $('#dialogue-messages').append('<div class="dialogue-prompt">' + prompt + '</div>');
                $('#dialogue-messages').append('<div class="dialogue-timestamp">' + timestamp + '</div>');
                $('#dialogue-messages').append('<div class="dialogue-loading dialogue-loading-animation">Loading...</div>');

                $.post(dialogue_ajax_object.ajax_url, {
                    action: 'dialogue_send_message',
                    chat_history: JSON.stringify(chatHistory)
                }, function(response) {
                    $('.dialogue-loading').remove();
                    isLoading = false;

                    if (response.success) {
                        chatHistory.push({ role: 'assistant', content: response.data });
                        $('#dialogue-messages').append('<div class="dialogue-response">' + md.render(response.data) + '</div>');
                        Prism.highlightAll(); // Add this line to initialize Prism.js
                        $('#dialogue-messages').append('<div class="dialogue-timestamp">' + timestamp + '</div>');
                    } else {
                        $('#dialogue-messages').append('<div class="dialogue-error">Error: Unable to get a response.</div>');
                    }
                });
            }
        });
    })(jQuery);

    
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('dialogue', 'dialogue_shortcode');

function dialogue_enqueue_colorpicker( $hook_suffix ) {
    // Check if it's the plugin settings page
 
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
}

add_action( 'admin_enqueue_scripts', 'dialogue_enqueue_colorpicker' );


// AJAX handling
function dialogue_send_message() {
    $chat_history = json_decode(stripslashes($_POST['chat_history']), true);
    $openai_api_key = get_option('dialogue_openai_api_key');
    $openai_model = get_option('dialogue_openai_model');
    
    $url = 'https://api.openai.com/v1/chat/completions';
    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $openai_api_key
    );
    $body = array(
        'model' => $openai_model,
        'messages' => $chat_history
    );
            
    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($body),
        'timeout' => 90
    ));
    
    if (!is_wp_error($response)) {
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        $chat_response = $response_data['choices'][0]['message']['content'];
        wp_send_json_success($chat_response);
    } else {
        wp_send_json_error('Error: Unable to get a response from the OpenAI API.');
    }
}

add_action('wp_ajax_dialogue_send_message', 'dialogue_send_message');
add_action('wp_ajax_nopriv_dialogue_send_message', 'dialogue_send_message');

// Enqueue scripts
function dialogue_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('markdown-it', plugin_dir_url(__FILE__) . 'assets/js/markdown-it.min.js', array(), '1.0.0', true);
    wp_enqueue_script('prism', plugin_dir_url(__FILE__) . 'assets/js/prism.js', array(), '1.0.0', true);
    wp_enqueue_style('prism', plugin_dir_url(__FILE__) . 'assets/css/prism.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'dialogue_enqueue_scripts');