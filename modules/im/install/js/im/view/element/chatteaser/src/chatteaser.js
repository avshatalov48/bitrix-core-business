/**
 * Bitrix Messenger
 * ChatTeaser element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './chatteaser.css';
import {Vue} from 'ui.vue';
import {Utils} from "im.lib.utils";

Vue.component('bx-im-view-element-chat-teaser',
{
	/*
	 * @emits 'click' {}
	 */
	props:
	{
		messageCounter: {default: 0},
		messageLastDate: {default: 0},
		languageId: {default: 'en'},
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('IM_MESSENGER_COMMENT_', this.$root.$bitrixMessages);
		},
		formattedDate()
		{
			return Utils.date.format(this.messageLastDate, null, this.$root.$bitrixMessages);
		},
		formattedCounter()
		{
			return this.messageCounter+' '+Utils.text.getLocalizeForNumber('IM_MESSENGER_COMMENT', this.messageCounter, this.languageId, this.$root.$bitrixMessages);
		},
	},
	template: `
		<div class="bx-im-element-chat-teaser" @click="$emit('click', $event)">
			<span class="bx-im-element-chat-teaser-join">{{localize.IM_MESSENGER_COMMENT_OPEN}}</span>
			<span class="bx-im-element-chat-teaser-comment">
				<span class="bx-im-element-chat-teaser-counter">{{formattedCounter}}</span>, {{formattedDate}}
			</span>
		</div>
	`
});