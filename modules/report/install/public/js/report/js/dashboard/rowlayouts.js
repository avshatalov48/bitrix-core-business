;(function ()
{
	"use strict";
	BX.namespace("BX.Report.Dashboard.Row");

	/**
	 * @param options
	 * @constructor
	 */
	BX.Report.Dashboard.Row.BaseLayout = function (options)
	{
		this.cellCount = options.cellCount;
		this.row = options.row;
		this.cells = [];
	};
	BX.Report.Dashboard.Row.BaseLayout.prototype = {
		getClassName: function()
		{
			return 'BX.Report.Dashboard.Row.BaseLayout';
		},
		getRow: function ()
		{
			return this.row;
		},
		getWidgets: function ()
		{
			return this.getRow().getWidgets();
		},
		getCells: function()
		{
			return this.cells;
		},
		getFirstEmptyCell: function()
		{
			var cells = this.getCells();
			for (var id = 1; id <= cells.length; id++)
			{
				if (cells[id] instanceof BX.Report.Dashboard.Cell && cells[id].isEmpty())
				{
					return cells[id];
				}
			}

			return null;
		},
		getMiniatureContainer: function()
		{
			var miniatureContainer = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-miniature-container'
				},
				events: {
					click: this.handlerRowAddByMiniature.bind(this)
				}
			});

			for (var i = 1; i <= this.cellCount; i++)
			{
				var linearCell = BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-dashboard-miniature-cell'
					}
				});
				miniatureContainer.appendChild(linearCell);
			}
			return miniatureContainer;
		},
		getPseudoRowId: function()
		{
			return	"pseudo_" + Math.floor(Math.random()* (99999 - 15000)) + 15000 + "";
		},
		getPseudoCellId: function()
		{
			return "js_" + BX.util.getRandomString();
		},
		handlerRowAddByMiniature: function()
		{
			this.getRow().getBoard().addRow(new BX.Report.Dashboard.Row({
				id: this.getPseudoRowId(),
				pseudo: true,
				maxWidgetCount: "" + this.cellCount,
				layoutMap: this.getLayoutMap()
			}), this.getRow(), false);

			this.getRow().addRowPopup.close();
		},
		getLayoutMap: function()
		{
			var elements = [];
			for (var widgetContainerWrapperNum = 1; widgetContainerWrapperNum <= this.cellCount; widgetContainerWrapperNum++)
			{
				var element = {
					type: 'cell',
					id: this.getPseudoCellId(),
					removable: 0
				};
				elements.push(element);
			}
		/*	elements.push({
				type: 'cell',
				id: this.getPseudoCellId(),
				flexValue: 2
			});*/
			return {
				type: 'cell-container',
				orientation: 'horizontal',
				elements: elements
			};
		}

	};

	/**
	 * Analog of base layout class
	 * @param options
	 * @extends {BX.Report.Dashboard.Row.BaseLayout}
	 * @constructor
	 */
	BX.Report.Dashboard.Row.HorizontalLinear = function (options)
	{
		BX.Report.Dashboard.Row.BaseLayout.apply(this, arguments);
	};

	BX.Report.Dashboard.Row.HorizontalLinear.prototype = {
		__proto__: BX.Report.Dashboard.Row.BaseLayout.prototype,
		constructor: BX.Report.Dashboard.Row.HorizontalLinear,
		getClassName: function()
		{
			return 'BX.Report.Dashboard.Row.HorizontalLinear';
		}

	};



	/**
	 * @param options
	 * @constructor
	 */
	BX.Report.Dashboard.Row.LayoutFabric = function(options)
	{
		this.row = options.row;
		this.map = options.map;
		this.layout = null;
		this.cells = [];
	};

	BX.Report.Dashboard.Row.LayoutFabric.prototype = {
		render: function()
		{
			var layout = this.getLayout();
			this.fillCellsWithWidgets();
			return layout;
		},
		getRow: function()
		{
			return this.row;
		},
		getCells: function()
		{
			return this.cells;
		},
		getWidgets: function ()
		{
			return this.getRow().getWidgets();
		},
		getMapWithoutDomElements: function(params, nested)
		{
			if (!nested)
			{
				params = this.getMap();
			}
			var map = {};
			if (params['type'] === 'cell-container')
			{
				map['type'] = params['type'];
				map['orientation'] = params['orientation'];
				map['elements'] = [];
				for (var i in params['elements'])
				{
					map['elements'].push(this.getMapWithoutDomElements(params['elements'][i], true))
				}
			}
			else if (params['type'] === 'cell')
			{
				map['type'] = params['type'];
				map['id'] = params['id'];
				map['flexValue'] = params['flexValue'];
				map['height'] = params['height'];
			}
			return map;
		},
		getMap: function()
		{
			return this.map;
		},
		getFirstEmptyCell: function()
		{
			var cells = this.getCells();
			for (var id = 0; id <= cells.length; id++)
			{
				if (cells[id] instanceof BX.Report.Dashboard.Cell && cells[id].isEmpty())
				{
					return cells[id];
				}
			}

			return null;
		},
		getLayout: function()
		{
			if (this.layout)
			{
				return this.layout;
			}

			this.layout = this.buildNestedCellNodeTree(this.getMap(), false);
			return this.layout;
		},
		destroy: function()
		{
			BX.remove(this.getLayout());
			var cells = this.getCells();
			for (var i = 0; i < cells.length; i++)
			{
				cells[i].destroy();
			}
		},
		fillCellsWithWidgets: function()
		{
			var widgets = this.getWidgets();
			var cells = this.getCells();
			BX.Report.Dashboard.Utils.forEach(widgets, function (key)
			{
				for (var i = 0; i < cells.length; i++)
				{
					var position = widgets[key].getWeight();
					if (position === cells[i].getId() && cells[i].isEmpty())
					{
						cells[i].setWidget(widgets[key]);
					}
				}
			}.bind(this));
		},
		buildNestedCellNodeTree: function(params, isNested)
		{
			if (params['type'] === 'cell')
			{
				var cell = new BX.Report.Dashboard.Cell({
					id : params['id'],
					height: params['height'] || 380,
					flexValue: params['flexValue'] || 1,
					row: this.getRow()
				});
				this.cells.push(cell);

				var cellRendered = cell.render();
				params['domElement'] = cellRendered;
				return cellRendered;
			}


			if (params['type'] === 'cell-container')
			{
				var rowContainer;
				if (params['orientation'] === 'horizontal')
				{
					rowContainer = this.getCellRowContainer();
				}
				else if (params['orientation'] === 'vertical')
				{
					rowContainer = this.getCellColumnContainer();
				}


				if (params['elements'])
				{
					for (var i = 0; i < params['elements'].length; i++)
					{
						rowContainer.appendChild(this.buildNestedCellNodeTree(params['elements'][i], true));
					}
				}


				if (isNested)
				{
					var cellContainer = this.getCellContainer();
					cellContainer.style.flex =  params['flexValue'] || 1;
					cellContainer.appendChild(rowContainer);
					params['domElement'] = cellContainer;
					return cellContainer;
				}
				else
				{
					params['domElement'] = rowContainer;
					return rowContainer;
				}
			}
		},
		getCellContainer: function()
		{
			return BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-cell report-visualconstructor-dashboard-cell-container',
					"data-type": 'cell-container'
				}
			});
		},
		getCellRowContainer: function()
		{
			return BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-row report-visualconstructor-dashboard-row-direction-row'
				}
			});
		},
		getCellColumnContainer: function()
		{
			return BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-row report-visualconstructor-dashboard-row-direction-column'
				}
			});
		},
		transformCellToContainer: function(cell, params)
		{
			var parentContainer = this.findContainerContainsCell(cell, this.getMap());

			var cellIndexInContainerElements = this.findCellIndexInContainerElements(cell, parentContainer['elements']);

			var cellToTransferDomContainer = parentContainer['elements'][cellIndexInContainerElements]['domElement'];
			var cellToTransfer = Object.assign({}, parentContainer['elements'][cellIndexInContainerElements]);
			var newCellId = this.getPseudoCellId();
			cellToTransfer.flexValue = 1;
			//TODO: remove hardcode from here, separation of container co cells will universal
			cellToTransfer.height = 380;
			var elements = [
				cellToTransfer
			];

			for (var i = 1; i < params.cellCount; i++)
			{
				elements.push({
					type: 'cell',
					id: newCellId,
					height: 180,
					flexValue: 1,
					removable: 1
				});
			}


			parentContainer['elements'][cellIndexInContainerElements] = {
				type: 'cell-container',
				flexValue: parentContainer['elements'][cellIndexInContainerElements]['flexValue'] || 1,
				orientation: params['orientation'],
				elements: elements
			};
			var oldCellIndex = BX.util.array_search(cell, this.getCells());
			this.cells.splice(oldCellIndex, 1);
			var replaceCellLayout = this.buildNestedCellNodeTree(parentContainer['elements'][cellIndexInContainerElements], true);

			parentContainer['domElement'].replaceChild(replaceCellLayout, cellToTransferDomContainer);

			this.fillCellsWithWidgets();
		},
		findContainerContainsCell: function(cell, containerMap)
		{
			var map = containerMap;

			if (map['type'] === 'cell-container')
			{
				for (var i = 0; i < map['elements'].length; i++)
				{
					if (map['elements'][i]['type'] === 'cell-container')
					{
						var result = this.findContainerContainsCell(cell, map['elements'][i]);
						if (result)
						{
							return result;
						}
					}

					if (map['elements'][i]['type'] === 'cell')
					{
						if (cell.getId() === map['elements'][i]['id'])
						{
							return containerMap;
						}
					}
				}
			}

			return null;
		},
		findCellIndexInContainerElements: function(cell, elements)
		{
			for (var i = 0; i < elements.length; i++)
			{
				if (elements[i]['id'] === cell.getId())
				{
					return i;
				}
			}
			return null;
		},
		getCellById: function(id)
		{
			var cells = this.getCells();
			for (var i = 0; i < cells.length; i++)
			{
				if (cells[i].getId() === id)
				{
					return cells[i]
				}
			}
			return false;
		},
		adjustNoEmptyCellsInCellLevel: function(cell)
		{
			var cellsContainsContainerMap = this.findContainerContainsCell(cell, this.getMap());
			var elementIndex = this.findCellIndexInContainerElements(cell, cellsContainsContainerMap['elements']);
			var currentCellInMap = cellsContainsContainerMap['elements'][elementIndex];

			for (var num = 0; num < cellsContainsContainerMap['elements'].length; num++)
			{
				if (cellsContainsContainerMap['elements'][num]['type'] === 'cell')
				{
					var cellById = this.getCellById(cellsContainsContainerMap['elements'][num]['id']);
					if (cellById.getId() !== currentCellInMap['id'] && !cellById.isEmpty())
					{
						var cellClientHeight = cellById.getContainer().clientHeight;
						var widgetHeight = cellById.getWidget().getHeight();

						var maxWidgetsByHeightCanBeHere = Math.round(cellClientHeight / widgetHeight);
						if (maxWidgetsByHeightCanBeHere > 1)
						{
							this.transformCellToContainer(cellById, {
								orientation: 'vertical',
								cellCount: 2
							});
						}
					}
				}
			}
		},
		adjustCellToMaxSize: function(cell)
		{
			var cellsContainsContainerMap = this.findContainerContainsCell(cell, this.getMap());

			if (cellsContainsContainerMap['orientation'] === 'vertical')
			{
				if (!this.isCellsContainerHasFilledCell(cellsContainsContainerMap))
				{
					this.clearCellsFromCollectionByContainer(cellsContainsContainerMap);
					var oldDomElement = cellsContainsContainerMap.domElement;
					cellsContainsContainerMap.type = 'cell';
					cellsContainsContainerMap.flexValue = 1;
					cellsContainsContainerMap.id = this.getPseudoCellId();
					delete cellsContainsContainerMap.orientation;
					delete cellsContainsContainerMap.elements;
					var newDomElement = this.buildNestedCellNodeTree(cellsContainsContainerMap, false);
					this.getLayout().replaceChild(newDomElement, oldDomElement);
					cellsContainsContainerMap.domElement = newDomElement;
				}
			}

		},
		clearCellsFromCollectionByContainer: function(containerMap)
		{
			if (containerMap['type'] === 'cell-container')
			{
				for (var num = 0; num < containerMap['elements'].length; num++)
				{
					if (containerMap['elements'][num]['type'] === 'cell')
					{
						var cell = this.getCellById(containerMap['elements'][num]['id']);
						var oldCellIndex = BX.util.array_search(cell, this.getCells());
						this.cells.splice(oldCellIndex, 1);
					}
					else
					{
						this.clearCellsFromCollectionByContainer(containerMap['elements'][num]);
					}
				}
			}

		},
		isCellsContainerHasFilledCell: function(cellsContainContainerMap)
		{
			for (var number = 0; number < cellsContainContainerMap['elements'].length; number++)
			{
				if (cellsContainContainerMap['elements'][number]['type'] === 'cell')
				{
					var cellById = this.getCellById(cellsContainContainerMap['elements'][number]['id']);
					if (!cellById.isEmpty())
					{
						return true;
					}
				}
			}

			return false;
		},
		getPseudoCellId: function()
		{
			return "js_" + BX.util.getRandomString();
		}

	}

})();