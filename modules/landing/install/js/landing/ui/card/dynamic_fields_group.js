;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	BX.Landing.UI.Card.DynamicFieldsGroup = function(options)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-dynamic-fields-group");
		this.fields = new BX.Landing.Collection.BaseCollection();

		options.items.forEach(function(field) {
			var item = BX.Landing.UI.Card.FieldGroup.createItem();
			item.appendChild(field.layout);
			this.body.appendChild(item);
			this.fields.add(field);
		}, this);
	};

	BX.Landing.UI.Card.DynamicFieldsGroup.prototype = {
		constructor: BX.Landing.UI.Card.DynamicFieldsGroup,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype
	};
})();