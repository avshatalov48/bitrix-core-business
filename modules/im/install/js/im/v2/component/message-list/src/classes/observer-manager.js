import 'main.polyfill.intersectionobserver';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';

export class ObserverManager
{
	#dialogId: string;
	#observer: IntersectionObserver;

	constructor(dialogId: string): ObserverManager
	{
		this.#dialogId = dialogId;
		this.#initObserver();
	}

	observeMessage(messageElement: HTMLElement)
	{
		this.#observer.observe(messageElement);
	}

	unobserveMessage(messageElement: HTMLElement)
	{
		this.#observer.unobserve(messageElement);
	}

	#initObserver()
	{
		this.#observer = new IntersectionObserver(((entries: IntersectionObserverEntry[]) => {
			entries.forEach((entry: IntersectionObserverEntry) => {
				const messageId = this.#getMessageIdFromElement(entry.target);
				if (!messageId || !entry.rootBounds)
				{
					return;
				}

				const messageIsFullyVisible = entry.isIntersecting && entry.intersectionRatio >= 0.99;
				if (messageIsFullyVisible || this.#isMessageBottomVisible(entry))
				{
					this.#sendVisibleEvent(messageId);
				}
				else
				{
					this.#sendNotVisibleEvent(messageId);
				}
			});
		}), { threshold: this.#getThreshold() });
	}

	#isMessageBottomVisible(entry: IntersectionObserverEntry): boolean
	{
		const wholeMessage = entry.boundingClientRect;
		const visibleMessagePart = entry.intersectionRect;

		if (visibleMessagePart.height === 0)
		{
			return false;
		}

		// +1 to offset browser floating point calculations
		return wholeMessage.bottom <= visibleMessagePart.bottom + 1;
	}

	#sendVisibleEvent(messageId: number): void
	{
		EventEmitter.emit(EventType.dialog.onMessageIsVisible, {
			messageId,
			dialogId: this.#dialogId,
		});
	}

	#sendNotVisibleEvent(messageId: number): void
	{
		EventEmitter.emit(EventType.dialog.onMessageIsNotVisible, {
			messageId,
			dialogId: this.#dialogId,
		});
	}

	#getThreshold(): number[]
	{
		const arrayWithZeros = Array.from({ length: 101 }).fill(0);

		return arrayWithZeros.map((zero, index) => index * 0.01);
	}

	#getMessageIdFromElement(messageElement: HTMLElement): number
	{
		return Number(messageElement.dataset.id);
	}
}
