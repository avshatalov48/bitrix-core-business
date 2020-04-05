;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	BX.Landing.UI.Card.AddPageCard = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-add-page");
		this.cache = new BX.Cache.MemoryCache();
		this.onSaveHandler = data.onSave || (function() {});
		this.siteId = data.siteId;

		var icon = BX.create('span', {props: {className: 'landing-ui-card-add-page-icon'}});
		var text = BX.create('span', {
			props: {className: 'landing-ui-card-add-page-text'},
			text: BX.Landing.Loc.getMessage('LANDING_LINK_PLACEHOLDER_NEW_PAGE'),
		});
		var inner = BX.create('div', {
			props: {className: 'landing-ui-card-add-page-inner'},
			children: [
				icon,
				text
			]
		});

		this.body.appendChild(inner);

		BX.bind(this.layout, 'click', this.onLayoutClick.bind(this));
	};


	BX.Landing.UI.Card.AddPageCard.prototype = {
		constructor: BX.Landing.UI.Card.AddPageCard,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		/**
		 * @param {MouseEvent} event
		 */
		onLayoutClick: function(event)
		{
			event.preventDefault();

			BX.replace(this.layout, this.getFormLayout());

			var titleField = this.getTitleField();
			titleField.setValue('');

			setTimeout(function () {
				titleField.enableEdit();
				titleField.input.focus();
			});
		},

		getFormLayout: function()
		{
			return this.cache.remember('formLayout', function() {
				var saveButton = BX.create('span', {
					props: {className: 'ui-btn ui-btn-primary ui-btn-sm'},
					text: BX.Landing.Loc.getMessage('LANDING_LINK_NEW_PAGE_SAVE_BUTTON_LABEL'),
					events: {
						click: this.onSaveClick.bind(this)
					}
				});

				var cancelButton = BX.create('span', {
					props: {className: 'ui-btn ui-btn-link ui-btn-sm'},
					text: BX.Landing.Loc.getMessage('LANDING_LINK_NEW_PAGE_CANCEL_BUTTON_LABEL'),
					events: {
						click: this.onCancelClick.bind(this)
					}
				});

				var buttonsContainer = BX.create('div', {
					props: {className: 'landing-ui-card-add-page-form-buttons'},
					children: [
						saveButton,
						cancelButton
					]
				});

				return BX.create('div', {
					props: {className: 'landing-ui-card-add-page-form'},
					children: [
						this.getForm().layout,
						buttonsContainer
					]
				});
			}.bind(this));
		},

		/**
		 * @param {MouseEvent} event
		 */
		onSaveClick: function(event)
		{
			event.preventDefault();

			var backend = BX.Landing.Backend.getInstance();
			var title = this.getTitleField().getValue();
			var code = BX.translit(
				title,
				{
					change_case: 'L',
					replace_space: '-',
					replace_other: '',
				}
			);

			void backend
				.createPage({title: title, code: code, siteId: this.siteId})
				.then(function(result) {
					this.onSaveHandler(result);
					BX.replace(this.getFormLayout(), this.layout);
				}.bind(this));
		},

		/**
		 * @param {MouseEvent} event
		 */
		onCancelClick: function(event)
		{
			event.preventDefault();

			BX.replace(this.getFormLayout(), this.layout);
		},

		getTitleField: function()
		{
			return this.cache.remember('titleField', function() {
				return new BX.Landing.UI.Field.Text({
					title: BX.Landing.Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FIELD_PAGE_TITLE'),
					textOnly: true
				});
			});
		},

		getForm: function()
		{
			return this.cache.remember('form', function() {
				return new BX.Landing.UI.Form.BaseForm({
					title: BX.Landing.Loc.getMessage('LANDING_LINK_PLACEHOLDER_NEW_PAGE'),
					fields: [
						this.getTitleField()
					]
				});
			}.bind(this));
		}
	};
})();