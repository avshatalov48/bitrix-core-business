import { Type, Dom, Event } from 'main.core';
import { Popup } from 'main.popup';

import './style.css';

export class Tooltip
{
	constructor(options)
	{
		this.popupOptions = Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
		this.bindElement = Type.isElementNode(options.bindElement) ? options.bindElement : null;
		this.title = Type.isString(options.title) ? options.title : null;
		this.minWidth = Type.isNumber(options.minWidth) ? options.minWidth : null;
		this.minHeight = Type.isNumber(options.minHeight) ? options.minHeight : null;
		this.maxWidth = Type.isNumber(options.maxWidth) ? options.maxWidth : 340;

		this.setContent(options.content);
	}

	getPopupWindow(): Popup
	{
		if (!this.popupWindow)
		{
			this.popupWindow = new Popup({
				bindElement: this.bindElement,
				className: 'ui-dialog-tooltip --ui-context-content-light',
				angle: true,
				titleBar: this.title ?? null,
				minWidth: this.minWidth,
				minHeight: this.minHeight,
				maxWidth: this.maxWidth,
				draggable: null,
				animation: 'fading-slide',
				closeByEsc: true,
				...this.popupOptions,
			});

			Dom.style(this.popupWindow.getTitleContainer(), 'cursor', null);
			Event.unbindAll(this.popupWindow.getTitleContainer());
		}

		return this.popupWindow;
	}

	setContent(content: string | Element | Node)
	{
		if (Type.isString(content) || Type.isElementNode(content))
		{
			this.getPopupWindow().setContent(content);
		}
	}

	show()
	{
		this.getPopupWindow().show();
	}

	close()
	{
		this.getPopupWindow().close();
	}
}
