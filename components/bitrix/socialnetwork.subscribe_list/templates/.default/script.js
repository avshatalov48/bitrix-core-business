if(!Array.indexOf)
{
	Array.prototype.indexOf = function(obj)
	{
		for(var i=0; i<this.length; i++)
		{
			if(this[i]===obj)
			{
				return i;
			}
		}
		return -1;
   }
}

// tree operations
BX.CSLTree = function() {
	this.Tree = [];
	this.arLevels = ['entity_event', 'entity_all', 'allmy_event', 'allmy_all', 'all_event', 'all_all', 'root_all'];
	this.changedNode = null;
}

BX.CSLTree.prototype.onChangeTransport = function()
{
	var checkboxKey = null;
	
	var ob = BX.proxy_context;
	var arrTmp = ob.id.split("_");
		
	if (arrTmp[6] == 'all')
		var tmpNode = this.Tree[arrTmp[4] + '_' + arrTmp[1]][arrTmp[5]]['all'];	
	else
		var tmpNode = this.Tree[arrTmp[4] + '_' + arrTmp[1]][arrTmp[5]]['event'];

	var valType = 't';
	var event_id = arrTmp[6];

	this.changedNode = tmpNode;
	
	var key = 'Transport';
	
	// set value for the tree node	
	tmpNode['arEvents'][event_id][key] = ob.value;
		
	if (tmpNode.children.length > 0)
	{
		if (
			ob.value == 'I' 
			&& tmpNode.parentNode != null
		)
		{
				// recalc from parent	
			var parentWithEventID = this.getParent(tmpNode, event_id, key);
			if (parentWithEventID.type == 'all')
				this.Recalc(tmpNode, valType, event_id, parentWithEventID['arEvents']['all'][key], parentWithEventID.node_type);
			else
				this.Recalc(tmpNode, valType, event_id, parentWithEventID['arEvents'][event_id][key], parentWithEventID.node_type);
		}
		else
		{
			// recalc from current
			this.Recalc(tmpNode, valType, event_id, ob.value, tmpNode.node_type);
		}
	}
}

BX.CSLTree.prototype.onChangeVisible = function(ob)
{
	var checkboxKey = null;
	
	var arrTmp = ob.node.split("_");
		
	if (ob.feature == 'all')
		var tmpNode = SLTree.Tree[arrTmp[2] + '_' + ob.cb_val][arrTmp[3]]['all'];	
	else
		var tmpNode = SLTree.Tree[arrTmp[2] + '_' + ob.cb_val][arrTmp[3]]['event'];
			
	var valType = 'v';
	var event_id = ob.feature;
		
	SLTree.changedNode = tmpNode;
	
	var key = 'Visible';
		
	if (tmpNode.children.length > 0)
	{
		if (
			ob.hiddenValue == 'I' 
			&& tmpNode.parentNode != null
		)
		{
			// recalc from parent	
			checkboxKey = 'bx_sl_' + arrTmp[2] + '_' + arrTmp[3] + '_' + event_id;
			if (ob.cb_val == 'Y')
				checkboxKey += '_cb';			

			// use instead of parent
			tmpNode['arEvents'][event_id][key] = ob.hiddenValue;
					
			var parentWithEventID = SLTree.getParent(tmpNode, event_id, key);
			if (parentWithEventID.type == 'all')
			{
				arVisibleCheckbox[checkboxKey].visibleValue = parentWithEventID['arEvents']['all'][key];
				SLTree.Recalc(tmpNode, valType, event_id, parentWithEventID['arEvents']['all'][key], parentWithEventID.node_type);
			}
			else
			{
				arVisibleCheckbox[checkboxKey].visibleValue = parentWithEventID['arEvents'][event_id][key];
				SLTree.Recalc(tmpNode, valType, event_id, parentWithEventID['arEvents'][event_id][key], parentWithEventID.node_type);
			}
				
			if (arVisibleCheckbox[checkboxKey].bTopLevel)
				var newCheckboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-Y';
			else
				var newCheckboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-i-' + arVisibleCheckbox[checkboxKey].visibleValue;

			BX.adjust(arVisibleCheckbox[checkboxKey].checkboxDiv, {
				props : {
					'className' : newCheckboxClassName
				}
			});				
		}
		else
		{
			// recalc from current

			// set value for the tree node	
			tmpNode['arEvents'][event_id][key] = ob.visibleValue;
			SLTree.Recalc(tmpNode, valType, event_id, ob.visibleValue, tmpNode.node_type);
		}
	}
}

