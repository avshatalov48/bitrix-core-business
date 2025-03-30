
;(function() {

	if (window.BXMainMailForm)
		return;

	var BXMainMailForm = function(id, fields, replyTo, replyCC, selectedRecipients, options)
	{
		if (BXMainMailForm.__forms[id])
			return BXMainMailForm.__forms[id];

		this.id = id;
		this.fields = fields;
		this.selectedRecipients = selectedRecipients;
		this.replyTo = replyTo;
		this.replyCC = replyCC;
		this.options = options;
		this.fieldsData = {};
		this.lastSearchText = '';

		this.helpDeskCalendarCode = 17198666;
		this.helpDeskCRMCalendarCode = 17502612;

		BXMainMailForm.__forms[this.id] = this;
	};

	BXMainMailForm.__forms = {};

	BXMainMailForm.getForm = function (id)
	{
		return BXMainMailForm.__forms[id];
	};

	BXMainMailForm.prototype.getField = function (name)
	{
		for (var i = this.fields.length; i-- > 0;)
		{
			if (this.fields[i].params.name == name)
				return this.fields[i];
		}

		return false;
	};

	BXMainMailForm.prototype.cleanFields = function()
	{
		var fields = this.getFieldsData();

		for (var key in fields)
		{
			if (fields.hasOwnProperty(key))
			{
				var field = fields[key];
				if (BX.type.isFunction(field.tagSelector.removeTags))
				{
					field.tagSelector.removeTags();
				}
			}
		}
	}

	BXMainMailForm.prototype.addTagsToField = function(dialog, tags)
	{
		for (var key in tags)
		{
			var itemOptions = tags[key];
			var item = dialog.addItem(itemOptions);

			if (item)
			{
				item.select();
			}
		}
	}

	BXMainMailForm.prototype.addItemsToField = function(dialog, items)
	{
		var itemsAdded = false;

		items.forEach(function(item) {
			if (Object.keys(item).length !== 0)
			{
				item.sort = 1;
				item.tabs.push(dialog.getRecentTab().getId());

				dialog.removeItem(item);
				var builtItem = dialog.addItem(item);

				if (builtItem)
				{
					builtItem.select();
					itemsAdded = true;
				}
			}
		});

		if (itemsAdded)
		{
			dialog.clearSearch();
		}
	}

	BXMainMailForm.prototype.fillFieldsForReply = function()
	{
		this.cleanFields();
		var fields = this.getFieldsData();

		for (var key in fields)
		{
			if (fields.hasOwnProperty(key))
			{
				var field = fields[key];

				if (key.toUpperCase() === 'DATA[TO]')
				{
					this.addTagsToField(field.dialog, this.selectedRecipients);
				}
			}
		}
	};

	BXMainMailForm.prototype.fillFieldsForReplyAll = function()
	{
		this.cleanFields();
		var fields = this.getFieldsData();

		for (var key in fields)
		{
			if (fields.hasOwnProperty(key))
			{
				var field = fields[key];

				if (key.toUpperCase() === 'DATA[TO]')
				{
					this.addTagsToField(field.dialog, this.replyTo);
				}
				else if (key.toUpperCase() === 'DATA[CC]')
				{
					this.addTagsToField(field.dialog, this.replyCC);
				}
			}
		}
	};

	BXMainMailForm.prototype.getFieldsData = function ()
	{
		return this.fieldsData;
	};

	BXMainMailForm.prototype.setFieldData = function(key, dialog, items, nodeForRender, tagSelector)
	{
		if (BX.type.isUndefined(nodeForRender) && !BX.type.isUndefined(this.fieldsData[key]))
		{
			nodeForRender = this.fieldsData[key]['nodeForRender'];
		}

		this.fieldsData[key] = {
			dialog: dialog,
			key: key,
			items: items,
			nodeForRender: nodeForRender,
			tagSelector: tagSelector,
		};

		if (!BX.type.isUndefined(nodeForRender))
		{
			var inputsContainer = nodeForRender.querySelector('div');
			inputsContainer.innerHTML = '';

			for (var i = 0; i < items.length; i++)
			{
				var itemMap = items[i];
				var itemObj = items[i];

				itemMap.forEach(function(value, itemKey) {
					itemObj[itemKey] = value;
				});

				inputsContainer.appendChild(BX.create('INPUT', {
					'props': {
						'type': 'hidden',
						'name': key + '[]',
						'value': JSON.stringify(itemObj)
					}
				}));
			}
		}
	};

	BXMainMailForm.prototype.onSubmit = function (event)
	{
		var form = this;

		var footer = BX.findChildByClassName(this.formWrapper, 'main-mail-form-footer', false) || this.footerNode;
		var button = BX.findChildByClassName(footer, 'main-mail-form-submit-button', true);

		if (button.disabled)
			return BX.PreventDefault();

		this.fillFieldsFromDialogs();

		this.editor.OnSubmit();

		var footerClone = footer.cloneNode(true);

		Array.prototype.forEach.call(
			footerClone.querySelectorAll('[id]'),
			function (item)
			{
				item.removeAttribute('id');
			}
		);

		BX(this.formId+'_dummy_footer').appendChild(footerClone);

		event = event || window.event;
		BX.onCustomEvent(this, 'MailForm:submit', [this, event]);

		if (!event.defaultPrevented && event.returnValue !== false)
		{
			BX.addClass(button, 'ui-btn-wait');
			button.disabled = true;
			button.offsetHeight; // hack to show loader

			if (this.options.submitAjax)
			{
				BX.ajax.submitAjax(this.htmlForm, {
					url: this.htmlForm.getAttribute('action'),
					method: 'POST',
					dataType: 'json',
					onsuccess: function(data)
					{
						button.disabled = false;
						BX.removeClass(button, 'ui-btn-wait');
						BX.onCustomEvent(form, 'MailForm:submit:ajaxSuccess', [form, data]);
					},
					onfailure: function(data)
					{
						button.disabled = false;
						BX.removeClass(button, 'ui-btn-wait');
						BX.onCustomEvent(form, 'MailForm:submit:ajaxFailure', [form, data]);
					}
				});

				return BX.PreventDefault(event);
			}
		}
	};

	BXMainMailForm.prototype.showError = function (html)
	{
		var errorNode = BX.findChildByClassName(this.formWrapper, 'main-mail-form-error', true);

		var alert = new BX.UI.Alert({
			text: html,
			inline: true,
			closeBtn: true,
			animate: true,
			color: BX.UI.Alert.Color.DANGER,
		});

		errorNode.innerHTML = '';
		errorNode.append(alert.getContainer());

		this.initScrollable();
		if (this.__scrollable)
		{
			var pos0 = BX.pos(this.__scrollable);
			var pos1 = BX.pos(this.formWrapper);
			var pos2 = BX.pos(errorNode);

			if (pos0.top > pos2.top-10-this.__scrollable.scrollTop)
				this.__scrollable.scrollTop = pos2.top-10;
			else if (pos0.bottom < pos1.bottom-10-this.__scrollable.scrollTop)
				this.__scrollable.scrollTop = pos1.bottom-10-pos0.bottom;
		}
	};

	BXMainMailForm.prototype.fillFieldsFromDialogs = function ()
	{
		var fields = this.getFieldsData();

		for (var key in fields)
		{
			if (fields.hasOwnProperty(key))
			{
				var field = fields[key];
				var dialog = field.dialog;

				if (dialog === null)
				{
					return;
				}

				if (BX.type.isFunction(dialog.getSelectedItems))
				{
					var selectedItems = dialog.getSelectedItems();
					var itemsData = [];
					for (var j = 0; j < selectedItems.length; j++)
					{
						var selectedItem = selectedItems[j];
						if (BX.type.isFunction(selectedItem.getCustomData))
						{
							itemsData.push(selectedItem.getCustomData());
						}
					}
					this.setFieldData(key, dialog, itemsData, field.nodeForRender, field.tagSelector);
				}
			}
		}
	}

	BXMainMailForm.prototype.addSearchInput = function(text)
	{
		this.lastSearchText = text;
	}

	BXMainMailForm.prototype.unbindToAddressBookEvents = function()
	{
		top.BX.Event.EventEmitter.unsubscribe('BX.DialogEditContact:onSaveContact', BXMainMailForm.prototype.onSaveContactToAddressBook);
	}

	BXMainMailForm.prototype.onSaveContactToAddressBook = function(dialog, prefixSliderId, event)
	{
		if (
			Object.keys(event.data) &&
			event.data.prefixId === prefixSliderId &&
			Array.isArray(event.data.items)
		)
		{
			this.addItemsToField(dialog, event.data.items);
			this.unbindToAddressBookEvents();
		}
	}

	BXMainMailForm.prototype.bindToAddressBookEvents = function(dialog, prefixSliderId, contactID = 'new')
	{
		var eventHandlerDialogClose = function()
		{
			this.unbindToAddressBookEvents();
			dialog.unsubscribe('onDestroy', eventHandlerDialogClose);
		}.bind(this);

		dialog.subscribe('onDestroy', eventHandlerDialogClose);

		var eventHandlerSliderClose = function (event) {
			if (event.getSlider().getUrl() === ('dialogEditContact_' + contactID + '_' + prefixSliderId))
			{
				top.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete",  eventHandlerSliderClose);
				this.unbindToAddressBookEvents()
				dialog.getTagSelector().unlock();
			}
		}.bind(this)

		top.BX.addCustomEvent("SidePanel.Slider:onCloseComplete", eventHandlerSliderClose);

		top.BX.Event.EventEmitter.subscribe('BX.DialogEditContact:onSaveContact', BXMainMailForm.prototype.onSaveContactToAddressBook.bind(this, dialog, prefixSliderId));
	}

	BXMainMailForm.prototype.openEditContact = function(dialog, contactID, email, name)
	{
		const prefixSliderId = this.generatePrefixSliderId();

		top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(() => {
			top.BX.Mail.AddressBook.DialogEditContact.openEditDialog({
				contactID,
				prefixId: prefixSliderId,
				contactData: {
					email,
					name,
				},
			});
		});

		this.bindToAddressBookEvents(dialog, prefixSliderId, contactID);
	}

	BXMainMailForm.prototype.generatePrefixSliderId = function()
	{
		return Math.floor(Math.random() * 1000);
	}

	BXMainMailForm.prototype.addContactToAddressBook = function(dialog, preInstalledSearchText = null)
	{
		if (preInstalledSearchText !== null)
		{
			this.lastSearchText = preInstalledSearchText;
		}

		var openCreateSlider = function(searchText, showEmailError = false, responseError)
		{
			const prefixSliderId = this.generatePrefixSliderId();

			var email;
			var name;

			if (searchText.includes("@"))
			{
				email = searchText;
				name = '';
			}
			else
			{
				email = '';
				name = searchText;
				showEmailError = false;
			}

			const contactID = top.BX.Mail.AddressBook.DialogEditContact.openCreateDialog({
				prefixId: prefixSliderId,
				showEmailError,
				responseError,
				contactData: {
					email,
					name,
				},
			});

			this.bindToAddressBookEvents(dialog, prefixSliderId, contactID);
		}.bind(this);

		return new Promise(function(resolve, reject) {
			top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(
				function(){
					if (BX.Validation.isEmail(this.lastSearchText))
					{
						top.BX.Mail.AddressBook.DialogEditContact.saveContact(this.lastSearchText, this.lastSearchText, 'new').then(
							function(response)
							{
								this.addItemsToField(dialog, response.data);
								resolve();
							}.bind(this)
						).catch(
							function(responseError)
							{
								openCreateSlider(this.lastSearchText, false, responseError);
								reject();
							}.bind(this)
						);
					}
					else
					{
						openCreateSlider(this.lastSearchText, true);
						reject();
					}
				}.bind(this),
			);
		}.bind(this));
	}

	BXMainMailForm.prototype.renderField = function(fieldNode, type, formId, ownerId, ownerType, selectedRecipients, replyTo, replyCC, isReplyAll, contextName)
	{
		let codeArticle;
		var dialogId = formId + '_' + type;

		var entitiesDialog = [];

		var dialogAdditionalOptions = {};
		var selectorAdditionalOptions = {};
		var selectorEvents = {};
		var dialogEvents = {};
		var dialogSearchOptions = {};

		const {
			oldRecipientsMode,
			ownerCategoryId,
		} = this.options;

		if (contextName === 'MAIL')
		{
			entitiesDialog.push(
				{
					id: 'address_book',
					dynamicLoad: true,
				},
				{
					id: 'contact',
					dynamicLoad: true,
					dynamicSearch: true,
					filters: [
						{
							id: 'mail.mailCrmRecipientAppearanceFilter',
						},
					],
					options: {
						onlyWithEmail: true,
					},
				},
				{
					id: 'company',
					dynamicLoad: true,
					dynamicSearch: true,
					filters: [
						{
							id: 'mail.mailCrmRecipientAppearanceFilter',
						},
					],
					options: {
						onlyWithEmail: true,
					},
				},
				{
					id: 'lead',
					dynamicLoad: true,
					dynamicSearch: true,
					filters: [
						{
							id: 'mail.mailCrmRecipientAppearanceFilter',
						},
					],
					options: {
						onlyWithEmail: true,
					},
				},
				{
					id: 'mail_crm_recipient',
					dynamicLoad: true,
				}
			);

			codeArticle = 24146582;
		}
		else
		{
			entitiesDialog.push(
				{
					options: {
						ownerId: ownerId,
						ownerType: ownerType,
					},
					id: 'mail_recipient',
					dynamicLoad: true,
				},
			);

			if (oldRecipientsMode)
			{
				if (ownerCategoryId === 0)
				{
					entitiesDialog.push(
						{
							id: 'contact',
							dynamicSearch: true,
							filters: [
								{
									id: 'mail.mailCrmRecipientAppearanceFilter',
								},
							],
							options: {
								onlyWithEmail: true,
							},
						},
						{
							id: 'company',
							dynamicSearch: true,
							filters: [
								{
									id: 'mail.mailCrmRecipientAppearanceFilter',
								},
							],
							options: {
								onlyWithEmail: true,
							},
						},
						{
							id: 'lead',
							dynamicSearch: true,
							filters: [
								{
									id: 'mail.mailCrmRecipientAppearanceFilter',
								},
							],
							options: {
								onlyWithEmail: true,
							},
						},
					);
				}

				entitiesDialog.push(
					{
						id: 'address_book',
						dynamicSearch: true,
					},
				);
			}

			codeArticle = 24196378;
		}

		if (contextName === 'MAIL' || oldRecipientsMode)
		{
			var loader;

			selectorAdditionalOptions = {
				tagClickable: true,
			};

			function showLoader()
			{
				loader.show();
				BX.Dom.addClass(addEmailToAddressBookNodeLink, 'hide-before');
			}

			function hideLoader()
			{
				loader.hide();
				BX.Dom.removeClass(addEmailToAddressBookNodeLink, 'hide-before');
			}

			var dialog;

			var addEmailToAddressBookNodeLink = BX.Dom.create('span', {
				props: {
					className: 'ui-selector-footer-link ui-selector-footer-link-add'
				},
				text: BX.Loc.getMessage('MAIN_MAIL_FORM_ADDRESS_BOOK_FOOTER_ADD_BUTTON_MSGVER_1'),
				events: {
					click: function(){
						if (!loader.isShown())
						{
							showLoader();
							this.addContactToAddressBook(dialog).then(function(){
								hideLoader();
							}.bind(this)).catch(function(){
								hideLoader();
							}.bind(this));
						}
					}.bind(this),
				}
			});

			loader = new BX.Loader({
				color: '#3bc8f5',
				offset: {
					left: 'calc(-50% - 19px)',
					top: '-2px',
				},
				size: 29,
				target: addEmailToAddressBookNodeLink
			});

			dialogAdditionalOptions = {
				searchTabOptions: {
					stub: true,
					stubOptions: {
						title: BX.Loc.getMessage('MAIN_MAIL_FORM_ADDRESS_BOOK_EMPTY_SEARCH_TITLE_MSGVER_1'),
						subtitle: BX.Loc.getMessage('MAIN_MAIL_FORM_ADDRESS_BOOK_EMPTY_SEARCH_SUBTITLE_MSGVER_1') +
							'<br>' +
							`<a style="cursor: pointer;" onclick="top.BX.Helper.show('redirect=detail&code=${codeArticle}');">` +
							BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_2') +
							'</a>',
						icon: '/bitrix/images/mail/entity_provider_icons/addressbook.svg',
						iconOpacity: 85,
						arrow: true,
					}
				},
				footer: [
					addEmailToAddressBookNodeLink,
				],
			};

			dialogSearchOptions = {
				allowCreateItem: true,
				footerOptions: {
					label: BX.Loc.getMessage('MAIN_MAIL_FORM_ADDRESS_BOOK_FOOTER_ADD_BUTTON_ALLOW_CREATE_ITEM_MSGVER_1'),
				}
			};

			selectorEvents = {
				onInput: function(event) {
					const selector = event.getTarget();
					const text = selector.getTextBoxValue();
					this.addSearchInput(text);
				}.bind(this),
				onBlur: function() {
					this.lastSearchText = '';
				}.bind(this),
				'TagItem:onClick': (event) => {
					const tagItem = event.getData().item;
					if (tagItem && tagItem.entityId === 'address_book')
					{
						const item = dialog.getItem([tagItem.getEntityId(), tagItem.getId()]);
						const customData = item.getCustomData();
						let id = Number(customData.get('entityId'));
						const email = customData.get('email');
						const name = customData.get('name');

						if (dialog.getTagSelector().isLocked())
						{
							return;
						}

						dialog.getTagSelector().lock();

						if (!BX.type.isNumber(id) || id === 0)
						{
							BX.ajax.runAction('mail.addressbook.getContactIdByEmail', {
								data: {
									email,
								},
							}).then((response) => {
								id = response.data;
								if (BX.type.isNumber(id) && id > 0)
								{
									this.openEditContact(dialog, id, email, name);
								}
							});
						}
						else
						{
							this.openEditContact(dialog, id, email, name);
						}
					}
				},
			};

			dialogEvents = {
				'Search:onItemCreateAsync': function(event) {
					return new Promise(function(resolve, reject) {
						const searchQuery = event.getData().searchQuery.getQuery()
						this.addContactToAddressBook(dialog, searchQuery).then(function(){
							resolve();
						}.bind(this)).catch(function(){
							reject();
						}.bind(this));
					}.bind(this))
				}.bind(this),
			};
		}
		else
		{
			let crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE');
			let crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE');

			switch (ownerType)
			{
				case 'DEAL':
					crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE_DEAL');
					crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_DEAL');
					break;
				case 'COMPANY':
					crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE_COMPANY');
					crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_COMPANY');
					break;
				case 'LEAD':
					crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE_LEAD');
					crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_LEAD');
					break;
				case 'CONTACT':
					crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE_CONTACT');
					crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_CONTACT');
					break;
				case 'SMART_INVOICE':
					crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE_SMART_INVOICE');
					crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_SMART_INVOICE');
					break;
				case 'QUOTE':
					crmEmptyTitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_TITLE_QUOTE');
					crmEmptySubtitleStub = BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_QUOTE');
					break;
			}

			dialogAdditionalOptions = {
				searchTabOptions: {
					stub: true,
					stubOptions: {
						title: crmEmptyTitleStub,
						subtitle: crmEmptySubtitleStub +
							'<br>' +
							`<a style="cursor: pointer;" onclick="top.BX.Helper.show('redirect=detail&code=${codeArticle}');">` +
							BX.Loc.getMessage('MAIN_MAIL_FORM_CRM_EMPTY_SEARCH_SUBTITLE_2') +
							'</a>',
					}
				},
			};
		}

		var selectedItemsDialog = [];

		switch (type.toUpperCase())
		{
			case 'DATA[TO]':
				if (isReplyAll === true)
				{
					selectedItemsDialog = replyTo;
				}
				else
				{
					selectedItemsDialog = selectedRecipients;
				}
				break;
			case 'DATA[CC]':
				if (isReplyAll === true)
				{
					selectedItemsDialog = replyCC;
				}
				break;
		}

		if (((contextName === 'MAIL' || oldRecipientsMode) && ['DATA[TO]', 'DATA[CC]', 'DATA[BCC]'].includes(type.toUpperCase())) ||
			['DATA[CC]', 'DATA[BCC]'].includes(type.toUpperCase()))
		{
			entitiesDialog.push({
				id: 'user',
				filters: [
					{
						id: 'mail.mailUserRecipientAppearanceFilter',
					},
				],
				options: {
					showInvitationFooter: false,
					onlyWithEmail: true,
				},
			});
		}

		const tagSelector = new BX.UI.EntitySelector.TagSelector({
			textBoxWidth: 220,
			tagMaxWidth: 400,
			...selectorAdditionalOptions,
			dialogOptions: Object.assign(
				{
					events: dialogEvents,
					searchOptions: dialogSearchOptions,
					context: 'MAIN_MAIL_FROM',
					id: dialogId,
					entities: entitiesDialog,
					selectedItems: selectedItemsDialog,
				},
				dialogAdditionalOptions,
			),
			events: selectorEvents,
		});

		dialog = tagSelector.getDialog();

		this.setFieldData(
			type,
			dialog,
			[],
			fieldNode,
			tagSelector,
		);

		tagSelector.renderTo(fieldNode);
	}

	BXMainMailForm.prototype.init = function(props)
	{
		props = props || {};
		var form = this;
		var isReplyAll = props.isReplyAll ?? false;
		var selectedRecipients = this.selectedRecipients ?? [];
		var replyTo = this.replyTo ?? [];
		var replyCC = this.replyCC ?? [];
		var ownerId = this.options.ownerId ?? null;
		var ownerType = this.options.ownerType ?? null;
		var contextName = this.options.contextName ?? null;
		var hideEmptyContactError = Boolean(props.hideEmptyContactError);

		if (this.__inited)
		{
			return false;
		}

		this.formId = 'main_mail_form_'+this.id;

		var formFieldsCollection = document.querySelectorAll('[data-field-form-id="' + this.formId + '"]');
		var formFieldsArray = Array.prototype.slice.call(formFieldsCollection);

		formFieldsArray.forEach(function(fieldNode) {
			var fieldFormId = fieldNode.dataset.fieldFormId;
			var fieldType = fieldNode.dataset.formFieldType;
			this.renderField(fieldNode, fieldType, fieldFormId, ownerId, ownerType, selectedRecipients, replyTo, replyCC, isReplyAll, contextName);
		}.bind(this));

		this.configureMenuItemId = 'signature-configure';
		this.formWrapper = BX(this.formId);
		this.htmlForm = BX.findParent(this.formWrapper, {tag: 'form'});

		if (contextName !== 'MAIL' && selectedRecipients.length === 0 && !hideEmptyContactError)
		{
			let text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION');

			switch (ownerType)
			{
				case 'DEAL':
					text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION_DEAL');
					break;
				case 'COMPANY':
					text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION_COMPANY');
					break;
				case 'LEAD':
					text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION_LEAD');
					break;
				case 'CONTACT':
					text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION_CONTACT');
					break;
				case 'SMART_INVOICE':
					text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION_SMART_INVOICE');
					break;
				case 'QUOTE':
					text = BX.Loc.getMessage('MAIN_MAIL_FORM_MESSAGE_SEND_WARNING_EMPTY_RECIPIENT_DESCRIPTION_QUOTE');
					break;
			}

			const alert = new BX.UI.Alert({
				text,
				color: BX.UI.Alert.Color.WARNING,
			});

			alert.renderTo(BX('main-mail-form-message-send-warning-empty'));
		}

		this.postForm = LHEPostForm.getHandler(this.formId+'_editor');
		this.editor = BXHtmlEditor.Get(this.formId+'_editor');
		// this.editor.config.autoLink = false;
		this.editorInited = false;

		this.timestamp = (new Date).getTime();

		// id names for smart nodes
		this.quoteNodeId = this.formId + '_quote_' + this.timestamp.toString(16);
		this.signatureNodeId = this.formId + '_signature_' + this.timestamp.toString(16);

		this.sharingLinkClassPrefix = '_sharing_calendar_link';
		this.sharingLinkNodeClass = this.formId + this.sharingLinkClassPrefix;

		// insert signature on change 'from' field
		BX.addCustomEvent(this, 'MailForm::from::change', BX.proxy(function(field, signature)
		{
			if(!BX.type.isString(signature))
			{
				signature = '';
				var currentSignatures = form.getSenderSignatures(field);
				var firstSignature = currentSignatures[0];
				if (BX.type.isNotEmptyObject(firstSignature) && BX.type.isNotEmptyString(firstSignature.full))
				{
					signature = firstSignature.full;
				}
			}
			this.rebuildSignatureMenu(currentSignatures, field.params);
			this.insertSignature(signature);
			this.appendCalendarLinkButton(field.params);
		}, this));

		this.initFields();
		this.initFooter();

		BX.bind(this.htmlForm, 'submit', this.onSubmit.bind(this));

		this.__inited = true;

		BX.onCustomEvent(BXMainMailForm, 'MailForm:init:'+this.id, [this]);

		BX.addCustomEvent(this, 'MailForm::editor::init', () => {
			this.showCalendarSharingInitialTour();
			BX(this.editor.GetIframeDoc()).onmouseup = () => {
				this.userSelection = this.editor.GetIframeDoc().body;
			};
			this.userSelection = this.editor.GetIframeDoc().body;
		});

		document.addEventListener('selectionchange', () => {
			this.userSelection = document.getSelection();
		});

		this.hideAiImageGeneratorButton();

		return true;
	};

	BXMainMailForm.prototype.initScrollable = function()
	{
		if (!this.__scrollable)
		{
			if (document.scrollingElement)
				this.__scrollable = document.scrollingElement;
		}

		if (!this.__scrollable)
		{
			if (document.documentElement.scrollTop > 0 || document.documentElement.scrollLeft > 0)
				this.__scrollable = document.documentElement;
			else if (document.body.scrollTop > 0 || document.body.scrollLeft > 0)
				this.__scrollable = document.body;
		}

		if (!this.__scrollable)
		{
			window.scrollBy(1, 1);

			if (document.documentElement.scrollTop > 0 || document.documentElement.scrollLeft > 0)
				this.__scrollable = document.documentElement;
			else if (document.body.scrollTop > 0 || document.body.scrollLeft > 0)
				this.__scrollable = document.body;

			window.scrollBy(-1, -1);
		}
	}

	BXMainMailForm.prototype.initFields = function()
	{
		for (var i = 0, fieldId; i < this.fields.length; i++)
		{
			this.fields[i] = new BXMainMailFormField(this, this.fields[i]);

			fieldId = this.fields[i].fieldId;
			this.fields[fieldId] = this.fields[i];
		}

		// hidden fields switches
		var fieldsFooter = BX(this.formId+'_fields_footer');
		var fieldsExtFooter = BX(this.formId+'_fields_ext_footer');
		var hiddenFields = []
			.concat(BX.findChildrenByClassName(fieldsFooter, 'main-mail-form-field-button', true) || [])
			.concat(BX.findChildrenByClassName(fieldsExtFooter, 'main-mail-form-field-button', true) || []);
		for (var i = 0, fieldId; i < hiddenFields.length; i++)
		{
			fieldId = hiddenFields[i].getAttribute('data-target');
			if (typeof this.fields[fieldId] != 'undefined')
			{
				this.fields[fieldId].__switch = hiddenFields[i];
				BX.bind(hiddenFields[i], 'click', this.fields[fieldId].unfold.bind(this.fields[fieldId]));
			}
		}
	}

	BXMainMailForm.prototype.initFooter = function()
	{
		var form = this;

		var footerWrapper = BX.findChildByClassName(this.formWrapper, 'main-mail-form-footer-wrapper', true);
		var footer = BX.findChildByClassName(footerWrapper, 'main-mail-form-footer', false);

		this.footerNode = footer;

		var footerButtons = BX.findChildrenByClassName(footer, 'main-mail-form-footer-button', true);
		for (var i in footerButtons)
		{
			(function(button)
			{
				BX.bind(button, 'click', function ()
				{
					BX.onCustomEvent(form, 'MailForm:footer:buttonClick', [form, button]);
					if (BX.hasClass(button, 'main-mail-form-submit-button'))
						BX.submit(form.htmlForm);
				});
			})(footerButtons[i]);
		}

		var resetFooter = function ()
		{
			if (BX.hasClass(footer, 'main-mail-form-footer-fixed'))
			{
				BX.removeClass(footer, 'main-mail-form-footer-fixed-hidden');
				BX.removeClass(footer, 'main-mail-form-footer-fixed');
				footer.style.left = '';
				footer.style.width = '';
				footerWrapper.style.height = '';
				footerWrapper.appendChild(footer);
			}
		};

		var positionFooter = function()
		{
			form.initScrollable();

			if (form.formWrapper.offsetHeight > 0 && form.__scrollable)
			{
				var pos0 = BX.pos(form.__scrollable);
				var pos1 = BX.pos(form.formWrapper);

				if (pos0.bottom < pos1.bottom-10-form.__scrollable.scrollTop)
				{
					footer.style.left = (pos1.left-pos0.left-form.__scrollable.scrollLeft)+'px';
					footer.style.width = form.formWrapper.offsetWidth+'px';

					if (!BX.hasClass(footer, 'main-mail-form-footer-fixed'))
					{
						if (pos0.bottom < BX.pos(footerWrapper).top-form.__scrollable.scrollTop)
							BX.addClass(footer, 'main-mail-form-footer-fixed-hidden');
						footerWrapper.style.height = footerWrapper.offsetHeight+'px';
						BX.addClass(footer, 'main-mail-form-footer-fixed');
						document.body.appendChild(footer);
					}

					var editorWrapper = BX.findChildByClassName(form.formWrapper, 'main-mail-form-editor-wrapper', true);
					if (pos0.bottom < BX.pos(editorWrapper).top+footer.offsetHeight-form.__scrollable.scrollTop)
						BX.addClass(footer, 'main-mail-form-footer-fixed-hidden');
					else
						BX.removeClass(footer, 'main-mail-form-footer-fixed-hidden');

					return;
				}
			}

			resetFooter();
		};

		var scrollableObserver = new MutationObserver(function ()
		{
			form.initScrollable();

			if (form.__scrollable)
			{
				var state = [
					form.__scrollable.scrollHeight,
					form.__scrollable.scrollTop
				].join(':');

				if (form.__scrollable.__lastState != state)
				{
					form.__scrollable.__lastState = state;

					positionFooter();
				}
			}
		});
		var startMonitoring = function ()
		{
			setTimeout(function ()
			{
				if (!form.__footerMonitoring)
				{
					form.__footerMonitoring = true;

					scrollableObserver.observe(
						document.body,
						{
							attributes: true,
							childList: true,
							subtree: true
						}
					);

					BX.bind(window, 'resize', positionFooter);
					BX.bind(window, 'scroll', positionFooter);
					BX.addCustomEvent(window, 'AutoResizeFinished', positionFooter); // OnEditorResizedAfter

					positionFooter();
				}
			}, 400);
		};
		var stopMonitoring = function ()
		{
			form.__footerMonitoring = false;

			scrollableObserver.disconnect();

			BX.unbind(window, 'resize', positionFooter);
			BX.unbind(window, 'scroll', positionFooter);
			BX.removeCustomEvent(window, 'AutoResizeFinished', positionFooter); // OnEditorResizedAfter

			resetFooter();
		};

		BX.addCustomEvent(this, 'MailForm:show', startMonitoring);
		BX.addCustomEvent(this, 'MailForm:hide', stopMonitoring);

		if (this.formWrapper.offsetHeight > 0)
			startMonitoring();
	}

	BXMainMailForm.prototype.insertSignature = function(signature)
	{
		if(this.editorInited)
		{
			this.editor.synchro.Sync();
			var signatureNode = this.editor.GetIframeDoc().getElementById(this.signatureNodeId);
			if(!BX.type.isNotEmptyString(signature))
			{
				if(signatureNode)
				{
					BX.remove(signatureNode);
				}

				const quoteNode = this.editor.GetIframeDoc().getElementById(this.quoteNodeId);
				if (quoteNode && !quoteNode.previousSibling)
				{
					BX.Dom.insertBefore(BX.Tag.render`<br>`, quoteNode);
				}

				return;
			}
			var signatureHtml = '--<br />' + signature;
			if(signatureNode)
			{
				signatureNode.innerHTML = signatureHtml;
			}
			else
			{
				signatureNode = BX.create('div', {
					attrs: {
						id: this.signatureNodeId
					},
					html: signatureHtml
				});
				var quoteNode = this.editor.GetIframeDoc().getElementById(this.quoteNodeId);
				if(quoteNode)
				{
					quoteNode.parentNode.insertBefore(signatureNode, quoteNode);
				}
				else
				{
					BX.append(signatureNode, this.editor.GetIframeDoc().body);
				}

				signatureNode.parentNode.insertBefore(document.createElement('BR'), signatureNode);
			}
			this.editor.synchro.FullSyncFromIframe();
		}
		else
		{
			// if editor is not inited yet - do it later
			BX.addCustomEvent(this, 'MailForm::editor::init', BX.proxy(function()
			{
				this.insertSignature(signature);
			}, this));
		}
	};

	var BXMainMailFormField = function(form, params)
	{
		this.form = form;
		this.params = params;

		this.fieldId = this.form.formId+'_'+this.params.id;

		this.init();
	};

	BXMainMailFormField.prototype.init = function()
	{
		this.params.__row = BX(this.fieldId);

		if (BXMainMailFormField.__types[this.params.type] && BXMainMailFormField.__types[this.params.type].init)
			BXMainMailFormField.__types[this.params.type].init(this);

		if (this.params.menu)
		{
			var field = this;
			var menuExtButton = BX.findChildByClassName(this.params.__row, 'main-mail-form-field-value-menu-ext-button', true);

			BX.addCustomEvent(this.form, 'MailForm::editor:click', function ()
			{
				var menu = BX.PopupMenu.getMenuById(field.fieldId+'-menu-ext');

				if (menu)
					menu.close();
			});

			BX.addCustomEvent('onSubMenuShow', function ()
			{
				var menuWindow = this.menuWindow;
				while (menuWindow.parentMenuWindow)
					menuWindow = menuWindow.parentMenuWindow;

				if (field.fieldId+'-menu-ext' == menuWindow.id)
					BX.addClass(this.subMenuWindow.popupWindow.popupContainer, 'main-mail-form-field-value-menu-ext-content');
			});

			BX.bind(menuExtButton, 'click', function ()
			{
				BX.onCustomEvent(field.form, 'MailForm:field:setMenuExt', [field.form, field]);

				const result = [];
				field.__menuExt.forEach((item) => {
					if (item.value === null
						|| !BX.type.isString(item.text)
						|| item.text.length === 0
					)
					{
						return;
					}

					if (item.items.length === 0)
					{
						result.push({
							id: item.value,
							entityId: item.text,
							title: item.text,
							customData: {
								field: item.value,
							},
							tabs: ['recents']
						});
						return;
					}

					const children = [];

					item.items.forEach((child) => {
						if (
							child.value !== undefined
							&& child.text !== undefined
							&& child.value.length > 0
							&& child.text.length > 0
						)
						{
							children.push(
								{
									supertitle: item.text,
									id: child.value,
									entityId: child.text,
									title: child.text,
									customData: {
										field: child.value,
									},
									tabs: ['recents'],
								},
							)
						}
					});

					result.push({
						id: item.value,
						entityId: item.text,
						title: item.text,
						tabs: ['recents'],
						children,
					});
				});

				const dialog = new BX.UI.EntitySelector.Dialog({
					targetNode: this,
					width: 500,
					height: 300,
					multiple: false,
					dropdownMode: true,
					showAvatars: false,
					compactView: true,
					enableSearch: true,
					items: result,
					events: {
						'Item:onBeforeSelect': (event) => {
							event.preventDefault();

							field.insert(event.getData().item.id);
						},
					}
				});

				dialog.show();
			});
		}
	}

	BXMainMailFormField.prototype.setMenuExt = function(items)
	{
		this.__menuExt = items;
	}

	BXMainMailFormField.prototype.insert = function(text)
	{
		if (BXMainMailFormField.__types[this.params.type] && BXMainMailFormField.__types[this.params.type].insert)
			BXMainMailFormField.__types[this.params.type].insert(this, text);
	}

	BXMainMailFormField.prototype.setValue = function(value, options)
	{
		if (BXMainMailFormField.__types[this.params.type] && BXMainMailFormField.__types[this.params.type].setValue)
			BXMainMailFormField.__types[this.params.type].setValue(this, value, options);
	}

	BXMainMailFormField.prototype.show = function()
	{
		// @TODO: enable form fields
		this.params.hidden = false;

		BX.addClass(this.fieldId, 'main-mail-form-drop-animation');

		BX(this.fieldId).style.display = this.params.folded ? 'none' : '';
		this.__switch.style.display = this.params.folded ? '' : 'none';
	}

	BXMainMailFormField.prototype.hide = function()
	{
		// @TODO: disable form fields
		this.params.hidden = true;

		BX(this.fieldId).style.display = 'none';
		this.__switch.style.display = 'none';

		BX.removeClass(this.fieldId, 'main-mail-form-drop-animation');
	}

	BXMainMailFormField.prototype.fold = function()
	{
		this.params.folded = true;

		if (!this.params.hidden)
			this.__switch.style.display = '';

		BX(this.fieldId).style.display = 'none';
		BX.removeClass(this.fieldId, 'main-mail-form-drop-animation');
	}

	BXMainMailFormField.prototype.unfold = function()
	{
		this.params.folded = false;

		if (!this.params.hidden)
		{
			BX.addClass(this.fieldId, 'main-mail-form-drop-animation');
			BX(this.fieldId).style.display = '';
		}

		this.__switch.style.display = 'none';
	}

	BXMainMailFormField.__types = {
		'list': {},
		'text': {},
		'from': {},
		'rcpt': {},
		'editor': {},
		'files': {}
	};

	BXMainMailFormField.__types['list'].init = function(field)
	{
		BX.addCustomEvent(field.form, 'MailForm::editor:click', function ()
		{
			var menu = BX.PopupMenu.getMenuById(field.fieldId+'-menu');

			if (menu)
				menu.close();
		});

		var selector = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-menu', true);
		BX.bind(selector, 'click', function()
		{
			var input = BX(field.fieldId+'_value');
			var apply = function(value, text)
			{
				input.value = value;
				BX.adjust(selector, { html: text });
			};
			var handler = function(event, item)
			{
				apply(item.options.value, item.text);
				item.menuWindow.close();
			};

			var items = [];

			if (!field.params.required)
			{
				items.push({
					text: BX.util.htmlspecialchars(field.params.placeholder),
					title: field.params.placeholder,
					value: '',
					onclick: handler
				});
				items.push({ delimiter: true });
			}

			for (var i in field.params.list)
			{
				items.push({
					text: BX.util.htmlspecialchars(field.params.list[i]),
					title: field.params.list[i],
					value: i,
					onclick: handler
				});
			}

			BX.PopupMenu.show(
				field.fieldId+'-menu',
				selector, items,
				{
					className: 'main-mail-form-field-value-menu-content',
					offsetLeft: 40,
					angle: true,
					closeByEsc: true
				}
			);
		});
	};

	BXMainMailFormField.__types['from'].init = function(field)
	{
		BX.addCustomEvent(field.form, 'MailForm::editor:click', function ()
		{
			var menu = BX.PopupMenu.getMenuById(field.fieldId+'-menu');

			if (menu)
				menu.close();
		});

		BX.onCustomEvent(field.form, 'MailForm::from::change', [field]);
		const senderInputNode = BX(`${field.fieldId}_value`);
		let senderButtonTextNode = null;

		if (senderInputNode)
		{
			senderButtonTextNode = senderInputNode.parentNode.querySelector('.sender-selector-button-text');
		}

		if (BX.UI.Mail?.SenderSelector && senderButtonTextNode)
		{
			const observer = new MutationObserver(() => {
				BX.onCustomEvent(field.form, 'MailForm::from::change', [field]);
			});

			observer.observe(senderButtonTextNode, {
				childList: true,
				subtree: true,
			});

			return;
		}

		var selector = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-menu', true);
		BX.bind(selector, 'click', function()
		{
			var input = BX(field.fieldId + '_value');

			BXMainMailConfirm.showList(
				field.fieldId,
				selector,
				{
					required: field.params.required,
					placeholder: field.params.placeholder,
					selected: input.value,
					callback: function (value, text)
					{
						input.value = value;
						BX.adjust(selector, {html: BX.util.strip_tags(text)});
						BX.onCustomEvent(field.form, 'MailForm::from::change', [field]);
					}
				}
			);
		});
	};

	BXMainMailFormField.__types['rcpt'].init = function(field)
	{
		var more    = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-rcpt-item-more', true);
		var wrapper = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-wrapper', true);
		var link    = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-rcpt-add-link', true);
		var input   = BX(field.fieldId+'_fvalue');

		field.selector = field.fieldId+'-selector';

		var select = function(item, type, search, undeleted, name, state)
		{
			// BX.hide(BX.findChildByClassName(createForm, 'crm-task-list-mail-reply-error', true));

			if (!field.params.multiple)
			{
				var selected = BX.SocNetLogDestination.getSelected(field.selector);
				for (var i in selected)
				{
					if (i != item.id || selected[i] != type)
						BX.SocNetLogDestination.deleteItem(i, selected[i], field.selector);
				}
			}

			var itemWrapper = document.createElement('SPAN');
			itemWrapper.setAttribute('data-id', item.id);
			BX.addClass(itemWrapper, 'main-mail-form-field-rcpt-item');
			wrapper.insertBefore(itemWrapper, more.parentNode);

			itemWrapper.appendChild(BX.create('INPUT', {
				'props': {
					'type': 'hidden',
					'name': field.params.name+'[]',
					'value': JSON.stringify(item)
				}
			}));

			item.showEmail = 'N';
			if (field.params.email && item.email && item.email.length > 0 && item.email != item.name)
			{
				item = BX.clone(item);
				item.name = item.name+' &lt;' + item.email + '&gt;';
			}

			BX.SocNetLogDestination.BXfpSelectCallback({
				item: item,
				type: type,
				varName: 'dummy_'+field.params.name,
				bUndeleted: false,
				containerInput: itemWrapper,
				valueInput: input,
				formName: name,
				tagInputName: link,
				tagLink1: field.params.placeholder,
				tagLink2: field.params.placeholder
			});

			if ('init' == state)
			{
				var limit = 9;
				var items = BX.findChildrenByClassName(wrapper, 'main-mail-form-field-rcpt-item', false);
				if (items.length > limit+1)
				{
					for (var i = limit; i < items.length; i++)
						items[i].style.display = 'none';

					more.setAttribute('title', more.getAttribute('title').replace(/-?\d+/, items.length-limit));
					more.parentNode.style.display = '';
				}
			}
		};

		var unselect = function(item, type, search, name)
		{
			var itemWrapper = BX.findChild(wrapper, {attribute: {'data-id': item.id}}, false);

			BX.SocNetLogDestination.BXfpUnSelectCallback.apply({
				formName: name,
				inputContainerName: itemWrapper,
				inputName: input,
				tagInputName: link,
				tagLink1: field.params.placeholder,
				tagLink2: field.params.placeholder
			}, [item]);

			if (itemWrapper && itemWrapper.parentNode == wrapper)
			{
				if (!BX.findChildByClassName(itemWrapper, 'feed-add-post-destination'))
					wrapper.removeChild(itemWrapper);
			}

			var limit = 9;
			var visible = 0;
			var items = BX.findChildrenByClassName(wrapper, 'main-mail-form-field-rcpt-item', false);
			for (var i = 0; i < items.length; i++)
			{
				if (items[i].offsetHeight > 0)
					visible++;
			}

			if (visible < items.length && (visible < limit || items.length <= limit+1))
			{
				for (var i = 0; i < items.length; i++)
				{
					if (items[i].offsetHeight > 0)
						continue;

					items[i].style.display = '';
					visible++;

					if (visible >= Math.min(limit, items.length) && items.length > limit+1)
						break;
				}

				more.setAttribute('title', more.getAttribute('title').replace(/-?\d+/, items.length-limit));
				if (visible >= items.length)
					more.parentNode.style.display = 'none';
			}
		};

		if (field.form.options.version < 2)
		{
			var selectorParams = {
				name: field.selector,
				searchInput: input,
				bindMainPopup: {
					node: wrapper,
					offsetTop: '5px',
					offsetLeft: '15px'
				},
				bindSearchPopup : {
					node: wrapper,
					offsetTop: '5px',
					offsetLeft: '15px'
				},
				callback: {
					select: select,
					unSelect: unselect,
					openDialog: BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
						inputBoxName: input.parentNode,
						inputName: input,
						tagInputName: link
					}),
					closeDialog: function()
					{
						BX.onCustomEvent(field.form, 'MailForm:field:rcptSelectorClose', [field.form, field]);
						BX.SocNetLogDestination.BXfpCloseDialogCallback.apply({
							inputBoxName: input.parentNode,
							inputName: input,
							tagInputName: link
						});
					},
					openSearch: BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
						inputBoxName: input.parentNode,
						inputName: input,
						tagInputName: link
					})
				},
				items: {},
				itemsLast: {},
				itemsSelected: {},
				destSort: {}
			};

			if (field.params.selector)
			{
				for (var i in field.params.selector)
					selectorParams[i] = field.params.selector[i];
			}

			BX.SocNetLogDestination.init(selectorParams);

			BX.bind(input, 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
				formName: field.selector,
				inputName: input
			}));
			BX.bind(input, 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
				formName: field.selector,
				inputName: input,
				tagInputName: link
			}));
			BX.bind(input, 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
				formName: field.selector,
				inputName: input,
				tagInputName: link,
				onPasteEvent: true
			}));
			BX.bind(input, 'blur', BX.delegate(BX.SocNetLogDestination.BXfpBlurInput, {
				inputBoxName: input.parentNode,
				tagInputName: link
			}));

			BX.bind(wrapper, 'click', function(e)
			{
				BX.SocNetLogDestination.openDialog(field.selector);
				BX.PreventDefault(e);
			});
		}

		BX.bind(more, 'click', function(e)
		{
			var items = BX.findChildrenByClassName(wrapper, 'main-mail-form-field-rcpt-item', false);
			for (var i = 0; i < items.length; i++)
				items[i].style.display = '';

			this.parentNode.style.display = 'none';

			BX.PreventDefault(e);
		});
	};

	BXMainMailFormField.__types['editor'].init = function(field)
	{
		var postForm = field.form.postForm;
		var editor = field.form.editor;

		field.quoteNode = document.createElement('div');
		if (field.form.options.foldQuote || field.params.value)
		{
			if (field.form.formId.includes('_crm_mail_template_edit_form_') && field.params.value)
			{
				field.quoteNode.innerHTML = field.params.value;
			}
			else
			{
				const quoteContentNode = document.createElement('div');
				quoteContentNode.setAttribute('id', field.form.quoteNodeId);
				if (field.params.value)
				{
					quoteContentNode.innerHTML = field.params.value;
				}
				else
				{
					quoteContentNode.innerHTML = '<br>';
				}
				BX.Dom.append(quoteContentNode, field.quoteNode);
			}
		}
		field.quoteNode.__folded = field.form.options.foldQuote ?? false;

		//postForm.controllerInit('hide');
		BX.onCustomEvent(postForm.eventNode, 'OnShowLHE', ['justShow']);

		BX.addClass(editor.dom.cont, 'main-mail-form-editor');
		editor.dom.toolbarCont.style.opacity = 'inherit';

		// close rctp selectors on focus on html-editor
		BX.addCustomEvent(
			editor, 'OnIframeClick',
			function()
			{
				if (field.form.options.version < 2)
				{
					BX.SocNetLogDestination.abortSearchRequest();
					BX.SocNetLogDestination.closeSearch();
					BX.SocNetLogDestination.closeDialog();
				}

				BX.onCustomEvent(field.form, 'MailForm::editor:click', []);
			}
		);

		// append original message quote
		var quoteButton = BX.findChildByClassName(field.form.htmlForm, 'main-mail-form-quote-button', true);
		var quoteHandler = function()
		{
			if (field.quoteNode.__folded)
			{
				field.quoteNode.__folded = false;

				field.setValue(editor.GetContent(), {quote: true, signature: false});
				editor.Focus(false);

				BX.hide(quoteButton.parentNode.parentNode || quoteButton.parentNode)

				const editorIframeCopilot = editor.iframeView?.copilot;
				if (
					!editorIframeCopilot
					|| BX.Type.isFunction(editorIframeCopilot?.copilot?.setContextParameters)
				)
				{
					return;
				}
				const newContextParams = editorIframeCopilot.copilotParams?.contextParameters ?? {};
				newContextParams.isAddedQuote = true;
				editorIframeCopilot.copilot.setContextParameters(newContextParams);
			}
		};
		BX.bind(quoteButton, 'click', quoteHandler);

		// append original message quote on switch from wysiwyg mode
		var modeHandler = function ()
		{
			if (editor.GetViewMode() != 'wysiwyg')
			{
				BX.removeCustomEvent(editor, 'OnSetViewAfter', modeHandler);
				quoteHandler();
			}
		};
		BX.addCustomEvent(editor, 'OnSetViewAfter', modeHandler);

		// wysiwyg -> code inline-attachments parser
		if (postForm.parser)
		{
			postForm.parser.disk_file.regexp = /(bxacid):(n?\d+)/ig;
		}
		editor.phpParser.AddBxNode('diskfile0', {
			Parse: function (params, bxid)
			{
				var node = editor.GetIframeDoc().getElementById(bxid) || BX.findChild(field.quoteNode, {attr: {id: bxid}}, true);
				var params = editor.GetBxTag(bxid);

				if (node && params)
				{
					var dummy = document.createElement('DIV');

					node = node.cloneNode(true);
					dummy.appendChild(node);

					if (node.tagName.toUpperCase() == 'IMG')
					{
						var image = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

						node.setAttribute('data-bx-orig-src', node.getAttribute('src'));
						node.setAttribute('src', image);

						return dummy.innerHTML.replace(image, 'bxacid:'+params.fileId);
					}

					return dummy.innerHTML;
				}

				return '[ ' + params.value + ' ]';
			}
		});

		// clear inline-attachments on attachment remove
		BX.addCustomEvent(
			postForm.eventNode, 'OnFileUploadRemove',
			function (result)
			{
				editor.synchro.Sync();

				for (i in editor.bxTags)
				{
					if (editor.bxTags[i].fileId && editor.bxTags[i].fileId == result)
					{
						var node = editor.GetIframeDoc().getElementById(editor.bxTags[i].id);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						var node = BX.findChild(field.quoteNode, {attr: {id: editor.bxTags[i].id}}, true);
						if (node && node.parentNode)
							node.parentNode.removeChild(node);

						delete editor.bxTags[i];
					}
				}

				editor.synchro.FullSyncFromIframe();
			}
		);

		// initialize editor content
		BX.addCustomEvent(
			editor, 'OnCreateIframeAfter',
			function ()
			{
				field.setValue('', {quote: true, signature: true});
				field.form.editorInited = true;
				BX.onCustomEvent(field.form, 'MailForm::editor::init', [field]);
			}
		);

		BX.addCustomEvent(field.form, 'MailForm:show', function ()
		{
			field.form.editor.CheckAndReInit();
			field.form.editor.ResizeSceleton();
		});

		BX.addCustomEvent(field.form, 'MailForm:hide', function ()
		{
			field.form.editor.SaveContent();
		});

		BX.addCustomEvent(
			field.form, 'MailForm:submit',
			function ()
			{
				var value = editor.GetContent();
				if (field.quoteNode.__folded)
					value += editor.Parse(field.quoteNode.innerHTML, true, false);

				BX(field.fieldId+'_value').value = value;
			}
		);
	};

	BXMainMailFormField.__types['from'].setValue = function(field, value)
	{
		var input = BX(field.fieldId+'_value');
		let selector = BX.findChildByClassName(field.params.__row, 'sender-selector-button-text', true);
		if (!selector)
		{
			selector = BX.findChildByClassName(field.params.__row, 'main-mail-form-field-value-menu', true);
		}

		if (!selector)
		{
			return;
		}

		if (!value.trim())
		{
			if (!field.params.required)
			{
				input.value = '';
				BX.adjust(selector, {html: ''});
			}
			BX.onCustomEvent(field.form, 'MailForm::from::change', [field, '']);

			return;
		}

		if (field.params.mailboxes && field.params.mailboxes.length > 0)
		{
			var escRegex = new RegExp('[-\/\\^$*+?.()|[\]{}]', 'g');
			for (var i in field.params.mailboxes)
			{
				var pattern = new RegExp(
					'(^|<)' + field.params.mailboxes[i].email.replace(escRegex, '\\$&') + '(>|$)', 'i'
				);
				if (value.trim().match(pattern))
				{
					input.value = value;
					BX.adjust(selector, {html: BX.util.htmlspecialchars(value)});
					BX.onCustomEvent(field.form, 'MailForm::from::change', [field]);

					break;
				}
			}
		}
	};

	BXMainMailFormField.__types['rcpt'].setValue = function(field, value)
	{
		if (field.form.options.version < 2)
		{
			var selected = BX.SocNetLogDestination.getSelected(field.selector);
			for (var id in selected)
				BX.SocNetLogDestination.deleteItem(id, selected[id], field.selector);
		}

		if (value && BX.type.isPlainObject(value))
		{
			if (field.form.options.version < 2)
			{
				for (var id in value)
				{
					if (value.hasOwnProperty(id))
					{
						BX.SocNetLogDestination.obItemsSelected[field.selector][id] = value[id];
						BX.SocNetLogDestination.runSelectCallback(id, value[id], field.selector, false, 'init');
					}
				}
			}

			BX.onCustomEvent("BX.Main.SelectorV2:reInitDialog", [ {
				selectorId: field.params.id,
				selectedItems: BX.clone(value)
			} ]);
		}
	};

	BXMainMailFormField.__types['text'].insert = function(field, text)
	{
		var input = BX(field.fieldId+'_value');

		if (typeof input.selectionStart != 'undefined')
		{
			var selection = {
				start: input.selectionStart,
				end: input.selectionEnd
			};

			input.value = input.value.substr(0, selection.start) + text + input.value.substr(selection.end);
			input.selectionStart = input.selectionEnd = selection.start + text.length;
		}
		else
		{
			input.value = input.value + text;
		}

		input.focus();
	};

	BXMainMailFormField.__types['text'].setValue = function(field, value)
	{
		var input = BX(field.fieldId+'_value');

		input.value = value;
	};

	BXMainMailFormField.__types['editor'].insert = function(field, text)
	{
		var editor = field.form.editor;

		if (editor.synchro.IsFocusedOnTextarea())
		{
			editor.textareaView.WrapWith('', '', text);
			if (editor.textareaView.element && typeof editor.textareaView.element.selectionStart != 'undefined')
				editor.textareaView.element.selectionStart = editor.textareaView.element.selectionEnd;
		}
		else
		{
			editor.selection.GetRange().deleteContents();
			editor.InsertHtml(text);
		}

		editor.Focus();
	};

	BXMainMailFormField.__types['editor'].setValue = function(field, value, options)
	{
		const filesInfo = options.filesInfo;
		if (Array.isArray(filesInfo))
		{
			const files = new Map(options.filesInfo.map((file) => [file.serverFileId, file.serverPreviewUrl]));
			if (value.length > 0 && files.size > 0)
			{
				for (let [id, previewUrl] of files)
				{
					value = value.replace('bxacid:' + id, previewUrl + '&__bxacid=' + id)
				}
			}
		}

		const editor = field.form.editor;

		if (options && options.signature)
		{
			editor.synchro.Sync();
			var signatureNode = editor.GetIframeDoc().getElementById(field.form.signatureNodeId);
			if (signatureNode)
			{
				var dummyNode = document.createElement('div');
				dummyNode.appendChild(signatureNode.cloneNode(true));

				value += dummyNode.innerHTML;
			}
		}

		if (options && options.quote && !field.quoteNode.__folded)
			value += field.quoteNode.innerHTML;

		editor.SetContent(value, true);

		var regex = /[&?]__bxacid=(n?\d+)/;

		var types = {
			'IMG': 'src',
			'A': 'href'
		};

		for (var name in types)
		{
			var nodeList = editor.GetIframeDoc().getElementsByTagName(name);
			for (var i = 0; i < nodeList.length; i++)
			{
				var matches = nodeList[i].getAttribute(types[name])
					? nodeList[i].getAttribute(types[name]).match(regex)
					: false;
				if (matches)
				{
					nodeList[i].removeAttribute('id');
					nodeList[i].setAttribute(
						types[name],
						nodeList[i].getAttribute(types[name]).replace(regex, '')
					);

					editor.SetBxTag(nodeList[i], {'tag': 'diskfile0', fileId: matches[1]});
				}
			}
		}

		editor.synchro.FullSyncFromIframe();
	};

	BXMainMailFormField.__types['files'].setValue = function(field, value)
	{
		var postForm = field.form.postForm;

		postForm.controllerInit('show');
		for (var uid in postForm.controllers)
		{
			if (!postForm.controllers.hasOwnProperty(uid))
				continue;

			var ctrl = postForm.controllers[uid];

			if (ctrl.storage != 'disk')
				continue;

			if (!ctrl.handler)
				break;

			value = BX.clone(value);

			if (ctrl.values)
			{
				for (var i = 0; i < value.length; i++)
				{
					if (ctrl.values[value[i].id])
						value.splice(i--, 1);
				}
			}

			if (ctrl.handler.removeFiles)
			{
				ctrl.handler.removeFiles(postForm.currentTemplateFiles);
			}

			ctrl.handler.selectFile({}, {}, value);
			postForm.currentTemplateFiles = value.map(item => item.serverFileId);

			break;
		}
	};

	BXMainMailForm.prototype.rebuildSignatureMenu = function(signatures, params)
	{
		if (BX.type.isNotEmptyObject(params)
			&& BX.type.isNotEmptyString(params.signatureSelectTitle)
			&& BX.type.isNotEmptyString(params.signatureConfigureTitle)
			&& BX.type.isNotEmptyString(params.pathToMailSignatures))
		{
			if (!this.signatureSelectButton) {
				this.appendSignatureSelectButton(params.signatureSelectTitle);
			}
			if (this.signatureSelectButton)
			{
				this.initSignatureMenu(params.signatureConfigureTitle, params.pathToMailSignatures);
				this.removeSignaturesFromMenu();
				this.appendSignaturesToMenu(signatures);
			}
		}
	};

	BXMainMailForm.prototype.appendSignatureSelectButton = function(title)
	{
		var id = 'signature-select';
		this.postForm.getToolbar().insertAfter({
			BODY: '<i></i>' + title,
			ID: id,
		});
		this.signatureSelectButton = this.getSelectButton(id);
	};

	BXMainMailForm.prototype.getSelectButton = function(id)
	{
		var items = this.postForm.getToolbar().getItems();
		for (var i in items)
		{
			if (items.hasOwnProperty(i)
				&& items[i].attributes
				&& items[i].attributes.getNamedItem('data-id')
				&& items[i].attributes.getNamedItem('data-id').value === id)
			{
				return items[i];
			}
		}
		return null;
	};

	BXMainMailForm.prototype.initSignatureMenu = function(configureTitle, configurePath)
	{
		if (!this.signatureSelectMenu)
		{
			var form = this;
			this.signatureSelectMenu = new BX.PopupMenuWindow({
				maxWidth: 300,
				maxHeight: 300,
				bindElement: this.signatureSelectButton,
				items: [
					{
						id: this.configureMenuItemId,
						text: configureTitle,
						onclick: function(event, item)
						{
							item.getMenuWindow().close();
							BX.SidePanel.Instance.open(configurePath, {
								cacheable: false,
								events: {
									onCloseComplete: function()
									{
										form.ajaxRefreshSignatures();
									}
								}
							});
						},
					}
				]
			});
			var signatureSelectMenu = this.signatureSelectMenu;
			this.signatureSelectButton.addEventListener("click", function()
			{
				if (signatureSelectMenu.getMenuItems().length > 1) {
					signatureSelectMenu.show();
				} else {
					BX.SidePanel.Instance.open(configurePath, {
						cacheable: false,
						events: {
							onCloseComplete: function()
							{
								form.ajaxRefreshSignatures();
							}
						}
					});
				}
			});
		}
	}

	BXMainMailForm.prototype.ajaxRefreshSignatures = function()
	{
		var form = this;
		BX.ajax.runComponentAction(
			'bitrix:main.mail.form',
			'signatures',
			{ mode: 'class' }
		).then(function(response)
		{
			if (BX.type.isNotEmptyObject(response)
				&& BX.type.isNotEmptyObject(response.data)
				&& response.data.hasOwnProperty('signatures'))
			{
				for (var i in form.fields)
				{
					if (form.fields.hasOwnProperty(i)
						&& BX.type.isNotEmptyObject(form.fields[i])
						&& BX.type.isNotEmptyObject(form.fields[i].params)
						&& form.fields[i].params.hasOwnProperty('allUserSignatures')) {
						var field = form.fields[i];
						field.params.allUserSignatures = response.data.signatures;
						var currentSignatures = form.getSenderSignatures(field);
						form.rebuildSignatureMenu(currentSignatures, field.params);
						break;
					}
				}
			}
		});
	}

	BXMainMailForm.prototype.getSenderSignatures = function(field)
	{
		var currentSender;
		var input = BX(field.fieldId+'_value');
		var currentSignatures = [];
		if (input)
		{
			currentSender = input.value;
		}
		if (currentSender
			&& field.params
			&& BX.type.isArray(field.params.mailboxes)
			&& BX.type.isNotEmptyObject(field.params.allUserSignatures))
		{
			for (var i in field.params.mailboxes)
			{
				if (field.params.mailboxes.hasOwnProperty(i))
				{
					if (field.params.mailboxes[i].formated === currentSender)
					{
						var mailbox = field.params.mailboxes[i];
						var signatures = field.params.allUserSignatures;
						if (BX.type.isArrayFilled(signatures[mailbox.formated]))
						{
							currentSignatures.push.apply(currentSignatures ,signatures[mailbox.formated]);
						}
						if (BX.type.isArrayFilled(signatures[mailbox.email]))
						{
							currentSignatures.push.apply(currentSignatures ,signatures[mailbox.email]);
						}
						if (BX.type.isArrayFilled(signatures['']))
						{
							currentSignatures.push.apply(currentSignatures , signatures['']);
						}
						break;
					}
				}
			}
		}
		return currentSignatures;
	}

	BXMainMailForm.prototype.removeSignaturesFromMenu = function()
	{
		var ids = this.signatureSelectMenu.getMenuItems().map(function(item)
		{
			return item.getId();
		});
		for (var i in ids)
		{
			if (ids.hasOwnProperty(i) && ids[i] !== this.configureMenuItemId)
			{
				this.signatureSelectMenu.removeMenuItem(ids[i]);
			}
		}
	}

	BXMainMailForm.prototype.appendSignaturesToMenu = function(signatures) {
		var items = this.getSignatureSelectItemsMenu(signatures);
		for (var i in items)
		{
			if (items.hasOwnProperty(i))
			{
				this.signatureSelectMenu.addMenuItem(items[i], this.configureMenuItemId);
			}
		}
	}

	BXMainMailForm.prototype.getSignatureSelectItemsMenu = function(signatures)
	{
		var signatureSelectItems = [];

		if (BX.type.isArrayFilled(signatures))
		{

			var form = this;
			for(var i in signatures)
			{
				if (signatures.hasOwnProperty(i)
					&& BX.type.isNotEmptyObject(signatures[i])
					&& BX.type.isNotEmptyString(signatures[i].list)
					&& BX.type.isNotEmptyString(signatures[i].full))
				{
					signatureSelectItems.push({
						id: 'signature-' + i,
						text: signatures[i].list,
						title: signatures[i].list,
						fullSignature: signatures[i].full,
						onclick: function(event, item)
						{
							item.getMenuWindow().close();
							form.insertSignature(item.fullSignature);
						},
					})
				}
			}

			if (signatureSelectItems.length)
			{
				signatureSelectItems.push({
					id: 'after-signatures-delimiter',
					delimiter: true,
				})
			}
		}
		return signatureSelectItems;
	};

	BXMainMailForm.prototype.appendCalendarLinkButton = function(params)
	{
		if (
			BX.type.isNotEmptyObject(params)
			&& BX.type.isBoolean(params.showCalendarSharingButton)
			&& !this.calendarSharingLinkButton
		)
		{
			const id = 'calendar-sharing-link';
			const ownerType = this.options.ownerType ?? null;
			const sharingFeatureLimitEnable = params.sharingFeatureLimitEnable
				|| (params.crmSharingFeatureLimitEnable && ownerType === 'DEAL')
			;
			if (sharingFeatureLimitEnable)
			{
				this.postForm.getToolbar().insertAfter({
					BODY: `<i></i>${BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_SELECT')}`,
					ID: id,
				});
			}
			else
			{
				this.postForm.getToolbar().insertAfter({
					BODY: `<div class="--locked"><i></i>${BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_SELECT')}</div>`,
					ID: id,
				});
			}

			this.calendarSharingLinkButton = this.getSelectButton(id);
			if (sharingFeatureLimitEnable)
			{
				BX.Event.bind(this.calendarSharingLinkButton, 'click', this.insertCalendarSharingLink.bind(this));
				this.calendarSharingLoader = new BX.Loader({
					target: this.calendarSharingLinkButton,
					size: 20,
					mode: 'inline',
					offset: {
						left: '4%',
						top: '-2%',
					},
				});
				this.initPopupOpenCalendar();
			}
			else
			{
				BX.Event.bind(this.calendarSharingLinkButton, 'click', () => {
					this.showCalendarSharingLimit(ownerType);
				});
			}
		}
	};

	BXMainMailForm.prototype.insertCalendarSharingLink = function()
	{
		const ownerId = this.options.ownerId ?? null;
		const ownerType = this.options.ownerType ?? null;

		this.calendarSharingLoader.show();
		BX.ajax.runComponentAction(
			'bitrix:main.mail.form',
			'getCalendarSharingLink',
			{
				mode: 'class',
				data: {
					entityId: ownerId,
					entityType: ownerType,
				},
			},
		).then((response) => {
			if (BX.type.isNotEmptyObject(response)
				&& BX.type.isNotEmptyObject(response.data)
				&& Object.hasOwn(response.data, 'isSharingFeatureEnabled'))
			{
				if (response.data.isSharingFeatureEnabled === true)
				{
					const sharingLink = BX.Text.encode(response.data.sharingUrl);
					const sharingTextNode = this.getCalendarSharingText(sharingLink);
					this.insertCalendarSharingMessage(sharingTextNode);
					const range = this.editor.selection.GetRange();
					range.setStartAfter(sharingTextNode);
					range.setEndAfter(sharingTextNode);
					this.editor.selection.SetSelection(range);
				}
				else
				{
					this.popupOpenCalendar.show();
				}
			}
			this.calendarSharingLoader.hide();
		});
	};

	BXMainMailForm.prototype.showCalendarSharingLimit = function(ownerType)
	{
		if (ownerType === 'DEAL')
		{
			BX.UI.InfoHelper.show('limit_crm_calendar_free_slots');
		}
		else
		{
			BX.Runtime.loadExtension('ui.info-helper')
				.then(({ FeaturePromotersRegistry }) => {
					if (FeaturePromotersRegistry)
					{
						FeaturePromotersRegistry.getPromoter({ featureId: 'calendar_sharing' }).show();
					}
				})
				.catch((error) => {})
			;
		}
	};

	BXMainMailForm.prototype.insertCalendarSharingMessage = function(sharingLinkNode)
	{
		const range = this.editor.selection.GetRange();
		if (this.userSelection === this.editor.GetIframeDoc().body)
		{
			const containerTags = ['DIV', 'HTML', 'BODY'];
			const parentTag = range.endContainer.parentElement.tagName;
			if (containerTags.includes(parentTag))
			{
				this.editor.selection.InsertNode(sharingLinkNode, range);

				return;
			}
			range.endContainer.parentElement.after(sharingLinkNode);

			return;
		}
		const signatureNode = this.editor.GetIframeDoc().getElementById(this.signatureNodeId);

		if (signatureNode)
		{
			signatureNode.before(sharingLinkNode);

			return;
		}
		const quoteNode = this.editor.GetIframeDoc().getElementById(this.quoteNodeId);
		if (quoteNode)
		{
			quoteNode.before(sharingLinkNode);

			return;
		}

		range.setStartAfter(this.editor.GetIframeDoc().body.lastChild);
		range.setEndAfter(this.editor.GetIframeDoc().body.lastChild);
		this.editor.selection.SetSelection(range);
		this.editor.selection.InsertNode(sharingLinkNode, range);
	};

	BXMainMailForm.prototype.getCalendarSharingText = function(sharingLink)
	{
		return BX.Tag.render`
			<span>
				${BX.Loc.getMessage(
					'MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_TEXT_MSGVER_1',
					{
						'[sharing_link]': `<a class="${this.sharingLinkNodeClass}" href="${sharingLink}">`,
						'[/sharing_link]': '</a>',
						'#SHARING_LINK#': sharingLink,
					},
				)}
			</span>
		`;
	};

	BXMainMailForm.prototype.initPopupOpenCalendar = function()
	{
		this.popupOpenCalendar = BX.Main.PopupManager.create(
			{
				id: 'popup-calendar-sharing-link',
				titleBar: BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_POPUP_CALENDAR_TITLE'),
				content: BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_POPUP_CALENDAR_TEXT'),
				width: 400,
				angle: true,
				overlay: true,
				bindElement: this.calendarSharingLinkButton,
				offsetLeft: 40,
				buttons: [
					new BX.UI.CloseButton({
						text: BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_POPUP_CALENDAR_OPEN_BUTTON'),
						color: BX.UI.ButtonColor.PRIMARY,
						events: {
							click: () => {
								BX.SidePanel.Instance.open(
									this.options.userCalendarPath,
								);
								this.popupOpenCalendar.close();
							},
						},
					}),
					new BX.UI.CancelButton({
						events: {
							click: () => {
								this.popupOpenCalendar.close();
							},
						},
					}),
				],
			},
		);
	};

	BXMainMailForm.prototype.needShowCalendarTour = function()
	{
		const hasShowParam = function hasShowCalendarSharingTour(field)
		{
			return BX.type.isNotEmptyObject(field)
				&& Object.hasOwn(field.params, 'showCalendarSharingTour');
		};

		return this.fields.find((element) => hasShowParam(element))?.params?.showCalendarSharingTour ?? false;
	};

	BXMainMailForm.prototype.showCalendarSharingInitialTour = function()
	{
		if (!this.needShowCalendarTour())
		{
			return;
		}

		const tourId = this.options.calendarSharingTourId;
		const ownerType = this.options.ownerType;

		BX.ajax.runComponentAction(
			'bitrix:main.mail.form',
			'getCalendarSharingLink',
			{ mode: 'class' },
		).then((response) => {
			if (BX.type.isNotEmptyObject(response)
				&& BX.type.isNotEmptyObject(response.data)
				&& Object.hasOwn(response.data, 'isSharingFeatureEnabled'))
			{
				let titleText = BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_TOUR_TITLE');
				let tourText = '';
				let helpDeskCode = this.helpDeskCalendarCode;
				if (ownerType === 'DEAL' && response.data.sharingLink !== '')
				{
					titleText = BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_TOUR_DEAL_TITLE');
					tourText = BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_TOUR_DEAL_TEXT');
					helpDeskCode = this.helpDeskCRMCalendarCode;
				}
				else if (response.data.isSharingFeatureEnabled === true)
				{
					tourText = BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_TOUR_SETTING_IS_ACTIVATE_TEXT');
				}
				else
				{
					tourText = BX.Loc.getMessage('MAIN_MAIL_FORM_EDITOR_CALENDAR_SHARING_TOUR_SETTING_IS_DEACTIVATE_TEXT');
				}

				const guide = new BX.UI.Tour.Guide({
					id: tourId,
					autoSave: true,
					simpleMode: true,
					steps: [{
						position: 'top',
						title: titleText,
						text: tourText,
						article: helpDeskCode,
					}],
				});
				const guidePopup = guide.getPopup();
				guidePopup.setWidth(400);
				setTimeout(() => {
					const step = guide.getCurrentStep();
					if (step)
					{
						guide.scrollToTarget(this.calendarSharingLinkButton);
						step.setTarget(this.calendarSharingLinkButton);
						guide.start();
					}
				}, 1500);
			}
		});
	};

	BXMainMailForm.prototype.updateSharingLinkNode = function(text, sharingLink = null) {
		const element = new DOMParser().parseFromString(text, 'text/html');
		const sharingLinkNodes = element.getElementsByClassName(this.sharingLinkNodeClass);
		for (const sharingLinkNode of sharingLinkNodes)
		{
			if (sharingLink)
			{
				sharingLinkNode.innerText = sharingLink;
				sharingLinkNode.href = sharingLink;
			}
			else
			{
				sharingLinkNode.remove();
			}
		}

		return element.documentElement.innerHTML;
	};

	BXMainMailForm.prototype.hideAiImageGeneratorButton = function() {
		if (this.editorInited)
		{
			this.editor.toolbar.HideControl('ai-image-generator');
		}
		else
		{
			BX.addCustomEvent(this, 'MailForm::editor::init', () => {
				this.editor.toolbar.HideControl('ai-image-generator');
			});
		}
	};

	window.BXMainMailForm = BXMainMailForm;

})();
