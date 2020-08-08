/**
 * Bitrix im
 * Sidebar vue component
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2019 Bitrix
 */

import {Vue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import "im.view.list.recent";
import {Logger} from "im.lib.logger";
import {EventType} from "im.const";
import {Utils} from "im.lib.utils";

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
Vue.component('bx-im-component-recent',
	{
		props: {
			hasDialog: false
		},
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
					return this.$root.$bitrixController;
				},

				getStore()
				{
					return this.$root.$bitrixController.store;
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
					if (this.hasDialog)
					{
						Vue.event.$emit('openMessenger', event);
					}
					else
					{
						this.getController().recent.openOldDialog(event);
					}
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
		<div class="recent-wrap">
			<bx-im-view-list-recent
				:recentData="recentData"
				@scroll="onScroll"
				@click="onClick"
				@rightClick="onRightClick"
			/>
		</div>
	`
	});
