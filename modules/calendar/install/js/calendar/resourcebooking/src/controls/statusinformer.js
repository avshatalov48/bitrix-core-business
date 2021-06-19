import {Loc, Dom, Tag, BookingUtil} from "../resourcebooking";

export class StatusInformer
{
	constructor(params)
	{
		this.DOM = {
			outerWrap: params.outerWrap
		};
		this.timezone = params.timezone;
		this.timezoneOffsetLabel = params.timezoneOffsetLabel;
		this.shown = false;
		this.built = false;
	}

	isShown()
	{
		return this.shown;
	}

	build()
	{
		this.DOM.wrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-result" style="display: none" 
></div>`);
		this.DOM.innerWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-result-inner"></div>`);
		this.DOM.labelWrap = this.DOM.innerWrap.appendChild(Dom.create("span", {props : { className : 'calendar-resbook-webform-block-result-text'}, text: Loc.getMessage('WEBF_RES_BOOKING_STATUS_LABEL')}));
		this.DOM.statusWrap = this.DOM.innerWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-result-value"></div>`);
		this.DOM.statusTimezone = this.DOM.innerWrap.appendChild(Dom.create("span", {props: {className: 'calendar-resbook-webform-block-result-timezone'}, text: this.timezoneOffsetLabel || '', style: {display: 'none'}}));
		this.built = true;
	}

	refresh(params)
	{
		if (!this.built)
		{
			this.build();
		}

		if (!this.isShown())
		{
			this.show();
		}

		if (params.dateFrom)
		{
			this.DOM.labelWrap.style.display = '';
			Dom.removeClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
			if (this.timezone)
			{
				this.DOM.statusTimezone.style.display = '';
			}
			Dom.adjust(this.DOM.statusWrap, {text: this.getStatusText(params)});
		}
		else if (!params.dateFrom && params.fullDay)
		{
			this.DOM.labelWrap.style.display = 'none';
			this.DOM.statusTimezone.style.display = 'none';
			Dom.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
			Dom.adjust(this.DOM.statusWrap, {text: Loc.getMessage('WEBF_RES_BOOKING_STATUS_DATE_IS_NOT_AVAILABLE')});
		}
		else
		{
			this.DOM.labelWrap.style.display = 'none';
			this.DOM.statusTimezone.style.display = 'none';
			Dom.removeClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
			Dom.adjust(this.DOM.statusWrap, {text: Loc.getMessage('WEBF_RES_BOOKING_STATUS_NO_TIME_SELECTED')});
		}
	}

	getStatusText(params)
	{
		let
			dateFrom = params.dateFrom,
			dateTo = new Date(dateFrom.getTime() + params.duration * 60 * 1000 + (params.fullDay ? -1 : 0)),
			text = '';

		if (params.fullDay)
		{
			if (BookingUtil.formatDate('Y-m-d', dateFrom.getTime() / 1000) === BookingUtil.formatDate('Y-m-d', dateTo.getTime() / 1000))
			{
				text = BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS'), dateFrom);
			}
			else
			{
				text = Loc.getMessage('WEBF_RES_DATE_FORMAT_FROM_TO')
					.replace('#DATE_FROM#', BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateFrom))
					.replace('#DATE_TO#', BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateTo));
			}
		}
		else
		{
			if (BookingUtil.formatDate('Y-m-d', dateFrom.getTime() / 1000) === BookingUtil.formatDate('Y-m-d', dateTo.getTime() / 1000))
			{
				text = BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS'), dateFrom)
					+ ' '
					+ Loc.getMessage('WEBF_RES_TIME_FORMAT_FROM_TO')
						.replace('#TIME_FROM#', BookingUtil.formatTime(dateFrom.getHours(), dateFrom.getMinutes()))
						.replace('#TIME_TO#', BookingUtil.formatTime(dateTo.getHours(), dateTo.getMinutes()));
			}
			else
			{
				text = Loc.getMessage('WEBF_RES_DATE_FORMAT_FROM_TO')
					.replace('#DATE_FROM#', BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateFrom) + ' '+ BookingUtil.formatTime(dateFrom.getHours(), dateFrom.getMinutes()))
					.replace('#DATE_TO#', BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateTo) + ' '+ BookingUtil.formatTime(dateTo.getHours(), dateTo.getMinutes()));
			}
		}

		return text;
	}

	hide()
	{
		if (this.built && this.shown)
		{
			this.DOM.wrap.style.display = 'none';
			this.shown = false;
		}
	}

	show()
	{
		if (this.built && !this.shown)
		{
			this.DOM.wrap.style.display = '';
			this.shown = true;
		}
	}

	setError(message)
	{
		if (this.DOM.labelWrap)
		{
			this.DOM.labelWrap.style.display = 'none';
		}
		Dom.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
		Dom.adjust(this.DOM.statusWrap, {text: message});
	}

	isErrorSet()
	{
		return this.shown && Dom.hasClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	}
}