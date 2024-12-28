import { EventEmitter } from 'main.core.events';

import { ChatHeader } from 'im.v2.component.content.elements';
import { EventType } from 'im.v2.const';
import { AddToChat as AddToChatPopup } from 'im.v2.component.entity-selector';

import { CollabTitle } from './collab-title';
import { EntitiesPanel } from './entities-panel/entities-panel';
import { AddToChatButton } from './add-to-chat-button';
import { PulseAnimation } from './pulse-animation/pulse-animation';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CollabHeader = {
	name: 'CollabHeader',
	components: { ChatHeader, CollabTitle, EntitiesPanel, AddToChatButton, AddToChatPopup, PulseAnimation },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {
			compactMode: false,
			showAddToChatPopupDelayed: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isInited(): boolean
		{
			return this.dialog.inited;
		},
	},
	watch:
	{
		async isInited(isInited: boolean)
		{
			if (isInited && this.showAddToChatPopupDelayed)
			{
				await this.$nextTick();
				this.openAddToChatPopup();
			}
		},
	},
	created()
	{
		EventEmitter.subscribe(EventType.header.openAddToChatPopup, this.onOpenAddToChatPopup);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.header.openAddToChatPopup, this.onOpenAddToChatPopup);
	},
	methods:
	{
		onOpenAddToChatPopup()
		{
			if (!this.isInited)
			{
				this.showAddToChatPopupDelayed = true;

				return;
			}

			this.openAddToChatPopup();
		},
		openAddToChatPopup()
		{
			this.$refs['add-to-chat-button'].openAddToChatPopup();
		},
		onCompactModeChange(compactMode: boolean)
		{
			this.compactMode = compactMode;
		},
	},
	template: `
		<ChatHeader :dialogId="dialogId" @compactModeChange="onCompactModeChange" class="bx-im-collab-header__container">
			<template #title>
				<CollabTitle :dialogId="dialogId" />
			</template>
			<template #before-actions>
				<EntitiesPanel :dialogId="dialogId" :compactMode="compactMode" />
			</template>
			<template #add-to-chat-button>
				<PulseAnimation :showPulse="showAddToChatPopupDelayed">
					<AddToChatButton 
						:withAnimation="showAddToChatPopupDelayed" 
						:dialogId="dialogId" 
						ref="add-to-chat-button" 
						@close="showAddToChatPopupDelayed = false"
					/>
				</PulseAnimation>
			</template>
		</ChatHeader>
	`,
};
