import { Type, Loc } from 'main.core';
import { TagSelector as EntityTagSelector } from 'ui.entity-selector';
import { BaseEvent } from 'main.core.events';

export class Tags
{
	constructor(
		params: {
			groupId: number,
			containerNodeId: string,
			hiddenFieldId: string,
		}
	)
	{
		const containerNode = document.getElementById(params.containerNodeId);
		if (!containerNode)
		{
			return;
		}

		this.hiddenFieldNode = document.getElementById(params.hiddenFieldId);

		const tagSelector = new EntityTagSelector({
			addButtonCaption: Loc.getMessage('SONET_GCE_T_TAG_ADD'),
			addButtonCaptionMore: Loc.getMessage('SONET_GCE_T_KEYWORDS_ADD_TAG'),
			dialogOptions: {
				width: 350,
				height: 300,
				offsetLeft: 50,
				compactView: true,
				preload: true,
				context: 'PROJECT_TAG',
				searchTabOptions: {
					stubOptions: {
						title: Loc.getMessage('SONET_GCE_T_TAG_SEARCH_FAILED'),
						subtitle: Loc.getMessage('SONET_GCE_T_TAG_SEARCH_ADD_HINT'),
						arrow: true,
					}
				},
				entities: [
					{
						id: 'project-tag',
						options: {
							groupId: params.groupId,
						},
					},
				],
				searchOptions: {
					allowCreateItem: true,
					footerOptions: {
						label: Loc.getMessage('SONET_GCE_T_TAG_SEARCH_ADD_FOOTER_LABEL'),
					}
				},
				events: {
					'Item:onSelect': (event) => {
						this.recalcinputValue(event.getTarget().getSelectedItems());
					},
					'Item:onDeselect': (event) => {
						this.recalcinputValue(event.getTarget().getSelectedItems());
					},
					'Search:onItemCreateAsync': (event: BaseEvent) => {

						return new Promise((resolve) => {

							const { searchQuery } = event.getData();
							const name = searchQuery.getQuery().toLowerCase();
							const dialog = event.getTarget();

							setTimeout(() => {
								const tagsList = name.split(',');

								tagsList.forEach((tag) => {
									const item = dialog.addItem({
										id: tag,
										entityId: 'project-tag',
										title: tag,
										tabs: [ 'all', 'recents' ],
									});
									if (item)
									{
										item.select();
									}
								});

								resolve();
							}, 1000);
						});
					},
				},
			},
		});

		tagSelector.renderTo(containerNode);
	}

	recalcinputValue(items)
	{
		if (
			!Type.isArray(items)
			|| !Type.isDomNode(this.hiddenFieldNode)
		)
		{
			return;
		}

		const tagsList = [];


		items.forEach((item) => {
			tagsList.push(item.id);
		});

		this.hiddenFieldNode.value = tagsList.join(',');
	}
}
