;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var append = BX.Landing.Utils.append;
	var findParent = BX.Landing.Utils.findParent;
	var offsetLeft = BX.Landing.Utils.offsetLeft;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var rect = BX.Landing.Utils.rect;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var bind = BX.Landing.Utils.bind;

	BX.Landing.UI.Field.Filter = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-filter");

		this.input.innerHTML = options.html;

		requestAnimationFrame(function() {
			BX.ajax.processScripts(BX.processHTML(options.html).SCRIPT, undefined, function() {
				this.filter = BX.Main.filterManager.getById(options.filterId);
				this.value = isPlainObject(options.value) ? options.value : {};
				append(
					this.filter.getPopup().popupContainer,
					findParent(this.layout, {className: "landing-ui-panel-content-body"})
				);
				onCustomEvent(this.filter.getPopup(), "onPopupShow", this.adjustPopupPosition.bind(this));
				bind(findParent(this.layout, {className: "landing-ui-panel-content-body-content"}), "scroll", this.adjustPopupPosition.bind(this));
				this.filter.getApi().setFields(this.value);
				this.filter.getApi().apply();
			}.bind(this));
		}.bind(this));
	};

	BX.Landing.UI.Field.Filter.prototype = {
		constructor: BX.Landing.UI.Field.Filter,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		getValue: function()
		{
			var values = this.filter.getFilterFieldsValues();

			if (isPlainObject(values))
			{
				if ("FIND" in values)
				{
					delete values["FIND"];
				}

				return values
			}

			return {};
		},

		/**
		 * @todo refactoring
		 */
		adjustPopupPosition: function()
		{
			if (this.filter.getPopup())
			{
				var inputRect = this.input.getBoundingClientRect();
				var yOffset = 6;

				requestAnimationFrame(function() {
					this.filter.getPopup().popupContainer.style.top = inputRect.top + inputRect.height + yOffset + "px";
					this.filter.getPopup().popupContainer.style.left = inputRect.left + "px";
				}.bind(this));
			}
		}
	};
})();