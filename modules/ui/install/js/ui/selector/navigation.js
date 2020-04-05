(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Selector.Navigation)
{
	return;
}

BX.UI.Selector.Navigation = function(params)
{
	this.selectorInstance = params.selectorInstance;
	this.selectorManager = this.selectorInstance.manager;
};

BX.UI.Selector.Navigation.create = function(params)
{
	return new BX.UI.Selector.Navigation(params);
};

BX.UI.Selector.Navigation.prototype.checkKeyboardNavigation = function(params)
{
	var
		keyCode = (params.keyCode ? params.keyCode : false),
		tab = (params.tab ? params.tab : false);

	if (keyCode == 37)
	{
		this.moveCurrentItem({
			tab: tab,
			direction: 'left'
		});

		return 'move';
	}
	else if (keyCode == 38)
	{
		this.moveCurrentItem({
			tab: tab,
			direction: 'up'
		});

		return 'move';
	}
	else if (keyCode == 39)
	{
		this.moveCurrentItem({
			tab: tab,
			direction: 'right'
		});

		return 'move';
	}
	else if (keyCode == 40)
	{
		this.moveCurrentItem({
			tab: tab,
			direction: 'down'
		});

		return 'move';
	}
	else if (keyCode == 13)
	{
		this.selectCurrentItem({
			tab: tab,
			keyCode: keyCode
		});

		return 'enter';
	}
	else if (
		keyCode == 32 // space
		&& tab != 'search'
	)
	{
		this.selectCurrentItem({
			tab: tab,
			keyCode: keyCode
		});

		return 'space';
	}

	return false;
};

BX.UI.Selector.Navigation.prototype.selectCurrentItem = function(params)
{
	var
		tab = params.tab,
		closeDialog = (typeof params.keyCode != 'undefined' && params.keyCode == 13);

	if (
		!this.selectorInstance.popups.search
		&& !this.selectorInstance.popups.main
		&& !this.selectorInstance.popups.container
	)
	{
		return;
	}

	if (tab == 'search')
	{
		clearTimeout(this.selectorInstance.timeouts.search);
		this.selectorInstance.getSearchInstance().abortSearchRequest();
	}

	var currentItem = this.selectorInstance.cursors[tab].currentItem;
	if (BX.type.isNotEmptyObject(currentItem))
	{
		var currentItemId = this.selectorInstance.getItemNodeId({
			entityType: currentItem.entityType,
			itemId: currentItem.itemCode
		});

		if (BX(currentItemId))
		{
			this.selectorInstance.selectItem({
				entityType: BX(currentItemId).getAttribute('data-entity-type'),
				itemNode: BX(currentItemId),
				itemId: currentItem.itemCode,
				tab: tab
			});
		}

		if (closeDialog)
		{
			this.selectorInstance.cursors[tab].currentItem = null;

			if (this.selectorInstance.isDialogOpen())
			{
				this.selectorInstance.closeDialog();
			}
			this.selectorInstance.closeSearch();
		}
	}
};

BX.UI.Selector.Navigation.prototype.selectFirstItem = function(params)
{
	var
		tab = params.tab;

	if (
		!this.selectorInstance.popups.search
		&& !this.selectorInstance.popups.main
		&& !this.selectorInstance.popups.container
	)
	{
		return;
	}

	if (tab == 'search')
	{

		clearTimeout(this.selectorInstance.timeouts.search);
		this.selectorInstance.getSearchInstance().abortSearchRequest();
	}

	var firstItem = this.selectorInstance.cursors[tab].firstItem;
	if (BX.type.isNotEmptyObject(firstItem))
	{
		var firstItemId = this.selectorInstance.getItemNodeId({
			entityType: firstItem.entityType,
			itemId: firstItem.itemCode
		});

		if (BX(firstItemId))
		{
			this.selectorInstance.selectItem({
				entityType: BX(firstItemId).getAttribute('data-entity-type'),
				itemNode: BX(firstItemId),
				itemId: firstItem.itemCode
			});
		}
	}
};

