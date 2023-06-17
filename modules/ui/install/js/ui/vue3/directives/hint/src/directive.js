/**
 * Hint Vue directive
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2022 Bitrix
 */

/*
	<span v-hint="$Bitrix.Loc.getMessage('HINT_HTML')" data-hint-html>Html code</span>
	<span v-hint="{text: 'Text node'}">Plain text</span>
	<span v-hint="{html: '<b>Html</b> code'}">Html code</span>
	<span v-hint="{text: 'Custom position top and light mode', position: 'top', popupOptions: {darkMode: false}}">Text top on light panel</span>
*/

import {Tooltip} from './tooltip';
import {Event} from 'main.core';
import 'ui.hint';

export const hint = {
	beforeMount(element: HTMLElement, bindings): void
	{
		if (!bindings.value)
		{
			return;
		}

		Event.bind(element, 'mouseenter', () => Tooltip.show(element, bindings));
		Event.bind(element, 'mouseleave', () => Tooltip.hide());
	}
};