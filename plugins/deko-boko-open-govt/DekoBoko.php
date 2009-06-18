<?php
/*
Plugin Name: Deko Boko
Plugin URI: http://www.toppa.com/deko-boko-wordpress-plugin/
Description: An easily extensible contact form, using re-captcha
Author: Michael Toppa
Version: 1.2.2
Author URI: http://www.toppa.com
*/

/**
 * DekoBoko Class File
 *
 * @author Michael Toppa
 * @version 1.2.2
 * @package DekoBoko
 *
 * Copyright 2008-2009 Michael Toppa
 *
 * Deko Boko is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Deko Boko is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('DEKOBOKO_OPTIONS', get_option('dekoboko_options'));
define('DEKOBOKO_PLUGIN_NAME', 'DekoBoko');
define('DEKOBOKO_L10N_NAME', 'dekoboko');
define('DEKOBOKO_FILE', basename(__FILE__));
define('DEKOBOKO_DIR', dirname(__FILE__));
define('DEKOBOKO_ADMIN_URL', $_SERVER['PHP_SELF'] . "?page=" . basename(DEKOBOKO_DIR) . '/' . DEKOBOKO_FILE);
define('DEKOBOKO_PATH', DEKOBOKO_DIR . '/' . DEKOBOKO_FILE);
define('DEKOBOKO_VERSION', '1.2.2');
define('DEKOBOKO_DISPLAY_NAME', 'Deko Boko');
define('DEKOBOKO_DISPLAY_URL', get_bloginfo('wpurl') . '/wp-content/plugins/' . basename(DEKOBOKO_DIR) . '/display');
define('DEKOBOKO_FAQ_URL', 'http://www.toppa.com/deko-boko-wordpress-plugin');
define('DEKOBOKO_DEFAULT_TEMPLATE', 'contact-form.php');

/**
 * A plugin for generating an extensible contact form, using reCAPTCHA.
 *
 * @author Michael Toppa
 * @package DekoBoko
 * @subpackage Classes
 */
class DekoBoko {
    /**
     * Called automatically (after the end of the class) to register hooks and
     * add the actions and filters.
     *
     * @static
     * @access public
     */
    function bootstrap() {
        // Add the installation and uninstallation hooks
        register_activation_hook(DEKOBOKO_PATH, array(DEKOBOKO_PLUGIN_NAME, 'install'));

        // load localization
        load_plugin_textdomain(DEKOBOKO_L10N_NAME, false, basename(DEKOBOKO_DIR) . '/languages/');

        // Add the actions and filters
        add_action('admin_menu', array(DEKOBOKO_PLUGIN_NAME, 'initAdminMenus'));
        add_shortcode('dekoboko', array(DEKOBOKO_PLUGIN_NAME, 'getContactPage'));
        add_action('template_redirect', array(DEKOBOKO_PLUGIN_NAME, 'getHeadTags'));
    }

    /**
     * Set/Update settings
     *
     * @static
     * @access public
     */
    function install() {
        $dekoboko_options = unserialize(DEKOBOKO_OPTIONS);
        $dekoboko_options_defaults = array(
            'recipient' => null,
            'success' => '<p>Thank you for your message. If you had a question, I will try to write back as soon as I can. You can <a href="' . get_bloginfo('wpurl') . '">return to my home page</a>.</p>',
            'subject' => '[' . get_option('blogname') . '] Contact Form Email',
            'cc_header' => "Thank you for writing to BLOGNAME. Below is a copy of the message you submitted via our contact form on DATETIME.\n\n--------------------------------------------",
            'cc_footer' => "\n\n--------------------------------------------\n\nThanks again for writing. If you had a question, someone will write back to you as soon as possible.",
            'recaptcha_lang' => 'en',
            'recaptcha_theme' => 'red',
            'pages' => null
        );

        // flag whether to add or update options
        $add_options = empty($dekoboko_options);

        // import and delete old-style Deko Boko options if necessary
        if (get_option('dekoboko_recipient')) {
            delete_option('dekoboko_version');

            foreach ($dekoboko_options_defaults as $k=>$v) {
                $old_opt = get_option('dekoboko_' . $k);

                if ($old_opt) {
                    $dekoboko_options[$k] = $old_opt;
                }

                delete_option('dekoboko_' . $k);
            }
        }

        else {
            foreach ($dekoboko_options_defaults as $k=>$v) {
                if (!$dekoboko_options[$k]) {
                    $dekoboko_options[$k] = $v;
                }
            }
        }

        $dekoboko_options['version'] = DEKOBOKO_VERSION;

        if ($add_options === false) {
            update_option('dekoboko_options', serialize($dekoboko_options));
        }

        else {
            add_option('dekoboko_options', serialize($dekoboko_options));
        }

    }

