;(function () {

'use strict';

BX.namespace('BX.UI');

BX.UI.AccessRights.ColumnItem = function(options) {
	this.grid = options.grid ? options.grid : null;
	this.id = options.id ? options.id : null;
	this.type = options.type ? options.type : null;
	this.text = options.text ? options.text : null;
	this.hint = options.hint ? options.hint : null;
	this.variables = options.variables ? options.variables : [];
	this.userGroup = options.userGroup;
	this.access = options.access ? options.access : null;
	this.currentParam = options.currentParam;
	this.controller = options.controller ? options.controller : null;
	this.openPopupEvent = options.openPopupEvent;
	this.popupContainer = options.popupContainer;
	this.accessCodes = options.accessCodes ? options.accessCodes : [];
	this.isModify = false;
	this.popupHelper = null;
	this.popupHint = null;
	this.popupTimer = null;
	this.popupConfirm = null;
	this.popupUsers = null;
	this.identificator = 'col-' + Math.random();
	this.updatedUsers = [];

	this.layout = {
		container: null,
		variablesValue: null,
		role: null,
		roleInput: null,
		roleValue: null,
		changer: null,
		switcher: null,
		controller: null,
		controllerMenu: null,
		controllerLink: null,
		addUserToRole: null,
		members: null
	};

	this.column = options.column;
	this.popupMenu = null;
	this.switcher = null;

	this.bindEvents();
};



BX.UI.AccessRights.ColumnItem.prototype = {
	bindEvents: function()
	{
		if(this.type === 'role')
		{
			BX.bind(window, 'click', function(ev) {
				if(ev.target === this.layout.role ||
					BX.findParent(ev.target, {
						className: 'ui-access-rights-role'
					}))
				{
					return;
				}

				this.updateRole();
				this.offRoleEditMode();
			}.bind(this));
		}

		if(this.type === 'toggler')
		{
			BX.addCustomEvent('BX.UI.AccessRights:reset', this.offChanger.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights:refresh', this.refreshStatus.bind(this));
		}

		if(this.type === 'members')
		{
			BX.addCustomEvent('BX.UI.AccessRights:addToAccessCodes', this.addToAccessCodes.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights:removeFromAccessCodes', this.removeFromAccessCodes.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights:reset', this.resetNewMembers.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights:refresh', this.resetNewMembers.bind(this));
		}
	},

	getTextNode: function()
	{
		return BX.create('div', {
			props: {
				className: 'ui-access-rights-column-item-text',
			},
			attrs: {
				'data-id': this.id
			},
			text: this.text,
			events: {
				mouseenter: function()
				{
					if(this.type === 'title')
					{
						this.adjustPopupHelper();
					}
				}.bind(this),
				mouseleave: function()
				{
					if(this.popupHelper)
					{
						this.popupHelper.close();
					}

					if(this.popupTimer)
					{
						clearTimeout(this.popupTimer);
					}
				}.bind(this)
			}
		});
	},

	getHint: function()
	{
		if(!this.layout.hint && this.hint)
		{
			this.popupHint = BX.PopupWindowManager.create(null, null, {
				className: 'ui-access-rights-popup-pointer-events',
				autoHide: true,
				darkMode: true,
				content: this.hint,
				maxWidth: 280,
				offsetTop: 0,
				offsetLeft: 8,
				angle: true,
				animation: 'fading-slide'
			});

			this.layout.hint = new BX.create('a', {
				props: {
					className: 'ui-access-rights-column-item-notify'
				},
				events: {
					mouseover: function () {
						this.popupHint.setBindElement(this.layout.hint);
						this.popupHint.show();
					}.bind(this),
					mouseleave: function () {
						this.popupHint.close();
					}.bind(this)
				}
			});
		}

		return this.layout.hint;
	},

	getChanger: function()
	{
		if(!this.layout.changer)
		{
			this.layout.changer = BX.create('div', {
				props: {
					className: 'ui-access-rights-column-item-changer'
				}
			})
		}

		return this.layout.changer;
	},

	adjustChanger: function()
	{
		if(!this.isModify)
		{
			this.isModify = true;
		}
		else
		{
			this.isModify = false;
		}

		if(this.layout.changer)
		{
			this.layout.changer.classList.toggle('ui-access-rights-column-item-changer-on');
		}
	},

	refreshStatus: function()
	{
		this.layout.changer.classList.remove('ui-access-rights-column-item-changer-on');
	},

	offChanger: function()
	{
		if(this.isModify)
		{
			this.layout.changer.classList.remove('ui-access-rights-column-item-changer-on');

			if(this.isModify)
			{
				this.switcher.isChecked() ? this.switcher.check(false) : this.switcher.check(true);
			}

			setTimeout(function()
			{
				this.layout.changer.classList.remove('ui-access-rights-column-item-changer-on');
			}.bind(this))
		}
	},

	addToAccessCodes: function(event)
	{
		var params = event.data;

		if(params.columnId !== this.identificator)
		{
			return;
		}

		var key = Object.keys(params.accessCodes)[0];
		var type = params.accessCodes[key].toUpperCase();
		this.userGroup.accessCodes = Object.keys(this.accessCodes);

		var item = params.item;

		if(typeof item !== 'undefined' && Object.keys(item).length)
		{
			this.userGroup.members[key] = {
				id: item.entityId,
				name: item.name,
				avatar: item.avatar,
				url: '',
				new: true,
				type: type.toLowerCase()
			};

			this.updateMembers();
		}

		this.userGroup.accessCodes = [];

		for(var key in this.userGroup.members)
		{
			this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
		}
	},

	removeFromAccessCodes: function(event)
	{
		var params = event.data;

		if(params.columnId !== this.identificator)
		{
			return;
		}

		var key = Object.keys(params.accessCodes)[0];

		delete this.userGroup.members[key];
		this.updateMembers();

		this.userGroup.accessCodes = [];

		for(var key in this.userGroup.members)
		{
			this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
		}
	},

	getToggler: function()
	{
		if(!this.switcher)
		{
			var item = this;

			this.switcher = new BX.UI.Switcher(
				{
					size: 'small',
					checked: this.currentParam === '1',
					handlers: {
						checked: function()
						{
							BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:accessOn', item);
						},
						unchecked: function()
						{
							BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:accessOff', item);
						},
						toggled: function()
						{
							this.adjustChanger();
							BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:update', item);
						}.bind(this)
					}
				}
			);
		}

		return this.switcher.getNode();
	},

	getVariables: function()
	{
		if(!this.layout.variablesValue)
		{
			this.layout.variablesValue = BX.create('div', {
				props: {
					className: 'ui-access-rights-column-item-text-link'
				},
				text: this.variables[0].title,
				events: {
					click: this.showVariablesPopup.bind(this)
				}
			})
		}

		return this.layout.variablesValue;
	},

	getController: function()
	{
		if(!this.layout.controller)
		{
			this.layout.controller = BX.create('div', {
				props: {
					className: 'ui-access-rights-column-item-controller'
				},
				children: [
					this.layout.controllerLink = BX.create('div', {
						props: {
							className: 'ui-access-rights-column-item-controller-link'
						},
						text: BX.message('JS_UI_ACCESSRIGHTS_CREATE_ROLE'),
						events: {
							click: function() {
								BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:addRole', [
									{
										id: '0',
										title: BX.message('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
										accessRights: [],
										members: [],
										accessCodes: [],
										type: 'role'
									}
								]);
								BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:update', this);
							}.bind(this)
						}
					}),
					// or arrow for link 'ui-access-rights-column-item-controller-menu'
					this.layout.controllerMenu = BX.create('div', {
						props: {
							className: 'ui-access-rights-column-item-controller-link'
						},
						text: BX.message('JS_UI_ACCESSRIGHTS_COPY_ROLE'),
						events: {
							click: function() {
								this.getPopupMenu(this.grid.getUserGroups()).show();
							}.bind(this)
						}
					})
				],
			});
		}

		return this.layout.controller;
	},

	getPopupMenu: function(options)
	{
		if(!options)
		{
			return;
		}

		var menuItems = [];

		options.map(function(data) {
			menuItems.push({
				text: BX.util.htmlspecialchars(data.title),
				onclick: function() {
					var accessRightsCopy = Object.assign([], data.accessRights);
					var accessCodesCopy =  Object.assign([], data.accessCodes);

					BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:copyRole', [
						{
							id: '0',
							title: BX.message('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
							accessRights: accessRightsCopy,
							accessCodes: accessCodesCopy,
							type: 'role',
							members: data.members
						}
					]);

					BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:update', this);
					this.popupMenu.destroy();
				}.bind(this)
			});
		}.bind(this));

		return this.popupMenu = new BX.PopupMenuWindow(
			null,
			this.layout.controllerMenu,
			menuItems,
			{
				events: {
					onPopupClose: function() {
						this.popupMenu.destroy();
						this.popupMenu = null;
					}.bind(this)
				}
			}
		);
	},

	showVariablesPopup: function()
	{
		var menuItems = [];

		this.variables.map(function(data) {
			menuItems.push({
				id: data.id,
				text: data.title
			});
		});

		BX.PopupMenu.show(
			'ui-access-rights-column-item-popup-variables',
			this.layout.variablesValue,
			menuItems,
			{
				autoHide: true,
				events : {
					onPopupClose: function() {
						BX.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
					}.bind(this)
				}
			}
		);
	},

	getRole: function()
	{
		if(!this.layout.role)
		{
			BX.addCustomEvent('BX.UI.AccessRights:preservation', this.updateRole.bind(this));
			BX.addCustomEvent('BX.UI.AccessRights:preservation', this.offRoleEditMode.bind(this));

			this.layout.role = BX.create('div', {
				props: {
					className: 'ui-access-rights-role'
				},
				children: [
					this.layout.roleInput = BX.create('input', {
						props: {
							type: 'text',
							className: 'ui-access-rights-role-input',
							value: this.text,
							placeholder: BX.message('JS_UI_ACCESSRIGHTS_ROLE_NAME')
						},
						events: {
							keydown: function(ev) {
								// Enter = keyCode 13
								if(ev.keyCode === 13)
								{
									this.updateRole();
									this.offRoleEditMode();
								}
							}.bind(this),
							input: function() {
								this.grid.getButtonPanel().show();
							}.bind(this)
						}
					}),
					this.layout.roleValue = BX.create('div', {
						props: {
							className: 'ui-access-rights-role-value',
							innerText: this.text
						},
					}),
					BX.create('div', {
						props: {
							className: 'ui-access-rights-role-controls'
						},
						children: [
							BX.create('div', {
								props: {
									className: 'ui-access-rights-role-edit'
								},
								events: {
									click: this.onRoleEditMode.bind(this)
								}
							}),
							BX.create('div', {
								props: {
									className: 'ui-access-rights-role-remove'
								},
								events: {
									click: this.showPopupConfirm.bind(this)
								}
							})
						]
					})
				]
			});
		}

		return this.layout.role;
	},

	showPopupConfirm: function()
	{
		var self = this;

		if(!this.popupConfirm)
		{

			this.popupConfirm = BX.PopupWindowManager.create(null, this.layout.container, {
				width: 250,
				overlay: true,
				contentPadding: 10,
				content: BX.message('JS_UI_ACCESSRIGHTS_POPUP_REMOVE_THIS_ROLE'),
				animation: 'fading-slide'
			});

			this.popupConfirm.setButtons([
				new BX.PopupWindowCustomButton({
					text: BX.message('JS_UI_ACCESSRIGHTS_POPUP_REMOVE'),
					className: 'ui-btn ui-btn-sm ui-btn-primary',
					events: {
						click: function() {
							self.popupConfirm.close();
							BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:removeRole', self);
						}
					}
				}),
				new BX.PopupWindowCustomButton({
					text: BX.message('JS_UI_ACCESSRIGHTS_POPUP_CANCEL'),
					className: 'ui-btn ui-btn-sm ui-btn-link',
					events: {
						click: function() {
							self.popupConfirm.close();
						}
					}
				})
			]);
		}

		this.popupConfirm.show();
	},

	onRoleEditMode: function()
	{
		this.layout.role.classList.add('ui-access-rights-role-edit-mode');
		this.layout.roleInput.focus();
	},

	updateRole: function()
	{
		if(
			this.layout.roleValue.innerHTML === this.layout.roleInput.value ||
			this.layout.roleInput.value === '')
		{
			return;
		}

		this.text = this.layout.roleInput.value;
		this.userGroup = this.column.getUserGroup();

		this.layout.roleValue.innerText = this.layout.roleInput.value;
		BX.onCustomEvent(window, 'BX.UI.AccessRights.ColumnItem:updateRole', this);
	},

	offRoleEditMode: function()
	{
		this.layout.role.classList.remove('ui-access-rights-role-edit-mode');
	},

	validateVariables: function()
	{
		if(typeof this.userGroup.accessCodes === 'undefined')
		{
			this.userGroup.accessCodes = [];
		}
	},

	getMembers: function()
	{
		if(!this.layout.members)
		{
			var members = this.userGroup.members || {};
			var membersFragment = document.createDocumentFragment();
			var counter = 0;
			this.validateVariables();

			Object.keys(members).reverse().forEach(function(item) {
				counter++;
				if(counter < 7)
				{
					var user = members[item];

					var classNew = user.new ? ' ui-access-rights-members-item-new' : '';
					var avatarClass = ' ui-icon-common-user';

					if(user.type === 'groups')
					{
						avatarClass = ' ui-icon-common-user-group';
					}

					if(user.type === 'sonetgroups')
					{
						avatarClass = ' ui-icon-common-company';
					}

					if(user.type === 'usergroups')
					{
						avatarClass = ' ui-icon-common-user-group';
					}

					membersFragment.appendChild(BX.create('div', {
						props: {
							className: 'ui-access-rights-members-item' + classNew
						},
						children: [
							user.avatar ? BX.create('a', {
								props: {
									className: 'ui-access-rights-members-item-avatar',
									title: user.name
								},
								style: {
									backgroundImage: 'url(' + user.avatar + ')',
									backgroundSize: 'cover'
								}
							}) : null,
							!user.avatar ? BX.create('a', {
								props: {
									className: 'ui-icon ui-icon-xs' + avatarClass,
									title: user.name,
									innerHTML: '<i></i>'
								},
							}) : null,
						]
					}));
				}
			});

			this.getAddUserToRole();

			membersFragment.appendChild(this.layout.addUserToRole);

			this.layout.members = BX.create('div', {
				props: {
					className: 'ui-access-rights-members'
				},
				children: [
					membersFragment
				],
				events: {
					click: this.adjustPopupUserControl.bind(this)
				}
			});
		}

		return this.layout.members;
	},

	resetNewMembers: function()
	{
		var newMembers = this.layout.members.querySelectorAll('.ui-access-rights-members-item-new');

		newMembers.forEach(function(item) {
			item.classList.remove('ui-access-rights-members-item-new');
		})
	},

	adjustPopupUserControl: function()
	{
		var users = [];
		var groups = [];
		var departments = [];
		var sonetgroups = [];

		for (var item in this.userGroup.members)
		{
			this.userGroup.members[item].key = item;

			if (this.userGroup.members[item].type === 'users')
			{
				users.push(this.userGroup.members[item]);
			}

			if (this.userGroup.members[item].type === 'groups')
			{
				groups.push(this.userGroup.members[item]);
			}

			if (this.userGroup.members[item].type === 'usergroups')
			{
				groups.push(this.userGroup.members[item]);
			}

			if (this.userGroup.members[item].type === 'departments')
			{
				departments.push(this.userGroup.members[item]);
			}

			if (this.userGroup.members[item].type === 'sonetgroups')
			{
				sonetgroups.push(this.userGroup.members[item]);
			}
		}

		var counterUsers = [];

		for(var key in this.userGroup.members)
		{
			counterUsers.push(this.userGroup.members[key])
		}

		if (counterUsers.length === 0)
		{
			this.showUserSelectorPopup();
			return;
		}

		this.getUserPopup(users, groups, departments, sonetgroups).show();
	},

	getAddUserToRole: function()
	{
		if(!this.layout.addUserToRole)
		{
			this.layout.addUserToRole = BX.create('span', {
				props: {
					className: 'ui-access-rights-members-item ui-access-rights-members-item-add',
				},
				attrs: { 'bx-data-column-id': this.identificator }
			});
		}

		return this.layout.addUserToRole;
	},

	updateMembers: function()
	{
		this.layout.members.parentNode.removeChild(this.layout.members);
		this.layout.members = null;

		this.layout.container.appendChild(this.getMembers());
		this.grid.getButtonPanel().show();
	},

	getUserPopupTogglerGroup: function(array, type)
	{
		var node = BX.create('div', {
			props: {
				className: 'ui-access-rights-popup-toggler-content ui-access-rights-popup-toggler-content-' + type
			}
		});

		var self = this;

		var iconClass = '';

		if(type === 'users')
		{
			iconClass = 'ui-icon-common-user';
		}

		if(type === 'groups')
		{
			iconClass = 'ui-icon-common-user-group';
		}

		if(type === 'sonetgroups' || type === 'departments')
		{
			iconClass = ' ui-icon-common-company';
		}

		array.forEach(function(item) {
			node.appendChild(BX.create('div', {
				props: {
					className: 'ui-access-rights-popup-toggler-content-item'
				},
				children: [
					item.avatar ? BX.create('a', {
						props: {
							className: 'ui-access-rights-popup-toggler-content-item-userpic',
							title: item.name
						},
						style: {
							backgroundImage: 'url(' + item.avatar + ')',
							backgroundSize: 'cover'
						}
					}) : null,
					!item.avatar ? BX.create('a', {
						props: {
							className: 'ui-icon ui-icon-sm ' + iconClass,
							title: item.name,
							innerHTML: '<i></i>'
						},
						style: {
							margin: '5px 10px'
						}
					}) : null,
					BX.create('div', {
						props: {
							className: 'ui-access-rights-popup-toggler-content-item-name'
						},
						text: item.name
					}),
					BX.create('div', {
						props: {
							className: 'ui-access-rights-popup-toggler-content-item-remove'
						},
						text: BX.message('JS_UI_ACCESSRIGHTS_REMOVE'),
						events: {
							click: function() {
								self.userGroup.accessCodes.splice(self.userGroup.accessCodes.indexOf(item.key), 1);

								delete self.userGroup.accessCodes[item.key];
								delete self.userGroup.members[item.key];

								var parent = BX.findParent(this, {
									className: 'ui-access-rights-popup-toggler-content'
								});

								parent.removeChild(this.parentNode);
								self.updateMembers();

								self.grid.getButtonPanel().show();
							}
						}
					})
				]
			}))
		});

		return node;
	},

	getUserPopup: function(users, groups, departments, sonetgroups)
	{
		if(!this.popupUsers)
		{
			users = users || [];
			groups = groups || [];
			departments = departments || [];
			sonetgroups = sonetgroups || [];

			BX.create('div', {
				props: {
					className: 'ui-access-rights-popup-toggler-content-item'
				}
			});

			var content = BX.create('div', {
				props: {
					className: 'ui-access-rights-popup-toggler'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'ui-access-rights-popup-toggler-title'
						},
						children: [
							groups.length > 0 ?
							BX.create('div', {
								props: {
									className: 'ui-access-rights-popup-toggler-title-item ui-access-rights-popup-toggler-title-item-active',
								},
								attrs: {
									'data-role': 'ui-access-rights-popup-toggler-content-groups'
								},
								text: BX.message('JS_UI_ACCESSRIGHTS_USER_GROUPS'),
								events: {
									click: function() {
										activate(this);
										adjustSlicker(this);
									}
								}
							}) : null,
							departments.length > 0 ?
							BX.create('div', {
								props: {
									className: 'ui-access-rights-popup-toggler-title-item',
								},
								attrs: {
									'data-role': 'ui-access-rights-popup-toggler-content-departments'
								},
								text: BX.message('JS_UI_ACCESSRIGHTS_DEPARTMENTS'),
								events: {
									click: function() {
										activate(this);
										adjustSlicker(this);
									}
								}
							}) : null,
							users.length > 0 ?
							BX.create('div', {
								props: {
									className: 'ui-access-rights-popup-toggler-title-item',
								},
								attrs: {
									'data-role': 'ui-access-rights-popup-toggler-content-users'
								},
								text: BX.message('JS_UI_ACCESSRIGHTS_STAFF'),
								events: {
									click: function() {
										activate(this);
										adjustSlicker(this);
									}
								}
							}) : null,
							sonetgroups.length > 0 ?
								BX.create('div', {
									props: {
										className: 'ui-access-rights-popup-toggler-title-item',
									},
									attrs: {
										'data-role': 'ui-access-rights-popup-toggler-content-sonetgroups'
									},
									text: BX.message('JS_UI_ACCESSRIGHTS_SOCNETGROUP'),
									events: {
										click: function() {
											activate(this);
											adjustSlicker(this);
										}
									}
								}) : null,
							BX.create('div', {
								props: {
									className: 'ui-access-rights-popup-toggler-title-slicker'
								}
							}),
						]
					}),
					groups.length > 0 ? this.getUserPopupTogglerGroup(groups, 'groups') : null,
					departments.length > 0 ? this.getUserPopupTogglerGroup(departments, 'departments') : null,
					users.length > 0 ? this.getUserPopupTogglerGroup(users, 'users') : null,
					sonetgroups.length > 0 ? this.getUserPopupTogglerGroup(sonetgroups, 'sonetgroups') : null,
					BX.create('div', {
						props: {
							className: 'ui-access-rights-popup-toggler-footer'
						},
						children: [
							BX.create('div', {
								props: {
									className: 'ui-access-rights-popup-toggler-footer-link'
								},
								text: BX.message('JS_UI_ACCESSRIGHTS_ADD'),
								events: {
									click: function(ev) {
										this.popupUsers.close();
										this.showUserSelectorPopup();
										BX.PreventDefault(ev);
									}.bind(this)
								}
							})
						]
					})
				]
			});

			var adjustSlicker = function(node) {
				if(!BX.type.isDomNode(node))
				{
					node = content.querySelector('.ui-access-rights-popup-toggler-title-item-active');
				};
				var slicker = content.querySelector('.ui-access-rights-popup-toggler-title-slicker');
				slicker.style.left = node.offsetLeft + 'px';
				slicker.style.width = node.offsetWidth + 'px';
			};

			var activate = function(node) {
				var titles = content.querySelectorAll('.ui-access-rights-popup-toggler-title-item');
				var contents = content.querySelectorAll('.ui-access-rights-popup-toggler-content');

				var target =  content.querySelector('.' + node.getAttribute('data-role'));

				titles.forEach(function(item) {
					item.classList.remove('ui-access-rights-popup-toggler-title-item-active')
				});

				contents.forEach(function(item) {
					item.style.display = 'none';
				});

				target.style.display = 'block';
				node.classList.add('ui-access-rights-popup-toggler-title-item-active')
			};

			this.popupUsers = BX.PopupWindowManager.create(null, this.layout.addUserToRole, {
				// width: 400,
				contentPadding: 10,
				animation: 'fading-slide',
				content: content,
				padding: 0,
				offsetTop: 5,
				angle: {
					position: 'top',
					offset: 35,
				},
				autoHide: true,
				closeEsc: true,
				events: {
					onPopupShow: function() {
						setTimeout(function() {
							var firstActiveNode = content.querySelector('.ui-access-rights-popup-toggler-title-item');

							if(!firstActiveNode)
							{
								return;
							}

							firstActiveNode.classList.add('ui-access-rights-popup-toggler-title-item-active');
							adjustSlicker(firstActiveNode);
						});
					},
					onPopupClose: function() {
						this.popupUsers.destroy();
						this.popupUsers = null;
					}.bind(this)
				}
			});
		}

		return this.popupUsers;
	},

	showUserSelectorPopup: function()
	{
		var selectorInstance = BX.Main
			.selectorManagerV2.controls[this.popupContainer].selectorInstance;

		selectorInstance.itemsSelected = {};

		BX.onCustomEvent(this.openPopupEvent, [{
			id: this.popupContainer,
			bindNode: this.layout.addUserToRole
		}]);

		BX.onCustomEvent('BX.Main.SelectorV2:reInitDialog', [{
			selectorId: this.popupContainer,
			selectedItems: this.userGroup.accessCodes
		}]);
	},

	adjustPopupHelper: function()
	{
		var set = this.layout.container.cloneNode(true);

		set.style.position = 'absolute';
		set.style.display = 'inline';
		set.style.height = '0';

		document.body.appendChild(set);
		setTimeout(function() {
			set.parentNode.removeChild(set);
		});

		if(set.offsetWidth > this.layout.container.offsetWidth)
		{
			this.getPopupHelper().show();
		}
	},

	getPopupHelper: function()
	{
		if(!this.popupHelper)
		{
			this.popupHelper = BX.PopupWindowManager.create(null, this.layout.container, {
				autoHide: true,
				darkMode: true,
				content: this.text,
				maxWidth: this.layout.container.offsetWidth,
				offsetTop: -9,
				offsetLeft: 5,
				animation: 'fading-slide'
			});
		}

		return this.popupHelper;
	},

	render: function()
	{
		var changerNode = this.getChanger();
		var controlNode;

		if(this.type === 'toggler')
		{
			controlNode = this.getToggler();
			changerNode.appendChild(controlNode);
		}

		if(this.type === 'variables')
		{
			controlNode = this.getVariables();
			changerNode.appendChild(controlNode);
		}

		if(!this.layout.container)
		{
			this.layout.container = BX.create('div', {
				props: {
					className: 'ui-access-rights-column-item'
				},
				children: [
					this.type === 'role' ? this.getRole() : null,
					this.type === 'members' ? this.getMembers() : null,
					this.type === 'title' ? this.getTextNode() : null,
					this.hint ? this.getHint() : null,
					this.type === 'userGroupTitle' ? this.getTextNode() : null,
					this.controller ? this.getController() : null,
					this.type === 'variables' || this.type === 'toggler' ? changerNode : null
				]
			})
		}

		if(this.type === 'role' && this.column.newColumn)
		{
			setTimeout(function() {
				this.onRoleEditMode();
				this.layout.roleInput.value = '';
			}.bind(this));
		}

		return this.layout.container;
	}
};
})();