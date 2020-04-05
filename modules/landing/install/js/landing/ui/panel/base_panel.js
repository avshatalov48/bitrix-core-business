;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements base interface for works with panel
	 *
	 * @param {?string} [id]
	 * @constructor
	 */
	BX.Landing.UI.Panel.BasePanel = function(id)
	{
		this.id = BX.type.isNotEmptyString(id) ? id : BX.Landing.UI.Panel.BasePanel.makeId();
		this.layout = BX.Landing.UI.Panel.BasePanel.createLayout(this.id);
		this.classShow = "landing-ui-show";
		this.classHide = "landing-ui-hide";
		this.forms = new BX.Landing.UI.Collection.FormCollection();
	};


	/**
	 * Makes panel id
	 * @return {string}
	 */
	BX.Landing.UI.Panel.BasePanel.makeId = function()
	{
		return "landing_ui_panel_" + (+new Date());
	};


	/**
	 * Creates panel layout
	 *
	 * @param {string} id - Panel id
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.BasePanel.createLayout = function(id)
	{
		return BX.create("div", {
			props: {className: "landing-ui-panel landing-ui-hide"},
			attrs: {"data-id": id}
		});
	};


	BX.Landing.UI.Panel.BasePanel.prototype = {
		/**
		 * Shows panel
		 */
		show: function()
		{
			if (!this.isShown())
			{
				return BX.Landing.Utils.Show(this.layout);
			}
		},


		/**
		 * Hides panel
		 */
		hide: function()
		{
			if (this.isShown())
			{
				return BX.Landing.Utils.Hide(this.layout);
			}
		},


		/**
		 * Checks that panel is shown
		 * @return {boolean}
		 */
		isShown: function()
		{
			return (
				(this.layout.classList.contains(this.classShow) &&
				!this.layout.classList.contains(this.classHide)) ||
				(!this.layout.classList.contains(this.classShow) &&
				!this.layout.classList.contains(this.classHide))
			);
		},


		/**
		 * Sets layout content
		 * @param {?string|HTMLElement|HTMLElement[]} content
		 */
		setContent: function(content)
		{
			this.layout.innerHTML = "";

			if (BX.type.isNotEmptyString(content))
			{
				this.layout.innerHTML = content;
			}

			if (BX.type.isDomNode(content))
			{
				this.appendContent(content);
			}

			if (BX.type.isArray(content))
			{
				content.forEach(this.appendContent, this);
			}
		},


		/**
		 * Appends element
		 * @param {HTMLElement} content
		 */
		appendContent: function(content)
		{
			if (BX.type.isDomNode(content))
			{
				this.layout.appendChild(content);
			}
		},


		/**
		 * Prepends content
		 * @param {HTMLElement} content
		 */
		prependContent: function(content)
		{
			if (BX.type.isDomNode(content))
			{
				BX.prepend(content, this.layout);
			}
		},


		/**
		 * Removes this panel
		 */
		remove: function()
		{
			BX.remove(this.layout);
		},


		/**
		 * Appends form to panel body
		 * @param {BX.Landing.UI.Form.BaseForm} form
		 */
		appendForm: function(form)
		{
			this.layout.appendChild(form.getNode());
		},


		/**
		 * Clears body content
		 */
		clear: function()
		{
			this.layout.innerHTML = "";
		}
	};
})();