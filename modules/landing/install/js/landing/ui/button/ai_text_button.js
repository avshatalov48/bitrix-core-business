;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements interface for works with AI (text) button.
	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @param {string} id
	 * @param {object} options
	 * @constructor
	 */
	BX.Landing.UI.Button.AiText = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.sections = options.sections;
		this.onSelect = options.onSelect;
	};

	BX.Landing.UI.Button.AiText.getInstance = function(id, options)
	{
		if (true || !BX.Landing.UI.Button.AiText.instance)
		{
			BX.Landing.UI.Button.AiText.instance = new BX.Landing.UI.Button.AiText(id, options);
		}

		//BX.Landing.UI.Button.AiText.instance.sections = options.sections;
		//BX.Landing.UI.Button.AiText.instance.onSelect = options.onSelect;

		return BX.Landing.UI.Button.AiText.instance;
	};

	BX.Landing.UI.Button.AiText.prototype = {
		constructor: BX.Landing.UI.Button.AiText,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function()
		{
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

			const repository = BX.Landing.Main.getInstance()["options"]["blocks"];
			const sections = this.sections || [];
			const engineParameters = {};

			// retrieve engine's parameters from blocks' sections

			for (let i = 0, c = sections.length; i < c; i++)
			{
				const section = sections[i];
				if (repository[section] && repository[section]["meta"])
				{
					if (repository[section]["meta"]["ai_text_max_tokens"])
					{
						engineParameters["max_tokens"] = parseInt(repository[section]["meta"]["ai_text_max_tokens"]);
					}
				}
			}

			// picker instance

			if (!this.aiTextPicker)
			{
				const siteId = BX.Landing.Main.getInstance()["options"]["site_id"];
				const picker = top.BX.AI ? top.BX.AI.Picker : BX.AI.Picker;

				this.aiTextPicker = new picker({
					moduleId: "landing",
					contextId: "text_site_" + siteId,
					analyticLabel: "landing_text",
					history: true,
					onSelect: this.onSelect,
					onTariffRestriction: function() {
						BX.UI.InfoHelper.show("limit_sites_TextAssistant_AI");
					},
				});

				this.aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text)
			}

			this.aiTextPicker.setSelectCallback(this.onSelect);
			this.aiTextPicker.setEngineParameters(engineParameters);

			this.aiTextPicker.text();
		}
	};
})();
