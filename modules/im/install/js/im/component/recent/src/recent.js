/**
 * Bitrix im
 * Recentlist vue component
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2019 Bitrix
 */
import {BitrixVue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {RestMethod, RestMethodHandler, EventType, RecentSection as Section, TemplateTypes} from "im.const";

import {RecentItem} from "./component/recent-item";
import { EventEmitter } from "main.core.events";
import "./recent.css";

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
BitrixVue.component('bx-im-component-recent',
	{
		components: {RecentItem},
		props: {
			hasDialog: false
		},
		data: function()
		{
			return {
				paginationCount: 50,
				loadingMore: false,
				hasMoreToLoad: true,
				placeholderCount: 0,
				lastMessageDate: null
			};
		},
		created()
		{

		},
		mounted()
		{
			this.drawPlaceholders().then(this.getFirstPage);
			this.initObserver();
		},
		computed:
		{
			pinnedItems()
			{
				return this.collection.filter(item => {
					return item.pinned === true;
				});
			},
			generalItems()
			{
				return this.collection.filter(item => {
					return item.pinned === false;
				});
			},
			...Vuex.mapState({
				collection: state => state.recent.collection
			})
		},
		methods:
		{
			/* region 01. Handlers */
			onScroll(event)
			{
				if (this.oneScreenRemaining(event))
				{
					this.loadNextPage();
				}
			},

			onClick(event)
			{
				EventEmitter.emit(EventType.dialog.open, event);
			},

			onRightClick(event)
			{
				this.openOldContextMenu(event);
			},
			/* endregion 01. Handlers */

			/* region 02. Ex-controller */
			generatePlaceholders(amount)
			{
				let placeholders = [];

				for (let i = 0; i < amount; i++)
				{
					placeholders.push({
						id: 'placeholder' + this.placeholderCount,
						templateId: 'placeholder' + this.placeholderCount,
						template: TemplateTypes.placeholder,
						sectionCode: Section.general
					});
					this.placeholderCount++;
				}

				return placeholders;
			},

			drawPlaceholders()
			{
				let placeholders = this.generatePlaceholders(this.paginationCount);

				return this.$store.dispatch('recent/addPlaceholders', placeholders);
			},

			getFirstPage()
			{
				let queryParams = {
					'SKIP_OPENLINES': 'Y',
					'LIMIT': this.paginationCount
				};

				this.getRestClient().callMethod(RestMethod.imRecentList, queryParams).then(result => {
					//save last message date to load next items starting from it
					this.lastMessageDate = this.getLastMessageDate(result.data().items);

					//if we got less items than page count = no more items
					if (!result.data().hasMore)
					{
						this.hasMoreToLoad = false;
					}

					this.$store.dispatch('recent/clearPlaceholders');
					//set first chunk of real data in rest handler
					this.getController().executeRestAnswer(RestMethodHandler.imRecentList, result);
				})
			},

			loadNextPage()
			{
				if (this.loadingMore || !this.hasMoreToLoad)
				{
					return false;
				}

				this.loadingMore = true;
				//get first placeholder which we need to update with new data
				this.firstPlaceholderToUpdate = this.placeholderCount;

				//draw new placeholders and get next items from backend
				this.drawPlaceholders().then(() => {this.getNextPage()});
			},

			getNextPage()
			{
				let queryParams = {
					'SKIP_OPENLINES': 'Y',
					'LIMIT': this.paginationCount,
					'LAST_MESSAGE_DATE': this.lastMessageDate
				};
				this.getRestClient().callMethod(RestMethod.imRecentList, queryParams).then(result =>
				{
					let items = result.data().items;
					//if we got nothing - clear placeholders
					if (!items || items.length === 0)
					{
						this.$store.dispatch('recent/clearPlaceholders');

						return false;
					}
					//if we got less items than page count = there are no more items
					if (!result.data().hasMore)
					{
						this.hasMoreToLoad = false;
					}
					//save last message date to load next items starting from it
					this.lastMessageDate = this.getLastMessageDate(items);
					this.updateModels(items)
						.then(() => {
							this.loadingMore = false;
							//if we got less items than page count - clear remaining placeholders
							if (!this.hasMoreToLoad)
							{
								this.$store.dispatch('recent/clearPlaceholders');
							}
						});
				});
			},

			getLastMessageDate(collection)
			{
				return collection.slice(-1)[0].message.date;
			},

			updateModels(items)
			{
				let data = this.prepareDataForModels(items);

				const usersPromise = this.$store.dispatch('users/set', data.users);
				const dialoguesPromise = this.$store.dispatch('dialogues/set', data.dialogues);
				const recentPromise = this.$store.dispatch('recent/updatePlaceholders',
					{
						items: data.recent,
						firstMessage: this.firstPlaceholderToUpdate
					})

				return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
			},

			prepareDataForModels(items)
			{
				let result = {
					users: [],
					dialogues: [],
					recent: []
				};

				items.forEach(item => {
					let userId = 0;
					let chatId = 0;

					if (item.user && item.user.id > 0)
					{
						userId = item.user.id;
						result.users.push(item.user);
					}
					if (item.chat)
					{
						chatId = item.chat.id;
						result.dialogues.push(Object.assign(item.chat, {dialogId: item.id}));
					}
					else
					{
						result.dialogues.push(Object.assign({}, {dialogId: item.id}));
					}
					result.recent.push({
						...item,
						avatar: item.avatar.url,
						color: item.avatar.color,
						userId: userId,
						chatId: chatId
					});
				});

				return result;
			},
			/* endregion 02. Ex-controller */

			/* region 03. Actions */
			openOldDialog(event)
			{
				if (event.id !== 'notify')
				{
					BXIM.openMessenger(event.id);
				}
				else
				{
					BXIM.openNotify();
				}
			},

			openOldContextMenu(event)
			{
				event.$event.preventDefault();
				const recentItem = this.$store.getters['recent/get'](event.id);
				if (!recentItem)
				{
					return false;
				}

				const params = {
					userId: event.id,
					userIsChat: event.id.toString().startsWith('chat'),
					dialogIsPinned: recentItem.element.pinned
				};
				BXIM.messenger.openPopupMenu(event.$event.target, 'contactList', undefined, params);
			},
			/* endregion 03. Actions */

			/* region 04. Helpers */
			getController()
			{
				return this.$Bitrix.Data.get('controller');
			},

			getRestClient()
			{
				return this.$Bitrix.RestClient.get();
			},

			oneScreenRemaining(event)
			{
				return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
			},

			initObserver()
			{
				this.observer = new IntersectionObserver(function(entries){
					entries.forEach(entry => {
						if (entry.isIntersecting && entry.intersectionRatio === 1)
						{
							// console.warn('I SEE ', entry);
						}
					});
				}, {threshold: [0, 1]});
			},
			/* endregion 04. Helpers */
		},
		directives:
		{
			'recent-list-observer':
				{
					inserted(element, bindings, vnode)
					{
						vnode.context.observer.observe(element);
					}
				}
		},
		// language=Vue
		template: `
			<div class="bx-messenger-recent">
				<div class="bx-messenger-recent-list" @scroll="onScroll">
					<template v-for="item in pinnedItems">
						<recent-item
							:itemData="item"
							:key="item.id"
							:data-id="item.id"
							v-recent-list-observer
							@click="onClick"
							@rightClick="onRightClick"
						/>
					</template>
					<template v-for="item in generalItems">
						<recent-item
							:itemData="item"
							:key="item.id"
							:data-id="item.id"
							v-recent-list-observer
							@click="onClick"
							@rightClick="onRightClick"
						/>
					</template>
				</div>
			</div>
	`
	});
