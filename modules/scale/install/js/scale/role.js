/**
 * Class BX.Scale.Role
 * Describes reoles's props, view & behavior
 */
;(function(window) {

	if (BX.Scale.Role) return;

	/**
	 * Class BX.Scale.Role
	 * @constructor
	 */
	BX.Scale.Role = function (id, hostname, params)
	{
		this.id = id;
		this.hostname = hostname;
		this.domObj = null;
		this.showLoadBar = !!BX.Scale.rolesList[this.id].LOADBAR_INFO;
		this.showMenu = true;
		this.showMenuClose = false;
		this.type = null;
		this.state = null;
		this.color = BX.Scale.rolesList[this.id].COLOR || "invisible";
		this.noActions = false;

		if(params !== undefined)
		{
			if(params.showMenu === false)
				this.showMenu = false;

			if(params.showMenuClose === true)
				this.showMenuClose = true;

			if(params.type !== undefined)
				this.type = params.type;

			if(params.type == "norole")
			{
				this.showLoadBar = false;

				if(this.color != "invisible")
					this.color = false;
			}

			if(params.noActions && params.noActions === true)
				this.noActions = true;

			if(params.state)
				this.state = params.state;
		}

		if(this.showLoadBar)
			this.loadBar = new BX.Scale.LoadBar(hostname+"_"+id+"_lb");
		else
			this.loadBar = null;

		this.actionsIds = this.getAviableActionsList();
	};

	/**
	 * Returns DOM object contains server data
	 * @returns {object}
	 */

	BX.Scale.Role.prototype.getMenuObj = function()
	{
		var domObj = BX.create('div',{props:{className:"adm-scale-item-menu"}});
		domObj.appendChild(BX.create('span',{props:{className:"adm-scale-item-menu-text"}, html: BX.message("SCALE_PANEL_JS_MENU")}));
		BX.bind(domObj, "click", BX.proxy(this.menuOpen, this));

		if(this.showMenuClose)
			domObj.appendChild(BX.create('span',{props:{className:"adm-scale-item-close"}}));

		return domObj;
	};

	BX.Scale.Role.prototype.menuOpen = function(event)
	{
		event = event || window.event;
		var menuButton = event.target || event.srcElement,
			menuItems =[];

		for(var key in this.actionsIds)
		{
			var action = BX.Scale.actionsCollection.getObject(key);

			if(action)
			{
				menuItems.push({
					TEXT: action.name,
					ONCLICK: "BX.Scale.actionsCollection.getObject('"+key+"').start('"+this.hostname+"');"
				});
			}
		}

		if (!menuButton.OPENER)
			BX.adminShowMenu(menuButton, menuItems, {active_class: "bx-adm-scale-menu-butt-active"});
		else
			menuButton.OPENER.SetMenu(menuItems);

		return BX.PreventDefault(event);
	};

	BX.Scale.Role.prototype.getAviableActionsList = function()
	{
		var result = {};

		if(!this.noActions)
		{
			if(BX.Scale.rolesList[this.id])
			{
				var role = BX.Scale.rolesList[this.id],
					type = this.type || "notype",
					actionId;

				if(role.ROLE_ACTIONS && role.ROLE_ACTIONS[type])
				{
					for(actionId in role.ROLE_ACTIONS[type])
					{
						if(!role.ROLE_ACTIONS[type].hasOwnProperty(actionId))
							continue;

						if(!result[role.ROLE_ACTIONS[type][actionId]] && BX.Scale.actionsCollection.getObject(role.ROLE_ACTIONS[type][actionId]))
						{
							result[role.ROLE_ACTIONS[type][actionId]] = true;
						}
					}
				}

				
				if(this.state && role.STATE_ACTIONS && role.STATE_ACTIONS[this.state])
				{
					for(actionId in role.STATE_ACTIONS[this.state])
					{
						if(!role.STATE_ACTIONS[this.state].hasOwnProperty(actionId))
							continue;

						if(this.id == 'mysql' && this.type == 'master' && role.STATE_ACTIONS[this.state][actionId] == 'MYSQL_STOP')
							continue;

						if(!result[role.STATE_ACTIONS[this.state][actionId]] && BX.Scale.actionsCollection.getObject(role.STATE_ACTIONS[this.state][actionId]))
						{
							result[role.STATE_ACTIONS[this.state][actionId]] = true;
						}
					}
				}
			}
		}

		if(BX.Scale.isObjEmpty(result))
			result = null;

		return result;
	};
	BX.Scale.Role.prototype.setLoadBarValue = function(value)
	{
		var result = false;

		if(this.loadBar !== null)
			result = this.loadBar.setValue(value);

		return result;
	};

	BX.Scale.Role.prototype.getDomObj = function()
	{
		if(this.color == "invisible")
			return null;

		if(!this.domObj)
		{
			if(BX.Scale.rolesList[this.id])
			{
				this.domObj = document.createElement("div");
				this.domObj.id = this.hostname+"_"+this.id;
				BX.addClass(this.domObj, "adm-scale-item-block");

				var item = document.createElement("div");
				BX.addClass(item, "adm-scale-item");

				if(this.color)
					BX.addClass(item, 'adm-scale-'+this.color);

				var align = document.createElement("span");
				BX.addClass(align, "adm-scale-item-alignment");
				item.appendChild(align);

				if(this.type && this.type != "norole")
				{
					var type = document.createElement("div");
					BX.addClass(type, "adm-scale-item-btn");

					if(BX.Scale.rolesList[this.id].TYPES && BX.Scale.rolesList[this.id].TYPES[this.type])
						type.innerHTML = BX.Scale.rolesList[this.id].TYPES[this.type];
					else
						type.innerHTML = this.type;

					item.appendChild(type);
				}

				var name = document.createElement("span");
				BX.addClass(name, "adm-scale-item-name");
				name.innerHTML = BX.Scale.rolesList[this.id].NAME;
				item.appendChild(name);

				if(this.state)
				{
					item.appendChild(
						BX.create(
							"span",
							{
								html: this.getStateHtml(),
								props:{
									className:'adm-scale-item-role-state'
					}}));
				}

				if(this.showMenu === true && this.actionsIds)
				{
					var menu = this.getMenuObj();

					if(menu)
						item.appendChild(menu);
				}

				this.domObj.appendChild(item);

				if(this.loadBar !== null)
					this.domObj.appendChild(this.loadBar.getDomObj());
			}
			else
			{
				BX.debug("Error! Role " + this.id + " not defined");
			}
		}

		return this.domObj;
	};

	BX.Scale.Role.prototype.getStateHtml = function()
	{
		return this.state === 'active' ? 'Active' : 'Not active';
	};

	BX.Scale.Role.prototype.getMonitoringCategories = function(hostname)
	{
		var result = {};

		if(BX.Scale.rolesList[this.id].MONITORING_CATEGORIES)
		{
			for(var c in BX.Scale.rolesList[this.id].MONITORING_CATEGORIES)
			{
				if(BX.Scale.monitoringCategories[hostname][BX.Scale.rolesList[this.id].MONITORING_CATEGORIES[c]])
					result[BX.Scale.rolesList[this.id].MONITORING_CATEGORIES[c]] = BX.Scale.monitoringCategories[hostname][BX.Scale.rolesList[this.id].MONITORING_CATEGORIES[c]];
			}
		}

		return result;
	};

})(window);