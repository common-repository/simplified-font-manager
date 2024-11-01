/* global simplyfmPreviewData */

(function( api, $ ) {
	'use strict';

	var mqtablet = 768, mqdesktop = 1140, styles = {};
	var olength = simplyfmPreviewData.olength || 0;
	var mainObj = simplyfmPreviewData.options || {};
	var advops  = simplyfmPreviewData.advops || {};
	var settingKeys  = [ 'delete', 'selectors', 'family', 'weights', 'italics', 'highlight', 'fsdesktop', 'fudesktop', 'fstablet', 'futablet', 'fsmobile', 'fumobile', 'fontweight', 'fontstyle', 'texttransform', 'lineheight', 'letterspacing', 'enforcestyle' ];
	var cssHelper = {
		'selectors': [ '', '{' ],
		'family': [ 'font-family: ', ';' ],
		'highlight': [ 'border: 2px dashed blue', ';' ],
		'fontweight': [ 'font-weight: ', ';' ],
		'fontstyle': [ 'font-style: ', ';' ],
		'texttransform': [ 'text-transform: ', ';' ],
		'lineheight': [ 'line-height: ', ';' ],
		'letterspacing': [ 'letter-spacing: ', ';' ],
	};

	if ( 'undefined' !== typeof advops.typekit ) {
		mainObj.typekit = advops.typekit;
	}

	// Init on document ready.
	api.bind('preview-ready', init);

	/**
	 * Initialize simplyfm script to preview ready.
	 * 
	 * @since 1.0.0
	 */
	function init() {

		var i = 0;

		for( ; i < olength; i++ ) {
			bindSettingsPreview(i);
			regenerateCss(i);
		}

		wp.customize.preview.bind('simplyfm-control-added', function(id) {
			bindSettingsPreview(id);
		});

		bindTypekitPreview();
		wp.customize.preview.bind('simplyfm-advanced-control-added', function(id) {
			bindTypekitPreview();
		});
	}

	/**
	 * Bind control settings for live preview.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {int} id
	 */
	function bindSettingsPreview(id) {
		settingKeys.forEach(function(key){
			api('simplfied_font_manager_options['+ id + '][' + key + ']', function(setting) {
				setting.bind( function (to) {
					mainObj[id] = mainObj[id] || {};
					mainObj[id][key] = to;
					if ('family' === key || 'weights' === key) {
						enqueuefont(id);
					}
					if ('weights' !== key && 'delete' !== key && 'italics' !== key) {
						regenerateCss(id);
						enqueueCss();
					}
					if ('delete' === key) {
						deleteCss(id);
						enqueueCss();
					}
				});
			});
		});
	}

	/**
	 * Bind typekit control settings for live preview.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {int} id
	 */
	function bindTypekitPreview() {
		api('simplfied_font_manager_adv_options[' + 'typekit' + ']', function(setting) {
			setting.bind( function (to) {
				mainObj.typekit = to;
			});
		});
	}

	/**
	 * Regenrate css for a perticular setting.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {int} controlID
	 */
	function regenerateCss(controlID) {
		var css = '';
		var imp = mainObj[controlID].enforcestyle ? '!important': '';
		var deskunit = mainObj[controlID].fudesktop ? mainObj[controlID].fudesktop : 'px';
		var tabunit = mainObj[controlID].futablet ? mainObj[controlID].futablet : 'px';
		var mobunit = mainObj[controlID].fumobile ? mainObj[controlID].fumobile : 'px';
		if ( ! mainObj[controlID].selectors ) {
			return;
		}

		$.each(cssHelper, function(key, args) {
			var important = 'selectors' === key ? '' : imp, value;
			important = 'letterspacing' === key ? 'px' + imp : important;

			if ( mainObj[controlID][key] ) {
				if ('highlight' === key) {
					value = '';
				} else if ('family' === key) {
					value = '"' + mainObj[controlID][key][1] + '", sans-serif';
				} else {
					value = mainObj[controlID][key];
				}

				css += args[0] + value + important + args[1];
			}
		});

		// Desktop font.
		if (mainObj[controlID].fsdesktop) {
			css += 'font-size: ' + mainObj[controlID].fsdesktop + deskunit + imp + ';';
		}

		// close css.
		css += '}';

		// Tablet font.
		if (mainObj[controlID].fstablet) {
			css += '@media only screen and (max-width: ' + mqdesktop + 'px){';
			css += mainObj[controlID].selectors + '{';
			css += 'font-size: ' + mainObj[controlID].fstablet + tabunit + imp + ';';
			css += '}';
			css += '}';
		}

		// Mobile font.
		if (mainObj[controlID].fsmobile) {
			css += '@media only screen and (max-width: ' + mqtablet + 'px){';
			css += mainObj[controlID].selectors + '{';
			css += 'font-size: ' + mainObj[controlID].fsmobile + mobunit + imp + ';';
			css += '}';
			css += '}';
		}

		styles[controlID] = css;
	}

	/**
	 * Enqueue web font.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {int} controlID
	 */
	function enqueuefont(controlID) {
		var family = mainObj[controlID].family, fontName, gfontUrl, gfontlink;
		var weight = '';
		if (! family) return;

		// Check if it is a google font.
		if (-1 !== family[2].indexOf('goo')) {
			fontName = family[1].split( ' ' ).join( '+' );
			if (mainObj[controlID].weights && Array.isArray(mainObj[controlID].weights)) {
				if ( 0 !== mainObj[controlID].weights.length ) {
					weight = ':' + mainObj[controlID].weights.join();
				}
			}
			gfontUrl = '//fonts.googleapis.com/css?family=' + fontName + weight;
			if ( 0 === $( 'link#simplyfm' + controlID ).length ) {
				gfontlink = $( '<link>', {
					id: 'simplyfm' + controlID,
					href: gfontUrl,
					rel: 'stylesheet',
					type: 'text/css'
				} );
				$( 'link:last' ).after( gfontlink );
			} else {
				$( 'link#' + 'simplyfm' + controlID ).attr( 'href', gfontUrl );
			}
		} else if (-1 !== family[2].indexOf('typekit')) {
			if ( mainObj.typekit ) {
				gfontUrl = 'https://use.typekit.net/' + mainObj.typekit + '.css';
			}
			if ( 0 === $( 'link#simplyfm-typekit-fonts-css' ).length ) {
				gfontlink = $( '<link>', {
					id: 'simplyfm-typekit-fonts-css',
					href: gfontUrl,
					rel: 'stylesheet',
					type: 'text/css'
				} );
				$( 'link:last' ).after( gfontlink );
			} else {
				$( 'link#simplyfm-typekit-fonts-css' ).attr( 'href', gfontUrl );
			}
		}
	}

	/**
	 * Enqueue css.
	 * 
	 * @since 1.0.0
	 */
	function enqueueCss() {
		var css = '';
		var styleID = 'simplyfm-font-css';

		// Generate css.
		$.each(styles, function( key, style ) {
			css += style;
		});

		if ( $( 'style#' + styleID ).length ) {
			$( 'style#' + styleID ).html( css );
		}
		else {
			$( 'head' ).append( '<style id="' + styleID + '">' + css + '</style>' );
			setTimeout( function () {
				$( 'style#' + styleID ).not( ':last' ).remove();
			}, 100 );
		}
	}

	/**
	 * Delete css.
	 * 
	 * @since 1.0.0
	 * 
	 * @param {int} controlID
	 */
	function deleteCss(controlID) {
		styles[controlID] = '';
	}

})(wp.customize, jQuery);
