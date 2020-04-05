;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * Implements interface for works with block preview card
	 *
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Card.FieldGroup = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.items = data.items;
		this.layout.classList.add("landing-ui-card-field-group");
		this.init();
	};


	BX.Landing.UI.Card.FieldGroup.createItem = function()
	{
		return BX.create("div", {props: {className: "landing-ui-card-field-group-item"}});
	};


	BX.Landing.UI.Card.FieldGroup.prototype = {
		constructor: BX.Landing.UI.Card.FieldGroup,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		init: function()
		{
			this.items.forEach(function(field) {
				var item = BX.Landing.UI.Card.FieldGroup.createItem();
				item.appendChild(field.layout);
				this.body.appendChild(item);
			}, this);
		}
	};
})();