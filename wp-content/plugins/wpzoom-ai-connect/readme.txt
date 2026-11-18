=== WPZOOM AI Connect – MCP Connector for Claude & ChatGPT ===
Contributors: wpzoom
Tags: ai, mcp, claude, chatgpt, connector
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.12.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect Claude or ChatGPT to WordPress over MCP and edit your site by chat: pages, theme settings, design sections and more.

== Description ==

Talk to your WordPress site in plain language. WPZOOM AI Connect links your site to Claude, ChatGPT or any other MCP app, so you ask for changes instead of clicking through the admin: switch the color palette, change the header layout, rewrite the About page, add a team section, create a contact form, or run a checkup. Your assistant reads what the site has, proposes the change, and makes it after you confirm.

https://www.youtube.com/watch?v=agnOSWSS6yo

**Try asking**

* "Show me an overview of my site and what you can change."
* "Switch my site to a darker color palette."
* "Rewrite my About page so it sounds clearer. Show me the new text first."
* "Create a Services page with a hero, three services and a pricing section, as a draft."
* "Run a checkup of my site."

**Made for site owners, built to be safe**

New pages start as drafts, changes to settings and content are marked so your AI asks before making them, nothing is ever deleted, and changes made through the WPZOOM connector can be undone for 30 days. The connection is an application password you approve on your own site and can revoke any time, and the plugin limits what that connection may do even for administrators: it cannot install or remove plugins and themes, manage users, change WordPress settings or add code (see the FAQ).

**How it works**

The plugin registers **WordPress abilities** (the Abilities API in WordPress 6.9+) that any MCP client can discover: the WPZOOM connector, WPVibe, Easy MCP AI, the official MCP Adapter and the WordPress AI client. Instead of guessing option names, the assistant sees your theme the way the Customizer does: every setting with its label, choices and current value. Instead of writing HTML, it inserts the design sections your theme ships. Everything runs under the permissions of the WordPress user it connects as.

**What runs where**

Everything in the list below is code in this plugin, running on your site. Plain WordPress work, such as writing pages and posts, uploading images and editing menus, is done by the WPZOOM connector (mcp.wpzoom.com) through WordPress's own REST API, as the connected user; there is no code for it in this plugin, and other MCP clients do it through their own tools. The plugin is useful on its own: its abilities work with any MCP client installed on the site, and it limits what the WPZOOM connection may do, even for administrators (see the FAQ).

**What the assistant can do**

