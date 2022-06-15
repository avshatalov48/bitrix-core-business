;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var SidebarButton = BX.Landing.UI.Button.SidebarButton;

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

		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton("save_block_content", {
				text: BX.Landing.Loc.getMessage("BLOCK_SAVE"),
				onClick: BX.Type.isFunction(data.onSaveHandler) ? data.onSaveHandler : () => {},
				className: "landing-ui-button-content-save",
				attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_SLIDER_SAVE")}
			})
		);
		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton("cancel_block_content", {
				text: BX.Landing.Loc.getMessage("BLOCK_CANCEL"),
				onClick: BX.Type.isFunction(data.onCancelHandler) ? data.onCancelHandler : () => {},
				className: "landing-ui-button-content-cancel",
				attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_SLIDER_CANCEL")}
			})
		);
	};

	BX.Landing.UI.Panel.ContentEdit.showedPanel = null;


	BX.Landing.UI.Panel.ContentEdit.prototype = {
		constructor: BX.Landing.UI.Panel.ContentEdit,
		__proto__: BX.Landing.UI.Panel.Content.prototype,

		show: function()
		{
			BX.Landing.UI.Panel.ContentEdit.showedPanel = this;

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
			this.checkReadyToSave();

			if (form.title)
			{
				var formButton = new SidebarButton(form.code, {
					text: form.title,
					empty: !form.fields.length,
					onClick: function()
					{
						this.scrollTo(form.layout);
					}.bind(this)
				});

				this.sidebarButtons.add(formButton);
				this.sidebar.appendChild(formButton.layout);
			}

			form.fields.forEach(function(field) {
				if (field.title)
				{
					var fieldButton = new SidebarButton(field.selector, {
						text: field.title,
						child: true,
						onClick: function(event)
						{
							event.preventDefault();
							event.stopPropagation();
							this.scrollTo(field.layout);
						}.bind(this)
					});
					this.sidebarButtons.add(fieldButton);
					this.sidebar.appendChild(fieldButton.layout);
				}
			}, this);
		},

		replaceForm: function(oldForm, newForm)
		{
			this.forms.remove(oldForm);
			this.forms.add(newForm);
			this.checkReadyToSave();

			BX.replace(oldForm.getNode(), newForm.getNode());

			var formButton = this.sidebarButtons.get(oldForm.code);

			if (formButton)
			{
				var newFormButton = new SidebarButton(newForm.code, {
					text: newForm.title,
					empty: newForm.type === 'dynamicCards' || !newForm.fields.length,
					onClick: function()
					{
						this.scrollTo(newForm.layout);
					}.bind(this)
				});

				BX.replace(formButton.layout, newFormButton.layout);
				this.sidebarButtons.remove(formButton);
				this.sidebarButtons.add(newFormButton);
			}
		},

		compact: function(enable)
		{
			this.layout.classList[enable?"add":"remove"]("landing-ui-panel-content-edit-compact");
		},
	};
})();