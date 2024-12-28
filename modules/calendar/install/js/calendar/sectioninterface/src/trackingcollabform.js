import { TrackingGroupsForm } from './trackinggroupsform';

export class TrackingCollabsForm extends TrackingGroupsForm
{
	constructor(options = {})
	{
		super(options);
		this.interfaceType = 'collabs';
		this.trackingIdList = options.trackingCollabs || [];
		this.groups = options.groups || [];
	}

	getSelectedSections(): Array<number>
	{
		const sections = [];
		this.superposedSections.forEach((section): void => {
			if (
				this.interfaceType === 'collabs'
				&& section.type === 'group'
				&& !this.trackingIdList?.includes(section.ownerId)
				&& !this.groups?.includes(section.ownerId)
			)
			{
				return;
			}
			sections.push(parseInt(section.id, 10));
		});

		return sections;
	}

	handleGroupSelectorChanges(): void
	{
		const selectedItems = this.groupTagSelector.getDialog().getSelectedItems();
		this.trackingIdList = [];
		selectedItems.forEach((item): void => {
			if (item.entityType !== 'collab')
			{
				return;
			}

			this.trackingIdList.push(item.id);
		});
		this.updateSectionList();
	}

	getSelectorEntities(): Array
	{
		return [
			{
				id: 'project',
				options: {
					type: ['collab'],
					createProjectLink: false,
				},
			},
		];
	}
}
