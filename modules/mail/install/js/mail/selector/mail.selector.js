(function() {

var BX = window.BX;

if (!!BX.MailUISelector)
{
	return;
}

BX.MailUISelector = {

	prefix: 'MC',

	onEmptySearchResult: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyString(params.searchString)
			|| !BX.type.isNotEmptyObject(BX.UI.SelectorManager)
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

		if (selectorInstance.getOption('allowAdd', 'MAILCONTACTS') == 'Y')
		{
			var contactData = selectorInstance.manager.checkEmail(params.searchString);
			if (
				BX.type.isNotEmptyObject(contactData)
				&& !BX.type.isNotEmptyObject(selectorInstance.entities.MAILCONTACTS.items[contactData.email])
			)
			{
				this.openAddDialog({
					selectorId: params.selectorId,
					contactData: contactData
				});
			}
		}
	},

	openAddDialog: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyObject(params.contactData)
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

		if (!selectorInstance.popups.addMailContact)
		{
			selectorInstance.popups.addMailContact = new BX.PopupWindow("add-mailcontact-popup", selectorInstance.getPopupBind(), {
				offsetTop: 1,
				content: this.getAddDialogContent(params),
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
							selectorInstance.popups.addMailContact != null
							|| !selectorInstance.popups.addMailContact.isShown()
						)
						{
							this.addMailContact({
								selectorId: params.selectorId,
								name: (selectorInstance.addMailContactSubmitted ? BX('add_mailcontact_name_' + params.selectorId).value : ''),
								lastName: (selectorInstance.addMailContactSubmitted ? BX('add_mailcontact_lastname_' + params.selectorId).value : ''),
								email: BX('add_mailcontact_email_' + params.selectorId).value
							});
						}
						selectorInstance.addMailContactSubmitted = false;

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
						BX.defer(BX.focus)(BX('add_mailcontact_name_' + params.selectorId));

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
			selectorInstance.popups.addMailContact.setContent(this.getAddDialogContent(params));
			selectorInstance.popups.addMailContact.setBindElement(selectorInstance.getPopupBind());
		}


		if (!selectorInstance.popups.addMailContact.isShown())
		{
			selectorInstance.popups.addMailContact.show();
		}
	},

	getAddDialogContent: function(params)
	{
		if (
			!BX.type.isNotEmptyObject(params)
			|| !BX.type.isNotEmptyString(params.selectorId)
			|| !BX.type.isNotEmptyObject(params.contactData)
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
					text: BX.message('MAIL_EXT_SELECTOR_ADD_MAILCONTACT_TITLE')
				}),
				BX.create('FORM', {
					style: {
						padding: 0,
						margin: 0
					},
					events: {
						submit : function(e) {
							this.submitAddMailContactForm(params);
							e.stopPropagation();
							e.preventDefault();
						}.bind(this)
					},
					children: [
						BX.create('DIV', {
							children: [
								BX.create('INPUT', {
									attrs: {
										id: 'add_mailcontact_email_' + params.selectorId,
										type: "hidden",
										value: params.contactData.email
									}
								}),
								BX.create('INPUT', {
									attrs: {
										id: 'add_mailcontact_name_' + params.selectorId,
										type: "text",
										placeholder: BX.message('MAIL_EXT_SELECTOR_ADD_MAILCONTACT_PLACEHOLDER_NAME'),
										value: params.contactData.name
									},
									props: {
										className: 'bx-feed-email-input'
									}
								}),
								BX.create('INPUT', {
									attrs: {
										id: 'add_mailcontact_lastname_' + params.selectorId,
										type: "text",
										placeholder: BX.message('MAIL_EXT_SELECTOR_ADD_MAILCONTACT_PLACEHOLDER_LAST_NAME'),
										value: params.contactData.lastName
									},
									props: {
										className: 'bx-feed-email-input'
									},
									events : {
										keyup : function(e) {
											if (
												BX('add_mailcontact_name_' + params.selectorId).value.length > 0
												|| BX('add_mailcontact_lastname_' + params.selectorId).value.length > 0
											)
											{
												BX.removeClass(BX('add_mailcontact_button_' + params.selectorId), 'ui-btn-disabled');
											}
											else
											{
												BX.addClass(BX('add_mailcontact_button_' + params.selectorId), 'ui-btn-disabled');
											}
											e.stopPropagation();
											e.preventDefault();
										}
									}
								}),
								BX.create('SPAN', {
									attrs: {
										id: 'add_mailcontact_button_' + params.selectorId
									},
									props: {
										className: 'ui-btn ui-btn-md ui-btn-primary ui-btn-disabled'
									},
									text: BX.message("MAIL_EXT_SELECTOR_ADD_MAILCONTACT_BUTTON_OK"),
									style: {
										cursor: 'pointer'
									},
									events : {
										click : function() {
											this.submitAddMailContactForm(params);
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
						})
					]
				})
			]
		});
	},

	addMailContact: function(params)
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
			|| !BX.type.isNotEmptyObject(selectorInstance.entities.MAILCONTACTS)
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

		selectorInstance.entities.MAILCONTACTS.items[userEmail] = {
			name: userName,
			email: userEmail,
			id: 'MC' + userEmail,
			showEmail: (showEmail ? 'Y' : 'N'),
			params: params,
			isEmail: 'Y',
		};

		if (selectorInstance.callback.select)
		{
			selectorInstance.callback.select({
				item: selectorInstance.entities.MAILCONTACTS.items[userEmail],
				entityType: 'MAILCONTACTS',
				selectorId: selectorInstance.id,
				state: 'select',
				prefix: this.prefix
			});
		}
	},

	submitAddMailContactForm: function(params)
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

		selectorInstance.addMailContactSubmitted = true;
		selectorInstance.popups.addMailContact.close();
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

		if (selectorInstance.getOption('enableMailContacts') == 'Y')
		{
			var selectedItemsData = {};
			selectorInstance.entityTypes.MAILCONTACTS = {
				options: {
					allowAdd: (selectorInstance.getOption('allowAddMailContact') == 'Y' ? 'Y' : 'N'),
					addTab: (selectorInstance.getOption('addMailContactsTab') == 'Y' ? 'Y' : 'N'),
					selectedItemsData: selectorInstance.getOption('selectedItemsData')
				},
			};
		}
	}
};

BX.addCustomEvent('BX.Main.SelectorV2:onGetEntityTypes', BX.MailUISelector.onGetEntityTypes);

BX.ready(function () {
	BX.addCustomEvent('BX.UI.Selector:onEmptySearchResult', BX.MailUISelector.onEmptySearchResult.bind(BX.MailUISelector));
});
})();
