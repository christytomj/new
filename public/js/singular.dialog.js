(function($) {
    var commands = {
        alert: '<a class="jqmClose">Fechar</a>',
        confirm: '<a class="jqmConfirm">OK</a> <a class="jqmCancel">Cancelar</a>',
        info: '<a class="jqmClose">Fechar</a>',
        process: null
    };
    
    function closeDialog(dialog) {
        dialog.find('.jqmContent').removeClass(
            'alertType confirmType infoType processType'
        ).empty().next().empty();
    }
    
    $.fn.addDialog = function() {
        return this.each(function() {
            $('<div/>').attr('id', 'dialog').addClass('jqmWindow').insertAfter(this).jqm({
                modal: true,
                toTop: true,
                onShow: function(h) {h.w.fadeIn('normal')},
                onHide: function(h) {h.w.fadeOut('fast', function() {
                    if (h.o) {h.o.remove();}
                    closeDialog(h.w);
                })}
            }).append($('<div/>').addClass('jqmContent')).append($('<div/>').addClass('jqmCommands'));
        });
    }
    
    $.closeDialog = function() {
        $('#dialog').jqmHide();
    }
    
    $.showDialog = function(type, message, callback, confirm, cancel) {
        if (message && (typeof message == 'object')) {
            m = '<ul>';
            for (var i in message) {
                m += '<li>' + message[i] + '</li>';
            }
            m += '</ul>';
            message = m;
        }
        
        var dialog = $('#dialog');
        closeDialog(dialog);
        dialog.find('.jqmContent').addClass(type + 'Type').html(message);

        if (commands[type]) {
            dialog.find('.jqmCommands').html(commands[type]);
            if (type == 'confirm') {
                dialog.jqmAddClose('.jqmCancel').find('.jqmConfirm').click(function() {
                    callback();
                    closeDialog(dialog);
                });
                if (confirm) {
                    dialog.find('.jqmConfirm').text(confirm);
                }
                if (cancel) {
                    dialog.find('.jqmCancel').text(cancel);
                }
            } else if (type == 'alert' && callback) {
                dialog.find('.jqmClose').click(function() {
                    callback();
                    dialog.jqmHide();
                });
            } else {
                dialog.jqmAddClose('.jqmClose');
            }

        }
        dialog.jqmShow();
    }

    $.showFormDialog = function(message, callback) {
        var dialog = $('#dialog');
        closeDialog(dialog);

        message = $(message);
        message.find('.formElementDate').datepicker({
            showOn: 'button',
            buttonImage: '../images/icons/calendar.gif',
            buttonImageOnly: true
        }).addClass('embedCalendar').keypress(dateFilter);
//        message.find('.formElementCurrency').keypress(moneyFilter);
        /*
        message.find('.formElementCurrency')
            .bind('blur', function(ev) {
                var re = /^\d+,\d{2}$/g;
                var e = $(this);
                var val = e.val();
                var retest = re.test(val);
                if (val.length>0 && !retest) {
                    alert(
                        'Valor numérico inválido'
                        //+ ': > == '+(val.length>0)
                        //+ ': re == '+(re.test(val))
                        + ': "'+val+'".'
                        //+ ': id == '+e.attr('id')
                    );
                    e.focus();
                }
        });
        */
        dialog.find('.jqmContent').addClass('confirmType').append(message);

        dialog
            .find('.jqmCommands')
            .html('<a class="jqmConfirm">OK</a> <a class="jqmCancel">Cancelar</a>');
        dialog
            .jqmAddClose('.jqmCancel')
            .find('.jqmConfirm')
            .click(function() {
                if (callback() !== false) {
                    dialog.jqmHide();
                }
            });
        dialog.jqmShow();
    }

    $.dialogContent = function(html) {
        $('#dialog').find('.jqmContent').html();
    }

    function dateFilter(e) {
        var currentDate = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if ((currentDate.length == 2 || currentDate.length == 5) && e.which!=8 && e.which!=0) {
            $(this).val(currentDate + '/');
        }
        if (currentDate.length > 9 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function moneyFilter(e) {
        var currentMoney = $(this).val();
        alert(currentMoney.length);
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if (currentMoney.length == 1 && e.which!=8 && e.which!=0) {
            $(this).val(currentMoney + ',');
        }
//        if (currentMoney.length > 1 && e.which!=8 && e.which!=0) {
//            currentMoney = currentMoney.replace(",","");
//            $(this).val(currentMoney);
//        }
        if ((currentMoney.length) >= 2 && e.which!=8 && e.which!=0) {
            first  = currentMoney.substr(0, currentMoney.length - 1);
            second = currentMoney.substr(currentMoney.length - 1);
            currentMoney = first + "," + second;
            $(this).val(currentMoney);
        }
        if (currentMoney.length > 8 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function currency(e) {
        var currentMoney = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if (currentMoney.length == 1 && e.which!=8 && e.which!=0) {
            $(this).val(currentMoney + ',');
        }
        if (currentMoney.length > 1 && e.which!=8 && e.which!=0) {
            currentMoney = currentMoney.replace(",","");
            $(this).val(currentMoney);
        }
        if ((currentMoney.length - 2) >= 0 && e.which!=8 && e.which!=0) {
            first  = currentMoney.substr(0, currentMoney.length - 1);
            second = currentMoney.substr(currentMoney.length - 1);
            currentMoney = first + "," + second;
            $(this).val(currentMoney);
        }
        return newDigit;
    }

})(jQuery);         
