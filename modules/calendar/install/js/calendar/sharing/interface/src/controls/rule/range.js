import { Dom, Loc, Tag, Type, Event } from 'main.core';
import Weekday from './weekday';
import { MenuManager, Popup } from 'main.popup';
import { Util } from 'calendar.util';

type RangeOptions = {
	readOnly: boolean,
	getSlotSize: void,
	from: number,
	to: number,
	weekdays: Array<number>,
	workDays: Array<number>,
	weekStart: number,
	ruleUpdated: void,
	addRange: void,
	removeRange: void,
	showReadOnlyPopup: void,
	show: boolean,
	deletable: boolean,
}

export default class Range
{
	constructor(options: RangeOptions)
	{
		this.layout = {
			wrap: null,
			weekdaysSelect: null,
			startSelect: null,
			endSelect: null,
		};

		this.readOnly = options.readOnly;
		this.weekStart = options.weekStart;
		this.workDays = options.workDays;

		this.getSlotSize = options.getSlotSize;
		this.rule = {
			from: options.from,
			to: options.to,
			weekdays: this.getSortedWeekdays(options.weekdays),
		};

		this.ruleUpdated = Type.isFunction(options.ruleUpdated) ? options.ruleUpdated : () => {};

		this.addRange = Type.isFunction(options.addRange) ? options.addRange : () => {};

		this.removeRange = Type.isFunction(options.removeRange) ? options.removeRange : () => {};

		this.showReadOnlyPopup = Type.isFunction(options.showReadOnlyPopup) ? options.showReadOnlyPopup : () => {};

		this.show = options.show;
		this.deletable = false;
	}

	getRule(): any
	{
		return this.rule;
	}

	settingPopupShown(): boolean
	{
		const weekdaysPopupShown = Dom.hasClass(this.layout.weekdaysSelect, '--active');
		const startPopupShown = Dom.hasClass(this.layout.fromTimeSelect, '--active');
		const endPopupShown = Dom.hasClass(this.layout.toTimeSelect, '--active');

		return weekdaysPopupShown || startPopupShown || endPopupShown;
	}

	getWrap(): HTMLElement
	{
		return this.layout.wrap;
	}

	disableAnimation()
	{
		this.show = false;
	}

	render(): HTMLElement
	{
		this.layout.wrap = Tag.render`
			<div class="calendar-sharing__settings-range">
				${this.renderWeekdaysSelect()}
				<div class="calendar-sharing__settings-time-interval">
					${this.renderTimeFromSelect()}
					<div class="calendar-sharing__settings-dash"></div>
					${this.renderTimeToSelect()}
				</div>
				${this.renderButton()}
			</div>
		`;

		if (this.show)
		{
			Dom.addClass(this.layout.wrap, '--animate-show');
			setTimeout(() => Dom.removeClass(this.layout.wrap, '--animate-show'), 300);
		}

		return this.layout.wrap;
	}

	renderButton(): HTMLElement
	{
		this.layout.button = this.getButton();

		return this.layout.button;
	}

	update()
	{
		const maxFrom = 24 * 60 - this.getSlotSize();
		if (this.rule.from > maxFrom)
		{
			this.rule.from = maxFrom;
			if (this.layout.fromTimeSelect)
			{
				this.layout.fromTimeSelect.innerHTML = this.formatAmPmSpan(this.formatMinutes(this.rule.from));
			}

			this.rule.to = 24 * 60;
			if (this.layout.toTimeSelect)
			{
				this.layout.toTimeSelect.innerHTML = this.formatAmPmSpan(this.formatMinutes(this.rule.to));
			}
		}
		else
		{
			this.updateTo();
		}
	}

	updateTo()
	{
		const minToMinutes = this.rule.from + this.getSlotSize();
		if (minToMinutes > this.rule.to)
		{
			this.rule.to = minToMinutes;
			if (this.layout.toTimeSelect)
			{
				this.layout.toTimeSelect.innerHTML = this.formatAmPmSpan(this.formatMinutes(this.rule.to));
			}
		}
	}

