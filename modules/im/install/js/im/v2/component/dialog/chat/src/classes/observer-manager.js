import 'main.polyfill.intersectionobserver';
import {EventEmitter} from 'main.core.events';

const EVENT_NAMESPACE = 'BX.Messenger.v2.Dialog.ObserverManager';

export class ObserverManager extends EventEmitter
{
	#observer: IntersectionObserver;
	#observedElements: {[messageId: string]: HTMLElement} = {};
	#visibleMessages: Set = new Set();
	#messagesToRead: Set = new Set();
	#dialogInited: boolean = false;

	static events = {
		onMessageIsVisible: 'onMessageIsVisible'
	};

	constructor(): ObserverManager
	{
		super();
		this.setEventNamespace(EVENT_NAMESPACE);

		this.#initObserver();
	}

	setDialogInited(flag: boolean)
	{
		Object.values(this.#observedElements).forEach(element => {
			this.unobserveMessage(element);
			this.observeMessage(element);
		});

		this.#dialogInited = flag;
	}

	observeMessage(messageElement: HTMLElement)
	{
		this.#observer.observe(messageElement);
		if (this.#getMessageIdFromElement(messageElement))
		{
			this.#observedElements[messageElement.dataset.id] = messageElement;
		}
	}

	unobserveMessage(messageElement: HTMLElement)
	{
		this.#observer.unobserve(messageElement);
		if (this.#getMessageIdFromElement(messageElement))
		{
			delete this.#observedElements[messageElement.dataset.id];
		}
	}

	onReadMessage(messageId: number)
	{
		this.#messagesToRead.delete(messageId);
	}

	getMessagesToRead(): number[]
	{
		return [...this.#messagesToRead];
	}

	getFirstVisibleMessage(): number
	{
		if (this.#visibleMessages.size === 0)
		{
			return 0;
		}

		const [firstVisibleMessage] = [...this.#visibleMessages].sort((a, b) => a - b);

		return firstVisibleMessage;
	}

	#initObserver()
	{
		this.#observer = new IntersectionObserver(((entries: IntersectionObserverEntry[]) => {
			entries.forEach((entry: IntersectionObserverEntry) => {
				const messageId = this.#getMessageIdFromElement(entry.target);
				if (!messageId || !entry.rootBounds || !this.#dialogInited)
				{
					return;
				}

				const messageIsFullyVisible = entry.isIntersecting && entry.intersectionRatio >= 0.99;
				const messageTakesHalfOfViewport = entry.intersectionRect.height >= entry.rootBounds.height / 2.2;
				// const messageIsBiggerThanViewport = entry.boundingClientRect.height + 20 > entry.rootBounds.height;
				// const messageCountsAsVisible = messageIsBiggerThanViewport && messageTakesMostOfViewport;
				if (messageIsFullyVisible || messageTakesHalfOfViewport)
				{
					this.#visibleMessages.add(messageId);
					if (!this.#messageIsViewed(entry.target))
					{
						this.#messagesToRead.add(messageId);
						this.emit(ObserverManager.events.onMessageIsVisible);
					}
				}
				else
				{
					this.#visibleMessages.delete(messageId);
					if (this.#messageIsViewed(entry.target))
					{
						this.#messagesToRead.delete(messageId);
					}
				}
			});
		}), {threshold: Array.from({length: 101}).fill(0).map((zero, index) => index * 0.01)});
	}

	#getMessageIdFromElement(messageElement: HTMLElement): number
	{
		return +messageElement.dataset.id;
	}

	#messageIsViewed(messageElement: HTMLElement): boolean
	{
		return messageElement.dataset['viewed'] === 'true';
	}
}