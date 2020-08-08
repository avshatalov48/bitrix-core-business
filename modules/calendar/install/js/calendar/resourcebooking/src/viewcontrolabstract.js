import {Loc, Dom, Tag} from "./resourcebooking";

export class ViewControlAbstract
{
	constructor(params)
	{
		if (new.target === ViewControlAbstract)
		{
			throw new TypeError("Cannot construct Abstract instances directly");
		}

		this.name = null;
		this.classNames = {
			wrap: params.wrapClassName || 'calendar-resbook-webform-block',
			innerWrap: 'calendar-resbook-webform-block-inner',
			title: 'calendar-resbook-webform-block-title',
			field: 'calendar-resbook-webform-block-field'
		};

		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: null,
			dataWrap: null,
			innerWrap: null,
			labelWrap: null
		};
		this.data = params.data;
		this.shown = false;
	}

	isDisplayed()
	{
		return this.data.show !== 'N';
	}

	isShown()
	{
		return this.shown;
	}

	display()
	{
		this.DOM.wrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props : { className : this.classNames.wrap}}));

		this.DOM.dataWrap = this.DOM.wrap.appendChild(Tag.render`<div data-bx-resource-data-wrap="Y"></div>`);

		if (this.isDisplayed())
		{
			this.show({animation: false});
		}
	}

	refresh(data)
	{
		this.refreshLabel(data);
		this.data = data;

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
			}
			else
			{
				this.hide({animation: true});
			}
		}
		this.data = data;
	}

	setDataConfig()
	{
		return true;
	}

	refreshLabel(data)
	{
		if (this.data.label !== data.label)
		{
			Dom.adjust(this.DOM.labelWrap, {text: data.label});
		}
	}

	show()
	{
		if (this.DOM.innerWrap)
		{
			this.hide();
		}

		this.DOM.innerWrap = this.DOM.wrap.appendChild(Dom.create("div", {props : { className : this.classNames.innerWrap}}));

		if (this.data.label || this.label)
		{
			this.DOM.labelWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {props : { className : this.classNames.title}, text: this.data.label || this.label}));
		}
		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {props : { className : this.classNames.field}}));
		this.displayControl();
		this.shown = true;
	}

	hide()
	{
		Dom.remove(this.DOM.innerWrap);
		this.DOM.innerWrap = null;
		this.shown = false;
	}

	displayControl()
	{
	}

	showWarning(errorMessage)
	{
		if (this.shown && this.DOM.wrap && this.DOM.innerWrap)
		{
			Dom.addClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
			this.displayErrorText(errorMessage || Loc.getMessage('WEBF_RES_BOOKING_REQUIRED_WARNING'));
		}
	}

	hideWarning()
	{
		if (this.DOM.wrap)
		{
			Dom.removeClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
			if (this.DOM.errorTextWrap)
			{
				Dom.remove(this.DOM.errorTextWrap);
			}
		}
	}

	displayErrorText(errorMessage)
	{
		if (this.DOM.errorTextWrap)
		{
			Dom.remove(this.DOM.errorTextWrap);
		}
		this.DOM.errorTextWrap = this.DOM.innerWrap.appendChild(Dom.create("span", {props : { className : 'calendar-resbook-webform-block-error-text'}, text: errorMessage}));
	}
}


