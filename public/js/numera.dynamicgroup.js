(function($) {
    $.addDynamicGroup = function(element, options) {
        var dg = {
            root: $(element),
            values: $('#' + (element.id).replace(/fieldset-/, '') + 'DynamicValues'),
            columns: new Array(),
            rows: new Array(),
            currentRow: null,
            createTable: function() {
                var legend = dg.root.find('legend').clone();
                dg.root.append('<div id="addContainer" class="formRow"><div class="formElement"><input type="button" value="Adicionar" class="groupAdd" /></div></div>')
                       .wrapInner('<table class="dynamicGroupTable"><tfoot><tr><td></td></tr></tfoot></table>')
                       .find('legend').remove();
                var table = dg.root.find('.dynamicGroupTable').before(legend).prepend('<thead><tr></tr></thead><tbody></tbody>');
                var header = table.find('thead > tr');
                table.find('tfoot').find('.formRow[id!=addContainer]').each(function() {
                    var colLabel = $(this).find('.formLabel').html();
                    header.append('<th>' + colLabel + '</th>');

                    var colElement = $(this).find('.formElement');
                    var colRequired = (colElement.hasClass('required')) ? true : false;
                    if (colRequired) $(this).find('.formLabel').prepend('*');

                    var colType = null;
                    var input = colElement.children(':first');
                    if (input.is('[type=text]')) colType = 'text';
                    else if (input.is('textarea')) colType = 'textarea';
                    else if (input.is('select')) colType = 'select';
                    else if (input.is('[type=radio]')) colType = 'radio';
                    else if (input.is('[type=checkbox]')) colType = 'checkbox';
                    else if (input.hasClass('multiCheckboxes')) colType = 'multicheckboxes';

                    dg.columns.push({label: colLabel, type: colType, required: colRequired, element: colElement});
                });
                header.append($('<th/>').addClass('tableActions'));
                dg.root.find('tfoot tr td').attr('colspan', dg.columns.length);

                var values = dg.values.val();
                if (values) {
                    values = $.secureEvalJSON(values);
                    for (var i in values) dg.addRow(values[i]);
                }
                table.find('.groupAdd').click(function() {
                    dg.addRow();
                    $('#content').animate({ scrollTop: dg.root.offset().top }, 0);
                    return false;
                });
            },
            rowsToValues: function() {
                 dg.values.val($.compactJSON(dg.rows));
            },
            getFormValues: function() {
                var row = new Array();
                for (var i in dg.columns) {
                    var input = null;
                    switch (dg.columns[i].type) {
                        case 'text':
                        case 'textarea':
                            input = dg.columns[i].element.find(':input');
                            row[i] = input.val();
                            input.val('');
                            break;
                        case 'select':
                            input = dg.columns[i].element.find(':input');
                            row[i] = input.val();
                            if (row[i] == 0) row[i] = null;
                            input.val(0);
                            break;
                        case 'radio':
                            input = dg.columns[i].element.find(':input');
                            row[i] = input.val();
                            input.val('');
                            break;
                        case 'checkbox':
                            input = dg.columns[i].element.find(':input');
                            row[i] = input.val();
                            input.attr('checked', false);
                            break;
                         case 'multicheckboxes':
                             row[i] = new Array();
                             dg.columns[i].element.find(':input').each(function() {
                                 if($(this).is(':checked')) row[i].push($(this).val());
                                 $(this).attr('checked', false);
                             });
                             break;
                    }
                }

                dg.root.find('.dynamicGroupTable .formRow').removeClass('formError').find('.errors').remove();
                for (i in row) {
                    if (dg.columns[i].required && !row[i]) {
                        dg.columns[i].element.append('<ul class="errors"><li>Preenchimento obrigatório</li></ul>').parent().addClass('formError');
                        return false;
                    }
                }
                return row;
            },
           setFormValues: function(row) {
                for (var i in dg.columns) {
                    switch (dg.columns[i].type) {
                        case 'text':
                        case 'textarea':
                        case 'radio':
                            dg.columns[i].element.find(':input').val(row[i]);
                            break;
                        case 'select':
                            dg.columns[i].element.find(':input').val((row[i] == null) ? 0 : row[i]);
                            break;
                        case 'checkbox':
                            if (row[i]) dg.columns[i].element.find(':input').attr('checked', 'checked');
                            break;
                        case 'multicheckboxes':
                            for (var j in row[i]) {
                                dg.columns[i].element.find(':input[value=' + row[i][j] + ']').attr('checked', 'checked');
                            }
                            break;
                    }
                }

                dg.root.find('.dynamicGroupTable .formRow').removeClass('formError').find('.errors').remove();
            },
            addRow: function(row) {
                if (row == undefined) row = dg.getFormValues();
                if (row != false) {
                    if (dg.currentRow != null) dg.rows[dg.currentRow] = row;
                    else dg.rows.push(row);
                    dg.rowsToValues();

                    if (dg.currentRow != null)  {
                        dg.root.find('.dynamicGroupTable tbody tr:eq(' + dg.currentRow + ')').replaceWith(dg.createRow(row));
                        dg.currentRow = null;
                    } else dg.root.find('.dynamicGroupTable tbody').append(dg.createRow(row));
                }
            },
            removeRow: function(key) {
                dg.rows.splice(key, 1);
                dg.root.find('.dynamicGroupTable tbody tr:eq(' + key + ')').remove();
                dg.rowsToValues();
            },
            editRow: function(key) {
                dg.root.find('.dynamicGroupTable tbody tr:eq(' + dg.currentRow + ')').removeClass('edit');
                dg.currentRow = key;
                dg.root.find('.dynamicGroupTable tbody tr:eq(' + key + ')').addClass('edit');
                dg.setFormValues(dg.rows[key]);
            },
            createRow: function(row) {
                var tr = $('<tr/>').addClass((dg.rows.length % 2) ? 'even' : 'odd');
                for (var i in row) {
                    if (row[i] == null) row[i] = '';
                    var value = null;
                    switch (dg.columns[i].type) {
                        case 'text':
                        case 'textarea':
                            value = row[i];
                            break;
                        case 'select':
                            value = row[i] ? dg.columns[i].element.find('option[value=' + row[i] + ']').text() : '';
                            break;
                        case 'radio':
                            value = row[i];
                            break;
                        case 'checkbox':
                            value = row[i] ? 'Sim' : 'Não';
                            break;
                        case 'multicheckboxes':
                            value = '<ul>';
                            for (var j in row[i]) {
                                var id = dg.columns[i].element.find(':input[value=' + row[i][j] + ']').attr('id');
                                value += '<li>' + dg.columns[i].element.find('label[for=' + id + ']').text() + '</li>';
                            }
                            value += '</ul>';
                            break;
                    }
                    tr.append('<td>' + value + '</td>');
                }
                var edit = $('<a class="groupEdit"><img title="Editar" alt="Editar" src="/singular/images/icons/16x16/edit.png"/></a>').bind("click", function (e) {
                    dg.editRow($(e.target).closest('tr')[0].sectionRowIndex);
                    return false;
                });
                var remove = $('<a class="groupRemove"><img title="Remover" alt="Remover" src="/singular/images/icons/16x16/remove_circle.png"/></a>').bind("click", function (e) {
                    dg.removeRow($(e.target).closest('tr')[0].sectionRowIndex);
                    return false;
                });
                return tr.append($('<td class="tableActions"></td>').append(edit).append(remove));
            }
        };
        element.dynamicGroup = dg;
        dg.createTable();
    };

    $.fn.dynamicGroup = function(options) {
        return this.each(function() {
            $.addDynamicGroup(this, options);
            var group = $(this);
            var interactElement = group.attr('interactElement');
            if (interactElement) {
                group.find('.groupAdd').click(function() {
                    var result = 0;
                    eval(group.attr('interactRule'));
                    $(interactElement).val(result);
                });
            }
        });
    };
})(jQuery);