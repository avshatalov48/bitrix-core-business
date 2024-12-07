import { Core } from 'im.v2.application.core';
import { Text } from 'main.core';
import { Utils } from 'im.v2.lib.utils';

import { BaseMessage } from 'im.v2.component.message.base';
import { Button as ButtonComponent, ButtonSize } from 'im.v2.component.elements';
import { MessageStatus } from 'im.v2.component.message.elements';
import { DefaultMessage } from 'im.v2.component.message.default';

import './css/sign.css';
import { Await, Failure, Success, SignButtonParams } from './const/sign';
import { metaData } from './const/configurations';

import type { ImModelMessage } from 'im.v2.model';
import type { SignMessageComponentParams } from './const/sign';

const PARAMS_KEY = {
	STAGE_ID: 'stageId',
	USER: 'user',
	INITIATOR: 'initiator',
	DOCUMENT: 'document',
	HELP_ARTICLE: 'helpArticle',
};

// @vue/component
export const SignMessage = {
	name: 'SignMessage',
	components: { ButtonComponent, BaseMessage, DefaultMessage, MessageStatus },
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
		ButtonSize: () => ButtonSize,
		componentParams(): Object
		{
			return this.message.componentParams;
		},
		stageId(): string
		{
			return this.componentParams[PARAMS_KEY.STAGE_ID] ?? '';
		},
		user(): ?Object
		{
			return this.componentParams[PARAMS_KEY.USER];
		},
		initiator(): ?Object
		{
			return this.componentParams[PARAMS_KEY.INITIATOR];
		},
		document(): ?Object
		{
			return this.componentParams[PARAMS_KEY.DOCUMENT];
		},
		helpArticle(): ?string
		{
			return this.componentParams[PARAMS_KEY.HELP_ARTICLE] ?? '';
		},
		signData(): SignMessageComponentParams
		{
			const data = metaData[this.stageId];
			if (!data)
			{
				console.error('SignMessage: signData is undefined.');
			}

			return data;
		},
		title(): string
		{
			return this.signData?.title ?? '';
		},
		description(): string
		{
			return this.signData?.description ?? '';
		},
		button(): ?SignButtonParams
		{
			return this.signData?.button;
		},
		isAwaitSign(): boolean
		{
			return Object.values(Await).includes(this.stageId);
		},
		isSuccessSign(): boolean
		{
			return Object.values(Success).includes(this.stageId);
		},
		isFailureSign(): boolean
		{
			return Object.values(Failure).includes(this.stageId);
		},
		isSelfMessage(): boolean
		{
			return this.message.authorId === Core.getUserId();
		},
		containerClasses(): Object
		{
			return {
				'--self': this.isSelfMessage,
				'--await': this.isAwaitSign,
				'--success': this.isSuccessSign,
				'--failure': this.isFailureSign,
			};
		},
	},
	methods:
	{
		replacePhrase(phrase: ?string): string
		{
			let text = phrase ?? '';
			const userLink = Utils.user.getProfileLink(this.user?.id);
			const initiatorLink = Utils.user.getProfileLink(this.initiator?.id);
			const articleLink = `BX.Helper?.show('redirect=detail&code=${this.helpArticle}')`;
			const LINK_CLASS = 'bx-im-message-sign__link';
			const DOCUMENT_CLASS = 'bx-im-message-sign__document';

			const phrases = {
				'#DOCUMENT_NAME#': `<span class="${DOCUMENT_CLASS}">${Text.encode(this.document?.name)}</span>`,
				'#USER_LINK#': `<a href="${userLink}" class="${LINK_CLASS}">${Text.encode(this.user?.name)}</a>`,
				'#INITIATOR_LINK#': `<a href="${initiatorLink}" class="${LINK_CLASS}">${Text.encode(this.initiator?.name)}</a>`,
				'[helpdesklink]': `<a onclick="${articleLink}" class="${LINK_CLASS}">`,
				'[/helpdesklink]': '</a>',
			};

			Object.keys(phrases).forEach((code) => {
				text = text.replaceAll(code, phrases[code]);
			});

			return text;
		},
	},
	template: `
		<DefaultMessage v-if="!signData" :item="item" :dialogId="dialogId" />
		<BaseMessage
			v-else
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-sign__scope"
		>
			<div :class="['bx-im-message-sign__container', containerClasses]">
				<div class="bx-im-message-sign__image" />
				<div class="bx-im-message-sign__content">
					<div class="bx-im-message-sign__title">
						{{ title }}
					</div>
					<div class="bx-im-message-sign__description" v-html="replacePhrase(description)" />
					<div class="bx-im-message-sign__buttons_container">
						<ButtonComponent
							v-if="button"
							:size="ButtonSize.L"
							isRounded
							:text="button.text"
							:color="button.color"
							@click="button.callback({ user, document })"
						/>
					</div>
				</div>
				<div class="bx-im-message-sign__status_container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</BaseMessage>
	`,
};
