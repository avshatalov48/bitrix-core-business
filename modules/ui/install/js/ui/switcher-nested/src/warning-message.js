import { Popup } from 'main.popup';
import type { WarningMessageOptions } from './types';

export class WarningMessage
{
	#popup: Popup;
	#id: string;
	#bindElement: HTMLElement;
	#message: HTMLElement;

	constructor(options: WarningMessageOptions)
	{
		this.#id = options.id;
		this.#bindElement = options.bindElement;
		this.#message = options.message;
	}

	#getPopup(): Popup
	{
		if (this.#popup)
		{
			return this.#popup;
		}

		this.#popup = new Popup({
			id: this.#id,
			bindElement: this.#bindElement,
			content: this.#message,
			darkMode: true,
			autoHide: true,
			angle: true,
			offsetLeft: 14,
			bindOptions: {
				position: 'bottom',
			},
			closeByEsc: true,
		});

		return this.#popup;
	}

	show(): void
	{
		this.#getPopup().show();
	}

	hide(): void
	{
		this.#getPopup().close();
	}
}