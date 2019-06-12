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

    $('#registerBtn').click(registerUser);

    $('#logout-btn').click(logout)

});

function registerUser(){
    let name = $('#registerName').val();
    let email = $('#registerEmail').val();
    let psw1 = $('#registerPassword').val();
    let psw2 = $('#registerPassword2').val();

    if(name == ""){
        $("#warningName").text("Nome non inserito");
        return;
    }else{$("#warningName").text("");}

    if(email == ""){
        $("#warningEmail").text("Email non inserita");
        return;
    }else{
        let emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if(!email.match(emailRegex)){
            //show warning
            $("#warningEmail").text("Email non valida");
            return;
        }else
            $("#warningEmail").text("");
    }

    if(psw1 == ""){
        $("#warningPsw1").text("Password non inserita");
        return;
    }else{$("#warningPsw1").text("");}

    if(psw2 == ""){
        $("#warningPsw2").text("Password non inserita");
        return;
    }else{$("#warningPsw2").text("");}

    if(psw1 !== psw2){
        $("#warningPsw2").text("Le due password non coincidono");
        return;
    }

    doRegisterRequest(name, email, psw1, psw2);
}

function doRegisterRequest(name, email, psw1, psw2){

    $.post("php/ajax_request.php", getRegisterJSON("registerUser", name, email, psw1, psw2))
        .done(function (data) {
            data = JSON.parse(data);
            let res = data["result"];
            if(res) {
                location.reload();
                return;
            }
            let cause = data["cause"];

            switch (cause) {
                case "no_email":
                    $("#warningEmail").text("Email non inserita o non valida");
                    break;
                case "no_psw1":
                    $("#warningPsw1").text("Password non inserita</>");
                    break;
                case "no_psw2":
                    $("#warningPsw2").text("Password non inserita</>");
                    break;
                case "empty_name":
                    $("#warningName").text("Nome non inserito");
                    break;
                case "psw_mismatch":
                    $("#warningPsw2").text("Le due password non coincidono");
                    break;
                case "user_exist":
                    $("#warningEmail").text("Email già esistente");
                    break;
                default:
                    $("#register-popup").append("<p>Qualcosa è andato storto</p>")
                    break;
            }
        });
}

function getRegisterJSON(method, name, email, psw1, psw2){
    return {
        method: method,
        name: name,
        email: email,
        psw1: psw1,
        psw2: psw2
    };
}

function logout() {
    $.post("php/ajax_request.php", {method: "logout"})
        .done(function (){
            location.reload();
        })
        .fail(function () {

        });
}