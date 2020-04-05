;(function() {
	'use strict';

	BX.namespace('BX.Grid');


	/**
	 * Updates grid
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.Updater = function(parent)
	{
		this.parent = parent;
	};


	/**
	 * Gets parent object
	 * @return {?BX.Main.grid}
	 */
	BX.Grid.Updater.prototype.getParent = function()
	{
		return this.parent;
	};


	/**
	 * Updates head rows
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.updateHeadRows = function(rows)
	{
		var headers;

		if (BX.type.isArray(rows) && rows.length)
		{
			headers = this.getParent().getHeaders();
			headers.forEach(function(header) {
				header = BX.cleanNode(header);
				rows.forEach(function(row) {
					if (BX.type.isDomNode(row))
					{
						header.appendChild(BX.clone(row));
					}
				});
			});
		}
	};


	/**
	 * Appends head rows
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.appendHeadRows = function(rows)
	{
		var headers;

		if (BX.type.isArray(rows) && rows.length)
		{
			headers = this.getParent().getHeaders();

			headers.forEach(function(header) {
				rows.forEach(function(row) {
					if (BX.type.isDomNode(row))
					{
						header.appendChild(BX.clone(row));
					}
				});
			});
		}
	};


	/**
	 * Prepends head rows
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.prependHeadRows = function(rows)
	{
		var headers;

		if (BX.type.isArray(rows) && rows.length)
		{
			headers = this.getParent().getHeaders();

			headers.forEach(function(header) {
				header = BX.cleanNode(header);
				rows.forEach(function(row) {
					if (BX.type.isDomNode(row))
					{
						header.prepend(BX.clone(row));
					}
				});
			});
		}
	};


	/**
	 * Updates body row by row id
	 * @param {?string|number} id
	 * @param {HTMLTableRowElement} row
	 */
	BX.Grid.Updater.prototype.updateBodyRowById = function(id, row)
	{
		if ((BX.type.isNumber(id) || BX.type.isNotEmptyString(id)) && BX.type.isDomNode(row))
		{
			var currentRow = this.getParent().getRows().getById(id);

			if (currentRow)
			{
				var currentNode = currentRow.getNode();
				BX.insertAfter(row, currentNode);
				BX.remove(currentNode);
			}
		}
	};


	/**
	 * Updates all body rows.
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.updateBodyRows = function(rows)
	{
		if (BX.type.isArray(rows))
		{
			var body = this.getParent().getBody();
			body.innerHTML = '';

			rows.forEach(function(current) {
				!!current && body.appendChild(current);
			});
		}
	};


	/**
	 * Appends body rows.
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.appendBodyRows = function(rows)
	{
		var body;

		if (BX.type.isArray(rows))
		{
			body = this.getParent().getBody();
			rows.forEach(function(current) {
				if (BX.type.isDomNode(current))
				{
					body.appendChild(current);
				}
			});
		}
	};


	/**
	 * Prepends body rows
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.prependBodyRows = function(rows)
	{
		var body;

		if (BX.type.isArray(rows))
		{
			body = this.getParent().getBody();
			rows.forEach(function(current) {
				if (BX.type.isDomNode(current))
				{
					BX.prepend(body, current);
				}
			});
		}
	};


	/**
	 * Updates table footer rows.
	 * @param {?HTMLTableRowElement[]} rows
	 */
	BX.Grid.Updater.prototype.updateFootRows = function(rows)
	{
		var foot;

		if (BX.type.isArray(rows))
		{
			foot = BX.cleanNode(this.getParent().getFoot());
			rows.forEach(function(current) {
				if (BX.type.isDomNode(current))
				{
					foot.appendChild(current);
				}
			});
		}
	};


	/**
	 * Updates total rows counter
	 * @param {?HTMLElement} counter
	 */
	BX.Grid.Updater.prototype.updateCounterTotal = function(counter)
	{
		var counterCell;

		if (BX.type.isDomNode(counter))
		{
			counterCell = BX.cleanNode(this.getParent().getCounterTotal());
			counterCell.appendChild(counter);
		}
	};


	/**
	 * Updates grid pagination
	 * @param {?HTMLElement} pagination
	 */
	BX.Grid.Updater.prototype.updatePagination = function(pagination)
	{
		var paginationCell = this.getParent().getPagination().getContainer();

		if (!!paginationCell)
		{
			paginationCell.innerHTML = '';

			if (BX.type.isDomNode(pagination))
			{
				paginationCell.appendChild(pagination);
			}
		}
	};


	/**
	 * Updates more button
	 * @param {?HTMLElement} button
	 */
	BX.Grid.Updater.prototype.updateMoreButton = function(button)
	{
		if (BX.type.isDomNode(button))
		{
			var buttonParent = BX.Grid.Utils.closestParent(this.getParent().getMoreButton().getNode());
			buttonParent.innerHTML = '';
			buttonParent.appendChild(button);
		}
	};


	/**
	 * Updates group actions panel
	 * @param {HTMLElement} panel
	 */
	BX.Grid.Updater.prototype.updateGroupActions = function(panel)
	{
		var GroupActions = this.parent.getActionsPanel();

		if (!!GroupActions && BX.type.isDomNode(panel))
		{
			var panelNode = GroupActions.getPanel();

			if (BX.type.isDomNode(panelNode))
			{
				panelNode.innerHTML = '';

				var panelChild = BX.firstChild(panel);

				if (BX.type.isDomNode(panelChild))
				{
					panelNode.appendChild(panelChild);
				}
			}
		}
	};
})();