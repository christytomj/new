    /*
     * Validações
     */
    function cpfVerify(cpf) {
        cpf = cpf.substr(0,3) + cpf.substr(4,3) + cpf.substr(8,3) + cpf.substr(12,2);
        if (cpf.length != 11 || cpf == "00000000000" || cpf == "11111111111" ||
            cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" ||
            cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" ||
            cpf == "88888888888" || cpf == "99999999999") {
            return false;
        }

        var i = 0;
        var add = 0;
        for (i = 0; i < 9; i++) {
            add += parseInt(cpf.charAt(i)) * (10 - i);
        }
        var rev = 11 - (add % 11);
        if (rev == 10 || rev == 11) {
            rev = 0;
        }
        if (rev != parseInt(cpf.charAt(9))) {
            return false;
        }
        add = 0;
        for (i = 0; i < 10; i ++) {
            add += parseInt(cpf.charAt(i)) * (11 - i);
        }
        rev = 11 - (add % 11);
        if (rev == 10 || rev == 11) {
            rev = 0;
        }
        if (rev != parseInt(cpf.charAt(10))) {
            return false;
        }
        return true;
    }

    function cgcVerify(cgc) {
        cgc = cgc.substr(0,2) + cgc.substr(3,3) + cgc.substr(7,3) + cgc.substr(11,4) + cgc.substr(16,2);
        if (cgc.length != 14 || cgc == "00000000000000" || cgc == "11111111111111" ||
            cgc == "22222222222222" || cgc == "33333333333333" || cgc == "44444444444444" ||
            cgc == "55555555555555" || cgc == "66666666666666" || cgc == "77777777777777" ||
            cgc == "88888888888888" || cgc == "99999999999999") {
            return false;
        }
        // Calcula o valor do 13º digito de verificação
        var i = 0;
        var add = 0;
        var digit = new Array(12);
        for (i = 0; i < 12; i++) {
            digit[i] = parseInt(cgc.charAt(i));
        }
        var digits = new Array(5,4,3,2,9,8,7,6,5,4,3,2);
        for (i = 0; i < 12; i++) {
            add += digit[i] * digits[i];
        }
        var rev = add % 11;
        if (rev == 0 || rev == 1) {
            rev = 0;
        } else {
            rev = 11 - rev;
        }
        if (rev != parseInt(cgc.charAt(12))) {
            return false;
        }
        // Calcula o valor do 14º digito de verificação
        var aux = rev;
        add = 0;
        var digit = new Array(12);
        for (i = 0; i < 12; i ++) {
            digit[i] = parseInt(cgc.charAt(i));
        }
        var digits = new Array(6,5,4,3,2,9,8,7,6,5,4,3);
        for (i = 0; i < 12; i++) {
            add += digit[i] * digits[i];
        }
        add += 2 * aux;
        var rev = add % 11;
        if (rev == 0 || rev == 1) {
            rev = 0;
        } else {
            rev = 11 - rev;
        }
        if (rev != parseInt(cgc.charAt(13))) {
            return false;
        }

        return true;
    }

    function dateVerify(date) {
        date = date.split("/");
        if (date[0] == parseInt(date[0], 10) && date[1] == parseInt(date[1], 10) && date[2] == parseInt(date[2], 10) &&
            date[0].length == 2 && date[1].length == 2 && date[2].length == 4 &&
            date[0] > 0 && date[0] < 32 && date[1] > 0 && date[1] < 13 && date[2] > 0) {
            return true;
        }
        return false;
    }

    function monthVerify(date) {
        date = date.split("/");

        d = new Date();
        year = d.getFullYear();

        if (date[0] == parseInt(date[0], 10) && date[1] == parseInt(date[1], 10) &&
            date[0].length == 2 && date[1].length == 4 &&
            date[0] > 0 && date[0] < 13 && date[1] >= year) {
            return true;
        }
        return false;
    }

    function cepVerify(cep) {
        cep = cep.substr(0,5) + cep.substr(6,3);
        if (cep.length != 8) {
            return false;
        }
        return true;
    }

    function emailVerify(email) {
        return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(email);
    }

    function siteVerify(url) {
        return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
    }

    function cellPhoneVerify(cell) {
        //sao paulo agora permite mais digitos.
        cell = cell.split("(")[1];
        cell = cell.split(")");

        if (cell[0] == parseInt(cell[0], 10) && cell[1] == parseInt(cell[1], 10) &&
            cell[0].length == 2 && (cell[1].length == 9 || cell[1].length == 8 || cell[1].length == 7)) {
            return true;
        }
        return false;
    }

    function multiTimeVerify(time) {
        time = time.split(":");
        if (time[0] == parseInt(time[0], 10) && time[1] == parseInt(time[1], 10) &&
            time[0].length == 2 && time[1].length == 2 &&
            time[0] >= 0 && time[0] < 25 && time[1] >= 0 && time[1] < 61) {
            return true;
        }
        return false;
    }

    function timeVerify(time) {
        time = time.split(":");
        if (time[0] == parseInt(time[0], 10) && time[1] == parseInt(time[1], 10) &&
            time[0].length == 2 && time[1].length == 2 &&
            time[0] >= 0 && time[0] < 25 && time[1] >= 0 && time[1] < 61) {
            return true;
        }
        return false;
    }

    function intVerify(value) {
        return (parseInt(value, 10) == value);
    }
