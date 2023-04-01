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
		data.placeholder = BX.Landing.Loc.getMessage('LANDING_EMBED_NOT_BG_FIELD_DESCRIPTION');
		data.description =
			data.description
			|| "<span class='landing-ui-anchor-preview'>"+BX.Landing.Loc.getMessage('LANDING_EMBED_NOT_BG_FIELD_DESCRIPTION')+"</span>";

		BX.Landing.UI.Field.Text.apply(this, arguments);

		// Input event handler already set in parent TextField

		this.hiddenInput = create("input", {
			props: {type: "hidden", value: content.src || this.input.innerText}
		});

		this.error = BX.create('div', {props: {className: 'landing-ui-field-error'}});
		BX.Dom.append(this.error, this.layout);

		BX.Dom.style(this.description, 'margin-bottom', '0px');

		this.adjustForm();
	};

	BX.Landing.UI.Field.Embed.isBgVideo = false;


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
				|| BX.Landing.Utils.Matchers.rutube.test(value)
				|| BX.Landing.Utils.Matchers.vk.test(value)
				|| BX.Landing.Utils.Matchers.vine.test(value)
				|| BX.Landing.Utils.Matchers.facebookVideos.test(value);
		},

		getValue: function()
		{
			return {
				src: this.mediaService ? this.mediaService.getEmbedURL() : this.input.innerText,
				preview: this.mediaService ? this.mediaService.getEmbedPreview() : '',
				source: this.input.innerText,
				ratio:
					(this.mediaService && this.mediaService.isVertical)
						? BX.Landing.Block.Node.Embed.DEFAULT_RATIO_V
						: BX.Landing.Block.Node.Embed.DEFAULT_RATIO_H
				,
			};
		},

		adjustForm: function(skipParams)
		{
			var value = String(this.input.innerText).trim();

			this.hideError();

			if (this.isEmbedUrl(value))
			{
				if (this.mediaService && this.mediaService.form)
				{
					remove(this.mediaService.form.layout);
				}

				const ServiceFactory = new BX.Landing.MediaService.Factory();
				this.mediaService = ServiceFactory.create(
					value,
					!skipParams ? getQueryParam(this.hiddenInput.value) : {}
				);

				this.mediaService.setBgVideoMode(this.constructor.isBgVideo);

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
						BX.addCustomEvent(this.mediaService, 'onDataLoadError', event =>
						{
							this.readyToSave = false;
							this.showError(event.message);
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
					this.showError(BX.Landing.Loc.getMessage("LANDING_EMBED_ERROR_WRONG_SOURCE_TEXT_ALL"));
				}
			}
		},

		showError: function(message)
		{
			BX.Dom.append(BX.Landing.UI.Field.BaseField.createError(message), this.error);
			BX.Dom.style(this.description, 'margin-bottom', null);
		},

		hideError: function()
		{
			BX.Dom.clean(this.error);
			BX.Dom.style(this.description, 'margin-bottom', '0px');
		}
	}
})();