BX.CSLTree.prototype.Recalc = function(node, valType, event_id, new_value, from_node_type)
{
	var tmpNode = null;

	for (var i = 0; i < node.children.length; i++)
	{
		tmpNode = node.children[i];
		
		if (
			event_id != 'all'
			&& tmpNode.type == 'event'
			&& tmpNode['arEvents'][event_id] == 'null'
		)
			continue;

		if (valType == 't')
			var key = 'Transport';
		else
			var key = 'Visible';

		if (
			(
				tmpNode.type == 'event'
				&& tmpNode['arEvents'] != null
			)
			||
			(
				tmpNode.type == 'all'
				&& event_id == 'all'
			)
		)
			this.Change(tmpNode, valType, event_id, new_value, from_node_type);

		if (tmpNode.children.length > 0)
			this.Recalc(tmpNode, valType, event_id, new_value, from_node_type);
	}
}

BX.CSLTree.prototype.getParent = function(node, event_id, key)
{
	if (BX(node.parentNode) == null)
		return node;
		
	if (node.parentNode.type == 'event')
		var eventToCheck = event_id;
	else
		var eventToCheck = 'all';

	if (
		BX(node.parentNode)
		&& node.parentNode['arEvents'][eventToCheck] != null
		&& node.parentNode['arEvents'][eventToCheck][key] != 'I'
	)
		return node.parentNode;
	else
		return this.getParent(node.parentNode, event_id, key);
}

