<?php
if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class GFOutoftheBoxAddOn extends GFAddOn {

        protected $_version = "1.0";
        protected $_min_gravityforms_version = "1.9";
        protected $_slug = "outoftheboxaddon";
        protected $_path = "out-of-the-box/includes/OutoftheBox_GravityForms.php";
        protected $_full_path = __FILE__;
        protected $_title = "Gravity Forms Out-of-the-Box Add-On";
        protected $_short_title = "Out-of-the-Box Add-On";

        public function init() {
            parent::init();

            if (isset($this->_min_gravityforms_version) && !$this->is_gravityforms_supported($this->_min_gravityforms_version)) {
                return;
            }

            /* Add a Out-of-the-Box button to the advanced to the field editor */
            add_filter('gform_add_field_buttons', array($this, "outofthebox_field"));
            add_filter('admin_enqueue_scripts', array($this, "outofthebox_extra_scripts"));

            /* Now we execute some javascript technicalitites for the field to load correctly */
            add_action("gform_editor_js", array($this, "gform_editor_js"));
            add_filter('gform_field_input', array($this, "outofthebox_input"), 10, 5);

            /* Add a custom setting to the field */
            add_action("gform_field_standard_settings", array($this, "outofthebox_settings"), 10, 2);

            /* Adds title to the custom field */
            add_filter('gform_field_type_title', array($this, 'outofthebox_title'), 10, 2);

            /* Filter to add the tooltip for the field */
            add_filter('gform_tooltips', array($this, 'add_outofthebox_tooltips'));

            /* Save some data for this field */
            add_action('gform_pre_submission', array($this, 'outofthebox_pre_submission_handler'));

            /* Display values in a proper way */
            add_filter('gform_entry_field_value', array($this, 'outofthebox_entry_field_value'), 10, 4);
            add_filter('gform_entries_field_value', array($this, 'outofthebox_entries_field_value'), 10, 4);
            add_filter('gform_merge_tag_filter', array($this, 'outofthebox_merge_tag_filter'), 10, 5);

            /* Integrate with Gravity PDF */
            if (class_exists("GFPDF_Core")) {
                add_action('gfpdf_post_save_pdf', array($this, 'outofthebox_post_save_pdf'), 10, 5);
                add_filter('gfpdf_form_settings_advanced', array($this, 'outofthebox_add_pdf_setting'), 10, 1);
            }
        }

        public function outofthebox_extra_scripts() {
            if (RGForms::is_gravity_page()) {
                add_thickbox();
            }
        }

        public function outofthebox_field($field_groups) {
            foreach ($field_groups as &$group) {
                if ($group["name"] == "advanced_fields") {
                    $group["fields"][] = array(
                        'class' => 'button',
                        'value' => 'Out-of-the-Box',
                        'date-type' => 'outofthebox',
                        'onclick' => "StartAddField('outofthebox');"
                    );
                    break;
                }
            }
            return $field_groups;
        }

        public function gform_editor_js() {
            ?>
            <script type='text/javascript'>
                jQuery(document).ready(function ($) {
                  /* Which settings field should be visible for our custom field*/
                  fieldSettings["outofthebox"] = ".label_setting, .description_setting, .admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting, .label_placement_setting, .outofthebox_setting, .conditional_logic_field_setting, .conditional_logic_page_setting, .conditional_logic_nextbutton_setting"; //this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.

                  /* binding to the load field settings event to initialize */
                  $(document).bind("gform_load_field_settings", function (event, field, form) {
                    if (field["OutoftheBoxShortcode"] !== undefined) {
                      jQuery("#field_outofthebox").val(field["OutoftheBoxShortcode"]);
                    } else {
                      /* Default value */
                      var defaultvalue = '[outofthebox mode="upload" upload="1" uploadrole="all" userfolders="auto"]';
                      jQuery("#field_outofthebox").val(defaultvalue);
                    }
                  });

                  /* Shortcode Generator Popup */
                  $('.OutoftheBox-GF-shortcodegenerator').click(function () {
                    var shortcode = jQuery("#field_outofthebox").val();
                    shortcode = shortcode.replace('[outofthebox ', '').replace('"]', '');
                    var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
                    tb_show("Build Shortcode for Form", ajaxurl + '?action=outofthebox-getpopup&' + query + '&type=gravityforms&TB_iframe=true&height=600&width=800');
                  });

                });
            </script>
            <?php
        }

        public function outofthebox_input($input, $field, $value, $lead_id, $form_id) {
            if ($field->type == "outofthebox") {
                if (!$this->is_form_editor()) {
                    $return = do_shortcode($field->OutoftheBoxShortcode);
                    return $return;
                } else {
                    $style = 'background: #176cff url(' . OUTOFTHEBOX_ROOTPATH . '/css/images/shortcode_image.png) no-repeat center center; height: 150px;  width: 99%;  border: 1px solid #aaa;  outline: 0;  cursor: pointer;';
                    return '<div style="' . $style . '"></div>';
                }
            }
            return $input;
        }

        public function outofthebox_settings($position, $form_id) {
            if ($position == 1430) {
                ?>
                <li class="outofthebox_setting field_setting">
                  <label for="field_outofthebox">Out-of-the-Box Shortcode <?php echo gform_tooltip("form_field_outofthebox"); ?></label>
                  <a href="#" class='button-primary OutoftheBox-GF-shortcodegenerator '><?php _e('Build your Out-of-the-Box shortcode', 'outofthebox'); ?></a>
                  <input type="text" id="field_outofthebox" class="fieldwidth-3" size="35" disabled="disabled"/>
                  <br/><small>Missing a Out-of-the-Box Gravity Form feature? Please let me <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/requests/new" target="_blank">know</a>!</small>
                </li>
                <?php
            }
        }

        function outofthebox_title($title, $field_type) {
            if ($field_type === 'outofthebox') {
                return __('Out-of-the-Box Upload', 'outofthebox');
            }
            return $title;
        }

        public function add_outofthebox_tooltips($tooltips) {
            $tooltips["form_field_outofthebox"] = "<h6>Out-of-the-Box Shortcode</h6>" . __('Build here your Out-of-the-Box shortcode', 'outofthebox');
            return $tooltips;
        }

        public function outofthebox_pre_submission_handler($form) {
            /* Get information uploaded files from hidden input */
            $filesinput = rgpost('fileupload-filelist');
            $uploadedfiles = json_decode($filesinput);

            if (($uploadedfiles !== NULL) && (count($uploadedfiles) > 0)) {
                /* Fill our custom field with the details of our upload session */
                foreach ($form['fields'] as &$field) {
                    if ($field->type === 'outofthebox') {
                        $_POST["input_{$field->id}"] = $filesinput;
                        break;
                    }
                }
            }

            return $form;
        }

        public function outofthebox_entry_field_value($value, $field, $lead, $form) {
            if ($field->type !== "outofthebox") {
                return $value;
            }

            return $this->renderUploadedFiles($value);
        }

        public function outofthebox_entries_field_value($value, $form_id, $field_id, $entry) {
            $form = RGFormsModel::get_form_meta($form_id);

            if (is_array($form["fields"])) {
                foreach ($form["fields"] as $field) {
                    if ($field->type === 'outofthebox' && $field_id == $field->id) {
                        if (!empty($value)) {
                            return $this->renderUploadedFiles(html_entity_decode($value));
                        }
                    }
                }
            }

            return $value;
        }

        public function outofthebox_set_export_values($value, $form_id, $field_id, $lead) {
            $form = RGFormsModel::get_form_meta($form_id);

            if (is_array($form["fields"])) {
                foreach ($form["fields"] as $field) {
                    if ($field->type === 'outofthebox' && $field_id == $field->id) {
                        return $this->renderUploadedFiles(html_entity_decode($value), false);
                    }
                }
            }

            return $value;
        }

        public function outofthebox_merge_tag_filter($value, $merge_tag, $modifier, $field, $rawvalue) {

            if ($field->type == 'outofthebox') {
                return $this->renderUploadedFiles(html_entity_decode($value));
            } else {
                return $value;
            }
        }

        public function renderUploadedFiles($data, $ashtml = true) {

            $uploadedfiles = json_decode($data);

            if (($uploadedfiles !== NULL) && (count((array) $uploadedfiles) > 0)) {
                /* Fill our custom field with the details of our upload session */
                $html = sprintf(__('%d file(s) uploaded:', 'outofthebox'), count((array) $uploadedfiles));
                $html .= ($ashtml) ? '<ul>' : "\r\n";

                foreach ($uploadedfiles as $fileid => $file) {
                    $html .= ($ashtml) ? '<li><a href="' . urldecode($file->link) . '">' : "";
                    $html .= $file->path;
                    $html .= ($ashtml) ? '</a>' : "";
                    $html .= ' (' . $file->size . ')';
                    $html .= ($ashtml) ? '</li>' : "\r\n";
                }

                $html .= ($ashtml) ? '</ul>' : "";
            } else {
                return $data;
            }

            return $html;
        }

        /*
         * GravityPDF 
         * Basic configuration in Form Settings -> PDF:
         * 
         * Always Save PDF = YES
         * [DROPBOX] Export PDF = YES
         * [DROPBOX] Path = Full path where the PDFs need to be stored
         */

        public function outofthebox_add_pdf_setting($fields) {
            $fields['outofthebox_save_to_dropbox'] = array(
                'id' => 'outofthebox_save_to_dropbox',
                'name' => '[DROPBOX] Export PDF',
                'desc' => 'Save the created PDF to Dropbox',
                'type' => 'radio',
                'options' => array(
                    'Yes' => __('Yes'),
                    'No' => __('No'),
                ),
                'std' => __('No'),
            );
            $fields['outofthebox_save_to_dropbox_path'] = array(
                'id' => 'outofthebox_save_to_dropbox_path',
                'name' => '[DROPBOX] Path',
                'desc' => 'Full path where the PDFs need to be stored. E.g. /path/to/folder',
                'type' => 'text',
                'std' => '',
            );

            return $fields;
        }

        public function outofthebox_post_save_pdf($pdf_path, $filename, $settings, $entry, $form) {

            global $OutoftheBox;
            $processor = new \TheLion\OutoftheBox\Processor($OutoftheBox);
            $upload = new \TheLion\OutoftheBox\Upload($processor);

            if (!isset($settings['outofthebox_save_to_dropbox']) || $settings['outofthebox_save_to_dropbox'] === "No") {
                return false;
            }

            $upload_path = \TheLion\OutoftheBox\Helpers::clean_folder_path($settings['outofthebox_save_to_dropbox_path'] . '/' . $filename);
            try {
                $result = $upload->do_upload_to_dropbox($pdf_path, $upload_path);
            } catch (Exception $ex) {
                return false;
            }

            return $result;
        }

    }

    $GFOutoftheBoxAddOn = new GFOutoftheBoxAddOn();
    /* This filter isn't fired if inside class */
    add_filter('gform_export_field_value', array($GFOutoftheBoxAddOn, 'outofthebox_set_export_values'), 10, 4);
}