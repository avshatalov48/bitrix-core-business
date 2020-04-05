function JCIBlockGenerator(arParams)
{
	if(!arParams) return;

	this.intERROR = 0;
	this.intIMAGE_ROW_ID = 0;
	this.PREFIX = arParams.PREFIX;
	this.PREFIX_TR = this.PREFIX+'ROW_';
	this.PROP_COUNT_ID = arParams.PROP_COUNT_ID;
	this.TABLE_PROP_ID = arParams.TABLE_PROP_ID;
	this.AR_ALL_PROPERTIES = arParams.AR_ALL_PROPERTIES;
	this.AR_FILE_PROPERTIES = arParams.AR_FILE_PROPERTIES;
	this.IMAGE_TABLE_ID = arParams.IMAGE_TABLE_ID;
	this.CELLS = [];
	this.CELL_CENT = [];
	this.PROPERTY_MAP = [];
	this.CHECKED_MAP = [];
	this.SELECTED_PROPERTIES = [];
	this.lockProperties = false;

	BX.ready(BX.proxy(this.Init, this));
}

JCIBlockGenerator.prototype.Init = function()
{
	var i,
		tmpMap,
		j;

	this.PROP_TBL = BX(this.TABLE_PROP_ID);

	if (!this.PROP_TBL)
	{
		this.intERROR = -1;
		return;
	}
	this.PROP_COUNT = BX(this.PROP_COUNT_ID);

	if (!this.PROP_COUNT)
	{
		this.intERROR = -1;
		return;
	}

	for (i = 0; i < this.AR_ALL_PROPERTIES.length; i++)
	{
		tmpMap = [];
		if (this.AR_ALL_PROPERTIES[i].hasOwnProperty('VALUE'))
		{
			for (j = 0; j < this.AR_ALL_PROPERTIES[i]["VALUE"].length; j++)
				tmpMap[this.AR_ALL_PROPERTIES[i]["VALUE"][j]["ID"]] = (this.AR_ALL_PROPERTIES[i]["VALUE"][j]["VALUE"]);
		}
		this.PROPERTY_MAP[this.AR_ALL_PROPERTIES[i]["ID"]] = (tmpMap);
		this.CHECKED_MAP[this.AR_ALL_PROPERTIES[i]["ID"]] = [];
	}
};

