import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { SearchInput } from 'im.v2.component.elements';

// @vue/component
export const ChatSearchInput = {
	name: 'ChatSearchInput',
	components: { SearchInput },
	props: {
		searchMode: {
			type: Boolean,
			required: true,
		},
		isLoading: {
			type: Boolean,
			required: false,
		},
		delayForFocusOnStart: {
			type: Number,
			default: 0,
		},
		withIcon: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['closeSearch', 'openSearch', 'updateSearch'],
	created()
	{
		EventEmitter.subscribe(EventType.search.close, this.onClose);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.search.close, this.onClose);
	},
	methods:
	{
		onInputFocus()
		{
			this.$emit('openSearch');
		},
		onClose()
		{
			this.$emit('closeSearch');
		},
		onInputUpdate(query: string)
		{
			this.$emit('updateSearch', query);
		},
		onKeyPressed(event: KeyboardEvent)
		{
			EventEmitter.emit(EventType.search.keyPressed, { keyboardEvent: event });
		},
	},
	template: `
		<SearchInput
			:placeholder="$Bitrix.Loc.getMessage('IM_SEARCH_INPUT_PLACEHOLDER_V2')"
			:searchMode="searchMode"
			:isLoading="isLoading"
			:withLoader="true"
			:delayForFocusOnStart="delayForFocusOnStart"
			:withIcon="withIcon"
			@inputFocus="onInputFocus"
			@inputBlur="onClose"
			@queryChange="onInputUpdate"
			@keyPressed="onKeyPressed"
			@close="onClose"
		/>
	`,
};
