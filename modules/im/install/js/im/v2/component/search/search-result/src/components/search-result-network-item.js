import {Text, Type} from 'main.core';
import {Loader} from 'im.v2.component.elements';
import '../css/search-result-network-item.css';
import type {SearchItem} from '../classes/search-item';
import {SearchUtils} from '../classes/search-utils';

// @vue/component
export const SearchResultNetworkItem = {
	name: 'SearchResultNetworkItem',
	components: {Loader},
	inject: ['searchService'],
	props: {
		item: {
			type: Object,
			required: true
		},
	},
	emits: ['clickItem'],
	data: function() {
		return {
			isLoading: false
		};
	},
	computed:
	{
		searchItem(): SearchItem
		{
			return this.item;
		},
		hasAvatar(): boolean
		{
			return Type.isStringFilled(this.searchItem.getAvatar());
		},
		avatarStyles(): {[string]: string}
		{
			if (!this.hasAvatar)
			{
				return {
					backgroundSize: '37px',
					backgroundPosition: 'center 8px',
					backgroundColor: this.searchItem.getAvatarOptions().color
				};
			}

			return {backgroundImage: `url('${this.searchItem.getAvatar()}')`};
		},
		title(): string
		{
			return Text.decode(this.searchItem.getTitle());
		}
	},
	methods:
	{
		onClick(event)
		{
			this.isLoading = true;
			const networkCode = this.searchItem.getId();

			this.searchService.loadNetworkItem(networkCode).then(response => {
				const searchItem = SearchUtils.getFirstItemFromMap(response);
				this.$emit('clickItem', {
					selectedItem: searchItem,
					nativeEvent: event
				});
			}).catch(error => {
				console.error(error);
			}).finally(() => {
				this.isLoading = false;
			});
		},
	},
	template: `
		<div
			class="bx-im-search-result-network-item__container bx-im-search-result-network-item__scope"
			@click="onClick"
		>
			<div class="bx-im-search-result-network-item__avatar-container">
				<div
					:title="searchItem.title"
					class="bx-im-search-result-network-item__avatar"
					:style="avatarStyles"
				></div>
			</div>
			<div class="bx-im-search-result-network-item__content-container">
				<div class="bx-im-search-result-network-item__title-text" :title="title">
					{{title}}
				</div>
				<div class="bx-im-search-result-network-item__item-text" :title="searchItem.getSubtitle()">
					{{ searchItem.getSubtitle() }}
				</div>
				<div v-if="isLoading" class="bx-im-search-result-network-item__loader">
					<Loader />
				</div>
			</div>
		</div>
	`
};