<?php

if (!defined('WP_UNINSTALL_PLUGIN'))
  wp_die(__('Plugin uninstallation can not be executed in this fashion.'));

global $wpdb;

delete_option('vimeography_pro_db_version');
delete_option('vimeography_pro_access_token');

$wpdb->query('DROP TABLE ' . $wpdb->prefix . "vimeography_pro_meta");