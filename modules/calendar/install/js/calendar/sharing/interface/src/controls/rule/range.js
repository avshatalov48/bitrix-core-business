import { Dom, Tag, Type, Event } from 'main.core';
import Weekday from './weekday';
import { MenuManager, Popup, PopupManager } from 'main.popup';
import { Util } from 'calendar.util';
import { RangeModel } from '../../model/index';

type Params = {
	readOnly: boolean,
	model: RangeModel,
	showReadOnlyPopup: function,
};

export default class Range
{
	#params: Params;
	#layout: {
		wrap: HTMLElement,
		weekdaysSelect: HTMLElement,
		fromTimeSelect: HTMLElement,
		toTimeSelect: HTMLElement,
		button: HTMLElement,
	};

	constructor(params: Params)
	{
		this.#params = params;
		this.#layout = {};

		this.showReadOnlyPopup = Type.isFunction(params.showReadOnlyPopup) ? params.showReadOnlyPopup : () => {};
		this.onRangeUpdated = this.#onRangeUpdated.bind(this);

		this.#bindEvents();
	}

	get #model(): RangeModel
	{
		return this.#params.model;
	}

	hasShownPopups(): boolean
	{
		const weekdaysPopupShown = this.weekdaysMenu.isShown();
		const startPopupShown = Dom.hasClass(this.#layout.fromTimeSelect, '--active');
		const endPopupShown = Dom.hasClass(this.#layout.toTimeSelect, '--active');

		return weekdaysPopupShown || startPopupShown || endPopupShown;
	}

	#bindEvents(): void
	{
		this.#model.subscribe('updated', this.onRangeUpdated);
	}

	destroy(): void
	{
		this.#layout.wrap.remove();
		this.#unbindEvents();
	}

	#unbindEvents(): void
	{
		this.#model.unsubscribe('updated', this.onRangeUpdated);
	}

	#onRangeUpdated(): void
	{
		this.updateWeekdaysTitle();
	}

	render(): HTMLElement
	{
		this.#layout.wrap = Tag.render`
			<div class="calendar-sharing__settings-range">
				${this.#renderWeekdaysSelect()}
				<div class="calendar-sharing__settings-time-interval">
					${this.#renderTimeFromSelect()}
					<div class="calendar-sharing__settings-dash"></div>
					${this.#renderTimeToSelect()}
				</div>
				${this.renderButton()}
			</div>
		`;

		if (this.#model.isNew())
		{
			this.#animate();
		}

		return this.#layout.wrap;
	}

	#animate(): void
	{
		Dom.addClass(this.#layout.wrap, '--animate-show');
		setTimeout(() => {
			Dom.removeClass(this.#layout.wrap, '--animate-show');
			this.#model.setNew(false);
		}, 300);
	}

	renderButton(): HTMLElement
	{
		const button = this.#getButton();

		this.#layout.button?.replaceWith(button);
		this.#layout.button = button;

		return this.#layout.button;
	}

	#getButton(): HTMLElement
	{
		if (this.#model.isDeletable())
		{
			return Tag.render`
				<div
					class="calendar-sharing__settings-delete"
					onclick="${this.#onDeleteButtonClickHandler.bind(this)}"
				></div>
			`;
		}

		return Tag.render`
			<div
				class="calendar-sharing__settings-add"
				onclick="${this.#onAddButtonClickHandler.bind(this)}"
			></div>
		`;
	}

	#onDeleteButtonClickHandler(): void
	{
		if (this.#params.readOnly)
		{
			this.showReadOnlyPopup(this.#layout.button);
		}
		else
		{
			this.#remove();
		}
	}

	#onAddButtonClickHandler(): void
	{
		if (this.#params.readOnly)
		{
			this.showReadOnlyPopup(this.#layout.button);
		}
		else
		{
			this.#add();
		}
	}

	#add(): void
	{
		this.#model.getRule().addRange();
	}

	#remove(): void
	{
		if (!this.#model.getRule().removeRange(this.#model))
		{
			return;
		}

		Dom.addClass(this.#layout.wrap, '--animate-remove');
		setTimeout(() => this.destroy(), 300);
	}

	#renderWeekdaysSelect(): HTMLElement
	{
		const weekdaysLoc = Util.getWeekdaysLoc().map((loc, index) => {
			return {
				loc,
				index,
				active: this.#model.getWeekDays().includes(index),
			};
		});
		weekdaysLoc.push(...weekdaysLoc.splice(0, this.#model.getWeekStart()));

		this.#layout.weekdaysSelect = Tag.render`
			<div
				class="calendar-sharing__settings-weekdays calendar-sharing__settings-select calendar-sharing__settings-select-arrow"
				title="${ this.#model.formatWeekdays()}"
			>
				${this.#model.getWeekdaysTitle()}
			</div>
		`;

		const observer = new IntersectionObserver(() => {
			if (this.#layout.weekdaysSelect.offsetWidth > 0)
			{
				this.updateWeekdaysTitle();
			}
		});
		observer.observe(this.#layout.weekdaysSelect);

		Event.bind(this.#layout.weekdaysSelect, 'click', this.#onWeekdaysSelectClickHandler.bind(this));

		this.weekdays = weekdaysLoc.map((weekdayLoc) => this.#createWeekday(weekdayLoc));

		const weekdaysPopupId = `calendar-sharing-settings-weekdays-${this.#params.model.id}`;
		this.weekdaysMenu = PopupManager.getPopupById(weekdaysPopupId);
		if (!this.weekdaysMenu)
		{
			this.weekdaysMenu = this.#createWeekdaysPopup(weekdaysPopupId);
			this.weekdaysMenu.canBeClosed = true;
		}
		this.weekdaysMenu.setBindElement(this.#layout.weekdaysSelect);

		return this.#layout.weekdaysSelect;
	}

	updateWeekdaysTitle(): void
	{
		this.#layout.weekdaysSelect.title = this.#model.formatWeekdays(false);

		this.#layout.weekdaysSelect.innerText = this.#model.getWeekdaysTitle(true);
		const weekdaysSelectWidth = this.#layout.weekdaysSelect.offsetWidth - 32;
		const weekdaysTextWidth = this.#getTextNodeWidth(this.#layout.weekdaysSelect.firstChild);
		const weekdaysWidthIsOverflowing = weekdaysSelectWidth < weekdaysTextWidth;
		if (weekdaysWidthIsOverflowing)
		{
			this.#layout.weekdaysSelect.innerText = this.#model.getWeekdaysTitle(false);
		}
	}

	#getTextNodeWidth(textNode): number
	{
		const spanNode = BX.Tag.render`<span style="position: absolute;">${textNode.cloneNode()}</span>`;
		textNode.replaceWith(spanNode);
		const textWidth = spanNode.offsetWidth;
		spanNode.replaceWith(textNode);

		return textWidth;
	}

	#createWeekdaysPopup(id: string): Popup
	{
		return new Popup({
			id,
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
				onPopupShow: () => Dom.addClass(this.#layout.weekdaysSelect, '--active'),
				onPopupClose: () => Dom.removeClass(this.#layout.weekdaysSelect, '--active'),
			},
		});
	}

	#createWeekday(weekdayLoc): Weekday
	{
		return new Weekday({
			name: weekdayLoc.loc,
			index: weekdayLoc.index,
			active: weekdayLoc.active,
			onSelected: () => this.#model.addWeekday(weekdayLoc.index),
			onDiscarded: () => this.#model.removeWeekday(weekdayLoc.index),
			canBeDiscarded: () => this.#model.getWeekDays().length > 1,
			onMouseDown: this.#onWeekdayMouseDown.bind(this),
		});
	}

	#onWeekdayMouseDown(event, currentWeekday): void
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

	#renderTimeFromSelect(): HTMLElement
	{
		this.#layout.fromTimeSelect = this.#renderTimeSelect(this.#model.getFromFormatted(), {
			getTimeStamps: () => this.#model.getAvailableTimeFrom(),
			isSelected: (minutes) => this.#model.getFrom() === minutes,
			onItemSelected: (minutes) => this.#model.setFrom(minutes),
		}, 'calendar-sharing-settings-range-from');

		return this.#layout.fromTimeSelect;
	}

	#renderTimeToSelect(): HTMLElement
	{
		this.#layout.toTimeSelect = this.#renderTimeSelect(this.#model.getToFormatted(), {
			getTimeStamps: () => this.#model.getAvailableTimeTo(),
			isSelected: (minutes) => this.#model.getTo() === minutes,
			onItemSelected: (minutes) => this.#model.setTo(minutes),
		}, 'calendar-sharing-settings-range-to');

		return this.#layout.toTimeSelect;
	}

	#renderTimeSelect(time, callbacks, dataId): HTMLElement
	{
		const timeSelect = Tag.render`
			<div
				class="calendar-sharing__settings-select calendar-sharing__settings-time calendar-sharing__settings-select-arrow"
				data-id="${dataId}"
			>
				${this.formatAmPmSpan(time)}
			</div>
		`;

		Event.bind(timeSelect, 'click', () => this.#onTimeSelectClickHandler(timeSelect, callbacks));

		return timeSelect;
	}

	#onTimeSelectClickHandler(timeSelect, callbacks): void
	{
		if (this.#params.readOnly)
		{
			this.showReadOnlyPopup(timeSelect);
		}
		else if (!Dom.hasClass(timeSelect, '--active'))
		{
			this.#showTimeMenu(timeSelect, callbacks);
		}
	}

	#showTimeMenu(timeSelect, callbacks): void
	{
		let timeMenu;

		const items = callbacks.getTimeStamps().map((timeStamp) => {
			return {
				html: Tag.render`
					<div class="calendar-sharing__am-pm-container">${timeStamp.name}</div>
				`,
				className: callbacks.isSelected(timeStamp.value) ? 'menu-popup-no-icon --selected' : 'menu-popup-no-icon',
				onclick: () => {
					timeSelect.innerHTML = timeStamp.name;
					callbacks.onItemSelected(timeStamp.value);
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

	#onWeekdaysSelectClickHandler(): void
	{
		if (this.#params.readOnly)
		{
			this.showReadOnlyPopup(this.#layout.weekdaysSelect);
		}
		else
		{
			this.weekdaysMenu.show();
		}
	}

	formatAmPmSpan(time): string
	{
		return time.toLowerCase().replace(/(am|pm)/g, '<span class="calendar-sharing__settings-time-am-pm">$1</span>');
	}
}
