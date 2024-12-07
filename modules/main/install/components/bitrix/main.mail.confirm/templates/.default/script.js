
;(function()
{

	if (window.BXMainMailConfirm)
		return;

	var options = {};
	var mailboxes = [];
	var listParams = {};
	var action;
	let senderSelector = null;
	let canCheckSmtp = false;
	var BXMainMailConfirm = {
		init: function(params)
		{
			mailboxes = params.mailboxes;
			action = params.action;
			canCheckSmtp = params.canCheckSmtp ?? false;
			senderSelector = new BX.UI.Mail.SenderSelector({
				mailboxes,
				isSenderAvailable: canCheckSmtp ?? false,
			});

			delete params.mailboxes;

			options = params;
		},
		getMailboxes: function()
		{
			return mailboxes;
		},
		showList: function(id, bind, params)
		{
			if (!BX.type.isNotEmptyString(params.placeholder))
			{
				params.placeholder = BX.message(params.required ? 'MAIN_MAIL_CONFIRM_MENU_UNKNOWN' : 'MAIN_MAIL_CONFIRM_MENU_PLACEHOLDER');
			}
			if (!(params.settings && params.settings.length))
			{
				params.settings = [];
			}
			if (!BX.type.isFunction(params.callback))
			{
				params.callback = function() {};
			}
			if((typeof params.popupSettings) != "object")
			{
				params.popupSettings = {};
			}
			params.popupSettings.className  = 'main-mail-confirm-menu-content';
			params.popupSettings.offsetLeft = 40;
			params.popupSettings.angle = true;
			params.popupSettings.closeByEsc = true;
			params.popupSettings.events = {
				onFirstShow: function(data) {
					if (data && data.target && data.target.contentContainer)
					{
						BX.UI.Hint.init(data.target.contentContainer);
					}
				},
			};

			const showNewSelector = !id.includes('crm_mail_template_edit_form');
			if (BX.UI.Mail?.SenderSelector && senderSelector === null && showNewSelector)
			{
				senderSelector = new BX.UI.Mail.SenderSelector({
					mailboxes,
					isSenderAvailable: canCheckSmtp ?? false,
				});
			}

			listParams[id] = params;
			if (senderSelector && showNewSelector)
			{
				senderSelector.selectCallback = listParams[id].callback;
				senderSelector.showDialog(bind);

				return;
			}

			var items = [];

			var handler = function(event, item)
			{
				var action = 'apply';

				if (event && event.target)
				{
					var deleteIconClass = 'main-mail-confirm-menu-delete-icon';
					if (BX.hasClass(event.target, deleteIconClass) || BX.findParent(event.target, { class: deleteIconClass }, item.layout.item))
					{
						action = 'delete';
					}
					if (BX.hasClass(event.target, "sender-hint") || BX.findParent(event.target, { class: "sender-hint" }, item.layout.item))
					{
						action = 'edit';
					}
				}

				if ('delete' == action)
				{
					BXMainMailConfirm.deleteSender(
						item.id, function()
						{
							mailboxes = mailboxes.filter(function(value, index)
							{
								return item.id !== value.id
							});
							item.menuWindow.removeMenuItem(item.id);
							if (listParams[id].selected === item.formated)
							{
								listParams[id].callback('', listParams[id].placeholder);
							}
						}
					);
				}
				else if ('edit' === action)
				{
					BXMainMailConfirm.showEditForm(item.id, function(mailbox, formated)
					{
						var menuItemHtml = BX.util.htmlspecialchars(formated);
						if (item.options && item.options.mailbox)
						{
							item.options.mailbox.name = mailbox.name;
							item.options.mailbox.formated = formated;
							if (item.options.mailbox.can_delete && item.options.mailbox.id > 0)
							{
								menuItemHtml += BXMainMailConfirm.getItemIconsHtml();
							}
						}
						item.text = BX.util.htmlspecialchars(formated);
						item.name = mailbox.name;
						item.formated = formated;
						item.layout.text.innerHTML = menuItemHtml;
						item.options.title = formated;
						listParams[id].callback(formated, BX.util.htmlspecialchars(formated));
					});
				}
				else
				{
					listParams[id].callback(item.formated, item.text);
					item.menuWindow.close();
				}
			};

			if (!params.required)
			{
				items.push({
					text: BX.util.htmlspecialchars(params.placeholder),
					formated: '',
					onclick: handler
				});
				items.push({ delimiter: true });
			}

			if (mailboxes && mailboxes.length > 0)
			{
				var itemText, itemClass;

				for (var i in mailboxes)
				{
					itemClass = 'menu-popup-no-icon';
					itemText = BX.util.htmlspecialchars(mailboxes[i].formated);
					// if (mailboxes[i]['can_delete'] && mailboxes[i].id > 0)
					// {
					// 	itemText += this.getItemIconsHtml();
					// 	itemClass = 'menu-popup-no-icon menu-popup-right-icon';
					// }
					// else if (mailboxes[i].showEditHint)
					// {
					// 	itemText += '<span class="main-mail-confirm-menu-hint-container" data-hint="'
					// 		+ BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_SMTP_SENDER_NO_EDIT_HINT'))
					// 		+ '"></span>';
					// 	itemClass = 'menu-popup-no-icon menu-popup-right-hint-icon';
					// }

					items.push({
						html: itemText,
						mailbox: mailboxes[i],
						formated: mailboxes[i].formated,
						onclick: handler,
						className: itemClass,
						id: 0,
					});
				}

				items.push({ delimiter: true });
			}

			items.push({
				text: BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_MENU')),
				onclick: function(event, item)
				{
					item.menuWindow.close();
					BXMainMailConfirm.showForm(function(mailbox)
					{
						const formated = `${mailbox.name} <${mailbox.email}>`
						mailboxes.push({
							email: mailbox.email,
							name: mailbox.name,
							id: 0,
							formated,
							can_delete: false,
						});

						listParams[id].callback(formated, BX.util.htmlspecialchars(formated));
						BX.PopupMenu.destroy(id + '-menu');
					});
				}
			});
			//additional settings
			if (params.settings.length > 0)
			{
				items = items.concat(params.settings);
			}
			BX.PopupMenu.show(
				id + '-menu',
				bind,
				items,
				params.popupSettings
			);
		},
		showForm: function(callback, params)
		{
			if (senderSelector)
			{
				senderSelector.showProviderShowcase(callback);

				return;
			}

			window.step = 'email';
			var senderId;

			window.mode = params && params.mode ? params.mode : 'add';

			var dlg = new BX.PopupWindow('add_from_email', null, {
				titleBar: BX.message('MAIN_MAIL_CONFIRM_TITLE'),
				draggable: true,
				closeIcon: true,
				lightShadow: true,
				contentColor: 'white',
				contentNoPaddings: true,
				cacheable: false,
				content: BX('new_from_email_dialog_content').innerHTML,
				buttons: this.prepareDialogButtons(null, 'add', params, callback)
			});

			this.prepareDialog(dlg);
		},

		prepareDialog: function(dlg)
		{
			dlg.formFieldHint = function(field, type, text)
			{
				if (!field)
				{
					return;
				}

				var container = BX.findParent(field, { 'class': 'new-from-email-dialog-cell' });
				var hint = BX.findChildByClassName(container, 'new-from-email-dialog-field-hint', true);

				BX.removeClass(container, 'new-from-email-dialog-field-error');
				BX.removeClass(container, 'new-from-email-dialog-field-warning');

				switch (type)
				{
					case 'error':
						BX.addClass(container, 'new-from-email-dialog-field-error');
						break;
					case 'warning':
						BX.addClass(container, 'new-from-email-dialog-field-warning');
						break;
				}

				if (typeof text != 'undefined' && text.length > 0)
				{
					BX.adjust(hint, { 'html': text });
					BX.show(hint, 'block');
				}
				else
				{
					BX.hide(hint, 'block');
				}
			};

			dlg.hideNotify = function()
			{
				var error = BX.findChild(dlg.contentContainer, { class: 'new-from-email-dialog-error' }, true);

				if (error)
				{
					BX.hide(error, 'block');
				}
			};
			dlg.showNotify = function(text)
			{
				var error = BX.findChild(dlg.contentContainer, { class: 'new-from-email-dialog-error' }, true);

				if (error)
				{
					error.innerHTML = text;
					BX.show(error, 'block');
				}
			};

			dlg.switchBlock = function(block, immediately)
			{
				var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);
				var codeBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-code-block', true);

				var hideBlock, showBlock;
				if ('code' != step && 'code' == block)
				{
					hideBlock = emailBlock;
					showBlock = codeBlock;

					dlg.buttons[0].setName(BX.message('MAIN_MAIL_CONFIRM_SAVE'));
					dlg.buttons[1].setName(BX.message('MAIN_MAIL_CONFIRM_BACK'));
				}
				else if ('code' == step && 'code' != block)
				{
					hideBlock = codeBlock;
					showBlock = emailBlock;

					dlg.buttons[0].setName(BX.message(
						'smtp' == block && options.canCheckSmtp
							? 'MAIN_MAIL_CONFIRM_SAVE'
							: 'MAIN_MAIL_CONFIRM_GET_CODE'
					));
					dlg.buttons[1].setName(BX.message('MAIN_MAIL_CONFIRM_CANCEL'));
				}

				step = block;

				if (hideBlock && showBlock)
				{
					if (immediately)
					{
						showBlock.style.position = '';
						showBlock.style.height = '';
						showBlock.style.display = '';

						hideBlock.style.display = 'none';
					}
					else
					{
						hideBlock.style.height = hideBlock.offsetHeight + 'px';
						hideBlock.offsetHeight;
						hideBlock.style.height = '0px';

						showBlock.style.position = 'absolute';
						showBlock.style.height = '';
						showBlock.style.display = '';
						var showBlockHeight = showBlock.offsetHeight;
						showBlock.style.height = '0px';
						showBlock.style.position = '';
						showBlock.offsetHeight;
						showBlock.style.height = showBlockHeight + 'px';
					}
				}
			};

			var smtpLink = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-link', true);
			var smtpBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-block', true);
			var useLimitCheckbox = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-smtp-use-limit', true);

			if (useLimitCheckbox)
			{
				BX.bind(
					useLimitCheckbox,
					'click',
					function()
					{
						var useLimitCheckbox = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-smtp-use-limit', true);
						var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);
						var smtpLimitField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-limit' } }, true);
						smtpLimitField.disabled = !useLimitCheckbox.checked;
					}
				)
			}

			if (smtpLink && smtpBlock)
			{
				BX.bind(
					smtpLink,
					'click',
					(event) => {
						var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);

						emailBlock.style.height = '';

						if ('smtp' == step)
						{
							step = 'email';

							BX.hide(smtpBlock, 'table-row-group');
							dlg.buttons[0].setName(BX.message('MAIN_MAIL_CONFIRM_GET_CODE'));
						}
						else
						{
							step = 'smtp';

							BX.show(smtpBlock, 'table-row-group');
							dlg.buttons[0].setName(BX.message(
								options.canCheckSmtp ? 'MAIN_MAIL_CONFIRM_SAVE' : 'MAIN_MAIL_CONFIRM_GET_CODE'
							));
						}

						event.preventDefault();
					}
				);
			}

			if ('confirm' == window.mode)
			{
				dlg.switchBlock('code', true);
				dlg.setOverlay(true);
			}

			BX.UI.Hint.init(dlg.contentContainer);

			dlg.show();

			var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);

			var nameField = BX.findChild(emailBlock, { attr: { 'data-name': 'name' } }, true);
			var emailField = BX.findChild(emailBlock, { attr: { 'data-name': 'email' } }, true);

			if (nameField.value.length > 0)
			{
				emailField.focus();
			}
			else
			{
				nameField.focus();
			}
		},

		showEditForm: function(senderId, callback)
		{
			window.step = 'email';
			window.mode = 'edit';
			var form = this;
			var dlg = new BX.PopupWindow('edit_from_email', null, {
				titleBar: BX.message('MAIN_MAIL_CONFIRM_EDIT_TITLE'),
				draggable: true,
				closeIcon: true,
				lightShadow: true,
				contentColor: 'white',
				contentNoPaddings: true,
				cacheable: false,
				content: BX('new_from_email_dialog_content').innerHTML,
				events: {
					onPopupShow: function () {
						BX.ajax({
							'url': BX.util.add_url_param(action, {
								'act': 'info',
								senderId: senderId,
							}),
							'method': 'GET',
							'dataType': 'json',
							onsuccess: function (data)
							{
								var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);

								var smtpLink = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-link', true);
								var useLimitCheckbox = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-smtp-use-limit', true);
								var publicField = BX.findChild(dlg.contentContainer, { attr: { 'data-name': 'public' } }, true);

								var nameField = BX.findChild(emailBlock, { attr: { 'data-name': 'name' } }, true);
								var emailField = BX.findChild(emailBlock, { attr: { 'data-name': 'email' } }, true);

								var smtpServerField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-server' } }, true);
								var smtpPortField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-port' } }, true);
								var smtpSslField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-ssl' } }, true);
								var smtpLoginField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-login' } }, true);
								var smtpLimitField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-limit' } }, true);

								nameField.value = data.name || '';
								emailField.value = BX.util.htmlspecialchars(data.email);
								smtpServerField.value = BX.util.htmlspecialchars(data.server || '');
								smtpPortField.value = BX.util.htmlspecialchars(data.port || '');
								smtpLoginField.value = BX.util.htmlspecialchars(data.login || '');

								if (data.isPublic > 0)
								{
									publicField.checked = true;
								}

								var hasNoLimit = typeof data.limit === 'undefined' || data.limit === null;
								smtpLimitField.value = hasNoLimit ? smtpLimitField.value : data.limit;

								if (!hasNoLimit)
								{
									useLimitCheckbox.checked = true;
									smtpLimitField.disabled = false;
								}

								if (data.protocol === 'smtps')
								{
									smtpSslField.checked = true;
								}

								if (data.isOauth) {
									form.disableSmtpFields(dlg.contentContainer);
									dlg.setTitleBar(BX.Loc.getMessage('MAIN_MAIL_CONFIRM_EDIT_TITLE_EMAIL', {
										'#EMAIL#': BX.util.htmlspecialchars(data.email),
									}));
								}

								if (data.server)
								{
									BX.fireEvent(smtpLink, 'click');
								}
							},
							onfailure: function (data)
							{

							},
						})
					}
				},
				buttons: this.prepareDialogButtons(senderId, 'edit', null, callback)
			});

			this.prepareDialog(dlg);

		},

		prepareDialogButtons: function(senderId, act, params, callback)
		{
			return [
				new BX.PopupWindowButton({
					text: BX.message('MAIN_MAIL_CONFIRM_GET_CODE'),
					className: 'popup-window-button-create',
					events: {
						click: function(event, popup)
						{
							var btn = this;
							var dlg = btn.popupWindow;

							if (BX.hasClass(btn.buttonNode, 'popup-window-button-wait'))
								return;

							var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);
							var codeBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-code-block', true);

							var nameField = BX.findChild(emailBlock, { attr: { 'data-name': 'name' } }, true);
							var emailField = BX.findChild(emailBlock, { attr: { 'data-name': 'email' } }, true);
							var codeField = BX.findChild(codeBlock, { attr: { 'data-name': 'code' } }, true);
							var publicField = BX.findChild(dlg.contentContainer, { attr: { 'data-name': 'public' } }, true);

							var smtpServerField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-server' } }, true);
							var smtpPortField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-port' } }, true);
							var smtpSslField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-ssl' } }, true);
							var smtpLoginField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-login' } }, true);
							var smtpPassField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-password' } }, true);
							var smtpLimit = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-limit' } }, true);

							dlg.formFieldHint(smtpPassField);

							if ('email' == window.step || 'smtp' == window.step)
							{
								codeField.value = '';

								var atom = "[=a-z0-9_+~'!$&*^`|#%/?{}-]";
								var pattern = new RegExp('^' + atom + '+(\\.' + atom + '+)*@([a-z0-9-]+\\.)+[a-z0-9-]{2,20}$', 'i');
								if (!emailField.value.match(pattern))
								{
									dlg.showNotify(BX.message(emailField.value.length > 0
										? 'MAIN_MAIL_CONFIRM_INVALID_EMAIL'
										: 'MAIN_MAIL_CONFIRM_EMPTY_EMAIL'
									));
									return;
								}
							}

							if ('smtp' == window.step)
							{
								if (!smtpServerField.value.match(/^([a-z0-9-]+\.)+[a-z0-9-]{2,20}$/))
								{
									dlg.showNotify(BX.message(smtpServerField.value.length > 0
										? 'MAIN_MAIL_CONFIRM_INVALID_SMTP_SERVER'
										: 'MAIN_MAIL_CONFIRM_EMPTY_SMTP_SERVER'
									));
									return;
								}

								if (!smtpPortField.value.match(/^[0-9]+$/) || smtpPortField.value < 1 || smtpPortField.value > 65535)
								{
									dlg.showNotify(BX.message(smtpPortField.value.length > 0
										? 'MAIN_MAIL_CONFIRM_INVALID_SMTP_PORT'
										: 'MAIN_MAIL_CONFIRM_EMPTY_SMTP_PORT'
									));
									return;
								}

								if (!(smtpLoginField.value.length > 0))
								{
									dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_EMPTY_SMTP_LOGIN'));
									return;
								}

								if (!senderId && smtpPassField.value.length > 0)
								{
									if (smtpPassField.value.match(/^\^/))
									{
										dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_INVALID_SMTP_PASSWORD_CARET'));
										return;
									}
									else if (smtpPassField.value.match(/\x00/))
									{
										dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_INVALID_SMTP_PASSWORD_NULL'));
										return;
									}
									else if (smtpPassField.value.match(/^\s|\s$/))
									{
										dlg.formFieldHint(smtpPassField, 'warning', BX.message('MAIN_MAIL_CONFIRM_SPACE_SMTP_PASSWORD'));
									}
								}
								else if (!senderId)
								{
									dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD'));
									return;
								}
							}

							if ('code' == window.step)
							{
								if (codeField.value.length == 0)
								{
									dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_EMPTY_CODE'));
									return;
								}
							}

							dlg.hideNotify();
							BX.addClass(btn.buttonNode, 'popup-window-button-wait');

							var data = {
								id: senderId,
								name: nameField.value,
								email: emailField.value,
								smtp: {},
								code: '',
								public: publicField.checked ? publicField.value : ''
							};

							if ('smtp' == window.step)
							{
								data.smtp = {
									server: smtpServerField.value,
									port: smtpPortField.value,
									ssl: smtpSslField.checked ? smtpSslField.value : '',
									login: smtpLoginField.value,
									password: smtpPassField.value,
									limit: smtpLimit.disabled ? null : smtpLimit.value
								};
							}

							if ('code' == window.step)
							{
								data.code = codeField.value;
							}

							if (params && params.data)
							{
								for (var i in params.data)
								{
									if (params.data.hasOwnProperty(i))
									{
										data[i] = params.data[i];
									}
								}
							}

							BX.ajax({
								'url': BX.util.add_url_param(action, {
									'act': act
								}),
								'method': 'POST',
								'dataType': 'json',
								'data': data,
								onsuccess: function(data)
								{
									BX.removeClass(btn.buttonNode, 'popup-window-button-wait');

									if (data.senderId)
									{
										senderId = data.senderId;
									}

									if (data.result == 'error')
									{
										dlg.showNotify(data.error);
									}
									else if (('email' == window.step || 'smtp' == window.step) && !data.confirmed)
									{
										dlg.formFieldHint(smtpPassField);

										dlg.switchBlock('code');
									}
									else
									{
										btn.popupWindow.close();

										if (callback && BX.type.isFunction(callback))
										{
											var mailboxName = nameField.value.length > 0
												? nameField.value
												: BX.message('MAIN_MAIL_CONFIRM_USER_FULL_NAME');
											callback(
												{
													name: mailboxName,
													email: emailField.value,
													id: senderId
												},
												mailboxName.length > 0 ? mailboxName + ' <' + emailField.value + '>' : emailField.value
											);
										}
									}
								},
								onfailure: function()
								{
									BX.removeClass(btn.buttonNode, 'popup-window-button-wait');
									dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_AJAX_ERROR'));
								}
							});
						}
					}
				}),
				new BX.PopupWindowButton({
					text: BX.message('MAIN_MAIL_CONFIRM_CANCEL'),
					className: 'popup-window-button-link',
					events: {
						click: function()
						{
							var dlg = this.popupWindow;

							if ('code' == window.step && 'confirm' != window.mode)
							{
								var smtpBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-block', true);

								dlg.switchBlock(smtpBlock && smtpBlock.offsetHeight > 0 ? 'smtp' : 'email');
							}
							else
							{
								this.popupWindow.close();
							}
						}
					}
				})
			]
		},
		updateListCanDel: function(id)
		{
			BX.ajax({
				'url':BX.util.add_url_param(action, {
					'act': 'sendersListCanDel',
				}),
				'method': 'POST',
				'dataType': 'json',
				'data': {},
				onsuccess: function(data)
				{
					if (data.result == 'error')
					{
						BX.UI.Notification.Center.notify({
							content: BX.message('MAIN_MAIL_DELETE_SENDER_ERROR')
						});
					}
					else
					{
						mailboxes = mailboxes.filter(function(value, index)
						{
							if (!value.can_delete)
							{
								return true;
							}
							for (var i in data.mailboxes)
							{
								if (data.mailboxes[i].id == value.id)
								{
									return true;
								}
							}
							return false;
						});
						BX.PopupMenu.destroy(id + '-menu');
					}
				},
				onfailure: function(data)
				{
					BX.UI.Notification.Center.notify({
						content: BX.message('MAIN_MAIL_DELETE_SENDER_ERROR')
					});
				}
			});
		},
		deleteSender: function(senderId, callback)
		{
			BX.UI.Dialogs.MessageBox.show({
				message: BX.message('MAIN_MAIL_CONFIRM_DELETE_SENDER_CONFIRM'),
				modal: true,
				buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
				onOk: function(messageBox)
				{
					return new Promise(
						function(resolve,reject)
						{
							BX.ajax({
								'url': BX.util.add_url_param(action, {
									'act': 'delete',
								}),
								'method': 'POST',
								'dataType': 'json',
								'data': {
									senderId: senderId
								},
								onsuccess: function(data)
								{
									if (data.result == 'error')
									{
										BX.UI.Notification.Center.notify({
											content: BX.message('MAIN_MAIL_DELETE_SENDER_ERROR')
										});
										reject(data);
									}
									else
									{
										if (BX.type.isFunction(callback))
										{
											callback();
										}
										resolve(data);
									}
								},
								onfailure: function(data)
								{
									BX.UI.Notification.Center.notify({
										content: BX.message('MAIN_MAIL_DELETE_SENDER_ERROR')
									});
									reject(data);
								}
							});
						}
					);
				},
				onCancel: function(messageBox)
				{
					messageBox.close();
				}
			});
		},

		disableSmtpFields: function(el)
		{
			var emailBlock = BX.findChildByClassName(el, 'new-from-email-dialog-email-block', true);

			var emailField = BX.findChild(emailBlock, { attr: { 'data-name': 'email' } }, true);
			this.disableAndHide(emailField);

			var smtpServerField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-server' } }, true);
			this.disableAndHide(smtpServerField);

			var smtpPortField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-port' } }, true);
			this.disableAndHide(smtpPortField);
			var smtpSslField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-ssl' } }, true);
			this.disableAndHide(smtpSslField);
			var smtpLoginField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-login' } }, true);
			this.disableAndHide(smtpLoginField);

			var smtpPasswordField = BX.findChild(emailBlock, { attr: { 'data-name': 'smtp-password' } }, true);
			this.disableAndHide(smtpPasswordField);

			var smtpWarning = BX.findChild(emailBlock, { class: 'new-from-email-dialog-smtp-warning' }, true);
			this.hideParentDialogRow(smtpWarning);

			var message = BX.findChild(emailBlock, { class: 'new-from-email-dialog-block-content-message'}, true);
			if (message) {
				BX.hide(message);
			}
		},

		hideParentDialogRow: function(el)
		{
			if (el)
			{
				var parent = el.closest('.new-from-email-dialog-row');
				if (parent)
				{
					BX.hide(parent);
				}
			}
		},

		disableAndHide: function(el)
		{
			this.hideParentDialogRow(el);
			this.safeDisable(el);
		},

		safeDisable: function(el)
		{
			if (el)
			{
				el.setAttribute('disabled', 'disabled');
			}
		},

		getItemIconsHtml: function()
		{
			return '<span class="main-mail-confirm-menu-delete-icon popup-window-close-icon popup-window-titlebar-close-icon"\
								title="' + BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_DELETE')) + '"></span>\
			';
		},

	};
	window.BXMainMailConfirm = BXMainMailConfirm;

})();
