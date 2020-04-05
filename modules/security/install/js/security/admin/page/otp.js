(function initialize(BX)
{
	function initOtpAdminPage()
	{
		var settings = BX('settings');
		if (!settings)
			return;

		settings = JSON.parse(settings.innerHTML);

		BX.Access.Init({
			other: {disabled:true}
		});

		BX.Access.SetSelected(settings['rights']);

		BX.bind(
			BX('otp_mandatory_using'),
			'click',
			onSwitchMandatory.bind(BX('otp_mandatory_using'))
		);

		BX.bind(
			BX('add_access'),
			'click',
			BX.Access.ShowForm.bind(null, {
				callback: function(obSelected) {
					insertAccess(obSelected)
				}
			})
		);

		[].map.call(
			window.document.querySelectorAll('a[data-role="delete-access"]'),
			function bindDeleteClick(el)
			{
				BX.bind(
					el,
					'click',
					onDeleteAccess.bind(el)
				);
			}
		);
	}

	function onSwitchMandatory()
	{
		var isActive = this.checked;

		var hiddenElements = document.body.querySelectorAll('[data-hide-by-mandatory="yes"]');
		[].forEach.call(
			hiddenElements,
			function switchHidden(element)
			{
				if (isActive)
					element.style.display = '';
				else
					element.style.display = 'none';
			}
		)
	}

	function insertAccess(rights)
	{
		var div = BX('bx_access_div');
		for(var provider in rights)
		{
			if (!rights.hasOwnProperty(provider))
				continue;

			for(var id in rights[provider])
			{
				if (!rights[provider].hasOwnProperty(id))
					continue;

				var pr = BX.Access.GetProviderPrefix(provider, id);
				var newDiv = BX.create('div', {
					'style': {
						'marginBottom': '4px'
					},
					'children': [
						BX.create('input', {
							'props': {
								'type': 'hidden',
								'name': 'otp_mandatory_rights[]',
								'value': id
							}
						}),
						BX.create('span', {
							'text': (pr? pr+': ':'') + rights[provider][id].name + ' '
						})
					]
				});

				var newA =  BX.create('a', {
					'props': {
						'href': 'javascript:void(0);',
						'className': 'access-delete'
					}
				});

				BX.bind(
					newA,
					'click',
					onDeleteAccess.bind(newA, id)
				);
				newDiv.appendChild(newA);
				div.appendChild(newDiv);
			}
		}
	}

	function onDeleteAccess(code)
	{
		var div = BX.findParent(this, {'tag':'div'});
		div.parentNode.removeChild(div);
		if (code)
			BX.Access.DeleteSelected(code);
		else
			BX.Access.DeleteSelected(this.getAttribute('data-code'));
	}

	BX.ready(initOtpAdminPage);

})(BX);