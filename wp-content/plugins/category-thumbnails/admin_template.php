<?php
	$exists		= (isset($category->term_thumbnail) && $category->term_thumbnail != 'none' && !empty($category->term_thumbnail) && $category->term_thumbnail != 'NULL' ? true : false);

	if(isset($_POST['action']) && $_POST['action'] == 'add-tag') {
		$exists	= false;
		print '<script type="text/javascript">category_thumbnail_clear();</script>';
	}
?>
<div id="create-thumbnail<?php print ($exists == true ? ' style="display: none;"' : ''); ?>">
	<p class="submit">
		<input type="button" name="submit" style="width: auto;" id="set-category-thumbnail" class="button button-primary" value="<?php print __('add Category image', PCT_I18N); ?>" />
	</p>
</div>
<div id="preview-thumbnail" style="display: <?php print ($exists == true ? 'block' : 'none'); ?>;">
	<a class="pct_image" title="<?php print __('edit Thumbnail', PCT_I18N); ?>" href="#" id="reset-category-thumbnail"></a>
	<p class="submit">
		<input type="button" name="submit" style="width: auto;" id="let-category-thumbnail" class="button button-primary" value="<?php print __('remove Category image', PCT_I18N); ?>" />
	</p>
</div>

<input type="hidden" name="lang_category-thumbnails_title" value="<?php print __('Set Category Thumbnail', PCT_I18N); ?>" />
<input type="hidden" name="lang_category-thumbnails_button" value="<?php print __('Insert Thumbnail', PCT_I18N); ?>" />
<input type="hidden" name="category-thumbnail" id="category-thumbnail" value="<?php print ($exists == true ? base64_encode($category->term_thumbnail) : 'none'); ?>" />
<p><?php printf(__('This Category image will be displayed if the Theme supports %s.', PCT_I18N), sprintf('<strong>%s</strong>', $this->name)); ?></p>