;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var bind = BX.Landing.Utils.bind;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var getQueryParam = BX.Landing.Utils.getQueryParams;
	var remove = BX.Landing.Utils.remove;
	var create = BX.Landing.Utils.create;

	/**
	 * Implements interface for works with text field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.Embed = function(data)
	{
		data.textOnly = true;
		var content = data.content;
		data.content = content.source || content.src;

		BX.Landing.UI.Field.Text.apply(this, arguments);

		// Make event handlers
		this.onInputInput = this.onInputInput.bind(this);

		// Bind on field events
		bind(this.input, "input", this.onInputInput);

		this.hiddenInput = create("input", {
			props: {type: "hidden", value: content.src || this.input.innerText}
		});

		this.adjustForm();
	};


	BX.Landing.UI.Field.Embed.prototype = {
		constructor: BX.Landing.UI.Field.Embed,
		__proto__: BX.Landing.UI.Field.Text.prototype,
		/**
		 * Handles input event on input field
		 */
		onInputInput: function()
		{
			this.adjustForm(true);
			this.onInputHandler(this.getValue());
			this.onValueChangeHandler(this);

			fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
		},

		getValue: function()
		{
			return {
				src: this.mediaService ? this.mediaService.getEmbedURL() : this.input.innerText,
				source: this.input.innerText
			};
		},

		adjustForm: function(skipParams)
		{
			var ServiceFactory = new BX.Landing.MediaService.Factory();

			if (this.mediaService && this.mediaService.form)
			{
				remove(this.mediaService.form.layout);
			}

			this.mediaService = ServiceFactory.create(
				this.input.innerText,
				!skipParams ? getQueryParam(this.hiddenInput.value) : {}
			);

			if (this.mediaService)
			{
				var form = this.mediaService.getSettingsForm();

				if (form)
				{
					this.layout.appendChild(form.layout);
				}
			}
		}
	}
})();