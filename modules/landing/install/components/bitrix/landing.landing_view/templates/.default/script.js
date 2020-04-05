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
			this.topInit = options.topInit || false;
			this.active = options.active || false;
			this.draftMode = options.draftMode || false;
			this.id = options.id || 0;
			this.siteId = options.siteId || 0;
			this.pagesCount = options.pagesCount || 0;
			this.siteTitle = options.siteTitle || '';
			this.storeEnabled = options.storeEnabled || false;
			this.fullPublication = options.fullPublication || false;
			this.urls = options.urls || {};
			this.rights = options.rights || {};
			this.sliderConditions = options.sliderConditions || [];
			if (!this.popupMenuIds)
			{
				this.popupMenuIds = [];
			}
			if (!this.placements)
			{
				this.placements = options.placements || [];
			}
			// clear menus
			for (var i = 0, c = this.popupMenuIds.length; i < c; i++)
			{
				var menu = BX.PopupMenu.getMenuById(
					this.popupMenuIds[i]
				);
				if (menu)
				{
					menu.destroy();
				}
			}
			this.popupMenuIds = [];
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
			// on required links click
			if (!this.topInit)
			{
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
			}
			// force top and style panel initialization
			if (this.topInit)
			{
				var editorWindow = BX.Landing.PageObject.getEditorWindow();
				var rootWindow = BX.Landing.PageObject.getRootWindow();

				editorWindow.addEventListener('load', function() {
					BX.Landing.UI.Panel.StylePanel.getInstance();
					rootWindow.BX.Landing.UI.Panel.Top.instance = null;
					BX.Landing.UI.Panel.Top.getInstance();
				});

				editorWindow.addEventListener('click', function() {
					this.closeAllPopupsMenu();
				}.bind(this));

				editorWindow.addEventListener('resize', BX.debounce(function() {
					this.closeAllPopupsMenu();
				}.bind(this), 200));
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

			var conditions = [];

			for (var i = 0, c = this.sliderConditions.length; i < c; i++)
			{
				conditions.push(this.sliderConditions[i]);
			}

			if (conditions.length <= 0)
			{
				return;
			}

			var sliderOptions = top.BX.clone({
				rules: [
					{
						condition: conditions
					}
				]
			});

			BX.SidePanel.Instance.bindAnchors(sliderOptions);
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
							document.querySelector('.landing-ui-panel-top-chain-link.landing-ui-panel-top-menu-link-settings').classList.add('landing-ui-disabled');
							[].slice.call(document.querySelectorAll('.landing-ui-panel-top-menu-link:not(.landing-ui-panel-top-menu-link-help)'))
								.forEach(function(item) {
									item.classList.add('landing-ui-disabled');
								});
						}
						else
						{
							iframe.classList.add('landing-ui-view-show');
						}

						setTimeout(function() {
							BX.remove(loaderContainer);
							BX.remove(userActionContainer);
						}, 200);
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
								if (iframe.contentWindow.BX.Landing.Block.Node.Text.currentNode)
								{
									iframe.contentWindow.BX.Landing.Block.Node.Text.currentNode.disableEdit();
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
			var href = element.getAttribute('href');

			if (href.substr(0, 1) !== '#')
			{
				window.open(href, '_top');
			}

			var linkTpl = href.substr(1);
			var urlParams = {};
			var linkTplAnchor = '';

			if (linkTpl.indexOf('@') > 0)
			{
				linkTplAnchor = linkTpl.split('@')[1];
				linkTpl = linkTpl.split('@')[0];
			}
			linkTpl = linkTpl.toUpperCase();

			if (linkTpl === 'PAGE_URL_CATALOG_EDIT')
			{
				linkTpl = 'PAGE_URL_SITE_EDIT';
				urlParams.tpl = 'catalog';
			}

			if (
				typeof landingParams[linkTpl] !== 'undefined' &&
				typeof BX.SidePanel !== 'undefined'
			)
			{
				BX.SidePanel.Instance.open(
					BX.util.add_url_param(
						landingParams[linkTpl],
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

			// direct id for some urls
			for (var key in this.urls)
			{
				var link = BX('landing-urls-' + key);
				if (link)
				{
					link.setAttribute('href', this.urls[key]);
				}
			}
			// settings menu
			var settingButtons = [].slice.call(
				document.querySelectorAll('.landing-ui-panel-top-menu-link-settings')
			);
			settingButtons.forEach(function(element, index) {
				element.addEventListener(
					'click',
					function()
					{
						this.onSettingsClick(index, element);
					}.bind(this)
				);
			}.bind(this));
			// publication menu
			if (BX('landing-publication'))
			{
				BX('landing-publication').setAttribute(
					'href',
					this.fullPublication
						? this.urls['publicationAll']
						: this.urls['publication']
				);
				if (!this.rights.public)
				{
					BX.addClass(
						BX('landing-publication').parentNode,
						'ui-btn-disabled'
					);
				}
				else
				{
					BX.removeClass(
						BX('landing-publication').parentNode,
						'ui-btn-disabled'
					);
				}
				if (BX('landing-publication-submenu'))
				{
					BX('landing-publication-submenu').addEventListener(
						'click',
						function()
						{
							var element = BX('landing-publication-submenu');
							if (!BX.hasClass(element.parentNode, 'ui-btn-disabled'))
							{
								this.onSubPublicationClick(element);
							}
						}.bind(this)
					);
				}
				BX('landing-publication').addEventListener(
					'click',
					function()
					{
						if (BX.hasClass(BX('landing-publication').parentNode, 'ui-btn-disabled'))
						{
							BX.PreventDefault();
						}
						else if (BX('landing-publication').getAttribute('target') === '_self')
						{
							BX.addClass(document.querySelector(".ui-btn-primary.landing-btn-menu"), 'ui-btn-wait');
						}
					}.bind(this)
				);
			}
			if (BX('landing-urls-preview'))
			{
				BX('landing-urls-preview').addEventListener(
					'click',
					function()
					{
						if (BX('landing-urls-preview').getAttribute('target') === '_self')
						{
							BX.SidePanel.Instance.open(
								BX('landing-urls-preview').getAttribute('href') + '&IFRAME=Y',
								{
									allowChangeHistory: false
								}
							);
							BX.PreventDefault();
						}
					}.bind(this)
				);
			}
			// nav chain
			// BX('landing-navigation-site').text = this.siteTitle;
			// BX('landing-navigation-site').setAttribute('title', this.siteTitle);
			// BX('landing-navigation-page').text = this.title;
			// BX('landing-navigation-page').setAttribute('title', this.title);
			// set browser title and url
			// if (options.changeState === true)
			// {
			// 	parent.window.history.pushState('', this.title, this.urls['landingView']);
			// }
			// document.title = this.title;
		},

		/**
		 * Handles click on publication sub button.
		 * @param element Node element (sub menu of publication).
		 */
		onSubPublicationClick: function(element)
		{
			if (BX.PopupMenu.getMenuById('landing-menu-publication'))
			{
				var menu = BX.PopupMenu.getMenuById('landing-menu-publication');
			}
			else
			{
				this.popupMenuIds.push('landing-menu-publication');
				var menu = new BX.Landing.UI.Tool.Menu({
					id: 'landing-menu-publication',
					bindElement: element,
					autoHide: true,
					zIndex: 1200,
					offsetLeft: 20,
					angle: true,
					closeByEsc: true,
					items: [
						{
							href: this.urls['publication'],
							text: BX.message('LANDING_TPL_PUBLIC_URL_PAGE'),
							target: '_blank',
							dataset: {
								sliderIgnoreAutobinding: true
							}
						},
						{
							href: this.urls['publicationAll'],
							text: BX.message('LANDING_TPL_PUBLIC_URL_ALL'),
							target: '_blank',
							dataset: {
								sliderIgnoreAutobinding: true
							}
						}
					]
				})
			}
			menu.show();
		},

		/**
		 * Handles click on settings button.
		 * @param index Number of node element.
		 * @param element Node element (settings button).
		 */
		onSettingsClick: function(index, element)
		{
			if (BX.PopupMenu.getMenuById('landing-menu-settings' + index))
			{
				var menu = BX.PopupMenu.getMenuById('landing-menu-settings' + index);
			}
			else
			{
				this.popupMenuIds.push('landing-menu-settings' + index);
				var menuItems = [
					{
						href: this.urls['landingEdit'],
						text: BX.message('LANDING_TPL_SETTINGS_PAGE_URL'),
						disabled: !this.rights.settings
					},
					{
						href: this.urls['landingSiteEdit'],
						text: BX.message('LANDING_TPL_SETTINGS_SITE_URL'),
						disabled: !this.rights.settings
					},
					this.storeEnabled
					? {
						href: this.urls['landingCatalogEdit'],
						text: BX.message('LANDING_TPL_SETTINGS_CATALOG_URL'),
						disabled: !this.rights.settings
					}
					: null,
					!this.draftMode
					? {
						href: this.urls['unpublic'],
						text: BX.message('LANDING_TPL_SETTINGS_UNPUBLIC'),
						disabled: !this.rights.public || !this.active
					}
					: null
				];
				var __this = this;
				for (var p = 0, cp = this.placements.length; p < cp; p++)
				{
					var placementItem = this.placements[p];
					menuItems.push({
						text: BX.util.htmlspecialchars(placementItem.TITLE),
						onclick: function()
						{
							BX.rest.AppLayout.openApplication(
								this.APP_ID,
								{
									SITE_ID: __this.siteId,
									LID: __this.id
								},
								{
									PLACEMENT: this.PLACEMENT,
									PLACEMENT_ID: this.ID
								}
							);
						}.bind(placementItem, __this)
					});
				}
				var menu = new BX.Landing.UI.Tool.Menu({
						id: 'landing-menu-settings' + index,
						bindElement: BX('landing-panel-settings'),
						autoHide: true,
						zIndex: 1200,
						offsetLeft: 20,
						angle: true,
						closeByEsc: true,
						items: menuItems
					}
				);
			}
			menu.show();
		},

		/**
		 * Close all popups menu.
		 */
		closeAllPopupsMenu: function()
		{
			this.popupMenuIds.forEach(function(id) {
				var menu = BX.PopupMenu.getMenuById(id);

				if (menu)
				{
					menu.close();
				}
			})
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
	}
})();

var landingAlertMessage = function landingAlertMessage(errorText, payment)
{
	if (
		payment === true &&
		typeof BX.Landing.PaymentAlertShow !== 'undefined'
	)
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
