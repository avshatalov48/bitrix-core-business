;(function () {

'use strict';

BX.namespace('BX.UI');

BX.UI.AccessRights.Section = function(options) {
	this.id = options.id ? options.id : null;
	this.headSection = options.headSection ? options.headSection : null;
	this.title = options.title;
	this.rights = options.rights ? options.rights : [];
	this.userGroups = options.userGroups ? options.userGroups : [];
	this.grid = options.grid ? options.grid : null;

	this.layout = {
		title: null,
		headColumn: null,
		columns: null,
		content: null,
		earLeft: null,
		earRight: null
	};

	this.scroll = 0;
	this.earTimer = null;
	this.earLeftTimer = null;
	this.earRightTimer = null;
	this.columns = [];

	this.bindEvents();
};

BX.UI.AccessRights.Section.prototype = {

	bindEvents: function()
	{
		BX.addCustomEvent('AccessRights.Section:scrollLeft', function(object) {
			if (this.title !== object.title)
			{
				this.getColumnsContainer().scrollLeft = object.getScroll()
			}
			object.adjustEars();
			BX.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
		}.bind(this));

		BX.addCustomEvent('AccessRights.Section:scrollRight', function(object) {
			if (this.title !== object.title)
			{
				this.getColumnsContainer().scrollLeft = object.getScroll()
			}
			object.adjustEars();
			BX.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
		}.bind(this));

		BX.bind(window, 'resize', this.adjustEars.bind(this));
	},

	addColumn: function(param)
	{
		if(!param)
		{
			return;
		}

		var options = Object.assign({}, param);
		options.userGroup = param;
		var column = this.getColumn(options);

		this.layout.columns.appendChild(column.render());
		this.columns.push(column);
	},

	removeColumn: function(param)
	{
		if(!param)
		{
			return;
		}

		for (var i = 0; i < this.columns.length; i++)
		{
			if(param.userGroup === this.columns[i].userGroup)
			{
				this.columns[i].remove();
				break;
			}

		}
	},

	getColumn: function(options)
	{
		var controls = [];

		this.rights.map(function(data) {
			controls.push({
				type: data.type,
				title: data.type === 'variables' ? data.title : null,
				hint: data.hint,
				variables: data.type === 'variables' ? data.variables : [],
				access: data
			})
		}.bind(this));

		return new BX.UI.AccessRights.Column({
			items: controls,
			userGroup: options.userGroup ? options.userGroup : null,
			section: this,
			headSection: options.headSection,
			grid: this.grid,
			newColumn: options.newColumn ? options.newColumn : null
		});
	},

	addHeadColumn: function()
	{
		var titles = [];

		if(!this.headSection)
		{
			this.rights.map(function(data) {
				titles.push({
					id: data.id,
					type: 'title',
					title: data.title,
					hint: data.hint
				})
			}.bind(this));
		}

		if(this.headSection)
		{
			titles = [
				{
					type: 'userGroupTitle',
					title: BX.message('JS_UI_ACCESSRIGHTS_ROLES'),
					controller: true

				},
				{
					type: 'userGroupTitle',
					title: BX.message('JS_UI_ACCESSRIGHTS_EMPLOYEES_AND_DEPARTMENTS'),
					controller: false
				}
			]
		}

		var column = new BX.UI.AccessRights.Column({
			items: titles,
			section: this,
			grid: this.grid
		});

		this.layout.headColumn.appendChild(column.render());
	},

	getTitle: function()
	{
		return BX.create('div', {
			props: {
				className: 'ui-access-rights-section-title'
			},
			text: this.title
		});
	},

	getMainContainer: function()
	{
		return BX.create('div', {
			props: {
				className: 'ui-access-rights-section-container'
			},
			children: [
				this.layout.headColumn = BX.create('div', {
					props: {
						className: 'ui-access-rights-section-head'
					}
				}),
				this.getContentContainer()
			]
		});
	},

	getColumnsContainer: function()
	{
		if (!this.layout.columns)
		{
			this.layout.columns = BX.create('div', {
				props: {
					className: 'ui-access-rights-section-wrapper'
				},
				events: {
					scroll: this.adjustScroll.bind(this)
				}
			})
		}

		return this.layout.columns;
	},

	getContentContainer: function()
	{
		if (!this.layout.content)
		{
			this.layout.content = BX.create('div', {
				props: {
					className: 'ui-access-rights-section-content'
				},
				children: [
					this.getColumnsContainer(),
					this.getEarLeft(),
					this.getEarRight()
				]
			});
		}

		return this.layout.content;
	},

	getEarLeft: function()
	{
		var self = this;

		if (!this.layout.earLeft)
		{
			this.layout.earLeft = BX.create('div', {
				props: {
					className: 'ui-access-rights-section-ear-left'
				},
				events: {
					mouseenter: function() {
						self.stopAutoScroll();
						self.earLeftTimer = setTimeout(function() {
							self.scrollToLeft()
						}, 110)
					},
					mouseleave: function() {
						clearTimeout(self.earLeftTimer);
						self.stopAutoScroll()
					}
				}
			})
		}

		return this.layout.earLeft;
	},

	getEarRight: function()
	{
		var self = this;

		if (!this.layout.earRight)
		{
			this.layout.earRight = BX.create('div', {
				props: {
					className: 'ui-access-rights-section-ear-right'
				},
				events: {
					mouseenter: function() {
						self.stopAutoScroll();
						self.earRightTimer = setTimeout(function() {
							self.scrollToRight()
						}, 110)
					},
					mouseleave: function() {
						clearTimeout(self.earRightTimer);
						self.stopAutoScroll()
					}
				}
			})
		}

		return this.layout.earRight;
	},

	adjustEars: function()
	{
		var container = this.getColumnsContainer();
		var scroll = container.scrollLeft;

		var isLeftVisible = scroll > 0;
		var isRightVisible = container.scrollWidth > (Math.round(scroll + container.offsetWidth));

		this.getContentContainer().classList[isLeftVisible ? 'add' : 'remove']('ui-access-rights-section-ear-left-shown');
		this.getContentContainer().classList[isRightVisible ? 'add' : 'remove']('ui-access-rights-section-ear-right-shown');
	},

	scrollToRight: function(param, stop)
	{
		var interval = 20;

		param ? interval = 2 : null;

		this.earTimer = setInterval(function() {
			// if(this.getColumnsContainer().scrollLeft === 0)
			// {
			// 	this.stopAutoScroll();
			// }
			this.getColumnsContainer().scrollLeft += 10;
			if(param)
			{
				param <= this.getColumnsContainer().scrollLeft ? this.stopAutoScroll() : null;
			}
		}.bind(this), interval);

		if(stop === 'stop')
		{
			setTimeout(function() {
				clearTimeout(this.earTimer);
			}.bind(this), param * 2)
		}
	},

	scrollToLeft: function()
	{
		this.earTimer = setInterval(function() {
			this.getColumnsContainer().scrollLeft -= 10;
		}.bind(this), 20)
	},

	stopAutoScroll: function()
	{
		clearInterval(this.earTimer);
	},

	adjustScroll: function()
	{
		if (this.scroll < this.getColumnsContainer().scrollLeft)
		{
			this.scroll = this.getColumnsContainer().scrollLeft;
			BX.onCustomEvent("AccessRights.Section:scrollRight", [this]);
		}

		if (this.scroll > this.getColumnsContainer().scrollLeft)
		{
			this.scroll = this.getColumnsContainer().scrollLeft;
			BX.onCustomEvent("AccessRights.Section:scrollLeft", [this]);
		}
	},

	getScroll: function()
	{
		return this.scroll;
	},

	render: function()
	{
		var sectionContainer = BX.create('div', {
			props: {
				className: 'ui-access-rights-section'
			},
			children: [
				this.title ? this.getTitle() : null,
				this.getMainContainer()
			]
		});

		this.addHeadColumn();

		var columnsFragment = document.createDocumentFragment();

		for (var i = 0; i < this.grid.getUserGroups().length; i++)
		{
			var column = this.getColumn(
				{
					headSection: this.headSection ? this.headSection : null,
					userGroup: this.grid.getUserGroups()[i]
				}
			);
			
			this.columns.push(column);
			columnsFragment.appendChild(column.render());
		}

		this.getColumnsContainer().appendChild(columnsFragment);

		return sectionContainer;
	}
};

})();