;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var create = BX.Landing.Utils.create;
	var bind = BX.Landing.Utils.bind;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;

	var FORMAT_SECONDS = "s";
	var FORMAT_MILLISECONDS = "ms";

	/**
	 * Implements interface for works with date field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.Date = function(data)
	{
		data.textOnly = true;

		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		this.format = data.format === FORMAT_SECONDS ||
			data.format === FORMAT_MILLISECONDS ? data.format : FORMAT_SECONDS;
		this.time = data.time === true;
		this.hiddenInput = create("input", {props: {type: "hidden"}, value: data.content});
		bind(this.input, "click", this.onInputClick.bind(this));

		this.setValue(this.formatDateToValue(data.content));
	};


	BX.Landing.UI.Field.Date.prototype = {
		constructor: BX.Landing.UI.Field.Date,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		/**
		 * Handles input click event
		 */
		onInputClick: function()
		{
			var params = {
				node: this.input,
				field: this.hiddenInput,
				bTime: this.time,
				value: BX.date.format(this.getFormat(), new Date(this.formatDateToValue(this.hiddenInput.value) * 1000)),
				bHideTime: !this.time,
				callback_after: function(date) {
					this.setValue(date.getTime() / 1000);
				}.bind(this)
			};

			return BX.calendar(params);
		},

		getFormat: function()
		{
			return BX.date.convertBitrixFormat(BX.Landing.Loc.getMessage(this.time ? "FORMAT_DATETIME" : "FORMAT_DATE"));
		},

		reset: function()
		{
			this.setValue("");
		},

		/**
		 * @param {Integer|string} value - timestamp in seconds
		 */
		setValue: function(value)
		{
			if (value)
			{
				this.input.innerText = BX.date.format(this.getFormat(), new Date(value * 1000));
				this.hiddenInput.value = this.formatValue(value);
				this.onValueChangeHandler(this);

				var event = new BX.Event.BaseEvent({
					data: {value: this.getValue()},
					compatData: [this.getValue()],
				});
				this.emit('change', event);
			}
		},

		/**
		 * Format seconds
		 * @param value
		 * @return {integer}
		 */
		formatValue: function(value)
		{
			switch (this.format)
			{
				case FORMAT_SECONDS:
					return value;
				case FORMAT_MILLISECONDS:
					return value * 1000;
				default:
					break;
			}
		},

		/**
		 * To seconds
		 * @param formattedValue
		 * @return {*}
		 */
		formatDateToValue: function(formattedValue)
		{
			switch (this.format)
			{
				case FORMAT_SECONDS:
					return formattedValue;
				case FORMAT_MILLISECONDS:
					return formattedValue / 1000;
				default:
					break;
			}
		},

		getValue: function()
		{
			return this.formatValue(this.formatDateToValue(this.hiddenInput.value));
		},

		clone: function(fieldData)
		{
			var data = Object.assign(
				{},
				fieldData || this.data,
				{content: (new Date()).getTime()}
			);
			var field = new this.constructor(data);

			if (this.type)
			{
				field.type = this.type;
			}

			return field;
		}
	}
})();