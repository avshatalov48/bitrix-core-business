import { Button as ButtonComponent, ButtonSize, ButtonIcon } from 'im.v2.component.elements';
import { Utils } from 'im.v2.lib.utils';

import '../css/extensions/call-invite.css';

import type { CustomColorScheme } from 'im.v2.component.elements';
import type { ImModelMessage } from 'im.v2.model';

type ExtensionParams = {
	link: string,
};

const BUTTON_COLOR = '#00ace3';

// @vue/component
export const CallInviteExtension = {
	name: 'CallInviteExtension',
	components: { ButtonComponent },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data(): Object
	{
		return {};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonIcon: () => ButtonIcon,

		buttonColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: 'transparent',
				borderColor: BUTTON_COLOR,
				iconColor: BUTTON_COLOR,
				textColor: BUTTON_COLOR,
				hoverColor: 'transparent',
			};
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		extensionParams(): ExtensionParams
		{
			return this.item.extensionParams;
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onCallButtonClick()
		{
			Utils.browser.openLink(this.extensionParams.link);
		},
	},
	template: `
		<div class="bx-im-message-base-call-invite__scope bx-im-message-base__extension">
			<div class="bx-im-message-base-call-invite__container">
				<div class="bx-im-message-base-call-invite__image"></div>
				<div class="bx-im-message-base-call-invite__content">
					<div class="bx-im-message-base-call-invite__title">{{ loc('IM_MESSENGER_MESSAGE_CALL_INVITE_TITLE') }}</div>
					<div class="bx-im-message-base-call-invite__description">{{ loc('IM_MESSENGER_MESSAGE_CALL_INVITE_DESCRIPTION') }}</div>
					<div class="bx-im-message-base-call-invite__buttons_container">
						<div class="bx-im-message-base-call-invite__buttons_item">
							<ButtonComponent
								:size="ButtonSize.L" 
								:icon="ButtonIcon.Call" 
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSENGER_MESSAGE_CALL_INVITE_BUTTON_JOIN')"
								@click="onCallButtonClick"
							/>
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};
