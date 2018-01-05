<?php
$settings = (array) get_option('out_of_the_box_settings');

if (
        !(\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) &&
        !(\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_links'])) &&
        !(\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_embedded']))
) {
    die();
}

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'default';

function wp_roles_checkbox($name, $selected = array()) {
    global $wp_roles;
    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }

    $roles = $wp_roles->get_names();


    foreach ($roles as $role_value => $role_name) {
        if (in_array($role_value, $selected) || $selected[0] == 'all') {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo '<div class="outofthebox-option-checkbox">';
        echo '<input class="simple" type="checkbox" name="' . $name . '[]" value="' . $role_value . '" ' . $checked . '>';
        echo '<label for="userfolders_method_auto1" class="outofthebox-option-checkbox-label">' . $role_name . '</label>';
        echo '</div>';
    }
    if (in_array('guest', $selected) || $selected[0] == 'all') {
        $checked = 'checked="checked"';
    } else {
        $checked = '';
    }
    echo '<div class="outofthebox-option-checkbox">';
    echo '<input class="simple" type="checkbox" name="' . $name . '[]" value="guest" ' . $checked . '>';
    echo '<label for="userfolders_method_auto1" class="outofthebox-option-checkbox-label">' . __('Guest', 'outofthebox') . '</label>';
    echo '</div>';
}

$this->load_scripts();
$this->load_styles();
$this->load_custom_css();

function OutoftheBox_remove_all_scripts() {
    global $wp_scripts;
    $wp_scripts->queue = array();

    wp_enqueue_script('jquery-effects-fade');
    wp_enqueue_script('jquery');
    wp_enqueue_script('OutoftheBox');
    wp_enqueue_script('OutoftheBox.tinymce');
}

function OutoftheBox_remove_all_styles() {
    global $wp_styles;
    $wp_styles->queue = array();
    wp_enqueue_style('qtip');
    wp_enqueue_style('OutoftheBox.tinymce');
    wp_enqueue_style('OutoftheBox');
    wp_enqueue_style('Awesome-Font-css');
}

add_action('wp_print_scripts', 'OutoftheBox_remove_all_scripts', 1000);
add_action('wp_print_styles', 'OutoftheBox_remove_all_styles', 1000);

/* Count number of openings for rating dialog */
$counter = get_option('out_of_the_box_shortcode_opened', 0) + 1;
update_option('out_of_the_box_shortcode_opened', $counter);

/* Initialize shortcode vars */
$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : 'files';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
      <?php
      if ($type === 'default') {
          $title = __('Shortcode Builder', 'outofthebox');
          $mcepopup = 'shortcode';
      } else if ($type === 'links') {
          $title = __('Insert direct Links', 'outofthebox');
          $mcepopup = 'links';
      } else if ($type === 'embedded') {
          $title = __('Embed files', 'outofthebox');
          $mcepopup = 'embedded';
      } else if ($type === 'gravityforms') {
          $title = __('Shortcode Builder', 'outofthebox');
          $mcepopup = 'shortcode';
      } else if ($type === 'woocommerce') {
          $title = __('Shortcode Builder', 'outofthebox');
          $mcepopup = 'shortcode';
      }
      ?></title>
    <?php if ($type !== 'gravityforms') { ?>
        <script type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
    <?php } ?>

    <?php wp_print_scripts(); ?>
    <?php wp_print_styles(); ?>
  </head>

  <body class="outofthebox" data-mode="<?php echo $mode; ?>">
    <?php $this->ask_for_review(); ?>

    <form action="#">

      <div class="wrap">
        <div class="outofthebox-header">
          <div class="outofthebox-logo"><img src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/logo64x64.png" height="64" width="64"/></div>
          <div class="outofthebox-form-buttons">
            <?php if ($type === 'default') { ?>
                <div id="get_shortcode" class="simple-button default get_shortcode" name="get_shortcode" title="<?php _e("Get raw Shortcode", 'outofthebox'); ?>"><?php _e("Raw", 'outofthebox'); ?><i class="fa fa-code" aria-hidden="true"></i></div>
                <div id="doinsert"  class="simple-button default insert_shortcode" name="insert"><?php _e("Insert Shortcode", 'outofthebox'); ?>&nbsp;<i class="fa fa-chevron-circle-right" aria-hidden="true"></i></div>
            <?php } elseif ($type === 'links') { ?>
                <div id="doinsert" class="simple-button default insert_links" name="insert"  ><?php _e("Insert Links", 'outofthebox'); ?>&nbsp;<i class="fa fa-chevron-circle-right" aria-hidden="true"></i></div>
            <?php } elseif ($type === 'embedded') { ?>
                <div id="doinsert" class="simple-button default insert_embedded" name="insert" ><?php _e("Embed Files", 'outofthebox'); ?>&nbsp;<i class="fa fa-chevron-circle-right" aria-hidden="true"></i></div>
            <?php } elseif ($type === 'gravityforms') { ?>
                <div id="doinsert" class="simple-button default insert_shortcode_gf" name="insert"><?php _e("Insert Shortcode", 'outofthebox'); ?>&nbsp;<i class="fa fa-chevron-circle-right" aria-hidden="true"></i></div>
            <?php } elseif ($type === 'woocommerce') { ?>
                <div id="doinsert" class="simple-button default insert_shortcode_woocommerce" name="insert"><?php _e("Insert Shortcode", 'outofthebox'); ?>&nbsp;<i class="fa fa-chevron-circle-right" aria-hidden="true"></i></div>
            <?php } ?>
          </div>

          <div class="outofthebox-title"><?php echo $title; ?></div>

        </div>
        <?php
        if ($type === 'links' || $type === 'embedded') {
            echo '<div class="outofthebox-panel outofthebox-panel-full">';
            if ($type === 'embedded') {
                echo "<p>" . __('Please note that the embedded files need to be public (with link)', 'outofthebox') . "</p>";
            }


            $user_folders = $this->settings['userfolder_backend'] === 'No' ? '0' : $this->settings['userfolder_backend'];


            $atts = array(
                'mode' => 'files',
                'showfiles' => '1',
                'upload' => '0',
                'delete' => '0',
                'rename' => '0',
                'addfolder' => '0',
                'showcolumnnames' => '0',
                'viewrole' => 'all',
                'candownloadzip' => '0',
                'showsharelink' => '0',
                'previewinline' => '0',
                'mcepopup' => $mcepopup,
                'includeext' => '*',
                '_random' => 'embed'
            );

            $user_folder_backend = $this->settings['userfolder_backend'];
            if ($user_folder_backend !== 'No') {
                $atts['userfolders'] = $user_folder_backend;

                $private_root_folder = $this->settings['userfolder_backend_auto_root'];
                if ($user_folder_backend === 'auto' && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                    $atts['dir'] = $private_root_folder['id'];

                    if (!isset($private_root_folder['view_roles'])) {
                        $private_root_folder['view_roles'] = 'none';
                    }
                    $atts['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
                }
            }

            echo $this->create_template($atts);
            echo '</div>';
            ?>
            <?php
        } else {
            ?>

            <div id="" class="outofthebox-panel outofthebox-panel-left">
              <div class="outofthebox-nav-header"><?php _e('Shortcode Settings', 'outofthebox'); ?></div>
              <ul class="outofthebox-nav-tabs">
                <li id="settings_general_tab" data-tab="settings_general" class="current"><a href="#settings_general"><span><?php _e('General', 'outofthebox'); ?></span></a></li>
                <li id="settings_folder_tab" data-tab="settings_folders"><a href="#settings_folders"><span><?php _e('Folders', 'outofthebox'); ?></span></a></li>
                <li id="settings_mediafiles_tab" data-tab="settings_mediafiles"><a href="#settings_mediafiles"><span><?php _e('Media Files', 'outofthebox'); ?></span></a></li>
                <li id="settings_layout_tab" data-tab="settings_layout"><a href="#settings_layout"><span><?php _e('Layout', 'outofthebox'); ?></span></a></li>
                <li id="settings_sorting_tab" data-tab="settings_sorting"><a href="#settings_sorting"><span><?php _e('Sorting', 'outofthebox'); ?></span></a></li>
                <li id="settings_advanced_tab" data-tab="settings_advanced"><a href="#settings_advanced"><span><?php _e('Advanced', 'outofthebox'); ?></span></a></li>
                <li id="settings_exclusions_tab" data-tab="settings_exclusions"><a href="#settings_exclusions"><span><?php _e('Exclusions', 'outofthebox'); ?></span></a></li>
                <li id="settings_upload_tab" data-tab="settings_upload"><a href="#settings_upload"><span><?php _e('Upload Box', 'outofthebox'); ?></span></a></li>
                <li id="settings_notifications_tab" data-tab="settings_notifications"><a href="#settings_notifications"><span><?php _e('Notifications', 'outofthebox'); ?></span></a></li>
                <li id="settings_manipulation_tab" data-tab="settings_manipulation"><a href="#settings_manipulation"><span><?php _e('File Manipulation', 'outofthebox'); ?></span></a></li>
                <li id="settings_permissions_tab" data-tab="settings_permissions" class=""><a href="#settings_permissions"><span><?php _e('User Permissions', 'outofthebox'); ?></span></a></li>
              </ul>
            </div>

            <div class="outofthebox-panel outofthebox-panel-right">

              <!-- General Tab -->
              <div id="settings_general" class="outofthebox-tab-panel current">

                <div class="outofthebox-tab-panel-header"><?php _e('General', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Plugin Mode', 'outofthebox'); ?></div>
                <div class="outofthebox-option-description"><?php _e('Select how you want to use Out-of-the-Box in your post or page', 'outofthebox'); ?>:</div>
                <div class="outofthebox-option-radio">
                  <input type="radio" id="files" name="mode" <?php echo (($mode === 'files') ? 'checked="checked"' : ''); ?> value="files" class="mode"/>
                  <label for="files" class="outofthebox-option-radio-label"><?php _e('File browser', 'outofthebox'); ?></label>
                </div>
                <div class="outofthebox-option-radio">
                  <input type="radio" id="upload" name="mode" <?php echo (($mode === 'upload') ? 'checked="checked"' : ''); ?> value="upload" class="mode"/>
                  <label for="upload" class="outofthebox-option-radio-label"><?php _e('Upload Box', 'outofthebox'); ?></label>
                </div>
                <?php if ($type !== 'gravityforms') { ?>
                    <div class="outofthebox-option-radio">
                      <input type="radio" id="gallery" name="mode" <?php echo (($mode === 'gallery') ? 'checked="checked"' : ''); ?> value="gallery" class="mode"/>
                      <label for="gallery" class="outofthebox-option-radio-label"><?php _e('Photo gallery', 'outofthebox'); ?></label>
                    </div>
                    <div class="outofthebox-option-radio">
                      <input type="radio" id="audio" name="mode" <?php echo (($mode === 'audio') ? 'checked="checked"' : ''); ?> value="audio" class="mode"/>
                      <label for="audio" class="outofthebox-option-radio-label"><?php _e('Audio player', 'outofthebox'); ?></label>
                    </div>
                    <div class="outofthebox-option-radio">
                      <input type="radio" id="video" name="mode" <?php echo (($mode === 'video') ? 'checked="checked"' : ''); ?> value="video" class="mode"/>
                      <label for="video" class="outofthebox-option-radio-label"><?php _e('Video player', 'outofthebox'); ?></label>
                    </div>
                    <div class="outofthebox-option-radio">
                      <input type="radio" id="search" name="mode" <?php echo (($mode === 'search') ? 'checked="checked"' : ''); ?> value="search" class="mode"/>
                      <label for="search" class="outofthebox-option-radio-label"><?php _e('Search Box', 'outofthebox'); ?></label>
                    </div>
                <?php } ?>

              </div>
              <!-- End General Tab -->
              <!-- User Folders Tab -->
              <div id="settings_folders" class="outofthebox-tab-panel">

                <div class="outofthebox-tab-panel-header"><?php _e('Folders', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Select start Folder', 'outofthebox'); ?></div>
                <div class="outofthebox-option-description"><?php _e('Select which folder should be used as starting point, or in case the Smart Client Area is enabled should be used for the Private Folders', 'outofthebox'); ?>. <?php _e('Users will not be able to navigate outside this folder', 'outofthebox'); ?>.</div>
                <div class="root-folder">
                  <?php
                  $user_folders = $this->settings['userfolder_backend'] === 'No' ? '0' : $this->settings['userfolder_backend'];

                  $atts = array(
                      'mode' => 'files',
                      'maxheight' => '300px',
                      'filelayout' => 'list',
                      'showfiles' => '1',
                      'filesize' => '0',
                      'filedate' => '0',
                      'upload' => '0',
                      'delete' => '0',
                      'rename' => '0',
                      'addfolder' => '0',
                      'showbreadcrumb' => '1',
                      'showcolumnnames' => '0',
                      'search' => '0',
                      'roottext' => '',
                      'viewrole' => 'all',
                      'downloadrole' => 'none',
                      'candownloadzip' => '0',
                      'showsharelink' => '0',
                      'previewinline' => '0',
                      'mcepopup' => $mcepopup
                  );

                  if (isset($_REQUEST['dir'])) {
                      $atts['startpath'] = $_REQUEST['dir'];
                  }

                  $user_folder_backend = $this->settings['userfolder_backend'];
                  if ($user_folder_backend !== 'No') {
                      $atts['userfolders'] = $user_folder_backend;

                      $private_root_folder = $this->settings['userfolder_backend_auto_root'];
                      if ($user_folder_backend === 'auto' && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                          $atts['dir'] = $private_root_folder['id'];

                          if (!isset($private_root_folder['view_roles'])) {
                              $private_root_folder['view_roles'] = 'none';
                          }
                          $atts['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
                      }
                  }

                  echo $this->create_template($atts);
                  ?>
                </div>

                <br/>
                <div class="outofthebox-tab-panel-header"><?php _e('Smart Client Area', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Use Private Folders', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_linkedfolders" id="OutoftheBox_linkedfolders" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['userfolders'])) ? 'checked="checked"' : ''; ?> data-div-toggle='option-userfolders'/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_linkedfolders"></label>
                  </div>
                </div>

                <div class="outofthebox-option-description">
                  <?php echo sprintf(__('The plugin can easily and securily share documents on your %s with your users/clients', 'outofthebox'), 'outofthebox'); ?>. 
                  <?php _e('This allows your clients to preview, download and manage their documents in their own private folder', 'outofthebox'); ?>.
                  <?php echo sprintf(__('Specific permissions can always be set via %s', 'outofthebox'), '<a href="#" onclick="jQuery(\'li[data-tab=settings_permissions]\').trigger(\'click\')">' . __('User Permissions', 'outofthebox') . '</a>'); ?>. 

                  <?php _e('The Smart Client Area can be useful in some situations, for example', 'outofthebox'); ?>:
                  <ul>
                    <li><?php _e('You want to share documents with your clients privately', 'outofthebox'); ?></li>
                    <li><?php _e('You want your clients, users or guests upload files to their own folder', 'outofthebox'); ?></li>
                    <li><?php _e('You want to give your customers a private folder already filled with some files directly after they register', 'outofthebox'); ?></li>
                  </ul>
                </div>

                <div class="option option-userfolders forfilebrowser forgallery <?php echo (isset($_REQUEST['userfolders'])) ? '' : 'hidden'; ?>">

                  <div class="outofthebox-option-title"><?php _e('Mode', 'outofthebox'); ?></div>
                  <div class="outofthebox-option-description"><?php _e('Do you want to link your users manually to their private or should the plugin handle this automatically for you', 'outofthebox'); ?>.</div>

                  <?php
                  $userfolders = 'auto';
                  if (isset($_REQUEST['userfolders'])) {
                      $userfolders = $_REQUEST['userfolders'];
                  }
                  ?>
                  <div class="outofthebox-option-radio">
                    <input type="radio" id="userfolders_method_manual" name="OutoftheBox_userfolders_method"<?php echo ($userfolders === 'manual') ? 'checked="checked"' : ''; ?> value="manual"/>
                    <label for="userfolders_method_manual" class="outofthebox-option-radio-label"><?php echo sprintf(__('I will link the users manually via %sthis page%s', 'outofthebox'), '<a href="' . admin_url('admin.php?page=OutoftheBox_settings_linkusers') . '" target="_blank">', '</a>'); ?></label>
                  </div>
                  <div class="outofthebox-option-radio">
                    <input type="radio" id="userfolders_method_auto" name="OutoftheBox_userfolders_method" <?php echo ($userfolders === 'auto') ? 'checked="checked"' : ''; ?> value="auto"/>
                    <label for="userfolders_method_auto" class="outofthebox-option-radio-label"><?php _e('Let the plugin automatically manage the Private Folders for me in the folder I have selected above', 'outofthebox'); ?></label>
                  </div>

                  <div class="option-userfolders_auto">
                    <div class="outofthebox-option-title"><?php _e('Template Folder', 'outofthebox'); ?>
                      <div class="outofthebox-onoffswitch">
                        <input type="checkbox" name="OutoftheBox_userfolders_template" id="OutoftheBox_userfolders_template" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['usertemplatedir'])) ? 'checked="checked"' : ''; ?> data-div-toggle='userfolders-template-option'/>
                        <label class="outofthebox-onoffswitch-label" for="OutoftheBox_userfolders_template"></label>
                      </div>
                    </div>
                    <div class="outofthebox-option-description">
                      <?php _e('Newly created Private Folders can be prefilled with files from a template template', 'outofthebox'); ?>. <?php _e('The content of the template folder selected will be copied to the user folder', 'outofthebox'); ?>.
                    </div>

                    <div class="userfolders-template-option <?php echo (isset($_REQUEST['usertemplatedir'])) ? '' : 'hidden'; ?>">
                      <div class="template-folder">
                        <?php
                        $atts = array(
                            'mode' => 'files',
                            'filelayout' => 'list',
                            'maxheight' => '300px',
                            'showfiles' => '1',
                            'filesize' => '0',
                            'filedate' => '0',
                            'upload' => '0',
                            'delete' => '0',
                            'rename' => '0',
                            'addfolder' => '0',
                            'showbreadcrumb' => '1',
                            'showcolumnnames' => '0',
                            'viewrole' => 'all',
                            'downloadrole' => 'none',
                            'candownloadzip' => '0',
                            'showsharelink' => '0',
                            'userfolders' => $user_folders,
                            'mcepopup' => $mcepopup
                        );

                        if (isset($_REQUEST['usertemplatedir'])) {
                            $atts['startpath'] = $_REQUEST['usertemplatedir'];
                        }

                        echo $this->create_template($atts);
                        ?>
                      </div>
                    </div>

                    <div class="outofthebox-option-title"><?php _e('Full Access', 'outofthebox'); ?></div>
                    <div class="outofthebox-option-description"><?php _e('By default only Administrator users will be able to navigate through all Private Folders', 'outofthebox'); ?>. <?php _e('When you want other User Roles to be able do browse to the Private Folders as well, please check them below', 'outofthebox'); ?>.</div>

                    <?php
                    $selected = (isset($_REQUEST['viewuserfoldersrole'])) ? explode('|', $_REQUEST['viewuserfoldersrole']) : array('administrator');
                    wp_roles_checkbox('OutoftheBox_view_user_folders_role', $selected);
                    ?>


                    <div class="outofthebox-option-title"><?php _e('Quota', 'outofthebox'); ?></div>
                    <div class="outofthebox-option-description"><?php _e("Set maximum size of the User Folder (e.g. 10M, 100M, 1G). When the Upload function is enabled, the user will not be able to upload when the limit is reached", "outofthebox"); ?>. <?php _e('Leave this field empty or set it to -1 for unlimited disk space', 'outofthebox'); ?>.</div>
                    <input type="text" name="OutoftheBox_maxuserfoldersize" id="OutoftheBox_maxuserfoldersize" placeholder="e.g. 10M, 100M, 1G" value="<?php echo (isset($_REQUEST['maxuserfoldersize'])) ? $_REQUEST['maxuserfoldersize'] : ''; ?>"/>
                  </div>
                </div>

              </div>
              <!-- End User Folders Tab -->
              <!-- Media Files Tab -->
              <div id="settings_mediafiles"  class="outofthebox-tab-panel">

                <div class="outofthebox-tab-panel-header"><?php _e('Media Files', 'outofthebox'); ?></div>

                <div class="foraudio">
                  <div class="outofthebox-option-title"><?php _e('Provided formats', 'outofthebox'); ?>*</div>
                  <div class="outofthebox-option-description"><?php _e('Select which sort of media files you will provide', 'outofthebox'); ?>. <?php _e('You may provide the same file with different extensions to increase cross-browser support', 'outofthebox'); ?>. <?php _e('Do always supply a mp3 (audio) or m4v/mp4 (video)file to support all browsers', 'outofthebox'); ?>.</div>
                  <?php
                  $mediaextensions = (!isset($_REQUEST['mediaextensions']) || ($mode !== 'audio')) ? array() : explode('|', $_REQUEST['mediaextensions']);
                  ?>

                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_mp3" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('mp3', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='mp3' /><label for="mediaextensions_mp3" class="outofthebox-option-checkbox-label">mp3</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_mp4"  type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('mp4', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='mp4' /><label for="mediaextensions_mp4" class="outofthebox-option-checkbox-label">mp4</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_m4a" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('m4a', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='m4a' /><label for="mediaextensions_m4a" class="outofthebox-option-checkbox-label">m4a</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_ogg"  type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('ogg', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='ogg' /><label for="mediaextensions_ogg" class="outofthebox-option-checkbox-label">ogg</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_oga" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('oga', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='oga' /><label for="mediaextensions_oga" class="outofthebox-option-checkbox-label">oga</label></div>
                </div>        

                <div class="forvideo">
                  <div class="outofthebox-option-title"><?php _e('Provided formats', 'outofthebox'); ?>*</div>
                  <div class="outofthebox-option-description"><?php _e('Select which sort of media files you will provide', 'outofthebox'); ?>. <?php _e('You may provide the same file with different extensions to increase cross-browser support', 'outofthebox'); ?>. <?php _e('Do always supply a mp3 (audio) or m4v/mp4 (video)file to support all browsers', 'outofthebox'); ?>.</div>
                  <?php
                  $mediaextensions = (!isset($_REQUEST['mediaextensions']) || ($mode !== 'video')) ? array() : explode('|', $_REQUEST['mediaextensions']);
                  ?>

                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_mp4" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('mp4', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='mp4' /><label for="mediaextensions_mp4" class="outofthebox-option-checkbox-label">mp4</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_m4v"  type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('m4v', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='m4v' /><label for="mediaextensions_m4v" class="outofthebox-option-checkbox-label">m4v</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_ogg" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('ogg', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='ogg' /><label for="mediaextensions_ogg" class="outofthebox-option-checkbox-label">ogg</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_ogv"  type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('ogv', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='ogv' /><label for="mediaextensions_ogv" class="outofthebox-option-checkbox-label">ogv</label></div>
                  <div class="outofthebox-option-checkbox" style="display: inline-block;"><input id="mediaextensions_webmv" type="checkbox" name="OutoftheBox_mediaextensions[]" <?php echo (in_array('webmv', $mediaextensions)) ? 'checked="checked"' : ''; ?> value='webmv' /><label for="mediaextensions_webmv" class="outofthebox-option-checkbox-label">webmv</label></div>
                </div>  

                <div class="outofthebox-option-title"><?php _e('Auto Play', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_autoplay" id="OutoftheBox_autoplay" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['autoplay']) && $_REQUEST['autoplay'] === '1') ? 'checked="checked"' : ''; ?>>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_autoplay"></label>
                  </div>
                </div>

                <div class="outofthebox-option-title"><?php _e('Show Playlist on start', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_showplaylist" id="OutoftheBox_showplaylist" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['hideplaylist']) && $_REQUEST['hideplaylist'] === '1') ? '' : 'checked="checked"'; ?>>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showplaylist"></label>
                  </div>
                </div>   

                <div class="outofthebox-option-title"><?php _e('Download Button', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_linktomedia" id="OutoftheBox_linktomedia" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['linktomedia']) && $_REQUEST['linktomedia'] === '1') ? 'checked="checked"' : ''; ?>>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_linktomedia"></label>
                  </div>
                </div>   

                <div class="outofthebox-option-title"><?php _e('Purchase Button', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_mediapurchase" id="OutoftheBox_mediapurchase" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['linktoshop']) && $_REQUEST['linktoshop'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle='webshop-options'>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_mediapurchase"></label>
                  </div>
                </div>  


                <div class="option webshop-options <?php echo (isset($_REQUEST['linktoshop'])) ? '' : 'hidden'; ?>">
                  <div class="outofthebox-option-title"><?php _e('Link to webshop', 'outofthebox'); ?></div>  
                  <input class="outofthebox-option-input-large" type="text" name="OutoftheBox_linktoshop" id="OutoftheBox_linktoshop" placeholder="https://www.yourwebshop.com/" value="<?php echo (isset($_REQUEST['linktoshop'])) ? $_REQUEST['linktoshop'] : ''; ?>"/>
                </div>

              </div>
              <!-- End Media Files Tab -->

              <!-- Layout Tab -->
              <div id="settings_layout"  class="outofthebox-tab-panel">

                <div class="outofthebox-tab-panel-header"><?php _e('Layout', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Plugin container width', 'outofthebox'); ?></div>
                <div class="outofthebox-option-description"><?php _e("Set max width for the Out-of-the-Box container", "outofthebox"); ?>. <?php _e("You can use pixels or percentages, eg '360px', '480px', '70%'", "outofthebox"); ?>. <?php echo __('Leave empty for default value', 'outofthebox'); ?>.</div>
                <input type="text" name="OutoftheBox_max_width" id="OutoftheBox_max_width" placeholder="100%" value="<?php echo (isset($_REQUEST['maxwidth'])) ? $_REQUEST['maxwidth'] : ''; ?>"/>


                <div class="forfilebrowser forgallery forsearch">
                  <div class="outofthebox-option-title"><?php _e('Plugin container height', 'outofthebox'); ?></div>
                  <div class="outofthebox-option-description"><?php _e("Set max height for the Out-of-the-Box container", "outofthebox"); ?>. <?php _e("You can use pixels or percentages, eg '360px', '480px', '70%'", "outofthebox"); ?>. <?php _e('Leave empty for default value', 'outofthebox'); ?>.</div>
                  <input type="text" name="OutoftheBox_max_height" id="OutoftheBox_max_height" placeholder="auto" value="<?php echo (isset($_REQUEST['maxheight'])) ? $_REQUEST['maxheight'] : ''; ?>"/>
                </div>

                <div class=" forfilebrowser forgallery">
                  <div class="outofthebox-option-title"><?php _e('Show header', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_breadcrumb" id="OutoftheBox_breadcrumb" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showbreadcrumb']) && $_REQUEST['showbreadcrumb'] === '0') ? '' : 'checked="checked"'; ?> data-div-toggle="header-options"/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_breadcrumb"></label>
                    </div>
                  </div>  

                  <div class="option header-options <?php echo (isset($_REQUEST['showbreadcrumb']) && $_REQUEST['showbreadcrumb'] === '0') ? 'hidden' : ''; ?>">
                    <div class="outofthebox-option-title"><?php _e('Show refresh button', 'outofthebox'); ?>
                      <div class="outofthebox-onoffswitch">
                        <input type="checkbox" name="OutoftheBox_showrefreshbutton" id="OutoftheBox_showrefreshbutton" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showrefreshbutton']) && $_REQUEST['showrefreshbutton'] === '0') ? '' : 'checked="checked"'; ?>/>
                        <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showrefreshbutton"></label>
                      </div>
                    </div>
                    <div class="outofthebox-option-description"><?php _e('Add a refresh button in the header so users can refresh the file list and pull changes', 'outofthebox'); ?></div>

                    <div class="outofthebox-option-title"><?php _e('Breadcrumb text for top folder', 'outofthebox'); ?></div>
                    <input type="text" name="OutoftheBox_roottext" id="OutoftheBox_roottext" placeholder="<?php _e('Start', 'outofthebox'); ?>" value="<?php echo (isset($_REQUEST['roottext'])) ? $_REQUEST['roottext'] : ''; ?>"/>
                  </div>
                </div>

                <div class=" forfilebrowser forgallery forsearch">
                  <div class="option forfilebrowser forsearch forlistonly">
                    <div class="outofthebox-option-title"><?php _e('Show columnnames', 'outofthebox'); ?>
                      <div class="outofthebox-onoffswitch">
                        <input type="checkbox" name="OutoftheBox_showcolumnnames" id="OutoftheBox_showcolumnnames" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showcolumnnames']) && $_REQUEST['showcolumnnames'] === '0') ? '' : 'checked="checked"'; ?> data-div-toggle="columnnames-options"/>
                        <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showcolumnnames"></label>
                      </div>
                    </div>
                    <div class="outofthebox-option-description"><?php _e('Display the columnnames of the date and filesize in the List View of the File Browser', 'outofthebox'); ?></div>

                    <div class="columnnames-options">
                      <div class="option-filesize">
                        <div class="outofthebox-option-title"><?php _e('Show file size', 'outofthebox'); ?>
                          <div class="outofthebox-onoffswitch">
                            <input type="checkbox" name="OutoftheBox_filesize" id="OutoftheBox_filesize" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['filesize']) && $_REQUEST['filesize'] === '0') ? '' : 'checked="checked"'; ?>/>
                            <label class="outofthebox-onoffswitch-label" for="OutoftheBox_filesize"></label>
                          </div>
                        </div>
                        <div class="outofthebox-option-description"><?php _e('Display or Hide column with file sizes in List view', 'outofthebox'); ?></div>
                      </div>

                      <div class="option-filedate">
                        <div class="outofthebox-option-title"><?php _e('Show last modified date', 'outofthebox'); ?>
                          <div class="outofthebox-onoffswitch">
                            <input type="checkbox" name="OutoftheBox_filedate" id="OutoftheBox_filedate" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['filedate']) && $_REQUEST['filedate'] === '0') ? '' : 'checked="checked"'; ?>/>
                            <label class="outofthebox-onoffswitch-label" for="OutoftheBox_filedate"></label>
                          </div>
                        </div>
                        <div class="outofthebox-option-description"><?php _e('Display or Hide column with last modified date in List view', 'outofthebox'); ?></div>
                      </div>
                    </div>
                  </div>

                  <div class="option forfilebrowser forsearch">
                    <div class="outofthebox-option-title"><?php _e('Show file extension', 'outofthebox'); ?>
                      <div class="outofthebox-onoffswitch">
                        <input type="checkbox" name="OutoftheBox_showext" id="OutoftheBox_showext" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showext']) && $_REQUEST['showext'] === '0') ? '' : 'checked="checked"'; ?>/>
                        <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showext"></label>
                      </div>
                    </div>
                    <div class="outofthebox-option-description"><?php _e('Display or Hide the file extensions', 'outofthebox'); ?></div>

                    <div class="outofthebox-option-title"><?php _e('Show files', 'outofthebox'); ?>
                      <div class="outofthebox-onoffswitch">
                        <input type="checkbox" name="OutoftheBox_showfiles" id="OutoftheBox_showfiles" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showfiles']) && $_REQUEST['showfiles'] === '0') ? '' : 'checked="checked"'; ?>/>
                        <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showfiles"></label>
                      </div>
                    </div>
                    <div class="outofthebox-option-description"><?php _e('Display or Hide files', 'outofthebox'); ?></div>
                  </div>

                  <div class="outofthebox-option-title"><?php _e('Show folders', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_showfolders" id="OutoftheBox_showfolders" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showfolders']) && $_REQUEST['showfolders'] === '0') ? '' : 'checked="checked"'; ?>/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showfolders"></label>
                    </div>
                  </div>
                  <div class="outofthebox-option-description"><?php _e('Display or Hide child folders', 'outofthebox'); ?></div>

                  <div class="showfiles-options">
                    <div class="outofthebox-option-title"><?php _e('Amount of files', 'outofthebox'); ?>
                    </div>
                    <div class="outofthebox-option-description"><?php _e('Number of files to show', 'outofthebox'); ?>. <?php _e('Can be used for instance to only show the last 5 updated documents', 'outofthebox'); ?>. <?php _e("Leave this field empty or set it to -1 for no limit", 'outofthebox'); ?></div>
                    <input type="text" name="OutoftheBox_maxfiles" id="OutoftheBox_maxfiles" placeholder="-1" value="<?php echo (isset($_REQUEST['maxfiles'])) ? $_REQUEST['maxfiles'] : ''; ?>"/>
                  </div>
                </div>

                <div class="option forgallery">
                  <div class="outofthebox-option-title"><?php _e('Show file names', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_showfilenames" id="OutoftheBox_showfilenames" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showfilenames"']) && $_REQUEST['showfilenames"'] === '1') ? 'checked="checked"' : ''; ?>/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showfilenames"></label>
                    </div>
                  </div>
                  <div class="outofthebox-option-description"><?php _e('Display or Hide the file names in the gallery', 'outofthebox'); ?></div>

                  <div class="outofthebox-option-title"><?php _e('Gallery row height', 'outofthebox'); ?></div>
                  <div class="outofthebox-option-description"><?php _e("The ideal height you want your grid rows to be", 'outofthebox'); ?>. <?php _e("It won't set it exactly to this as plugin adjusts the row height to get the correct width", 'outofthebox'); ?>. <?php _e('Leave empty for default value', 'outofthebox'); ?> (200px).</div>
                  <input type="text" name="OutoftheBox_targetHeight" id="OutoftheBox_targetHeight" placeholder="200" value="<?php echo (isset($_REQUEST['targetheight'])) ? $_REQUEST['targetheight'] : ''; ?>"/>

                  <div class="outofthebox-option-title"><?php _e('Number of images lazy loaded', 'outofthebox'); ?></div>
                  <div class="outofthebox-option-description"><?php _e("Number of images to be loaded each time", 'outofthebox'); ?>. <?php _e("Set to 0 to load all images at once", 'outofthebox'); ?>.</div>
                  <input type="text" name="OutoftheBox_maximage" id="OutoftheBox_maximage" placeholder="25" value="<?php echo (isset($_REQUEST['maximages'])) ? $_REQUEST['maximages'] : ''; ?>"/>

                  <div class="outofthebox-option-title"><?php _e('Show Folder Thumbnails in Gallery', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_folderthumbs" id="OutoftheBox_folderthumbs" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['folderthumbs']) && $_REQUEST['folderthumbs'] === '0') ? '' : 'checked="checked"'; ?> />
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_folderthumbs"></label>
                    </div>
                  </div>
                  <div class="outofthebox-option-description"><?php _e("Do you want to show thumbnails for the Folders in the gallery mode?", 'outofthebox'); ?> <?php _e("Please note, when enabled the loading performance can drop proportional to the number of folders present in the Gallery", 'outofthebox'); ?>.</div>

                  <div class="outofthebox-option-title"><?php _e('Slideshow', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_slideshow" id="OutoftheBox_slideshow" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['slideshow']) && $_REQUEST['slideshow'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="slideshow-options"/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_slideshow"></label>
                    </div>
                  </div>

                  <div class="slideshow-options">                  
                    <div class="outofthebox-option-description"><?php _e('Enable or disable the Slideshow mode in the Lightbox', 'outofthebox'); ?></div>                  
                    <div class="outofthebox-option-title"><?php _e('Delay between cycles (ms)', 'outofthebox'); ?></div>
                    <div class="outofthebox-option-description"><?php _e('Delay between cycles in milliseconds, the default is 5000', 'outofthebox'); ?>.</div>
                    <input type="text" name="OutoftheBox_pausetime" id="OutoftheBox_pausetime" placeholder="5000" value="<?php echo (isset($_REQUEST['pausetime'])) ? $_REQUEST['pausetime'] : ''; ?>"/>
                  </div>
                </div>
              </div>
              <!-- End Layout Tab -->

              <!-- Sorting Tab -->
              <div id="settings_sorting"  class="outofthebox-tab-panel">

                <div class="outofthebox-tab-panel-header"><?php _e('Sorting', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Sort field', 'outofthebox'); ?></div>
                <?php
                $sortfield = (!isset($_REQUEST['sortfield'])) ? 'name' : $_REQUEST['sortfield'];
                ?>
                <div class="outofthebox-option-radio">
                  <input type="radio" id="name" name="sort_field" <?php echo ($sortfield === 'name') ? 'checked="checked"' : ''; ?> value="name"/>
                  <label for="name" class="outofthebox-option-radio-label"><?php _e('Name', 'outofthebox'); ?></label>
                </div>
                <div class="outofthebox-option-radio">
                  <input type="radio" id="size" name="sort_field" <?php echo ($sortfield === 'size') ? 'checked="checked"' : ''; ?> value="size" />
                  <label for="size" class="outofthebox-option-radio-label"><?php _e('Size', 'outofthebox'); ?></label>
                </div>
                <div class="outofthebox-option-radio">
                  <input type="radio" id="modified" name="sort_field" <?php echo ($sortfield === 'modified') ? 'checked="checked"' : ''; ?> value="modified" />
                  <label for="modified" class="outofthebox-option-radio-label"><?php _e('Date modified', 'outofthebox'); ?></label>
                </div>
                <div class="outofthebox-option-radio">
                  <input type="radio" id="shuffle" name="sort_field" <?php echo ($sortfield === 'shuffle') ? 'checked="checked"' : ''; ?> value="shuffle" />
                  <label for="shuffle" class="outofthebox-option-radio-label"><?php _e('Shuffle/Random', 'outofthebox'); ?></label>
                </div>

                <div class="option-sort-field">
                  <div class="outofthebox-option-title"><?php _e('Sort order', 'outofthebox'); ?></div>

                  <?php
                  $sortorder = (isset($_REQUEST['sortorder']) && $_REQUEST['sortorder'] === 'desc') ? 'desc' : 'asc';
                  ?>
                  <div class="outofthebox-option-radio">
                    <input type="radio" id="asc" name="sort_order" <?php echo ($sortorder === 'asc') ? 'checked="checked"' : ''; ?> value="asc"/>
                    <label for="asc" class="outofthebox-option-radio-label"><?php _e('Ascending', 'outofthebox'); ?></label>
                  </div>
                  <div class="outofthebox-option-radio">
                    <input type="radio" id="desc" name="sort_order" <?php echo ($sortorder === 'desc') ? 'checked="checked"' : ''; ?> value="desc"/>
                    <label for="desc" class="outofthebox-option-radio-label"><?php _e('Descending', 'outofthebox'); ?></label>
                  </div>
                </div>
              </div>
              <!-- End Sorting Tab -->
              <!-- Advanced Tab -->
              <div id="settings_advanced"  class="outofthebox-tab-panel">
                <div class="outofthebox-tab-panel-header"><?php _e('Advanced', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Inline Preview', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_previewinline" id="OutoftheBox_previewinline" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['previewinline']) && $_REQUEST['previewinline'] === '0') ? '' : 'checked="checked"'; ?> data-div-toggle="preview-options"/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_previewinline"></label>
                  </div>
                </div>
                <div class="outofthebox-option-description"><?php _e('Open preview inside a lightbox or open in a new window', 'outofthebox'); ?></div>

                <div class="option preview-options <?php echo (isset($_REQUEST['previewinline']) && $_REQUEST['previewinline'] === '0') ? 'hidden' : ''; ?>">
                  <div class="outofthebox-option-title"><?php _e('Enable Google pop out Button', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_canpopout" id="OutoftheBox_canpopout"  class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['canpopout']) && $_REQUEST['canpopout'] === '1') ? 'checked="checked"' : ''; ?>/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_canpopout"></label>
                    </div>
                  </div>
                  <div class="outofthebox-option-description"><?php _e('Disables the Google Pop Out button which is visible in the inline preview for a couple of file formats', 'outofthebox'); ?>. </div>
                </div>

                <div class="outofthebox-option-title"><?php _e('Allow Searching', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_search" id="OutoftheBox_search" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['search']) && $_REQUEST['search'] === '0') ? '' : 'checked="checked"'; ?> data-div-toggle="search-options"/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_search"></label>
                  </div>
                </div>
                <div class="outofthebox-option-description"><?php _e('The search function allows your users to find files by filename and content (when files are indexed)', 'outofthebox'); ?></div>

                <div class="option forfilebrowser forgallery">
                  <div class="option search-options <?php echo (isset($_REQUEST['search']) && $_REQUEST['search'] === '1') ? '' : 'hidden'; ?>">
                    <div class="outofthebox-option-title"><?php _e('Perform Full-Text search', 'outofthebox'); ?>
                      <div class="outofthebox-onoffswitch">
                        <input type="checkbox" name="OutoftheBox_search_field" id="OutoftheBox_search_field"  class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['searchcontents']) && $_REQUEST['searchcontents'] === '1') ? 'checked="checked"' : ''; ?>/>
                        <label class="outofthebox-onoffswitch-label" for="OutoftheBox_search_field"></label>
                      </div>
                    </div>
                    <div class="outofthebox-option-description"><?php _e('Business Accounts only', 'outofthebox'); ?>. </div>
                  </div>
                </div>

                <div class="outofthebox-option-title"><?php _e('Allow Sharing', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_showsharelink" id="OutoftheBox_showsharelink" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['showsharelink']) && $_REQUEST['showsharelink'] === '1') ? 'checked="checked"' : ''; ?>/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_showsharelink"></label>
                  </div>
                </div>
                <div class="outofthebox-option-description"><?php _e('Allow users to generate permanent shared links to the files', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Allow ZIP Download', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_candownloadzip" id="OutoftheBox_candownloadzip" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['candownloadzip']) && $_REQUEST['candownloadzip'] === '1') ? 'checked="checked"' : ''; ?>/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_candownloadzip"></label>
                  </div>
                </div>
                <div class="outofthebox-option-description"><?php _e('Allow users to download multiple files at once', 'outofthebox'); ?></div>

              </div>
              <!-- End Advanced Tab -->
              <!-- Exclusions Tab -->
              <div id="settings_exclusions"  class="outofthebox-tab-panel">
                <div class="outofthebox-tab-panel-header"><?php _e('Exclusions', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Only show files with those extensions', 'outofthebox'); ?>:</div>
                <div class="outofthebox-option-description"><?php echo __('Add extensions separated with | e.g. (jpg|png|gif)', 'outofthebox') . '. ' . __('Leave empty to show all files', 'outofthebox'); ?>.</div>
                <input type="text" name="OutoftheBox_ext" id="OutoftheBox_ext" class="outofthebox-option-input-large" value="<?php echo (isset($_REQUEST['ext'])) ? $_REQUEST['ext'] : ''; ?>"/>

                <div class="outofthebox-option-title"><?php _e('Only show the following files or folders', 'outofthebox'); ?>:</div>
                <div class="outofthebox-option-description"><?php echo __('Add files or folders by name separated with | e.g. (file1.jpg|long folder name)', 'outofthebox'); ?>.</div>
                <input type="text" name="OutoftheBox_include" id="OutoftheBox_include" class="outofthebox-option-input-large" value="<?php echo (isset($_REQUEST['include'])) ? $_REQUEST['include'] : ''; ?>"/>

                <div class="outofthebox-option-title"><?php _e('Hide the following files or folders', 'outofthebox'); ?>:</div>
                <div class="outofthebox-option-description"><?php echo __('Add files or folders by name separated with | e.g. (file1.jpg|long folder name)', 'outofthebox'); ?>.</div>
                <input type="text" name="OutoftheBox_exclude" id="OutoftheBox_exclude"  class="outofthebox-option-input-large" value="<?php echo (isset($_REQUEST['exclude'])) ? $_REQUEST['exclude'] : ''; ?>"/>

              </div>
              <!-- End Exclusions Tab -->

              <!-- Upload Tab -->
              <div id="settings_upload"  class="outofthebox-tab-panel">

                <div class="outofthebox-tab-panel-header"><?php _e('Upload Box', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Allow Upload', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_upload" id="OutoftheBox_upload" data-div-toggle="upload-options" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1') ? 'checked="checked"' : ''; ?>/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_upload"></label>
                  </div>
                </div>
                <div class="outofthebox-option-description"><?php _e('Allow users to upload files', 'outofthebox'); ?>. <?php echo sprintf(__('You can select which Users Roles should be able to upload via %s', 'outofthebox'), '<a href="#" onclick="jQuery(\'li[data-tab=settings_permissions]\').trigger(\'click\')">' . __('User Permissions', 'outofthebox') . '</a>'); ?>.</div>

                <div class="option upload-options <?php echo (isset($_REQUEST['upload']) && $_REQUEST['upload'] === '1' && in_array($mode, array('files', 'upload', 'gallery'))) ? '' : 'hidden'; ?>">

                  <div class="outofthebox-option-title"><?php _e('Overwrite existing files', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_overwrite" id="OutoftheBox_overwrite"  class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['overwrite']) && $_REQUEST['overwrite'] === '1') ? 'checked="checked"' : ''; ?>/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_overwrite"></label>
                    </div>
                  </div>
                  <div class="outofthebox-option-description"><?php _e('Overwrite already existing files or auto-rename the new uploaded files', 'outofthebox'); ?>. </div>

                  <div class="outofthebox-option-title"><?php _e('Restrict file extensions', 'outofthebox'); ?></div>
                  <div class="outofthebox-option-description"><?php echo __('Add extensions separated with | e.g. (jpg|png|gif)', 'outofthebox') . ' ' . __('Leave empty for no restricion', 'outofthebox', 'outofthebox'); ?>.</div>
                  <input type="text" name="OutoftheBox_upload_ext" id="OutoftheBox_upload_ext" value="<?php echo (isset($_REQUEST['uploadext'])) ? $_REQUEST['uploadext'] : ''; ?>"/>

                  <div class="outofthebox-option-title"><?php _e('Max uploads per session', 'outofthebox'); ?></div>
                  <div class="outofthebox-option-description"><?php echo __('Number of maximum uploads per upload session', 'outofthebox') . ' ' . __('Leave empty for no restricion', 'outofthebox'); ?>.</div>
                  <input type="text" name="OutoftheBox_maxnumberofuploads" id="OutoftheBox_maxnumberofuploads" placeholder="-1" value="<?php echo (isset($_REQUEST['maxnumberofuploads'])) ? $_REQUEST['maxnumberofuploads'] : ''; ?>"/>

                  <div class="outofthebox-option-title"><?php _e('Maximum file size', 'outofthebox'); ?></div>
                  <?php
                  $max_size_bytes = min(\TheLion\OutoftheBox\Helpers::return_bytes(ini_get('post_max_size')), \TheLion\OutoftheBox\Helpers::return_bytes(ini_get('upload_max_filesize')));
                  $max_size_string = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($max_size_bytes);


                  /* Convert bytes in version before 1.8 to MB */
                  $max_size_value = (isset($_REQUEST['maxfilesize']) ? $_REQUEST['maxfilesize'] : '');
                  if (!empty($max_size_value) && ctype_digit($max_size_value)) {
                      $max_size_value = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($max_size_value);
                  }
                  ?>
                  <div class="outofthebox-option-description"><?php _e('Max filesize for uploading in bytes', 'outofthebox'); ?>. <?php _e('Leave empty for server maximum', 'outofthebox'); ?> (<?php echo $max_size_string; ?>).</div>
                  <input type="text" name="OutoftheBox_maxfilesize" id="OutoftheBox_maxfilesize" placeholder="<?php echo $max_size_string; ?>" value="<?php echo $max_size_value; ?>"/>

                </div>
              </div>
              <!-- End Upload Tab -->

              <!-- Notifications Tab -->
              <div id="settings_notifications"  class="outofthebox-tab-panel">

                <div class="outofthebox-tab-panel-header"><?php _e('Notifications', 'outofthebox'); ?></div>

                <div class="outofthebox-option-title"><?php _e('Download email notification', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_notificationdownload" id="OutoftheBox_notificationdownload" class="outofthebox-onoffswitch-checkbox"  <?php echo (isset($_REQUEST['notificationdownload']) && $_REQUEST['notificationdownload'] === '1') ? 'checked="checked"' : ''; ?>/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_notificationdownload"></label>
                  </div>
                </div>

                <div class="outofthebox-option-title"><?php _e('Upload email notification', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_notificationupload" id="OutoftheBox_notificationupload" class="outofthebox-onoffswitch-checkbox"  <?php echo (isset($_REQUEST['notificationupload']) && $_REQUEST['notificationupload'] === '1') ? 'checked="checked"' : ''; ?>/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_notificationupload"></label>
                  </div>
                </div>
                <div class="outofthebox-option-title"><?php _e('Delete email notification', 'outofthebox'); ?>
                  <div class="outofthebox-onoffswitch">
                    <input type="checkbox" name="OutoftheBox_notificationdeletion" id="OutoftheBox_notificationdeletion" class="outofthebox-onoffswitch-checkbox"  <?php echo (isset($_REQUEST['notificationdeletion']) && $_REQUEST['notificationdeletion'] === '1') ? 'checked="checked"' : ''; ?>/>
                    <label class="outofthebox-onoffswitch-label" for="OutoftheBox_notificationdeletion"></label>
                  </div>
                </div>

                <div class="outofthebox-option-title"><?php _e('Receiver', 'outofthebox'); ?></div>
                <div class="outofthebox-option-description"><?php _e('On which email address would you like to receive the notification? You can use <code>%admin_email%</code> and <code>%user_email%</code> (user that executes the action)', 'outofthebox'); ?>.</div>
                <input type="text" name="OutoftheBox_notification_email" id="OutoftheBox_notification_email" class="outofthebox-option-input-large" placeholder="<?php echo get_site_option('admin_email'); ?>" value="<?php echo (isset($_REQUEST['notificationemail'])) ? $_REQUEST['notificationemail'] : ''; ?>" />

              </div>
              <!-- End Notifications Tab -->

              <!-- Manipulation Tab -->
              <div id="settings_manipulation"  class="outofthebox-tab-panel">
                <div class="outofthebox-tab-panel-header"><?php _e('File Manipulation', 'outofthebox'); ?></div>

                <div class="option forfilebrowser forgallery">

                  <div class="outofthebox-option-title"><?php _e('Rename files and folders', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_rename" id="OutoftheBox_rename" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['rename']) && $_REQUEST['rename'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="rename-options"/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_rename"></label>
                    </div>
                  </div>

                  <div class="outofthebox-option-title"><?php _e('Move files and folders', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_move" id="OutoftheBox_move" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['move']) && $_REQUEST['move'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="move-options"/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_move"></label>
                    </div>
                  </div>

                  <div class="outofthebox-option-title"><?php _e('Delete files and folders', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_delete" id="OutoftheBox_delete" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['delete']) && $_REQUEST['delete'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="delete-options"/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_delete"></label>
                    </div>
                  </div>
                </div>

                <div class="option forfilebrowser forgallery">
                  <div class="outofthebox-option-title"><?php _e('Create new folders', 'outofthebox'); ?>
                    <div class="outofthebox-onoffswitch">
                      <input type="checkbox" name="OutoftheBox_addfolder" id="OutoftheBox_addfolder" class="outofthebox-onoffswitch-checkbox" <?php echo (isset($_REQUEST['addfolder']) && $_REQUEST['addfolder'] === '1') ? 'checked="checked"' : ''; ?> data-div-toggle="addfolder-options"/>
                      <label class="outofthebox-onoffswitch-label" for="OutoftheBox_addfolder"></label>
                    </div>
                  </div>
                </div>

                <br/><br/>

                <div class="outofthebox-option-description">
                  <?php echo sprintf(__('Select via %s which User Roles are able to perform the actions', 'outofthebox'), '<a href="#" onclick="jQuery(\'li[data-tab=settings_permissions]\').trigger(\'click\')">' . __('User Permissions', 'outofthebox') . '</a>'); ?>.
                </div>

              </div>
              <!-- End Manipulation Tab -->
              <!-- Permissions Tab -->
              <div id="settings_permissions"  class="outofthebox-tab-panel">
                <div class="outofthebox-tab-panel-header"><?php _e('User Permissions', 'outofthebox'); ?></div>

                <div class="option forfilebrowser forupload forgallery foraudio forvideo forsearch outofthebox-permissions-box">
                  <div class="outofthebox-option-title"><?php _e('Who can see the plugin', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['viewrole'])) ? explode('|', $_REQUEST['viewrole']) : array('administrator', 'author', 'contributor', 'editor', 'subscriber', 'pending', 'guest');
                  wp_roles_checkbox('OutoftheBox_view_role', $selected);
                  ?>

                </div>

                <div class="option forfilebrowser outofthebox-permissions-box">
                  <div class="outofthebox-option-title"><?php _e('Who can preview', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['previewrole'])) ? explode('|', $_REQUEST['previewrole']) : array('all');
                  wp_roles_checkbox('OutoftheBox_preview_role', $selected);
                  ?>
                </div>

                <div class="option forfilebrowser forupload forgallery foraudio forvideo outofthebox-permissions-box">
                  <div class="outofthebox-option-title"><?php _e('Who can download', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['downloadrole'])) ? explode('|', $_REQUEST['downloadrole']) : array('all');
                  wp_roles_checkbox('OutoftheBox_download_role', $selected);
                  ?>

                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery forupload upload-options">
                  <div class="outofthebox-option-title"><?php _e('Who can upload', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['uploadrole'])) ? explode('|', $_REQUEST['uploadrole']) : array('administrator', 'author', 'contributor', 'editor', 'subscriber');
                  wp_roles_checkbox('OutoftheBox_upload_role', $selected);
                  ?>
                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery forsearch rename-options ">
                  <div class="outofthebox-option-title"><?php _e('Who can rename files', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['renamefilesrole'])) ? explode('|', $_REQUEST['renamefilesrole']) : array('administrator', 'author', 'contributor', 'editor');
                  wp_roles_checkbox('OutoftheBox_renamefiles_role', $selected);
                  ?>
                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery forsearch rename-options ">
                  <div class="outofthebox-option-title"><?php _e('Who can rename folders', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['renamefoldersrole'])) ? explode('|', $_REQUEST['renamefoldersrole']) : array('administrator', 'author', 'contributor', 'editor');
                  wp_roles_checkbox('OutoftheBox_renamefolders_role', $selected);
                  ?>
                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery forsearch move-options">
                  <div class="outofthebox-option-title"><?php _e('Who can move files and folders', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['moverole'])) ? explode('|', $_REQUEST['moverole']) : array('administrator', 'editor');
                  wp_roles_checkbox('OutoftheBox_move_role', $selected);
                  ?>
                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery forsearch delete-options ">
                  <div class="outofthebox-option-title"><?php _e('Who can delete files', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['deletefilesrole'])) ? explode('|', $_REQUEST['deletefilesrole']) : array('administrator', 'author', 'contributor', 'editor');
                  wp_roles_checkbox('OutoftheBox_deletefiles_role', $selected);
                  ?>
                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery forsearch delete-options ">
                  <div class="outofthebox-option-title"><?php _e('Who can delete folders', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['deletefoldersrole'])) ? explode('|', $_REQUEST['deletefoldersrole']) : array('administrator', 'author', 'contributor', 'editor');
                  wp_roles_checkbox('OutoftheBox_deletefolders_role', $selected);
                  ?>
                </div>

                <div class="option outofthebox-permissions-box forfilebrowser forgallery addfolder-options ">
                  <div class="outofthebox-option-title"><?php _e('Who can create new folders', 'outofthebox'); ?></div>
                  <?php
                  $selected = (isset($_REQUEST['addfolderrole'])) ? explode('|', $_REQUEST['addfolderrole']) : array('administrator', 'author', 'contributor', 'editor');
                  wp_roles_checkbox('OutoftheBox_addfolder_role', $selected);
                  ?>
                </div>
              </div>
              <!-- End Permissions Tab -->

            </div>
            <?php
        }
        ?>

        <div class="footer">

        </div>
      </div>
    </form>
  </body>
</html>