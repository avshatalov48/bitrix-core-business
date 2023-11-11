import { Loc } from 'main.core';

import { UserRole } from 'im.v2.const';

import type { DropdownItem } from 'im.v2.component.elements';

export const rightsDropdownItems: DropdownItem[] = [
	{
		value: UserRole.member,
		text: Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_MEMBER'),
	},
	{
		value: UserRole.manager,
		text: Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_MANAGER'),
	},
	{
		value: UserRole.owner,
		text: Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_ROLE_OWNER'),
		default: true,
	},
];