JCIBlockGenerator.prototype.addPropertyTable = function(id)
{
	if (0 > this.intERROR || BX("property_table"+id))
		return;

	this.PROP_TBL = BX(this.TABLE_PROP_ID);

	var numberOfProperties = Number(BX("generator_property_table_max_id").value);
	if(numberOfProperties && numberOfProperties < this.AR_ALL_PROPERTIES.length && numberOfProperties > 0)
	{
		this.PROP_TBL.appendChild(BX.create('div', {
			props:{id:"property_separator_"+id, className:'adm-shop-table-increase'}
		}));
	}

	var content = BX.create('div', {
		props: {id : 'property_td_'+id, className: 'adm-shop-table-wrap'},
		style: { verticalAlign: 'top' },
		children: [
			BX.create('table', {
				props : {
					className: "adm-shop-table",
					id : "property_table"+id
				},
				children : [
					BX.create('tr', {
						props : {
							className : 'adm-shop-table-title'
						},
						children :[
							BX.create('td', {
								props: {
									className : "adm-shop-table-cell"
								},
								children :[
									BX.create('span', {text:this.AR_ALL_PROPERTIES[id]["NAME"]})
								]
							}),
							BX.create('td', {
								props: {
									className : "adm-shop-table-cell"
								}
							}),
							BX.create('td', {
								props: {
									className : "adm-shop-table-cell"
								},
								children :[
									BX.create('span', {
										props : {
											className:"adm-shop-del-btn"
										},
										events : {
											click : (function(_this)
											{
												return function()
												{
													_this.deleteTd(id)
												}
											})(this)
										}
									})
								]
							})
						]
					}),
					BX.create('tr', {
						props : {
							className: 'adm-shop-table-header'
						},
						children :[
							BX.create('td', {
								props : {
									className:'adm-shop-table-cell'
								},
								children :[
									BX.create('span', {text : BX.message("IB_SEG_TITLE")/**/})
								]
							}),
							BX.create('td', {
								props : {
									className:'adm-shop-table-cell'
								},
								children :[
									BX.create('span', {text : BX.message("IB_SEG_SORT")})
								]
							}),
							BX.create('td', {
								props : {
									className:'adm-shop-table-cell'
								},
								children :[
									BX.create('input', {
										props : {
											type : "checkbox",
											id : "checked_all_"+id,
											checked : false,
											className : "adm-designed-checkbox"
										},
										events : {
											click : (function(_this)
											{
												return function()
												{
													_this.checkboxManage(this, id)
												}
											})(this)
										}
									}),
									BX.create('label', {
										props : {
											className : "adm-designed-checkbox-label",
											htmlFor : "checked_all_"+id
										}
									})
								]
							})
						]
					})
				]
			})
		]
	});

	this.PROP_TBL.appendChild(content);
	BX("generator_property_table_max_id").value = Number(BX("generator_property_table_max_id").value) + 1;
	this.AR_ALL_PROPERTIES[id]['USE'] = 'N';
	this.CHECKED_MAP[this.AR_ALL_PROPERTIES[id]["ID"]] = [];
	if(BX('property_table'+id) && this.AR_ALL_PROPERTIES[id]["VALUE"])
	{
		for(var i = 0; i < this.AR_ALL_PROPERTIES[id]["VALUE"].length; i++)
		{
			BX('property_table'+id).appendChild(BX.create('tr', {
				children :[
					BX.create('td', {
						props:{
							className:'adm-shop-table-cell'
						},
						children:[
							BX.create('span', {text : this.AR_ALL_PROPERTIES[id]["VALUE"][i]["VALUE"]}),
							BX.create('input', {
								props : {
									type : "hidden",
									id : "property_value_"+id,
									name : "PROPERTY_VALUE["+this.AR_ALL_PROPERTIES[id]["VALUE"][i]['PROPERTY_ID']+"]["+this.AR_ALL_PROPERTIES[id]["VALUE"][i]['ID']+"]",
									value : this.AR_ALL_PROPERTIES[id]["VALUE"][i]['ID']
								}
							})
						]
					}),
					BX.create('td', {
						props:{
							className:'adm-shop-table-cell'
						},
						children:[
							BX.create('span', {text : this.AR_ALL_PROPERTIES[id]["VALUE"][i]["SORT"]})
						]
					}),
					BX.create('td', {
						props:{
							className:'adm-shop-table-cell'
						},
						children:[
							BX.create('input', {
								props : {
									type : "checkbox",
									id : "PROPERTY_CHECK_"+this.AR_ALL_PROPERTIES[id]["VALUE"][i]['PROPERTY_ID']+"_"+i,
									checked : false,
									name : "PROPERTY_CHECK["+this.AR_ALL_PROPERTIES[id]["VALUE"][i]['PROPERTY_ID']+"]["+this.AR_ALL_PROPERTIES[id]["VALUE"][i]['ID']+"]",
									className : "adm-designed-checkbox property_value_checkbox"+id
								},
								events: {
									change: (function(_this){
										return function() {
											_this.checkboxMapManage(this);
										};
									})(this)
								}
							}),
							BX.create('label', {
								props : {
									className : "adm-designed-checkbox-label",
									htmlFor : "PROPERTY_CHECK_"+this.AR_ALL_PROPERTIES[id]["VALUE"][i]['PROPERTY_ID']+"_"+i
								}
							})
						]
					})
				]
			}))
		}
	}
};

JCIBlockGenerator.prototype.deleteTd = function(id)
{
	var deleteTd = BX('property_td_'+id),
		prevSibling = BX('property_table'+id).parentNode.previousSibling,
		nextSibling = BX('property_table'+id).parentNode.nextSibling;

	if(prevSibling)
		var prevSeparator = BX('property_table'+id).parentNode.previousSibling.className == 'adm-shop-table-increase';
	if(nextSibling)
		var nextSeparator = BX('property_table'+id).parentNode.nextSibling.className == 'adm-shop-table-increase';
	if(deleteTd)
	{
		this.AR_ALL_PROPERTIES[id]['USE'] = 'N';
		delete this.CHECKED_MAP[this.AR_ALL_PROPERTIES[id]["ID"]];
		if(prevSeparator)
			prevSibling.parentNode.removeChild(prevSibling);
		else if(nextSeparator)
			nextSibling.parentNode.removeChild(nextSibling);
		deleteTd.parentNode.removeChild(deleteTd);
		BX("generator_property_table_max_id").value = Number(BX("generator_property_table_max_id").value) - 1;
	}
};

