const AJAXURL = "php/ajax_request.php";
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

    $('#loginBtn').click(login);

    $('#logout-btn').click(logout);

    $('.my-checkbox input[type="checkbox"]').change(function () {
        if ($(this).is(":checked")){
            doPreorderSeat($(this));
        }else{
            doCancelSeat($(this));
        }
    });

    $('#btn-cancelPreorderedSeats').click(cancelPreorderedSeats);

    $('#btn-compra').click(buySeats);
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
        if(!validateEmail(email)){
            //show warning
            $("#warningEmail").text("Email non valida");
            return;
        }else
            $("#warningEmail").text("");
    }

    if(psw1 == ""){
        $("#warningPsw1").text("Password non inserita");
        return;
    }else{
        if(!validatePassword(psw1))    {
            $("#warningPsw1").text("La password deve contenere almeno un carattere minuscolo e almeno un carattere maiuscolo o numerico");
            return;
        }else
            $("#warningPsw1").text("");
    }

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

    $.post(AJAXURL, getRegisterJSON("registerUser", name, email, psw1, psw2))
        .done(function (data) {
            data = JSON.parse(data);
            let res = data["result"];
            if(res) {
                location.reload();
                return;
            }
            let cause = data["cause"];

            switch (cause) {
                case "db_error":
                    $("#warningDefault").text("Connessione al database rifiutata");
                    break;
                case "no_email":
                    $("#warningEmail").text("Email non inserita o non valida");
                    break;
                case "no_psw1":
                    $("#warningPsw1").text("Password non inserita");
                    break;
                case "no_psw2":
                    $("#warningPsw2").text("Password non inserita");
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
                case "invalid_psw":
                    $("#warningPsw1").text("La password deve contenere almeno un carattere minuscolo e almeno un carattere maiuscolo o numerico");
                    break;
                default:
                    $("#warningDefault").text("Qualcosa è andato storto");
                    break;
            }
        })
        .fail(function () {
            $("#warningDefault").text("Qualcosa è andato storto");
        });
}

function validateEmail(email){
    let emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return email.match(emailRegex);
}

function validatePassword(psw){
    if (typeof psw !== 'string') {
        throw "Psw parameter must be a string"
    }
    let lower = false;
    let upperOrDigit = false;

    for(let i = 0; i< psw.length ; i++){
        let char = psw.charAt(i);
        if(!isNaN(char) || char === char.toUpperCase())
            upperOrDigit = true;
        if(char === char.toLowerCase())
            lower = true;

        if(lower && upperOrDigit)
            return true;
    }

    return false;
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

function login(){
    let email = $('#loginEmail').val();
    let psw = $('#loginPassword').val();

    if(email == ""){
        $("#warningEmail-log").text("Email non inserita");
        return;
    }else{
        if(!validateEmail(email)){
            //show warning
            $("#warningEmail-log").text("Email non valida");
            return;
        }else
            $("#warningEmail-log").text("");
    }

    if(psw == ""){
        $("#warningPsw-log").text("Password non inserita");
        return;
    }else
        $("#warningPsw-log").text("");

    doLoginRequest(email, psw);
}

function doLoginRequest(email, psw) {
    $.post(AJAXURL,
        {
            method: "login",
            email: email,
            psw: psw
        })
        .done(function (data) {
            data = JSON.parse(data);
            let res = data["result"];
            if(res) {
                location.reload();
                return;
            }
            let cause = data["cause"];
            switch (cause) {
                case "db_error":
                    $("#warningDefault-log").text("Connessione al database rifiutata");
                    break;
                case "no_email":
                    $("#warningEmail-log").text("Email non inserita o non valida");
                    break;
                case "no_psw":
                    $("#warningPsw-log").text("Password non inserita");
                    break;
                case "mismatch":
                    $("#warningDefault-log").text("Email o password errati");
                    break;
                default:
                    $("#warningDefault-log").text("Qualcosa è andato storto");
                    break;
            }
        })
        .fail(function () {
            $("#warningDefault-log").text("Qualcosa è andato storto");
        });
}

function logout() {
    $.post(AJAXURL, {method: "logout"})
        .done(function (){
            location.reload();
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });
}

function doPreorderSeat(checkbox){
    console.log(checkbox.attr("id"));

    $.post(AJAXURL,
        {
            method: "preorderSeat",
            id: checkbox.attr("id")
        })
        .done(function (data){
            data = JSON.parse(data);
            let res = data["result"];

            if(res) return;
            let cause = data['cause'];
            if(cause === "already_bought"){
                $("#error-field").text("Il posto è già stato comprato e non e' possibile prenotarlo!");
                checkbox.prop("disabled", true);
                checkbox.prop("checked", false);
                checkbox.parent().attr("disabled", "");
                checkbox.parent().attr("state", "bought");
            }
            else
                alert("Qualcosa è andato storto");
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });
}

function doCancelSeat(checkbox){
    $.post(AJAXURL,
        {
            method: "cancelSeat",
            id: checkbox.attr("id")
        })
        .done(function (data){
            data = JSON.parse(data);
            let res = data["result"];

            if(res){
                checkbox.parent().removeAttr("state");
                return;
            }

            let cause = data['cause'];
            alert("Qualcosa è andato storto: " + cause);
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });
}

function cancelPreorderedSeats(){
    $.post(AJAXURL, {method: "cancelPreorderedSeats"})
        .done(function (data){
            data = JSON.parse(data);
            let res = data["result"];

            if(res){
                location.reload();
                return;
            }
            let cause = data['cause'];
            alert("Qualcosa è andato storto: " + cause);
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });

    return false;
}

function buySeats() {
    let ids = [];
    let next = 0;
    let checkboxes = $('.my-checkbox');

    checkboxes.each(function () {
       let box = $(this).children("input");
       if(box.is(":checked"))
           ids[next++] = box.attr("id");
    });

    console.log(ids);

    $.post(AJAXURL,
        {
            method: "buySeats",
            ids: ids
        })
        .done(function (data) {
            data = JSON.parse(data);
            let res = data["result"];

            if(res){
                location.reload();
                return;
            }
            let cause = data['cause'];
            switch (cause) {
                case "invalid_email":
                    $('#error-field').text("Il tuo account non è stato trovato nel database");
                    break;
                case "not_your_seat":
                    window.location.href = data['redirect'];
                    break;

                default:
                    alert("Qualcosa è andato storto: " + cause);
                    break;
            }
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });
}