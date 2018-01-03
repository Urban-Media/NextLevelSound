<div class="outofthebox admin-settings">

  <div class="outofthebox-header">
    <div class="outofthebox-logo"><img src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/logo64x64.png" height="64" width="64"/></div>
    <div class="outofthebox-title"><?php _e('File Browser', 'outofthebox'); ?></div>
  </div>

  <div class="outofthebox-panel outofthebox-panel-full">
    <?php
    $processor = new \TheLion\OutoftheBox\Processor($this->get_main());
    $params = array('mode' => 'files',
        'viewrole' => 'all',
        'downloadrole' => 'all',
        'uploadrole' => 'all',
        'upload' => '1',
        'rename' => '1',
        'delete' => '1',
        'addfolder' => '1',
        'edit' => '1',
        'candownloadzip' => '1',
        'showsharelink' => '1',
        'editdescription' => '1');

    $user_folder_backend = $processor->get_setting('userfolder_backend');
    if ($user_folder_backend !== 'No') {
        $params['userfolders'] = $user_folder_backend;

        $private_root_folder = $processor->get_setting('userfolder_backend_auto_root');
        if ($user_folder_backend === 'auto' && !empty($private_root_folder) && isset($private_root_folder['id'])) {
            $params['dir'] = $private_root_folder['id'];

            if (!isset($private_root_folder['view_roles'])) {
                $private_root_folder['view_roles'] = 'none';
            }
            $params['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
        }
    }

    echo $processor->create_from_shortcode($params);
    ?>
  </div>
</div>