JCIBlockGenerator.prototype.loadAllProperties = function()
{
	var table, inputs;
	if(!this.lockProperties)
	{
		for(var i = 0; i < this.AR_ALL_PROPERTIES.length; i++)
		{
			if(table = BX("property_table"+i))
			{
				for(var j = 0; j < table.children.length; j++)
				{
					inputs = table.children[j].getElementsByTagName('input');
				}
			}
			this.addPropertyTable(i);
		}
	}
};

JCIBlockGenerator.prototype.checkboxManage = function(e, id)
{
	var checkboxGroup = document.getElementsByClassName('property_value_checkbox'+id),
		i;

	this.AR_ALL_PROPERTIES[id]['USE'] = (e.checked ? 'Y' : 'N');
	if (checkboxGroup)
	{
		for(i = 0; i < checkboxGroup.length; i++)
		{
			checkboxGroup[i].checked = e.checked;
			this.checkboxMapManage(checkboxGroup[i]);
		}
	}

};

JCIBlockGenerator.prototype.checkboxMapManage = function(e)
{
	var checkboxName = e.name;
	var checkboxClassName = e.className;
	var allCheckboxes = document.getElementsByClassName(checkboxClassName);
	var reg = /\[([0-9a-zA-Z]+)\]/g;
	var reg2 = /([0-9]+)/g;
	var propId = checkboxName.match(reg)[0].match(/[0-9a-zA-Z]+/g)[0];
	var propValueId = checkboxName.match(reg)[1].match(/[0-9a-zA-Z]+/g)[0];
	var propIdByClass = checkboxClassName.match(reg2);
	var disableCount = 0;

	if(e.checked)
	{
		this.CHECKED_MAP[propId][propValueId] = 'Y';
		this.AR_ALL_PROPERTIES[propIdByClass]['USE'] = 'Y';
	}
	else
	{
		for(var i in allCheckboxes)
		{
			if(allCheckboxes.hasOwnProperty(i))
				if(allCheckboxes[i].type == 'checkbox' && !allCheckboxes[i].checked)
					disableCount++;
		}
		if(disableCount == allCheckboxes.length)
		{
			this.AR_ALL_PROPERTIES[propIdByClass]['USE'] = 'N';
		}
		delete this.CHECKED_MAP[propId][propValueId];
	}

};

JCIBlockGenerator.prototype.addPropertyImages = function()
{
	var postData,
		isChecked = false,
		i,
		j;

	for (i in this.CHECKED_MAP)
	{
		if (this.CHECKED_MAP.hasOwnProperty(i))
		{
			for (j in this.CHECKED_MAP[i])
			{
				if (this.CHECKED_MAP[i].hasOwnProperty(j) && this.CHECKED_MAP[i][j] === 'Y')
					isChecked = true;
			}
		}
	}

	if (!isChecked)
	{
		alert(BX.message('PROPERTY_VALUES_EMPTY'));
		return;
	}

	this.disableControls();
	postData = {
		"PROPERTY_CHECK": this.CHECKED_MAP,
		"PROPERTY_VALUE": this.PROPERTY_MAP,
		"AJAX_MODE": 'Y',
		"sessid": BX.bitrix_sessid()
	};
	BX.showWait('ib_seg_add_images_button');
	BX.ajax.post('/bitrix/tools/catalog/iblock_subelement_generator.php', postData, BX.proxy(this.fPropertyImagesResult, this));
};

JCIBlockGenerator.prototype.fPropertyImagesResult = function(result)
{
	BX.closeWait();
	if(result.length > 0)
	{
		if(!BX('image_table_thead'))
			this.addImageTableHead();
		else
			BX('image_table_thead').style.display = "table-row";

		var objMap = eval(result);

		this.addImageTableRow(objMap);
	}

};