	setDeletable(isDeletable)
	{
		this.deletable = isDeletable;

		const button = this.layout.button;
		this.layout.button = this.getButton();
		button?.replaceWith(this.layout.button);
	}

	getButton(): HTMLElement
	{
		let button;

		if (this.deletable)
		{
			button = Tag.render`
				<div class="calendar-sharing__settings-delete"></div>
			`;

			Event.bind(button, 'click', this.onDeleteButtonClickHandler.bind(this));
		}
		else
		{
			button = Tag.render`
				<div class="calendar-sharing__settings-add"></div>
			`;

			Event.bind(button, 'click', this.onAddButtonClickHandler.bind(this));
		}

		return button;
	}

	onDeleteButtonClickHandler()
	{
		if (this.readOnly)
		{
			this.showReadOnlyPopup(this.layout.button);
		}
		else
		{
			this.remove();
		}
	}

	onAddButtonClickHandler()
	{
		if (this.readOnly)
		{
			this.showReadOnlyPopup(this.layout.button);
		}
		else
		{
			this.addRange(this);
		}
	}

	hideButton()
	{
		Dom.addClass(this.layout.button, '--hidden');
	}

	showButton()
	{
		Dom.removeClass(this.layout.button, '--hidden');
	}

	remove()
	{
		if (!this.removeRange(this))
		{
			return;
		}

		Dom.addClass(this.layout.wrap, '--animate-remove');
		setTimeout(() => this.layout.wrap.remove(), 300);
	}

	renderWeekdaysSelect(): HTMLElement
	{
		const weekdaysLoc = Util.getWeekdaysLoc().map((loc, index) => {
			return {
				loc,
				index,
				active: this.rule.weekdays.includes(index),
			};
		});
		weekdaysLoc.push(...weekdaysLoc.splice(0, this.weekStart));

		this.layout.weekdaysSelect = Tag.render`
			<div
				class="calendar-sharing__settings-weekdays calendar-sharing__settings-select calendar-sharing__settings-select-arrow"
				title="${this.formatWeekdays(false)}"
			>
				${this.getWeekdaysTitle()}
			</div>
		`;

		Event.bind(this.layout.weekdaysSelect, 'click', this.onWeekdaysSelectClickHandler.bind(this));

		this.weekdays = weekdaysLoc.map((weekdayLoc) => this.createWeekday(weekdayLoc));

		this.weekdaysMenu = new Popup({
			id: `calendar-sharing-settings-weekdays${Date.now()}`,
			bindElement: this.layout.weekdaysSelect,
			content: Tag.render`
				<div class="calendar-sharing__settings-popup-weekdays">
					${this.weekdays.map((weekday) => weekday.render())}
				</div>
			`,
			autoHide: true,
			closeByEsc: true,
			angle: {
				position: 'top',
				offset: 105,
			},
			autoHideHandler: () => this.weekdaysMenu.canBeClosed,
			events: {
				onPopupShow: () => Dom.addClass(this.layout.weekdaysSelect, '--active'),
				onPopupClose: () => Dom.removeClass(this.layout.weekdaysSelect, '--active'),
			},
		});
		this.weekdaysMenu.canBeClosed = true;

		return this.layout.weekdaysSelect;
	}

	createWeekday(weekdayLoc): Weekday
	{
		return new Weekday({
			name: weekdayLoc.loc,
			index: weekdayLoc.index,
			active: weekdayLoc.active,
			onSelected: () => {
				if (this.rule.weekdays.includes(weekdayLoc.index))
				{
					return;
				}
				this.rule.weekdays.push(weekdayLoc.index);
				this.rule.weekdays = this.getSortedWeekdays(this.rule.weekdays);
				this.layout.weekdaysSelect.title = this.formatWeekdays();
				this.layout.weekdaysSelect.innerText = this.getWeekdaysTitle();

				this.ruleUpdated();
			},
			onDiscarded: () => {
				const index = this.rule.weekdays.indexOf(weekdayLoc.index);
				if (index < 0)
				{
					return;
				}
				this.rule.weekdays.splice(index, 1);
				this.layout.weekdaysSelect.title = this.formatWeekdays();
				this.layout.weekdaysSelect.innerText = this.getWeekdaysTitle();

				this.ruleUpdated();
			},
			canBeDiscarded: () => this.rule.weekdays.length > 1,
			onMouseDown: this.onWeekdayMouseDown.bind(this),
		});
	}

