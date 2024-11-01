<?php
/**
 * Plugin Name: ST Widgets
 * Plugin URI: https://wordpress.org/plugins/servicetitan-widgets
 * Description: Adding this plugin to your website will ease the process of installing ServiceTitan Widgets.
 * Version: 1.0.0
 * Author: stuser
 */

function stw_register_st_widgets_settings() {
    register_setting(
        'st_widgets_settings',
        'enable_chat_to_text',
        array(
            'type' => 'bool',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false,
            'show_in_rest' => true,
        )
    );
    
    register_setting(
        'st_widgets_settings',
        'chat_to_text_token',
        'sanitize_text_field'
    );

    add_settings_section(
        'chat_to_text_section',
        'Chat to Text',
        '',
        'st-widgets-plugin-options'
    );

    add_settings_field(
        'enable_chat_to_text',
        'Enable Chat to Text',
        'stw_enable_chat_to_text_field_html',
        'st-widgets-plugin-options',
        'chat_to_text_section',
        array('label_for' => 'enable_chat_to_text')
    );

    add_settings_field(
        'chat_to_text_token',
        'Chat to Text Token',
        'stw_chat_to_text_token_field_html',
        'st-widgets-plugin-options',
        'chat_to_text_section',
        array('label_for' => 'chat_to_text_token')
    );

    register_setting(
        'st_widgets_settings',
        'enable_web_scheduler',
        array(
            'type' => 'bool',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false,
            'show_in_rest' => true,
        )
    );
    register_setting(
        'st_widgets_settings',
        'web_scheduler_token',
        'sanitize_text_field'
    );

    add_settings_section(
        'web_scheduler_section',
        'Web Scheduler',
        '',
        'st-widgets-plugin-options'
    );

    add_settings_field(
        'enable_web_scheduler',
        'Enable Web Scheduler',
        'stw_enable_web_scheduler_field_html',
        'st-widgets-plugin-options',
        'web_scheduler_section',
        array('label_for' => 'enable_web_scheduler')
    );

    add_settings_field(
        'web_scheduler_token',
        'Web Scheduler Token',
        'stw_web_scheduler_token_field_html',
        'st-widgets-plugin-options',
        'web_scheduler_section',
        array('label_for' => 'web_scheduler_token')
    );

}

function stw_enable_web_scheduler_field_html() {
    $enable_web_scheduler = get_option( 'enable_web_scheduler' );

    echo '<input type="hidden" value="false" name="enable_web_scheduler">
          <input type="checkbox" value="true" id="enable_web_scheduler" name="enable_web_scheduler"'.($enable_web_scheduler ?' checked' : '').' />';
}

function stw_enable_chat_to_text_field_html() {
    $enable_chat_to_text = get_option( 'enable_chat_to_text' );

    echo '<input type="hidden" value="false" name="enable_chat_to_text">
          <input type="checkbox" value="true" id="enable_chat_to_text" name="enable_chat_to_text"'.($enable_chat_to_text ?' checked' : '').' />';
}

function stw_web_scheduler_token_field_html() {
    $web_scheduler_token = get_option( 'web_scheduler_token' );

    printf(
        '<input type="text" id="web_scheduler_token" name="web_scheduler_token" value="%s" />',
        esc_attr( $web_scheduler_token )
    );
}

function stw_chat_to_text_token_field_html() {
    $chat_to_text_token = get_option( 'chat_to_text_token' );

    printf(
        '<input type="text" id="chat_to_text_token" name="chat_to_text_token" value="%s" />',
        esc_attr( $chat_to_text_token )
    );
}

function stw_settings_page() {
    echo '<div class="wrap"><h1>ST Widgets Plugin Settings</h1><form method="post" action="options.php">';

    settings_fields( 'st_widgets_settings' );
    do_settings_sections( 'st-widgets-plugin-options' );
    submit_button();

    echo '</form></div>';
}

function stw_add_st_widgets_options() {
    add_options_page(
        'ST Widgets Plugin Options',
        'ST Widgets Options',
        'manage_options',
        'st-widgets-plugin-options',
        'stw_settings_page'
    );
}

function stw_hook_web_scheduler_triggers() {
    $enable_web_scheduler = get_option('enable_web_scheduler');

    if ($enable_web_scheduler) {
        ?>
        <script>
            const addHandlers = () => {
                const elements = document.getElementsByClassName('web-scheduler-trigger');

                for (const element of elements) {
                    element.onclick = () => STWidgetManager("ws-open");
                }
            };

            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                addHandlers();
            } else {
                window.addEventListener('DOMContentLoaded', () => {
                    addHandlers();
                });
            }
        </script>
        <?php
    }
}

function stw_hook_st_widgets_snippet() {
    $enable_web_scheduler = get_option('enable_web_scheduler');
    $web_scheduler_token = get_option('web_scheduler_token');

    if ($enable_web_scheduler) {

        $params = array(
            'k' => $web_scheduler_token,
        );

        wp_enqueue_script( 'ws-widget-script', 'https://static.servicetitan.com/webscheduler/shim.js', );
        wp_get_script_tag(array('async' => true));

        wp_add_inline_script( 'ws-widget-script', 'var p = ' . wp_json_encode( $params ) . ';', 'before' );
        wp_add_inline_script( 'ws-widget-script', '
            (function(q,w){q[w]=q[w]||function(){(q[w].q = q[w].q || []).push(arguments)};
                q[w].l=1*new Date();q[w]("init", p.k);
            })(window, "STWidgetManager");
        ', 'before');
    }

    $enable_chat_to_text = get_option('enable_chat_to_text');
    $chat_to_text_token = get_option('chat_to_text_token');

    if ($enable_chat_to_text) {

        $params = array(
            'k' => $chat_to_text_token,
        );

        wp_enqueue_script( 'ct-widget-script', 'https://static.servicetitan.com/text2chat/shim.js');
        wp_get_script_tag(array('async' => true));

        wp_add_inline_script( 'ct-widget-script', 'var p = ' . wp_json_encode( $params ) . ";" , 'before' );
        wp_add_inline_script( 'ct-widget-script', '
            (function(q,w){q[w]=q[w]||function(){(q[w].q = q[w].q || []).push(arguments)};
                q[w].l=1*new Date();q[w]("init", p.k);
            })(window, "T2CWidgetManager");
        ', 'before');
    }
}

function stw_add_async_attribute($tag, $handle) {	
	$scripts_to_async = array('ws-widget-script', 'ct-widget-script');
    
	if (in_array($handle, $scripts_to_async)){
		return str_replace(' src', ' async src', $tag);
	} else {
		return $tag;
	}
}

add_action('admin_init',  'stw_register_st_widgets_settings');

add_action('admin_menu', 'stw_add_st_widgets_options');

add_action('wp_footer', 'stw_hook_web_scheduler_triggers');

add_action('wp_enqueue_scripts', 'stw_hook_st_widgets_snippet');

add_filter('script_loader_tag', 'stw_add_async_attribute', 10, 2);
