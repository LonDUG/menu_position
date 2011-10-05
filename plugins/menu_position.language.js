(function ($) {

/**
 * Provide the summary information for the language plugin's vertical tab.
 */
Drupal.behaviors.menuPositionPagesSettingsSummary = {
  attach: function (context) {
    $('fieldset#edit-language', context).drupalSetSummary(function (context) {
      var val = $('select[name="language"]', context).val();
      if (!val) {
        return Drupal.t('No language restriction');
      }
      else {
        return Drupal.t('Restricted to language') + ' ' + val;
      }
    });
  }
};

})(jQuery);
