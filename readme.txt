=== FakerPress ===
Contributors:      bordoni
Tags:              generator, dummy content, dummy data, lorem ipsun, admin, exemples, testing, images, attachments, featured image, taxonomies, users, post type, faker, fake data, random, developer, dev, development, test, tests
Requires at least: 4.7
Tested up to:      5.9
Requires PHP:      5.6
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Donate link:       https://fakerpress.com/r/sponsor

FakerPress is a clean way to generate fake and dummy content to your WordPress, great for developers who need testing

== Description ==

Whenever you create a new Theme or Plugin you will always need to create custom data to test whether your plugin is working or not, and as Developers ourselves we had this problem quite alot.

Our goal with this plugin is to fill this gap where you have problem with a good solution both for Developers and for Users of WordPress.

> **Note: This plugin requires PHP 5.6 or higher to be activated.**

[**Checkout our GitHub Repository**](http://fakerpress.com/r/github)

---

= Components Included =

* Posts
* Custom Post Types
* Meta Data
* Featured Image
* Users
* Tags
* Categories
* Comments
* Custom Comment Types

= Creating Dummy Content =
Normally a WordPress developer will need to perform the task of filling up an empty theme with dummy content, and doing this manually can be really time consuming, the main reasons this plugin was create was to speed up this process.

= Random Featured Images =
Create randomly generated attachments as the Featured Images for your WordPress dummy content.

= Create random Meta Information =
WordPress has Meta for Users, Posts, Terms and Comments, FakerPress will allow you to generate custom dummy meta for all four, with *20 types of Data*

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

= Types of Meta Included =
* Attachment
* WP_Query
* Number
* Elements
* Letter
* Words
* Text
* HTML
* Lexify
* Asciify
* Regexify
* Person
* Geo Information
* Company
* Date
* TimeZone
* Email
* Domain
* IP
* Browser User Agent

= Languages =
We moved away from _Transifex_ due to the new GlotPress on WordPress.org, so if you want to translate FakerPress to your language please [follow this guidelines](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/#translating-themes-plugins).

= See room for improvement? =

Great! There are several ways you can get involved to help make FakerPress better:

1. **Report Bugs:** If you find a bug, error or other problem, please report it! You can do this by [creating a new topic](http://wordpress.org/support/plugin/fakerpress) in the plugin forum. Once a developer can verify the bug by reproducing it, they will create an official bug report in GitHub where the bug will be worked on.
2. **Suggest New Features:** Have an awesome idea? Please share it! Simply [create a new topic](http://wordpress.org/support/plugin/fakerpress) in the plugin forum to express your thoughts on why the feature should be included and get a discussion going around your idea.
3. **Issue Pull Requests:** If you're a developer, the easiest way to get involved is to help out on [issues already reported](https://github.com/bordoni/fakerpress/issues) in GitHub. Be sure to check out the [contributing guide](https://github.com/bordoni/fakerpress/blob/master/contributing.md) for developers.

Thank you for wanting to make FakerPress better for everyone! [We salute you](https://www.youtube.com/watch?v=8fPf6L0XNvM).

== Changelog ==

= 0.5.2 &mdash; 27 of January 2022 =

* Feature: Proper changelog page for users trying to figure out what has changed in the latest version of FakerPress.
* Feature: Excerpt size field added to ensure better control over Post generation prop @gtsantos
* Tweak: jQuery 3.5.X compatibility
* Fix: Prevent warning around post_excerpt usage introduced earlier on in the year. props @pattisahusiwa
* Fix: One more PHP 8 compatibility problem related to the generation of passwords props @DumahX
* Fix: Prevent PHP 8 from throwing a warning because of unnecessary `unlink()` call for attachments props @wpuzman
* Fix: Resolve problems around failed inline images that were unavailable which would cause a PHP Warning.

= 0.5.1 &mdash; 05 of January 2021 =

* Feature: Completely change folder structure to comply with [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading with namespaces.
* Feature: Included a Fields API to more easily generate Fields for controlling fields used in the admin pages.
* Feature: Included a Template class to allow better separation of HTML and PHP files.
* Tweak: Include compatibility with PHP 8+
* Tweak: More well structured `composer.json` and using it's autoloader.
* Tweak: All PHP files now live inside of `src` folder.
* Tweak: Moved all files to use the `[]` array syntax.
* Tweak: GitHub repository no longer tracks the `vendor` folder.
* Fix: Correctly order the `fp_array_get` params with it's usage props @henrikwirth.
* Fix: Make sure WordPress 5.6+ administration proper display fields.
* Fix: Menu icon is properly displayed on version of WordPress 5.5 or higher

= 0.5.0 &mdash; 09 of November, 2019 =

* Feature: Removed 500px as a image provider as that source was deprecated a while ago.
* Fix: Properly handle downloading of images into WordPress, which makes this a lot safer and faster.
* Fix: Resolved the problem where images from the Meta Attachment were been generated with 0x0 size and breaking the attachment.
* Fix: Removed 500px as a image provider as that source was deprecated a while ago.
* Fix: Moved from Unplash.it to their new name as Lorem Picsum.
* Tweak: Started adding proper docblocks to the new methods. Note it will take a couple versions to have all methods with proper docblocks.
* Tweak: Control timeout of image download with filter `fakerpress.module.attachment.download_url_timeout` defaulting to 10 seconds per attachment.
* Tweak: Update required version of PHP to 5.6 to be more along the requirements of WordPress.
* Tweak: Test and update the version of WordPress FakerPress was tested up to, now on 5.3.
* Tweak: Updated the version of fzaninotto/faker (v1.6.0 => v1.8.0)
* Tweak: Updated the version of nesbot/carbon (1.21.0 => 1.39.1)

= 0.4.11 &mdash; 25 of February, 2018 =

* Fix: Prevent fatals from oversight with Namespace and Classnames for User and Post generation &mdash; Thanks [@radgh](https://wordpress.org/support/topic/fakerpress-generate-users-fails/) & [@johny-dotpad](https://wordpress.org/support/topic/post-generation-fails/)
* Fix: Rollback change to `file_get_contents` it was making peoples servers hang to long &mdash; Thanks [@bilimokur](https://wordpress.org/support/topic/most-images-are-corrupted/)

= 0.4.10 &mdash; 11 of February, 2018 =

* Feature: Added size control to Post Content, Comment Content and User Description
* Fix: On image and attachment download we try one more time using `file_get_contents` &mdash; Thanks [@lazlo-toth](https://wordpress.org/support/topic/most-of-the-featured-image-links-appear-to-be-invalid/)
* Fix: Resolve Quantity Min and Max fields weird interaction &mdash; Thanks [@rahmantanvir](https://github.com/bordoni/fakerpress/issues/124)

= 0.4.9 &mdash; 07 of August, 2017 =

* Feature: Allow Attachments to be generated with a given Width and Height range &mdash; Thanks [@COLABORATI](https://github.com/bordoni/fakerpress/issues/86)
* Feature: Generate `post_excerpt` for Posts (how I forgot that is beyond me) &mdash; Thanks [@njbarrett](https://github.com/bordoni/fakerpress/issues/104)
* Fix: Meta field rules were not respecting Configurations due to a JavaScript bug on indexing the fields &mdash; Thanks [@ckpicker](https://github.com/bordoni/fakerpress/issues/115)
* Fix: Resolve problems where attachments wouldn't get setup correctly and throw an Empty Message &mdash; Thanks [@r083r7 and @oyvind_skjelstad](https://wordpress.org/support/topic/featured-image-not-showing-up-6)
* Fix: Allow Term Meta to generate the fields correctly again &mdash; Thanks [@stratboy](https://github.com/bordoni/fakerpress/issues/105)
* Tweak: Prevent `_encloseme` and `_pingme` for FakerPress generated Posts

= 0.4.8 &mdash; 18 of July, 2017 =

* Feature: Now Comments can be generated with different types, allowing for WooCommerce Notes for example - Thanks [@dibbyo456](https://wordpress.org/support/topic/can-i-create-custom-comments/)
* Feature: Comments for Custom Post Types - Thanks [@jasondevine](https://github.com/bordoni/fakerpress/issues/109)
* Tweak: Added two new filters to Filter Meta Value `fakerpress.module.meta.value` and `fakerpress.module.meta.{$key}.value` - Thanks [@Mte90](https://github.com/bordoni/fakerpress/pull/111)
* Fix: Resolve problems on failed Meta generation - Thanks [@Mte90](https://github.com/bordoni/fakerpress/pull/110)
* Fix: Typo on Provider text for Attachment Meta - Thanks [@codiceovvio](https://github.com/bordoni/fakerpress/pull/103)

= 0.4.7 &mdash; 2 of October, 2016 =

* Feature: Image Attachment Meta Field to allow more Flexibility all around the plugin

= 0.4.6 &mdash; 14 of June, 2016 =

* Fix: Post Meta and taxonomy is finally working again &mdash; Thanks [@peachey_a](https://wordpress.org/support/topic/generated-posts-not-assigned-categories) and [@zoeitsolutions](https://wordpress.org/support/topic/user-meta-not-being-generated)
* Fix: Allow Meta Number generation using any type of range, doesn't limit from 0 to 9

= 0.4.5 &mdash; 11 of June, 2016 =

* Fix: Users Module was using Post Meta methods on flag related methods, preventing the users to be deleted when "Let it Go!" &mdash; Thanks [@derpixler](https://github.com/bordoni/fakerpress/issues/84)
* Fix: Taxonomy and Meta Modules had a compatibility problem with Faker, preveting users to use Meta and Taxonomy Properly &mdash; Thanks [@rayrutjes](https://github.com/bordoni/fakerpress/issues/94)
* Tweak: Add a better description for a few fields
* Tweak: Include [latest code from Faker](https://github.com/fzaninotto/Faker) version > 1.6.0

= 0.4.4 &mdash; 1 of April, 2016 =

* Feature: Address Meta templating now allows you to fetch Country ABBR and Code &mdash; Thanks [@kirilisa](https://wordpress.org/support/topic/excellent-3360)
* Tweak: Include [latest code from Faker](https://github.com/fzaninotto/Faker) version > 1.5.0
* Tweak: WP_Query Meta for attachments is a little bit easier now, prediction of forgotten `post_status`
* Tweak: Make the Post Parent selection easier to know which posts by showing more information on the items &mdash; Thanks [@fxbernard](https://github.com/bordoni/fakerpress/issues/81)
* Fix: Get some missing Text Domains working &mdash; Thanks [@ginsterbusch](https://github.com/bordoni/fakerpress/issues/77)

= 0.4.3 &mdash; 1 of March, 2016 =

* Feature: No more Hot-linking external sites on Content Images &mdash; Thanks [b0rg](https://profiles.wordpress.org/b0rg) + [mvaneijgen](https://profiles.wordpress.org/mvaneijgen) + [okvee](https://profiles.wordpress.org/okvee)
* Tweak: Use `wp_remote_get` to fetch external images &mdash; Thanks [revaxarts](https://twitter.com/revaxarts)
* Tweak: Improve how we handle the Deletes to make sure it doesn't delete all your site &mdash; Thanks [Paul Mckay](https://twitter.com/McKay_1988/status/700299519825723392)
* Fix: Prevent Notices from happening when no Image providers were selected

= 0.4.2 &mdash; 9 of November, 2015 =

* Feature: Include meta for Terms for WordPress 4.4 and Up
* Tweak: Include a more granular control over Taxonomy selector for Posts
* Fix: CSS changes for WordPress 4.4

= 0.4.1 &mdash; 24 of September, 2015 =

* Tweak: Improve Modules code in general, if you have custom code based on Modules please check ([#71](https://github.com/bordoni/fakerpress/pull/71))
* Fix: Generate button was locking after a warning for trying a bad request
* Fix: Improved Modules JavaScript to prevent duplicated arguments on AJAX requests &mdash; Thanks [Jonathan Brinley](https://profiles.wordpress.org/jbrinley/)

= 0.4.0 &mdash; 21 of September, 2015 =

* Feature: Now Modules use AJAX to fake, AKA no more timeouts for big dummy creation
* Fix: handle WP_Error on term creation, avoids fatal errors if the term already exists &mdash; Thanks [Jonathan Brinley](https://profiles.wordpress.org/jbrinley/)

= 0.3.3 &mdash; 10 of June, 2015 =

* Tweak: Our menus and submenus no longer require JavaScript to work
* Tweak: Featured Images now are linked to the post using the `post_parent` column &mdash; Reported by [Bruno DC](https://profiles.wordpress.org/decarvalho_bruno)
* Fix: Dates on Meta fields are fully working again &mdash; Thanks [Ethan Clevenger](https://profiles.wordpress.org/eclev91)

= 0.3.2 &mdash; 25 of May, 2015 =

* New: Including LoremPixel as a Image Provider &mdash; Thanks [examinedliving](https://github.com/examinedliving)
* Fix: A few JavaScript/jQuery tweeks for better Select2 Handling on Dates
* Fix: Intervals now have a better Handling for non-timed Strings &mdash; Thanks [alfiemx_](https://profiles.wordpress.org/alfiemx_)
* Fix: Better verification of Carbon inclusion &mdash; Thanks [Frankie Jarrett](https://profiles.wordpress.org/fjarrett/)
* Fix: Closures now using self variables better, prevents Fatal Error &mdash; Thanks [fccoelho7](https://profiles.wordpress.org/fccoelho7/)

= 0.3.1 &mdash; 02 of May, 2015 =

* Fix: Date Meta Field is now working as expected
* Fix: Empty meta fields don't throw Fatal Errors anymore &mdash; Thanks [Jeffrey Carandang](https://profiles.wordpress.org/phpbits/)

= 0.3.0 &mdash; 01 of May, 2015 =

* New: Now you will be able to generate Custom dummy Meta for your Posts, Users and Comments

= 0.2.2 &mdash; 15 of April, 2015 =

* New: 500px as a Image provider ( You will need a Customer App Key )
* Tweak: New formula for Taxonomy randomization for Posts ( with new filters )

= 0.2.1 &mdash; 02 of April, 2015 =

* Fix: User generator now working again (sorry about that)

= 0.2.0 &mdash; 01 of April, 2015 =

* New: Featured Images is now an Option on our Plugin
* New: Handling of Post Meta, still under the hood but preparation for the next versions

= 0.1.6 &mdash; 07 of March, 2015 =

* Fix: Prevent Carbon to Fatal error if trying to be included twice ([#50](https://github.com/bordoni/fakerpress/issues/50))
* Tweak: Better checking for the content flag when deleting

= 0.1.5 &mdash; 03 September, 2014 =

* New: Allow post Parent to be chosen on the Admin Form ([#35](https://github.com/bordoni/fakerpress/issues/35))
* New: Now allow Image to be used in HTML, with Placehold.it ([#38](https://github.com/bordoni/fakerpress/issues/38))
* Tweak: Allow users to choose which HTML tags will be used ([#37](https://github.com/bordoni/fakerpress/issues/37))
* Tweak: User Select2 now uses AJAX to prevent bugs on bigger databases ([#43](https://github.com/bordoni/fakerpress/issues/43))
* Tweak: Now you can select a range of items to be randomized, instead of always having to input a single number ([#44](https://github.com/bordoni/fakerpress/issues/44))

= 0.1.4 &mdash; 15 of August, 2014 =

* New: Delete all content created by Fakerpress ([#26](https://github.com/bordoni/fakerpress/issues/26))
* New: Allow users to control `comment_status` on Posts ([#26](https://github.com/bordoni/fakerpress/issues/26))
* New: Predefined interval set of dates ([#21](https://github.com/bordoni/fakerpress/issues/21))
* Tweak: Prevent the user from selecting a bad combination of date fields ([#20](https://github.com/bordoni/fakerpress/issues/20))

= 0.1.3 &mdash; 25 of June, 2014 =

* Fixing a problem where the UI folder was not included in the final version

= 0.1.2 &mdash; 24 of June, 2014 =

* New: Admin messages for all pages ([#10](https://github.com/bordoni/fakerpress/issues/10))
* New: Select Date range for Comments and Posts ([#11](https://github.com/bordoni/fakerpress/issues/11))
* New: Select Author sampling group for Posts ([#11](https://github.com/bordoni/fakerpress/issues/11))
* New: Roles sampling group for Users ([#13](https://github.com/bordoni/fakerpress/issues/13))
* New: Taxonomies sampling group for Terms ([#13](https://github.com/bordoni/fakerpress/issues/13))
* New: Selection of Post Type for Posts ([#13](https://github.com/bordoni/fakerpress/issues/13))
* New: Selection of Terms sampling group for Posts ([#13](https://github.com/bordoni/fakerpress/issues/13))
* Tweak: Select2 usage to improve fields ([#13](https://github.com/bordoni/fakerpress/issues/13))
* Fix: `admin_title` been overwritten ([#14](https://github.com/bordoni/fakerpress/issues/14))

= 0.1.1 &mdash; 17 of June, 2014 =

* Fatal Error generate by a missing file Carbon related fixed

= 0.1.0 &mdash; 17 of June, 2014 =

* First initial concept of using [Faker](https://github.com/fzaninotto/Faker) to generate data on WordPress
