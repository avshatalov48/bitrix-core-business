;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");


	/**
	 * Implements interface for works with style form
	 *
	 * @extends {BX.Landing.UI.Form.BaseForm}
	 *
	 * @param {{[title]: ?string}} data
	 *
	 * @constructor
	 */
	BX.Landing.UI.Form.StyleForm = function(data)
	{
		BX.Landing.UI.Form.BaseForm.apply(this, arguments);
		this.layout.classList.add("landing-ui-form-style");
		this.iframe = "iframe" in data ? data.iframe : null;
		this.header.addEventListener("mouseenter", this.onHeaderEnter.bind(this));
		this.header.addEventListener("mouseleave", this.onHeaderLeave.bind(this));
		this.header.addEventListener("click", this.onHeaderClick.bind(this));
		this.node = "node" in data ? data.node : null;
		this.selector = "selector" in data ? data.selector : null;

		if (this.type === "attrs")
		{
			this.header.classList.add("landing-ui-static");
		}

		if (this.iframe)
		{
			this.onFrameLoad();
		}
	};


	BX.Landing.UI.Form.StyleForm.prototype = {
		constructor: BX.Landing.UI.Form.StyleForm,
		__proto__: BX.Landing.UI.Form.BaseForm.prototype,

		onFrameLoad: function ()
		{
			if (!this.node)
			{
				this.node = [].slice.call(this.iframe.document.querySelectorAll(this.selector));
			}
		},

		onHeaderEnter: function()
		{
			BX.Landing.UI.Highlight.getInstance().show(this.node);
		},

		onHeaderLeave: function()
		{
			BX.Landing.UI.Highlight.getInstance().hide();
		},

		onHeaderClick: function(event)
		{
			event.preventDefault();
		},

		addField: function(field)
		{
			if (field)
			{
				this.fields.add(field);
				this.body.appendChild(field.layout);
			}
		}
	};
})();