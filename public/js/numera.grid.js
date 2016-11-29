/*
 * Configura conteúdo do tipo lista
 */
(function($) {
    $.addNumeraGrid = function(element, config) {
        var ajaxArea = element.ajaxArea.root;
        var flex = ajaxArea.append($('<table/>').attr('id', 'flex')).children('#flex');
        flex.flexigrid({
            buttons : createTableButtons(config.buttons),
            colModel : config.columns,
            dataType: 'json',
            errormsg: 'Erro na conexão',
            height: '100%',
            width: '100%',
            nomsg: 'Nenhum item',
            pagestat: 'Mostrando {from} a {to} de {total} itens',
            procmsg: 'Processando, aguarde ...',
        //    resizable: false,
            rp: 15,
            searchitems : config.searchItems,
            showToggleBtn: false,
            sortname: config.sortName,
            sortorder: config.sortOrder,
            usepager: true,
            useRp: true,
            url: config.url
        });

        if (config.advancedSearch) {
            ajaxArea.find('.flexigrid').css('top', '100px').before(
                    '<div class="advancedSearch">'
                    + 'Período <input type="text" id="start" />'
                    + 'até <input type="text" id="end" /> &nbsp;&nbsp;&nbsp; '
                    + 'Assinante <input type="text" id="name" />'
                    + '<button>Buscar</button></div>');
            $('#start').datepicker({
                showOn: 'button',
                buttonImage: '../images/icons/calendar.gif',
                buttonImageOnly: true
            }).addClass('embedCalendar');
            $('#end').datepicker({
                showOn: 'button',
                buttonImage: '../images/icons/calendar.gif',
                buttonImageOnly: true
            }).addClass('embedCalendar');

            $('#name').keypress(function (e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                    $(this).siblings('button').click();
                    return false;
                } else {
                    return true;
                }
            });

            $('.advancedSearch button').click(function() {
                flex.flexOptions({
                    query: $.compactJSON({
                        name: $('#name').val(),
                        start: $('#start').val(),
                        end: $('#end').val()
                    })
                })
                .flexReload({url: config.url});
            });
        }

        function actionContent(command, options) {
            var text = null;
            var button = null;
            switch (command) {
                case 'block':
                    text = 'bloquear';
                    button = 'Bloquear';
                    break;
                case 'unblock':
                    text = 'desbloquear';
                    button = 'Desbloquear';
                    break;
                case 'delete':
                    text = 'remover';
                    button = 'Remover';
                    break;
            }

            var message = (options.message) ? options.message : 'Tem certeza que deseja ' + text + ' as linhas selecionadas?';

            var selectedRows = $("#flex").flexGetSelectedRows();
            if (selectedRows.length > 0) {
                $.showDialog('confirm', message, function() {
                    for (i = 0; i < selectedRows.length; i++) {
                        var rowId = selectedRows[i].id.substr(3);
                        $.post(
                            options.url,
                            { id: rowId, command: command },
                            function() {
                                $('.pReload').click();
                                $.closeDialog();
                            }
                        );
                    }
                }, button);
            }
            else {
                var emptyMessage = (options.emptyMessage) ? options.emptyMessage : 'Nenhuma linha da tabela foi selecionada.';
                $.showDialog('alert', emptyMessage);
            }
        };

        function loadContent(command, options) {
            element.ajaxArea.update(options.url);
        };

        function createTableButtons(buttonConfig) {
            var buttons = [];
            for (var i in buttonConfig) {
                var name = '';
                if (typeof(buttonConfig[i].label) != 'undefined') {
                    name = buttonConfig[i].label;
                }
                var buttonClass = '';
                var callback = actionContent;
                var callbackArg = { url: buttonConfig[i].url};
                if (buttonConfig[i].message)
                {
                    callbackArg.message = buttonConfig[i].message;
                    callbackArg.emptyMessage = buttonConfig[i].emptyMessage;
                }
                var type = buttonConfig[i].type;
                switch (type) {
                   case 'export':
                        if (name == '') name = 'Exportar';
                        buttonClass = 'export';
                        callback = exportContent;
                        break;
                    case 'exportall':
                        if (name == '') name = 'Exportar Todos';
                        buttonClass = 'exportall';
                        callback = exportAllContent;
                        break;
                    case 'viewselect':
                        if (name == '') name = 'Visualizar';
                        buttonClass = 'viewselect';
                        callback = viewContent;
                        break;
                    case 'add':
                        if (name == '') name = 'Adicionar';
                        buttonClass = 'add';
                        callback = loadContent;
                        break;
                    case 'block':
                        if (name == '') name = 'Bloquear';
                        buttonClass = 'block';
                        break;
                    case 'unblock':
                        if (name == '') name = 'Desbloquear';
                        buttonClass = 'unblock';
                        break;
                    case 'remove':
                        if (name == '') name = 'Remover';
                        buttonClass = 'delete';
                        break;
                    case 'approve':
                        if (name == '') name = 'Aprovar';
                        buttonClass = 'approve';
                        break;
                    default:
                        // TODO: (de mauro) porque não deixar a classe default
                        // sendo o tipo?
                        //if (name == '') name = '';
                        buttonClass = type;
                        break;
                }
                buttons[i] = {
                    'name': name,
                    'bclass': buttonClass,
                    'callback': callback,
                    'callbackArg': callbackArg
                };
            }
            return buttons;
        };
    }


    $.fn.numeraGrid = function(options) {
        return this.each(function() {
            $.addNumeraGrid(this, options);
        });
    }
})(jQuery);
