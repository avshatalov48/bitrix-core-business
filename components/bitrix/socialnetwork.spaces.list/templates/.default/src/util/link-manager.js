export class LinkManager
{
	static groupPath = '';
	static commonSpacePath = '';

	static getSpaceLink(spaceId: number): string
	{
		let path = LinkManager.commonSpacePath;
		if (spaceId > 0)
		{
			path = LinkManager.groupPath.replace('#group_id#', spaceId);
		}

		return path;
	}
}
