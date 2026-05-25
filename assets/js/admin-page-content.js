(function ($) {
  'use strict';

  function openMediaFrame(targetId, previewId) {
    var frame = wp.media({
      title: 'Select image',
      button: { text: 'Use image' },
      multiple: false
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $('#' + targetId).val(attachment.id);
      $('#' + previewId).html('<img src="' + attachment.url + '" alt="" style="max-width:240px;height:auto;border-radius:10px;">');
    });

    frame.open();
  }

  $(document).on('click', '.anna-content-media-select', function (event) {
    event.preventDefault();
    openMediaFrame($(this).data('target'), $(this).data('preview'));
  });

  $(document).on('click', '.anna-content-media-remove', function (event) {
    event.preventDefault();
    $('#' + $(this).data('target')).val('');
    $('#' + $(this).data('preview')).empty();
  });
})(jQuery);
