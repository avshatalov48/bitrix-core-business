;(function() {

"use strict";

BX.namespace("BX.Kanban");

/**
 *
 * @param {BX.Kanban.Grid} grid
 * @param {object} options
 * @constructor
 */
BX.Kanban.DropZoneArea = function(grid, options)
{
	this.dropZoneType = this.getDropZoneType(options.dropZoneType);
	this.dropZoneTimeout =
		BX.type.isNumber(options.dropZoneTimeout) && options.dropZoneTimeout > 500 ? options.dropZoneTimeout : 8000;

	/** @var {BX.Kanban.Grid} **/
	this.grid = grid;

	this.layout = {
		container: null
	};

	this.dropZones = Object.create(null);
	this.dropZonesOrder = [];
};

BX.Kanban.DropZoneArea.prototype =
{
	/**
	 * @returns {BX.Kanban.Grid}
	 */
	getGrid: function()
	{
		return this.grid;
	},
	/**
	 *
	 * @param {object} options
	 * @returns {BX.Kanban.DropZone|null}
	 */
	addDropZone: function(options)
	{
		options = options || {};

		if (this.getDropZone(options.id) !== null)
		{
			return null;
		}

		var dropZoneType = this.getDropZoneType(options.type);
		var dropZone = new dropZoneType(options);
		if (!(dropZone instanceof BX.Kanban.DropZone))
		{
			throw new Error("DropZone type must be an instance of BX.Kanban.DropZone");
		}

		dropZone.setDropZoneArea(this);
		this.dropZones[dropZone.getId()] = dropZone;

		var targetDropZone = this.getDropZone(options.targetId);
		var targetIndex = BX.util.array_search(targetDropZone, this.dropZonesOrder);
		if (targetIndex >= 0)
		{
			this.dropZonesOrder.splice(targetIndex, 0, dropZone);
		}
		else
		{
			this.dropZonesOrder.push(dropZone);
		}

		if (this.getGrid().isRendered())
		{
			this.render();
		}

		return dropZone;
	},

	updateDropZone: function(dropZone, options)
	{
		dropZone = this.getDropZone(dropZone);
		if (!dropZone)
		{
			return false;
		}

		dropZone.setOptions(options);
		dropZone.render();

		return true;
	},

	/**
	 *
	 * @param {BX.Kanban.DropZone|string|number} dropZoneId
	 * @returns {BX.Kanban.Item}
	 */
	removeDropZone: function(dropZoneId)
	{
		var dropZone = this.getDropZone(dropZoneId);
		if (dropZone)
		{
			this.dropZonesOrder = this.dropZonesOrder.filter(function(element) {
				return dropZone !== element;
			});

			delete this.dropZones[dropZone.getId()];

			if (this.getGrid().isRendered())
			{
				this.render();
			}
		}

		return dropZone;
	},

	render: function()
	{
		var dropZoneItems = document.createDocumentFragment();
		var dropZones = this.getDropZones();
		for (var i = 0; i < dropZones.length; i++)
		{
			dropZoneItems.appendChild(dropZones[i].render());
		}

		BX.cleanNode(this.getContainer());
		this.getContainer().appendChild(dropZoneItems);
	},

	/**
	 *
	 * @param {string|number|BX.Kanban.DropZone} dropZone
	 * @returns {BX.Kanban.DropZone}
	 */
	getDropZone: function(dropZone)
	{
		var dropZoneId = dropZone instanceof BX.Kanban.DropZone ? dropZone.getId() : dropZone;

		return this.dropZones[dropZoneId] ? this.dropZones[dropZoneId] : null;
	},

	/**
	 *
	 * @returns {BX.Kanban.DropZone[]}
	 */
	getDropZones: function()
	{
		return this.dropZonesOrder;
	},

	getDropZonesCount: function()
	{
		return this.dropZonesOrder.length;
	},

	/**
	 *
	 * @param {string} [className]
	 * @returns {BX.Kanban.DropZone}
	 */
	getDropZoneType: function(className)
	{
		var classFn = BX.Kanban.Utils.getClass(className);
		if (BX.type.isFunction(classFn))
		{
			return classFn;
		}

		return this.dropZoneType || BX.Kanban.DropZone;
	},

	getDropZoneTimeout: function()
	{
		return this.dropZoneTimeout;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getContainer: function()
	{
		if (this.layout.container)
		{
			return this.layout.container;
		}

		this.layout.container = BX.create("div", {
			attrs: {
				className: "main-kanban-dropzone-area"
			}
		});

		return this.layout.container;
	},

	emptyAll: function()
	{
		this.getDropZones().forEach(function(/*BX.Kanban.DropZone*/dropZone) {
			dropZone.empty();
		});
	},

	show: function()
	{
		if (this.getDropZonesCount())
		{
			this.getContainer().classList.add("main-kanban-dropzone-show");
		}
	},

	hide: function()
	{
		this.getContainer().classList.remove("main-kanban-dropzone-show");
	},

	setActive: function()
	{
		this.getContainer().classList.add("main-kanban-dropzone-area-active");
	},

	unsetActive: function()
	{
		this.getContainer().classList.remove("main-kanban-dropzone-area-active");
	}
};

})();
