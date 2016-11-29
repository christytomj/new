(function($) {
    $.addNumeraReport = function(element, config) {
        var ajaxArea = element.ajaxArea.root;
        
        var report = $('<div/>').addClass('report').appendTo(ajaxArea);
        var toolbar = $('<ul/>').addClass('toolbar').insertBefore(report);
        $('<li/>').append($('<a/>').addClass('print').text('Imprimir')
        .click(function(){
            var p = location.hash.split('/');
            window.location = location.href.replace(p[1], p[1] + 'pdf').replace(/#/g, '');
        }))
        .appendTo(toolbar);

        if (config.options) {
            if (config.options.edit) {
                $('<li/>').append($('<a/>').addClass('edit').text('Editar')
                        .attr('href', '#' + config.options.edit))
                        .appendTo(toolbar);
            }
            if (config.options.exportAnac) {
                var exportHref = location.protocol + '//' + location.host + location.pathname;
                $('<li/>').append($('<a/>').addClass('export').text('Gerar ficha ANAC')
                        .attr('href', exportHref + config.options.exportAnac))
                        .appendTo(toolbar);
            }
        }
		//report.append('<p>botao</p>');
        if (config.data) {
            reportData(report, config.data);
        }
        if (config.group) {
            reportGroup(report, config.group);
        }
        if (config.segment) {
            reportSegment(report, config.segment);
        }

        function reportData(report, data) {
            for (var i in data) {
                switch (data[i].type) {
                    case 'image':
                        var image = $('<img/>').attr('src', data[i].source).addClass('photo');
                        report.append(image);
                        break;
                    case 'message':
                        var message = (data[i].message);
                        report.append(message);
                        break;
                    case 'list':
                        var list = $('<dl/>').appendTo(report);
                        for (var j in data[i].items) {
                            list.append('<dt>' + data[i].items[j].label
                                        + ':</dt><dd>' + data[i].items[j].value + '</dd>');
                        }
                        break;
                    case 'table':
                        var table = $('<table/>').appendTo(report).append('<thead><tr></tr></thead>').append('<tbody/>');
                        for (var j in data[i].header) {
                            table.find('thead tr').append('<th>' + data[i].header[j] + '</th>');
                        }
                        for (var j in data[i].rows) {
                            var row = $('<tr/>').appendTo(table.find('tbody'));
                            if (j%2 == 0) {
                                row.addClass('even');
                            }
                            for (var k in data[i].rows[j]) {
                                cell = data[i].rows[j][k] ?  data[i].rows[j][k] : '';
                                row.append('<td>' + cell + '</td>');
                            }
                        }
                        break;
                }
            }
        };

        function reportSelector(selector) {

        };

        function reportGroup(report, group) {
            for (var i in group) {
                report.append('<h4>' + group[i].label + '</h4>');
                reportData(report, group[i].data);
            }
        };

        function reportSegment(report, segment) {
            var segmentsWrapper = $('<div/>').addClass('segment').appendTo(report);
            for (var i in segment) {
                var currentSegment = $('<div/>').addClass('divMulti').appendTo(segmentsWrapper);
                currentSegment.append('<h3 class="label">' + segment[i].label + '</h3>');
                if (segment[i].data) {
                    reportData(currentSegment, segment[i].data);
                }
                if (segment[i].group) {
                    reportGroup(currentSegment, segment[i].group);
                }
            }

            // Configura abas
            segmentsWrapper.multiTab({tabItem:'.divMulti', label:'h3.label'});
        };

    }

    $.fn.numeraReport = function(options) {
        return this.each(function() {
            $.addNumeraReport(this, options);
        });
    }
})(jQuery);