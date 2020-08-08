
;(function() {

	if (window.BXMainMailConfirm)
		return;

	var options = {};
	var mailboxes = [];

	var listParams = {};

	var BXMainMailConfirm = {
		init: function (params)
		{
			mailboxes = params.mailboxes;
			delete params.mailboxes;

			options = params;
		},
		getMailboxes: function ()
		{
			return mailboxes;
		},
		showList: function (id, bind, params)
		{
			if (!BX.type.isNotEmptyString(params.placeholder))
			{
				params.placeholder = BX.message(params.required ? 'MAIN_MAIL_CONFIRM_MENU_UNKNOWN' : 'MAIN_MAIL_CONFIRM_MENU_PLACEHOLDER');
			}

			if (!BX.type.isFunction(params.callback))
			{
				params.callback = function () {};
			}

			listParams[id] = params;

			var items = [];

			var handler = function(event, item)
			{
				var action = 'apply';

				if (event && event.target)
				{
					var deleteIconClass = 'main-mail-confirm-menu-delete-icon';
					if (BX.hasClass(event.target, deleteIconClass) || BX.findParent(event.target, {class: deleteIconClass}, item.layout.item))
					{
						action = 'delete';
					}
				}

				if ('delete' == action)
				{
					BXMainMailConfirm.deleteSender(
						item.id,
						function ()
						{
							item.menuWindow.removeMenuItem(item.id);
							if (listParams[id].selected == item.title)
							{
								listParams[id].callback('', listParams[id].placeholder);
							}
						}
					);
				}
				else
				{
					listParams[id].callback(item.title, item.text);
					item.menuWindow.close();
				}
			};

			if (!params.required)
			{
				items.push({
					text: BX.util.htmlspecialchars(params.placeholder),
					title: '',
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
					if (mailboxes[i]['can_delete'] && mailboxes[i].id > 0)
					{
						itemText += '<span class="main-mail-confirm-menu-delete-icon popup-window-close-icon popup-window-titlebar-close-icon"\
							title="' + BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_DELETE')) + '"></span>';
						itemClass = 'menu-popup-no-icon menu-popup-right-icon';
					}
					items.push({
						text: itemText,
						title: mailboxes[i].formated,
						onclick: handler,
						className: itemClass,
						id: mailboxes[i].id
					});
				}

				items.push({ delimiter: true });
			}

			items.push({
				text: BX.util.htmlspecialchars(BX.message('MAIN_MAIL_CONFIRM_MENU')),
				onclick: function(event, item)
				{
					item.menuWindow.close();
					BXMainMailConfirm.showForm(function (mailbox, formated)
					{
						mailboxes.push({
							email: mailbox.email,
							name: mailbox.name,
							id: mailbox.id,
							formated: formated
						});

						listParams[id].callback(formated, BX.util.htmlspecialchars(formated));
						BX.PopupMenu.destroy(id + '-menu');
					});
				}
			});

			BX.PopupMenu.show(
				id + '-menu',
				bind,
				items,
				{
					className: 'main-mail-confirm-menu-content',
					offsetLeft: 40,
					angle: true,
					closeByEsc: true
				}
			);
		},
		showForm: function (callback, params)
		{
			var step = 'email';
			var senderId;

			var mode = params && params.mode ? params.mode : 'add';

			var dlg = new BX.PopupWindow('add_from_email', null, {
				titleBar: BX.message('MAIN_MAIL_CONFIRM_TITLE'),
				draggable: true,
				closeIcon: true,
				lightShadow: true,
				contentColor: 'white',
				contentNoPaddings: true,
				content: BX('new_from_email_dialog_content').innerHTML,
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('MAIN_MAIL_CONFIRM_GET_CODE'),
						className: 'popup-window-button-create',
						events: {
							click: function()
							{
								var btn = this;

								if (BX.hasClass(btn.buttonNode, 'popup-window-button-wait'))
									return;

								var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);
								var codeBlock  = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-code-block', true);

								var nameField   = BX.findChild(emailBlock, {attr: {'data-name': 'name'}}, true);
								var emailField  = BX.findChild(emailBlock, {attr: {'data-name': 'email'}}, true);
								var codeField   = BX.findChild(codeBlock, {attr: {'data-name': 'code'}}, true);
								var publicField = BX.findChild(dlg.contentContainer, {attr: {'data-name': 'public'}}, true);

								var smtpServerField = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-server'}}, true);
								var smtpPortField   = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-port'}}, true);
								var smtpSslField    = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-ssl'}}, true);
								var smtpLoginField  = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-login'}}, true);
								var smtpPassField   = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-password'}}, true);

								dlg.formFieldHint(smtpPassField);

								if ('email' == step || 'smtp' == step)
								{
									codeField.value = '';

									var atom = "[=a-z0-9_+~'!$&*^`|#%/?{}-]";
									var pattern = new RegExp('^'+atom+'+(\\.'+atom+'+)*@([a-z0-9-]+\\.)+[a-z0-9-]{2,20}$', 'i');
									if (!emailField.value.match(pattern))
									{
										dlg.showNotify(BX.message(emailField.value.length > 0
											? 'MAIN_MAIL_CONFIRM_INVALID_EMAIL'
											: 'MAIN_MAIL_CONFIRM_EMPTY_EMAIL'
										));
										return;
									}
								}

								if ('smtp' == step)
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

									if (smtpPassField.value.length > 0)
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
									else
									{
										dlg.showNotify(BX.message('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD'));
										return;
									}
								}

								if ('code' == step)
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
									name: nameField.value,
									email: emailField.value,
									smtp: {},
									code: '',
									public: publicField.checked ? publicField.value : ''
								};

								if ('smtp' == step)
								{
									data.smtp = {
										server: smtpServerField.value,
										port: smtpPortField.value,
										ssl: smtpSslField.checked ? smtpSslField.value : '',
										login: smtpLoginField.value,
										password: smtpPassField.value
									};
								}

								if ('code' == step)
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
									'url': '/bitrix/components/bitrix/main.mail.confirm/ajax.php?act=add',
									'method': 'POST',
									'dataType': 'json',
									'data': data,
									onsuccess: function(data)
									{
										BX.removeClass(btn.buttonNode, 'popup-window-button-wait');

										if(data.senderId)
										{
											senderId = data.senderId;
										}

										if (data.result == 'error')
										{
											dlg.showNotify(data.error);
										}
										else if (('email' == step || 'smtp' == step) && !data.confirmed)
										{
											dlg.formFieldHint(smtpPassField);

											dlg.switchBlock('code');
										}
										else
										{
											btn.popupWindow.close();

											if (BX.type.isFunction(callback))
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
													mailboxName.length > 0 ? mailboxName+' <'+emailField.value+'>' : emailField.value
												);
											}
										}
									},
									onfailure: function(data)
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
								if ('code' == step && 'confirm' != mode)
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
			});

			dlg.formFieldHint = function (field, type, text)
			{
				if (!field)
				{
					return;
				}

				var container = BX.findParent(field, {'class': 'new-from-email-dialog-cell'});
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
					BX.adjust(hint, {'html': text});
					BX.show(hint, 'block');
				}
				else
				{
					BX.hide(hint, 'block');
				}
			};

			dlg.hideNotify = function()
			{
				var error = BX.findChild(dlg.contentContainer, {class: 'new-from-email-dialog-error'}, true);
				BX.hide(error, 'block');
			};
			dlg.showNotify = function(text)
			{
				var error = BX.findChild(dlg.contentContainer, {class: 'new-from-email-dialog-error'}, true);

				error.innerHTML = text;
				BX.show(error, 'block');
			};

			dlg.switchBlock = function(block, immediately)
			{
				var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);
				var codeBlock  = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-code-block', true);

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
						hideBlock.style.height = hideBlock.offsetHeight+'px';
						hideBlock.offsetHeight;
						hideBlock.style.height = '0px';

						showBlock.style.position = 'absolute';
						showBlock.style.height = '';
						showBlock.style.display = '';
						var showBlockHeight = showBlock.offsetHeight;
						showBlock.style.height = '0px';
						showBlock.style.position = '';
						showBlock.offsetHeight;
						showBlock.style.height = showBlockHeight+'px';
					}
				}
			};

			var smtpLink = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-link', true);
			var smtpBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-block', true);

			if (smtpLink && smtpBlock)
			{
				BX.bind(
					smtpLink,
					'click',
					function (event)
					{
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

			if ('confirm' == mode)
			{
				dlg.switchBlock('code', true);
				dlg.setOverlay(true);
			}

			dlg.show();

			var emailBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-email-block', true);

			var nameField   = BX.findChild(emailBlock, {attr: {'data-name': 'name'}}, true);
			var emailField  = BX.findChild(emailBlock, {attr: {'data-name': 'email'}}, true);

			if (nameField.value.length > 0)
			{
				emailField.focus();
			}
			else
			{
				nameField.focus();
			}
		},
		deleteSender: function (senderId, callback)
		{
			if(senderId > 0)
			{
				if(confirm(BX.message('MAIN_MAIL_CONFIRM_DELETE_SENDER_CONFIRM')))
				{
					BX.ajax({
						'url': '/bitrix/components/bitrix/main.mail.confirm/ajax.php?act=delete',
						'method': 'POST',
						'dataType': 'json',
						'data': {
							senderId: senderId
						},
						onsuccess: function(data)
						{
							if(data.result == 'error')
							{
								BX.UI.Notification.Center.notify({
									content: BX.message('MAIN_MAIL_DELETE_SENDER_ERROR')
								});
							}
							else
							{
								if (BX.type.isFunction(callback))
								{
									callback();
								}
							}
						},
						onfailure: function(data)
						{
							BX.UI.Notification.Center.notify({
								content: BX.message('MAIN_MAIL_DELETE_SENDER_ERROR')
							});
						}
					});
				}
			}
		}
	};

	window.BXMainMailConfirm = BXMainMailConfirm;

})();