JCIBlockGenerator.prototype.addImageTableHead = function()
{
	var table = BX(this.IMAGE_TABLE_ID),
		thead = table.appendChild(
		BX.create('tr', {
			props :{id:"image_table_thead", className:'heading'},
			children:[
				BX.create("td")
			]
		})
		),
		showedProperty = [];

	for(var i = 0; i < this.AR_ALL_PROPERTIES.length; i++)
	{
		if(this.AR_ALL_PROPERTIES[i].hasOwnProperty('VALUE') && (typeof this.AR_ALL_PROPERTIES[i] == "object") && (this.AR_ALL_PROPERTIES[i] !== null) && (this.AR_ALL_PROPERTIES[i]['USE'] !== 'N'))
		{
			thead.appendChild(
				BX.create('td', {
					text : this.AR_ALL_PROPERTIES[i]["NAME"]
				})
			);
		}
	}
	for(var key in this.SELECTED_PROPERTIES)
	{
		if(!this.SELECTED_PROPERTIES.hasOwnProperty(key))
			continue;
		if (BX.util.in_array(this.SELECTED_PROPERTIES[key], showedProperty))
			continue;
		if(this.SELECTED_PROPERTIES[key] == 'DETAIL')
		{
			thead.appendChild(
				BX.create('td', {
					text : BX.message('IB_SEG_DETAIL')
				}));
		}
		if(this.SELECTED_PROPERTIES[key] == 'ANNOUNCE')
		{
			thead.appendChild(
				BX.create('td', {
					text : BX.message('IB_SEG_ANNOUNCE')
				}));
		}
		for(var key2 in this.AR_FILE_PROPERTIES)
		{
			if(this.AR_FILE_PROPERTIES.hasOwnProperty(key2))
			{
				if(this.AR_FILE_PROPERTIES[key2]["ID"] == this.SELECTED_PROPERTIES[key])
				{
					thead.appendChild(
						BX.create('td', {
							text : this.AR_FILE_PROPERTIES[key2]["NAME"]
						})
					);
				}
			}
		}
		showedProperty[showedProperty.length] = this.SELECTED_PROPERTIES[key];
	}
};

JCIBlockGenerator.prototype.addImageTableRow = function(objResult)
{
	var table = BX(this.IMAGE_TABLE_ID),
		tbody,
		showedProperty = [],
		key,
		objResultMap;
	for(key in objResult)
	{
		if(objResult.hasOwnProperty(key))
			objResultMap = objResult[key];
	}

	this.intIMAGE_ROW_ID = 0;
	if(BX('ib_seg_max_image_row_id'))
	{
		this.intIMAGE_ROW_ID = BX('ib_seg_max_image_row_id').value;
		BX('ib_seg_max_image_row_id').value = Number(BX('ib_seg_max_image_row_id').value) + 1;
	}

	if(BX("image_table_tbody"))
		tbody = BX("image_table_tbody");
	else
		tbody = table.appendChild(
			BX.create('tbody', {
				props: {id:"image_table_tbody"}
			})
		);

	var row = tbody.appendChild(
		BX.create('tr', {
			props:{
				id:"ib_seg_image_row_"+this.intIMAGE_ROW_ID,
				className: "ib_seg_image_row"
			}
		})
	);
	row.appendChild(
		BX.create('td', {
				children :[
					BX.create('div', {
						props : {
							className:"adm-shop-del-btn"
						},
						style : {
							marginLeft:"5px"
						},
						events:{
							click:
								(function(){
									return function() {
										var tr = this.parentNode.parentNode;
										tr.style.display = "none";
										tr.className += '_hidden';
										var selects = tr.getElementsByTagName('select');

										for(var pId in selects)
										{
											if(selects.hasOwnProperty(pId) && !isNaN(pId))
											{
												selects[pId].appendChild(BX.create('OPTION', {
														'props': {'value':-2},
														'text': '-2'}
												));
												selects[pId].value = -2;
											}
										}
										var visibleTableRows = BX('image_table_tbody').querySelectorAll('tr.ib_seg_image_row');
										if(visibleTableRows.length == 0 && BX('image_table_thead'))
										{
											BX('image_table_thead').style.display = "none";
										}
									};
								}
									)()
						}
					})
				]
			}
		)
	);

	for(var i = 0; i < this.AR_ALL_PROPERTIES.length; i++)
	{
		if(this.AR_ALL_PROPERTIES[i].hasOwnProperty('VALUE') && (typeof this.AR_ALL_PROPERTIES[i] == "object") && (this.AR_ALL_PROPERTIES[i] !== null) && (this.AR_ALL_PROPERTIES[i]['USE'] !== 'N'))
		{
			key = this.AR_ALL_PROPERTIES[i].ID;
			if (typeof (objResultMap[key]) !== 'undefined')
			{
				var options = [BX.create('OPTION', {
					'props': {'value':-1},
					'text': BX.message('IB_SEG_FOR_ALL')}
				)];
				for(var key2 in objResultMap[key])
				{
					if(objResultMap[key].hasOwnProperty(key2))
					{
						options[options.length] = BX.create('OPTION', {
							'props': {'value':key2},
							'text': objResultMap[key][key2]}
						);
					}
				}
				row.appendChild(
					BX.create('td', {
						children:[
							BX.create('span', {
								props: {
									className:'adm-select-wrap'
								},
								children:[
									BX.create('select', {
										props: {
											className : 'adm-select',
											name:"PROP["+key+"]["+this.intIMAGE_ROW_ID+"]",
											id:"PROP["+key+"]["+this.intIMAGE_ROW_ID+"]"
										},
										style : {
											width:'130px'
										},
										children:options
									})
								]
							})
						]
					})
				);
			}
		}
	}

	for(key in this.SELECTED_PROPERTIES)
	{
		if (!this.SELECTED_PROPERTIES.hasOwnProperty(key))
			continue;
		if (BX.util.in_array(this.SELECTED_PROPERTIES[key], showedProperty))
			continue;
		this.fIblockInputGet(this.SELECTED_PROPERTIES[key]);
		showedProperty[showedProperty.length] = this.SELECTED_PROPERTIES[key];
	}
};

