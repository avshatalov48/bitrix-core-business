import { Runtime } from 'main.core';

import { SendingService } from 'im.v2.provider.service';
import { Loader, Spinner, SpinnerSize, SpinnerColor } from 'im.v2.component.elements';
import { GifService, GifItem } from '../../classes/gif-service';

import '../../../../css/smile-selector/tabs/tab-smiles.css';
import '../../../../css/smile-selector/tabs/tab-giphy.css';

type GiphyConfig = {
	searchQuery: string;
	gifList: GifItem[],
	popularGifList: GifItem[],
	isSearching: boolean,
	isError: boolean,
};

const UrlTag = Object.freeze({
	open: '[url]',
	close: '[/url]',
});

// @vue/component
export const TabGiphy = {
	name: 'GiphyContent',
	components: { Loader, Spinner },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
	data(): GiphyConfig
	{
		return {
			searchQuery: '',
			gifList: [],
			popularGifList: [],
			isSearching: false,
			isLoading: false,
			isError: false,
			needBottomShadow: true,
		};
	},
	computed:
	{
		itemsReceived(): boolean
		{
			return this.popularGifList.length > 0;
		},
		SpinnerSize: () => SpinnerSize,
		SpinnerColor: () => SpinnerColor,
		errorText(): string
		{
			if (this.gifList.length === 0)
			{
				return this.loc('IM_TEXTAREA_GIPHY_EMPTY_STATE');
			}

			if (this.isError)
			{
				return this.loc('IM_TEXTAREA_GIPHY_UNAVAILABLE_STATE');
			}

			return '';
		},
		errorClass(): string
		{
			return this.gifList.length === 0 || this.isError ? '--is-error' : '';
		},
		showInputClearButton(): boolean
		{
			return this.searchQuery.length > 0 && !this.isSearching;
		},
		trimmedQuery(): string
		{
			return this.searchQuery.trim();
		},
	},
	created()
	{
		this.loadPopular();
		this.loadQueryWithDebounce = Runtime.debounce(this.loadQueryList, 500, this);
	},
	methods:
	{
		handleResponse(gifs: GifItem[])
		{
			this.isSearching = false;
			this.scrollToTop();
			this.gifList = gifs.length > 0 ? gifs : [];
		},
		loadPopular()
		{
			this.getGifService().getPopular()
				.then((gifs: GifItem[]) => {
					this.popularGifList = gifs.length > 0 ? gifs : [];
					this.handleResponse(this.popularGifList);
				})
				.catch(() => {
					this.isError = true;
				});
		},
		loadQueryList(query: string, nextPage: boolean)
		{
			this.getGifService().getQuery(query, nextPage)
				.then((gifs: GifItem[]) => {
					this.handleResponse(gifs);
				})
				.catch(() => {
					this.isError = true;
				});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		getGifService(): GifService
		{
			if (!this.giphyLoader)
			{
				this.giphyLoader = new GifService();
			}

			return this.giphyLoader;
		},
		getSendingService(): SendingService
		{
			if (!this.sendingService)
			{
				this.sendingService = SendingService.getInstance();
			}

			return this.sendingService;
		},
		onGifClick(item: GifItem)
		{
			const text = `${UrlTag.open}${item.original}${UrlTag.close}`;
			this.getSendingService().sendMessage({ text, dialogId: this.dialogId });
			this.$emit('close');
		},
		onInputUpdate()
		{
			if (this.trimmedQuery.length >= 3)
			{
				this.isSearching = true;
				this.loadQueryWithDebounce(this.trimmedQuery, false);
			}

			if (this.trimmedQuery.length === 0)
			{
				this.gifList = this.popularGifList;
			}
		},
		onInputClearClick()
		{
			this.searchQuery = '';
			this.scrollToTop();
			this.onInputUpdate();
		},
		onEnterKeyPress()
		{
			if (this.gifList.length > 0 && !this.isSearching)
			{
				const firstGif = this.gifList[0];
				this.onGifClick(firstGif);
			}
		},
		needToLoadNextPage(event): boolean
		{
			return event.target.scrollTop + event.target.clientHeight
				>= event.target.scrollHeight - event.target.clientHeight;
		},
		onScroll(event)
		{
			this.needBottomShadow = event.target.scrollTop + event.target.clientHeight !== event.target.scrollHeight;
			if (this.isLoading)
			{
				return;
			}

			if (this.trimmedQuery.length === 0)
			{
				return;
			}

			if (!this.needToLoadNextPage(event) || !this.getGifService().hasMoreItemsToLoad)
			{
				return;
			}
			this.isLoading = true;
			this.getGifService().getQuery(this.trimmedQuery, true)
				.then((gifs) => {
					this.isLoading = false;
					this.gifList.push(...gifs);
				})
				.catch(() => {
					this.isLoading = false;
					this.isError = true;
				});
		},
		scrollToTop()
		{
			const scrollContainer = this.$refs.gifsContainer;
			if (scrollContainer)
			{
				scrollContainer.scrollTop = 0;
			}
		},
		openHelpArticle()
		{
			const ARTICLE_CODE = '17942324';
			BX.Helper?.show(`redirect=detail&code=${ARTICLE_CODE}`);
		},
	},
	template: `
		<div class="bx-im-smiles-content__scope bx-im-smile-popup-giphy-content__container">
			<div 
				v-if="!itemsReceived" 
				class="bx-im-smiles-content-popup__loader"
			>
				<Spinner :color="SpinnerColor.blue" :size="SpinnerSize.S" />
			</div>
			<template v-else>
				<div 
					v-if="itemsReceived"
				 	class="bx-im-smile-popup-search-input__container"
				>
					<div class="bx-im-smile-popup-giphy-content__search-icon"></div>
					<input
						@input="onInputUpdate"
						@keydown.enter="onEnterKeyPress"
						v-model="searchQuery"
						class="bx-im-smile-popup-giphy-content__input bx-im-smile-popup-search-input__element"
						:placeholder="loc('IM_TEXTAREA_GIPHY_INPUT_PLACEHOLDER')"
					/>
					<div
						v-if="showInputClearButton"
						class="bx-im-smile-popup-search-input__clear"
						@click="onInputClearClick"
					 ></div>
					<div v-show="isSearching" class="bx-im-smile-popup-search-input__loader">
						<Spinner :color="SpinnerColor.grey" :size="SpinnerSize.XXS" />
					</div>
				</div>
				<div 
					class="bx-im-smiles-content__smiles-box bx-im-smiles-content__gifs-box"
				 	:class="errorClass"
				 	ref="gifsContainer"
					@scroll="onScroll"
				>
					<div 
						v-if="gifList.length === 0" 
						class="bx-im-smiles-content__gifs-empty"
					>
						<div class="bx-im-smiles-content__gifs-empty_icon bx-im-messenger__search-icon --size-large"></div>
						<div class="bx-im-smiles-content__gifs-empty_title">
							{{ errorText }}
						</div>
					</div>
					<div 
						v-else-if="isError" 
						class="bx-im-smiles-content__gifs-empty"
					>
						<div 
							v-if="isError" 
							class="bx-im-smiles-content__gifs-warning_icon"
						></div>
						<div class="bx-im-smiles-content__gifs-empty_title">
							{{ errorText }}
						</div>
						<div @click="openHelpArticle" class="bx-im-smiles-content__gifs-empty_link">
							{{ loc('IM_TEXTAREA_GIPHY_MORE') }}
						</div>
					</div>
					<template v-else>
						<div v-for="item in gifList" class="bx-im-smiles-content__gifs-item" :key="item.preview">
							<img @click="onGifClick(item)" class="bx-im-smiles-content__gifs-item_img"
								 :src="item.preview"
								 :data-original="item.original" alt="gif"
							>
						</div>
					</template>
					<div :class="needBottomShadow ? '' : '--is-hidden'" class="bx-im-smiles-content__gifs-gradient"></div>
					<Loader v-show="isLoading && !isError" class="bx-im-sidebar-detail__loader-container" />
				</div>
			</template>
		</div>
	`,
};
