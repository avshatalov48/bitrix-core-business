import { Dom, Tag, Type, Loc, bind, unbind } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { ZIndexManager } from 'main.core.z-index-manager';
import { Icon, Outline } from 'ui.icon-set.api.core';

import 'ui.icon-set.outline';

import { Reaction, ReactionTitle, ReactionName } from 'ui.reaction.item';

import './css/popover.css';
import { ReactionPickerRanking } from './ranking';

type ReactionPickerTarget = HTMLElement | {
	top: number;
	left: number;
};

export type ReactionPickerOptions = {
	target: ReactionPickerTarget;
	reactions?: string[];
};

export const ReactionPickerEvents = {
	show: 'show',
	hide: 'hide',
	expand: 'expand',
	select: 'select',
	mouseenter: 'mouseenter',
	mouseleave: 'mouseleave',
};

export class ReactionPicker extends EventEmitter
{
	#target: ReactionPickerTarget;

	#popover: ?HTMLElement = null;
	#isShown: boolean;
	#listContainer: ?HTMLElement;
	#expandedListContainer: ?HTMLElement;
	#reactions: Reaction[];
	#allowedReactions: ?string[];

	#ranking: ReactionPickerRanking;

	#mouseenterHandler: ?Function = null;
	#mouseleaveHandler: ?Function = null;

	static #rowSize = 7;
	static #minHeight = 56;
	static #offsetBetweenTargetElement = 8;

	constructor(options: ReactionPickerOptions)
	{
		super();

		this.setEventNamespace('UI.ReactionPicker.V2');
		this.#target = options.target;
		this.#allowedReactions = Type.isArrayFilled(options.reactions) ? options.reactions : null;

		this.#listContainer = null;
		this.#expandedListContainer = null;
		this.#isShown = false;
		this.#reactions = [];
		this.#ranking = new ReactionPickerRanking();
	}