* Describe the site: theme, palette, header layout, content, WPZOOM plugins.
* List and change theme settings: colors, global palette, header and footer layout, fonts, slider, and the rest of the Customizer.
* List and change the options in WPZOOM → Theme Options on older WPZOOM themes (Tribune, Indigo, Foodica, Academica and more): homepage sections, featured categories, banners.
* Read and add Additional CSS for small visual changes no setting covers (a hover color, spacing, hiding an element). Each change is a labelled block it can replace or remove, CSS you wrote is never touched, WordPress keeps a revision, and you can switch this off on the AI Connect screen.
* List the theme's design sections (hero, services, pricing, FAQ, team, testimonials, contact…) and add them to a page, or create a draft page from one.
* Add ready-made design sections to any page on any theme: 100+ free sections from the WPZOOM catalog (hero, about, services, pricing, team, FAQ, testimonials, stats, contact, call to action…), more with an Inspiro Premium license. They take your site's colors and buttons and your own photos, and a new page starts as a draft.
* Edit pages built with Elementor: read a page's widgets (Elementor, Elementor Pro and WPZOOM Elementor Addons) and change their texts, links, images and lists, such as team members or pricing features, saved through Elementor with a revision.
* Clear the page cache after every change made through the WPZOOM connector (WP Rocket, LiteSpeed Cache, W3 Total Cache, WP Super Cache, SiteGround, Breeze, Kinsta, WP Engine and others), so visitors see it at once.
* Browse the starter sites of Inspiro Starter Sites (you import the one you pick in WordPress admin), then adapt the imported pages to your business; build new draft pages with AI-written copy.
* Create portfolio items, forms and recipe cards with the WPZOOM plugins you use, and find, read and edit existing recipe cards (details, ingredients, steps, find-and-replace).
* Read and set the Yoast SEO or Rank Math title, description, focus keyword and noindex flag of a post or page.
* Check a page for common accessibility problems (missing alt text, empty links, skipped headings, unlabelled fields, missing page language, untitled embeds, low contrast in the theme's colors) and fix them with the tools above.
* Change the site title and tagline. The other Settings stay read-only for the connection.
* Check the license and run theme-aware diagnostics, including outdated themes and plugins and an old PHP version.
* List available theme and plugin updates and what blocks one (a missing license, PHP or WordPress too old). Updates are installed by you, in Dashboard → Updates.

**Connect your AI**

1. Install and activate the plugin.
2. In Claude or ChatGPT, add the WPZOOM connector and sign in.
3. Ask it to pair your site. WordPress asks you to approve the connection; it is stored as an application password you can revoke any time.

Already using WPVibe or Easy MCP AI? The abilities show up there too, no extra setup.

**Themes and plugins**

Theme settings work with WPZOOM themes built on the WPZOOM framework (Inspiro Premium, Foodica and others). Design sections come from the active theme's patterns and from the WPZOOM section catalog, which works on any theme once the site is connected to WPZOOM AI with an email (free) or an Inspiro Premium license. Starter sites and AI-written pages need Inspiro Starter Sites. Content abilities need WPZOOM Portfolio, WPZOOM Forms or Recipe Card Blocks. Elementor page editing needs Elementor; it covers the classic widgets that WPZOOM demos use, while Elementor 4's atomic elements are left to Elementor's own abilities. The SEO abilities need Yoast SEO or Rank Math; with both active, Yoast SEO's fields are used.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it from the Plugins screen.
2. Activate it. There are no settings; Settings → WPZOOM AI Connect shows the status and the connector URL.
3. Application passwords must be available (your site needs HTTPS).

== Frequently Asked Questions ==

= Does the AI get full access to my site? =

No. The connection is an application password named "WPZOOM AI Connect" for the WordPress user who approved it, and this plugin gives requests made with it a reduced copy of that user's permissions. Through it the AI can work on content (posts, pages, media, menus), the theme's design settings and the abilities listed on the status page. It can never install, update, activate or delete plugins and themes, switch themes, create, edit or delete users, add or remove application passwords, change the Settings screens, edit theme or plugin files, save unfiltered HTML or scripts or use XML-RPC, and it reaches only WordPress core's REST API, the Abilities API and oEmbed. The one kind of code it may add is Additional CSS, under WordPress's own edit_css permission, and you can switch that off on the AI Connect screen. Writes default to drafts where possible, and nothing is deleted. Your own sessions and your other application passwords are not affected. Disconnect any time from the AI Connect screen or under Users → Profile → Application Passwords.

= Can the AI install, update or remove plugins and themes? =

No. The plugin has no ability that installs, updates, activates, deactivates or deletes plugins or themes, imports demo content, manages users or saves JavaScript or PHP, and the connection's permissions exclude all of that even when the connected user is an administrator. The AI can list which updates are available and which starter sites fit; you install updates in Dashboard → Updates and import a starter site in Inspiro Starter Sites.

= Does this send my content to WPZOOM? =

The abilities run on your site. AI features that generate content (demo generation, page building) call ai.wpzoom.com, the same service the WPZOOM themes already use.

= What does the WPZOOM connector keep? =

Your sign-in email, the address of each site you connect, its application password (encrypted), and the changes the AI made (30 days, for undo). To find and fix problems it also logs each request, without its content: the time, the site, the AI app, the tool used, whether it worked and the error if not, plus the plugin, WordPress and PHP versions and the active theme. These logs are kept for 90 days.

= Which AI assistants work? =

Any MCP client: Claude (web, desktop, Claude Code), ChatGPT, Cursor and others, through the WPZOOM connector or another MCP plugin on your site.

== External services ==

This plugin works with two WPZOOM services. Nothing is sent to either until you use the feature that needs it.

* **WPZOOM connector** (mcp.wpzoom.com): the address you add in Claude, ChatGPT or another MCP app. When you press "Connect this site", WordPress asks you to approve an application password for your user, and it is sent to the connector, which stores it encrypted and uses it to call this site's abilities when your AI asks. Connecting lets the connector act on this site as your user, within the limits described in the FAQ, until you disconnect. With each request it tells this site which WPZOOM account (sign-in email) the connection belongs to; the plugin saves it with your user and shows it on its screen, so you know which email to use in your AI app. The connector keeps your sign-in email, each connected site's address, the changes the AI made (30 days) and a request log without content (90 days).
* **WPZOOM AI** (ai.wpzoom.com): AI page building and the free AI registration (your email and a 6-digit code) run through the Inspiro Starter Sites plugin's AI client, only when you ask for them. This plugin itself sends nothing there.

Terms: https://www.wpzoom.com/terms-and-conditions/ · Privacy policy: https://www.wpzoom.com/privacy-policy/

== Changelog ==

= 0.12.5 =
* Changed: the plugin is now listed as "WPZOOM AI Connect – MCP Connector for Claude & ChatGPT", so it is easier to find when you search for MCP. Nothing changes on your site or in your connection.

= 0.12.4 =
* Improved: the AI Connect screen shows which WPZOOM account (email) your site is connected to, and tells you to sign in with that email when you add WPZOOM to Claude or ChatGPT. It doesn't have to match your Claude or ChatGPT login.

= 0.12.3 =
* Improved: every message the plugin shows or sends to your AI app can now be translated: the site check, theme settings, accessibility check, updates list, recipes, SEO, Elementor, forms and starter-site messages, plus the names and descriptions of what the AI can do.

= 0.12.2 =
* Improved: after you connect your site, the AI Connect screen says whether an AI has used it yet. Until one does, it shows the one remaining step: adding WPZOOM to Claude, ChatGPT or another AI app.
* Fixed: installing the plugin from another plugin's or a theme's setup screen (such as Inspiro Starter Sites) no longer jumps to the AI Connect welcome screen in the middle of that setup.
* Fixed: "Clear the page cache" and "Change Theme Options" were always refused through the connection, because they check the Settings permission, which the connection only has for reading. They work now; Settings themselves stay read-only.

= 0.12.1 =
* Security: WooCommerce's own AI abilities (query orders, update an order's status, add order notes, create, update and delete products) now follow your choice on the AI Connect screen. Before, they answered the connection even while orders were closed, because they check permissions in their own way. Orders stay closed unless you open them; Orders can now be set on the AI Connect screen too.
* Changed: WooCommerce products are readable through the connection by default (they are the public catalog), so an AI can list, search and discuss them right away; editing them still needs your choice on the AI Connect screen, and orders, coupons and customer data stay closed. Close products there if you prefer.

