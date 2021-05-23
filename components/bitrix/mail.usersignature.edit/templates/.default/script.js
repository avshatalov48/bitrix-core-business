;(function ()
{
	BX.namespace('BX.Mail.UserSignature.Edit');

	BX.Mail.UserSignature.Edit = {
		signatureId: null
	};

	BX.Mail.UserSignature.Edit.init = function(params)
	{
		this.signatureId = params.signatureId || null;

		var singleselect = function(input)
		{
			var options = BX.findChildren(input, {tag: 'input', attr: {type: 'radio'}}, true);
			for (var i in options)
			{
				BX.bind(options[i], 'change', function()
				{
					if (this.checked)
					{
						if (this.value == 0)
						{
							var input1 = BX(input.getAttribute('data-checked'));
							if (input1)
							{
								var label0 = BX.findNextSibling(this, {tag: 'label', attr: {'for': this.id}});
								var label1 = BX.findNextSibling(input1, {tag: 'label', attr: {'for': input1.id}});
								if (label0 && label1)
									BX.adjust(label0, {text: label1.innerHTML});
							}
						}
						else
						{
							input.setAttribute('data-checked', this.id);
						}
					}
				});
			}

			BX.bind(input, 'click', function(event)
			{
				event = event || window.event;
				event.skip_singleselect = input;
			});

			BX.bind(document, 'click', function(event)
			{
				event = event || window.event;
				if (event.skip_singleselect !== input)
				{
					if(BX(input.getAttribute('data-checked')))
					{
						BX(input.getAttribute('data-checked')).checked = true;
					}
				}
			});
		};

		var selectInputs = BX.findChildrenByClassName(document, 'mail-set-singleselect', true);
		for (var i in selectInputs)
			singleselect(selectInputs[i]);
	};

	BX.Mail.UserSignature.Edit.save = function(closeAfter)
	{
		closeAfter = closeAfter === true;
		var signatureId = BX('mail-signature-signature-id').value;
		var signature = BXHtmlEditor.Get('signatureeditorid').GetContent();
		var sender = '', list;
		if(BX('sender_bind_checkbox').checked)
		{
			if(BX('mail_user_signature_sender_type_sender').checked)
			{
				list = BX('mail_user_signature_list_sender');
			}
			else
			{
				list = BX('mail_user_signature_list_address');
			}
			var senders = BX.findChildren(list, {tag: 'input', attr: {type: 'radio'}}, true);
			for(var i in senders)
			{
				if(senders.hasOwnProperty(i))
				{
					if(senders[i].checked)
					{
						sender = senders[i].value;
						break;
					}
				}
			}
		}

		if(signatureId > 0)
		{
			BX.ajax.runAction('mail.api.usersignature.update', {
				data: {
					userSignatureId: signatureId,
					fields: {
						signature: signature,
						sender: sender
					}
				}
			}).then(function(response)
			{
				if(closeAfter)
				{
					BX.Mail.UserSignature.Edit.closeSlider(signatureId);
				}
				else
				{
					BX.UI.Notification.Center.notify({
						content: BX.message('MAIL_SIGNATURE_UPDATE_SUCCESS')
					});
				}
			}, function(response)
			{
				BX.Mail.UserSignature.Edit.showError(response.errors.pop().message);
			});
		}
		else
		{
			BX.ajax.runAction('mail.api.usersignature.add', {
				data: {
					fields: {
						signature: signature,
						sender: sender
					}
				}
			}).then(function(response)
			{
				BX.Mail.UserSignature.Edit.closeSlider(response.data.userSignature.id);
			}, function(response)
			{
				BX.Mail.UserSignature.Edit.showError(response.errors.pop().message);
			});
		}
	};

	BX.Mail.UserSignature.Edit.showError = function(text)
	{
		var alert = new BX.UI.Alert({
			color: BX.UI.Alert.Color.DANGER,
			icon: BX.UI.Alert.Icon.DANGER,
			text: text
		});
		BX.adjust(BX('signature-alert-container'), {
			html: ''
		});
		BX.append(alert.getContainer(), BX('signature-alert-container'));
	};

	BX.Mail.UserSignature.Edit.closeSlider = function(signatureId)
	{
		if(BX.SidePanel)
		{
			var slider = BX.SidePanel.Instance.getTopSlider();
			if(slider)
			{
				BX.SidePanel.Instance.postMessage(slider, 'mail-add-signature', {userSignatureId: signatureId});
			}
		}
		BX.fireEvent(BX('ui-button-panel-close'), 'click');
	};

	BX.Mail.UserSignature.Edit.showList = function(list)
	{
		if(list === 'sender')
		{
			BX.show(BX('mail_user_signature_list_sender'), 'inline-block');
			BX.hide(BX('mail_user_signature_list_address'));
		}
		else
		{
			BX.hide(BX('mail_user_signature_list_sender'));
			BX.show(BX('mail_user_signature_list_address'), 'inline-block');
		}
	};

})();