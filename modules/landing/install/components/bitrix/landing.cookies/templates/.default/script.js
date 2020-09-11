(function() {

	'use strict';

	BX.namespace('BX.Landing');

	/**
	 * Constructor.
	 * @param params
	 * @constructor
	 */
	BX.Landing.Cookies = function(params)
	{
		this.storageKey = 'bxCookies-v34';
		this.storage = window.localStorage;
		this.currentStorage = this.storage;

		this.enable = params.enable === true;
		this.siteId = parseInt(params.siteId);
		this.availableCodes = params.availableCodes || [];
		this.idButtonOpt = params.idButtonOpt || 'bx-landing-cookies-opt';
		this.idButtonOptLink = params.idButtonOptLink || 'bx-landing-cookies-opt-link';
		this.idButtonAccept = params.idButtonAccept || 'bx-landing-cookies-accept';
		this.idAgreementPopup = params.idAgreementPopup || 'bx-landing-cookies-popup';
		this.idCookiesNotice = params.idCookiesNotice || 'bx-landing-cookies-popup-notice';
		this.idAgreementSmallPopup = params.idAgreementSmallPopup || 'bx-landing-cookies-popup-warning';
		this.classNameMainAgreement = params.classNameMainAgreement || 'bx-landing-cookies-main-agreement';
		this.classNameAnalyticAgreements = params.classNameAnalyticAgreements || 'bx-landing-cookies-system-agreements';
		this.classNameTechnicalAgreements = params.classNameTechnicalAgreements || 'bx-landing-cookies-technical-agreements';
		this.classNameOtherAgreements = params.classNameOtherAgreements || 'bx-landing-cookies-other-agreements';
		this.classNameButtonSave = params.classNameButtonSave || 'bx-landing-cookies-button-save';
		this.classNameButtonCancel = params.classNameButtonCancel || 'bx-landing-cookies-button-cancel';
		this.classNameButtonClose = params.classNameButtonClose || 'bx-landing-cookies-button-close';
		this.classNameCookiesSwitcher = params.classNameCookiesSwitcher || 'bx-landing-cookies-switcher';
		this.messages = params.messages || {};
		this.agreementAjaxPath = params.agreementAjaxPath || '/';

		this.overlay = BX.create('div', {
			attrs: {className: 'bx-landing-cookies-popup-overlay'}
		});

		this.cookiesNotice = BX(this.idCookiesNotice);

		this.acceptedAgreements = {};
		this.agreementsChckRefs = {};
		this.popupModified = false;
		this.popupInited = false;
		this.dataLoaded = false;
		this.idMainAgreementContainer = null;
		this.idAnalyticAgreementsContainer = null;
		this.idTechnicalAgreementsContainer = null;
		this.idOtherAgreementsContainer = null;
		this.idButtonSave = null;
		this.idButtonCancel = null;
		this.idButtonClose = null;
		this.idButtonSwitcher = null;

		if (!this.enable)
		{
			this.enableAllCookies();
		}
		else
		{
			// bind buttons and initialize containers
			if (BX(this.idButtonOpt) && BX(this.idAgreementPopup))
			{
				BX.bind(BX(this.idButtonOpt), 'click', BX.delegate(this.openPopup, this));
			}
			if (BX(this.idButtonOptLink) && BX(this.idAgreementPopup))
			{
				BX.bind(BX(this.idButtonOptLink), 'click', BX.delegate(this.openPopup, this));
			}
			if (BX(this.idButtonAccept))
			{
				BX.bind(BX(this.idButtonAccept), 'click', BX.delegate(this.enableAllCookies, this));
			}
			if (this.cookiesNotice)
			{
				BX.bind(this.cookiesNotice, 'mouseenter', BX.delegate(this.showCookiesNoticeText, this));
				BX.bind(this.cookiesNotice, 'mouseleave', BX.delegate(this.hideCookiesNoticeText, this));
				BX.bind(this.cookiesNotice, 'click', BX.delegate(this.openPopup, this));
			}
			this.actualizeFromStorage();
		}
	};

	BX.Landing.Cookies.prototype =
	{
		/**
		 * Returns all available codes in system.
		 * @return {[]}
		 */
		getAvailableHooks: function()
		{
			var codes = [];
			this.availableCodes.map(function(code)
			{
				if (!BX.util.in_array(code, codes))
				{
					codes.push(code);
				}
			});
			return codes;
		},

		/**
		 * Enables all cookies to the site (hook is off).
		 */
		enableAllCookies: function()
		{
			var hooks = this.getAvailableHooks();
			this.setStorage(hooks);

			this.hideSmallPopup();
			this.showCookiesNotice();
		},

		/**
		 * Disables all cookies to the site (hook is off).
		 */
		disableAllCookies: function()
		{
			this.setStorage([]);
		},

		/**
		 * Gets saved data from storage and actualize cookies.
		 */
		actualizeFromStorage: function()
		{
			var storage = this.getStorage(true);
			if (storage !== null)
			{
				this.showCookiesNotice();
			}
			else
			{
				this.showSmallPopup();
			}
			this.fireEvent(storage || []);
		},

		/**
		 * Initializes elements in the popup.
		 */
		initializePopup: function()
		{
			if (this.popupInited)
			{
				return;
			}
			this.popupInited = true;
			if (this.classNameMainAgreement)
			{
				if (BX(this.idAgreementPopup))
				{
					this.idMainAgreementContainer = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameMainAgreement
					);
					this.idAnalyticAgreementsContainer = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameAnalyticAgreements
					);
					this.idTechnicalAgreementsContainer = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameTechnicalAgreements
					);
					this.idOtherAgreementsContainer = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameOtherAgreements
					);
					this.idButtonClose = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameButtonClose
					);
					this.idButtonSave = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameButtonSave
					);
					this.idButtonCancel = BX(this.idAgreementPopup).querySelector(
						'.' + this.classNameButtonCancel
					);
					this.idButtonSwitcher = [].slice.call(BX(this.idAgreementPopup).querySelectorAll(
						'.' + this.classNameCookiesSwitcher
					));
				}
				if (this.idButtonClose)
				{
					BX.bind(BX(this.idButtonClose), 'click', BX.delegate(this.onClickCloseIcon, this));
				}
				if (this.idButtonSave)
				{
					BX.bind(BX(this.idButtonSave), 'click', BX.delegate(this.savePopup, this));
				}
				if (this.idButtonCancel)
				{
					BX.bind(BX(this.idButtonCancel), 'click', BX.delegate(this.cancelPopup, this));
				}
				if (this.idButtonSwitcher)
				{
					this.idButtonSwitcher.map(function(node)
					{
						BX.bind(BX(node), 'click', this.switchAgreements.bind(this, BX.data(BX(node), 'type')));
					}.bind(this));
				}
			}
		},

		/**
		 * Loads agreements from backend.
		 */
		loadAgreements: function()
		{
			if (!this.idMainAgreementContainer || this.dataLoaded)
			{
				this.initCheckboxes();
				return;
			}

			this.idMainAgreementContainer.innerHTML = '...';

			BX.ajax({
				url: this.agreementAjaxPath + '?action=landing.api.cookies.getAgreements',
				method: 'POST',
				dataType: 'json',
				data: {
					siteId: this.siteId
				},
				onsuccess: function(result)
				{
					if (!result.data)
					{
						return;
					}
					this.dataLoaded = true;
					if (result.data['main'])
					{
						this.idMainAgreementContainer.innerHTML = result.data['main']['AGREEMENT_TEXT'];
					}
					var agreementsLoaded = false;
					if (result.data['analytic'] && this.idAnalyticAgreementsContainer)
					{
						agreementsLoaded = true;
						this.buildAgreements(
							this.idAnalyticAgreementsContainer,
							result.data['analytic']
						);
					}
					else if (this.idAnalyticAgreementsContainer)
					{
						BX.hide(
							this.idAnalyticAgreementsContainer.parentNode
						);
					}
					if (result.data['technical'] && this.idTechnicalAgreementsContainer)
					{
						agreementsLoaded = true;
						this.buildAgreements(
							this.idTechnicalAgreementsContainer,
							result.data['technical']
						);
					}
					else if (this.idTechnicalAgreementsContainer)
					{
						BX.hide(
							this.idTechnicalAgreementsContainer.parentNode
						);
					}
					if (result.data['other'] && this.idOtherAgreementsContainer)
					{
						agreementsLoaded = true;
						this.buildAgreements(
							this.idOtherAgreementsContainer,
							result.data['other']
						);
					}
					else if (this.idOtherAgreementsContainer)
					{
						BX.hide(
							this.idOtherAgreementsContainer.parentNode
						);
					}
					if (agreementsLoaded)
					{
						this.initCheckboxes();
					}
				}.bind(this)
			});
		},

		/**
		 * Shows settings popup.
		 */
		openPopup: function()
		{
			this.initializePopup();
			if (BX(this.idAgreementPopup))
			{
				BX.show(BX(this.idAgreementPopup));
				this.showOverlay();
				this.hideSmallPopup();
				this.loadAgreements();
			}
		},

		/**
		 * Closes settings popup.
		 */
		closePopup: function()
		{
			this.setPopupModified(false);
			if (BX(this.idAgreementPopup))
			{
				this.hideOverlay();
				BX.hide(BX(this.idAgreementPopup));
			}
			// this.hideSmallPopup();
			this.showCookiesNotice();
		},

		/**
		 * Saves accepted agreements and closes popup.
		 */
		savePopup: function()
		{
			if (this.popupModified)
			{
				var agreementCodes = this.getAcceptedAgreements();
				this.setStorage(agreementCodes);
			}
			else
			{
				this.enableAllCookies();
			}

			this.closePopup();
		},

		/**
		 * Declines agreements and closes popup.
		 */
		cancelPopup: function()
		{
			if (this.popupModified)
			{
				var agreementCodes = this.currentStorage;
				this.setStorage(agreementCodes);
			}
			else
			{
				this.disableAllCookies();
			}

			this.showSmallPopup();
			this.closePopup();
		},

		/**
		 * Declines agreements and closes popup.
		 */
		onClickCloseIcon: function()
		{
			this.closePopup();
		},

		/**
		 * Sets flag popup modified and rename buttons.
		 * @param {bool} flag Flag.
		 */
		setPopupModified: function(flag)
		{
			this.popupModified = flag;

			if (this.idButtonSave)
			{
				this.idButtonSave.textContent = this.popupModified
					? this.messages.acceptModified
					: this.messages.acceptAll;
			}
			if (this.idButtonCancel)
			{
				this.idButtonCancel.textContent = this.popupModified
					? this.messages.declineModified
					: this.messages.declineAll;
			}
		},

		/**
		 * Returns data from local storage.
		 * @package {bool} asIs If true returns original value.
		 * @return {object}
		 */
		getStorage: function(asIs)
		{
			var store = this.storage.getItem(this.storageKey);
			if (store) {
				store = JSON.parse(store);
			}
			if (asIs === true)
			{
				return store;
			}
			return store || [];
		},

		/**
		 * Saves data to local storage.
		 * @param store
		 */
		setStorage: function(store)
		{
			this.fireEvent(store);
			this.storage.setItem(this.storageKey, JSON.stringify(store));

			BX.ajax({
				url: this.agreementAjaxPath + '?action=landing.api.cookies.acceptAgreements',
				method: 'POST',
				dataType: 'json',
				data: {
					siteId: this.siteId,
					accepted: store
				}
			});
		},

		/**
		 * Fires event.
		 * @param {mixed} params Params to fire.
		 */
		fireEvent: function(params)
		{
			BX.onCustomEvent('BX.Landing.Cookies:onAccept', [params]);
		},

		/**
		 * Sets all checkboxes to the one state.
		 */
		switchAgreements: function(type)
		{
			if (!this.idButtonSwitcher)
			{
				return;
			}

			var globalSwitchersState = {};
			this.idButtonSwitcher.map(function(node)
			{
				var type = BX(node).getAttribute('data-type');
				globalSwitchersState[type] = BX(node).getAttribute('data-state') === 'true';
			});
			var setToState = globalSwitchersState[type] === true;

			for (var key in this.agreementsChckRefs)
			{
				var switcher = BX.UI.Switcher.getById(key);
				if (switcher)
				{
					var switcherType = switcher.getNode().getAttribute('data-type');
					if (switcherType === type)
					{
						switcher.check(setToState);
						this.acceptAgreement(key, setToState);
					}
				}
			}

			this.actualizeAgreementsSwitcher();
			this.setPopupModified(true);
		},

		/**
		 * Actualize agreements switcher title.
		 */
		actualizeAgreementsSwitcher: function()
		{
			if (this.idButtonSwitcher)
			{
				var typesChecked = {};
				for (var key in this.agreementsChckRefs)
				{
					var switcher = BX.UI.Switcher.getById(key);
					if (switcher)
					{
						var type = switcher.getNode().getAttribute('data-type');
						if (switcher.isChecked())
						{
							typesChecked[type] = true;
						}
					}
				}
				this.idButtonSwitcher.map(function(node)
				{
					var type = BX.data(BX(node), 'type');
					if (typesChecked[type] === true)
					{
						BX(node).textContent = this.messages.switcherOff;
						BX(node).setAttribute('data-state', false);
					}
					else
					{
						BX(node).textContent = this.messages.switcherOn;
						BX(node).setAttribute('data-state', true);
					}
				}.bind(this));
			}
		},

		/**
		 * Accepts one agreement.
		 * @param {string} code Agreement code.
		 * @param {bool} flag Checked flag.
		 */
		acceptAgreement: function(code, flag)
		{
			this.acceptedAgreements[code] = flag;
		},

		/**
		 * Returns all accepted agreements.
		 * @return {[]}
		 */
		getAcceptedAgreements: function()
		{
			var codes = [];

			for (var code in this.acceptedAgreements)
			{
				if (this.acceptedAgreements[code] === true)
				{
					codes.push(code);
				}
			}

			return codes;
		},

		/**
		 * Sets checked accepted agreements.
		 */
		initCheckboxes: function()
		{
			var codesFromStorage = this.getStorage();
			this.currentStorage = this.getStorage();

			for (var key in this.agreementsChckRefs)
			{
				var switcher = BX.UI.Switcher.getById(key);
				if (switcher)
				{
					var checked = BX.util.in_array(key, codesFromStorage) ||
								switcher.getNode().getAttribute('data-type') !== 'analytic';
					switcher.check(checked);
					this.acceptAgreement(key, checked);
				}
			}

			this.actualizeAgreementsSwitcher();
		},

		/**
		 * Builds agreements div.
		 * @param {HTMLElement} node Root element.
		 * @param {object} agreements Agreements data.
		 */
		buildAgreements: function(node, agreements)
		{
			var agreementsNodes = [];

			for (var key in agreements)
			{
				this.agreementsChckRefs[key] = BX.create('span', {
					attrs: {
						className: 'ui-switcher ui-switcher-size-sm',
						'data-type': agreements[key]['TYPE'],
						'data-switcher': '{"id":"' + key + '"}'
					},
					events: {
						click: function(key)
						{
							this.acceptAgreement(
								key,
								!BX.UI.Switcher.getById(key).isChecked()
							);
							this.setPopupModified(true);
							setTimeout(function() {
								this.actualizeAgreementsSwitcher();
							}.bind(this), 0);
						}.bind(this, key)
					}
				});
				agreementsNodes.push(BX.create('div', {
					attrs: {
						className: 'bx-landing-cookies-analytics-block' +
							((agreements[key]['TYPE'] !== 'analytic') ? ' bx-landing-cookies-analytics-block-disabled' : '')
					},
					children: [
						BX.create('div', {
							attrs: {className: 'bx-landing-cookies-analytics-header'},
							children: [
								BX.create('div', {
									attrs: {className: 'bx-landing-cookies-analytics-title'},
									html: agreements[key]['TITLE']
								}),
								this.agreementsChckRefs[key],
							]
						}),
						BX.create('div', {
							attrs: {className: 'bx-landing-cookies-analytics-content'},
							html: agreements[key]['CONTENT']
						})
					]
				}));
			}

			node.appendChild(BX.create('div', {
				children: agreementsNodes
			}));

			BX.UI.Switcher.initByClassName();
		},

		//Popup parts below

		showCookiesNotice: function()
		{
			if (BX(this.idAgreementSmallPopup))
			{
				this.cookiesNotice.style.display = 'flex';
			}
		},

		showCookiesNoticeText: function()
		{
			this.cookiesNotice.classList.add('bx-landing-cookies-popup-notice-full');
			var textWidth = this.cookiesNotice.querySelector('.bx-landing-cookies-popup-notice-text').offsetWidth;
			this.cookiesNotice.style.width = this.cookiesNotice.offsetWidth + textWidth + 'px';
		},

		hideCookiesNoticeText: function()
		{
			this.cookiesNotice.style.width = '27px';
			this.cookiesNotice.classList.remove('bx-landing-cookies-popup-notice-full');
		},

		showOverlay: function()
		{
			document.body.append(this.overlay);
		},

		hideOverlay: function()
		{
			this.overlay.remove();
		},

		showSmallPopup: function()
		{
			if (BX(this.idAgreementSmallPopup))
			{
				BX(this.idAgreementSmallPopup).style.display = 'block';
			}
		},

		hideSmallPopup: function()
		{
			if (BX(this.idAgreementSmallPopup))
			{
				BX(this.idAgreementSmallPopup).style.display = 'none';
			}
		}
	};

})();