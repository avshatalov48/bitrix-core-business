landingAccessExtendedSelected = {};

function deleteAccessRow(link)
{
	landingAccessSelected[BX.data(BX(link), 'id')] = false;
	BX.remove(BX.findParent(BX(link), {tag: 'tr'}, true));
}

function deleteAccessRowExtended(link)
{
	var code = BX.data(BX(link), 'code');
	if (typeof landingAccessExtendedSelected[code] !== 'undefined')
	{
		var id = BX.data(BX(link), 'id');
		landingAccessExtendedSelected[code][id] = false;
	}
	BX.remove(BX.findParent(BX(link), {tag: 'div'}, true));
}

(function() {

	'use strict';

	BX.namespace('BX.Landing');

	/**
	 * Rights in role mode.
	 */
	BX.Landing.Access = function(params)
	{
		// init vars
		var selected = landingAccessSelected;
		var name = 'rights';
		var tbl = BX('landing-' + name + '-table');
		var select = params.select;
		var inc = params.inc;

		// access init
		BX.Access.Init({
			other: {
				disabled_cr: true
			}
		});
		BX.Access.SetSelected(
			selected,
			name
		);

		// show form
		function showForm()
		{
			BX.Access.ShowForm(
			{
				callback: function(obSelected)
				{
					for (var provider in obSelected)
					{
						if (obSelected.hasOwnProperty(provider))
						{
							for (var id in obSelected[provider])
							{
								if (obSelected[provider].hasOwnProperty(id))
								{
									// build table row
									var cnt = tbl.rows.length;
									var row = tbl.insertRow(cnt-1);

									selected[id] = true;
									row.insertCell(-1);
									row.insertCell(-1);
									row.insertCell(-1);
									row.insertCell(-1);

									row.cells[0].classList.add('table-blue-td-name');
									row.cells[1].classList.add('table-blue-td-param');
									row.cells[2].classList.add('table-blue-td-select');
									row.cells[3].classList.add('table-blue-td-action');

									row.cells[0].innerHTML = BX.Access.GetProviderName(provider);
									row.cells[1].textContent = obSelected[provider][id].name;
									row.cells[2].innerHTML = '<input type="hidden" name="' + name + '[ACCESS_CODE][]" value="' + id + '">' +
															select.replace('#inc#', inc++);
									row.cells[3].innerHTML = '<span onclick="deleteAccessRow(this);" data-id="' + id + '" class="table-blue-delete table-blue-delete-landing-role bitrix24-metrika" data-metrika24="permission_delete"></span>';
								}
							}
						}
					}
				},
				bind: name
			});
		}

		// bind for show form
		BX('landing-rights-form').addEventListener(
			'click',
			showForm.bind(this)
		);

		// remove roles
		var fields = BX.findChild(
			BX('landing-roles'),
			{
				class: 'landing-role-delete'
			},
			true,
			true
		);
		fields.forEach(function(element){
			BX(element).addEventListener(
				'click',
				function()
				{
					BX.remove(
						BX.findParent(
							BX(this),
							{tag: 'tr'},
							true
						)
					);
				}
			);
		});

		// etended mode
		BX.bind(
			BX('landing-mode-form-submit'),
			'click',
			function()
			{
				BX('landing-mode-form').submit();
			}
		);
	};

	/**
	 * Rights in extended mode.
	 */
	BX.Landing.AccessExtended = function(id)
	{
		function showForm(rightId, selected)
		{
			var name = 'rights';

			// init access
			BX.Access.bInit = false;
			BX.Access.Init({
				other: {
					disabled_cr: true
				}
			});

			// set selected ids
			if (typeof landingAccessExtendedSelected[rightId] === 'undefined')
			{
				landingAccessExtendedSelected[rightId] = selected;
			}
			else
			{
				for (var index in selected)
				{
					if (typeof landingAccessExtendedSelected[rightId][index] === 'undefined')
					{
						landingAccessExtendedSelected[rightId][index] = selected[index];
					}
				}
			}
			BX.Access.SetSelected(
				landingAccessExtendedSelected[rightId],
				name
			);

			// show form
			BX.Access.ShowForm(
				{
					callback: function(obSelected)
					{
						for (var provider in obSelected)
						{
							if (obSelected.hasOwnProperty(provider))
							{
								for (var id in obSelected[provider])
								{
									if (obSelected[provider].hasOwnProperty(id))
									{
										landingAccessExtendedSelected[rightId][id] = true;
										var html = '';
										var providerType = BX.Access.GetProviderName(provider);

										html = '<div class="landing-role-users">';
										html += '<input type="hidden" name="' + name + '[' + rightId + '][]" value="' + id + '">';
										html += (providerType !== '') ? providerType + ': ' : '';
										html += obSelected[provider][id].name;
										html += '<span onclick="deleteAccessRowExtended(this);" data-code="' + rightId + '" data-id="' + id + '" class="table-blue-delete table-blue-delete-landing-role bitrix24-metrika" data-metrika24="permission_delete"></span>';
										html += '</div>';
										BX('landing-additional-rights-fields-' + rightId).innerHTML += html;
									}
								}
							}
						}
					},
					bind: name
				}
			);
		}

		// set additional rights
		var fields = BX.findChild(
			BX('landing-additional-rights-table'),
			{
				class: 'landing-additional-rights-form'
			},
			true,
			true
		);
		fields.forEach(function(element){
			BX(element).addEventListener(
				'click',
				function()
				{
					var selected = {};
					var codes = BX.data(BX(element), 'codes').split(',');
					for (var i = 0, c = codes.length; i < c; i++)
					{
						selected[codes[i]] = true;
					}
					showForm(
						BX.data(BX(element), 'id'),
						selected
					);
				}.bind(this)
			);
		});
	};

})();