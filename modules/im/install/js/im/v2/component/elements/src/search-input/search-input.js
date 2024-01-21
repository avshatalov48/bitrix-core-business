import { Utils } from 'im.v2.lib.utils';
import { Spinner, SpinnerSize, SpinnerColor } from '../registry';

import './search-input.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const SearchInput = {
	name: 'SearchInput',
	components: {
		Spinner,
	},
	props: {
		placeholder: {
			type: String,
			default: '',
		},
		searchMode: {
			type: Boolean,
			default: true,
		},
		withIcon: {
			type: Boolean,
			default: true,
		},
		withLoader: {
			type: Boolean,
			default: false,
		},
		isLoading: {
			type: Boolean,
			default: false,
		},
		delayForFocusOnStart: {
			type: Number,
			default: 0,
		},
	},
	emits: ['queryChange', 'inputFocus', 'inputBlur', 'keyPressed', 'close'],
	data(): JsonObject
	{
		return {
			query: '',
			hasFocus: false,
		};
	},
	computed:
	{
		SpinnerSize: () => SpinnerSize,
		SpinnerColor: () => SpinnerColor,
		isEmptyQuery(): boolean
		{
			return this.query.length === 0;
		},
	},
	watch:
	{
		searchMode(newValue: boolean)
		{
			if (newValue === true)
			{
				this.focus();
			}
			else
			{
				this.query = '';
				this.blur();
			}
		},
	},
	created()
	{
		if (this.delayForFocusOnStart > 0)
		{
			setTimeout(() => {
				this.focus();
			}, this.delayForFocusOnStart);
		}
	},
	methods:
	{
		onInputUpdate()
		{
			this.$emit('queryChange', this.query);
		},
		onFocus()
		{
			this.focus();
			this.$emit('inputFocus');
		},
		onCloseClick()
		{
			this.query = '';
			this.hasFocus = false;
			this.$emit('queryChange', this.query);
			this.$emit('close');
		},
		onClearInput()
		{
			this.query = '';
			this.focus();
			this.$emit('queryChange', this.query);
		},
		onKeyUp(event: KeyboardEvent)
		{
			if (Utils.key.isCombination(event, 'Escape'))
			{
				this.onEscapePressed();

				return;
			}

			this.$emit('keyPressed', event);
		},
		onEscapePressed()
		{
			if (this.isEmptyQuery)
			{
				this.onCloseClick();
				this.blur();
			}
			else
			{
				this.onClearInput();
			}
		},
		focus()
		{
			this.hasFocus = true;
			this.$refs.searchInput.focus();
		},
		blur()
		{
			this.hasFocus = false;
			this.$refs.searchInput.blur();
		},
	},
	template: `
		<div class="bx-im-search-input__scope bx-im-search-input__container" :class="{'--has-focus': hasFocus}">
			<div v-if="!isLoading" class="bx-im-search-input__search-icon"></div>
			<Spinner 
				v-if="withLoader && isLoading" 
				:size="SpinnerSize.XXS" 
				:color="SpinnerColor.grey" 
				class="bx-im-search-input__loader"
			/>
			<input
				@focus="onFocus"
				@input="onInputUpdate"
				@keyup="onKeyUp"
				v-model="query"
				class="bx-im-search-input__element"
				:class="{'--with-icon': withIcon}"
				:placeholder="placeholder"
				ref="searchInput"
			/>
			<div v-if="hasFocus" class="bx-im-search-input__close-icon" @click="onCloseClick"></div>
		</div>
	`,
};
