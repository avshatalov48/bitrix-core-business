;(function ()
{
	'use strict';

	BX.namespace('BX.Landing.Component.Filter');

	BX.Landing.Component.Filter.filterId = null;
	BX.Landing.Component.Filter.canCreateFolder = false;
	BX.Landing.Component.Filter.createButton = null;
	BX.Landing.Component.Filter.createFolderButton = null;
	BX.Landing.Component.Filter.settingsButtons = null;
	BX.Landing.Component.Filter.ajaxPath = null;
	BX.Landing.Component.Filter.ajaxSend = false;

	BX.Landing.Component.Filter = function (params)
	{
		if (params.filterId !== undefined)
		{
			BX.Landing.Component.Filter.filterId = params.filterId;
		}

		if (BX.Type.isBoolean(params.canCreateFolder))
		{
			BX.Landing.Component.Filter.canCreateFolder = params.canCreateFolder;
		}

		const toolbar = BX.UI.ToolbarManager.getDefaultToolbar();
		if (toolbar && BX.Type.isString(params.landingCreateButtonId))
		{
			BX.Landing.Component.Filter.createButton = toolbar.getButton(params.landingCreateButtonId);
		}

		if (toolbar && BX.Type.isString(params.landingCreateFolderButtonId))
		{
			BX.Landing.Component.Filter.createFolderButton = toolbar.getButton(params.landingCreateFolderButtonId);
		}

		if (params.landingSettingsButtons !== undefined)
		{
			BX.Landing.Component.Filter.settingsButtons = params.landingSettingsButtons;
		}

		if (params.landingAjaxPath !== undefined)
		{
			BX.Landing.Component.Filter.ajaxPath = params.landingAjaxPath;
		}

		if (
			params.landingShowSiteAIPopup !== undefined
			&& BX.Type.isBoolean(params.landingShowSiteAIPopup)
			&& BX.Landing.Component.Filter.createButton
		)
		{
			BX.Runtime.loadExtension([
				'ai.copilot-promo-popup',
				'ui.banner-dispatcher',
				'landing.metrika',
			])
				.then((exports) => {
					const createButton = BX.Landing.Component.Filter.createButton.button;
					const { CopilotPromoPopup, BannerDispatcher, Metrika } = exports;
					BannerDispatcher.high.toQueue((onDone) => {
						const metrika = new Metrika(true);
						metrika.sendData({
							category: 'site',
							event: 'creating_scenario_hint_show',
							type: 'standart',
						});

						const popup = CopilotPromoPopup.createByPresetId({
							presetId: CopilotPromoPopup.Preset.SITE_WITH_COPILOT,
							targetOptions: createButton,
							angleOptions: {
								position: CopilotPromoPopup.AnglePosition.TOP,
								offset: createButton.offsetWidth / 2 + 25,
							},
						});
						popup.subscribe(CopilotPromoPopup.PromoVideoPopupEvents.HIDE, () => onDone());
						popup.show();
						BX.userOptions.save('landing', 'site-ai-popup', 'isShow', 'Y');
					});
				})
				.catch(() => {});
		}

		BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(BX.Landing.Component.Filter.onFilterCallback));
		BX.addCustomEvent('BX.Landing.Filter:apply', BX.delegate(BX.Landing.Component.Filter.onFilterCallback));

		// force creating folder item to correctly BX.Landing.TileGrid init
		if (BX.Landing.Component.Filter.canCreateFolder)
		{
			BX.Landing.Component.Filter.getFolderCard();
		}
	};

	BX.Landing.Component.Filter.onRecycleBinClick = () =>
	{
		if (BX.Landing.Component.Filter.filterId === null)
		{
			return;
		}

		const filterManager = BX.Main.filterManager.getById(BX.Landing.Component.Filter.filterId);
		const filterApi = filterManager.getApi();
		const currValues = filterManager.getFilterFieldsValues();
		if (currValues.DELETED === 'Y')
		{
			currValues.DELETED = '';
		}
		else
		{
			currValues.DELETED = 'Y';
		}

		filterApi.setFields(currValues);
		filterApi.apply();
	};

	BX.Landing.Component.Filter.onFilterCallback = (id, data, filter) =>
	{
		if (BX.Landing.Component.Filter.ajaxPath === null)
		{
			return;
		}

		if (BX.Landing.Component.Filter.ajaxSend)
		{
			return;
		}
		BX.Landing.Component.Filter.ajaxSend = true;

		if (filter)
		{
			const currValues = filter.getFilterFieldsValues();
			if (currValues.DELETED === 'Y')
			{
				BX.Landing.Component.Filter.createButton?.setDisabled(true);
				BX.Landing.Component.Filter.createFolderButton?.setDisabled(true);
			}
			else
			{
				BX.Landing.Component.Filter.createButton?.setDisabled(false);
				BX.Landing.Component.Filter.createFolderButton?.setDisabled(false);
			}
		}

		const workArea = BX('workarea-content') || BX('air-workarea-content');
		const loaderContainer = BX.create('div', {
			attrs: {
				className: 'landing-filter-loading',
			},
		});
		document.body.appendChild(loaderContainer);

		const loader = new BX.Loader({size: 130, color: '#bfc3c8'});
		loader.show(loaderContainer);

		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: BX.Landing.Component.Filter.ajaxPath,
			onsuccess: function (data)
			{
				BX.Landing.Component.Filter.ajaxSend = false;
				loader.hide();
				loaderContainer.classList.add('landing-filter-loading-hide');
				workArea.innerHTML = data;
			},
		});
	};

	BX.Landing.Component.Filter.onSettingsClick = (button) => {
		if (BX.Landing.Component.Filter.settingsButtons === null)
		{
			return;
		}

		const lastLocation = top.location.toString();
		const events = {
			onClose: () =>
			{
				if (window['landingSettingsSaved'] === true)
				{
					top.location = lastLocation;
				}

				if (BX.PopupMenu.getCurrentMenu())
				{
					BX.PopupMenu.getCurrentMenu().close();
				}
			},
		};

		const landingSettingsButtons = BX.Landing.Component.Filter.settingsButtons;
		if (landingSettingsButtons.length === 1)
		{
			const item = landingSettingsButtons[0];
			const skipSlider = item.dataset && item.dataset.skipSlider === true;
			if (!skipSlider)
			{
				BX.SidePanel.Instance.open(item.href, {
					allowChangeHistory: false,
					events,
				});
			}
		}
		else
		{
			for (let i = 0, c = landingSettingsButtons.length; i < c; i++)
			{
				landingSettingsButtons[i].onclick = (event, item) =>
				{
					const skipSlider = item && item.dataset && item.dataset.skipSlider === true;
					if (!skipSlider)
					{
						BX.SidePanel.Instance.open(item.href, {
							allowChangeHistory: false,
							events,
						});

						BX.PreventDefault(event);
					}
					BX.PopupMenu.getMenuById('landing-menu-settings').close();
				};
			}

			let menu = BX.PopupMenu.getMenuById('landing-menu-settings');
			if (!menu)
			{
				menu = BX.Main.MenuManager.create({
					id: 'landing-menu-settings',
					bindElement: button.button,
					autoHide: true,
					zIndex: 1200,
					offsetLeft: 20,
					angle: true,
					closeByEsc: true,
					items: landingSettingsButtons,
				});
			}
			if (menu)
			{
				menu.show();
			}
		}
		BX.PreventDefault(event);
	};

	BX.Landing.Component.Filter.onFolderCreateClick = (button) =>
	{
		if (!BX.Landing.Component.Filter.canCreateFolder)
		{
			return;
		}

		const createFolderEl = button.button;
		if (!createFolderEl)
		{
			return;
		}

		const folderCard = BX.Landing.Component.Filter.getFolderCard();
		const createFolderText = BX.Landing.Component.Filter.getFolderText();

		let folderCreating = false;

		function ajaxAddFolder()
		{
			if (folderCreating)
			{
				return;
			}

			folderCreating = true;

			BX.ajax({
				url: '/bitrix/tools/landing/ajax.php?' +
					'action=Site::addFolder&' +
					'type=' + BX.data(createFolderEl, 'type'),
				method: 'POST',
				data: {
					data: {
						siteId: BX.data(createFolderEl, 'siteId'),
						fields: {
							TITLE: createFolderText.value,
							PARENT_ID: BX.data(createFolderEl, 'folderId'),
						},
					},
					sessid: BX.message('bitrix_sessid'),
				},
				dataType: 'json',
				onsuccess: function (data)
				{
					if (
						typeof data.type !== 'undefined' &&
						typeof data.result !== 'undefined'
					)
					{
						if (data.type === 'error')
						{
							var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();
							msg.show({
								content: data.result[0].error_description,
								confirm: 'OK',
								type: 'alert',
							}).then(
								function ()
								{
									folderCreating = false;

									if (data.result[0].error === 'FOLDER_IS_NOT_UNIQUE')
									{
										createFolderText.value = '';
										createFolderText.focus();
									}
									else
									{
										BX.fireEvent(createFolderEl, 'click');
									}
								},
							);
						}
						else
						{
							window.location.reload();
						}
					}
				},
				onfailure: function (error)
				{
					folderCreating = false;
				},
			});
		}

		createFolderText.addEventListener('keydown', function (event)
		{
			if (event.keyCode === 13)
			{
				ajaxAddFolder();
			}
		});

		createFolderText.addEventListener('blur', function ()
		{
			if (createFolderText.value.length !== 0)
			{
				ajaxAddFolder();
			}
		});

		BX.style(folderCard, 'display', 'block');
		BX.focus(createFolderText);
	};
	BX.Landing.Component.Filter.folderCard = null;
	BX.Landing.Component.Filter.getFolderCard = () =>
	{
		if (BX.Landing.Component.Filter.folderCard)
		{
			return BX.Landing.Component.Filter.folderCard;
		}

		const createFolderText = BX.Landing.Component.Filter.getFolderText();

		const folderCard = BX.create('div', {
			props: {
				className: 'landing-item landing-item-folder',
			},
			children: [
				BX.create('div', {
					props: {
						className: 'landing-title',
					},
					children: [
						BX.create('div', {
							props: {
								className: 'landing-title-wrap',
							},
							children: [
								BX.create('div', {
									props: {
										className: 'landing-title-overflow --create-folder-input',
									},
									children: [
										createFolderText,
									],
								}),
							],
						}),
					],
				}),
				BX.create('div', {
					props: {
						className: 'landing-item-cover',
					},
					children: [
						BX.create('div', {
							props: {
								className: 'landing-item-preview',
							},
						}),
						BX.create('div', {
							props: {
								className: 'landing-item-folder-corner',
							},
							children: [
								BX.create('div', {
									props: {
										className: 'landing-item-folder-dropdown',
									},
								}),
							],
						}),
					],
				}),
			],
		});

		BX.style(folderCard, 'display', 'none');
		BX.Landing.Component.Filter.folderCard = folderCard;
		BX.insertAfter(folderCard, document.body.querySelector(".landing-folder-placeholder"));

		return folderCard;
	};
	BX.Landing.Component.Filter.folderText = null;
	BX.Landing.Component.Filter.getFolderText = () =>
	{
		if (BX.Landing.Component.Filter.folderText)
		{
			return BX.Landing.Component.Filter.folderText;
		}

		const createFolderText = BX.create('input', {
			props: {
				type: 'text',
			},
		});

		BX.Landing.Component.Filter.folderText = createFolderText;

		return createFolderText;
	};
})();
