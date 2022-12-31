"use strict";
import {Loc, Dom} from 'main.core';
import {DateTimeControl, TimeSelector} from 'calendar.controls';

export class SliderDateTimeControl extends DateTimeControl
{
	create()
	{
		this.DOM.dateTimeWrap = this.DOM.outerContent.querySelector(`#${this.UID}_datetime_container`);
		this.DOM.fromDate = this.DOM.outerContent.querySelector(`#${this.UID}_date_from`);
		this.DOM.toDate = this.DOM.outerContent.querySelector(`#${this.UID}_date_to`);
		this.DOM.fromTime = this.DOM.outerContent.querySelector(`#${this.UID}_time_from`);
		this.DOM.toTime = this.DOM.outerContent.querySelector(`#${this.UID}_time_to`);

		this.fromTimeControl = new TimeSelector({
			input: this.DOM.fromTime,
			onChangeCallback: this.handleTimeFromChange.bind(this)
		});

		this.toTimeControl = new TimeSelector({
			input: this.DOM.toTime,
			onChangeCallback: this.handleTimeToChange.bind(this)
		});

		this.DOM.fullDay = this.DOM.outerContent.querySelector(`#${this.UID}_date_full_day`);
		this.DOM.defTimezoneWrap = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_default_wrap`);
		this.DOM.defTimezone = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_default`);

		this.DOM.fromTz = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_from`);
		this.DOM.toTz = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_to`);
		this.DOM.tzButton = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_btn`);
		this.DOM.tzOuterCont = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_wrap`);
		this.DOM.tzCont = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_inner_wrap`);

		this.DOM.outerContent.querySelector(`#${this.UID}_timezone_hint`).title = Loc.getMessage('EC_EVENT_TZ_HINT');
		this.DOM.outerContent.querySelector(`#${this.UID}_timezone_default_hint`).title = Loc.getMessage('EC_EVENT_TZ_DEF_HINT');

		this.prepareModel();
		this.bindEventHandlers();

		if (BX.isAmPmMode())
		{
			this.DOM.fromTime.style.minWidth = '8em';
			this.DOM.toTime.style.minWidth = '8em';
		}
		else
		{
			this.DOM.fromTime.style.minWidth = '6em';
			this.DOM.toTime.style.minWidth = '6em';
		}
	}

	prepareModel()
	{
		Dom.adjust(this.DOM.fromDate, {props: {autocomplete: 'off'}});
		Dom.adjust(this.DOM.toDate, {props: {autocomplete: 'off'}});
		Dom.adjust(this.DOM.fromTime, {props: {autocomplete: 'off'}});
		Dom.adjust(this.DOM.toTime, {props: {autocomplete: 'off'}});
	}
}