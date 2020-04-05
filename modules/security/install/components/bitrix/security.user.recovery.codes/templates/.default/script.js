BX.namespace('BX.Security');

BX.Security.UserRecoveryCodes = (function getUserOtp(BX)
{
	"use strict";

	var Otp = function(options)
	{
		var defaults = {
			'actionUrl': location.href,
			'ui': {
				'containerId': 'recovery-codes-container'
			}
		};

		options = options || {};
		this._options = mergeObjects(defaults, options);
		this._container = BX(this._options.ui.containerId);
		this.codesTemplate = null;
		this.codesContainer = null;
		this.initializeInterface();
	};

	Otp.prototype.initializeInterface = function(data)
	{
		var regenerateButtons = this._container.querySelectorAll('[data-role="regenerate-button"]');
		var codeTemplate = this._container.querySelector('[data-role="code-template"]');
		if (codeTemplate) {
			this.codesContainer = codeTemplate.parentNode;
			this.codesTemplate = codeTemplate.cloneNode(true);
		}

		[].forEach.call(
			regenerateButtons,
			function bindRegeneration(item)
			{
				BX.bind(item, 'click', this.onRegenerate.bind(this));
			},
			this
		);
	};

	Otp.prototype.onRegenerate = function(event)
	{
		if (event)
			event.preventDefault();

		this.sendRequest(
			'regenerate',
			null,
			(function onGetParams(response)
			{
				this.drawRecoveryCodes(response.codes);
			}).bind(this)
		);
	};

	Otp.prototype.drawRecoveryCodes = function(codes)
	{
		// Clean old codes
		[].forEach.call(
			this._container.querySelectorAll('[data-autoclear="yes"]'),
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
				node.style.display = '';
				if (code.USED == 'Y' && node.getAttribute('data-used-class'))
					BX.addClass(node, node.getAttribute('data-used-class'));

				var childs = node.querySelectorAll("*");
				[].forEach.call(
					childs,
					function initCodeTemplate(element) {
						if (code.USED != 'Y' && element.getAttribute('data-visible-on-used') == 'yes')
						{
							BX.remove(element);
							return;
						}

						var role = element.getAttribute('data-code-template-role');
						switch (role) {
							case 'code':
								element.innerHTML = BX.util.htmlspecialchars(code.VALUE);
								break;
							case 'using-date':
								if (code.USING_DATE)
									element.innerHTML = BX.util.htmlspecialchars(code.USING_DATE);
								break;
							default:
								break;
						}
					},
					this
				);
				this.codesContainer.appendChild(node);
			},
			this
		);
	};

	Otp.prototype.sendRequest = function(action, data, onSuccess, onFailure)
	{
		data = data || {};
		data.action = action || 'check';
		data.sessid = BX.bitrix_sessid();
		data = BX.ajax.prepareData(data);

		return BX.ajax({
			'method': 'POST',
			'dataType': 'json',
			'url': this._options.actionUrl,
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
		location.href = this._successfulUrl;
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

