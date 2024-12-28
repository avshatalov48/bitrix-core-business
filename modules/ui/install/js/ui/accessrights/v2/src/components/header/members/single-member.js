import type { Member } from '../../../store/model/user-groups-model';

export const SingleMember = {
	name: 'SingleMember',
	props: {
		member: {
			/** @type Member */
			type: Object,
			required: true,
		},
	},
	computed: {
		avatarBackgroundImage(): string {
			return `url(${encodeURI(this.member.avatar)})`;
		},
		noAvatarClass(): string {
			if (this.member.type === 'groups')
			{
				return 'ui-icon-common-user-group';
			}

			if (this.member.type === 'sonetgroups' || this.member.type === 'departments')
			{
				return 'ui-icon-common-company';
			}

			if (this.member.type === 'usergroups')
			{
				return 'ui-icon-common-user-group';
			}

			return 'ui-icon-common-user';
		},
	},
	template: `
		<div class='ui-access-rights-v2-members-item'>
			<a v-if="member.avatar" class='ui-access-rights-v2-members-item-avatar' :title="member.name" :style="{
				backgroundImage: avatarBackgroundImage,
				backgroundSize: 'cover',
			}"></a>
			<a v-else class='ui-icon ui-access-rights-v2-members-item-icon' :class="noAvatarClass" :title="member.name">
				<i></i>
			</a>
		</div>
	`,
};
