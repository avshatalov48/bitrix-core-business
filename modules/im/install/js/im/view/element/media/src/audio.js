/**
 * Bitrix Messenger
 * File element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './audio.css';
import "ui.vue.components.audioplayer";

import {Vue} from 'ui.vue';
import {MessageType} from 'im.const';

Vue.cloneComponent('bx-im-view-element-file-audio', 'bx-im-view-element-file',
{
	computed:
	{
		background()
		{
			return this.messageType === MessageType.self? 'dark': 'light';
		},
	},
	template: `
		<div :class="['bx-im-element-file-audio', 'bx-im-element-file-audio-'+messageType]" ref="container">
			<bx-audioplayer :id="file.id" :src="file.urlShow" :background="background"/>
		</div>	
	`
});