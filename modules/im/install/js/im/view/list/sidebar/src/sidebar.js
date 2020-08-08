/**
 * Bitrix IM
 * Sidebar Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./sidebar.css";

import 'ui.vue.components.list';
import 'im.view.list.item.sidebar';

import {Vue} from 'ui.vue';
import {Logger} from 'im.lib.logger';

Vue.cloneComponent('bx-im-view-list-sidebar', 'bx-list',
{
	props: ['recentData'],
	data()
	{
		return Object.assign(this.parentData(), {
			cssPrefix: 'bx-messenger-list-sidebar',
			elementComponent: 'bx-im-view-list-item-sidebar',
			showSectionNames: false
		});
	},
	created()
	{
		this.parentCreated();
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
		}
});