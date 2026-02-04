# Easy FAQ and HowTo Schema

A WordPress plugin that adds FAQ and HowTo structured data to your posts and pages via metabox, shortcode, and Yoast SEO integration.

## Description

This plugin provides a simple way to add structured data (schema.org) for FAQs and HowTo guides to your WordPress posts and pages. It seamlessly integrates with Yoast SEO to inject the appropriate JSON-LD schema markup into your pages.

### Features

- ✅ **Metabox Interface**: Easy-to-use metabox in the post editor for adding FAQ and HowTo content
- ✅ **Post Meta Storage**: Stores your FAQ/HowTo data as post meta (source of truth)
- ✅ **Shortcode Display**: Renders visible FAQ/HowTo content via shortcodes `[easy_faq]` and `[easy_howto]`
- ✅ **Yoast SEO Integration**: Automatically injects JSON-LD graph pieces into Yoast SEO's schema output
- ✅ **Schema.org Compliant**: Follows schema.org specifications for FAQPage and HowTo
- ✅ **Clean UI**: Intuitive admin interface with add/remove functionality
- ✅ **Responsive Design**: Mobile-friendly frontend display

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Yoast SEO plugin (for schema integration)

## Installation

1. Download the plugin files and upload the `easy-faq-howto-schema` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure Yoast SEO is installed and activated for schema integration

## Usage

### Adding FAQ Content

1. Edit any post or page
2. Scroll down to the "FAQ Schema" metabox
3. Click "Add FAQ Item" to add question/answer pairs
4. Fill in your questions and answers
5. Add the shortcode `[easy_faq]` anywhere in your post content where you want the FAQs to appear
6. Save/update your post

The FAQ content will be displayed on the frontend, and the structured data will automatically be added to Yoast SEO's schema output.

### Adding HowTo Content

1. Edit any post or page
2. Scroll down to the "HowTo Schema" metabox
3. Fill in the HowTo title, description, and total time (optional)
4. Click "Add Step" to add individual steps
5. Fill in each step's name and instructions
6. Add the shortcode `[easy_howto]` anywhere in your post content where you want the guide to appear
7. Save/update your post

The HowTo guide will be displayed on the frontend with numbered steps, and the structured data will automatically be added to Yoast SEO's schema output.

### Shortcodes

- `[easy_faq]` - Displays the FAQ content for the current post
- `[easy_howto]` - Displays the HowTo guide for the current post

### Total Time Format

For HowTo guides, use ISO 8601 duration format for the total time:
- `PT30M` = 30 minutes
- `PT1H` = 1 hour
- `PT1H30M` = 1 hour 30 minutes
- `PT2H` = 2 hours

## How It Works

### 1. Data Storage
All FAQ and HowTo data is stored as post meta in your WordPress database:
- FAQ data: `_easy_faq_data` meta key
- HowTo data: `_easy_howto_data` meta key

### 2. Frontend Display
When you use the shortcodes, the plugin renders the content with appropriate HTML markup and microdata attributes:
- FAQs are displayed as question/answer pairs
- HowTo guides are displayed with numbered steps and metadata

### 3. Schema Integration
The plugin hooks into Yoast SEO's schema graph using the following filters:
- `wpseo_schema_graph_pieces` - Adds custom schema pieces
- `wpseo_schema_graph` - Adds schema directly to the graph

The plugin implements the `WPSEO_Graph_Piece` interface to properly integrate with Yoast SEO's schema system, just like the native Yoast blocks.

## Customization

### Supported Post Types

By default, the metaboxes appear on posts and pages. You can customize this using the filter:

```php
add_filter( 'easy_faq_howto_post_types', function( $post_types ) {
    $post_types[] = 'custom_post_type';
    return $post_types;
} );
```

### Styling

The plugin includes basic styling for both admin and frontend. You can override these styles in your theme:
- Admin styles: `assets/css/admin.css`
- Frontend styles: `assets/css/frontend.css`

## File Structure

```
easy-faq-howto-schema/
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin metabox styles
│   │   └── frontend.css       # Frontend display styles
│   └── js/
│       └── admin.js           # Admin metabox functionality
├── includes/
│   ├── class-metabox.php      # Metabox registration and rendering
│   ├── class-shortcodes.php   # Shortcode handlers
│   └── class-yoast-integration.php  # Yoast SEO schema integration
├── easy-faq-howto-schema.php  # Main plugin file
└── README.md                  # This file
```

## Frequently Asked Questions

### Does this work without Yoast SEO?

The metabox and shortcodes will work, but the structured data won't be added to your pages without Yoast SEO. The plugin is designed to integrate with Yoast SEO's schema system.

### Can I use both FAQ and HowTo on the same page?

Yes! You can add both FAQ and HowTo content to the same post/page. Just use both shortcodes where appropriate.

### Does the schema validation pass Google's Rich Results Test?

Yes, the plugin generates schema.org-compliant structured data that should pass Google's validation tools.

### Can I reorder FAQ items or HowTo steps?

Yes, if you have jQuery UI Sortable available (included in WordPress by default), you can drag and drop items to reorder them.

## Support

For issues, questions, or contributions, please visit the plugin repository.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed to provide an easy way to add FAQ and HowTo structured data that integrates seamlessly with Yoast SEO, following the same patterns used by Yoast's native blocks.

## Changelog

### 1.0.0
- Initial release
- FAQ metabox and shortcode
- HowTo metabox and shortcode
- Yoast SEO schema integration
- Admin and frontend styling
