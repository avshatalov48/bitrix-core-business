"use strict";
import {Loc, Dom, Tag, Type} from 'main.core';
import {DateTimeControl, TimeSelector} from 'calendar.controls';
import {Util} from "calendar.util";

export class SliderDateTimeControl extends DateTimeControl
{
	create()
	{
		this.DOM.dateTimeWrap = this.DOM.outerContent.querySelector(`#${this.UID}_datetime_container`);
		this.DOM.editor = this.DOM.outerContent.querySelector(`#${this.UID}_datetime_editor`);
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

	setReadonly(timezoneHint)
	{
		const value = this.getValue();
		let result = '';
		const dateFrom = Util.formatDateUsable(value.from, true, true);
		const dateTo = Util.formatDateUsable(value.to, true, true);
		const timeFrom = this.DOM.fromTime.value;
		const timeTo = this.DOM.toTime.value;
		result += dateFrom + ', ';

		if (value.fullDay)
		{
			if (dateFrom === dateTo)
			{
				result += Loc.getMessage('EC_ALL_DAY');
			}
			else
			{
				result += ' - '
			}
		}
		else
		{
			result += timeFrom + ' - ' + timeTo;
		}

		if (!value.fullDay && dateFrom !== dateTo)
		{
			result += ', ' + dateTo;
		}

		let timezoneIcon = '';
		if (Type.isStringFilled(timezoneHint))
		{
			timezoneIcon = Tag.render`
				<div class="calendar-date-selector-readonly-timezone" title="${timezoneHint}">
					<div class="calendar-date-selector-readonly-timezone-icon"></div>
				</div>
			`;
		}

		Dom.style(this.DOM.editor, 'display', 'none');
		const readonlyElement = Tag.render`
			<div class="calendar-options-item-column-right">
				<div class="calendar-field calendar-date-selector-readonly">
					${result}
					${timezoneIcon}
				</div>
			</div>
		`;
		Dom.append(readonlyElement, this.DOM.dateTimeWrap);
	}
}