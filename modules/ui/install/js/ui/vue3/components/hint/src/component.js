/**
 * Hint Vue directive
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

import {hint} from "ui.vue3.directives.hint";

/*
	<Hint :text="$Bitrix.Loc.getMessage('HINT_PLAIN')"/>
	<Hint :html="$Bitrix.Loc.getMessage('HINT_PLAIN')"/>
	<Hint text="Custom position top and light mode" position="top" :popupOptions="{darkMode: false}"/>
*/

export const Hint = {
	props:
	{
		text: { default: '' },
		html: { default: '' },
		position: { default: 'bottom' },
		popupOptions:
		{
			default() {
				return {}
			}
		},
	},
	directives: {
		hint
	},
	template: `
		<span class="ui-hint" v-hint="{text, html, position, popupOptions}" data-hint-init="vue">
			<span class="ui-hint-icon"/>
		</span>
	`
};