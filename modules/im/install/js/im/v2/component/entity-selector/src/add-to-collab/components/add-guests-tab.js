import { InvitationInput } from 'intranet.invitation-input';
import { EventEmitter } from 'main.core.events';

import { UserType, SliderCode } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { openHelpdeskArticle } from 'im.v2.lib.helpdesk';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { ScrollWithGradient } from 'im.v2.component.elements.scroll-with-gradient';
import { ChatButton, ButtonSize, ButtonColor } from 'im.v2.component.elements.button';
import { FeaturePromoter } from 'ui.info-helper';

import { CopyInviteLink } from './copy-invite-link';
import { InviteLanguageSelector } from './invite-language-selector';

import type { ImModelChat, ImModelCollabInfo, ImModelUser } from 'im.v2.model';
import type { JsonObject } from 'main.core';

const HELPDESK_SLIDER_CLOSE_EVENT = 'SidePanel.Slider:onClose';
const HELPDESK_SLIDER_ID = 'main:helper';

// @vue/component
export const AddGuestsTab = {
	name: 'AddGuestsTab',
	components: { ChatButton, ScrollWithGradient, CopyInviteLink, InviteLanguageSelector },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		height: {
			type: Number,
			default: 0,
		},
	},
	emits: ['close', 'closeHelpdeskSlider', 'openHelpdeskSlider', 'closeLanguageSelector', 'openLanguageSelector'],
	data(): JsonObject
	{
		return {
			isAddButtonDisabled: true,
			isInvitingGuests: false,
			invitationLangCode: '',
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		isCurrentUserCollaber(): boolean
		{
			const currentUser: ImModelUser = this.$store.getters['users/get'](Core.getUserId(), true);

			return currentUser.type === UserType.collaber;
		},
		preparedDescription(): string
		{
			if (this.isCurrentUserCollaber)
			{
				return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TEXT_GUEST');
			}

			return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TEXT_EMPLOYEE');
		},
		preparedDescriptionTitle(): string
		{
			if (this.isCurrentUserCollaber)
			{
				return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TITLE_GUEST');
			}

			return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TITLE_EMPLOYEE');
		},
		chatId(): number
		{
			const chat: ImModelChat = this.$store.getters['chats/get'](this.dialogId, true);

			return chat.chatId;
		},
		collabId(): number
		{
			const collab: ImModelCollabInfo = this.$store.getters['chats/collabs/getByChatId'](this.chatId);

			return collab.collabId;
		},
		containerStyles(): {height: string}
		{
			return {
				height: `${this.height}px`,
			};
		},
		isPhoneInviteAvailable(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.inviteByPhoneAvailable);
		},
		preparedInvitationTitle(): string
		{
			if (this.isPhoneInviteAvailable)
			{
				return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_PHONE_OR_EMAIL');
			}

			return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_EMAIL');
		},
		isCollaber(): boolean
		{
			const currentUser: ImModelUser = this.$store.getters['users/get'](Core.getUserId());

			return currentUser.type === UserType.collaber;
		},
		isEnabledCollabersInvitation(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.enabledCollabersInvitation);
		},
		isChangeInviteLanguageAvailable(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.changeInviteLanguageAvailable);
		},
		defaultLanguageCode(): string
		{
			return Core.getLanguageId();
		},
	},
	created()
	{
		this.initInvitationInput();
		EventEmitter.subscribe(HELPDESK_SLIDER_CLOSE_EVENT, this.onCloseOpenHelpdeskSlider);
	},
	mounted()
	{
		this.invitationLangCode = this.defaultLanguageCode;
		this.invitationGuests.renderTo(this.$refs['im-collab-invitation-guests-input']);
	},
	beforeUnmount()
	{
		this.invitationGuests.unsubscribe('readyState', this.onReadySaveInputHandler);
		this.invitationGuests.unsubscribe('onUnreadySave', this.onUnreadySaveInputHandler);
		EventEmitter.unsubscribe(HELPDESK_SLIDER_CLOSE_EVENT, this.onCloseOpenHelpdeskSlider);
	},
	methods:
	{
		openHelpdesk()
		{
			this.$emit('openHelpdeskSlider');

			const ARTICLE_CODE = '22706836';
			openHelpdeskArticle(ARTICLE_CODE);
		},
		initInvitationInput()
		{
			this.invitationGuests = new InvitationInput();
			this.invitationGuests.subscribe('onReadySave', this.onReadySaveInputHandler);
			this.invitationGuests.subscribe('onUnreadySave', this.onUnreadySaveInputHandler);
		},
		onReadySaveInputHandler()
		{
			this.isAddButtonDisabled = false;
		},
		onUnreadySaveInputHandler()
		{
			this.isAddButtonDisabled = true;
			this.isInvitingGuests = false;
		},
		async addGuestToCollab()
		{
			this.isInvitingGuests = true;
			await this.invitationGuests.inviteToGroup(this.collabId);
			this.isInvitingGuests = false;
			this.$emit('close');
		},
		onInvitationGuests()
		{
			if (!this.isEnabledCollabersInvitation)
			{
				this.showHelper();
			}
		},
		showHelper()
		{
			new FeaturePromoter({ code: SliderCode.collabInviteOff }).show();
		},
		onCloseOpenHelpdeskSlider({ data })
		{
			const [event] = data;

			const sliderId = event.getSlider().getUrl().toString();
			if (sliderId === HELPDESK_SLIDER_ID)
			{
				this.$emit('closeHelpdeskSlider');
			}
		},
		onInviteLanguageSelected(langCode: string)
		{
			this.invitationLangCode = langCode;
			this.invitationGuests.changeLanguage(langCode);
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-add-to-collab__container" :style="containerStyles">
			<div class="bx-im-add-to-collab__invite-section">
				<ScrollWithGradient :gradientHeight="28" :withShadow="true">
					<div class="bx-im-add-to-collab__content">
						<div class="bx-im-add-to-collab__description">
							<div class="bx-im-add-to-collab__description_content">
								<div class="bx-im-add-to-collab__description_title">{{ preparedDescriptionTitle }}</div>
								<div class="bx-im-add-to-collab__description_text">{{ preparedDescription }}</div>
								<a class="bx-im-add-to-collab__helpdesk-link" @click.prevent="openHelpdesk">
									{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_HELPDESK_LINK') }}
								</a>
							</div>
							<div class="bx-im-add-to-collab__description_icon"></div>
						</div>
						<InviteLanguageSelector
							v-if="isChangeInviteLanguageAvailable"
							:defaultLanguageCode="defaultLanguageCode"
							@selectLanguage="onInviteLanguageSelected"
							@openLanguageSelector="$emit('openLanguageSelector')"
							@closeLanguageSelector="$emit('closeLanguageSelector')"
						/>
						<CopyInviteLink 
							:collabId="collabId"
							:dialogId="dialogId"
							:langCode="invitationLangCode"
						/>
						<div class="bx-im-add-to-collab__invite-block">
							<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
								{{ preparedInvitationTitle }}
							</span>
							<div 
								ref="im-collab-invitation-guests-input" 
								class="bx-im-add-to-collab__invite-block-input"
								@click="onInvitationGuests"
							></div>
						</div>
					</div>
				</ScrollWithGradient>
			</div>
			<div class="bx-im-add-to-collab__buttons">
				<ChatButton
					:size="ButtonSize.L"
					:color="ButtonColor.Collab"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="isAddButtonDisabled || isInvitingGuests"
					:isLoading="isInvitingGuests"
					@click="addGuestToCollab"
				/>
				<ChatButton
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_CANCEL_BUTTON')"
					@click="$emit('close')"
				/>
			</div>
		</div>
	`,
};
