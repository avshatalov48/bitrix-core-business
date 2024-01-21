import { DateTimeFormat } from 'main.date';
import { Dom, Event, Loc, Tag, Type } from 'main.core';
import { Popup, MenuManager } from 'main.popup';
import '../../css/settings.css';
import Range from './range';
import { Util } from 'calendar.util';
import { Analytics } from 'calendar.sharing.analytics';

type SettingsOptions = {
	readOnly: boolean,
	rule: {
		slotSize: number,
		ranges: Array<any>,
	},
	weekStart: number,
	workDays: Array<number>,
	workTimeStart: string,
	workTimeEnd: string,
	collapsed: boolean,
}

export default class Settings
{
	AVAILABLE_INTERVALS = [30, 45, 60, 90, 120, 180];
	MAX_RANGES = 5;

	layout: {
		wrap: HTMLElement,
		subtitle: HTMLElement,
		expandRuleArrow: HTMLElement,
		expandRuleButton: HTMLElement,
		rule: HTMLElement,
		weekdaysSelect: HTMLElement,
		slotSizeSelect: HTMLElement,
		rangesContainer: HTMLElement,
	};

	constructor(options: SettingsOptions)
	{
		this.layout = {};

		this.readOnly = options.readOnly;

		this.collapsed = !options.readOnly && options.collapsed;
		if (!Type.isBoolean(options.collapsed))
		{
			this.notExpandable = true;
		}

		this.workTimeStart = options.workTimeStart;
		this.workTimeEnd = options.workTimeEnd;
		this.workDays = options.workDays;
		this.weekStart = options.weekStart;
		this.rule = {
			slotSize: options.rule.slotSize,
		};

		this.ranges = [];
		for (const rangeOptions of options.rule.ranges)
		{
			this.ranges.push(this.getRange({
				...rangeOptions,
				show: false,
			}));
		}

		if (!Type.isArrayFilled(this.ranges))
		{
			this.ranges = [this.getRange()];
		}

		this.sortRanges();

		this.updateRanges();
	}

	getRule(): any
	{
		const ranges = this.ranges.map((range) => range.getRule());
		ranges.sort((a, b) => this.compareRanges(a, b));

		return JSON.parse(JSON.stringify({
			ranges,
			...this.rule,
		}));
	}

	sortRanges()
	{
		this.ranges.sort((a, b) => this.compareRanges(a.getRule(), b.getRule()));
	}

	compareRanges(range1, range2): number
	{
		const weekdaysWeight1 = this.getWeekdaysWeight(range1.weekdays);
		const weekdaysWeight2 = this.getWeekdaysWeight(range2.weekdays);

		if (weekdaysWeight1 !== weekdaysWeight2)
		{
			return weekdaysWeight1 - weekdaysWeight2;
		}

		if (range1.from !== range2.from)
		{
			return range1.from - range2.from;
		}

		return range1.to - range2.to;
	}

	getWeekdaysWeight(weekdays): number
	{
		return weekdays
			.map((w) => (w < this.weekStart ? w + 10 : w))
			.sort((a, b) => a - b)
			.reduce((accumulator, w, index) => {
				return accumulator + w * 10 ** (10 - index);
			}, 0);
	}

	settingPopupShown(): boolean
	{
		const rangesWithPopup = this.ranges.filter((range) => range.settingPopupShown()) ?? [];
		const rangePopupShown = rangesWithPopup.length > 0;
		const slotSizePopupShown = Dom.hasClass(this.layout.slotSizeSelect, '--active');
		const readOnlyPopupShown = this.readOnlyPopup?.isShown();

		return rangePopupShown || slotSizePopupShown || readOnlyPopupShown;
	}

	render(): HTMLElement
	{
		const readOnlyClass = this.readOnly ? '--read-only' : '';
		const expandedClass = this.collapsed ? '--hide' : '';

		this.layout.wrap = Tag.render`
			<div class="calendar-sharing__settings ${readOnlyClass} ${expandedClass}">
				${this.renderHeader()}
				${this.renderRule()}
			</div>
		`;

		return this.layout.wrap;
	}

