;(function (w) {

	if (!w.BX)
	{
		w.BX = {};
	}
	if (BX.AjaxAction)
	{
		return;
	}

	var manager = function (controllerUri)
	{
		this.controllerUri = controllerUri;
	};
	manager.prototype.mess = function (code)
	{
		return BX.message('MAIN_AJAX_ACTION_' + (code || '').toUpperCase()) || '';
	};
	manager.prototype.getRequestingUri = function (action, sendData, uri)
	{
		sendData = sendData || {};
		sendData.action = action;
		sendData.sessid = BX.bitrix_sessid();

		return BX.util.add_url_param(uri || this.controllerUri, sendData);
	};
	manager.prototype.confirmDelete = function (name, callbackApply, callbackRefuse)
	{
		this.confirm(
			this.mess('confirm_delete').replace('%name%', name || ''),
			callbackApply,
			this.mess('delete'),
			callbackRefuse
		);
	};
	manager.prototype.confirm = function (text, callbackApply, textButtonApply, callbackRefuse)
	{
		textButtonApply = textButtonApply || this.mess('apply');
		if (!this.confirmPopup)
		{
			this.confirmPopup = BX.PopupWindowManager.create(
				'main_ajax_action_confirm',
				null,
				{
					autoHide: true,
					lightShadow: true,
					closeByEsc: true,
					overlay: {backgroundColor: 'black', opacity: 500}
				}
			);

			this.confirmPopup.setButtons([
				new BX.PopupWindowButton({
					text: textButtonApply,
					className: "popup-window-button-accept",
					events: {click: this.onConfirmPopup.bind(this, callbackApply)}
				}),
				new BX.PopupWindowButton({
					text: this.mess('cancel'),
					events: {click: this.onConfirmPopup.bind(this, callbackRefuse)}
				})
			]);
		}

		var className = 'main-ajax-action-text main-ajax-action-confirm';
		text = text || this.mess('confirm');
		text = BX.util.htmlspecialchars(text);
		this.confirmPopup.setContent('<span class="' + className + '">' + text + '</span>');
		this.confirmPopup.show();
	};
	manager.prototype.onConfirmPopup = function (callback)
	{
		this.confirmPopup.close();
		if (!callback)
		{
			return;
		}
		if (BX.type.isFunction(callback))
		{
			callback.apply(this);
		}
		else if (BX.type.isPlainObject(callback))
		{
			this.request(callback);
		}
	};
	manager.prototype.showResult = function (data)
	{
		if (!this.resultPopup)
		{
			this.resultPopup = BX.PopupWindowManager.create(
				'main_ajax_action_result',
				null,
				{
					autoHide: true,
					lightShadow: true,
					closeByEsc: true,
					overlay: {backgroundColor: 'black', opacity: 500}
				}
			);

			this.resultPopup.setButtons([
				new BX.PopupWindowButton({
					text: this.mess('close'),
					events: {click: this.resultPopup.close.bind(this.resultPopup)}
				})
			]);
		}

		var className = 'main-ajax-action-text main-ajax-action-result-' + (data.error ? 'error' : 'success');
		var text = data.text || (data.error ? this.mess('error') : this.mess('success'));
		text = BX.util.htmlspecialchars(text);
		this.resultPopup.setContent('<span class="' + className + '">' + text + '</span>');
		this.resultPopup.show();
	};
	manager.prototype.requestHtml = function (config)
	{
		config.method = config.method || 'GET';
		config.dataType = 'html';
		config.processData = config.processData || false;

		this.request(config);
	};
	manager.prototype.request = function (config)
	{
		var action = config.action;
		var sendData = config.data || {};
		var urlParams = config.urlParams || {};
		var callbackSuccess = config.onsuccess || null;
		var callbackFailure = config.onfailure || null;
		var showErrors = (config.showErrors !== undefined) ? !!config.showErrors : true;
		var showSuccess = config.showSuccess || false;
		var uri = config.url || this.controllerUri;
		var requestMethod = config.method || 'POST';
		if (requestMethod === 'GET')
		{
			uri = this.getRequestingUri(action, sendData, uri);
		}
		else
		{
			uri = BX.util.add_url_param(uri, BX.merge({'action': action}, urlParams));
		}
		var responseDataType = config.dataType || 'json';
		var processResponseData = (config.processData !== undefined) ? !!config.processData : true;

		sendData.action = action;
		sendData.sessid = BX.bitrix_sessid();
		BX.ajax({
			url: uri,
			method: requestMethod,
			data: sendData,
			timeout: config.timeout || 30,
			dataType: responseDataType,
			processData: processResponseData,
			onsuccess: this.onResponse.bind(this, showSuccess, showErrors, callbackSuccess, callbackFailure),
			onfailure: this.onResponseFailure.bind(this, showErrors, callbackFailure)
		});
	};
	manager.prototype.onResponse = function (showSuccess, showErrors, callbackSuccess, callbackFailure, data)
	{
		data = data || {};
		if(data.error)
		{
			this.onResponseFailure(showErrors, callbackFailure, data);
		}
		else
		{
			if (showSuccess)
			{
				this.showResult(data);
			}

			if (BX.type.isFunction(callbackSuccess))
			{
				callbackSuccess.apply(this, [data]);
			}
		}
	};
	manager.prototype.onResponseFailure = function (showErrors, callback, data)
	{
		data = BX.type.isPlainObject(data) ? data : {};
		data.error = true;
		data.text = data.text || this.mess('error');

		if (showErrors)
		{
			this.showResult(data);
		}

		if (BX.type.isFunction(callback))
		{
			callback.apply(this, [data]);
		}
	};

	BX.AjaxAction = manager;

})(window);