var _active = false,
        _updatetimer,
        _resizeTimer = null,
        _thumbTimer = null,
        _uploadPostProcesstimer = null,
        readArrCheckBoxes,
        oftb_playlists = {},
        _DBcache = {},
        _DBuploads = {},
        mobile = false,
        _windowwidth;

var DB_iLightbox = {};

function initate_out_of_the_box() {
  jQuery(function ($) {
    'use strict';

    if (/Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      var userAgent = navigator.userAgent.toLowerCase();
      if ((userAgent.search("android") > -1) && (userAgent.search("mobile") > -1)) {
        mobile = true;
      } else if ((userAgent.search("android") > -1) && !(userAgent.search("mobile") > -1)) {
        mobile = false;
      } else {
        mobile = true;
      }
    }

    /* Simple check if browser is Chrome > allow folder uploads */
    var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;

    /* Check if user is using a mobile device (including tables) detected by WordPress, alters css*/
    if (OutoftheBox_vars.is_mobile === '1') {
      $('html').addClass('oftb-mobile');
    }

    refreshLists();

    //Remove no JS message
    $(".OutoftheBox.jsdisabled").removeClass('jsdisabled');
    $(".OutoftheBox,#OutoftheBox").show();

    //Add return to home event to nav-home
    $('.OutoftheBox .nav-home').click(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token'),
              orgpath = $(this).closest(".OutoftheBox").attr('data-org-path'),
              data = {
                listtoken: listtoken
              };
      $(".OutoftheBox[id$='search-" + listtoken + "'] .search-input").val('');
      $(this).closest(".OutoftheBox").attr('data-path', orgpath);
      getFileList(data);
    });

    //Add refresh event to nav-refresh
    $('.OutoftheBox .nav-refresh').click(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token'),
              data = {
                listtoken: listtoken
              };
      $(".OutoftheBox[id$='search-" + listtoken + "'] .search-input").val('');
      getFileList(data, 'hardrefresh');
    });

    //Add scroll event to nav-upload
    $('.OutoftheBox .nav-upload').click(function () {
      $('.qtip.OutoftheBox').qtip('hide');
      var listtoken = $(this).closest(".gear-menu").attr('data-token'),
              uploadcontainer = $(".OutoftheBox[data-token='" + listtoken + "']").find('.fileupload-container');
      $('html, body').animate({
        scrollTop: uploadcontainer.offset().top
      }, 1500);
      for (var i = 0; i < 3; i++) {
        uploadcontainer.find('.fileupload-buttonbar').fadeTo('slow', 0.5).fadeTo('slow', 1.0);
      }
    });

    /* Add Link to event*/
    $('.outofthebox .linkbutton').click(function () {
      $('.outofthebox .thickbox_opener').removeClass("thickbox_opener");
      $(this).parent().addClass("thickbox_opener");
      tb_show("(Re) link to folder", '#TB_inline?height=450&amp;width=800&amp;inlineId=oftb-embedded');
    });

    $('.outofthebox .unlinkbutton').click(function () {
      var curbutton = $(this),
              user_id = $(this).attr('data-user-id');

      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: {
          action: 'outofthebox-unlinkusertofolder',
          userid: user_id,
          _ajax_nonce: OutoftheBox_vars.createlink_nonce
        },
        beforeSend: function () {
          curbutton.parent().find('.oftb-spinner').show();
        },
        success: function (response) {
          if (response === '1') {

            curbutton.addClass('hidden');
            curbutton.prev().removeClass('hidden');
            curbutton.parent().parent().find('.column-private_folder').text('');
          } else {
            location.reload(true);
          }
        },
        complete: function (reponse) {
          $('.oftb-spinner').hide();
        },
        dataType: 'text'
      });
    });

    /* Delete files event */
    $(".OutoftheBox .selected-files-delete").click(function () {
      var listtoken = $(this).closest(".gear-menu").attr('data-token');
      $('.qtip.OutoftheBox').qtip('hide');

      var entries = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");

      if (entries.length === 0) {
        return false;
      }

      var list_of_files = '';
      $.each(entries, function () {
        list_of_files += '<li>' + $('.entry[data-id="' + this + '"]').attr('data-name') + '</li>';
      });


      /* Close any open modal windows */
      $('#outofthebox-modal-action').remove();

      /* Build the Delete Dialog */
      var modalbuttons = '';
      modalbuttons += '<button class="button outofthebox-modal-confirm-btn" data-action="confirm" type="button" title="' + OutoftheBox_vars.str_delete_title + '" >' + OutoftheBox_vars.str_delete_title + '</button>';
      modalbuttons += '<button class="button outofthebox-modal-cancel-btn" data-action="cancel" type="button" onclick="modal_action.close();" title="' + OutoftheBox_vars.str_cancel_title + '" >' + OutoftheBox_vars.str_cancel_title + '</button>';
      var modalheader = $('<a tabindex="0" class="close-button" title="' + OutoftheBox_vars.str_close_title + '" onclick="modal_action.close();"><i class="fa fa-times fa-lg" aria-hidden="true"></i></a></div>');
      var modalbody = $('<div class="outofthebox-modal-body" tabindex="0" >' + OutoftheBox_vars.str_delete + '</br></br><ul>' + list_of_files + '</ul></div>');
      var modalfooter = $('<div class="outofthebox-modal-footer"><div class="outofthebox-modal-buttons">' + modalbuttons + '</div></div>');
      var modaldialog = $('<div id="outofthebox-modal-action" class="OutoftheBox outofthebox-modal ' + OutoftheBox_vars.content_skin + '"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
      $('body').append(modaldialog);
      $('#outofthebox-modal-action .modal-content').append(modalheader, modalbody, modalfooter);

      /* Set the button actions */
      $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').unbind('click');
      $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').click(function () {

        var data = {
          action: 'outofthebox-delete-entries',
          entries: entries,
          listtoken: listtoken,
          _ajax_nonce: OutoftheBox_vars.delete_nonce
        };
        changeEntry(data);

        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').prop('disabled', true);
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').html('<i class="fa fa-cog fa-spin fa-fw"></i><span> ' + OutoftheBox_vars.str_processing + '</span>');
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

    });

    /* Settings menu */
    $('.OutoftheBox .nav-gear').each(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token');

      $(this).qtip({
        prerender: true,
        id: 'nav-' + listtoken,
        content: {
          text: $(this).next('.gear-menu')
        },
        position: {
          my: 'top right',
          at: 'bottom center',
          target: $(this).find('i'),
          viewport: $(window),
          adjust: {
            scroll: false
          }
        },
        style: {
          classes: 'OutoftheBox ' + OutoftheBox_vars.content_skin
        },
        show: {
          event: 'click, mouseenter',
          solo: true
        },
        hide: {
          event: 'mouseleave unfocus',
          fixed: true,
          delay: 200
        },
        events: {
          show: function (event, api) {
            var selectedboxes = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");
            api.elements.content.find(".all-files-to-zip").parent().show();

            if (selectedboxes.length === 0) {
              api.elements.content.find(".selected-files-to-zip").parent().hide();
              api.elements.content.find(".selected-files-delete").parent().hide();
            } else {
              api.elements.content.find(".selected-files-to-zip").parent().show();
              api.elements.content.find(".selected-files-delete").parent().show();
            }

            var visibleelements = api.elements.content.find('ul > li').not('.gear-menu-no-options').filter(function () {
              return $(this).css('display') !== 'none';
            });

            if (visibleelements.length > 0) {
              api.elements.content.find('.gear-menu-no-options').hide();
            } else {
              api.elements.content.find('.gear-menu-no-options').show();
            }

          }
        }
      });
    });

    // Searchbox
    $('.OutoftheBox .nav-search').each(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token');

      $(this).qtip({
        prerender: true,
        id: 'search-' + listtoken,
        content: {
          text: $(this).next('.search-div'),
          button: $(this).next('.search-div').find('.search-remove')
        },
        position: {
          my: 'top right',
          at: 'bottom center',
          target: $(this).find('i'),
          viewport: $(window),
          adjust: {
            scroll: false
          }
        },
        style: {
          classes: 'OutoftheBox search ' + OutoftheBox_vars.content_skin
        },
        show: {
          effect: function () {
            $(this).fadeTo(90, 1, function () {
              $('input', this).focus();
            });
          }
        },
        hide: {
          fixed: true,
          delay: 1500
        }
      });
    });

    $('.OutoftheBox .search-input').each(function () {
      $(this).on("keyup", function (event) {
        var listtoken = $(this).closest(".OutoftheBox").attr('id').replace(/.*search-/, '');

        if ($(this).val().length > 0) {
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").addClass('search');
          $(".OutoftheBox[data-token='" + listtoken + "'] .nav-search").addClass('inuse');
          $(".OutoftheBox[data-token='" + listtoken + "'] .searchlist .loading, .OutoftheBox[data-token='" + listtoken + "'] .searchlist .ajax-filelist").show();

          clearTimeout(_updatetimer);
          var data = {
            listtoken: listtoken
          };
          _updatetimer = setTimeout(function () {
            $('#OutoftheBox .searchlist .loading, #OutoftheBox .searchlist .ajax-filelist').show();
            getFileList(data);
          }, 1000);

        } else {
          $(".OutoftheBox[data-token='" + listtoken + "'] .nav-search").removeClass('inuse');
          if ($(".OutoftheBox[data-token='" + listtoken + "']").hasClass('searchlist')) {
            $(".OutoftheBox[data-token='" + listtoken + "'].searchlist .loading, .OutoftheBox[data-token='" + listtoken + "'].searchlist .ajax-filelist").hide();
            $(".OutoftheBox[data-token='" + listtoken + "'].searchlist .ajax-filelist").html('');
          }
        }
      });

      $(this).parent().find('.submit-search').click(function () {
        var listtoken = $(this).closest(".OutoftheBox").attr('id').replace(/.*search-/, '');

        if ($(this).val().length > 0) {
          $(".OutoftheBox[data-token='" + listtoken + "'] .searchlist .loading, .OutoftheBox[data-token='" + listtoken + "'] .searchlist .ajax-filelist").show();

          clearTimeout(_updatetimer);
          var data = {
            listtoken: listtoken
          };
          _updatetimer = setTimeout(function () {
            getFileList(data);
          }, 1);

        } else {
          $(".OutoftheBox[data-token='" + listtoken + "'].searchlist .loading, .OutoftheBox[data-token='" + listtoken + "'].searchlist .ajax-filelist").hide();
          $(".OutoftheBox[data-token='" + listtoken + "'].searchlist .ajax-filelist").html('');
        }

      });
    });

    $('.OutoftheBox .search-remove').click(function () {
      if ($(this).parent().find('.search-input').val() !== '') {
        $(this).parent().find('.search-input').val('');
        $(this).parent().find('.search-input').trigger('keyup');
      }
    });

    //Sortable column Names
    $(".OutoftheBox .sortable").click(function () {

      var listtoken = $(this).closest(".OutoftheBox").attr('data-token');

      var newclass = 'asc';
      if ($(this).hasClass('asc')) {
        newclass = 'desc';
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .sortable").removeClass('asc').removeClass('desc');
      $(this).addClass(newclass);
      var sortstr = $(this).attr('data-sortname') + ':' + newclass;
      $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort', sortstr);

      var data = {
        listtoken: listtoken
      };

      clearTimeout(_updatetimer);
      _updatetimer = setTimeout(function () {
        getFileList(data);
      }, 300);
    });


    //To ZIP
    $('.select-all-files').click(function () {
      $(this).closest(".OutoftheBox").find(".selected-files:checkbox").prop("checked", $(this).prop("checked"));
      if ($(this).prop("checked") === true) {
        $(this).closest(".OutoftheBox").find(".selected-files:checkbox").show();
      } else {
        $(this).closest(".OutoftheBox").find(".selected-files:checkbox").hide();
      }
    });

    $(".OutoftheBox .all-files-to-zip, .OutoftheBox .selected-files-to-zip").click(function (event) {
      var location = OutoftheBox_vars.ajax_url;

      var listtoken = $(this).closest(".gear-menu").attr('data-token'),
              lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path');

      var data = {
        action: 'outofthebox-create-zip',
        listtoken: listtoken,
        lastpath: lastpath,
        _ajax_nonce: OutoftheBox_vars.createzip_nonce
      };

      if ($(event.target).hasClass('all-files-to-zip')) {
        if (($(".OutoftheBox[id$='search-" + listtoken + "'] .search-input").length > 0) && $(".OutoftheBox[id$='search-" + listtoken + "'] .search-input").val() !== '') {
          $(".OutoftheBox[data-token='" + listtoken + "'] .select-all-files").trigger('click');
          data.files = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");
        }
      }

      if ($(event.target).hasClass('selected-files-to-zip')) {
        data.files = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");
      }

      $('.qtip.OutoftheBox').qtip('hide');
      $(this).attr('href', location + "?" + $.param(data));

      return;
    });

    function isCached(identifyer, listtoken) {
      if (typeof _DBcache[listtoken] === 'undefined') {
        _DBcache[listtoken] = {};
      }

      if (typeof _DBcache[listtoken][identifyer] === 'undefined' || $.isEmptyObject(_DBcache[listtoken][identifyer])) {
        return false;
      } else {

        var unixtime = Math.round((new Date()).getTime() / 1000);
        if (_DBcache[listtoken][identifyer].expires < unixtime) {
          _DBcache[listtoken][identifyer] = {};
          return false;
        }
        return _DBcache[listtoken][identifyer];
      }
    }

    function updateDiv(response, identifyer, listtoken) {
      $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 1);

      if (typeof _DBcache[listtoken] === 'undefined') {
        _DBcache[listtoken] = {};
      }

      _DBcache[listtoken][identifyer] = response;

      $(".OutoftheBox[data-token='" + listtoken + "'] .ajax-filelist").html(response.html);
      $(".OutoftheBox[data-token='" + listtoken + "'] .nav-title").html(response.breadcrumb);
      $(".OutoftheBox[data-token='" + listtoken + "'] .current-folder-raw").text(response.rawpath);

      if (response.lastpath !== null) {
        $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path', response.lastpath);
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeOut(400);

      updateActions(listtoken);
    }

    function getFileList(data, hardrefresh) {
      var listtoken = data.listtoken,
              list = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-list'),
              lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path'),
              sort = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort'),
              query = $(".OutoftheBox[id$='search-" + listtoken + "'] .search-input").val(),
              ajax_action = 'outofthebox-get-filelist',
              deeplink = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-deeplink'),
              nonce = OutoftheBox_vars.refresh_nonce;

      if (list === 'gallery') {
        ajax_action = 'outofthebox-get-gallery';
        nonce = OutoftheBox_vars.gallery_nonce;
      }

      if (typeof query !== 'undefined' && query.length > 2 && query !== 'Search filenames') {
        data.query = query;
      }

      if (typeof hardrefresh !== 'undefined') {
        _DBcache = [];
      }

      data.deeplink = deeplink;
      data.sort = sort;
      data.action = ajax_action;
      data.mobile = mobile;
      data._ajax_nonce = nonce;
      data.lastpath = lastpath;

      /* Identifyer for cache */
      var str = JSON.stringify(data);
      var identifyer = str.hashCode();
      var request = false;

      request = isCached(identifyer, listtoken);

      if (request !== false) {
        return updateDiv(request, identifyer, listtoken);
      }

      /* Don't add in the identifyer */
      if (typeof hardrefresh !== 'undefined') {
        data.hardrefresh = true;
      }

      $.ajax({
        type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: data,
        beforeSend: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .no_results").remove();
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").removeClass('initialize upload error');
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 1);
        },
        complete: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").removeClass('search');
        },
        success: function (response) {
          if (response !== null && response !== 0) {
            updateDiv(response, identifyer, listtoken);
          } else {
            $(".OutoftheBox[data-token='" + listtoken + "'] .nav-title").html(OutoftheBox_vars.str_no_filelist);
            $(".OutoftheBox[data-token='" + listtoken + "'] .loading").addClass('error');
            updateActions(listtoken);
          }
        },
        error: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .nav-title").html(OutoftheBox_vars.str_no_filelist);
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").addClass('error');
          updateActions(listtoken);
        },
        dataType: 'json'
      });
    }

    function changeEntry(data) {
      var listtoken = data.listtoken,
              lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path');
      data.lastpath = lastpath;
      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: data,
        beforeSend: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 1);
        },
        complete: function () {
          var data = {
            listtoken: listtoken
          };
          getFileList(data, 'hardrefresh');

          if (typeof modal_action !== 'undefined') {
            modal_action.close();
          }

        }, success: function (response) {

          if (typeof response !== 'undefined') {
            if (typeof response.lastpath !== 'undefined' && (response.lastpath !== null)) {
              $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path', response.lastpath);
            }
          }

        },
        dataType: 'json'
      });
    }

    function refreshLists() {
      var selector = $('.OutoftheBox.files, .OutoftheBox.gridgallery');
      if (_active) {
        var selector = $('.OutoftheBox.files');
      }

      //Create file lists
      selector.each(function () {

        var data = {
          OutoftheBoxpath: $(this).attr('data-path'),
          listtoken: $(this).attr('data-token')
        };

        if ($(this).hasClass('searchlist')) {
          return true;
        }

        getFileList(data);
      });
      _active = true;
    }

    window.updateCollage = function updateCollage(listtoken) {
      var selector = $(".OutoftheBox.gridgallery[data-token='" + listtoken + "']");
      var image_collage = (selector).find(".image-collage");
      image_collage.outerWidth(selector.find('.ajax-filelist').width() - 1, true);

      var targetheight = selector.attr('data-targetheight');
      image_collage.removeWhitespace().collagePlus({
        'targetHeight': targetheight,
        'fadeSpeed': "slow",
        'allowPartialLastRow': true
      });

      selector.find(".entry.hidden-for-gallery").hide();

      selector.find("img.preloading").not('.hidden-for-gallery').unveil(200, $(window), function () {
        $(this).load(function () {
          $(this).removeClass('preloading').removeAttr('data-src');
          $(this).prev('.preloading').remove();
        });
      });

      image_collage.fadeTo(1500, 1);

      selector.find(".image-container").each(function () {
        var folder_thumb = $(this).find(".folder-thumb");

        $(this).find(".image-folder-img").width($(this).width()).height($(this).height());

        if (folder_thumb.length > 0) {
          folder_thumb.width($(this).width()).height($(this).height());
          $(this).find(".image-folder-img").hide();
        }
      });

      if (_thumbTimer) {
        clearInterval(_thumbTimer);
      }

      updateImageFolders();
      _thumbTimer = setInterval(updateImageFolders, 15000);

    };

    function updateImageFolders() {
      $(".OutoftheBox.gridgallery .image-folder").each(function () {
        $(this).find('.folder-thumb').fadeIn(1500);
        var delay = Math.floor(Math.random() * 3000) + 1500;
        $(this).find(".thumb3").delay(delay).fadeOut(1500);
        $(this).find(".thumb2").delay(delay + 1500).delay(delay).fadeOut(1500);
        $(this).find(".thumb3").delay(2 * (delay + 1500)).delay(delay).fadeIn(1500);
      });
    }
    function updateActions(listtoken) {

      if ($(".OutoftheBox[data-token='" + listtoken + "']").hasClass('gridgallery')) {
        updateCollage(listtoken);
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").unbind('hover');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").hover(
              function () {
                $(this).addClass('hasfocus');
              },
              function () {
                $(this).removeClass('hasfocus');
              }
      );

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").on('mouseover', function () {
        $(this).addClass('hasfocus');
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").click(function () {
        $(this).find('.entry_checkbox input[type="checkbox"]').trigger('click');
      });


      /* Edit menu popup */
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry .entry_edit_menu").each(function () {
        $(this).click(function (e) {
          e.stopPropagation();
        });

        $(this).qtip({
          content: {
            text: $(this).next('.oftb-dropdown-menu')
          },
          position: {
            my: 'top center',
            at: 'bottom center',
            target: $(this),
            scroll: false,
            viewport: $(".OutoftheBox[data-token='" + listtoken + "']")
          },
          show: {
            event: 'click',
            solo: true
          },
          hide: {
            event: 'mouseleave unfocus',
            delay: 200,
            fixed: true
          },
          events: {
            show: function (event, api) {
              api.elements.target.closest('.entry').addClass('hasfocus').addClass('popupopen');
            },
            hide: function (event, api) {
              api.elements.target.closest('.entry').removeClass('hasfocus').removeClass('popupopen');
            }
          },
          style: {
            classes: 'OutoftheBox ' + OutoftheBox_vars.content_skin
          }
        });
      });


      /* Load more images */
      var loadmoreimages = function () {
        // the element should probably be expected to be off-screen (beneath the visible viewport) when domready fires, but this can be tested using similar logic
        var element = $(".OutoftheBox[data-token='" + listtoken + "'] .image-container.entry:not(.hidden-for-gallery):last()");
        // is the element at least 10% visible along both axes?
        var visible = element.isOnScreen(0.1, 0.1);
        if (visible) {
          var loadimages = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-loadimages'),
                  images = $(".OutoftheBox[data-token='" + listtoken + "'] .image-container:hidden:lt(" + loadimages + ")");

          if (images.length > 0) {
            images.each(function () {
              $(this).fadeIn(500);
              $(this).removeClass('hidden-for-gallery');
              $(this).find('img').removeClass('hidden-for-gallery');
            });

            $(".OutoftheBox[data-token='" + listtoken + "']").find("img.preloading").not('.hidden-for-gallery').unveil(200, $(window), function () {
              $(this).load(function () {
                $(this).removeClass('preloading').removeAttr('data-src');
                $(this).prev('.preloading').remove();
              });
            });

          } else {
            // tidy up
            $(window).off('scroll', debounced);
          }

        }
      };
      /* wrap it in the functor so that it's only called every 50 ms */
      var debounced = $.noop;
      debounced = loadmoreimages.debounce(50);
      $(window).on('scroll', debounced);
      $(window).trigger('scroll');

      /* Drag and Drop folders and files */
      if ($('#OutoftheBox .entry.moveable').length > 0) {
        $('#OutoftheBox .entry').not('.parentfolder').draggable({
          revert: "invalid",
          stack: "#OutoftheBox .entry",
          cursor: 'move',
          containment: 'parent',
          distance: 50,
          delay: 50,
          start: function (event, ui) {
            $(this).addClass('isdragged');
            $(this).css('transform', 'scale(0.8)');
          },
          stop: function (event, ui) {
            setTimeout(function () {
              $(this).removeClass('isdragged');
            }, 300);
            $(this).css('transform', 'scale(1)');
          }
        });

        $('#OutoftheBox .entry.folder').droppable({
          accept: $('#OutoftheBox .entry'),
          activeClass: "ui-state-hover",
          hoverClass: "ui-state-active",
          drop: function (event, ui) {
            var listtoken = ui.draggable.closest('.OutoftheBox').attr('data-token');
            $(ui.draggable).fadeOut(500);

            var data = {
              action: 'outofthebox-move-entry',
              OutoftheBoxpath: ui.draggable.attr('data-url'),
              copy: false,
              target: $(this).attr('data-url'),
              listtoken: listtoken,
              _ajax_nonce: OutoftheBox_vars.move_nonce
            };
            changeEntry(data);
          }
        });
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .folder, .OutoftheBox[data-token='" + listtoken + "'] .image-folder").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .folder, .OutoftheBox[data-token='" + listtoken + "'] .image-folder").click(function (e) {

        if ($(this).hasClass('isdragged')) {
          return false;
        }
        $(".OutoftheBox[id$='search-" + listtoken + "'] .search-input").val('');
        var data = {
          OutoftheBoxpath: $(this).closest('.folder, .image-folder').attr('data-url'),
          listtoken: listtoken
        };
        getFileList(data);
        e.stopPropagation();
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .image-container .image-rollover").css("opacity", "0");
      $(".OutoftheBox[data-token='" + listtoken + "'] .image-container").hover(
              function () {
                $(this).find('.image-rollover').stop().animate({opacity: 1}, 400);
              },
              function () {
                $(this).find('.image-rollover').stop().animate({opacity: 0}, 400);
              });

      var groupsArr = [];

      if (typeof DB_iLightbox[listtoken] === 'undefined') {
        DB_iLightbox[listtoken] = {};
      } else if (!$.isEmptyObject(DB_iLightbox[listtoken])) {
        DB_iLightbox[listtoken].destroy();
      }

      $('.OutoftheBox[data-token="' + listtoken + '"] .ilightbox-group[rel^="ilightbox["]').each(function () {
        var group = this.getAttribute("rel");
        $.inArray(group, groupsArr) === -1 && groupsArr.push(group);
      });
      $.each(groupsArr, function (i, groupName) {
        var selector = $('.OutoftheBox[data-token="' + listtoken + '"]');

        DB_iLightbox[listtoken] = $('.OutoftheBox[data-token="' + listtoken + '"] .ilightbox-group[rel="' + groupName + '"]').iLightBox({
          skin: OutoftheBox_vars.lightbox_skin,
          path: OutoftheBox_vars.lightbox_path,
          maxScale: 1,
          slideshow: {
            pauseOnHover: true,
            pauseTime: selector.attr('data-pausetime'),
            startPaused: ((selector.attr('data-list') === 'gallery') && (selector.attr('data-slideshow') === '1')) ? false : true
          },
          controls: {
            slideshow: (selector.attr('data-list') === 'gallery') ? true : false,
            arrows: true,
            thumbnail: ((mobile) ? false : true)
          },
          caption: {
            start: (OutoftheBox_vars.lightbox_showcaption === 'mouseenter') ? true : false,
            show: OutoftheBox_vars.lightbox_showcaption,
            hide: (OutoftheBox_vars.lightbox_showcaption === 'mouseenter') ? 'mouseleave' : OutoftheBox_vars.lightbox_showcaption,
          },
          keepAspectRatio: true,
          callback: {
            onBeforeLoad: function (api, position) {
              $('.ilightbox-holder').addClass('OutoftheBox');
              $('.ilightbox-holder').find('iframe').addClass('oftb-embedded');
              $('.ilightbox-holder .oftb-hidepopout').remove();
              $('.ilightbox-holder').find('.oftb-embedded').after('<div class="oftb-hidepopout">&nbsp;</div>');

              iframeFix();

            },
            onBeforeChange: function () {
              /* Stop all HTML 5 players */
              var players = $('.ilightbox-holder video, .ilightbox-holder audio');
              $.each(players, function (i, element) {
                if (element.paused === false) {
                  element.pause();
                }
              });
            },
            onAfterChange: function (api) {
              /* Auto Play new players*/
              var players = api.currentElement.find('video, audio');
              $.each(players, function (i, element) {
                if (element.paused) {
                  element.play();
                }
              });
            },
            onRender: function (api, position) {
              /* Auto-size HTML 5 player */
              var $video_html5_players = $('.ilightbox-holder').find('video, audio');
              $.each($video_html5_players, function (i, video_html5_player) {

                var $video_html5_player = $(this);

                video_html5_player.addEventListener('playing', function () {
                  var container_width = api.currentElement.find('.ilightbox-container').width() - 1;
                  var container_height = api.currentElement.find('.ilightbox-container').height() - 1;

                  $video_html5_player.width(container_width);

                  $video_html5_player.parent().width(container_width)

                  if ($video_html5_player.height() > api.currentElement.find('.ilightbox-container').height() - 2) {
                    $video_html5_player.height(container_height);
                  }
                }, false);
                $video_html5_player.find('source').attr('src', $video_html5_player.find('source').attr('data-src'));
              });

            },
            onShow: function (api, position) {
              if (api.currentElement.find('.empty_iframe').length === 0) {
                api.currentElement.find('.oftb-embedded').after(OutoftheBox_vars.str_iframe_loggedin);
              }

              /* Bugfix for PDF files that open very narrow */
              if (api.currentElement.find('iframe').length > 0) {
                setTimeout(function () {
                  api.currentElement.find('.oftb-embedded').width(api.currentElement.find('.ilightbox-container').width() - 1);
                }, 500);
              }

              api.currentElement.find('.empty_iframe').hide();
              if (api.currentElement.find('img').length !== 0) {
                setTimeout(function () {
                  api.currentElement.find('.empty_iframe').fadeIn();
                }, 5000);
              }

              /* Auto Play new players*/
              var players = api.currentElement.find('video, audio');
              $.each(players, function (i, element) {
                if (element.paused) {
                  element.play();
                }
              });

              $('.OutoftheBox .ilightbox-container img').on("contextmenu", function (e) {
                return (OutoftheBox_vars.lightbox_rightclick === 'Yes');
              });
            }
          },
          errors: {
            loadImage: OutoftheBox_vars.str_imgError_title,
            loadContents: OutoftheBox_vars.str_xhrError_title
          },
          text: {
            next: OutoftheBox_vars.str_next_title,
            previous: OutoftheBox_vars.str_previous_title,
            slideShow: OutoftheBox_vars.str_startslideshow
          }
        });
      });

      /* Disable right clicks */
      $('#OutoftheBox .entry').on("contextmenu", function (e) {
        return false;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_checkbox").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_checkbox").click(function (e) {
        e.stopPropagation();
        return true;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_checkbox :checkbox").click(function (e) {
        if ($(this).prop('checked')) {
          $(this).closest('.entry').addClass('isselected');
        } else {
          $(this).closest('.entry').removeClass('isselected');
        }
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_linkto").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_linkto").click(function (e) {

        var folder_text = $(this).parent().attr('data-name'),
                folder_path = decodeURIComponent($(this).parent().attr('data-url')),
                user_id = $('.outofthebox .thickbox_opener').find('[data-user-id]').attr('data-user-id');

        if ($('.thickbox_opener').hasClass('private-folders-auto')) {
          $('.thickbox_opener').find('.private-folders-auto-current').val(folder_path);
          $('.thickbox_opener').find('.private-folders-auto-input-id').val(folder_path);
          $('.thickbox_opener').find('.private-folders-auto-input-name').val(folder_path);
          $('.thickbox_opener').find('.private-folders-auto-button').removeClass('disabled').find('.oftb-spinner').fadeOut()
          tb_remove();
          e.stopPropagation();
          return true;
        }

        if ($('.thickbox_opener').hasClass('woocommerce_upload_location')) {

          $('#woocommerce_outofthebox-woocommerce_upload_location_id').val(folder_id);
          $('#woocommerce_outofthebox-woocommerce_upload_location').val(folder_text);
          tb_remove();
          e.stopPropagation();
          return true;
        }

        $.ajax({type: "POST",
          url: OutoftheBox_vars.ajax_url,
          data: {
            action: 'outofthebox-linkusertofolder',
            id: folder_path,
            text: folder_path,
            userid: user_id,
            _ajax_nonce: OutoftheBox_vars.createlink_nonce
          },
          beforeSend: function () {
            tb_remove();
            $('.outofthebox .thickbox_opener').find('.oftb-spinner').show();
          },
          complete: function () {
            $('.oftb-spinner').hide();
          },
          success: function (response) {
            if (response === '1') {
              $('.outofthebox .thickbox_opener').parent().find('.column-private_folder').text(folder_path);
              $('.outofthebox .thickbox_opener .unlinkbutton').removeClass('hidden');
              $('.outofthebox .thickbox_opener .linkbutton').addClass('hidden');
              $('.outofthebox .thickbox_opener').removeClass("thickbox_opener");
            } else {
              location.reload(true);
            }
          },
          dataType: 'text'
        });

        e.stopPropagation();
        return true;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_woocommerce_link").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_woocommerce_link").click(function (e) {

        var file_id = $(this).parent().attr('data-url');
        var file_name = $(this).attr('data-filename');

        tb_remove();
        window.wc_outofthebox.afterFileSelected(file_id, file_name);
        e.stopPropagation();
        return true;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_view").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_view").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');
        var datapath = $(this).closest("ul").attr('data-path');
        var link = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").find(".entry_link")[0].click();
      });


      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_download").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_download").click(function (e) {
        e.stopPropagation();

        var href = $(this).attr('href'),
                dataname = $(this).attr('data-filename');

        sendGooglePageView('Download', dataname);

        // Delay a few milliseconds for Tracking event
        setTimeout(function () {
          window.location = href;
        }, 300);

        return false;

      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_shortlink").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_shortlink").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        var datapath = $(this).closest("ul").attr('data-path');

        var dataurl = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-url');
        var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-name');

        /* Close any open modal windows */
        $('#outofthebox-modal-action').remove();

        /* Build the Delete Dialog */
        var modalbuttons = '';
        modalbuttons += '<button class="button outofthebox-modal-confirm-btn" data-action="confirm" type="button" title="' + OutoftheBox_vars.str_create_shared_link + '" >' + OutoftheBox_vars.str_create_shared_link + '</button>';
        modalbuttons += '<button class="button outofthebox-modal-cancel-btn" data-action="cancel" type="button" onclick="modal_action.close();" title="' + OutoftheBox_vars.str_close_title + '" >' + OutoftheBox_vars.str_close_title + '</button>';
        var modalheader = $('<a tabindex="0" class="close-button" title="' + OutoftheBox_vars.str_close_title + '" onclick="modal_action.close();"><i class="fa fa-times fa-lg" aria-hidden="true"></i></a></div>');
        var modalbody = $('<div class="outofthebox-modal-body" tabindex="0" ></div>');
        var modalfooter = $('<div class="outofthebox-modal-footer"><div class="outofthebox-modal-buttons">' + modalbuttons + '</div></div>');
        var modaldialog = $('<div id="outofthebox-modal-action" class="OutoftheBox outofthebox-modal ' + OutoftheBox_vars.content_skin + '"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
        $('body').append(modaldialog);
        $('#outofthebox-modal-action .modal-content').append(modalheader, modalbody, modalfooter);

        $.ajax({type: "POST",
          url: OutoftheBox_vars.ajax_url,
          data: {
            action: 'outofthebox-create-link',
            listtoken: listtoken,
            OutoftheBoxpath: dataurl,
            _ajax_nonce: OutoftheBox_vars.createlink_nonce
          },
          complete: function () {
            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').remove();
          },
          success: function (response) {
            if (response !== null) {
              if (response.link !== null) {
                $('.outofthebox-modal-body').append('<input type="text" class="shared-link-url" value="' + response.link + '" style="width: 98%;" readonly/><div class="outofthebox-shared-social"></div>');
                sendGooglePageView('Create shared link');

                $(".outofthebox-shared-social").jsSocials({
                  url: response.link,
                  text: dataname + ' | ',
                  showLabel: false,
                  showCount: "inside",
                  shareIn: "popup",
                  shares: ["email", "twitter", "facebook", "googleplus", "linkedin", "pinterest", "whatsapp"]
                });

              } else {
                $('.outofthebox-modal-body').find('.shared-link-url').val(response.error);
              }
            }
          },
          dataType: 'json'
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

        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').prop('disabled', true);
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').html('<i class="fa fa-cog fa-spin fa-fw"></i><span> ' + OutoftheBox_vars.str_processing + '</span>');

        return false;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_delete").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_delete").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        var datapath = $(this).closest("ul").attr('data-path');
        var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-name');
        var dataurl = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-url');

        /* Close any open modal windows */
        $('#outofthebox-modal-action').remove();

        /* Build the Delete Dialog */
        var modalbuttons = '';
        modalbuttons += '<button class="button outofthebox-modal-confirm-btn" data-action="confirm" type="button" title="' + OutoftheBox_vars.str_delete_title + '" >' + OutoftheBox_vars.str_delete_title + '</button>';
        modalbuttons += '<button class="button outofthebox-modal-cancel-btn" data-action="cancel" type="button" onclick="modal_action.close();" title="' + OutoftheBox_vars.str_cancel_title + '" >' + OutoftheBox_vars.str_cancel_title + '</button>';
        var modalheader = $('<a tabindex="0" class="close-button" title="' + OutoftheBox_vars.str_close_title + '" onclick="modal_action.close();"><i class="fa fa-times fa-lg" aria-hidden="true"></i></a></div>');
        var modalbody = $('<div class="outofthebox-modal-body" tabindex="0" >' + OutoftheBox_vars.str_delete + '</br></br><strong>' + dataname + '</strong></div>');
        var modalfooter = $('<div class="outofthebox-modal-footer"><div class="outofthebox-modal-buttons">' + modalbuttons + '</div></div>');
        var modaldialog = $('<div id="outofthebox-modal-action" class="OutoftheBox outofthebox-modal ' + OutoftheBox_vars.content_skin + '"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
        $('body').append(modaldialog);
        $('#outofthebox-modal-action .modal-content').append(modalheader, modalbody, modalfooter);

        /* Set the button actions */
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').unbind('click');
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').click(function () {

          var data = {
            action: 'outofthebox-delete-entry',
            entries: [dataname],
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.delete_nonce
          };
          changeEntry(data);

          $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').prop('disabled', true);
          $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').html('<i class="fa fa-cog fa-spin fa-fw"></i><span> ' + OutoftheBox_vars.str_processing + '</span>');
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
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_rename").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_rename").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        var datapath = $(this).closest("ul").attr('data-path');
        var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-name');
        var dataurl = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-url');

        /* Close any open modal windows */
        $('#outofthebox-modal-action').remove();
        /* Build the Rename Dialog */
        var modalbuttons = '';
        modalbuttons += '<button class="button outofthebox-modal-confirm-btn" data-action="rename" type="button" title="' + OutoftheBox_vars.str_rename_title + '" >' + OutoftheBox_vars.str_rename_title + '</button>';
        modalbuttons += '<button class="button outofthebox-modal-cancel-btn" data-action="cancel" type="button" onclick="modal_action.close();" title="' + OutoftheBox_vars.str_cancel_title + '" >' + OutoftheBox_vars.str_cancel_title + '</button>';
        var renameinput = '<input id="outofthebox-modal-rename-input" name="outofthebox-modal-rename-input" type="text" value="' + dataname + '" style="width:100%"/>';
        var modalheader = $('<a tabindex="0" class="close-button" title="' + OutoftheBox_vars.str_close_title + '" onclick="modal_action.close();"><i class="fa fa-times fa-lg" aria-hidden="true"></i></a></div>');
        var modalbody = $('<div class="outofthebox-modal-body" tabindex="0" >' + OutoftheBox_vars.str_rename + '<br/>' + renameinput + '</div>');
        var modalfooter = $('<div class="outofthebox-modal-footer"><div class="outofthebox-modal-buttons">' + modalbuttons + '</div></div>');
        var modaldialog = $('<div id="outofthebox-modal-action" class="OutoftheBox outofthebox-modal ' + OutoftheBox_vars.content_skin + '"><div class="modal-dialog"><div class="modal-content"></div></div></div>');

        $('body').append(modaldialog);
        $('#outofthebox-modal-action .modal-content').append(modalheader, modalbody, modalfooter);
        /* Set the button actions */

        $('#outofthebox-modal-action #outofthebox-modal-rename-input').unbind('keyup');
        $('#outofthebox-modal-action #outofthebox-modal-rename-input').on("keyup", function (event) {
          if (event.which == 13 || event.keyCode == 13) {
            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').trigger('click');
          }
        });
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').unbind('click');
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').click(function () {

          var new_filename = $('#outofthebox-modal-rename-input').val();
          /* Check if there are illegal characters in the new name*/
          if (/[<>:"/\\|?*]/g.test($('#outofthebox-modal-rename-input').val())) {
            $('#outofthebox-modal-action .outofthebox-modal-error').remove();
            $('#outofthebox-modal-rename-input').after('<div class="outofthebox-modal-error">' + OutoftheBox_vars.str_rename_failed + '</div>');
            $('#outofthebox-modal-action .outofthebox-modal-error').fadeIn();
          } else {

            var data = {
              action: 'outofthebox-rename-entry',
              OutoftheBoxpath: dataurl,
              newname: encodeURIComponent(new_filename),
              listtoken: listtoken,
              _ajax_nonce: OutoftheBox_vars.rename_nonce
            };
            changeEntry(data);

            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').prop('disabled', true);
            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').html('<i class="fa fa-cog fa-spin fa-fw"></i><span> ' + OutoftheBox_vars.str_processing + '</span>');
          }

        });
        /* Open the dialog */
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
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .newfolder").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .newfolder").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        /* Close any open modal windows */
        $('#outofthebox-modal-action').remove();
        /* Build the Rename Dialog */
        var modalbuttons = '';
        modalbuttons += '<button class="button outofthebox-modal-confirm-btn" data-action="rename" type="button" title="' + OutoftheBox_vars.str_addfolder_title + '" >' + OutoftheBox_vars.str_addfolder_title + '</button>';
        modalbuttons += '<button class="button outofthebox-modal-cancel-btn" data-action="cancel" type="button" onclick="modal_action.close();" title="' + OutoftheBox_vars.str_cancel_title + '" >' + OutoftheBox_vars.str_cancel_title + '</button>';
        var addfolder_input = '<input type="text" id="outofthebox-modal-addfolder-input" name="outofthebox-modal-addfolder-input" value="" style="width:100%"/>';
        var modalheader = $('<a tabindex="0" class="close-button" title="' + OutoftheBox_vars.str_close_title + '" onclick="modal_action.close();"><i class="fa fa-times fa-lg" aria-hidden="true"></i></a></div>');
        var modalbody = $('<div class="outofthebox-modal-body" tabindex="0" >' + OutoftheBox_vars.str_addfolder + ' <br/>' + addfolder_input + '</div>');
        var modalfooter = $('<div class="outofthebox-modal-footer"><div class="outofthebox-modal-buttons">' + modalbuttons + '</div></div>');
        var modaldialog = $('<div id="outofthebox-modal-action" class="OutoftheBox outofthebox-modal ' + OutoftheBox_vars.content_skin + '"><div class="modal-dialog"><div class="modal-content"></div></div></div>');

        $('body').append(modaldialog);
        $('#outofthebox-modal-action .modal-content').append(modalheader, modalbody, modalfooter);
        /* Set the button actions */

        $('#outofthebox-modal-action #outofthebox-modal-addfolder-input').unbind('keyup');
        $('#outofthebox-modal-action #outofthebox-modal-addfolder-input').on("keyup", function (event) {
          if (event.which == 13 || event.keyCode == 13) {
            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').trigger('click');
          }
        });
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').unbind('click');
        $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').click(function () {

          var newinput = $('#outofthebox-modal-addfolder-input').val();
          /* Check if there are illegal characters in the new name*/
          if (/[<>:"/\\|?*]/g.test($('#outofthebox-modal-addfolder-input').val())) {
            $('#outofthebox-modal-action .outofthebox-modal-error').remove();
            $('#outofthebox-modal-addfolder-input').after('<div class="outofthebox-modal-error">' + OutoftheBox_vars.str_rename_failed + '</div>');
            $('#outofthebox-modal-action .outofthebox-modal-error').fadeIn();
          } else {

            var data = {
              action: 'outofthebox-add-folder',
              newfolder: encodeURIComponent(newinput),
              listtoken: listtoken,
              _ajax_nonce: OutoftheBox_vars.addfolder_nonce
            };
            changeEntry(data);

            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').prop('disabled', true);
            $('#outofthebox-modal-action .outofthebox-modal-confirm-btn').html('<i class="fa fa-cog fa-spin fa-fw"></i><span> ' + OutoftheBox_vars.str_processing + '</span>');
          }

        });
        /* Open the dialog */
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
      });
    }

    /* Remove Folder upload button if isn't supported by browser */
    if (!is_chrome) {
      $('.upload-multiple-files').parent().remove();
    }

    // Initialize the jQuery File Upload widget:
    var _uploaded_files = {};
    var _number_of_uploaded_files = 0;

    $('.OutoftheBox .fileuploadform').each(function () {

      /* Set Cookie for Guest uploads */
      if ($(this).closest('.upload').length > 0 && document.cookie.indexOf("OftB-ID=") == -1) {
        var date = new Date();
        date.setTime(date.getTime() + (7 * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toUTCString();
        var id = Math.random().toString(36).substr(2, 16)
        document.cookie = "OftB-ID=" + id + expires + "; path=" + OutoftheBox_vars.cookie_path + "; domain=" + OutoftheBox_vars.cookie_domain + ";";
      }

      $(this).fileupload({
        url: OutoftheBox_vars.ajax_url,
        type: 'POST',
        dataType: 'json',
        autoUpload: true,
        maxFileSize: OutoftheBox_vars.post_max_size,
        acceptFileTypes: new RegExp($(this).find('input[name="acceptfiletypes"]').val(), "i"),
        dropZone: $(this).closest('.OutoftheBox'),
        messages: {
          maxNumberOfFiles: OutoftheBox_vars.maxNumberOfFiles,
          acceptFileTypes: OutoftheBox_vars.acceptFileTypes,
          maxFileSize: OutoftheBox_vars.maxFileSize,
          minFileSize: OutoftheBox_vars.minFileSize
        },
        limitConcurrentUploads: 3,
        disableImageLoad: true,
        disableImageResize: true,
        disableImagePreview: true,
        disableAudioPreview: true,
        disableVideoPreview: true,
        uploadTemplateId: null,
        downloadTemplateId: null, add: function (e, data) {
          var listtoken = $(this).attr('data-token');

          $.each(data.files, function (index, file) {
            _number_of_uploaded_files++;
            file.hash = file.name.hashCode() + '_' + Math.floor(Math.random() * 1000000);
            file.listtoken = listtoken;
            file = validateFile(file);
            var row = renderFileUploadRow(file);

            if (file.error !== false) {
              data.files.splice(index, 1);
            }
          });

          if (data.autoUpload || (data.autoUpload !== false &&
                  $(this).fileupload('option', 'autoUpload'))) {
            if (data.files.length > 0) {
              data.process().done(function () {
                data.submit();
              });
            }
          }
        }
      }).on('fileuploadsubmit', function (e, data) {
        var datatoken = $(this).attr('data-token');
        $(".OutoftheBox[data-token='" + datatoken + "'] .loading").addClass('upload');
        $(".OutoftheBox[data-token='" + datatoken + "'] .loading").fadeTo(400, 1);

        var filehash;
        $.each(data.files, function (index, file) {
          uploadStart(file);
          filehash = file.hash;
        });

        $('.gform_button:submit').prop("disabled", true).fadeTo(400, 0.3);

        data.formData = {
          action: 'outofthebox-upload-file',
          type: 'do-upload',
          hash: filehash,
          lastpath: $(".OutoftheBox[data-token='" + datatoken + "']").attr('data-path'),
          listtoken: datatoken,
          _ajax_nonce: OutoftheBox_vars.upload_nonce
        };

      }).on('fileuploadprogress', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10) / 2;

        $.each(data.files, function (index, file) {
          uploadProgress(file, {percentage: progress, progress: 'uploading_to_server'});

          if (progress >= 50) {
            uploadProgress(file, {percentage: 50, progress: 'uploading_to_cloud'});

            setTimeout(function () {
              getProgress(file);
            }, 2000);
          }
        });

      }).on('fileuploadstopped', function () {
        $('.gform_button:submit').prop("disabled", false).fadeTo(400, 1);
      }).on('fileuploaddone', function (e, data) {
        sendGooglePageView('Upload file');
      }).on('fileuploadalways', function (e, data) {

        if (typeof data.result !== 'undefined') {
          if (typeof data.result.status !== 'undefined') {
            if (data.result.status.progress === 'finished' || data.result.status.progress === 'failed') {
              uploadFinished(data.result.file);
            }
          } else {
            data.result.file.error = OutoftheBox_vars.str_error;
            uploadFinished(data.result.file);
          }
        } else {
          $.each(data.files, function (index, file) {
            file.error = OutoftheBox_vars.str_error;
            uploadFinished(file);
          });
        }
      }).on('fileuploaddrop', function (e, data) {
        var uploadcontainer = $(this);
        $('html, body').animate({
          scrollTop: uploadcontainer.offset().top
        }, 1500);
      });
    });

    /* ***** Helper functions for File Upload ***** */
    /* Validate File for Upload */
    function validateFile(file, position) {

      var maxFileSize = $(".OutoftheBox[data-token='" + file.listtoken + "']").find('input[name="maxfilesize"]').val(),
              maxNumberOfUploads = $(".OutoftheBox[data-token='" + file.listtoken + "']").find('input[name="maxnumberofuploads"]').val(),
              acceptFileType = new RegExp($(".OutoftheBox[data-token='" + file.listtoken + "']").find('input[name="acceptfiletypes"]').val(), "i");

      file.error = false;
      if (file.name.length && !acceptFileType.test(file.name)) {
        file.error = OutoftheBox_vars.acceptFileTypes;
      }
      if (maxFileSize !== '' && file.size > 0 && file.size > maxFileSize) {
        file.error = OutoftheBox_vars.maxFileSize;
      }

      if (maxNumberOfUploads > 0 && (_number_of_uploaded_files > maxNumberOfUploads)) {
        var max_reached = true;
        /* Allow upload of the same file */
        $.each(_DBuploads[file.listtoken], function () {
          if (this.name === file.name) {
            max_reached = false;
            _number_of_uploaded_files--; // Don't count this as an extra file
          }
        });

        if (max_reached) {
          file.error = OutoftheBox_vars.maxNumberOfFiles
        }
      }

      return file;
    }

    /* Get Progress for uploading files to cloud*/
    function getProgress(file) {

      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: {
          action: 'outofthebox-upload-file',
          type: 'get-status',
          listtoken: file.listtoken,
          hash: file.hash,
          _ajax_nonce: OutoftheBox_vars.upload_nonce
        },
        success: function (response) {
          if (response !== null) {
            if (typeof response.status !== 'undefined') {
              if (response.status.progress === 'starting' || response.status.progress === 'uploading') {
                setTimeout(function () {
                  getProgress(response.file);
                }, 1500);
              }
              uploadProgress(response.file, {percentage: 50 + (response.status.percentage / 2), progress: response.status.progress});
            } else {
              file.error = OutoftheBox_vars.str_error;
              uploadFinished(file);
            }
          }
        },
        error: function (response) {
          file.error = OutoftheBox_vars.str_error;
          uploadFinished(file);
        },
        complete: function (response) {

        },
        dataType: 'json'
      });
    }

    /* Render file in upload list */
    function renderFileUploadRow(file) {
      var row = ($(".OutoftheBox[data-token='" + file.listtoken + "']").find('.template-row').clone().removeClass('template-row'));

      row.attr('data-file', file.name).attr('data-id', file.hash);
      row.find('.file-name').text(file.name);
      if (file.size !== 'undefined' && file.size > 0) {
        row.find('.file-size').text(humanFileSize(file.size, true));
      }
      row.find('.upload-thumbnail img').attr('src', getThumbnail(file));

      row.addClass('template-upload');
      row.find('.upload-status').removeClass().addClass('upload-status queue').text(OutoftheBox_vars.str_inqueue);
      row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-circle');

      $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list .files").append(row);

      $('.OutoftheBox .fileuploadform[data-token="' + file.listtoken + '"] div.fileupload-drag-drop').fadeOut();

      if (typeof file.error !== 'undefined' && file.error !== false) {
        uploadFinished(file);
      }

      return row;
    }

    function uploadStart(file) {
      var row = $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list [data-id='" + file.hash + "']");
      row.find('.upload-status').removeClass().addClass('upload-status succes').text(OutoftheBox_vars.str_uploading_local);
      row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-circle-o-notch fa-spin');
      row.find('.upload-progress').slideDown();
      $('input[type="submit"]').prop('disabled', true);
    }

    /* Render the progress of uploading cloud files */
    function uploadProgress(file, status) {
      var row = $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list [data-id='" + file.hash + "']");

      row.find('.progress')
              .attr('aria-valuenow', status.percentage)
              .children().first().fadeIn().animate({
        width: status.percentage + '%'
      }, 'fast');

      if (status.progress === 'uploading_to_cloud') {
        row.find('.upload-status').text(OutoftheBox_vars.str_uploading_cloud);
      }

      if (status.progress === 'finished' || status.progress === 'failed') {
        //uploadFinished(file);
      }
    }

    function uploadFinished(file) {
      var row = $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list [data-id='" + file.hash + "']");

      row.addClass('template-download').removeClass('template-upload');
      row.find('.file-name').text(file.name);
      row.find('.upload-thumbnail img').attr('src', getThumbnail(file));
      row.find('.upload-progress').slideUp();

      if (typeof file.error !== 'undefined' && file.error !== false) {
        row.find('.upload-status').removeClass().addClass('upload-status error').text(OutoftheBox_vars.str_error);
        row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-exclamation-circle');
        row.find('.upload-error').text(file.error).slideUp().delay(500).slideDown();
        _number_of_uploaded_files--;
      } else {
        row.find('.upload-status').removeClass().addClass('upload-status succes').text(OutoftheBox_vars.str_success);
        row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-check-circle');

        if (typeof _uploaded_files[file.listtoken] === 'undefined') {
          _uploaded_files[file.listtoken] = [];
        }
        _uploaded_files[file.listtoken].push(file.fileid);
      }

      if ($(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list").find('.template-upload').length < 1) {
        clearTimeout(_uploadPostProcesstimer);
        _uploadPostProcesstimer = setTimeout(function () {
          uploadPostProcess(file.listtoken);
        }, 1000);
      }

      if (row.closest('.gform_wrapper').length > 0 || ($(".OutoftheBox[data-token='" + file.listtoken + "']").hasClass('upload') === true)) {

      } else {
        row.delay(5000).animate({"opacity": "0"}, "slow", function () {
          $(this).remove();
        });

        if ($(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list").find('.template-upload').length < 1) {
          $('.OutoftheBox .fileuploadform[data-token="' + file.listtoken + '"]').find('div.fileupload-drag-drop').fadeIn();
        }
      }


    }

    /* Upload Notification function
     * to send notifications if needed after upload */
    function uploadPostProcess(listtoken) {

      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: {
          action: 'outofthebox-upload-file',
          type: 'upload-postprocess',
          listtoken: listtoken,
          files: _uploaded_files[listtoken],
          _ajax_nonce: OutoftheBox_vars.upload_nonce
        },
        success: function (response) {
          if (response !== null) {
            _uploaded_files[listtoken] = [];

            if (typeof _DBuploads[listtoken] === 'undefined') {
              _DBuploads[listtoken] = {};
            }
            $.each(response.files, function (fileid, file) {
              _DBuploads[listtoken][fileid] = {
                "name": file.name,
                "path": file.completepath,
                "size": file.filesize,
                "link": file.link
              };
            });

            $('.OutoftheBox .fileuploadform[data-token="' + listtoken + '"] .fileupload-filelist').val(JSON.stringify(_DBuploads[listtoken]));
          }
        },
        error: function (response) {

        },
        complete: function (response) {

          if ($(".OutoftheBox[data-token='" + listtoken + "']").hasClass('upload') === false) {
            /* Update Filelist */
            var formData = {
              listtoken: listtoken
            };
            _DBcache = [];
            clearTimeout(_updatetimer);
            getFileList(formData, 'hardrefresh');
          }

          $('.gform_button:submit').prop("disabled", false).fadeTo(400, 1);
          $('input[type="submit"]').prop('disabled', false);
        },
        dataType: 'json'
      });
    }

    /* Get thumbnail for local and cloud files */
    function getThumbnail(file) {

      var thumbnailUrl = OutoftheBox_vars.plugin_url + '/css/icons/128x128/';
      if (typeof file.thumbnail === 'undefined' || file.thumbnail === null || file.thumbnail === '') {
        var icon;

        if (typeof file.type === 'undefined' || file.type === null) {
          icon = 'unknown';
        } else if (file.type.indexOf("word") >= 0) {
          icon = 'application-msword';
        } else if (file.type.indexOf("excel") >= 0 || file.type.indexOf("spreadsheet") >= 0) {
          icon = 'application-vnd.ms-excel';
        } else if (file.type.indexOf("powerpoint") >= 0 || file.type.indexOf("presentation") >= 0) {
          icon = 'application-vnd.ms-powerpoint';
        } else if (file.type.indexOf("access") >= 0 || file.type.indexOf("mdb") >= 0) {
          icon = 'application-vnd.ms-access';
        } else if (file.type.indexOf("image") >= 0) {
          icon = 'image-x-generic';
        } else if (file.type.indexOf("audio") >= 0) {
          icon = 'audio-x-generic';
        } else if (file.type.indexOf("video") >= 0) {
          icon = 'video-x-generic';
        } else if (file.type.indexOf("pdf") >= 0) {
          icon = 'application-pdf';
        } else if (file.type.indexOf("zip") >= 0 ||
                file.type.indexOf("archive") >= 0 ||
                file.type.indexOf("tar") >= 0 ||
                file.type.indexOf("compressed") >= 0
                ) {
          icon = 'application-zip';
        } else if (file.type.indexOf("html") >= 0) {
          icon = 'text-xml';
        } else if (file.type.indexOf("application/exe") >= 0 ||
                file.type.indexOf("application/x-msdownload") >= 0 ||
                file.type.indexOf("application/x-exe") >= 0 ||
                file.type.indexOf("application/x-winexe") >= 0 ||
                file.type.indexOf("application/msdos-windows") >= 0 ||
                file.type.indexOf("application/x-executable") >= 0
                ) {
          icon = 'application-x-executable';
        } else if (file.type.indexOf("text") >= 0) {
          icon = 'text-x-generic';
        } else {
          icon = 'unknown';
        }
        return thumbnailUrl + icon + '.png';
      } else {
        return file.thumbnail;
      }

    }

    /* drag and drop functionality*/
    $(document).bind('dragover', function (e) {
      var dropZone = $('.OutoftheBox .fileuploadform').closest('.OutoftheBox'),
              timeout = window.dropZoneTimeout;
      if (!timeout) {
        dropZone.addClass('in');
      } else {
        clearTimeout(timeout);
      }
      var found = false, node = e.target;
      do {
        if ($(node).is(dropZone)) {
          found = true;
          break;
        }
        node = node.parentNode;
      } while (node !== null);
      if (found) {
        $(node).addClass('hover');
      } else {
        dropZone.removeClass('hover');
      }
      window.dropZoneTimeout = setTimeout(function () {
        window.dropZoneTimeout = null;
        dropZone.removeClass('in hover');
      }, 100);
    });
    $(document).bind('drop dragover', function (e) {
      e.preventDefault();
    });

    // Resize handlers
    _windowwidth = $(window).width();
    $(window).resize(function () {

      if (_windowwidth === $(window).width()) {
        _windowwidth = $(window).width();
        return;
      }
      _windowwidth = $(window).width();

      $('.OutoftheBox.media.video .jp-jplayer').each(function () {
        var status = ($(this).data().jPlayer.status);
        if (status.videoHeight !== 0 && status.videoWidth !== 0) {
          var ratio = status.videoWidth / status.videoHeight;
          var jpvideo = $(this);
          if ($(this).find('object').length > 0) {
            var jpobject = $(this).find('object');
          } else {
            var jpobject = $(this).find('video');
          }

          if (jpvideo.height() !== jpvideo.width() / ratio) {
            if ((screen.height >= (jpvideo.width() / ratio)) || (status.cssClass !== "jp-video-full")) {
              jpobject.height(jpobject.width() / ratio);
              jpvideo.height(jpobject.width() / ratio);
            } else {
              jpobject.width(screen.height * ratio);
              jpvideo.width(screen.height * ratio);
            }
          }
          $(this).parent().find(".jp-video-play").height(jpvideo.height());

        }

      });

      // set a timer to re-apply the plugin
      if (_resizeTimer) {
        clearTimeout(_resizeTimer);
      }

      $(".OutoftheBox .image-collage").fadeTo(100, 0);

      _resizeTimer = setTimeout(function () {
        $(".OutoftheBox .image-collage").each(function () {
          var listtoken = $(this).closest('.OutoftheBox').attr('data-token');
          updateCollage(listtoken);
        });
      }, 500);
    });

    var downloadURL = function downloadURL(url) {
      var hiddenIFrameID = 'hiddenDownloader',
              iframe = document.getElementById(hiddenIFrameID);
      if (iframe === null) {
        iframe = document.createElement('iframe');
        iframe.id = hiddenIFrameID;
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
      }
      iframe.src = url;
    };

    readArrCheckBoxes = function (element) {
      var values = $(element + ":checked").map(function () {
        return this.value;
      }).get();

      return values;
    };

    iframeFix();


    function iframeFix() {
      /* Safari bug fix for embedded iframes*/
      if (/iPhone|iPod|iPad/.test(navigator.userAgent)) {
        $('iframe.oftb-embedded').each(function () {
          if ($(this).closest('#safari_fix').length === 0) {
            $(this).wrap(function () {
              return $('<div id="safari_fix"/>').css({
                'width': "100%",
                'height': "100%",
                'overflow': 'auto',
                'z-index': '2',
                '-webkit-overflow-scrolling': 'touch'
              });
            });
          }
        });
      }
    }

    $.fn.isOnScreen = function (x, y) {

      if (x == null || typeof x == 'undefined')
        x = 1;
      if (y == null || typeof y == 'undefined')
        y = 1;

      var win = $(window);

      var viewport = {
        top: win.scrollTop(),
        left: win.scrollLeft()
      };
      viewport.right = viewport.left + win.width();
      viewport.bottom = viewport.top + win.height();

      var height = this.outerHeight();
      var width = this.outerWidth();

      if (!width || !height) {
        return false;
      }

      var bounds = this.offset();
      bounds.right = bounds.left + width;
      bounds.bottom = bounds.top + height;

      var visible = (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

      if (!visible) {
        return false;
      }

      var deltas = {
        top: Math.min(1, (bounds.bottom - viewport.top) / height),
        bottom: Math.min(1, (viewport.bottom - bounds.top) / height),
        left: Math.min(1, (bounds.right - viewport.left) / width),
        right: Math.min(1, (viewport.right - bounds.left) / width)
      };

      return (deltas.left * deltas.right) >= x && (deltas.top * deltas.bottom) >= y;

    };
  });
}

