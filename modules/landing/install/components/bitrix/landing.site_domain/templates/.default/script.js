(function() {

	'use strict';

	BX.namespace('BX.Landing');

	/**
	 * Constructor for helper.
	 * @param params
	 * @constructor
	 */
	BX.Landing.SiteDomainHelper = function(params)
	{
		this.idDomainName = params.idDomainName;
		this.idDomainMessage = params.idDomainMessage;
		this.idDomainLoader = params.idDomainLoader;
		this.idDomainErrorAlert = params.idDomainErrorAlert;

		this.classes = {
			dangerBorder: 'ui-ctl-danger',
			successBorder: 'ui-ctl-success',
			dangerAlert: 'landing-domain-alert-danger',
			successAlert: 'landing-domain-alert-success'
		};

		if (this.idDomainName)
		{
			this.idDomainNameParent = this.idDomainName.parentNode;
		}
	};
	BX.Landing.SiteDomainHelper.prototype =
	{
		/**
		 * Shows loader div near input.
		 */
		showLoader: function()
		{
			this.clearMessage();
			BX.show(this.idDomainLoader);
		},
		/**
		 * Hides loader div near input.
		 */
		hideLoader: function()
		{
			BX.hide(this.idDomainLoader);
		},
		/**
		 * Returns true if loader showed.
		 * @return {boolean}
		 */
		isLoaderShowed: function()
		{
			return this.idDomainLoader && this.idDomainLoader.style.display !== 'none';
		},
		/**
		 * Marks input with success class.
		 * @param {string} successMessage Success message.
		 */
		setSuccess: function(successMessage)
		{
			if (this.idDomainErrorAlert)
			{
				BX.hide(this.idDomainErrorAlert);
			}
			this.setMessage(successMessage);
		},
		/**
		 * Sets error message on error occurred or hide message if errorMessage is empty.
		 * @param {string} errorMessage Error message.
		 */
		setError: function(errorMessage)
		{
			this.setMessage(errorMessage, true);
		},
		/**
		 * Returns true if error message showed.
		 * @return {boolean}
		 */
		isErrorShowed: function()
		{
			return this.idDomainMessage &&
					BX.hasClass(this.idDomainMessage, this.classes.dangerAlert) &&
					this.idDomainMessage.style.display !== 'none';
		},
		/**
		 * Sets success or fail message.
		 * @param {string} message Error message.
		 * @param {boolean} error Error message (false by default).
		 */
		setMessage: function(message, error)
		{
			if (!this.idDomainMessage)
			{
				return;
			}
			error = !!error;
			this.clearMessage();
			if (message)
			{
				if (this.idDomainNameParent)
				{
					BX.addClass(
						this.idDomainNameParent,
						error
							? this.classes.dangerBorder
							: this.classes.successBorder
					);
				}
				BX.addClass(
					this.idDomainMessage,
					error
						? this.classes.dangerAlert
						: this.classes.successAlert
				);
				BX.show(this.idDomainMessage);
				this.idDomainMessage.innerHTML = message;
			}
		},
		/**
		 * Clears message alert.
		 */
		clearMessage: function()
		{
			if (!this.idDomainMessage)
			{
				return;
			}

			if (this.idDomainNameParent)
			{
				BX.removeClass(this.idDomainNameParent, this.classes.dangerBorder);
				BX.removeClass(this.idDomainNameParent, this.classes.successBorder);
			}
			BX.removeClass(this.idDomainMessage, this.classes.dangerAlert);
			BX.removeClass(this.idDomainMessage, this.classes.successAlert);

			this.idDomainMessage.innerHTML = '';
		}
	};

	/**
	 * Constructor for work with common input.
	 * @param params
	 * @constructor
	 */
	BX.Landing.SiteDomainInput = function(params)
	{
		this.domainId = params.domainId;
		this.domainName = params.domainName;
		this.domainPostfix = params.domainPostfix || '';
		this.idDomainName = params.idDomainName;
		this.idDomainINA = params.idDomainINA;
		this.idDomainDnsInfo = params.idDomainDnsInfo;
		this.idDomainSubmit = params.idDomainSubmit;
		this.previousDomainName = null;
		this.helper = new BX.Landing.SiteDomainHelper(params);

		this.classes = {
			submit: 'ui-btn-clock'
		};

		if (this.idDomainName)
		{
			BX.bind(this.idDomainName, 'keyup', BX.debounce(function(event)
			{
				this.keyupCallback(event);
			}.bind(this), 500, this));
		}

		if (this.idDomainSubmit)
		{
			BX.bind(this.idDomainSubmit, 'click', function(event)
			{
				this.checkSubmit(event);
			}.bind(this));
		}

		this.fillDnsInstruction(this.domainName);
	};
	BX.Landing.SiteDomainInput.prototype =
	{
		/**
		 * Returns true if domain name is empty.
		 * return {bool}
		 */
		domainNameIsEmpty: function()
		{
			this.idDomainName.value = BX.util.trim(this.idDomainName.value);
			return this.idDomainName.value === '';
		},
		/**
		 * Makes some check before submit.
		 */
		checkSubmit: function()
		{
			if (BX.hasClass(this.idDomainSubmit, this.classes.submit))
			{
				BX.PreventDefault();
			}
			else if (this.domainNameIsEmpty())
			{
				this.helper.setError(BX.message('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
				BX.PreventDefault();
			}
			else if (this.helper.isErrorShowed())
			{
				BX.PreventDefault();
			}
			else
			{
				BX.addClass(this.idDomainSubmit, this.classes.submit);
			}
		},
		/**
		 * Handler on keyup input.
		 */
		keyupCallback: function()
		{
			this.idDomainName.value = BX.util.trim(this.idDomainName.value);
			if (this.idDomainName.value === '')
			{
				this.helper.setError(BX.message('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
				return;
			}

			var domainName = this.idDomainName.value;

			if (this.previousDomainName === domainName)
			{
				return;
			}

			this.previousDomainName = domainName;
			this.helper.showLoader();

			BX.ajax({
				url: '/bitrix/tools/landing/ajax.php?action=Domain::check',
				method: 'POST',
				data: {
					data: {
						domain: domainName + this.domainPostfix,
						filter:
							this.domainId
							? { '!ID': this.domainId }
							: {}
					},
					sessid: BX.message('bitrix_sessid')
				},
				dataType: 'json',
				onsuccess: function (data)
				{
					this.helper.hideLoader();
					if (data.type === 'success')
					{
						if (!data.result.available)
						{
							this.helper.setError(
								!!data.result.deleted
								? BX.message('LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED')
								: BX.message('LANDING_TPL_ERROR_DOMAIN_EXIST')
							);
						}
						else if (!data.result.domain)
						{
							this.helper.setError(
								BX.message('LANDING_TPL_ERROR_DOMAIN_INCORRECT')
							);
						}
						else
						{
							this.fillDnsInstruction(data.result.domain);
							this.helper.setSuccess(
								BX.message('LANDING_TPL_DOMAIN_AVAILABLE')
							);
						}

						if (data.result.dns && this.idDomainINA)
						{
							this.idDomainINA.textContent = data.result.dns['INA'];
						}
					}
					else
					{
						this.helper.setError('Error processing');
					}
				}.bind(this)
			});
		},
		/**
		 * Sets new DNS instructions after domain name change.
		 * @param {string} domainName Domain name.
		 */
		fillDnsInstruction: function(domainName)
		{
			if (!this.idDomainDnsInfo)
			{
				return;
			}
			if (!domainName)
			{
				return;
			}
			if (!this.idDomainDnsInfo.rows[1])
			{
				return;
			}
			if (!this.idDomainDnsInfo.rows[2])
			{
				return;
			}
			if (
				this.idDomainDnsInfo.rows[1].cells.length < 3 ||
				this.idDomainDnsInfo.rows[2].cells.length < 3
			)
			{
				return;
			}

			var cNameRecordRow = this.idDomainDnsInfo.rows[1];
			var aRecordRow = this.idDomainDnsInfo.rows[2];
			var domainParts = domainName.split('.');
			var domainRe = /^(com|net|org|co|kiev|spb|kharkov|msk|in)\.[a-z]{2}$/;

			aRecordRow.style.display = 'none';
			cNameRecordRow.cells[0].textContent = domainName ? domainName : 'landing.mydomain';

			if (
				(domainParts.length === 2) ||
				(domainParts.length === 3 && domainParts[0] === 'www') ||
				(domainParts.length === 3 && (domainParts[1] + '.' + domainParts[2]).match(domainRe))
			)
			{
				aRecordRow.style.display = 'table-row';
				if ((domainParts.length === 3 && domainParts[0] === 'www'))
				{
					aRecordRow.cells[0].textContent = domainParts[1] + '.' + domainParts[2] + '.';
				}
				else
				{
					cNameRecordRow.cells[0].textContent = 'www.' + domainName + '.';
					aRecordRow.cells[0].textContent = domainName + '.';
				}
			}
		}
	};
	/**
	 * Constructor for 'private' tab.
	 * @param {Object} params Params object.
	 * @constructor
	 */
	BX.Landing.SiteDomainPrivate = function(params)
	{
		new BX.Landing.SiteDomainInput(params);
	};
	/**
	 * Constructor for 'bitrix24' tab.
	 * @param {Object} params Params object.
	 * @constructor
	 */
	BX.Landing.SiteDomainBitrix24 = function(params)
	{
		new BX.Landing.SiteDomainInput(params);
	};
	/**
	 * Constructor for 'free' tab.
	 * @param {Object} params Params object.
	 * @constructor
	 */
	BX.Landing.SiteDomainFree = function(params)
	{
		this.idDomainSubmit = params.idDomainSubmit;
		this.idDomainCheck = params.idDomainCheck;
		this.idDomainName = params.idDomainName;
		this.idDomainAnother = params.idDomainAnother;
		this.idDomainAnotherMore = params.idDomainAnotherMore;
		this.idDomainErrorAlert = params.idDomainErrorAlert;
		this.saveBlocker = params.saveBlocker;
		this.saveBlockerCallback = params.saveBlockerCallback;
		this.promoCloseIcon = params.promoCloseIcon;
		this.promoCloseLink = params.promoCloseLink;
		this.promoBlock = params.promoBlock;
		this.maxVisibleSuggested = parseInt(params.maxVisibleSuggested || 10);
		this.tld = params.tld;
		this.helper = new BX.Landing.SiteDomainHelper(params);

		this.classes = {
			submit: 'ui-btn-clock'
		};

		if (this.promoCloseIcon && this.promoCloseLink) {
			BX.bind(this.promoCloseIcon, 'click', this.closePromoBlock.bind(this));
			BX.bind(this.promoCloseLink, 'click', this.closePromoBlock.bind(this));
		}

		if (this.idDomainAnotherMore) {
			BX.bind(this.idDomainAnotherMore, 'click', this.showMoreDomains.bind(this));
		}

		if (this.idDomainSubmit)
		{
			BX.bind(this.idDomainSubmit, 'click', function(event)
			{
				this.checkSubmit(event);
			}.bind(this));
		}

		if (this.idDomainCheck && this.idDomainName)
		{
			BX.bind(this.idDomainCheck, 'click', function(event)
			{
				this.checkDomain(event);
			}.bind(this));
		}

		if (this.idDomainName)
		{
			BX.bind(this.idDomainName, 'keyup', BX.debounce(function(event)
			{
				this.keyupCallback(event);
			}.bind(this), 500, this));
		}
	};
	BX.Landing.SiteDomainFree.prototype =
	{
		/**
		 * Handler on keyup input.
		 */
		keyupCallback: function()
		{
			if (this.idDomainName.value === '')
			{
				this.helper.setError(BX.message('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
				return;
			}

			this.helper.setSuccess('');
		},
		/**
		 * Closes promo banner.
		 */
		closePromoBlock: function()
		{
			this.promoBlock.remove();
		},
		/**
		 * Shows full block of suggesiotn domains.
		 */
		showMoreDomains: function()
		{
			this.idDomainAnother.style.height = this.idDomainAnother.children[0].offsetHeight + 'px';
			this.idDomainAnotherMore.classList.add('landing-domain-block-available-btn-hide');
		},
		/**
		 * Makes some check before submit.
		 */
		checkSubmit: function()
		{
			if (BX.hasClass(this.idDomainSubmit, this.classes.submit))
			{
				BX.PreventDefault();
				return;
			}

			this.checkDomainName();

			if (this.helper.isErrorShowed())
			{
				BX.PreventDefault();
			}
			else if (this.saveBlocker && this.saveBlockerCallback)
			{
				this.saveBlockerCallback();
				BX.PreventDefault();
			}
			else
			{
				BX.addClass(this.idDomainSubmit, this.classes.submit);
			}
		},
		/**
		 * Sets suggested domain to the main input.
		 * @param {string} domainName Domain name.
		 */
		selectSuggested: function(domainName)
		{
			this.idDomainName.value = domainName;
			this.helper.setSuccess(
				BX.message('LANDING_TPL_DOMAIN_AVAILABLE')
			);
		},
		/**
		 * Fill suggested domain area.
		 * @param {array} suggest Suggested domains.
		 */
		fillSuggest: function(suggest)
		{
			if (!this.idDomainAnother)
			{
				return;
			}

			if (this.idDomainAnotherMore)
			{
				if (suggest.length > this.maxVisibleSuggested)
				{
					BX.show(this.idDomainAnotherMore);
					this.idDomainAnotherMore.classList.remove('landing-domain-block-available-btn-hide');
				}
				else
				{
					BX.hide(this.idDomainAnotherMore);
				}
			}

			if (suggest.length)
			{
				BX.show(this.idDomainAnother.parentNode);
			}
			else
			{
				BX.hide(this.idDomainAnother.parentNode);
			}

			var children = [];

			for (var i = 0, c = suggest.length; i < c; i++)
			{
				children.push(
					BX.create(
						'div',
						{
							props: {
								className: 'landing-domain-block-available-item'
							},
							children: [
								BX.create(
									'input',
									{
										props: {
											className: ''
										},
										attrs: {
											name: 'domain-edit-suggest',
											id: 'domain-edit-suggest-' + i,
											type: 'radio'
										},
										events: {
											click: function(i)
											{
												this.selectSuggested(suggest[i]);
											}.bind(this, i)
										}
									}
								),
								BX.create(
									'label',
									{
										props: {
											className: 'landing-domain-block-available-label'
										},
										attrs: {
											for: 'domain-edit-suggest-' + i
										},
										text: suggest[i]
									}
								)
							]
						}
					)
				);
			}

			this.idDomainAnother.innerHTML = '';
			this.idDomainAnother.appendChild(BX.create(
				'div',
				{
					props: {
						className: 'landing-domain-block-available-list'
					},
					children: children
				}
			));

			if (this.idDomainAnotherMore.style.display === 'none')
			{
				this.idDomainAnother.style.height = this.idDomainAnother.children[0].offsetHeight + 'px';
			}
			else
			{
				this.idDomainAnother.style.height = 80 + 'px';
			}
		},
		/**
		 * Checks that domain name is correct.
		 */
		checkDomainName: function()
		{
			this.idDomainName.value = BX.util.trim(this.idDomainName.value);
			var domainRe = RegExp('^[a-z0-9-]+\.' + (this.tld[0].toLowerCase()) + '$');

			if (this.idDomainName.value === '')
			{
				this.helper.setError(BX.message('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
			}
			else if (!domainRe.test(this.idDomainName.value.toLowerCase()))
			{
				this.helper.setError(BX.message('LANDING_TPL_ERROR_DOMAIN_CHECK'));
			}
			else if (
				this.idDomainName.value.indexOf('--') !== -1 ||
				this.idDomainName.value.indexOf('-.') !== -1 ||
				this.idDomainName.value.indexOf('-') === 0
			)
			{
				this.helper.setError(BX.message('LANDING_TPL_ERROR_DOMAIN_CHECK_DASH'));
			}
		},
		/**
		 * Makes whois query for user pointed domain.
		 */
		checkDomain: function()
		{
			BX.PreventDefault();

			if (this.helper.isLoaderShowed())
			{
				return;
			}

			this.checkDomainName();
			if (this.helper.isErrorShowed())
			{
				return;
			}

			this.helper.showLoader();
			this.fillSuggest([]);

			BX.ajax({
				url: '/bitrix/tools/landing/ajax.php?action=Domain::whois',
				method: 'POST',
				data: {
					data: {
						domainName: this.idDomainName.value,
						tld: this.tld
					},
					sessid: BX.message('bitrix_sessid')
				},
				dataType: 'json',
				onsuccess: function (data)
				{
					this.helper.hideLoader();
					if (data.type === 'success')
					{
						var result = data.result;
						if (!result.enable)
						{
							if (result.suggest)
							{
								this.fillSuggest(result.suggest);
							}
							this.helper.setError(
								BX.message('LANDING_TPL_ERROR_DOMAIN_EXIST')
							);
						}
						else
						{
							this.helper.setSuccess(
								BX.message('LANDING_TPL_DOMAIN_AVAILABLE')
							);
						}
					}
				}.bind(this)
			});
		}
	};
})();