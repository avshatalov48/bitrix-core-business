
;(function() {

	if (window.BXMainMailConfirm)
		return;

	var BXMainMailConfirm = {
		showForm: function(callback)
		{
			var step = 'email';
			var dlg = new BX.PopupWindow('add_from_email', null, {
				width: 480,
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
								var smtpLoginField  = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-login'}}, true);
								var smtpPassField   = BX.findChild(emailBlock, {attr: {'data-name': 'smtp-password'}}, true);

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

									if (!(smtpPassField.value.length > 0))
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
										login: smtpLoginField.value,
										password: smtpPassField.value
									};
								}

								if ('code' == step)
								{
									data.code = codeField.value;
								}

								BX.ajax({
									'url': '/bitrix/components/bitrix/main.mail.confirm/ajax.php?act=add',
									'method': 'POST',
									'dataType': 'json',
									'data': data,
									onsuccess: function(data)
									{
										BX.removeClass(btn.buttonNode, 'popup-window-button-wait');

										if (data.result == 'error')
										{
											dlg.showNotify(data.error);
										}
										else if (step == 'email')
										{
											step = 'code';

											emailBlock.style.height = emailBlock.offsetHeight+'px';
											emailBlock.offsetHeight;
											emailBlock.style.height = '0px';

											codeBlock.style.position = 'absolute';
											codeBlock.style.display = '';
											var codeBlockHeight = codeBlock.offsetHeight;
											codeBlock.style.height = '0px';
											codeBlock.style.position = '';
											codeBlock.offsetHeight;
											codeBlock.style.height = codeBlockHeight+'px';

											btn.setName(BX.message('MAIN_MAIL_CONFIRM_SAVE'));
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
														email: emailField.value
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
								this.popupWindow.close();
							}
						}
					})
				]
			});

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

			var smtpLink = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-link', true);
			var smtpBlock = BX.findChildByClassName(dlg.contentContainer, 'new-from-email-dialog-smtp-block', true);

			if (smtpLink && smtpBlock)
			{
				BX.bind(
					smtpLink,
					'click',
					function (event)
					{
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

							dlg.buttons[0].setName(BX.message('MAIN_MAIL_CONFIRM_SAVE'));
						}

						event.preventDefault();
					}
				);
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
		}
	};

	window.BXMainMailConfirm = BXMainMailConfirm;

})();
