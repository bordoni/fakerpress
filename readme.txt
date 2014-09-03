=== FakerPress ===
Contributors:      iryz, bordoni, luancuba
Tags:              generator, dummy content, lorem ipsun, admin, exemples, testing, taxonomies, users, post type, faker, fake data, random, developer, dev, development, test, tests
Requires at least: 3.7
Tested up to:      4.0
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

FakerPress is a clean way to generate fake data to your WordPress, great for developers who need testing

== Description ==

Whenever you create a new Theme or Plugin you will always need to create custom data to test whether your plugin is working or not, and as Developers ourselves we had this problem quite alot.

Our goal with this plugin is to fill this gap where you have problem with a good solution both for Developers and for Users of WordPress.

**Note: This plugin requires PHP 5.3 or higher to be activated.**

---

= Components Included =

* Posts
* Custom Post Types
* Users
* Tags
* Categories
* Comments

= Creating Dummy Content =
Normally a WordPress developer will need to perform the task of filling up an empty theme with dummy content, and doing this manually can be really time consuming, the main reasons this plugin was create was to speed up this process.

= Delete the Content Generated =
After you are done with your testing it should be easy to delete all the content created using FakerPress, now you will be able to do it.

= Generate Random HTML =
When creating dummy posts what you really want is that the HTML is really random so that you might see bugs that an XML import wouldn't.

= Generate Images in your HTML =
When you are testing your website images are important, so FakerPress will allow you to output Images to your HTML tests.

= Real Browser data on User Comments =
For comments our plugin is prepared to generate a real Browser data instead of leaving the field empty.

= Random Terms generation =
For creating and assigning the terms you will have a much better tool that will allow you to select which kind of taxonomy you want to assign to your posts, and leaving the randomization to the plugin's code.

= Real random User profiles =
If you fill up your WordPress with any data for the user profiles you might not catch an edge case, this plugin will fill up the fields with data that will really matter in the tests.

= Languages =

* English
* Portuguese (Brazil)
* Portuguese (Portugal)

= See room for improvement? =

Great! There are several ways you can get involved to help make FakerPress better:

1. **Report Bugs:** If you find a bug, error or other problem, please report it! You can do this by [creating a new topic](http://wordpress.org/support/plugin/fakerpress) in the plugin forum. Once a developer can verify the bug by reproducing it, they will create an official bug report in GitHub where the bug will be worked on.
2. **Suggest New Features:** Have an awesome idea? Please share it! Simply [create a new topic](http://wordpress.org/support/plugin/fakerpress) in the plugin forum to express your thoughts on why the feature should be included and get a discussion going around your idea.
3. **Issue Pull Requests:** If you're a developer, the easiest way to get involved is to help out on [issues already reported](https://github.com/iryz/fakerpress/issues) in GitHub. Be sure to check out the [contributing guide](https://github.com/iryz/fakerpress/blob/master/contributing.md) for developers.

Thank you for wanting to make FakerPress better for everyone! [We salute you](https://www.youtube.com/watch?v=8fPf6L0XNvM).

== Changelog ==

= 0.1.5 =
* New: Allow post Parent to be chosen on the Admin Form ([#35](https://github.com/iryz/fakerpress/issues/35))
* New: Now allow Image to be used in HTML, with Placehold.it ([#38](https://github.com/iryz/fakerpress/issues/38))
* Tweak: Allow users to choose which HTML tags will be used ([#37](https://github.com/iryz/fakerpress/issues/37))
* Tweak: User Select2 now uses AJAX to prevent bugs on bigger databases ([#43](https://github.com/iryz/fakerpress/issues/43))
* Tweak: Now you can select a range of items to be randomized, instead of always having to input a single number ([#44](https://github.com/iryz/fakerpress/issues/44))

= 0.1.4 =
* New: Delete all content created by Fakerpress ([#26](https://github.com/iryz/fakerpress/issues/26))
* New: Allow users to control `comment_status` on Posts ([#26](https://github.com/iryz/fakerpress/issues/26))
* New: Predefined interval set of dates ([#21](https://github.com/iryz/fakerpress/issues/21))
* Tweak: Prevent the user from selecting a bad combination of date fields ([#20](https://github.com/iryz/fakerpress/issues/20))

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