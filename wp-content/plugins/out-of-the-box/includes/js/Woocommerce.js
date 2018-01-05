jQuery(function ($) {
  var wc_outofthebox = {
    // hold a reference to the last selected Dropbox button
    lastSelectedButton: false,

    init: function () {
      // add button for simple product
      this.addButtons();
      this.addButtonEventHandler();
      // add buttons when variable product added
      $('#variable_product_options').on('woocommerce_variations_added', function () {
        wc_outofthebox.addButtons();
        wc_outofthebox.addButtonEventHandler();
      });
      // add buttons when variable products loaded
      $('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
        wc_outofthebox.addButtons();
        wc_outofthebox.addButtonEventHandler();
      });

      return this;
    },

    addButtons: function () {
      var button = $('<a href="#TB_inline?height=450&amp;width=800&amp;inlineId=oftb-embedded" class="button insert-outofthebox thickbox">' + outofthebox_woocommerce_translation.choose_from_dropbox + '</a>');
      $('.downloadable_files').each(function (index) {

        // we want our button to appear next to the insert button
        var insertButton = $(this).find('a.button.insert');
        // check if googledrive button already exists on element, bail if so
        if ($(this).find('a.button.insert-outofthebox').length) {
          return;
        }

        // finally clone the button to the right place
        var plugin_button = insertButton.after(button.clone());

      });
    },
    /**
     * Adds the click event to the dropbox buttons
     * and opens the Dropbox chooser
     */
    addButtonEventHandler: function () {
      $('a.button.insert-outofthebox').on('click', function (e) {
        e.preventDefault();

        // save a reference to clicked button
        wc_outofthebox.lastSelectedButton = $(this);

      });
    },
    /**
     * Handle selected files
     */
    afterFileSelected: function (id, name) {
      var table = $(wc_outofthebox.lastSelectedButton).closest('.downloadable_files').find('tbody');
      var template = $(wc_outofthebox.lastSelectedButton).parent().find('.button.insert:first').data("row");
      var fileRow = $(template);

      // Create Base64 Object
      var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9+/=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/rn/g,"n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

      fileRow.find('.file_name > input').val(name).change();
      fileRow.find('.file_url > input').val(outofthebox_woocommerce_translation.download_url + encodeURIComponent(Base64.encode(id)));
      table.append(fileRow);

      // trigger change event so we can save variation
      $(table).find('input').last().change();
    }

  };
  window.wc_outofthebox = wc_outofthebox.init();
});



