;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	BX.Landing.UI.Card.AddYourFirstBlock = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-add-first-block");
		this.subheader = BX.create("div", {props: {className: "landing-ui-card-add-first-block-subheader"}});
		this.layout.insertBefore(this.subheader, this.body);
	};

	BX.Landing.UI.Card.AddYourFirstBlock.prototype = {
		constructor: BX.Landing.UI.Card.AddYourFirstBlock,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		/**
		 * @inheritDoc
		 */
		show: function()
		{
			BX.Landing.Utils.Show(this.layout);
		},


		/**
		 * @inheritDoc
		 */
		hide: function()
		{
			BX.Landing.Utils.Hide(this.layout);
		}
	};
})();