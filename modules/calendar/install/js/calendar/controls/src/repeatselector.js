import { Util } from 'calendar.util';
import { Type, Dom, Event, Loc } from 'main.core';
import { DatePicker } from 'ui.date-picker';
import { Menu, MenuItem } from 'main.popup';

export class RepeatSelector
{
	#untilPicker: DatePicker;
	#rruleTypeMenu: Menu;
	#rruleCountMenu: Menu;

	constructor(params)
	{
		let formElements = params.rruleType.form.elements;

		this.uid = params.uid;
		this.getDate = params.getDate;
		this.DOM = {
			formElements,
			wrap: params.wrap,
			rruleType: params.rruleType,
			rruleCount: params.rruleCount,
			interval: formElements['EVENT_RRULE[INTERVAL]'],
			rruleEndsOn: {
				never: formElements['rrule_endson'][0],
				count: formElements['rrule_endson'][1],
				until: formElements['rrule_endson'][2],
			},
			count: formElements['EVENT_RRULE[COUNT]'],
			until: formElements['EVENT_RRULE[UNTIL]'],
		};
		this.viewMode = false;

		this.create();
		this.initDatePicker();
	}

	create()
	{
		Event.bind(this.DOM.rruleType, 'click', this.#showRruleTypeMenu);

		Event.bind(this.DOM.rruleCount, 'click', this.#showRruleCountMenu);

		Event.bind(this.DOM.until, 'click', () => {
			this.showUntilPicker();
			this.DOM.rruleEndsOn.until.checked = true;
		});

		Event.bind(this.DOM.count, 'click', () => {
			this.DOM.rruleEndsOn.count.checked = true;
		});
	}

	initDatePicker()
	{
		this.#untilPicker ??= new DatePicker({
			targetNode: this.DOM.until,
			events: {
				onSelect: this.#onUntilSelected,
			},
		});
	}

	#onUntilSelected = (event) => {
		const { date } = event.getData();
		this.DOM.until.value = Util.formatDate(date.getTime());
	};

	showUntilPicker()
	{
		this.#untilPicker.show();
	}

	#showRruleTypeMenu = () => {
		this.#rruleTypeMenu ??= new Menu({
			id: `${this.uid}-calendar-rrule-type-menu`,
			bindElement: this.DOM.rruleType,
			closeByEsc: true,
			items: this.#getRruleTypeMenuItems(),
		});

		this.#rruleTypeMenu.show();
	};

	#getRruleTypeMenuItems(): Array
	{
		const result = [];

		const items = {
			NONE: Loc.getMessage('EC_CONTROL_REPEAT_NONE'),
			DAILY: Loc.getMessage('EC_CONTROL_REPEAT_DAILY'),
			WEEKLY: Loc.getMessage('EC_CONTROL_REPEAT_WEEKLY'),
			MONTHLY: Loc.getMessage('EC_CONTROL_REPEAT_MONTHLY'),
			YEARLY: Loc.getMessage('EC_CONTROL_REPEAT_YEARLY'),
		};

		Object.entries(items).forEach(([key, value]) => {
			result.push(new MenuItem({
				text: value,
				onclick: () => this.#selectRruleType(key, value),
			}));
		});

		return result;
	}

	#selectRruleType = (key, value) => {
		this.#rruleTypeMenu.close();
		this.DOM.rruleType.value = value;
		this.DOM.rruleType.dataset.value = key;
		this.changeType(key);
	};

	#showRruleCountMenu = () => {
		this.#rruleCountMenu ??= new Menu({
			id: `${this.uid}-calendar-rrule-count-menu`,
			bindElement: this.DOM.rruleCount,
			closeByEsc: true,
			items: this.#getRruleCountMenuItems(),
			maxHeight: 300,
		});

		this.#rruleCountMenu.show();
	};

	#getRruleCountMenuItems(): Array
	{
		const result = [];

		for (let i = 1; i <= 36; i++)
		{
			result.push(new MenuItem({
				text: String(i),
				onclick: () => this.#selectRruleCount(i),
			}));
		}

		return result;
	}

	#selectRruleCount = (item) => {
		this.#rruleCountMenu.close();
		this.DOM.rruleCount.value = item;
	};

	changeType(type)
	{
		this.DOM.rruleType.dataset.value = type ? type.toUpperCase() : 'NONE';
		this.DOM.rruleType.value = this.getTypeName();
		let rruleType = this.DOM.rruleType.dataset.value.toLowerCase();
		this.DOM.wrap.className = 'calendar-rrule-type-' + rruleType;

		if (rruleType === 'weekly' && BX.type.isFunction(this.getDate))
		{
			let fromDate = this.getDate();
			if (BX.type.isDate(fromDate))
			{
				let day = Util.getWeekDayByInd(fromDate.getDay());
				this.DOM.formElements['EVENT_RRULE[BYDAY][]'].forEach(function(input)
				{
					if (input.checked && this.previousDay === input.value && this.previousDay !== day)
					{
						input.checked = false;
					}
					else
					{
						input.checked = input.checked || input.value === day;
					}
				}, this);

				this.previousDay = day;
			}
		}
	}

	setValue(rrule = {})
	{
		if (Type.isNil(rrule))
		{
			rrule = {};
		}

		this.changeType(rrule.FREQ);
		this.DOM.interval.value = rrule.INTERVAL || 1;
		if (rrule.COUNT)
		{
			this.DOM.rruleEndsOn.count.checked = 'checked';
			this.DOM.count.value = rrule.COUNT;
		}
		else if(rrule['~UNTIL'])
		{
			this.DOM.rruleEndsOn.until.checked = 'checked';
			this.DOM.until.value = rrule['~UNTIL'];
		}
		else
		{
			this.DOM.rruleEndsOn.never.checked = 'checked';
		}

		if (BX.type.isPlainObject(rrule.BYDAY))
		{
			this.DOM.formElements['EVENT_RRULE[BYDAY][]'].forEach(function(input)
			{
				input.checked = rrule.BYDAY.hasOwnProperty(input.value);
			}, this);
		}
	}

	getType()
	{
		return this.DOM.rruleType.dataset.value.toLowerCase();
	}

	getTypeName(): string
	{
		switch (this.DOM.rruleType.dataset.value)
		{
			case 'NONE': {
				return Loc.getMessage('EC_CONTROL_REPEAT_NONE');
			}

			case 'DAILY': {
				return Loc.getMessage('EC_CONTROL_REPEAT_DAILY');
			}

			case 'WEEKLY': {
				return Loc.getMessage('EC_CONTROL_REPEAT_WEEKLY');
			}

			case 'MONTHLY': {
				return Loc.getMessage('EC_CONTROL_REPEAT_MONTHLY');
			}

			case 'YEARLY': {
				return Loc.getMessage('EC_CONTROL_REPEAT_YEARLY');
			}

			default: {
				return '';
			}
		}
	}

	setViewMode(description: string)
	{
		if (!Type.isStringFilled(description))
		{
			description = this.DOM.rruleType.value;
		}

		Dom.clean(this.DOM.wrap);
		this.DOM.wrap.innerText = description.toLowerCase();
		Dom.addClass(this.DOM.wrap, 'calendar-field calendar-repeat-selector-readonly');
	}
}
