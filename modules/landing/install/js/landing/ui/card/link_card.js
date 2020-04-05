;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Card.Link = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-link");

		if (!!data && typeof data === "object" && "child" in data && data.child)
		{
			this.layout.classList.add("landing-ui-card-link-child");
		}

		this.body.appendChild((new BX.Landing.UI.Button.BaseButton(data.button.id.toString(), {
			text: data.button.text,
			onClick: data.button.onChange
		})).layout);
	};

	BX.Landing.UI.Card.Link.prototype = {
		constructor: BX.Landing.UI.Card.Link,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype
	};
})();