BX.UI.Selector.Navigation.prototype.moveCurrentItem = function(params)
{
	var
		direction = params.direction,
		tab = params.tab;

	if (!direction)
	{
		return;
	}

	if (
		this.selectorInstance.popups.search == null
		&& this.selectorInstance.popups.main == null
		&& this.selectorInstance.popups.container == null
	)
	{
		return;
	}

	this.selectorInstance.resultChanged[tab] = true;

	if (
		tab == 'search'
		&& this.selectorInstance.searchXhr
	)
	{
		this.selectorInstance.getSearchInstance().abortSearchRequest();
		this.selectorInstance.getSearchInstance().hideSearchWaiter();
	}

	if (!BX.type.isNotEmptyObject(this.selectorInstance.cursors[tab]))
	{
		return;
	}

	var moved = false;

	switch (direction)
	{
		case 'left':
			if (this.selectorInstance.cursors[tab].position.column == 1)
			{
				if (typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row][this.selectorInstance.cursors[tab].position.column - 1] != 'undefined')
				{
					this.selectorInstance.cursors[tab].position.column--;
					moved = true;
				}
			}
			break;
		case 'right':
			if (
				this.selectorInstance.cursors[tab].position.column == 0
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row][this.selectorInstance.cursors[tab].position.column + 1] != 'undefined'
			)
			{
				this.selectorInstance.cursors[tab].position.column++;
				moved = true;
			}
			break;
		case 'up':
			if (
				this.selectorInstance.cursors[tab].position.row > 0
				&& typeof this.selectorInstance.result[tab] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row - 1] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row - 1][this.selectorInstance.cursors[tab].position.column] != 'undefined'
			)
			{
				this.selectorInstance.cursors[tab].position.row--;
				moved = true;
			}
			else if (
				this.selectorInstance.cursors[tab].position.row == 0
				&& typeof this.selectorInstance.result[tab] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group - 1] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group - 1][this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group - 1].length - 1] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group - 1][this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group - 1].length - 1][0] != 'undefined'
			)
			{
				this.selectorInstance.cursors[tab].position.row = this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group - 1].length - 1;
				this.selectorInstance.cursors[tab].position.column = 0;
				this.selectorInstance.cursors[tab].position.group--;
				moved = true;
			}
			break;
		case 'down':
			if (
				typeof this.selectorInstance.result[tab] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row + 1] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row + 1][this.selectorInstance.cursors[tab].position.column] != 'undefined'
			)
			{
				this.selectorInstance.cursors[tab].position.row++;
				moved = true;
			}
			else if (
				typeof this.selectorInstance.result[tab] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row + 1] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row + 1][0] != 'undefined'
			)
			{
				this.selectorInstance.cursors[tab].position.column = 0;
				this.selectorInstance.cursors[tab].position.row++;
				moved = true;
			}
			else if (
				typeof this.selectorInstance.result[tab] != 'undefined'
				&& this.selectorInstance.cursors[tab].position.row == (this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group].length - 1)
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group + 1] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group + 1][0] != 'undefined'
				&& typeof this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group + 1][0][0] != 'undefined'
			)
			{
				this.selectorInstance.cursors[tab].position.group++;
				this.selectorInstance.cursors[tab].position.row = 0;
				this.selectorInstance.cursors[tab].position.column = 0;
				moved = true;
			}
			break;
		default:
	}

	if (moved)
	{
		var currentItem = this.selectorInstance.cursors[tab].currentItem;

		var currentItemId = this.selectorInstance.getItemNodeId({
			entityType: currentItem.entityType,
			itemId: currentItem.itemCode
		});

		if (BX(currentItemId))
		{
			this.selectorInstance.getRenderInstance().unhoverItem({
				node: BX(currentItemId)
			});
		}

		currentItem = this.selectorInstance.result[tab][this.selectorInstance.cursors[tab].position.group][this.selectorInstance.cursors[tab].position.row][this.selectorInstance.cursors[tab].position.column];

		this.selectorInstance.cursors[tab].currentItem = currentItem;

		currentItemId = this.selectorInstance.getItemNodeId({
			entityType: currentItem.entityType,
			itemId: currentItem.itemCode
		});

		if (BX(currentItemId))
		{
			var
				i = 0,
				hoveredNode = BX(currentItemId);

			var containerNode = BX.findParent(this.selectorInstance.dialogNodes.contentsContainer, { className: this.selectorInstance.getRenderInstance().class.boxContainer });

			if (containerNode)
			{
				var
					containerNodePos = BX.pos(containerNode),
					hoveredNodePos = BX.pos(hoveredNode);

				if (
					hoveredNodePos.bottom > containerNodePos.bottom
					|| hoveredNodePos.top < containerNodePos.top
				)
				{
					containerNode.scrollTop += (
						hoveredNodePos.bottom > containerNodePos.bottom
							? (hoveredNodePos.bottom - containerNodePos.bottom)
							: (hoveredNodePos.top - containerNodePos.top)
					);
				}

				this.selectorInstance.getRenderInstance().hoverItem({
					node: hoveredNode
				});
			}
		}
	}
};

BX.UI.Selector.Navigation.prototype.hoverFirstItem = function(params)
{
	var
		tab = params.tab;

	if (typeof this.selectorInstance.cursors[tab] == 'undefined')
	{
		return;
	}

	var firstItem = this.selectorInstance.cursors[tab].firstItem;
	if (!firstItem)
	{
		return;
	}

	var firstItemId = this.selectorInstance.getItemNodeId({
		entityType: firstItem.entityType,
		itemId: firstItem.itemCode
	});

	if (!BX(firstItemId))
	{
		return;
	}

	this.selectorInstance.getRenderInstance().hoverItem({
		node: BX(firstItemId)
	});
};


})();
