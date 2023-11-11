import { Extension, Runtime, Type, type JsonObject } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { EventType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { SearchService } from 'im.v2.provider.service';
import { Loader } from 'im.v2.component.elements';

import { MentionItem } from './mention-item';
import { MentionEmptyState } from './mention-empty-state';

import '../css/mention-popup-content.css';

import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const MentionPopupContent = {
	name: 'MentionPopupContent',
	components: { MentionItem, Loader, MentionEmptyState },
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
			recentChats: [],
			searchResult: [],
			currentServerQueries: 0,
			needTopShadow: false,
			needBottomShadow: true,
		};
	},
	computed:
	{
		itemsToShow(): string[]
		{
			return this.preparedQuery.length > 0 ? this.searchResult : this.recentChats;
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
		async recentChats()
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

			this.startSearch(newQuery);
		},
	},
	created()
	{
		this.initSettings();
		this.searchService = new SearchService();
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);

		this.requestChatParticipants();
		EventEmitter.subscribe(EventType.mention.selectFirstItem, this.onInsertFirstItem);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.mention.selectFirstItem, this.onInsertFirstItem);
	},
	methods:
	{
		requestChatParticipants()
		{
			this.isLoading = true;
			this.searchService.loadChatParticipants(this.dialogId).then((dialogIds: string[]) => {
				this.recentChats = dialogIds;
				this.isLoading = false;
			}).catch((error) => {
				Logger.warn('Mention: loadChatParticipants', error);
			});
		},
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.textarea');
			const defaultMinTokenSize = 3;
			this.minTokenSize = settings.get('minSearchTokenSize', defaultMinTokenSize);
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

				this.searchResult = dialogIds;
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

					this.searchResult = dialogIds;
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
			this.searchResult = [];
		},
		onItemsScroll(event: Event)
		{
			this.needBottomShadow = event.target.scrollTop + event.target.clientHeight !== event.target.scrollHeight;

			if (event.target.scrollTop === 0)
			{
				this.needTopShadow = false;

				return;
			}

			this.needTopShadow = true;
		},
		async adjustPosition()
		{
			await this.$nextTick();
			this.$emit('adjustPosition');
		},
		onInsertFirstItem()
		{
			if (!Type.isArrayFilled(this.itemsToShow))
			{
				return;
			}

			const [firstItem] = this.itemsToShow;
			this.sendInsertMentionEvent(firstItem);
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
				return this.$store.getters['dialogues/get'](dialogId, true).name;
			}

			return this.$store.getters['users/get'](dialogId, true).name;
		},
	},
	template: `
		<div class="bx-im-mention-popup-content__container">
			<div v-if="!isEmptyState && needTopShadow" class="bx-im-mention-popup-content__shadow --top">
				<div class="bx-im-mention-popup-content__shadow-inner"></div>
			</div>
			<div v-if="itemsToShow.length > 0" class="bx-im-mention-popup-content__items" @scroll="onItemsScroll">
				<MentionItem
					v-for="dialogId in itemsToShow"
					:dialogId="dialogId"
					:query="query"
					@itemClick="onItemClick"
				/>
			</div>
			<MentionEmptyState v-if="isEmptyState" />
			<Loader v-if="isLoading" class="bx-im-mention-popup-content__loader" />
			<div v-if="!isEmptyState && needBottomShadow" class="bx-im-mention-popup-content__shadow --bottom">
				<div class="bx-im-mention-popup-content__shadow-inner"></div>
			</div>
		</div>
	`,
};
