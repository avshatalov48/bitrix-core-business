var roleFormFieldsChanged = false;

BX.ready(function()
{
	BX.bind(
		BX('landing-mode-form-submit'),
		'click',
		function()
		{
			BX('landing-mode-form').submit();
		}
	);

	// bind all select/input onchange
	var obSelect = BX('landing-role-edit').querySelectorAll('select,input');
	obSelect.forEach(function(element)
	{
		BX(element).addEventListener(
			'change',
			function()
			{
				roleFormFieldsChanged = true;
			}
		);
	});

	// remove rights
	var links = BX.findChild(
		BX('landing-role-rights-table'),
		{
			class: 'landing-rightsblock-remove'
		},
		true,
		true
	);
	links.forEach(function(element){
		BX(element).addEventListener(
			'click',
			function()
			{
				var id = BX.data(BX(element), 'id');
				if (id)
				{
					var tr = BX.findChild(
						BX('landing-role-rights-table'),
						{
							class: 'landing-rightsblock-' + id
						},
						true,
						true
					);
					tr.forEach(function(element){
						BX.remove(BX(element));
					});
				}
			}.bind(this)
		);
	});
});

function showSiteMenu(node, items, messages)
{
	var menuItems = [];
	var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();

	for (var id in items)
	{
		if (items[id].DELETED === 'Y')
		{
			continue;
		}
		menuItems.push({
			text: BX.util.htmlspecialchars(items[id].TITLE),
			onclick: (function (value)
			{
				return function ()
				{
					var redirect = function()
					{
						window.location.href = BX.util.add_url_param(
							window.location.href.split('#')[0] + '#site' + items[value].ID,
							{
								site: items[value].ID
							}
						);
					};
					if (roleFormFieldsChanged)
					{
						var promise = msg.show({
							content: messages.LANDING_ALERT_CONTENT_RELOADED,
							type: 'confirm'
						});
						promise
							.then(
								function()
								{
									redirect();
								},
								function()
								{
								}
							);
					}
					else
					{
						redirect();
					}
				}
			})(id)
		});
	}

	var popup = new BX.PopupMenuWindow('landing-role-popup', BX('landing-role-add'), menuItems, {
		autoHide : true,
		angle: true,
		className: 'landing-role-popup',
		offsetTop: 0,
		offsetLeft: 0
	});
	popup.show();
}