(function($) {
    /*
     * Torna o elemento em uma "área AJAX", ou seja, com conteúdo alterado sem
     * haver recarregamento da página. Ocorre quando:
     * - Hash da URL é alterado, chamando http://host/#hash como http://host/hash.
     *   A resposta deve estar no formato JSON, com a configuração adequada.
     * - Houver clique em uma âncora, <a>, com classe de disparo (por padrão 'ajaxTrigger').
     * - For chamado os métodos auxiliares $.updateAjaxArea ou $.modifyAjaxArea.
     */
    $.addAjaxArea = function(element, options) {
        // Configura varáveis iniciais
        options = $.extend({
            breadcrumb:  '#breadcrumb > p',
            defaultHash: '#dashboard/index',
            title:       '#contentWrapper h2',
            trigger:     'a.ajaxTrigger',
            callback:    null
        }, options);

        var a = {
            update: function(hash) {
                location.hash = a.currentHash = hash;
                if (hash.length == 0) hash = options.defaultHash;
                var url = a.baseUrl + hash.replace(/#/,"");

                // Coloca aviso "carregando" e pega conteúdo da URL via JSON
                //a.loading = $('<div/>').addClass('contentLoading').text('Aguarde');
                a.root.hide().empty().before(a.loading);
                $.getJSON(url, a.modify);
                if (options.callback != null) options.callback(hash);
            },
            modify: function(response) {
                // Atualiza breadcrumb
                a.breadcrumb.empty().append(response.breadcrumb);
                // Atualiza título
                a.title.text(response.title);

                // Configura conteúdo conforme seu tipo
                switch (response.type) {
                    case 'html':
                    case 'data':
                        a.root.html(response.data);
                        break;
                    case 'form':
                        a.root.numeraForm(response.config);
                        break;
                    case 'report':
                        a.root.numeraReport(response.config);
                        break;
                    case 'grid':
                    case 'list':
                        a.root.numeraGrid(response.config);
                        break;
                    case 'error':
                        break;
                }

                // Mostra o conteúdo (necessário se a função for chamada via updateAjaxArea)
                if (a.loading) {
                    a.loading.remove();
                    a.loading = null;
                }
                a.root.show();
            },
            baseUrl: ((location.href.match(/^([^#]+)/)||[])[1]),
            breadcrumb: $(options.breadcrumb),
            defaultHash: options.defaultHash,
            currentHash: location.hash,
            loading: null,
            root: $(element),
            title: $(options.title)
        };
        element.ajaxArea = a;

        // Adiciona diálogo (modal) para erros, avisos, e confirmações
        a.root.addDialog();
        // Verifica continuamente alterações na URL
        setInterval(function() {
            if (location.hash != a.currentHash) a.update(location.hash);
        }, 100);
        // Carrega o conteúdo inicial
        if (a.currentHash != '') $.getJSON(location.href.replace(/#/,""), a.modify);
        else location.hash = a.defaultHash;

        // Ajusta tratamento de erro no carregamento via AJAX
        $.ajaxSetup({error:function(XMLHttpRequest) {
            if (XMLHttpRequest.status == 401) {
                var message = 'O sistema ficou ocioso por um período muito longo. Por favor, realize novamente o processo de login.';
                $.showDialog('alert', message, function() {
                    location.href = a.baseUrl + 'auth/logout';
                });
            }
        }});
        // Coloca efeito em bolha no elemento especificado para verificar clique em âncoras com chamada AJAX
//        $('body').click(function(e) {
//            if ($(e.target).is(options.trigger)) {
//                console.log('trigger');
//                a.update($(e.target).attr('href'));
//                return false;
//            }
//        });
    };

    $.fn.ajaxArea = function(options) {
        return this.each(function() {
            $.addAjaxArea(this, options);
        });
    };


    $.fn.updateAjaxArea = function(hash) {
        return this.each(function() {
            if (this.ajaxArea) this.ajaxArea.update(hash);
        });
    };

    $.fn.modifyAjaxArea = function(response) {
        return this.each(function() {
            if (this.ajaxArea) this.ajaxArea.modify(response);
        });
    };
})(jQuery);