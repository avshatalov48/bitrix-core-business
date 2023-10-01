;(function ()
{
	BX.namespace('BX.Sender.Im');
	if (BX.Sender.Im.Message)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * TextEditor.
	 *
	 */
	function Message()
	{
	}

	Message.prototype.initFields = function ()
	{
		Helper.tag.init(this.context.parentElement, this.input);
	};
	Message.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.mess = params.mess;
		this.AITextContextId = params.AITextContextId;
		this.isAITextAvailable = params.isAITextAvailable === 'Y';

		this.input = Helper.getNode('input', this.context);
		this.counter = Helper.getNode('counter', this.context);

		BX.bind(this.input, 'bxchange', this.onChange.bind(this));
		BX.bind(this.input, 'input', this.onChange.bind(this));

		this.initPanelToolsButtons();

		this.refresh();
		this.initFields();
	};
	Message.prototype.initPanelToolsButtons = function() {
		if (this.isAITextAvailable)
		{
			const aiTextButton = this.context.querySelector('[data-bx-im-panel-tools-button="ai-text"]');
			aiTextButton.addEventListener('click', (event) => {
				const aiTextPicker = new BX.AI.Picker({
					moduleId: 'sender',
					contextId: this.AITextContextId,
					analyticLabel: 'sender_im_ai_text',
					history: true,
					onSelect: (info) => {
						const text = info.data;
						this.input.value = this.input.value + text;
					},
					onTariffRestriction: () => {
						// BX.UI.InfoHelper.show(`limit_sonet_ai_image`);
					},
				});
				aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text);
				aiTextPicker.text();
			});
		}
	};
	Message.prototype.onChange = function ()
	{
		this.refresh();
	};
	Message.prototype.refresh = function ()
	{
		this.counter.textContent = this.input.value.length;
	};

	BX.Sender.Im.Message = new Message();

})(window);
