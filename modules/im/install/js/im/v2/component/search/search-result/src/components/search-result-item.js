import {EventEmitter} from 'main.core.events';
import {DialogType, EventType} from 'im.v2.const';
import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import '../css/search-result-item.css';
import type {ImModelDialog, ImModelUser} from 'im.v2.model';
import type {SearchItem} from '../classes/search-item';

// @vue/component
export const SearchResultItem = {
	name: 'SearchResultItem',
	components: {Avatar, ChatTitle},
	inject: ['searchService'],
	props: {
		item: {
			type: Object,
			required: true
		},
		selectMode: {
			type: Boolean,
			default: false
		},
		isSelected: {
			type: Boolean,
			required: false,
		}
	},
	emits: ['clickItem'],
	data() {
		return {
			selected: this.isSelected,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		searchItem(): SearchItem
		{
			return this.item;
		},
		dialogId(): string
		{
			return this.searchItem.getDialogId();
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		userItemText(): string
		{
			if (!this.isUser)
			{
				return '';
			}

			const status = this.$store.getters['users/getLastOnline'](this.dialogId);
			if (status)
			{
				return status;
			}

			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		chatItemText(): string
		{
			if (this.isUser)
			{
				return '';
			}

			return this.$Bitrix.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_GROUP_V2');
		},
		itemText(): string
		{
			return this.isUser ? this.userItemText : this.chatItemText;
		},
		selectedStyles()
		{
			return {
				'--selected': this.selectMode && this.selected
			};
		}
	},
	watch:
	{
		isSelected(newValue: boolean, oldValue: boolean)
		{
			if (newValue === true && oldValue === false)
			{
				this.selected = true;
			}
			else if (newValue === false && oldValue === true)
			{
				this.selected = false;
			}
		}
	},
	methods:
	{
		onClick(event)
		{
			if (this.selectMode)
			{
				this.selected = !this.selected;
			}
			else
			{
				this.searchService.addItemToRecent(this.searchItem);
			}

			this.$emit('clickItem', {
				selectedItem: this.searchItem,
				selectedStatus: this.selected,
				nativeEvent: event
			});
		},
		onRightClick(event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			const item = {dialogId: this.dialogId};
			EventEmitter.emit(EventType.search.openContextMenu, {item, nativeEvent: event});
		},
	},
	template: `
		<div 
			@click="onClick" 
			@click.right.prevent="onRightClick" 
			class="bx-im-search-result-item__container bx-im-search-result-item__scope"
			:class="selectedStyles"
		>
			<div class="bx-im-search-result-item__avatar-container">
				<Avatar :dialogId="dialogId" :size="AvatarSize.XL" />
			</div>
			<div class="bx-im-search-result-item__content-container">
				<ChatTitle :dialogId="dialogId" />
				<div class="bx-im-search-result-item__item-text" :title="itemText">
					{{ itemText }}
				</div>
			</div>
			<div v-if="selectMode && selected" class="bx-im-search-result-item__selected"></div>
		</div>
	`
};