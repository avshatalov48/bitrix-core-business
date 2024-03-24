import { Extension, Runtime, Type, type JsonObject, Event, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { EventType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { SearchService } from 'im.v2.provider.service';
import { ScrollWithGradient } from 'im.v2.component.elements';

import { MentionItem } from './mention-item';
import { MentionEmptyState } from './mention-empty-state';
import { MentionLoadingState } from './mention-loading-state';
import { MentionContentFooter } from './mention-content-footer';

import '../css/mention-popup-content.css';

// @vue/component
export const MentionPopupContent = {
	name: 'MentionPopupContent',
	components: { MentionItem, MentionContentFooter, MentionEmptyState, ScrollWithGradient, MentionLoadingState },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		query: {
			type: String,
			default: '',
		},
	},
	emits: ['close', 'adjustPosition'],
	data(): JsonObject
	{
		return {
			isLoading: false,
			searchResult: [],
			chatParticipants: [],
			currentServerQueries: 0,
			needTopShadow: false,
			needBottomShadow: true,
			selectedIndex: 0,
			selectedItem: '',
		};
	},
	computed:
	{
		itemsToShow(): string[]
		{
			if (this.preparedQuery.length === 0)
			{
				return this.chatParticipants;
			}

			return this.searchResult;
		},
		preparedQuery(): string
		{
			return this.query.trim().toLowerCase();
		},
		isEmptyState(): boolean
		{
			if (this.isLoading)
			{
				return false;
			}

			return this.searchResult.length === 0 && this.preparedQuery.length > 0;
		},
	},
	watch:
	{
		async isLoading()
		{
			await this.adjustPosition();
		},
		async searchResult()
		{
			await this.adjustPosition();
		},
		preparedQuery(newQuery: string, previousQuery: string)
		{
			if (newQuery === previousQuery)
			{
				return;
			}

			this.selectedIndex = 0;
			this.startSearch(newQuery);
		},
	},
	created()
	{
		this.initSettings();
		this.searchService = new SearchService({ findByParticipants: false });
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);
		void this.loadChatParticipants();

		Event.bind(window, 'keydown', this.onKeyDown);
		EventEmitter.subscribe(EventType.mention.selectItem, this.onInsertItem);
	},
	beforeUnmount()
	{
		Event.unbind(window, 'keydown', this.onKeyDown);
		EventEmitter.unsubscribe(EventType.mention.selectItem, this.onInsertItem);
	},
	methods:
	{
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.textarea');
			const defaultMinTokenSize = 3;
			this.minTokenSize = settings.get('minSearchTokenSize', defaultMinTokenSize);
		},
		async loadChatParticipants()
		{
			this.isLoading = true;
			this.chatParticipants = await this.searchService.loadChatParticipants(this.dialogId);
			this.searchResult = this.chatParticipants;
			this.isLoading = false;
		},
		searchOnServer(query: string)
		{
			this.currentServerQueries++;

			this.searchService.searchOnServer(query).then((dialogIds: string[]) => {
				if (query !== this.preparedQuery)
				{
					this.isLoading = false;

					return;
				}

				this.searchResult = [...new Set([...this.searchResult, ...dialogIds])];
			}).catch((error) => {
				console.error(error);
			}).finally(() => {
				this.currentServerQueries--;
				this.stopLoader();
			});
		},
		startSearch(query: string)
		{
			if (query.length > 0)
			{
				this.searchService.searchLocal(query).then((dialogIds: string[]) => {
					if (query !== this.preparedQuery)
					{
						return;
					}

					this.searchResult = this.appendResult(dialogIds);
				}).catch((error) => {
					Logger.error('Mention: searchLocalOnlyUsers', error);
				});
			}

			if (query.length >= this.minTokenSize)
			{
				this.isLoading = true;
				this.searchOnServerDelayed(query);
			}

			if (query.length === 0)
			{
				this.cleanSearchResult();
			}
		},
		stopLoader()
		{
			if (this.currentServerQueries > 0)
			{
				return;
			}

			this.isLoading = false;
		},
		cleanSearchResult()
		{
			this.searchResult = this.chatParticipants;
		},
		async adjustPosition()
		{
			await this.$nextTick();
			this.$emit('adjustPosition');
		},
		onInsertItem()
		{
			if (!Type.isArrayFilled(this.itemsToShow))
			{
				return;
			}

			this.sendInsertMentionEvent(this.itemsToShow[this.selectedIndex]);
		},
		onItemClick({ dialogId })
		{
			this.sendInsertMentionEvent(dialogId);
			this.$emit('close');
		},
		sendInsertMentionEvent(dialogId)
		{
			const mentionText = this.getMentionText(dialogId);
			const mentionReplacement = Utils.text.getMentionBbCode(dialogId, mentionText);

			EventEmitter.emit(EventType.textarea.insertMention, {
				mentionText,
				mentionReplacement,
				textToReplace: this.query,
			});
		},
		getMentionText(dialogId: string): string
		{
			if (dialogId.startsWith('chat'))
			{
				return this.$store.getters['chats/get'](dialogId, true).name;
			}

			return this.$store.getters['users/get'](dialogId, true).name;
		},
		onKeyDown(event: KeyboardEvent)
		{
			if (event.key === 'ArrowDown')
			{
				this.selectedIndex = this.selectedIndex === this.itemsToShow.length - 1 ? 0 : this.selectedIndex + 1;
			}

			if (event.key === 'ArrowUp')
			{
				this.selectedIndex = this.selectedIndex === 0 ? this.itemsToShow.length - 1 : this.selectedIndex - 1;
			}

			const element = this.getDomElementById(this.selectedIndex);
			if (!element)
			{
				this.selectedIndex = 0;
			}

			this.selectedItem = this.itemsToShow[this.selectedIndex];
			this.scrollToItem(element);
		},
		scrollToItem(element: HTMLElement)
		{
			const scrollContainer = document.querySelector('.bx-im-mention-popup-content__container .bx-im-scroll-with-gradient__content');

			const tabRect = Dom.getPosition(scrollContainer);
			const nodeRect = Dom.getPosition(element);
			const margin = 12; // 'bx-im-mention-popup-content__items' margin

			if (nodeRect.top < tabRect.top) // scroll up
			{
				scrollContainer.scrollTop -= (tabRect.top - nodeRect.top + margin);
			}
			else if (nodeRect.bottom > tabRect.bottom) // scroll down
			{
				scrollContainer.scrollTop += nodeRect.bottom - tabRect.bottom + margin;
			}
		},
		onItemHover(index: number)
		{
			this.selectedIndex = index;
			this.selectedItem = this.itemsToShow[this.selectedIndex];
		},
		getDomElementById(id: number | string): ?HTMLElement
		{
			return this.$refs['mention-content'].querySelector(`[data-index="${id}"]`);
		},
		appendResult(newItems: string[]): string[]
		{
			const filtered = this.searchResult.filter((dialogId) => newItems.includes(dialogId));

			return [...new Set([...filtered, ...newItems])];
		},
	},
	template: `
		<div class="bx-im-mention-popup-content__container" ref="mention-content">
			<ScrollWithGradient 
				v-if="itemsToShow.length > 0" 
				:gradientHeight="13" 
				:containerMaxHeight="226"
				:withShadow="false"
			>
				<div class="bx-im-mention-popup-content__items">
					<MentionItem
						v-for="(dialogId, index) in itemsToShow"
						:data-index="index"
						:dialogId="dialogId"
						:query="query"
						:selected="selectedIndex === index"
						@itemClick="onItemClick"
						@itemHover="onItemHover(index)"
					/>
				</div>
			</ScrollWithGradient>
			<MentionEmptyState v-if="isEmptyState" />
			<MentionLoadingState v-if="isLoading && itemsToShow.length === 0" />
			<MentionContentFooter :isLoading="isLoading" />
		</div>
	`,
};
