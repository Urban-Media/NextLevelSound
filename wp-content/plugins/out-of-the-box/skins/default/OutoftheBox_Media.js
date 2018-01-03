jQuery(document).ready(function ($) {
  $(window).load(function () {
    'use strict';

    /* Audio Players*/
    $('.OutoftheBox.media.audio').each(function () {
      var listtoken = $(this).attr('data-token'),
              extensions = $(this).attr('data-extensions'),
              autoplay = $(this).attr('data-autoplay'),
              jPlayerSelector = '#' + $(this).find('.jp-jplayer').attr('id'),
              cssSelector = '#' + $(this).find('.jp-video').attr('id');
      oftb_playlists[listtoken] = new jPlayerPlaylist({
        jPlayer: jPlayerSelector,
        cssSelectorAncestor: cssSelector
      }, [], {
        playlistOptions: {
          autoPlay: (autoplay === '1' ? true : false)
        },
        swfPath: OutoftheBox_vars.js_url,
        supplied: extensions,
        solution: "html,flash",
        wmode: "window",
        size: {
          width: "100%",
          height: "0px"
        },
        ready: function () {
          var data = {
            action: 'outofthebox-get-playlist',
            lastFolder: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-id'),
            sort: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort'),
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.getplaylist_nonce
          };
          $.ajax({
            type: "POST",
            url: OutoftheBox_vars.ajax_url,
            data: data,
            success: function (result) {
              if (result instanceof Array) {
                oftb_playlists[listtoken].setPlaylist(result);
                if (!$(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").hasClass('hideonstart')) {
                  $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").slideDown("slow");
                }

                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").unbind('click');
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").click(function (e) {
                  e.stopPropagation();
                  var href = $(this).attr('href') + '&dl=1',
                          dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html() +
                          " - " + $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-artist").html();

                  sendGooglePageView('Download', dataname);

                  // Delay a few milliseconds for Tracking event
                  setTimeout(function () {
                    window.location = href;
                  }, 300);

                  return false;

                });

                switchSong(listtoken);
              } else {
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-title").html(OutoftheBox_vars.str_error);
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-artist").html(OutoftheBox_vars.str_xhrError_title);
                $("#OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").fadeOut();
              }
            },
            error: function () {
              $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-title").html(OutoftheBox_vars.str_error);
              $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-artist").html(OutoftheBox_vars.str_xhrError_title);
              $("#OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").fadeOut();
            },
            dataType: 'json'
          });
          $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer img").imagesLoaded(function () {
            $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").stop().delay(1500).animate({height: "200px"});
          });

        },
        play: function (e) {
          var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html() +
                  " - " + $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-artist").html();
          switchSong(listtoken);
          sendGooglePageView('Play Music', dataname);
        },
        loadstart: function (e) {
          $(".OutoftheBox[data-token='" + listtoken + "']").find('.jp-song-title .jp-playlist-item-song-artist').html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
        },
        loadedmetadata: function (e) {
          var $song_artist = $(".OutoftheBox[data-token='" + listtoken + "']").find('.jp-playlist ul li.jp-playlist-current .jp-playlist-current .jp-playlist-item-song-artist').html();
          $(".OutoftheBox[data-token='" + listtoken + "']").find('.jp-song-title .jp-playlist-item-song-artist').html($song_artist);
        }
      });
    });


    /* Video Players*/
    $('.OutoftheBox.media.video').each(function () {
      var listtoken = $(this).attr('data-token'),
              extensions = $(this).attr('data-extensions'),
              autoplay = $(this).attr('data-autoplay'),
              jPlayerSelector = '#' + $(this).find('.jp-jplayer').attr('id'),
              cssSelector = '#' + $(this).find('.jp-video').attr('id');
      oftb_playlists[listtoken] = new jPlayerPlaylist({
        jPlayer: jPlayerSelector,
        cssSelectorAncestor: cssSelector
      }, [], {
        playlistOptions: {
          autoPlay: (autoplay === '1' ? true : false)
        },
        swfPath: OutoftheBox_vars.js_url,
        supplied: extensions,
        solution: "html,flash",
        audioFullScreen: true,
        errorAlerts: false,
        warningAlerts: false,
        size: {
          width: "100%",
          height: "100%"
        },
        ready: function (e) {
          var data = {
            action: 'outofthebox-get-playlist',
            lastFolder: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-id'),
            sort: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort'),
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.getplaylist_nonce
          };
          $.ajax({
            type: "POST",
            url: OutoftheBox_vars.ajax_url,
            data: data,
            success: function (result) {
              if (result instanceof Array) {
                oftb_playlists[listtoken].setPlaylist(result);

                if (!$(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").hasClass('hideonstart')) {
                  $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").slideDown("slow");
                }
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").unbind('click');
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").click(function (e) {
                  e.stopPropagation();
                  var href = $(this).attr('href') + '&dl=1',
                          dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html() +
                          " - " + $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-artist").html();

                  sendGooglePageView('Download', dataname);

                  // Delay a few milliseconds for Tracking event
                  setTimeout(function () {
                    window.location = href;
                  }, 300);

                  return false;

                });
              } else {
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-title").html(OutoftheBox_vars.str_error);
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-artist").html(OutoftheBox_vars.str_xhrError_title);
                $("#OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").fadeOut();
              }
              switchSong(listtoken);
            },
            error: function () {
              $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-title").html(OutoftheBox_vars.str_error);
              $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-song-artist").html(OutoftheBox_vars.str_xhrError_title);
              $("#OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").fadeOut();
            },
            dataType: 'json'
          });
          $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height($(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").width() / 1.6);
          $(".OutoftheBox[data-token='" + listtoken + "'] object").width('100%');
          $(".OutoftheBox[data-token='" + listtoken + "'] object").height($(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height());
        },
        ended: function (e) {

        },
        pause: function (e) {
          $(".OutoftheBox[data-token='" + listtoken + "'] .jp-video-play").height($(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height());
        },
        loadedmetadata: function (e) {

          if (e.jPlayer.status.videoHeight !== 0 && e.jPlayer.status.videoWidth !== 0) {
            var ratio = e.jPlayer.status.videoWidth / e.jPlayer.status.videoHeight;
            var videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] object");
            if (e.jPlayer.html.active === true) {
              videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] video");
              videoselector.bind('contextmenu', function () {
                return false;
              });
            }
            if (videoselector.height() === 0 || videoselector.height() !== videoselector.parent().width() / ratio) {
              videoselector.width(videoselector.parent().width());
              videoselector.height(videoselector.width() / ratio);
              $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").animate({height: (videoselector.width() / ratio)});
            }
            $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer img").hide();
          }
        },
        waiting: function (e) {
          var videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] object");
          if (e.jPlayer.html.active === true) {
            videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] video");
            videoselector.bind('contextmenu', function () {
              return false;
            });
          }
        },
        resize: function (e) {
        },
        play: function (e) {
          var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html() +
                  " - " + $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-artist").html();
          sendGooglePageView('Play Video', dataname);
          switchSong(listtoken);

          $('html, body').animate({
            scrollTop: $(".OutoftheBox[data-token='" + listtoken + "'].media").offset().top
          }, 1500);
        }
      });
    });

    function switchSong(listtoken) {
      var $this = $(".OutoftheBox[data-token='" + listtoken + "'].media");

      $this.find(".jp-previous").removeClass('disabled');
      $this.find(".jp-next").removeClass('disabled');

      if (($this.find('.jp-playlist ul li:last-child')).hasClass('jp-playlist-current')) {
        $this.find(".jp-next").addClass('disabled');
      }

      if (($this.find('.jp-playlist ul li:first-child')).hasClass('jp-playlist-current')) {
        $this.find(".jp-previous").addClass('disabled');
      }

      var $song_title = $this.find('.jp-playlist ul li.jp-playlist-current .jp-playlist-current').html();
      $this.find('.jp-song-title').html($song_title);
    }

    $(".OutoftheBox .jp-playlist-toggle").click(function () {
      var $this = $(this).closest('.media');
      $this.find(".jp-playlist").slideToggle("slow");
    });

  });
});