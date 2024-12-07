import { Loc } from 'main.core';
import { UserStatisticsLink as CheckInQrAuthPopup } from 'stafftrack.user-statistics-link';

import { Analytics } from 'im.v2.lib.analytics';
import { BaseMessage } from 'im.v2.component.message.base';
import { DefaultMessageContent } from 'im.v2.component.message.elements';
import { DefaultMessage } from 'im.v2.component.message.default';

import './css/check-in.css';

import type { ImModelMessage } from 'im.v2.model';

const paramsKey = Object.freeze({
	url: 'url',
	status: 'status',
	location: 'location',
});

// @vue/component
export const CheckInMessage = {
	name: 'CheckInMessage',
	components: { BaseMessage, DefaultMessage, DefaultMessageContent },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		componentParams(): $Values<typeof paramsKey>
		{
			return this.message.componentParams;
		},
		mapUrl(): string
		{
			const origin = window.location.origin;
			const url = this.componentParams[paramsKey.url];

			return url.startsWith('/') ? origin + url : url;
		},
		status(): ?string
		{
			return this.componentParams[paramsKey.status] ?? '';
		},
		location(): ?string
		{
			return this.componentParams[paramsKey.location] ?? '';
		},
		hasLocation(): boolean
		{
			return Boolean(this.location);
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return Loc.getMessage(phraseCode, replacements);
		},
		onClick()
		{
			this.showQrPopup();
			Analytics.getInstance().onOpenCheckInPopup();
		},
		showQrPopup()
		{
			if (!CheckInQrAuthPopup)
			{
				return;
			}

			new CheckInQrAuthPopup({ intent: 'check-in' }).show();
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
		>
			<div class="bx-im-message-check-in__container">
				<div class="bx-im-message-check-in__image-container">
					<img class="bx-im-message-check-in__image" :src="mapUrl" alt="map" />
					<div v-if="hasLocation" class="bx-im-message-check-in__marker" />
					<div v-else class="bx-im-message-check-in__status">
						{{ status }}
					</div>
				</div>
				<div v-if="hasLocation" :title="location" class="bx-im-message-check-in__location">
					{{ location }}
				</div>
				<div
					class="bx-im-message-check-in__action" 
					@click="onClick"
					:title="loc('IM_MESSAGE_CHECK_IN_ACTION_TEXT')"
				>
					<div class="bx-im-message-check-in__action-icon"></div>
					<span>{{ loc('IM_MESSAGE_CHECK_IN_ACTION_TEXT') }}</span>
				</div>
				<div class="bx-im-message-check-in__bottom-panel">
					<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
				</div>
			</div>
		</BaseMessage>
	`,
};
