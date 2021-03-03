import {Dom, Type} from "calendar.resourcebooking";

export class FormFieldTunnerAbstract {
	constructor()
	{
		this.label = '';
		this.formLabel = '';
		this.displayed = false;
		this.valuePopup = null;
		this.statePopup = null;
		this.displayCheckboxDisabled = false;
		this.DOM = {};
	}

	build(params)
	{
		this.updateConfig(params.params);

		this.DOM.fieldWrap = Dom.create("div", {props: {className: 'calendar-resbook-webform-settings-popup-item'}});

		this.DOM.labelWrap = this.DOM.fieldWrap.appendChild(Dom.create("div", {
			props: {className: 'calendar-resbook-webform-settings-popup-field'}
		}));
		this.DOM.labelNode = this.DOM.labelWrap.appendChild(Dom.create("span", {
			props: {className: 'calendar-resbook-webform-settings-popup-field-title'}, text: this.getLabel()
		}));

		// Label in form
		this.DOM.formTitleWrap = this.DOM.labelWrap.appendChild(Dom.create("span", {
			props: {
				className: 'calendar-resbook-webform-settings-popup-field-subtitle' + (this.isDisplayed() ? ' show' : '')
			}
		}));
		this.DOM.formTitleLabel = this.DOM.formTitleWrap.appendChild(Dom.create("span", {
			props: {className: 'calendar-resbook-webform-settings-popup-field-subtitle-text'},
			text: this.getFormLabel(),
			events: {click: this.enableFormTitleEditMode.bind(this)}
		}));
		this.DOM.formTitleEditIcon = this.DOM.formTitleWrap.appendChild(Dom.create("span", {
			props: {className: 'calendar-resbook-webform-settings-popup-field-edit'},
			events: {click: this.enableFormTitleEditMode.bind(this)}
		}));

		// Display checkbox
		this.DOM.checkboxNode = this.DOM.fieldWrap.appendChild(Dom.create("div", {props: {className: 'calendar-resbook-webform-settings-popup-checkbox-container'}})).appendChild(Dom.create("input", {
			attrs: {
				type: "checkbox", value: 'Y', checked: this.isDisplayed(), disabled: this.displayCheckboxDisabled
			}, events: {
				click: this.checkDisplayMode.bind(this)
			}
		}));

		// State popup
		this.buildStatePopup({
			wrap: this.DOM.fieldWrap, config: params.config || {}
		});

		// Value popup
		this.buildValuePopup({
			wrap: this.DOM.fieldWrap,
			config: params.config || {}
		});

		if (Type.isFunction(params.changeSettingsCallback))
		{
			this.changeSettingsCallback = params.changeSettingsCallback;
		}

		params.wrap.appendChild(this.DOM.fieldWrap);
	}

	destroy()
	{
		if (this.valuePopup && Type.isFunction(this.valuePopup.closePopup))
		{
			this.valuePopup.closePopup();
		}
		if (this.statePopup && Type.isFunction(this.statePopup.closePopup))
		{
			this.statePopup.closePopup();
		}
	}

	updateConfig(params = {})
	{
		this.setFormLabel(params.label || this.formLabel);
		if (params.show)
		{
			this.displayed = params.show !== 'N';
		}
	}

	buildStatePopup(params)
	{
	}

	buildValuePopup(params)
	{
	}

	getLabel()
	{
		return this.label;
	}

	getFormLabel()
	{
		return this.formLabel;
	}

	setFormLabel(formLabel)
	{
		this.formLabel = formLabel || '';
	}

	isDisplayed()
	{
		return this.displayed;
	}

	checkDisplayMode()
	{
		this.displayed = !!this.DOM.checkboxNode.checked;
		if (this.displayed)
		{
			this.displayInForm();
		}
		else
		{
			this.hideInForm();
		}
	}

	displayInForm()
	{
		Dom.addClass(this.DOM.formTitleWrap, 'show');
		this.triggerChangeRefresh();
	}

	hideInForm ()
	{
		Dom.removeClass(this.DOM.formTitleWrap, 'show');
		this.triggerChangeRefresh();
	}

	enableFormTitleEditMode()
	{
		if (!this.DOM.formTitleInputNode)
		{
			this.DOM.formTitleInputNode = this.DOM.formTitleWrap.appendChild(Dom.create("input", {
				attrs: {
					type: 'text',
					className: 'calendar-resbook-webform-settings-popup-field-subtitle-text'
				},
				events: {blur: this.finishFormTitleEditMode.bind(this)}
			}));
		}

		this.DOM.formTitleInputNode.value = this.getFormLabel();
		this.DOM.formTitleInputNode.style.display = '';
		this.DOM.formTitleLabel.style.display = 'none';
		this.DOM.formTitleEditIcon.style.display = 'none';
		this.DOM.formTitleInputNode.focus();
	}

	finishFormTitleEditMode()
	{
		this.setFormLabel(this.DOM.formTitleInputNode.value);
		Dom.adjust(this.DOM.formTitleLabel, {text: this.getFormLabel()});
		this.DOM.formTitleLabel.style.display = '';
		this.DOM.formTitleEditIcon.style.display = '';
		this.DOM.formTitleInputNode.style.display = 'none';
		this.triggerChangeRefresh();
	}

	getSettingsValue()
	{

	}

	triggerChangeRefresh()
	{
		setTimeout(function(){BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');}.bind(this), 50);
	}
}




