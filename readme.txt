=== FakerPress ===
Contributors:      iryz, bordoni, luancuba
Tags:              generator, dummy content, lorem ipsun, admin, exemples, testing, taxonomies, users, post type, faker, fake data, random
Requires at least: 3.7
Tested up to:      3.9
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

FakerPress is a clean way to generate fake data to your WordPress, great for developers who need testing

== Description ==

**Note: This plugin requires PHP 5.3 or higher to be activated.**

Whenever you create a new Theme or Plugin you will always need to create custom data to test whether your plugin is working or not, and as Developers ourselves we had this problem quite alot.

Our goal with this plugin is to fill this gap where you have problem with a good solution both for Developers and for Users of WordPress.

**Create Fake Data for:**

 * Posts
 * Custom Post Types
 * Users
 * Tags
 * Categories
 * Comments

**Noteworthy features:**

 * Create random HTML to test your Theme
 * Generate comments with random User data
 * Create fake Taxonomy terms and Assign it to random posts
 * Generate fake User Data to test your plugin

**Languages:**

 * English
 * Portuguese (Brazil)

**See room for improvement?**

Great! There are several ways you can get involved to help make FakerPress better:

1. **Report Bugs:** If you find a bug, error or other problem, please report it! You can do this by [creating a new topic](http://wordpress.org/support/plugin/fakerpress) in the plugin forum. Once a developer can verify the bug by reproducing it, they will create an official bug report in GitHub where the bug will be worked on.
2. **Suggest New Features:** Have an awesome idea? Please share it! Simply [create a new topic](http://wordpress.org/support/plugin/fakerpress) in the plugin forum to express your thoughts on why the feature should be included and get a discussion going around your idea.
3. **Issue Pull Requests:** If you're a developer, the easiest way to get involved is to help out on [issues already reported](https://github.com/iryz/fakerpress/issues) in GitHub. Be sure to check out the [contributing guide](https://github.com/iryz/fakerpress/blob/master/contributing.md) for developers.

Thank you for wanting to make FakerPress better for everyone! [We salute you](https://www.youtube.com/watch?v=8fPf6L0XNvM).

== Changelog ==

= 0.1.3 =
* Fixing a problem where the UI folder was not included in the final version

= 0.1.2 =
* New: Admin messages for all pages ([#10](https://github.com/iryz/fakerpress/issues/10))
* New: Select Date range for Comments and Posts ([#11](https://github.com/iryz/fakerpress/issues/11))
* New: Select Author sampling group for Posts ([#11](https://github.com/iryz/fakerpress/issues/11))
* New: Roles sampling group for Users ([#13](https://github.com/iryz/fakerpress/issues/13))
* New: Taxonomies sampling group for Terms ([#13](https://github.com/iryz/fakerpress/issues/13))
* New: Selection of Post Type for Posts ([#13](https://github.com/iryz/fakerpress/issues/13))
* New: Selection of Terms sampling group for Posts ([#13](https://github.com/iryz/fakerpress/issues/13))
* Tweak: Select2 usage to improve fields ([#13](https://github.com/iryz/fakerpress/issues/13))
* Fix: `admin_title` been overwritten ([#14](https://github.com/iryz/fakerpress/issues/14))

**Props**: [bordoni](http://profiles.wordpress.org/bordoni/), [luancuba](http://profiles.wordpress.org/luancuba/),

= 0.1.1 =
* Fatal Error gerated by a missing file Carbon related fixed

= 0.1.0 =
* First initial concept of using [Faker](https://github.com/fzaninotto/Faker) to gerenate data on WordPress