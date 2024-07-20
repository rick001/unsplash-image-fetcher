=== Unsplash Image Fetcher ===
Contributors: Techbreeze IT Solutions
Tags: Unsplash, images, featured image, automation
Requires at least: 4.6
Tested up to: 6.6
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Plugin URI: https://www.techbreeze.in/unsplash-image-fetcher-plugin/

Fetches images from Unsplash based on post titles, converts them to PNG, and sets them as featured images automatically.

== Description ==
**Unsplash Image Fetcher** is a simple yet powerful WordPress plugin that automates the process of setting featured images for your posts. It fetches a relevant image from Unsplash based on your post title, converts it to PNG, and sets it as the featured image. Save time and enhance your posts with beautiful, high-quality images effortlessly.

== Features ==
* Automatically fetches an image from Unsplash based on the post title.
* Converts the fetched image to PNG format.
* Sets the PNG image as the featured image for your post.
* Easy-to-use settings page for configuring the Unsplash API key.
* Seamless integration with the WordPress post editor.

== Installation ==
1. **Upload the Plugin Files:**
   - Download the plugin zip file.
   - Go to your WordPress dashboard.
   - Navigate to Plugins > Add New > Upload Plugin.
   - Choose the downloaded zip file and click "Install Now."

2. **Activate the Plugin:**
   - After installation, click on "Activate."

3. **Configure the Unsplash API Key:**
   - Navigate to Settings > Unsplash Image Fetcher.
   - Enter your Unsplash API key in the provided field and save the settings.

== Usage ==
1. **Create or Edit a Post:**
   - Go to Posts > Add New or edit an existing post.
   - Ensure your post has a title.

2. **Save the Post:**
   - When you save or publish your post, the plugin will automatically fetch a relevant image from Unsplash based on your post title, convert it to PNG, and set it as the featured image.

== Frequently Asked Questions ==
= Do I need to sign up for an Unsplash API key? =
Yes, you need to sign up for a free Unsplash API key to use this plugin. You can get it from [Unsplash Developer](https://unsplash.com/developers).

= What if the post already has a featured image? =
If the post already has a featured image, the plugin will not overwrite it.

= What happens if no image is found? =
If no image is found, the plugin will log an error and not set any featured image.

= Can I use this plugin with custom post types? =
Currently, the plugin supports only standard WordPress posts.

== Screenshots ==
1. **Settings Page:** Configure Unsplash API key.
   ![Settings Page](screenshot-1.png)

2. **Example of Featured Image:** Image fetched from Unsplash via API and set as the featured image for the post.
   ![Example of Featured Image](screenshot-2.png)

== Changelog ==
= 1.5 =
* Replaced file_get_contents with wp_remote_get and WP_Filesystem methods.
* Added license information to the plugin header.

= 1.1 =
* Initial release.

== Upgrade Notice ==
= 1.5 =
* Replaced file_get_contents with wp_remote_get and WP_Filesystem methods.
* Added license information to the plugin header.

= 1.1 =
* Initial release.

== Support ==
For any issues or questions, please drop an email to rick@techbreeze.in
