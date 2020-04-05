;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	BX.Landing.UI.Card.IconPreview = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-icon");
		this.body.innerHTML = "<span class=\""+data.iconClassName+"\"></span>";
	};

	BX.Landing.UI.Card.IconPreview.prototype = {
		constructor: BX.Landing.UI.Card.IconPreview,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype
	};
})();