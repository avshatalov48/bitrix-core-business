;(function () {

	'use strict';

	BX.namespace('BX.UI');

	BX.UI.AccessRights = function(options) {
		options = options || {};

		this.options = options;
		this.renderTo = options.renderTo;
		this.buttonPanel = BX.UI.ButtonPanel || null;

		this.layout = {
			container: null
		};
		this.component = options.component ? options.component : null;
		this.actionSave = options.actionSave ? options.actionSave : 'save';
		this.actionDelete = options.actionDelete ? options.actionDelete : 'delete';
		this.actionLoad = options.actionLoad ? options.actionLoad : 'load';
		this.mode = options.mode ? options.mode : 'ajax';
		this.openPopupEvent = options.openPopupEvent ? options.openPopupEvent : null;
		this.popupContainer = options.popupContainer ? options.popupContainer :null;
		this.additionalSaveParams = options.additionalSaveParams ? options.additionalSaveParams : null;
		this.loadParams = options.loadParams ? options.loadParams : null;
		this.loader = null;
		this.timer = null;

		this.initData();
		if (options.userGroups)
		{
			this.userGroups = options.userGroups;
		}
		if (options.accessRights)
		{
			this.accessRights = options.accessRights;
		}

		this.isRequested = false;

		this.loadData();
		this.bindEvents();
	};

	BX.UI.AccessRights.prototype = {

		bindEvents: function()
		{
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:updateRole', this.updateRole.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:accessOn', this.updateAccessRight.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:accessOff', this.updateAccessRight.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:update', this.adjustButtonPanel.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:addRole', this.addUserGroup.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:addRole', this.addRoleColumn.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:copyRole', this.addRoleColumn.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:copyRole', this.addUserGroup.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:removeRole', this.removeRoleColumn.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights.ColumnItem:removeRole', this.adjustButtonPanel.bind(this));
			BX.addCustomEvent('BX.Main.SelectorV2:onGetEntityTypes', this.onGetEntityTypes.bind(this));
		},

		initData: function()
		{
			this.accessRights = [];
			this.userGroups = [];
			this.accessRightsSections = [];
			this.headSection = null;
			this.members = [];
			this.columns = [];
		},

		fireEventReset: function()
		{
			BX.onCustomEvent(window, 'BX.UI.AccessRights:reset', this);
		},

		fireEventRefresh: function()
		{
			BX.onCustomEvent(window, 'BX.UI.AccessRights:refresh', this);
		},

		getButtonPanel: function()
		{
			return this.buttonPanel;
		},

		showNotification: function(title)
		{
			BX.UI.Notification.Center.notify({
				content: title,
				position: 'top-right',
				autoHideDelay: 3000,
			});
		},

		sendActionRequest: function ()
		{
			if (this.isRequested)
			{
				return;
			}

			this.isRequested = true;

			this.timer = setTimeout(function() {
				this.blockGrid();
			}.bind(this), 1000);

			var needReload = false;
			var dataToSave = [];
			for(var i = 0; i< this.userGroups.length; i++)
			{
				if (this.userGroups[i].id == 0)
				{
					needReload = true;
				}

				dataToSave.push({
					accessCodes: this.userGroups[i].accessCodes,
					id: this.userGroups[i].id,
					title: this.userGroups[i].title,
					type: this.userGroups[i].type,
					accessRights: this.userGroups[i].accessRights
				})
			}

			BX.ajax.runComponentAction(
				this.component,
				this.actionSave,
				{
					mode: this.mode,
					data: {
						userGroups: dataToSave,
						parameters: this.additionalSaveParams
					},
					// analyticsLabel: {
					// 	viewMode: 'grid',
					// 	filterState: 'closed'
					// }
				}
			).then(function () {
				if (needReload)
				{
					this.reloadGrid();
				}

				this.isRequested = false;
				this.showNotification(BX.message('JS_UI_ACCESSRIGHTS_STTINGS_HAVE_BEEN_SAVED'));
				this.unBlockGrid();
				this.fireEventRefresh();
				setTimeout(function() {
					this.adjustButtonPanel();
				}.bind(this));
				clearTimeout(this.timer);
				this.buttonPanel.getContainer().querySelector('.ui-btn-wait').classList.remove('ui-btn-wait');
			}.bind(this), function () {
				this.isRequested = false;
				this.showNotification('Error message');
				this.unBlockGrid();
				clearTimeout(this.timer);
				this.buttonPanel.getContainer().querySelector('.ui-btn-wait').classList.remove('ui-btn-wait');
			}.bind(this));

			BX.onCustomEvent(window, 'BX.UI.AccessRights:preservation', this);
		},

		lock: function()
		{
			this.getMainContainer().classList.add('--lock');
		},

		unlock: function()
		{
			this.getMainContainer().classList.remove('--lock');
		},

		deleteActionRequest: function (roleId)
		{
			if (this.isRequested)
			{
				return;
			}

			this.isRequested = true;

			this.timer = setTimeout(function() {
				this.blockGrid();
			}.bind(this), 1000);

			BX.ajax.runComponentAction(
				this.component,
				this.actionDelete,
				{
					mode: this.mode,
					data: {
						roleId: roleId
					},
					// analyticsLabel: {
					// 	viewMode: 'grid',
					// 	filterState: 'closed'
					// }
				}
			).then(function () {
				this.isRequested = false;
				this.showNotification(BX.message('JS_UI_ACCESSRIGHTS_ROLE_REMOVE'));
				this.unBlockGrid();
				clearTimeout(this.timer);
			}.bind(this), function () {
				this.isRequested = false;
				this.showNotification('Error message');
				this.unBlockGrid();
				clearTimeout(this.timer);
			}.bind(this));
		},

		reloadGrid: function()
		{
			this.initData();

			BX.ajax.runComponentAction(
				this.component,
				this.actionLoad,
				{
					mode: this.mode,
					data: {
						parameters: this.loadParams
					},
				}
			).then(function (response) {
				if (
					response.data['ACCESS_RIGHTS']
					&& response.data['USER_GROUPS']
				) {
					this.accessRights = response.data.ACCESS_RIGHTS;
					this.userGroups = response.data.USER_GROUPS;
					this.loadData();
					this.draw();
				}
				this.unBlockGrid();
			}.bind(this), function () {
				this.unBlockGrid();
			}.bind(this));
		},

		blockGrid: function()
		{
			var offsetTop = this.layout.container.getBoundingClientRect().top < 0 ? '0' : this.layout.container.getBoundingClientRect().top;

			this.layout.container.classList.add('ui-access-rights-block');
			this.layout.container.style.height = 'calc(100vh - ' + offsetTop  + 'px)';

			setTimeout(function() {
				this.layout.container.style.height = 'calc(100vh - ' + offsetTop  + 'px)';
			}.bind(this));

			var self = this;
			this.getLoader().then(function()
			{
				self.loader.show();
			});
		},

		unBlockGrid: function()
		{
			this.layout.container.classList.remove('ui-access-rights-block');
			this.layout.container.style.height = null;

			var self = this;
			this.getLoader().then(function()
			{
				self.loader.hide();
			});
		},

		loadLoaderExtension: function()
		{
			return new Promise(function(resolve)
			{
				if(BX.Loader)
				{
					return resolve();
				}

				BX.loadExt("main.loader").then(function()
				{
					resolve();
				})
			});
		},

		getLoader: function()
		{
			var self = this;
			return new Promise(function(resolve)
			{
				self.loadLoaderExtension().then(function()
				{
					if(!self.loader)
					{
						self.loader = new BX.Loader({
							target: self.layout.container
						});
					}
					resolve();
				});
			})
		},

		removeRoleColumn: function(param)
		{
			this.headSection.removeColumn(param.data);
			this.accessRightsSections.map(function(data) {
				data.removeColumn(param.data);
			});

			var targetIndex = this.userGroups.indexOf(param.data.userGroup);
			this.userGroups.splice(targetIndex, 1);

			var roleId = param.data.userGroup.id;
			this.deleteActionRequest(roleId);
		},

		addRoleColumn: function(param)
		{
			if(!param)
			{
				return;
			}

			var sections = this.accessRightsSections;

			for (var i = 0; i < sections.length; i++)
			{
				param.headSection = false;
				param.newColumn = true;
				sections[i].addColumn(param);
				sections[i].scrollToRight(sections[i].getColumnsContainer().scrollWidth - sections[i].getColumnsContainer().offsetWidth, 'stop');
			}

			param.headSection = true;
			param.newColumn = true;
			this.headSection.addColumn(param);
			// this.userGroups.push(param);
		},

		addUserGroup: function(options)
		{
			options = options || {};
			this.userGroups.push(options);
		},

		updateRole: function(options)
		{
			var index = this.userGroups.indexOf(options.data.userGroup);
			if(index >= 0)
			{
				this.userGroups[index].title = options.data.text;
			}
		},

		adjustButtonPanel: function()
		{
			var modifiedItems = this.getMainContainer().querySelectorAll('.ui-access-rights-column-item-changer-on');
			var modifiedRoles = this.getMainContainer().querySelectorAll('.ui-access-rights-column-new');
			var modifiedUsers = this.getMainContainer().querySelectorAll('.ui-access-rights-members-item-new');

			if(modifiedItems.length > 0 || modifiedRoles.length > 0 || modifiedUsers.length > 0)
			{
				this.buttonPanel.show();
			}
			else
			{
				this.buttonPanel.hide();
			}
		},

		updateAccessRight: function(options)
		{
			var data = options.data;
			var userGroup = this.userGroups[this.userGroups.indexOf(data.userGroup)];
			var accessId = data.access.id;

			for (var i = 0; i < userGroup.accessRights.length; i++)
			{
				var item = userGroup.accessRights[i];
				if(item.id === accessId)
				{
					(item.value === '0') ? item.value = '1' : item.value = '0';

					return;
				}
			}

			userGroup.accessRights.push({
				id: accessId,
				value: data.switcher.checked ? '1' : '0'
			});
		},

		loadData: function()
		{
			this.accessRights.map(function(data, index) {
				data.id = index;
				this.accessRightsSections.push(this.addSection(data));
			}.bind(this));
		},

		getColumns: function()
		{
			return this.columns;
		},

		getSections: function()
		{
			return this.accessRightsSections;
		},

		getUserGroups:  function()
		{
			this.userGroups.forEach(function(item) {
				if(item.accessCodes)
				{
					for(var user in item.members)
					{
						item.accessCodes[user] = item.members[user].type
					}
				}
			});

			return this.userGroups;
		},

		getHeadSection: function()
		{
			if(!this.headSection)
			{
				this.headSection = new BX.UI.AccessRights.Section({
					headSection: true,
					userGroups: this.userGroups,
					grid: this
				});
			}

			return this.headSection;
		},

		addSection: function(options)
		{
			options = options || {};
			return new BX.UI.AccessRights.Section({
				id: options.id,
				title: options.sectionTitle,
				rights: options.rights ? options.rights : [],
				grid: this
			});
		},

		getSection: function()
		{
			return BX.create('div', {
				props: {
					className: 'ui-access-rights-section'
				}
			});
		},

		getMainContainer: function()
		{
			if(!this.layout.container)
			{
				this.layout.container = BX.create('div', {
					props: {
						className: 'ui-access-rights'
					}
				});
			}

			return this.layout.container;
		},

		draw: function()
		{
			var docFragmentSections = document.createDocumentFragment();
			docFragmentSections.appendChild(this.getHeadSection().render());

			this.getSections().map(function(data) {
				docFragmentSections.appendChild(data.render())
			}.bind(this));

			this.layout.container = null;
			this.getMainContainer().appendChild(docFragmentSections);

			this.renderTo.innerHTML = '';
			this.renderTo.appendChild(this.getMainContainer());
			this.afterRender();
		},

		afterRender: function()
		{
			this.getHeadSection().adjustEars();
			this.getSections().map(function(data) {
				data.adjustEars();
			});
		},

		onMemberSelect: function(params)
		{
			var option = BX.UI.AccessRights.buildOption(params);
			if(!option)
			{
				return;
			}

			if(params.state === 'select')
			{
				BX.onCustomEvent('BX.UI.AccessRights:addToAccessCodes', option);
			}
		},

		onMemberUnselect: function(params)
		{
			var option = BX.UI.AccessRights.buildOption(params);

			if(!option)
			{
				return;
			}

			BX.onCustomEvent('BX.UI.AccessRights:removeFromAccessCodes', option);
		},

		onGetEntityTypes: function()
		{
			var controls = BX.Main
				.selectorManagerV2.controls;
			var selectorInstance = controls[Object.keys(controls)[0]];

			selectorInstance.entityTypes.USERGROUPS = {
				options: {
					enableSearch: 'Y',
					searchById: 'Y',
					addTab: 'Y',
					returnItemUrl: (selectorInstance.getOption('returnItemUrl') == 'N' ? 'N' : 'Y')
				}
			};
		}
	};

	BX.UI.AccessRights.buildOption = function(params)
	{
		var controls = BX.Main
			.selectorManagerV2.controls;
		var selectorInstance = controls[Object.keys(controls)[0]].selectorInstance;
		var dataColumnAttribute = 'bx-data-column-id';

		var node = selectorInstance.bindOptions.node;

		if(!node.hasAttribute(dataColumnAttribute) || typeof params.item === 'undefined')
		{
			return false;
		}

		var columnId =  node.getAttribute(dataColumnAttribute);

		var accessItem = params.item.id;
		var entityType = params.entityType;
		var accessCodesResult =  {};
		accessCodesResult[accessItem] = entityType;

		return {
			accessCodes: accessCodesResult,
			columnId: columnId,
			item: params.item
		};
	}

})();
