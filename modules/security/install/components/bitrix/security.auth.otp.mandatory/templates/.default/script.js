BX.namespace('BX.Security');

BX.Security.AuthOtpMandatory = (function getAuthOtpMandatory(BX)
{
	"use strict";

	var Otp = function(options)
	{
		var defaults = {
			'actionUrl': location.href,
			'data': {
				'secret': null,
				'provisionUri': null
			},
			'ui': {
				'containerId': 'user-otp-container',
				'qrcode': {
					'width': 164,
					'height': 164,
					'colorDark': '#000000',
					'colorLight': '#ffffff'
				}
			}
		};

		options = options || {};
		this._options = mergeObjects(defaults, options);

		this._secret = this._options.data.secret;
		this._container = BX(this._options.ui.containerId);
		this._actionUrl = this._options.actionUrl;
		// ToDo: need options here
		this._completeCallback = BX.proxy(this.onComplete, this);
		this._errorContainer = null;
		this.initializeInterface(this._options.data);
	};

	Otp.prototype.initializeInterface = function(data)
	{
		this.drawQrCode(
			this._container.querySelector('[data-role="qr-code-block"]'),
			data.provisionUri,
			this._options.ui.qrcode
		);

		var checkCodes = this._container.querySelectorAll('input[data-role="check-code"]');
		BX.bind(
			document.querySelector('[data-role="check-button"]'),
			'click',
			BX.proxy(function proxyCheck() {
				this.onCheck(
					checkCodes[0],
					checkCodes[1] || null // Second check code may be absent for some OtpAlgorithm
				);
			}, this)
		);

		this._errorContainer = this._container.querySelector('[data-role="error-container"]');

		BX.bind(
			BX('connect-mobile-manual-input'),
			'click',
			function(event)
			{
				BX.PreventDefault(event);
				BX('connect-by-manual-input').style.display = '';
				BX('connect-by-qr').style.display = 'none';
			}
		);

		BX.bind(
			BX('connect-mobile-scan-qr'),
			'click',
			function(event)
			{
				BX.PreventDefault(event);
				BX('connect-by-manual-input').style.display = 'none';
				BX('connect-by-qr').style.display = '';
			}
		);
	};

	Otp.prototype.onCheck = function(elSync1, elSync2)
	{
		this.clearErrors();
		this.activate(elSync1.value, elSync2 ? elSync2.value: null);
	};

	Otp.prototype.activate = function(sync1, sync2)
	{
		var data = {
			'secret': this._secret,
			'sync1': sync1,
			'sync2': sync2
		};

		this.sendRequest('check_activate', data, this._completeCallback);
	};

	Otp.prototype.drawQrCode = function(elementId, provisionUri, options)
	{
		new QRCode(BX(elementId), {
			text: provisionUri,
			width: options.width,
			height: options.height,
			colorDark : options.colorDark,
			colorLight : options.colorLight,
			correctLevel : QRCode.CorrectLevel.H
		});
	};

	Otp.prototype.sendRequest = function(action, data, onSuccess, onFailure)
	{
		BX.addClass(document.querySelector('[data-role="check-button"]'), "wait");

		data = data || {};
		data.action = action || 'check';
		data.sessid = BX.bitrix_sessid();
		data = BX.ajax.prepareData(data);

		return BX.ajax({
			'method': 'POST',
			'dataType': 'json',
			'url': this._actionUrl,
			'data':  data,
			'onsuccess': BX.proxy(function proxySuccess(response)
			{
				return this.onRequestSuccess(onSuccess, response);
			}, this),
			'onfailure': BX.proxy(function proxySuccess(response)
			{
				return this.onRequestFailed(onFailure, response);
			}, this)
		});
	};

	Otp.prototype.onRequestSuccess = function(callback, response)
	{
		BX.removeClass(document.querySelector('[data-role="check-button"]'), "wait");

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

	Otp.prototype.onRequestFailed = function(callback, response)
	{
		BX.closeWait();

		if (!callback)
		{
			if (response['error'])
				this.showError(response['error']);
			else
				this.showError(BX.message('SECURITY_OTP_UNKNOWN_ERROR'));
		}
		else
		{
			callback(response);
		}
	};

	Otp.prototype.showError = function(errorMessage)
	{
		if (!this._errorContainer)
			return;

		var errorElement = BX.create('div', {
			'children': [
				BX.create('div', {
					'text': BX.message('SECURITY_OTP_ERROR_TITLE')
				}),
				BX.create('div', {
					'html': errorMessage
				})
			],
			attrs: {className: "bx-notice error"}
		});

		this._errorContainer.appendChild(errorElement);
	};

	Otp.prototype.clearErrors = function()
	{
		if (!this._errorContainer)
			return;

		BX.cleanNode(this._errorContainer);
	};

	Otp.prototype.onComplete = function()
	{
		BX.reload();
	};

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

	return Otp;
}(BX));

