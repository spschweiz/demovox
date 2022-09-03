import demovoxChart from 'chart.js'

var fontSize, textColor = [0, 0, 0], fontFamily = 'Helvetica';

(function ($) {
	'use strict';

	var demovoxAdminClass = {
		getField: function (name) {
			return parseInt(this.configValue('field_' + name))
		},
		createField: function (value, name, lang, fontSize) {
			var x = this.getField(name + '_' + lang + '_x'),
				y = this.getField(name + '_' + lang + '_y'),
				rotate = this.getField(name + '_' + lang + '_rot');
			return {
				"drawText": value,
				"x": x,
				"y": y,
				"rotate": rotate,
				"size": fontSize,
				"font": fontFamily,
				"color": textColor
			};
		},
		setOnVal: function ($check, $set, checkValue, setValue) {
			if ($check.is("input")) {
				$check.keyup(function () {
					if ($(this).val() === checkValue) {
						$set.val(setValue).change();
					}
				});
			}
			$check.change(function () {
				if ($(this).val() === checkValue) {
					$set.val(setValue).change();
				}
			});
			if ($check.val() === checkValue) {
				$set.val(setValue).change();
			}
		},
		showOnVal: function ($check, $showHide, value, invert) {
			var self = this;
			var invert = (invert !== undefined) ? invert : false;
			if ($check.is("input")) {
				$check.keyup(function () {
					self.showHideEl($showHide, self.isIn($(this).val(), value), invert);
				});
			}
			$check.change(function () {
				self.showHideEl($showHide, self.isIn($(this).val(), value), invert);
			});
			self.showHideEl($showHide, $check.val() === value, invert);
		},
		hideOnVal: function ($check, $showHide, value) {
			this.showOnVal($check, $showHide, value, true);
		},
		showOnChecked: function ($check, $showHide, invert) {
			var self = this;
			var invert = (invert !== undefined) ? invert : false;
			$check.change(function () {
				self.showHideEl($showHide, $(this).is(':checked'), invert);
			});
			self.showHideEl($showHide, $check.is(':checked'), invert);
		},
		hideOnChecked: function ($check, $showHide) {
			this.showOnVal($check, $showHide, true);
		},
		isIn: function (needle, haystack) {
			if (Array.isArray(haystack)) {
				return haystack.indexOf(needle) !== -1;
			} else {
				return needle === haystack;
			}
		},
		showHideEl: function ($els, show, invert) {
			var invert = (invert !== undefined) ? invert : false;
			if ((show && !invert) || (!show && invert)) {
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
		},
		nDate: function (year, month, day) {
			var monthIndex = month - 1;
			return new Date(year, monthIndex, day);
		},
		configValue: function (selector) {
			selector = '#demovox_' + this.getCollectionId() + '_' + selector;
			var $el = $(selector);
			if ($el.length < 1) {
				console.error('demovoxAdminClass.configValue: $el not found', {$el: $el, selector: selector});
				return '';
			}
			return $el.val()
		},
		getCollectionId: function() {
			var $el = $('#cln');
			if ($el.length < 1) {
				console.error('demovoxAdminClass.getCollectionId: $el not found', $el);
				return '';
			}
			return $el.val()
		},
		initShowPdf: function ($btn) {
			let self = this;
			$btn.click(function() {
				var $container = $(this).closest('div'),
					lang = $(this).data('lang'),
					fontSize = parseInt(self.configValue('fontsize')),
					qrMode = self.configValue('field_qr_mode'),
					pdfUrl = self.configValue('signature_sheet_' + lang),
					fields = [
						self.createField('BE', 'canton', lang, fontSize),
						self.createField('Bern', 'commune', lang, fontSize),
						self.createField('3001', 'zip', lang, fontSize),
						self.createField('21', 'birthdate_day', lang, fontSize),
						self.createField('10', 'birthdate_month', lang, fontSize),
						self.createField('88', 'birthdate_year', lang, fontSize),
						self.createField('Theaterplatz 4', 'street', lang, fontSize),
					],
					qrData = qrMode === 'disabled'
						? null
						: {
							"text": "JNXWE",
							"x": self.getField('qr_img_' + lang + '_x'),
							"y": self.getField('qr_img_' + lang + '_y'),
							"rotate": self.getField('qr_img_' + lang + '_rot'),
							"size": self.getField('qr_img_size_' + lang),
							"textX": self.getField('qr_text_' + lang + '_x'),
							"textY": self.getField('qr_text_' + lang + '_y'),
							"textRotate": self.getField('qr_text_' + lang + '_rot'),
							"textSize": fontSize,
							"textColor": textColor
						};
				createPdf('preview', pdfUrl, fields, qrData, $container);
			});
		},
		initAjaxButton: function ($container) {
			$container.find('.ajaxButton').click(function() {
				var cont = $(this).data('container'),
					ajaxUrl = $(this).data('ajax-url'),
					confirmTxt = $(this).data('confirm'),
					$ajaxContainer = $(this).parent().find(cont ? cont : '.ajaxContainer');
				if(!$ajaxContainer.length){
					if (cont) {
						$ajaxContainer = $(cont);
					}
					if (!$ajaxContainer.length) {
						console.error('initAjaxButton: $ajaxContainer not found', $ajaxContainer);
						return;
					}
				}
				if (typeof confirmTxt !== 'undefined' && !confirm(confirmTxt)) {
					return;
				}
				$ajaxContainer.css('cursor', 'progress');
				$ajaxContainer.html('Loading...');
				$.get(ajaxUrl)
					.done(function (data) {
						$ajaxContainer.html(data);
						demovoxAdminClass.initAjaxButton($ajaxContainer);
					})
					.fail(function () {
						$ajaxContainer.html('Error');
					})
					.always(function () {
						$ajaxContainer.css('cursor', 'auto');
					});
			});
		}
	};

	global.demovoxAdminClass = demovoxAdminClass;

	var $input;
	$(function () {
		var demovoxMediaUploader;
		$('.demovox .uploadButton').click(function (e) {
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
				title: demovoxData.uploader.title,
				button: {
					text: demovoxData.uploader.text
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
		demovoxAdminClass.initShowPdf($('.demovox .showPdf'));
		demovoxAdminClass.initAjaxButton($('.demovox'));
	});
})(jQuery);

global.demovoxChart = demovoxChart;
