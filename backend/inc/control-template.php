<?php
/**
 * Simplified Font Manager custom control template for customizer.
 *
 * @package Simplified_Font_Manager
 * @since 1.0.0
 */

?>

<script id="tmpl-simplified-font-manager-control-content" type="text/html">
	<div class="simplyfm">
		<div class="simplyfm__selectors css-selectors">
			<textarea value="{{ data.value.selectors }}" placeholder="{{data.l10n.slabel}}" data-customize-setting-key-link="selectors" rows="2"></textarea>
		</div>
		<div class="simplyfm__font-selection simplyfm-fonts">
			<# if ( data.value.family ) { #>
				<a class="simplyfm-fonts__toggle" href="#">{{{data.value.family[3]}}}</a>
			<# } else { #>
				<a class="simplyfm-fonts__toggle" href="#">{{{data.l10n.flabel}}}</a>
			<# } #>
		</div>
		<span class="simplyfm__advanced-btn">
			<span class="screen-reader-text">{{{ data.l10n.advset }}}</span>
		</span>
		<div class="simplyfm-fonts__wrapper fwrapper">
			<div class="fwrapper__list">
				<div class="fwrapper__search">
					<input type="text" placeholder="{{data.l10n.sfont}}">
					<span class="simplyfm__search-cls">
						<span class="screen-reader-text">{{{ data.l10n.sfont }}}</span>
					</span>
					<span class="simplyfm__search-filter">
						<span class="screen-reader-text">{{{ data.l10n.ffilter }}}</span>
					</span>
				</div>
			</div>
			<div class="fwrapper__font-settings">
				<div class="font-settings">
					<span class="font-settings__back"><span class="text">{{{data.l10n.back}}}</span></span>
					<div class="font-settings__name"><span class="name__font"></span></div>
					<div class="font-settings__weights"></div>
					<div class="font-settings__submit">
						<button type="button" class="button button-primary">{{{data.l10n.afont}}}</button>
					</div>
				</div>
			</div>
			<div class="fwrapper__font-filter ffilter">
				<span class="ffilter__title">{{{data.l10n.ffilter}}}</span>
				<# _.each( data.fdata.ffilter, function( args, label ) { #>
				<div class="ffilter__container ffilter-{{label}}">
					<span class="ffilter__item"><input type="checkbox" class="ffilter__cat-title" checked/><span>{{args.title}}</span></span>
					<ul class="ffilter__items">
					<# _.each( args.list, function( label, choice ) { #>
						<li class="ffilter__item">
							<input type="checkbox" value="{{ choice }}" checked/><span>{{ label }}</span>
						</li>
					<# } ) #>
					</ul>
				</div>
				<# } ) #>
			</div>
		</div>
		<div class="simplyfm__advanced-settings fsettings">

			<div class="fsettings__highlight fsettings__child">
				<input type="checkbox" value="{{ data.value.highlight }}" data-customize-setting-key-link="highlight" />
				<label>{{{data.l10n.hlight}}}</label>
			</div>
			<hr />
			<div class="fsettings__size fontsize fsettings__child">
				<# if ( data.l10n.fsize ) { #>
				<span class="customize-control-title">{{{data.l10n.fsize}}}</span>
				<# } #>
				<div class="fsettings__devices fdevices">
					<button type="button" class="fdevices__desktop active" data-control="fontsize__desktop">
						<span class="screen-reader-text">{{{data.l10n.dbtn}}}</span>
					</button>
					<button type="button" class="fdevices__tablet" data-control="fontsize__tablet">
						<span class="screen-reader-text">{{{data.l10n.tbtn}}}</span>
					</button>
					<button type="button" class="fdevices__mobile" data-control="fontsize__mobile">
						<span class="screen-reader-text">{{{data.l10n.mbtn}}}</span>
					</button>
				</div>
				<div class="fontsize__desktop">
					<# if ( data.fdata.scrn.desktop ) { #>
					<span class="customize-control-title">{{{data.fdata.scrn.desktop}}}</span>
					<# } #>
					<input type="number" value="{{ data.value.fsdesktop }}" min="0" step="1" data-customize-setting-key-link="fsdesktop" />
					<select class="fudesktop" data-customize-setting-key-link="fudesktop">
						<# _.each( data.fdata.funit, function( text, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.value.fudesktop ) { #> selected="selected" <# } #>>{{ text }}</option>
						<# } ) #>
					</select>
				</div>
				<div class="fontsize__tablet">
					<# if ( data.fdata.scrn.tablet ) { #>
					<span class="customize-control-title">{{{data.fdata.scrn.tablet}}}</span>
					<# } #>
					<input type="number" value="{{ data.value.fstablet }}" min="0" step="1" data-customize-setting-key-link="fstablet" />
					<select class="futablet" data-customize-setting-key-link="futablet">
						<# _.each( data.fdata.funit, function( text, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.value.futablet ) { #> selected="selected" <# } #>>{{ text }}</option>
						<# } ) #>
					</select>
				</div>
				<div class="fontsize__mobile">
					<# if ( data.fdata.scrn.mobile ) { #>
					<span class="customize-control-title">{{{data.fdata.scrn.mobile}}}</span>
					<# } #>
					<input type="number" value="{{ data.value.fsmobile }}" min="0" step="1" data-customize-setting-key-link="fsmobile" />
					<select class="fumobile" data-customize-setting-key-link="fumobile">
						<# _.each( data.fdata.funit, function( text, choice ) { #>
							<option value="{{ choice }}" <# if ( choice === data.value.fumobile ) { #> selected="selected" <# } #>>{{ text }}</option>
						<# } ) #>
					</select>
				</div>
			</div>
			<div class="fsettings__weight fsettings__child">
				<# if ( data.l10n.fweight ) { #>
				<span class="customize-control-title">{{{data.l10n.fweight}}}</span>
				<# } #>
				<select class="fontweight" data-customize-setting-key-link="fontweight">
					<# _.each( data.fdata.fweight, function( text, choice ) { #>
						<option value="{{ choice }}" <# if ( choice === data.value.fontweight ) { #> selected="selected" <# } #>>{{ text }}</option>
					<# } ) #>
				</select>
			</div>
			<div class="fsettings__style fsettings__child">
				<# if ( data.l10n.fstyle ) { #>
				<span class="customize-control-title">{{{data.l10n.fstyle}}}</span>
				<# } #>
				<select class="fontstyle" data-customize-setting-key-link="fontstyle">
					<# _.each( data.fdata.fstyle, function( text, choice ) { #>
						<option value="{{ choice }}" <# if ( choice === data.value.fonstyle ) { #> selected="selected" <# } #>>{{ text }}</option>
					<# } ) #>
				</select>
			</div>
			<div class="fsettings__ttform fsettings__child">
				<# if ( data.l10n.ttform ) { #>
				<span class="customize-control-title">{{{data.l10n.ttform}}}</span>
				<# } #>
				<select class="texttransform" data-customize-setting-key-link="texttransform">
					<# _.each( data.fdata.ftt, function( text, choice ) { #>
						<option value="{{ choice }}" <# if ( choice === data.value.text_transform ) { #> selected="selected" <# } #>>{{ text }}</option>
					<# } ) #>
				</select>
			</div>
			<div class="fsettings__lheight fsettings__child">
				<# if ( data.l10n.lheight ) { #>
				<span class="customize-control-title">{{{data.l10n.lheight}}}</span>
				<# } #>
				<input type="number" value="{{ data.value.lineheight }}" min="0" data-customize-setting-key-link="lineheight" />
			</div>
			<div class="fsettings__lspacing fsettings__child">
				<# if ( data.l10n.lspace ) { #>
				<span class="customize-control-title">{{{data.l10n.lspace}}}</span>
				<# } #>
				<input type="number" value="{{ data.value.letterspacing }}" min="0" data-customize-setting-key-link="letterspacing" />
				<span class="customize-control-description">px</span>
			</div>
			<div class="fsettings__enforce fsettings__child">
				<span class="customize-control-title">{{{data.l10n.enforce}}}</span>
				<input type="checkbox" value="{{ data.value.enforcestyle }}" data-customize-setting-key-link="enforcestyle" />
			</div>
			<span class="simplyfm__delete-btn">
				<span class="tiny-text">{{{ data.l10n.delete }}}</span>
			</span>
		</div>
		<hr/>
	</div>
