;(function () {

'use strict';

BX.namespace('BX.UI');

BX.UI.AccessRights.Column = function(options) {
	this.layout = {
		container: null
	};
	this.grid = options.grid ? options.grid : null;
	this.items = options.items ? options.items : [];
	this.userGroup = options.userGroup ? options.userGroup : null;
	this.accessCodes = options.accessCodes ? options.accessCodes : null;
	this.section = options.section ? options.section : null;
	this.headSection = options.headSection;
	this.newColumn = options.newColumn ? options.newColumn : null;
	this.openPopupEvent = options.grid.openPopupEvent ? options.grid.openPopupEvent : null;
	this.popupContainer = options.grid.popupContainer ? options.grid.popupContainer : null;
};

BX.UI.AccessRights.Column.prototype = {
	getItem: function(options)
	{
		options = options || {};

		var param = {};

		if (options.type === 'userGroupTitle')
		{
			param = {
				type: options.type,
				text: options.title,
				controller: options.controller
			}
		}

		if (options.type === 'title')
		{
			param = {
				id: options.id,
				type: options.type,
				hint: options.hint,
				text: options.title,
				controller: options.controller
			}
		}

		if (options.type === 'toggler')
		{
			param = {
				type: options.type,
				access: options.access
			}
		}

		if (options.type === 'variables')
		{
			param = {
				type: options.type,
				text: options.title,
				variables: options.variables,
				access: options.access
			}
		}

		if (options.type === 'role')
		{
			param = {
				type: options.type,
				text: options.title
			}
		}

		if (options.type === 'members')
		{
			var accessCodes = [];

			for(var item in options.members)
			{
				accessCodes[item] = options.members[item].type;
			}
			
			param = {
				type: options.type,
				accessCodes: accessCodes
			}
		}

		param.column = this;
		param.userGroup = this.userGroup;
		param.openPopupEvent = this.openPopupEvent;
		param.popupContainer = this.popupContainer;
		param.currentParam = null;

		param.grid = this.grid;

		if(options.type === 'variables' || options.type === 'toggler')
		{
			var accessId = param.access.id;
			var accessRights = param.userGroup.accessRights;

			for (var i = 0; i < accessRights.length; i++)
			{
				if (accessId === accessRights[i].id)
				{
					param.currentParam = accessRights[i].value;
				}
			}
		}

		return new BX.UI.AccessRights.ColumnItem(param);
	},

	getUserGroup: function()
	{
		return this.userGroup;
	},

	remove: function()
	{
		if(this.layout.container.classList.toggle('ui-access-rights-column-new'))
		{
			this.layout.container.classList.remove('ui-access-rights-column-new');
		}

		this.layout.container.classList.add('ui-access-rights-column-remove');
		this.layout.container.style.width = this.layout.container.offsetWidth + 'px';

		BX.bind(this.layout.container, 'animationend', function() {
			this.layout.container.style.minWidth = '0px';
			this.layout.container.style.maxWidth = '0px';
		}.bind(this));

		setTimeout(function() {
			this.layout.container.parentNode.removeChild(this.layout.container);
		}.bind(this), 500);
	},

	resetClassNew: function()
	{
		this.layout.container.classList.remove('ui-access-rights-column-new');
	},

	render: function()
	{
		if (!this.layout.container)
		{
			var itemsFragment = document.createDocumentFragment();

			if(this.headSection)
			{
				this.userGroup.type = 'role';
				itemsFragment.appendChild(this.getItem(this.userGroup).render());

				this.userGroup.type = 'members';
				itemsFragment.appendChild(this.getItem(this.userGroup).render());
			}

			this.items.map(function(data) {
				var item = this.getItem(data);
				itemsFragment.appendChild(item.render());
			}.bind(this));

			this.layout.container = BX.create('div', {
				props: {
					className: this.newColumn ? 'ui-access-rights-column ui-access-rights-column-new' : 'ui-access-rights-column'
				}
			});

			BX.addCustomEvent('BX.UI.AccessRights:refresh', this.resetClassNew.bind(this));

			this.layout.container.appendChild(itemsFragment);

			return this.layout.container;
		}
	}
};

})();