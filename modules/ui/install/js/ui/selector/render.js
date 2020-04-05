(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Selector.Render)
{
	return;
}

BX.UI.Selector.Render = function(params)
{
	this.selectorInstance = params.selectorInstance;
	this.selectorManager = this.selectorInstance.manager;
	this.class = {

		popup: 'bx-finder-popup bx-finder-v2',

		boxCommon: 'bx-lm-box',
		boxDefault: 'bx-lm-socnet-log-destination',
		boxContainer: 'bx-finder-box',
		boxContainerVertical: 'bx-finder-box-vertical',

		containerContent: 'bx-finder-container-content',
		containerSearchBlock: 'bx-finder-search-block',
		containerSearchBlockCell: 'bx-finder-search-block-cell',
		containerSearchBlockInputBox: 'feed-add-destination-input-box',
		containerSearchBlockInput: 'feed-add-destination-inp',

		tabsContainer: 'bx-finder-box-tabs',
		tabsContentContainer: 'bx-finder-box-tabs-content',
		tabsContentContainerWindow: 'bx-finder-box-tabs-content-window',
		tabsContentContainerTable: 'bx-finder-box-tabs-content-table',
		tabsContentContainerCell: 'bx-finder-box-tabs-content-cell',

		tab: 'bx-finder-box-tab',
		tabLast: 'bx-lm-tab-last',
		tabSelected: 'bx-finder-box-tab-selected',

		tabContent: 'bx-finder-box-tab-content',
		tabContentSelected: 'bx-finder-box-tab-content-selected',
		tabContentPrefix: 'bx-lm-box-tab-content-',

		groupBox: 'bx-finder-groupbox',
		groupBoxPrefix: 'bx-finder-groupbox-',
		groupBoxSearch: 'bx-lm-groupbox-search',
		groupBoxName: 'bx-finder-groupbox-name',
		groupBoxContent: 'bx-finder-groupbox-content',

		item: 'bx-finder-box-item-t7',
		itemElement: 'bx-finder-element',
		itemElementExtranet: 'bx-lm-element-extranet',
		itemElementEmail: 'bx-lm-element-email',
		itemElementCrmEmail: 'bx-lm-element-crmemail',
		itemElementVacation: 'bx-lm-element-vacation',

		itemElementTypePrefix: 'bx-lm-element-',
		itemHover: 'bx-finder-box-item-t7-hover',
		itemSelected: 'bx-finder-box-item-t7-selected',
		itemShowDescriptionMode: 'bx-finder-box-item-t7-desc-mode',
		itemAvatarlessMode: 'bx-finder-box-item-t7-avatarless',
		itemAvatar: 'bx-finder-box-item-t7-avatar',
		itemAvatarCustom: 'bx-finder-box-item-t7-avatar-custom',
		itemAvatarImage: 'bx-finder-box-item-t7-avatar-img',
		itemAvatarStatus: 'bx-finder-box-item-avatar-status',
		itemSpace: 'bx-finder-box-item-t7-space',
		itemInfo: 'bx-finder-box-item-t7-info',
		itemName: 'bx-finder-box-item-t7-name',
		itemDescription: 'bx-finder-box-item-t7-desc',

		itemDestination: 'feed-add-post-destination',
		itemDestinationPrefix: 'feed-add-post-destination-',
		itemDestinationUndeletable: 'feed-add-post-destination-undelete',
		itemDestinationHover: 'feed-add-post-destination-hover',
		itemDestinationText: 'feed-add-post-destination-text',
		itemDestinationDeleteButton: 'feed-add-post-del-but',




		treeBranch: 'bx-finder-company-department',
		treeBranchOpened: 'bx-finder-company-department-opened',
		treeBranchInner: 'bx-finder-company-department-inner',
		treeBranchArrow: 'bx-finder-company-department-arrow',
		treeBranchText: 'bx-finder-company-department-text',
		treeBranchCheckBox: 'bx-finder-company-department-check',
		treeBranchCheckBoxSelected: 'bx-finder-company-department-check-checked',
		treeBranchCheckBoxInner: 'bx-finder-company-department-check-inner',
		treeBranchCheckBoxArrow: 'bx-finder-company-department-check-arrow',
		treeBranchCheckBoxText: 'bx-finder-company-department-check-text',
		treeBranchLeavesContainer: 'bx-finder-company-department-children',
		treeBranchLeavesContainerOpened: 'bx-finder-company-department-children-opened',
		treeBranchLeavesWaiter: 'bx-finder-company-department-employees-loading',
		treeLeavesList: 'bx-finder-company-department-employees',
		treeLeaf: 'bx-finder-company-department-employee',
		treeLeafSelected: 'bx-finder-company-department-employee-selected',
		treeLeafAvatar: 'bx-finder-company-department-employee-avatar',
		treeLeafName: 'bx-finder-company-department-employee-name',
		treeLeafInfo: 'bx-finder-company-department-employee-info',
		treeLeafDescription: 'bx-finder-company-department-employee-position',

		searchWaiter: 'bx-finder-box-search-waiter',
		searchWaiterBackground: 'bx-finder-box-search-waiter-background',
		searchWaiterText: 'bx-finder-box-search-waiter-text'
	};
};

BX.UI.Selector.Render.create = function(params)
{
	return new BX.UI.Selector.Render(params);
};

BX.UI.Selector.Render.prototype.hoverItem = function(params)
{
	var
		node = params.node;

	node.classList.add(this.class.itemHover);
};

BX.UI.Selector.Render.prototype.unhoverItem = function(params)
{
	var
		node = params.node;

	node.classList.remove(this.class.itemHover);
};

BX.UI.Selector.Render.prototype.deleteItem = function(params)
{
	var
		entityType = params.entityType,
		itemId = params.itemId;

	if (this.selectorInstance.result.hasOwnProperty(entityType))
	{
		var elementId = this.selectorInstance.getItemNodeId({ entityType: entityType, itemId: itemId });
		if (BX(elementId))
		{
			BX.removeClass(BX(elementId), this.class.itemSelected);
		}
	}

	if (this.selectorInstance.callback.unSelect)
	{
		this.selectorInstance.callback.unSelect({
			item: this.selectorInstance.entities[entityType].items[itemId],
			entityType: entityType,
			selectorId: this.selectorInstance.id
		});
	}
};

})();
