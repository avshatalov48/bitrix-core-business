;(function ()
{
	BX.namespace('BX.Mail.UserSignature.Edit');

	BX.Mail.UserSignature.Edit = {
		signatureId: null,
		parameters: {
			bindingType: 'SENDER',
			bindingNum: 0,
		},
	};


	BX.Mail.UserSignature.Edit.checkBinding = function ()
	{
		BX('sender_bind_checkbox').checked = true;
	}

	BX.Mail.UserSignature.Edit.setParam = function(key, value)
	{
		this.parameters[key] = value;
	}

	BX.Mail.UserSignature.Edit.getParam = function(key)
	{
		return this.parameters[key];
	}

	BX.Mail.UserSignature.Edit.setBindingType = function(type)
	{
		this.setParam('bindingType', type);
	}

	BX.Mail.UserSignature.Edit.setBindingNum = function(num)
	{
		this.setParam('bindingNum', num);
	}

	BX.Mail.UserSignature.Edit.getBindingNum = function()
	{
		return this.getParam('bindingNum');
	}

	BX.Mail.UserSignature.Edit.getBindingType = function()
	{
		return this.getParam('bindingType');
	}

	BX.Mail.UserSignature.Edit.setBinding = function(bindingText)
	{
		this.setParam('bindingText', bindingText);
		this.bindingSignatureList.innerText = bindingText;
		this.bindingSignatureList.title = bindingText;
	}

	BX.Mail.UserSignature.Edit.getBinding = function()
	{
		return this.getParam('bindingText');
	}

	BX.Mail.UserSignature.Edit.changeBindingType = function(type)
	{
		BX.Mail.UserSignature.Edit.setBindingType(type);
		this.bindingTypeField.innerText = BX.message('MAIL_USERSIGNATURE_SENDER_TYPE_' + type);
		this.bindingTypeField.title = BX.message('MAIL_USERSIGNATURE_SENDER_TYPE_' + type);

		if(type === 'SENDER')
		{
			this.setBinding(this.bindingsForPopup['SENDERS'][this.getBindingNum()]['text']);
		}
		else
		{
			this.setBinding(this.bindingsForPopup['ADDRESSES'][this.getBindingNum()]['text']);
		}
	}

	BX.Mail.UserSignature.Edit.init = function(params)
	{
		var type = params['senderType'].toUpperCase();
		var bindingTypeFieldWrapper = BX("binding-type-field-wrapper");
		var selectedSender = params['selectedSender'];
		var selectedAddress = params['selectedAddress'];

		var bindings = {
			'ADDRESSES': params['addresses'],
			'SENDERS' : params['senders'],
		};

		this.bindingsForPopup = {
			'ADDRESSES' : [],
			'SENDERS' : [],
		};

		var bindingTypes = {
			'ADDRESSES' : 'ADDRESSES',
			'SENDERS' : 'SENDERS',
		};

		for (bindingType in bindingTypes)
		{
			var i = 0;
			for (key in bindings[bindingType])
			{
				var item = {
					text: bindings[bindingType][key],
					num: i++,
					onclick: BX.proxy(function(event, item)
					{
						this.checkBinding();
						item.getMenuWindow().close();
						this.setBindingNum(item.num);
						this.setBinding(item.text);
					}, this),
				};

				if((selectedSender === item.text && type === 'SENDER') || (selectedAddress === item.text && type === 'ADDRESS'))
				{
					this.setBindingNum(item.num);
				}

				this.bindingsForPopup[bindingType].push(item);
			}
		}


		this.bindingTypeField = BX.create('a',
			{
				text: BX.message('MAIL_USERSIGNATURE_SENDER_TYPE_SENDER'),
				attrs : {
					title : BX.message('MAIL_USERSIGNATURE_SENDER_TYPE_SENDER')
				},
				props : {
					className : "mail-signature-binding-type-field"
				},
				events:{
					click: BX.proxy(function(){
						this.contextMenuBindingType.show();
					}, this)
				}
			}
		);

		this.contextMenuBindingType = new BX.PopupMenuWindow({
			bindElement: this.bindingTypeField,
			items: [
				{
					text: BX.message('MAIL_USERSIGNATURE_SENDER_TYPE_SENDER'),
					onclick: BX.proxy(function(event, item){
						this.changeBindingType('SENDER');
						item.getMenuWindow().close();
						this.checkBinding();
					}, this),
				},
				{
					text: BX.message('MAIL_USERSIGNATURE_SENDER_TYPE_ADDRESS'),
					onclick: BX.proxy(function(event, item){
						this.changeBindingType('ADDRESS');
						item.getMenuWindow().close();
						this.checkBinding();
					}, this),

				}
			]
		});

		this.bindingSignatureList = BX.create('a',
			{
				text: '',
				attrs : {
					title : ''
				},
				props : {
					className : "mail-signature-binding-type-field"
				},
				events:{
					click: BX.proxy(function(){
						if(this.getBindingType() === 'SENDER')
						{
							this.contextMenuSignatureSenders.show();
						}
						else
						{
							this.contextMenuSignatureAddress.show();
						}
					}, this)
				}
			}
		);

		this.contextMenuSignatureAddress = new BX.PopupMenuWindow({
			maxHeight: 150,
			bindElement: this.bindingSignatureList,
			items: this.bindingsForPopup['ADDRESSES'],
		});

		this.contextMenuSignatureSenders = new BX.PopupMenuWindow({
			maxHeight: 150,
			bindElement: this.bindingSignatureList,
			items: this.bindingsForPopup['SENDERS'],
		});

		this.changeBindingType(type);

		bindingTypeFieldWrapper.appendChild(
			this.bindingTypeField
		);

		bindingTypeFieldWrapper.appendChild(
			this.bindingSignatureList
		);

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
		var sender = '';
		if(BX('sender_bind_checkbox').checked)
		{
			sender = this.getBinding();
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

})();