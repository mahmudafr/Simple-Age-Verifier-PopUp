<?php
/**
 * Plugin Name: Simple Age Verifier
 * Description: Final v3.0 - Popup with On/Off toggle, color controls, text fields, and responsive width/height sliders in %.
 * Version: 3.0
 * Author: Afsar Al Mahmud
 */

if (!defined('ABSPATH')) exit;

/*------------------------------------------
 FRONTEND: Load CSS & JS
-------------------------------------------*/
function sav_enqueue_assets() {
    wp_enqueue_style('sav-style', plugins_url('style.css', __FILE__), array(), '3.0');
    wp_enqueue_script('sav-popup', plugins_url('popup.js', __FILE__), array('jquery'), '3.0', true);
}
add_action('wp_enqueue_scripts', 'sav_enqueue_assets');

/*------------------------------------------
 FRONTEND: Popup HTML
-------------------------------------------*/
function sav_add_age_verification_popup() {
    if (get_option('sav_popup_enabled', 'off') !== 'on') return;
    if (isset($_COOKIE['age_verified']) && $_COOKIE['age_verified'] === 'true') return;

    $headline    = get_option('sav_headline_text', 'Are you over 18?');
    $description = get_option('sav_description_text', 'You must be 18 years or older to enter.');
    $enter_text  = get_option('sav_enter_btn_text', 'I AM 18 OR OLDER');
    $exit_text   = get_option('sav_exit_btn_text', 'I AM UNDER 18');

    $bg          = get_option('sav_bg_color', '#fff');
    $width       = get_option('sav_popup_width', 90);
    $height      = get_option('sav_popup_height', 60);

    $headline_bg = get_option('sav_headline_bg_color', '');
    $headline_cl = get_option('sav_headline_color', '#333');
    $desc_bg     = get_option('sav_description_bg_color', '');
    $desc_cl     = get_option('sav_description_color', '#666');
    $enter_bg    = get_option('sav_enter_bg_color', '#f7b500');
    $enter_cl    = get_option('sav_enter_text_color', '#333');
    $exit_bg     = get_option('sav_exit_bg_color', '#d9534f');
    $exit_cl     = get_option('sav_exit_text_color', '#fff');

    echo "<style>
    #age-verification-popup {
        width: {$width}% !important;
        height: {$height}% !important;
        background: {$bg};
    }
    #age-verification-popup h2 {color: {$headline_cl}; background: {$headline_bg};}
    #age-verification-popup p {color: {$desc_cl}; background: {$desc_bg};}
    #age-btn-enter {background: {$enter_bg}; color: {$enter_cl};}
    #age-btn-exit {background: {$exit_bg}; color: {$exit_cl};}
    </style>";

    ?>
    <div id="age-verification-overlay">
        <div id="age-verification-popup">
            <h2><?php echo esc_html($headline); ?></h2>
            <p><?php echo esc_html($description); ?></p>
            <button id="age-btn-enter" class="age-btn"><?php echo esc_html($enter_text); ?></button>
            <button id="age-btn-exit" class="age-btn"><?php echo esc_html($exit_text); ?></button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'sav_add_age_verification_popup');

/*------------------------------------------
 ADMIN MENU
-------------------------------------------*/
add_action('admin_menu', function() {
    add_options_page('Simple Age Verifier', 'Simple Age Verifier', 'manage_options', 'simple-age-verifier', 'sav_settings_page');
});

/*------------------------------------------
 REGISTER SETTINGS
-------------------------------------------*/
add_action('admin_init', function(){
    $fields = [
        'sav_popup_enabled','sav_headline_text','sav_description_text','sav_enter_btn_text','sav_exit_btn_text',
        'sav_bg_color','sav_headline_bg_color','sav_headline_color','sav_description_bg_color','sav_description_color',
        'sav_enter_bg_color','sav_enter_text_color','sav_exit_bg_color','sav_exit_text_color','sav_popup_width','sav_popup_height'
    ];
    foreach($fields as $f) register_setting('sav_settings_group', $f);
});

