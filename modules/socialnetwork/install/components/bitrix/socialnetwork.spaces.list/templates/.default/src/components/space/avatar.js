// @vue/component
import type { AvatarModel } from '../../model/avatar-model';

export const Avatar = {
	props: {
		avatar: {
			type: Object,
			default: () => {},
		},
		isSecret: {
			type: Boolean,
			default: true,
		},
		isInvitation: {
			type: Boolean,
			default: true,
		},
	},
	data(): Object
	{
		return {
			secretSpaceImage: '/bitrix/components/bitrix/socialnetwork.spaces.list/templates/.default/images/socialnetwork-spaces_icon_close-spase.svg',
		};
	},
	computed: {
		avatarModel(): AvatarModel
		{
			return this.avatar;
		},
		avatarClass(): string
		{
			let result = '';
			if (this.avatarModel.type === 'icon')
			{
				if (this.avatarModel.id.length > 0)
				{
					result = `sonet-common-workgroup-avatar --${this.avatarModel.id}`;
				}
				else
				{
					result = 'ui-icon-common-user-group ui-icon';
				}
			}

			return result;
		},
		iconStyle(): string
		{
			let result = '';
			if (this.avatarModel.type === 'image')
			{
				result = `background-image: url(${this.avatarModel.id});`;
			}

			return result;
		},
		iconClass(): string
		{
			return this.avatarModel.type === 'image' ? 'sn-spaces__list-item_img' : '';
		},
	},
	template: `
		<div class="sn-spaces__list-item_icon" :class="avatarClass">
			<i :style="iconStyle" :class="iconClass"/>
			<div v-if="isInvitation" class="sn-spaces__list-item_invitation-icon">
				<div class="ui-icon-set --mail" style="--ui-icon-set__icon-size: 18px;"></div>
			</div>
			<div class="sn-spaces__list-item_icon-close" v-if="isSecret"/>
		</div>
	`,
};
