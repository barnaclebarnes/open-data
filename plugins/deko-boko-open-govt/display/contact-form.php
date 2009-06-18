<?php
/**
 * The contact form for Deko Boko.
 *
 * This file is part of Deko Boko. Please see the DekoBoko.php file for
 * copyright and license information.
 *
 * @author Michael Toppa
 * @version 1.2.2
 * @package DekoBoko
 */

?>

<form action="<?php echo get_permalink(); ?>" method="post" id="dekoboko_form">
<?php wp_nonce_field('dekoboko_nonce', 'dekoboko_nonce'); ?>

<?php if ($dekoboko_options['welcome']) {
        echo "<p>" . $dekoboko_options['welcome'] . "</p>";
} ?>

<p><?php _e("Fields marked with ", DEKOBOKO_L10N_NAME); ?><span class="dekoboko_required">*</span> <?php _e("are required", DEKOBOKO_L10N_NAME); ?>.</p>

<fieldset>
<ol>
<li>
    <label for="dekoboko_name"><?php _e("Name", DEKOBOKO_L10N_NAME); ?><span class="dekoboko_required">*</span></label>
    <input type="text" name="dekoboko_required[name]" id="dekoboko_name" value="<?php echo $dekoboko_required['name']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_email"><?php _e("Email", DEKOBOKO_L10N_NAME); ?><span class="dekoboko_required">*</span></label>
    <input type="text" name="dekoboko_required[email]" id="dekoboko_email" value="<?php echo $dekoboko_required['email']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_subject"><?php _e("Subject", DEKOBOKO_L10N_NAME); ?></label>
    <input type="text" name="dekoboko_optional[subject]" id="dekoboko_subject" value="<?php echo $dekoboko_optional['subject']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_message"><?php _e("Message", DEKOBOKO_L10N_NAME); ?><span class="dekoboko_required">*</span></label>
    <textarea name="dekoboko_required[message]" cols="30" rows="5" id="dekoboko_message"><?php echo $dekoboko_required['message']; ?></textarea>
</li>

<li>
    <label for="dekoboko_cc_me"><?php _e("CC Me", DEKOBOKO_L10N_NAME); ?></label>
    <input type="checkbox" name="dekoboko_optional[cc_me]" id="dekoboko_cc_me" value="Y"<?php if ($dekoboko_optional['cc_me'] == 'Y') echo ' checked="checked"'; ?> />
    <span style="font-size: x-small;"><?php _e("Check this box to send a copy of your message to yourself.", DEKOBOKO_L10N_NAME); ?></span>
</li>

<li><label for="recaptcha_challenge_field"><?php _e("Are You Human?", DEKOBOKO_L10N_NAME); ?><span class="dekoboko_required">*</span><br />
    <span style="font-size: x-small;"><a href="http://recaptcha.net/popuphelp/" onclick="window.open('http://recaptcha.net/popuphelp/','name','height=600,width=500'); return false;" title="Help"><?php _e("What's this?", DEKOBOKO_L10N_NAME); ?></a></span></label>
    <?php echo $recaptcha_html ?>
</li>
</ol>
</fieldset>

<fieldset id="dekoboko_end">
    <input type="submit" name="dekoboko_submit" id="dekoboko_submit" value="<?php _e("Send Message", DEKOBOKO_L10N_NAME); ?>" />
</fieldset>
</form>