/*------------------------------------------
 ADMIN: Enqueue Color Picker + JS
-------------------------------------------*/
add_action('admin_enqueue_scripts', function($hook){
    if($hook !== 'settings_page_simple-age-verifier') return;
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('sav-style', plugins_url('style.css', __FILE__));
    wp_add_inline_script('wp-color-picker', "
        jQuery(function($){
            $('.sav-color-field').wpColorPicker();
            function toggleSec(){
                if ($('input[name=\"sav_popup_enabled\"]').is(':checked')) $('.sav-hidden-section').slideDown();
                else $('.sav-hidden-section').slideUp();
            }
            toggleSec(); $('input[name=\"sav_popup_enabled\"]').on('change', toggleSec);
            $('#sav_popup_width').on('input', function(){ $('#sav_width_val').text($(this).val()+'%'); });
            $('#sav_popup_height').on('input', function(){ $('#sav_height_val').text($(this).val()+'%'); });
        });
    ");
});

/*------------------------------------------
 ADMIN SETTINGS PAGE
-------------------------------------------*/
function sav_settings_page(){ ?>
<div class="wrap">
<h1>Simple Age Verifier Settings</h1>
<form method="post" action="options.php">
<?php settings_fields('sav_settings_group'); ?>

<!-- Toggle -->
<div class="sav-card">
  <div class="sav-card-body sav-toggle-box">
    <label class="sav-switch">
      <input type="checkbox" name="sav_popup_enabled" value="on" <?php checked(get_option('sav_popup_enabled'),'on'); ?> />
      <span class="sav-slider"></span>
    </label>
    <strong>Enable Popup</strong>
  </div>
</div>

<div class="sav-hidden-section">

  <!-- Popup Background (33%) + Size (66%) -->
  <div class="sav-two-col-33-66">
    <div class="sav-card">
      <div class="sav-card-header">Popup Background</div>
      <div class="sav-card-body">
        <label class="sav-label">Popup Background Color</label>
        <input type="text" class="sav-color-field" name="sav_bg_color"
               value="<?php echo esc_attr(get_option('sav_bg_color','#fff')); ?>">
      </div>
    </div>

    <div class="sav-card">
      <div class="sav-card-header">Popup Size</div>
      <div class="sav-card-body sav-sub-row">
        <div>
          <label class="sav-label">Width (%) <span id="sav_width_val"><?php echo esc_attr(get_option('sav_popup_width',90)); ?>%</span></label>
          <input type="range" min="1" max="100" step="1" id="sav_popup_width" name="sav_popup_width" value="<?php echo esc_attr(get_option('sav_popup_width',90)); ?>">
        </div>
        <div>
          <label class="sav-label">Height (%) <span id="sav_height_val"><?php echo esc_attr(get_option('sav_popup_height',60)); ?>%</span></label>
          <input type="range" min="1" max="100" step="1" id="sav_popup_height" name="sav_popup_height" value="<?php echo esc_attr(get_option('sav_popup_height',60)); ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- Headline + Description -->
  <div class="sav-two-col">
    <div class="sav-card">
      <div class="sav-card-header">Headline</div>
      <div class="sav-card-body">
        <label class="sav-label">Headline Text</label>
        <input type="text" name="sav_headline_text" value="<?php echo esc_attr(get_option('sav_headline_text')); ?>" class="regular-text">
        <div class="sav-sub-row">
          <div><label class="sav-label">Headline BG</label><input type="text" class="sav-color-field" name="sav_headline_bg_color" value="<?php echo esc_attr(get_option('sav_headline_bg_color')); ?>"></div>
          <div><label class="sav-label">Headline Color</label><input type="text" class="sav-color-field" name="sav_headline_color" value="<?php echo esc_attr(get_option('sav_headline_color','#333')); ?>"></div>
        </div>
      </div>
    </div>

    <div class="sav-card">
      <div class="sav-card-header">Description</div>
      <div class="sav-card-body">
        <label class="sav-label">Description Text</label>
        <textarea name="sav_description_text" rows="2" class="large-text"><?php echo esc_textarea(get_option('sav_description_text')); ?></textarea>
        <div class="sav-sub-row">
          <div><label class="sav-label">Description BG</label><input type="text" class="sav-color-field" name="sav_description_bg_color" value="<?php echo esc_attr(get_option('sav_description_bg_color')); ?>"></div>
          <div><label class="sav-label">Description Color</label><input type="text" class="sav-color-field" name="sav_description_color" value="<?php echo esc_attr(get_option('sav_description_color','#666')); ?>"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Enter + Exit Buttons -->
  <div class="sav-two-col">
    <div class="sav-card">
      <div class="sav-card-header">Enter Button</div>
      <div class="sav-card-body">
        <label class="sav-label">Enter Button Text</label>
        <input type="text" name="sav_enter_btn_text" value="<?php echo esc_attr(get_option('sav_enter_btn_text')); ?>" class="regular-text">
        <div class="sav-sub-row">
          <div><label class="sav-label">Enter BG</label><input type="text" class="sav-color-field" name="sav_enter_bg_color" value="<?php echo esc_attr(get_option('sav_enter_bg_color','#f7b500')); ?>"></div>
          <div><label class="sav-label">Enter Color</label><input type="text" class="sav-color-field" name="sav_enter_text_color" value="<?php echo esc_attr(get_option('sav_enter_text_color','#333')); ?>"></div>
        </div>
      </div>
    </div>

    <div class="sav-card">
      <div class="sav-card-header">Exit Button</div>
      <div class="sav-card-body">
        <label class="sav-label">Exit Button Text</label>
        <input type="text" name="sav_exit_btn_text" value="<?php echo esc_attr(get_option('sav_exit_btn_text')); ?>" class="regular-text">
        <div class="sav-sub-row">
          <div><label class="sav-label">Exit BG</label><input type="text" class="sav-color-field" name="sav_exit_bg_color" value="<?php echo esc_attr(get_option('sav_exit_bg_color','#d9534f')); ?>"></div>
          <div><label class="sav-label">Exit Color</label><input type="text" class="sav-color-field" name="sav_exit_text_color" value="<?php echo esc_attr(get_option('sav_exit_text_color','#fff')); ?>"></div>
        </div>
      </div>
    </div>
  </div>

</div>

<?php submit_button(); ?>
</form>
</div>
<?php } ?>