= 0.12.0 =
* New: the WPZOOM section catalog works on every theme. wpzoom/list-sections and wpzoom/insert-sections no longer need the Inspiro Starter Sites plugin: the plugin fetches the catalog from ai.wpzoom.com itself and renders the sections, with the site's colors (from theme.json on block themes), the theme's own buttons on block themes, photos from the site's Media Library and a neutral placeholder image when it has none (named in the result, so your AI asks you for photos). Premium sections still need an Inspiro Premium license; sections that need a plugin or published posts say so.
* New: wpzoom/connect-ai, wpzoom/verify-ai-code and wpzoom/ai-status work without Inspiro Starter Sites too (the connection is shared with it). The first catalog request on an unconnected site answers with the next step instead of an error.
* Changed: with Inspiro Starter Sites active, the catalog abilities keep using its code, cache and stylesheet.
* New: check a published page for common accessibility problems: missing alt text, empty or unclear links, skipped headings, unlabelled fields, missing page language, untitled embeds, and low contrast in the theme's own color settings, in colors set on blocks and in Elementor widgets. Each finding says where it is, how to fix it and which tool makes the change; the check itself changes nothing. An automated check finds common problems; it does not cover everything a person with a screen reader or keyboard would meet.
* Improved: theme settings on GeneratePress, Astra, Kadence, Blocksy, OceanWP, Neve, Hello Elementor, Twenty Twenty-Five and other themes: range sliders (logo width, sizes) are described as numbers with their limits, and a theme's cached stylesheet is refreshed after a change, so the front end shows it at once. The README lists what works on each theme.
* Fixed: theme settings whose ids contain brackets (GeneratePress, Astra, OceanWP) can now be changed through the connection; the abilities routed as DELETE read their input from the request body as well as the URL.