	show(): void
	{
		if (!this.#popover)
		{
			this.#initPopover();
		}

		Dom.append(this.#popover, document.body);

		this.adjustPosition();

		ZIndexManager.register(this.#popover);
		ZIndexManager.bringToFront(this.#popover);

		requestAnimationFrame(() => {
			Dom.addClass(this.#popover, '--visible');
		});

		this.#isShown = true;

		this.emit(ReactionPickerEvents.show);
	}

	hide(): void
	{
		if (!this.#popover)
		{
			return;
		}

		Dom.addClass(this.#popover, '--hiding');
		Dom.removeClass(this.#popover, '--visible');

		setTimeout(() => {
			this.#destroyReactions();
			this.#destroyPopover();
		}, 150);

		this.#isShown = false;

		this.emit(ReactionPickerEvents.hide);
	}

	isShown(): boolean
	{
		return this.#isShown;
	}

	adjustPosition(): void
	{
		if (!this.#popover)
		{
			return;
		}

		if (Type.isNumber(this.#target.top) && Type.isNumber(this.#target.left))
		{
			this.#adjustPositionToCoordinates(this.#target);
		}
		else if (this.#target instanceof HTMLElement)
		{
			this.#adjustPositionToElement(this.#target);
		}
	}

	getPopoverRect(): ?DOMRect
	{
		return this.#popover ? Dom.getPosition(this.#popover) : undefined;
	}

	#adjustPositionToElement(target: HTMLElement): void
	{
		const elementRect = Dom.getPosition(target);
		const popoverRect = Dom.getPosition(this.#popover);

		let top = 0;

		const isEnoughSpaceBelowTargetElement = this.#isEnoughSpaceBelowTargetElement();

		if (isEnoughSpaceBelowTargetElement === false)
		{
			top = elementRect.top - popoverRect.height - ReactionPicker.#offsetBetweenTargetElement;
		}
		else if (this.#isEnoughSpaceAboveTargetElement())
		{
			top = elementRect.top - ReactionPicker.#minHeight - ReactionPicker.#offsetBetweenTargetElement;
		}
		else if (isEnoughSpaceBelowTargetElement)
		{
			top = elementRect.top + elementRect.height + ReactionPicker.#offsetBetweenTargetElement;
		}

		let left = elementRect.left - 53;

		const rightEdge = left + popoverRect.width;
		const windowWidth = window.innerWidth;
		const rightPadding = 40;

		if (rightEdge + rightPadding > windowWidth)
		{
			left = windowWidth - popoverRect.width - rightPadding;
		}

		Dom.style(this.#popover, {
			top: `${top}px`,
			left: `${left}px`,
		});
	}

	#adjustPositionToCoordinates(position: { top: number, left: number }): void
	{
		Dom.style(this.#popover, {
			top: `${position.top}px`,
			left: `${position.left}px`,
		});
	}

	#initPopover(): void
	{
		if (!this.#popover)
		{
			this.#listContainer = this.#renderReactionsList(this.#getReactionsNames().slice(0, ReactionPicker.#rowSize - 1));

			this.#expandedListContainer = Tag.render`
				<div class="reactions-select-popover__expanded-list"></div>
			`;
			const showExpandButton = this.#getReactionsNames().length > ReactionPicker.#rowSize;
			this.#popover = Tag.render`
				<div class="reactions-select-popover --ui-context-content-light">
					<div class="reactions-select-popover__inner">
						${this.#listContainer}
						${showExpandButton ? this.#renderExpandButton() : null}
						${this.#expandedListContainer}
					</div>
				</div>
			`;

			this.#mouseenterHandler = () => {
				this.emit(ReactionPickerEvents.mouseenter);
			};

			this.#mouseleaveHandler = () => {
				this.emit(ReactionPickerEvents.mouseleave);
			};

			bind(this.#popover, 'mouseenter', this.#mouseenterHandler);
			bind(this.#popover, 'mouseleave', this.#mouseleaveHandler);
		}
	}

	#renderReactionsList(reactionsIds: string[], isExpanded: boolean = false): HTMLElement
	{
		const list = Tag.render`
			<div class="reactions-select__list"></div>
		`;

		if (isExpanded)
		{
			Dom.addClass(list, '--expanded');
		}

		reactionsIds.forEach((reactionId) => {
			Dom.append(this.#renderReactionElement(reactionId), list);
		});

		return list;
	}

	#renderReactionElement(reactionName: string): HTMLElement
	{
		const reactionTitle = ReactionTitle[reactionName];

		const reaction = new Reaction({
			name: reactionName,
			size: 32,
			animation: {
				animate: true,
				infinite: true,
			},
		});

		this.#reactions.push(reaction);

		const element = Tag.render`
			<div class="reactions-select__list-elem" title="${reactionTitle}">
				<div ref="inner" class="reactions-select__list-elem-inner">
					${reaction.render()}
				</div>
			</div>
		`;

		bind(element.inner, 'mouseenter', () => {
			// reaction.playAnimation(true);
		});

		bind(element.inner, 'mouseleave', () => {
			// reaction.pauseAnimation(false);
		});

		bind(element.root, 'click', () => {
			this.emit(ReactionPickerEvents.select, {
				reaction: reactionName,
			});

			this.#ranking.incrementReactionCounter(reactionName);
		});

		return element.root;
	}

	#renderExpandButton(): HTMLElement
	{
		const icon = new Icon({
			icon: Outline.CHEVRON_DOWN_M,
			size: 24,
		});

		const button = Tag.render`
			<button
				class="reactions-select__expand-button --ui-hoverable"
				title="${Loc.getMessage('UI_REACTIONS_LIST_EXPAND_BUTTON_TITLE')}"
			>
				${icon.render()}
			</button>
		`;

		const wrapper = Tag.render`
			<div class="reactions-select__expand-button-wrapper">
				${button}
			</div>
		`;

		bind(button, 'click', () => {
			Dom.remove(wrapper);
			Dom.append(
				this.#renderReactionElement(this.#getReactionsNames()[ReactionPicker.#rowSize - 1]),
				this.#listContainer,
			);
			this.#expand();
		});

		return wrapper;
	}

	#destroyPopover(): void
	{
		if (!this.#popover)
		{
			return;
		}

		if (this.#mouseenterHandler)
		{
			unbind(this.#popover, 'mouseenter', this.#mouseenterHandler);
			this.#mouseenterHandler = null;
		}

		if (this.#mouseleaveHandler)
		{
			unbind(this.#popover, 'mouseleave', this.#mouseleaveHandler);
			this.#mouseleaveHandler = null;
		}

		ZIndexManager.unregister(this.#popover);
		this.#popover.remove();
		this.#popover = null;
		this.#listContainer = null;
		this.#expandedListContainer = null;
	}

	#destroyReactions(): void
	{
		this.#reactions.forEach((reaction: Reaction) => {
			reaction.destroy();
		});

		this.#reactions = [];
	}

	#expand(): void
	{
		Dom.addClass(this.#popover, '--expanded');
		Dom.append(
			this.#renderReactionsList(this.#getReactionsNames().slice(ReactionPicker.#rowSize), true),
			this.#expandedListContainer,
		);

		if (this.#isEnoughSpaceBelowTargetElement() === false)
		{
			this.adjustPosition();
		}
	}

	#isEnoughSpaceBelowTargetElement(): boolean
	{
		const popoverRect = Dom.getPosition(this.#popover);
		const targetRect = Dom.getPosition(this.#target);

		return targetRect.bottom + popoverRect.height + ReactionPicker.#offsetBetweenTargetElement < window.innerHeight;
	}

	#isEnoughSpaceAboveTargetElement(): boolean
	{
		const elementRect = Dom.getPosition(this.#target);

		return (
			elementRect.top
			- ReactionPicker.#minHeight
			- window.scrollY
			- ReactionPicker.#offsetBetweenTargetElement)
		> 0;
	}

	#getReactionsNames(): string[]
	{
		const rankedReactions = this.#ranking.getRankedReactionsNames();

		const excludedReactions = new Set([
			ReactionName.signHorns,
			ReactionName.faceWithStuckOutTongue,
			ReactionName.handshake,
			ReactionName.hundredPoints,
			ReactionName.sleepingSymbol,
			ReactionName.crossMark,
			ReactionName.whiteHeavyCheckMark,
			ReactionName.eyes,
		]);

		if (this.#allowedReactions)
		{
			return rankedReactions.filter((reaction) => this.#allowedReactions.includes(reaction));
		}

		return rankedReactions.filter((reaction) => !excludedReactions.has(reaction));
	}
}
