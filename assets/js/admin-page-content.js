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

  $(document).on('click', '[data-anna-content-repeater-add="true"]', function (event) {
    event.preventDefault();

    var repeater = $(this).closest('[data-anna-content-repeater]');
    var rowsWrap = repeater.find('[data-anna-content-repeater-rows="true"]').first();
    var template = repeater.find('[data-anna-content-repeater-template="true"]').first();

    if (!template.length || !rowsWrap.length) {
      return;
    }

    var index = rowsWrap.find('[data-anna-content-repeater-row="true"]').length;
    var html = template.html().replace(/__INDEX__/g, String(index));
    rowsWrap.append(html);
    updateRepeaterCollapseLabel(repeater);
  });

  $(document).on('click', '[data-anna-content-repeater-remove="true"]', function (event) {
    event.preventDefault();
    $(this).closest('[data-anna-content-repeater-row="true"]').remove();
    updateRepeaterCollapseLabel($(this).closest('[data-anna-content-repeater]'));
  });

  function updateRepeaterCollapseLabel(repeater) {
    var collapse = repeater.closest('.anna-repeater-collapse');
    if (!collapse.length) {
      return;
    }

    var count = repeater.find('[data-anna-content-repeater-row="true"]').length;
    var toggle = collapse.find('[data-anna-repeater-collapse-toggle="true"]').first();
    var expanded = toggle.attr('aria-expanded') === 'true';
    var showText = 'Show all cards (' + count + ')';
    var hideText = 'Hide all cards (' + count + ')';

    toggle.find('.anna-repeater-collapse__label').text(expanded ? hideText : showText);
  }

  $(document).on('click', '[data-anna-repeater-collapse-toggle="true"]', function (event) {
    event.preventDefault();

    var toggle = $(this);
    var panel = toggle.closest('.anna-repeater-collapse').find('[data-anna-repeater-collapse-panel="true"]').first();
    var expanded = toggle.attr('aria-expanded') === 'true';
    var repeater = panel.find('[data-anna-content-repeater]').first();

    toggle.attr('aria-expanded', expanded ? 'false' : 'true');
    panel.toggleClass('is-collapsed', expanded);

    if (repeater.length) {
      updateRepeaterCollapseLabel(repeater);
    }
  });

  $('[data-anna-content-repeater]').each(function () {
    updateRepeaterCollapseLabel($(this));
  });
})(jQuery);
