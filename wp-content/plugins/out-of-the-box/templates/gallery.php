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
                <?php
            }

            if ($this->get_user()->can_download_zip() || $this->get_user()->can_delete_files() || $this->get_user()->can_delete_folders()) {
                ?>

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
  <div class="loading initialize">&nbsp;</div>
  <div class="ajax-filelist" style="<?php echo (!empty($this->options['maxheight'])) ? 'max-height:' . $this->options['maxheight'] . ';overflow-y: scroll;' : '' ?>">&nbsp;</div>
</div>