= 0.11.0 =
* New: change the site title and tagline through the connection (the AI asks for it often). The other Settings screens stay read-only for the connection, as before.

= 0.10.0 =
* New: read and set the Yoast SEO or Rank Math title, meta description, focus keyword and noindex flag of a post or page. Template variables are kept, titles over 60 characters and descriptions over 160 are flagged, and fields that were not passed are left alone. Canonical URLs, social previews and schema are not covered.

= 0.9.0 =
* New: choose on the AI Connect screen what your AI may do with each custom post type: no access, read, or read and edit. WordPress enforces your choice for the WPZOOM AI Connect connection. Posts and pages are not affected.
* Changed: custom post types are now closed to the connection until you open them, because some hold private data such as form entries or orders. WPZOOM's own types (portfolio, slideshow, recipes, forms and Elementor templates) stay open unless you close them. If your AI edits other custom post types built with Elementor, open them on the AI Connect screen.

= 0.8.0 =
* New: read and add Additional CSS (Customizer, or Site Editor styles on block themes). The AI adds small changes as labelled blocks it can replace or remove; CSS you wrote is not touched and WordPress keeps a revision of every change.
* CSS the Customizer would reject is refused, as are markup and @import. Writing needs WordPress's edit_css permission (administrators on a single site; nobody when DISALLOW_UNFILTERED_HTML is set).
* New: a switch on the AI Connect screen to stop the WPZOOM AI Connect connection changing Additional CSS. It is on by default.

= 0.7.3 =
* Security: requests made with the WPZOOM AI Connect application password now get a reduced copy of the user's permissions. They cannot install, update, activate or delete plugins and themes, manage users or application passwords, change the Settings screens, edit files or save unfiltered HTML, and they reach only WordPress core's REST API, the Abilities API and oEmbed (no XML-RPC). Your own sessions and other application passwords are unchanged.
* The readme now says which work is done by this plugin and which by the WPZOOM connector through WordPress's own REST API.

= 0.7.2 =
* Changed: the AI no longer imports starter sites. It lists them and links to the Inspiro Starter Sites screen, where you import the one you pick; then it can adapt the pages.
* Security: text, textarea and rich-text fields written to Elementor pages keep only the HTML a post may contain (no scripts, event handlers or iframes).
* Security: block markup sent for a portfolio item or recipe is filtered like post content.
* Security: creating a portfolio category also needs the taxonomy's manage_terms capability.

= 0.7.1 =
* Fixed: a missing translator comment, and notes on the cache plugins' own hooks the plugin calls (Plugin Check).

