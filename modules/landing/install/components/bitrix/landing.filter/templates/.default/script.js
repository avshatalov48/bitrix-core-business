var landingAjaxSend = false;
var scriptIsLoaded = false;

BX.ready(function()
{
	var createFolderEl = BX('landing-create-folder');
	var recycleBin = BX('landing-recycle-bin');
	var folderGrid = BX('grid-tile-inner');

	if (scriptIsLoaded)
	{
		return;
	}

	scriptIsLoaded = true;

	// recycle bin
	BX.bind(
		recycleBin,
		'click',
		function()
		{
			var filterManager = BX.Main.filterManager.getById(landingFilterId),
				filterApi = filterManager.getApi(),
				currValues = filterManager.getFilterFieldsValues();

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
		}
	);

	// apply filter
	var landingFilterCallback = function()
	{
		if (landingAjaxSend)
		{
			return;
		}
		landingAjaxSend = true;

		var workArea = BX('workarea-content');

		var loaderContainer = BX.create('div',{
			attrs:{className:'landing-filter-loading'}
		});
		document.body.appendChild(loaderContainer);

		var loader = new BX.Loader({size: 130, color: '#bfc3c8'});
		loader.show(loaderContainer);

		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: landingAjaxPath,
			onsuccess: function(data)
			{
				landingAjaxSend = false;
				loader.hide();
				loaderContainer.classList.add('landing-filter-loading-hide');
				workArea.innerHTML = data;
			}
		});
	};

	BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(landingFilterCallback));
	BX.addCustomEvent('BX.Landing.Filter:apply', BX.delegate(landingFilterCallback));

	// create folder
	if (createFolderEl)
	{
		var createFolderText = BX.create('input', {
			props: {
				type: 'text',
			}
		});

		var folderCreating = false;

		function ajaxAddFolder() {

			if (folderCreating) {
				return;
			}

			folderCreating = true;

			BX.ajax({
				url: '/bitrix/tools/landing/ajax.php?' + '' +
					'action=Site::addFolder&' +
					'type=' + BX.data(createFolderEl, 'type'),
				method: 'POST',
				data: {
					data: {
						siteId: BX.data(createFolderEl, 'siteId'),
						fields: {
							TITLE: createFolderText.value,
							PARENT_ID: BX.data(createFolderEl, 'folderId')
						}
					},
					sessid: BX.message('bitrix_sessid')
				},
				dataType: 'json',
				onsuccess: function(data)
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
								type: 'alert'
							}).then(
								function()
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
								}
							);
						}
						else
						{
							window.location.reload();
						}
					}
				},
				onfailure: function(error)
				{
					folderCreating = false;
				}
			});
		}

		createFolderText.addEventListener('keydown', function(event) {
			if (event.keyCode === 13)
			{
				ajaxAddFolder();
			}
		})

		createFolderText.addEventListener('blur', function() {
			if (createFolderText.value.length !== 0)
			{
				ajaxAddFolder();
			}
		});

		var folderCard = BX.create('div', {
			props: {
				className: 'landing-item landing-item-folder'
			},
			children: [
				BX.create('div', {
					props: {
						className: 'landing-title'
					},
					children: [
						BX.create('div', {
							props: {
								className: 'landing-title-wrap'
							},
							children: [
								BX.create('div', {
									props: {
										className: 'landing-title-overflow --create-folder-input'
									},
									children: [
										createFolderText,
									]
								}),
							]
						}),
					]
				}),
				BX.create('div', {
					props: {
						className: 'landing-item-cover'
					},
					children: [
						BX.create('div', {
							props: {
								className: 'landing-item-preview'
							},
						}),
						BX.create('div', {
							props: {
								className: 'landing-item-folder-corner'
							},
							children: [
								BX.create('div', {
									props: {
										className: 'landing-item-folder-dropdown'
									},
								}),
							]
						}),
					]
				}),
			]
		});
		folderCard.style.display = 'none';

		BX.insertAfter(folderCard, document.body.querySelector(".landing-folder-placeholder"))

		BX.bind(
			createFolderEl,
			'click',
			function()
			{
				if (!folderCard.prepareNode)
				{
					BX.insertAfter(folderCard, document.body.querySelector(".landing-folder-placeholder"))
				}
				if (BX.hasClass(createFolderEl, 'ui-btn-disabled'))
				{
					return;
				}

				folderCard.style.display = 'block';
				BX.focus(createFolderText);
			}
		);
	}

	// settings button
	var onSettingsClick = function(event) {
		if (!Array.isArray(landingSettingsButtons))
		{
			return;
		}
		var lastLocation = top.location.toString();
		var events = {
			onClose: function()
			{
				if (window['landingSettingsSaved'] === true)
				{
					top.location = lastLocation;
				}
				if (BX.PopupMenu.getCurrentMenu())
				{
					BX.PopupMenu.getCurrentMenu().close();
				}
			}
		};
		if (landingSettingsButtons.length === 1)
		{
			var skipSlider =
				landingSettingsButtons[0]['dataset'] && landingSettingsButtons[0]['dataset']['skipSlider'] === true
			;

			if (!skipSlider)
			{
				BX.SidePanel.Instance.open(landingSettingsButtons[0]['href'], {
					allowChangeHistory: false,
					events: events
				});
			}
		}
		else
		{
			for (var i = 0, c = landingSettingsButtons.length; i < c; i++)
			{
				landingSettingsButtons[i]['onclick'] = function(event, item)
				{
					var skipSlider = item && item.dataset && item.dataset.skipSlider === true;
					if (!skipSlider)
					{
						BX.SidePanel.Instance.open(item.href, {
							allowChangeHistory: false,
							events: events
						});

						BX.PreventDefault(event);
					}
					BX.PopupMenu.getMenuById('landing-menu-settings').close();
				};
			}
			var menu = (
				BX.PopupMenu.getMenuById('landing-menu-settings') ||
				BX.Main.MenuManager.create({
					id: 'landing-menu-settings',
					bindElement: event.currentTarget,
					autoHide: true,
					zIndex: 1200,
					offsetLeft: 20,
					angle: true,
					closeByEsc: true,
					items: landingSettingsButtons
				})
			);
			menu.show();
		}
		BX.PreventDefault(event);
	};
	if (BX('landing-menu-settings'))
	{
		BX('landing-menu-settings').addEventListener(
			'click',
			BX.proxy(onSettingsClick, BX('landing-menu-settings'))
		);
	}

	// create buttons
	var onCreateActionsClick = function(event) {
		if (BX.hasClass(BX('landing-create-element'), 'ui-btn-disabled'))
		{
			BX.PreventDefault(event);
			return;
		}
		var menu = (
			BX.PopupMenu.getMenuById('landing-menu-action') ||
			BX.Main.MenuManager.create({
				id: 'landing-menu-action',
				bindElement: event.currentTarget,
				autoHide: true,
				zIndex: 1200,
				offsetLeft: 20,
				angle: true,
				closeByEsc: true,
				items: landingCreateButtons || []
			})
		);
		menu.show();
		BX.PreventDefault(event);
	};
	if (BX('landing-menu-actions'))
	{
		BX('landing-menu-actions').addEventListener(
			'click',
			BX.proxy(onCreateActionsClick, BX('landing-menu-actions'))
		);
	}
});
