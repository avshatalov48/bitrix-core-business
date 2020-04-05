BX.namespace('BX.Security.UserEdit');

BX.Security.UserEdit.Otp = (function getUserOtp(BX)
{
	"use strict";

	var Manager = function(userId, options)
	{
		var defaults = {
			'successfulUrl': document.location.href,
			'deactivateDays' : null,
			'availableTypes': null,
			'ui': {
				'activateButtonId': 'otp-activate',
				'deactivateButtonId': 'otp-deactivate',
				'defferButtonId': 'otp-deffer',
				'mandatoryActivateButtonId': 'otp-mandatory-active',
				'reinitializeButtonId': 'otp-reinitialize'
			}
		};
		options = options || {};
		this._options = mergeObjects(defaults, options);

		this.userId = userId || 0;

		var modelOptions = {
			'availableTypes': this._options.availableTypes,
			'successfulUrl': this._options.successfulUrl
		};

		this.device = new Device(this.userId, modelOptions);
		this.mobile = new Mobile(this.userId, modelOptions);
		this.recovery = new RecoveryCodes(this.userId, modelOptions);

		var tmp = null;
		tmp = BX(this._options.ui.deactivateButtonId);
		if (tmp)
		{
			this.initializeDeactivatePopup(tmp, 'deactivate');
		}

		tmp = BX(this._options.ui.defferButtonId);
		if (tmp)
		{
			this.initializeDeactivatePopup(tmp, 'deffer');
		}

		tmp = BX(this._options.ui.mandatoryActivateButtonId);
		if (tmp)
		{
			this.initializeDeactivatePopup(tmp, 'deffer', 5);
		}

		tmp = BX(this._options.ui.activateButtonId);
		if (tmp)
		{
			BX.bind(
				tmp,
				'click',
				this.mobile.activateOtp.bind(this.mobile)
			)
		}

		tmp = BX(this._options.ui.reinitializeButtonId);
		if (tmp)
		{
			BX.bind(
				tmp,
				'click',
				BX.proxy(function onReinitialize()
				{
					var elements = window.document.body.querySelectorAll('[data-show-on-reinitialize="yes"]');
					[].forEach.call(
						elements,
						function showElements(element)
						{
							if (element.style.display)
								element.style.display = '';
							else
								element.style.display = 'none'
						},
						this
					);
				}, this)
			)
		}
	};

	Manager.prototype.initializeDeactivatePopup = function(element, action, startDay)
	{
		action = action || 'deactivate';

		if (this._options.deactivateDays)
		{

			var daysObj = [];
			for (var i in this._options.deactivateDays)
			{
				if (!this._options.deactivateDays.hasOwnProperty(i))
					continue;

				if (startDay && i < startDay)
					continue;

				daysObj.push({
					'TEXT': this._options.deactivateDays[i],
					'ONCLICK': this.mobile.deactivateOtp.bind(this.mobile, null, action, i)
				});
			}

			BX.bind(
				element,
				'click',
				(function (event)
				{
					if (event)
						event.preventDefault();

					BX.adminShowMenu(element, daysObj, {'active_class': '', 'close_on_click': true});
				}).bind(this)
			);
		}
		else
		{
			BX.bind(
				element,
				'click',
				this.mobile.deactivateOtp.bind(this, null, action, startDay? startDay: 0)
			);
		}
	};

	/* -----------/Base popup model/--------------*/

	var BaseModel = function(userId, options)
	{
		var defaults = {
			'actionUrl': '/bitrix/admin/security_otp.ajax.php?lang=' + BX.message('LANGUAGE_ID'),
			'onCompleteCallback': BX.DoNothing,
			'ui': {
				'showButtonId': null,
				'id': null
			}
		};

		options = options || {};
		this._options = mergeObjects(defaults, options);
		this.initialized = false;
		this.popup = null;
		this.container = null;
		this.errorContainer = null;
		this.userId = userId;
		this.showButton = BX(this._options.ui.showButtonId);
		this.type = null;
		this.typeMenu = [];

		BX.bind(
			this.showButton,
			'click',
			this.show.bind(this)
		);
	};

	BaseModel.prototype.show = function(event)
	{
		if (!this.initialized)
			this.initialize();
		else
			this.cleanPopup();

		this.onShow();

		if (event)
			event.preventDefault();
	};

	// Override this methods if needed;-)
	BaseModel.prototype.onShow = function() {};
	BaseModel.prototype.onInitialize = function() {};
	BaseModel.prototype.getSecret = function () {};

	BaseModel.prototype.getType = function() {
		return this.type;
	};

	BaseModel.prototype.initialize = function()
	{
		this.initialized = true;
		this.container = BX(this._options.ui.id);
		this.popup = this.getPopup();
		this.errorContainer = this.container.querySelector('[data-role="error-container"]');

		for(var type in this._options.availableTypes)
		{
			if (!this._options.availableTypes.hasOwnProperty(type))
				continue;

			this.typeMenu.push({
				'TEXT': this._options.availableTypes[type].title,
				'ONCLICK': (function onType(type, isTwoCodeRequired) {
					if (isTwoCodeRequired === void 0)
						isTwoCodeRequired = true;

					this.type = type;
					this.onShow(true, isTwoCodeRequired);
				}).bind(this, this._options.availableTypes[type].type, this._options.availableTypes[type]['required_two_code'])
			});
		}

		var checkCodes = this.container.querySelectorAll('input[data-role="check-code"]');

		this.popup.ClearButtons();
		this.popup.SetButtons([
			{
				title: BX.message('JS_CORE_WINDOW_SAVE'),
				id: 'check-button',
				className: 'adm-btn-save',
				action: (function proxyCheck(event) {
					if (event)
						event.preventDefault();

					this.onCheck(
						checkCodes[0],
						checkCodes[1] || null // Second check code may be absent for some OtpAlgorithm
					);
				}).bind(this)
			},
			BX.CDialog.btnCancel
		]);

		this.onInitialize();
	};

	BaseModel.prototype.getPopup = function()
	{
		return new BX.CDialog({
			title : this.container.getAttribute('data-title') || '',
			resizable: false,
			content: this.container
		});
	};

	BaseModel.prototype.cleanPopup = function()
	{
		[].forEach.call(
			this.container.querySelectorAll('[data-autoclear="yes"]'),
			function cleanElement(element)
			{
				switch (element.tagName)
				{
					case 'INPUT':
						element.value = '';
						break;
					case 'SELECT':
						break;
					default:
						BX.cleanNode(element);
				}
			},
			this
		);
	};

	BaseModel.prototype.sendRequest = function(action, data, onSuccess, onFailure)
	{
		BX.showWait();

		data = data || {};
		data.action = action || 'check';
		data.sessid = BX.bitrix_sessid();
		if (!data.userId)
			data.user = this.userId;

		data = BX.ajax.prepareData(data);

		return BX.ajax({
			'method': 'POST',
			'dataType': 'json',
			'url': this._options['actionUrl'],
			'data':  data,
			'onsuccess': this.onRequestSuccess.bind(this, onSuccess),
			'onfailure': this.onRequestFailed.bind(this, onFailure)
		});
	};

	BaseModel.prototype.onRequestSuccess = function(callback, response)
	{
		BX.closeWait();

		if (!response['status'])
		{
			this.onRequestFailed(null, response);
		}
		else if (response['status'] !== 'ok')
		{
			this.onRequestFailed(null, response);
		}
		else
		{
			callback(response);
		}
	};

	BaseModel.prototype.onRequestFailed = function(callback, response)
	{
		BX.closeWait();

		if (!callback)
		{
			if (response['error'])
				this.showError(response['error']);
			else
				this.showError(BX.message('SEC_OTP_UNKNOWN_ERROR'));
		}
		else
		{
			callback(response);
		}
	};

	BaseModel.prototype.showError = function(errorMessage)
	{
		if (!this.errorContainer)
			return;

		var errorElement = BX.create('div', {
			'children': [
				BX.create('div', {
					'text': BX.message('SEC_OTP_ERROR_TITLE')
				}),
				BX.create('div', {
					'html': errorMessage
				})
			],
			'attrs': {'className': "bx-notice error"}
		});

		this.errorContainer.appendChild(errorElement);
	};

	BaseModel.prototype.clearErrors = function()
	{
		if (!this.errorContainer)
			return;

		BX.cleanNode(this.errorContainer);
	};

	BaseModel.prototype.onCheck = function(sync1, sync2)
	{
		this.clearErrors();
		this.activate(sync1.value, sync2? sync2.value: '');
	};

	BaseModel.prototype.activate = function(sync1, sync2)
	{
		var data = {
			'secret': this.getSecret(),
			'type': this.getType(),
			'sync1': sync1,
			'sync2': sync2
		};

		this.sendRequest('check_activate', data, BX.proxy(this.onFinish, this));
	};

	BaseModel.prototype.onFinish = function()
	{
		window.location.replace(this._options.successfulUrl);
	};

	BaseModel.prototype.showPopup = function()
	{
		this.popup.Show();
		this.popup.adjustSizeEx();
		BX.defer(this.popup.adjustPos, this.popup)();
	};

	BaseModel.prototype.showTypeTitle = function(type)
	{
		var elements = this.container.querySelectorAll('[data-show-type]');
		[].forEach.call(
			elements,
			function showHide(el)
			{
				if (el.getAttribute('data-show-type') == type)
					el.style.display = '';
				else
					el.style.display = 'none';
			}
		)
	};

	BaseModel.prototype.showHideRedundantCodes = function(isTwoCodeRequired)
	{
		var elements = this.container.querySelectorAll('[data-require-two-codes="yes"]');

		[].forEach.call(
			elements,
			function showHide(el)
			{
				if (isTwoCodeRequired)
					el.style.display = '';
				else
					el.style.display = 'none';
			}
		);
	};

	/* -----------/Device popup/--------------*/

	var Device = function(userId, options)
	{
		var defaults = {
			'ui': {
				'showButtonId': 'otp-connect-device',
				'id': 'otp-device-popup'
			}
		};
		options = options || {};
		options = mergeObjects(defaults, options);

		this.secretCodeElement = null;
		this.typeElement = null;

		Device.superclass.constructor.call(this, userId, options);
	};

	BX.extend(Device, BaseModel);

	Device.prototype.onShow = function(typeChosen, isTwoCodeRequired)
	{
		if (!typeChosen && this.typeMenu.length)
		{
			// User must choose OTP generation algorithm
			BX.adminShowMenu(this.showButton, this.typeMenu, {'active_class': 'adm-btn-save-active'});
			return;
		}

		this.showHideRedundantCodes(isTwoCodeRequired);
		this.showTypeTitle(this.type);
		this.showPopup();
	};

	Device.prototype.onInitialize = function()
	{
		this.secretCodeElement = this.container.querySelector('[data-role="secret-code"]');
		this.typeElement = this.container.querySelector('[data-role="type-selector"]');
	};

	Device.prototype.getSecret = function ()
	{
		return this.secretCodeElement.value;
	};

	/* -----------/Mobile popup/--------------*/

	var Mobile = function(userId, options)
	{
		var defaults = {
			'ui': {
				'showButtonId': 'otp-connect-mobile',
				'id': 'otp-mobile-popup'
			}
		};
		options = options || {};
		options = mergeObjects(defaults, options);

		this.secret = null;
		this.qrCodeElement = null;
		this.appSecretElement = null;

		Mobile.superclass.constructor.call(this, userId, options);
	};

	BX.extend(Mobile, BaseModel);

	Mobile.prototype.onShow = function(typeChosen)
	{
		if (!typeChosen && this.typeMenu.length)
		{
			// User must choose OTP generation algorithm
			BX.adminShowMenu(this.showButton, this.typeMenu, {'active_class': 'adm-btn-save-active'});
			return;
		}

		this.sendRequest(
			'get_vew_params',
			{'type': this.type || ''},
			(function onGetParams(response)
			{
				this.secret = response.data.secret;
				this.type = response.data.type;

				this.showHideRedundantCodes(response.data.isTwoCodeRequired);
				this.showTypeTitle(response.data.type);
				this.drawQrCode(this.qrCodeElement, response.data.provisionUri);
				this.appSecretElement.innerHTML = BX.util.htmlspecialchars(response.data.appSecretSpaced);

				this.showPopup();
			}).bind(this)
		);
	};

	Mobile.prototype.onInitialize = function()
	{
		this.qrCodeElement = this.container.querySelector('[data-role="qr-code-block"]');
		this.appSecretElement = this.container.querySelector('[data-role="app-code-block"]');

		BX.bind(
			BX('connect-mobile-manual-input'),
			'click',
			function()
			{
				BX('connect-by-manual-input').style.display = '';
				BX('connect-by-qr').style.display = 'none';
			}
		);

		BX.bind(
			BX('connect-by-manual-input'),
			'click',
			function()
			{
				BX('connect-by-manual-input').style.display = 'none';
				BX('connect-by-qr').style.display = '';
			}
		);
	};

	Mobile.prototype.getSecret = function ()
	{
		return this.secret;
	};

	Mobile.prototype.drawQrCode = function(element, provisionUri)
	{
		new QRCode(element, {
			text: provisionUri,
			width: 200,
			height: 200,
			colorDark : '#000000',
			colorLight : '#ffffff',
			correctLevel : QRCode.CorrectLevel.H
		});
	};

	Mobile.prototype.deactivateOtp = function(event, action, numDays)
	{
		if (event)
			event.preventDefault();

		this.sendRequest(
			action,
			{'days': numDays},
			(function onDeactivated()
			{
				window.location.replace(this._options.successfulUrl);
			}).bind(this)
		);
	};

	Mobile.prototype.activateOtp = function(event)
	{
		if (event)
			event.preventDefault();

		this.sendRequest(
			'activate',
			null,
			(function onActivated()
			{
				window.location.replace(this._options.successfulUrl);
			}).bind(this)
		);
	};

	/* -----------/Recovery codes popup/--------------*/

	var RecoveryCodes = function(userId, options)
	{
		var defaults = {
			'actionUrl': '/bitrix/admin/security_otp.ajax.php?lang=' + BX.message('LANGUAGE_ID'),
			'publicUrl': '/bitrix/admin/security_otp_recovery_codes.php?lang=' + BX.message('LANGUAGE_ID'),
			'ui': {
				'showButtonId': 'otp-show-recovery-codes',
				'id': 'otp-recovery-codes'
			}
		};
		options = options || {};
		options = mergeObjects(defaults, options);

		RecoveryCodes.superclass.constructor.call(this, userId, options);
	};

	BX.extend(RecoveryCodes, BaseModel);

	RecoveryCodes.prototype.onShow = function(regenerate)
	{
		regenerate = regenerate || null;
		this.sendRequest(
			regenerate? 'regenerate_recovery_codes': 'get_recovery_codes',
			regenerate? null: {'allow_regenerate': 'Y'},
			(function onGetParams(response)
			{
				this.drawRecoveryCodes(response.codes);

				var warningElement = document.body.querySelector('[data-role="otp-recovery-codes-warning"]');
				if (warningElement)
					warningElement.style.display = 'none';

				this.showPopup();
			}).bind(this)
		);
	};

	RecoveryCodes.prototype.onInitialize = function()
	{
		// Clear unwanted dialog buttons...
		this.popup.ClearButtons();

		this.codesContainer = this.container.querySelector('[data-role="recoverycodes-container"]');
		this.codesContainer.style.display = '';
		this.codesTemplate = this.container.querySelector('[data-role="recoverycode-template"]').cloneNode(true);

		BX.bind(
			this.container.querySelector('[data-role="print-codes"]'),
			'click',
			(function onPrint()
			{
				window.open(this._options['publicUrl'] + '&user=' + this.userId);
			}).bind(this)
		);

		BX.bind(
			this.container.querySelector('[data-role="save-codes"]'),
			'click',
			(function onDownload()
			{
				window.location.href = this._options['publicUrl'] + '&action=download' + '&user=' + this.userId
			}).bind(this)
		);

		BX.bind(
			this.container.querySelector('[data-role="regenerate-codes"]'),
			'click',
			this.onShow.bind(this, true)
		);
	};

	RecoveryCodes.prototype.drawRecoveryCodes = function(codes)
	{
		// Clean old codes
		[].forEach.call(
			this.codesContainer.querySelectorAll('[data-autoclear="yes"]'),
			function cleanElement(element)
			{
				BX.remove(element);
			},
			this
		);

		// Create new:-)
		[].forEach.call(
			codes,
			function drawCode(code)
			{
				var node = this.codesTemplate.cloneNode(true);
				if (!code.used)
				{
					BX.adjust(node, {
						'text': code.value,
						'attrs': {
							'className': 'active'
						}
					});
				}
				else
				{
					node.innerHTML = '';
					BX.adjust(node, {
						'html': '',
						'children': [
							BX.create('span', {
								'text': code.value
							}),
							BX.create('span', {
								'text': ' (' + formatDatetime(code.using_date) + ')'
							})
						],
						'attrs': {
							'className': 'used'
						}
					});
				}
				this.codesContainer.appendChild(node);
			},
			this
		);
	};

	/* -----------/Utils/--------------*/

	function mergeObjects(origin, add) {
		for (var p in add) {
			if (!add.hasOwnProperty(p))
				continue;

			if (add[p] && add[p].constructor === Object) {
				if (origin[p] && origin[p].constructor === Object) {
					origin[p] = mergeObjects(origin[p], add[p]);
				} else {
					origin[p] = clone(add[p]);
				}
			} else {
				origin[p] = add[p];
			}
		}
		return origin;
	}

	function clone(o) {
		return JSON.parse(JSON.stringify(o));
	}

	function formatDatetime(timestamp)
	{
		var format = null;
		if (!BX.isAmPmMode())
			format = [
				["tommorow", "tommorow, H:i"],
				["today", "today, H:i"],
				["yesterday", "yesterday, H:i"],
				["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"))]
			];
		else
			format = [
				["tommorow", "tommorow, g:i a"],
				["today", "today, g:i a"],
				["yesterday", "yesterday, g:i a"],
				["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"))]
			];
		return BX.date.format(format, parseInt(timestamp), BX.date.convertToUTC(new Date()));
	}

	return Manager;
}(BX));