BX.CSLTree.prototype.Change = function(node, valType, event_id, new_value, from_node_type)
{
	var arrTmp = node.id.split("_");
	var arrTmp2 = null;	
	var controlIdTmp = null;
	var value = null;
	var checkboxKey	= null;
	var element = null;
	var new_value_tmp = null;

	if  (
		event_id == 'all'
		&& node.type == 'event'
	)
	{
		for (node_event in node['arEvents'])
		{
			if (valType == 't')
			{
				if (this.LevelIncluded(node['arEvents'][node_event]['TransportInheritedFrom'], from_node_type))
					continue;

				node['arEvents'][node_event]['TransportInheritedFrom'] = from_node_type;
			
				controlIdTmp = arrTmp[2] + '_bx_sl_' + arrTmp[1] + '_' + arrTmp[3] +  '_' + node_event;
				element = BX('t_' + controlIdTmp + '_optioni');
				
				if (element)
				{
					parentWithEventID = SLTree.getParent(node, node_event, 'Transport');
					if (parentWithEventID != null)
					{
						if (parentWithEventID.type == 'all')
							new_value_tmp = parentWithEventID['arEvents']['all']['Transport'];
						else
							new_value_tmp = parentWithEventID['arEvents'][node_event]['Transport'];
					}
					else
						new_value_tmp = new_value;
						
					if (node['node_type'] == 'all_all')
						value = BX.message('sonetSLTransport' + new_value_tmp);
					else
						value = BX.message('sonetSLInherited') + ' (' + BX.message('sonetSLTransport' + new_value_tmp) + ')';
					
					BX.adjust(element, {
						html: value
					});
				}
			}
			else if (valType == 'v')
			{
				if (this.LevelIncluded(node['arEvents'][node_event]['VisibleInheritedFrom'], from_node_type))
					continue;
			
				if (node['arEvents'][node_event] != null)
					node['arEvents'][node_event]['VisibleInheritedFrom'] = from_node_type;
					
				controlIdTmp = arrTmp[2] + '_bx_sl_' + arrTmp[1] + '_' + arrTmp[3] +  '_' + node_event;
				element = BX('v_' + controlIdTmp + '_div');
				
				if (element)
				{
					arrTmp2 = element.id.split("_");
					
					checkboxKey = 'bx_sl_' + arrTmp2[4] + '_' + arrTmp2[5] + '_' + node_event;
					if (arrTmp2[1] == 'Y')
						checkboxKey += '_cb';

					parentWithEventID = SLTree.getParent(node, node_event, 'Visible');
					if (parentWithEventID != null)
					{
						if (parentWithEventID.type == 'all')
							new_value_tmp = parentWithEventID['arEvents']['all']['Visible'];
						else
							new_value_tmp = parentWithEventID['arEvents'][node_event]['Visible'];
					}
					else
						new_value_tmp = new_value;
	
					if (arVisibleCheckbox[checkboxKey] != null)
						arVisibleCheckbox[checkboxKey].visibleValue = new_value_tmp;

					if (arVisibleCheckbox[checkboxKey].hiddenValue == 'I')
					{
						if (node['node_type'] == 'all_all')
							value = 'subscribe-list-checkbox subscribe-list-checkbox-' + new_value_tmp;
						else
							value = 'subscribe-list-checkbox subscribe-list-checkbox-i-' + new_value_tmp;

						BX.adjust(element, {
							props : {
								'className': value
							}
						});
					}
				}
			}
		}
	}
	else
	{
		if (
			typeof(node['arEvents']) == 'undefined'
			|| typeof(node['arEvents'][event_id]) == 'undefined'
			|| node['arEvents'][event_id] == null
		)
			return;
		
		if (valType == 't')
		{				
			if (this.LevelIncluded(node['arEvents'][event_id]['TransportInheritedFrom'], from_node_type))
				return;
		
			if (node['arEvents'][event_id] != null)
				node['arEvents'][event_id]['TransportInheritedFrom'] = from_node_type;
		
			controlIdTmp = arrTmp[2] + '_bx_sl_' + arrTmp[1] + '_' + arrTmp[3] +  '_' + event_id;
			element = BX('t_' + controlIdTmp + '_optioni');
			
			if (element)
			{
				if (node['node_type'] == 'all_all')
					value = BX.message('sonetSLTransport' + new_value);
				else
					value = BX.message('sonetSLInherited') + ' (' + BX.message('sonetSLTransport' + new_value) + ')';
				
				BX.adjust(element, {
					html: value
				});
			}
		}
		else if (valType == 'v')
		{
			if (this.LevelIncluded(node['arEvents'][event_id]['VisibleInheritedFrom'], from_node_type))
				return;
		
			if (node['arEvents'][event_id] != null)
				node['arEvents'][event_id]['VisibleInheritedFrom'] = from_node_type;
				
			controlIdTmp = arrTmp[2] + '_bx_sl_' + arrTmp[1] + '_' + arrTmp[3] +  '_' + event_id;
			element = BX('v_' + controlIdTmp + '_div');
			
			if (element)
			{
				arrTmp = element.id.split("_");
				
				checkboxKey = 'bx_sl_' + arrTmp[4] + '_' + arrTmp[5] + '_' + event_id;
				if (arrTmp[1] == 'Y')
					checkboxKey += '_cb';
				
				if (arVisibleCheckbox[checkboxKey] != null)
					arVisibleCheckbox[checkboxKey].visibleValue = new_value;

				if (arVisibleCheckbox[checkboxKey].hiddenValue == 'I')
				{
					if (node['node_type'] == 'all_all')
						value = 'subscribe-list-checkbox subscribe-list-checkbox-' + new_value;
					else
						value = 'subscribe-list-checkbox subscribe-list-checkbox-i-' + new_value;

					BX.adjust(element, {
						props : {
							'className': value
						}
					});					
				}
			}
		}
	}
}

BX.CSLTree.prototype.LevelIncluded = function(firstLevel, secondLevel)
{
	if (
		this.changedNode != null
		&& firstLevel == this.changedNode.node_type
	)
		return false;

	if (
		firstLevel == 'all_all'
		&& secondLevel == 'root_all'
	)
		return false;

	if (this.arLevels.indexOf(firstLevel) < this.arLevels.indexOf(secondLevel))
		return true;
	else
		return false;	
}

	
// visible checkbox 
BX.CSLVisibleCheckbox = function(arParams) {
	this.bindElement = arParams.bindElement;
	this.checkboxClassName = arParams.checkboxClassName;
	this.node = arParams.node;
	this.feature = arParams.feature;	
	this.cb = arParams.cb;
	this.hiddenValue = arParams.hiddenValue;
	this.visibleValue = arParams.visibleValue;
	this.arCheckboxVal = arParams.arCheckboxVal;
	this.checkboxDiv = null;
	this.hidden = null;	
	this.bTopLevel = arParams.bTopLevel || false;
	
	if (this.cb == 'cb_')
		this.cb_val = 'Y';
	else
		this.cb_val = 'N';
}

