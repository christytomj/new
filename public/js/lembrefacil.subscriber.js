$(document).ready(function() {
    $('#content').ajaxArea({
        defaultHash: '#accounts',
        callback: updateSectionTab
    });

    $('body').click(function(e) {
        var link = $(e.target).parent();
        if (link.hasClass('infoLink')) {
            $.get(link.attr('href'), null, function() {
                var message = 'SMS com o link do aplicativo enviado para o usuário.'
                $.showDialog('info', message);
            });
            return false;            
        }
    });
    
});


function updateSectionTab(hash) {
    var p = hash.indexOf('/');
    if (p != '-1') hash = hash.substring(0, p);
    $('#sidebar li').removeClass('selected');
    $('#sidebar li').addClass('unselected');
    $(hash).removeClass('unselected')
    $(hash).addClass('selected');
}