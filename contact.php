<?php
$page_title = 'Contact - EasyMart';
require 'includes/header.php';
?>
<h1>Contact Us</h1>
<form method="post">
    <label>Name <input type="text" name="name" required></label>
    <label>Email <input type="email" name="email" required></label>
    <label>Message <textarea name="message" required></textarea></label>
    <button type="submit">Send Message</button>
</form>
<?php require 'includes/footer.php'; ?>
