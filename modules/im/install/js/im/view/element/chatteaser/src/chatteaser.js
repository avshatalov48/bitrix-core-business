/**
 * Bitrix Messenger
 * ChatTeaser element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './chatteaser.css';
import {BitrixVue} from 'ui.vue';
import {Utils} from "im.lib.utils";

BitrixVue.component('bx-im-view-element-chat-teaser',
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
		formattedDate()
		{
			return Utils.date.format(this.messageLastDate, null, this.$Bitrix.Loc.getMessages());
		},
		formattedCounter()
		{
			return this.messageCounter+' '+Utils.text.getLocalizeForNumber('IM_MESSENGER_COMMENT', this.messageCounter, this.languageId, this.$Bitrix.Loc.getMessages());
		},
	},
	template: `
		<div class="bx-im-element-chat-teaser" @click="$emit('click', $event)">
			<span class="bx-im-element-chat-teaser-join">{{$Bitrix.Loc.getMessage('IM_MESSENGER_COMMENT_OPEN')}}</span>
			<span class="bx-im-element-chat-teaser-comment">
				<span class="bx-im-element-chat-teaser-counter">{{formattedCounter}}</span>, {{formattedDate}}
			</span>
		</div>
	`
});