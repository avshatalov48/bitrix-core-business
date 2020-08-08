/**
 * Bitrix UI
 * Recent list Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import "./recent.css";

import 'ui.vue.components.list';
import 'im.view.list.item.recent';

import {Vue} from 'ui.vue';
import {Logger} from "im.lib.logger";

Vue.cloneComponent('bx-im-view-list-recent', 'bx-list',
{
	props: [
		'recentData'
	],
	data()
	{
		return Object.assign(this.parentData(), {
			cssPrefix: 'bx-messenger-list-recent',
			elementComponent: 'bx-im-view-list-item-recent',
			showSectionNames: false
		});
	},
	created()
	{
		this.parentCreated();
	},
	computed:
	{
		list()
		{
			return this.recentData;
		},
		sections()
		{
			return ['pinned', 'general'];
		}
	},
	methods:
	{
		onClick(event, id)
		{
			this.$emit('click', {id: id, $event: event});
		},

		onRightClick(event, id)
		{
			this.$emit('rightClick', {id: id, $event: event});
		},

		onScroll(event)
		{
			this.$emit('scroll', event);
		}
	}
});
