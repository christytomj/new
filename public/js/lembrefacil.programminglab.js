(function($) {
    $.addProgrammingLab = function(element, options) {
        var pg = {
            root: $(element),
            values: $('#descriptionsProgrammingValues'),
            rows: new Array(),
            currentRow: null,
            sendType: 0,
            createTable: function() {
                var legend = pg.root.find('legend').clone();
                pg.root.append(
                        '<div id="addContainer" class="formRow">'
                        + '<div class="formElement"><input type="button" '
                        + 'value="Adicionar" class="groupAdd" />'
                        + '</div></div>')
                    .wrapInner(
                        '<table class="programmingGroupTable">'
                        + '<tfoot><tr><td></td></tr></tfoot></table>')
                    .find('legend').remove();
                var table = pg.root.find('.programmingGroupTable')
                        .before(legend)
                        .prepend('<thead><tr></tr></thead><tbody></tbody>');
                var header = table.find('thead > tr');
                
                header.append(
                    '<th>Remédio</th>'
                    + '<th>Quant.</th>'
                    + '<th>Horários</th>'
                    + '<th>Repetição</th>'
                    + '<th class="tableActions"></th>');
                pg.root.find('tfoot tr td').attr('colSpan', 7);

                var values = pg.values.val();
                if (values) {
                    values = $.secureEvalJSON(values);
                    for (var i in values) pg.addRow(values[i]);
                }
                table.find('.groupAdd').click(function() {
                    pg.addRow();
                    $('#content').animate({ scrollTop: pg.root.offset().top }, 0);
                    return false;
                });
            },
            rowsToValues: function() {
                 pg.values.val($.compactJSON(pg.rows));
            },
            getFormValues: function() {
                var error = false;
                var r0 = $('#remedy');
                var rQT = $('#qt_pills');
                var rQB = $('#qt_cx');
                var r1 = $('#dt_start');
                var r2 = $('#dt_end');
                var r3 = $('#time');
                var r4 = $('#reminder');
                var r5 = $('input[name=in_repetition]:checked');
                var row = new Array('','','','','','','','','','','','','');

                row[pg.IDX_TYPE] = '3'; // sms send option LAB
                // Pega os valores
                var remsel = r0.find('option[value='+r0.val()+']');
                row[pg.IDX_DESCR] = remsel.html();
                row[pg.IDX_RDESCR] = remsel.attr('descr');
                row[pg.IDX_RMDY] = remsel.attr('value');
                row[pg.IDX_QTY] = rQT.val();
                row[pg.IDX_QTB] = rQB.val();
                row[pg.IDX_DTS] = 0;
                row[pg.IDX_DTE] = 0;
                row[pg.IDX_HR] = new Array();
                r3.find('.formElementMultiValues').each(function(i) {
                    var t = $(this).val();
                    if (t) row[pg.IDX_HR].push(t);
                });
                row[pg.IDX_RMND] = r4.val();
                row[pg.IDX_REP] = r5.val();
                switch(parseInt(row[pg.IDX_REP], 10)) {
                    case 1:
                        row[pg.IDX_FREQ] = $('#frequency').val();
                        switch(parseInt(row[pg.IDX_FREQ], 10)) {
                            case 2:
                                row[pg.IDX_DUR] = new Array();
                                $('input[name=week_frequency[]]:checked')
                                    .each(function(i) {
                                        row[pg.IDX_DUR].push($(this).val());
                                    }
                                );
                                break;
                            case 3:
                                row[pg.IDX_DUR] = new Array();
                                $('#mouth_frequency .formElementMultiValues')
                                    .each(function(i) {
                                        row[pg.IDX_DUR].push($(this).val());
                                    }
                                );
                                break;
                            case 4:
                                row[pg.IDX_DUR] =
                                        $('#year_day_frequency').val();
                                row[pg.IDX_INTR] =
                                        $('#year_mouth_frequency').val();
                                break;
                        }
                        break;
                    case 2:
                        if ($('#interrupt-0').attr('checked') == true) {
                            row[pg.IDX_FREQ] = 0;
                            row[pg.IDX_DUR] = new Array();
                            row[pg.IDX_DUR] = $('#during_days').val();
                            row[pg.IDX_INTR] = $('#interrupt_days').val();
                        } else if ($('#interrupt-1').attr('checked') == true) {
                            row[pg.IDX_FREQ] = 1;
                            row[pg.IDX_DUR] = $('#interval_days').val();
                        }
                        break;
                }


                // Erros
                pg.root.find('.programmingGroupTable .formRow')
                    .removeClass('formError').find('.errors').remove();
                if (!row[pg.IDX_DESCR]) {
                    pg.setMsgErr(r0, pg.MSG_PREEN);
                    error = true;
                } else {
                    if (row[pg.IDX_DESCR].length > 150) {
                        pg.setMsgErr(r0, pg.MSG_TOOLONG);
                        error = true;
                    }
                    if (remsel.val() == '') {
                        pg.setMsgErr(r0, pg.MSG_PREEN);
                        error = true;
                    }
                }
                if (row[pg.IDX_QTY] && row[pg.IDX_QTB]) {
                    pg.setMsgErr(rQT, pg.MSG_SOUM);
                    pg.setMsgErr(rQB, pg.MSG_SOUM);
                    error = true;
                }
                if (!row[pg.IDX_QTY] && !row[pg.IDX_QTB]) {
                    pg.setMsgErr(rQT, pg.MSG_PMUM);
                    pg.setMsgErr(rQB, pg.MSG_PMUM);
                    error = true;
                }
                if (row[pg.IDX_QTY] && !parseInt(row[pg.IDX_QTY], 10)) {
                    pg.setMsgErr(rQT, pg.MSG_NOINT);
                    error = true;
                }
                if (row[pg.IDX_QTB] && !parseInt(row[pg.IDX_QTB], 10)) {
                    pg.setMsgErr(rQB, pg.MSG_NOINT);
                    error = true;
                }
                /*
                if (!row[pg.IDX_DTS]) {
                    pg.setMsgErr(r1, pg.MSG_PREEN);
                    error = true;
                } else if (!dateVerify(row[pg.IDX_DTS])) {
                    pg.setMsgErr(r1, pg.MSG_DTINV);
                    error = true;
                }
                if (row[pg.IDX_DTE]) {
                    if (!dateVerify(row[pg.IDX_DTE])) {
                        pg.setMsgErr(r2, pg.MSG_DTINV);
                        error = true;
                    } else if (row[pg.IDX_DTS]) {
                        Date.fromDDMMYYYY = function (s) {
                            return (/^(\d\d?)\D(\d\d?)\D(\d{4})$/).test(s)
                                ? new Date(RegExp.$3, RegExp.$2 - 1, RegExp.$1)
                                : new Date (s)
                        }
                        var start = Date.fromDDMMYYYY(row[pg.IDX_DTS]);
                        var end = Date.fromDDMMYYYY(row[pg.IDX_DTE]);
                        if (end < start) {
                            pg.setMsgErr(r2, pg.MSG_DTORD);
                            error = true;
                        }
                    }
                }
                */
                if (!row[pg.IDX_HR][0]) {
                    pg.setMsgErr(r3, pg.MSG_PREEN);
                    error = true;
                } else {
                    for (var i in row[pg.IDX_HR]) {
                        if (!timeVerify(row[pg.IDX_HR][i])) {
                            r3.find('.formElementMultiValues:eq(' + i + ')')
                                .next()
                                .after(pg.MSG_HRINV).parent().addClass('formError');
                            error = true;
                        }
                    }
                }
                if (row[pg.IDX_RMND] && !intVerify(row[pg.IDX_RMND])) {
                    pg.setMsgErr(r4, pg.MSG_NOINT);
                    error = true;
                }
                if (parseInt(row[pg.IDX_REP], 10) == 1 
                        && parseInt(row[pg.IDX_FREQ], 10) == 2
                        && !row[pg.IDX_DUR][0]) {
                    pg.setMsgErr($('#week_frequency-7'), pg.MSG_PREEN);
                    error = true;
                }
                if (parseInt(row[pg.IDX_REP], 10) == 2) {
                    if (!row[pg.IDX_DUR]
                            || (row[pg.IDX_FREQ] == 0 && !row[pg.IDX_INTR])) {
                        pg.setMsgErr($('#interrupt-0').parent(), pg.MSG_PREEN);
                        error = true;
                    } else {
                        if (row[pg.IDX_FREQ] == 0 
                                && (!intVerify(row[pg.IDX_DUR])
                                    || !intVerify(row[pg.IDX_INTR]))) {
                            $('#interrupt-0')
                                .parent().after(pg.MSG_NOINT)
                                .parent().addClass('formError');
                            error = true;
                        } else if (row[pg.IDX_FREQ] == 1 && !intVerify(row[pg.IDX_DUR])) {
                            $('#interrupt-1')
                                .parent().after(pg.MSG_NOINT)
                                .parent().addClass('formError');
                            error = true;
                        }
                    }
                }
                if (error == true) return false;

                pg.resetFormValues();
                return row;
            },
            resetFormValues: function() {
                console.log("reset");
                $('#remedy').val('');
                $('#qt_pills').val('');
                $('#time button:contains(-)').click();
                $('#time input').val('');
                $('#reminder').val('');
//                $('#in_repetition-0').attr('checked', true);
                $('#frequency').val(1);
                $('.programmingGroupTable fieldset').hide();
                $('input[name=week_frequency[]]').removeAttr('checked');
                $('#mouth_frequency button:contains(-)').click();
                $('#mouth_frequency input').val('');
                $('#year_mouth_frequency').val('');
                $('#year_day_frequency').val('');
                $('input[name=interrupt]').removeAttr('checked');
                $('#during_days').val('');
                $('#interrupt_days').val('');
                $('#interval_days').val('');
            },
            setFormValues: function(row) {
                console.log("set");
                pg.resetFormValues();

                $('#remedy').val(row[pg.IDX_DESCR]);
                $('#qt_pills').val(row[pg.IDX_QTY]);
                $('#dt_cx').val(row[pg.IDX_QTB]);
                var i;
                for (i in row[pg.IDX_HR]) {
                    if (i == 0) $('#time input:last').val(row[pg.IDX_HR][i]);
                    else {
                        $('#time button:contains(+)').click();
                        $('#time input:last').val(row[pg.IDX_HR][i]);
                    }
                }
                $('#reminder').val(row[pg.IDX_RMND]);
                $('#in_repetition-' + row[pg.IDX_REP]).attr('checked', true);
                
                if (row[pg.IDX_REP] == '1') {
                    $('#frequency').val(row[pg.IDX_FREQ]);
                    $('#interact-in_repetition-1').show();
                    if (row[pg.IDX_FREQ] != '1') {
                        $('#frequency-' + row[pg.IDX_FREQ]).show();
                    }
                    switch (row[pg.IDX_FREQ]) {
                        case '2':
                            for (i in row[pg.IDX_DUR]) {
                                $('#week_frequency-' + row[pg.IDX_DUR][i])
                                    .attr('checked', 'checked');
                            }
                            break;
                        case '3':
                            for (i in row[pg.IDX_DUR]) {
                                if (i == 0) {
                                    $('#mouth_frequency input:last')
                                        .val(row[pg.IDX_DUR][i]);
                                }
                                else {
                                    $('#mouth_frequency button:contains(+)')
                                        .click();
                                    $('#mouth_frequency input:last')
                                        .val(row[pg.IDX_DUR][i]);
                                }
                            }
                            break;
                        case '4':
                            $('#year_mouth_frequency').val(row[pg.IDX_DUR]);
                            $('#year_day_frequency').val(row[pg.IDX_INTR]);
                            break;
                    }
                } else if (row[pg.IDX_REP] == '2') {
                    $('#interact-in_repetition-2').show();
                    $('#interrupt-' + row[pg.IDX_FREQ])
                        .attr('checked', 'checked');
                    if (row[pg.IDX_FREQ] == '0') {
                        $('#during_days').val(row[pg.IDX_DUR]);
                        $('#interrupt_days').val(row[pg.IDX_INTR]);
                    } else {
                        $('#interval_days').val(row[pg.IDX_DUR]);
                    }
                }

                pg.root.find('.programmingGroupTable .formRow')
                    .removeClass('formError')
                    .find('.errors').remove();
            },
            addRow: function(row) {
                console.log("addrow");
                if (row == undefined) row = pg.getFormValues();
                if (row != false) {
                    if (pg.currentRow != null) pg.rows[pg.currentRow] = row;
                    else pg.rows.push(row);
                    pg.rowsToValues();

                    if (pg.currentRow != null)  {
                        pg.root
                            .find(
                                '.programmingGroupTable tbody tr:eq('
                                + pg.currentRow + ')')
                            .replaceWith(pg.createRow(row));
                        pg.currentRow = null;
                    } else {
                        pg.root.find('.programmingGroupTable tbody')
                            .append(pg.createRow(row));
                    }
                }
            },
            removeRow: function(key) {
                console.log("remove");
                pg.rows.splice(key, 1);
                pg.root.find('.programmingGroupTable tbody tr:eq(' + key + ')').remove();
                pg.rowsToValues();
            },
            editRow: function(key) {
                pg.root.find(
                    '.programmingGroupTable tbody tr:eq(' + pg.currentRow + ')')
                    .removeClass('edit');
                pg.currentRow = key;
                pg.root.find('.programmingGroupTable tbody tr:eq(' + key + ')').addClass('edit');
                pg.setFormValues(pg.rows[key]);
            },
            createRow: function(row) {
                var i;
                var tr = $('<tr/>').addClass((pg.rows.length % 2) ? 'even' : 'odd');
                tr.append('<td>' + row[pg.IDX_DESCR] + '</td>');
                if (row[pg.IDX_QTY]) {
                    tr.append('<td>' + row[pg.IDX_QTY] + ' (avisos)</td>');
                } else {
                    tr.append('<td>' + row[pg.IDX_QTB] + ' (caixas)</td>');
                }
                var times = '';
                for (i in row[pg.IDX_HR]) {
                    times += '<li>' + row[pg.IDX_HR][i] + '</li>';
                }
                tr.append('<td><ul>' + times + '</ul></td>');

                var repetition;
                switch (parseInt(row[pg.IDX_REP], 10)) {
                    case 0:
                        repetition = 'Nenhuma';
                        break;
                    case 1:
                        switch (parseInt(row[pg.IDX_FREQ], 10)) {
                            case 1:
                                repetition = 'Com freqüência diária';
                                break;
                            case 2:
                                repetition = 'Com freqüência semanal:<ul>';
                                for (i in row[pg.IDX_DUR]) {
                                    var day = $('label[for='+ $('input[name=week_frequency[]][value=' +  row[pg.IDX_DUR][i] + ']').attr('id') + ']').text()
                                    repetition += '<li>' + day + '</li>';
                                }
                                repetition += '</ul>';
                                break;
                            case 3:
                                repetition = 'Com freqüência mensal:<ul>';
                                for (i in row[pg.IDX_DUR]) {
                                    repetition += '<li>' + row[pg.IDX_DUR][i] + '</li>';
                                }
                                repetition += '</ul>';
                                break;
                            case 4:
                                repetition = 'Com freqüência anual, no dia ' + row[pg.IDX_DUR] + ' de ';
                                repetition += $('#year_mouth_frequency option[value=' + row[pg.IDX_INTR] + ']').text();
                                break;
                        }
                        break;
                    case 2:
                        if (row[pg.IDX_FREQ] === 0) repetition = 'Envio durante ' + row[pg.IDX_DUR] + ' dias com a interrupção de ' + row[pg.IDX_INTR]  + ' dias';
                        else repetition = 'Envio a cada ' + row[pg.IDX_DUR] + ' dias';
                        break;
                }
                tr.append('<td><ul>' + repetition + '</ul></td>');

                var edit = $(
                        '<a class="groupEdit">'
                        + '<img title="Editar" alt="Editar" '
                        + 'src="../images/icons/16x16/edit.png"/></a>')
                    .bind("click", function (e) {
                        pg.editRow($(e.target).closest('tr')[0].sectionRowIndex);
                        return false;
                    });
                var remove = $(
                        '<a class="groupRemove">'
                        + '<img title="Remover" alt="Remover" '
                        + 'src="../images/icons/16x16/remove_circle.png"/></a>')
                    .bind("click", function (e) {
                        pg.removeRow($(e.target).closest('tr')[0].sectionRowIndex);
                        return false;
                    });
                return tr.append($('<td class="tableActions"></td>').append(edit).append(remove));
            },
            setMsgErr: function (elem, msg) {
                elem.parent().append(msg)
                    .parent().addClass('formError');
            },
            IDX_TYPE: 0,
            IDX_DESCR: 9,
            IDX_DTS: 1,
            IDX_DTE: 2,
            IDX_HR: 3,
            IDX_RMND: 4,
            IDX_REP: 5,
            IDX_FREQ: 6,
            IDX_DUR: 7,
            IDX_INTR: 8,
            IDX_RMDY: 10,
            IDX_QTY: 11,
            IDX_RDESCR: 12,
            IDX_QTB: 13,
            MSG_PREEN: '<ul class="errors"><li>'
                        + 'Preenchimento obrigatório</li></ul>',
            MSG_NOINT: '<ul class="errors"><li>'
                        + 'O valor inserido não é inteiro</li></ul>',
            MSG_DTINV: '<ul class="errors"><li>'
                        + 'A data inserida não é valida</li></ul>',
            MSG_TOOLONG: '<ul class="errors"><li>'
                        + 'Mensagem muito longa (com mais de 150 caracteres)'
                        + '</li></ul>',
            MSG_DTORD: '<ul class="errors"><li>'
                        + 'A data de término é menor que a de início</li></ul>',
            MSG_HRINV: '<ul class="errors"><li>'
                        + 'A hora inserida não é valida</li></ul>',
            MSG_SOUM: '<ul class="errors"><li>'
                        + 'Preencha apenas uma das quantidades</li></ul>',
            MSG_PMUM: '<ul class="errors"><li>'
                        + 'Preencha uma das quantidades</li></ul>'

        };
        element.programmingGroup = pg;
        pg.createTable();

        /*
        $(element).next().find('input').click(function() {
            var message = 
                    'Esta programação terá um custo a ser definido pelo '
                    + 'desenvolvedor do software e a conta deve adquirir '
                    + 'um pacote SMS para receber mensagens. '
                    + 'Deseja prosseguir assim mesmo?';
            var form = $(element).parent('form');

            var dialog = $('#dialog');
            dialog.find('.jqmContent').addClass('confirmType').html(message);
            dialog.find('.jqmCommands').html(
                    '<a class="jqmConfirm">Prosseguir</a>'
                    + '<a class="jqmCancel">Cancelar</a>');
            dialog.jqmAddClose('.jqmCancel').find('.jqmConfirm').click(function() {
                form.submit();
            });
            dialog.jqmShow();

           // $.showDialog('confirm', message, function() {
           //     form.submit();
           // }, 'Prosseguir');
            return false;
        });
        */
    };

    $.fn.programmingLab = function(options) {
        return this.each(function() {
            $.addProgrammingLab(this, options);
        });
    };
})(jQuery);