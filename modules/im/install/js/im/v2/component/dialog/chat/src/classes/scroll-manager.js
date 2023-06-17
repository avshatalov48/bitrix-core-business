import {EventEmitter} from 'main.core.events';

import {Logger} from 'im.v2.lib.logger';
import {Animation} from 'im.v2.lib.animation';

const EVENT_NAMESPACE = 'BX.Messenger.v2.Dialog.ScrollManager';
const SCROLLING_THRESHOLD = 1500;
const POSITION_THRESHOLD = 40;
const SCROLLED_UP_THRESHOLD = 400;

export class ScrollManager extends EventEmitter
{
	container: HTMLElement;
	isScrolling: boolean = false;
	currentScroll: number = 0;
	lastScroll: number = 0;
	chatIsScrolledUp: boolean = false;
	scrollButtonClicked: boolean = false;

	static events = {
		onScrollTriggerUp: 'onScrollTriggerUp',
		onScrollTriggerDown: 'onScrollTriggerDown',
		onScrollThresholdPass: 'onScrollThresholdPass'
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
		// if (this.isScrolling || !event.target || this.currentScroll === event.target.scrollTop)
		if (this.isScrolling || !event.target)
		{
			return false;
		}

		this.currentScroll = event.target.scrollTop;
		const isScrollingDown = this.lastScroll < this.currentScroll;
		const isScrollingUp = !isScrollingDown;
		if (isScrollingUp)
		{
			this.scrollButtonClicked = false;
		}

		const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
		if (isScrollingDown && this.lastScroll > 0 && leftSpaceBottom < SCROLLING_THRESHOLD)
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

	scrollToMessage(messageId: number, offset: number = -10)
	{
		Logger.warn('Dialog: ScrollManager: scroll to message - ', messageId);
		const element = this.getDomElementById(messageId);
		if (!element)
		{
			Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
			return;
		}

		const position = element.offsetTop + offset;
		this.forceScrollTo(position);
	}

	animatedScrollToMessage(messageId: number, offset: number = -10): Promise
	{
		Logger.warn('Dialog: ScrollManager: animated scroll to message - ', messageId);
		const element = this.getDomElementById(messageId);
		if (!element)
		{
			Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
			return;
		}

		const position = element.offsetTop + offset;
		return this.animatedScrollTo(position);
	}

	forceScrollTo(position: number)
	{
		Logger.warn('Dialog: ScrollManager: Force scroll to - ', position);
		this.cancelAnimatedScroll();
		this.container.scroll({top: position, behavior: 'instant'});
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
				}
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
		return this.container.scrollHeight - this.container.scrollTop - this.container.clientHeight < POSITION_THRESHOLD;
	}

	getDomElementById(id: number | string): ?HTMLElement
	{
		return this.container.querySelector(`[data-id="${id}"]`);
	}
}