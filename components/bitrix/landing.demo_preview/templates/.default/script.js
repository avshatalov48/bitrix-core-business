;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var proxy = BX.Landing.Utils.proxy;
	var bind = BX.Landing.Utils.bind;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var isNumber = BX.Landing.Utils.isNumber;
	var style = BX.Landing.Utils.style;
	var data = BX.Landing.Utils.data;
	var addQueryParams = BX.Landing.Utils.addQueryParams;

	/**
	 * Implements interface for works with template preview
	 * @constructor
	 */
	BX.Landing.TemplatePreview = function(params)
	{
		this.previewContainer = document.querySelector(".landing-template-preview");
		this.closeButton = document.querySelector(".landing-template-preview-close");
		this.createButton = document.querySelector(".landing-template-preview-create");
		this.createByImportButton = document.querySelector(".landing-template-preview-create-by-import");
		this.title = document.querySelector(".landing-template-preview-input-title");
		this.description = document.querySelector(".landing-template-preview-input-description");
		this.themesPalette = document.querySelector(".landing-template-preview-themes");
		this.themesSiteColorNode = document.querySelector(".landing-template-preview-site-color");
		this.imageContainer = document.querySelector(".preview-desktop-body-image");
		this.loaderContainer = document.querySelector(".preview-desktop-body-loader-container");
		this.previewFrame = document.querySelector(".preview-desktop-body-preview-frame");
		this.baseUrlNode = document.querySelector(".landing-template-preview-base-url");
		this.siteGroupPalette = document.querySelector(".landing-template-preview-site-group");
		this.loader = new BX.Loader({});
		this.messages = params.messages || {};
		this.loaderText = null;
		this.progressBar = null;
		this.IsLoadedFrame = false;
		this.baseUrl = '';
		this.color = null;
		this.ajaxUrl = '';
		this.ajaxParams = {};

		this.createStore = BX.type.isBoolean(params.createStore)
						? params.createStore
						: false;
		this.createMainpage = BX.type.isBoolean(params.createMainpage)
			? params.createMainpage
			: false;
		this.isMainpageExists = BX.type.isBoolean(params.isMainpageExists)
			? params.isMainpageExists
			: false;

		this.disableStoreRedirect = BX.type.isBoolean(params.disableStoreRedirect)
						? params.disableStoreRedirect
						: false;
		this.disableClickHandler = BX.type.isBoolean(params.disableClickHandler)
						? params.disableClickHandler
						: false;
		this.adminSection = BX.type.isBoolean(params.adminSection)
						? params.adminSection
						: null;
		this.zipInstallPath = params.zipInstallPath
						? params.zipInstallPath
						: null;
		this.appCode = params.appCode || '';
		this.siteId = params.siteId || 0;
		this.langId = BX.type.isString(params.langId)
						? params.langId
						: '';
		this.folderId = params.folderId || 0;
		this.replaceLid = params.replaceLid || 0;
		this.isCrmForm = (params.isCrmForm || 'N') === 'Y';
		this.context_section = params.context_section || null;
		this.context_element = params.context_element || null;
		this.urlPreview = params.urlPreview || '';

		this.onCreateButtonClick = proxy(this.onCreateButtonClick, this);
		this.onCancelButtonClick = proxy(this.onCancelButtonClick, this);
		this.onFrameLoad = proxy(this.onFrameLoad, this);

		this.init();

		return this;
	};

	/**
	 * Gets instance of BX.Landing.TemplatePreview
	 * @return {BX.Landing.TemplatePreview}
	 */
	BX.Landing.TemplatePreview.getInstance = function(params)
	{
		return (
			BX.Landing.TemplatePreview.instance ||
			(BX.Landing.TemplatePreview.instance = new BX.Landing.TemplatePreview(params))
		);
	};

	BX.Landing.TemplatePreview.prototype = {
		/**
		 * Initializes template preview elements
		 */
		init: function ()
		{
			bind(this.previewFrame, "load", this.onFrameLoad);
			bind(this.closeButton, "click", this.onCancelButtonClick);

			if (!this.disableClickHandler)
			{
				bind(this.createButton, "click", this.onCreateButtonClick);
			}

			this.setBaseUrl();
			this.setDefaultColor();
			this.showPreview();
			this.buildHeader();

			if (BX.SidePanel.Instance.isReload === true)
			{
				this.createButton.click();
			}
		},

		setBaseUrl: function (url)
		{
			if (url === undefined)
			{
				this.baseUrl = data(this.baseUrlNode, "data-base-url");
			}
			else
			{
				this.baseUrl = url;
			}
		},

		setColor: function (theme)
		{
			if (theme !== undefined)
			{
				this.color = theme;
			}
		},

		setDefaultColor: function ()
		{
			if (this.getActiveColorNode())
			{
				this.color = data(this.getActiveColorNode(), "data-value");
			}
		},

		getColor: function ()
		{
			return this.color;
		},

		createPreviewUrl: function ()
		{
			var queryParams = {};
			if (!this.baseUrl)
			{
				this.setBaseUrl();
			}

			if (this.getColor())
			{
				queryParams = {color: this.getColor()};
			}

			return addQueryParams(this.baseUrl, queryParams);
		},

		onFrameLoad: function ()
		{
			if (this.createStore)
			{
				new BX.Landing.SaveBtn(this.createButton);
			}
			this.IsLoadedFrame = true;
		},

		/**
		 *
		 * @returns {HTMLElement|null}
		 */
		getActiveColorNode: function ()
		{
			var active = this.themesPalette.querySelector(".active");
			if (!active && this.themesSiteColorNode)
			{
				active = this.themesSiteColorNode.querySelector(".active");
			}

			return active;
		},

		/**
		 *
		 * @returns {HTMLElement}
		 */
		getActiveSiteGroupItem: function ()
		{
			return this.siteGroupPalette.querySelector(".active");
		},

		/**
		 * Shows preview
		 * @param {?string} src
		 * @return {Promise<T>}
		 */
		showPreview: function (src)
		{
			if (src === undefined)
			{
				src = this.createPreviewUrl();
			}

			return this.showLoader()
				.then(this.createFrameIfNeeded())
				.then(this.loadPreview(src))
				.then(this.hideLoader());
		},

		buildHeader: function ()
		{
			var qrContainer = BX.create('div');
			new QRCode(qrContainer, {
				text: this.urlPreview,
				width: 156,
				height: 156,
				colorLight: "transparent"
			});

			this.showPopupButton = document.querySelector(".mobile-view");
			if (this.showPopupButton)
			{
				var popupPreview = BX.PopupWindowManager.create(
					'landing-popup-preview',
					this.showPopupButton,
					{
						content: BX.create('div', {
							props: {className: 'landing-popup-preview-content'},
							children: [
								BX.create('div', {
									props: {className: 'landing-popup-preview-title'},
									text: this.messages.LANDING_TPL_POPUP_TITLE
								}),
								BX.create('div', {
									props: {className: 'landing-popup-preview-qr'},
									children: [
										qrContainer
									],
								}),
								BX.create('div', {
									props: {className: 'landing-popup-preview-text'},
									text: this.messages.LANDING_TPL_POPUP_TEXT
								}),
							]
						}),
						closeIcon: true,
						closeByEsc: true,
						noAllPaddings: true,
						autoHide: true,
						animation: 'fading-slide',
						angle: {
							position: "top",
							offset: 75
						},
						minWidth: 375,
						maxWidth: 375,
						contentBackground: "transparent",
					}
				);

				this.showPopupButton.addEventListener(
					'click',
					function ()
					{
						popupPreview.toggle();
					});
			}
		},

		/**
		 * Creates frame if needed
		 * @return {Function}
		 */
		createFrameIfNeeded: function ()
		{
			return function ()
			{
				return new Promise(function (resolve)
				{
					var createFrame = function ()
					{
						if (!this.previewFrame)
						{
							this.previewFrame = BX.create('iframe', {
								props: {
									className: 'preview-desktop-body-preview-frame'
								}
							});

							this.imageContainer.appendChild(this.previewFrame);
							bind(this.previewFrame, "load", this.onFrameLoad);
						}

						if (!this.previewFrame.style.width)
						{
							let previewFrameHeaderHeight = '69';
							if (this.previewContainer)
							{
								const mainpagePreview = this.previewContainer.querySelector('.--main-page');
								if (mainpagePreview)
								{
									previewFrameHeaderHeight = '132';
								}
							}
							void style(this.previewFrame, {
								"width": "100%",
								"height": `calc(100vh - ${previewFrameHeaderHeight}px)`,
								"border": "none"
							});
						}

						resolve(this.previewFrame);
					}.bind(this);

					if (document.readyState !== "complete")
					{
						BX.bind(window, 'load', createFrame.bind(this));
					}
					else
					{
						createFrame();
					}
				}.bind(this));
			}.bind(this)
		},

		/**
		 * Loads template preview
		 * @param {string} src
		 * @return {Function}
		 */
		loadPreview: function (src)
		{
			return function ()
			{
				return new Promise(function (resolve)
				{
					if (this.previewFrame.src !== src)
					{
						this.previewFrame.src = src;
						this.previewFrame.onload = function ()
						{
							resolve(this.previewFrame);
						}.bind(this);
						return;
					}

					resolve(this.previewFrame);
				}.bind(this));
			}.bind(this)
		},

		/**
		 * Shows preview loader
		 * @return {Promise}
		 */
		showLoader: function ()
		{
			return new Promise(function (resolve)
			{
				void this.loader.show(this.loaderContainer);
				addClass(this.imageContainer, "landing-template-preview-overlay");
				resolve();
			}.bind(this));
		},

		/**
		 * Hides loader
		 * @return {Function}
		 */
		hideLoader: function ()
		{
			return function (iframe)
			{
				return new Promise(function (resolve)
				{
					void this.loader.hide();
					removeClass(this.imageContainer, "landing-template-preview-overlay");
					resolve(iframe);
				}.bind(this));
			}.bind(this);
		},

		/**
		 * Creates delay
		 * @param delay
		 * @return {Function}
		 */
		delay: function (delay)
		{
			delay = isNumber(delay) ? delay : 0;

			return function (image)
			{
				return new Promise(function (resolve)
				{
					setTimeout(resolve.bind(null, image), delay);
				});
			}
		},

		/**
		 * Gets value
		 * @return {Object}
		 */
		getValue: function ()
		{
			var result = {};

			if (this.getActiveColorNode())
			{
				if (this.themesSiteColorNode && this.getActiveColorNode().parentElement === this.themesSiteColorNode)
				{
					result[this.themesSiteColorNode.dataset.name] = this.getActiveColorNode().dataset.value;
				}

				if (this.siteGroupPalette)
				{
					result[this.siteGroupPalette.dataset.name] = this.getActiveSiteGroupItem().dataset.value;
				}
				result[this.themesPalette.dataset.name] = this.getActiveColorNode().dataset.value;
			}
			result[this.title.dataset.name] = this.title.value.replaceAll('&', '').replaceAll('?', '');
			result[this.description.dataset.name] = this.description.value;

			return result;
		},

		/**
		 * Makes create url
		 * @return {string}
		 */
		getCreateUrl: function ()
		{
			return addQueryParams(this.createButton.getAttribute("href"), this.getValue());
		},

		/**
		 * Handles click event on close button
		 * @param {MouseEvent} event
		 */
		onCancelButtonClick: function (event)
		{
			event.preventDefault();
			top.BX.SidePanel.Instance.close();
		},

		/**
		 * Handles click event on create button
		 * @param {MouseEvent} event
		 */
		onCreateButtonClick: function (event)
		{
			event.preventDefault();

			if (BX.Dom.hasClass(this.createButton.parentNode, 'needed-market-subscription'))
			{
				top.BX.UI.InfoHelper.show('limit_subscription_market_templates');
				new Promise(resolve => {
					const timerId = setInterval(() => {
						if (BX.Dom.hasClass(this.createButton, 'ui-btn-clock'))
			 			{
							clearInterval(timerId);
							resolve();
						}
					}, 500);
				})
				.then(() => {
					BX.Dom.removeClass(this.createButton, 'ui-btn-clock');
					BX.Dom.attr(this.createButton, 'style', '');
				});

				return;
			}

			// Analytic
			const metrika = new BX.Landing.Metrika(true);
			metrika.sendData(this.getMetrikaParams('attempt'));

			if (this.isStore() && this.IsLoadedFrame)
			{
				this.loaderText = BX.create("div", {
					props: {className: "landing-template-preview-loader-text"},
					text: this.messages.LANDING_LOADER_WAIT
				});

				this.progressBar = new BX.UI.ProgressBar({
					column: true
				});

				this.progressBar.getContainer().classList.add("ui-progressbar-landing-preview");

				this.loaderContainer.appendChild(this.loaderText);
				this.loaderContainer.appendChild(this.progressBar.getContainer());
			}

			if (this.isStore())
			{
				if (this.IsLoadedFrame)
				{
					this.showLoader().then(() =>
					{
						this.initCatalogParams();
						this.createCatalog();
					});
				}
			}
			else if (this.zipInstallPath)
			{
				if (
					this.isMainpage()
					&& this.isMainpageExists
				)
				{
					let isClickOnButtonOk = false;
					BX.Runtime.loadExtension('ui.dialogs.messagebox').then(() => {
						const messageBox = new BX.UI.Dialogs.MessageBox({
							message: this.messages.LANDING_PREVIEW_MAINPAGE_MESSAGE,
							title: this.messages.LANDING_PREVIEW_MAINPAGE_TITLE,
							buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
							okCaption: this.messages.LANDING_PREVIEW_MAINPAGE_BUTTON_OK_TEXT,
							cancelCaption: this.messages.LANDING_PREVIEW_MAINPAGE_BUTTON_CANCEL_TEXT,
							onOk: () => {
								isClickOnButtonOk = true;
								this.finalRedirectAjax(this.getCreateUrl());

								return true;
							},
							onCancel: () => {
								BX.Dom.removeClass(this.createButton, 'ui-btn-clock');
								BX.Dom.attr(this.createButton, 'style', '');

								return true;
							},
							popupOptions: {
								bindElement: BX('popup-window-titlebar-close-icon'),
								offsetLeft: 20,
								closeIcon: true,
								events: {
									onPopupClose: () => {
										if (!isClickOnButtonOk)
										{
											BX.Dom.removeClass(this.createButton, 'ui-btn-clock');
											BX.Dom.attr(this.createButton, 'style', '');
										}

										return true;
									},
								},
							},
						});
						messageBox.show();
						if (messageBox.popupWindow && messageBox.popupWindow.popupContainer)
						{
							messageBox.popupWindow.popupContainer.classList.add('landing-template-preview-create-popup');
						}
					});
				}
				else
				{
					this.finalRedirectAjax(this.getCreateUrl());
				}
			}
			else
			{
				this.showLoader()
					.then(this.delay(200))
					.then(() => {
						this.finalRedirectAjax(this.getCreateUrl());
					});
			}
		},

		/**
		 * @param {'success'|'attempt'} status
		 * @return {{
		 *  [category]: string,
		 * 	[event]: string,
		 * 	[c_section]: string|null,
		 * 	[c_sub_section]: string|null,
		 *  [c_element]: string|null,
		 *  [params]: {object},
		 * }}
		 */
		getMetrikaParams: function (status)
		{
			let category = 'landings';
			if (this.isCrmForm)
			{
				category = 'crm_forms';
			}
			if (this.isMainpage())
			{
				category = 'vibe';
			}
			if (this.isStore())
			{
				category = 'stores';
			}
			if (this.isMainpage())
			{
				category = 'vibe';
			}

			const metrikaParams = {
				category,
				event: this.isMainpage() ? 'create_template' : 'create_page_template',
				params: {
					appCode: this.appCode,
				},
				status,
			};

			if (this.replaceLid > 0)
			{
				metrikaParams.event = 'replace_template';
			}
			else if (this.siteId !== 0)
			{
				metrikaParams.event = 'create_site_template';
			}

			if (this.context_section)
			{
				metrikaParams.c_section = this.context_section;
			}
			if (this.context_element)
			{
				metrikaParams.c_element = this.context_element;
			}

			return metrikaParams;
		},

		/**
		 * Init params for create catalog.
		 */
		initCatalogParams: function()
		{
			if (this.createButton.hasAttribute('data-href'))
			{
				this.ajaxUrl = this.createButton.getAttribute('data-href');
			}
			this.ajaxParams = this.getValue();
			this.ajaxParams['start'] = 'Y';
			this.ajaxParams['showcaseId'] = 'clothes';
		},

		/**
		 * Base actions for create catalog.
		 */
		createCatalog: function()
		{
			if (this.ajaxUrl === '')
			{
				this.hideLoader();
				return;
			}
			BX.ajax({
				'method': 'POST',
				'dataType': 'json',
				'url': this.ajaxUrl,
				'data':  BX.ajax.prepareData(this.ajaxParams),
				'onsuccess': BX.proxy(this.createCatalogResult, this)
			})
		},

		/**
		 * Result step in create catalog.
		 * @param data
		 */
		createCatalogResult: function(data)
		{
			if (data.status === 'continue')
			{
				this.ajaxParams['start'] = 'N';
				this.progressBar.update(data.progress);
				this.progressBar.setTextAfter(data.message);
				this.createCatalog();
			}
			else
			{
				this.finalRedirectAjax(data.url);
			}
		},

		/**
		 * Redirect to final URL or submit it by ajax and close slider.
		 * @param url
		 */
		finalRedirectAjax: function(url)
		{
			if (this.zipInstallPath)
			{
				let add = [];
				const value = this.getValue();
				for (let name in value)
				{
					add['additional[' + name + ']'] = value[name];
				}

				add['additional[siteId]'] = this.siteId;
				add['additional[folderId]'] = this.folderId;
				if (this.replaceLid > 0)
				{
					add['additional[replaceLid]'] = this.replaceLid;
				}

				const metrikaParams = this.getMetrikaParams('success');

				add['additional[st_category]'] = metrikaParams.category;
				add['additional[st_event]'] = metrikaParams.event;
				if (metrikaParams.c_section)
				{
					add['additional[st_section]'] = metrikaParams.c_section;
				}
				if (metrikaParams.c_element)
				{
					add['additional[st_element]'] = metrikaParams.c_element;
				}
				add['additional[app_code]'] = this.appCode;

				// 'form' is for analytic
				add['from'] = this.createParamsStrFromUrl(url);

				if (this.adminSection && this.langId !== '')
				{
					add['lang'] = this.langId;
				}

				if (typeof top.BX.SidePanel !== 'undefined')
				{
					const popupImport = document.querySelector(".landing-popup-import");
					const popupImportLoaderContainer = document.querySelector(".landing-popup-import-loader");
					const previewFrame = document.querySelector(".preview-left");
					if (previewFrame && popupImportLoaderContainer)
					{
						this.loader.show(popupImportLoaderContainer);
						BX.Dom.addClass(previewFrame, 'landing-import-start');
					}
					add['inSlider'] = 'N';
					if (this.siteId !== 0)
					{
						add['createType'] = 'PAGE';
					}
					let interval;
					BX.ajax({
						method: 'POST',
						dataType: 'html',
						url: addQueryParams(this.zipInstallPath, add),
						onsuccess: data => {
							const promise = new Promise((resolve, reject) => {
								const result = BX.Dom.create('div', {html: data});
								BX.Dom.style(result, 'display', 'none');
								popupImport.append(result);
								let restImportElement;
								let count = 0;
								interval = setInterval(
									() => {
										if (count > 100)
										{
											reject(new Error('Time is up'));
										}
										restImportElement = result.querySelector('.rest-configuration-wrapper');
										if (restImportElement !== null)
										{
											resolve(restImportElement);
										}
										count++;
									},
									300
								);
							});
							promise.then(
								result => {
									clearInterval(interval);
									if (BX.Dom.hasClass(result, 'rest-configuration-wrapper'))
									{
										const importTitle = result.querySelector('.rest-configuration-title');
										const importIconContainer = result.querySelector('.rest-configuration-start-icon-main-container');
										if (importTitle && importIconContainer)
										{
											BX.Dom.remove(importTitle);
											BX.Dom.insertBefore(importTitle, importIconContainer.nextSibling);
										}
										this.loader.hide();
										BX.Dom.append(result, popupImport);
										BX.Dom.style(popupImportLoaderContainer, 'display', 'none');
									}
								},
								error => {
									clearInterval(interval);
									this.addRepeatCreateButton();
								}
							);
						}
					});
				}
			}
			else if (this.disableStoreRedirect)
			{
				BX.ajax({
					'method': 'POST',
					'dataType': 'html',
					'url': url,
					'onsuccess': function()
					{
						if (typeof top.BX.SidePanel !== 'undefined')
						{
							setTimeout(function() {
								top.BX.onCustomEvent('Landing:onDemoCreateStart');
								top.BX.SidePanel.Instance.close();
							}, 100);
						}
					}
				});
			}
			else
			{
				window.location = url;
			}
		},

		addRepeatCreateButton: function()
		{
			const popupImportError = document.querySelector(".landing-popup-import-repeat");
			if (popupImportError)
			{
				BX.Dom.removeClass(popupImportError, 'hide');
			}
			const repeatButton = document.querySelector(".landing-popup-import-repeat-button");
			if (repeatButton)
			{
				bind(repeatButton, "click", this.onRepeatButtonClick);
			}
		},

		onRepeatButtonClick: function()
		{
			const popupImportError = document.querySelector(".landing-popup-import-repeat");
			if (popupImportError)
			{
				BX.Dom.addClass(popupImportError, 'hide');
			}
			if (this.createButton)
			{
				this.createButton.click();
			}
		},

		isStore: function()
		{
			return this.createStore;
		},

		isMainpage: function()
		{
			return this.createMainpage;
		},

		createParamsStrFromUrl(url)
		{
			var appCodeMatch = url.match(/&app_code=[^&]+/i);
			var appCode = '';
			if (appCodeMatch !== null)
			{
				appCode = appCodeMatch[0].substr(10);
			}
			var titleMatch = url.match(/&title=[^&]+/iu);
			var title = '';
			if (titleMatch !== null)
			{
				title = titleMatch[0].substr(7);
			}
			var hrefUrlMatch = url.match(/&preview_id=[^&]+/i);
			var hrefId = '';
			if (hrefUrlMatch !== null)
			{
				hrefId = hrefUrlMatch[0].substr(12);
			}

			return '|' + appCode + '|' + title + '|' + hrefId;
		}
	};
})();