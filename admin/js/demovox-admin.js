var fontSize, textColor = [0, 0, 0], fontFamily = 'Helvetica';
(function ($) {
	'use strict';

	var $input;
	$(function () {
		var demovoxMediaUploader;
		$('.uploadButton').click(function (e) {
			e.preventDefault();
			$input = $('#' + $(this).data('inputId'));
			// If the uploader object has already been created, reopen the dialog.
			if (demovoxMediaUploader) {
				demovoxMediaUploader.open();
				return;
			}
			// Extend the wp.media object.
			demovoxMediaUploader = wp.media.frames.file_frame = wp.media({
				// Set the values through wp_localize_script so that they can be localised/translated.
				title: demovoxAdmin.uploader.title,
				button: {
					text: demovoxAdmin.uploader.text
				}, multiple: false
			});
			// When a file is selected, grab the URL and set it as the fields value.
			demovoxMediaUploader.on('select', function () {
				var attachment = demovoxMediaUploader.state().get('selection').first().toJSON();
				$input.val(attachment.url);
			});
			// Open the uploader dialog.
			demovoxMediaUploader.open();
		});

		fontSize = parseInt($('#demovox_fontsize').val());
		$('.showPdf').click(function () {
			var $container = $(this).closest('div'),
				lang = $(this).data('lang'),
				qrMode = $('#demovox_field_qr_mode').val(),
				pdfUrl = $('#demovox_signature_sheet_' + lang).val(),
				fields = [
					createField('BE', 'canton', lang),
					createField('Bern', 'commune', lang),
					createField('3001', 'zip', lang),
					createField('21', 'birthdate_day', lang),
					createField('10', 'birthdate_month', lang),
					createField('88', 'birthdate_year', lang),
					createField('Theaterplatz 4', 'street', lang),
				],
				qrData = qrMode === 'disabled'
					? null
					: {
						"text": "JNXWE",
						"x": getField('qr_img_' + lang + '_x'),
						"y": getField('qr_img_' + lang + '_y'),
						"rotate": getField('qr_img_' + lang + '_rot'),
						"size": getField('qr_img_size_' + lang),
						"textX": getField('qr_text_' + lang + '_x'),
						"textY": getField('qr_text_' + lang + '_y'),
						"textRotate": getField('qr_text_' + lang + '_rot'),
						"textSize": fontSize,
						"textFont": fontFamily,
						"textColor": textColor
					};
			createPdf($container, 'preview', pdfUrl, fields, qrData);
		});
		$('.ajaxButton').click(function () {
			var cont = $(this).data('container'),
				ajaxUrl = $(this).data('ajax-url'),
				confirmTxt = $(this).data('confirm'),
				$ajaxContainer = $(this).parent().find(cont ? cont : '.ajaxContainer');
			if (typeof confirmTxt !== 'undefined' && !confirm(confirmTxt)) {
				return;
			}
			$ajaxContainer.html('Loading...');
			$.get(ajaxUrl)
				.done(function (data) {
					$ajaxContainer.html(data);
				})
				.fail(function () {
					$ajaxContainer.html('Error');
				});
		});
	});
})(jQuery);

function getField(name) {
	return parseInt($('#demovox_field_' + name).val())
}

function createField(value, name, lang) {
	var x = getField(name + '_' + lang + '_x'),
		y = getField(name + '_' + lang + '_y'),
		rotate = getField(name + '_' + lang + '_rot');
	return {
		"drawText": value,
		"x": x,
		"y": y,
		"rotate": rotate,
		"size": fontSize,
		"font": fontFamily,
		"color": textColor
	};
}

function hideOnChecked($check, $showHide) {
	$check.change(function () {
		showHide($showHide, !$(this).is(':checked'));
	});
	showHide($showHide, !$check.is(':checked'));
}

function showOnChecked($check, $showHide) {
	$check.change(function () {
		showHide($showHide, $(this).is(':checked'));
	});
	showHide($showHide, $check.is(':checked'));
}

function hideOnSet($check, $showHide, value) {
	if ($check.is("input")) {
		$check.keyup(function () {
			showHide($showHide, $(this).val() !== value);
		});
	}
	$check.change(function () {
		showHide($showHide, $(this).val() !== value);
	});
	showHide($showHide, $check.val() !== value);
}

function showOnSet($check, $showHide, value) {
	if ($check.is("input")) {
		$check.keyup(function () {
			showHide($showHide, $(this).val() === value);
		});
	}
	$check.change(function () {
		showHide($showHide, $(this).val() === value);
	});
	showHide($showHide, $check.val() === value);
}

function showHide($els, show) {
	if (show) {
		var $el;
		$els.each(function () {
			$el = $(this);
			if (!$el.hasClass('hidden')) {
				$el.show();
			}
		});
	} else {
		$els.hide();
	}
}

function nDate(year, month, day) {
	var monthIndex = month - 1;
	return new Date(year, monthIndex, day);
}