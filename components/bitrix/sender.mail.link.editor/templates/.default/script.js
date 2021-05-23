;(function ()
{
	BX.namespace('BX.Sender.Mail');
	if (BX.Sender.Mail.LinkEditor)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * LinkEditor.
	 *
	 */
	function LinkEditor()
	{
	}
	LinkEditor.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.defaultValue = params.defaultValue;
		this.placeholders = params.placeholders || [];
		this.mess = params.mess;

		this.input = Helper.getNode('input', this.context);
		this.button = Helper.getNode('button', this.context);
		this.popupContent = Helper.getNode('popup-content', this.context);

		BX.bind(this.button, 'click', this.onClick.bind(this));

		this.initPlaceholders();
		if (params.useDefault)
		{
			this.setDefaultParameters();
			this.runPlaceholderWatcher();
		}
	};
	LinkEditor.prototype.setDefaultParameters = function ()
	{
		this.setParameters(this.getDefaultParameters());
	};
	LinkEditor.prototype.initPlaceholders = function ()
	{
		this.placeholders = this.placeholders.filter(function (placeholder) {
			if (!placeholder.inputName)
			{
				return false;
			}
			placeholder.input = document.body.querySelector('input[name="' + placeholder.inputName + '"]');
			return !!placeholder.input;
		}, this);
	};
	LinkEditor.prototype.onPlaceholderWatcher = function ()
	{
		if (this.placeholderWatchersStopped)
		{
			return;
		}

		this.canStopPlaceholderWatchers = false;
		this.setDefaultParameters();
		this.canStopPlaceholderWatchers = true;
	};
	LinkEditor.prototype.runPlaceholderWatcher = function ()
	{
		this.placeholderWatchersStopped = false;
		this.placeholders.forEach(function (placeholder) {

			BX.bind(placeholder.input, 'bxchange', this.onPlaceholderWatcher.bind(this));
			BX.bind(placeholder.input, 'input', this.onPlaceholderWatcher.bind(this));

		}, this);

		BX.bind(this.input, 'input', this.stopPlaceholderWatcher.bind(this));
		BX.bind(this.input, 'bxchange', this.stopPlaceholderWatcher.bind(this));
	};
	LinkEditor.prototype.stopPlaceholderWatcher = function ()
	{
		if (!this.canStopPlaceholderWatchers)
		{
			return;
		}

		this.placeholderWatchersStopped = true;
	};
	LinkEditor.prototype.onClick = function ()
	{
		this.showPopup();
	};
	LinkEditor.prototype.showPopup = function ()
	{
		if (!this.popup)
		{
			this.popup = BX.PopupWindowManager.create(
				'sender-mail-link-editor-utm',
				this.button,
				{
					content: this.popupContent,
					// titleBar: this.mess.title,
					autoHide: true,
					lightShadow: false,
					closeByEsc: true,
					// closeIcon: true,
					contentColor: 'white',
					angle: true,
					buttons: [
						new BX.PopupWindowButton({
							text: this.mess.accept,
							className: "popup-window-button-accept",
							events: {
								click: this.onPopupApply.bind(this)
							}
						}),
						new BX.PopupWindowButtonLink({
							text: this.mess.cancel,
							events: {
								click: function() {
									this.popupWindow.close();
								}
							}
						})
					]
				}
			);
		}

		if (this.popup.isShown())
		{
			return;
		}

		this.loadUtmParameters();
		Helper.changeDisplay(this.popupContent, true);
		this.popup.show();
	};
	LinkEditor.prototype.onPopupApply = function ()
	{
		this.saveUtmParameters();
		this.popup.close();
	};
	LinkEditor.prototype.getDefaultParameters = function ()
	{
		var replaceData = {};
		this.placeholders.forEach(function (placeholder) {
			if (!placeholder.input.value)
			{
				return;
			}

			replaceData[placeholder.code] = BX.translit(placeholder.input.value);
		}, this);

		var value = Helper.replace(this.defaultValue, replaceData, true);
		return this.parseParameters(value);
	};
	LinkEditor.prototype.loadUtmParameters = function ()
	{
		var parameters = this.getParameters();
		var utmParameters = {};
		var hasUtmParameters = false;
		this.getUtmNames().forEach(function (name) {
			utmParameters[name] = parameters[name] || '';
			if (!hasUtmParameters)
			{
				hasUtmParameters = utmParameters[name].length > 0;
			}
		}, this);

		if (!hasUtmParameters)
		{
			utmParameters = this.getDefaultParameters();
		}

		this.getUtmNames().forEach(function (name) {
			var value = utmParameters[name] || '';
			var input = Helper.getNode(name, this.popupContent);
			input ? (input.value = value) : null;
		}, this);
	};
	LinkEditor.prototype.saveUtmParameters = function ()
	{
		var parameters = {};
		this.getUtmNames().forEach(function (name) {
			var input = Helper.getNode(name, this.popupContent);
			parameters[name] = input ? BX.translit(input.value) : '';
		}, this);

		this.setParameters(parameters);
	};
	LinkEditor.prototype.getUtmNames = function ()
	{
		return ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
	};
	LinkEditor.prototype.setParameters = function (parameters)
	{
		parameters = parameters || {};
		parameters = BX.mergeEx(this.getParameters(), parameters);

		var value = [];
		for (var name in parameters)
		{
			if (!parameters.hasOwnProperty(name))
			{
				continue;
			}

			if (parameters[name] === '' || parameters[name] === null)
			{
				continue;
			}

			value.push(name + '=' + encodeURIComponent(parameters[name]));
		}

		this.input.value = value.join('&');
	};
	LinkEditor.prototype.getParameters = function ()
	{
		return this.parseParameters(this.input.value);
	};
	LinkEditor.prototype.parseParameters = function (value)
	{
		var parameters = {};
		value = value.trim();
		if (value.substring(0, 1) === '?')
		{
			value = value.substring(1);
		}
		if (!value)
		{
			return parameters;
		}

		value.split('&').reduce(this.reduceParameter.bind(this), parameters);
		return parameters;
	};
	LinkEditor.prototype.reduceParameter = function (obj, param)
	{
		var list = param.split('=');
		if (!list[0])
		{
			return obj;
		}

		var name = decodeURIComponent(list[0].trim());
		if (!name)
		{
			return obj;
		}

		var value = list[1].trim();
		if (!/%[a-zA-Z]{3,50}%/.test(value))
		{
			value = decodeURIComponent(value);
		}
		if (BX.util.in_array(name, this.getUtmNames()))
		{
			value = BX.translit(value);
		}


		obj[name] = value;
		return obj;
	};


	BX.Sender.Mail.LinkEditor = new LinkEditor();

})(window);