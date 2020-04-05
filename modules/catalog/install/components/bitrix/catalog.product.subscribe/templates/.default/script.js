(function (window) {

	if (!!window.JCCatalogProductSubscribe)
	{
		return;
	}

	var subscribeButton = function(params)
	{
		subscribeButton.superclass.constructor.apply(this, arguments);
		this.nameNode = BX.create('span', {
			props : { id : this.id },
			style: typeof(params.style) === 'object' ? params.style : {},
			text: params.text
		});
		this.buttonNode = BX.create('span', {
			attrs: { className: params.className },
			style: { marginBottom: '0', borderBottom: '0 none transparent' },
			children: [this.nameNode],
			events : this.contextEvents
		});
		if (BX.browser.IsIE())
		{
			this.buttonNode.setAttribute("hideFocus", "hidefocus");
		}
	};
	BX.extend(subscribeButton, BX.PopupWindowButton);

	window.JCCatalogProductSubscribe = function(params)
	{
		this.buttonId = params.buttonId;
		this.buttonClass = params.buttonClass;
		this.jsObject = params.jsObject;
		this.ajaxUrl = '/bitrix/components/bitrix/catalog.product.subscribe/ajax.php';
		this.alreadySubscribed = params.alreadySubscribed;
		this.urlListSubscriptions = params.urlListSubscriptions;
		this.listOldItemId = {};

		this.elemButtonSubscribe = null;
		this.elemPopupWin = null;
		this.defaultButtonClass = 'bx-catalog-subscribe-button';

		this._elemButtonSubscribeClickHandler = BX.delegate(this.subscribe, this);
		this._elemHiddenClickHandler = BX.delegate(this.checkSubscribe, this);

		BX.ready(BX.delegate(this.init,this));
	};

	window.JCCatalogProductSubscribe.prototype.init = function()
	{
		if (!!this.buttonId)
		{
			this.elemButtonSubscribe = BX(this.buttonId);
			this.elemHiddenSubscribe = BX(this.buttonId+'_hidden');
		}

		if (!!this.elemButtonSubscribe)
		{
			BX.bind(this.elemButtonSubscribe, 'click', this._elemButtonSubscribeClickHandler);
		}

		if (!!this.elemHiddenSubscribe)
		{
			BX.bind(this.elemHiddenSubscribe, 'click', this._elemHiddenClickHandler);
		}

		this.setButton(this.alreadySubscribed);
	};

	window.JCCatalogProductSubscribe.prototype.checkSubscribe = function()
	{
		if(!this.elemHiddenSubscribe || !this.elemButtonSubscribe) return;

		if(this.listOldItemId.hasOwnProperty(this.elemButtonSubscribe.dataset.item))
		{
			this.setButton(true);
		}
		else
		{
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: {
					sessid: BX.bitrix_sessid(),
					checkSubscribe: 'Y',
					itemId: this.elemButtonSubscribe.dataset.item
				},
				onsuccess: BX.delegate(function (result) {
					if(result.subscribe)
					{
						this.setButton(true);
						this.listOldItemId[this.elemButtonSubscribe.dataset.item] = true;
					}
					else
					{
						this.setButton(false);
					}
				}, this)
			});
		}
	};

	window.JCCatalogProductSubscribe.prototype.subscribe = function()
	{
		this.elemButtonSubscribe = BX.proxy_context;
		if(!this.elemButtonSubscribe) return false;

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: this.ajaxUrl,
			data: {
				sessid: BX.bitrix_sessid(),
				subscribe: 'Y',
				itemId: this.elemButtonSubscribe.dataset.item,
				siteId: BX.message('SITE_ID')
			},
			onsuccess: BX.delegate(function (result) {
				if(result.success)
				{
					this.createSuccessPopup(result);
					this.setButton(true);
					this.listOldItemId[this.elemButtonSubscribe.dataset.item] = true;
				}
				else if(result.contactFormSubmit)
				{
					this.initPopupWindow();
					this.elemPopupWin.setTitleBar(BX.message('CPST_SUBSCRIBE_POPUP_TITLE'));
					var form = this.createContentForPopup(result);
					this.elemPopupWin.setContent(form);
					this.elemPopupWin.setButtons([
						new subscribeButton({
							text: BX.message('CPST_SUBSCRIBE_BUTTON_NAME'),
							className : 'btn btn-primary',
							events: {
								click : BX.delegate(function() {
									if(!this.validateContactField(result.contactTypeData))
									{
										return false;
									}
									BX.ajax.submitAjax(form, {
										method : 'POST',
										url: this.ajaxUrl,
										processData : true,
										onsuccess: BX.delegate(function (resultForm) {
											resultForm = BX.parseJSON(resultForm, {});
											if(resultForm.success)
											{
												this.createSuccessPopup(resultForm);
												this.setButton(true);
												this.listOldItemId[this.elemButtonSubscribe.dataset.item] = true;
											}
											else if(resultForm.error)
											{
												if(resultForm.hasOwnProperty('setButton'))
												{
													this.listOldItemId[this.elemButtonSubscribe.dataset.item] = true;
													this.setButton(true);
												}
												var errorMessage = resultForm.message;
												if(resultForm.hasOwnProperty('typeName'))
												{
													errorMessage = resultForm.message.replace('USER_CONTACT',
														resultForm.typeName);
												}
												BX('bx-catalog-subscribe-form-notify').style.color = 'red';
												BX('bx-catalog-subscribe-form-notify').innerHTML = errorMessage;
											}
										}, this)
									});
								}, this)
							}
						}),
						new subscribeButton({
							text : BX.message('CPST_SUBSCRIBE_BUTTON_CLOSE'),
							className : 'btn',
							events : {
								click : BX.delegate(function() {
									this.elemPopupWin.destroy();
								}, this)
							}
						})
					]);
					this.elemPopupWin.show();
				}
				else if(result.error)
				{
					if(result.hasOwnProperty('setButton'))
					{
						this.listOldItemId[this.elemButtonSubscribe.dataset.item] = true;
						this.setButton(true);
					}
					this.showWindowWithAnswer({status: 'error', message: result.message});
				}
			}, this)
		});
	};

	window.JCCatalogProductSubscribe.prototype.validateContactField = function(contactTypeData)
	{
		var inputFields = BX.findChildren(BX('bx-catalog-subscribe-form'),
			{'tag': 'input', 'attribute': {id: 'userContact'}}, true);
		if(!inputFields.length || typeof contactTypeData !== 'object')
		{
			BX('bx-catalog-subscribe-form-notify').style.color = 'red';
			BX('bx-catalog-subscribe-form-notify').innerHTML = BX.message('CPST_SUBSCRIBE_VALIDATE_UNKNOW_ERROR');
			return false;
		}

		var contactTypeId, contactValue, useContact, errors = [], useContactErrors = [];
		for(var k = 0; k < inputFields.length; k++)
		{
			contactTypeId = inputFields[k].getAttribute('data-id');
			contactValue = inputFields[k].value;
			useContact = BX('bx-contact-use-'+contactTypeId);
			if(useContact && useContact.value == 'N')
			{
				useContactErrors.push(true);
				continue;
			}
			if(!contactValue.length)
			{
				errors.push(BX.message('CPST_SUBSCRIBE_VALIDATE_ERROR_EMPTY_FIELD').replace(
					'#FIELD#', contactTypeData[contactTypeId].contactLable));
			}
		}

		if(inputFields.length == useContactErrors.length)
		{
			BX('bx-catalog-subscribe-form-notify').style.color = 'red';
			BX('bx-catalog-subscribe-form-notify').innerHTML = BX.message('CPST_SUBSCRIBE_VALIDATE_ERROR');
			return false;
		}

		if(errors.length)
		{
			BX('bx-catalog-subscribe-form-notify').style.color = 'red';
			for(var i = 0; i < errors.length; i++)
			{
				BX('bx-catalog-subscribe-form-notify').innerHTML = errors[i];
			}
			return false;
		}

		return true;
	};

	window.JCCatalogProductSubscribe.prototype.reloadCaptcha = function()
	{
		BX.ajax.get(this.ajaxUrl+'?reloadCaptcha=Y', '', function(captchaCode) {
			BX('captcha_sid').value = captchaCode;
			BX('captcha_img').src = '/bitrix/tools/captcha.php?captcha_sid='+captchaCode+'';
		});
	};

	window.JCCatalogProductSubscribe.prototype.createContentForPopup = function(responseData)
	{
		if(!responseData.hasOwnProperty('contactTypeData'))
		{
			return null;
		}

		var contactTypeData = responseData.contactTypeData, contactCount = Object.keys(contactTypeData).length,
			styleInputForm = '', manyContact = 'N', content = document.createDocumentFragment();

		if(contactCount > 1)
		{
			manyContact = 'Y';
			styleInputForm = 'display:none;';
			content.appendChild(BX.create('p', {
				text: BX.message('CPST_SUBSCRIBE_MANY_CONTACT_NOTIFY')
			}));
		}

		content.appendChild(BX.create('p', {
			props: {id: 'bx-catalog-subscribe-form-notify'}
		}));

		for(var k in contactTypeData)
		{
			if(contactCount > 1)
			{
				content.appendChild(BX.create('div', {
					props: {
						className: 'bx-catalog-subscribe-form-container'
					},
					children: [
						BX.create('div', {
							props: {
								className: 'checkbox'
							},
							children: [
								BX.create('lable', {
									props: {
										className: 'bx-filter-param-label'
									},
									attrs: {
										onclick: this.jsObject+'.selectContactType('+k+', event);'
									},
									children: [
										BX.create('input', {
											props: {
												type: 'hidden',
												id: 'bx-contact-use-'+k,
												name: 'contact['+k+'][use]',
												value: 'N'
											}
										}),
										BX.create('input', {
											props: {
												id: 'bx-contact-checkbox-'+k,
												type: 'checkbox'
											}
										}),
										BX.create('span', {
											props: {
												className: 'bx-filter-param-text'
											},
											text: contactTypeData[k].contactLable
										})
									]
								})
							]
						})
					]
				}));
			}
			content.appendChild(BX.create('div', {
				props: {
					id: 'bx-catalog-subscribe-form-container-'+k,
					className: 'bx-catalog-subscribe-form-container',
					style: styleInputForm
				},
				children: [
					BX.create('div', {
						props: {
							className: 'bx-catalog-subscribe-form-container-label'
						},
						text: BX.message('CPST_SUBSCRIBE_LABLE_CONTACT_INPUT').replace(
							'#CONTACT#', contactTypeData[k].contactLable)
					}),
					BX.create('div', {
						props: {
							className: 'bx-catalog-subscribe-form-container-input'
						},
						children: [
							BX.create('input', {
								props: {
									id: 'userContact',
									className: '',
									type: 'text',
									name: 'contact['+k+'][user]'
								},
								attrs: {'data-id': k}
							})
						]
					})
				]
			}));
		}
		if(responseData.hasOwnProperty('captchaCode'))
		{
			content.appendChild(BX.create('div', {
				props: {
					className: 'bx-catalog-subscribe-form-container'
				},
				children: [
					BX.create('span', {props: {className: 'bx-catalog-subscribe-form-star-required'}, text: '*'}),
					BX.message('CPST_ENTER_WORD_PICTURE'),
					BX.create('div', {
						props: {className: 'bx-captcha'},
						children: [
							BX.create('input', {
								props: {
									type: 'hidden',
									id: 'captcha_sid',
									name: 'captcha_sid',
									value: responseData.captchaCode
								}
							}),
							BX.create('img', {
								props: {
									id: 'captcha_img',
									src: '/bitrix/tools/captcha.php?captcha_sid='+responseData.captchaCode+''
								},
								attrs: {
									width: '180',
									height: '40',
									alt: 'captcha',
									onclick: this.jsObject+'.reloadCaptcha();'
								}
							})
						]
					}),
					BX.create('div', {
						props: {className: 'bx-catalog-subscribe-form-container-input'},
						children: [
							BX.create('input', {
								props: {
									id: 'captcha_word',
									className: '',
									type: 'text',
									name: 'captcha_word'
								},
								attrs: {maxlength: '50'}
							})
						]
					})
				]
			}));
		}
		var form = BX.create('form', {
			props: {
				id: 'bx-catalog-subscribe-form'
			},
			children: [
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'manyContact',
						value: manyContact
					}
				}),
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'sessid',
						value: BX.bitrix_sessid()
					}
				}),
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'itemId',
						value: this.elemButtonSubscribe.dataset.item
					}
				}),
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'siteId',
						value: BX.message('SITE_ID')
					}
				}),
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'contactFormSubmit',
						value: 'Y'
					}
				})
			]
		});

		form.appendChild(content);

		return form;
	};

	window.JCCatalogProductSubscribe.prototype.selectContactType = function(contactTypeId, event)
	{
		var contactInput = BX('bx-catalog-subscribe-form-container-'+contactTypeId), visibility = '',
			checkboxInput = BX('bx-contact-checkbox-'+contactTypeId);
		if(!contactInput)
		{
			return false;
		}

		if(checkboxInput != event.target)
		{
			if(checkboxInput.checked)
			{
				checkboxInput.checked = false;
			}
			else
			{
				checkboxInput.checked = true;
			}
		}

		if (contactInput.currentStyle)
		{
			visibility = contactInput.currentStyle.display;
		}
		else if (window.getComputedStyle)
		{
			var computedStyle = window.getComputedStyle(contactInput, null);
			visibility = computedStyle.getPropertyValue('display');
		}

		if(visibility === 'none')
		{
			BX('bx-contact-use-'+contactTypeId).value = 'Y';
			BX.style(contactInput, 'display', '');
		}
		else
		{
			BX('bx-contact-use-'+contactTypeId).value = 'N';
			BX.style(contactInput, 'display', 'none');
		}
	};

	window.JCCatalogProductSubscribe.prototype.createSuccessPopup = function(result)
	{
		this.initPopupWindow();
		this.elemPopupWin.setTitleBar(BX.message('CPST_SUBSCRIBE_POPUP_TITLE'));
		var content = BX.create('div', {
			props:{
				className: 'bx-catalog-popup-content'
			},
			children: [
				BX.create('p', {
					props: {
						className: 'bx-catalog-popup-message'
					},
					text: result.message
				})
			]
		});
		this.elemPopupWin.setContent(content);
		this.elemPopupWin.setButtons([
			new subscribeButton({
				text : BX.message('CPST_SUBSCRIBE_BUTTON_CLOSE'),
				className : 'btn btn-primary',
				events : {
					click : BX.delegate(function() {
						this.elemPopupWin.destroy();
					}, this)
				}
			})
		]);
		this.elemPopupWin.show();
	};

	window.JCCatalogProductSubscribe.prototype.initPopupWindow = function()
	{
		this.elemPopupWin = BX.PopupWindowManager.create('CatalogSubscribe_'+this.buttonId, null, {
			autoHide: false,
			offsetLeft: 0,
			offsetTop: 0,
			overlay : true,
			closeByEsc: true,
			titleBar: true,
			closeIcon: true,
			contentColor: 'white'
		});
	};

	window.JCCatalogProductSubscribe.prototype.setButton = function(statusSubscription)
	{
		this.alreadySubscribed = Boolean(statusSubscription);
		if(this.alreadySubscribed)
		{
			this.elemButtonSubscribe.className = this.buttonClass + ' ' + this.defaultButtonClass + ' disabled';
			this.elemButtonSubscribe.innerHTML = '<span>' + BX.message('CPST_TITLE_ALREADY_SUBSCRIBED') + '</span>';
			BX.unbind(this.elemButtonSubscribe, 'click', this._elemButtonSubscribeClickHandler);
		}
		else
		{
			this.elemButtonSubscribe.className = this.buttonClass + ' ' + this.defaultButtonClass;
			this.elemButtonSubscribe.innerHTML = '<span>' + BX.message('CPST_SUBSCRIBE_BUTTON_NAME') + '</span>';
			BX.bind(this.elemButtonSubscribe, 'click', this._elemButtonSubscribeClickHandler);
		}
	};

	window.JCCatalogProductSubscribe.prototype.showWindowWithAnswer = function(answer)
	{
		answer = answer || {};
		if (!answer.message) {
			if (answer.status == 'success') {
				answer.message = BX.message('CPST_STATUS_SUCCESS');
			} else {
				answer.message = BX.message('CPST_STATUS_ERROR');
			}
		}
		var messageBox = BX.create('div', {
			props: {
				className: 'bx-catalog-subscribe-alert'
			},
			children: [
				BX.create('span', {
					props: {
						className: 'bx-catalog-subscribe-aligner'
					}
				}),
				BX.create('span', {
					props: {
						className: 'bx-catalog-subscribe-alert-text'
					},
					text: answer.message
				}),
				BX.create('div', {
					props: {
						className: 'bx-catalog-subscribe-alert-footer'
					}
				})
			]
		});
		var currentPopup = BX.PopupWindowManager.getCurrentPopup();
		if(currentPopup) {
			currentPopup.destroy();
		}
		var idTimeout = setTimeout(function () {
			var w = BX.PopupWindowManager.getCurrentPopup();
			if (!w || w.uniquePopupId != 'bx-catalog-subscribe-status-action') {
				return;
			}
			w.close();
			w.destroy();
		}, 3500);
		var popupConfirm = BX.PopupWindowManager.create('bx-catalog-subscribe-status-action', null, {
			content: messageBox,
			onPopupClose: function () {
				this.destroy();
				clearTimeout(idTimeout);
			},
			autoHide: true,
			zIndex: 2000,
			className: 'bx-catalog-subscribe-alert-popup'
		});
		popupConfirm.show();
		BX('bx-catalog-subscribe-status-action').onmouseover = function (e) {
			clearTimeout(idTimeout);
		};
		BX('bx-catalog-subscribe-status-action').onmouseout = function (e) {
			idTimeout = setTimeout(function () {
				var w = BX.PopupWindowManager.getCurrentPopup();
				if (!w || w.uniquePopupId != 'bx-catalog-subscribe-status-action') {
					return;
				}
				w.close();
				w.destroy();
			}, 3500);
		};
	};

})(window);
