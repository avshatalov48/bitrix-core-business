/**
 * @bxjs_lang_path template.php
 */

(function() {

	'use strict';

	BX.namespace('BX.Landing.Component.View');

	/**
	 * Base component JS.
	 * @constructor
	 */
	BX.Landing.Component.View = function(options)
	{
	};
	BX.Landing.Component.View.instance = null;
	BX.Landing.Component.View.getInstance = function()
	{
		return BX.Landing.Component.View.instance;
	};
	BX.Landing.Component.View.create = function(options, topInit)
	{
		options.topInit = topInit === true;
		BX.Landing.Component.View.instance = new BX.Landing.Component.View(
			options
		);
		BX.Landing.Component.View.instance.setNewOptions(options);
		BX.Landing.Component.View.instance.init();

		return BX.Landing.Component.View.instance;
	};
	BX.Landing.Component.View.loadEditor = function()
	{
		var component = new BX.Landing.Component.View({});
		var editorWindow = BX.Landing.PageObject.getEditorWindow();
		var rootWindow = BX.Landing.PageObject.getRootWindow();

		component.loadEditor();
		component.buildTop();

		editorWindow.addEventListener('load', function() {
			BX.Landing.UI.Panel.StylePanel.getInstance();
			rootWindow.BX.Landing.UI.Panel.Top.resetInstance();
			BX.Landing.UI.Panel.Top.getInstance();

			editorWindow.BX.onCustomEvent('Landing.Editor:load')
		});
	};

	BX.Landing.Component.View.prototype =
	{
		/**
		 * Set new options array.
		 * @param options Some additional options.
		 */
		setNewOptions: function(options)
		{
			this.type = options.type || '';
			this.title = options.title || '';
			this.url = options.url || '';
			this.isMainpage = options.type === 'MAINPAGE';
			this.isFormEditor = options.specialType === 'crm_forms';
			this.topInit = options.topInit || false;
			this.active = options.active || false;
			this.draftMode = options.draftMode || false;
			this.id = options.id || 0;
			this.siteId = options.siteId || 0;
			this.siteTitle = options.siteTitle || '';
			this.storeEnabled = options.storeEnabled || false;
			this.fullPublication = options.fullPublication || false;
			this.urls = options.urls || {};
			this.rights = options.rights || {};
			this.helperFrameOpenUrl = options.helperFrameOpenUrl || null;
			this.helpCodes = options.helpCodes || {};
			this.sliderConditions = options.sliderConditions || [];
			this.sliderFullConditions = options.sliderFullConditions || [];
			top.window.autoPublicationEnabled = !!options.autoPublicationEnabled;
			if (!this.rights.public)
			{
				top.window.autoPublicationEnabled = false;
			}
			if (this.helperFrameOpenUrl)
			{
				BX.Helper.init({
					frameOpenUrl : this.helperFrameOpenUrl,
					langId: BX.message('LANGUAGE_ID')
				});
			}
		},

		/**
		 * Some init preparing.
		 */
		init: function()
		{
			var viewInstance = BX.Landing.Component.View.getInstance();

			// for open app pages in slider
			if (
				typeof BX.rest !== 'undefined' &&
				typeof BX.rest.Marketplace !== 'undefined'
			)
			{
				BX.rest.Marketplace.bindPageAnchors({});
			}
			// event on app install
			BX.addCustomEvent(
				window,
				'Rest:AppLayout:ApplicationInstall',
				function(installed)
				{
					if (installed) {}
				}
			);
			// event on settings slider close
			if (this.topInit)
			{
				BX.addCustomEvent('SidePanel.Slider:onMessage',
					function(event)
					{
						if (event.getEventId() === 'landingEditClose')
						{
							setTimeout(function()
							{
								window.location.reload();
							}, 1000);
						}
					}
				);
			}

			if (!this.topInit)
			{
				// on required links click
				BX.addCustomEvent('BX.Landing.Block:init', function(event)
				{
					if (event.data.requiredUserActionIsShown)
					{
						BX.bind(event.data.button, 'click', function()
						{
							viewInstance.onRequiredLinkClick(this);
						});
					}
				});
				var requiredLinks = [].slice.call(
					document.querySelectorAll('.landing-required-link')
				);
				requiredLinks.forEach(function(element, index)
				{
					BX.bind(element, 'click', function()
					{
						viewInstance.onRequiredLinkClick(this);
					});
				});

				// highliht expired blocks
				var blocks = BX.Landing.PageObject.getBlocks();
				for (var i = 0, c = blocks.length; i < c; i++)
				{
					if (!blocks[i].isAllowedByTariff())
					{
						var overlay = BX.create('div', {
							props: {className: 'landing-block-expired-overlay'},
						});
						blocks[i].node.appendChild(overlay);
					}
				}
			}
			// force top and style panel initialization
			if (this.topInit)
			{
				var editorWindow = BX.Landing.PageObject.getEditorWindow();
				var rootWindow = BX.Landing.PageObject.getRootWindow();

				editorWindow.addEventListener('load', function() {
					BX.Landing.UI.Panel.StylePanel.getInstance();
					rootWindow.BX.Landing.UI.Panel.Top.resetInstance()
					BX.Landing.UI.Panel.Top.getInstance();
				});
			}
			// build top panel
			if (this.topInit)
			{
				this.buildTop();
				this.initSliders();
				this.loadEditor();
				this.hideEditorsPanelHandlers();
			}
		},

		/**
		 * Init sliders by conditions.
		 */
		initSliders: function()
		{
			if (typeof BX.SidePanel === 'undefined')
			{
				return;
			}

			const conditions = [];
			for (let i = 0, c = this.sliderConditions.length; i < c; i++)
			{
				conditions.push(this.sliderConditions[i]);
			}
			const conditionsFull = this.sliderFullConditions;

			if (conditions.length <= 0 && conditionsFull.length <= 0)
			{
				return;
			}

			const sliderOptions = top.BX.clone({
				rules: [
					{
						condition: conditions,
						stopParameters: [
							'action',
							'fields%5Bdelete%5D',
							'nav'
						],
						options: {
							allowChangeHistory: false
						}
					}
				]
			});
			BX.SidePanel.Instance.bindAnchors(sliderOptions);

			const sliderFullOptions = top.BX.clone({
				rules: [
					{
						condition: conditionsFull,
						options: {
							allowChangeHistory: false,
							customLeftBoundary: 0,
							cacheable: false,
						}
					}
				]
			});
			BX.SidePanel.Instance.bindAnchors(sliderFullOptions);

			const topPanelLogoLink = document.querySelector('.landing-ui-panel-top-logo-link');
			if (topPanelLogoLink && BX.Dom.hasClass(topPanelLogoLink, '--mainpage-link'))
			{
				BX.Event.bind(topPanelLogoLink, 'click', () => {
					event.preventDefault();
					event.stopPropagation();
					BX.SidePanel.Instance.close();
				});
			}
		},

		/**
		 * Load main editor.
		 */
		loadEditor: function()
		{
			var loaderContainer = document.querySelector('.landing-editor-loader-container');
			var userActionContainer = document.querySelector('.landing-editor-required-user-action');

			if (loaderContainer)
			{
				var loader = new BX.Loader({offset: {top: '-70px'}});
				loader.show(loaderContainer);

				BX.Landing.PageObject.getInstance().view().then(function(iframe) {
					BX.bindOnce(iframe, 'load', function() {
						var action = BX.Landing.Main.getInstance().options.requiredUserAction;

						if (BX.Landing.Utils.isPlainObject(action) && !BX.Landing.Utils.isEmpty(action))
						{
							if (action.header)
							{
								userActionContainer.querySelector('h3').innerText = action.header;
							}

							if (action.description)
							{
								userActionContainer.querySelector('p').innerText = action.description;
							}

							if (action.href)
							{
								userActionContainer.querySelector('a').setAttribute('href', action.href);
							}

							if (action.text)
							{
								userActionContainer.querySelector('a').innerText = action.text;
							}

							userActionContainer.classList.add('landing-ui-user-action-show');

							document.querySelector('.landing-ui-panel-top-history').classList.add('landing-ui-disabled');
							document.querySelector('.landing-ui-panel-top-devices').classList.add('landing-ui-disabled');
						}
						else
						{
							iframe.classList.add('landing-ui-view-show');
						}

						setTimeout(function() {
							BX.Dom.addClass(loaderContainer, 'landing-ui-hide');
							setTimeout(function() {
								BX.remove(loaderContainer);
								BX.remove(userActionContainer);
							}, 200);
						}, 300);
					});
				});
			}
		},

		/**
		 * Hide panel by click on top panel.
		 */
		hideEditorsPanelHandlers: function()
		{
			BX.Landing.PageObject.getInstance().top().then(function(panel) {
				panel.addEventListener('click', function() {
					BX.Landing.PageObject.getInstance().view()
						.then(function(iframe) {
							if (iframe.contentWindow.BX)
							{
								if (iframe.contentWindow.BX.Landing.Node.Text.currentNode)
								{
									iframe.contentWindow.BX.Landing.Node.Text.currentNode.disableEdit();
								}

								if (iframe.contentWindow.BX.Landing.UI.Field.BaseField.currentField)
								{
									iframe.contentWindow.BX.Landing.UI.Field.BaseField.currentField.disableEdit();
								}

								iframe.contentWindow.BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
							}

						})
				});
			});
		},

		/**
		 * Handler function for required link.
		 * @param element Node element (required link).
		 */
		onRequiredLinkClick: function(element)
		{
			const href = element.getAttribute('href');

			if (href.substr(0, 1) !== '#')
			{
				window.open(href, '_top');
			}

			let linkTpl = href.substr(1);
			const urlParams = {};
			let linkTplAnchor = '';

			if (linkTpl.indexOf('@') > 0)
			{
				linkTplAnchor = linkTpl.split('@')[1];
				linkTpl = linkTpl.split('@')[0];
			}
			linkTpl = linkTpl.toUpperCase();

			const pageUrl = 'PAGE_URL_LANDING_SETTINGS';
			urlParams.PAGE = linkTpl.replace('PAGE_URL_', '');

			if (
				typeof landingParams[pageUrl] !== 'undefined' &&
				typeof BX.SidePanel !== 'undefined'
			)
			{
				BX.SidePanel.Instance.open(
					BX.util.add_url_param(
						landingParams[pageUrl],
						urlParams
					) +
					(linkTplAnchor ? '#' + linkTplAnchor : ''),
					{
						allowChangeHistory: false
					}
				);
			}
		},

		/**
		 * Change top panel.
		 * @param options Some additional options.
		 */
		buildTop: function(options)
		{
			options = options || {};
			this.urls = this.urls || {};

			// direct id for some urls
			for (var key in this.urls)
			{
				var link = BX('landing-urls-' + key);
				if (link)
				{
					link.setAttribute('href', this.urls[key]);
				}
			}

			const publicationBtn = BX('landing-popup-publication-btn');
			if (publicationBtn && publicationBtn.classList.contains('landing-ui-panel-top-pub-btn-enable'))
			{
				var oPopupPublication = null;
				var oPopupError = null;
				publicationBtn.addEventListener(
					'click',
					function()
					{
						var landingId = this.id,
							popupPublicationContent;

						const errorMsgBlock = this.getErrorMessageBlock();

						if (errorMsgBlock)
						{
							const errorCode = BX('landing-popup-publication-error-area').getAttribute('data-error');
							const clickHandler = this.getErrorClickHandler(errorCode);

							popupPublicationContent = BX.create('div', {
								props: { className: 'landing-popup-publication-content 1' },
								children: [
									errorMsgBlock,
									BX.create('form', {
										props: { className: 'landing-popup-publication-content-block-gray landing-popup-publication-content-center landing-popup-publication-content-block-gray-disabled' },
										attrs: {
											target: '_blank',
											method: 'post',
											action: this.urls['publicationGlobal']
										},
										children: [
											BX.create('input', {
												attrs: {
													type: 'hidden',
													name: 'sessid',
													value: BX.message('bitrix_sessid')
												}
											}),
											BX.create('div', {
												props: {
													className: "landing-popup-publication-content-autopub-btn-container"
												},
												children: [
													BX.create('button', {
														props: {
															className: "landing-popup-publication-content-autopub-btn ui-btn ui-btn-round ui-btn-no-caps ui-btn-shadow ui-btn-icon-lock ui-btn-light landing-popup-btn-disabled"
														},
														attrs: {
															type: 'submit',
															disabled: true,
														},
														text: BX.message('LANDING_PUBLICATION_SUBMIT')
													}),
													BX.create('div', {
														props: {
															className: "landing-popup-publication-content-autopub-btn-clicker"
														},
														events:
															clickHandler
																? {click: clickHandler}
																: null
														,
													})
												],
											})
										]
									}),
								]
							});
						}
						else
						{
							var popupPublicationContentHint = BX.create('div', {
								props: { className: 'landing-popup-publication-content-hint' },
							});

							popupPublicationContent = BX.create('div', {
								props: { className: 'landing-popup-publication-content 2' },
								children: [
									BX.create('div', {
										props: { className: 'landing-popup-publication-content-block' },
										children: [
											BX.create('label', {
												props: { className: 'landing-popup-publication-content-autopub' },
												children: [
													BX.create('span', {
														props: { className: (top.window.autoPublicationEnabled) ? "landing-popup-publication-content-autopub-icon landing-ui-panel-top-pub-btn-auto" : "landing-popup-publication-content-autopub-icon" },
														html: '<svg class="landing-ui-panel-top-pub-btn-icon" width="25" height="25" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">' +
															'<path class="landing-ui-panel-top-pub-btn-icon-defs-cloud" fill="#C6CDD3" d="M18.5075 18.8896H10.4177C10.3485 18.8896 10.2799 18.887 10.2119 18.882C8.38363 18.8398 6.91434 17.3271 6.91434 15.4671C6.91487 14.5606 7.27128 13.6914 7.90517 13.0507C8.2301 12.7223 8.61429 12.4678 9.03227 12.2978C9.02528 12.2055 9.02172 12.1123 9.02172 12.0182C9.02229 11.0617 9.39838 10.1446 10.0672 9.46862C10.7361 8.79266 11.6429 8.41324 12.5883 8.41382C13.7992 8.41531 14.8683 9.02804 15.5108 9.96325C15.816 9.85386 16.1444 9.79441 16.4866 9.79459C17.9982 9.79643 19.2397 10.9624 19.3836 12.4534C20.832 12.7729 21.9159 14.0785 21.9146 15.6395C21.9131 17.4385 20.4711 18.8958 18.6932 18.895C18.6309 18.895 18.569 18.8932 18.5075 18.8896Z" fill-rule="evenodd" clip-rule="evenodd"/>\n' +
															'<path class="landing-ui-panel-top-pub-btn-icon-defs-success" fill="#FFFFFF" d="M7.46967 13.782L9.1093 12.14L12.2726 15.2532L18.6078 8.91091L20.2474 10.5529L12.2881 18.5218L7.46967 13.782Z" fill-rule="evenodd" clip-rule="evenodd"/>\n' +
															'<path class="landing-ui-panel-top-pub-btn-icon-defs-error" fill="#FF7975" d="M19.8991 9.8334L17.607 7.54126L7.00036 18.1479L9.2925 20.44L19.8991 9.8334Z"/>\n' +
															'<path class="landing-ui-panel-top-pub-btn-icon-defs-error" fill="#FFFFFF" d="M19.9323 10.0725C20.2657 9.73913 20.2657 9.19867 19.9323 8.86532C19.599 8.53198 19.0585 8.53198 18.7252 8.86532L8.6579 18.9326C8.32455 19.266 8.32455 19.8064 8.6579 20.1398C8.99124 20.4731 9.5317 20.4731 9.86505 20.1398L19.9323 10.0725Z"/>' +
															'</svg>'
													}),
													BX.create('span', {
														props: { className: 'landing-popup-publication-content-autopub-text' },
														html: BX.message('LANDING_PUBLICATION_AUTO')
													}),
													BX.create('input', {
														props: { className: 'landing-popup-publication-content-autopub-input' },
														attrs: {
															type: 'checkbox',
															checked: top.window.autoPublicationEnabled
														},
														events: {
															click: function()
															{
																top.window.autoPublicationEnabled = this.checked;
																BX.ajax({
																	url: BX.util.add_url_param(
																		window.location.href,
																		{action: 'changeAutoPublication'}
																	),
																	method: 'POST',
																	data: {
																		param: this.checked ? 'Y' : 'N',
																		sessid: BX.message('bitrix_sessid'),
																		actionType: 'json'
																	},
																	dataType: 'json',
																	onsuccess: data => {
																		BX.removeClass(BX('landing-popup-publication-btn'), "landing-ui-panel-top-pub-btn-error");
																		if (this.checked)
																		{
																			BX.addClass(BX('landing-popup-publication-btn'), "landing-ui-panel-top-pub-btn-auto");
																			BX.addClass(BX('landing-popup-publication-btn'), "landing-ui-panel-top-pub-btn-loader");
																			BX.addClass(document.body.querySelector(".landing-popup-publication-content-autopub-icon"), "landing-ui-panel-top-pub-btn-auto");
																			BX.Landing.Backend.getInstance()
																				.action('Landing::publication', {
																					lid: landingId
																				})
																				.then(() => {
																					BX.removeClass(BX('landing-popup-publication-btn'), "landing-ui-panel-top-pub-btn-loader");
																				})
																		}
																		else
																		{
																			BX.removeClass(BX('landing-popup-publication-btn'), "landing-ui-panel-top-pub-btn-auto");
																			BX.removeClass(document.body.querySelector(".landing-popup-publication-content-autopub-icon"), "landing-ui-panel-top-pub-btn-auto");
																		}
																	}
																});
															}
														}
													}),
													BX.create('span', {
														props: { className: 'landing-popup-publication-content-autopub-switcher' },
														children: [
															BX.create('span', {
																props: { className: 'landing-popup-publication-content-autopub-switcher-on' },
																text: BX.message('LANDING_PUBLICATION_AUTO_TOGGLE_ON')
															}),
															BX.create('span', {
																props: { className: 'landing-popup-publication-content-autopub-switcher-off' },
																text: BX.message('LANDING_PUBLICATION_AUTO_TOGGLE_OFF')
															}),
														]
													}),
												]
											})
										]
									}),
									BX.create('div', {
										props: {
											className: 'landing-popup-publication-content-block-gray landing-popup-publication-content-center landing-popup-publication-content-column',
											style: {
												'position':'relative'
											}
										},
										children: [
											popupPublicationContentHint,
											BX.create('form', {
												attrs: {
													target: '_blank',
													method: 'post',
													action: this.urls['preview']
												},
												children: [
													BX.create('input', {
														attrs: {
															type: 'hidden',
															name: 'sessid',
															value: BX.message('bitrix_sessid')
														}
													}),
													BX.create('button', {
														props: { className: "landing-popup-publication-content-preview-btn ui-btn ui-btn-round ui-btn-no-caps ui-btn-shadow ui-btn-light" },
														attrs: {
															type: 'submit',
														},
														text: BX.message('LANDING_TPL_PREVIEW_URL')
													})
												]
											}),
											BX.create('form', {
												attrs: {
													target: '_blank',
													method: 'post',
													action: this.urls['publicationGlobal']
												},
												children: [
													BX.create('input', {
														attrs: {
															type: 'hidden',
															name: 'sessid',
															value: BX.message('bitrix_sessid')
														}
													}),
													BX.create('button', {
														props: { className: "landing-popup-publication-content-autopub-btn ui-btn ui-btn-round ui-btn-no-caps ui-btn-shadow ui-btn-success" },
														attrs: {
															type: 'submit',
														},
														text: BX.message('LANDING_PUBLICATION_SUBMIT')
													})
												]
											}),
										]
									}),
								]
							});

							popupPublicationContentHint.appendChild(
								BX.UI.Hint.createNode(BX.message('LANDING_TPL_PREVIEW_URL_HINT'))
							);
						}

						if (!oPopupPublication)
						{
							oPopupPublication = BX.PopupWindowManager.create(
								'landing-popup-publication',
								BX('landing-popup-publication-btn'),
								{
									content: popupPublicationContent,
									autoHide : true,
									closeIcon : false,
									titleBar : false,
									closeByEsc : true,
									animation: 'fading-slide',
									noAllPaddings : true,
									angle: {
										offset: 37
									},
									minWidth: 410,
									maxWidth: 460,
									background: "#E9EAED",
									contentBackground: "transparent",
								}
							);
						}
						else
						{
							oPopupPublication.setContent(popupPublicationContent)
						}

						var listener = window.addEventListener('blur', function() {
							oPopupPublication.close();
							window.removeEventListener('blur', listener);
						});

						oPopupPublication.toggle();

						BX.PreventDefault();
					}.bind(this)
				);
			}

			if (BX('landing-popup-preview-btn'))
			{
				var oPopupPreview = null;
				BX('landing-popup-preview-btn').addEventListener(
					'click',
					function()
					{
						if (!oPopupPreview)
						{
							if (top.window.autoPublicationEnabled)
							{
								const backend = BX.Landing.Backend.getInstance();
								if (this.storeEnabled)
								{
									backend
										.action('Landing::publication', {
											lid: this.id
										})
										.then(() => {
											return backend
												.action('Site::publication', {
													id: this.siteId
												});
										})
									;
								}
								else
								{
									backend
										.action('Landing::publication', {
											lid: this.id
										});
								}
							}

							var previewButton = BX('landing-popup-preview-btn');
							var fullUrl = this.url;
							var qrContainer = BX.create('div');
							new QRCode(qrContainer, {
								text: fullUrl,
								width: 156,
								height: 156,
								colorLight: "transparent"
							});
							oPopupPreview = BX.PopupWindowManager.create(
								'landing-popup-preview',
								previewButton,
								{
									content: BX.create('div', {
										props: { className: 'landing-popup-preview-content' },
										children: [
											BX.create('div', {
												props: { className: 'landing-popup-preview-title' },
												text: BX.message('LANDING_PREVIEW_MOBILE_TITLE')
											}),
											BX.create('div', {
												props: { className: 'landing-popup-preview-qr' },
												children: [
													qrContainer
												],
											}),
											BX.create('div', {
												props: { className: 'landing-popup-preview-text' },
												text: BX.message('LANDING_PREVIEW_MOBILE_TEXT')
											}),
											BX.create('div', {
												props: { className: 'landing-popup-preview-link-container' },
												children: [
													BX.create('a', {
														props: { className: 'landing-popup-preview-link ui-btn ui-btn-light-border ui-btn-round' },
														text: BX.message('LANDING_PREVIEW_MOBILE_NEW_TAB'),
														attrs: {
															target: '_blank',
															href: fullUrl
														}
													}),
												],
											}),
											BX.create('hr'),
											BX.create('div', {
												props: { className: 'landing-popup-preview-link-row-container' },
												children: [
													BX.create('div', {
														props: {className: 'landing-popup-preview-link-target-container'},
														children: [
															BX.create('a', {
																props: { className: 'landing-popup-preview-link-target' },
																text: (function() {
																	if (this.isFormEditor)
																	{
																		return fullUrl;
																	}
																	else
																	{
																		return BX.data(previewButton, 'domain');
																	}
																}.bind(this))(),
																attrs: {
																	target: '_blank',
																	href: fullUrl
																}
															}),
															BX.create('input', {
																props: {
																	className: 'landing-popup-preview-link-target-value-hd',
																	type: 'text',
																}
															}),
														],
													}),

													BX.create('div', {
														children: [
															BX.create('div', {
																props: { className: 'landing-popup-preview-link-target-copy' },
																text: BX.message('LANDING_PREVIEW_MOBILE_COPY_LINK'),
																events: {
																	click: function()
																	{
																		let href = null;
																		const linkElement = document.body.querySelector('.landing-popup-preview-link-target');
																		const linkElementValue = document.body.querySelector('.landing-popup-preview-link-target-value-hd');
																		if (linkElement)
																		{
																			href = linkElement.getAttribute('href');
																			linkElementValue.value = href;
																			linkElementValue.select();
																			document.execCommand('copy');
																			BX.UI.Notification.Center.notify({
																				content: BX.message('LANDING_SITE_TILE_POPUP_COPY_LINK_COMPLETE'),
																				autoHideDelay: 2000,
																			});
																		}
																	}.bind(this)
																}
															}),
														],
													}),
												],
											}),
										]
									}),
									//titleBar: {content: BX.create('span', {html: ''})},
									closeIcon : true,
									closeByEsc : true,
									noAllPaddings : true,
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
						}

						var listener = window.addEventListener('blur', function() {
							oPopupPreview.close();
							window.removeEventListener('blur', listener);
						});

						var formVerificationRequired = BX.data(BX('landing-popup-preview-btn'), 'form-verification-required') === '1';
						if (formVerificationRequired && top.BX.Bitrix24 && BX.Type.isObject(BX.Bitrix24.PhoneVerify))
						{
							var formId = BX.data(BX('landing-popup-preview-btn'), 'form-verification-entity');
							BX.Bitrix24.PhoneVerify
								.getInstance()
								.setEntityType('crm_webform')
								.setEntityId(formId)
								.startVerify({
									sliderTitle: BX.Loc.getMessage('LANDING_OPEN_FORM_PHONE_VERIFY_CUSTOM_SLIDER_TITLE'),
									title: BX.Loc.getMessage('LANDING_OPEN_FORM_PHONE_VERIFY_CUSTOM_TITLE'),
									description: BX.Loc.getMessage('LANDING_OPEN_FORM_PHONE_VERIFY_CUSTOM_DESCRIPTION'),
									callback: function (verified) {
										if (verified)
										{
											oPopupPreview.toggle();
										}
									}
								});
						}
						else
						{
							oPopupPreview.toggle();
						}

						BX.PreventDefault();
					}.bind(this)
				);
			}

			var settingsClick = BX.create('div', {
				props: { className: 'landing-popup-features-content-block landing-popup-features-content-block-settings' },
				html: '<div class="landing-popup-features-content-block-settings-icon ui-icon ui-icon-service-light-settings"><i></i></div>',
				events: {
					click: function()
					{
						this.onSettingsClick();
					}.bind(this)
				}
			})

			if (BX('landing-popup-features-btn'))
			{
				var oPopupFeatures = null;
				BX('landing-popup-features-btn').addEventListener(
					'click',
					function()
					{
						if (!oPopupFeatures)
						{
							var featuresButton = BX('landing-popup-features-btn');

							oPopupFeatures = BX.PopupWindowManager.create(
								'landing-popup-features',
								featuresButton,
								{
									content: BX.create('div', {
										props: { className: 'landing-popup-features-content' },
										children: [
											this.draftMode ? null : BX.create('div', {
												props: { className: 'landing-popup-features-content-block landing-popup-features-content-dflex' },
												children: [
													BX.create('div', {
														html: '<div class="ui-icon landing-popup-features-icon-1 ui-icon-md"><i></i></div>'
													}),
													BX.create('div', {
														style: { flexGrow: 1 },
														children: [
															BX.create('div', {
																props: { className: 'landing-popup-features-content-block-title' },
																text: BX.message('LANDING_TPL_FEATURES_FORMS_TITLE')
															}),
															BX.create('a', {
																props: { className: 'landing-popup-features-content-block-link' },
																text: BX.message('LANDING_TPL_FEATURES_FORMS_PROMO_LINK'),
																attrs: {
																	href: '#'
																},
																events: {
																	click: function()
																	{
																		if (this.helperFrameOpenUrl)
																		{
																			BX.Helper.show('redirect=detail&code=' + this.helpCodes['form_general'][0]);
																		}
																		BX.PreventDefault();
																	}.bind(this)
																}
															}),
														]
													}),
													BX.create('div', {
														children: [
															BX.create('input', {
																props: { className: 'landing-popup-features-content-block-btn ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-light-border' },
																attrs: {
																	type: 'button',
																	value: BX.message('LANDING_TPL_FEATURES_SETTINGS')
																},
																events: {
																	click: function()
																	{
																		var editorWindow = BX.Landing.PageObject.getEditorWindow();
																		var formBlock = editorWindow.document.querySelector('div[data-subtype="form"]');
																		var scrollToBlock = function(formBlock)
																		{
																			if (formBlock)
																			{
																				var blockId = formBlock.getAttribute('id').substr(5);
																				if (blockId)
																				{
																					var block = BX.Landing.PageObject.getBlocks().get(blockId);
																					block.onShowContentPanel();
																				}
																			}
																		}
																		if (!formBlock)
																		{
																			var blocksCollection = BX.Landing.PageObject.getBlocks();
																			BX.Landing.Main.getInstance().currentBlock = (blocksCollection.length <= 2)
																				? blocksCollection[1]
																				: blocksCollection[blocksCollection.length - 2];

																			var main = BX.Landing.Main.getInstance();
																			var editor = BX.Landing.PageObject.getEditorWindow();
																			main.currentArea = editor.document.body.querySelector('.landing-main');
																			main
																				.onAddBlock('33.13.form_2_light_no_text')
																				.then(function(res) {
																					res.setAttribute('data-subtype', 'form');
																					var maxIterations = 1000;
																					var iteration = 0;
																					var checkFormNode = function() {
																						requestAnimationFrame(function() {
																							if (res.querySelector('.b24-form div[id]'))
																							{
																								scrollToBlock(res);
																							}
																							else
																							{
																								if (iteration < maxIterations)
																								{
																									iteration += 1;
																									checkFormNode();
																								}
																							}
																						});
																					};

																					checkFormNode();
																				});
																			scrollToBlock(formBlock);
																		}
																		else
																		{
																			scrollToBlock(formBlock);
																		}

																		oPopupFeatures.close();
																	}.bind(this)
																}
															}),
														]
													}),
												]
											}),
											this.draftMode ? null : BX.create('div', {
												props: { className: 'landing-popup-features-content-block landing-popup-features-content-dflex' },
												children: [
													BX.create('div', {
														html: '<div class="ui-icon ui-icon ui-icon-service-livechat landing-popup-features-icon-2 ui-icon-md"><i></i></div>'
													}),
													BX.create('div', {
														style: { flexGrow: 1 },
														children: [
															BX.create('div', {
																props: { className: 'landing-popup-features-content-block-title' },
																text: BX.message('LANDING_TPL_FEATURES_OL_TITLE')
															}),
															BX.create('a', {
																props: { className: 'landing-popup-features-content-block-link' },
																text: BX.message('LANDING_TPL_FEATURES_OL_PROMO_LINK'),
																attrs: {
																	href: '#'
																},
																events: {
																	click: function()
																	{
																		if (this.helperFrameOpenUrl)
																		{
																			BX.Helper.show('redirect=detail&code=' + this.helpCodes['widget_general'][0]);
																		}
																		BX.PreventDefault();
																	}.bind(this)
																}
															}),
														]
													}),
													BX.create('div', {
														children: [
															BX.create('input', {
																props: { className: 'landing-popup-features-content-block-btn ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-light-border' },
																attrs: {
																	type: 'button',
																	value: BX.message('LANDING_TPL_FEATURES_SETTINGS')
																},
																events: {
																	click: function()
																	{
																		var button24hook = BX.Landing.Main.getInstance().options.hooks['B24BUTTON'];
																		if (button24hook && button24hook['ID'])
																		{
																			BX.SidePanel.Instance.open(
																				'/crm/button/edit/' + button24hook['ID'] + '/',
																				{ allowChangeHistory: false, cacheable: false }
																			);
																		}
																		else
																		{
																			BX.SidePanel.Instance.open(
																				BX.message['LANDING_PAR_PAGE_URL_SITE_EDIT'] + '#b24widget',
																				{ allowChangeHistory: false, cacheable: false }
																			);
																		}
																	}.bind(this)
																}
															}),
														]
													}),
												]
											}),
											BX.create('div', {
												props: { className: 'landing-popup-features-content-row' },
												children: [
													BX.create('div', {
														style: {
															marginRight: "12px",
															flexGrow: 1,
														},
														props: { className: 'landing-popup-features-content-block landing-popup-features-content-dflex' },
														children: [
															BX.create('div', {
																html: '<div class="ui-icon ui-icon-service-light-common landing-popup-features-icon-3"><i></i></div>'
															}),
															BX.create('div', {
																children: [
																	BX.create('div', {
																		props: { className: 'landing-ui-panel-top-menu-link-help' },
																		text: BX.message('LANDING_TPL_FEATURES_HELP_TITLE_MSGVER_1')
																	}),
																	BX.create('a', {
																		props: { className: 'landing-popup-features-content-block-link' },
																		text: BX.message('LANDING_TPL_FEATURES_HELP_PROMO_LINK_MSGVER_1'),
																		attrs: {
																			href: '#'
																		}
																	})
																]
															}),
														],
														events: {
															click: function()
															{
																BX.fireEvent(BX(featuresButton.getAttribute('data-feedback')), 'click');
															}
														}
													}),
													settingsClick
												],
											}),
										]
									}),
									closeIcon : false,
									titleBar : false,
									closeByEsc : true,
									animation: 'fading-slide',
									noAllPaddings : true,
									angle: {
										position: "top",
										offset: 115
									},
									minWidth: 410,
									background: "#E9EAED",
									contentBackground: "transparent",
								}
							);
						}

						var listener = window.addEventListener('blur', function() {
							oPopupFeatures.close();
							window.removeEventListener('blur', listener);
						});

						oPopupFeatures.toggle();
						BX.PreventDefault();
					}.bind(this)
				);
			}
			else
			{
				BX.removeClass(settingsClick, "landing-popup-features-content-block");
				var settingsKbButton = BX('landing-panel-settings-kb');
				if (BX.Type.isDomNode(settingsKbButton))
				{
					settingsKbButton.appendChild(settingsClick)
				}
			}

			if (BX('landing-design-block-close'))
			{
				BX('landing-design-block-close').addEventListener(
					'click',
					function()
					{
						BX.SidePanel.Instance.close();
					}
				);
			}

			var crmFormShareButton = document.querySelector('.landing-form-editor-share-button');
			if (BX.Type.isDomNode(crmFormShareButton))
			{
				BX.Event.bind(crmFormShareButton, 'click', this.onCrmFormShareButtonClick.bind(this));
			}

			var crmFormSettingsButton = document.querySelector('.landing-ui-panel-top-menu-link-settings');
			if (BX.Type.isDomNode(crmFormSettingsButton))
			{
				BX.Event.bind(crmFormSettingsButton, 'click', this.onSettingsClick.bind(this));
			}
		},

		onCrmFormShareButtonClick: function(event)
		{
			event.preventDefault();

			if (!this.formSharePopup)
			{
				var phoneVerified = BX.data(document.querySelector('#landing-popup-preview-btn'), 'form-verification-required') !== '1';

				this.formSharePopup = new BX.Landing.Form.SharePopup({
					bindElement: event.currentTarget,
					phoneVerified: phoneVerified,
				});
			}

			this.formSharePopup.show();
		},

		getErrorClickHandler: function(errorCode)
		{
			if (errorCode === 'LANDING_PAYMENT_FAILED_BLOCK')
			{
				return function() {
					var blocks = BX.Landing.PageObject.getBlocks();
					for (var i = 0, c = blocks.length; i < c; i++)
					{
						if (!blocks[i].isAllowedByTariff())
						{
							blocks[i].getBlockNode().scrollIntoView();
						}
					}
				};
			}
			else if (errorCode === 'FREE_DOMAIN_IS_NOT_ALLOWED')
			{
				return function() {
					BX.UI.InfoHelper.show('limit_free_domen');
				};
			}
			else if (errorCode === 'EMAIL_NOT_CONFIRMED')
			{
				return function() {
					BX.UI.InfoHelper.show('limit_sites_confirm_email');
				};
			}
			else if (errorCode === 'PHONE_NOT_CONFIRMED' && top.BX.Bitrix24 && BX.Type.isObject(top.BX.Bitrix24.PhoneVerify))
			{
				return function() {
					top.BX.Bitrix24.PhoneVerify
						.getInstance()
						.setEntityType('landing_site')
						.setEntityId(this.siteId)
						.startVerify({
							mandatory: false,
							callback: function (verified) {
								if (verified)
								{
									top.window.location.reload();
								}
							}
						})
					;
				}.bind(this);
			}
			else if (
				errorCode === 'PUBLIC_PAGE_REACHED' ||
				errorCode === 'PUBLIC_SITE_REACHED' ||
				errorCode === 'PUBLIC_SITE_REACHED_FREE' ||
				errorCode === 'TOTAL_SITE_REACHED'
			)
			{
				if (errorCode === 'PUBLIC_PAGE_REACHED')
				{
					return function () {
						BX.UI.InfoHelper.show('limit_sites_number_page');
					};
				}
				else if (errorCode === 'PUBLIC_SITE_REACHED_FREE')
				{
					return function() {
						BX.UI.InfoHelper.show('limit_sites_free');
					};
				}
				else
				{
					if (this.storeEnabled)
					{
						return function() {
							BX.UI.InfoHelper.show('limit_shop_number');
						};
					}
					else
					{
						return function() {
							BX.UI.InfoHelper.show('limit_sites_number');
						};
					}
				}
			}
			else if (errorCode === 'SHOP_1C')
			{
				return function() {
					window.open(BX.message('LANDING_PUBLICATION_SHOP_ERROR_1C_BUTTON_LINK'), '_blank');
				};
			}

			return null;
		},

		getErrorButtonTitle: function(errorCode)
		{
			if (errorCode === 'LANDING_PAYMENT_FAILED_BLOCK')
			{
				return BX.message('LANDING_PUBLICATION_GOTO_BLOCK');
			}
			else if (
				errorCode === 'EMAIL_NOT_CONFIRMED' ||
				errorCode === 'PHONE_NOT_CONFIRMED'
			)
			{
				return BX.message('LANDING_PUBLICATION_CONFIRM_EMAIL');
			}
			else if (
				errorCode === 'FREE_DOMAIN_IS_NOT_ALLOWED' ||
				errorCode === 'PUBLIC_PAGE_REACHED' ||
				errorCode === 'PUBLIC_SITE_REACHED' ||
				errorCode === 'TOTAL_SITE_REACHED' ||
				errorCode === 'PUBLIC_SITE_REACHED_FREE'
			)
			{
				return BX.message('LANDING_PUBLICATION_BUY_RENEW');
			}
			else if (errorCode === 'SHOP_1C')
			{
				return BX.message('LANDING_PUBLICATION_SHOP_ERROR_1C_BUTTON');
			}
		},

		getErrorMessageBlock: function ()
		{
			if (BX('landing-popup-publication-error-area').hasAttribute('data-error'))
			{
				var errorCode = BX('landing-popup-publication-error-area').getAttribute('data-error');
				var errorArea = document.querySelector('#landing-popup-publication-error-area');
				var buttonTitle = this.getErrorButtonTitle(errorCode);
				var clickHandler = this.getErrorClickHandler(errorCode);
				return BX.create('div', {
					props: { className: 'landing-popup-publication-error-content-block landing-popup-features-content-dflex' },
					children: [
						BX.create('div', {
							props: { className: 'landing-popup-publication-error-content-icon' },
							html: '<svg width="36" height="37" viewBox="0 0 36 37" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.189937" d="M18 36.5C27.9411 36.5 36 28.4411 36 18.5C36 8.55887 27.9411 0.5 18 0.5C8.05887 0.5 0 8.55887 0 18.5C0 28.4411 8.05887 36.5 18 36.5Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M23.1036 24.1965H13.238C13.1535 24.1965 13.0699 24.1933 12.987 24.1871C10.7573 24.1352 8.96548 22.2714 8.96548 19.98C8.96613 18.8631 9.40079 17.7923 10.1738 17.003C10.5701 16.5984 11.0386 16.2848 11.5484 16.0754C11.5398 15.9617 11.5355 15.8468 11.5355 15.731C11.5362 14.5525 11.9948 13.4226 12.8105 12.5898C13.6262 11.7571 14.7321 11.2896 15.885 11.2903C17.3618 11.2922 18.6655 12.0471 19.4492 13.1992C19.8212 13.0645 20.2217 12.9912 20.639 12.9914C22.4825 12.9937 23.9966 14.4301 24.1721 16.2671C25.9384 16.6608 27.2602 18.2693 27.2587 20.1924C27.2569 22.4087 25.4983 24.2041 23.3301 24.2032C23.2541 24.2032 23.1786 24.2008 23.1036 24.1965Z" fill="white"/></svg>'
						}),
						BX.create('div', {
							style: { flexGrow: 1 },
							children: [
								BX.create('div', {
									props: { className: 'landing-popup-publication-error-content-block-title' },
									html: errorArea.getAttribute('data-error-title') || BX.message('LANDING_PUBLICATION_AUTO_OFF')
								}),
								BX.create('span', {
									props: { className: 'landing-popup-publication-error-content-block-text' },
									text: errorArea.getAttribute('data-error-description')
								}),
							]
						}),
						clickHandler
							? BX.create('div', {
								children: [
									BX.create('a', {
										props: { className: 'landing-btn-buy-renew ui-btn ui-btn-xs ui-btn-no-caps ui-btn-round' },
										text: buttonTitle,
										events: {
											click: clickHandler
										}
									})
								]
							})
							: null
					]
				})
			}
			else
			{
				return false;
			}
		},

		/**
		 * Handles click on settings button.
		 */
		onSettingsClick: function()
		{
			if (
				typeof landingParams['PAGE_URL_LANDING_SETTINGS'] !== 'undefined' &&
				typeof BX.SidePanel !== 'undefined'
			)
			{
				BX.SidePanel.Instance.open(landingParams['PAGE_URL_LANDING_SETTINGS'], {
					events: {
						onCloseComplete: ()=> {
							if (top.window['landingSettingsSaved'] === true)
							{
								top.window['landingSettingsSaved'] = false;

								if (
									this.type === 'KNOWLEDGE'
									|| this.type === 'GROUP'
									|| this.isMainpage

								)
								{
									if (BX.SidePanel.Instance.getTopSlider())
									{
										BX.SidePanel.Instance.reload();
										return;
									}
								}

								top.window.location.reload();
							}
						},
					},
				});
			}
		},
	};

	/**
	 * Block's auto publication.
	 * @param {Object} options
	 * @constructor
	 */
	BX.Landing.Component.View.AutoPublication = function(options)
	{
		this.blockId = null;
		this.landingId = null;
		this.fullPublication = false;
		this.pendingPublication = false;
		this.editorEnabled = false;
		this.pageIsUnActive = options.pageIsUnActive;
		this.allowedCommands = {
			'Landing::upBlock': true,
			'Landing::downBlock': true,
			'Landing::showBlock': true,
			'Landing::hideBlock': true,
			'Landing::markDeletedBlock': true,
			'Landing::addBlock': true,
			'Landing::copyBlock': true,
			'Landing::moveBlock': true,
			'Block::changeNodeName': true,
			'Block::updateContent': true,
			'Landing\\Block::addCard': true,
			'Landing\\Block::cloneCard': true,
			'Landing\\Block::removeCard': true,
			'Landing\\Block::updateNodes': true,
			'Landing\\Block::updateStyles': true
		};
		this.fullPublicationCommands = {
			'Landing::upBlock': true,
			'Landing::downBlock': true,
			'Landing::addBlock': true,
			'Landing::copyBlock': true,
			'Landing::moveBlock': true,
			'Landing::markDeletedBlock': true
		};

		BX.addCustomEvent('BX.Landing.Editor:enable', BX.delegate(this.enableEditor, this));
		BX.addCustomEvent('BX.Landing.Editor:disable', BX.delegate(this.disableEditor, this));
		BX.addCustomEvent('BX.Landing.Backend:action', BX.delegate(this.onAction, this));
		BX.addCustomEvent('BX.Landing.Backend:batch', BX.delegate(this.onAction, this));
	};

	BX.Landing.Component.View.AutoPublication.prototype =
	{
		enableEditor: function()
		{
			this.editorEnabled = true;
		},

		disableEditor: function()
		{
			if (this.pendingPublication)
			{
				this.processing();
			}
			this.editorEnabled = false;
			this.pendingPublication = false;
		},

		getStatusArea: function()
		{
			var rootWindow = BX.Landing.PageObject.getRootWindow();
			return rootWindow.document.querySelector('#landing-popup-publication-btn');
		},

		getErrorArea: function()
		{
			var rootWindow = BX.Landing.PageObject.getRootWindow();
			return rootWindow.document.querySelector('#landing-popup-publication-error-area');
		},

		resolveEntityId: function(data, entityCode)
		{
			if (typeof data[entityCode] !== 'undefined')
			{
				return parseInt(data[entityCode]);
			}
			var keys = Object.keys(data);
			for (var i = 0, c = keys.length; i < c; i++)
			{
				if (
					typeof data[keys[i]].data !== 'undefined' &&
					typeof data[keys[i]].data[entityCode] !== 'undefined'
				)
				{
					return parseInt(data[keys[i]].data[entityCode]);
				}
			}
			return null;
		},

		isActionAllowed: function(action)
		{
			this.fullPublication = this.fullPublicationCommands[action] === true;
			return this.allowedCommands[action] === true;
		},

		onAction: function(action, data)
		{
			if (this.isActionAllowed(action))
			{
				this.blockId = this.resolveEntityId(data, 'block');
				this.landingId = this.resolveEntityId(data, 'lid');
				this.revertStatusMessage();
				if (this.editorEnabled)
				{
					this.pendingPublication = true;
				}
				else
				{
					this.processing();
				}
			}
		},

		actualizeStatusMessage: function()
		{
			if (!top.window.autoPublicationEnabled)
			{
				this.revertStatusMessage();
			}
			else
			{
				this.updateStatusMessage();
			}
		},

		updateStatusMessage: function()
		{
			BX.message({
				LANDING_PAGE_STATUS_UPDATED: BX.message('LANDING_PAGE_STATUS_PUBLIC'),
				LANDING_PAGE_STATUS_UPDATED_NOW: BX.message('LANDING_PAGE_STATUS_PUBLIC_NOW')
			});
			BX.Landing.UI.Panel.StatusPanel.getInstance().update();
		},

		revertStatusMessage: function()
		{
			BX.message({
				LANDING_PAGE_STATUS_UPDATED: BX.message('LANDING_PAGE_STATUS_UPDATED_ORIG'),
				LANDING_PAGE_STATUS_UPDATED_NOW: BX.message('LANDING_PAGE_STATUS_UPDATED_NOW_ORIG')
			});
			BX.Landing.UI.Panel.StatusPanel.getInstance().update();
		},

		processing: function()
		{
			this.actualizeStatusMessage();
			if (!top.window.autoPublicationEnabled)
			{
				this.blockId = null;
				this.landingId = null;
				return;
			}
			if (this.blockId || this.fullPublication)
			{
				setTimeout(function() {
					BX.addClass(this.getStatusArea(), "landing-ui-panel-top-pub-btn-loader")
					const action = (this.fullPublication || this.pageIsUnActive) ? 'Landing::publication' : 'Block::publication';
					BX.Landing.Backend.getInstance()
						.action(action, {
							block: this.blockId,
							lid: this.landingId
						})

						.then(response => {
							this.pageIsUnActive = false ;
							this.setSuccess();
						})

						.catch(function(response) {
							if (
								response.result &&
								typeof response.result[0] !== 'undefined'
							)
							{
								this.setError(response.result[0]);
							}
							else
							{
								this.setError({
									error: 'system_error',
									error_description: 'System error'
								});
							}
						}.bind(this));

					this.blockId = null;
					this.landingId = null;
				}.bind(this), 0);
			}
		},

		setSuccess: function()
		{
			var errorArea = this.getErrorArea();
			var statusArea = this.getStatusArea();
			errorArea.removeAttribute('data-error');
			errorArea.removeAttribute('data-error-description');
			BX.addClass(statusArea, "landing-ui-panel-top-pub-btn-success")
			BX.removeClass(statusArea, "landing-ui-panel-top-pub-btn-error");
			BX.removeClass(statusArea, "landing-ui-panel-top-pub-btn-loader");
			setTimeout(function() {
				statusArea.style.backgroundColor = '';
				BX.removeClass(statusArea, "landing-ui-panel-top-pub-btn-success")
			}.bind(this), 1000);
		},

		setError: function(error)
		{
			var errorArea = this.getErrorArea();
			var statusArea = this.getStatusArea();
			BX.removeClass(statusArea, "landing-ui-panel-top-pub-btn-loader");
			BX.addClass(statusArea, "landing-ui-panel-top-pub-btn-error");
			errorArea.setAttribute('data-error-description', error.error_description);
			errorArea.setAttribute('data-error', error.error);
		}
	};

	/**
	 * Change top panel for new landing instance.
	 * @param {int} id New landing id.
	 * @param {Object} params Additional params.
	 */
	BX.Landing.Component.View.changeTop = function(id, params)
	{
		params = params || {};

		if (typeof params.changeState === 'undefined')
		{
			params.changeState = true;
		}

		BX.ajax({
			url: BX.util.add_url_param(
				window.location.href,
				{action: 'changeTop'}
			),
			method: 'POST',
			data: {
				param: id,
				sessid: BX.message('bitrix_sessid'),
				actionType: 'json'
			},
			dataType: 'json',
			onsuccess: function(data)
			{
				BX.Landing.Component.View.instance.closeAllPopupsMenu();
				BX.Landing.Component.View.instance.setNewOptions(
					data
				);
				BX.Landing.Component.View.instance.buildTop({
					changeState: params.changeState
				});
			}
		});
	};

	BX.Landing.Component.View.MainpagePublication = function(options)
	{
		this.buttonPublic = options.buttonPublic;
		this.buttonUnpublic = options.buttonUnpublic;
		if (this.buttonPublic && this.buttonUnpublic)
		{
			BX.bind(this.buttonPublic, 'click', this.public.bind(this));
			BX.bind(this.buttonUnpublic, 'click', this.unpublic.bind(this));
		}
	};

	BX.Landing.Component.View.MainpagePublication.prototype = {
		public: function ()
		{
			BX.addClass(this.buttonPublic, 'ui-btn-wait');
			BX.ajax.runAction('intranet.mainpage.publish')
				.then(() => {
					BX.removeClass(this.buttonPublic, 'ui-btn-wait');
					BX.hide(this.buttonPublic);
					BX.show(this.buttonUnpublic);
				});
			BX.UI.Analytics.sendData({
				tool: 'landing',
				category: 'vibe',
				event: 'publish_page',
				c_sub_section: 'from_editor',
			});
		},

		unpublic: function ()
		{
			BX.addClass(this.buttonUnpublic, 'ui-btn-wait');
			BX.ajax.runAction('intranet.mainpage.withdraw')
				.then(() => {
					BX.removeClass(this.buttonUnpublic, 'ui-btn-wait');
					BX.hide(this.buttonUnpublic);
					BX.show(this.buttonPublic);
				});
			BX.UI.Analytics.sendData({
				tool: 'landing',
				category: 'vibe',
				event: 'unpublish_page',
				c_sub_section: 'from_editor',
			});
		},
	};
})();

var landingAlertMessage = function landingAlertMessage(errorText, payment, errorCode)
{
	if (payment === true && (errorCode === 'PUBLIC_SITE_REACHED' || errorCode === 'PUBLIC_SITE_REACHED_FREE'))
	{
		(function()
		{
			if (landingSiteType === 'STORE')
			{
				top.BX.UI.InfoHelper.show('limit_shop_number');
			}
			else
			{
				top.BX.UI.InfoHelper.show('limit_sites_number');
			}
		})();
	}
	else if (errorCode === 'FREE_DOMAIN_IS_NOT_ALLOWED')
	{
		top.BX.UI.InfoHelper.show('limit_free_domen');
	}
	else if (errorCode === 'EMAIL_NOT_CONFIRMED')
	{
		top.BX.UI.InfoHelper.show('limit_sites_confirm_email');
	}
	else if (payment === true && typeof BX.Landing.PaymentAlertShow !== 'undefined')
	{
		BX.Landing.PaymentAlertShow({
			message: errorText
		});
	}
	else
	{
		var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();
		msg.show({
			content: errorText,
			confirm: 'OK',
			contentColor: 'grey',
			type: 'alert'
		});
	}
}
