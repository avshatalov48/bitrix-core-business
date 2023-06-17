import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

const EVENT_NAMESPACE = 'BX.Messenger.v2.Content.Chat.ResizeManager';

export class ResizeManager extends EventEmitter
{
	#observer: ResizeObserver;
	#textareaHeight: number;

	static events = {
		onHeightChange: 'onHeightChange'
	};

	constructor(): ResizeManager
	{
		super();
		this.setEventNamespace(EVENT_NAMESPACE);

		this.#initObserver();
	}

	observeTextarea(element: HTMLElement)
	{
		this.#observer.observe(element);
		this.#textareaHeight = element.clientHeight;
	}

	unobserveTextarea(element: HTMLElement)
	{
		this.#observer.unobserve(element);
		this.#textareaHeight = 0;
	}

	#initObserver()
	{
		this.#observer = new ResizeObserver(((entries: ResizeObserverEntry[]) => {
			entries.forEach((entry: ResizeObserverEntry) => {
				const height = entry.borderBoxSize?.[0].blockSize;
				if (Type.isNumber(height) && height !== this.#textareaHeight)
				{
					this.emit(ResizeManager.events.onHeightChange, {newHeight: height});
					this.#textareaHeight = height;
				}
			});
		}));
	}
}