import { Vue } from "ui.vue";
import { RestMethod, RestMethodHandler } from "im.const";

export const Chat =
{
	props: ['value', 'popupInstance'],
	data()
	{
		return {
			chat: {},
			hasError: false,
			requestFinished: false
		};
	},
	created()
	{
		const chatData = this.getChat(this.value);
		if (chatData)
		{
			this.chat = chatData;
			this.requestFinished = true;
		}
		else
		{
			this.requestChatData(this.value);
		}
	},
	mounted()
	{
		this.popupInstance.show();
	},
	beforeDestroy()
	{
		this.popupInstance.destroy();
	},
	methods:
	{
		getChat(dialogId)
		{
			return this.$store.getters['dialogues/get'](dialogId);
		},
		requestChatData(dialogId)
		{
			this.$Bitrix.RestClient.get().callMethod(RestMethod.imChatGet, {
				dialog_id: dialogId
			}).then(response => {
				this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imChatGet, response);

				this.chat = this.getChat(this.value);
				this.requestFinished = true;
			}).catch((error) => {
				this.hasError = true;
				console.error(error);
				this.requestFinished = true;
			});
		},

		//events
		onOpenChat(event)
		{
			this.popupInstance.destroy();
			BXIM.openMessenger(this.value);
		},
		onOpenHistory(event)
		{
			this.popupInstance.destroy();
			BXIM.openHistory(this.value);
		},
	},
	computed:
	{
		avatarStyles()
		{
			const styles = {};
			if (this.emptyAvatar)
			{
				styles.backgroundColor = this.chat.color;
			}

			return styles;
		},
		chatAvatar()
		{
			if (this.emptyAvatar)
			{
				return '/bitrix/js/im/images/blank.gif';
			}
			else
			{
				return this.chat.avatar;
			}
		},
		emptyAvatar()
		{
			return this.chat.avatar === '' || this.chat.avatar.indexOf('/bitrix/js/im/images/blank.gif') >= 0;
		},
	},
	//language=Vue
	template: `
		<div class="bx-messenger-external-data" style="width: 272px; max-width: 272px; height: 100px;">
			<div v-if="requestFinished && !hasError">
				<div class="bx-messenger-external-avatar">
					<div class="bx-messenger-panel-avatar bx-messenger-panel-avatar-chat">
						<img
							:src="chatAvatar"
							:alt="chat.name"
							:style="avatarStyles"
							:class="[emptyAvatar ? 'bx-messenger-panel-avatar-img-default' : '', 'bx-messenger-panel-avatar-img']"
						>
					</div>
					<span v-if="chat.extranet" class="bx-messenger-panel-title"><div class="bx-messenger-user-extranet">{{ chat.name }}</div></span>
					<span v-else class="bx-messenger-panel-title">{{ chat.name }}</span>
					<span class="bx-messenger-panel-desc">{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_GROUP_CHAT') }}</span>
				</div>
				<div class="bx-messenger-external-data-buttons">
				<span class="bx-notifier-item-button bx-notifier-item-button-white" @click="onOpenChat">
					{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_OPEN_CHAT') }}
				</span>
					<span class="bx-notifier-item-button bx-notifier-item-button-white" @click="onOpenHistory">
					{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_OPEN_HISTORY') }}
				</span>
				</div>
			</div>
			<span v-else-if="!requestFinished && !hasError" class="bx-messenger-content-load-img"></span>
			<div v-else-if="requestFinished && hasError">
				{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_NO_ACCESS') }}
			</div>
		</div>
	`
};
