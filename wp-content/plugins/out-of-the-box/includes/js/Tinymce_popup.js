jQuery(document).ready(function ($) {
  'use strict';

  /* Tabs*/
  $('ul.outofthebox-nav-tabs li:not(".disabled")').click(function () {
    if ($(this).hasClass('disabled')) {
      return false;
    }
    var tab_id = $(this).attr('data-tab');

    $('ul.outofthebox-nav-tabs  li').removeClass('current');
    $('.outofthebox-tab-panel').removeClass('current');

    $(this).addClass('current');
    $("#" + tab_id).addClass('current');
    var hash = location.hash.replace('#', '');
    location.hash = tab_id;
    window.scrollTo(0, 0);
  });
  if (location.hash) {
    jQuery("ul.outofthebox-nav-tabs " + location.hash + "_tab").trigger('click');
  }

  /* Fix for not scrolling popup*/
  if (/Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    var parent = $(tinyMCEPopup.getWin().document);

    if (parent.find('#safari_fix').length === 0) {
      parent.find('.mceWrapper iframe').wrap(function () {
        return $('<div id="safari_fix"/>').css({
          'width': "100%",
          'height': "100%",
          'overflow': 'auto',
          '-webkit-overflow-scrolling': 'touch'
        });
      });
    }
  }

  var mode = $('body').attr('data-mode');


  $("input[name=mode]:radio").change(function () {

    $('.forfilebrowser, .forgallery, .foraudio, .forvideo, .forsearch').hide();
    $("#OutoftheBox_linkedfolders").trigger('change');

    $('#settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab, #settings_mediafiles_tab, #settings_layout_tab, #settings_sorting_tab, #settings_exclusions_tab').removeClass('disabled');
    $('.download-options').show();

    mode = $(this).val();
    switch (mode) {
      case 'files':
        $('.forfilebrowser').not('.hidden').show();
        $('#settings_mediafiles_tab').addClass('disabled');
        break;

      case'upload':
        $('.forfilebrowser').not('.hidden').show();
        $('#settings_upload_tab, #settings_notifications_tab').removeClass('disabled');
        $('#settings_mediafiles_tab, #settings_layout_tab, #settings_sorting_tab, #settings_advanced_tab, #settings_exclusions_tab, #settings_manipulation_tab').addClass('disabled');
        $('.download-options').hide();
        $('#OutoftheBox_upload').prop("checked", true).trigger('change');
        $('#OutoftheBox_notificationdownload, #OutoftheBox_notificationdeletion').closest('.option').hide();
        break;

      case 'gallery':
        $('.forgallery').show();
        $('#settings_mediafiles_tab').addClass('disabled');
        $('#OutoftheBox_upload_ext, #OutoftheBox_include_ext').val('gif|jpg|jpeg|png|bmp');
        break;

      case 'search':
        $('.forsearch').not('.hidden').show();
        $('#settings_mediafiles_tab').addClass('disabled');
        $('#settings_upload_tab').addClass('disabled');
        $('#OutoftheBox_search_field').prop("checked", true).trigger('change');
        break;

      case 'audio':
        $('.foraudio').show();
        $('.root-folder').show();
        $('#settings_mediafiles_tab').removeClass('disabled');
        $('#settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab').addClass('disabled');
        break;

      case 'video':
        $('.forvideo').show();
        $('.root-folder').show();
        $('#settings_mediafiles_tab').removeClass('disabled');
        $('#settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab').addClass('disabled');
        break;
    }

    $("#OutoftheBox_breadcrumb, #OutoftheBox_showcolumnnames, #OutoftheBox_mediapurchase, #OutoftheBox_search, #OutoftheBox_showfiles, #OutoftheBox_slideshow, #OutoftheBox_upload, #OutoftheBox_upload_convert, #OutoftheBox_rename, #OutoftheBox_move, #OutoftheBox_editdescription, #OutoftheBox_delete, #OutoftheBox_addfolder").trigger('change');
    $('input[name=OutoftheBox_file_layout]:radio:checked').trigger('change').prop('checked', true);
    $('#OutoftheBox_linkedfolders').trigger('change');
  });

  $("input[name=OutoftheBox_file_layout]:radio").change(function () {
    switch ($(this).val()) {
      case 'grid':
        $('.columnnames-options, .option-filesize, .option-filedate').hide();
        break;
      case 'list':
        $('.columnnames-options, .option-filesize, .option-filedate').show();
        break;
    }
  });

  $('[data-div-toggle]').change(function () {
    var toggleelement = '.' + $(this).attr('data-div-toggle');

    if ($(this).is(":checkbox")) {
      if ($(this).is(":checked")) {
        $(toggleelement).fadeIn().removeClass('hidden');
      } else {
        $(toggleelement).fadeOut().addClass('hidden');
      }
    } else if ($(this).is("select")) {
      if ($(this).val() === $(this).attr('data-div-toggle-value')) {
        $(toggleelement).fadeIn().removeClass('hidden');
      } else {
        $(toggleelement).fadeOut().addClass('hidden');
      }
    }
  });

  $("#OutoftheBox_linkedfolders").change(function () {
    $('input[name=OutoftheBox_userfolders_method]:radio:checked').trigger('change').prop('checked', true);
  });

  $("input[name=OutoftheBox_userfolders_method]:radio").change(function () {
    var is_checked = $("#OutoftheBox_linkedfolders").is(":checked");

    $('.root-folder').show();
    switch ($(this).val()) {
      case 'manual':
        if (is_checked) {
          $('.root-folder').hide();
        }
        $('.option-userfolders_auto').hide().addClass('hidden');
        break;
      case 'auto':
        $('.root-folder').show();
        $('.option-userfolders_auto').show().removeClass('hidden');
        break;
    }
  });

  $("input[name=sort_field]:radio").change(function () {
    switch ($(this).val()) {
      case 'shuffle':
        $('.option-sort-field').hide();
        break;
      default:
        $('.option-sort-field').show();
        break;
    }
  });


  $('.outofthebox .get_shortcode').click(showRawShortcode);
  $(".outofthebox  .insert_links").click(createDirectLinks);
  $(".outofthebox .insert_embedded").click(insertEmbedded);
  $('.outofthebox  .insert_shortcode').click(function (event) {
    insertOutoftheBoxShortCode(event)
  });
  $('.outofthebox  .insert_shortcode_gf').click(function (event) {
    insertOutoftheBoxShortCodeGF(event)
  });

  $('.outofthebox  .insert_shortcode_woocommerce').click(function (event) {
    insertOutoftheBoxShortCodeWC(event)
  });

  $(".OutoftheBox img.preloading").unveil(200, $(".OutoftheBox .ajax-filelist"), function () {
    $(this).load(function () {
      $(this).removeClass('preloading');
    });
  });

  /* Initialise from shortcode */
  $('input[name=mode]:radio:checked').trigger('change').prop('checked', true)

  function createShortcode() {

    var dir = $(".root-folder .current-folder-raw").text(),
            linkedfolders = $('#OutoftheBox_linkedfolders').prop("checked"),
            show_files = $('#OutoftheBox_showfiles').prop("checked"),
            max_files = $('#OutoftheBox_maxfiles').val(),
            show_folders = $('#OutoftheBox_showfolders').prop("checked"),
            ext = $('#OutoftheBox_ext').val(),
            show_filesize = $('#OutoftheBox_filesize').prop("checked"),
            show_filedate = $('#OutoftheBox_filedate').prop("checked"),
            show_ext = $('#OutoftheBox_showext').prop("checked"),
            show_columnnames = $('#OutoftheBox_showcolumnnames').prop("checked"),
            candownloadzip = $('#OutoftheBox_candownloadzip').prop("checked"),
            canpopout = $('#OutoftheBox_canpopout').prop("checked"),
            showsharelink = $('#OutoftheBox_showsharelink').prop("checked"),
            showrefreshbutton = $('#OutoftheBox_showrefreshbutton').prop("checked"),
            show_breadcrumb = $('#OutoftheBox_breadcrumb').prop("checked"),
            breadcrumb_roottext = $('#OutoftheBox_roottext').val(),
            show_root = $('#OutoftheBox_rootname').prop("checked"),
            search = $('#OutoftheBox_search').prop("checked"),
            search_field = $('#OutoftheBox_search_field').prop("checked"),
            search_from = $('#OutoftheBox_searchfrom').prop("checked"),
            previewinline = $('#OutoftheBox_previewinline').prop("checked"),
            force_download = $('#OutoftheBox_forcedownload').prop("checked"),
            include = $('#OutoftheBox_include').val(),
            exclude = $('#OutoftheBox_exclude').val(),
            sort_field = $("input[name=sort_field]:radio:checked").val(),
            sort_order = $("input[name=sort_order]:radio:checked").val(),
            crop = $('#OutoftheBox_crop').prop("checked"),
            slideshow = $('#OutoftheBox_slideshow').prop("checked"),
            pausetime = $('#OutoftheBox_pausetime').val(),
            show_filenames = $('#OutoftheBox_showfilenames').prop("checked"),
            maximages = $('#OutoftheBox_maximage').val(),
            target_height = $('#OutoftheBox_targetHeight').val(),
            folder_thumbs = $('#OutoftheBox_folderthumbs').prop("checked"),
            max_width = $('#OutoftheBox_max_width').val(),
            max_height = $('#OutoftheBox_max_height').val(),
            upload = $('#OutoftheBox_upload').prop("checked"),
            overwrite = $('#OutoftheBox_overwrite').prop("checked"),
            upload_ext = $('#OutoftheBox_upload_ext').val(),
            maxfilesize = $('#OutoftheBox_maxfilesize').val(),
            maxnumberofuploads = $('#OutoftheBox_maxnumberofuploads').val(),
            rename = $('#OutoftheBox_rename').prop("checked"),
            move = $('#OutoftheBox_move').prop("checked"),
            can_delete = $('#OutoftheBox_delete').prop("checked"),
            can_addfolder = $('#OutoftheBox_addfolder').prop("checked"),
            notification_download = $('#OutoftheBox_notificationdownload').prop("checked"),
            notification_upload = $('#OutoftheBox_notificationupload').prop("checked"),
            notification_deletion = $('#OutoftheBox_notificationdeletion').prop("checked"),
            notification_emailaddress = $('#OutoftheBox_notification_email').val(),
            use_template_dir = $('#OutoftheBox_userfolders_template').prop("checked"),
            template_dir = $(".template-folder .OutoftheBox.files .current-folder-raw").text(),
            maxuserfoldersize = $('#OutoftheBox_maxuserfoldersize').val(),
            view_role = readCheckBoxes("input[name='OutoftheBox_view_role[]']"),
            preview_role = readCheckBoxes("input[name='OutoftheBox_preview_role[]']"),
            download_role = readCheckBoxes("input[name='OutoftheBox_download_role[]']"),
            upload_role = readCheckBoxes("input[name='OutoftheBox_upload_role[]']"),
            renamefiles_role = readCheckBoxes("input[name='OutoftheBox_renamefiles_role[]']"),
            renamefolders_role = readCheckBoxes("input[name='OutoftheBox_renamefolders_role[]']"),
            move_role = readCheckBoxes("input[name='OutoftheBox_move_role[]']"),
            deletefiles_role = readCheckBoxes("input[name='OutoftheBox_deletefiles_role[]']"),
            deletefolders_role = readCheckBoxes("input[name='OutoftheBox_deletefolders_role[]']"),
            addfolder_role = readCheckBoxes("input[name='OutoftheBox_addfolder_role[]']"),
            view_user_folders_role = readCheckBoxes("input[name='OutoftheBox_view_user_folders_role[]']"),
            mediaextensions = readCheckBoxes("input[name='OutoftheBox_mediaextensions[]']"),
            autoplay = $('#OutoftheBox_autoplay').prop("checked"),
            showplaylist = $('#OutoftheBox_showplaylist').prop("checked"),
            linktomedia = $('#OutoftheBox_linktomedia').prop("checked"),
            mediapurchase = $('#OutoftheBox_mediapurchase').prop("checked"),
            linktoshop = $('#OutoftheBox_linktoshop').val();



    var data = '';

    if (OutoftheBox_vars.shortcodeRaw === '1') {
      data += '[raw]';
    }

    data += '[outofthebox ';


    if (dir !== '/' && dir !== '') {
      if (linkedfolders) {
        if ($("input[name=OutoftheBox_userfolders_method]:radio:checked").val() !== 'manual') {
          data += 'dir="' + dir + '" ';
        }
      } else {
        data += 'dir="' + dir + '" ';
      }
    }

    if (max_width !== '') {
      if (max_width.indexOf("px") !== -1 || max_width.indexOf("%") !== -1) {
        data += 'maxwidth="' + max_width + '" ';
      } else {
        data += 'maxwidth="' + parseInt(max_width) + '" ';
      }
    }

    if (max_height !== '') {
      if (max_height.indexOf("px") !== -1 || max_height.indexOf("%") !== -1) {
        data += 'maxheight="' + max_height + '" ';
      } else {
        data += 'maxheight="' + parseInt(max_height) + '" ';
      }
    }

    data += 'mode="' + $("input[name=mode]:radio:checked").val() + '" ';

    if (ext !== '') {
      data += 'ext="' + ext + '" ';
    }

    if (include !== '') {
      data += 'include="' + include + '" ';
    }
    if (exclude !== '') {
      data += 'exclude="' + exclude + '" ';
    }

    if (view_role !== 'administrator|editor|author|contributor|subscriber|guest') {
      data += 'viewrole="' + view_role + '" ';
    }

    if (sort_field !== 'name') {
      data += 'sortfield="' + sort_field + '" ';
    }

    if (sort_field !== 'shuffle' && sort_order !== 'asc') {
      data += 'sortorder="' + sort_order + '" ';
    }

    if (linkedfolders === true) {
      var method = $("input[name=OutoftheBox_userfolders_method]:radio:checked").val();
      data += 'userfolders="' + method + '" ';

      if (method === 'auto' && use_template_dir === true && template_dir !== '') {
        data += 'usertemplatedir="' + template_dir + '" ';
      }

      if (view_user_folders_role !== 'administrator') {
        data += 'viewuserfoldersrole="' + view_user_folders_role + '" ';
      }
    }

    if (mode === 'upload') {
      data += 'downloadrole="none" ';
    } else if (download_role !== 'administrator|editor|author|contributor|subscriber|pending|guest') {
      data += 'downloadrole="' + download_role + '" ';
    }


    var mode = $("input[name=mode]:radio:checked").val();
    switch (mode) {
      case 'audio':
      case 'video':

        if (mediaextensions === '') {
          $('#settings_mediafiles_tab a').trigger('click');
          $(".mediaextensions").css("color", "red");
          return false;
        }
        data += 'mediaextensions="' + mediaextensions + '" ';

        if (autoplay === true) {
          data += 'autoplay="1" ';
        }

        if (showplaylist === false) {
          data += 'hideplaylist="1" ';
        }

        if (linktomedia === true) {
          data += 'linktomedia="1" ';
        }

        if (mediapurchase === true && linktoshop !== '') {
          data += 'linktoshop="' + linktoshop + '" ';
        }

        break;

      case 'files':
      case 'gallery':
      case 'upload':
      case 'search':
        if (mode === 'gallery') {

          if (show_filenames === true) {
            data += 'showfilenames="1" ';
          }

          if (maximages !== '') {
            data += 'maximages="' + maximages + '" ';
          }

          if (folder_thumbs === false) {
            data += 'folderthumbs="0" ';
          }

          if (target_height !== '') {
            data += 'targetheight="' + target_height + '" ';
          }

          if (slideshow === true) {
            data += 'slideshow="1" ';
            if (pausetime !== '') {
              data += 'pausetime="' + pausetime + '" ';
            }
          }
        }

        if (mode === 'files' || mode === 'search') {
          if (show_files === false) {
            data += 'showfiles="0" ';
          }
          if (show_folders === false) {
            data += 'showfolders="0" ';
          }
          if (show_filesize === false) {
            data += 'filesize="0" ';
          }

          if (show_filedate === false) {
            data += 'filedate="0" ';
          }

          if (show_ext === false) {
            data += 'showext="0" ';
          }

          if (force_download === true) {
            data += 'forcedownload="1" ';
          }

          if (canpopout === true) {
            data += 'canpopout="1" ';
          }

          if (show_columnnames === false) {
            data += 'showcolumnnames="0" ';
          }

          if (preview_role !== 'all') {
            data += 'previewrole="' + preview_role + '" ';
          }
        }

        if (max_files !== '-1' && max_files !== '') {
          data += 'maxfiles="' + max_files + '" ';
        }

        if (previewinline === false) {
          data += 'previewinline="0" ';
        }

        if (candownloadzip === true) {
          data += 'candownloadzip="1" ';
        }

        if (showsharelink === true) {
          data += 'showsharelink="1" ';
        }

        if (showrefreshbutton === false) {
          data += 'showrefreshbutton="0" ';
        }

        if (search === false && mode !== 'search') {
          data += 'search="0" ';
        } else {
          if (search_field === true) {
            data += 'searchcontents="1" ';
          }

          if (search_from === true) {
            data += 'searchfrom="selectedroot" ';
          }
        }

        if (show_breadcrumb === true) {
          if (show_root === true) {
            data += 'showroot="1" ';
          }
          if (breadcrumb_roottext !== '') {
            data += 'roottext="' + breadcrumb_roottext + '" ';
          }
        } else {
          data += 'showbreadcrumb="0" ';
        }

        if (notification_download === true || notification_upload === true || notification_deletion === true) {
          if (notification_emailaddress !== '') {
            data += 'notificationemail="' + notification_emailaddress + '" ';
          }
        }

        if (notification_download === true) {
          data += 'notificationdownload="1" ';
        }

        if (upload === true) {
          data += 'upload="1" ';

          if (upload_role !== 'administrator|editor|author|contributor|subscriber') {
            data += 'uploadrole="' + upload_role + '" ';
          }
          if (maxfilesize !== '') {
            data += 'maxfilesize="' + maxfilesize + '" ';
          }

          if (maxnumberofuploads !== '-1' && maxnumberofuploads !== '0' && maxnumberofuploads !== '') {
            data += 'maxnumberofuploads="' + maxnumberofuploads + '" ';
          }

          if (overwrite === true) {
            data += 'overwrite="1" ';
          }

          if (upload_ext !== '') {
            data += 'uploadext="' + upload_ext + '" ';
          }

          if (notification_upload === true) {
            data += 'notificationupload="1" ';
          }

          if (maxuserfoldersize !== '-1' && maxuserfoldersize !== '') {
            data += 'maxuserfoldersize="' + maxuserfoldersize + '" ';
          }
        }

        if (rename === true) {
          data += 'rename="1" ';

          if (renamefiles_role !== 'administrator|editor') {
            data += 'renamefilesrole="' + renamefiles_role + '" ';
          }
          if (renamefolders_role !== 'administrator|editor') {
            data += 'renamefoldersrole="' + renamefolders_role + '" ';
          }
        }

        if (move === true) {
          data += 'move="1" ';

          if (move_role !== 'administrator|editor') {
            data += 'moverole="' + move_role + '" ';
          }
        }

        if (can_delete === true) {
          data += 'delete="1" ';

          if (deletefiles_role !== 'administrator|editor') {
            data += 'deletefilesrole="' + deletefiles_role + '" ';
          }
          if (deletefolders_role !== 'administrator|editor') {
            data += 'deletefoldersrole="' + deletefolders_role + '" ';
          }

          if (notification_deletion === true) {
            data += 'notificationdeletion="1" ';
          }
        }

        if (can_addfolder === true) {
          data += 'addfolder="1" ';

          if (addfolder_role !== 'administrator|editor') {
            data += 'addfolderrole="' + addfolder_role + '" ';
          }
        }



        break;
    }

    data += ']';

    if (OutoftheBox_vars.shortcodeRaw === '1') {
      data += '[/raw]';
    }

    return data;

  }

  function insertOutoftheBoxShortCode(event) {
    var data = createShortcode();
    event.preventDefault();

    if (data !== false) {
      tinyMCEPopup.execCommand('mceInsertContent', false, data);
      // Refocus in window
      if (tinyMCEPopup.isWindow)
        window.focus();
      tinyMCEPopup.editor.focus();
      tinyMCEPopup.close();
    }
  }

  function insertOutoftheBoxShortCodeGF(event) {
    event.preventDefault();

    var data = createShortcode();
    if (data !== false) {
      $('#field_outofthebox', window.parent.document).val(data);
      window.parent.SetFieldProperty('OutoftheBoxShortcode', data);
      window.parent.tb_remove();
    }
  }

  function insertOutoftheBoxShortCodeWC(event) {
    event.preventDefault();

    var data = createShortcode();
    if (data !== false) {
      $('#outofthebox_upload_box_shortcode', window.parent.document).val(data);
      window.parent.tb_remove();
    }
  }


  function showRawShortcode() {
    /* Close any open modal windows */
    $('#outofthebox-modal-action').remove();
    var shortcode = createShortcode();
    /* Build the Delete Dialog */

    var modalbuttons = '';
    modalbuttons += '<button class="simple-button blue outofthebox-modal-copy-btn" type="button" title="' + OutoftheBox_vars.str_copy_to_clipboard_title + '" >' + OutoftheBox_vars.str_copy_to_clipboard_title + '</button>';
    var modalheader = $('<a tabindex="0" class="close-button" title="' + OutoftheBox_vars.str_close_title + '" onclick="modal_action.close();"><i class="fa fa-times fa-lg" aria-hidden="true"></i></a></div>');
    var modalbody = $('<div class="outofthebox-modal-body" tabindex="0" ><strong>' + shortcode + '</strong></div>');
    var modalfooter = $('<div class="outofthebox-modal-footer"><div class="outofthebox-modal-buttons">' + modalbuttons + '</div></div>');
    var modaldialog = $('<div id="outofthebox-modal-action" class="OutoftheBox outofthebox-modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
    $('body').append(modaldialog);
    $('#outofthebox-modal-action .modal-content').append(modalheader, modalbody, modalfooter);

    /* Set the button actions */
    $('#outofthebox-modal-action .outofthebox-modal-copy-btn').unbind('click');
    $('#outofthebox-modal-action .outofthebox-modal-copy-btn').click(function () {

      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val(shortcode).select();
      document.execCommand("copy");
      $temp.remove();

    });

    /* Open the Dialog and load the images inside it */
    var modal_action = new RModal(document.getElementById('outofthebox-modal-action'), {
      dialogOpenClass: 'animated slideInDown',
      dialogCloseClass: 'animated slideOutUp',
      escapeClose: true
    });
    document.addEventListener('keydown', function (ev) {
      modal_action.keydown(ev);
    }, false);
    modal_action.open();
    window.modal_action = modal_action;

    return false;
  }

  function createDirectLinks() {
    var listtoken = $(".OutoftheBox.files").attr('data-token'),
            lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path'),
            entries = $(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']:checked").closest('.entry').map(function () {
      return $(this).attr('data-name');
    }).get();

    if (entries.length === 0) {
      if (tinyMCEPopup.isWindow)
        window.focus();
      tinyMCEPopup.editor.focus();
      tinyMCEPopup.close();
    }

    $.ajax({
      type: "POST",
      url: OutoftheBox_vars.ajax_url,
      data: {
        action: 'outofthebox-create-link',
        listtoken: listtoken,
        lastpath: lastpath,
        entries: entries,
        _ajax_nonce: OutoftheBox_vars.createlink_nonce
      },
      beforeSend: function () {
        $(".OutoftheBox .loading").height($(".OutoftheBox .ajax-filelist").height());
        $(".OutoftheBox .loading").fadeTo(400, 0.8);
        $(".OutoftheBox .insert_links").attr('disabled', 'disabled');
      },
      complete: function () {
        $(".OutoftheBox .loading").fadeOut(400);
        $(".OutoftheBox .insert_links").removeAttr('disabled');
      },
      success: function (response) {
        if (response !== null) {
          if (response.links !== null && response.links.length > 0) {

            var data = '<table>';

            $.each(response.links, function (key, linkresult) {
              data += '<tr><td><a href="' + linkresult.link + '"  target="_blank">' + linkresult.name + '</a></td><td>&nbsp;</td><td>' + linkresult.size + '</td></tr>';
            });

            data += '</table>';

            tinyMCEPopup.execCommand('mceInsertContent', false, data);
            // Refocus in window
            if (tinyMCEPopup.isWindow)
              window.focus();
            tinyMCEPopup.editor.focus();
            tinyMCEPopup.close();
          } else {
          }
        }
      },
      dataType: 'json'
    });
    return false;
  }

  function insertEmbedded() {
    var listtoken = $(".OutoftheBox.files").attr('data-token'),
            lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path'),
            entries = $(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']:checked").closest('.entry').map(function () {
      return $(this).attr('data-name');
    }).get();

    if (entries.length === 0) {
      if (tinyMCEPopup.isWindow)
        window.focus();
      tinyMCEPopup.editor.focus();
      tinyMCEPopup.close();
    }

    $.ajax({
      type: "POST",
      url: OutoftheBox_vars.ajax_url,
      data: {
        action: 'outofthebox-embedded',
        listtoken: listtoken,
        lastpath: lastpath,
        entries: entries,
        _ajax_nonce: OutoftheBox_vars.createlink_nonce
      },
      beforeSend: function () {
        $(".OutoftheBox .loading").height($(".OutoftheBox .ajax-filelist").height());
        $(".OutoftheBox .loading").fadeTo(400, 0.8);
        $(".OutoftheBox .insert_links").attr('disabled', 'disabled');
      },
      complete: function () {
        $(".OutoftheBox .loading").fadeOut(400);
        $(".OutoftheBox .insert_links").removeAttr('disabled');
      },
      success: function (response) {
        if (response !== null) {
          if (response.links !== null && response.links.length > 0) {

            var data = '';

            $.each(response.links, function (key, linkresult) {
              if ($.inArray(linkresult.extension, ['jpg', 'jpeg', 'png', 'gif']) > -1) {
                data += '<img src="' + linkresult.embeddedlink + '" />';
              } else {
                data += '<iframe src="' + linkresult.embeddedlink + '" height="480" style="width:100%;" frameborder="0" scrolling="no" class="oftb-embedded" allowfullscreen></iframe>';
              }
            });

            tinyMCEPopup.execCommand('mceInsertContent', false, data);
            // Refocus in window
            if (tinyMCEPopup.isWindow)
              window.focus();
            tinyMCEPopup.editor.focus();
            tinyMCEPopup.close();
          } else {
          }
        }
      },
      dataType: 'json'
    });
    return false;
  }

  function readCheckBoxes(element) {
    var values = $(element + ":checked").map(function () {
      return this.value;
    }).get();


    if (values.length === 0) {
      return "none";
    }

    if (values.length === $(element).length) {
      return "all";
    }

    return values.join('|');
  }
});