function sendGooglePageView(action, value) {
  if (OutoftheBox_vars.google_analytics === "1") {
    if (typeof ga !== "undefined" && ga !== null) {
      ga('send', 'event', 'Out-of-the-Box', action, value);
    } else if (typeof _gaq !== "undefined" && _gaq !== null) {
      _gaq.push(['_trackEvent', 'Out-of-the-Box', action, value]);
    }
  }
}
/* Helper functions */
function humanFileSize(bytes, si) {
  var thresh = si ? 1000 : 1024;
  if (Math.abs(bytes) < thresh) {
    return bytes + ' B';
  }
  var units = si
          ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
          : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
  var u = -1;
  do {
    bytes /= thresh;
    ++u;
  } while (Math.abs(bytes) >= thresh && u < units.length - 1);
  return bytes.toFixed(1) + ' ' + units[u];
}

String.prototype.hashCode = function () {
  var hash = 0, i, char;
  if (this.length === 0)
    return hash;
  for (i = 0, l = this.length; i < l; i++) {
    char = this.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash |= 0; // Convert to 32bit integer
  }
  return Math.abs(hash);
};

Function.prototype.debounce = function (threshold) {
  var callback = this;
  var timeout;
  return function () {
    var context = this, params = arguments;
    window.clearTimeout(timeout);
    timeout = window.setTimeout(function () {
      callback.apply(context, params);
    }, threshold);
  };
};

initate_out_of_the_box();