	renderHeader(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-sharing__settings-header-container">
				<div class="calendar-sharing__settings-header">
					<div class="calendar-sharing__settings-title">
						${Loc.getMessage('CALENDAR_SHARING_SETTINGS_TITLE_V2')}
					</div>
					${this.renderSubtitle()}
				</div>
				<div class="calendar-sharing__settings-header-button">
					${this.renderExpandRuleButton()}
				</div>
			</div>
		`;
	}

	renderSubtitle(): HTMLElement
	{
		this.layout.subtitle = Tag.render`
			<div class="calendar-sharing__settings-subtitle">
				${this.getSubtitleText()}
			</div>
		`;

		return this.layout.subtitle;
	}

	updateSubtitle(): void
	{
		if (!this.layout.subtitle)
		{
			return;
		}

		this.layout.subtitle.innerText = this.getSubtitleText();
	}

	getSubtitleText(): string
	{
		if (this.isDefaultRule())
		{
			return Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_DEFAULT');
		}

		return Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_PERSONAL');
	}

	renderExpandRuleButton(): HTMLElement|string
	{
		if (this.readOnly || this.notExpandable)
		{
			return '';
		}

		this.layout.expandRuleArrow = Tag.render`
			<div class="calendar-sharing__settings-select-arrow ${this.collapsed ? '' : '--active'}"></div>
		`;

		this.layout.expandRuleButton = Tag.render`
			<div class="calendar-sharing__settings-expand">
				${this.layout.expandRuleArrow}
			</div>
		`;

		Event.bind(this.layout.expandRuleButton, 'click', this.toggle.bind(this));

		return this.layout.expandRuleButton;
	}

	renderRule(): HTMLElement
	{
		this.layout.rule = Tag.render`
			<div class="calendar-sharing__settings-rule">
				${this.renderRanges()}
				<div class="calendar-sharing__settings-slotSize">
					<span class="calendar-sharing__settings-slotSize-title">${Loc.getMessage('CALENDAR_SHARING_SETTINGS_SLOT_SIZE_V2')}</span>
					${this.getSettingsSlotSizeSelect()}
				</div>
			</div>
		`;

		return this.layout.rule;
	}

	toggle(): void
	{
		this.updateRuleHeight();
		setTimeout(() => {
			Dom.toggleClass(this.layout.wrap, '--hide');
			Dom.toggleClass(this.layout.expandRuleArrow, '--active');

			this.updateSharingSettingsCollapsedAction(Dom.hasClass(this.layout.wrap, '--hide'));
		}, 0);
	}

	updateSharingSettingsCollapsedAction(isCollapsed: boolean): void
	{
		BX.ajax.runAction('calendar.api.sharingajax.updateSharingSettingsCollapsed', {
			data: {
				collapsed: isCollapsed ? 'Y' : 'N',
			},
		});
	}

	renderRanges(): HTMLElement
	{
		this.ranges.forEach((range) => range.disableAnimation());

		const rangesContainer = Tag.render`
			<div class="calendar-sharing__settings-range-list">
				${this.ranges.map((range) => range.render())}
			</div>
		`;

		if (this.ranges.length === this.MAX_RANGES)
		{
			this.ranges[0].hideButton();
		}

		if (Type.isDomNode(this.layout.rangesContainer))
		{
			this.layout.rangesContainer.replaceWith(rangesContainer);
		}

		this.layout.rangesContainer = rangesContainer;

		return rangesContainer;
	}

	getRange(rangeOptions): Range
	{
		const date = new Date().toDateString();
		const from = new Date(`${date} ${`${this.workTimeStart}`.replace('.', ':')}:00`);
		const to = new Date(`${date} ${`${this.workTimeEnd}`.replace('.', ':')}:00`);

		const isNotFirst = this.ranges?.length >= 1;

		return new Range({
			getSlotSize: () => this.rule.slotSize,
			from: this.getMinutesFromDate(from),
			to: this.getMinutesFromDate(to),
			weekdays: this.workDays,
			weekStart: this.weekStart,
			workDays: this.workDays,
			addRange: (range) => this.addRange(range),
			removeRange: (range) => this.removeRange(range),
			showReadOnlyPopup: (node) => this.showReadOnlyPopup(node),
			ruleUpdated: () => this.ruleUpdated(),
			show: isNotFirst,
			readOnly: this.readOnly,
			...rangeOptions,
		});
	}

	addRange(afterRange)
	{
		if (this.ranges.length >= this.MAX_RANGES)
		{
			return;
		}

		const newRange = this.getRange();
		this.ranges.push(newRange);

		afterRange.getWrap().after(newRange.render());

		this.updateRanges();
	}

	removeRange(deletedRange): boolean
	{
		if (this.ranges.length <= 1)
		{
			return false;
		}

		this.ranges = this.ranges.filter((range) => range !== deletedRange);

		this.updateRanges();

		return true;
	}

	getSettingsSlotSizeSelect(): HTMLElement
	{
		this.layout.slotSizeText = Tag.render`
			<span class="calendar-sharing__settings-select-link">
				${Util.formatDuration(this.rule.slotSize)}
			</span>
		`;

		this.layout.slotSizeSelect = Tag.render`
			<span class="calendar-sharing__settings-select-arrow --small-arrow">
				${this.layout.slotSizeText}
			</span>
		`;

		Event.bind(this.layout.slotSizeSelect, 'click', this.slotSizeSelectClickHandler.bind(this));

		const items = this.AVAILABLE_INTERVALS.map((minutes) => {
			return {
				text: Util.formatDuration(minutes),
				onclick: () => {
					this.rule.slotSize = minutes;
					this.layout.slotSizeText.innerHTML = Util.formatDuration(this.rule.slotSize);
					this.slotSizeMenu.close();
					this.updateRanges();
				},
			};
		});

		this.slotSizeMenu = MenuManager.create({
			id: `calendar-sharing-settings-slotSize${Date.now()}`,
			bindElement: this.layout.slotSizeSelect,
			items,
			closeByEsc: true,
			events: {
				onShow: () => Dom.addClass(this.layout.slotSizeSelect, '--active'),
				onClose: () => Dom.removeClass(this.layout.slotSizeSelect, '--active'),
			},
		});

		return this.layout.slotSizeSelect;
	}

	updateRanges()
	{
		for (const range of this.ranges.slice(0, -1))
		{
			range.setDeletable(true);
			range.update();
		}

		const lastRange = this.ranges.slice(-1)[0];
		lastRange.setDeletable(this.ranges.length === 5);
		lastRange.update();

		this.ruleUpdated();
	}

	ruleUpdated()
	{
		this.updateSubtitle();
		this.removeRuleHeight();
	}

	updateRuleHeight()
	{
		Dom.style(this.layout.rule, 'height', `${this.calculateRuleHeight()}px`);
	}

	calculateRuleHeight()
	{
		const topMarginHeight = 10;
		const bottomMarginHeight = 2;
		const marginsHeight = topMarginHeight + bottomMarginHeight;
		const slotSizeHeight = 15;
		const rangeHeight = 45;

		return rangeHeight * this.ranges.length + (marginsHeight + slotSizeHeight);
	}

	removeRuleHeight()
	{
		Dom.style(this.layout.rule, 'height', null);
	}

	slotSizeSelectClickHandler()
	{
		if (this.readOnly)
		{
			this.showReadOnlyPopup(this.layout.slotSizeSelect);
		}
		else
		{
			this.slotSizeMenu.show();
		}
	}

	showReadOnlyPopup(pivotNode)
	{
		this.closeReadOnlyPopup();
		this.getReadOnlyPopup(pivotNode).show();
	}

	getReadOnlyPopup(pivotNode): Popup
	{
		this.readOnlyPopup = new Popup({
			bindElement: pivotNode,
			className: 'calendar-sharing__settings-read-only-hint',
			content: Loc.getMessage('CALENDAR_SHARING_SETTINGS_READ_ONLY_HINT'),
			angle: {
				offset: 0,
			},
			width: 300,
			offsetLeft: pivotNode.offsetWidth / 2,
			darkMode: true,
			autoHide: true,
		});

		Event.bind(this.readOnlyPopup.popupContainer, 'click', () => this.closeReadOnlyPopup());

		clearTimeout(this.closePopupTimeout);
		this.closePopupTimeout = setTimeout(() => this.closeReadOnlyPopup(), 3000);

		return this.readOnlyPopup;
	}

	closeReadOnlyPopup()
	{
		this.readOnlyPopup?.destroy();
	}

	getMinutesFromDate(date): number
	{
		const parsedTime = Util.parseTime(DateTimeFormat.format(DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), date / 1000));

		return parsedTime.h * 60 + parsedTime.m;
	}

	isDefaultRule(): boolean
	{
		return !this.isDifferentFrom(this.getDefaultRule());
	}

	isDifferentFrom(anotherRule): boolean
	{
		return !this.objectsEqual(anotherRule, this.getRule());
	}

	getChanges(): string[]
	{
		const defaultRule = this.getDefaultRule();
		const rule = this.getRule();

		const sizeChanged = rule.slotSize !== defaultRule.slotSize;
		const daysChanged = JSON.stringify(rule.ranges) !== JSON.stringify(defaultRule.ranges);

		const changes = [];

		if (daysChanged)
		{
			changes.push(Analytics.ruleChanges.custom_days);
		}

		if (sizeChanged)
		{
			changes.push(Analytics.ruleChanges.custom_length);
		}

		return changes;
	}

	getDefaultRule()
	{
		return {
			slotSize: 60,
			ranges: [this.getRange().getRule()],
		};
	}

	objectsEqual(obj1, obj2): boolean
	{
		return JSON.stringify(this.sortKeys(obj1)) === JSON.stringify(this.sortKeys(obj2));
	}

	sortKeys(object): any
	{
		return Object.keys(object).sort().reduce(
			(obj, key) => {
				obj[key] = object[key];

				return obj;
			},
			{},
		);
	}
}
