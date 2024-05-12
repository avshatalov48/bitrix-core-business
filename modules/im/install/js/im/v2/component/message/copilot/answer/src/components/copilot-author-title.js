import { BitrixVue } from 'ui.vue3';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Core } from 'im.v2.application.core';
import { AuthorTitle } from 'im.v2.component.message.elements';

import './copilot-author-title.css';

// @vue/component
export const CopilotAuthorTitle = BitrixVue.cloneComponent(AuthorTitle, {
	name: 'CopilotAuthorTitle',
	computed:
	{
		isCopilot(): boolean
		{
			const authorId = Number.parseInt(this.authorDialogId, 10);
			const copilotUserId = this.$store.getters['users/bots/getCopilotUserId'];

			return copilotUserId === authorId;
		},
	},
	methods:
	{
		onAuthorNameClick()
		{
			const authorId = Number.parseInt(this.authorDialogId, 10);
			if (!authorId || authorId === Core.getUserId() || this.isCopilot)
			{
				return;
			}

			EventEmitter.emit(EventType.textarea.insertMention, {
				mentionText: this.user.name,
				mentionReplacement: Utils.text.getMentionBbCode(this.user.id, this.user.name),
			});
		},
	},
	template: `
		<div 
			v-if="showTitle" 
			@click="onAuthorNameClick" 
			class="bx-im-message-copilot-author-title__container"
			:class="{'--clickable': !isCopilot}"
		>
			<ChatTitle
				:dialogId="authorDialogId"
				:showItsYou="false"
				:withColor="true"
				:withLeftIcon="false"
			/>
		</div>
	`,
});
