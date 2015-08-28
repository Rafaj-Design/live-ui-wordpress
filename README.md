LiveUI WordPress plugin
================

## Instalation instruction

1. Download the module
2. Move files into a ./wp-content/plugins/liveui
3. Activate the plugin in your WordPress admin panel
4. Go to LiveUI settings in the admin panel and put in your app API key

All keys below are used without the hash symbol (so #MY_IMAGE in teh admin panel will be only MY_IMAGE in WP)

<!-- INFO -->
## Translations

To return a translated string you can use method LUI(key, locale), method returns a string with the translation or the key if translation is missing 
Example:  
```html
<h1><?php echo LUI('MyWebHeader', 'en_US'); ?></h1>
```

To get available locales in an array ( `{ 'en_US', 'it', 'de' }` )
```php
liveui::get_available_locales();
```

## Images

To get an image URL, you can call `LUIImage(key, locale)`. This method returns a local link to the file.
If the file is not present in the local cache, it will be downloaded from the LiveUI system and cached locally so
the local server is responsible for the distribution.  
Example:  
```html
<img src="<?php echo LUIImage('MyLogoImage', 'it'); ?> alt="Company Logo (Italian)" />
```

## Colors

To get a color you can use `LUIColor(key)` method. This will return the color HEX representation (Ex. FF0000). The value will be returned without the hash (#) character.  
Example:  
```html
<span style="color: #<?php echo LUIColor('MyHeaderColor'); ?>;">My text with remotely controlled color</span>
```  

Some colors can be set to use an alpha value, in that case you can check for `LUIColorAlpha(key)` which returns a value between 0-100 which represent teh opacity. For example value 80 means the color should be displayed with 80% opacity.

For any technical questions please use StackOverflow with a tag "liveui" for any issues , please create a github.com issue.

<!-- INFOEND -->
