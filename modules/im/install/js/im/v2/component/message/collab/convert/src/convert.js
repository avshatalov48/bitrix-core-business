import { Loc } from 'main.core';
import { RichLoc } from 'ui.vue3.components.rich-loc';

import { BaseMessage } from 'im.v2.component.message.base';

import './css/convert.css';

// @vue/component
export const ConvertToCollabMessage = {
	name: 'ConvertToCollabMessage',
	components: { BaseMessage, RichLoc },
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
	methods: {
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
		>
			<div class="bx-im-message-collab-convert__container">
				<div class="bx-im-message-collab-convert__image"/>
				<div class="bx-im-message-collab-convert__content">
					<div class="bx-im-message-collab-convert__title">
						{{ loc('IM_MESSAGE_COLLAB_CONVERT_TITLE') }}
					</div>
					<div class="bx-im-message-collab-convert__description">
						<RichLoc :text="loc('IM_MESSAGE_COLLAB_CONVERT_DESCRIPTION')" placeholder="[br/]">
							<template #br>
								<br>
							</template>
						</RichLoc>
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
