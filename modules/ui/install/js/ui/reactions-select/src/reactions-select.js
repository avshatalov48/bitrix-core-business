import {Type, Dom, Tag, Browser, Loc, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Lottie} from "ui.lottie";
import {Popup} from 'main.popup';

import "./reactions-select.css";
import "./reactions-icon.css";

import likeAnimatedEmojiData from '../animations/em_01.json';
import laughAnimatedEmojiData from '../animations/em_02.json';
import wonderAnimatedEmojiData from '../animations/em_03.json';
import cryAnimatedEmojiData from '../animations/em_04.json';
import angryAnimatedEmojiData from '../animations/em_05.json';
import facepalmAnimatedEmojiData from '../animations/em_06.json';
import admireAnimatedEmojiData from '../animations/em_07.json';

export const reactionType = Object.freeze({
	like: 'like',
	kiss: 'kiss',
	laugh: 'laugh',
	wonder: 'wonder',
	cry: 'cry',
	angry: 'angry',
	facepalm: 'facepalm',
});

export const reactionLottieAnimations = Object.freeze({
	like: likeAnimatedEmojiData,
	laugh: laughAnimatedEmojiData,
	wonder: wonderAnimatedEmojiData,
	cry: cryAnimatedEmojiData,
	angry: angryAnimatedEmojiData,
	facepalm: facepalmAnimatedEmojiData,
	kiss: admireAnimatedEmojiData,
});

export const reactionCssClass = Object.freeze({
	like: "reaction-icon_like",
	laugh: "reaction-icon_laugh",
	wonder: "reaction-icon_wonder",
	cry: "reaction-icon_cry",
	angry: "reaction-icon_angry",
	facepalm: "reaction-icon_facepalm",
	kiss: "reaction-icon_kiss",
});

export const reactionSelectEvents = Object.freeze({
	show: 'show',
	hide: 'hide',
	mouseenter: 'mouseenter',
	mouseleave: 'mouseleave',
	select: 'select',
	touchenter: 'touchenter',
	touchleave: 'touchleave',
	touchend: 'touchend',
	touchmove: 'touchmove',
});

type TouchEventHandler = (e: TouchEvent) => void;

type ReactionsSelectForcePosition = {
	left: number;
	top: number;
}

type ReactionsSelectPosition = | HTMLElement | ReactionsSelectForcePosition;
type ReactionsSelectOptions = {
	name?: string;
	position: ReactionsSelectPosition;
	containerClassname?: string;
}

/*
* Emitted events
* show,
* hide,
* select,
* mouseleave,
* mouseenter,
* touchenter,
* touchleave,
* touchend
*/

export class ReactionsSelect extends EventEmitter
{
	#name: string;
	#containerClassname: string = '';
	#position: ReactionsSelectPosition | null;
	#baseClassname: string;
	#popupContentClassname: string;
	#reactionsPopup: Popup | null;
	#popupContent: HTMLElement | null = null;
	#availableReactions: string[];
	#hoveredElement: HTMLElement | null;
	#touchMoveHandler: TouchEventHandler | null = null;
	#touchEndHandler: TouchEventHandler | null = null;
	#mouseEnterHandler: MouseEvent | null = null;
	#mouseLeaveHandler: MouseEvent | null = null;
	#isPopupTouched: boolean = false;
	#showClassname: null;
	#hideClassname: null;

	constructor(options: ReactionsSelectOptions = {name: 'ReactionsSelect'})
	{
		super();
		this.setEventNamespace('UI:ReactionsSelect');

		this.#name = Type.isString(options.name) ? options.name : this.#generateName();
		this.#baseClassname = 'reaction-select';
		this.#popupContentClassname = `${this.#baseClassname}_container`;
		this.#availableReactions = Object.keys(reactionType);
		this.#reactionsPopup = null;
		this.#containerClassname = Type.isString(options.containerClassname) ? options.containerClassname : '';
		this.#position = this.#checkPositionOption(options.position) ? options.position : null;
		this.#hoveredElement = null;
		this.#showClassname = 'reactions-popup-show';
		this.#hideClassname = 'reactions-popup-close';

		if (Browser.isMobile())
		{
			this.#touchMoveHandler = this.#handleTouchMove.bind(this);
			this.#touchEndHandler = this.#handleTouchEnd.bind(this);
		}
		else
		{
			this.#mouseEnterHandler = this.#handleMouseEnter.bind(this);
			this.#mouseLeaveHandler = this.#handleMouseLeave.bind(this);
			this.#touchMoveHandler = null;
			this.#touchEndHandler = null;
		}
	}

	static Events = reactionSelectEvents;
	static getLottieAnimation(reactionName?: string): Object | null
	{
		if (!reactionName)
		{
			return reactionLottieAnimations;
		}

		return reactionLottieAnimations[reactionName] || null;
	}

