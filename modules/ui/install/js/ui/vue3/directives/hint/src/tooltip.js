import { Event, Tag, Text, Type } from 'main.core';
import { Popup, type PopupOptions } from 'main.popup';

export type HintParams = {
	text: string,
	html: string,
	popupOptions: PopupOptions,
	position: 'top',
	timeout: number,
	interactivity: boolean,
};

class Tooltip
{
	popup: ?Popup;
	cursorOnPopup: boolean;

	constructor(): void
	{
		this.popup = null;
		this.cursorOnPopup = false;
	}

	show(element: HTMLElement, params: HintParams): void
	{
		this.hide(false);

		const popupOptions: PopupOptions = {
			id: `bx-vue-hint-${Date.now()}`,
			bindElement: element,
			bindOptions: {
				position: (params.position === 'top') ? 'top' : 'bottom',
			},
			content: Tag.render`
				<span class='ui-hint-content'>${this.#getText(element, params)}</span>
			`,
			darkMode: true,
			autoHide: true,
			cacheable: false,
			animation: 'fading',
			...(params.popupOptions ?? null),
		};

		this.popup = new Popup(popupOptions);
		this.popup.show();

		if (params.interactivity && this.popup?.getPopupContainer())
		{
			Event.bind(this.popup.getPopupContainer(), 'mouseenter', () => {
				this.cursorOnPopup = true;
			});
			Event.bind(this.popup.getPopupContainer(), 'mouseleave', () => {
				this.cursorOnPopup = false;
				this.hide(true);
			});
		}
	}

	hide(isInteractive: boolean): void
	{
		if (isInteractive)
		{
			setTimeout(() => {
				if (this.popup && this.popup.getPopupContainer() && !(this.cursorOnPopup))
				{
					this.popup.close();
				}
			}, 100);
		}
		else
		{
			this.popup?.close();
		}
	}

	#getText(element: HTMLElement, params: HintParams): string
	{
		if (Type.isStringFilled(params) && Type.isUndefined(element.dataset.hintHtml))
		{
			return Text.encode(params);
		}

		return params.html || Text.encode(params.text) || params;
	}
}

export const tooltip = new Tooltip();
