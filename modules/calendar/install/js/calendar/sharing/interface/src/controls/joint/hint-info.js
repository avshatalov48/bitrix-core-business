import { Loc, Tag } from 'main.core';
import { Popup } from 'main.popup';

export default class HintInfo
{
	#bindElement: HTMLElement;
	#layout: {
		wrapper: HTMLElement,
	};
	#popup: Popup;

	constructor(props)
	{
		this.#bindElement = props.bindElement;
		this.#layout = {};

		this.#popup = new Popup({
			bindElement: this.#bindElement,
			bindOptions: {
				position: 'top',
			},
			angle: {
				offset: this.#bindElement.offsetWidth / 2 + 24,
			},
			borderRadius: '24px',
			width: 425,
			content: this.getContent(),
			animation: 'fading-slide',
		});
	}

	getContent(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__user-selector-hint-wrapper">
					<div class="calendar-sharing__user-selector-hint-text-wrapper">
						<div class="calendar-sharing__user-selector-hint-text-title">
							${Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_HINT_TITLE')}
						</div>
						<div class="calendar-sharing__user-selector-hint-text-desc">
							${Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_HINT_DESC')}
						</div>
					</div>
					<div class="calendar-sharing__user-selector-hint-icon"></div>
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	show(): void
	{
		this.#popup?.show();
	}

	close(): void
	{
		this.#popup?.close();
	}
}