    /**
     * Deletes the Deko Boko settings.
     *
     * @static
     * @access public
     * @param string $dekoboko_remove_keys whether to also remove the the recaptcha API keys shared with WP-reCAPTCHA
     * @return boolean true: uninstall successful
     */
    function uninstall($dekoboko_remove_keys = null) {
        delete_option('dekoboko_options');

        if ($dekoboko_remove_keys) {
            $recaptcha_options = get_option('recaptcha');
            unset($recaptcha_options['privkey']);
            unset($recaptcha_options['pubkey']);

            if (empty($recaptcha_options)) {
                delete_option('recaptcha');
            }

            else {
                update_option('recaptcha', $recaptcha_options);
            }
        }

        return true;
    }

    /**
     * Adds the Deko Boko Options page.
     *
     * @static
     * @access public
     * @uses getOptionsMenu()
     */
    function initAdminMenus() {
        add_options_page(DEKOBOKO_DISPLAY_NAME, DEKOBOKO_DISPLAY_NAME, 6, __FILE__, array(DEKOBOKO_PLUGIN_NAME, 'getOptionsMenu'));
    }

    /**
     * Generates and echoes the HTML for the options menu and sets Deko Boko
     * options in WordPress.
     *
     * @static
     * @access public
     * @uses DekoBoko::uninstall()
     * @returns string HTML for Settings form
     */
    function getOptionsMenu() {
        // check for valid nonce on form submission (WP displays its own message on failure)
        if ($_REQUEST['dekoboko_action'] && !check_admin_referer('dekoboko_nonce', 'dekoboko_nonce')) {
            return false;
        }

        // Start the cache
        ob_start();

        // uninstall
        if ($_REQUEST['dekoboko_action'] == 'uninstall') {
            // make doubly sure they want to uninstall
            if ($_REQUEST['dekoboko_uninstall'] == 'y') {
                if (DekoBoko::uninstall($_REQUEST['dekoboko_remove_keys']) == true) {
                    $message = __("Deko Boko has been uninstalled. You can now deactivate Deko Boko on your plugins management page.", DEKOBOKO_L10N_NAME);
                }

                else {
                    $message = __("Uninstall of Deko Boko failed. Database error:", DEKOBOKO_L10N_NAME);
                    $db_error = true;
                }
            }

            else {
                $message = __("You must check the 'Uninstall Deko Boko' checkbox to confirm you want to uninstall Deko Boko", DEKOBOKO_L10N_NAME);
            }
        }

        // update options
        elseif ($_REQUEST['dekoboko_action'] == 'update_options') {
            array_walk($_REQUEST['dekoboko_options'], array(DEKOBOKO_PLUGIN_NAME, '_trim'));
            array_walk($_REQUEST['recaptcha'], array(DEKOBOKO_PLUGIN_NAME, '_trim'));

            // process any page slugs - save as an array
            if ($_REQUEST['dekoboko_options']['pages']) {
                $_REQUEST['dekoboko_options']['pages'] = preg_split("/[\s,]+/", $_REQUEST['dekoboko_options']['pages']);
            }

            $dekoboko_options = array_merge(unserialize(get_option('dekoboko_options')), $_REQUEST['dekoboko_options']);
            update_option('dekoboko_options', serialize($dekoboko_options));
            $recaptcha_options = get_option('recaptcha');

            if (is_array($recaptcha_options)) {
                $recaptcha_options = array_merge($recaptcha_options, $_REQUEST['recaptcha']);
            }

            else {
                $recaptcha_options = $_REQUEST['recaptcha'];
            }

            update_option('recaptcha', $recaptcha_options);
            $message = __("Deko Boko settings saved.", DEKOBOKO_L10N_NAME);
        }

        // don't try to refresh options data if we just uninstalled
        if ($_REQUEST['dekoboko_action'] != 'uninstall') {
            // options may have changed - re-fetch
            $dekoboko_options = unserialize(get_option('dekoboko_options'));

            // flatten pages array for display
            if (is_array($dekoboko_options['pages'])) {
                $dekoboko_options['pages'] = implode("\n", $dekoboko_options['pages']);
            }

            // prepare for display in the form
            array_walk($dekoboko_options, array(DEKOBOKO_PLUGIN_NAME, '_htmlentities'));
            array_walk($dekoboko_options, array(DEKOBOKO_PLUGIN_NAME, '_stripslashes'));
            $recaptcha_options = get_option('recaptcha');

            if (is_array($recaptcha_options)) {
                array_walk($recaptcha_options, array(DEKOBOKO_PLUGIN_NAME, '_htmlentities'));
                array_walk($recaptcha_options, array(DEKOBOKO_PLUGIN_NAME, '_stripslashes'));
            }
        }

        // for linking to the recaptcha site for key signup
        $site_url = parse_url(get_option('site_url'));

        if (!function_exists('recaptcha_get_signup_url')) {
            require_once(DEKOBOKO_DIR . '/recaptcha/recaptchalib.php');
        }

        // Get the markup and display
        require(DEKOBOKO_DIR . '/display/options-main.php');
        $options_form = ob_get_contents();
        ob_end_clean();
        echo $options_form;
    }

