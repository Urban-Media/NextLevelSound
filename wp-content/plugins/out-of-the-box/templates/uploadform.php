<div class="fileupload-container" style="width:<?php echo $this->options['maxwidth']; ?>;max-width:<?php echo $this->options['maxwidth']; ?>" >
  <div>
    <div id="fileupload-<?php echo $this->listtoken; ?>" class="fileuploadform" data-token='<?php echo $this->listtoken; ?>'>
      <input type="hidden" name="acceptfiletypes" value="<?php echo $acceptfiletypes; ?>">
      <input type="hidden" name="maxfilesize" value="<?php echo $max_file_size; ?>">
      <input type="hidden" name="maxnumberofuploads" value="<?php echo $max_number_of_uploads; ?>">
      <div class="fileupload-drag-drop">
        <div>
          <img src="<?php echo OUTOFTHEBOX_ROOTPATH . '/css/images/drag-upload.png'; ?>" height="98" width="190"/>
          <p><?php _e('Drag your files here', 'outofthebox'); ?> ...</p>
        </div>
      </div>

      <div class='fileupload-list'>
        <div role="presentation">
          <div class="files"></div>

        </div>
        <input type='hidden' name='fileupload-filelist' id='fileupload-filelist' class='fileupload-filelist' value='<?php echo (isset($_REQUEST['fileupload-filelist']) ? stripslashes($_REQUEST['fileupload-filelist']) : ''); ?>'>
      </div>
      <div class="fileupload-buttonbar">

        <div class="fileupload-buttonbar-text">
          <?php _e('... or find documents on your device', 'outofthebox'); ?></div>
        <div class="upload-btn-container upload-btn upload-btn-primary">
          <span><?php _e('Add files', 'outofthebox'); ?></span>
          <?php
          ## Mobile browser don't always like the multiple attribute causing bad uploads
          if (wp_is_mobile()) {
              ?>
              <input type="file" name="files[]" class='upload-input-button'>
              <?php
          } else {
              ?>
              <input type="file" name="files[]" multiple="multiple" class='upload-input-button'>
              <?php
          }
          ?>

        </div>
        <div class="upload-btn-container upload-btn upload-btn-primary upload-folder">
          <span><?php _e('Upload folder', 'outofthebox'); ?></span>
          <input type="file" name="files[]" multiple="multiple" class='upload-input-button upload-multiple-files' multiple directory webkitdirectory>
        </div>
      </div>
    </div>
  </div>
  <div class="template-row">
    <div class="upload-thumbnail">
      <img class="" src="" />
    </div>

    <div class="upload-file-info">
      <div class="upload-status-container"><i class="upload-status-icon fa fa-circle"></i> <span class="upload-status"></span></div>
      <div class="file-size"></div>
      <div class="file-name"></div>
      <div class="upload-progress">
        <div class="progress progress-striped active ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
          <div class="ui-progressbar-value ui-widget-header ui-corner-left" style="display: none; width: 0%;"></div>
        </div>
      </div>
      <div class="upload-error"></div>
    </div>
  </div>
  <div class="fileupload-info-container">
    <?php _e('Max file size: ', 'outofthebox'); ?> <span class="max-file-size"><?php echo $post_max_size_str; ?></span>
    <?php
    if (!empty($this->options['upload_ext']) && $this->options['upload_ext'] !== '.') {
        echo " | " . __('Allowed formats: ', 'outofthebox') . ' <span class="max-file-size">' . str_replace('|', ', ', $this->options['upload_ext']) . '</span>';
    }
    ?>
  </div>
</div>