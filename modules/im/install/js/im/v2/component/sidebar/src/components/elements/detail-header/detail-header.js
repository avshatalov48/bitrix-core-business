import { SearchInput, Button as ChatButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';
import { ChatType, Layout } from 'im.v2.const';

import './detail-header.css';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const DetailHeader = {
	name: 'DetailHeader',
	components: { ChatButton, SearchInput },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
		withAddButton: {
			type: Boolean,
			default: false,
		},
		withSearch: {
			type: Boolean,
			default: false,
		},
		isSearchHeaderOpened: {
			type: Boolean,
			default: false,
		},
		delayForFocusOnStart: {
			type: Number || null,
			default: null,
		},
	},
	emits: ['back', 'addClick', 'changeQuery', 'toggleSearchPanelOpened'],
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		isCopilotLayout(): boolean
		{
			const { name: currentLayoutName } = this.$store.getters['application/getLayout'];

			return currentLayoutName === Layout.copilot.name;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isCollab(): boolean
		{
			return this.dialog.type === ChatType.collab;
		},
		addButtonColor(): ButtonColor
		{
			if (this.isCopilotLayout)
			{
				return this.ButtonColor.Copilot;
			}

			if (this.isCollab)
			{
				return this.ButtonColor.Collab;
			}

			return this.ButtonColor.PrimaryLight;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-detail-header__container bx-im-sidebar-detail-header__scope">
			<div class="bx-im-sidebar-detail-header__title-container">
				<button
					:class="{'bx-im-messenger__cross-icon': !secondLevel, 'bx-im-sidebar__back-icon': secondLevel}"
					@click="$emit('back')"
				/>
				<div v-if="!isSearchHeaderOpened" class="bx-im-sidebar-detail-header__title-text">{{ title }}</div>
				<slot name="action">
					<div v-if="withAddButton && !isSearchHeaderOpened" class="bx-im-sidebar-detail-header__add-button" ref="add-button">
						<ChatButton
							:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="addButtonColor"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="$emit('addClick', {target: $refs['add-button']})"
						/>
					</div>
				</slot>
				<div v-if="withSearch" class="bx-im-sidebar-detail-header__search">
					<SearchInput
						v-if="isSearchHeaderOpened"
						:placeholder="loc('IM_SIDEBAR_SEARCH_MESSAGE_PLACEHOLDER')"
						:withIcon="false"
						:delayForFocusOnStart="delayForFocusOnStart"
						@queryChange="$emit('changeQuery', $event)"
						@close="$emit('toggleSearchPanelOpened', $event)"
						class="bx-im-sidebar-search-header__input"
					/>
					<div v-else @click="$emit('toggleSearchPanelOpened', $event)" class="bx-im-sidebar-detail-header__search__icon --search"></div>
				</div>
			</div>
		</div>
	`,
};
