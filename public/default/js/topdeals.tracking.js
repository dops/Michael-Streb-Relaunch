
/**
 * TOPDEALS TRACKING BACKEND JAVASCRIPT
 */

window.topdeals = window.topdeals || {};

window.topdeals.tracking = {

	periodFormSelect: function(element) {
		if (element.val() === 'custom') {
			element.blur();
			$('#period-custom').fadeIn('fast', function() {
				$('#period-from').focus();
			});
			$('#period-custom + li').fadeIn('fast');
		} else {
			$('input[type="text"]').val('');
			$('#period-custom , #period-custom + li').fadeOut('fast');
			element.attr('form').submit();
		}
	},

	initDateInputs: function() {
		$.tools.dateinput.localize('de', {
			months:        'Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember',
			shortMonths:   'Jan,Feb,Mär,Apr,Mai,Jun,Jul,Aug,Sep,Okt,Nov,Dez',
			days:          'Sonntag,Montag,Dienstag,Mittwoch,Donnerstag,Freitag,Samstag',
			shortDays:     'So,Mo,Di,Mi,Do,Fr,Sa'
		});
		$('input[type="date"]').dateinput({
			format: 'dd.mm.yyyy',
			lang: 'de',
			selectors: true
		});
		if ($('#period-from').length > 0 && $('#period-to').length > 0) {
			$('#period-from').data('dateinput').change(function() {
				$('#period-to').data('dateinput').setMin(this.getValue(), true);
			});
			$('#period-to').data('dateinput').change(function() {
				$('#period-from').data('dateinput').setMax(this.getValue(), true);
			});
		}
	},

	init: function() {
		// initalize date inputs
		window.topdeals.tracking.initDateInputs();
		// handle period selector
		$('#period').bind('change', function() {
			var $this = $(this);
			topdeals.tracking.periodFormSelect($this);
		});
		$('#group').bind('change', function() {
			var $this = $(this);
			topdeals.tracking.periodFormSelect($this);
		});
		if ($('#period').val() === 'custom') {
			$('#period-custom , #period-custom + li').fadeIn('fast');
		} else {
			$('#period-custom + li').fadeOut('fast');
		}
	}

};

$(function() {
	window.topdeals.tracking.init();
});