	onWeekdayMouseDown(event, currentWeekday)
	{
		this.weekdaysMenu.canBeClosed = false;
		const startX = event.clientX;
		const select = currentWeekday.active;
		this.controllableWeekdays = [];

		this.collectIntersectedWeekdays = (e) => {
			for (const weekday of this.weekdays)
			{
				const right = weekday.wrap.getBoundingClientRect().right;
				const left = weekday.wrap.getBoundingClientRect().left;

				if (
					(startX > right && e.clientX < right)
					|| (startX < left && e.clientX > left)
					|| (left < startX && startX < right)
				)
				{
					if (!this.controllableWeekdays.includes(weekday))
					{
						this.controllableWeekdays.push(weekday);
					}
					weekday.intersected = true;
				}
			}
		};

		this.onMouseMove = (e) => {
			this.controllableWeekdays.forEach((controllableWeekday) => {
				controllableWeekday.intersected = false;
			});

			this.collectIntersectedWeekdays(e);

			for (const weekday of this.controllableWeekdays)
			{
				if ((weekday.intersected && select) || (!weekday.intersected && !select))
				{
					weekday.select();
				}
				else
				{
					weekday.discard();
				}
			}
		};

		Event.bind(document, 'mousemove', this.onMouseMove);
		Event.bind(document, 'mouseup', () => {
			Event.unbind(document, 'mousemove', this.onMouseMove);
			setTimeout(() => {
				this.weekdaysMenu.canBeClosed = true;
			}, 0);
		});
	}

	renderTimeFromSelect(): HTMLElement
	{
		const fromFormatted = this.formatMinutes(this.rule.from);
		this.layout.fromTimeSelect = this.renderTimeSelect(fromFormatted, {
			isSelected: (minutes) => this.rule.from === minutes,
			onItemSelected: (minutes) => {
				this.rule.from = minutes;
				this.updateTo();
			},
			getMaxMinutes: () => 24 * 60 - this.getSlotSize(),
		}, 'calendar-sharing-settings-range-from');

		return this.layout.fromTimeSelect;
	}

	renderTimeToSelect(): HTMLElement
	{
		const toFormatted = this.formatMinutes(this.rule.to);
		this.layout.toTimeSelect = this.renderTimeSelect(toFormatted, {
			isSelected: (minutes) => this.rule.to === minutes,
			onItemSelected: (minutes) => {
				this.rule.to = minutes;
			},
			getMinMinutes: () => this.rule.from + this.getSlotSize(),
		}, 'calendar-sharing-settings-range-to');

		return this.layout.toTimeSelect;
	}

	renderTimeSelect(time, callbacks, dataId): HTMLElement
	{
		const timeSelect = Tag.render`
			<div
				class="calendar-sharing__settings-select calendar-sharing__settings-time calendar-sharing__settings-select-arrow"
				data-id="${dataId}"
			>
				${this.formatAmPmSpan(time)}
			</div>
		`;

		Event.bind(timeSelect, 'click', () => this.onTimeSelectClickHandler(timeSelect, callbacks));

		return timeSelect;
	}

