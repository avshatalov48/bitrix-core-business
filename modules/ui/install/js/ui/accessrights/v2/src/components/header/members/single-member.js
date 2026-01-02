import { Type } from 'main.core';

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
			return `url('${encodeURI(this.member.avatar)}')`;
		},
		noAvatarClass(): string {
			if (this.member.type === 'groups')
			{
				return 'ui-icon-common-user-group';
			}

			if (
				this.member.type === 'sonetgroups'
				|| this.member.type === 'departments'
				|| this.member.type === 'structuredepartments'
			)
			{
				return 'ui-icon-common-company';
			}

			if (this.member.type === 'usergroups')
			{
				return 'ui-icon-common-user-group';
			}

			if (this.member.type === 'structureteams')
			{
				return 'ui-icon-common-my-plan';
			}

			return 'ui-icon-common-user';
		},
		memberTitle(): string
		{
			if (Type.isStringFilled(this.member.name))
			{
				return this.member.name;
			}

			return '';
		},
	},
	template: `
		<div class='ui-access-rights-v2-members-item'>
			<a
				v-if="member.avatar"
				class='ui-access-rights-v2-members-item-avatar'
				:title="memberTitle"
				:style="{
						backgroundImage: avatarBackgroundImage,
						backgroundSize: 'cover',
					}"
			>
			</a>
			<a v-else class='ui-icon ui-access-rights-v2-members-item-icon' :class="noAvatarClass" :title="memberTitle">
				<i></i>
			</a>
		</div>
	`,
};
