(function() {

	'use strict';

	BX.namespace('BX.Landing');

	/**
	 * For change domain name.
	 */
	BX.Landing.EditDomainForm = function (node, params)
	{
		this.domain = node.querySelector('.ui-domain-input-btn-js');
		this.postfix = node.querySelectorAll('.ui-postfix');
		this.domains = node.querySelectorAll('.ui-domainname');
		this.content = params.content || '';
		this.messages = params.messages || {};
		this.fieldId = params.fieldId || 'domain_id';
		this.popup = new BX.PopupWindow('landing-domain-popup', null, {
			titleBar: this.messages.title,
			content : this.content,
			contentBackground: '#eef2f4',
			overlay: true,
			buttons: [
				new BX.PopupWindowButton({
					id: 'landing-popup-window-button-accept',
					text : BX.message('BLOCK_CONTINUE'),
					className: 'popup-window-button-accept'
				}),
				new BX.PopupWindowButton({
					text : BX.message('BLOCK_CANCEL'),
					className: 'popup-window-button-link',
					events: {
						click : function(){
							this.popupWindow.close();
						}
					}
				})
			],
		});

		BX.bind(this.domain, 'click', BX.delegate(this.showPopup, this));
	};

	BX.Landing.EditDomainForm.prototype =
	{
		showPopup: function ()
		{
			for (var i = 0, c = this.postfix.length; i < c; i++)
			{
				if (BX(BX.data(this.postfix[i], 'input-id')).value)
				{
					this.postfix[i].checked = true;
				}
			}
			this.popup.show();
		},

		editDomain: function ()
		{
			var domainName = '';
			for (var i = 0, c = this.postfix.length; i < c; i++)
			{
				if (
					this.postfix[i].checked &&
					typeof this.domains[i] !== 'undefined'
				)
				{
					this.domains[i].value = BX.util.trim(this.domains[i].value);
					if (this.domains[i].value !== '')
					{
						domainName = this.domains[i].value + this.postfix[i].value;
					}
				}
			}
			BX(this.fieldId + '_title').textContent = domainName;
			BX(this.fieldId).value = domainName;
		}
	};

	/**
	 * Domain name popup.
	 */
	BX.Landing.DomainNamePopup = function(params)
	{
		this.params = params;
		this.messages = params.messages || {};
		this.dialog = new BX.Landing.EditDomainForm(BX('ui-editable-domain'), {
			fieldId: params.fieldId,
			messages: {
				title: this.messages.title || '',
				errorEmpty: this.messages.errorEmpty || ''
			},
			content: BX('ui-editable-domain-content')
		});
		this.domainRadioBtn = this.dialog.popup.contentContainer.querySelectorAll('.ui-radio');
		this.domainInput = this.dialog.popup.contentContainer.querySelectorAll('.ui-domainname');
		this.inpList = this.dialog.popup.contentContainer.querySelectorAll('input.ui-domainname');
		this.textNode1 = this.dialog.popup.contentContainer.querySelector('#landing-form-domain-name-text');
		this.textNode2 = this.dialog.popup.contentContainer.querySelector('#landing-form-domain-any-name-text');
		this.saveBtn = BX('landing-popup-window-button-accept');
		this.clickBySavebtn = false;

		for (var a = 0, b = this.domainRadioBtn.length; a < b; a++) {

			BX.bind(this.domainRadioBtn[a], 'click', BX.delegate(function(event)
			{
				var targetInput = event.target.nextElementSibling.querySelector('.ui-domainname');
				var targetStatus = event.target.nextElementSibling.querySelector('.landing-site-name-status');
				this.runDomainCheck(targetInput, targetStatus);
			}, this));
		}

		for (var i = 0, c = this.domainInput.length; i < c; i++) {

			var inp = this.domainInput[i];
			inp.addEventListener( 'keyup', BX.debounce(function(event)
			{
				var diff = new Date() - this.time;
				if(diff < 1000)
				{
					return;
				}
				this.onKeyUp(event);
			}, 1000, this));
		}

		// domain type
		for (var i = 0; i < this.inpList.length; i++)
		{
			BX.bind(this.inpList[i], 'focus', BX.delegate(function(event)
			{
				event.target.parentNode.previousElementSibling.checked = true;
				var targetStatus = event.target.parentNode.querySelector('.landing-site-name-status');
				this.runDomainCheck(event.target, targetStatus);
			}, this));
		}

		BX.bind(this.saveBtn, 'click', BX.delegate(function()
		{
			this.findSelectedItem();
			this.clickBySavebtn = true;
		}, this));

		BX.addCustomEvent(this.dialog.popup, 'onPopupShow', BX.delegate(function()
		{
			this.findSelectedItem();
		}, this));
	};

	BX.Landing.DomainNamePopup.prototype =
	{
		runDomainCheck: function(targetInput, targetStatus)
		{
			this.findUnselectedItem();

			this.isAvailableDomain = null;
			this.isDeletedDomain = null;
			this.handlerDomainName(targetInput.value, targetInput);

			if(this.isAvailableDomain !== null && this.isDeletedDomain !== null)
			{
				this.highlight(this.isAvailableDomain, this.isDeletedDomain, targetInput, targetStatus);
			}
		},

		fillInstruction: function(domainName)
		{
			var domainParts = domainName.split('.');
			var domainRe = /^(com|net|org)\.[a-z]{2}$/;

			this.textNode2.parentNode.style.display = 'none';

			this.textNode1.textContent = domainName ? domainName : 'landing.mydomain';

			if (
				(domainParts.length === 2) ||
				(domainParts.length === 3 && domainParts[0] === 'www') ||
				(domainParts.length === 3 && (domainParts[1] + '.' + domainParts[2]).match(domainRe))
			)
			{
				this.textNode2.parentNode.style.display = 'table-row';
				if ((domainParts.length === 3 && domainParts[0] === 'www'))
				{
					this.textNode2.textContent = domainParts[1] + '.' + domainParts[2];
				}
				else
				{

					this.textNode1.textContent = 'www.' + domainName;
					this.textNode2.textContent = domainName;
				}
			}

			this.textNode1.textContent = BX.util.trim(this.textNode1.textContent) + '.';
			this.textNode2.textContent = BX.util.trim(this.textNode2.textContent) + '.';
		},

		onKeyUp: function(event)
		{
			this.time = new Date();
			this.handlerDomainName(event.target.value, event.target);
		},

		closePopup: function()
		{
			if(this.isAvailableDomain)
			{
				this.dialog.editDomain();
				this.dialog.popup.close();
			}
		},

		ajax: function(domainName, postfix)
		{
			BX.ajax({
				url: '/bitrix/tools/landing/ajax.php?action=Domain::check',
				method: 'POST',
				data: {
					data: {
						domain: domainName + postfix,
						filter: {
							'!ID': BX(this.params.fieldId + '_id').value
						}
					},
					sessid: BX.message('bitrix_sessid')
				},
				dataType: 'json',
				onsuccess: function (data) {
					if (data.result)
					{
						this.isAvailableDomain = data.result.available;
						this.isDeletedDomain = data.result.deleted;

						if (
							(
								postfix === '.bitrix24.site' ||
								postfix === '.bitrix24.shop' ||
								postfix === '.bitrix24site.by' ||
								postfix === '.bitrix24shop.by'
							)
							&&
							this.domainRadioBtn[0].checked
						)
						{
							// check available symbols for subdomain
							this.checkSubdomain();

						}
						else {
							this.fillInstruction(data.result.domain);

							if(this.domainRadioBtn[1].checked) {
								var domainLength = data.result.domain.length;
								this.checkDomain(domainLength);
							}
						}

						if(this.clickBySavebtn)
						{
							this.closePopup();
						}
						this.clickBySavebtn = false;

					}
				}.bind(this)
			});
		},

		checkSubdomain: function() {
			var subdomainInput = this.domainRadioBtn[0].nextElementSibling.querySelector('.ui-domainname');
			var subdomainStatus = this.domainRadioBtn[0].nextElementSibling.querySelector('.landing-site-name-status');

			// check available symbols for subdomain
			var domain = subdomainInput.value;
			if(this.domainRadioBtn[0].checked)
			{
				if(!(domain === '') && !(/^[\w_\-]+$/.test(domain)))
				{
					this.addDisableClass(subdomainInput);
					subdomainStatus.textContent = BX.message('LANDING_DOMAIN_INCORRECT');
					return;
				} else {
					this.removeDisableClass(subdomainInput);
					subdomainStatus.textContent = '';
				}
				this.saveBtn.classList.remove('btn-disabled');

				if(subdomainInput.classList.contains('ui-domainname-unavailable'))
				{
					subdomainInput.classList.remove('ui-domainname-unavailable');
					subdomainStatus.textContent = '';
				}
				this.highlight(this.isAvailableDomain, this.isDeletedDomain, subdomainInput, subdomainStatus);
			}
		},

		checkDomain: function(domainLength) {

			var domainInput = this.domainRadioBtn[1].nextElementSibling.querySelector('.ui-domainname');
			var domainStatus = this.domainRadioBtn[1].nextElementSibling.querySelector('.landing-site-name-status');

			var domainMaxlength = domainInput.getAttribute('maxlength');

			//check max length and availability domain name
			if (domainLength >= domainMaxlength && domainMaxlength !== null)
			{
				this.addDisableClass(domainInput);
				domainStatus.textContent = BX.message('LANDING_DOMAIN_LIMIT_LENGTH');
			} else {
				this.saveBtn.classList.remove('btn-disabled');

				if (domainInput.classList.contains('ui-domainname-unavailable'))
				{
					domainInput.classList.remove('ui-domainname-unavailable');
					domainStatus.textContent = '';
				}
				this.highlight(this.isAvailableDomain, this.isDeletedDomain, domainInput, domainStatus);
			}
		},

		handlerDomainName: function(value, target)
		{
			var domainName = value;
			var postfix = BX.data(target, 'postfix');
			this.ajax(domainName, postfix);
		},

		removeDisableClass: function(item)
		{
			item.classList.remove('ui-domainname-unavailable');
			this.saveBtn.classList.remove('btn-disabled');
		},

		addDisableClass: function(item)
		{
			item.classList.add('ui-domainname-unavailable');
			this.saveBtn.classList.add('btn-disabled');
		},

		resetHighlight: function(item, status)
		{
			item.classList.remove('ui-domainname-available');
			item.classList.remove('ui-domainname-unavailable');
			status.textContent = '';
		},

		findUnselectedItem: function()
		{
			for (var i = 0, c = this.domainRadioBtn.length; i < c; i++) {
				if(!(this.domainRadioBtn[i].checked))
				{
					var uncheckedInput = this.domainRadioBtn[i].nextElementSibling.querySelector('.ui-domainname');
					var uncheckedStatus = this.domainRadioBtn[i].nextElementSibling.querySelector('.landing-site-name-status');
					this.resetHighlight(uncheckedInput, uncheckedStatus);
				}
			}
		},

		findSelectedItem: function()
		{
			for (var i = 0, c = this.domainRadioBtn.length; i < c; i++)
			{
				var checkedInput = this.domainRadioBtn[i].nextElementSibling.querySelector('.ui-domainname');
				var checkedStatus = this.domainRadioBtn[i].nextElementSibling.querySelector('.landing-site-name-status');
				if(this.domainRadioBtn[i].checked)
				{
					this.runDomainCheck(checkedInput, checkedStatus);
				}
			}
		},

		highlight: function(isAvailableDomain, isDeletedDomain, item, status)
		{
			if(isAvailableDomain)
			{
				if(item.value === '')
				{
					this.saveBtn.classList.add('btn-disabled');
				}
				else {
					this.removeDisableClass(item);
				}

				status.textContent = '';
				item.classList.add('ui-domainname-available');

			} else {
				if(isDeletedDomain)
				{
					status.textContent = BX.message('LANDING_DOMAIN_EXIST2');
				} else {
					status.textContent = BX.message('LANDING_DOMAIN_EXIST');
				}
				this.addDisableClass(item);
				item.classList.remove('ui-domainname-available');
			}
		},
	};
})();