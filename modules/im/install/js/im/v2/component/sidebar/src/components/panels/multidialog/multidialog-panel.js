import { Messenger } from 'im.public';
import { EventType, SidebarDetailBlock, MultidialogStatus } from 'im.v2.const';
import { ButtonColor, ButtonSize, Loader, Button as ChatButton } from 'im.v2.component.elements';
import { EventEmitter } from 'main.core.events';

import { Multidialog } from '../../../classes/panels/multidialog';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { MultidialogItem } from './multidialog-item';

import './css/multidialog-panel.css';

import type { ImModelSidebarMultidialogItem } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const MultidialogPanel = {
	name: 'MultidialogPanel',
	components: {
		DetailHeader,
		MultidialogItem,
		ChatButton,
		Loader,
	},
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
			isCreating: false,
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		SidebarDetailBlock: () => SidebarDetailBlock,
		activeMultidialogs(): ImModelSidebarMultidialogItem[]
		{
			const multidialogs = this.$store.getters['sidebar/multidialog/getMultidialogsByStatus']([MultidialogStatus.new, MultidialogStatus.open]);

			return multidialogs.sort((a, b) => b.date - a.date);
		},
		closedMultidialogs(): ImModelSidebarMultidialogItem[]
		{
			const multidialogs = this.$store.getters['sidebar/multidialog/getMultidialogsByStatus']([MultidialogStatus.close]);

			return multidialogs.sort((a, b) => b.date - a.date);
		},
		limitReached(): boolean
		{
			const openMultidialogs = this.$store.getters['sidebar/multidialog/getMultidialogsByStatus']([MultidialogStatus.open]);
			const openSessionsLimit = this.$store.getters['sidebar/multidialog/getOpenSessionsLimit'];

			return openSessionsLimit <= openMultidialogs.length;
		},
		isInitedDetail(): boolean
		{
			return this.$store.getters['sidebar/multidialog/isInitedDetail'];
		},
		isDisabledButtonCreate(): boolean
		{
			return this.limitReached || !this.isInitedDetail;
		},
		buttonCreateTitle(): boolean
		{
			if (!this.limitReached || !this.isInitedDetail)
			{
				return '';
			}

			return this.loc('IM_SIDEBAR_SUPPORT_TICKET_LIMIT');
		},
	},
	created()
	{
		this.service = new Multidialog();
	},
	mounted()
	{
		void this.loadFirstPage();
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.multidialog });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const hasNextPage = this.$store.getters['sidebar/multidialog/hasNextPage'];

			return isAtThreshold && hasNextPage;
		},
		async loadFirstPage()
		{
			if (this.isLoading)
			{
				return;
			}

			this.isLoading = true;
			await this.service.loadFirstPage();
			this.isLoading = false;
		},
		async onScroll(event: Event)
		{
			if (this.isLoading || !this.needToLoadNextPage(event))
			{
				return;
			}

			this.isLoading = true;
			await this.service.loadNextPage();
			this.isLoading = false;
		},
		async onAddSupport()
		{
			if (this.isCreating)
			{
				return;
			}

			this.isCreating = true;
			const newDialogId = await this.service.createSupportChat();
			if (newDialogId)
			{
				this.openChat(newDialogId);
			}
			this.isCreating = false;
		},
		openChat(dialogId)
		{
			void Messenger.openChat(dialogId);
		},
	},
	template: `
		<div class="bx-im-sidebar-multidialog-detail__scope">
			<DetailHeader
				:title="loc('IM_SIDEBAR_SUPPORT_TICKET_DETAIL_TITLE')"
				:secondLevel="true"
				@back="onBackClick"
			>
				<template #action>
					<div :title="buttonCreateTitle" class="bx-im-sidebar-detail-header__add-button">
						<ChatButton
							:text="loc('IM_SIDEBAR_SUPPORT_TICKET_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="ButtonColor.PrimaryLight"
							:isLoading="isCreating"
							:isDisabled="isDisabledButtonCreate"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddSupport"
						/>
					</div>
				</template>
			</DetailHeader>
			<div class="bx-im-sidebar-multidialog-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<MultidialogItem
					v-for="multidialog in activeMultidialogs"
					:key="multidialog.chatId"
					:item="multidialog"
					@click="openChat(multidialog.dialogId)"
				/>
				<MultidialogItem
					v-for="multidialog in closedMultidialogs"
					:key="multidialog.chatId"
					:item="multidialog"
					@click="openChat(multidialog.dialogId)"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
