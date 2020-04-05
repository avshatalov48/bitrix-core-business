var landingAjaxSend = false;
var scriptIsLoaded = false;

BX.ready(function()
{
	var createFolderEl = BX('landing-create-folder');
	var recycleBin = BX('landing-recycle-bin');

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
	BX.addCustomEvent('BX.Main.Filter:apply', function()
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

		var loader = new BX.Loader({size: 130, color: "#bfc3c8"});
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
	});

	// create folder
	BX.bind(
		createFolderEl,
		'click',
		function()
		{
			if (BX.hasClass(createFolderEl, 'ui-btn-disabled'))
			{
				return;
			}
			var createFolderText = BX.create('input', {
				props: {
					type: 'text',
					className: 'landing-filter-folder-input'
				}
			});
			BX.focus(createFolderText);
			BX.Landing.UI.Tool.ActionDialog.getInstance()
				.show({
					title: createFolderEl.getAttribute('title'),
					content: createFolderText,
					confirm: BX.data(createFolderEl, 'action')
				})
				.then(
					function() {
						BX.ajax({
							url: '/bitrix/tools/landing/ajax.php?action=Landing::add&folder=Y',
							method: 'POST',
							data: {
								data: {
									fields: {
										TITLE: createFolderText.value,
										FOLDER: 'Y',
										SITE_ID: BX.data(createFolderEl, 'siteId')
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
												BX.fireEvent(createFolderEl, 'click');
											}
										);
									}
									else
									{
										var url = BX.util.add_url_param(
											window.location.href,
											{
												folderId: data.result
											}
										);
										window.location.href = url;
									}
								}
							}
						});
					},
					function(error) {
						//
					}
				);
		}
	);
});