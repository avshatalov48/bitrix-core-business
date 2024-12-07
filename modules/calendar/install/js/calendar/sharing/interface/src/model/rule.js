import { Util } from 'calendar.util';
import { EventEmitter } from 'main.core.events';
import { CalendarSettings } from './calendar-settings';
import { RangeModel, RangeParams } from './range';

export type RuleParams = {
	slotSize: number,
	ranges: RangeParams[],
};

type Params = {
	rule: RuleParams,
	calendarSettings: CalendarSettings,
};

export class RuleModel extends EventEmitter
{
	AVAILABLE_INTERVALS = [30, 45, 60, 90, 120, 180];
	MAX_RANGES = 5;
	DEFAULT_SLOT_SIZE = 60;

	#calendarSettings: CalendarSettings;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('Calendar.Sharing.Rule');

		const { rule, calendarSettings } = params;

		this.#calendarSettings = calendarSettings;

		this.ranges = [];
		for (const range of rule.ranges)
		{
			this.addRange(range, false);
		}

		this.setSlotSize(rule.slotSize);
		this.sortRanges();
	}

	toArray(): RuleParams
	{
		return {
			slotSize: this.getSlotSize(),
			ranges: this.getSortedRanges().map((range) => range.toArray()),
		};
	}

	getDefaultRule(): RuleModel
	{
		return new RuleModel({
			rule: {
				slotSize: this.DEFAULT_SLOT_SIZE,
				ranges: [{
					from: this.#calendarSettings.workTimeStart,
					to: this.#calendarSettings.workTimeEnd,
					weekdays: this.#calendarSettings.workDays,
				}],
			},
			calendarSettings: this.#calendarSettings,
		});
	}

	getAvailableIntervals(): number[]
	{
		return this.AVAILABLE_INTERVALS;
	}

	getFormattedSlotSize(): string
	{
		return Util.formatDuration(this.getSlotSize());
	}

	getSlotSize(): number
	{
		return this.slotSize;
	}

	setSlotSize(value)
	{
		const slotSize = parseInt(value, 10);
		this.slotSize = this.getAvailableIntervals().includes(slotSize) ? slotSize : this.DEFAULT_SLOT_SIZE;
		for (const range of this.getRanges())
		{
			range.updateSlotSize();
		}
	}

	getRanges(): RangeModel[]
	{
		return this.ranges;
	}

	sortRanges()
	{
		this.ranges = this.getSortedRanges();
	}

	getSortedRanges()
	{
		return [...this.ranges].sort((a, b) => this.compareRanges(a, b));
	}

	compareRanges(firstRange, secondRange): 1 | 0 | -1
	{
		const firstWeekdaysWeight = this.getWeekdaysWeight(firstRange.getWeekDays());
		const secondWeekdaysWeight = this.getWeekdaysWeight(secondRange.getWeekDays());

		if (firstWeekdaysWeight !== secondWeekdaysWeight)
		{
			return firstWeekdaysWeight - secondWeekdaysWeight;
		}

		if (firstRange.getFrom() !== secondRange.getFrom())
		{
			return firstRange.getFrom() - secondRange.getFrom();
		}

		return firstRange.getTo() - secondRange.getTo();
	}

	getWeekdaysWeight(weekdays): number
	{
		return weekdays.reduce((accumulator, w, index) => {
			return accumulator + w * 10 ** (10 - index);
		}, 0);
	}

	addRange(range, isNew = true): void
	{
		if (!this.canAddRange())
		{
			return;
		}

		this.internalRangeId ??= 1;

		this.ranges.push(
			new RangeModel({
				id: this.internalRangeId++,
				range: range ?? this.getDefaultRule().getRanges()[0],
				calendarSettings: this.#calendarSettings,
				isNew,
				rule: this,
			}),
		);

		this.#updateRanges();
		this.updated();
	}

	removeRange(rangeToRemove: RangeModel): boolean
	{
		if (!this.canRemoveRange())
		{
			return false;
		}

		this.ranges = this.ranges.filter((range) => {
			return range.getId() !== rangeToRemove.getId();
		});

		this.#updateRanges();
		this.rangeDeleted();

		return true;
	}

	#updateRanges()
	{
		for (const range of this.ranges.slice(0, -1))
		{
			range.setDeletable(true);
		}

		this.ranges.slice(-1)[0].setDeletable(this.ranges.length === 5);
	}

	canAddRange(): boolean
	{
		return this.ranges.length < this.MAX_RANGES;
	}

	canRemoveRange(): boolean
	{
		return this.ranges.length > 1;
	}

	updated(): void
	{
		this.emit('updated');
	}

	rangeDeleted(): void
	{
		this.emit('rangeDeleted');
	}
}