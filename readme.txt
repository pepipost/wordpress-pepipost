=== Pepipost ===
Contributors: Pepipost
Donate link: http://www.pepipost.com/
Tags: email, email reliability, email templates, pepipost, smtp, transactional email, wp_mail,email infrastructure, email marketing, marketing email, deliverability, email deliverability, email delivery, email server, mail server, email integration, cloud email
Requires at least: 3.3
Tested up to: 4.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send emails through Pepipost from your WordPress installation using SMTP integration.

== Description ==

Pepipost is built on the philosophy to encourage good senders and to keep the email eco-system clean. 
85% of today's email is Spam and we don't want to add up to that.
We have carried up that philosophy into the pricing model where we don't charge for the emails which are being opened by the customers. Emails that are clicked, opened or engaged with will always be free. 
Industry stats says 35-40% is the average open rate, hence that much of email volumes can be free. There is no limits, More your customer engagement is lesser will be your marketing spends. Entire pricing model is in your hands.

We care for each and every emails, and this is how we differentiate from our competitors.

The Pepipost plugin uses API integration to send outgoing emails from your WordPress installation. It replaces the wp_mail function included with WordPress. 

First, you need to have PHP-curl extension enabled.

To have the Pepipost plugin running after you have activated it, go to the plugin's settings page and set the Pepipost credentials, and how your email will be sent - either through SMTP or API.


How to use `wp_mail()` function:

We amended `wp_mail()` function so all email sends from WordPress should go through Pepipost.

You can send emails using the following function: `wp_mail($to, $subject, $message, $headers = '', $attachments = array())`

Where:

* `$to` - Array or comma-separated list of email addresses to send message.
* `$subject` - Email subject
* `$message` - Message contents
* `$headers` - Array or "\n" separated  list of additional headers. Optional.
* `$attachments` - Array or "\n"/"," separated list of files to attach. Optional.

The wp_mail function is sending text emails as default. If you want to send an email with HTML content you have to set the content type to 'text/html' running `add_filter('wp_mail_content_type', 'set_html_content_type');` function before to `wp_mail()` one.

After wp_mail function you need to run the `remove_filter('wp_mail_content_type', 'set_html_content_type');` to remove the 'text/html' filter to avoid conflicts --http://core.trac.wordpress.org/ticket/23578

Example about how to send an HTML email using different headers:

`$subject = 'test plugin';
$message = 'testing WordPress plugin';
$to = 'address1@pepipost.com, Address2 <address2@pepipost.com@>, address3@pepipost.com';
or
$to = array('address1@pepipost.com', 'Address2 <address2@pepipost.com>', 'address3@pepipost.com');
 
$headers = array();
$headers[] = 'From: Me Myself <me@example.net>';
$headers[] = 'Cc: address4@pepipost.com';
$headers[] = 'Bcc: address5@pepipost.com';
 
$attachments = array('/tmp/img1.jpg', '/tmp/img2.jpg');
 
add_filter('wp_mail_content_type', 'set_html_content_type');
$mail = wp_mail($to, $subject, $message, $headers, $attachments);
 
remove_filter('wp_mail_content_type', 'set_html_content_type');`

== Installation ==

Requirements:

1. PHP version >= 5.3.0
2. You need to have PHP-curl extension enabled.

To upload the Pepipost Plugin .ZIP file:

1. Upload the WordPress Pepipost Plugin to the /wp-contents/plugins/ folder.
2. Activate the plugin from the "Plugins" menu in WordPress.
3. Create a Pepipost account at <a href="http://www.pepipost.com/" target="_blank">http://www.pepipost.com/</a>  
4. Once the account is created. Login to your Pepipost account and navigate to "Settings" -> "Account Settings" to get your Pepipost SMTP credentials

== Changelog ==

= 1.0.0 =
* Pepipost Wordpress App released
= 2.0.0 =
* Added pepipost API for sending the emails
= 2.1.0 =
* Bug fixes
= 2.1.1 =
* Bug fixes

