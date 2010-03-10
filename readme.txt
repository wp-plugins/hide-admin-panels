=== Hide Admin Panels ===
Contributors: businessxpand
Tags: administration, admin, user, users
Requires at least: 2.7.1
Tested up to: 2.9.2
Stable tag: 0.9.8.2

Allows you to hide admin menus from specific users.

== Description ==

Scans your WordPress admin sidebar for menu items and gives you option to hide them from a specific user. This will only work on WordPress versions greater than 2.7.1 as it relies on the newer admin look.

Any of the following can be hidden: posts, media, links, pages, comments, appearance, dashboard, plugins, users, tools, settings, and as of version 0.9.5 can now hide third party plugins.

The plugin is also compatible with [Ozh' Admin Drop Down Menu](http://wordpress.org/extend/plugins/ozh-admin-drop-down-menu/ "Ozh' Admin Drop Down Menu") (v3.3.1) plugin. Please ensure that Ozh' Admin Drop Down Menu plugin is activated before hiding menu options.

Please note that this is only an aestheic effect, if your user has something installed in their browser that allows them to change the CSS of the page they will be able to unhide the menu item. This also does not prevent them from accessing the hidden menu sections directly by web address.

**Bug fixes for version 0.9.8.2**

* Some installations generating an error with wp_get_current_user(), so switched to using the $current_user global variable.

**New features/bug fixes for version 0.9.8.1**

* Custom table prefix now supported for the roles, thanks to Marc.

**New features/bug fixes for version 0.9.8**

* Added ability to hide admin panels bases on user roles.
* Now compatible with [Ozh' Admin Drop Down Menu](http://wordpress.org/extend/plugins/ozh-admin-drop-down-menu/ "Ozh' Admin Drop Down Menu") (v3.3.1) plugin.
* Capable of hiding third party plugin menu options.
* Does not allow the disabling of the setting menu for the currently logged in user.
* User friendly panel display, now shows the menu name rather than it's ID.

== Installation ==

1. If you are using the [Ozh' Admin Drop Down Menu](http://wordpress.org/extend/plugins/ozh-admin-drop-down-menu/ "Ozh' Admin Drop Down Menu") plugin, please ensure you unhide any hidden panels before uploading.
1. Upload the `hide-admin-panels` directory to your `/wp-content/plugins/` directory.
1. Activate the plugin through the `Plugins` menu in WordPress.
1. Navigate to new `Admin Panels` menu item under the `Settings` menu.
1. Select the user you wish to change.
1. Select the menu options you wish to hide.
1. Save the changes.
