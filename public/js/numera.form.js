(function($) {
    /*
     * Configura conteúdo do tipo formulário
     */
    $.addNumeraForm = function(element, config) {
        var ajaxArea = element.ajaxArea.root;
        if (typeof config == 'object' && config.form) {
            var form = $(config.form).appendTo(ajaxArea);
            formElementsConfigure(form);

            // Torna o formulário Ajax
            form.ajaxForm({
                dataType: 'json',
                beforeSubmit: function(formData, jqForm, options) {                   
                    var el = $('#showCostMessage');
                    if (el.val() == "") {
                        var message = 'Esta programação terá um custo a ser definido '
                                + 'pelo desenvolvedor do software. '
                                + 'Deseja prosseguir assim mesmo?';

                        $.showDialog('confirm', message, function() {
                            $('#showCostMessage').val(1);
                            form.submit();
                        }, 'Prosseguir');

                       return false;
                    } else 
                        $.showDialog('process', 'Processando');                   
                },
                success: function(data) {
                    var message = null;
                    if (data.response == 'formError') {
                        message = (data.message) 
                            ? data.message
                            : 'Problemas no preenchimento do formulário. Verfique.'
                        $.showDialog('alert', message);
                        $('#content').animate({ 
                            scrollTop: 0
                        }, 0);
                        ajaxArea.empty();
                        $.addNumeraForm(element, data.config);
                    } else if (data.response == 'formSuccess') {
                        if (data.message) {
                            message = data.message;
                            $.showDialog('info', message);
                            element.ajaxArea.update('#' + data.redirect);
                        } else {
                            $.closeDialog();
                            $('#content').animate({ 
                                scrollTop: 0
                            }, 0);
                            ajaxArea.empty();
                            $.addNumeraForm(element, data.config);
                        }
                    } else if (data.response == 'formRedirect') {
                        $.closeDialog();
                        element.ajaxArea.update('#' + data.redirect);
                    }
                }
            });
        }

        function formElementsConfigure(content) {
            var formElements = content.find('.formElement');

            $('#printContrato')
                .bind('click', function() {
                    window.open('http://www.lembrefacil.com.br/panel/ManualDestinatario.html','impr');
                    window.close();
                })
                .text('Imprimir Manual do Destinatário');
            // Coloca destaque nos erros
            $('.formElement:has(.errors)').parent().addClass('formError');

            //configura descrição
            formElements.find('.hint').each(function(){
                $(this).wrapInner('<p></p>').append('<div/>');
            });

            // Adiciona destaque e descrição nos itens selecionados
            formElements.find('input, textarea, select').each(function() {
                $(this).focus(function() {
                    $(this).parents('.formRow').addClass('formSelected');
                    $(this).siblings('.hint').fadeIn('fast');
                }).blur(function() {
                    $(this).parents('.formRow').removeClass('formSelected');
                    $(this).siblings('.hint').fadeOut('normal');
                });
            });

            // Configura elemento do tipo date
            formElements.find('.formElementDate').todate().datepicker({
                showOn: 'button',
                buttonImage: '../images/icons/calendar.gif',
                buttonImageOnly: true
            }).addClass('embedCalendar').keypress(dateFilter).blur(function() {
                validateFormElement($(this), dateVerify, 'A data inserida não é valida');
            });

            // Configura elemento do tipo cpf
            formElements.find('.formElementCpf').tocpf().keypress(cpfFilter).blur(function() {
                validateFormElement($(this), cpfVerify, 'O número de CPF inserido não é valido');
            });

            // Configura elemento do tipo cep
            formElements.find('.formElementCep').keypress(cepFilter).blur(function() {
                validateFormElement($(this), cepVerify, 'O CEP inserido não é valido');
            });

            // Configura elemento do tipo cgc
            formElements.find('.formElementCgc').keypress(cgcFilter).blur(function() {
                validateFormElement($(this), cgcVerify, 'O CGC inserido não é valido');
            });

            // Configura elemento do tipo phone
            formElements.find('.formElementPhone').keypress(phoneFilter).blur(function() {
                });

            // Configura elemento do tipo money
            formElements.find('.formElementMoney')
                    .keypress(moneyFilter)
                    .blur(function() {
                    
                    });

            // Configura elemento do tipo return
            formElements.find('.formElementReturn').click(function() {
                history.back();
            });

            // Configura elemento do tipo month
            formElements.find('.formElementMonth').keypress(monthFilter).blur(function() {
                validateFormElement($(this), monthVerify, 'A data inserida não é valida');
            });

            // Configura elemento do tipo email
            formElements.find('.formElementEmail').blur(function() {
                validateFormElement($(this), emailVerify, 'E-mail inserido não é valido');
            });

            // Configura elemento do tipo site
            formElements.find('.formElementSite').blur(function() {
                validateFormElement($(this), siteVerify, 'Site inserido não é valido');
            });

            // Configura elemento do tipo autocomplete
            formElements.find('.formElementAutocomplete').each(function() {
                //var url = $('#' + this.id + '-autocomplete').val();
                var url = $(this)
                        .parents('form').eq(0).attr('autocompleteurl');
                var opt = {serviceUrl:url}

                $(this).autocomplete(opt);
            });


            // Configura elemento do tipo select com estados para autocompletar
            // cidades
            formElements.find('.formElementStateForAutocomplete')
                    .each(function() {
                //var url = $('#' + this.id + '-autocomplete').val();
                $(this).bind('load change', function() {
                    var val = $(this).val();

                    if (val.length == 2) {
                        var url = $(this)
                                .parents('form').eq(0).attr('autocompleteurl');

                        var ctyelm = $.find('.formElementAutocompleteCity');
                        var cval = $(ctyelm).val();
                        ctyelm = $(ctyelm).parents('.formRow').get(0);

                        var wrapper = $(ctyelm).wrap('<span/>').parent();
                        $(wrapper).empty();
                        $(wrapper).data('oldval', cval);
                        $(wrapper).load(url, {uf: val}, function() {
                            //var cval = $(this).data('oldval');
                            //$(this)
                            //    .find('.formElementAutocompleteCity')
                            //    .val(cval);
                        });
                    }
                });
            });

            // Configura elemento do tipo autocomplete de cidades
            formElements.find('.formElementAutocompleteCity').each(function() {
            });

            // Configura elemento do tipo file
            formElements.find('.formElementUpload').each(function() {
                var uploadOutput = $('<div id="uploadOutput"></div>').insertAfter($(this));
                var upload = $(this);
                upload.change(function() {
                    upload.siblings('img').remove();
                    $(this).parents().find('form').ajaxSubmit({
                        url: 'upload/' + $(this).attr('ref'),
                        dataType: 'json',
                        beforeSubmit: function(a,f,o) {
                        // upload.attr('disabled', 'disabled');
                        //upload.siblings('img').remove();
                        },
                        success: function(data) {
                            upload.removeAttr('disabled');
                            if (data.response == 'formSuccess') {
                                var img = new Image();
                                img.src = 'upload/preview/i/'
                                + upload.parents().find('form').find('.formId').val()
                                + data.i;
                                upload.after(img);
                            } else {
                                $.showDialog('alert', data.message);
                                upload.val('');
                            }
                        }
                    });
                });
            });

            // Configura elemento do tipo selector
            formElements.find('.formElementSelector').change(function() {
                var selector = $(this);
                selector.parents('form').ajaxSubmit({
                    data: {
                        selectorRequest: true
                    },
                    dataType: 'json',
                    beforeSubmit: function(a,f,o) {
                        selector.attr('disabled', 'disabled');
                        $('#fieldset-' + selector.get(0).id + 'Selector').remove();
                    },
                    success: function(data) {
                        if (data && data.type == 'form') {
                            selector.removeAttr('disabled');
                            //var selectorContent = $('<div id="' + selector.get(0).id + 'SelectorArea">' + data.config.form + '</div>');
                            var selectorContent = $(data.config.form);
                            selectorContent.insertAfter(selector.parents('.formRow'));
                            formElementsConfigure(selectorContent);
                        }
                    }
                });
            });

            // Configura elemento do tipo autoGrow
            formElements.find('.formElementAutogrow').change(function() {
                var length = $(this).find('option[value!=0]').length;
                if ($(this).val() == length) {
                    var value = parseInt($(this).find('option').length, 10);
                    $(this).append('<option label="' + value + '" value="' +  value + '">' +  value + '</option>');
                }
            });
            
            // Configura elemento do tipo button
            formElements.find('.formElementButton').click(function() {
                var button = $(this);
                var buttonForm = button.parents('form');
                buttonForm.ajaxSubmit({
                    dataType: 'json',
                    beforeSubmit: function(a,f,o) {
                        button.attr('disabled', 'disabled');
                    },
                    success: function(data) {
                        if (data && data.type == 'form') {
                            button.removeAttr('disabled');
                            buttonForm.remove();
                            addForm(data.config.form);
                        }
                    }
                });
            });

            // Configura elemento do tipo chainedSelect
            formElements.find('.formElementChainedSelect').each(function() {
                var chainedSelect = $(this);
                if (chainedSelect.val() == 0) {
                    chainedSelect.attr('disabled', 'disabled');
                }
                $('#' + chainedSelect.attr('ref')).change(function() {
                    if (chainedSelect.attr('src') == undefined) {
                        chainedSelect.removeAttr('disabled');
                    } else {
                        chainedSelect.attr('disabled', 'disabled').empty();
                        if ($(this).val() != 0) {
                            chainedSelect.append('<option value="0">Aguarde</option>');
                            $.getJSON(chainedSelect.attr('src') + '?q=' + $(this).val(), function(data) {
                                chainedSelect.empty().append('<option value=0>Selecione</option>');
                                for (var i in data) {
                                    chainedSelect.append('<option value="' + i + '">' + data[i] + '</option>');
                                }
                                chainedSelect.removeAttr('disabled');
                            });
                        }
                    }
                });
            });

            // Configura elemento do tipo interact
            formElements.find('.formElementInteract').bind(($.browser.msie ? 'click' : 'change'), function () {
                if (this.type == 'radio') {
                    $('.interactElement-' + this.name).hide();
                    $('#interact-' + this.id).show();
                } else {
                    $('.interactElement-' + this.id).hide();
                    $('#' + this.id + '-' + $(this).val()).show();
                }
            });

            // Configura elemento do tipo multi values
            formElements.find('.formElementMultiValues').each(function() {
                var id = this.id;
                var count = 0;
                var firstValue = $(this);
                firstValue.removeAttr('id').removeAttr('name');
                firstValue.wrap('<div id="' + id + '" class="multiValues"></div>');
                $('<button>+</button>').insertAfter(this).click(function() {
                    count++;
                    var newElement = firstValue.clone(true).val('');
                    $('#' + id).append(newElement);
                    $('<button>-</button>').insertAfter(newElement).click(function() {
                        count--;
                        newElement.remove();
                        $(this).remove();
                        return false;
                    });
                    return false;
                });
            });

            // Configura elemento do tipo time
            formElements.find('.formElementTime').keypress(timeFilter).blur(function() {
                validateFormElement($(this), timeVerify, 'A hora inserida não é valida');
            });

            // Configura elemento do tipo cellPhone
            formElements.find('.formElementCellPhone').keypress(CellPhoneFilter).blur(function() {
                validateFormElement($(this), cellPhoneVerify, 'O celular inserido não é válido.');
            });

            formElements.find('.formElementCellPhone').blur(function() {
                var cellno = $("#cell_phone").val();
                $.post("accounts/autocomplete", {
                    cell_phone: $("#cell_phone").val()
                },    function(data) {
                    $("#name").val(data.name);
                    document.getElementById('in_send_option-'+data.in_send_option).checked = true;
                }, "json");
            });

            // Configura elemento do tipo multiTime
            formElements.find('.formElementMultiTime').after(
                '<div id="new_time">'
                ).keypress(multiTimeFilter).blur(function() {
                validateFormElement($(this), multiTimeVerify, 'O horário inserido não é valido');
            });

            // Configura elemento do tipo integer
            formElements.find('.formElementInteger').keypress(intFilter).blur(function() {
                validateFormElement($(this), intVerify, 'O valor inserido não é inteiro');
            });

            formElements.find('.formElementRichText').each(function() {
                var editor = $(this).rte({
                    css: ['../css/rte.css'],
                    controls_rte: rte_toolbar,
                    controls_html: html_toolbar
                });
            /*                if (editor[0].get_content() == '') {
                    editor[0].editor_cmd('formatblock', '<p>');
                }*/
            });

            formElements.find('.formElementTextareaLimited').maxlength({
                maxCharacters: 150,
                statusText: 'caracteres restantes'
            });

            // Configura elementos do tipo DynamicGroup
            content.find('.dynamicGroup').dynamicGroup();
            content.find('.programmingGroup').each(function() {
                $(this).programmingGroup();
            });
            content.find('.programmingLab').each(function() {
                $(this).programmingLab();
            });
            content.find('.remedyBoxes').each(function() {
                $(this).remedyBoxes();
            });
            
            // Configura abas
            //            content.multiTab({tabItem:'.formMulti', label:'legend', onchange: function(tab) {
            //                if (!tab.prev().length) { content.find('#previous').attr('disabled', 'disabled'); }
            //                else { content.find('#previous').removeAttr('disabled', 'disabled'); }
            //                if (!tab.next().length) { content.find('#next').attr('disabled', 'disabled'); }
            //                else { content.find('#next').removeAttr('disabled', 'disabled'); }
            //            }});
            //            if (content.find('.tabList').length) {
            //                if (content.find('.formError').length) setTabError(formElements.eq(0));
            //
            //                var previous = $('<button id="previous" type="button" disabled="disabled">&lt; Anterior</button>');
            //                var next = $('<button id="next" type="button" class="marginRight">Próximo &gt;</button>');
            //                content.find('#confirm').before(previous,next);
            //                var tabs = content.find('.tabList li');
            //
            //                next.click(function() {
            //                    var nextTab = tabs.filter('.selected').next().click();
            //                    $('#content').animate({ scrollTop: 0 }, 0);
            //                    previous.removeAttr('disabled');
            //                    if (!nextTab.next().length) {
            //                        next.attr('disabled', 'disabled');
            //                    }
            //                });
            //                previous.click(function() {
            //                    var previousTab = tabs.filter('.selected').prev().click();
            //                    $('#content').animate({ scrollTop: 0 }, 0);
            //                    next.removeAttr('disabled');
            //                    if (!previousTab.prev().length) {
            //                        previous.attr('disabled', 'disabled');
            //                    }
            //                });
            //            }

            // Configura sequência (multipassos)
            content.find('.formSequence').each(function() {
                var step = content.find('#step');
                var currentStep = step.val();
                var totalSteps = step.attr('steps');
                var confirm = content.find('#confirm');
                var previous = content.find('#previous');
                if (currentStep == 1) previous.attr('disabled', 'disabled');
                var next = content.find('#next').addClass('marginRight');
                if (currentStep == totalSteps) next.attr('disabled', 'disabled');
                confirm.before(previous,next);
                if (currentStep != totalSteps) confirm.attr('disabled', 'disabled');
            });
        };
    }

    /*
     * Filtros
     */
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

    function cpfFilter(e) {
        var currentCpf = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if ((currentCpf.length == 3 || currentCpf.length == 7) && e.which!=8 && e.which!=0) {
            $(this).val(currentCpf + '.');
        }
        if (currentCpf.length == 11 && e.which!=8 && e.which!=0) {
            $(this).val(currentCpf + '-');
        }
        if (currentCpf.length > 13 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function cgcFilter(e) {
        var currentCgc = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if ((currentCgc.length == 2 || currentCgc.length == 6) && e.which!=8 && e.which!=0) {
            $(this).val(currentCgc + '.');
        }
        if (currentCgc.length == 10 && e.which!=8 && e.which!=0) {
            $(this).val(currentCgc + '/');
        }
        if (currentCgc.length == 15 && e.which!=8 && e.which!=0) {
            $(this).val(currentCgc + '-');
        }
        if (currentCgc.length > 17 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function cepFilter(e) {
        var currentCep = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if (currentCep.length == 5 && e.which!=8 && e.which!=0) {
            $(this).val(currentCep + '-');
        }
        if (currentCep.length > 8 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function moneyFilter(e) {
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
        if (currentMoney.length > 8 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function monthFilter(e) {
        var currentMonth = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if (currentMonth.length == 2 && e.which!=8 && e.which!=0) {
            $(this).val(currentMonth + '/');
        }
        if (currentMonth.length > 6 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function phoneFilter(e) {
        var currentPhone = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if (currentPhone.length == 0 && e.which!=8 && e.which!=0) {
            $(this).val(currentPhone + '(');
        }
        if (currentPhone.length == 3 && e.which!=8 && e.which!=0) {
            $(this).val(currentPhone + ')');
        }

        if (currentPhone.length > 13 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function CellPhoneFilter(e) {
        var currentCell = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0  && (e.which<48 || e.which>57)) ? false : true ;
        if ((currentCell.length == 0) && e.which!=8 && e.which!=0) {
            $(this).val(currentCell + '(');
        }
        if ((currentCell.length == 3) && e.which!=8 && e.which!=0) {
            $(this).val(currentCell + ')');
        }
        //        if ((currentCell.length == 8)) {
        //            $(this).val(currentCell + '-');
        //        }
        if (currentCell.length > 12 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function multiTimeFilter(e) {
        var currentTime = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if ((currentTime.length == 2) && e.which!=8 && e.which!=0) {
            $(this).val(currentTime + ':');
        }
        if (currentTime.length > 4 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function timeFilter(e) {
        var currentTime = $(this).val();
        var newDigit = (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
        if ((currentTime.length == 2) && e.which!=8 && e.which!=0) {
            $(this).val(currentTime + ':');
        }
        if (currentTime.length > 4 && e.which!=8 && e.which!=0) {
            newDigit = false;
        }
        return newDigit;
    }

    function intFilter(e) {
        return (e.which!=8 && e.which!=0 && (e.which<48 || e.which>57)) ? false : true ;
    }


    function validateFormElement(element, callback, message) {
        var value = element.val();
        if (value != '') {
            var container = element.parent();
            if (callback(value)) {
                if (container.hasClass('formError')) {
                    container.find('ul').remove();
                    container.removeClass('formError').prev().removeClass('formError');
                    setTabError(container);
                }
            } else {
                if (!container.hasClass('formError')) {
                    container.append('<ul class="errors"><li>' + message + '</li></ul>');
                    container.addClass('formError').prev().addClass('formError');
                    setTabError(container);
                }
            }
        }
    }

    function setTabError(element) {
        if (element.parents('.formMulti').length) {
            var form = element.parents('form');
            var tabs = form.find('.tabList a');
            form.find('.formMulti').each(function(i) {
                if ($(this).find('.formError').length) {
                    tabs.eq(i).css('color', '#ff0000');
                } else {
                    tabs.eq(i).css('color', '#000');
                }
            });
        }

    }

    function processForm(data) {
        if (data.response == 'formError') {
            configureContent(data);
        }
    }

    $.fn.numeraForm = function(options) {
        return this.each(function() {
            $.addNumeraForm(this, options);
        });
    }

    $.fn.todate = function() {
        return this.each(function() {
            var timestamp = $(this).val();
            if (timestamp != 0) {
                var date = new Date(timestamp * 1000);
                var day = String(date.getDate());
                if (day.length < 2) {
                    day = 0 + day;
                }
                var month = String(date.getMonth() + 1);
                if (month.length < 2) {
                    month = 0 + month;
                }
                var dateString = day + '/' + month + '/' + String(date.getFullYear());
                $(this).val(dateString);
            }
        });
    }

    $.fn.tocpf = function() {
        return this.each(function() {
            var cpf = $(this).val();
            if (cpf != 0) {
        //    $(this).val();
        }
        });
    }

    
})(jQuery);

function celllist() {

    var width = 462;
    var height = 450;

    var left = 99;
    var top = 99;
    
    var url = document.location.toString();
    url = url.substr(0, url.indexOf('#'));
    url += 'cellphonelist';

    window.open(url,
        'janela',
        'width='+width
        +',height='+height
        +',top='+top
        +',left='+left
        +',scrollbars=yes,status=no,toolbar=no,location=no,'
        +'directories=no,menubar=no,resizable=no,fullscreen=no'
    );
}