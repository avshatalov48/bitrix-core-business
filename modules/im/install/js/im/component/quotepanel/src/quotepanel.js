/**
 * Bitrix Messenger
 * Textarea Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './quotepanel.css';
import {Vue} from "ui.vue";

Vue.component('bx-messenger-quote-panel',
{
	/**
	 * @emits 'close' {}
	 */

	props:
	{
		id: { default: 0 },
		title: { default: '' },
		description: { default: '' },
		color: { default: '' },
		canClose: { default: true },
	},
	methods:
	{
		close(event)
		{
			this.$emit('close', event);
		},
	},
	computed:
	{
		formattedTittle()
		{
			return this.title? this.title.substr(0, 255): this.localize.IM_QUOTE_PANEL_DEFAULT_TITLE;
		},
		formattedDescription()
		{
			return this.description? this.description.substr(0, 255): '';
		},
		localize()
		{
			return Vue.getFilteredPhrases('IM_QUOTE_PANEL_', this.$root.$bitrixMessages);
		},
	},
	template: `
		<transition enter-active-class="bx-im-quote-panel-animation-show" leave-active-class="bx-im-quote-panel-animation-close">				
			<div v-if="id > 0" class="bx-im-quote-panel">
				<div class="bx-im-quote-panel-wrap">
					<div class="bx-im-quote-panel-box" :style="{borderLeftColor: color}">
						<div class="bx-im-quote-panel-box-title" :style="{color: color}">{{formattedTittle}}</div>
						<div class="bx-im-quote-panel-box-desc">{{formattedDescription}}</div>
					</div>
					<div v-if="canClose" class="bx-im-quote-panel-close" @click="close"></div>
				</div>
			</div>
		</transition>
	`
});