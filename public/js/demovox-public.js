/**
 * @property {Object} demovoxData
 *
 * @property {string} demovoxData.ajaxUrl ""|<url>
 * @property {string} demovoxData.successPageRedir "1"|""
 * @property {string} demovoxData.analyticsMatomo "1"|""
 *
 * @property {string} demovoxData.apiAddressEnabled "1"|""
 * @property {string} demovoxData.apiAddressKey <key>|undefined
 * @property {string} demovoxData.apiAddressUrl <url>|undefined
 * @property {string} demovoxData.apiAddressCityInput "1"|""|undefined
 * @property {string} demovoxData.apiAddressGdeInput "1"|""|undefined
 * @property {string} demovoxData.apiAddressGdeSelect "1"|""|undefined
 */
import $ from 'jquery';
import 'select2'; // globally assign select2 fn to $ element
import 'select2/dist/css/select2.css'; // optional if you have css loader
import 'parsleyjs';
import 'parsleyjs/src/i18n/de'; // not supported by Browserify (ES6 import() is used)
import 'parsleyjs/src/i18n/fr';
import 'parsleyjs/src/i18n/it';

$(() => {
	let currentPage = null,
		$el = [];

	// Page 2
	let callApiRequest,
		__cache = [],
		isSwissAbroad = false;

	let apiCache = {
		remove: function (cacheKey) {
			delete __cache[cacheKey];
		},
		exist: function (cacheKey) {
			return __cache.hasOwnProperty(cacheKey) && __cache[cacheKey] !== null;
		},
		get: function (cacheKey) {
			trace('Getting from cache for cacheKey', cacheKey);
			return __cache[cacheKey];
		},
		set: function (cacheKey, cachedData, callback) {
			this.remove(cacheKey);
			__cache[cacheKey] = cachedData;
			if ($.isFunction(callback)) callback(cachedData);
		}
	};

	function getCacheId(reqSource, data) {
		let string = '', count = 0;
		data = data || getReqData(reqSource);
		trace('getCacheId() data for ajax', {data: data});
		$.each(data, function (id, val) {
			if (id === 'api_key') {
				return;
			}
			if (val) {
				count++;
			}
			if (typeof val === "undefined") {
				val = '';
			}
			string += "#" + val;
		});
		return count ? string : null;
	}

	function getReqData(reqSource, params) {
		let gde_name = '', city = '',
			zip = '', street = '', street_no = '';
		if (!isSwissAbroad) {
			zip = ($el.zip.val().length === 4) ? $el.zip.val() : '';
			street = $el.street.val();
			street_no = $el.streetNo.val();
		}
		if (params && typeof params.term !== 'undefined' && params.term !== '') {
			switch (reqSource) {
				case 1:// ZIP
					// Search for ZIP is always enabled
					break;
				case 2:// City
					// Search for cities is not supported (yet)
					//city = params.term;
					break;
				case 3:// gde
					// search term
					if (demovoxData.apiAddressGdeSelect) {
						gde_name = params.term;
					}
					break;
			}
		}
		return {
			zip: zip,
			street: street,
			street_no: street_no,
			api_key: demovoxData.apiAddressKey,
			city: city,
			gde_name: gde_name,
		}
	}

	function getS2AdressData(reqSource, params, success, failure) {
		cancelGetAdressData();
		if (params.dataType === "json") {
			trace('getS2AdressData', {params: params, success: success, failure: failure});
			let cacheKey = getCacheId(reqSource, params.data);
			if (!cacheKey) {
				success({});
				return {
					abort: function () {
						trace("ajax req data was empty, no ajax call executed", {cacheKey: cacheKey, params: params});
					}
				}
			} else if (apiCache.exist(cacheKey)) {
				success(apiCache.get(cacheKey));
				return {
					abort: function () {
						trace("data was cached, no ajax call executed. cacheKey:", cacheKey);
					}
				}
			} else {
				trace("Requesting ajax data", params);
				let $request = $.ajax(params);
				$request.then(function (data) {
					apiCache.set(cacheKey, data);
					return data;
				}).then(success);
				$request.fail(failure);
				return $request;
			}
		} else {
			let $request = $.ajax(params);
			$request.then(success);
			$request.fail(failure);
			return $request;
		}
	}

	function getAdressData(reqSource) {
		trace('getAdressData', reqSource);
		let cacheKey = getCacheId(reqSource);
		if (!cacheKey) {
			return;
		} else if (apiCache.exist(cacheKey)) {
			let data = apiCache.get(cacheKey);
			getAdressDataSuccess(data);
		} else {
			let reqData = getReqData(reqSource);
			callApiRequest = $.ajax({
				url: demovoxData.apiAddressUrl,
				type: 'POST',
				data: reqData,
				dataType: 'json',
				beforeSend: function() {
					ajaxIsLoading();
				},
			})
				.done(function (data) {
					//do something
					apiCache.set(cacheKey, data);
					getAdressDataSuccess(data);
				})
				.fail(function (data) {
					trace('getAdressData: AJAX call failed', data);
				})
				.always(function () {
					ajaxIsLoading(true);
				});
		}
	}

	function ajaxIsLoading(stop, $cont) {
		stop = (typeof stop !== 'undefined') ? stop : false;
		$cont = (typeof $cont !== 'undefined') ? $cont : $('body');
		$cont.css('cursor', stop ? 'auto' : 'progress');
		trace('ajaxIsLoading', stop ? 'auto' : 'progress');
	}

	function getAdressDataSuccess(ajaxData) {
		let city = (ajaxData.hasOwnProperty('city_names') && ajaxData.city_names.length === 1)
			? ajaxData.city_names[0]
			: {city: '', zip: '',};
		let optionCity = new Option(city.city, city.city, true, true);
		$el.city.append(optionCity).trigger('change');
		$el.city.trigger({
			type: 'select2:select',
			params: {data: city}
		});

		let commune = (ajaxData.hasOwnProperty('communes') && ajaxData.communes.length === 1)
			? ajaxData.communes[0]
			: {gde_name: '', gde_nr: '', kanton: '', zip: ''};
		let optionCommune = new Option(commune.gde_name, commune.gde_name, true, true);
		$el.gdeName.append(optionCommune).trigger('change');
		$el.gdeName.trigger({
			type: 'select2:select',
			params: {data: commune}
		});
		trace('getAdressDataSuccess, set to forms', {ajaxData: ajaxData, commune: commune, city: city});
	}

	function cancelGetAdressData() {
		//check if request is defined, and status pending
		if (typeof (callApiRequest) != 'undefined'
			&& callApiRequest.state() === 'pending') {
			//abort request
			callApiRequest.abort()
		}
	}

	// add star for new options
	function createCity(params) {
		let term = $.trim(params.term);
		if (term === '') {
			return null;
		}
		return {id: term, text: term[0].toUpperCase() + term.slice(1) + ' *', city: term, zip: ''};
	}

	function createGde(params) {
		let term = $.trim(params.term);
		if (term === '') {
			return null;
		}
		return {
			id: term,
			text: term[0].toUpperCase() + term.slice(1) + ' *',
			gde_name: term,
			gde_nr: '',
			kanton: '',
			zip: ''
		};
	}

	function submitDemovoxForm() {
		let formData = $el.form.serialize();
		let redirect = false, replace = true;
		if (currentPage === 2) {
			if (demovoxData.successPageRedir) {
				redirect = true;
				formData += '&redirect=true';
			}
		}
		formData += '&ajax=true';
		if (currentPage === 'opt-in') {
			replace = true;
		}
		$.ajax({
			method: "POST",
			url: demovoxData.ajaxUrl,
			data: formData,
			beforeSend: function() {
				ajaxIsLoading();
			},
		})
			.done(function (data) {
				if (redirect) {
					window.location = data;
				} else if (replace) {
					$el.form.replaceWith(data);
					initDemovoxForm();
				}
			})
			.fail(function (data) {
				trace('submitDemovoxForm: AJAX call failed', data);
			})
			.always(function () {
				ajaxIsLoading(true);
			});
	}

	function showFormElements($el) {
		$el.each(function (index) {
			let $this = $(this);
			$this.removeClass('hidden');
			if ($this.hasClass('required')) {
				$this.attr('required', '');
			}
			if ($this.hasClass('select2-hidden-accessible')) {
				$this.next(".select2-container").show();
			}
		});
	}

	function hideFormElements($el) {
		$el.each(function (index) {
			let $this = $(this);
			$this.addClass('hidden');
			if ($this.hasClass('required')) {
				$this.removeAttr('required');
			}
			if ($this.hasClass('select2-hidden-accessible')) {
				$this.next(".select2-container").hide();
			}
			var parsleyid = $this.data('parsley-id');
			if (parsleyid) {
				let $parsley = $('#parsley-id-' + parsleyid);
				if ($parsley.length) {
					$parsley.empty();
				}
			}
		});
	}

	function initDemovoxForm() {
		$el.form = $('form.demovox');

		if ($('#demovox_form_opt-in').length) {
			currentPage = 'opt-in';
		} else if ($('#demovox_form_1').length) {
			currentPage = 1;
		} else if ($('#demovox_form_2').length) {
			currentPage = 2;
		} else {
			currentPage = 3;
		}

		if (currentPage === 1 || currentPage === 2 || currentPage === 'opt-in') {
			window.ParsleyValidator.setLocale(demovoxData.language);
			if (!demovoxData.ajaxUrl) {
				$el.form.parsley();
			} else {
				$el.form.parsley()
					.on('form:submit', function () {
						track('SubmitForm', currentPage);
						submitDemovoxForm();
						return false;
					});
				$el.form.submit(function (e) {
					e.preventDefault();
					return false;
				});
			}
		}
		if (currentPage === 2) {
			$el.zip = $('#zip');
			$el.street = $('#street');
			$el.streetNo = $('#street_no');
			$el.gdeCanton = $('#gde_canton');
			$el.birthDate = $('#birth_date');
			$el.swissAbroad = $('#swiss_abroad');

			$el.birthDate.datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'dd.mm.yy',
				yearRange: '-150:-17',
				defaultDate: '-30y',
			});
			$el.birthDate.focus();
			$el.gdeCanton.select2();

			if ($el.swissAbroad.length) {
				$el.gdeCanton.select2();
				let $country = $('#country'),
					countriesLoaded;
				$el.swissAbroad.change(function () {
					if ($(this).is(':checked')) {
						if (!countriesLoaded) {
							// Text to let user know data is being loaded for long requests.
							$country.find('option:eq(0)').text('Data is being loaded...');
							$.ajax({
								type: 'POST',
								url: demovoxData.ajaxUrl,
								data: {action: 'demovox_countries',},
								dataType: 'json',
								success: function (data) {
									const s2data = $.map(data, function (value, index) {
										return {text: value, id: index,}
									});
									// Clear the notification text of the option.
									$country.find('option:eq(0)').text('');
									// Initialize the Select2 with the data returned from the AJAX.
									$country.select2({data: s2data});
									countriesLoaded = true;
								}
							});
						}

						showFormElements($('.showOnAbroad'));
						hideFormElements($('.hideOnAbroad'));

						$country.select2();
						$country.attr('required', '');

						isSwissAbroad = true;
					} else {
						showFormElements($('.hideOnAbroad'));
						hideFormElements($('.showOnAbroad'));

						$country.val(null).trigger('change');
						$country.removeAttr('required');

						isSwissAbroad = false;
					}
				});
			}
		}

		if (currentPage === 2 && demovoxData.apiAddressEnabled) {
			$el.city = $('#city');
			$el.gdeName = $('#gde_name');
			$el.gdeId = $('#gde_id');
			$el.gdeZip = $('#gde_zip');

			$el.city.select2({
				tags: true,
				ajax: {
					url: demovoxData.apiAddressUrl,
					delay: 300,
					type: 'POST',
					dataType: 'json',
					data: function (params) {
						return getReqData(2, params);
					},
					processResults: function (data) {
						/**
						 * @typedef {Object} data.city_names
						 * @property {string} city
						 * @property {string} zip
						 */
						return {
							results: $.map(data.city_names, function (item) {
								return {
									text: item.city,
									id: item.city,
									zip: item.zip
								}
							})
						}
					},
					transport: function (params, success, failure) {
						return getS2AdressData(2, params, success, failure);
					},
					cache: true,
				},
				minimumResultsForSearch: demovoxData.apiAddressCityInput ? 0 : -1,
				createTag: createCity,
			});

			$el.gdeName.select2({
				tags: true,
				//tokenSeparators: [','],
				ajax: {
					url: demovoxData.apiAddressUrl,
					delay: 300,
					type: 'POST',
					// contentType: 'application/json; charset=utf-8',
					dataType: 'json',
					data: function (params) {
						return getReqData(3, params);
					},
					processResults: function (data) {
						/**
						 * @typedef {Object} data.communes
						 * @property {string} gde_name
						 * @property {string} gde_nr
						 * @property {string} kanton
						 * @property {string} zip
						 */
						return {
							results: $.map(data.communes, function (item) {
								return {
									text: item.gde_name,
									id: item.gde_name,
									gde_nr: item.gde_nr,
									kanton: item.kanton,
									zip: item.zip
								}
							})
						}
					},
					transport: function (params, success, failure) {
						return getS2AdressData(3, params, success, failure);
					},
					cache: true,
				},
				minimumResultsForSearch: demovoxData.apiAddressGdeInput ? 0 : -1,
				createTag: createGde,
			});

			$el.gdeName.on('select2:select', function (e) {
				let data = e.params.data;
				/**
				 * @typedef {Object} data
				 * @property {string} gde_name
				 * @property {string} gde_nr
				 * @property {string} kanton
				 * @property {string} zip
				 */
				$el.gdeCanton.val(data.kanton.toLowerCase()).trigger('change');
				$el.gdeId.val(data.gde_nr);
				$el.gdeZip.val(data.zip);
			});

			if ($el.zip.is("input")) {
				$el.zip.keyup(function () {
					getAdressData(1);
				});
				$el.zip.change(function () {
					getAdressData(1);
				});
			}
		}

		$el.form.on('focus', '.select2.select2-container', function (e) {
			if (e.originalEvent && $(this).find(".select2-selection--single").length > 0) {
				var isOpen = $(this).hasClass('select2-container--open'),
					hasFocus = $(this).hasClass('select2-container--focus');

				if (!isOpen && !hasFocus) {
					$(this).siblings('select:enabled').select2('open');
				}
			}
		});
	}

	function track(name, value) {
		if (demovoxData.analyticsMatomo) {
			if(_paq === undefined){
				console.error('Matomo script was not found on the page, please disable this option in demovox settings');
				return;
			}
			_paq.push(['trackEvent', 'demovox', name, value]);
		}
	}

	function trace(msg1, msg2) {
		//console.log(msg1, msg2);
	}

	$(document).ready(function () {
		if ($('form.demovox').length) {
			initDemovoxForm();
		}
	});
});