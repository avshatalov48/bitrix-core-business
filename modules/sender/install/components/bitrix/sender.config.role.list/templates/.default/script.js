;(function ()
{
	BX.namespace('BX.Sender.Role');
	if (BX.Sender.Role.List)
	{
		return;
	}

	var RoleList = function()
	{
	};
	RoleList.prototype = {
		init: function(params)
		{
			this.componentName = params.componentName;
			this.signedParameters = params.signedParameters;
			this.mess = params.mess;
			this.elements = {
				main: BX(params.elementId),
				accessTable: null,
				accessTableBody: null,
				accessTableLastRow: null
			};

			this.elements.accessTable = this.elements.main.querySelector('table.bx-vi-js-role-access-table');
			this.elements.accessTableBody = this.elements.accessTable.querySelector('tbody');
			this.elements.accessTableLastRow = this.elements.main.querySelector('tr.bx-vi-js-access-table-last-row');
			this.bindHandlers();
			BX.Access.Init({other:{disabled:true}});
		},

		bindHandlers: function()
		{
			var deleteRoleNodes = this.elements.main.querySelectorAll('.bx-vi-js-delete-role');
			var deleteAccessNodes = this.elements.main.querySelectorAll('.bx-vi-js-delete-access');
			var addAccessNodes = this.elements.main.querySelectorAll('.bx-vi-js-add-access');
			var selectRoleNodes = this.elements.main.querySelectorAll('.bx-vi-js-select-role');
			for(var i = 0; i < deleteRoleNodes.length; i++)
			{
				deleteRoleNodes[i].removeEventListener('click', this.handleDeleteRoleClick.bind(this));
				deleteRoleNodes[i].addEventListener('click', this.handleDeleteRoleClick.bind(this));
			}

			for(i = 0; i < deleteAccessNodes.length; i++)
			{
				deleteAccessNodes[i].removeEventListener('click', this.handleDeleteAccessClick.bind(this));
				deleteAccessNodes[i].addEventListener('click', this.handleDeleteAccessClick.bind(this));
			}

			for(i = 0; i < addAccessNodes.length; i++)
			{
				addAccessNodes[i].removeEventListener('click', this.handleAddAccessClick.bind(this));
				addAccessNodes[i].addEventListener('click', this.handleAddAccessClick.bind(this));
			}

			for(i = 0; i < selectRoleNodes.length; i++)
			{
				selectRoleNodes[i].removeEventListener('change', this.handleSelectRoleChange.bind(this));
				selectRoleNodes[i].addEventListener('change', this.handleSelectRoleChange.bind(this));
			}
		},

		handleDeleteRoleClick: function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			var element = e.target;
			var roleId = element.dataset.roleId;
			var self = this;
			var elementsToRemove = document.querySelectorAll('*[data-role-id="'+roleId+'"]');

			self.confirm(
				self.mess.delete,
				self.mess.deleteConfirm,
				function(e)
				{
					if(!e.confirmed)
					{
						return;
					}

					BX.ajax.runComponentAction(self.componentName, 'deleteRole', {
						mode: 'class',
						signedParameters: self.signedParameters,
						data: {
							'roleId': roleId
						}
					}).then(function (data) {
						if(!data || data.ERROR)
						{
							self.notify(
								self.mess.error,
								self.mess.errorDelete
							);
							return;
						}
						for(var i = 0; i < elementsToRemove.length; i++)
						{
							BX.remove(elementsToRemove[i]);
						}
					});
				}
			);
		},

		handleAddAccessClick: function()
		{
			var self = this;
			var selectedAccessCodes = {};
			var rowCount = this.elements.accessTable.rows.length;

			for(var i = 0; i < rowCount; i++)
			{
				if(this.elements.accessTable.rows[i].dataset.accessCode)
				{
					selectedAccessCodes[this.elements.accessTable.rows[i].dataset.accessCode] = true;
				}
			}

			BX.Access.SetSelected(selectedAccessCodes, 'imopenlinesPerms');
			BX.Access.ShowForm(
				{
					bind: 'imopenlinesPerms',
					callback: function(data)
					{
						var providerName;
						var accessName;
						for(var provider in data)
						{
							if (!data.hasOwnProperty(provider))
							{
								continue;
							}

							for(var id in data[provider])
							{
								if (!data[provider].hasOwnProperty(id))
								{
									continue;
								}

								providerName = BX.Access.GetProviderName(data[provider][id].provider);
								accessName = data[provider][id].name;
								self.renderNewAccessCode(id, providerName, accessName, 1);
							}
						}
						self.bindHandlers();
					}
				});

		},

		handleDeleteAccessClick: function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			var element = e.target;
			var roleAccessCode = element.dataset.accessCode;
			var elementsToRemove = this.elements.accessTable.querySelectorAll('tr[data-access-code="'+roleAccessCode+'"]');
			for(var i = 0; i < elementsToRemove.length; i++)
			{
				BX.remove(elementsToRemove[i]);
			}
		},

		handleSelectRoleChange: function(e)
		{
			var element = e.target;
			var roleId = element.value;
			var roleAccessCode = element.dataset.accessCode;

			var tableRow = this.elements.main.querySelector('tr[data-access-code='+roleAccessCode+']');
			if(tableRow)
			{
				tableRow.dataset.roleId = roleId;
			}
		},

		renderNewAccessCode: function(accessCode, provider, name, roleId)
		{
			var template = BX('bx-vi-new-access-row').innerHTML;
			template = this.__replaceAll(template, {PROVIDER: provider, NAME: name, ACCESS_CODE: accessCode});
			var newElement = BX.create('tr', {html: template});
			newElement.dataset.roleId = roleId;
			newElement.dataset.accessCode = accessCode;
			newElement.querySelector('select').value = roleId;
			this.elements.accessTableBody.insertBefore(newElement, this.elements.accessTableLastRow);
		},

		confirm: function(title, text, callback)
		{
			var result = {
				confirmed: false
			};

			var popupId = this.elements.main.id + '-confirm-popup';

			var popupWindow = new BX.PopupWindow(popupId, null, {
				content: text,
				titleBar: title,
				closeByEsc: true,
				buttons: [
					new BX.PopupWindowButton({
						text : this.mess.apply,
						className : "popup-window-button-accept",
						events : {
							click : function() {
								popupWindow.close();
								result.confirmed = true;
								if(BX.type.isFunction(callback))
								{
									callback(result);
								}
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : this.mess.cancel,
						className : "popup-window-button-link-cancel",
						events : {
							click : function() {
								popupWindow.close();
								result.confirmed = false;
								if(BX.type.isFunction(callback))
								{
									callback(result);
								}
							}
						}
					})
				]
			});
			popupWindow.show();
		},

		notify: function(title, text, callback)
		{
			var popupId = this.elements.main.id + '-notify-popup';
			var popupWindow = new BX.PopupWindow(popupId, null, {
				content: text,
				titleBar: title,
				closeByEsc: true,
				buttons: [
					new BX.PopupWindowButton({
						text : "Ok",
						className : "popup-window-button-accept",
						events : {
							click : function() {
								popupWindow.close();
								if(BX.type.isFunction(callback))
								{
									callback({});
								}
							}
						}
					})
				]
			});
			popupWindow.show();
		},

		__replaceAll: function(template, data)
		{
			if(!BX.type.isPlainObject(data))
				return template;

			return template.replace(
				/#(\w+?)#/g,
				function(match, variable)
				{
					if(data.hasOwnProperty(variable))
					{
						return data[variable];
					}
					else
					{
						return match;
					}
				}
			);
		}
	};

	BX.Sender.Role.List = new RoleList();

})(window);