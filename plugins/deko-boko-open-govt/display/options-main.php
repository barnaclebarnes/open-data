<?php
/**
 * Set options for Deko Boko.
 *
 * This file is part of Deko Boko. Please see the DekoBoko.php file for
 * copyright and license information.
 *
 * @author Michael Toppa
 * @version 1.2.2
 * @package DekoBoko
 */
?>

<div class="wrap">
    <div style="float: right; font-weight: bold; margin-top: 20px;">
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="5378623">
        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
        <?php _e("Support Deko Boko", DEKOBOKO_L10N_NAME); ?> &raquo; <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="<?php _e("Support Deko Boko", DEKOBOKO_L10N_NAME); ?>" title="<?php _e("Support Deko Boko", DEKOBOKO_L10N_NAME); ?>" style="vertical-align: middle;" />
        <a href="<?php echo DEKOBOKO_FAQ_URL; ?>" target="_blank"><?php _e("Deko Boko Help", DEKOBOKO_L10N_NAME); ?></a>
        </form>
    </div>

    <h2><?php echo DEKOBOKO_DISPLAY_NAME . " " . __("Settings", DEKOBOKO_L10N_NAME); ?></h2>

    <?php if (strlen($message)) {
        echo '<div id="message" class="updated fade"><p>' . $message .'</p></div>';
        unset($message);
    } ?>

    <form action="<?php echo DEKOBOKO_ADMIN_URL; ?>" method="post">
    <?php wp_nonce_field('dekoboko_nonce', 'dekoboko_nonce'); ?>
    <input type="hidden" name="dekoboko_action" value="update_options">
    <table border="0" cellspacing="3" cellpadding="3" class="form-table">
    <tr valign="top">
    <td nowrap="nowrap"><?php _e("reCAPTCHA public key:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><input type="text" name="recaptcha[pubkey]" value="<?php echo $recaptcha_options['pubkey']; ?>" size="40" /></td>
    <td rowspan="2"><strong>&laquo;</strong> <?php _e("If you are already using the WP-reCAPTCHA plugin for comments, Deko Boko will use the API key you've already set. If you are not using the WP-reCAPTCHA plugin, then you need to get a", DEKOBOKO_L10N_NAME); ?>
    <a href="<?php echo recaptcha_get_signup_url($site_url['host'], 'wordpress');?>" target="_blank"><?php _e('free reCAPTCHA API key for your site', DEKOBOKO_L10N_NAME); ?></a> <?php _e('and enter the public and private keys here.', DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("reCAPTCHA private key:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><input type="text" name="recaptcha[privkey]" value="<?php echo $recaptcha_options['privkey']; ?>" size="40" /></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("Recipient email address:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><input type="text" name="dekoboko_options[recipient]" value="<?php echo $dekoboko_options['recipient']; ?>" size="40" /></td>
    <td><strong>&laquo;</strong> <?php _e('Where to email submissions from the the contact form. Separate multiple addresses with commas.', DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("Email subject line:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><input type="text" name="dekoboko_options[subject]" value="<?php echo $dekoboko_options['subject']; ?>" size="40" /></td>
    <td><strong>&laquo;</strong> <?php _e('An optional subject line that will appear on emails sent to you through the contact form. If you also include a subject line in the contact form, then the subject provided by users will be appended to this subject line.', DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("Welcome message:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><textarea name="dekoboko_options[welcome]" cols="40" rows="5"><?php echo $dekoboko_options['welcome']; ?></textarea></td>
    <td><strong>&laquo;</strong> <?php _e('An optional message to display at the top of the contact form. You can include HTML.', DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("Success message:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><textarea name="dekoboko_options[success]" cols="40" rows="5"><?php echo $dekoboko_options['success']; ?></textarea></td>
    <td><strong>&laquo;</strong> <?php _e('The message to show users after they successfully submit the contact form. You can include HTML.', DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e('"CC Me" header:', DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><textarea name="dekoboko_options[cc_header]" cols="40" rows="5"><?php echo $dekoboko_options['cc_header']; ?></textarea></td>
    <td><strong>&laquo;</strong> <?php _e("If a user checks the 'CC Me' box, this text will appear at the top of the message that's sent to them. If you use the special word BLOGNAME in all capital letters, Deko Boko will substitute the name of your blog. If you use DATETIME it will substitute the date and time the email was sent. HTML is not currently supported.", DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e('"CC Me" footer:', DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><textarea name="dekoboko_options[cc_footer]" cols="40" rows="5"><?php echo $dekoboko_options['cc_footer']; ?></textarea></td>
    <td><strong>&laquo;</strong> <?php _e("If a user checks the 'CC Me' box, this text will appear at the bottom of the message that's sent to them. If you use the special word BLOGNAME in all capital letters, Deko Boko will substitute the name of your blog. If you use DATETIME it will substitute the date and time the email was sent. HTML is not currently supported.", DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("Page Slugs/IDs:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><textarea name="dekoboko_options[pages]" cols="40" rows="5"><?php echo $dekoboko_options['pages']; ?></textarea></td>
    <td><strong>&laquo;</strong> <?php _e("Optional: enter the slugs or IDs for the pages/posts where you use Deko Boko, separated by line breaks. This lets Deko Boko know on which pages to load its stylesheet (otherwise the stylesheet is loaded on every page). You can find the slug for a page by clicking 'Quick Edit' on your Edit Posts or Edit Pages screen.", DEKOBOKO_L10N_NAME); ?></td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("reCAPTCHA theme:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><select name="dekoboko_options[recaptcha_theme]">
        <option value="red"<?php if ($dekoboko_options['recaptcha_theme'] == 'red') echo ' selected="selected"'; ?>><?php _e('red', DEKOBOKO_L10N_NAME); ?></option>
        <option value="white"<?php if ($dekoboko_options['recaptcha_theme'] == 'white') echo ' selected="selected"'; ?>><?php _e('white', DEKOBOKO_L10N_NAME); ?></option>
        <option value="blackglass"<?php if ($dekoboko_options['recaptcha_theme'] == 'blackglass') echo ' selected="selected"'; ?>><?php _e('blackglass', DEKOBOKO_L10N_NAME); ?></option>
        <option value="clean"<?php if ($dekoboko_options['recaptcha_theme'] == 'clean') echo ' selected="selected"'; ?>><?php _e('clean', DEKOBOKO_L10N_NAME); ?></option>
        <option value="custom"<?php if ($dekoboko_options['recaptcha_theme'] == 'custom') echo ' selected="selected"'; ?>><?php _e('custom', DEKOBOKO_L10N_NAME); ?></option>
    </select></td>
    <td><strong>&laquo;</strong> <?php _e("'Red' is the default theme. Activating the 'clean' theme allows you to adjust the reCAPTCHA widget's colors - see dekoboko.css for the classes.", DEKOBOKO_L10N_NAME); ?> <a href="http://wiki.recaptcha.net/index.php/Theme" target="_blank"><?php _e("You can preview the themes here", DEKOBOKO_L10N_NAME); ?></a>. <?php _e("'Custom' is for advanced users only, who want to change the layout of the reCAPTCHA widget - see the", DEKOBOKO_L10N_NAME); ?> <a href="http://recaptcha.net/apidocs/captcha/client.html" target="_blank"><?php _e("reCAPTCHA Client API Documentation", DEKOBOKO_L10N_NAME); ?></a>.</td>
    </tr>

    <tr valign="top">
    <td nowrap="nowrap"><?php _e("reCAPTCHA language:", DEKOBOKO_L10N_NAME); ?></td>
    <td nowrap="nowrap"><select name="dekoboko_options[recaptcha_lang]">
        <option value="en" <?php if ($dekoboko_options['recaptcha_lang'] == 'en') echo 'selected="selected"'; ?>><?php _e('English', DEKOBOKO_L10N_NAME); ?></option>
        <option value="nl" <?php if ($dekoboko_options['recaptcha_lang'] == 'nl') echo 'selected="selected"'; ?>><?php _e('Dutch', DEKOBOKO_L10N_NAME); ?></option>
        <option value="fr" <?php if ($dekoboko_options['recaptcha_lang'] == 'fr') echo 'selected="selected"'; ?>><?php _e('French', DEKOBOKO_L10N_NAME); ?></option>
        <option value="de" <?php if ($dekoboko_options['recaptcha_lang'] == 'de') echo 'selected="selected"'; ?>><?php _e('German', DEKOBOKO_L10N_NAME); ?></option>
        <option value="pt" <?php if ($dekoboko_options['recaptcha_lang'] == 'pt') echo 'selected="selected"'; ?>><?php _e('Portuguese', DEKOBOKO_L10N_NAME); ?></option>
        <option value="ru" <?php if ($dekoboko_options['recaptcha_lang'] == 'ru') echo 'selected="selected"'; ?>><?php _e('Russian', DEKOBOKO_L10N_NAME); ?></option>
        <option value="es" <?php if ($dekoboko_options['recaptcha_lang'] == 'es') echo 'selected="selected"'; ?>><?php _e('Spanish', DEKOBOKO_L10N_NAME); ?></option>
        <option value="tr" <?php if ($dekoboko_options['recaptcha_lang'] == 'tr') echo 'selected="selected"'; ?>><?php _e('Turkish', DEKOBOKO_L10N_NAME); ?></option>
    </select></td>
    <td>&nbsp;</td>
    </tr>
    </table>

    <p><input type="submit" class="button-primary" name="save" value="<?php _e('Save Options', DEKOBOKO_L10N_NAME); ?>" /></p>
    </form>

    <div style="border: thin solid; padding: 5px;">
        <h3><?php _e("Uninstall Deko Boko", DEKOBOKO_L10N_NAME); ?></h3>

        <form action="<?php echo DEKOBOKO_ADMIN_URL ?>" method="post">
        <?php wp_nonce_field('dekoboko_nonce', 'dekoboko_nonce'); ?>
        <input type="hidden" name="dekoboko_action" value="uninstall">
        <table border="0" cellspacing="3" cellpadding="3" class="form-table">
        <tr style="vertical-align: top;">
        <td nowrap="nowrap"><?php _e("Uninstall Deko Boko?", DEKOBOKO_L10N_NAME); ?></td>
        <td><input type="checkbox" name="dekoboko_uninstall" value="y" /></td>
        <td><?php _e("After uninstalling, you can deactivate Deko Boko on your plugins management page.", DEKOBOKO_L10N_NAME); ?></td>
        </tr>
        <tr style="vertical-align: top;">
        <td nowrap="nowrap"><?php _e("Remove reCAPTCHA API key?", DEKOBOKO_L10N_NAME); ?></td>
        <td><input type="checkbox" name="dekoboko_remove_keys" value="y" /></td>
        <td><?php _e("Deko Boko shares its reCAPTCHA API key with the WP-reCAPTCHA plugin. Do not check this box if you are using the WP-reCAPTCHA plugin for comments.", DEKOBOKO_L10N_NAME); ?></td>
        </tr>
        </table>

        <p class="submit"><input class="button-secondary" type="submit" name="save" value="<?php _e("Uninstall Deko Boko", DEKOBOKO_L10N_NAME); ?>" onclick="return confirm('<?php _e("Are you sure you want to uninstall Deko Boko?", DEKOBOKO_L10N_NAME); ?>');" /></p>
        </form>
    </div>
</div>

