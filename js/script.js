const AJAXURL = "php/ajax_request.php";
const INDEX = "index.php";
const COOKIE_DISABLED = "cookies_disabled.html";

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

    $(this).keypress(function(event){
        if(event.which === 13){
            if($('#login-popup').prop('hidden') === false){
                login();
            }else if($('#register-popup').prop('hidden') === false){
                registerUser();
            }
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

function areCookiesEnabled() {
    let enabled = navigator.cookieEnabled;
    if(!enabled)
        window.location.href = COOKIE_DISABLED;
}

function registerUser(){
    areCookiesEnabled();
    let name = $('#registerName').val();
    let email = $('#registerEmail').val();
    let psw1 = $('#registerPassword').val();
    let psw2 = $('#registerPassword2').val();

    /*if(name == ""){
        $("#warningName").text("Nome non inserito");
        return;
    }else{$("#warningName").text("");}*/

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
            data = parseJSON(data);
            let res = data["result"];
            if(res) {
                success(INDEX);
                return;
            }
            let cause = data["cause"];

            switch (cause) {
                case "no_email":
                    $("#warningEmail").text("Email non inserita o non valida");
                    break;
                case "no_psw1":
                    $("#warningPsw1").text("Password non inserita");
                    break;
                case "no_psw2":
                    $("#warningPsw2").text("Password non inserita");
                    break;
                /*case "empty_name":
                    $("#warningName").text("Nome non inserito");
                    break;*/
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
    areCookiesEnabled();
    let email = $('#loginEmail').val();
    let psw = $('#loginPassword').val();

    if(email == ""){
        $("#warningEmail-log").text("Email non inserita");
        return;
    }else{
        if(!validateEmail(email)){
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
            psw: psw,
            remember: $('#loginCheck').is(":checked")
        })
        .done(function (data) {
            data = parseJSON(data);
            let res = data["result"];
            if(res) {
                success(INDEX);
                return;
            }
            let cause = data["cause"];

            switch (cause) {
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
    areCookiesEnabled();
    $.post(AJAXURL, {method: "logout"})
        .done(function (){
            success(INDEX);
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });
}

function doPreorderSeat(checkbox){
    areCookiesEnabled();
    console.log(checkbox.attr("id"));

    $.post(AJAXURL,
        {
            method: "preorderSeat",
            id: checkbox.attr("id")
        })
        .done(function (data){
            data = parseJSON(data);
            let res = data["result"];

            if(res){
                if(data['success-msg'] === "success") {
                    $('#error-field').text("");
                    $('#success-field').text("Preordine effettuato con successo!");
                    updateStats(0, 1, -1);
                }
                else if(data['success-msg'] === "overwritten"){
                    $('#success-field').text("");
                    $('#error-field').text("Hai sovrascritto la prenotazione di un altro utente").css("color", "#FF5722");
                    if(checkbox.parent().attr("state") !== 'preordered')
                        updateStats(0, 1, -1);
                }

                return;
            }
            $('#success-field').text("");
            let cause = data['cause'];
            switch (cause) {
                case "already_bought":
                    $("#error-field").text("Il posto è già stato comprato e non è possibile prenotarlo!");
                    checkbox.prop("disabled", true);
                    checkbox.prop("checked", false);
                    checkbox.parent().attr("disabled", "");
                    checkbox.parent().attr("state", "bought");
                    updateStats(1, 0, -1);
                    break;
                case "session_expired":
                    window.location.href = data['redirect'];
                    break;
                default:
                    alert("Qualcosa è andato storto");
                    break;

            }
        })
        .fail(function () {
            alert("Qualcosa è andato storto");
        });
}

function doCancelSeat(checkbox){
    areCookiesEnabled();
    $.post(AJAXURL,
        {
            method: "cancelSeat",
            id: checkbox.attr("id")
        })
        .done(function (data){
            data = parseJSON(data);
            let res = data["result"];

            if(res){
                checkbox.parent().removeAttr("state");
                $('#error-field').text("");
                $('#success-field').text("Preordine cancellato con successo!");
                updateStats(0, -1, 1);
                return;
            }
            $('#success-field').text("");
            let cause = data['cause'];
            switch (cause) {
                case "session_expired":
                    window.location.href = data['redirect'];
                    break;
                case "not_your_seat":
                    $("#error-field").text("Il posto era già stato prenotato da altri");
                    checkbox.parent().attr("state", "preordered");
                    updateStats(0, 1, -1);
                    break;
                case "your_seat":
                    $("#error-field").text("Hai già comprato questo posto");
                    checkbox.prop("disabled", true);
                    checkbox.prop("checked", false);
                    checkbox.parent().attr("disabled", "");
                    checkbox.parent().attr("state", "bought");
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

function cancelPreorderedSeats(){
    areCookiesEnabled();
    $.post(AJAXURL, {method: "cancelPreorderedSeats"})
        .done(function (data){
            data = parseJSON(data);
            let res = data["result"];

            if(res){
                success(data['success-msg']);
                //location.reload();
                return;
            }

            $('#success-field').text("");
            let cause = data['cause'];
            switch (cause) {
                case "session_expired":
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

    return false;
}

function buySeats() {
    areCookiesEnabled();
    let ids = [];
    let next = 0;
    let checkboxes = $('.my-checkbox');

    checkboxes.each(function () {
       let box = $(this).children("input");
       if(box.is(":checked"))
           ids[next++] = box.attr("id");
    });

    if(next === 0){
        $('#success-field').text("");
        $('#error-field').text("Non hai nessun posto da acquistare");
        return;
    }
    console.log(ids);

    $.post(AJAXURL,
        {
            method: "buySeats",
            ids: ids
        })
        .done(function (data) {
            data = parseJSON(data);
            let res = data["result"];

            if(res){
                success(data['success-msg']);
                //location.reload();
                return;
            }
            $('#success-field').text("");
            let cause = data['cause'];
            switch (cause) {
                case "invalid_email":
                    $('#error-field').text("Il tuo account non è stato trovato nel database");
                    break;
                case "not_your_seat":
                    window.location.href = data['redirect'];
                    break;
                case "session_expired":
                    window.location.href = data['redirect'];
                    break;
                case "update_error":
                    $('#error-field').text("C'è stato un errore durante la transazione");
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

function parseJSON(data){
    let index = data.indexOf("{\"result\"");
    if(index === -1) {
        let index2 = data.indexOf("{\"cause\"");
        if(index2 === -1)
            location.reload();
        else
            index = index2;
    }

    data = data.substring(index);
    return JSON.parse(data);
}

function success(res){
    window.location.href = res;
}

function updateStats(boughtInc, preorderedInc, freeInc){
    if(isNaN(boughtInc) || isNaN(preorderedInc) || isNaN(freeInc))
        return;

    let totalSeat = parseInt($('#totalSeat span').text());
    let bought = parseInt($('.progress>.bg-red').text());
    let preordered = parseInt($('.progress>.bg-orange').text());
    let free = parseInt($('.progress>.bg-success').text());

    bought += boughtInc;
    preordered += preorderedInc;
    free += freeInc;

    let freePerc, boughtPerc, preorderedPerc;
    if(totalSeat !== 0){
        freePerc = free/totalSeat === 0 ? 2 : free/totalSeat*100;
        boughtPerc = bought/totalSeat === 0 ? 2 : bought/totalSeat*100;
        preorderedPerc = preordered/totalSeat === 0 ? 2 : preordered/totalSeat*100;
    }else{
        freePerc = 2;
        boughtPerc = 2;
        preorderedPerc = 2;
    }


    $('.progress>.bg-red').text(""+bought);
    $('.progress>.bg-red').attr("style", "width: " +boughtPerc+"%");
    $('.progress>.bg-red').attr("aria-valuenow", ""+boughtPerc);

    $('.progress>.bg-orange').text(""+preordered);
    $('.progress>.bg-orange').attr("style", "width: " +preorderedPerc+"%");
    $('.progress>.bg-orange').attr("aria-valuenow", ""+preorderedPerc);

    $('.progress>.bg-success').text(""+free);
    $('.progress>.bg-success').attr("style", "width: " +freePerc+"%");
    $('.progress>.bg-success').attr("aria-valuenow", ""+freePerc);
}