</script>
<script id="tmpl-simplyfm-weight-checkbox" type="text/html">
	<span class="weight-checkbox-info">{{{data.itext}}}</span>
	<ul class="custom-checkbox">
	<# _.each( data.choices, function( label, choice ) { #>
		<# if ( '' !== choice ) { #>
		<li class="checkbtn<# if ( ! _.contains( data.weights, choice ) ) { #> disabled<# } #>">
			<label>
				<input class="screen-reader-text" type="checkbox" value="{{ choice }}" <# if ( _.contains( data.value, choice ) && _.contains( data.weights, choice ) ) { #> checked<# } #><# if ( ! _.contains( data.weights, choice ) ) { #> disabled<# } #> /><span>{{ label }}</span>
			</label>
		</li>
		<# } #>
	<# } ) #>
	</ul>
	<div class="wieght-checkbox-italic">
		<input type="checkbox" value="{{ data.ivalue }}" data-customize-setting-key-link="italics" />
		<label>{{{data.italics}}}</label>
	</div>
</script>
<script id="tmpl-simplyfm-weight-select" type="text/html">
	<# _.each( data.choices, function( text, choice ) { #>
		<# if ( _.contains( data.weights, choice ) || '' === choice ) { #>
			<option value="{{ choice }}" <# if ( choice === data.value ) { #> selected="selected" <# } #>>{{ text }}</option>
		<# } #>
	<# } ) #>
</script>
<script id="tmpl-simplyfm-advanced-options" type="text/html">
	<div class="simplyfm-advanced simplyfm">
			<a class="simplyfm-advanced__toggle" href="#">{{{data.l10n.adv_label}}}</a>
			<div class="simplyfm-advanced__wrapper simplyfm-advop">
				<div class="simplyfm-advop-typekit simplyfm-typekit">
					<span class="customize-control-title">{{{data.l10n.add_adobe}}}</span>
					<div class="simplyfm-typekit__docs typekit-docs">
						<span class="typekit-docs__title">{{{data.l10n.docstitle}}}</span>
						<a class="typekit-docs__toggle" href="#">?</a>
						<ul class="typekit-docs__content">
							<li class="docs__items">{{{data.l10n.typedocs.p1}}}</li>
							<li class="docs__items">{{{data.l10n.typedocs.p2}}}</li>
							<li class="docs__items">{{{data.l10n.typedocs.p3}}}</li>
							<li class="docs__items">{{{data.l10n.typedocs.p4}}}</li>
							<li class="docs__items last"><a class="typekit-docs__close" href="#">{{{data.l10n.typedocs.p5}}}</a></li>
						</ul>
					</div>
					<input type="text" placeholder="{{data.l10n.pro_id}}" value="{{ data.value.typekit }}">
					<span class="simplyfm-typekit__btn"><span class="screen-reader-text">{{{data.l10n.submit}}}</span></span>
					<span class="simplyfm-ajax-response"></span>
				</div>
			</div>
			<# if ( 0 !== parseInt(data.tlength) ) { #>
				<a class="simplyfm-reset__btn" href="#">{{{data.l10n.reset}}}</a>
			<# } #>
	</div>
</script>
