import { EventEmitter } from 'main.core.events';

import { Logger } from 'im.v2.lib.logger';
import { Animation } from 'im.v2.lib.animation';

const EVENT_NAMESPACE = 'BX.Messenger.v2.Dialog.ScrollManager';

export class ScrollManager extends EventEmitter
{
	container: HTMLElement;
	isScrolling: boolean = false;
	currentScroll: number = 0;
	lastScroll: number = 0;
	chatIsScrolledUp: boolean = false;
	scrollButtonClicked: boolean = false;
	startScrollNeeded: boolean = true;

	static events = {
		onScrollTriggerUp: 'onScrollTriggerUp',
		onScrollTriggerDown: 'onScrollTriggerDown',
		onScrollThresholdPass: 'onScrollThresholdPass',
	};

	static scrollPosition = {
		messageTop: 'messageTop',
		messageBottom: 'messageBottom',
	};

	constructor(): ScrollManager
	{
		super();
		this.setEventNamespace(EVENT_NAMESPACE);
	}

	setContainer(container: HTMLElement)
	{
		this.container = container;
	}

	onScroll(event: Event)
	{
		if (this.isScrolling || !event.target)
		{
			return;
		}

		this.currentScroll = event.target.scrollTop;
		const isScrollingDown = this.lastScroll < this.currentScroll;
		const isScrollingUp = !isScrollingDown;
		if (isScrollingUp)
		{
			this.scrollButtonClicked = false;
		}

		const SCROLLING_THRESHOLD = 1500;
		const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
		if (isScrollingDown && this.isStartScrollCompleted() && leftSpaceBottom < SCROLLING_THRESHOLD)
		{
			this.emit(ScrollManager.events.onScrollTriggerDown);
		}
		else if (isScrollingUp && this.currentScroll <= SCROLLING_THRESHOLD)
		{
			this.emit(ScrollManager.events.onScrollTriggerUp);
		}

		this.lastScroll = this.currentScroll;

		this.checkIfChatIsScrolledUp();
	}

	checkIfChatIsScrolledUp()
	{
		const SCROLLED_UP_THRESHOLD = 400;

		const availableScrollHeight = this.container.scrollHeight - this.container.clientHeight;
		const newFlag = this.currentScroll + SCROLLED_UP_THRESHOLD < availableScrollHeight;
		if (newFlag !== this.chatIsScrolledUp)
		{
			this.emit(ScrollManager.events.onScrollThresholdPass, newFlag);
		}
		this.chatIsScrolledUp = newFlag;
	}

	scrollToBottom()
	{
		Logger.warn('Dialog: ScrollManager: scroll to bottom');
		this.forceScrollTo(this.container.scrollHeight - this.container.clientHeight);
	}

	animatedScrollToBottom()
	{
		Logger.warn('Dialog: ScrollManager: animated scroll to bottom');
		this.animatedScrollTo(this.container.scrollHeight - this.container.clientHeight);
	}

	scrollToMessage(messageId: number, params: { withDateOffset: boolean, position: string } = {})
	{
		Logger.warn('Dialog: ScrollManager: scroll to message - ', messageId);
		const element = this.getDomElementById(messageId);
		if (!element)
		{
			Logger.warn('Dialog: ScrollManager: message not found - ', messageId);

			return;
		}

		const scrollPosition = this.#getScrollPosition(element, params);
		this.forceScrollTo(scrollPosition);
	}

	setStartScrollNeeded(flag: boolean): void
	{
		this.startScrollNeeded = flag;
	}

	isStartScrollCompleted(): boolean
	{
		if (!this.startScrollNeeded)
		{
			return true;
		}

		return this.lastScroll > 0;
	}

	animatedScrollToMessage(messageId: number, params: { withDateOffset: boolean, position: string } = {}): Promise
	{
		Logger.warn('Dialog: ScrollManager: animated scroll to message - ', messageId);
		const element = this.getDomElementById(messageId);
		if (!element)
		{
			Logger.warn('Dialog: ScrollManager: message not found - ', messageId);

			return Promise.resolve();
		}

		const scrollPosition = this.#getScrollPosition(element, params);

		return this.animatedScrollTo(scrollPosition);
	}

	forceScrollTo(position: number)
	{
		Logger.warn('Dialog: ScrollManager: Force scroll to - ', position);
		this.cancelAnimatedScroll();
		this.container.scroll({ top: position, behavior: 'instant' });
	}

	adjustScrollOnHistoryAddition(oldContainerHeight: number)
	{
		Logger.warn('Dialog: ScrollManager: Adjusting scroll after history addition');
		const newContainerHeight = this.container.scrollHeight - this.container.clientHeight;
		const newScrollPosition = this.container.scrollTop + newContainerHeight - oldContainerHeight;
		this.forceScrollTo(newScrollPosition);
	}

	animatedScrollTo(position: number): Promise
	{
		Logger.warn('Dialog: ScrollManager: Animated scroll to - ', position);

		return new Promise((resolve) => {
			Animation.start({
				start: this.container.scrollTop,
				end: position,
				element: this.container,
				elementProperty: 'scrollTop',
				callback: () => {
					this.checkIfChatIsScrolledUp();
					resolve();
				},
			});
		});
	}

	cancelAnimatedScroll()
	{
		if (!this.isScrolling)
		{
			return;
		}

		Animation.cancel();
		this.isScrolling = false;
	}

	isAtTheTop(): boolean
	{
		return this.container.scrollTop === 0;
	}

	isAtTheBottom(): boolean
	{
		return this.container.scrollTop + this.container.clientHeight >= this.container.scrollHeight;
	}

	isAroundBottom(): boolean
	{
		const POSITION_THRESHOLD = 40;

		return this.container.scrollHeight - this.container.scrollTop - this.container.clientHeight < POSITION_THRESHOLD;
	}

	getDomElementById(id: number | string): ?HTMLElement
	{
		return this.container.querySelector(`[data-id="${id}"]`);
	}

	#getScrollPosition(element: HTMLElement, params: { withDateOffset: boolean, position: string } = {}): number
	{
		const FLOATING_DATE_OFFSET = 52;
		const MESSAGE_BOTTOM_OFFSET = 100;

		const { withDateOffset = true, position = ScrollManager.scrollPosition.messageTop } = params;
		const offset = withDateOffset ? -FLOATING_DATE_OFFSET : -10;

		let scrollPosition = element.offsetTop + offset;
		if (position === ScrollManager.scrollPosition.messageBottom)
		{
			scrollPosition += element.clientHeight - MESSAGE_BOTTOM_OFFSET;
		}

		return scrollPosition;
	}
}
