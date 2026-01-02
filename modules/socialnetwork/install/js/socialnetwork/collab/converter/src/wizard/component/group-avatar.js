import { AvatarHexagonGuest } from 'ui.avatar';
import type { Group } from '../../service/types';

// @vue/component
export const GroupAvatar = {
	props: {
		group: {
			type: Object,
			required: true,
		},
		size: {
			type: Number,
			default: 48,
		},
	},
	mounted(): void
	{
		const group: Group = this.group;
		const avatar = new AvatarHexagonGuest({
			size: this.size,
			userName: group.name,
			userpicPath: group.image,
		});

		return avatar.renderTo(this.$refs.container);
	},
	template: `
		<div class="socialnetwork-collab-converter-wizard-group-avatar" ref="container"/>
	`,
};
