/**
 * @file
 * Adds draggable functionality to the table display of the view.
 */

(function ($) {
  Drupal.behaviors.draggableviewsAutosave = {
    attach: function(){
      if (typeof Drupal.tableDrag == 'undefined') {
        return;
      }
      for (var prop in Drupal.tableDrag){
        if (prop.substring(0, 14) == 'draggableviews'){
          var table = Drupal.tableDrag[prop];
          table.onDrop = function() {
            // Hide change messages that are not relevant when saving form
            // through AJAX.
            $('.tabledrag-changed').hide();
            $('.tabledrag-changed-warning').hide();
            $table = $(this.table);
            // Submit form with AJAX.
            $table.parent().find('#edit-actions input').triggerHandler('mousedown');
            // The previously dragged row is left with class styling the row
            // yellow style, indicating unsaved state. To increate UX we remove
            // this class with some delay to indicate that progress was made in
            // the background.
            $('.drag-previous').removeClass('drag-previous');
            $('<div class="draggableviews-changed-notice messages warning">' + Drupal.t('Order of this view has been changed.') + '</div>')
              .insertBefore($table).hide().fadeIn('slow').delay(3000).fadeOut('slow');
          }
          // Hide Save button.
          $('#' + prop).parent().find('#edit-actions input').hide();
        }
      }
    }
  }
})(jQuery);