	static getReactionCssClass(reactionName?: string): Object | null
	{
		if (!reactionName)
		{
			return reactionCssClass;
		}

		return reactionCssClass[reactionName] || null;
	}

	show(): void
	{
		if (!this.#reactionsPopup)
		{
			this.#createReactionsPopup();
		}

		if (Browser.isMobile())
		{
			this.#disableScrollOnMobile();
			Event.bind(window, 'touchmove', this.#touchMoveHandler);
			Event.bind(window, 'touchend', this.#touchEndHandler);
		}

		this.#reactionsPopup.show();

		this.emit(ReactionsSelect.Events.show);
	}

	hide(): void
	{
		if (this.#reactionsPopup)
		{
			Event.unbind(this.#popupContent, 'mouseleave', this.#mouseLeaveHandler);
			Event.unbind(this.#popupContent, 'mouseenter', this.#mouseEnterHandler);
			Event.unbind(window, 'touchmove', this.#touchMoveHandler);
			Event.unbind(window, 'touchend', this.#touchEndHandler);

			this.#reactionsPopup.close();
			this.#reactionsPopup = null;
			this.#popupContent = null;

			this.#enableScrollOnMobile();
		}

		this.emit(ReactionsSelect.Events.hide);
	}

	isShown(): boolean
	{
		return this.#reactionsPopup && this.#reactionsPopup.isShown();
	}

	getName(): string
	{
		return this.#name;
	}

	#createReactionsPopup()
	{
		this.#reactionsPopup = new Popup({
			id: 'reactions-list-'+this.#name,
			content: this.#renderPopupContent(),
			...this.#getPopupPositionOptions(),
			noAllPaddings: true,
			borderRadius: '25px',
			animation: {
				showClassName: this.#showClassname,
				closeClassName: this.#hideClassname,
				closeAnimationType: 'animation',
			},
			cacheable: false,
			disableScroll: Browser.isMobile(),
			className: 'reaction-select-popup',
		});
	}

	#renderPopupContent(): HTMLElement
	{
		this.#popupContent = Tag.render`
			<div class="${this.#getPopupContentClassname()}">
				${this.#renderReactionsList()}
			</div>
		`;

		if (!Browser.isMobile())
		{
			Event.bind(this.#popupContent, 'mouseleave', this.#mouseLeaveHandler);
			Event.bind(this.#popupContent, 'mouseenter', this.#mouseEnterHandler);
		}

		return this.#popupContent;
	}

	#getPopupContentClassname(): string
	{
		const baseClassname = `${this.#popupContentClassname}`;
		const mobileDeviceModifier = `${Browser.isMobile() ? '--mobile' : ''}`;

		return [
			baseClassname,
			this.#containerClassname,
			mobileDeviceModifier
		].join(' ');
	}

	#renderReactionsList(): HTMLElement
	{
		const container: HTMLElement = Tag.render`<div class="${this.#baseClassname}_list"></div>`;

		this.#availableReactions.forEach((reactionName) => {
			Dom.append(this.#renderReactionItem(reactionName), container);
		});

		return container;
	}

	#renderReactionItem(reactionName: string): HTMLElement
	{
		const className = `${this.#baseClassname}_reaction-icon-item`;
		const reactionTitle = Loc.getMessage(`REACTIONS_SELECT_${reactionName.toUpperCase()}`);
		const reactionIcon = this.#renderAnimatedReactionIcon(reactionName);
		const reactionHoverArea = this.#renderReactionItemHoverArea(reactionName);

		return Tag.render`
			<div
				class="${className}"
				data-reaction="${reactionName}"
				title="${reactionTitle}"
			>
				${reactionHoverArea}
				${reactionIcon}
			</div>
		`;
	}

	#renderAnimatedReactionIcon(reactionName: string): HTMLElement
	{
		const reactionIcon = Tag.render`<div class="${this.#baseClassname}_reaction-icon"></div>`;

		Lottie.loadAnimation({
			renderer: 'svg',
			container: reactionIcon,
			animationData: reactionLottieAnimations[reactionName],
		});

		return reactionIcon;
	}

	#renderReactionItemHoverArea(reactionName: string): HTMLElement
	{
		const className = `${this.#baseClassname}_reaction-hover-area`;
		const reactionHoverArea: HTMLElement= Tag.render`<div class="${className}"></div>`;

		if (!Browser.isMobile())
		{
			Event.bind(reactionHoverArea, 'click', () => {
				this.emit(ReactionsSelect.Events.select, {
					reaction: reactionName,
				});
			});
		}

		return reactionHoverArea;
	}

	#getPopupPositionForBindElement()
	{
		const leftShift = -50;
		const topShift = -60;

		const {left = 0, top = 0} = Dom.getPosition(this.#position);

		return {left: left + leftShift, top: top + topShift};
	}

	#getPopupPositionOptions(): Object
	{
		if (Type.isPlainObject(this.#position) && this.#position?.left && this.#position?.top)
		{
			return {
				bindElement: this.#position,
			};
		}
		else if (Type.isDomNode(this.#position))
		{
			return {
				bindElement: this.#getPopupPositionForBindElement(),
			};
		}

		return {};
	}

	#handleTouchMove(e: TouchEvent): void
	{
		const reactionHoverArea = this.#getReactionHoverAreaFromTouch(e);
		const isCurrentTouchOnPopup = this.#checkIsPopupTouched(e);

		if (this.#isPopupTouched === false && isCurrentTouchOnPopup === true)
		{
			this.emit( ReactionsSelect.Events.touchenter);
		}
		else if (this.#isPopupTouched === true && isCurrentTouchOnPopup === false)
		{
			this.emit(ReactionsSelect.Events.mouseleave);
		}

		this.#isPopupTouched = isCurrentTouchOnPopup;

		if (reactionHoverArea === null)
		{
			Dom.removeClass(this.#hoveredElement, '--hover');
			this.#hoveredElement = null;
		}
		else if (this.#hoveredElement !== reactionHoverArea)
		{
			Dom.removeClass(this.#hoveredElement, '--hover');
			this.#hoveredElement = reactionHoverArea;
			Dom.addClass(this.#hoveredElement, '--hover');
		}

		this.emit(ReactionsSelect.Events.touchmove);
	}

	#handleTouchEnd(e: TouchEvent): void
	{
		const reactionHoverArea = this.#hoveredElement || this.#getReactionHoverAreaFromTouch(e);

		const reactionName = reactionHoverArea?.parentElement.getAttribute('data-reaction');

		if (reactionName)
		{
			this.emit(ReactionsSelect.Events.select, {
				reaction: reactionName || null,
			});
		}
		this.emit(ReactionsSelect.Events.touchend);
	}

	#handleMouseLeave(e: MouseEvent): void
	{
		this.emit(ReactionsSelect.Events.mouseleave, e);
	}

	#handleMouseEnter(e: MouseEvent): void
	{
		this.emit(ReactionsSelect.Events.mouseenter, e);
	}

	#getReactionHoverAreaFromTouch(e: TouchEvent): HTMLElement | null
	{
		const element = this.#getElementFromTouchEvent(e);

		return this.#isReactionHoverArea(element) ? element : null;
	}

	#isReactionHoverArea(element: Element | null): boolean
	{
		return element && element.classList.contains(`reaction-select_reaction-hover-area`);
	}

	#touchMoveScrollListener(e)
	{
		e.preventDefault();
	}

	#disableScrollOnMobile(): void
	{
		if (!Browser.isMobile())
		{
			return;
		}

		if (app)
		{
			app.exec('disableTabScrolling');
		}
		this.emit('onPullDownDisable');
		Event.bind(window, 'touchmove', this.#touchMoveScrollListener, { passive: false });
	}

	#enableScrollOnMobile(): void
	{
		if (!Browser.isMobile())
		{
			return;
		}
		document.removeEventListener('touchmove', this.#touchMoveScrollListener, { passive: false });
		this.emit('onPullDownEnable');
	}

	#generateName(): string
	{
		const num = Math.round(Math.random() * 1000);

		return `ReactionsSelect${num}`;
	}

	#checkPositionOption(position: ReactionsSelectPosition): boolean
	{
		if (position === undefined)
		{
			console.warn('UI.ReactionSelect: "position" parameter is required');
			return false;
		}
		else if (!Type.isDomNode(position) && !Type.isPlainObject(position))
		{
			console.warn('UI.ReactionSelect: "position" parameter must be an Object or an HTMLElement');
			return false;
		}
		else if (
			!Type.isPlainObject(position)
			&& !Type.isDomNode(position)
		)
		{
			console.warn('UI.ReactionSelect: "position" must be HTMLElement');
			return false;
		}
		else if (
			Type.isPlainObject(position) && !Type.isNumber(position.left)
		)
		{
			console.warn('UI.ReactionSelect: position.left must be a number');
			return false;
		}
		else if (Type.isPlainObject(position) && !Type.isNumber(position.top))
		{
			console.warn('UI.ReactionSelect: position.top must be a number');
			return false;
		}

		return true;
	}

	#getElementFromTouchEvent(e: TouchEvent): HTMLElement | null
	{
		if (!e || !e.touches || e.touches.length < 1)
		{
			return null;
		}
		const touchX = e.touches.item(0)?.pageX;
		const touchY = e.touches.item(0)?.pageY;

		return document.elementFromPoint(touchX, touchY);
	}

	#checkIsPopupTouched(e: TouchEvent): boolean
	{
		const element = this.#getElementFromTouchEvent(e);

		return Boolean(element.closest(`.${this.#popupContentClassname}`));
	}
}