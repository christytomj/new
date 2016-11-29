/*
 * Lightweight RTE - jQuery Plugin, v1.2
 * Basic Toolbars
 * Copyright (c) 2009 Andrey Gayvoronsky - http://www.gayvoronsky.com
 */
var rte_tag		= '-rte-tmp-tag-';

var	rte_toolbar = {
    block           : {
        command: 'formatblock',
        select: '\
        <select>\
            <option value="">- Estilo -</option>\
            <option value="<p>">Parágrafo comum</option>\
            <option value="<h3>">Título principal</option>\
            <option value="<h4>">Título secundário</options>\
        </select>\
            ',
        tag_cmp: rteBlockCompare,
        tags: ['p', 'h3', 'h4']
    },
    
    color           : {
        command: 'forecolor',
        select: '\
            <select>\
        <option value="">- Cor -</option>\
<option	value="#FFFFFF"	style=”background-color: 	#FFFFFF	"	></option>\
<option	value="#CCCCCC"	style="background-color: 	#CCCCCC	"	>	</option>\
<option	value="#C0C0C0"	style="background-color: 	#C0C0C0	"	>	</option>\
<option	value="#999999"	style="background-color: 	#999999	"	>	</option>\
<option	value="#666666"	style="background-color: 	#666666	"	>	</option>\
<option	value="#333333"	style="background-color: 	#333333	"	>	</option>\
<option	value="#000000"	style="background-color: 	#000000	"	>	</option>\
<option	value="#FFCCCC"	style="background-color: 	#FFCCCC	"	>	</option>\
<option	value="#FF6666"	style="background-color: 	#FF6666	"	>	</option>\
<option	value="#FF0000"	style="background-color: 	#FF0000	"	>	</option>\
<option	value="#CC0000"	style="background-color: 	#CC0000	"	>	</option>\
<option	value="#990000"	style="background-color: 	#990000	"	>	</option>\
<option	value="#660000"	style="background-color: 	#660000	"	>	</option>\
<option	value="#330000"	style="background-color: 	#330000	"	>	</option>\
<option	value="#FFCC99"	style="background-color: 	#FFCC99	"	>	</option>\
<option	value="#FF9966"	style="background-color: 	#FF9966	"	>	</option>\
<option	value="#FF9900"	style="background-color: 	#FF9900	"	>	</option>\
<option	value="#FF6600"	style="background-color: 	#FF6600	"	>	</option>\
<option	value="#CC6600"	style="background-color: 	#CC6600	"	>	</option>\
<option	value="#993300"	style="background-color: 	#993300	"	>	</option>\
<option	value="#663300"	style="background-color: 	#663300	"	>	</option>\
<option	value="#FFFF99"	style="background-color: 	#FFFF99	"	>	</option>\
<option	value="#FFFF66"	style="background-color: 	#FFFF66	"	>	</option>\
<option	value="#FFCC66"	style="background-color: 	#FFCC66	"	>	</option>\
<option	value="#FFCC33"	style="background-color: 	#FFCC33	"	>	</option>\
<option	value="#CC9933"	style="background-color: 	#CC9933	"	>	</option>\
<option	value="#996633"	style="background-color: 	#996633	"	>	</option>\
<option	value="#663333"	style="background-color: 	#663333	"	>	</option>\
<option	value="#FFFFCC"	style="background-color: 	#FFFFCC	"	>	</option>\
<option	value="#FFFF33"	style="background-color: 	#FFFF33	"	>	</option>\
<option	value="#FFFF00"	style="background-color: 	#FFFF00	"	>	</option>\
<option	value="#FFCC00"	style="background-color: 	#FFCC00	"	>	</option>\
<option	value="#999900"	style="background-color: 	#999900	"	>	</option>\
<option	value="#666600"	style="background-color: 	#666600	"	>	</option>\
<option	value="#333300"	style="background-color: 	#333300	"	>	</option>\
<option	value="#99FF99"	style="background-color: 	#99FF99	"	>	</option>\
<option	value="#66FF99"	style="background-color: 	#66FF99	"	>	</option>\
<option	value="#33FF33"	style="background-color: 	#33FF33	"	>	</option>\
<option	value="#33CC00"	style="background-color: 	#33CC00	"	>	</option>\
<option	value="#009900"	style="background-color: 	#009900	"	>	</option>\
<option	value="#006600"	style="background-color: 	#006600	"	>	</option>\
<option	value="#003300"	style="background-color: 	#003300	"	>	</option>\
<option	value="#99FFFF"	style="background-color: 	#99FFFF	"	>	</option>\
<option	value="#33FFFF"	style="background-color: 	#33FFFF	"	>	</option>\
<option	value="#66CCCC"	style="background-color: 	#66CCCC	"	>	</option>\
<option	value="#00CCCC"	style="background-color: 	#00CCCC	"	>	</option>\
<option	value="#339999"	style="background-color: 	#339999	"	>	</option>\
<option	value="#336666"	style="background-color: 	#336666	"	>	</option>\
<option	value="#003333"	style="background-color: 	#003333	"	>	</option>\
<option	value="#CCFFFF"	style="background-color: 	#CCFFFF	"	>	</option>\
<option	value="#66FFFF"	style="background-color: 	#66FFFF	"	>	</option>\
<option	value="#33CCFF"	style="background-color: 	#33CCFF	"	>	</option>\
<option	value="#3366FF"	style="background-color: 	#3366FF	"	>	</option>\
<option	value="#3333FF"	style="background-color: 	#3333FF	"	>	</option>\
<option	value="#000099"	style="background-color: 	#000099	"	>	</option>\
<option	value="#000066"	style="background-color: 	#000066	"	>	</option>\
<option	value="#CCCCFF"	style="background-color: 	#CCCCFF	"	>	</option>\
<option	value="#9999FF"	style="background-color: 	#9999FF	"	>	</option>\
<option	value="#6666CC"	style="background-color: 	#6666CC	"	>	</option>\
<option	value="#6633FF"	style="background-color: 	#6633FF	"	>	</option>\
<option	value="#6600CC"	style="background-color: 	#6600CC	"	>	</option>\
<option	value="#333399"	style="background-color: 	#333399	"	>	</option>\
<option	value="#330099"	style="background-color: 	#330099	"	>	</option>\
<option	value="#FFCCFF"	style="background-color: 	#FFCCFF	"	>	</option>\
<option	value="#FF99FF"	style="background-color: 	#FF99FF	"	>	</option>\
<option	value="#CC66CC"	style="background-color: 	#CC66CC	"	>	</option>\
<option	value="#CC33CC"	style="background-color: 	#CC33CC	"	>	</option>\
<option	value="#993399"	style="background-color: 	#993399	"	>	</option>\
<option	value="#663366"	style="background-color: 	#663366	"	>	</option>\
<option	value="#330033"	style="background-color: 	#330033	"	>	</option>\            \n\
</select>\
        '
    },
    fontsize           : {
        command: 'fontsize',
        select: '\
        <select>\
            <option value="">- Tamanho -</option>\
            <option value="1">8pt</option>\
            <option value="2">10pt</option>\
            <option value="3">12pt</option>\
            <option value="4">14pt</options>\
            <option value="5">16pt</option>\
            <option value="6">18pt</option>\
            <option value="7">20pt</option>\
        </select>\
            '
    },
    bold            : {
        command: 'bold',
        tags: ['b', 'strong']
    },
    italic          : {
        command: 'italic',
        tags:['i', 'em']
    },
    orderedList		: {
        command: 'insertorderedlist',
        tags: ['ol']
    },
    unorderedList	: {
        command: 'insertunorderedlist',
        tags: ['ul']
    },
    link			: {
        exec: rteAddLink,
        tags: ['a']
    },
    unlink			: {
        command: 'unlink'
    },
    removeFormat	: {
        exec: rteUnformat
    }
    


};

