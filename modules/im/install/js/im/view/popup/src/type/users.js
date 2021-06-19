import { RestMethod, RestMethodHandler } from "im.const";

export const Users = {
	props: ['value', 'popupInstance'],
	data()
	{
		return {
			users: {},
			hasError: false,
			requestFinished: false
		};
	},
	created()
	{
		const needRequest = this.isNeedUserRequest(this.value);
		if (needRequest)
		{
			this.requestUserData(this.value);
		}
		else //!needRequest
		{
			this.users = this.getUsersForPopup();
			this.requestFinished = true;
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
	computed:
	{
		popupHeight()
		{
			let height = this.value.length * 30;
			if (height > 150)
			{
				height = 150
			}
			return height + 'px'
		}
	},
	methods:
	{
		getUser(userId)
		{
			return this.$store.getters['users/get'](userId);
		},
		getUsersForPopup()
		{
			return this.value.map((userId) => {
				return this.getUser(userId);
			});
		},
		getUserAvatar(user)
		{
			if (this.isEmptyAvatar(user))
			{
				return '/bitrix/js/im/images/blank.gif';
			}
			else
			{
				return user.avatar;
			}
		},
		isEmptyAvatar(user)
		{
			return user.avatar === '' || user.avatar.indexOf('/bitrix/js/im/images/blank.gif') >= 0;
		},
		getAvatarStyles(user)
		{
			const styles = {};
			if (this.isEmptyAvatar(user))
			{
				styles.backgroundColor = user.color;
			}

			return styles;
		},
		getUserStatusClass(user)
		{
			return `bx-notifier-popup-avatar-status-${user.status}`;
		},
		isNeedUserRequest(users)
		{
			for (let i = 0; i < users.length; i++)
			{
				if (!this.getUser(users[i]))
				{
					return true;
				}
			}

			return false;
		},
		requestUserData(userIds)
		{
			this.$Bitrix.RestClient.get().callMethod(RestMethod.imUserListGet, {
				ID: userIds
			}).then(response => {
				this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imUserListGet, response);
				this.users = this.getUsersForPopup();
				this.requestFinished = true;
			}).catch((error) => {
				this.hasError = true;
				console.error(error);
				this.requestFinished = true;
			});
		},
		onUserClick(userId)
		{
			this.popupInstance.destroy();
			BXIM.openMessenger(userId)
		},
	},
	//language=Vue
	template: `
		<div
			class="bx-im-vue-popup-container" 
			:style="{height: popupHeight, width: '180px', display: 'flex', alignItems: 'center', justifyContent: 'center'}"
		>
			<span v-if="requestFinished && !hasError" class="bx-notifier-item-help-popup">
				<a 
					v-for="user in users"
					class="bx-notifier-item-help-popup-img"
					@click.prevent="onUserClick(user.id)"
				>
					<span :class="[getUserStatusClass(user), 'bx-notifier-popup-avatar']">
						<img 
							:src="getUserAvatar(user)"
							:class="['bx-notifier-popup-avatar-img', isEmptyAvatar(user) ? 'bx-notifier-popup-avatar-img-default' : '']"
							:style="getAvatarStyles(user)"
							:alt="user.name"
						/>
					</span>
					<span 
						:class="['bx-notifier-item-help-popup-name', user.extranet ? 'bx-notifier-popup-avatar-extranet' : '']"
					>
						{{ user.name }}
					</span>
				</a>
			</span>
			<span v-else-if="!requestFinished && !hasError" class="bx-messenger-content-load-img"></span>
			<div v-else-if="requestFinished && hasError">
				{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_NO_ACCESS') }}
			</div>
		</div>
	`
};