    /**
     * Generates and returns the HTML for the contact form page. When the form
     * is submitted, it checks for errors and initiates sending the email.
     *
     * @static
     * @access public
     * @uses DekoBoko::checkMessage()
     * @uses DekoBoko::sendMessage()
     * @uses recaptchalib::recaptcha_get_html()
     * @returns string HTML for contact form
     */
    function getContactPage($atts) {
        extract(shortcode_atts(array('template' => DEKOBOKO_DEFAULT_TEMPLATE), $atts));
        $status = false;
        $dekoboko_options = unserialize(get_option('dekoboko_options'));
        array_walk($dekoboko_options, array(DEKOBOKO_PLUGIN_NAME, '_stripslashes'));
        $recaptcha_options = get_option('recaptcha');
        $headers = array('name', 'email', 'subject');

        // don't try to include the recaptcha lib if it's already been included
        // by another plugin - if you have recaptcha_get_html then you'll have
        // the other functions in recaptchalib too.
        if (!function_exists('recaptcha_get_html')) {
            require_once(DEKOBOKO_DIR . '/recaptcha/recaptchalib.php');
        }

        // if the form has been submitted, check to make sure it's safe to send
        if ($_POST['dekoboko_submit']) {
            $status = DekoBoko::checkMessage($recaptcha_options, $headers);
        }

        if ($status === true) {
            $status = DekoBoko::sendMessage($headers);

            if ($status === false) {
                $status = array(__("Failed call to wp_mail() - unable to send message", DEKOBOKO_L10N_NAME));
            }
        }

        $dekoboko_required = $_POST['dekoboko_required'];
        $dekoboko_optional = $_POST['dekoboko_optional'];

        if (!empty($dekoboko_required)) {
            array_walk($dekoboko_required, array('DekoBoko', '_cleanInput'), true);
        }

        if (!empty($dekoboko_optional)) {
            array_walk($dekoboko_optional, array('DekoBoko', '_cleanInput'), true);
        }

        ob_start();

        // display the success message if the message was sent
        if ($status === true) {
            echo '<div class="dekoboko_success">' . $dekoboko_options['success'] . '</div>';
        }

        // display the contact form
        else {
            // display any errors from a previous submission
            if (is_array($status)) {
                echo "\n" . '<div class="dekoboko_errors"><p>' . __("Sorry, there were errors in your submission. Please try again.", DEKOBOKO_L10N_NAME) . "</p>\n<ul>\n";

                foreach($status as $error) {
                    echo "<li>$error</li>\n";
                }

                echo "</ul></div>\n";
            }

            $js_opts = "
                <script type='text/javascript'>
                var RecaptchaOptions = { theme : '"
                . $dekoboko_options['recaptcha_theme'] . "', lang : '"
                . $dekoboko_options['recaptcha_lang'] . "' };
                </script>
            ";

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
                $use_ssl = true;
            }

            else {
                $use_ssl = false;
            }

            $recaptcha_html = $js_opts . recaptcha_get_html($recaptcha_options['pubkey'], null, $use_ssl);

            require DEKOBOKO_DIR . "/display/$template";
        }

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Checks the re-captcha response and checks for bad or malicious data
     * submissions.
     *
     * @static
     * @access public
     * @uses recaptchalib::recaptcha_check_answer()
     * @uses recaptchalib::is_valid()
     * @uses DekoBoko::checkHeader()
     * @uses DekoBoko::checkEmail()
     * @returns boolean|array true if message is safe; array of error messages if not
     */
    function checkMessage($recaptcha_options, $headers) {
        $errors = array();

        $resp = recaptcha_check_answer($recaptcha_options['privkey'], $_SERVER["REMOTE_ADDR"],
            $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
            $errors[] = "<strong>" . __("ReCAPTCHA error", DEKOBOKO_L10N_NAME) . ":</strong> "
                . __("your captcha response was incorrect - please try again", DEKOBOKO_L10N_NAME);
        }

        if (!wp_verify_nonce($_POST['dekoboko_nonce'], 'dekoboko_nonce')) {
            $errors[] = "<strong>" . __("Invalid Nonce", DEKOBOKO_L10N_NAME) . "</strong>";
        }

        foreach ($headers as $header) {
            if (DekoBoko::checkHeader($_POST['dekoboko_required'][$header]) === false) {
                $errors[] = "<strong>$header</strong> " . __("header contains malicious data", DEKOBOKO_L10N_NAME);
            }

            if (DekoBoko::checkHeader($_POST['dekoboko_optional'][$header]) === false) {
                $errors[] = "<strong>$header</strong> " . __("header contains malicious data", DEKOBOKO_L10N_NAME);
            }
        }

        foreach ($_POST['dekoboko_required'] as $k=>$v) {
            if (!strlen($v)) {
                $errors[] = __("Required field", DEKOBOKO_L10N_NAME)
                    . " <strong>$k</strong> " . __("is blank", DEKOBOKO_L10N_NAME);
            }

            if (strlen($v) && $k == 'email') {
                if (DekoBoko::checkEmail($v) == 0) {
                    // htmlentities for XSS protection
                    $errors[] = "<strong>" . htmlentities($v) . "</strong> "
                        . __("is not a valid email address", DEKOBOKO_L10N_NAME);
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Checks for malicious data in email headers. Inspired by
     * http://www.phpbuilder.com/columns/ian_gilfillan20060412.php3
     *
     * @static
     * @access public
     * @returns boolean true if headers are clean, false otherwise
     */
    function checkHeader($header) {
        $badStrings = array("content-type:","mime-version:","multipart/mixed",
            "content-transfer-encoding:","bcc:","cc:","to:","%0A","%0D","\n",
            "\r");

        foreach($badStrings as $bad) {
            if(strpos(strtolower($header), $bad)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates email addresses. Borrowed from
     * http://www.phpbuilder.com/columns/ian_gilfillan20060412.php3
     *
     * @static
     * @access public
     * @returns int|boolean 0 if not a valid email, 1 if it is valid, false on error
     */
    function checkEmail($email) {
        return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
    }

    /**
     * Sends the email message. Takes any form data that is not a header or the
     * regular message body, and appends it to the body. Note wp_mail() is used
     * to send the mail, not mail()
     *
     * @static
     * @access public
     */
    function sendMessage($headers) {
        $dekoboko_options = unserialize(get_option('dekoboko_options'));
        array_walk($dekoboko_options, array(DEKOBOKO_PLUGIN_NAME, '_stripslashes'));
        $to = $dekoboko_options['recipient'];
        $subject = $dekoboko_options['subject'];

        if ($_POST['dekoboko_required'] && $_POST['dekoboko_optional']) {
            $form_data = array_merge($_POST['dekoboko_required'], $_POST['dekoboko_optional']);
        }

        else if ($_POST['dekoboko_required']) {
            $form_data = $_POST['dekoboko_required'];
        }

        else {
            $form_data = $_POST['dekoboko_optional'];
        }

        array_walk($form_data, array('DekoBoko', '_cleanInput'));

        if ($form_data['subject']) {
            $subject .=  strlen($subject) ? ': ' : '';
            $subject .= $form_data['subject'];
        }

        $message = '';

        foreach ($form_data as $k=>$v) {
            if ($form_data['name'] && !$form_data['email']) {
                $message .= __("Message from ", DEKOBOKO_L10N_NAME) . $form_data['name'] . "\n\n";
            }

            if ($k == 'message') {
                $message .= "$v\n\n";
            }

            else if (!in_array($k, $headers)) {
                $message .= "$k: $v\n";
            }
        }

        if ($form_data['name'] && $form_data['email']) {
            $from = $form_data['name'] . " <" . $form_data['email'] . ">";
        }

        else if ($form_data['email']) {
            $from = $form_data['email'];
        }

        if ($from) {
            $from = "From: " . $from;
        }

        $status = wp_mail($to, $subject, $message, $from);

        if (($form_data['email']) && $status && $form_data['cc_me']) {
            // add "cc me" header and footer text to email, and make substitutions
            $message_header = $dekoboko_options['cc_header'] . "\n\n";
            $message_footer = $dekoboko_options['cc_footer'];

            if (strlen($message_header)) {
                $message_header = str_replace('BLOGNAME', get_option('blogname'), $message_header);
                $message_header = str_replace('DATETIME', date('F jS, Y \a\t h:i A'), $message_header);
                $message = $message_header . $message;
            }

            if (strlen($message_footer)) {
                $message_footer = str_replace('BLOGNAME', get_option('blogname'), $message_footer);
                $message_footer = str_replace('DATETIME', date('F jS, Y \a\t h:i A'), $message_footer);
                $message .= $message_footer;
            }

            $status = wp_mail($form_data['email'], $subject, $message, $from);
        }

        return $status;
    }

    /**
     * Gets the Deko Boko stylesheet. Loads it only on pages where needed, and
     * allows for a custom stylesheet in the active theme folder.
     *
     * @static
     * @access public
     */
    function getHeadTags() {
        if (file_exists(TEMPLATEPATH . '/dekoboko.css')) {
            $dekoboko_css = get_bloginfo('template_directory') . '/dekoboko.css';
        }

        else {
            $dekoboko_css = DEKOBOKO_DISPLAY_URL . '/dekoboko.css';
        }

        $dekoboko_options = unserialize(DEKOBOKO_OPTIONS);

        // limit inclusion of the stylesheet if we know which pages...
        if (is_array($dekoboko_options['pages'])) {
            foreach ($dekoboko_options['pages'] as $page) {
                if (is_page($page)) {
                    wp_enqueue_style('dekoboko_css', $dekoboko_css, false, DEKOBOKO_VERSION);
                    break;
                }
            }
        }

        // ...otherwise always load it
        else {
            wp_enqueue_style('dekoboko_css', $dekoboko_css, false, DEKOBOKO_VERSION);
        }
    }

    /**
     * Helper function for array_walk in sendMessage() - makes sure data is
     * clean.
     *
     * @static
     * @access private
     */
    function _cleanInput(&$value, $key, $html = null) {
        if ($html) {
            $value = htmlspecialchars(stripslashes(trim($value)), ENT_COMPAT, 'UTF-8');
        }

        else {
            $value = stripslashes(trim($value));
        }
    }

    /**
     * array_walk callback method for htmlentities()
     *
     * @static
     * @access private
     * @param string $string (required): the string to update
     * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
     */
    function _htmlentities(&$string, $key) {
        $string = htmlentities($string, ENT_COMPAT, 'UTF-8');
    }

    /**
     * array_walk callback method for trim()
     *
     * @static
     * @access private
     * @param string $string (required): the string to update
     * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
     */
    function _trim(&$string, $key) {
        $string = trim($string);
    }

    /**
     * array_walk callback method for stripslashes()
     *
     * @static
     * @access private
     * @param string $string (required): the string to update
     * @param mixed $key (ignored): the array key of the string (not needed but passed automatically by array_walk)
     */
    function _stripslashes(&$string, $key) {
        $string = stripslashes($string);
    }
}

DekoBoko::bootstrap();

