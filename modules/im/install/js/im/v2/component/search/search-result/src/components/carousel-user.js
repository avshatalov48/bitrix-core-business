import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';
import {Avatar, AvatarSize} from 'im.v2.component.elements';
import {SearchContextMenu} from '../classes/search-context-menu';
import '../css/carousel-user.css';
import type {ImModelUser} from 'im.v2.model';
import type {SearchItem} from '../classes/search-item';

// @vue/component
export const CarouselUser = {
	name: 'CarouselUser',
	components: {Avatar},
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
		userId(): number
		{
			return this.searchItem.getId();
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.userId, true);
		},
		name(): string
		{
			return this.user.firstName ? this.user.firstName : this.user.name;
		},
		isExtranet(): boolean
		{
			return this.user.extranet;
		},
		userDialogId(): string
		{
			return this.userId.toString();
		},
	},
	watch:
	{
		isSelected(newValue, oldValue)
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
	created()
	{
		this.contextMenuManager = new SearchContextMenu();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods:
	{
		onClick(event)
		{
			if (this.selectMode)
			{
				this.selected = !this.selected;
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

			const item = {dialogId: this.userDialogId};
			EventEmitter.emit(EventType.search.openContextMenu, {item, nativeEvent: event});
		},
	},
	template: `
		<div 
			class="bx-im-carousel-user__container bx-im-carousel-user__scope"
			:class="{'--extranet': isExtranet, '--selected': selectMode && selected}"
			@click="onClick" 
			@click.right.prevent="onRightClick"
		>
			<div v-if="selectMode && selected" class="bx-im-carousel-user__selected-mark"></div>
			<Avatar :dialogId="userDialogId" :size="AvatarSize.XL" />
			<div class="bx-im-carousel-user__title" :title="user.name">
				{{ name }}
			</div>
		</div>
	`
};