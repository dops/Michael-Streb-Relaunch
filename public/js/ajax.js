/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function hideDialog() {
    $('#ajaxDialogWrapper #ajaxDialogWindow').animate(
        {left: ($(document).width() + 1000) + 'px'},
        {
            duration: 500,
            easing: 'easeInBack',
            complete: function() {
                $('#ajaxDialogWrapper').animate(
                    { opacity: 0 },
                    {
                        duration: 500,
                        queue: true,
                        complete: function() {
                            $('#ajaxDialogWrapper').detach();
                        }
                    }
                );

            }
        });
};

function showDialog() {
    $('#ajaxDialogWrapper').animate(
        { opacity: 1 },
        {
            duration: 500,
            complete: function() {
                var padding = $('#ajaxDialogWindow').css('padding').replace(/px/, '');
                var posLeft = ($(document).width() / 2) - (($('#ajaxDialogWindow').width() + (padding * 2)) / 2);

                $('#ajaxDialogWrapper #ajaxDialogWindow').animate({left: posLeft + 'px'}, { duration: 500, easing: 'easeOutBack' });
            }
        }
    );
}

$(document).ready(function() {
    $('.ajaxDialog').click(function() {
        var requestUri  = $(this).attr('href');
        var title       = $(this).attr('title');

        var dialogFrame = '<div id="ajaxDialogWrapper"><div id="ajaxDialogWindow"><span onclick="hideDialog()" class="close">close</span><h3></h3><div class="content">#content#</div></div></div>';

        $.ajax({
            type: 'POST',
            url: requestUri,
            data: 'ajaxRequest=1',
            success: function(data) {
                $('body').append(dialogFrame);
                $('#ajaxDialogWrapper h3').html(title);
                $('#ajaxDialogWrapper .content').html(data);

                showDialog();
            },
            error: function(data) {
                $('body').append(dialogFrame);
                $('#ajaxDialogWrapper h3').text(title);
                $('#ajaxDialogWrapper .content').html('Es ist ein Fehler aufgetreten. Bitte wenden Sie sich an Ihren Administrator.');

                showDialog();
            }
        });

        return false;
    });
});