import { RestMethod, RestMethodHandler } from "im.const";

export const User = {
	props: ['value', 'popupInstance'],
	data()
	{
		return {
			user: {},
			hasError: false,
			requestFinished: false
		};
	},
	created()
	{
		const userData = this.getUser(this.value);

		if (userData)
		{
			this.user = userData;
			this.requestFinished = true;
		}
		else
		{
			this.requestUserData(this.value);
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
		getUser(userId)
		{
			return this.$store.getters['users/get'](userId);
		},
		requestUserData(userId)
		{
			this.$Bitrix.RestClient.get().callMethod(RestMethod.imUserGet, {
				ID: userId
			}).then(response => {
				this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imUserGet, response);

				this.user = this.getUser(this.value);
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
		userAvatar()
		{
			if (this.emptyAvatar)
			{
				return '/bitrix/js/im/images/blank.gif';
			}
			else
			{
				return this.user.avatar;
			}
		},
		emptyAvatar()
		{
			return this.user.avatar === '' || this.user.avatar.indexOf('/bitrix/js/im/images/blank.gif') >= 0;
		},
		botStyles()
		{
			//todo handle all the bot types im/install/js/im/im.js:5887
			return 'bx-messenger-user-bot';
		},
		userStatusText()
		{
			//todo remove old code
			return BX.MessengerCommon.getUserStatus(this.user.id, false).statusText;
		},
		userStatusClass()
		{
			//todo remove old code
			return 'bx-messenger-panel-avatar-status-' + BX.MessengerCommon.getUserStatus(this.user.id, true);
		},
		userPosition()
		{
			//todo remove old code
			return BX.MessengerCommon.getUserPosition(this.user.id);
		},
	},
	//language=Vue
	template: `
		<div class="bx-messenger-external-data" style="width: 272px; max-width: 272px; height: 100px;">
			<div v-if="requestFinished && !hasError">
				<div class="bx-messenger-external-avatar">
					<div :class="[userStatusClass, 'bx-messenger-panel-avatar']">
						<img
							:src="userAvatar"
							:style="avatarStyles"
							:class="[emptyAvatar ? 'bx-messenger-panel-avatar-img-default' : '', 'bx-messenger-panel-avatar-img']"
							:alt="user.name"
						/>
						<span :title="userStatusText" class="bx-messenger-panel-avatar-status"></span>
					</div>
	
					<span v-if="user.extranet" class="bx-messenger-panel-title"><div class="bx-messenger-user-extranet">{{ user.name }}</div></span>
					<span v-else-if="user.bot" class="bx-messenger-panel-title"><div :class="botStyles">{{ user.name }}</div></span>
					<span v-else class="bx-messenger-panel-title">{{ user.name }}</span>
	
					<span class="bx-messenger-panel-desc">{{ userPosition }}</span>
				</div>
				<div class="bx-messenger-external-data-buttons">
					<span class="bx-notifier-item-button bx-notifier-item-button-white" @click="onOpenChat">
						{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_USER_OPEN_CHAT') }}
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
