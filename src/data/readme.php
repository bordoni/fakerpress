<?php return json_decode( '{"headers":{"name":"headers","content":"=== FakerPress ===\nContributors:      bordoni\nTags:              generator, dummy content, dummy data, lorem ipsun, admin, exemples, testing, images, attachments, featured image, taxonomies, users, post type, faker, fake data, random, developer, dev, development, test, tests\nRequires at least: 5.5\nTested up to:      6.2\nRequires PHP:      7.4\nStable tag:        0.6.2\nLicense:           GPLv2 or later\nLicense URI:       http:\/\/www.gnu.org\/licenses\/gpl-2.0.html\nDonate link:       https:\/\/fakerpress.com\/r\/sponsor\n\nFakerPress is a clean way to generate fake and dummy content to your WordPress, great for developers who need testing\n\n"},"description":{"name":"Description","content":"\n\nWhenever you create a new Theme or Plugin you will always need to create custom data to test whether your plugin is working or not, and as Developers ourselves we had this problem quite alot.\n\nOur goal with this plugin is to fill this gap where you have problem with a good solution both for Developers and for Users of WordPress.\n\n\u003E **Note: This plugin requires PHP 7.4 or higher to be activated.**\n\n[**Checkout our GitHub Repository**](http:\/\/fakerpress.com\/r\/github)\n\n---\n\n= Components Included =\n\n* Posts\n* Custom Post Types\n* Meta Data\n* Featured Image\n* Users\n* Tags\n* Categories\n* Comments\n* Custom Comment Types\n\n= Creating Dummy Content =\nNormally a WordPress developer will need to perform the task of filling up an empty theme with dummy content, and doing this manually can be really time consuming, the main reasons this plugin was create was to speed up this process.\n\n= Random Featured Images =\nCreate randomly generated attachments as the Featured Images for your WordPress dummy content.\n\n= Create random Meta Information =\nWordPress has Meta for Users, Posts, Terms and Comments, FakerPress will allow you to generate custom dummy meta for all four, with *20 types of Data*\n\n= Delete the Content Generated =\nAfter you are done with your testing it should be easy to delete all the content created using FakerPress, now you will be able to do it.\n\n= Generate Random HTML =\nWhen creating dummy posts what you really want is that the HTML is really random so that you might see bugs that an XML import wouldn\u0027t.\n\n= Generate Images in your HTML =\nWhen you are testing your website images are important, so FakerPress will allow you to output Images to your HTML tests.\n\n= Real Browser data on User Comments =\nFor comments our plugin is prepared to generate a real Browser data instead of leaving the field empty.\n\n= Random Terms generation =\nFor creating and assigning the terms you will have a much better tool that will allow you to select which kind of taxonomy you want to assign to your posts, and leaving the randomization to the plugin\u0027s code.\n\n= Real random User profiles =\nIf you fill up your WordPress with any data for the user profiles you might not catch an edge case, this plugin will fill up the fields with data that will really matter in the tests.\n\n= Types of Meta Included =\n* Attachment\n* WP_Query\n* Number\n* Elements\n* Letter\n* Words\n* Text\n* HTML\n* Lexify\n* Asciify\n* Regexify\n* Person\n* Geo Information\n* Company\n* Date\n* TimeZone\n* Email\n* Domain\n* IP\n* Browser User Agent\n\n= Languages =\nWe moved away from _Transifex_ due to the new GlotPress on WordPress.org, so if you want to translate FakerPress to your language please [follow this guidelines](https:\/\/make.wordpress.org\/polyglots\/handbook\/rosetta\/theme-plugin-directories\/#translating-themes-plugins).\n\n= See room for improvement? =\n\nGreat! There are several ways you can get involved to help make FakerPress better:\n\n1. **Report Bugs:** If you find a bug, error or other problem, please report it! You can do this by [creating a new topic](http:\/\/wordpress.org\/support\/plugin\/fakerpress) in the plugin forum. Once a developer can verify the bug by reproducing it, they will create an official bug report in GitHub where the bug will be worked on.\n2. **Suggest New Features:** Have an awesome idea? Please share it! Simply [create a new topic](http:\/\/wordpress.org\/support\/plugin\/fakerpress) in the plugin forum to express your thoughts on why the feature should be included and get a discussion going around your idea.\n3. **Issue Pull Requests:** If you\u0027re a developer, the easiest way to get involved is to help out on [issues already reported](https:\/\/github.com\/bordoni\/fakerpress\/issues) in GitHub. Be sure to check out the [contributing guide](https:\/\/github.com\/bordoni\/fakerpress\/blob\/master\/contributing.md) for developers.\n\nThank you for wanting to make FakerPress better for everyone! [We salute you](https:\/\/www.youtube.com\/watch?v=8fPf6L0XNvM).\n\n"},"changelog":{"name":"Changelog","content":"\n\n= 0.6.2 \u0026mdash; 10 of July 2023 =\n\n* Fix - Resolve a problem with Numbers Meta throwing errors on PHP 8.0+ [#168]\n\n= 0.6.1 \u0026mdash; 04 of April 2023 =\n\n* Requirement - PHP Version 7.4 required for usage of FakerPress, important step to allow further improvements and tests.\n* Version - Updated composer dependency `lucatume\/di52` to version `3.3.1`.\n* Version - Updated composer dependency `fakerphp\/faker` to version `1.21`.\n* Version - Updated composer dependency `nesbot\/carbon to version `2.66`.\n* Fix - Resolve some errors happening with Carbon and version 8.2 of PHP.\n\n= 0.6.0 \u0026mdash; 30 of March 2022 =\n\n* Requirement - PHP Version 7.1 required for usage of FakerPress, important step to allow further improvements and tests.\n* Feature - Modifications to the internal Modules of FakerPress to enable future work around WP-CLI.\n* Feature - Include a new way to handle Administration Pages, included here to enable future work.\n* Enhancement - Modifications to Select2 Styles for better accessibility usage.\n* Tweak - Faker version dependency updated from `fzaninotto\/Faker` to `fakerphp\/Faker\/`.\n* Tweak - Move everything into the FakerPress namespace.\n* Tweak - Autoload using Composer properly.\n\n= 0.5.3 \u0026mdash; 04 of March 2022 =\n\n* Fix - Ensure Select2 usage of AJAX search properly encodes on all usages props @TheMMMdev\n* Fix - Ensure Select2 usage of AJAX properly checks for nonces and permissions props @TheMMMdev\n\n= 0.5.2 \u0026mdash; 27 of January 2022 =\n\n* Feature: Proper changelog page for users trying to figure out what has changed in the latest version of FakerPress.\n* Feature: Excerpt size field added to ensure better control over Post generation prop @gtsantos\n* Tweak: jQuery 3.5.X compatibility\n* Fix: Prevent warning around post_excerpt usage introduced earlier on in the year. props @pattisahusiwa\n* Fix: One more PHP 8 compatibility problem related to the generation of passwords props @DumahX\n* Fix: Prevent PHP 8 from throwing a warning because of unnecessary `unlink()` call for attachments props @wpuzman\n* Fix: Resolve problems around failed inline images that were unavailable which would cause a PHP Warning.\n\n= 0.5.1 \u0026mdash; 05 of January 2021 =\n\n* Feature: Completely change folder structure to comply with [PSR-4](https:\/\/www.php-fig.org\/psr\/psr-4\/) autoloading with namespaces.\n* Feature: Included a Fields API to more easily generate Fields for controlling fields used in the admin pages.\n* Feature: Included a Template class to allow better separation of HTML and PHP files.\n* Tweak: Include compatibility with PHP 8+\n* Tweak: More well structured `composer.json` and using it\u0027s autoloader.\n* Tweak: All PHP files now live inside of `src` folder.\n* Tweak: Moved all files to use the `[]` array syntax.\n* Tweak: GitHub repository no longer tracks the `vendor` folder.\n* Fix: Correctly order the `fp_array_get` params with it\u0027s usage props @henrikwirth.\n* Fix: Make sure WordPress 5.6+ administration proper display fields.\n* Fix: Menu icon is properly displayed on version of WordPress 5.5 or higher\n\n= 0.5.0 \u0026mdash; 09 of November, 2019 =\n\n* Feature: Removed 500px as a image provider as that source was deprecated a while ago.\n* Fix: Properly handle downloading of images into WordPress, which makes this a lot safer and faster.\n* Fix: Resolved the problem where images from the Meta Attachment were been generated with 0x0 size and breaking the attachment.\n* Fix: Removed 500px as a image provider as that source was deprecated a while ago.\n* Fix: Moved from Unplash.it to their new name as Lorem Picsum.\n* Tweak: Started adding proper docblocks to the new methods. Note it will take a couple versions to have all methods with proper docblocks.\n* Tweak: Control timeout of image download with filter `fakerpress.module.attachment.download_url_timeout` defaulting to 10 seconds per attachment.\n* Tweak: Update required version of PHP to 5.6 to be more along the requirements of WordPress.\n* Tweak: Test and update the version of WordPress FakerPress was tested up to, now on 5.3.\n* Tweak: Updated the version of fzaninotto\/faker (v1.6.0 =\u003E v1.8.0)\n* Tweak: Updated the version of nesbot\/carbon (1.21.0 =\u003E 1.39.1)\n\n= 0.4.11 \u0026mdash; 25 of February, 2018 =\n\n* Fix: Prevent fatals from oversight with Namespace and Classnames for User and Post generation \u0026mdash; Thanks [@radgh](https:\/\/wordpress.org\/support\/topic\/fakerpress-generate-users-fails\/) \u0026 [@johny-dotpad](https:\/\/wordpress.org\/support\/topic\/post-generation-fails\/)\n* Fix: Rollback change to `file_get_contents` it was making peoples servers hang to long \u0026mdash; Thanks [@bilimokur](https:\/\/wordpress.org\/support\/topic\/most-images-are-corrupted\/)\n\n= 0.4.10 \u0026mdash; 11 of February, 2018 =\n\n* Feature: Added size control to Post Content, Comment Content and User Description\n* Fix: On image and attachment download we try one more time using `file_get_contents` \u0026mdash; Thanks [@lazlo-toth](https:\/\/wordpress.org\/support\/topic\/most-of-the-featured-image-links-appear-to-be-invalid\/)\n* Fix: Resolve Quantity Min and Max fields weird interaction \u0026mdash; Thanks [@rahmantanvir](https:\/\/github.com\/bordoni\/fakerpress\/issues\/124)\n\n= 0.4.9 \u0026mdash; 07 of August, 2017 =\n\n* Feature: Allow Attachments to be generated with a given Width and Height range \u0026mdash; Thanks [@COLABORATI](https:\/\/github.com\/bordoni\/fakerpress\/issues\/86)\n* Feature: Generate `post_excerpt` for Posts (how I forgot that is beyond me) \u0026mdash; Thanks [@njbarrett](https:\/\/github.com\/bordoni\/fakerpress\/issues\/104)\n* Fix: Meta field rules were not respecting Configurations due to a JavaScript bug on indexing the fields \u0026mdash; Thanks [@ckpicker](https:\/\/github.com\/bordoni\/fakerpress\/issues\/115)\n* Fix: Resolve problems where attachments wouldn\u0027t get setup correctly and throw an Empty Message \u0026mdash; Thanks [@r083r7 and @oyvind_skjelstad](https:\/\/wordpress.org\/support\/topic\/featured-image-not-showing-up-6)\n* Fix: Allow Term Meta to generate the fields correctly again \u0026mdash; Thanks [@stratboy](https:\/\/github.com\/bordoni\/fakerpress\/issues\/105)\n* Tweak: Prevent `_encloseme` and `_pingme` for FakerPress generated Posts\n\n= 0.4.8 \u0026mdash; 18 of July, 2017 =\n\n* Feature: Now Comments can be generated with different types, allowing for WooCommerce Notes for example - Thanks [@dibbyo456](https:\/\/wordpress.org\/support\/topic\/can-i-create-custom-comments\/)\n* Feature: Comments for Custom Post Types - Thanks [@jasondevine](https:\/\/github.com\/bordoni\/fakerpress\/issues\/109)\n* Tweak: Added two new filters to Filter Meta Value `fakerpress.module.meta.value` and `fakerpress.module.meta.{$key}.value` - Thanks [@Mte90](https:\/\/github.com\/bordoni\/fakerpress\/pull\/111)\n* Fix: Resolve problems on failed Meta generation - Thanks [@Mte90](https:\/\/github.com\/bordoni\/fakerpress\/pull\/110)\n* Fix: Typo on Provider text for Attachment Meta - Thanks [@codiceovvio](https:\/\/github.com\/bordoni\/fakerpress\/pull\/103)\n\n= 0.4.7 \u0026mdash; 2 of October, 2016 =\n\n* Feature: Image Attachment Meta Field to allow more Flexibility all around the plugin\n\n= 0.4.6 \u0026mdash; 14 of June, 2016 =\n\n* Fix: Post Meta and taxonomy is finally working again \u0026mdash; Thanks [@peachey_a](https:\/\/wordpress.org\/support\/topic\/generated-posts-not-assigned-categories) and [@zoeitsolutions](https:\/\/wordpress.org\/support\/topic\/user-meta-not-being-generated)\n* Fix: Allow Meta Number generation using any type of range, doesn\u0027t limit from 0 to 9\n\n= 0.4.5 \u0026mdash; 11 of June, 2016 =\n\n* Fix: Users Module was using Post Meta methods on flag related methods, preventing the users to be deleted when \u0022Let it Go!\u0022 \u0026mdash; Thanks [@derpixler](https:\/\/github.com\/bordoni\/fakerpress\/issues\/84)\n* Fix: Taxonomy and Meta Modules had a compatibility problem with Faker, preveting users to use Meta and Taxonomy Properly \u0026mdash; Thanks [@rayrutjes](https:\/\/github.com\/bordoni\/fakerpress\/issues\/94)\n* Tweak: Add a better description for a few fields\n* Tweak: Include [latest code from Faker](https:\/\/github.com\/fzaninotto\/Faker) version \u003E 1.6.0\n\n= 0.4.4 \u0026mdash; 1 of April, 2016 =\n\n* Feature: Address Meta templating now allows you to fetch Country ABBR and Code \u0026mdash; Thanks [@kirilisa](https:\/\/wordpress.org\/support\/topic\/excellent-3360)\n* Tweak: Include [latest code from Faker](https:\/\/github.com\/fzaninotto\/Faker) version \u003E 1.5.0\n* Tweak: WP_Query Meta for attachments is a little bit easier now, prediction of forgotten `post_status`\n* Tweak: Make the Post Parent selection easier to know which posts by showing more information on the items \u0026mdash; Thanks [@fxbernard](https:\/\/github.com\/bordoni\/fakerpress\/issues\/81)\n* Fix: Get some missing Text Domains working \u0026mdash; Thanks [@ginsterbusch](https:\/\/github.com\/bordoni\/fakerpress\/issues\/77)\n\n= 0.4.3 \u0026mdash; 1 of March, 2016 =\n\n* Feature: No more Hot-linking external sites on Content Images \u0026mdash; Thanks [b0rg](https:\/\/profiles.wordpress.org\/b0rg) + [mvaneijgen](https:\/\/profiles.wordpress.org\/mvaneijgen) + [okvee](https:\/\/profiles.wordpress.org\/okvee)\n* Tweak: Use `wp_remote_get` to fetch external images \u0026mdash; Thanks [revaxarts](https:\/\/twitter.com\/revaxarts)\n* Tweak: Improve how we handle the Deletes to make sure it doesn\u0027t delete all your site \u0026mdash; Thanks [Paul Mckay](https:\/\/twitter.com\/McKay_1988\/status\/700299519825723392)\n* Fix: Prevent Notices from happening when no Image providers were selected\n\n= 0.4.2 \u0026mdash; 9 of November, 2015 =\n\n* Feature: Include meta for Terms for WordPress 4.4 and Up\n* Tweak: Include a more granular control over Taxonomy selector for Posts\n* Fix: CSS changes for WordPress 4.4\n\n= 0.4.1 \u0026mdash; 24 of September, 2015 =\n\n* Tweak: Improve Modules code in general, if you have custom code based on Modules please check ([#71](https:\/\/github.com\/bordoni\/fakerpress\/pull\/71))\n* Fix: Generate button was locking after a warning for trying a bad request\n* Fix: Improved Modules JavaScript to prevent duplicated arguments on AJAX requests \u0026mdash; Thanks [Jonathan Brinley](https:\/\/profiles.wordpress.org\/jbrinley\/)\n\n= 0.4.0 \u0026mdash; 21 of September, 2015 =\n\n* Feature: Now Modules use AJAX to fake, AKA no more timeouts for big dummy creation\n* Fix: handle WP_Error on term creation, avoids fatal errors if the term already exists \u0026mdash; Thanks [Jonathan Brinley](https:\/\/profiles.wordpress.org\/jbrinley\/)\n\n= 0.3.3 \u0026mdash; 10 of June, 2015 =\n\n* Tweak: Our menus and submenus no longer require JavaScript to work\n* Tweak: Featured Images now are linked to the post using the `post_parent` column \u0026mdash; Reported by [Bruno DC](https:\/\/profiles.wordpress.org\/decarvalho_bruno)\n* Fix: Dates on Meta fields are fully working again \u0026mdash; Thanks [Ethan Clevenger](https:\/\/profiles.wordpress.org\/eclev91)\n\n= 0.3.2 \u0026mdash; 25 of May, 2015 =\n\n* New: Including LoremPixel as a Image Provider \u0026mdash; Thanks [examinedliving](https:\/\/github.com\/examinedliving)\n* Fix: A few JavaScript\/jQuery tweeks for better Select2 Handling on Dates\n* Fix: Intervals now have a better Handling for non-timed Strings \u0026mdash; Thanks [alfiemx_](https:\/\/profiles.wordpress.org\/alfiemx_)\n* Fix: Better verification of Carbon inclusion \u0026mdash; Thanks [Frankie Jarrett](https:\/\/profiles.wordpress.org\/fjarrett\/)\n* Fix: Closures now using self variables better, prevents Fatal Error \u0026mdash; Thanks [fccoelho7](https:\/\/profiles.wordpress.org\/fccoelho7\/)\n\n= 0.3.1 \u0026mdash; 02 of May, 2015 =\n\n* Fix: Date Meta Field is now working as expected\n* Fix: Empty meta fields don\u0027t throw Fatal Errors anymore \u0026mdash; Thanks [Jeffrey Carandang](https:\/\/profiles.wordpress.org\/phpbits\/)\n\n= 0.3.0 \u0026mdash; 01 of May, 2015 =\n\n* New: Now you will be able to generate Custom dummy Meta for your Posts, Users and Comments\n\n= 0.2.2 \u0026mdash; 15 of April, 2015 =\n\n* New: 500px as a Image provider ( You will need a Customer App Key )\n* Tweak: New formula for Taxonomy randomization for Posts ( with new filters )\n\n= 0.2.1 \u0026mdash; 02 of April, 2015 =\n\n* Fix: User generator now working again (sorry about that)\n\n= 0.2.0 \u0026mdash; 01 of April, 2015 =\n\n* New: Featured Images is now an Option on our Plugin\n* New: Handling of Post Meta, still under the hood but preparation for the next versions\n\n= 0.1.6 \u0026mdash; 07 of March, 2015 =\n\n* Fix: Prevent Carbon to Fatal error if trying to be included twice ([#50](https:\/\/github.com\/bordoni\/fakerpress\/issues\/50))\n* Tweak: Better checking for the content flag when deleting\n\n= 0.1.5 \u0026mdash; 03 September, 2014 =\n\n* New: Allow post Parent to be chosen on the Admin Form ([#35](https:\/\/github.com\/bordoni\/fakerpress\/issues\/35))\n* New: Now allow Image to be used in HTML, with Placehold.it ([#38](https:\/\/github.com\/bordoni\/fakerpress\/issues\/38))\n* Tweak: Allow users to choose which HTML tags will be used ([#37](https:\/\/github.com\/bordoni\/fakerpress\/issues\/37))\n* Tweak: User Select2 now uses AJAX to prevent bugs on bigger databases ([#43](https:\/\/github.com\/bordoni\/fakerpress\/issues\/43))\n* Tweak: Now you can select a range of items to be randomized, instead of always having to input a single number ([#44](https:\/\/github.com\/bordoni\/fakerpress\/issues\/44))\n\n= 0.1.4 \u0026mdash; 15 of August, 2014 =\n\n* New: Delete all content created by Fakerpress ([#26](https:\/\/github.com\/bordoni\/fakerpress\/issues\/26))\n* New: Allow users to control `comment_status` on Posts ([#26](https:\/\/github.com\/bordoni\/fakerpress\/issues\/26))\n* New: Predefined interval set of dates ([#21](https:\/\/github.com\/bordoni\/fakerpress\/issues\/21))\n* Tweak: Prevent the user from selecting a bad combination of date fields ([#20](https:\/\/github.com\/bordoni\/fakerpress\/issues\/20))\n\n= 0.1.3 \u0026mdash; 25 of June, 2014 =\n\n* Fixing a problem where the UI folder was not included in the final version\n\n= 0.1.2 \u0026mdash; 24 of June, 2014 =\n\n* New: Admin messages for all pages ([#10](https:\/\/github.com\/bordoni\/fakerpress\/issues\/10))\n* New: Select Date range for Comments and Posts ([#11](https:\/\/github.com\/bordoni\/fakerpress\/issues\/11))\n* New: Select Author sampling group for Posts ([#11](https:\/\/github.com\/bordoni\/fakerpress\/issues\/11))\n* New: Roles sampling group for Users ([#13](https:\/\/github.com\/bordoni\/fakerpress\/issues\/13))\n* New: Taxonomies sampling group for Terms ([#13](https:\/\/github.com\/bordoni\/fakerpress\/issues\/13))\n* New: Selection of Post Type for Posts ([#13](https:\/\/github.com\/bordoni\/fakerpress\/issues\/13))\n* New: Selection of Terms sampling group for Posts ([#13](https:\/\/github.com\/bordoni\/fakerpress\/issues\/13))\n* Tweak: Select2 usage to improve fields ([#13](https:\/\/github.com\/bordoni\/fakerpress\/issues\/13))\n* Fix: `admin_title` been overwritten ([#14](https:\/\/github.com\/bordoni\/fakerpress\/issues\/14))\n\n= 0.1.1 \u0026mdash; 17 of June, 2014 =\n\n* Fatal Error generate by a missing file Carbon related fixed\n\n= 0.1.0 \u0026mdash; 17 of June, 2014 =\n\n* First initial concept of using [Faker](https:\/\/github.com\/fzaninotto\/Faker) to generate data on WordPress","versions":{"0.6.2":{"number":"0.6.2","date":"10 of July 2023","content":"* Fix - Resolve a problem with Numbers Meta throwing errors on PHP 8.0+ [#168]","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFix - Resolve a problem with Numbers Meta throwing errors on PHP 8.0+ [#168]\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.6.1":{"number":"0.6.1","date":"04 of April 2023","content":"* Requirement - PHP Version 7.4 required for usage of FakerPress, important step to allow further improvements and tests.\n* Version - Updated composer dependency `lucatume\/di52` to version `3.3.1`.\n* Version - Updated composer dependency `fakerphp\/faker` to version `1.21`.\n* Version - Updated composer dependency `nesbot\/carbon to version `2.66`.\n* Fix - Resolve some errors happening with Carbon and version 8.2 of PHP.","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003ERequirement - PHP Version 7.4 required for usage of FakerPress, important step to allow further improvements and tests.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EVersion - Updated composer dependency \u003Ccode\u003Elucatume\/di52\u003C\/code\u003E to version \u003Ccode\u003E3.3.1\u003C\/code\u003E.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EVersion - Updated composer dependency \u003Ccode\u003Efakerphp\/faker\u003C\/code\u003E to version \u003Ccode\u003E1.21\u003C\/code\u003E.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EVersion - Updated composer dependency \u003Ccode\u003Enesbot\/carbon to version \u003C\/code\u003E2.66`.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix - Resolve some errors happening with Carbon and version 8.2 of PHP.\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.6.0":{"number":"0.6.0","date":"30 of March 2022","content":"* Requirement - PHP Version 7.1 required for usage of FakerPress, important step to allow further improvements and tests.\n* Feature - Modifications to the internal Modules of FakerPress to enable future work around WP-CLI.\n* Feature - Include a new way to handle Administration Pages, included here to enable future work.\n* Enhancement - Modifications to Select2 Styles for better accessibility usage.\n* Tweak - Faker version dependency updated from `fzaninotto\/Faker` to `fakerphp\/Faker\/`.\n* Tweak - Move everything into the FakerPress namespace.\n* Tweak - Autoload using Composer properly.","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003ERequirement - PHP Version 7.1 required for usage of FakerPress, important step to allow further improvements and tests.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFeature - Modifications to the internal Modules of FakerPress to enable future work around WP-CLI.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFeature - Include a new way to handle Administration Pages, included here to enable future work.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EEnhancement - Modifications to Select2 Styles for better accessibility usage.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak - Faker version dependency updated from \u003Ccode\u003Efzaninotto\/Faker\u003C\/code\u003E to \u003Ccode\u003Efakerphp\/Faker\/\u003C\/code\u003E.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak - Move everything into the FakerPress namespace.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak - Autoload using Composer properly.\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.5.3":{"number":"0.5.3","date":"04 of March 2022","content":"* Fix - Ensure Select2 usage of AJAX search properly encodes on all usages props @TheMMMdev\n* Fix - Ensure Select2 usage of AJAX properly checks for nonces and permissions props @TheMMMdev","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFix - Ensure Select2 usage of AJAX search properly encodes on all usages props @TheMMMdev\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix - Ensure Select2 usage of AJAX properly checks for nonces and permissions props @TheMMMdev\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.5.2":{"number":"0.5.2","date":"27 of January 2022","content":"* Feature: Proper changelog page for users trying to figure out what has changed in the latest version of FakerPress.\n* Feature: Excerpt size field added to ensure better control over Post generation prop @gtsantos\n* Tweak: jQuery 3.5.X compatibility\n* Fix: Prevent warning around post_excerpt usage introduced earlier on in the year. props @pattisahusiwa\n* Fix: One more PHP 8 compatibility problem related to the generation of passwords props @DumahX\n* Fix: Prevent PHP 8 from throwing a warning because of unnecessary `unlink()` call for attachments props @wpuzman\n* Fix: Resolve problems around failed inline images that were unavailable which would cause a PHP Warning.","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Proper changelog page for users trying to figure out what has changed in the latest version of FakerPress.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Excerpt size field added to ensure better control over Post generation prop @gtsantos\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: jQuery 3.5.X compatibility\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Prevent warning around post_excerpt usage introduced earlier on in the year. props @pattisahusiwa\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: One more PHP 8 compatibility problem related to the generation of passwords props @DumahX\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Prevent PHP 8 from throwing a warning because of unnecessary \u003Ccode\u003Eunlink()\u003C\/code\u003E call for attachments props @wpuzman\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Resolve problems around failed inline images that were unavailable which would cause a PHP Warning.\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.5.1":{"number":"0.5.1","date":"05 of January 2021","content":"* Feature: Completely change folder structure to comply with [PSR-4](https:\/\/www.php-fig.org\/psr\/psr-4\/) autoloading with namespaces.\n* Feature: Included a Fields API to more easily generate Fields for controlling fields used in the admin pages.\n* Feature: Included a Template class to allow better separation of HTML and PHP files.\n* Tweak: Include compatibility with PHP 8+\n* Tweak: More well structured `composer.json` and using it\u0027s autoloader.\n* Tweak: All PHP files now live inside of `src` folder.\n* Tweak: Moved all files to use the `[]` array syntax.\n* Tweak: GitHub repository no longer tracks the `vendor` folder.\n* Fix: Correctly order the `fp_array_get` params with it\u0027s usage props @henrikwirth.\n* Fix: Make sure WordPress 5.6+ administration proper display fields.\n* Fix: Menu icon is properly displayed on version of WordPress 5.5 or higher","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Completely change folder structure to comply with \u003Ca href=\u0027https:\/\/www.php-fig.org\/psr\/psr-4\/\u0027\u003EPSR-4\u003C\/a\u003E autoloading with namespaces.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Included a Fields API to more easily generate Fields for controlling fields used in the admin pages.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Included a Template class to allow better separation of HTML and PHP files.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Include compatibility with PHP 8+\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: More well structured \u003Ccode\u003Ecomposer.json\u003C\/code\u003E and using it\u0027s autoloader.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: All PHP files now live inside of \u003Ccode\u003Esrc\u003C\/code\u003E folder.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Moved all files to use the \u003Ccode\u003E[]\u003C\/code\u003E array syntax.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: GitHub repository no longer tracks the \u003Ccode\u003Evendor\u003C\/code\u003E folder.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Correctly order the \u003Ccode\u003Efp_array_get\u003C\/code\u003E params with it\u0027s usage props @henrikwirth.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Make sure WordPress 5.6+ administration proper display fields.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Menu icon is properly displayed on version of WordPress 5.5 or higher\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.5.0":{"number":"0.5.0","date":"09 of November, 2019","content":"* Feature: Removed 500px as a image provider as that source was deprecated a while ago.\n* Fix: Properly handle downloading of images into WordPress, which makes this a lot safer and faster.\n* Fix: Resolved the problem where images from the Meta Attachment were been generated with 0x0 size and breaking the attachment.\n* Fix: Removed 500px as a image provider as that source was deprecated a while ago.\n* Fix: Moved from Unplash.it to their new name as Lorem Picsum.\n* Tweak: Started adding proper docblocks to the new methods. Note it will take a couple versions to have all methods with proper docblocks.\n* Tweak: Control timeout of image download with filter `fakerpress.module.attachment.download_url_timeout` defaulting to 10 seconds per attachment.\n* Tweak: Update required version of PHP to 5.6 to be more along the requirements of WordPress.\n* Tweak: Test and update the version of WordPress FakerPress was tested up to, now on 5.3.\n* Tweak: Updated the version of fzaninotto\/faker (v1.6.0 =\u003E v1.8.0)\n* Tweak: Updated the version of nesbot\/carbon (1.21.0 =\u003E 1.39.1)","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Removed 500px as a image provider as that source was deprecated a while ago.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Properly handle downloading of images into WordPress, which makes this a lot safer and faster.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Resolved the problem where images from the Meta Attachment were been generated with 0x0 size and breaking the attachment.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Removed 500px as a image provider as that source was deprecated a while ago.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Moved from Unplash.it to their new name as Lorem Picsum.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Started adding proper docblocks to the new methods. Note it will take a couple versions to have all methods with proper docblocks.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Control timeout of image download with filter \u003Ccode\u003Efakerpress.module.attachment.download_url_timeout\u003C\/code\u003E defaulting to 10 seconds per attachment.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Update required version of PHP to 5.6 to be more along the requirements of WordPress.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Test and update the version of WordPress FakerPress was tested up to, now on 5.3.\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Updated the version of fzaninotto\/faker (v1.6.0 =\u003E v1.8.0)\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Updated the version of nesbot\/carbon (1.21.0 =\u003E 1.39.1)\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.4.11":{"number":"0.4.11","date":"25 of February, 2018","content":"* Fix: Prevent fatals from oversight with Namespace and Classnames for User and Post generation \u0026mdash; Thanks [@radgh](https:\/\/wordpress.org\/support\/topic\/fakerpress-generate-users-fails\/) \u0026 [@johny-dotpad](https:\/\/wordpress.org\/support\/topic\/post-generation-fails\/)\n* Fix: Rollback change to `file_get_contents` it was making peoples servers hang to long \u0026mdash; Thanks [@bilimokur](https:\/\/wordpress.org\/support\/topic\/most-images-are-corrupted\/)","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Prevent fatals from oversight with Namespace and Classnames for User and Post generation \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/wordpress.org\/support\/topic\/fakerpress-generate-users-fails\/\u0027\u003E@radgh\u003C\/a\u003E \u0026 \u003Ca href=\u0027https:\/\/wordpress.org\/support\/topic\/post-generation-fails\/\u0027\u003E@johny-dotpad\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Rollback change to \u003Ccode\u003Efile_get_contents\u003C\/code\u003E it was making peoples servers hang to long \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/wordpress.org\/support\/topic\/most-images-are-corrupted\/\u0027\u003E@bilimokur\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.4.10":{"number":"0.4.10","date":"11 of February, 2018","content":"* Feature: Added size control to Post Content, Comment Content and User Description\n* Fix: On image and attachment download we try one more time using `file_get_contents` \u0026mdash; Thanks [@lazlo-toth](https:\/\/wordpress.org\/support\/topic\/most-of-the-featured-image-links-appear-to-be-invalid\/)\n* Fix: Resolve Quantity Min and Max fields weird interaction \u0026mdash; Thanks [@rahmantanvir](https:\/\/github.com\/bordoni\/fakerpress\/issues\/124)","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Added size control to Post Content, Comment Content and User Description\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: On image and attachment download we try one more time using \u003Ccode\u003Efile_get_contents\u003C\/code\u003E \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/wordpress.org\/support\/topic\/most-of-the-featured-image-links-appear-to-be-invalid\/\u0027\u003E@lazlo-toth\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Resolve Quantity Min and Max fields weird interaction \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/github.com\/bordoni\/fakerpress\/issues\/124\u0027\u003E@rahmantanvir\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"},"0.4.9":{"number":"0.4.9","date":"07 of August, 2017","content":"* Feature: Allow Attachments to be generated with a given Width and Height range \u0026mdash; Thanks [@COLABORATI](https:\/\/github.com\/bordoni\/fakerpress\/issues\/86)\n* Feature: Generate `post_excerpt` for Posts (how I forgot that is beyond me) \u0026mdash; Thanks [@njbarrett](https:\/\/github.com\/bordoni\/fakerpress\/issues\/104)\n* Fix: Meta field rules were not respecting Configurations due to a JavaScript bug on indexing the fields \u0026mdash; Thanks [@ckpicker](https:\/\/github.com\/bordoni\/fakerpress\/issues\/115)\n* Fix: Resolve problems where attachments wouldn\u0027t get setup correctly and throw an Empty Message \u0026mdash; Thanks [@r083r7 and @oyvind_skjelstad](https:\/\/wordpress.org\/support\/topic\/featured-image-not-showing-up-6)\n* Fix: Allow Term Meta to generate the fields correctly again \u0026mdash; Thanks [@stratboy](https:\/\/github.com\/bordoni\/fakerpress\/issues\/105)\n* Tweak: Prevent `_encloseme` and `_pingme` for FakerPress generated Posts","html":"\u003Cul\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Allow Attachments to be generated with a given Width and Height range \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/github.com\/bordoni\/fakerpress\/issues\/86\u0027\u003E@COLABORATI\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFeature: Generate \u003Ccode\u003Epost_excerpt\u003C\/code\u003E for Posts (how I forgot that is beyond me) \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/github.com\/bordoni\/fakerpress\/issues\/104\u0027\u003E@njbarrett\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Meta field rules were not respecting Configurations due to a JavaScript bug on indexing the fields \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/github.com\/bordoni\/fakerpress\/issues\/115\u0027\u003E@ckpicker\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Resolve problems where attachments wouldn\u0027t get setup correctly and throw an Empty Message \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/wordpress.org\/support\/topic\/featured-image-not-showing-up-6\u0027\u003E@r083r7 and @oyvind_skjelstad\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003EFix: Allow Term Meta to generate the fields correctly again \u0026mdash; Thanks \u003Ca href=\u0027https:\/\/github.com\/bordoni\/fakerpress\/issues\/105\u0027\u003E@stratboy\u003C\/a\u003E\u003C\/p\u003E\u003C\/li\u003E\n\u003Cli\u003E\u003Cp\u003ETweak: Prevent \u003Ccode\u003E_encloseme\u003C\/code\u003E and \u003Ccode\u003E_pingme\u003C\/code\u003E for FakerPress generated Posts\u003C\/p\u003E\u003C\/li\u003E\n\u003C\/ul\u003E"}}}}' );