JCIBlockGenerator.prototype.fIblockInputResult = function(result)
{
	var rand = Math.random();
	var td = BX('ib_seg_image_row_'+this.intIMAGE_ROW_ID).appendChild(
		BX.create('td', {
			props:{id:'ib_seg_image_td_'+this.intIMAGE_ROW_ID+'_'+rand}
		})
	);
	td.innerHTML = result;
	if(BX('ib_seg_image_td_'+this.intIMAGE_ROW_ID+'_'+rand).firstChild)
	{
		BX.bind(BX('ib_seg_image_td_'+this.intIMAGE_ROW_ID+'_'+rand).firstChild, "click", BX.proxy(function (e) {
			BX('bx_admin_form').parentNode.scrollTop += 60;
		}, this));
	}
};
JCIBlockGenerator.prototype.fIblockInputGet = function(propertyId)
{
	var postData = [];
	postData["AJAX_MODE"] = 'Y';
	postData["sessid"] = BX.bitrix_sessid();
	postData["GET_INPUT"] = 'Y';
	postData["PROPERTY_ID"] = propertyId;
	postData["ROW_ID"] = this.intIMAGE_ROW_ID;

	BX.ajax({
		'method': 'POST',
		'dataType': 'html',
		'url': '/bitrix/tools/catalog/iblock_subelement_generator_ajax.php',
		'data': BX.ajax.prepareData(postData),
		'async': false,
		'onsuccess': BX.proxy(this.fIblockInputResult, this)
	});

};

JCIBlockGenerator.prototype.disableControls = function()
{
	var checkboxAllGroup;
	this.lockProperties = true;
	for(var i = 0; i < this.AR_ALL_PROPERTIES.length; i++)
	{
		if(BX('checked_all_'+i))
		{
			BX('checked_all_'+i).onclick = function() {
				return false;
			};
		}
		checkboxAllGroup = document.getElementsByClassName('property_value_checkbox'+i);
		var checkboxGroupLength = checkboxAllGroup.length;
		for(var j = 0; j < checkboxGroupLength; j++)
		{
			if(checkboxAllGroup[j])
			{
				checkboxAllGroup[j].onclick = function() {
					return false;
				};

				if(checkboxAllGroup[j].nextSibling.className == "adm-designed-checkbox-label")
				{
					if(checkboxAllGroup[j].checked)
						checkboxAllGroup[j].nextSibling.style.backgroundPosition = "0 -3459px";
					else
						checkboxAllGroup[j].nextSibling.style.backgroundPosition = "0 -1350px";
				}
			}
		}
	}
	var addPropertySelects = BX('ib_seg_select_prop_bar').querySelectorAll('select');
	[].forEach.call(addPropertySelects, function padParent(item) {
		item.disabled = true;
	});

	var deleteTableBut = BX(this.TABLE_PROP_ID).querySelectorAll('span.adm-shop-del-btn');
	[].forEach.call(deleteTableBut, function padParent(item) {
		item.parentNode.innerHTML += ' ';
	});

	if(BX('ib_seg_property_add_button_span_click'))
	{
		BX('ib_seg_property_add_button_span_click').parentNode.innerHTML = '';
	}
	if(BX('mnu_ADD_PROPERTY'))
	{
		BX('mnu_ADD_PROPERTY').parentNode.innerHTML += ' ';
	}
};

JCIBlockGenerator.prototype.addPropertyInTitle = function(propertyCode)
{
	var titleInput = BX('IB_SEG_TITLE');
	if(titleInput)
	{
		titleInput.value += ' '+propertyCode;
	}
};