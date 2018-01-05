<?php
$page = isset($_GET["page"]) ? '?page=' . $_GET["page"] : '';
$location = get_admin_url(null, 'admin.php' . $page);
$admin_nonce = wp_create_nonce("outofthebox-admin-action");

function wp_roles_checkbox($name, $selected = array(), $always_include_admin = true) {
    global $wp_roles;
    if (!isset($wp_roles)) {
        $wp_roles = new \WP_Roles();
    }

    $roles = $wp_roles->get_names();

    if ($always_include_admin && !in_array('administrator', $selected)) {
        $selected[] = 'administrator';
    }

    foreach ($roles as $role_value => $role_name) {
        if (in_array($role_value, $selected)) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }

        $checkbox = '<div class="outofthebox-option-checkbox">';
        $checkbox .= '<input class="simple" type="checkbox" name="' . $name . '[]" value="' . $role_value . '" ' . $checked . '>';
        $checkbox .= '<label for="userfolders_method_auto1" class="outofthebox-option-checkbox-label">' . $role_name . '</label>';
        $checkbox .= '</div>';

        if ($always_include_admin && $role_value === 'administrator') {
            $checkbox .= sprintf("<div style='display:none'> %s </div>", $checkbox);
        }

        echo $checkbox;
    }
}

function create_color_boxes_table($colors, $settings) {

    if (count($colors) === 0) {
        return '';
    }

    $table_html = '<table class="color-table">';

    foreach ($colors as $color_id => $color) {

        $value = isset($settings['colors'][$color_id]) ? sanitize_text_field($settings['colors'][$color_id]) : $color['default'];

        $table_html .= '<tr>';
        $table_html .= "<td>{$color['label']}</td>";
        $table_html .= "<td><input value='$value' data-default-color='{$color['default']}'  name='out_of_the_box_settings[colors][$color_id]' id='colors-$color_id' type='text'  class='outofthebox-color-picker' data-alpha='true' ></td>";
        $table_html .= '</tr>';
    }

    $table_html .= '</table>';
    return $table_html;
}

function create_upload_button_for_custom_images($option) {

    $field_value = $option['value'];
    $button_html = '<div class="upload_row">';

    $button_html .= '<div class="screenshot" id="' . $option['id'] . '_image">' . "\n";

    if ('' !== $field_value) {
        $button_html .= '<img src="' . $field_value . '" alt="" />' . "\n";
        $button_html .= '<a href="javascript:void(0)" class="upload-remove">' . __('Remove Media', 'outofthebox') . '</a>' . "\n";
        $button_html .= '<a href="javascript:void(0)" class="upload-default">' . __('Default', 'outofthebox') . '</a>' . "\n";
    }

    $button_html .= '</div>';

    $button_html .= '<input id="' . esc_attr($option['id']) . '" class="upload outofthebox-option-input-large" type="text" name="' . esc_attr($option['name']) . '" value="' . esc_attr($field_value) . '" autocomplete="off" />';
    $button_html .= '<input id="upload_image_button" class="upload_button simple-button blue" type="button" value="' . __('Select Image', 'outofthebox') . '" title="' . __('Upload or select a file from the media library', 'useyoudrive') . '" />';

    if ($field_value !== $option['default']) {
        $button_html .= '<input id="default_image_button" class="default_image_button simple-button" type="button" value="' . __('Default', 'outofthebox') . '" title="' . __('Fallback to the default value', 'useyoudrive') . '"  data-default="' . $option['default'] . '"/>';
    }

    $button_html .= '</div>' . "\n";

    return $button_html;
}
?>

