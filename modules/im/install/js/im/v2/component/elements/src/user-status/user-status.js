import {UserStatus as UserStatusType} from 'im.v2.const';

import './user-status.css';

export const UserStatusSize = {
	S: 'S',
	M: 'M',
	L: 'L',
	XL: 'XL',
	XXL: 'XXL'
};

// @vue/component
export const UserStatus = {
	name: 'UserStatus',
	props: {
		status: {
			type: String,
			required: true,
			validator(value) {
				return Object.values(UserStatusType).includes(value);
			}
		},
		size: {
			type: String,
			default: UserStatusSize.M,
			validator(value) {
				return Object.values(UserStatusSize).includes(value);
			}
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		containerClasses()
		{
			return [`--size-${this.size.toLowerCase()}`, `--${this.status}`];
		}
	},
	template:
	`
		<div :class="containerClasses" class="bx-im-user-status__container bx-im-user-status__scope"></div>
	`
};