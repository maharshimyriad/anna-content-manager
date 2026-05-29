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

  function getCollapseWrap(toggleBtn) {
    return toggleBtn.closest('.anna-repeater-collapse');
  }

  function getCollapsePanel(wrap) {
    return wrap.find('.anna-repeater-collapse__panel').first();
  }

  function isPanelCollapsed(panel) {
    return panel.hasClass('is-collapsed');
  }

  function setPanelCollapsed(panel, collapsed) {
    if (collapsed) {
      panel.addClass('is-collapsed').hide();
    } else {
      panel.removeClass('is-collapsed').show();
    }
  }

  function updateRepeaterCollapseLabel(wrap) {
    var toggle = wrap.find('.anna-repeater-collapse__toggle').first();
    var panel = getCollapsePanel(wrap);
    var repeater = panel.find('[data-anna-content-repeater]').first();
    var count = repeater.find('[data-anna-content-repeater-row="true"]').length;
    var expanded = toggle.attr('aria-expanded') === 'true';
    var showText = 'Show all cards (' + count + ')';
    var hideText = 'Hide all cards (' + count + ')';

    toggle.find('.anna-repeater-collapse__label').text(expanded ? hideText : showText);
  }

  function initRepeaterCollapses() {
    $('.anna-repeater-collapse').each(function () {
      var wrap = $(this);
      var toggle = wrap.find('.anna-repeater-collapse__toggle').first();
      var panel = getCollapsePanel(wrap);

      if (!toggle.length || !panel.length) {
        return;
      }

      var expanded = toggle.attr('aria-expanded') === 'true';
      setPanelCollapsed(panel, !expanded);
      updateRepeaterCollapseLabel(wrap);
    });
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

    var wrap = repeater.closest('.anna-repeater-collapse');
    if (wrap.length) {
      updateRepeaterCollapseLabel(wrap);
    }
  });

  $(document).on('click', '[data-anna-content-repeater-remove="true"]', function (event) {
    event.preventDefault();
    var repeater = $(this).closest('[data-anna-content-repeater]');
    $(this).closest('[data-anna-content-repeater-row="true"]').remove();

    var wrap = repeater.closest('.anna-repeater-collapse');
    if (wrap.length) {
      updateRepeaterCollapseLabel(wrap);
    }
  });

  $(document).on('click', '.anna-repeater-collapse__toggle', function (event) {
    event.preventDefault();
    event.stopPropagation();

    var toggle = $(this).closest('.anna-repeater-collapse__toggle');
    if (!toggle.length) {
      toggle = $(this);
    }

    var wrap = getCollapseWrap(toggle);
    var panel = getCollapsePanel(wrap);
    var collapsed = isPanelCollapsed(panel);

    if (collapsed) {
      toggle.attr('aria-expanded', 'true');
      panel.removeClass('is-collapsed').slideDown(200);
    } else {
      toggle.attr('aria-expanded', 'false');
      panel.slideUp(200, function () {
        panel.addClass('is-collapsed');
      });
    }

    updateRepeaterCollapseLabel(wrap);
  });

  $(function () {
    initRepeaterCollapses();
  });
})(jQuery);
