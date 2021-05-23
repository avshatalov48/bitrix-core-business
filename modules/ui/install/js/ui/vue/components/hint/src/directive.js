/**
 * Hint Vue directive
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

/*
	<span v-bx-hint="$Bitrix.Loc.getMessage('HINT_HTML')" data-hint-html>Html code</span>
	<span v-bx-hint="{text: 'Text node'}">Plain text</span>
	<span v-bx-hint="{html: '<b>Html</b> code'}">Html code</span>
	<span v-bx-hint="{text: 'Custom position top and light mode', position: 'top', popupOptions: {darkMode: false}}">Text top on light panel</span>
*/

import {BitrixVue} from 'ui.vue';
import {Text, Tag, Event, Type} from 'main.core';
import {Popup, PopupOptions} from "main.popup";
import 'ui.hint';

BitrixVue.directive('bx-hint',
{
	bind(element: HTMLElement, bindings)
	{
		Event.bind(element, 'mouseenter', () => TooltipManager.show(element, bindings));
		Event.bind(element, 'mouseleave', () => TooltipManager.hide());
	}
});

class Tooltip
{
	constructor()
	{
		this.popup = null;
		this.elements
	}

	show(
		element: HTMLElement,
		bindings: Object = {}
	)
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

	hide()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}
}

const TooltipManager = new Tooltip;