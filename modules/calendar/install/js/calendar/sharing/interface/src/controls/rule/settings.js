import { Dom, Event, Loc, Tag } from 'main.core';
import { Popup, MenuManager } from 'main.popup';
import { Util } from 'calendar.util';
import Range from './range';
import { SettingsModel, RuleModel } from '../../model/index';

import '../../css/settings.css';

export type Params = {
	readOnly: boolean,
	model: SettingsModel,
};

export default class Settings
{
	#params: Params;

	#layout: {
		wrap: HTMLElement,
		subtitle: HTMLElement,
		expandRuleArrow: HTMLElement,
		expandRuleButton: HTMLElement,
		rule: HTMLElement,
		weekdaysSelect: HTMLElement,
		slotSizeText: HTMLElement,
		slotSizeSelect: HTMLElement,
		rangesContainer: HTMLElement,
		ranges: Range[],
	};

	constructor(params: Params)
	{
		this.#params = params;
		this.#layout = {};

		this.readOnly = params.readOnly;

		this.#bindEvents();
	}

	get #model(): SettingsModel
	{
		return this.#params.model;
	}

	get #rule(): RuleModel
	{
		return this.#model.getRule();
	}

	#bindEvents(): void
	{
		this.#rule.subscribe('updated', this.#onRuleUpdated.bind(this));
		this.#rule.subscribe('rangeDeleted', this.#onRangeDeleted.bind(this));
	}

	#onRuleUpdated(): void
	{
		this.#updateSubtitle();
		this.#removeRuleHeight();
		this.#renderRanges();
	}

	#onRangeDeleted()
	{
		this.#updateSubtitle();
		this.#removeRuleHeight();
		this.#layout.ranges.forEach((range) => range.renderButton());
	}

	hasShownPopups(): boolean
	{
		const rangesWithPopup = this.#layout.ranges.filter((range) => range.hasShownPopups()) ?? [];
		const rangePopupShown = rangesWithPopup.length > 0;
		const slotSizePopupShown = Dom.hasClass(this.#layout.slotSizeSelect, '--active');
		const readOnlyPopupShown = this.readOnlyPopup?.isShown();

		return rangePopupShown || slotSizePopupShown || readOnlyPopupShown;
	}

	render(): HTMLElement
	{
		const readOnlyClass = this.readOnly ? '--read-only' : '';
		const expandedClass = this.#model.isCollapsed() ? '--hide' : '';
		const contextClass = `--${this.#model.getContext()}`;

		this.#layout.wrap = Tag.render`
			<div class="calendar-sharing__settings ${readOnlyClass} ${expandedClass} ${contextClass}">
				${this.#renderHeader()}
				${this.#renderRule()}
			</div>
		`;

		return this.#layout.wrap;
	}

	#renderHeader(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-sharing__settings-header-container">
				<div class="calendar-sharing__settings-header">
					<div class="calendar-sharing__settings-title">
						${Loc.getMessage('CALENDAR_SHARING_SETTINGS_TITLE_V2')}
					</div>
					${this.#renderSubtitle()}
				</div>
				<div class="calendar-sharing__settings-header-button">
					${this.#renderExpandRuleButton()}
				</div>
			</div>
		`;
	}

	#renderSubtitle(): HTMLElement
	{
		this.#layout.subtitle = Tag.render`
			<div class="calendar-sharing__settings-subtitle">
				${this.#getSubtitleText()}
			</div>
		`;

		return this.#layout.subtitle;
	}

	#updateSubtitle(): void
	{
		if (!this.#layout.subtitle)
		{
			return;
		}

		this.#layout.subtitle.innerText = this.#getSubtitleText();
	}

	#getSubtitleText(): string
	{
		if (this.#model.isDefaultRule())
		{
			return Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_DEFAULT');
		}

		return Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_PERSONAL');
	}

	#renderExpandRuleButton(): HTMLElement|string
	{
		if (this.readOnly)
		{
			return '';
		}

		this.#layout.expandRuleArrow = Tag.render`
			<div class="calendar-sharing__settings-select-arrow ${this.#model.isCollapsed() ? '' : '--active'}"></div>
		`;

		this.#layout.expandRuleButton = Tag.render`
			<div class="calendar-sharing__settings-expand">
				${this.#layout.expandRuleArrow}
			</div>
		`;

		Event.bind(this.#layout.expandRuleButton, 'click', this.#toggleExpand.bind(this));

		return this.#layout.expandRuleButton;
	}

	#renderRule(): HTMLElement
	{
		this.#layout.rule = Tag.render`
			<div class="calendar-sharing__settings-rule">
				${this.#renderRanges()}
				<div class="calendar-sharing__settings-slotSize">
					<span class="calendar-sharing__settings-slotSize-title">${Loc.getMessage('CALENDAR_SHARING_SETTINGS_SLOT_SIZE_V2')}</span>
					${this.#renderSettingsSlotSizeSelect()}
				</div>
			</div>
		`;

		return this.#layout.rule;
	}

	#toggleExpand(): void
	{
		this.#updateRuleHeight();
		setTimeout(() => {
			Dom.toggleClass(this.#layout.wrap, '--hide');
			Dom.toggleClass(this.#layout.expandRuleArrow, '--active');

			this.#model.updateCollapsed(Dom.hasClass(this.#layout.wrap, '--hide'));
		}, 0);
	}

	#updateRuleHeight(): void
	{
		Dom.style(this.#layout.rule, 'height', `${this.#calculateRuleHeight()}px`);
	}

	#renderRanges(): HTMLElement
	{
		this.#layout.ranges?.forEach((range) => range.destroy());
		this.#layout.ranges = this.#rule.getRanges().map((range) => this.#createRange(range));

		const rangesContainer = Tag.render`
			<div class="calendar-sharing__settings-range-list">
				${this.#layout.ranges.map((range) => range.render())}
			</div>
		`;

		this.#layout.rangesContainer?.replaceWith(rangesContainer);
		this.#layout.rangesContainer = rangesContainer;

		this.#layout.ranges.forEach((range) => range.updateWeekdaysTitle());

		return rangesContainer;
	}

	#createRange(range): Range
	{
		return new Range({
			model: range,
			readOnly: this.readOnly,
			showReadOnlyPopup: this.#showReadOnlyPopup.bind(this),
		});
	}

	#renderSettingsSlotSizeSelect(): HTMLElement
	{
		this.#layout.slotSizeText = Tag.render`
			<span class="calendar-sharing__settings-select-link">
				${this.#rule.getFormattedSlotSize()}
			</span>
		`;

		this.#layout.slotSizeSelect = Tag.render`
			<span class="calendar-sharing__settings-select-arrow --small-arrow">
				${this.#layout.slotSizeText}
			</span>
		`;

		Event.bind(this.#layout.slotSizeSelect, 'click', this.#slotSizeSelectClickHandler.bind(this));

		this.slotSizeMenu = MenuManager.create({
			id: `calendar-sharing-settings-slotSize${Date.now()}`,
			bindElement: this.#layout.slotSizeSelect,
			items: this.#model.getRule().getAvailableIntervals().map((minutes) => {
				return {
					text: Util.formatDuration(minutes),
					onclick: () => {
						this.#rule.setSlotSize(minutes);
						this.#layout.slotSizeText.innerHTML = this.#rule.getFormattedSlotSize();
						this.slotSizeMenu.close();
					},
				};
			}),
			closeByEsc: true,
			events: {
				onShow: () => Dom.addClass(this.#layout.slotSizeSelect, '--active'),
				onClose: () => Dom.removeClass(this.#layout.slotSizeSelect, '--active'),
			},
		});

		return this.#layout.slotSizeSelect;
	}

	#calculateRuleHeight(): number
	{
		const topMarginHeight = 10;
		const bottomMarginHeight = 2;
		const marginsHeight = topMarginHeight + bottomMarginHeight;
		const slotSizeHeight = 15;
		const rangeHeight = 45;

		return rangeHeight * this.#model.getRule().getRanges().length + (marginsHeight + slotSizeHeight);
	}

	#removeRuleHeight(): void
	{
		Dom.style(this.#layout.rule, 'height', null);
	}

	#slotSizeSelectClickHandler(): void
	{
		if (this.readOnly)
		{
			this.#showReadOnlyPopup(this.#layout.slotSizeSelect);
		}
		else
		{
			this.slotSizeMenu.show();
		}
	}

	#showReadOnlyPopup(pivotNode): void
	{
		this.#closeReadOnlyPopup();
		this.#getReadOnlyPopup(pivotNode).show();
	}

	#getReadOnlyPopup(pivotNode): Popup
	{
		const readonlyHint = this.#model.getCalendarContext()?.sharingObjectType === 'group'
			? 'CALENDAR_SHARING_SETTINGS_READ_ONLY_HINT_GROUP'
			: 'CALENDAR_SHARING_SETTINGS_READ_ONLY_HINT'
		;
		this.readOnlyPopup = new Popup({
			bindElement: pivotNode,
			className: 'calendar-sharing__settings-read-only-hint',
			content: Loc.getMessage(readonlyHint),
			angle: {
				offset: 0,
			},
			width: 300,
			offsetLeft: pivotNode.offsetWidth / 2,
			darkMode: true,
			autoHide: true,
		});

		Event.bind(this.readOnlyPopup.popupContainer, 'click', () => this.#closeReadOnlyPopup());

		clearTimeout(this.closePopupTimeout);
		this.closePopupTimeout = setTimeout(() => this.#closeReadOnlyPopup(), 3000);

		return this.readOnlyPopup;
	}

	#closeReadOnlyPopup(): void
	{
		this.readOnlyPopup?.destroy();
	}
}
