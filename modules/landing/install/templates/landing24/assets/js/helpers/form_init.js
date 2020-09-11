;(function ()
{
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		var selector = event.makeRelativeSelector(".bitrix24forms");

		if (event.block.querySelectorAll(selector).length > 0)
		{
			if (typeof window["landingForms"] == 'undefined')
			{
				window["landingForms"] = {};
			}
			window["landingForms"][event.block.id] = new LandingForm(selector, event.block);
			window["landingForms"][event.block.id].initForm();
		}
	});


	// reinit form with new style - if it needed
	BX.addCustomEvent("BX.Landing.Block:updateStyle", function (event)
	{
		var selector = event.makeRelativeSelector(".bitrix24forms");

		if (event.block.querySelectorAll(selector).length > 0)
		{
			var currentForm = window["landingForms"][event.block.id];
			if (typeof event.node != 'undefined' && typeof event.data != 'undefined' && typeof currentForm != 'undefined')
			{
				// recreate styles if needed, Use just first node
				if (currentForm.readFormStylesFromNode(event.node[0]))
				{
					currentForm.createFormOptions();
					currentForm.onFormReloadWithDebounce();
				}
			}
		}
	});


	// If change form - remove old, create new. If change settings - ewinit form with new view
	BX.addCustomEvent("BX.Landing.Block:Node:updateAttr", function (event)
	{
		var selector = event.makeRelativeSelector(".bitrix24forms");

		//if change form - clear form params, but save form options. Use options of prev form
		if (event.block.querySelectorAll(selector).length > 0)
		{
			var currentForm = window["landingForms"][event.block.id];
			if (typeof event.data != 'undefined' && typeof currentForm != 'undefined')
			{
				for (var attr in event.data)
				{
					attr = attr.replace('data-', '');

					// CHANGE form
					if (attr == currentForm.dataFormId)
					{
						currentForm.onFormRemove();
						currentForm.initForm();
					}

					// show or not HEADER
					else if (attr == currentForm.dataAttributeShowHeader)
					{
						if (
							Object.keys(currentForm.formOptions).length > 0
						)
						{
							currentForm.formOptions.css.content =
								currentForm.matchShowHeader(currentForm.formOptions.css.content);
						}
						currentForm.onFormReload();
					}

					// use CUSTOM STYLE of FORM STYLE
					else if (attr == currentForm.dataAttributeUseStyle)
					{
						currentForm.createFormOptions();
						currentForm.onFormReload();
					}
				}
			}
		}
	});


	// on REMOVE we must remove form from array
	BX.addCustomEvent("BX.Landing.Block:remove", function (event)
	{
		var selector = event.makeRelativeSelector(".bitrix24forms");

		if (document.querySelectorAll(selector).length > 0)
		{
			var currentForm = window["landingForms"][event.block.id];
			if (typeof currentForm != 'undefined')
			{
				currentForm.onFormRemove();
			}
			delete (window["landingForms"][event.block.id]);
		}
	});


	var LandingForm = function (selector, block)
	{
		this.dataFormId = "b24form";
		this.dataAttributeDomain = "b24form-original-domain";
		this.dataAttributePrefix = "data-form-style-";
		this.dataAttributeUseStyle = "b24form-use-style";
		this.dataAttributeShowHeader = "b24form-show-header";
		this.dataAttributeIsConnector = "b24form-connector";

		this.hideHeaderString = ".crm-webform-header-container{display:none;}";
		this.hideBitrixLogoString = ".crm-webform-bottom-link{display:none}.crm-webform-bottom-logo-container{height:0;margin:0;}";
		this.additionalCssString =
			".content-wrap{min-height:170px;}" +
			".crm-webform-body{padding-bottom:0;padding-top:0;}" +
			".crm-webform-block.crm-webform-default{margin-bottom:0;}" +
			".calendar-resbook-webform-block-date-item-inner{transition: border-color ease-in-out 0.5s;}";
		this.fixHeightCssString =
			".crm-webform-fieldset-footer{padding-bottom:0;}" +
			".content-wrap{padding-bottom:0;}";

		this.block = block;
		this.selector = selector;
		this.iframe = null;	//will be determinate when frame initialized

		// initialize form loader only in first run
		var domainNode = BX.findChild(this.block, {'attribute': 'data-' + this.dataAttributeDomain}, true, false);
		if (domainNode && this.isFormChosen())
		{
			this.domain = BX.data(domainNode, this.dataAttributeDomain);
			this.initFormLoader(window, document, this.createFullDomain() + '/bitrix/js/crm/form_loader.js', 'b24form');
		}

		// what style may find in block
		// format data-attribute: param: style name in BX.style
		this.styleParams = {
			'wrapper-padding': {'params': ['padding-top']},
			'bg': {'params': ['background-color', 'background-image']},
			'bg-content': {'params': ['background-color']},
			'bg-block': {'params': ['background-color']},
			'bg-as-text': {'params': ['background-color']},
			'light-bg': {'params': ['background-color']},
			'main-bg': {'params': ['background-color']},
			'main-bg-light': {'params': ['background-color']},
			'main-border-color': {'params': ['border-top-color', 'border-bottom-color', 'border-left-color', 'border-right-color']},
			'main-font-family': {'params': ['font-family']},
			'main-font-color': {'params': ['color']},
			'main-font-color-hover': {'params': ['color']},
			'main-font-weight': {'params': ['font-weight']},
			'second-font-color': {'params': ['color']},
			'icon-font-color': {'params': ['color']},
			'button-font-color': {'params': ['color']},
			'header-font-size': {'params': ['font-size']},
			'header-font-weight': {'params': ['font-weight']},
			'header-text-font-size': {'params': ['font-size']},
			'label-font-size': {'params': ['font-size']},
			'border-block': {
				'params': [
					'border-top-color', 'border-bottom-color', 'border-left-color', 'border-right-color',
					'border-top-style', 'border-bottom-style', 'border-left-style', 'border-right-style',
					'border-top-width', 'border-bottom-width', 'border-left-width', 'border-right-width'
				]
			},
			'input-bg': {'params': ['background-color']},
			'input-box-shadow': {'params': ['box-shadow']},
			'input-select-bg': {'params': ['background-color']},
			'input-bg-light': {'params': ['background-color']},
			'input-bg-light2': {'params': ['background-color']},
			'input-bg-light3': {'params': ['background-color']},
			'gradient-box-shadow': {'params': ['box-shadow']},
			'input-border': {
				'params': [
					'border-top-color', 'border-bottom-color', 'border-left-color', 'border-right-color',
					'border-top-style', 'border-bottom-style', 'border-left-style', 'border-right-style',
					'border-top-width', 'border-bottom-width', 'border-left-width', 'border-right-width',
					'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius'
				]
			},
			'input-border-radius': {
				'params': ['border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius']
			},
			'input-border-color': {
				'params': ['border-top-color', 'border-bottom-color', 'border-left-color', 'border-right-color']
			},
			'input-border-hover': {
				'params': [
					'border-top-color', 'border-bottom-color', 'border-left-color', 'border-right-color',
					'border-top-style', 'border-bottom-style', 'border-left-style', 'border-right-style',
					'border-top-width', 'border-bottom-width', 'border-left-width', 'border-right-width'
				]
			},
			'agreement-label-font-size': {'params': ['font-size']}
		};

		// computed styles
		this.styles = {};

		// what class in form may be modify
		// format class: keys from styles object, they must be attach to class
		this.selectors = {
			'.crm-webform-wrapper, .content-wrap': ['wrapper-padding'],
			'body.crm-webform-iframe': ['bg'],
			'.content, .page-theme-transparent .content': ['bg-content'],
			'.crm-webform-block, .page-theme-transparent .crm-webform-block': ['bg-block', 'border-block'],
			'.crm-webform-header-container': [
				'bg-block', 'border-block', 'main-font-family', 'main-font-color', 'main-font-weight',
				'header-text-font-size'
			],
			'.crm-webform-header-container h2': ['main-font-color', 'bg-block'],
			'.crm-webform-resourcebooking-wrap-live': ['bg-block'],
			'.crm-webform-field-resourcebooking .crm-webform-label-content': ['bg-block'],
			'.crm-webform-inner-header': ['main-font-color', 'main-font-family'],
			'.crm-webform-mini-cart-title, .crm-webform-mini-cart-services-container': [
				'main-font-color', 'main-font-family'
			],
			'.crm-webform-header': ['main-font-family', 'header-font-weight', 'header-font-size'],
			'.crm-webform-label': ['main-font-family', 'label-font-weight', 'label-font-size', 'second-font-color'],
			'button.crm-webform-submit-button, .crm-webform-file-upload .crm-webform-file-button': [
				'main-bg', 'main-font-family', 'button-font-color', 'input-border-radius'
			],
			'.crm-webform-label-content, .crm-webform-file-text-field': ['input-bg'],
			'.crm-webform-input-label': ['input-box-shadow'],
			'.crm-webform-input, .crm-webform-file-text-field': [
				'main-font-family', 'main-font-weight', 'input-border', 'main-font-color'
			],
			'.crm-webform-icon': ['input-border-color', 'second-font-color', 'icon-font-color'],
			'.crm-webform-desktop-font-style a': ['second-font-color'],
			'.crm-webform-desktop-font-style a:hover': ['main-font-color'],
			'.crm-webform-input option': ['main-font-family', 'input-select-bg', 'main-font-color'],
			'.crm-webform-active .crm-webform-input, .crm-webform-active mark, .crm-webform-input:hover': [
				'input-border-hover'
			],
			'.crm-webform-checkbox-container:hover i': ['main-border-color'],
			'.crm-webform-checkbox-name': ['main-font-family', 'second-font-color'],
			'.crm-webform-input+i:after': ['main-font-color-hover'],
			'.crm-webform-agreement-modifier .crm-webform-checkbox-name': ['agreement-label-font-size'],
			// resource booking
			'.calendar-resbook-webform-block-input-dropdown': [
				'input-bg', 'input-border', 'main-font-color'
			],
			'.calendar-resbook-webform-block-strip': ['input-bg'],
			'.calendar-resbook-webform-block-strip-date': ['main-font-color'],
			'.calendar-resbook-webform-block-strip-day': ['second-font-color'],
			'.popup-window[id^="calendar_popup"], .popup-window[id^="calendar_popup"] .popup-window-content': ['light-bg'],
			'.calendar-resbook-webform-block-input-dropdown:hover': ['input-border-hover'],
			'.calendar-resbook-webform-block-input-dropdown::before': ['bg-as-text'],
			'.popup-window, .popup-window .popup-window-content': ['input-bg-light'],
			'.popup-window .menu-popup-item-text': ['main-font-color'],
			'.popup-window .menu-popup-item:hover, .popup-window .menu-item-selected': ['input-bg-light2'],
			'.popup-window .popup-window-content .menu-popup-item:hover .menu-popup-item-text': ['main-font-color'],
			'.calendar-resbook-webform-block-inner .calendar-resbook-webform-block-title': ['second-font-color'],
			'.calendar-resbook-webform-block-date-item-select .calendar-resbook-webform-block-date-item-inner': ['main-bg'],
			'.calendar-resbook-webform-block-date-item-inner': ['input-bg', 'input-border'],
			'.calendar-resbook-webform-block-date-item-inner:hover': ['input-border-hover'],
			'.calendar-resbook-webform-block-date-number': ['main-font-color'],
			'.calendar-resbook-webform-block-date-item-select .calendar-resbook-webform-block-date-number': ['button-font-color'],
			'.calendar-resbook-webform-block-date-item-select .calendar-resbook-webform-block-date-day': ['button-font-color'],
			'.calendar-resbook-webform-block-arrow': ['input-bg-light3', 'gradient-box-shadow'],
			'.calendar-resbook-webform-block-col-item-inner': ['main-bg-light'],
			'.calendar-resbook-webform-block-col-item-select .calendar-resbook-webform-block-col-item-inner': ['main-bg', 'button-font-color'],
			'.calendar-resbook-webform-block-col-time:first-child': ['main-font-color'],
			'.calendar-resbook-webform-block-col-item-select .calendar-resbook-webform-block-col-time:first-child': ['button-font-color'],
			'.calendar-resbook-webform-block-result-inner, .page-theme-image .calendar-resbook-webform-block-result-inner': [
				'input-bg', 'main-border-color', 'main-font-color'
			],
			'.calendar-resbook-webform-block-result-text': ['second-font-color']
		};

		this.formParams = {};
		this.formOptions = {};
	};

	LandingForm.prototype = {
		// if not set form id - return false
		isFormChosen: function ()
		{
			var b24Forms = document.querySelectorAll(this.selector);
			if (b24Forms.length > 0)
			{
				for (var i = 0, c = b24Forms.length; i < c; i++)
				{
					if (BX.data(b24Forms[i], this.dataFormId))
					{
						return true;
					}
				}
			}
			return false;
		},

		initFormLoader: function (w, d, u, b)
		{
			// if first run - init form loader
			if (typeof w["Bitrix24FormLoaderInitialised"] == 'undefined' || w["Bitrix24FormLoaderInitialised"] != true)
			{
				w['Bitrix24FormObject'] = b;
				w[b] = w[b] || function ()
				{
					arguments[0].ref = u;
					(w[b].forms = w[b].forms || []).push(arguments[0])
				};
				if (w[b]['forms']) return;
				var s = d.createElement('script');
				var r = 1 * new Date();
				s.async = 1;
				s.src = u + '?' + r;
				var h = d.getElementsByTagName('script')[0];
				h.parentNode.insertBefore(s, h);

				// check init flag
				w["Bitrix24FormLoaderInitialised"] = true;
			}
		},

		initForm: function ()
		{
			// do nothing if form not chosen
			if (!this.isFormChosen())
			{
				this.createNoFormMessage();
				return;
			}

			// do nothing if domain not set
			if (!this.domain)
			{
				this.createErrorDomainMessage();
				return;
			}

			this.createFormParams();
			// apply form options only after frame creating
			BX.addCustomEvent('onFormFrameLoad', BX.proxy(this.onFormFrameLoad, this));

			// clear container to preserve no-js alert
			var container = document.querySelector(this.selector);
			container.innerHTML = '';

			this.addFormInLoader();
			if (typeof Bitrix24FormLoader != 'undefined')
			{
				// init Bitrix24FormLoader only once!
				if (typeof Bitrix24FormLoader.forms == 'undefined' || Object.keys(Bitrix24FormLoader.forms).length == 0)
				{
					Bitrix24FormLoader.init();
				}
				// if Bitrix24FormLoader already init - just load new form
				else
				{
					Bitrix24FormLoader.preLoad(this.formParams);
				}
			}
		},

		createNoFormMessage: function ()
		{
			if (BX.Landing.getMode() == "view")
			{
				return;
			}

			var formContainer = document.querySelector(this.selector);
			if (
				typeof BX.data(formContainer, this.dataAttributeIsConnector) != 'undefined'
				&& BX.data(formContainer, this.dataAttributeIsConnector) == 'Y'
			)
			{
				this.createErrorMessage(BX.message('LANDING_BLOCK_WEBFORM_NO_FORM'), BX.message('LANDING_BLOCK_WEBFORM_NO_FORM_BUS_NEW'));
			}
			else
			{
				this.createErrorMessage(BX.message('LANDING_BLOCK_WEBFORM_NO_FORM'), BX.message('LANDING_BLOCK_WEBFORM_NO_FORM_CP'));
			}
		},


		createErrorDomainMessage: function ()
		{
			this.createErrorMessage();
		},


		createErrorMessage: function (title, message)
		{
			// show alert only in edit mode
			if (BX.Landing.getMode() == "view")
			{
				return;
			}

			if (title === undefined || title === null || !title)
			{
				title = BX.message('LANDING_BLOCK_WEBFORM_ERROR');
			}

			if (message === undefined || message === null || !message)
			{
				message = BX.message('LANDING_BLOCK_WEBFORM_CONNECT_SUPPORT');
			}

			var formContainer = document.querySelector(this.selector);
			if (formContainer)
			{
				alertHtml = '';
				if (title != '')
				{
					var alertHtml = '<h2 class="u-form-alert-title"><i class="fa fa-exclamation-triangle g-mr-15"></i>' +
						title + '</h2><hr class="u-form-alert-divider">';
				}
				alertHtml += '<p class="u-form-alert-text">' + message + '</p>';

				var messageNode = BX.create('div', {
					props: {className: 'u-form-alert'},
					html: alertHtml
				});
				BX.adjust(formContainer, {children: [messageNode]});
			}
		},

		createFormParams: function ()
		{
			var b24Forms = document.querySelectorAll(this.selector);
			if (b24Forms.length > 0)
			{
				for (var i = 0, c = b24Forms.length; i < c; i++)
				{
					var formCode = BX.data(b24Forms[i], this.dataFormId);
					var formParts = formCode.split('|');
					// find lang param from url if exist
					var formLang = window.location.search.match(new RegExp('user_lang' + '=([^&=]+)'));
					if (formParts.length === 2)
					{
						this.formParams = {
							id: formParts[0],
							lang: formLang ? formLang[1] : BX.message('LANGUAGE_ID'),
							sec: formParts[1],
							type: 'inline' + '_' + this.block.id,
							node: b24Forms[i]
						};

						this.createFormOptions();
					}
				}
			}
		},


		addFormInLoader: function ()
		{
			if (Object.keys(this.formParams).length != 0)
			{
				b24form(this.formParams);
			}
		},


		removeFormFromLoader: function ()
		{
			// remove from loader
			if (!window.Bitrix24FormObject || !window[window.Bitrix24FormObject])
				return;
			if (!window[window.Bitrix24FormObject].forms)
				return;
			window[window.Bitrix24FormObject].forms.forEach(function (form, i)
			{
				if (
					form.id == this.formParams.id &&
					form.node == this.formParams.node &&
					form.sec == this.formParams.sec
				)
				{
					delete window[window.Bitrix24FormObject].forms[i];
				}
			}, this);
		},


		sendFrameMessage: function (params, uniqueLoadId)
		{
			// frame not init yet
			if (!this.iframe)
			{
				return;
			}

			var ie = 0 /*@cc_on + @_jscript_version @*/;
			if (typeof window.postMessage === 'function' && !ie)
			{
				// prepare PARAMS
				if (typeof params != 'object')
				{
					params = {};
				}
				var messageDomain = (this.createFullDomain() + '/').match(/((http|https):\/\/[^\/]+?)\//)[1];
				params.domain = messageDomain;

				// get id default or from params
				if (uniqueLoadId === undefined)
				{
					uniqueLoadId = this.type + '_' + this.id;
				}
				params.uniqueLoadId = uniqueLoadId;

				//init postMessage
				this.iframe.contentWindow.postMessage(
					JSON.stringify(params), messageDomain
				);
			}
		},


		onFormFrameLoad: function (form, uniqueLoadId)
		{
			if (form.id == this.formParams.id && form.sec == this.formParams.sec && form.type == this.formParams.type)
			{
				// save iframe
				this.iframe = form.iframe;
				this.sendFrameMessage({'options': this.formOptions}, uniqueLoadId);
			}
		},


		onFormRemove: function ()
		{
			if (typeof Bitrix24FormLoader != 'undefined')
			{
				Bitrix24FormLoader.unload(this.formParams);
			}
			this.removeFormFromLoader();
		},

		onFormReload: function ()
		{
			// not need reload duplicate form
			if (typeof Bitrix24FormLoader != 'undefined')
			{
				Bitrix24FormLoader.unload(this.formParams);
				Bitrix24FormLoader.preLoad(this.formParams);
			}
		},


		/**
		 * To preserve overreloading when style changes
		 * @returns {*}
		 */
		onFormReloadWithDebounce: function ()
		{
			return BX.debounce(this.onFormReload(), 1000, this);
		},


		// if not exist protocol - add default
		createFullDomain: function ()
		{
			var fullDomain = this.domain;
			if (!(this.domain).match(/(http|https):/))
			{
				fullDomain = 'https://' + this.domain;
			}

			return fullDomain;
		},

		// may create other options for form, but now - only css
		createFormOptions: function ()
		{
			// create options only if need customization
			if (this.isUsingCustomStyle())
			{
				var cssContent = this.createFormOptionsCss();
				var cssFiles = this.createFormOptionsCssFiles();
				if (cssContent.length > 0 || cssFiles.length > 0)
				{
					this.formOptions.css = {
						'content': cssContent,
						'file': cssFiles
					};
				}
			}

			// clear options
			else
			{
				this.formOptions.css = {content: ''};
			}

			// ALWAYS
			// show/hide HEADER
			this.formOptions.css.content = this.matchShowHeader(this.formOptions.css.content);
			// hide bitrix LABEL
			this.formOptions.css.content = this.createHideBitrixLabelCss(this.formOptions.css.content);
			// fixes to form HEIGHT
			this.formOptions.css.content = this.createAdditionalCss(this.formOptions.css.content);
		},


		// dbg need correct font load!!!
		createFormOptionsCssFiles: function ()
		{
			var files = [];
			files.push("https://fonts.googleapis.com/css?family=Ek+Mukta:400,600,700");
			files.push("https://fonts.googleapis.com/css?family=Roboto:300,400,500,700");

			return files;
		},


		createFormOptionsCss: function ()
		{
			if (Object.keys(this.styles).length == 0)
			{
				this.readFormStyles();
			}

			var cssString = "";
			for (var selector in this.selectors)
			{
				var cssStringCurrent = "";
				this.selectors[selector].forEach(function (style)
				{
					if (typeof this.styles[style] != 'undefined')
					{
						for (var styleValue in this.styles[style])
						{
							cssStringCurrent +=
								this.styles[style][styleValue].param + ":" +
								this.styles[style][styleValue].value + ";";
						}
					}
				}, this);

				if (cssStringCurrent.length > 0)
				{
					cssString += selector + "{" + cssStringCurrent + "}";
				}
			}

			cssString += this.fixHeightCssString;

			return cssString;
		},


		// hide "zaryazheno Bitriks 24"
		createHideBitrixLabelCss: function (string)
		{
			string = (typeof string == 'undefined') ? '' : string;

			return string + this.hideBitrixLogoString;
		},

		createAdditionalCss: function (string)
		{
			string = (typeof string == 'undefined') ? '' : string;

			return string + this.additionalCssString;
		},

		isUsingCustomStyle: function ()
		{
			var node = BX.findChild(this.block, {'attribute': 'data-' + this.dataAttributeUseStyle}, true, false);
			if (
				node
				&& typeof BX.data(node, this.dataAttributeUseStyle) != 'undefined'
				&& BX.data(node, this.dataAttributeUseStyle) == 'N'
			)
			{
				return false;
			}
			else
			{
				return true;
			}
		},

		matchShowHeader: function (string)
		{
			string = (typeof string == 'undefined') ? '' : string;

			var node = BX.findChild(this.block, {'attribute': 'data-' + this.dataAttributeShowHeader}, true, false);
			if (
				node
				&& typeof BX.data(node, this.dataAttributeShowHeader) != 'undefined'
				&& BX.data(node, this.dataAttributeShowHeader) == 'N'
			)
			{
				string += this.hideHeaderString;
			}
			else
			{
				string = string.replace(this.hideHeaderString, "");
			}

			return string;
		},

		/**
		 * Get computed style from one node by params. Save in styles array
		 * @param node
		 * @param style
		 */
		readNodeStyles: function (node, style)
		{
			this.styleParams[style].params.forEach(BX.delegate(function (param)
			{
				var value = BX.style(node, param);
				if (value)
				{
					if (typeof this.styles[style] == 'undefined')
					{
						this.styles[style] = {};
					}
					this.styles[style][param] = {param: param, value: value};
				}
			}), this);
		},


		readFormStyles: function ()
		{
			// dbg: need bx dom write
			// BX.DOM.read(BX.delegate(function ()
			// {
			for (var style in this.styleParams)
			{
				var node = BX.findChild(this.block, {'attribute': this.dataAttributePrefix + style}, true, false);
				if (node)
				{
					this.readNodeStyles(node, style);
				}
			}
			// }), this);
		},

		/**
		 * return false if styles was changed, or false if not
		 * @param node
		 * @returns {boolean}
		 */
		readFormStylesFromNode: function (node)
		{
			// check if node have style attrs
			var change = false;
			var attrs = node.attributes;
			for (var i = 0; i < attrs.length; i++)
			{
				var attr = attrs[i].name.replace(this.dataAttributePrefix, '');
				if (typeof this.styleParams[attr] !== 'undefined')
				{
					change = true;
					this.readNodeStyles(node, attr);
				}
			}

			return change;
		}
	}
})();