var html_toolbar = {};

/*** tag compare callbacks ***/
function rteBlockCompare(node, tag) {
    tag = tag.replace(/<([^>]*)>/, '$1');
    return (tag.toLowerCase() == node.nodeName.toLowerCase());
}

function rteUnformat() {
    this.editor_cmd('removeFormat');
    this.editor_cmd('unlink');
}

function rteClear() {
    if(confirm('Clear Document?'))
        this.set_content('');
}

function rteAddLink() {
    var self = this;
    if (self.get_selected_text().length <= 0) {
        $.showDialog('alert', 'Nenhum texto selecionado para se tornar link!');
        return false;
    }

    var content = $('<h3>Adicionar link</h3>' +
        '<p><label for="rteImportUrl">Endereço:</label><span><input type="text" id="rteImportUrl"/><input type="button" id="rteImportShow" value="Ver" /></span></p>');

    $.showDialog('info', '');
    var dialog = $('#dialog');
    dialog.find('.jqmContent').removeClass('infoType').empty().addClass('formType').append(content);
    dialog.find('.jqmCommands').empty().html('<a class="jqmConfirm">OK</a> <a class="jqmCancel">Cancelar</a>');

    $('#rteImportShow').click(function() {
        var url = $('#rteImportUrl').val();
        if (url.length > 0) {
            window.open(url);
        }
    });

    dialog.jqmAddClose('.jqmCancel').find('.jqmConfirm').click(function() {
        dialog.jqmHide();
        self.editor_cmd('unlink');

        var href = $('#rteImportUrl').val();
        if (href.search('javascript://') != 0) {
            self.editor_cmd('createLink', href);
        }
        return false;
    });

}
