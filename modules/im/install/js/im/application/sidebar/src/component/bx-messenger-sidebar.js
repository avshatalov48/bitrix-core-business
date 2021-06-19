/**
 * Bitrix im
 * Sidebar vue component
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2019 Bitrix
 */

import {BitrixVue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
BitrixVue.component('bx-im-component-sidebar',
	{
		data: function()
		{
			return {

			};
		},
		created()
		{

		},
		computed:
			{
				...Vuex.mapState({
					recent: state => state.recent.collection.general,
					pinned: state => state.recent.collection.pinned
				}),

				recentData()
				{
					return [...this.recent, ...this.pinned];
				}
			},
		methods:
			{
				getController()
				{
					return this.$Bitrix.Data.get('controller');
				},

				getStore()
				{
					return this.getController().store;
				},

				onScroll(event)
				{
					if (this.oneScreenRemaining(event))
					{
						this.getController().recent.loadMore();
					}
				},

				onClick(event)
				{
					this.getController().recent.openOldDialog(event);
				},

				onRightClick(event)
				{
					this.getController().recent.openOldContextMenu(event);
				},

				oneScreenRemaining(event)
				{
					return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
				}
			},
		template: `
			<div class="sidebar-wrap">
				<bx-im-view-list-sidebar
					:recentData="recentData"
					@scroll="onScroll"
					@click="onClick"
					@rightClick="onRightClick"
				/>
			</div>
		`
	});