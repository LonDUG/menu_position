(function ($) {

/**
 * Provide the summary information for the lolspeak plugin's vertical tab.
 */
Drupal.behaviors.menuPositionLolSpeakSettingsSummary = {
  attach: function (context) {
    $('fieldset#edit-content-type', context).drupalSetSummary(function (context) {
      var vals = [];
      $('input[type="checkbox"]:checked', context).each(function () {
        vals.push($.trim($(this).next('label').text()));
      });
      if (!vals.length) {
        vals.push(Drupal.t('Any language'));
      }
      return vals.join(', ');
    });
  }
};

})(jQuery);
