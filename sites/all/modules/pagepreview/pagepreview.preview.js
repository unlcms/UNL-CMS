(function($){
  // Prevent links, forms, and inputs from unloading the preview page. 
  $(document).delegate('a', 'click', function(ev){
    ev.preventDefault();
    return false;
  });
  $(document).delegate('form', 'submit', function(ev){
    ev.preventDefault();
    return false;
  });
  $(document).delegate('input', 'mousedown', function(ev){
    ev.preventDefault();
    return false;
  });
  $(document).delegate('input', 'click', function(ev){
    ev.preventDefault();
    return false;
  });
})(jQuery);