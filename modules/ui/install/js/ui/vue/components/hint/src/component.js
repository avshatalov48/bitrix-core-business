/**
 * Hint Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

import "./directive";

/*
	<bx-hint :text="$Bitrix.Loc.getMessage('HINT_PLAIN')"/>
	<bx-hint :html="$Bitrix.Loc.getMessage('HINT_PLAIN')"/>
	<bx-hint text="Custom position top and light mode" position="top" :popupOptions="{darkMode: false}"/>
*/

import {BitrixVue} from 'ui.vue';

BitrixVue.component('bx-hint',
{
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
	template: `
		<span class="ui-hint" v-bx-hint="{text, html, position, popupOptions}" data-hint-init="vue">
			<span class="ui-hint-icon"/>
		</span>
	`
});