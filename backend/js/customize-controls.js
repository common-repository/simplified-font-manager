/* global simplyfmControlsData */

(function( api, $ ) {
	'use strict';

	var l10n     = simplyfmControlsData.l10n || {};
	var fdata    = simplyfmControlsData.fdata || {};
	var options  = simplyfmControlsData.options || {};
	var toptions = simplyfmControlsData.themeoptions || {};
	var advops   = simplyfmControlsData.advops || {};
	var ajax     = simplyfmControlsData.ajax_info;
	var olength  = simplyfmControlsData.olength || 0;
	var tlength  = simplyfmControlsData.tolength || 0;
	var gfonts   = window.simplifiedFonts || {};
	var typekit  = window.simplyfmFontsData;
	var fMarkup  = $('<ul />', {
		'class': 'fwrapper__font-categories'
	});

	if ( typekit && ! $.isEmptyObject(typekit) ) {
		gfonts.typekit = typekit;
	}

	// Init on document ready.
	api.bind('ready', init);

	/**
	 * A control that implements the simplified font manager.
	 *
	 * @class    wp.customize.SimplyfmControl
	 * @augments wp.customize.Control
	 */
	api.SimplyfmControl = api.Control.extend(/** @lends wp.customize.SimplyfmControl.prototype */ {
		/**
		 * Render the control from its JS template, if it exists.
		 *
		 * The control's container must already exist in the DOM.
		 *
		 * @since 1.0.0
		 */
		renderContent: function () {
			var control = this, template, templateId;

			templateId = control.templateSelector;

			// Send setting values to the control template.
			$.each(control.settings, function(key, setting) {
				control.params.value = control.params.value || {};
				control.params.value[key] = setting.get();
			});

			// Send google fonts object to control template.
			control.params.gf = gfonts;

			// l10n for control templates.
			control.params.l10n = l10n.ctmpl;

			// Font data for control templates.
			control.params.fdata = fdata;

			// Replace the container element's content with the control.
			if (document.getElementById('tmpl-' + templateId)) {
				template = wp.template(templateId);
				if (template && control.container) {
					control.container.hide();
					control.container.html(template(control.params));
					
					/**
					 * SetTimeout to prevent abrupt jumping of created control from
					 * below addLoadMoreButton to above the button.
					 */
					setTimeout(function() {
						control.container.slideDown();
						control.container.find('.simplyfm__selectors > textarea').focus();
					}, 100);
				}
			}
		}
	});

	/**
	 * A control that implements the simplified font manager advanced settings.
	 *
	 * @class    wp.customize.SimplyfmAdvancedControl
	 * @augments wp.customize.Control
	 */
	api.SimplyfmAdvancedControl = api.Control.extend(/** @lends wp.customize.SimplyfmControl.prototype */ {
		/**
		 * Render the control from its JS template, if it exists.
		 *
		 * The control's container must already exist in the DOM.
		 *
		 * @since 1.0.0
		 */
		renderContent: function () {
			var control = this, template, templateId;

			templateId = control.templateSelector;

			// Send setting values to the control template.
			$.each(control.settings, function(key, setting) {
				control.params.value = control.params.value || {};
				control.params.value[key] = setting.get();
			});

			// l10n for Advanced Options.
			control.params.l10n = l10n.advanced;

			// If theme specific options are available.
			control.params.tlength = tlength;

			// Replace the container element's content with the control.
			if (document.getElementById('tmpl-' + templateId)) {
				template = wp.template(templateId);
				if (template && control.container) {
					control.container.html(template(control.params));
				}
			}
		}
	});

	/**
	 * Initialize simplyfm script to document ready.
	 * 
	 * @since 1.0.0
	 */
	function init() {
		var i = 0;

		// Add section.
		api.section.add(
			new api.Section('simplyfm_section', {
				title: l10n.section_title,
				priority: 201,
				customizeAction: 'Customizing ▸ Font Manager Options'
			})
		);

		// Add already registered settings and controls.
		if (0 !== olength) {

			for ( ; i < olength; i++ ) {
				addcontrols('simplfied_font_manager_options[' + i + ']', i);
			}
		}

		// Add a button to create new controls.
		addLoadMoreButton();

		// Add plugin advanced options.
		advancedSettings();

		// Create font markup.
		createFontMarkup();
	}

	/**
	 * Add settings and controls.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {string} controlID
	 * @param {int} id
	 */
	function addcontrols(controlID, id) {
		var settings = {}, cursetting, control;
		var settingKeys = [ 'delete', 'family', 'weights', 'italics', 'selectors', 'highlight', 'fsdesktop', 'fudesktop', 'fstablet', 'futablet', 'fsmobile', 'fumobile', 'fontweight', 'fontstyle', 'texttransform', 'lineheight', 'letterspacing', 'enforcestyle' ];

		settingKeys.forEach(function(key) {
			settings[key] = controlID + '[' + key + ']';
		});

		$.each(settings, function(key, setting) {
			var value;

			// Create settings for current control.
			cursetting = api(setting);
			if (! cursetting) {
				if ('undefined' !== typeof options[id] && 'undefined' !== typeof options[id][key]) {
					value = options[id][key];
				} else {
					value = '';
				}
				cursetting = new api.Setting(setting, value, { transport: 'postMessage' });
				api.add(cursetting);
			}

			// Send the setting to the preview to ensure it exists there.
			cursetting.previewer.send('setting', [ cursetting.id, cursetting() ]);
		});

		// Create control.
		control = api.control(controlID);
		if (! control) {
			control = new api.SimplyfmControl(controlID, {
				section: 'simplyfm_section',
				type: 'simplyfm_fonts',
				label: l10n.control_label,
				settings: settings,
				templateId: 'simplified-font-manager-control-content',
				priority: 10,
			});

			// Enable control functionality.
			addControlFunctionality(control);

			//control.params.val = control.settings;
			api.control.add(control);
		}

		// Send control added information to preview js.
		api.previewer.send('simplyfm-control-added', id);
	}

	/**
	 * Add load more button.
	 *
	 * @since 1.0.0
	 */
	function addLoadMoreButton() {
		var control;
		control = new api.Control('simplyfm_load_more', {
			section: 'simplyfm_section',
			type: 'button',
			input_attrs: { value: l10n.loadmore },
			settings: [],
			priority: 20
		});
		api.control.add(control);

		// Manage UI state of control.
		control.deferred.embedded.done(function() {
			var button = control.container.find(':button');
			button.on('click', function() {
				addcontrols('simplfied_font_manager_options[' + olength + ']', olength);
				olength++;
			});
		});
	}

	/**
	 * Font manager advanced settings.
	 * 
	 * @since 1.0.0
	 */
	function advancedSettings() {
		var settings = {}, cursetting, control;
		var controlID = 'simplfied_font_manager_adv_options';
		var settingKeys = [ 'typekit' ];

		settingKeys.forEach(function(key) {
			settings[key] = controlID + '[' + key + ']';
		});

		$.each(settings, function(key, setting) {
			var value;

			// Create settings for current control.
			cursetting = api(setting);
			if (! cursetting) {
				if ('undefined' !== typeof advops[key]) {
					value = advops[key];
				} else {
					value = '';
				}
				cursetting = new api.Setting(setting, value, { transport: 'postMessage' });
				api.add(cursetting);
			}

			// Send the setting to the preview to ensure it exists there.
			cursetting.previewer.send('setting', [ cursetting.id, cursetting() ]);
		});

		// Create control.
		control = api.control(controlID);
		if (! control) {
			control = new api.SimplyfmAdvancedControl(controlID, {
				section: 'simplyfm_section',
				type: 'simplyfm_advanced_options',
				label: l10n.adv_ctrl_labl,
				settings: settings,
				templateId: 'simplyfm-advanced-options',
				priority: 30,
			});

			//control.params.val = control.settings;
			api.control.add(control);
		}

		// Enable control functionality.
		advancedControlFunctionality(control);

		// Send control added information to preview js.
		api.previewer.send('simplyfm-advanced-control-added', controlID);
	}

	/**
	 * Create font markup.
	 *
	 * @since 1.0.0
	 */
	function createFontMarkup() {
			$.each(gfonts, function(cat, fonts) {
				var ul = $('<ul />', { 'class': 'font-cat-' + cat });
				$.each(fonts, function(id, args){
					var li = $('<li />', {
						'class'    : 'font-item',
						'data-font': id,
						'data-cat' : cat,
					}).text(args.family);

					// Add font stack for websafe fonts.
					if ('undefined' !== typeof args.stack) {
						li.attr('data-stack', args.stack);
					}
					ul.append(li);
				});
				fMarkup.append(ul);
			});
	}

	/**
	 * Set font as current font.
	 *
	 * @since 1.0.0
	 * 
	 * @param {object} control
	 * @param {object} currentFont
	 */
	function setCurrentFont(control, currentFont) {
		control.container.find('.selected').removeClass('selected');
		currentFont.addClass('selected');
		control.container.find('.font-settings__name > .name__font').text(currentFont.text());
	}

	/**
	 * Create font-weight checkbox markup.
	 *
	 * @since 1.0.0
	 * 
	 * @param {object} control
	 * @param {object} currentFont
	 */
	function fontAdditional(control, currentFont) {
		var category = currentFont.attr('data-cat');
		var name, params, template;
		
		if (-1 !== category.indexOf('goo')) { // If google font.
			name = currentFont.attr('data-font');
			params = {
				choices: fdata.fweight,
				weights: gfonts[category][name].variants,
				value: control.settings.weights.get(),
				itext: l10n.ctmpl.sfwei,
				italics: l10n.ctmpl.italic,
				ivalue: control.settings.italics.get()
			};
			template = wp.template( 'simplyfm-weight-checkbox' );
			if (template) {
				control.container.find('.font-settings__weights').html(template(params));
			}
		} else if (-1 !== category.indexOf('websafe')) { // If Web Safe font.
			control.container.find('.font-settings__weights').text(l10n.ctmpl.stack + ' - ' + currentFont.attr('data-stack'));
		} else if (-1 !== category.indexOf('typekit')) { // If Adobe fonts.
			control.container.find('.font-settings__weights').text(l10n.ctmpl.stack + ' - ' + currentFont.attr('data-stack'));
		}
	}

	/**
	 * Update font-weight select markup.
	 *
	 * @since 1.0.0
	 * 
	 * @param {object} control
	 * @param {str} category
	 * @param {str} name
	 */
	function fontweightSelect(control, category, name) {
		var params = {
			choices: fdata.fweight,
			value: control.settings.fontweight.get(),
		},
		template = wp.template('simplyfm-weight-select'),
		weights = control.settings.weights.get();

		weights = ( weights && Array.isArray(weights) && weights.length ) ? weights : gfonts[category][name].variants;
		params.weights = weights;
		if (template) {
			control.container.find('select.fontweight').html(template(params));
		}
	}

	/**
	 * Add customizer control functionality.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {object} control
	 */
	function addControlFunctionality(control) {

		var fontWeights = control.container.find('.font-settings__weights');
		var fontToggle = control.container.find('.simplyfm-fonts__toggle');
		var advToggle = control.container.find('.simplyfm__advanced-btn');
		var fontWrapper = control.container.find('.simplyfm-fonts__wrapper');
		var currentFont, fontList, family;

		// Toggle fonts collection container.
		fontToggle.on('click', function(e) {
			var _this = $(this);
			var fontsContainer = fontWrapper.find('.fwrapper__font-categories');
			e.preventDefault();

			// Close font wrapper, if open.
			if (_this.hasClass('toggled-on')) {
				fontWrapper.slideUp('fast');
				_this.removeClass('toggled-on');
				return;
			}

			// Hide all font wrappers and advanced settings.
			control.container.parent().find('.simplyfm__advanced-btn').removeClass('toggled-on');
			control.container.parent().find('.simplyfm-fonts__toggle').removeClass('toggled-on');
			control.container.parent().find('.simplyfm-fonts__wrapper').hide();
			control.container.parent().find('.simplyfm__advanced-settings').hide();

			if (! fontsContainer.length) {
				// Populate font markup.
				fMarkup.find('ul').show();
				fontWrapper.find('.fwrapper__list').append(fMarkup);
				fontWrapper.find('.fwrapper__font-filter input').prop('checked', true);
				fontWrapper.find('.ffilter__items').show();

				// Set current font, if any.
				family = control.settings.family.get();
				currentFont = family ? fontWrapper.find('.font-item[data-font=' + family[0] + ']') : '';
				if ('' !== currentFont && 0 !== currentFont.length) {
					setCurrentFont(control, currentFont);
					fontAdditional(control, currentFont);
					fontweightSelect(control, currentFont.attr('data-cat'), currentFont.attr('data-font'));
					fontWrapper.find('.font-settings__submit > button').attr('disabled', true);
				}
				fontList = fontWrapper.find('.font-item');
				fontList.show();
			}

			// Display font wrapper.
			_this.addClass('toggled-on');
			fontWrapper.slideDown('fast');
		});

		// Font search functionality.
		fontWrapper.find('.fwrapper__search > input').on('keyup', function() {
			var searchTerm = $.trim($(this).val().toLowerCase()).replace(/\s+/g, '');

			if ('undefined' === typeof fontList || 0 === fontList.length) {
				return;
			}

			if (searchTerm) {
				$(this).next().show();
			} else {
				$(this).next().hide();
			}

			fontList.each(function() {
				if ($(this).filter('[data-font *= ' + searchTerm + ']').length > 0 || searchTerm.length < 1) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		});

		// Clear search on click of a button.
		fontWrapper.find('.simplyfm__search-cls').on('click', function() {
			$(this).prev().val('');
			fontList.show();
			$(this).hide();
		});

		// Toggle advanced settings functionality.
		advToggle.on('click', function() {
			var _this = $(this);
			var advset = control.container.find('.simplyfm__advanced-settings');
			if (_this.hasClass('toggled-on')) {
				_this.removeClass('toggled-on');
				advset.slideUp('fast');
				return;
			}

			if ('undefined' === typeof currentFont) {
				family = control.settings.family.get();
				currentFont = family ? fMarkup.find('.font-item[data-font=' + family[0] + ']') : '';
				if ('' !== currentFont && 0 !== currentFont.length) {
					fontweightSelect(control, currentFont.attr('data-cat'), currentFont.attr('data-font'));
				}
			}

			// Hide all font wrappers and advanced settings.
			control.container.parent().find('.simplyfm__advanced-btn').removeClass('toggled-on');
			control.container.parent().find('.simplyfm-fonts__toggle').removeClass('toggled-on');
			control.container.parent().find('.simplyfm-fonts__wrapper').hide();
			control.container.parent().find('.simplyfm__advanced-settings').hide();

			// Show advanced settings.
			_this.addClass('toggled-on');
			advset.slideDown('fast');
		});

		// Delete Control.
		control.container.find('.simplyfm__delete-btn').on('click', function() {
			control.settings.delete.set( true );
			control.active(false);
		});

		// Font size screen switch button funtionality.
		control.container.find('.fsettings__devices > button').on('click', function() {
			var _this = $(this), prevActive;
			if (_this.hasClass('active')) return;
			prevActive = _this.parent().find('.active');
			prevActive.removeClass('active');
			$( '.' + prevActive[0].getAttribute('data-control')).hide();
			_this.addClass('active');
			$( '.' + _this[0].getAttribute('data-control')).show();
		});

		// Enable add font button after click.
		fontWrapper.on('click', function(e) {
			var fname;
			if (! $(e).parent().hasClass('font-settings__submit')) {
				if (0 === currentFont.length) {
					return;
				}
				fname = currentFont.attr('data-cat');
				if (-1 !== fname.indexOf('goo')) { // If google font.
					fontWrapper.find('.font-settings__submit > button').attr('disabled', false);
				} else {
					fontWrapper.find('.font-settings__submit > button').attr('disabled', true);
				}
			}
		});

		// Show font settings when click on a font.
		fontWrapper.on('click', '.font-item', function() {
			var font = [];
			currentFont = $(this);

			if (0 === currentFont.length || currentFont.hasClass('selected')) {
				return;
			}
			
			// Build and save currently selected font's data.
			font[0] = currentFont.attr('data-font'); // id.
			font[1] = currentFont.attr('data-stack') || currentFont.text(); // Font or font stack.
			font[2] = currentFont.attr('data-cat'); //category.
			font[3] = currentFont.text(); // name.
			control.settings.family.set(font);

			setCurrentFont(control, currentFont);
			fontAdditional(control, currentFont);
			fontToggle.text(currentFont.text());
			fontweightSelect(control, currentFont.attr('data-cat'), currentFont.attr('data-font'));
		});

		fontWrapper.on('click', '.font-item.selected', function() {
			fontWrapper.addClass('font-selected');
		});

		// Switch from single font view to fonts-list view.
		control.container.find('.font-settings__back').on('click', function() {
			fontWrapper.removeClass('font-selected');
		});
		
		// Font selection submit.
		fontWrapper.find('.font-settings__submit > button').on('click', function(e) {
			var value = [], i = 0, fname;

			e.stopPropagation();
			if (0 === currentFont.length) {
				return;
			}

			fname = currentFont.attr('data-cat');

			if (-1 !== fname.indexOf('goo')) { // If google font.
				// Build the value as an object using keys from individual checkboxes.
				$.each(fdata.fweight, function(key, subValue) {
					var input  = fontWeights.find('input[value="' + key + '"]');
					var italic = fontWeights.find('.wieght-checkbox-italic > input');
					var category = currentFont.attr('data-cat');
					var name = currentFont.attr('data-font');
					var weights = gfonts[category][name].variants;
					if ('' !== key && input.is(':checked') && input.not(':disabled')) {
						value[ i ] = key;
						i++;
						if (italic.is(':checked') && _.contains(weights, key + 'i')) {
							value[ i ] = key + 'italic';
							i++;
						}
					}
				});
				control.settings.weights.set(value);
				fontweightSelect(control, currentFont.attr('data-cat'), currentFont.attr('data-font'));
			}
			$(this).attr('disabled', true);
		});

		// Font category filter.
		fontWrapper.find('.simplyfm__search-filter').on('click', function() {
			var typekit = fontWrapper.find('.font-cat-typekit');
			var filterContainer = fontWrapper.find('.fwrapper__font-filter');
			$(this).toggleClass('toggled-on');
			$(this).removeClass('tickmark');
			if (0 === typekit.length) {
				filterContainer.find('.ffilter-typekit').hide();
			} else {
				filterContainer.find('.ffilter-typekit').show();
			}
			filterContainer.slideToggle('fast');
		});

		fontWrapper.find('.ffilter__cat-title').on('change', function() {
			var _this = $(this);
			var itemsContainer = _this.parent().next('.ffilter__items');
			var items = itemsContainer.find('input[type="checkbox"]');
			if (_this.is(':checked')) {
				itemsContainer.slideDown('fast');
				items.prop("checked", true).trigger('change');
			} else {
				itemsContainer.slideUp('fast');
				items.prop("checked", false).trigger('change');
			}
		});

		fontWrapper.find('ul.ffilter__items input[type="checkbox"]').on('change', function() {
			var _this = $(this);
			var cat = _this.attr('value');
			var categories = fontWrapper.find('.font-cat-' + cat);
			var btn = fontWrapper.find('.simplyfm__search-filter');
			btn.addClass('tickmark');
			if (_this.is(':checked')) {
				categories.show();
			} else {
				categories.hide();
			}
		});
	}

	/**
	 * Add advanced customizer control functionality.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {object} control
	 */
	function advancedControlFunctionality(control) {
		control.container.on('click', function(e) {
			var _this = $(e.target);

			if (_this.hasClass('simplyfm-advanced__toggle')) {
				e.preventDefault();
				e.stopPropagation();

				// Hide all font wrappers and advanced settings.
				control.container.parent().find('.simplyfm__advanced-btn').removeClass('toggled-on');
				control.container.parent().find('.simplyfm-fonts__toggle').removeClass('toggled-on');
				control.container.parent().find('.simplyfm-fonts__wrapper').hide();
				control.container.parent().find('.simplyfm__advanced-settings').hide();

				// Display plugin advanced options.
				_this.next().slideToggle('fast');
			} else if (_this.hasClass('simplyfm-typekit__btn')) {
				var input = _this.prev('input');
				var msg = control.container.find('.simplyfm-ajax-response');
				var id = $.trim(input.val());
				e.stopPropagation();

				// Disable input.
				input.prop('disabled', true);
				_this.addClass('disabled-click');
				msg.removeClass('simplyfm-failed simplyfm-success').text(l10n.advanced.aj_fetch);

				// Ajax request
				$.ajax( {
					url: ajax.ajaxurl,
					data: {
						action : 'simplyfm_typekit',
						security: ajax.security,
						kitid  : id
					},
					type: 'POST',
					timeout: 6000,
					success: function(response) {
						var fonts = $.parseJSON(response);
						var wrappers = control.container.parent().find('.simplyfm-fonts__wrapper');

						if ($.isEmptyObject(fonts)) {
							if (id) {
								// Print failure message.
								msg.addClass('simplyfm-failed').text(l10n.advanced.aj_fail);
							} else {
								if ( 'undefined' === typeof gfonts.typekit ) {
									// Print empty message.
									msg.addClass('simplyfm-failed').text(l10n.advanced.aj_none);
								} else {
									// Print empty message.
									msg.addClass('simplyfm-success').text(l10n.advanced.aj_delete);
								}
							}

							// Update font object.
							if ( 'undefined' !== typeof gfonts.typekit ) {
								delete gfonts.typekit;
							}

							// Remove saved project id.
							control.settings.typekit.set('');
						} else {
							// Add/Update font's list.
							gfonts.typekit = fonts;

							// Update project id.
							control.settings.typekit.set(id);

							// Print success message.
							msg.addClass('simplyfm-success').text(l10n.advanced.aj_pass);
						}

						// Reset fMarkup.
						fMarkup = $('<ul />', { 'class': 'fwrapper__font-categories' });

						// Regenerate fonts markup.
						createFontMarkup();

						// Remove all previously created font lists.
						wrappers.find('.fwrapper__font-categories').remove();

						// Reset font display in controls.
						wrappers.removeClass('font-selected');
						wrappers.find('.font-settings__submit > button').attr('disabled', false);

						// Enable input.
						input.prop('disabled', false);
						_this.removeClass('disabled-click');
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(errorThrown);

						// Remove saved project id.
						control.settings.typekit.set('');

						// Print failure message.
						msg.addClass('simplyfm-failed').text(l10n.advanced.aj_fail);

						// Enable input.
						input.prop('disabled', false);
						_this.removeClass('disabled-click');
					}
				} );
			} else if (_this.hasClass('typekit-docs__toggle')) {
				e.preventDefault();
				e.stopPropagation();
				_this.next('.typekit-docs__content').slideToggle('fast');
			} else if (_this.hasClass('typekit-docs__close')) {
				e.preventDefault();
				e.stopPropagation();
				_this.parents('.typekit-docs__content').slideUp('fast');
			} else if (_this.hasClass('simplyfm-reset__btn')) {
				e.preventDefault();
				e.stopPropagation();
				resetToThemeDefaults();
			}
		});
	}

	/**
	 * Reset to theme default controls.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {object} control
	 */
	function resetToThemeDefaults() {
		deleteControls();
		setupThemeDefaults();
	}

	/**
	 * Delete all font controls.
	 * 
	 * @since 1.0.0
	 */
	function deleteControls() {
		var i = 0, controlID, control;
		
		// Remove already registered settings and controls.
		if (0 !== olength) {

			for ( ; i < olength; i++ ) {
				controlID = 'simplfied_font_manager_options[' + i + ']';
				control = api.control(controlID);
				if ( control ) {
					control.settings.delete.set( true );
					control.active(false);
				}
			}
		}
	}

	/**
	 * Setup theme default font controls.
	 * 
	 * @since 1.0.0
	 */
	function setupThemeDefaults() {
		var j = 0, newControlID, newControl, fontToggle,
			settingKeys = [ 'delete', 'family', 'weights', 'italics', 'selectors', 'highlight', 'fsdesktop', 'fudesktop', 'fstablet', 'futablet', 'fsmobile', 'fumobile', 'fontweight', 'fontstyle', 'texttransform', 'lineheight', 'letterspacing', 'enforcestyle' ];

		// Add theme default registered settings and controls.
		if (0 !== tlength) {

			for ( ; j < tlength; j++ ) {
				if ( 'undefined' !== typeof toptions[j] ) {
					addcontrols('simplfied_font_manager_options[' + olength + ']', olength);
					newControlID = 'simplfied_font_manager_options[' + olength + ']';
					newControl = api.control(newControlID);
					if (newControl) {
						settingKeys.forEach(function(key) {
							if ( 'undefined' !== typeof toptions[j][key] ) {
								newControl.settings[key].set(toptions[j][key]);
							}
						});
						if ( 'undefined' !== typeof toptions[j].family ) {
							fontToggle = newControl.container.find('.simplyfm-fonts__toggle');
							fontToggle.text(toptions[j]['family'][3]);
						}
					}
					olength++;
				}
			}
		}
	}

})(wp.customize, jQuery);
