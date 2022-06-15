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
		data.placeholder = BX.Landing.Loc.getMessage('LANDING_EMBED_FIELD_PLACEHOLDER');
		data.description = "<span class='landing-ui-anchor-preview'>"+BX.Landing.Loc.getMessage('LANDING_EMBED_FIELD_DESCRIPTION')+"</span>";

		BX.Landing.UI.Field.Text.apply(this, arguments);

		// Input event handler already set in parent TextField

		this.hiddenInput = create("input", {
			props: {type: "hidden", value: content.src || this.input.innerText}
		});

		this.error = BX.Landing.UI.Field.BaseField.createDescription(
			BX.Landing.Loc.getMessage("LANDING_EMBED_ERROR_WRONG_SOURCE_TEXT_2")
		);

		BX.Dom.addClass(this.error, 'landing-ui-error');
		BX.Dom.style(this.description, 'margin-bottom', '0px');

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
			var value = this.getValue();
			this.adjustForm(true);
			this.onInputHandler(value);
			this.onValueChangeHandler(this);

			var event = new BX.Event.BaseEvent({
				data: {value: value},
				compatData: [value],
			});
			this.emit('change', event);
		},

		isEmbedUrl: function(value)
		{
			return BX.Landing.Utils.Matchers.youtube.test(value)
				|| BX.Landing.Utils.Matchers.vimeo.test(value)
				|| BX.Landing.Utils.Matchers.vk.test(value)
				|| BX.Landing.Utils.Matchers.rutube.test(value)
				|| BX.Landing.Utils.Matchers.vine.test(value)
				|| BX.Landing.Utils.Matchers.facebookVideos.test(value);
		},

		getValue: function()
		{
			return {
				src: this.mediaService ? this.mediaService.getEmbedURL() : this.input.innerText,
				preview: this.mediaService ? this.mediaService.getEmbedPreview() : '',
				source: this.input.innerText
			};
		},

		adjustForm: function(skipParams)
		{
			var value = String(this.input.innerText).trim();

			this.hideError();

			if (this.isEmbedUrl(value))
			{
				var ServiceFactory = new BX.Landing.MediaService.Factory();

				if (this.mediaService && this.mediaService.form)
				{
					remove(this.mediaService.form.layout);
				}

				this.mediaService = ServiceFactory.create(
					value,
					!skipParams ? getQueryParam(this.hiddenInput.value) : {}
				);

				if (this.mediaService)
				{
					var form = this.mediaService.getSettingsForm();

					if (form)
					{
						this.layout.appendChild(form.layout);
					}

					if (!this.mediaService.isDataLoaded)
					{
						this.readyToSave = false;
						BX.addCustomEvent(this.mediaService, 'onDataLoaded', () =>
						{
							this.readyToSave = true;
							this.emit('onChangeReadyToSave');
						});
					}
					this.emit('onChangeReadyToSave');
				}
			}
			else
			{
				if (this.mediaService)
				{
					remove(this.mediaService.form.layout);
				}

				if (BX.Type.isStringFilled(value))
				{
					this.showError();
				}
			}
		},

		showError: function()
		{
			BX.Dom.append(this.error, this.layout);
			BX.Dom.style(this.description, 'margin-bottom', null);
		},

		hideError: function()
		{
			BX.Dom.remove(this.error);
			BX.Dom.style(this.description, 'margin-bottom', '0px');
		}
	}
})();