;(function (window)
{
	BX.namespace('BX.Sender.SMS');
	if (BX.Sender.SMS.TextEditor)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * TextEditor.
	 *
	 */
	function TextEditor()
	{
	}

	TextEditor.prototype.initPanelToolsButtons = function() {
		if (this.isAITextAvailable)
		{
			const aiTextButton = this.context.querySelector('[data-bx-sms-panel-tools-button="ai-text"]');
			aiTextButton.addEventListener('click', () => {
				const aiTextPicker = new BX.AI.Picker({
					moduleId: 'sender',
					contextId: this.AITextContextId,
					analyticLabel: 'sender_sms_ai_text',
					history: true,
					onSelect: (info) => {
						const text = info.data;
						this.input.value = this.input.value + text;
					},
					onTariffRestriction: () => {
						// BX.UI.InfoHelper.show(`limit_sender_ai_image`);
					},
				});
				aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text);
				aiTextPicker.text();
			});
		}
	}


	TextEditor.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.mess = params.mess;
		this.AITextContextId = params.AITextContextId;
		this.isAITextAvailable = params.isAITextAvailable === 'Y';

		this.input = Helper.getNode('input', this.context);
		this.counter = Helper.getNode('counter', this.context);
		this.num = Helper.getNode('num', this.context);
		this.sms = Helper.getNode('sms', this.context);

		BX.bind(this.input, 'bxchange', this.onChange.bind(this));
		BX.bind(this.input, 'input', this.onChange.bind(this));

		this.initPanelToolsButtons();

		this.refresh();
	};
	TextEditor.prototype.onChange = function ()
	{
		this.refresh();
	};
	TextEditor.prototype.refresh = function ()
	{
		var count = this.input.value.length;
		var hasMultiBites = this.hasMultiBites();

		var numberCharsAtSms = hasMultiBites ? 70 : 160;
		var effectiveNumberCharsAtSms = numberCharsAtSms;
		if (count > numberCharsAtSms)
		{
			effectiveNumberCharsAtSms = hasMultiBites ? 67 : 153;
		}

		this.sms.textContent = Math.ceil(count / effectiveNumberCharsAtSms);
		this.num.textContent = numberCharsAtSms;
		this.counter.textContent = count;
	};
	TextEditor.prototype.hasMultiBites = function ()
	{
		var value = this.input.value;
		if (value.length === 0)
		{
			return false;
		}
		for (var i = 0; i < value.length; i++)
		{
			if (value.charCodeAt(i) > 128)
			{
				return true;
			}
		}

		return false;
	};

	BX.Sender.SMS.TextEditor = new TextEditor();

})(window);
