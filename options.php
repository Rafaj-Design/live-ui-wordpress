<div>
	<h2><?php __('LiveUI Options', 'liveui') ?></h2>
	
	<form method="post" action="options.php">
		<h3 class="title"><?php __('Settings', 'liveui') ?></h3>
		<p><?php __('LIVEUI_SETTINGS_INFO_MESSAGE', 'liveui') ?></p>
		<?php wp_nonce_field('update-options'); ?>
		<?php settings_fields('liveui_settings'); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="liveui_translation_api_key"><?php __('LiveUI Translation API key', 'liveui') ?></label></th>
					<td>
						<input name="liveui_translation_api_key" type="text" id="liveui_translation_api_key" class="regular-text" placeholder="XXXX-XXXXXX-XXXXX-XXXX-XXXXXX" value="<?php echo get_option('liveui_translation_api_key'); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="liveui_debugging"><?php __('Debugging', 'liveui') ?></label></th>
					<td>
						<input name="liveui_debugging" type="checkbox" id="liveui_debugging" value="1"<?php echo ((bool)get_option('liveui_debugging') ? ' checked="checked"' : ''); ?> />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="liveui_debugging_text_with_underscores"><?php __('Replace translated text with underscores', 'liveui') ?></label></th>
					<td>
						<input name="liveui_debugging_text_with_underscores" type="checkbox" id="liveui_debugging_text_with_underscores" value="1"<?php echo ((bool)get_option('liveui_debugging_text_with_underscores') ? ' checked="checked"' : ''); ?> />
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="update" />
		<p>
			<input type="submit" value="<?php __('Save Changes', 'liveui') ?>" />
		</p>
	</form>
	
	<form method="post" action="options.php">
		<h3 class="title"><?php __('Actions', 'liveui') ?></h3>
		<?php wp_nonce_field('update-options'); ?>
		<p><?php __('LIVEUI_RELOAD_CACHE_INFO_MESSAGE', 'liveui') ?></p>
		<p>
			<input type="submit" name="reload" value="<?php __('Reload LiveUI cache', 'liveui') ?>" />
		</p>
		<p><?php __('LIVEUI_REPORT_MISSING_TRANSLATION_INFO_MESSAGE', 'liveui') ?></p>
		<p><?php __('LIVEUI_MISSING_TRANSLATIONS_COUNT', 'liveui') ?>: <?php echo $missingTranslationsCount; ?></p>
		<p>
			<input type="submit" name="report" value="<?php __('Report missing translations', 'liveui') ?>"<?php //echo ($missingTranslationsCount > 1) ? '' : ' disabled="disabled"'; ?> />
		</p>
	
	</form>
</div>
