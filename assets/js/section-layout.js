(function ($) {
  'use strict';

  function toggleIdField($row) {
    var type = $row.find('.anna-section-layout__type').val();
    var $wrap = $row.find('.anna-section-layout__id-wrap');
    if (type === 'text-image') {
      $wrap.show();
    } else {
      $wrap.hide();
    }
  }

  $(document).on('change', '.anna-section-layout__type', function () {
    toggleIdField($(this).closest('.anna-section-layout__row'));
  });

  $('.anna-section-layout__row').each(function () {
    toggleIdField($(this));
  });
})(jQuery);
