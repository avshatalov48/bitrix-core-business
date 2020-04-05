;(function() {
	'use strict';

	BX.namespace('BX.Grid');


	/**
	 * BX.Grid.Rows
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.Rows = function(parent)
	{
		this.parent = null;
		this.rows = null;
		this.headChild = null;
		this.bodyChild = null;
		this.footChild = null;
		this.init(parent);
	};

	BX.Grid.Rows.prototype = {
		init: function(parent)
		{
			this.parent = parent;
		},

		reset: function()
		{
			this.rows = null;
			this.headChild = null;
			this.bodyChild = null;
			this.footChild = null;
		},

		enableDragAndDrop: function()
		{
			this.parent.arParams["ALLOW_ROWS_SORT"] = true;

			if (!(this.parent.getRowsSortable() instanceof BX.Grid.RowsSortable))
			{
				this.parent.rowsSortable = new BX.Grid.RowsSortable(this.parent);
			}
		},

		disableDragAndDrop: function()
		{
			this.parent.arParams["ALLOW_ROWS_SORT"] = false;
			if (this.parent.getRowsSortable() instanceof BX.Grid.RowsSortable)
			{
				this.parent.getRowsSortable().destroy();
				this.parent.rowsSortable = null;
			}
		},

		getFootLastChild: function()
		{
			return this.getLast(this.getFootChild());
		},

		getFootFirstChild: function()
		{
			return this.getFirst(this.getFootChild());
		},

		getBodyLastChild: function()
		{
			return this.getLast(this.getBodyChild());
		},

		getBodyFirstChild: function()
		{
			return this.getFirst(this.getBodyChild());
		},

		getHeadLastChild: function()
		{
			return this.getLast(this.getHeadChild());
		},

		getHeadFirstChild: function()
		{
			return this.getFirst(this.getHeadChild());
		},

		getEditSelectedValues: function()
		{
			var selectedRows = this.getSelected();
			var values = {};

			selectedRows.forEach(
				function(current)
				{
					values[current.getId()] = current.editGetValues();
				}
			);

			return values;
		},

		getSelectedIds: function()
		{
			return this.getSelected().map(function(current) {
				return current.getId();
			});
		},

		initSelected: function()
		{
			var selected = this.getSelected();

			if (BX.type.isArray(selected) && selected.length)
			{
				selected.forEach(function(row) {
					row.initSelect();
				});

				this.parent.enableActionsPanel();
			}
		},

		editSelected: function()
		{
			this.getSelected().forEach(function(current) {
				current.edit();
			});

			BX.onCustomEvent(window, 'Grid::thereEditedRows', []);
		},

		editSelectedCancel: function()
		{
			this.getSelected().forEach(function(current) {
				current.editCancel();
			});

			BX.onCustomEvent(window, 'Grid::noEditedRows', []);
		},

		isSelected: function()
		{
			return this.getBodyChild().some(function(current) {
				return current.isShown() && current.isSelected();
			});
		},

		isAllSelected: function()
		{
			return !this.getBodyChild().some(function(current) {
				return !current.isSelected();
			});
		},

		getParent: function()
		{
			return this.parent;
		},

		getCountSelected: function()
		{
			var result;

			try {
				result = this.getSelected().filter(function(row) {
					return !row.isNotCount() && row.isShown();
				}).length;
			} catch(err) {
				result = 0;
			}

			return result;
		},

		getCountDisplayed: function()
		{
			var result;

			try {
				result = this.getBodyChild().filter(function(row) { return row.isShown() && !row.isNotCount(); }).length;
			} catch(err) {
				result = 0;
			}

			return result;
		},

		addRows: function(rows)
		{
			var body = BX.findChild(
				this.getParent().getTable(),
				{tag: 'TBODY'},
				true,
				false
			);

			rows.forEach(function(current) {
				body.appendChild(current);
			});
		},


		/**
		 * Gets all rows of table
		 * @return {BX.Grid.Row[]}
		 */
		getRows: function()
		{
			var result;
			var self = this;

			if (!this.rows)
			{
				result = [].slice.call(this.getParent().getTable().querySelectorAll('tr[data-id], thead > tr'));

				this.rows = result.map(function(current) {
					return new BX.Grid.Row(self.parent, current);
				});
			}

			return this.rows;
		},


		/**
		 * Gets selected rows
		 * @return {BX.Grid.Row[]}
		 */
		getSelected: function()
		{
			return this.getBodyChild().filter(function(current) {
				return current.isShown() && current.isSelected();
			});
		},

		normalizeNode: function(node)
		{
			if (!BX.hasClass(node, this.getParent().settings.get('classBodyRow')))
			{
				node = BX.findParent(node, {className: this.getParent().settings.get('classBodyRow')}, true, false);
			}

			return node;
		},


		/**
		 * Gets BX.Grid.Row by id
		 * @param {string|number} id
		 * @return {?BX.Grid.Row}
		 */
		getById: function(id)
		{
			id = id.toString();
			var rows = this.getBodyChild();
			var row = rows.filter(function(current) {
				return current.getId() === id;
			});

			return row.length === 1 ? row[0] : null;
		},


		/**
		 * Gets BX.Grid.Row for tr node
		 * @param {HTMLTableRowElement} node
		 * @return {?BX.Grid.Row}
		 */
		get: function(node)
		{
			var result = null;
			var filter;

			if (BX.type.isDomNode(node))
			{
				node = this.normalizeNode(node);

				filter = this.getRows().filter(function(current) {
					return node === current.getNode();
				});

				if (filter.length)
				{
					result = filter[0];
				}
			}

			return result;
		},

		/** @static @method getLast */
		getLast: function(array)
		{
			var result;

			try {
				result = array[array.length-1];
			} catch (err) {
				result = null;
			}

			return result;
		},

		/** @static @method getFirst */
		getFirst: function(array)
		{
			var result;

			try {
				result = array[0];
			} catch (err) {
				result = null;
			}

			return result;
		},

		getHeadChild: function()
		{
			this.headChild = this.headChild || this.getRows().filter(function(current) {
					return current.isHeadChild();
				});

			return this.headChild;
		},


		/**
		 * Gets child rows of tbody
		 * @return {BX.Grid.Row[]}
		 */
		getBodyChild: function()
		{
			this.bodyChild = this.bodyChild || this.getRows().filter(function(current) {
				return current.isBodyChild();
			});

			return this.bodyChild;
		},

		getFootChild: function()
		{
			this.footChild = this.footChild || this.getRows().filter(function(current) {
				return current.isFootChild();
			});

			return this.footChild;
		},


		selectAll: function()
		{
			this.getRows().map(function(current) {
				current.isShown() && current.select();
			});
		},

		unselectAll: function()
		{
			this.getRows().map(function(current) {
				current.unselect();
			});
		},


		/**
		 * Gets row by rowIndex
		 * @param {number} rowIndex
		 * @return {?BX.Grid.Row}
		 */
		getByIndex: function(rowIndex)
		{
			var filter = this.getBodyChild()
				.filter(function(item) {
					return item;
				})
				.filter(function(item) {
					return item.getNode().rowIndex === rowIndex;
				});

			return filter.length ? filter[0] : null;
		},


		/**
		 * Gets child rows
		 * @param {number|string} parentId
		 * @param {boolean} [recursive]
		 * @return {BX.Grid.Row[]}
		 */
		getRowsByParentId: function(parentId, recursive)
		{
			var result = [];
			var self = this;

			if (!parentId)
			{
				return result;
			}

			parentId = parentId.toString();

			function getByParentId(parentId)
			{
				self.getBodyChild().forEach(function(row) {
					if (row.getParentId() === parentId) {
						result.push(row);
						recursive && getByParentId(row.getId());
					}
				}, self);
			}

			getByParentId(parentId);

			return result;
		},

		getRowsByGroupId: function(groupId)
		{
			var result = [];
			var self = this;

			if (!groupId)
			{
				return result;
			}

			groupId = groupId.toString();

			function getByParentId(groupId)
			{
				self.getBodyChild().forEach(function(row) {
					if (row.getGroupId() === groupId && !row.isCustom()) {
						result.push(row);
					}
				}, self);
			}

			getByParentId(groupId);

			return result;
		},

		getExpandedRows: function()
		{
			return this.getRows().filter(function(row) {
				return row.isShown() && row.isExpand();
			});
		},

		getIdsExpandedRows: function()
		{
			return this.getExpandedRows().map(function(row) {
				return row.getId();
			});
		},


		getIdsCollapsedGroups: function()
		{
			return this.getRows().filter(function(row) {
				return row.isCustom() && !row.isExpand();
			}).map(function(row) {
				return row.getId();
			});
		},


		/**
		 * @return {HTMLElement[]}
		 */
		getSourceRows: function()
		{
			return BX.Grid.Utils.getByTag(this.getParent().getTable(), 'tr');
		},


		/**
		 * @return {HTMLElement[]}
		 */
		getSourceBodyChild: function()
		{
			return this.getSourceRows().filter(function(current) {
				return BX.Grid.Utils.closestParent(current).nodeName === 'TBODY';
			});
		},


		/**
		 * @return {HTMLElement[]}
		 */
		getSourceHeadChild: function()
		{
			return this.getSourceRows().filter(function(current) {
				return BX.Grid.Utils.closestParent(current).nodeName === 'THEAD';
			});
		},


		/**
		 * @return {HTMLElement[]}
		 */
		getSourceFootChild: function()
		{
			return this.getSourceRows().filter(function(current) {
				return BX.Grid.Utils.closestParent(current).nodeName === 'TFOOT';
			});
		}
	};
})();
