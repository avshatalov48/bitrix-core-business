import { BaseChatContent } from 'im.v2.component.content.elements';
import { PromoId } from 'im.v2.const';
import { Notifier } from 'im.v2.lib.notifier';
import { PromoManager } from 'im.v2.lib.promo';

import { CollabHeader } from './components/header';

import './css/collab.css';

import type { ImModelChat } from 'im.v2.model';
import type { PromoParams } from 'im.v2.provider.pull';

export const CollabContent = {
	name: 'CollabContent',
	components: { BaseChatContent, CollabHeader },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
	},
	watch:
	{
		dialogInited(newValue: boolean, oldValue: boolean): void
		{
			if (!newValue || oldValue)
			{
				return;
			}

			this.initPromo();
		},
	},
	methods:
	{
		initPromo(): void
		{
			const promoManager = PromoManager.getInstance();

			const promoId = PromoId.collaberNotAcceptInvitationOneDay;
			const promoParams = { chatId: this.dialog.chatId };

			if (promoManager.needToShow(promoId, promoParams))
			{
				this.showNotAcceptInvitationPromo(promoId, promoParams);
			}
		},
		showNotAcceptInvitationPromo(promoId: $Values<typeof PromoId>, promoParams: PromoParams): void
		{
			Notifier.collab.onCollaberNotAcceptInvitation();

			void PromoManager.getInstance().markAsWatched(promoId, promoParams);
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<CollabHeader :dialogId="dialogId" :key="dialogId" />
			</template>
		</BaseChatContent>
	`,
};