BX.CSLVisibleCheckbox.prototype.Show = function()
{
	this.bindElement.appendChild(BX.create('BR', {}));
		
	this.checkboxDiv = this.bindElement.appendChild(BX.create('SPAN', {
			props: {
				'className': this.checkboxClassName,
				'id': 'v_' + this.cb_val + '_' + this.node + '_' + this.feature + '_div'
			}
		}));
		
	this.hidden = this.checkboxDiv.appendChild(BX.create('INPUT', {
			props: {
				'type': 'hidden',		
				'name': 'v_' + this.cb + this.node + '_' + this.feature,
				'value': this.hiddenValue,
				'id': 'v_' + this.cb_val + '_' + this.node + '_' + this.feature
			}
		}));		
		
	this.checkboxDiv.appendChild(BX.create('SPAN', {
			props: {
				'className': 'subscribe-list-checkbox-icon'
			}
		}));

	this.checkboxDiv.appendChild(BX.create('SPAN', {
			props: {
				'className': 'subscribe-list-checkbox-text'
			},
			html: BX.message('sonetSLShowInList')
		}));

	BX.bind(this.checkboxDiv, "click", BX.delegate(this.CheckboxChange, this));
}

BX.CSLVisibleCheckbox.prototype.CheckboxChange = function()
{
	var newHiddenValue = null;
	var newCheckboxClassName = null;
	
	for (var i = 0; i < this.arCheckboxVal.length; i++)
	{
		if (this.arCheckboxVal[i] == this.hiddenValue)
		{
			if (i == this.arCheckboxVal.length - 1)
				newHiddenValue = this.arCheckboxVal[0];
			else
				newHiddenValue = this.arCheckboxVal[i + 1];

			break;
		}
	}
	
	BX.adjust(this.hidden, {
		props : {
			'value' : newHiddenValue
		}
	});
	this.hiddenValue = newHiddenValue;

	if (newHiddenValue == 'I')
	{
		if (this.bTopLevel)
		{
			this.visibleValue = 'Y';
			newCheckboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-Y';
		}
		else
			newCheckboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-i-' + this.visibleValue;
	}
	else
	{
		this.visibleValue = newHiddenValue;

		newCheckboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-' + newHiddenValue;
	}

	BX.adjust(this.checkboxDiv, {
		props : {
			'className' : newCheckboxClassName
		}
	});

	SLTree.onChangeVisible(this);
}

BX.CSLBlock = function(arParams) {
}

BX.CSLBlock.prototype.DelEvent = function()
{
	var ob = BX.proxy_context;
	var node = ob.bx_node_code;
	var entity_type = ob.bx_entity_type;	
	var entity_id = ob.bx_entity_id;
	var event_id = ob.bx_event_id;
	var entity_cb = ob.bx_entity_cb;

	sonet_sl_del(node, entity_type, entity_id, event_id, entity_cb);
	return false;
}				

