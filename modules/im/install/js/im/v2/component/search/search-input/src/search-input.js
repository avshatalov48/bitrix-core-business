import {EventEmitter} from 'main.core.events';

import {EventType} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import './css/search-input.css';

// @vue/component
export const SearchInput = {
	props: {
		searchMode: {
			type: Boolean,
			required: true
		}
	},
	emits: ['closeSearch', 'openSearch', 'updateSearch'],
	data()
	{
		return {
			isActive: false,
			searchQuery: ''
		};
	},
	computed:
	{
		isEmptyQuery(): boolean
		{
			return this.searchQuery.length === 0;
		}
	},
	watch:
	{
		searchMode(newValue: boolean, oldValue: boolean)
		{
			if (newValue === true && oldValue === false)
			{
				this.focus();
			}
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.search.close, this.onCloseClick);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.search.close, this.onCloseClick);
	},
	methods:
	{
		onInputClick()
		{
			this.isActive = true;
			this.$emit('openSearch');
		},
		onCloseClick()
		{
			this.isActive = false;
			this.searchQuery = '';
			this.$emit('closeSearch');
		},
		onClearInput()
		{
			this.isActive = true;
			this.searchQuery = '';
			this.$emit('updateSearch', this.searchQuery);
		},
		onInputUpdate()
		{
			this.isActive = true;
			this.$emit('updateSearch', this.searchQuery);
		},
		onKeyUp(event: KeyboardEvent)
		{
			if (Utils.key.isCombination(event, 'Escape'))
			{
				this.onEscapePressed();

				return;
			}

			EventEmitter.emit(EventType.search.keyPressed, {keyboardEvent: event});
		},
		onEscapePressed()
		{
			if (this.isEmptyQuery)
			{
				this.onCloseClick();
				this.$refs['searchInput'].blur();
			}
			else
			{
				this.onClearInput();
			}
		},
		focus()
		{
			this.isActive = true;
			this.$refs.searchInput.focus();
		}
	},
	template: `
		<div class="bx-im-search-input__scope bx-im-search-input__container">
			<div class="bx-im-search-input__search-icon"></div>
			<input
				@click="onInputClick"
				@input="onInputUpdate"
				@keyup="onKeyUp"
				v-model="searchQuery"
				class="bx-im-search-input__element"
				:placeholder="$Bitrix.Loc.getMessage('IM_SEARCH_INPUT_PLACEHOLDER')"
				ref="searchInput"
			/>
			<div v-if="isActive" @click="onCloseClick" class="bx-im-search-input__close-icon"></div>
		</div>
	`
};