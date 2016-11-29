//var home = 'http://189.8.192.146/teste/lembrefacil/admin/';
//var home = 'http://localhost/lembrefacil/admin/';

$(document).ready(function() {
    $('#content').ajaxArea();
});


function reportSearch(e) {
    $(e).parent('form').ajaxSubmit({
        url: document.location + 'reports/grid',
        type: 'post',
        success: function(response) {
            $('#content').modifyAjaxArea($.secureEvalJSON(response));
        }
    });
    return false;
}

function exportContent() {
    var selectedRows = $("#flex").flexGetSelectedRows();
    var message
            = '<p class="dialogInput">Multiplicar por (Aplicativo Java)'
            + '<input id="multiplyj" type="text" class="formElementCurrency" />'
            + '</p>'
            + '<p class="dialogInput">Multiplicar por (SMS)'
            + '<input id="multiplys" type="text" class="formElementCurrency" />'
            + '</p>'
            + '<p class="dialogInput">Somar com '
            + '<input id="add" type="text" class="formElementCurrency" />'
            + '</p>'
            + '<p class="dialogInput">Vencimento '
            + '<input id="maturity" type="text" class="formElementDate"/></p>';
    if (selectedRows.length > 0) {
        $.showFormDialog(message, function() {

            var start = $('#start').val();
            var end = $('#end').val();
            var maturity = $('#maturity').val();
            var redt = new RegExp('^\\d{2}/\\d{2}/\\d{4}$', 'g');
            if (! redt.test(maturity)) {
                alert(
                    'Data de vencimento inválida "'+maturity+'".'
                    + "\n" + 'Use o formato dd/mm/aaaa.'
                );
                $('#maturity').focus();
                return false;
            }
            var multiplyj = $('#multiplyj').val().replace(",", ".");
            if (! checkNumeric($('#multiplyj'))) {
                return false;
            }
            var multiplys = $('#multiplys').val().replace(",", ".");
            if (! checkNumeric($('#multiplys'))) {
                return false;
            }
            var add = $('#add').val().replace(",", ".");
            if (! checkNumeric($('#add'))) {
                return false;
            }
            var query
                    = 'multiplyj/' + multiplyj
                    + '/multiplys/' + multiplys
                    + '/add/' + add
                    + '/maturity/'
                        + (maturity ? maturity.replace(/\//g, '_') : 0) + '/';
            query += 'start/' + (start ? start.replace(/\//g, '_') : 0)
                    + '/end/' + (end ? end.replace(/\//g, '_') : 0);
            var selected = '';
            for (i = 0; i < selectedRows.length; i++) {
                var rowId = selectedRows[i].id.substr(3);
                selected += ((selected == '') ? rowId : ',' + rowId);
            }
            var loc = getHomeURL() + 'reports/export/' + query
                    + '/ids/' + selected;

            window.location = loc;
            return true;
        });
    } else {
        var emptyMessage = 'Nenhuma linha da tabela foi selecionada.';
        $.showDialog('alert', emptyMessage);
    }    
}

function checkNumeric(elem) {
    var re = new RegExp('^\\d+,\\d{2}$', "g");
    var vl = elem.val().toString();
    if (! re.test(vl)) {
        alert('Valor numérico "'
            +vl
            +'" inválido.'
            +"\n"+"Digite valor numérico com duas casas decimais."
        );
        elem.focus();
        return false;
    }
    return true;
}

function getHomeURL() {
    var u = document.location.toString();
    u = u.substr(0, u.indexOf("#"));
    
    return u;
}

function viewContent() {
    var selectedRows = $("#flex").flexGetSelectedRows();
    //var message = 'Tem certeza que deseja exportar as linhas selecionadas?';

    if (selectedRows.length > 0) {
        //$.showDialog('confirm', message, function() {
            var start = $('#start').val();
            var end = $('#end').val();
            //var query = 'multiply/10/add/50/';
            var query = 'start/' + (start ? start.replace(/\//g, '_') : '')
                    + '/end/' + (end ? end.replace(/\//g, '_') : '');
            var selected = '';
            for (i = 0; i < selectedRows.length; i++) {
                var rowId = selectedRows[i].id.substr(3);
                selected += ((selected == '') ? rowId : ',' + rowId);
            }
            var loc = getHomeURL() + '#reports/viewselect/' + query
                    + '/ids/' + selected;

            window.location = loc;
        //});
    }
    else {
        var emptyMessage = 'Nenhuma linha da tabela foi selecionada.';
        $.showDialog('alert', emptyMessage);
    }
}

function exportAllContent() {
    var message = '<p class="dialogInput">Multiplicar por (Aplicativo Java)'
            + '<input id="multiplyj" type="text" class="formElementCurrency" />'
            + '</p><p class="dialogInput">Multiplicar por (SMS)'
            + '<input id="multiplys" type="text" class="formElementCurrency" />'
            + '</p><p class="dialogInput">Somar com '
            + '<input id="add" type="text" class="formElementCurrency" />'
            + '</p><p class="dialogInput">Vencimento '
            + '<input id="maturity" type="text" class="formElementDate"/></p>';
    $.showFormDialog(message, function() {
        var start = $('#start').val();
        var end = $('#end').val();
        var name = $('#name').val();
        var maturity = $('#maturity').val();
        var redt = new RegExp('^\\d{2}/\\d{2}/\\d{4}$', 'g');
        if (! redt.test(maturity)) {
            alert(
                'Data de vencimento inválida "'+maturity+'".'
                + "\n" + 'Use o formato dd/mm/aaaa.'
            );
            $('#maturity').focus();
            return false;
        }
        var multiplyj = $('#multiplyj').val().replace(",", ".");
        if (! checkNumeric($('#multiplyj'))) {
            return false;
        }
        var multiplys = $('#multiplys').val().replace(",", ".");
        if (! checkNumeric($('#multiplys'))) {
            return false;
        }
        var add = $('#add').val().replace(",", ".");
        if (! checkNumeric($('#add'))) {
            return false;
        }
        var query 
                = 'multiplyj/' + multiplyj
                + '/multiplys/' + multiplys
                + '/add/' + add
                + '/maturity/' + (maturity ? maturity.replace(/\//g, '_') : 0)
                + '/start/' + (start ? start.replace(/\//g, '_') : 0)
                + '/end/' + (end ? end.replace(/\//g, '_') : 0)
                + '/name/' + (name ? name : 0);
        window.location = getHomeURL() + 'reports/exportall/' + query;
    });
}