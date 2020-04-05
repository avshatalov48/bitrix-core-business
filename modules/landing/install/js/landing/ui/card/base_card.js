;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * Implements base interface for works with cards
	 * @param {?object} [data]
	 * @constructor
	 */
	BX.Landing.UI.Card.BaseCard = function(data)
	{
		data = !!data && typeof data === "object" ? data : {};
		this.id = typeof data.id === "string" ? data.id : "";
		this.hidden = typeof data.hidden === "boolean" ? data.hidden : false;
		this.layout = BX.Landing.UI.Card.BaseCard.createLayout();
		this.header = BX.Landing.UI.Card.BaseCard.createHeader();
		this.body = BX.Landing.UI.Card.BaseCard.createBody();
		this.layout.appendChild(this.header);
		this.layout.appendChild(this.body);
		this.header.innerText = typeof data.title === "string" ? data.title : "";
		this.layout.hidden = this.hidden;
		this.onClickHandler = typeof data.onClick === "function" ? data.onClick : (function() {});

		if (typeof data.className === "string")
		{
			this.layout.classList.add(data.className);
		}

		this.layout.addEventListener("click", this.onClick.bind(this));
	};


	/**
	 * Creates card layout
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Card.BaseCard.createLayout = function()
	{
		return BX.create("div", {props: {className: "landing-ui-card"}});
	};


	/**
	 * Creates card header layout
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Card.BaseCard.createHeader = function()
	{
		return BX.create("div", {props: {className: "landing-ui-card-header"}});
	};


	/**
	 * Creates card body layout
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Card.BaseCard.createBody = function()
	{
		return BX.create("div", {props: {className: "landing-ui-card-body"}});
	};


	BX.Landing.UI.Card.BaseCard.prototype = {
		onClick: function()
		{
			this.onClickHandler(this);
		},


		/**
		 * Shows card
		 */
		show: function()
		{
			this.layout.hidden = false;
		},


		/**
		 * Hides card
		 */
		hide: function()
		{
			this.layout.hidden = true;
		}
	};
})();
