import type { AccessRightValue, UserGroup } from '../../../user-groups-model';
import { BaseUserGroupsExporter } from './base-user-groups-exporter';

export class AllUserGroupsExporter extends BaseUserGroupsExporter
{
	shouldBeIncludedInExport(userGroup: UserGroup, accessRightValue: AccessRightValue): boolean
	{
		return true;
	}
}
