(function() {

var BX = window.BX;

if (!!BX.SocialnetworkUISelector)
{
	return;
}

BX.SocialnetworkUISelector = {

	newSonetgroupsCounter: 0,

	onEmptySearchResult: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyString(params.searchString)
			|| typeof BX.UI.SelectorManager == 'undefined'
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		if (selectorInstance.getOption('allowAdd', 'EMAILUSERS') == 'Y')
		{
			var emailUserData = selectorInstance.manager.checkEmail(params.searchString);
			if (
				BX.type.isNotEmptyObject(emailUserData)
				&& !BX.type.isNotEmptyObject(selectorInstance.entities.EMAILUSERS.items[emailUserData.email])
			)
			{
				this.openInviteEmailUserDialog({
					selectorId: params.selectorId,
					emailUserData: emailUserData
				});
			}
		}
		else if(selectorInstance.getOption('allowAdd', 'SONETGROUPS') == 'Y')
		{
			this.openCreateSonetgroupDialog({
				selectorId: params.selectorId,
				name: (BX.type.isNotEmptyString(params.searchStringOriginal) ? params.searchStringOriginal : params.searchString),
				isExtranet: (selectorInstance.getOption('newGroupType', 'SONETGROUPS') == 'extranet')
			});
		}
	},

	openInviteEmailUserDialog: function(params) // obUserEmail, name, bCrm
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyObject(params.emailUserData)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		if (!selectorInstance.popups.inviteEmailUser)
		{
			selectorInstance.popups.inviteEmailUser = new BX.PopupWindow({
				id: "invite-email-email-user-popup",
				bindElement: selectorInstance.getPopupBind(),
				offsetTop: 1,
				content: this.inviteEmailUserContent(params),
				zIndex: 1250,
				lightShadow: true,
				autoHide: true,
				closeByEsc: true,
				angle: {
					position: "bottom",
					offset : 20
				},
				events: {
					onPopupClose : function()
					{
						if (
							selectorInstance.popups.inviteEmailUser != null
							|| !selectorInstance.popups.inviteEmailUser.isShown()
						)
						{
							var inviteParams = {
								selectorId: params.selectorId,
								name: (selectorInstance.inviteEmailUserWindowSubmitted ? BX('invite_email_user_name').value : ''),
								lastName: (selectorInstance.inviteEmailUserWindowSubmitted ? BX('invite_email_user_last_name').value : ''),
								email: BX('invite_email_user_email').value,
								createCrmContact: (BX('invite_email_user_create_crm_contact') && BX('invite_email_user_create_crm_contact').checked)
							};

							this.inviteEmailAddUser(inviteParams);
						}
						selectorInstance.inviteEmailUserWindowSubmitted = false;

						if (
							selectorInstance.manager.statuses.allowSendEvent
							&& selectorInstance.callback.closeEmailAdd
						)
						{
							selectorInstance.callback.closeEmailAdd({
								selectorId: selectorInstance.id
							});
						}
					}.bind(this),
					onPopupShow: function()
					{
						BX.defer(BX.focus)(BX('invite_email_user_name'));

						if (
							selectorInstance.manager.statuses.allowSendEvent
							&& selectorInstance.callback.openEmailAdd
						)
						{
							selectorInstance.callback.openEmailAdd({
								selectorId: selectorInstance.id
							});
						}
					}
				}
			});
		}
		else
		{
			selectorInstance.popups.inviteEmailUser.setContent(this.inviteEmailUserContent(params));
			selectorInstance.popups.inviteEmailUser.setBindElement(selectorInstance.getPopupBind());
		}


		if (!selectorInstance.popups.inviteEmailUser.isShown())
		{
			selectorInstance.popups.inviteEmailUser.show();
		}
	},

	inviteEmailUserContent: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyObject(params.emailUserData)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		return BX.create('DIV', {
			props: {
				className: 'bx-ui-selector bx-feed-email-popup'
			},
			children: [
				BX.create('DIV', {
					props: {
						className: 'bx-feed-email-title'
					},
					text: BX.message('SONET_EXT_SELECTOR_INVITE_EMAIL_USER_TITLE')
				}),
				BX.create('FORM', {
					style: {
						padding: 0,
						margin: 0
					},
					events: {
						submit : function(e) {
							this.inviteEmailUserSubmitForm(params);
							e.stopPropagation();
							e.preventDefault();
						}.bind(this)
					},
					children: [
						BX.create('DIV', {
							children: [
								BX.create('INPUT', {
									attrs: {
										id: 'invite_email_user_email',
										type: "hidden",
										value: params.emailUserData.email
									}
								}),
								BX.create('INPUT', {
									attrs: {
										id: 'invite_email_user_name',
										type: "text",
										placeholder: BX.message('SONET_EXT_SELECTOR_INVITE_EMAIL_USER_PLACEHOLDER_NAME'),
										value: params.emailUserData.name
									},
									props: {
										className: 'bx-feed-email-input'
									}
								}),
								BX.create('INPUT', {
									attrs: {
										id: 'invite_email_user_last_name',
										type: "text",
										placeholder: BX.message('SONET_EXT_SELECTOR_INVITE_EMAIL_USER_PLACEHOLDER_LAST_NAME'),
										value: params.emailUserData.lastName
									},
									props: {
										className: 'bx-feed-email-input'
									},
									events : {
										keyup : function(e) {
											if (
												BX('invite_email_user_name').value.length > 0
												|| BX('invite_email_user_last_name').value.length > 0
											)
											{
												BX.removeClass(BX('invite_email_user_button'), 'ui-btn-disabled');
											}
											else
											{
												BX.addClass(BX('invite_email_user_button'), 'ui-btn-disabled');
											}
											e.stopPropagation();
											e.preventDefault();
										}
									}
								}),
								BX.create('SPAN', {
									attrs: {
										id: 'invite_email_user_button'
									},
									props: {
										className: 'ui-btn ui-btn-md ui-btn-primary ui-btn-disabled'
									},
									text: BX.message("SONET_EXT_SELECTOR_INVITE_EMAIL_USER_BUTTON_OK"),
									style: {
										cursor: 'pointer'
									},
									events : {
										click : function() {
											this.inviteEmailUserSubmitForm(params);
										}.bind(this)
									}
								}),
								BX.create('INPUT', {
									style: {
										display: 'none'
									},
									attrs: {
										type: 'submit'
									}
								})
							]
						}),
						(
							selectorInstance.getOption('allowAddCrmContact', 'EMAILUSERS') == 'Y'
								? BX.create('DIV', {
									props: {
										className: 'bx-feed-email-crm-contact'
									},
									children: [
										BX.create('INPUT', {
											attrs: {
												className: 'bx-feed-email-checkbox',
												type: 'checkbox',
												id: 'invite_email_user_create_crm_contact',
												value: 'Y'
											}
										}),
										BX.create('LABEL', {
											attrs: {
												for: 'invite_email_user_create_crm_contact'
											},
											html: BX.message('SONET_EXT_SELECTOR_INVITE_EMAIL_CRM_CREATE_CONTACT')
										})
									]
								})
								: null
						)
					]
				})
			]
		});
	},

	inviteEmailAddUser: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (
			!BX.type.isNotEmptyObject(selectorInstance)
			|| !BX.type.isNotEmptyObject(selectorInstance.entities.EMAILUSERS)
		)
		{
			return;
		}

		var
			showEmail = false,
			userEmail = params.email,
			userName = BX.util.htmlspecialchars(params.name) + (BX.type.isNotEmptyString(params.name) ? ' ' : '') + BX.util.htmlspecialchars(params.lastName);

		if (!BX.type.isNotEmptyString(userName))
		{
			userName = userEmail;
		}
		else
		{
			showEmail = true;
		}

		selectorInstance.entities.EMAILUSERS.items[userEmail] = {
			name: userName,
			email: userEmail,
			id: userEmail,
			isEmail: 'Y',
			isCrmEmail: (typeof params.createCrmContact != 'undefined' && !!params.createCrmContact ? 'Y' : 'N'),
			showEmail: (showEmail ? 'Y' : 'N'),
			params: params
		};

		if (selectorInstance.callback.select)
		{
			selectorInstance.callback.select({
				item: selectorInstance.entities.EMAILUSERS.items[userEmail],
				entityType: 'EMAILUSERS',
				selectorId: selectorInstance.id,
				state: 'select'
			});
		}
	},

	inviteEmailUserSubmitForm: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		selectorInstance.inviteEmailUserWindowSubmitted = true;
		selectorInstance.popups.inviteEmailUser.close();
	},

	openCreateSonetgroupDialog: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyString(params.name)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId],
			groupName = params.name;

		selectorInstance.timeouts.createSonetgroup = setTimeout(function()
		{
			if (!selectorInstance.popups.createSonetgroup)
			{
				selectorInstance.popups.createSonetgroup = new BX.PopupWindow({
					id: "invite-dialog-creategroup-popup",
					bindElement: selectorInstance.getPopupBind(),
					offsetTop : 1,
					autoHide : true,
					content : this.createSocNetGroupContent(groupName),
					zIndex : 1200,
					buttons : this.createSocNetGroupButtons({
						selectorId: selectorInstance.id,
						groupName: groupName,
						isExtranet: !!params.isExtranet
					})
				});
			}
			else
			{
				selectorInstance.popups.createSonetgroup.setContent(this.createSocNetGroupContent(groupName));
				selectorInstance.popups.createSonetgroup.setButtons(this.createSocNetGroupButtons({
					selectorId: selectorInstance.id,
					groupName: groupName,
					isExtranet: !!params.isExtranet
				}));
			}

			if (!selectorInstance.popups.createSonetgroup.isShown())
			{
				selectorInstance.popups.createSonetgroup.show();
			}
		}.bind(this), 1000);
	},

	createSocNetGroupContent: function(text)
	{
		return BX.create('DIV', {
			children: [
				BX.create('DIV', {
					text: BX.message('SONET_EXT_SELECTOR_CREATE_SONETGROUP_TITLE').replace("#TITLE#", text)
				})
			]
		});
	},

	createSocNetGroupButtons: function(params) // text, selectorId
	{

		if (
			!BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyString(params.groupName)
		)
		{
			return [];
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		return [
			new BX.PopupWindowButton({
				text: BX.message("SONET_EXT_SELECTOR_CREATE_SONETGROUP_BUTTON_CREATE"),
				events: {
					click: function() {
						var groupCode = 'SGN' + this.newSonetgroupsCounter + '';
						selectorInstance.entities.SONETGROUPS.items[groupCode] = {
							id: groupCode,
							entityId: this.newSonetgroupsCounter,
							name: params.groupName,
							desc: '',
							isExtranet: (!!params.isExtranet ? 'Y' : 'N')
						};

						var itemsNew = {
							SONETGROUPS: {}
						};
						itemsNew.SONETGROUPS[groupCode] = groupCode;

						selectorInstance.openSearch({
							itemsList: itemsNew
						});

						this.newSonetgroupsCounter++;
						selectorInstance.popups.createSonetgroup.close();
					}.bind(this)
				}
			}),
			new BX.PopupWindowButtonLink({
				text: BX.message("SONET_EXT_SELECTOR_CREATE_SONETGROUP_BUTTON_CANCEL"),
				className: "popup-window-button-link-cancel",
				events: {
					click: function() {
						selectorInstance.popups.createSonetgroup.close();
					}.bind(this)
				}
			})
		];
	},

	beforeRunSearch: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyObject(params.selectorInstance)
		)
		{
			return;
		}

		var
			selectorInstance = params.selectorInstance;

		if (selectorInstance.timeouts.createSonetgroup)
		{
			clearTimeout(selectorInstance.timeouts.createSonetgroup);
		}
	},

	setFilterSelected: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyObject(BX.UI.SelectorManager)
			|| !BX.type.isNotEmptyObject(BX.Main)
			|| !BX.type.isNotEmptyObject(BX.Main.selectorManagerV2)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId],
			componentSelectorInstance = BX.Main.selectorManagerV2.getById(params.selectorId);

		if (
			!BX.type.isNotEmptyObject(selectorInstance)
			|| !BX.type.isNotEmptyObject(componentSelectorInstance)
		)
		{
			return;
		}

		var
			isNumeric = componentSelectorInstance.getOption('isNumeric'),
			prefix = componentSelectorInstance.getOption('prefix');

		if (BX.type.isArray(params.current))
		{
			for (var i = 0; i < params.current.length; i++)
			{
				if (isNumeric == 'Y' && prefix == 'U')
				{
					componentSelectorInstance.items.selected[prefix + params.current[i].value] = 'users';
				}
			}
		}
	},

	select: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isDomNode(params.contextNode)
			|| !BX.type.isNotEmptyObject(params.item)
			|| !BX.type.isNotEmptyString(params.item.id)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		if (
			BX.type.isNotEmptyObject(selectorInstance.entities.EMAILUSERS)
			&& BX.type.isNotEmptyObject(selectorInstance.entities.EMAILUSERS.items)
			&& BX.type.isNotEmptyObject(selectorInstance.entities.EMAILUSERS.items[params.item.id])
			&& BX.type.isNotEmptyObject(params.item.params) // new email user
		)
		{
			if (BX.type.isNotEmptyString(params.item.params.name))
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'INVITED_USER_NAME[' + params.item.id + ']',
						value : params.item.params.name
					}
				}), params.contextNode.firstChild);
			}

			if (BX.type.isNotEmptyString(params.item.params.lastName))
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'INVITED_USER_LAST_NAME[' + params.item.id + ']',
						value : params.item.params.lastName
					}
				}), params.contextNode.firstChild);
			}

			if (!!params.item.params.createCrmContact)
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'INVITED_USER_CREATE_CRM_CONTACT[' + params.item.id + ']',
						value : 'Y'
					}
				}), params.contextNode.firstChild);
			}
		}
		else if (
			BX.type.isNotEmptyObject(selectorInstance.entities.CRMEMAILUSERS)
			&& BX.type.isNotEmptyObject(selectorInstance.entities.CRMEMAILUSERS.items)
			&& BX.type.isNotEmptyObject(selectorInstance.entities.CRMEMAILUSERS.items[params.item.id])
			&& BX.type.isNotEmptyObject(params.item.params) // new crm email user
		)
		{
			if (BX.type.isNotEmptyString(params.item.params.name))
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'INVITED_USER_NAME[' + params.item.email + ']',
						value : params.item.params.name
					}
				}), params.contextNode.firstChild);
			}

			if (BX.type.isNotEmptyString(params.item.params.lastName))
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'INVITED_USER_LAST_NAME[' + params.item.email + ']',
						value : params.item.params.lastName
					}
				}), params.contextNode.firstChild);
			}

			if (BX.type.isNotEmptyString(params.item.crmEntity))
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'INVITED_USER_CRM_ENTITY[' + params.item.email + ']',
						value : params.item.crmEntity
					}
				}), params.contextNode.firstChild);
			}
		}
		else if (
			BX.type.isNotEmptyObject(selectorInstance.entities.SONETGROUPS)
			&& BX.type.isNotEmptyObject(selectorInstance.entities.SONETGROUPS.items)
			&& BX.type.isNotEmptyObject(selectorInstance.entities.SONETGROUPS.items[params.item.id])
		)
		{
			var found = params.item.id.match(/^SGN(\d+)$/i); // new sonetgroup
			if (found)
			{
				params.contextNode.insertBefore(BX.create("INPUT", {
					attrs : {
						type : 'hidden',
						name : 'SONET_GROUPS_NAME[' + found[0] + ']',
						value : selectorInstance.entities.SONETGROUPS.items[params.item.id].name
					}
				}), params.contextNode.firstChild);
			}
		}
	},

	unselect: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isDomNode(params.contextNode)
			|| !BX.type.isNotEmptyObject(params.item)
			|| !BX.type.isNotEmptyString(params.item.id)
		)
		{
			return;
		}

		var
			selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];

		if (!BX.type.isNotEmptyObject(selectorInstance))
		{
			return;
		}

		var res = null;

		res = BX.findChild(params.contextNode, {
			tagName: 'input',
			attrs: {
				type: 'hidden',
				name: 'INVITED_USER_NAME[' + params.item.id + ']'
			}
		});
		if (res)
		{
			BX.cleanNode(res, true);
		}

		res = BX.findChild(params.contextNode, {
			tagName: 'input',
			attrs: {
				type: 'hidden',
				name: 'INVITED_USER_LAST_NAME[' + params.item.id + ']'
			}
		});
		if (res)
		{
			BX.cleanNode(res, true);
		}

		res = BX.findChild(params.contextNode, {
			tagName: 'input',
			attrs: {
				type: 'hidden',
				name: 'INVITED_USER_CREATE_CRM_CONTACT[' + params.item.id + ']'
			}
		});
		if (res)
		{
			BX.cleanNode(res, true);
		}
	},

	onGetEntityTypes: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyObject(params.selector)
		)
		{
			return;
		}

		var
			selectorInstance = params.selector;

		selectorInstance.entityTypes.GROUPS = {
			options: {
				context: (BX.type.isNotEmptyString(selectorInstance.getOption('context')) ? selectorInstance.getOption('context') : false),
				enableAll: (selectorInstance.getOption('enableAll') == 'Y' ? 'Y' : 'N'),
				enableEmpty: (selectorInstance.getOption('enableEmpty') == 'Y' ? 'Y' : 'N'),
				enableUserManager: (selectorInstance.getOption('enableUserManager') == 'Y' ? 'Y' : 'N')
			}
		};

		if (selectorInstance.getOption('enableUsers') != 'N')
		{
			selectorInstance.entityTypes.USERS = {
				options: {
					scope: selectorInstance.getOption('userSearchArea'), // userSearchArea
					extranetContext: selectorInstance.getOption('userSearchArea'), // extranetContext
					allowAdd: (selectorInstance.getOption('allowAddUser') == 'Y' ? 'Y' : 'N'), // allowAddUser
					allowSearch: (selectorInstance.getOption('allowUserSearch') != 'N' ? 'Y' : 'N'), // allowUserSearch
					allowSearchByEmail: (selectorInstance.getOption('allowSearchEmailUsers') == 'Y' ? 'Y' : 'N'), // allowSearchEmailUsers / search users by email X
					allowSearchNetwork: (selectorInstance.getOption('allowSearchNetworkUsers') == 'Y' ? 'Y' : 'N'), // allowSearchNetworkUsers,
					allowSearchSelf: (selectorInstance.getOption('allowSearchSelf') == 'N' ? 'N' : 'Y'), // obAllowSearchSelf,
					allowSearchCrmEmailUsers: (selectorInstance.getOption('allowSearchCrmEmailUsers') == 'Y' ? 'Y' : 'N'), // allowSearchCrmEmailUsers
					showVacations: (selectorInstance.getOption('showVacations') == 'Y' ? 'Y' : 'N'), // showVacations
					onlyWithEmail: (selectorInstance.getOption('onlyWithEmail') == 'Y' ? 'Y' : 'N'),
					allowBots: (selectorInstance.getOption('allowBots') == 'Y' ? 'Y' : 'N'),
					showAllExtranetContacts: (selectorInstance.getOption('showAllExtranetContacts') == 'Y' ? 'Y' : 'N')
				}
			};
		}

		if (
			selectorInstance.getOption('enableUsers') != 'N'
			|| selectorInstance.getOption('enableEmailUsers') != 'N'
		)
		{
			selectorInstance.entityTypes.EMAILUSERS = {
				options: {
					allowAdd: (
						selectorInstance.getOption('allowAddUser') == 'Y'
						|| selectorInstance.getOption('allowEmailInvitation') == 'Y'
							? 'Y'
							: 'N'
					), // allowAddUser
					allowAddCrmContact: (selectorInstance.getOption('allowAddCrmContact') == 'Y' ? 'Y' : 'N'), // allowAddCrmContact
					allowSearchCrmEmailUsers: (selectorInstance.getOption('allowSearchCrmEmailUsers') == 'Y' ? 'Y' : 'N'), // allowSearchCrmEmailUsers
					addTab: (selectorInstance.getOption('allowSearchEmailUsers') == 'Y' ? 'Y' : 'N'), // allowSearchEmailUsers / add tab
				}
			};
		}

		if (
			selectorInstance.getOption('enableUsers') != 'N'
			&& selectorInstance.getOption('allowSearchCrmEmailUsers') == 'Y'
		)
		{
			selectorInstance.entityTypes.CRMEMAILUSERS = {
				options: {
					addTab: 'Y',
					allowSearchCrmEmailUsers: (selectorInstance.getOption('allowSearchCrmEmailUsers') == 'Y' ? 'Y' : 'N'), // allowSearchCrmEmailUsers
				}
			};
		}

		if (selectorInstance.getOption('enableSonetgroups') == 'Y')
		{
			selectorInstance.entityTypes.SONETGROUPS = {
				options: {
					allowAdd: (selectorInstance.getOption('allowAddSocNetGroup') == 'Y' ? 'Y' : 'N'), // allowAddSocNetGroup
					enableProjects: (selectorInstance.getOption('enableProjects') == 'Y' ? 'Y' : 'N'), // enableProjects
					siteId: selectorInstance.getOption('socNetGroupsSiteId'),
					landing: (selectorInstance.getOption('landing') == 'Y' ? 'Y' : 'N'),
					feature: selectorInstance.getOption('sonetGroupsFeature')
				}
			};
		}
		if (selectorInstance.getOption('enableProjects') == 'Y')
		{
			selectorInstance.entityTypes.PROJECTS = {
				options: {
					allowAdd: (selectorInstance.getOption('allowAddSocNetGroup') == 'Y' ? 'Y' : 'N'), // allowAddSocNetGroup
				}
			};
		}
	}
};

BX.addCustomEvent('BX.Main.SelectorV2:onGetEntityTypes', BX.SocialnetworkUISelector.onGetEntityTypes);

BX.ready(function () {
	BX.addCustomEvent('BX.UI.Selector:onEmptySearchResult', BX.SocialnetworkUISelector.onEmptySearchResult.bind(BX.SocialnetworkUISelector));
	BX.addCustomEvent('BX.Main.User.SelectorController:select', BX.SocialnetworkUISelector.select);
	BX.addCustomEvent('BX.Main.User.SelectorController:unSelect', BX.SocialnetworkUISelector.unselect);
	BX.addCustomEvent('BX.UI.SelectorManager:beforeRunSearch', BX.SocialnetworkUISelector.beforeRunSearch);
	BX.addCustomEvent('BX.Filter.DestinationSelector:setSelected', BX.SocialnetworkUISelector.setFilterSelected);
});

})();
