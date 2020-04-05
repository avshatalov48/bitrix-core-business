;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");

	var append = BX.Landing.Utils.append;
	var clone = BX.Landing.Utils.clone;

	/**
	 * Implements base interface for works with forms
	 *
	 * @param {{
	 * 		[title]: ?string,
	 * 		[description]: string,
	 * 		[type]: string,
	 * 		[label]: string
	 * }} [data]
	 *
	 * @constructor
	 */
	BX.Landing.UI.Form.BaseForm = function(data)
	{
		this.data = BX.type.isPlainObject(data) ? data : {};
		this.id = "id" in this.data ? this.data.id : "";
		this.selector = "selector" in this.data ? this.data.selector : "";
		this.title = "title" in this.data ? this.data.title : "";
		this.label = "label" in this.data ? this.data.label : "";
		this.type = "type" in this.data ? this.data.type : "content";
		this.descriptionText = "description" in this.data ? this.data.description : "";
		this.layout = BX.Landing.UI.Form.BaseForm.createLayout();
		this.fields = new BX.Landing.Collection.BaseCollection();
		this.description = BX.Landing.UI.Form.BaseForm.createDescription();
		this.header = BX.Landing.UI.Form.BaseForm.createHeader();
		this.body = BX.Landing.UI.Form.BaseForm.createBody();
		this.footer = BX.Landing.UI.Form.BaseForm.createFooter();
		this.header.innerHTML = this.title;
		this.layout.appendChild(this.header);

		if (this.descriptionText)
		{
			this.description.innerHTML = this.descriptionText;
			this.layout.appendChild(this.description);
		}

		this.layout.appendChild(this.body);
		this.layout.appendChild(this.footer);

	};


	/**
	 * Creates form layout
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Form.BaseForm.createLayout = function()
	{
		return BX.create("div", {props: {className: "landing-ui-form"}});
	};


	/**
	 * Creates form header layout
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Form.BaseForm.createHeader = function()
	{
		return BX.create("div", {props: {className: "landing-ui-form-header"}});
	};

	/**
	 * Creates form description layout
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Form.BaseForm.createDescription = function()
	{
		return BX.create("div", {props: {className: "landing-ui-form-description"}});
	};

	/**
	 * Creates form body layout
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Form.BaseForm.createBody = function()
	{
		return BX.create("div", {props: {className: "landing-ui-form-body"}});
	};


	/**
	 * Creates form footer layout
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Form.BaseForm.createFooter = function()
	{
		return BX.create("div", {props: {className: "landing-ui-form-footer"}});
	};


	BX.Landing.UI.Form.BaseForm.prototype = {
		addField: function(field)
		{
			this.fields.add(field);
			this.body.appendChild(field.getNode());
		},

		getNode: function()
		{
			return this.layout;
		},

		addCard: function(card)
		{
			append(card.layout, this.body);
			card.fields.forEach(function(field) {
				this.fields.add(field);
			}, this)
		},

		clone: function()
		{
			var instance = new this.constructor(clone(this.data));

			this.fields.forEach(function(field) {
				instance.addField(field.clone());
			});

			return instance;
		},

		serialize: function()
		{
			var result = {};

			this.fields.forEach(function(field) {
				result[field.selector] = field.getValue();
			});

			return result;
		}
	};
})();