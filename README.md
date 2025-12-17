# FormAssembly User Prefill

Automatically pass logged-in WordPress user data as URL parameters to embedded FormAssembly forms.

## Description

FormAssembly User Prefill is a lightweight WordPress plugin that bridges the gap between your WordPress user accounts and FormAssembly forms. When users are logged into your WordPress site, this plugin automatically appends their information as URL parameters to any embedded FormAssembly forms, enabling seamless data prefilling and Salesforce lookups.

### Key Features

- 🔐 **Automatic User Detection** - Only adds parameters when users are logged in
- ⚙️ **Flexible Configuration** - Map any WordPress user field to any URL parameter
- 🎯 **Zero Code Required** - Simple settings interface for configuration
- 🔄 **Works with Multiple Embed Methods** - Supports both FormAssembly plugin shortcodes and manual iframe embeds
- 📊 **Salesforce Integration Ready** - Perfect for passing user IDs for Salesforce lookups

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- **One of the following:**
  - [FormAssembly WordPress Plugin](https://wordpress.org/plugins/formassembly-web-forms/) installed (if using shortcodes)
  - OR manually embedded FormAssembly iframes in your content

## Installation

### Method 1: Upload via WordPress Admin

1. Download the latest release ZIP file from the [Releases page](https://github.com/tommy2mx/WPUser-to-FormAssembly-prefill/releases)
2. Go to **Plugins → Add New** in your WordPress admin
3. Click **Upload Plugin** at the top
4. Choose the ZIP file and click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual Installation

1. Download and extract the plugin files
2. Upload the `formassembly-user-prefill` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

## Configuration

1. Go to **Settings → FA User Prefill** in your WordPress admin
2. Add parameter mappings, one per line, in this format:
   ```
   parameter_name=user_field
   ```

### Available User Fields

- `ID` - WordPress user ID
- `user_email` - User's email address
- `user_login` - Username
- `display_name` - Display name
- `first_name` - First name
- `last_name` - Last name
- Any custom user meta field key

### Example Configurations

**Pass user ID for Salesforce lookup:**
```
sfid=ID
```

**Pass email address:**
```
email=user_email
```

**Pass multiple fields:**
```
email=user_email
userid=ID
firstname=first_name
lastname=last_name
```

## Usage

### With FormAssembly Plugin (Shortcode Method)

If you have the FormAssembly WordPress plugin installed, simply use their shortcode as normal:

```
[formassembly formid=123456 iframe=1]
```

The plugin will automatically detect the generated iframe and append your configured parameters.

### With Manual iframe Embed

If you're manually embedding FormAssembly forms with iframes:

```html
<iframe src="https://yourInstance.tfaforms.net/123456" width="100%" height="600"></iframe>
```

The plugin will automatically append parameters to any iframe containing `tfaforms` or `formassembly` in the URL.

## How It Works

When a logged-in user views a page with a FormAssembly form:

**Before:**
```html
<iframe src="https://example.tfaforms.net/123"></iframe>
```

**After (automatically modified):**
```html
<iframe src="https://example.tfaforms.net/123?email=user@example.com&sfid=42"></iframe>
```

Non-logged-in users see the form without parameters, ensuring your forms still work for anonymous visitors.

## Use Cases

- **Salesforce Contact Lookup** - Pass WordPress user ID to lookup Salesforce contacts
- **Pre-filled Forms** - Improve user experience by pre-filling name, email, etc.
- **User Tracking** - Track form submissions by WordPress user ID
- **Membership Sites** - Integrate member data with FormAssembly forms
- **Customer Portals** - Pre-populate customer information in support forms

## Frequently Asked Questions

### Do I need the FormAssembly WordPress plugin?

No, but it's recommended. This plugin works with:
- FormAssembly plugin shortcodes that generate iframes
- Manually embedded FormAssembly iframes

### What happens if a user isn't logged in?

The form displays normally without any parameters appended. This ensures your forms work for all visitors.

### Can I pass custom user meta fields?

Yes! Use any custom user meta field key as the value. For example, if you have a custom field called `salesforce_id`, you can use:
```
sfid=salesforce_id
```

### Will this work with other form plugins?

This plugin is specifically designed for FormAssembly forms. It looks for iframes containing `tfaforms` or `formassembly` in the URL.

### Does this slow down my site?

No. The plugin only processes content that contains FormAssembly iframes, and the processing is minimal.

## Support

- **Issues:** Report bugs or request features on [GitHub Issues](https://github.com/tommy2mx/WPUser-to-FormAssembly-prefill/issues)
- **Documentation:** Check the [Wiki](https://github.com/tommy2mx/WPUser-to-FormAssembly-prefill/wiki) for detailed guides

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL-2.0+ License. See the [LICENSE](LICENSE) file for details.

## Credits

Developed by [Tom G](https://github.com/Tommy2Mx)

## Changelog

### 1.0.0 - 2024-12-16
- Initial release
- Support for WordPress user field mapping
- Support for FormAssembly plugin shortcodes
- Support for manual iframe embeds
- Settings page for easy configuration
