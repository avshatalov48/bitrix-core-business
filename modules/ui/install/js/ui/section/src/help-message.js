import {Popup} from "main.popup";

export class HelpMessage
{
	#popup: Popup

	constructor(id: string, node: HTMLElement, message: HTMLElement)
	{
		this.#popup = new Popup(id, node, {
			content: message,
			darkMode: true,
			autoHide: true,
			angle: true,
			offsetLeft: 20,
			bindOptions: {
				position: 'bottom',
			},
			closeByEsc: true,
		});
	}

	getPopup(): Popup
	{
		return this.#popup;
	}

	show(): void
	{
		this.#popup.show();
	}
}