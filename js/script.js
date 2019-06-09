$(document).ready(function () {
    $('#sidebarCollapse').click(function () {
        let sidebar = $('#sidebar');
        sidebar.toggleClass('active');
        let toggleButton = $("#sidebarCollapse>i");
        if(sidebar.hasClass("active")){
          toggleButton.removeClass("fa-align-right");
          toggleButton.addClass("fa-align-left");
        }else{
          toggleButton.removeClass("fa-align-left");
          toggleButton.addClass("fa-align-right");
        }
    });

    $('#login-btn').click(function(){
      $('#login-popup').prop('hidden', false);
      return false;
    });

    $('#close-login').click(function(){
      $('#login-popup').prop('hidden', true);
      return false;
    });

    $('#register-btn').click(function(){
      $('#register-popup').prop('hidden', false);
      return false;
    });

    $('#close-register').click(function(){
      $('#register-popup').prop('hidden', true);
      return false;
    });
});
