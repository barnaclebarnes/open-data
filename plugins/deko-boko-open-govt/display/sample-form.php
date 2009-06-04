<?php
// Use this file as a template for making your own custom contact form
?>

<form action="<?php echo get_permalink(); ?>" method="post" id="dekoboko_form">
<?php wp_nonce_field('dekoboko_nonce', 'dekoboko_nonce'); ?>

<?php
    // you can comment out or delete this "if" block if you don't want to
    // display the welcome message that's in your Deko Boko Settings.
    if ($dekoboko_options['welcome']) {
        echo "<p>" . $dekoboko_options['welcome'] . "</p>";
    }
?>

<p>Fields marked with <span class="dekoboko_required">*</span> are required.</p>

<fieldset>
<ol>
<li>
    <label for="dekoboko_name">Name<span class="dekoboko_required">*</span></label>
    <input type="text" name="dekoboko_required[name]" id="dekoboko_name" value="<?php echo $dekoboko_required['name']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_email">Email<span class="dekoboko_required">*</span></label>
    <input type="text" name="dekoboko_required[email]" id="dekoboko_email" value="<?php echo $dekoboko_required['email']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_subject">Subject</label>
    <input type="text" name="dekoboko_optional[subject]" id="dekoboko_subject" value="<?php echo $dekoboko_optional['subject']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_favorite_color">What is your favorite color?</label>
    <input type="text" name="dekoboko_optional[favorite_color]" id="dekoboko_favorite_color" value="<?php echo $dekoboko_optional['favorite_color']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_message">Message<span class="dekoboko_required">*</span></label>
    <textarea name="dekoboko_required[message]" cols="30" rows="5" id="dekoboko_message"><?php echo $dekoboko_required['message']; ?></textarea>
</li>

<li>
    <label for="dekoboko_cc_me">CC Me</label>
    <input type="checkbox" name="dekoboko_optional[cc_me]" id="dekoboko_cc_me" value="Y"<?php if ($dekoboko_optional['cc_me'] == 'Y') echo ' checked="checked"'; ?> />
    <span style="font-size: x-small;">Check this box to send a copy of your message to yourself.</span>
</li>

<li><label for="recaptcha_challenge_field">Are You Human?<span class="dekoboko_required">*</span><br />
    <span style="font-size: x-small;"><a href="http://recaptcha.net/popuphelp/" onclick="window.open('http://recaptcha.net/popuphelp/','name','height=600,width=500'); return false;" title="Help">What's this?</a></span></label>
    <?php echo $recaptcha_html ?>
</li>
</ol>
</fieldset>

<fieldset id="dekoboko_end">
    <input type="submit" name="dekoboko_submit" id="dekoboko_submit" value="Send Message" />
</fieldset>
</form>