	showTimeMenu(timeSelect, callbacks)
	{
		const timeStamps = [];
		for (let hour = 0; hour <= 24; hour++)
		{
			if (
				(!Type.isFunction(callbacks.getMinMinutes) || hour * 60 >= callbacks.getMinMinutes())
				&& (!Type.isFunction(callbacks.getMaxMinutes) || hour * 60 <= callbacks.getMaxMinutes())
			)
			{
				timeStamps.push({ minutes: hour * 60, label: this.formatAmPmSpan(Util.formatTime(hour, 0)) });
			}

			if (
				hour !== 24
				&& (!Type.isFunction(callbacks.getMinMinutes) || hour * 60 + 30 >= callbacks.getMinMinutes())
				&& (!Type.isFunction(callbacks.getMaxMinutes) || hour * 60 + 30 <= callbacks.getMaxMinutes())
			)
			{
				timeStamps.push({ minutes: hour * 60 + 30, label: this.formatAmPmSpan(Util.formatTime(hour, 30)) });
			}
		}

		let timeMenu;
		const items = timeStamps.map((timeStamp) => {
			return {
				html: Tag.render`
					<div class="calendar-sharing__am-pm-container">${timeStamp.label}</div>
				`,
				className: callbacks.isSelected(timeStamp.minutes) ? 'menu-popup-no-icon --selected' : 'menu-popup-no-icon',
				onclick: () => {
					timeSelect.innerHTML = timeStamp.label;
					callbacks.onItemSelected(timeStamp.minutes);
					this.ruleUpdated();
					timeMenu.close();
				},
			};
		});

		timeMenu = MenuManager.create({
			id: `calendar-sharing-settings-time-menu${Date.now()}`,
			className: 'calendar-sharing-settings-time-menu',
			bindElement: timeSelect,
			items,
			autoHide: true,
			closeByEsc: true,
			events: {
				onShow: () => Dom.addClass(timeSelect, '--active'),
				onClose: () => Dom.removeClass(timeSelect, '--active'),
			},
			maxHeight: 300,
			minWidth: timeSelect.offsetWidth,
		});
		timeMenu.show();

		const timezonesPopup = timeMenu.getPopupWindow();
		const popupContent = timezonesPopup.getContentContainer();
		const selectedTimezoneItem = popupContent.querySelector('.menu-popup-item.--selected');
		popupContent.scrollTop = selectedTimezoneItem.offsetTop - selectedTimezoneItem.offsetHeight * 2;
	}

	onWeekdaysSelectClickHandler()
	{
		if (this.readOnly)
		{
			this.showReadOnlyPopup(this.layout.weekdaysSelect);
		}
		else
		{
			this.weekdaysMenu.show();
		}
	}

	onTimeSelectClickHandler(timeSelect, callbacks)
	{
		if (this.readOnly)
		{
			this.showReadOnlyPopup(timeSelect);
		}
		else
		if (!Dom.hasClass(timeSelect, '--active'))
		{
			this.showTimeMenu(timeSelect, callbacks);
		}
	}

	formatAmPmSpan(time): string
	{
		return time.toLowerCase().replace(/(am|pm)/g, '<span class="calendar-sharing__settings-time-am-pm">$1</span>');
	}

	formatMinutes(minutes): string
	{
		const date = new Date(Util.parseDate('01.01.2000').getTime() + minutes * 60 * 1000);

		return Util.formatTime(date);
	}

	getWeekdaysTitle(): string
	{
		if ([...this.rule.weekdays].sort().toString() === this.workDays.sort().toString())
		{
			return Loc.getMessage('CALENDAR_SHARING_SETTINGS_WORKDAYS_MSGVER_1');
		}

		return this.formatWeekdays();
	}

	formatWeekdays(singleDay = true): string
	{
		if (singleDay && this.rule.weekdays.length === 1)
		{
			return Util.getWeekdaysLoc(true)[this.rule.weekdays[0]];
		}

		const weekdaysLoc = Util.getWeekdaysLoc();

		return this.rule.weekdays.map((w) => weekdaysLoc[w]).reduce((a, b) => `${a}, ${b}`, '');
	}

	getSortedWeekdays(weekdays): []
	{
		return weekdays
			.map((w) => (w < this.weekStart ? w + 10 : w))
			.sort((a, b) => a - b)
			.map((w) => w % 10);
	}
}
