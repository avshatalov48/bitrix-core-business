import {AvatarSize} from 'im.v2.component.old-chat-embedding.elements';
import {SearchItem} from '../search-item';
import {Extension} from 'main.core';
import 'ui.fonts.opensans';
import './avatar-openline.css';

const OpenlineAvatarType = {
	lines: 'lines',
	network: 'network',
	livechat: 'livechat',
	whatsappbytwilio: 'whatsappbytwilio',
	avito: 'avito',
	viber: 'viber',
	telegrambot: 'telegrambot',
	imessage: 'imessage',
	wechat: 'wechat',
	yandex: 'yandex',
	vkgroup: 'vkgroup',
	ok: 'ok',
	olx: 'olx',
	facebook: 'facebook',
	facebookcomments: 'facebookcomments',
	fbinstagramdirect: 'fbinstagramdirect',
	fbinstagram: 'fbinstagram',
	notifications: 'notifications',
};

export const AvatarOpenline = {
	name: 'Avatar',
	props: {
		item: {
			type: SearchItem,
			required: true
		},
		size: {
			type: String,
			default: AvatarSize.M
		},
	},
	computed:
	{
		openlineType()
		{
			return this.item.getOpenlineEntityId();
		},
		chatAvatarStyle(): Object
		{
			return {backgroundImage: `url('${this.item.getAvatar()}')`};
		},
		chatTypeIconClasses(): string
		{
			if (OpenlineAvatarType[this.openlineType])
			{
				return `bx-im-search-avatar-openline__icon-${this.openlineType}`;
			}

			return 'bx-im-search-avatar-openline__icon-lines';
		},
		needCrmBadge(): boolean
		{
			if (!this.isCrmAvailable)
			{
				return false;
			}

			return this.item.isCrmSession();
		}
	},
	created()
	{
		this.isCrmAvailable = Extension.getSettings('im.v2.component.old-chat-embedding.search').get('isCrmAvailable', false);
	},
	template: `
		<div 
			:title="item.getTitle()" 
			:class="'bx-im-search-avatar-openline__size-' + size.toLowerCase()" 
			class="bx-im-search-avatar-openline__wrap"
		>
			<div 
				v-if="item.getAvatar()" 
				:style="chatAvatarStyle" 
				class="bx-im-search-avatar-openline__content bx-im-search-avatar-openline__image"
			></div>
			<div 
				v-else 
				:style="{backgroundColor: this.item.getAvatarColor()}" 
				:class="chatTypeIconClasses" 
				class="bx-im-search-avatar-openline__content bx-im-search-avatar-openline__icon"
			></div>
			<div v-if="needCrmBadge" class="bx-im-search-avatar-openline__crm-badge"></div>
		</div>
	`
};