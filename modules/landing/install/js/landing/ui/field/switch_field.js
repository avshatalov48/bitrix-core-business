;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var isFunction = BX.Landing.Utils.isFunction;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var bind = BX.Landing.Utils.bind;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var toggleClass = BX.Landing.Utils.toggleClass;
	var create = BX.Landing.Utils.create;
	var append = BX.Landing.Utils.append;
	var random = BX.Landing.Utils.random;
	var join = BX.Landing.Utils.join;

	/**
	 * Implements interface for works with text field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.Switch = function(data)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-switch");

		this.value = data.value;
		this.id = join("switch_", random());

		// Make input event external handler
		this.onValueChangeHandler = isFunction(data.onValueChange) ? data.onValueChange : (function() {});

		this.label = create("label", {
			props: {className: "landing-ui-field-switch-label"},
			attrs: {"for": this.id},
			html: this.title
		});

		this.checkbox = create("input", {
			props: {className: "landing-ui-field-switch-checkbox"},
			attrs: {type: "checkbox", id: this.id}
		});

		this.slider = create("div", {
			props: {className: "landing-ui-field-switch-slider"}
		});

		append(this.checkbox, this.label);
		append(this.slider, this.label);
		append(this.label, this.input);

		this.setValue(this.value);

		bind(this.checkbox, "change", this.onChange.bind(this));
	};


	BX.Landing.UI.Field.Switch.prototype = {
		constructor: BX.Landing.UI.Field.Switch,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		onChange: function()
		{
			this.onValueChangeHandler();
		},

		setValue: function(value)
		{
			if (!isBoolean(value))
			{
				value = value === "true";
			}

			this.checkbox.checked = value;
		},

		getValue: function()
		{
			return !!this.checkbox.checked;
		}
	};
})();