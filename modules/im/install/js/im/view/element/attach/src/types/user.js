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
import { AttachLinks } from "../mixin/attachLinks";

export const AttachTypeUser =
{
	property: 'USER',
	name: 'bx-im-view-element-attach-user',
	component:
	{
		mixins: [
			AttachLinks
		],
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		methods:
		{
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
		//language=Vue
		template: `
			<div class="bx-im-element-attach-type-user">
				<template v-for="(element, index) in config.USER">
					<div class="bx-im-element-attach-type-user-body">
						<div class="bx-im-element-attach-type-user-avatar">
							<div :class="['bx-im-element-attach-type-user-avatar-type', getAvatarType(element)]" :style="{backgroundColor: element.AVATAR? '': color}">
								<img v-if="element.AVATAR" 
									v-bx-lazyload
									class="bx-im-element-attach-type-user-avatar-source"
									:data-lazyload-src="element.AVATAR"
								/>
							</div>
						</div>
						<a
							v-if="element.LINK"
							:href="element.LINK" 
							class="bx-im-element-attach-type-user-name"
							target="_blank"
							@click="openLink({element: element, event: $event})"
						>
							{{element.NAME}}
						</a>
						<span v-else @click.prevent="openLink({element: element, event: $event})">
							{{element.NAME}}
						</span>
					</div>
				</template>
			</div>
		`
	},
};