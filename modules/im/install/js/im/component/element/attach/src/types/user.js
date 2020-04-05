/**
 * Bitrix Messenger
 * Vue component
 *
 * User (Attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./user.css";
import {Utils} from "im.utils";

export const AttachTypeUser =
{
	property: 'USER',
	name: 'bx-messenger-element-attach-user',
	component:
	{
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		methods:
		{
			openLink(element)
			{
				if (element.LINK)
				{
					Utils.platform.openNewPage(element.LINK);
				}
				else
				{
					// element.NETWORK_ID
					// element.USER_ID
					// element.CHAT_ID
					// TODO exec openDialog with params
				}
			},
			getAvatarType(element)
			{
				if (element.AVATAR)
				{
					return '';
				}

				let avatarType = 'user';

				if (element.AVATAR_TYPE === 'CHAT')
				{
					avatarType = 'chat';
				}
				else if (element.AVATAR_TYPE === 'BOT')
				{
					avatarType = 'bot';
				}

				return 'bx-im-element-attach-type-user-avatar-type-'+avatarType;
			}
		},
		template: `
			<div class="bx-im-element-attach-type-user">
				<template v-for="(element, index) in config.USER">
					<div class="bx-im-element-attach-type-user-body" @click="openLink(element)">
						<div class="bx-im-element-attach-type-user-avatar">
							<div :class="['bx-im-element-attach-type-user-avatar-type', getAvatarType(element)]" :style="{backgroundColor: element.AVATAR? '': color}">
								<img v-if="element.AVATAR" 
									v-bx-lazyload
									class="bx-im-element-attach-type-user-avatar-source"
									:data-lazyload-src="element.AVATAR"
								/>
							</div>
						</div>
						<div class="bx-im-element-attach-type-user-name" v-html="element.NAME"></div>
					</div>
				</template>
			</div>
		`
	},
};