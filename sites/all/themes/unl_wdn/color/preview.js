
(function ($) {
  Drupal.color = {
    logoChanged: false,
    callback: function(context, settings, form, farb, height, width) {
      // Change the logo to be the real one.
      if (!this.logoChanged) {
        $('#preview #preview-logo img').attr('src', Drupal.settings.color.logo);
        this.logoChanged = true;
      }
      // Remove the logo if the setting is toggled off. 
      if (Drupal.settings.color.logo == null) {
        $('div').remove('#preview-logo');
      }

      // Text preview.
      $('#preview #preview-content a', form).css('color', $('#palette input[name="palette[link]"]', form).val());

      // CSS3 Gradients - navigation.
      var gradient_start = $('#palette input[name="palette[top]"]', form).val();
      var gradient_end = $('#palette input[name="palette[bottom]"]', form).val();

      $('#preview #preview-main-menu', form).attr('style', "background-color: " + gradient_start + "; background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(" + gradient_start + "), to(" + gradient_end + ")); background-image: -moz-linear-gradient(-90deg, " + gradient_start + ", " + gradient_end + ");");

      // CSS3 Gradients - navigation hover.
      var navhover_start = $('#palette input[name="palette[navhovertop]"]', form).val();
      var navhover_end = $('#palette input[name="palette[navhoverbottom]"]', form).val();
      
      $('#preview #preview-main-menu', form).hover(
        function(){
          $(this).attr('style', "background-color: " + navhover_start + "; background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(" + navhover_start + "), to(" + navhover_end + ")); background-image: -moz-linear-gradient(-90deg, " + navhover_start + ", " + navhover_end + ");");
        },
        function(){
          $(this).attr('style', "background-color: " + gradient_start + "; background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(" + gradient_start + "), to(" + gradient_end + ")); background-image: -moz-linear-gradient(-90deg, " + gradient_start + ", " + gradient_end + ");");
        }
      );

      // CSS3 Gradients - footer.
      var footer_start = $('#palette input[name="palette[footertop]"]', form).val();
      var footer_end = $('#palette input[name="palette[footerbottom]"]', form).val();

      $('#preview #preview-footer-wrapper', form).attr('style', "background-color: " + footer_start + "; background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(" + footer_start + "), to(" + footer_end + ")); background-image: -moz-linear-gradient(-90deg, " + footer_start + ", " + footer_end + ");");

    }
  };
})(jQuery);