<div class="outofthebox admin-settings">
  <form id="outofthebox-options" method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?>
    <?php settings_fields('out_of_the_box_settings'); ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="out_of_the_box_settings[purcasecode]" id="purcasecode" value="<?php echo esc_attr($this->settings['purcasecode']); ?>">
    <input type="hidden" name="out_of_the_box_settings[dropbox_app_token]" id="dropbox_app_token" value="<?php echo @esc_attr($this->settings['dropbox_app_token']); ?>" >

    <div class="wrap">
      <div class="outofthebox-header">
        <div class="outofthebox-logo"><img src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/logo64x64.png" height="64" width="64"/></div>
        <div class="outofthebox-form-buttons"> <div id="save_settings" class="simple-button default save_settings" name="save_settings"><?php _e("Save Settings", 'outofthebox'); ?>&nbsp;<div class='oftb-spinner'></div></div></div>
        <div class="outofthebox-title">Out-of-the-Box <?php _e('Settings', 'outofthebox'); ?></div>
      </div>


      <div id="" class="outofthebox-panel outofthebox-panel-left">      
        <div class="outofthebox-nav-header"><?php _e('Settings', 'outofthebox'); ?></div>

        <ul class="outofthebox-nav-tabs">
          <li id="settings_general_tab" data-tab="settings_general" class="current"><a ><?php _e('General', 'outofthebox'); ?></a></li>
          <li id="settings_layout_tab" data-tab="settings_layout" ><a ><?php _e('Layout', 'outofthebox'); ?></a></li>
          <li id="settings_userfolders_tab" data-tab="settings_userfolders" ><a ><?php _e('Private Folders', 'outofthebox'); ?></a></li>
          <li id="settings_advanced_tab" data-tab="settings_advanced" ><a ><?php _e('Advanced', 'outofthebox'); ?></a></li>
          <li id="settings_notifications_tab" data-tab="settings_notifications" ><a ><?php _e('Notifications', 'outofthebox'); ?></a></li>
          <li id="settings_permissions_tab" data-tab="settings_permissions" ><a><?php _e('Permissions', 'outofthebox'); ?></a></li>
          <li id="settings_stats_tab" data-tab="settings_stats" ><a><?php _e('Statistics', 'outofthebox'); ?></a></li>
          <li id="settings_system_tab" data-tab="settings_system" ><a><?php _e('System information', 'outofthebox'); ?></a></li>
          <li id="settings_help_tab" data-tab="settings_help" ><a><?php _e('Need help?', 'outofthebox'); ?></a></li>

        </ul>

        <div class="outofthebox-nav-header" style="margin-top: 50px;"><?php _e('Other Cloud Plugins', 'outofthebox'); ?></div>
        <ul class="outofthebox-nav-tabs">
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://codecanyon.net/item/useyourdrive-google-drive-plugin-for-wordpress/6219776?ref=_deleeuw_" target="_blank" style="color:#0078d7;">Google Drive <i class="fa fa-external-link-square" aria-hidden="true"></i></a></li>
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://codecanyon.net/item/outofthebox-onedrive-plugin-for-wordpress/11453104?ref=_DeLeeuw_" target="_blank" style="color:#0078d7;">OneDrive <i class="fa fa-external-link-square" aria-hidden="true"></i></a></li>
          <li id="settings_help_tab" data-tab="settings_help"><a href="https://codecanyon.net/item/letsbox-box-plugin-for-wordpress/8204640?ref=_DeLeeuw_" target="_blank" style="color:#0078d7;">Box <i class="fa fa-external-link-square" aria-hidden="true"></i></a></li>
        </ul> 

        <div class="outofthebox-nav-footer"><a href="<?php echo admin_url('update-core.php'); ?>"><?php _e('Version', 'outofthebox'); ?>: <?php echo OUTOFTHEBOX_VERSION; ?></a></div>
      </div>


      <div class="outofthebox-panel outofthebox-panel-right">

        <!-- General Tab -->
        <div id="settings_general" class="outofthebox-tab-panel current">

          <div class="outofthebox-tab-panel-header"><?php _e('General', 'outofthebox'); ?></div>

          <div class="outofthebox-option-title"><?php _e('Authorization', 'outofthebox'); ?></div>
          <?php
          echo $this->get_plugin_authorization_box();
          ?>
          <div class="outofthebox-option-title"><?php _e('Activation', 'outofthebox'); ?></div>
          <?php
          echo $this->get_plugin_activated_box();
          ?>

        </div>
        <!-- End General Tab -->


        <!-- Layout Tab -->
        <div id="settings_layout"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('Layout', 'outofthebox'); ?></div>

          <div class="outofthebox-accordion">

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Loading Spinner & Images', 'outofthebox'); ?>         </div>
            <div>

              <div class="outofthebox-option-title"><?php _e('Select Loader Spinner', 'outofthebox'); ?></div>
              <select type="text" name="out_of_the_box_settings[loaders][style]" id="loader_style">
                <option value="beat" <?php echo ($this->settings['loaders']['style'] === "beat" ? "selected='selected'" : ''); ?>><?php _e('Beat', 'outofthebox'); ?></option>
                <option value="spinner" <?php echo ($this->settings['loaders']['style'] === "spinner" ? "selected='selected'" : ''); ?>><?php _e('Spinner', 'outofthebox'); ?></option>
                <option value="custom" <?php echo ($this->settings['loaders']['style'] === "custom" ? "selected='selected'" : ''); ?>><?php _e('Custom Image (selected below)', 'outofthebox'); ?></option>
              </select>

              <div class="outofthebox-option-title"><?php _e('General Loader', 'outofthebox'); ?></div>
              <?php
              $button = array('value' => $this->settings['loaders']['loading'], 'id' => 'loaders_loading', 'name' => 'out_of_the_box_settings[loaders][loading]', 'default' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_loading.gif');
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="outofthebox-option-title"><?php _e('Upload Loader', 'outofthebox'); ?></div>
              <?php
              $button = array('value' => $this->settings['loaders']['upload'], 'id' => 'loaders_upload', 'name' => 'out_of_the_box_settings[loaders][upload]', 'default' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_upload.gif');
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="outofthebox-option-title"><?php _e('No Results', 'outofthebox'); ?></div>
              <?php
              $button = array('value' => $this->settings['loaders']['no_results'], 'id' => 'loaders_no_results', 'name' => 'out_of_the_box_settings[loaders][no_results]', 'default' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_no_results.png');
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="outofthebox-option-title"><?php _e('Access Forbidden Image', 'outofthebox'); ?></div>
              <?php
              $button = array('value' => $this->settings['loaders']['protected'], 'id' => 'loaders_protected', 'name' => 'out_of_the_box_settings[loaders][protected]', 'default' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_protected.png');
              echo create_upload_button_for_custom_images($button);
              ?>
              <div class="outofthebox-option-title"><?php _e('Error Image', 'outofthebox'); ?></div>
              <?php
              $button = array('value' => $this->settings['loaders']['error'], 'id' => 'loaders_error', 'name' => 'out_of_the_box_settings[loaders][error]', 'default' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_error.png');
              echo create_upload_button_for_custom_images($button);
              ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Color Palette', 'outofthebox'); ?></div>
            <div>

              <div class="outofthebox-option-title"><?php _e('Content Skin', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e("Select the general content skin", 'outofthebox'); ?>.</div>
              <select name="skin_selectbox" id="content_skin_selectbox" class="ddslickbox">
                <option value="dark" <?php echo ($this->settings['colors']['style'] === "dark" ? "selected='selected'" : ''); ?> data-imagesrc="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/skin-dark.png" data-description=""><?php _e('Dark', 'outofthebox'); ?></option>
                <option value="light" <?php echo ($this->settings['colors']['style'] === "light" ? "selected='selected'" : ''); ?> data-imagesrc="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/skin-light.png" data-description=""><?php _e('Light', 'outofthebox'); ?></option>
              </select>
              <input type="hidden" name="out_of_the_box_settings[colors][style]" id="content_skin" value="<?php echo esc_attr($this->settings['colors']['style']); ?>">

              <?php
              $colors = array(
                  'background' => array(
                      'label' => __('Content Background Color', 'outofthebox'),
                      'default' => '#f2f2f2'
                  ),
                  'accent' => array(
                      'label' => __('Accent Color', 'outofthebox'),
                      'default' => '#29ADE2'
                  ),
                  'black' => array(
                      'label' => __('Black', 'outofthebox'),
                      'default' => '#222'
                  ),
                  'dark1' => array(
                      'label' => __('Dark 1', 'outofthebox'),
                      'default' => '#666666'
                  ),
                  'dark2' => array(
                      'label' => __('Dark 2', 'outofthebox'),
                      'default' => '#999999'
                  ),
                  'white' => array(
                      'label' => __('White', 'outofthebox'),
                      'default' => '#fff'
                  ),
                  'light1' => array(
                      'label' => __('Light 1', 'outofthebox'),
                      'default' => '#fcfcfc'
                  ),
                  'light2' => array(
                      'label' => __('Light 2', 'outofthebox'),
                      'default' => '#e8e8e8'
                  )
              );

              echo create_color_boxes_table($colors, $this->settings);
              ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Lightbox', 'outofthebox'); ?></div>
            <div>
              <div class="outofthebox-option-title"><?php _e('Lightbox Skin', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e('Select which skin you want to use for the lightbox', 'outofthebox'); ?>.</div>
              <select name="lightbox_skin_selectbox" id="lightbox_skin_selectbox" class="ddslickbox">
                <?php
                foreach (new DirectoryIterator(OUTOFTHEBOX_ROOTDIR . '/includes/iLightBox/') as $fileInfo) {
                    if ($fileInfo->isDir() && !$fileInfo->isDot() && (strpos($fileInfo->getFilename(), 'skin') !== false)) {
                        if (file_exists(OUTOFTHEBOX_ROOTDIR . '/includes/iLightBox/' . $fileInfo->getFilename() . '/skin.css')) {
                            $selected = '';
                            $skinname = str_replace('-skin', '', $fileInfo->getFilename());

                            if ($skinname === $this->settings['lightbox_skin']) {
                                $selected = 'selected="selected"';
                            }

                            $icon = file_exists(OUTOFTHEBOX_ROOTDIR . '/includes/iLightBox/' . $fileInfo->getFilename() . '/thumb.jpg') ? OUTOFTHEBOX_ROOTPATH . '/includes/iLightBox/' . $fileInfo->getFilename() . '/thumb.jpg' : '';
                            echo '<option value="' . $skinname . '" data-imagesrc="' . $icon . '" data-description="" ' . $selected . '>' . $fileInfo->getFilename() . "</option>\n";
                        }
                    }
                }
                ?>
              </select>
              <input type="hidden" name="out_of_the_box_settings[lightbox_skin]" id="lightbox_skin" value="<?php echo esc_attr($this->settings['lightbox_skin']); ?>">


              <div class="outofthebox-option-title"><?php _e('Lightbox Scroll', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e("Sets path for switching windows. Possible values are 'vertical' and 'horizontal' and the default is 'vertical", 'outofthebox'); ?>.</div>
              <select type="text" name="out_of_the_box_settings[lightbox_path]" id="lightbox_path">
                <option value="horizontal" <?php echo ($this->settings['lightbox_path'] === "horizontal" ? "selected='selected'" : ''); ?>>Horizontal</option>
                <option value="vertical" <?php echo ($this->settings['lightbox_path'] === "vertical" ? "selected='selected'" : ''); ?>>Vertical</option>
              </select>

              <div class="outofthebox-option-title"><?php _e('Allow Mouse Click on Image', 'outofthebox'); ?>
                <div class="outofthebox-onoffswitch">
                  <input type='hidden' value='No' name='out_of_the_box_settings[lightbox_rightclick]'/>
                  <input type="checkbox" name="out_of_the_box_settings[lightbox_rightclick]" id="lightbox_rightclick" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['lightbox_rightclick'] === "Yes") ? 'checked="checked"' : ''; ?>/>
                  <label class="outofthebox-onoffswitch-label" for="lightbox_rightclick"></label>
                </div>
              </div>
              <div class="outofthebox-option-description"><?php _e("Should people be able to access the right click context menu to e.g. save the image?", 'outofthebox'); ?>.</div>

              <div class="outofthebox-option-title"><?php _e('Lightbox Caption', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e("Choose when the caption containing the title and (if available) description are shown", 'outofthebox'); ?>.</div>
              <select type="text" name="out_of_the_box_settings[lightbox_showcaption]" id="lightbox_showcaption">
                <option value="click" <?php echo ($this->settings['lightbox_showcaption'] === "click" ? "selected='selected'" : ''); ?>><?php _e('Show caption after clicking on the Lightbox', 'outofthebox'); ?></option>
                <option value="mouseenter" <?php echo ($this->settings['lightbox_showcaption'] === "mouseenter" ? "selected='selected'" : ''); ?>><?php _e('Show caption when Lightbox opens', 'outofthebox'); ?></option>
              </select>              



            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Media Player Skin', 'outofthebox'); ?></div>
            <div> 
              <div class="outofthebox-option-title"><?php _e('Media Player Skin', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e("Select which skin you want to use for the Media Player", 'outofthebox'); ?>.</div>
              <select name="mediaplayer_skin_selectbox" id="mediaplayer_skin_selectbox" class="ddslickbox">
                <?php
                foreach (new DirectoryIterator(OUTOFTHEBOX_ROOTDIR . '/skins/') as $fileInfo) {
                    if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                        if (file_exists(OUTOFTHEBOX_ROOTDIR . '/skins/' . $fileInfo->getFilename() . '/Media.js')) {
                            $selected = '';
                            if ($fileInfo->getFilename() === $this->settings['mediaplayer_skin']) {
                                $selected = 'selected="selected"';
                            }

                            $icon = file_exists(OUTOFTHEBOX_ROOTDIR . '/skins/' . $fileInfo->getFilename() . '/thumb.jpg') ? OUTOFTHEBOX_ROOTPATH . '/skins/' . $fileInfo->getFilename() . '/thumb.jpg' : '';
                            echo '<option value="' . $fileInfo->getFilename() . '" data-imagesrc="' . $icon . '" data-description="" ' . $selected . '>' . $fileInfo->getFilename() . "</option>\n";
                        }
                    }
                }
                ?>
              </select>
              <input type="hidden" name="out_of_the_box_settings[mediaplayer_skin]" id="mediaplayer_skin" value="<?php echo esc_attr($this->settings['mediaplayer_skin']); ?>">

            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Custom CSS', 'outofthebox'); ?></div>
            <div>
              <div class="outofthebox-option-title"><?php _e('Custom CSS', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e("If you want to modify the looks of the plugin slightly, you can insert here your custom CSS. Don't edit the CSS files itself, because those modifications will be lost during an update.", 'outofthebox'); ?>.</div>
              <textarea name="out_of_the_box_settings[custom_css]" id="custom_css" cols="" rows="10"><?php echo esc_attr($this->settings['custom_css']); ?></textarea>
            </div>
          </div>
        </div>
        <!-- End Layout Tab -->

        <!-- UserFolders Tab -->
        <div id="settings_userfolders"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('Private Folders', 'outofthebox'); ?></div>

          <div class="outofthebox-option-title"><?php _e('Create Private Folders on registration', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[userfolder_oncreation]'/>
              <input type="checkbox" name="out_of_the_box_settings[userfolder_oncreation]" id="userfolder_oncreation" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['userfolder_oncreation'] === "Yes") ? 'checked="checked"' : ''; ?>/>
              <label class="outofthebox-onoffswitch-label" for="userfolder_oncreation"></label>
            </div>
          </div>
          <div class="outofthebox-option-description"><?php _e("Create a new Private Folders automatically after a new user has been created", 'outofthebox'); ?>.</div>

          <div class="outofthebox-option-title"><?php _e('Create all Private Folders on first visit', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[userfolder_onfirstvisit]'/>
              <input type="checkbox" name="out_of_the_box_settings[userfolder_onfirstvisit]" id="userfolder_onfirstvisit" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['userfolder_onfirstvisit'] === "Yes") ? 'checked="checked"' : ''; ?>/>
              <label class="outofthebox-onoffswitch-label" for="userfolder_onfirstvisit"></label>
            </div>
          </div>
          <div class="outofthebox-option-description"><?php _e("Create all Private Folders on first visit", 'outofthebox'); ?>.</div>
          <div class="oftb-warning">
            <i><strong>NOTICE</strong>: Creating User Folders takes around 1 sec per user, so it isn't recommended to create those on first visit when you have tons of users.</i>
          </div>


          <div class="outofthebox-option-title"><?php _e('Update Private Folders after profile update', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[userfolder_update]'/>
              <input type="checkbox" name="out_of_the_box_settings[userfolder_update]" id="userfolder_update" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['userfolder_update'] === "Yes") ? 'checked="checked"' : ''; ?>/>
              <label class="outofthebox-onoffswitch-label" for="userfolder_update"></label>
            </div>
          </div>
          <div class="outofthebox-option-description"><?php _e("Update the folder name of the user after they have updated their profile", 'outofthebox'); ?>.</div>

          <div class="outofthebox-option-title"><?php _e('Remove Private Folders after account removal', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[userfolder_remove]'/>
              <input type="checkbox" name="out_of_the_box_settings[userfolder_remove]" id="userfolder_remove" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['userfolder_remove'] === "Yes") ? 'checked="checked"' : ''; ?> />
              <label class="outofthebox-onoffswitch-label" for="userfolder_remove"></label>
            </div>
          </div>
          <div class="outofthebox-option-description"><?php _e("Try to remove Private Folders after they are deleted", 'outofthebox'); ?>.</div>

          <div class="outofthebox-option-title"><?php _e('Private Folders in Back-End', 'outofthebox'); ?></div>
          <div class="outofthebox-option-description"><?php _e("Enables Private Folders in the Shortcode Builder and Back-End File Browser", 'outofthebox'); ?>.</div>
          <select type="text" name="out_of_the_box_settings[userfolder_backend]" id="userfolder_backend" data-div-toggle="private-folders-auto" data-div-toggle-value="auto">
            <option value="No" <?php echo ($this->settings['userfolder_backend'] === "No" ? "selected='selected'" : ''); ?>>No</option>
            <option value="manual" <?php echo ($this->settings['userfolder_backend'] === "manual" ? "selected='selected'" : ''); ?>><?php _e('Yes, I link the users Manually', 'outofthebox'); ?></option>
            <option value="auto" <?php echo ($this->settings['userfolder_backend'] === "auto" ? "selected='selected'" : ''); ?>><?php _e('Yes, let the plugin create the User Folders for me', 'outofthebox'); ?></option>
          </select>

          <?php if ($this->get_app()->has_access_token()) { ?>
              <div class="outofthebox-suboptions private-folders-auto <?php echo (($this->settings['userfolder_backend']) === 'auto') ? '' : 'hidden' ?> ">
                <div class="outofthebox-option-title"><?php _e('Root folder for Private Folders', 'outofthebox'); ?></div>
                <div class="outofthebox-option-description"><?php _e("Select in which folder the Private Folders should be created", 'outofthebox'); ?>. <?php _e('Current selected folder', 'outofthebox'); ?>:</div>
                <?php
                $private_auto_folder = $this->settings['userfolder_backend_auto_root'];

                if (empty($private_auto_folder)) {
                    $root = '/';
                    $private_auto_folder = array();
                    $private_auto_folder['id'] = '/';
                    $private_auto_folder['name'] = '/';
                    $private_auto_folder['view_roles'] = array('administrator');
                }
                ?>
                <input class="outofthebox-option-input-large private-folders-auto-current" type="text" value="<?php echo $private_auto_folder['name']; ?>" disabled="disabled">
                <input class="private-folders-auto-input-id" type='hidden' value='<?php echo $private_auto_folder['id']; ?>' name='out_of_the_box_settings[userfolder_backend_auto_root][id]'/>
                <input class="private-folders-auto-input-name" type='hidden' value='<?php echo $private_auto_folder['name']; ?>' name='out_of_the_box_settings[userfolder_backend_auto_root][name]'/>
                <div id="root_folder_button" type="button" class="button-primary private-folders-auto-button"><?php _e('Select Folder', 'outofthebox'); ?>&nbsp;<div class='oftb-spinner'></div></div>

                <div id='oftb-embedded' style='clear:both;display:none'>
                  <?php
                  $processor = new \TheLion\OutoftheBox\Processor($this->get_main());

                  echo $processor->create_from_shortcode(
                          array('mode' => 'files',
                              'showfiles' => '1',
                              'filesize' => '0',
                              'filedate' => '0',
                              'upload' => '0',
                              'delete' => '0',
                              'rename' => '0',
                              'addfolder' => '0',
                              'showbreadcrumb' => '1',
                              'showcolumnnames' => '0',
                              'showfiles' => '0',
                              'downloadrole' => 'none',
                              'candownloadzip' => '0',
                              'showsharelink' => '0',
                              'mcepopup' => 'linktobackendglobal',
                              'search' => '0'));
                  ?>
                </div>

                <br/><br/>
                <div class="outofthebox-option-title"><?php _e('Full Access', 'outofthebox'); ?></div>
                <div class="outofthebox-option-description"><?php _e('By default only Administrator users will be able to navigate through all Private Folders', 'outofthebox'); ?>. <?php _e('When you want other User Roles to be able do browse to the Private Folders as well, please check them below', 'outofthebox'); ?>.</div>

                <?php
                $selected = (isset($private_auto_folder['view_roles'])) ? $private_auto_folder['view_roles'] : array();
                wp_roles_checkbox('out_of_the_box_settings[userfolder_backend_auto_root][view_roles]', $selected, false);
                ?>
              </div>
          <?php } ?>

          <div class="outofthebox-option-title"><?php _e('Name Template', 'outofthebox'); ?></div>
          <div class="outofthebox-option-description"><?php _e("Template name for automatically created Private Folders. You can use <code>%user_login%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%jjjj-mm-dd%</code>", 'outofthebox'); ?>.</div>
          <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[userfolder_name]" id="userfolder_name" value="<?php echo esc_attr($this->settings['userfolder_name']); ?>">

        </div>
        <!-- End UserFolders Tab -->


        <!--  Advanced Tab -->
        <div id="settings_advanced"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('Advanced', 'outofthebox'); ?></div>

          <div class="outofthebox-option-title"><?php _e('Own Dropbox App', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[dropbox_app_own]'/>
              <input type="checkbox" name="out_of_the_box_settings[dropbox_app_own]" id="dropbox_app_own" class="outofthebox-onoffswitch-checkbox" <?php echo (empty($this->settings['dropbox_app_key']) || empty($this->settings['dropbox_app_secret'])) ? '' : 'checked="checked"'; ?> data-div-toggle="own-app"/>
              <label class="outofthebox-onoffswitch-label" for="dropbox_app_own"></label>
            </div>
          </div>

          <div class="outofthebox-suboptions own-app <?php echo (empty($this->settings['dropbox_app_key']) || empty($this->settings['dropbox_app_secret'])) ? 'hidden' : '' ?> ">
            <div class="outofthebox-option-description">
              <strong>Using your own Dropbox App is <u>optional</u></strong>. For an easy setup you can just use the default App of the plugin itself by leaving the Key and Secret empty. The advantage of using your own app is limited. If you decided to create your own Dropbox App anyway, please enter your settings. In the <a href="http://goo.gl/dsT71e" target="_blank">documentation</a> you can find how you can create a Dropbox App.
              <br/><br/>
              <div class="oftb-warning">
                <i><strong>NOTICE</strong>: If you encounter any issues when trying to use your own App with Out-of-the-Box, please fall back on the default App by disabling this setting.</i>
              </div>
            </div>

            <div class="outofthebox-option-title"><?php _e('Dropbox App Key', 'outofthebox'); ?></div>
            <div class="outofthebox-option-description"><?php _e('<strong>Only</strong> if you want to use your own App, insert your Dropbox App Key here', 'outofthebox'); ?>.</div>
            <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[dropbox_app_key]" id="dropbox_app_key" value="<?php echo esc_attr($this->settings['dropbox_app_key']); ?>" placeholder="<--- <?php _e('Leave empty for easy setup', 'outofthebox') ?> --->" >

            <div class="outofthebox-option-title"><?php _e('Dropbox App Secret', 'outofthebox'); ?></div>
            <div class="outofthebox-option-description"><?php _e('If you want to use your own App, insert your Dropbox App Secret here', 'outofthebox'); ?>.</div>
            <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[dropbox_app_secret]" id="dropbox_app_secret" value="<?php echo esc_attr($this->settings['dropbox_app_secret']); ?>" placeholder="<--- <?php _e('Leave empty for easy setup', 'outofthebox') ?> --->" >   

            <div>
              <div class="outofthebox-option-title"><?php _e('OAuth 2.0 Redirect URI', 'outofthebox'); ?></div>
              <div class="outofthebox-option-description"><?php _e('Set the redirect URI in your application to the following', 'outofthebox'); ?>:</div>
              <code style="user-select:initial">
                <?php
                if ($this->get_app()->has_plugin_own_app()) {
                    echo $this->get_app()->get_redirect_uri();
                } else {
                    _e('Enter Client Key and Secret, save settings and reload the page to see the Redirect URI you will need', 'outofthebox');
                }
                ?>
              </code>
            </div>
          </div>

          <div class="outofthebox-option-title"><?php _e('Enable Gzip compression', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[gzipcompression]'/>
              <input type="checkbox" name="out_of_the_box_settings[gzipcompression]" id="gzipcompression" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['gzipcompression'] === "Yes") ? 'checked="checked"' : ''; ?> />
              <label class="outofthebox-onoffswitch-label" for="gzipcompression"></label>
            </div>
          </div>
          <div class="outofthebox-option-description">Enables gzip-compression if the visitor's browser can handle it. This will increase the performance of the plugin if you are displaying large amounts of files and it reduces bandwidth usage as well. It uses the PHP <code>ob_gzhandler()</code> callback.</div>
          <div class="oftb-warning">
            <i><strong>NOTICE</strong>: Please use this setting with caution. Always test if the plugin still works on the Front-End as some servers are already configured to gzip content!</i>
          </div>

          <div class="outofthebox-option-title"><?php _e('Max Age Cache Request', 'outofthebox'); ?></div>
          <div class="outofthebox-option-description"><?php _e('How long are the requests to view the plugin cached? Number is in minutes', 'outofthebox'); ?>.</div>
          <input type="text" name="out_of_the_box_settings[request_cache_max_age]" id="request_cache_max_age" value="<?php echo esc_attr($this->settings['request_cache_max_age']); ?>" maxlength="3" size="3" >   <?php _e('Minutes'); ?>


          <div class="outofthebox-option-title"><?php _e('Shortlinks API', 'outofthebox'); ?></div>
          <div class="outofthebox-option-description"><?php _e('Select which Url Shortener Service you want to use', 'outofthebox'); ?>.</div>
          <select type="text" name="out_of_the_box_settings[shortlinks]" id="shortlinks">
            <option value="Dropbox"  <?php echo ($this->settings['shortlinks'] === "Dropbox" ? "selected='selected'" : ''); ?>>Dropbox Urlshortener</option>
            <option value="Bit.ly"  <?php echo ($this->settings['shortlinks'] === "Bit.ly" ? "selected='selected'" : ''); ?>>Bit.ly</option>
          </select>   


          <div class="option bitly" <?php echo ($this->settings['shortlinks'] === "Dropbox" ? "style='display:none;'" : ''); ?>>
            <div class="outofthebox-option-description"><a href="https://bitly.com/a/sign_up" target="_blank">Sign up by Bitly</a> and <a href="https://bitly.com/a/oauth_apps" target="_blank">generate a Generic Access Token</a></div>

            <div class="outofthebox-option-title"><?php _e('Bitly login', 'outofthebox'); ?></div>
            <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[bitly_login]" id="bitly_login" value="<?php echo esc_attr($this->settings['bitly_login']); ?>">

            <div class="outofthebox-option-title"><?php _e('Bitly apiKey', 'outofthebox'); ?></div>
            <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[bitly_apikey]" id="bitly_apikey" value="<?php echo esc_attr($this->settings['bitly_apikey']); ?>">
          </div> 

        </div>
        <!-- End Advanced Tab -->

        <!-- Notifications Tab -->
        <div id="settings_notifications"  class="outofthebox-tab-panel">

          <div class="outofthebox-tab-panel-header"><?php _e('Notifications', 'outofthebox'); ?></div>

          <div class="outofthebox-accordion">
            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Download Notifications', 'outofthebox'); ?>         </div>
            <div>
              <div class="outofthebox-option-title"><?php _e('Subject download notification', 'outofthebox'); ?>:</div>
              <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[download_template_subject]" id="download_template_subject" value="<?php echo esc_attr($this->settings['download_template_subject']); ?>">
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>%number_of_files%</code>, <code>%visitor%</code>, <code>%user_email%</code>, <code>%ip%</code>, <code>%location%</code>, <code>%filename%</code>, <code>%filepath%</code>, <code>%folder%</code></div>

              <div class="outofthebox-option-title"><?php _e('Subject zip notification', 'outofthebox'); ?>:</div>
              <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[download_template_subject_zip]" id="download_template_subject_zip" value="<?php echo esc_attr($this->settings['download_template_subject_zip']); ?>">
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>%number_of_files%</code>, <code>%visitor%</code>, <code>%user_email%</code>, <code>%ip%</code>, <code>%location%</code>, <code>%filename%</code>, <code>%filepath%</code>, <code>%folder%</code></div>

              <div class="outofthebox-option-title"><?php _e('Template download', 'outofthebox'); ?>:</div>
              <?php
              ob_start();
              wp_editor($this->settings['download_template'], 'out_of_the_box_settings_download_template', array(
                  'textarea_name' => 'out_of_the_box_settings[download_template]',
                  'teeny' => true,
                  'textarea_rows' => 15,
                  'media_buttons' => false
              ));
              echo ob_get_clean();
              ?>
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>%currenturl%</code>, <code>%filelist%</code>,  <code>%ip%</code>, <code>%location%</code></div>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Upload Notifications', 'outofthebox'); ?>         </div>
            <div>  
              <div class="outofthebox-option-title"><?php _e('Subject upload notification', 'outofthebox'); ?>:</div>
              <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[upload_template_subject]" id="upload_template_subject" value="<?php echo esc_attr($this->settings['upload_template_subject']); ?>">
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>%number_of_files%</code>, <code>%visitor%</code>, <code>%user_email%</code>, <code>%ip%</code>, <code>%location%</code>, <code>%filename%</code>, <code>%filepath%</code>, <code>%folder%</code></div>

              <div class="outofthebox-option-title"><?php _e('Template upload', 'outofthebox'); ?>:</div>
              <?php
              ob_start();
              wp_editor($this->settings['upload_template'], 'out_of_the_box_settings_upload_template', array(
                  'textarea_name' => 'out_of_the_box_settings[upload_template]',
                  'teeny' => true,
                  'textarea_rows' => 15,
                  'media_buttons' => false
              ));
              echo ob_get_clean();
              ?>
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>%currenturl%</code>, <code>%filelist%</code>,  <code>%ip%</code>, <code>%location%</code></div>
            </div>


            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Delete Notifications', 'outofthebox'); ?>         </div>
            <div>
              <div class="outofthebox-option-title"><?php _e('Subject delete notification', 'outofthebox'); ?>:</div>
              <input class="outofthebox-option-input-large" type="text" name="out_of_the_box_settings[delete_template_subject]" id="delete_template_subject" value="<?php echo esc_attr($this->settings['delete_template_subject']); ?>">
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>"%number_of_files%</code>, <code>%visitor%</code>, <code>%user_email%</code>, <code>%ip%</code>, <code>%location%</code>, <code>%filename%</code>, <code>%filepath%</code>, <code>%folder%</code></div>

              <div class="outofthebox-option-title"><?php _e('Template deletion', 'outofthebox'); ?>:</div>

              <?php
              ob_start();
              wp_editor($this->settings['delete_template'], 'out_of_the_box_settings_delete_template', array(
                  'textarea_name' => 'out_of_the_box_settings[delete_template]',
                  'teeny' => true,
                  'textarea_rows' => 15,
                  'media_buttons' => false
              ));
              echo ob_get_clean();
              ?>
              <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%sitename%</code>, <code>%currenturl%</code>, <code>%filelist%</code>,  <code>%ip%</code>, <code>%location%</code></div>
            </div>
          </div>

          <div class="outofthebox-option-title"><?php _e('Template File line in %filelist%', 'outofthebox'); ?>:</div>
          <div class="outofthebox-option-description"><?php _e('Template for File item in File List in the download/upload/delete template', 'outofthebox'); ?>.</div>
          <?php
          ob_start();
          wp_editor($this->settings['filelist_template'], 'out_of_the_box_settings_filelist_template', array(
              'textarea_name' => 'out_of_the_box_settings[filelist_template]',
              'teeny' => true,
              'textarea_rows' => 15,
              'media_buttons' => false
          ));
          echo ob_get_clean();
          ?>
          <div class="outofthebox-option-description"><?php _e('Available placeholders', 'outofthebox'); ?>: <code>%filename%</code>, <code>%filesize%</code>, <code>%fileurl%</code>,  <code>%filepath%</code></div>


        </div>
        <!-- End Notifications Tab -->

        <!--  Permissions Tab -->
        <div id="settings_permissions"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('Permissions', 'outofthebox'); ?></div>

          <div class="outofthebox-accordion">
            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Change Plugin Settings', 'outofthebox'); ?>         </div>
            <div>
              <?php wp_roles_checkbox('out_of_the_box_settings[permissions_edit_settings]', $this->settings['permissions_edit_settings']); ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Link Users to Private Folders', 'outofthebox'); ?>        </div>
            <div>
              <?php wp_roles_checkbox('out_of_the_box_settings[permissions_link_users]', $this->settings['permissions_link_users']); ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('See Back-End Filebrowser', 'outofthebox'); ?>        </div>
            <div>
              <?php wp_roles_checkbox('out_of_the_box_settings[permissions_see_filebrowser]', $this->settings['permissions_see_filebrowser']); ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Add Plugin Shortcodes', 'outofthebox'); ?>         </div>
            <div>
              <?php wp_roles_checkbox('out_of_the_box_settings[permissions_add_shortcodes]', $this->settings['permissions_add_shortcodes']); ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Add Direct Links', 'outofthebox'); ?>        </div>
            <div>
              <?php wp_roles_checkbox('out_of_the_box_settings[permissions_add_links]', $this->settings['permissions_add_links']); ?>
            </div>

            <div class="outofthebox-accordion-title outofthebox-option-title"><?php _e('Embed Documents', 'outofthebox'); ?>        </div>
            <div>
              <?php wp_roles_checkbox('out_of_the_box_settings[permissions_add_embedded]', $this->settings['permissions_add_embedded']); ?>
            </div>

          </div>
        </div>
        <!-- End Permissions Tab -->

        <!--  Statistics Tab -->
        <div id="settings_stats"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('Statistics', 'outofthebox'); ?></div>

          <div class="outofthebox-option-title"><?php _e('Google Analytics', 'outofthebox'); ?>
            <div class="outofthebox-onoffswitch">
              <input type='hidden' value='No' name='out_of_the_box_settings[google_analytics]'/>
              <input type="checkbox" name="out_of_the_box_settings[google_analytics]" id="google_analytics" class="outofthebox-onoffswitch-checkbox" <?php echo ($this->settings['google_analytics'] === "Yes") ? 'checked="checked"' : ''; ?> />
              <label class="outofthebox-onoffswitch-label" for="google_analytics"></label>
            </div>
          </div>
          <div class="outofthebox-option-description"><?php _e("Would you like to see some statistics about your files? Out-of-the-Box can send all download/upload events to Google Analytics", "outofthebox"); ?>. <?php _e("If you enable this feature, please make sure you already added your <a href='https://support.google.com/analytics/answer/1008080?hl=en'>Google Analytics web tracking</a> code to your site.", "outofthebox"); ?>.</div>
        </div>
        <!-- End Statistics Tab -->

        <!-- System info Tab -->
        <div id="settings_system"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('System information', 'outofthebox'); ?></div>
          <?php echo $this->get_system_information(); ?>
        </div>
        <!-- End System info -->

        <!-- Help Tab -->
        <div id="settings_help"  class="outofthebox-tab-panel">
          <div class="outofthebox-tab-panel-header"><?php _e('Need help?', 'outofthebox'); ?></div>

          <div class="outofthebox-option-title"><?php _e('Support & Documentation', 'outofthebox'); ?></div>
          <div id="message">
            <p><?php _e('Check the documentation of the plugin in case you encounter any problems or are looking for support.', 'outofthebox'); ?></p>
            <div id='documentation_button' type='button' class='simple-button blue'><?php _e('Open Documentation', 'outofthebox'); ?></div>
          </div>
          <br/>
          <div class="outofthebox-option-title"><?php _e('Reset Cache', 'outofthebox'); ?></div>
          <?php echo $this->get_plugin_reset_box(); ?>

        </div>  
      </div>
      <!-- End Help info -->
    </div>
  </form>
  <script type="text/javascript" >
      jQuery(document).ready(function ($) {
        var media_library;

        $(".outofthebox-accordion").accordion({
          active: false,
          collapsible: true,
          header: ".outofthebox-accordion-title",
          heightStyle: "content",
          classes: {
            "ui-accordion-header": "outofthebox-accordion-top",
            "ui-accordion-header-collapsed": "outofthebox-accordion-collapsed",
            "ui-accordion-content": "outofthebox-accordion-content"
          },
          icons: {
            "header": "fa fa-angle-down",
            "activeHeader": "fa fa-angle-up"
          }
        });

        $('.outofthebox-color-picker').wpColorPicker();
        $('#content_skin_selectbox').ddslick({
          width: '638px',
          background: '#f4f4f4',
          onSelected: function (item) {
            $("#content_skin").val($('#content_skin_selectbox').data('ddslick').selectedData.value);
          }
        });
        $('#lightbox_skin_selectbox').ddslick({
          width: '638px',
          imagePosition: "right",
          background: '#f4f4f4',
          onSelected: function (item) {
            $("#lightbox_skin").val($('#lightbox_skin_selectbox').data('ddslick').selectedData.value);
          }
        });
        $('#mediaplayer_skin_selectbox').ddslick({
          width: '638px',
          imagePosition: "right",
          background: '#f4f4f4',
          onSelected: function (item) {
            $("#mediaplayer_skin").val($('#mediaplayer_skin_selectbox').data('ddslick').selectedData.value);
          }
        });

        $('.upload_button').click(function () {
          var input_field = $(this).prev("input").attr("id");
          media_library = wp.media.frames.file_frame = wp.media({
            title: '<?php echo __('Select your image', 'outofthebox'); ?>',
            button: {
              text: '<?php echo __('Use this Image', 'outofthebox'); ?>'
            },
            multiple: false
          });
          media_library.on("select", function () {
            var attachment = media_library.state().get('selection').first().toJSON();

            var mime = attachment.mime;
            var regex = /^image\/(?:jpe?g|png|gif|svg)$/i;
            var is_image = mime.match(regex)

            if (is_image) {
              $("#" + input_field).val(attachment.url);
              $("#" + input_field).trigger('change');
            }

            $('.upload-remove').click(function () {
              $(this).hide();
              $(this).parent().parent().find(".upload").val('');
              $(this).parent().parent().find(".screenshot").slideUp();
            })
          })
          media_library.open()
        });

        $('.upload-remove').click(function () {
          $(this).hide();
          $(this).parent().parent().find(".upload").val('');
          $(this).parent().parent().find(".screenshot").slideUp();
        })

        $('.default_image_button').click(function () {
          $(this).parent().find(".upload").val($(this).attr('data-default'));
          $('input.upload').trigger('change');
        });

        $('input.upload').change(function () {
          var img = '<img src="' + $(this).val() + '" />'
          img += '<a href="javascript:void(0)" class="upload-remove">' + '<?php echo __('Remove Media', 'outofthebox'); ?>' + "</a>";
          $(this).parent().find(".screenshot").slideDown().html(img);

          var default_button = $(this).parent().find(".default_image_button");
          default_button.hide();
          if ($(this).val() !== default_button.attr('data-default')) {
            default_button.fadeIn();
          }
        });

        $('#shortlinks').on('change', function () {
          $('.option.bitly').hide();
          if ($(this).val() == 'Bit.ly') {
            $('.option.bitly').show();
          }
        });

        $('#authorizeDropbox_button').click(function () {
          var $button = $(this);
          $button.addClass('disabled');
          $button.find('.oftb-spinner').fadeIn();
          $('#authorizeDropbox_options').fadeIn();
          popup = window.open($(this).attr('data-url'), "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=700");
        });

        $('#revokeDropbox_button').click(function () {
          $(this).addClass('disabled');
          $(this).find('.oftb-spinner').show();
          $.ajax({type: "POST",
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
              action: 'outofthebox-revoke',
              _ajax_nonce: '<?php echo $admin_nonce; ?>'
            },
            complete: function (response) {
              location.reload(true)
            },
            dataType: 'json'
          });
        });

        $('#resetDropbox_button').click(function () {
          var $button = $(this);
          $button.addClass('disabled');
          $button.find('.oftb-spinner').show();
          $.ajax({type: "POST",
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
              action: 'outofthebox-reset-cache',
              _ajax_nonce: '<?php echo $admin_nonce; ?>'
            },
            complete: function (response) {
              $button.removeClass('disabled');
              $button.find('.oftb-spinner').hide();
            },
            dataType: 'json'
          });
        });

        $('#updater_button').click(function () {
          popup = window.open('https://www.wpcloudplugins.com/updates/activate.php?init=1&client_url=<?php echo strtr(base64_encode($location), '+/=', '-_~'); ?>&plugin_id=<?php
          echo $this->plugin_id;
          ?>', "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=700");
        });

        $('#check_updates_button').click(function () {
          window.location = '<?php echo admin_url('update-core.php'); ?>';
        });


        $('#root_folder_button').click(function () {
          var $button = $(this);
          $(this).parent().addClass("thickbox_opener");
          $button.addClass('disabled');
          $button.find('.oftb-spinner').show();
          tb_show("Select Folder", '#TB_inline?height=450&amp;width=800&amp;inlineId=oftb-embedded');
        });

        $('#documentation_button').click(function () {
          popup = window.open('<?php echo plugins_url('_documentation/index.html', dirname(__FILE__)); ?>', "_blank");
        });


        $('#save_settings').click(function () {
          var $button = $(this);
          $button.addClass('disabled');
          $button.find('.oftb-spinner').fadeIn();

          $('#outofthebox-options').ajaxSubmit({
            success: function () {
              $button.removeClass('disabled');
              $button.find('.oftb-spinner').fadeOut();

              if (location.hash === '#settings_advanced') {
                location.reload(true);
              }

            },
            error: function () {
              $button.removeClass('disabled');
              $button.find('.oftb-spinner').fadeOut();

              location.reload(true);
            },
          });
          //setTimeout("$('#saveMessage').hide('slow');", 5000);
          return false;
        });
      });


  </script>
</div>