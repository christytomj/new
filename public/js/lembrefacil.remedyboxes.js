(function($) {
    $.addRemedyBoxes = function(element, options) {
        var pg = {
            root: $(element),
            values: $('#remedyBoxesValues'),
            rows: new Array(),
            currentRow: null,
            createTable: function() {
                var legend = pg.root.find('legend').clone();
                pg.root.append('<div id="addContainer" class="formRow">'
                        + '<div class="formElement">'
                        + '<input type="button" value="Adicionar" '
                        + 'class="groupAdd" /></div></div>')
                   .wrapInner('<table class="remedyBoxesTable"><tfoot>'
                        + '<tr><td/></tr></tfoot></table>')
                   .find('legend').remove();
                var table = pg.root.find('.remedyBoxesTable')
                    .before(legend)
                    .prepend('<thead><tr></tr></thead><tbody></tbody>');
                var header = table.find('thead > tr');
                header.append('<th>Remédio</th><th>Quantidade</th>'
                    + '<th class="tableActions"></th>');
                pg.root.find('tfoot tr td').attr('colSpan', 3);

                var values = pg.values.val();
                if (values) {
                    values = $.secureEvalJSON(values);
                    for (var i in values) pg.addRow(values[i]);
                }
                table.find('.groupAdd').click(function() {
                    pg.addRow();
                    $('#content')
                            .animate({ scrollTop: pg.root.offset().top }, 0);
                    return false;
                });
            },
            rowsToValues: function() {
                 pg.values.val($.compactJSON(pg.rows));
            },
            getFormValues: function() {
                var error = false;
                var inRemedy = $('#remedy');
                var inQty = $('#qty');
                var row = new Array();

                // Pega os valores
                row[0] = inRemedy.val();
                row[1] = inQty.val();
                row[2] = inRemedy.find('option[value='+row[0]+']')
                        .attr('label');

                // Erros
                pg.root.find('.programmingGroupTable .formRow')
                    .removeClass('formError').find('.errors').remove();
                if (!row[0]) {
                    inRemedy.parent().append(
                            '<ul class="errors">'
                            + '<li>Preenchimento obrigatório</li></ul>')
                        .parent().addClass('formError');
                    error = true;
                }
                if (!row[1]) {
                    inQty.parent().append(
                            '<ul class="errors">'
                            + '<li>Preenchimento obrigatório</li></ul>')
                        .parent().addClass('formError');
                    error = true;
                } else if (parseInt(row[1]) === NaN) {
                    inQty.parent().append(
                            '<ul class="errors"><li>A quantidade inserida '
                            + 'não é valida</li></ul>')
                        .parent().addClass('formError');
                    error = true;
                }
                if (error == true) return false;

                pg.resetFormValues();
                return row;
            },
            resetFormValues: function() {
                $('#remedy').val('');
                $('#qty').val('');
            },
            setFormValues: function(row) {
                pg.resetFormValues();

                $('#remedy').val(row[0]);
                $('#qty').val(row[1]);

                pg.root.find('.remedyBoxesTable .formRow')
                    .removeClass('formError')
                    .find('.errors').remove();
            },
            addRow: function(row) {
                if (row == undefined) row = pg.getFormValues();
                if (row != false) {
                    if (pg.currentRow != null) {
                        pg.rows[pg.currentRow] = row;
                    } else {
                        pg.rows.push(row);
                    }
                    pg.rowsToValues();

                    if (pg.currentRow != null)  {
                        pg.root.find(
                                '.remedyBoxesTable tbody tr:eq('
                                + pg.currentRow
                                + ')')
                            .replaceWith(pg.createRow(row));
                        pg.currentRow = null;
                    } else {
                        pg.root.find('.remedyBoxesTable tbody')
                            .append(pg.createRow(row));
                    }
                }
            },
            removeRow: function(key) {
                pg.rows.splice(key, 1);
                pg.root.find('.remedyBoxesTable tbody tr:eq(' + key + ')')
                    .remove();
                pg.rowsToValues();
            },
            editRow: function(key) {
                pg.root.find(
                        '.programmingGroupTable tbody tr:eq('
                        + pg.currentRow + ')')
                    .removeClass('edit');
                pg.currentRow = key;
                pg.root.find(
                        '.programmingGroupTable tbody tr:eq('
                        + key + ')')
                    .addClass('edit');
                pg.setFormValues(pg.rows[key]);
            },
            createRow: function(row) {
                var i;
                var tr = $('<tr/>').addClass(
                        (pg.rows.length % 2) ? 'even' : 'odd');
                tr.append('<td>' + row[2] + '</td>');
                tr.append('<td>' + row[1] + '</td>');

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
                return tr
                    .append($('<td class="tableActions"></td>')
                    .append(edit)
                    .append(remove));
            }
        };
        element.remedyBoxes = pg;
        pg.createTable();

        $(element).next().find('input').click(function() {
            var form = $(element).parent('form');
            form.submit();
            return false;
            /*
            var message = 'Esta programação terá um custo a ser definido '
                    + 'pelo desenvolvedor do software. '
                    + 'Deseja prosseguir assim mesmo?';

            var form = $(element).parent('form');
            var dialog = $('#dialog');
            dialog.find('.jqmContent').addClass('confirmType').html(message);
            dialog.find('.jqmCommands').html(
                    '<a class="jqmConfirm">Prosseguir</a> '
                    + '<a class="jqmCancel">Cancelar</a>');
            dialog.jqmAddClose('.jqmCancel')
                    .find('.jqmConfirm')
                    .click(function() {
                        form.submit();
                    });
            dialog.jqmShow();

           // $.showDialog('confirm', message, function() {
           //     form.submit();
           // }, 'Prosseguir');
            return false;
           */
        });
    };

    $.fn.remedyBoxes = function(options) {
        return this.each(function() {
            $.addRemedyBoxes(this, options);
        });
    };
})(jQuery);