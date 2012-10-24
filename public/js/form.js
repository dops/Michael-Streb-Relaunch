$(document).ready(function() {
    // Create datepicker fields
    if ($('.fieldDatepicker input').length > 0) {
        $('.fieldDatepicker input').datepicker({
            dateFormat: "dd.mm.yy",
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            dayNamesShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
            changeYear: true,
            changeMonth: true,
            showAnim: 'slideDown',
            showButtonPanel: true,
            showOn: 'focus'
        });
    }
});