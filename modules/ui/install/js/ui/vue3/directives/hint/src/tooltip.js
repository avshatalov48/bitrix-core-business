import { Popup, PopupOptions } from 'main.popup';
import { Tag, Text, Type } from 'main.core';

class Tooltip
{
	constructor(): void
	{
		this.popup = null;
	}

	show(
		element: HTMLElement,
		bindings: Object = {}
	): void
	{
		if (this.popup)
		{
			this.popup.close();
		}

		let popupOptions: PopupOptions = {};

		let text;
		if (Type.isObject(bindings.value))
		{
			if (bindings.value.text)
			{
				text = Text.encode(bindings.value.text);
			}
			else if (bindings.value.html)
			{
				text = bindings.value.html;
			}

			if (Type.isObject(bindings.value.popupOptions))
			{
				popupOptions = bindings.value.popupOptions;
			}

			if (bindings.value.position === 'top')
			{
				if (!Type.isObject(popupOptions.bindOptions))
				{
					popupOptions.bindOptions = {};
				}

				popupOptions.bindOptions.position = 'top';
			}
		}
		else
		{
			text = bindings.value;
			if (Type.isUndefined(element.dataset.hintHtml))
			{
				text = Text.encode(text);
			}
		}

		popupOptions.bindElement = element;

		if (Type.isUndefined(popupOptions.id))
		{
			popupOptions.id = 'bx-vue-hint';
		}

		if (Type.isUndefined(popupOptions.darkMode))
		{
			popupOptions.darkMode = true;
		}

		if (Type.isUndefined(popupOptions.content))
		{
			const content = Tag.render`<span class='ui-hint-content'></span>`;
			content.innerHTML = text;
			popupOptions.content = content;
		}

		if (Type.isUndefined(popupOptions.autoHide))
		{
			popupOptions.autoHide = true;
		}

		if (!Type.isObject(popupOptions.bindOptions))
		{
			popupOptions.bindOptions = {};
		}
		if (Type.isUndefined(popupOptions.bindOptions.position))
		{
			popupOptions.bindOptions.position = 'bottom';
		}

		popupOptions.cacheable = false;

		this.popup = new Popup(popupOptions);
		this.popup.show();
	}

	hide(): void
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}
}

const TooltipManager = new Tooltip;
export {TooltipManager as Tooltip};