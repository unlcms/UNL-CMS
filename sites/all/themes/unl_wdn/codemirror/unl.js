/**
 * Adds links to enable syntax highlighting to CSS and JS textareas 
 *   under admin/appearance/settings/unl_wdn
 * This code from cpn (Code Per Node) module.
 */

(function ($) {

  Drupal.behaviors.unlCodeMirror = {

    attach: function(context, settings) {

      // Append enable/disable links.
      $('.form-item-unl-css, .form-item-unl-js').each(function() {
        $('.description', this).append(' <a href="#" class="codemirror-toggle">Enable syntax highlighting</a>.');
      });

      // Toggle syntax highlighting.
      $('.codemirror-toggle').click(function() {
        var $textarea = $(this).parents('.form-item').find('textarea');
        var $grippie = $textarea.parents('.resizable-textarea').find('.grippie');
        var type = $textarea.attr('id').replace('edit-unl-', '');

        // Enable
        if (!$(this).hasClass('enabled')) {
          $grippie.hide();
          var editor = CodeMirror.fromTextArea($textarea.get(0), {
            mode: type == 'css' ? 'css' : 'javascript',
            tabMode: 'shift'
          });
          $(this).data('editor', editor);
          $(this).text(Drupal.t('Disable syntax highlighting')).addClass('enabled');
        }

        // Disable
        else {
          $(this).data('editor').toTextArea();
          $grippie.show();
          $(this).text(Drupal.t('Enable syntax highlighting')).removeClass('enabled');
        }
        return false;
      });

    }

  };

})(jQuery);