BX.CSLBlock.prototype.ShowContent = function(node, data, entity_type, entity_id, entity_cb)
{
	var tr = null;
	var checked = null;
	var hiddenValue = null;
	var arCheckboxVal = [];
	var SLVisibleCheckbox = null;
	var transportSelect = null;
	var tmpTransportVal = null;
	var tmpVisibleVal = null;
	var q, qq;
	var arrTmp = null;
	var tmpNode = null;
	var parentWithEventID = null;
	var bFound = false;

	if (data["Subscription"].length <= 0)
	{
		BX(node + '_content').firstChild.nextSibling.appendChild(BX.create('DIV', {
				props: {},
				html: BX.message('sonetSLNoSubscriptions')
			})
		);
		return;
	}
	
	var table = BX(node + '_content').appendChild(BX.create('table', {
		props: { 
			'width': '100%',
			'className': 'subscribe-list-feature'
		}
	}));

	var tbody = table.appendChild(BX.create('tbody', { }));

	q = {'props': { 'arEvents': {} }};

	for (var i = 0; i < data["Subscription"].length; i++)
	{
		if (
			entity_id != "all" && entity_id != "allmy"
			&& (
				(entity_cb != "Y" && data["Subscription"][i]["TransportInherited"] && data["Subscription"][i]["VisibleInherited"])
				|| 
				(
					entity_cb == "Y" 
					&& 
					(
						typeof(data["Subscription"][i]["TransportInheritedCB"]) == "undefined" 
						|| data["Subscription"][i]["TransportInheritedCB"] 
					)
					&& 
					(
						typeof(data["Subscription"][i]["VisibleInheritedCB"]) == "undefined" 
						|| data["Subscription"][i]["VisibleInheritedCB"] 
					)
				)
			)
		)
			continue;
	
		bFound = true;

		if (entity_cb == 'Y')
		{
			var cb = 'cb_';
			var cb_val = 'Y';
			var transportInheritedKey = "TransportInheritedCB";
			var transportKey = "TransportCB";
			var visibleInheritedKey = "VisibleInheritedCB";
			var visibleKey = "VisibleCB";
			var transportInheritedFromKey = "TransportInheritedFromCB";
			var visibleInheritedFromKey = "VisibleInheritedFromCB";
		}
		else
		{
			var cb = '';
			var cb_val = 'N';
			var transportInheritedKey = "TransportInherited";
			var transportKey = "Transport";
			var visibleInheritedKey = "VisibleInherited";
			var visibleKey = "Visible";
			var transportInheritedFromKey = "TransportInheritedFrom";
			var visibleInheritedFromKey = "VisibleInheritedFrom";
		}

		tbody.appendChild(BX.create('tr', {
			props: {
				'className': 'subscribe-list-features'
			},
			children: [
				BX.create('td', 
					{ 
						props: {
							'className': 'subscribe-list-corners'
						},
						attrs: { 
							'colSpan': 3
						}, 
						children: [
							BX.create('DIV', 
								{ 
									props: { 
										'className': 'subscribe-list-features-lt'
									}, 
									children: [
										BX.create('DIV', 
											{ 
												props: { 
													'className': 'subscribe-list-features-rt'
												}
											}
										)
									]
								}
							)
						]
					}
				)
			]
		}));

		if (data["Subscription"][i][transportInheritedKey])
			tmpTransportVal = 'I';
		else
			tmpTransportVal = data["Subscription"][i][transportKey];

		if (data["Subscription"][i][visibleInheritedKey])
			tmpVisibleVal = 'I';
		else
			tmpVisibleVal = data["Subscription"][i][visibleKey];

		q.props['arEvents'][data['Subscription'][i]['Feature']] = {
					'Transport': tmpTransportVal,
					'Visible': tmpVisibleVal
				};
		
		if (data["Subscription"][i][transportInheritedKey])
			q.props['arEvents'][data['Subscription'][i]['Feature']]['TransportInheritedFrom'] = data['Subscription'][i][transportInheritedFromKey];
		if (data["Subscription"][i][visibleInheritedKey])
			q.props['arEvents'][data['Subscription'][i]['Feature']]['VisibleInheritedFrom'] = data['Subscription'][i][visibleInheritedFromKey];

		BX.adjust(SLTree.Tree[entity_type + '_' + entity_cb][entity_id]['event'], q);
			
		tr = tbody.appendChild(BX.create('tr', {
			props: {
				'id': 'bx_sl_' + entity_type + '_' + entity_id + '_' + data["Subscription"][i]["Feature"] + '_' + cb + 'tr',
				'className': 'subscribe-list-features'
			},
			children: [
				BX.create('td', 
					{ 
						props: { 
							'className': 'subscribe-list-feature-name'
						}, 
						children: [
							BX.create('b', {
								html: data["Subscription"][i]["Name"]
							})
						]
				}),
				BX.create('td', 
					{
						props: { 'width': '65%'}
				}),
				BX.create('td', 
					{
						props: { 'width': '5%'}
				})
			]
		}));

		if (
			entity_id == 'all' 
			|| entity_id == 'allmy'
			|| 
			(
				typeof(data["Subscription"][i][transportInheritedKey]) != "undefined"
				&& !data["Subscription"][i][transportInheritedKey]
			)
		)
		{
			transportSelect = tr.firstChild.nextSibling.appendChild(BX.create('select', {
					props: {
						'name': 't_' + cb + node + '_' + data["Subscription"][i]["Feature"],
						'id': 't_' + cb_val + '_' + node + '_' + data["Subscription"][i]["Feature"]
					}
				}));
		}
		else
			transportSelect = null;
		
		if (entity_id != 'all' && entity_id != 'allmy')
		{
			tr.firstChild.nextSibling.nextSibling.appendChild(BX.create('A', {
					props: {
						'name': 't_' + cb + node + '_' + data["Subscription"][i]["Feature"],
						'bx_node_code': 'bx_sl_' + entity_type + '_' + entity_id + '_' + data["Subscription"][i]["Feature"] + '_' + cb + 'tr',
						'bx_entity_type': entity_type,
						'bx_entity_id': entity_id,
						'bx_event_id': data["Subscription"][i]["Feature"],
						'bx_entity_cb': entity_cb,
						'href': 'javascript:void(0)',
						'className': 'subscribe-list-del',
						'title': BX.message('sonetSLDeleteSubscription')
					},
					events: {
						'click': BX.delegate(this.DelEvent, this)
					}
				}));
		}
		
		if (transportSelect != null)
		{
			if (data["Subscription"][i][transportInheritedKey])
			{

				transportSelect.appendChild(BX.create('option', {
					props: {
						'value': 'I',
						'selected': true,
						'defaultSelected': true,
						'id': transportSelect.id + '_optioni'
					},
					html: BX.message('sonetSLInherited') + ' (' + BX.message('sonetSLTransport' + data["Subscription"][i][transportKey]) + ')'
				}
				));

				arrTmp = transportSelect.id.split("_");
				tmpNode = SLTree.Tree[arrTmp[4] + '_' + arrTmp[1]][arrTmp[5]]['event'];

				parentWithEventID = SLTree.getParent(tmpNode, data['Subscription'][i]['Feature'], 'Transport');

				if (parentWithEventID.type == 'event')
					tmpTransportVal = parentWithEventID['arEvents'][data['Subscription'][i]['Feature']]['Transport'];
				else
					tmpTransportVal = parentWithEventID['arEvents']['all']['Transport'];
				
				SLTree.Change(tmpNode, 't', data['Subscription'][i]['Feature'], tmpTransportVal, parentWithEventID.node_type);
			}

			for (var k = 0; k < data["Transport"].length; k++)
			{
				if (
					!data["Subscription"][i][transportInheritedKey] 
					&& data["Transport"][k]["Key"] == data["Subscription"][i][transportKey]
				)
					selected = true;
				else
					selected = false;
					
				transportSelect.appendChild(BX.create('option', {
					props: {
						'value': data["Transport"][k]["Key"],
						'selected': selected,
						'defaultSelected': selected
					},
					html: data["Transport"][k]["Value"]
				}
				));
			}

			BX.bind(transportSelect, "change", BX.delegate(SLTree.onChangeTransport, SLTree));
		}

		if (
			BX.message('sonetSLBUseVisible') == 'Y'
			&&
			(
				entity_id == 'all' 
				|| entity_id == 'allmy'
				|| 
				(
					typeof(data["Subscription"][i][visibleInheritedKey]) != "undefined"
					&& !data["Subscription"][i][visibleInheritedKey]
				)
			)

		)
		{
			if (data["Subscription"][i][visibleInheritedKey])
			{
				hiddenValue = 'I';
				checkboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-i-' + data["Subscription"][i][visibleKey];
				
				if (data["Subscription"][i][visibleKey] == 'Y')
					arCheckboxVal = ['I', 'N', 'Y'];
				else
					arCheckboxVal = ['I', 'Y', 'N'];
			}			
			else
			{
				hiddenValue = data["Subscription"][i][visibleKey];
				checkboxClassName = 'subscribe-list-checkbox subscribe-list-checkbox-' + data["Subscription"][i][visibleKey];
				
				arCheckboxVal = ['Y', 'N'];
			}

			SLVisibleCheckbox = new BX.CSLVisibleCheckbox(
				{
					'arCheckboxVal': arCheckboxVal,
					'bindElement': tr.firstChild.nextSibling,
					'checkboxClassName': checkboxClassName,
					'node': node,
					'cb': cb,
					'feature': data["Subscription"][i]["Feature"],
					'hiddenValue': hiddenValue,
					'visibleValue': data["Subscription"][i][visibleKey]
				}
			);
			qq = node + '_' + data["Subscription"][i]["Feature"];
			if (entity_cb == 'Y')
				qq += '_cb';
			arVisibleCheckbox[qq] = SLVisibleCheckbox;

			SLVisibleCheckbox.Show();
			
			tmpNode = SLTree.Tree[entity_type + '_' + entity_cb][entity_id]['event'];
			parentWithEventID = SLTree.getParent(tmpNode, data['Subscription'][i]['Feature'], 'Visible');

			if (parentWithEventID.type == 'event')
				tmpVisibleVal = parentWithEventID['arEvents'][data['Subscription'][i]['Feature']]['Visible'];
			else
				tmpVisibleVal = parentWithEventID['arEvents']['all']['Visible'];

			SLTree.Change(tmpNode, 'v', data['Subscription'][i]['Feature'], tmpVisibleVal, parentWithEventID.node_type);
		}
		
		
		
		
		
		
		tbody.appendChild(BX.create('tr', {
			props: {
				'className': 'subscribe-list-features'
			},
			children: [
				BX.create('td', 
					{ 
						attrs: { 
							'colSpan': 3						
						}, 
						props: {
							'className': 'subscribe-list-corners'
						},						
						children: [
							BX.create('DIV', 
								{ 
									props: { 
										'className': 'subscribe-list-features-lb'
									}, 
									children: [
										BX.create('DIV', 
											{ 
												props: { 
													'className': 'subscribe-list-features-rb'
												}
											}
										)
									]
								}
							)
						]
					}
				)
			]
		}));

		tbody.appendChild(BX.create('tr', {
			children: [
				BX.create('td', 
				{ 
					props: {
						'className': 'subscribe-list-feature-sep'
					},
					attrs: { 
						'colSpan': 3
					}
				})
			]
		}));
	}
	
	if (bFound == true && BX('plus_' + node))
	{
		BX.removeClass(BX('plus_' + node), 'subscribe-list-selector-plus');
		BX.addClass(BX('plus_' + node), 'subscribe-list-selector-minus');
	}
	else if (bFound == false && BX('plus_' + node))
		BX.removeClass(BX('plus_' + node), 'subscribe-list-selector-plus');
}


