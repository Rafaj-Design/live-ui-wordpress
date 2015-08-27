<div>
	<h2><?php _e('LiveUI Options', 'liveui') ?></h2>
	
	<form method="post" action="options.php">
		<h3 class="title"><?php _e('Settings', 'liveui') ?></h3>
		<p><?php _e('LIVEUI_SETTINGS_INFO_MESSAGE', 'liveui') ?></p>
		<?php wp_nonce_field('update-options'); ?>
		<?php settings_fields('liveui_settings'); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="liveui_translation_api_key"><?php _e('LiveUI Translation API key', 'liveui') ?></label></th>
					<td>
						<input name="liveui_translation_api_key" type="text" id="liveui_translation_api_key" class="regular-text" placeholder="XXXX-XXXXXX-XXXXX-XXXX-XXXXXX" value="<?php echo get_option('liveui_translation_api_key'); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="liveui_image_temp_folder"><?php _e('Temp folder for images', 'liveui') ?></label></th>
					<td>
						<input name="liveui_image_temp_folder" type="text" id="liveui_image_temp_folder" class="regular-text" placeholder="wp-content/images/" value="<?php echo get_option('liveui_image_temp_folder'); ?>" /><br />
						<small<?php echo $tempImageFolderWritable ? '' : ' style="color:red;"'; ?>><?php echo ($tempImageFolderWritable ? '' : __('Folder is NOT writable or doesn\'t exist.', 'liveui')); ?></small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="liveui_data_cache_expiry_time"><?php _e('Cache expiry', 'liveui') ?></label></th>
					<td>
						<input name="liveui_data_cache_expiry_time" type="text" id="liveui_data_cache_expiry_time" class="regular-text" placeholder="180" value="<?php echo get_option('liveui_data_cache_expiry_time'); ?>" />
						<small><?php _e('in minutes', 'liveui'); ?></small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="liveui_debugging"><?php _e('Debugging', 'liveui') ?></label></th>
					<td>
						<input name="liveui_debugging" type="checkbox" id="liveui_debugging" value="1"<?php echo ((bool)get_option('liveui_debugging') ? ' checked="checked"' : ''); ?> />
						<small><?php _e('Enable missing translations reporting', 'liveui') ?></small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="liveui_debugging_text_with_underscores"><?php _e('Replace translated text with underscores', 'liveui') ?></label></th>
					<td>
						<input name="liveui_debugging_text_with_underscores" type="checkbox" id="liveui_debugging_text_with_underscores" value="1"<?php echo ((bool)get_option('liveui_debugging_text_with_underscores') ? ' checked="checked"' : ''); ?> />
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="update" />
		<p>
			<input type="submit" value="<?php _e('Save Changes', 'liveui') ?>" />
		</p>
	</form>
	<hr />
	<form method="post" action="">
		<h3 class="title"><?php _e('Actions', 'liveui') ?></h3>
		<?php wp_nonce_field('other-options'); ?>
		<p><?php _e('LIVEUI_RELOAD_CACHE_INFO_MESSAGE', 'liveui') ?></p>
		<p>
			<input type="submit" name="reload" value="<?php _e('Reload LiveUI cache', 'liveui') ?>" />
		</p>
		<hr />
		<p><?php _e('LIVEUI_REPORT_MISSING_TRANSLATION_INFO_MESSAGE', 'liveui') ?></p>
		<p><?php _e('LIVEUI_MISSING_TRANSLATIONS_COUNT', 'liveui') ?>: <strong><?php echo $missingTranslationsCount; ?></strong></p>
		<p><?php _e('LIVEUI_REPORTED_MISSING_TRANSLATIONS_COUNT', 'liveui') ?>: <strong><?php echo $reportedMissingTranslationsCount; ?></strong></p>
		<p>
			<input type="submit" name="report" value="<?php _e('Report missing translations', 'liveui') ?>"<?php //echo ($missingTranslationsCount > 1) ? '' : ' disabled="disabled"'; ?> />
		</p>
		<hr />
	</form>
</div>
