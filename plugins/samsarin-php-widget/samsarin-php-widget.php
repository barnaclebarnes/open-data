<?php
/*
Plugin Name:    Samsarin PHP Widget
Plugin URI:     http://blog.samsarin.com/samsarin-php-widget
Description:    A text widget with support for including PHP.
Author:         Chris Pettitt
Version:        1.3.2
Author URI:     http://samsarin.com

1.3.2 Add an options panel to reset all Samsarin PHP widgets.
1.2   Increase max widgets to 25
1.1   Workaround bug widget compatibility bug in WP 2.2
1.0   Initial Release

A text widget with support for including PHP.
Copyright (C) 2007-2008 Chris Pettitt (http://samsarin.com). All rights reserved.

This software is free to use. It may be redistributed provided the
copyright is included. The author provides this software with no warranty and
cannot be held liable for any results occurring from its use or misuse.
*/

// Configuration
// ----------------------------------------------------------------------------

// Change this value to set the maximum number of widgets that can be displayed at one time.
define('SPW_MAXWIDGETS', 25);

// Don't edit below here
// ----------------------------------------------------------------------------

function spw_register_options() {
    if (function_exists('add_options_page')) {
        add_options_page("Samsarin PHP Widget Options", "Samsarin PHP", 10, __FILE__, 'spw_options_form'); 
    }
}

function spw_options_form() {
    $options = get_option('samsarin_php_widget');

    if (isset($_POST['spw_reset_all_submit'])) {
        for ( $i = 1; $i <= SPW_MAXWIDGETS; ++$i ) unset($options[$i]);
        update_option('samsarin_php_widget', $options);
    } elseif (isset($_POST['spw_reset_submit'])) {
        $number = (int) $_POST['spw_number'];
        if ($number > 0 && $number <= SPW_MAXWIDGETS) {
            unset($options[$number]);
            update_option('samsarin_php_widget', $options);
        }
    }
?>
    <div class="wrap">
        <h2>Samsarin PHP Widget Options</h2>
        <form method="post" onSubmit="return confirm('Are you sure you want to reset ALL Samsarin PHP Widgets?');">
            <h3>Reset all Samsarin PHP Widgets</h3>
            <p>Click 'Reset All' below to reset all Samsarin PHP Widgets to a default, empty state. <strong>You cannot undo this action</strong>,
               so it is recommended that you bcakup your database before using it. If you want to reset specific widget, see the 
               next section.</p>
            <span class="submit"><input type="submit"  name="spw_reset_all_submit" value="<?php _e('Reset All'); ?>" /></span></p>
        </form>
        <form method="post" onSubmit="return confirm('Are you sure you want to reset this Samsarin PHP Widget?');">
            <h3>Reset specific Samsarin PHP Widget</h3>
            <p>Select the nubmer of a Samsarin PHP widget to reset to a default, empty state and click 'Reset' below. <strong>You cannot undo
               this action</strong>, so it is recommended that you backup your database before using it. If you want to reset all widgets, 
               please see the previous section.</p>
            <p><?php _e('Reset Samsarin PHP widget:', 'widgets'); ?>
            <select id="spw_number" name="spw_number" value="<?php echo $options['number']; ?>">
                <option value='' selected='true'></option>
<?php for ( $i = 1; $i <= $options['number']; ++$i ) echo "<option value='$i'>Samsarin PHP Widget $i: {$options[$i]['title']}</option>"; ?>
            </select>
            <span class="submit"><input type="submit" name="spw_reset_submit" value="<?php _e('Reset'); ?>" /></span></p>
        </form>
    </div>
<?php
}

function spw_widget($args, $number = 1) {
    extract($args);
    $options = get_option('samsarin_php_widget');

    $title = $options[$number]['title'];

    $body = $options[$number]['body'];
    if (empty($body)) {
        $body = '&nbsp;';
    }

    print $before_widget; 
    
    if (!empty($title)) {  
        print $before_title;  
        eval(" ?> $title <?php "); 
        print $after_title;
    }

    eval(" ?> $body <?php ");
    print $after_widget;     
}

function spw_control($number) {
    $options = get_option('samsarin_php_widget');
    if (!is_array($options)) {
        $options = array();
    }

    if ($_POST["samsarin_widget_submit_$number"]) {
        $options[$number]['title'] = stripslashes($_POST["samsarin_widget_title_$number"]);
        $options[$number]['body'] = stripslashes($_POST["samsarin_widget_body_$number"]);
    }

    update_option('samsarin_php_widget', $options);

    $title = htmlspecialchars($options[$number]['title'], ENT_QUOTES);
    $body = htmlspecialchars($options[$number]['body'], ENT_QUOTES);
?>          
    <dl>
        <dt><strong>Title</strong></dt>
        <dd>
            <input style="width: 350px; height: 50px;" name="samsarin_widget_title_<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" />
        </dd>
        <dt><strong>Body<strong></dt>
        <dd>
            <textarea style="width: 350px; height: 200px;" name="samsarin_widget_body_<?php echo "$number"; ?>"><?php echo $body; ?></textarea>
        </dd>
    </dl> 
    <input type="hidden" name="samsarin_widget_submit_<?php echo "$number"; ?>" value="1" />
<?php
}

function spw_admin_setup() {
    $options = get_option('samsarin_php_widget');
    if (isset($_POST['samsarin_php_widget_number_submit'])) {
        $number = (int) $_POST['samsarin_php_widget_number'];
        if ( $number < 1 ) $number = 1;
        if ($number > SPW_MAXWIDGETS) $number = SPW_MAXWIDGETS;
        $options['number'] = $number;
    }

    update_option('samsarin_php_widget', $options);
    spw_register_plugin($options['number']);
}

function spw_admin_page() {
    $options = get_option('samsarin_php_widget');
?>
    <div class="wrap">
        <h2><?php _e('Samsarin PHP Widgets', 'widgets'); ?></h2>
        <form method="POST">
            <p style="line-height: 30px;"><?php _e('How many Samsarin PHP widgets would you like?', 'widgets'); ?>
            <select id="samsarin_php_widget_number" name="samsarin_php_widget_number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i <= SPW_MAXWIDGETS; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
            </select>
            <span class="submit"><input type="submit" name="samsarin_php_widget_number_submit" value="<?php _e('Save'); ?>" /></span></p>
        </form>
    </div>
<?php
}

function spw_register_plugin() {
    global $wp_version;
    if (function_exists('register_sidebar_widget') && function_exists('register_widget_control')) {
        $options = get_option('samsarin_php_widget');
        $number = $options['number'];
        if ( $number < 1 ) $number = 1;
        if ($number > SPW_MAXWIDGETS) $number = SPW_MAXWIDGETS;
        for ($i = 1; $i <= SPW_MAXWIDGETS; $i++) {
            $name = array('Samsarin PHP %s', 'samsarin', $i);
            // Hack to support broken compatibility in WP 2.2
            if ($wp_version == "2.2") {
                register_sidebar_widget($name, $i <= $number ? 'spw_widget' : '', 'spw_widget', $i);
            } else {
                register_sidebar_widget($name, $i <= $number ? 'spw_widget' : '', $i);
            }
            register_widget_control($name, $i <= $number ? 'spw_control' : '', 460, 350, $i);
        }

        add_action('sidebar_admin_setup', 'spw_admin_setup');
        add_action('sidebar_admin_page', 'spw_admin_page');
    }
}

add_action('plugins_loaded', 'spw_register_plugin'); 
add_action('admin_menu', 'spw_register_options');
?>
