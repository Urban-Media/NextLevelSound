<div class="list-container" style="width:<?php echo $this->options['maxwidth']; ?>;max-width:<?php echo $this->options['maxwidth']; ?>">
  <?php
  if ($this->options['show_breadcrumb'] === '1' || $this->options['search'] === '1' || $this->options['show_refreshbutton'] === '1' ||
          $this->get_user()->can_download_zip() || $this->get_user()->can_delete_files() || $this->get_user()->can_delete_folders()) {
      ?>
      <div class="nav-header">
        <?php if ($this->options['show_breadcrumb'] === '1') { ?>
            <a class="nav-home" title="<?php _e('Back to our first folder', 'outofthebox'); ?>">
              <i class="fa fa-home pull-left"></i>
            </a>
            <?php if ($this->options['show_refreshbutton'] === '1') { ?>
                <a class="nav-refresh" title="<?php _e('Refresh', 'outofthebox'); ?>">
                  <i class="fa fa-refresh pull-right"></i>
                </a>
            <?php } ?>
            <a class="nav-gear" title="<?php _e('Options', 'outofthebox'); ?>">
              <i class="fa fa-gear pull-right"></i>
            </a>
            <div class="gear-menu" data-token="<?php echo $this->listtoken; ?>">
              <ul>
                <?php
                if ($this->get_user()->can_upload()) {
                    ?>
                    <li><a class="nav-upload" title="<?php _e('Upload files', 'outofthebox'); ?>"><i class="fa fa-upload fa-lg"></i><?php _e('Upload files', 'outofthebox'); ?></a></li>
                    <?php
                }

                if ($this->get_user()->can_download_zip()) {
                    ?>
                    <li><a class="all-files-to-zip"><i class='fa fa-cloud-download fa-lg'></i><?php _e('Download all files', 'outofthebox'); ?> (.zip)</a></li>
                    <li><a class="selected-files-to-zip"><i class='fa fa-cloud-download fa-lg'></i><?php _e('Download selected files', 'outofthebox'); ?> (.zip)</a></li>
                    <?php
                }
                if ($this->get_user()->can_delete_files() || $this->get_user()->can_delete_folders()) {
                    ?>
                    <li><a class="selected-files-delete" title="<?php _e('Delete selected files', 'outofthebox'); ?>"><i class="fa fa-times-circle fa-lg"></i><?php _e('Delete selected files', 'outofthebox'); ?></a></li>
                    <?php
                }
                ?>
                <li class='gear-menu-no-options' style="display: none"><a><i class='fa fa-info-circle fa-lg'></i><?php _e('No options...', 'outofthebox') ?></a></li>
              </ul>
            </div>
            <?php
        }

        if ($this->options['search'] === '1') {
            ?>
            <a class="nav-search">
              <i class="fa fa-search pull-right"></i>
            </a>

            <div class="search-div">
              <div class="search-remove"><i class="fa fa-times-circle fa-lg"></i></div>
              <input name="q" type="text" size="40" placeholder="<?php echo __('Search filenames', 'outofthebox'); ?>" class="search-input" />
            </div>
        <?php }; ?>
        <?php if ($this->options['show_breadcrumb'] === '1') { ?>
            <div class="nav-title"><?php _e('Loading...', 'outofthebox'); ?></div>
        <?php }; ?>
      </div>
  <?php } ?>
  <?php if ($this->options['show_columnnames'] === '1') { ?>
      <div class='column_names'>
        <div class='entry_icon'></div>
        <?php
        if ($this->get_user()->can_download_zip() || $this->get_user()->can_delete_files() || $this->get_user()->can_delete_folders()) {
            ?>
            <div class='entry_checkallbox'><input type='checkbox' name='select-all-files' class='select-all-files'/></div>
            <?php
        };
        ?>
        <div class='entry_edit'>&nbsp;</div>
        <?php
        if ($this->options['show_filesize'] === '1') {
            ?>
            <div class='entry_size sortable <?php echo ($this->options['sort_field'] === 'size') ? $this->options['sort_order'] : ''; ?>' data-sortname="size"><span class="sort_icon">&nbsp;</span><a class='entry_sort'><?php _e('Size', 'outofthebox'); ?></a></div>
            <?php
        };

        if ($this->options['show_filedate'] === '1') {
            ?>
            <div class='entry_lastedit sortable <?php echo ($this->options['sort_field'] === 'modified') ? $this->options['sort_order'] : ''; ?>' data-sortname="modified"><a class='entry_sort'><?php _e('Date modified', 'outofthebox'); ?></a><span class="sort_icon">&nbsp;</span></div>
            <?php
        };
        ?>
        <div class='entry_name sortable <?php echo ($this->options['sort_field'] === 'name') ? $this->options['sort_order'] : ''; ?>' data-sortname="name"><a class='entry_sort'><?php _e('Name', 'outofthebox'); ?></a><span class="sort_icon">&nbsp;</span></div>
      </div>
  <?php }; ?>
  <div class="loading initialize">&nbsp;</div>
  <div class="ajax-filelist" style="<?php echo (!empty($this->options['maxheight'])) ? 'max-height:' . $this->options['maxheight'] . ';overflow-y: scroll;' : '' ?>">&nbsp;</div>
</div>