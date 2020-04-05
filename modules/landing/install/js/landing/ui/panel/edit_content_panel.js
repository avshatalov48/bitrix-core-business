;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements interface for works with content edit panel
	 *
	 * @extends {BX.Landing.UI.Panel.Content}
	 *
	 * @param {string} id - Panel id
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Panel.ContentEdit = function(id, data)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-content-edit");
	};


	BX.Landing.UI.Panel.ContentEdit.prototype = {
		constructor: BX.Landing.UI.Panel.ContentEdit,
		__proto__: BX.Landing.UI.Panel.Content.prototype,

		show: function()
		{
			if (BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				BX.Landing.UI.Panel.StylePanel.getInstance().hide().then(function() {
					BX.Landing.UI.Panel.Content.prototype.show.call(this);
				}.bind(this));
			}
			else
			{
				BX.Landing.UI.Panel.Content.prototype.show.call(this);
			}
		},

		/**
		 * Appends form to panel body
		 * @param {BX.Landing.UI.Form.BaseForm} form
		 */
		appendForm: function(form)
		{
			this.forms.add(form);
			this.content.appendChild(form.getNode());

			if (form.title)
			{
				var formButton = new BX.Landing.UI.Button.SidebarButton("form_button", {
					text: form.title,
					onClick: function()
					{
						this.scrollTo(form.layout);
					}.bind(this)
				});

				this.sidebar.appendChild(formButton.layout);
			}

			form.fields.forEach(function(field) {
				if (field.title)
				{
					var fieldButton = new BX.Landing.UI.Button.SidebarButton("form_button", {
						text: field.title,
						child: true,
						onClick: function(event)
						{
							event.preventDefault();
							event.stopPropagation();
							this.scrollTo(field.layout);
						}.bind(this)
					});
					this.sidebar.appendChild(fieldButton.layout);
				}
			}, this);
		},

		compact: function(enable)
		{
			this.layout.classList[enable?"add":"remove"]("landing-ui-panel-content-edit-compact");
		}
	};
})();