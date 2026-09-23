=== FranchiseVault ===
Contributors: prateekpatidar-prsagr
Tags: franchise, training, portal, lms, learning, modules
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium franchise training portal plugin — manage videos, SOPs, recipes and documents for your franchise network. Zero server storage.

== Description ==

FranchiseVault lets you create a beautiful, secure training portal on any WordPress site.

**Features:**
* Netflix-style dark-mode training portal
* YouTube embed (unlisted/private videos) — no server storage
* Google Drive embed for PDFs, recipes, SOPs, documents, audio
* Image gallery viewer
* Login wall — only registered users can access content
* Dynamic watermark (user email) on all media
* Right-click, keyboard shortcut, and DevTools detection
* Filter by content type and category
* Search and sort modules
* Configurable brand color, name, and portal title

**Storage Strategy:**
* Videos → YouTube (unlisted/private)
* PDFs, SOPs, Recipes, Documents → Google Drive (inline viewer)
* Audio → Google Drive
* Images → Google Drive direct URLs

No server storage needed for media!

== Installation ==

1. Upload the `franchise-vault` folder to `/wp-content/plugins/`
2. Activate the plugin from **Plugins → Installed Plugins**
3. A **Training Portal** page with the `[franchise_vault]` shortcode is created automatically
4. Go to **FranchiseVault → Settings** to configure brand name, color, and login message
5. Go to **FranchiseVault → Add Module** to add your first training module

== Usage ==

= Adding a Module =
1. Go to **FranchiseVault → Add Module**
2. Enter a title and short description
3. Select the content type (Video, PDF, Recipe, etc.)
4. Paste the YouTube URL or Google Drive link
5. Assign categories if needed
6. Publish

= Shortcode =
Place `[franchise_vault]` on any page to display the training portal.

= Google Drive Setup =
1. Upload your file to Google Drive
2. Right-click → Share → Change to "Anyone with the link" (Viewer)
3. Copy the link and paste in the module editor

= YouTube Setup =
1. Upload your video to YouTube
2. Set visibility to "Unlisted"
3. Copy the video URL and paste in the module editor

== Changelog ==

= 1.0.0 =
* Initial release