var sonetSLErrorDiv;

var SLBlock = new BX.CSLBlock();


if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var sonetSLXmlHttpGet = new XMLHttpRequest();

function sonet_sl_parse(str)
{
	str = str.replace(/^\s+|\s+$/g, '');
	while (str.length > 0 && str.charCodeAt(0) == 65279)
		str = str.substring(1);

	if (str.length <= 0)
		return false;
	
	if (str.substring(0, 1) != '{' && str.substring(0, 1) != '[' && str.substring(0, 1) != '*')
		str = '"*"';
		
	eval("arData = " + str);

	return arData;
}

function sonet_sl_get(node, entity_type, entity_id, entity_cb)
{
	if (typeof(entity_cb) == 'undefined')
		entity_cb = 'N';

	if (BX.message('sonetSLUserId') <= 0)
		return;

	if (sonetSLXmlHttpGet.readyState % 4)
		return;

	if (BX(node + '_content').innerHTML != null && BX(node + '_content').innerHTML.length > 0)
	{
		if (BX(node).style.display == 'none')
		{
			if (BX('plus_' + node))
			{
				BX.removeClass(BX('plus_' + node), 'subscribe-list-selector-plus');
				BX.addClass(BX('plus_' + node), 'subscribe-list-selector-minus');			
			}
		
			BX(node).style.display = 'block';
		}
		else
		{
			if (BX('plus_' + node))
			{
				BX.removeClass(BX('plus_' + node), 'subscribe-list-selector-minus');
				BX.addClass(BX('plus_' + node), 'subscribe-list-selector-plus');
			}
		
			BX(node).style.display = 'none';
		}

		return;
	}

	sonetSLXmlHttpGet.open(
		"get",
		BX.message('sonetSLGetPath') + "?" + BX.message('sonetSLSessid')
		+ "&action=get_data"
		+ "&lang=" + BX.util.urlencode(BX.message('sonetSLLangId'))
		+ "&site=" + BX.util.urlencode(BX.message('sonetSLSiteId'))		
		+ "&et=" + BX.util.urlencode(entity_type)
		+ "&eid=" + BX.util.urlencode(entity_id)
		+ "&ecb=" + BX.util.urlencode(entity_cb)
		+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetSLXmlHttpGet.send(null);

	sonetSLXmlHttpGet.onreadystatechange = function()
	{
		if (sonetSLXmlHttpGet.readyState == 4 && sonetSLXmlHttpGet.status == 200)
		{
			var data = sonet_sl_parse(sonetSLXmlHttpGet.responseText);
			if (typeof(data) == "object")
			{
				if (data[0] == '*')
				{
					if (sonetSLErrorDiv != null)
					{
						sonetSLErrorDiv.style.display = "block";
						sonetSLErrorDiv.innerHTML = sonetSLXmlHttpSet.responseText;
					}
					return;
				}
				
				sonetSLXmlHttpGet.abort();

				SLBlock.ShowContent(node, data, entity_type, entity_id, entity_cb);
				if (BX(node).style.display == 'none')
					BX(node).style.display = 'block';
			}
		}
	}
}

function sonet_sl_del(node, entity_type, entity_id, event_id, entity_cb)
{
	if (typeof(entity_cb) == 'undefined')
		entity_cb = 'N';

	if (BX.message('sonetSLUserId') <= 0)
		return;

	if (sonetSLXmlHttpGet.readyState % 4)
		return;

	sonetSLXmlHttpGet.open(
		"get",
		BX.message('sonetSLGetPath') + "?" + BX.message('sonetSLSessid')
		+ "&action=delete"		
		+ "&site=" + BX.util.urlencode(BX.message('sonetSLSiteId'))		
		+ "&et=" + BX.util.urlencode(entity_type)
		+ "&eid=" + BX.util.urlencode(entity_id)
		+ "&evid=" + BX.util.urlencode(event_id)
		+ "&ecb=" + BX.util.urlencode(entity_cb)
		+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetSLXmlHttpGet.send(null);

	sonetSLXmlHttpGet.onreadystatechange = function()
	{
		if (sonetSLXmlHttpGet.readyState == 4 && sonetSLXmlHttpGet.status == 200)
		{
			var data = sonet_sl_parse(sonetSLXmlHttpGet.responseText);
			if (typeof(data) == "object")
			{
				if (data[0] == '*' && sonetSLErrorDiv != null)
				{
					sonetSLErrorDiv.style.display = "block";
					sonetSLErrorDiv.innerHTML = sonetSLXmlHttpSet.responseText;
				}
				else if (data["ActionResult"] == 'OK' && BX(node) != null)
				{
					if (event_id == 'all')
					{
						if (BX(SLTree.Tree[entity_type + '_' + entity_cb][entity_id]['all']))
							BX.cleanNode(BX(SLTree.Tree[entity_type + '_' + entity_cb][entity_id]['all']).id)
						
						SLTree.Tree[entity_type + '_' + entity_cb][entity_id]['all'] = null;
						SLTree.Tree[entity_type + '_' + entity_cb][entity_id]['entity'] = null;
					}

					if (BX(node).nextSibling)
						BX(node).nextSibling.parentNode.removeChild(BX(node).nextSibling);
					if (BX(node).previousSibling)
						BX(node).previousSibling.parentNode.removeChild(BX(node).previousSibling);
					BX(node).parentNode.removeChild(BX(node));
						
				}

				sonetSLXmlHttpGet.abort();
				return;
			}
		}
	}
}
	
function sonet_sl_list_show(node)
{
	if (BX(node).style.display == 'none')
	{
		if (BX('plus_' + node))
		{
			BX.removeClass(BX('plus_' + node), 'subscribe-list-selector-plus');
			BX.addClass(BX('plus_' + node), 'subscribe-list-selector-minus');			
		}
		
		BX(node).style.display = 'block';
	}
	else
	{
		if (BX('plus_' + node))
		{
			BX.removeClass(BX('plus_' + node), 'subscribe-list-selector-minus');
			BX.addClass(BX('plus_' + node), 'subscribe-list-selector-plus');
		}
		
		BX(node).style.display = 'none';
	}

	return;
}