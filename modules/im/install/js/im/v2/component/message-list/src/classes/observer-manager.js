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
				const messageTakesHalfOfViewport = entry.intersectionRect.height >= entry.rootBounds.height / 2.2;
				// const messageIsBiggerThanViewport = entry.boundingClientRect.height + 20 > entry.rootBounds.height;
				// const messageCountsAsVisible = messageIsBiggerThanViewport && messageTakesMostOfViewport;
				if (messageIsFullyVisible || messageTakesHalfOfViewport)
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
