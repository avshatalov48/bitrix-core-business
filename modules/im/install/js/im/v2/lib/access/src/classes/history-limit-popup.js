import { Tag, Event, Dom, Cache } from 'main.core';
import { Popup, type PopupOptions } from 'main.popup';

import { PopupType } from 'im.v2.const';
import { FeatureManager } from 'im.v2.lib.feature';

import '../css/limit-popup.css';

type MemoryCache<T> = {
	remember: (key: string, defaultValue: () => T) => T
};

export class HistoryLimitPopup
{
	#popupInstance: Popup;
	#cache: MemoryCache = new Cache.MemoryCache();

	constructor(): Popup
	{
		this.#popupInstance = new Popup(this.#getPopupConfig());
		this.#bindEvents();
	}

	show(): void
	{
		this.#popupInstance.show();
	}

	close(): void
	{
		this.#popupInstance.destroy();
	}

	#getPopupConfig(): PopupOptions
	{
		return {
			id: PopupType.messageHistoryLimit,
			className: 'bx-im-messenger__scope',
			closeIcon: false,
			autoHide: false,
			closeByEsc: false,
			animation: 'fading',
			overlay: true,
			padding: 0,
			content: this.#getContainer(),
			events: {
				onPopupDestroy: () => {
					this.#unbindEvents();
				},
			},
		};
	}

	#getContainer(): Element
	{
		return this.#cache.remember('', () => {
			const container: Element = Tag.render`
				<div class="bx-im-history-limit-popup__container">
					<div class="bx-im-history-limit-popup__image"></div>
					<div class="bx-im-history-limit-popup__title">
						${FeatureManager.chatHistory.getLimitTitle()}
					</div>
					<div class="bx-im-history-limit-popup__subtitle">
						${FeatureManager.chatHistory.getLimitSubtitle()}
					</div>
				</div>
			`;

			Dom.append(this.#getButtonContainer(), container);

			return container;
		});
	}

	#getButtonContainer(): Element
	{
		return this.#cache.remember('', () => {
			return Tag.render`
				<div class="bx-im-history-limit-popup__button">
					${FeatureManager.chatHistory.getLearnMoreText()}
				</div>
			`;
		});
	}

	#bindEvents(): void
	{
		Event.bind(this.#getButtonContainer(), 'click', () => {
			FeatureManager.chatHistory.openFeatureSlider();
			this.close();
		});
	}

	#unbindEvents(): void
	{
		Event.unbindAll(this.#getButtonContainer(), 'click');
	}
}