= 0.7.0 =
* Changed: the AI no longer updates themes or plugins. It can still list available updates and what blocks one; you install them in Dashboard → Updates.
* Removed: adding or reading Additional CSS. Change the look with theme settings, or edit Additional CSS in the Customizer or Site Editor yourself. CSS added earlier stays where it is.
* Security: theme settings and WPZOOM Theme Options that hold code (header and footer scripts, ad codes, custom CSS) are no longer listed or changed.
* Security: creating portfolio items, forms and recipes checks the capabilities of their own post types, including permission to publish and to create categories.
* Security: reading or editing a recipe or an Elementor page checks permission to edit that post.
* Security: available updates list themes only for users who can update themes, and plugins only for users who can update plugins.
* The status screen escapes all of its output when it is shown.

= 0.6.2 =
* Security: importing a starter site no longer installs or activates plugins. Plugins a demo needs that are not active are listed, so you can install them from the dashboard first.
* Security: a background job's status is shown only to the user who started it, or to an administrator.
* Security: the WPZOOM AI status (connected email, remaining generations) is limited to administrators.
* Security: creating a portfolio item or recipe with an image from a URL also needs permission to upload files.

= 0.6.1 =
* Responses to the WPZOOM connector name the plugin, WordPress and PHP versions, so errors can be traced to a version.

= 0.6.0 =
* New: edit pages built with Elementor. List Elementor pages and templates, read a page as a list of widgets with their content fields, and change texts, links, images, galleries and lists (team members, pricing features, slides). Saved through Elementor with a revision; the previous values are returned so the change can be undone.
* New: the page cache is cleared after each change made through the WPZOOM connector, and a "clear cache" ability (optionally regenerating Elementor CSS) is available when a change does not show. Turn the automatic clearing off with the wpzoom_ai_connect_clear_cache filter.

= 0.5.1 =
* WPZOOM themes with built-in AI abilities (theme settings, Theme Options) now provide those themselves; the plugin keeps its own copies for themes that don't.

= 0.5.0 =
* New: list available theme and plugin updates, with the reason when one cannot be installed (missing WPZOOM license, PHP or WordPress too old).
* New: update one theme or plugin. Active plugins are reactivated, the homepage is checked, and the previous version is restored if the update broke the site.
* The checkup flags updates for the active theme and plugins, and PHP versions without security support.

= 0.4.1 =
* Copies downloaded from wpzoom.com now receive updates in Dashboard → Updates until the plugin is available on WordPress.org.

= 0.4.0 =
* New: read and add Additional CSS in labelled changes that can be replaced or removed (classic and block themes).
* New: list and change WPZOOM → Theme Options on older WPZOOM themes.
* New: list, read and edit existing Recipe Card Blocks recipes.

= 0.3.5 =
* On Inspiro Premium, the AI's list-demos and import-demo tools no longer offer the Inspiro Starter Sites demos; they point the user to the theme's Demo Importer.

= 0.3.4 =
* With Inspiro Premium active, the welcome screen points at the theme's own Demo Importer instead of the Inspiro Starter Sites demos, which that plugin hides on Premium.

= 0.3.3 =
* The welcome screen's "Browse starter sites" button opened a broken address; it now opens the Import Demo page.

= 0.3.2 =
* The welcome screen's ChatGPT guide now says what is true: adding WPZOOM as a custom MCP server works on every paid ChatGPT plan, Plus included, not only Business and Enterprise.

= 0.3.1 =
* Theme-setting updates and demo imports are marked destructive in their ability annotations, so AI clients confirm before running them.
* The starter-sites list no longer carries a purchase link.

= 0.3.0 =
* New welcome screen (AI Connect in the admin menu): pick your AI, connect this site in one click, prompts to try, what your AI can do here, checkups worth fixing.
* Connections are shown and can be disconnected from the page.

= 0.2.0 =
* Starter sites, design-section catalog, portfolio, forms, recipes, license status and diagnostics.
* Settings → WPZOOM AI Connect status page.
* Fallback for hosts that strip the Authorization header.

= 0.1.0 =
* First release: site overview, theme settings, patterns.
