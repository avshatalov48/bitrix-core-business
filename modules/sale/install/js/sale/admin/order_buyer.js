BX.namespace("BX.Sale.Admin.OrderBuyer");

BX.Sale.Admin.OrderBuyer =
{
	propertyCollection: null,
	savedPropsCollections: {},
	profilesData: null,
	oldBuyerId: 0,
	isFeatureSaleAccountsEnabled: false,

	getFieldsUpdaters: function()
	{
		return {
			"BUYER_USER_NAME": BX.Sale.Admin.OrderBuyer.setBuyerName,
			"BUYER_PROFILES_LIST": BX.Sale.Admin.OrderBuyer.setBuyerProfilesList,
			"PROPERTIES_ARRAY": BX.Sale.Admin.OrderBuyer.setOrderPropsArray,
			"PROPERTIES": BX.Sale.Admin.OrderBuyer.setOrderProps,
			"BUYER_PROFILES_DATA": BX.Sale.Admin.OrderBuyer.setProfilesData
		};
	},

	setProfilesData: function(data)
	{
		BX.Sale.Admin.OrderBuyer.profilesData = data;
		BX.Sale.Admin.OrderBuyer.onBuyerProfileChange();
	},

	setBuyerName: function(name)
	{
		var nameLink = BX("BUYER_USER_NAME"),
			button = BX("sale-order-buyer-find-button-wrap"),
			nameWrap = BX("sale-order-buyer-name-wrap");

		if(nameLink)
			nameLink.innerHTML = BX.util.htmlspecialchars(name);

		if(name)
		{
			button.style.display = "none";
			nameWrap.style.display = "";
		}
		else
		{
			button.style.display = "";
			nameWrap.style.display = "none";
		}
	},

	setBuyerProfilesList: function(params)
	{
		var profList = BX("BUYER_PROFILE_ID"),
			buyerProfileId = 0;

		if(!profList)
		{
			var selectWrap = BX("BUYER_PROFILE_ID_CONTAINER");
			profList = BX.create("select", {props:{name: "BUYER_PROFILE_ID", id:"BUYER_PROFILE_ID"}});
			BX.bind(profList, "change", BX.Sale.Admin.OrderBuyer.onBuyerProfileChange);
			selectWrap.appendChild(profList);
		}

		if(profList.length > 0)
			for(var j= 0, l= profList.length; j<l; j++)
				profList.remove(profList[j]);

		if(!params)
		{
			profList.add(BX.create("option", { props:{value: "", text: BX.message("SALE_ORDER_BUYER_CREATE_NEW")}}));
			return;
		}

		for(var i in params)
		{
			if(!params.hasOwnProperty(i))
				continue;

			profList.add(BX.create("option", { props:{value: i,	text: params[i]}}));

			if(i > 0 && buyerProfileId == 0)
				buyerProfileId = i;
		}

		BX.Sale.Admin.OrderBuyer.setBuyerProfileId(buyerProfileId);
		BX.Sale.Admin.OrderBuyer.showBuyerProfilesList();
	},

	setBuyerProfileId: function(profileId)
	{
		var profList = BX("BUYER_PROFILE_ID");

		if(profList)
			profList.value = profileId;
	},

	showBuyerProfilesList: function()
	{
		var profList = BX("sale-order-buyer-profiles-list-row");

		if(profList)
			profList.style.display = "";
	},

	hideBuyerProfilesList: function()
	{
		var profList = BX("sale-order-buyer-profiles-list-row");

		if(profList)
			profList.style.display = "none";
	},

	setOrderProps: function(params)
	{
		for(var i in params)
		{
			if(!params.hasOwnProperty(i))
				continue;

			var property = BX.Sale.Admin.OrderBuyer.propertyCollection.getById(i);

			if(property)
				property.setValue(params[i]);
		}

		BX.Sale.Admin.OrderBuyer.callPropertiesUpdaters();
	},

	/* user activity handlers */
	showChooseBuyerWindow: function(languageId)
	{
		window.open(
			'/bitrix/admin/user_search.php?lang='+languageId+'&FN='+BX.Sale.Admin.OrderEditPage.formId+'&FC=USER_ID',
			'',
			'scrollbars=yes,resizable=yes,width=840,height=500,top='+Math.floor((screen.height - 840)/2-14)+',left='+Math.floor((screen.width - 760)/2-5)
		);
	},

	clearBuyer: function()
	{
		BX.Sale.Admin.OrderBuyer.setBuyerName("");
		BX.Sale.Admin.OrderBuyer.setBuyerId(0);
		BX.Sale.Admin.OrderBuyer.hideBuyerProfilesList();
		BX.Sale.Admin.OrderBuyer.setBuyerProfilesList(false);
		BX.Sale.Admin.OrderBuyer.onBuyerIdChange(BX("USER_ID"));
	},

	onBuyerIdChange: function(buyerIdNode)
	{
		BX.Sale.Admin.OrderBuyer.updateBuyerProfileLink(buyerIdNode.value);
		BX("OLD_USER_ID").value = BX.Sale.Admin.OrderBuyer.oldBuyerId;
		BX.Sale.Admin.OrderBuyer.oldBuyerId = buyerIdNode.value;

		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.getOrderFields({
				givenFields:{
					"USER_ID": buyerIdNode.value,
					"PERSON_TYPE_ID": this.getBuyerTypeId(),
					"CURRENCY": BX.Sale.Admin.OrderEditPage.currency,
					"ORDER_ID": BX.Sale.Admin.OrderEditPage.orderId,
					"SITE_ID": BX.Sale.Admin.OrderEditPage.siteId,
					"BUYER_ID_CHANGED": "Y"
				},
				demandFields:[
					"BUYER_PROFILES_LIST",
					"BUYER_PROFILES_DATA",
					"BUYER_USER_NAME",
					"BUYER_BUDGET",
					"PROPERTIES"
				]
			}), false, true
		);
	},

	onBuyerTypeChange: function(personTypeId)
	{
		var demanded = ["BUYER_PROFILES_LIST", "BUYER_PROFILES_DATA"];

		if(!BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId])
		{
			demanded.push("PROPERTIES_ARRAY");
		}
		else
		{
			BX.Sale.Admin.OrderBuyer.setOrderPropsArray();
		}

//		demanded.push("PROPERTIES");

		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
				demandFields: demanded,
				operation: "BUYER_CHANGED",
				givenFields: {
					"USER_ID": this.getBuyerId(),
					"PERSON_TYPE_ID": personTypeId,
					"SITE_ID": BX.Sale.Admin.OrderEditPage.siteId
				}
			})
		);
	},

	onBuyerProfileChange: function()
	{
		var profileId = BX.Sale.Admin.OrderBuyer.getBuyerProfileId(),
			typeId = BX.Sale.Admin.OrderBuyer.getBuyerTypeId(),
			profData = BX.Sale.Admin.OrderBuyer.profilesData;

		if(profData && profData[typeId] && profData[typeId][profileId])
		{
			BX.Sale.Admin.OrderBuyer.setOrderProps(profData[typeId][profileId]);

			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
						operation: "BUYER_PROFILE_CHANGED"
					}
				)
			);
		}
		else if(profileId != 0)
		{
			BX.Sale.Admin.OrderAjaxer.sendRequest(
				BX.Sale.Admin.OrderEditPage.ajaxRequests.getOrderFields({
					givenFields:{
						"USER_ID": this.getBuyerId(),
						"BUYER_PROFILE_ID": this.getBuyerProfileId()
					},
					demandFields:["PROPERTIES"]
				}),
				true
			);
		}
	},

	getBuyerProfileId: function()
	{
		return BX.Sale.Admin.OrderEditPage.getElementValue("BUYER_PROFILE_ID");
	},

	getBuyerTypeId: function()
	{
		return BX.Sale.Admin.OrderEditPage.getElementValue("PERSON_TYPE_ID");
	},

	getBuyerId: function()
	{
		return BX.Sale.Admin.OrderEditPage.getElementValue("USER_ID");
	},

	setBuyerId: function(buyerId)
	{
		BX("USER_ID").value = buyerId;
		BX.Sale.Admin.OrderBuyer.updateBuyerProfileLink(buyerId);
	},

	updateBuyerProfileLink: function(buyerId)
	{
		if(BX.Sale.Admin.OrderBuyer.isFeatureSaleAccountsEnabled)
			BX("BUYER_USER_NAME").href = "/bitrix/admin/sale_buyers_profile.php?lang="+BX.Sale.Admin.OrderEditPage.languageId+"&USER_ID="+buyerId;
		else
			BX("BUYER_USER_NAME").href = "/bitrix/admin/user_edit.php?lang="+BX.Sale.Admin.OrderEditPage.languageId+"&ID="+buyerId;
	},

	setOrderPropsArray: function(props)
	{
		var personTypeId = BX.Sale.Admin.OrderBuyer.getBuyerTypeId();

		if(!BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId])
			BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId] = new BX.Sale.PropertyCollection(props);

		BX.Sale.Admin.OrderBuyer.propertyCollection = BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId];

		if(!BX.Sale.Admin.OrderBuyer.propertyCollection)
		{
			BX.debug('Error! Can\'t initialize property collection!');
			return;
		}

		BX.Sale.Admin.OrderBuyer.callPropertiesUpdaters();
		var propEmail, propPhone, propLocation, propFio;

		if(propEmail = BX.Sale.Admin.OrderBuyer.propertyCollection.getUserEmail())
			propEmail.addEvent("change", function(e){BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater(
				"BUYER_EMAIL", propEmail.getValue());
			});

		if(propPhone = BX.Sale.Admin.OrderBuyer.propertyCollection.getPhone())
			propPhone.addEvent("change", function(e){BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater(
				"BUYER_PHONE", propPhone.getValue());
			});

		if(propLocation = BX.Sale.Admin.OrderBuyer.propertyCollection.getDeliveryLocation())
			propLocation.addEvent("change", function(e){BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater(
				"LOCATION", propLocation.getValue());
			});

		if(propFio = BX.Sale.Admin.OrderBuyer.propertyCollection.getPayerName())
			propFio.addEvent("change", function(e){BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater(
				"BUYER_FIO", propFio.getValue());
			});

		var container = BX("order_properties_container"),
			group, property,
			groupIterator = BX.Sale.Admin.OrderBuyer.propertyCollection.getGroupIterator();

		for(var i= 0, l = container.children.length; i < l; i++)
			container.removeChild(container.children[0]);

		while (group = groupIterator())
		{
			var name = group.getName() ? BX.util.htmlspecialchars(group.getName()) : BX.message('SALE_ORDER_BUYER_UNKNOWN_GROUP'),
				div1 = BX.create('DIV',{props:{className:"adm-bus-table-container caption border sale-order-props-group"}}),
				divName = BX.create('DIV',{props:{className:"adm-bus-table-caption-title"}, html:name}),
				table = BX.create('TABLE',{props:{className:"adm-detail-content-table edit-table"}}),
				propsIterator =  group.getIterator();

			table.border = 0;
			table.cellspacing = 0;
			table.cellpadding = 0;
			table.width = "100%";

			while (property = propsIterator())
			{
				var tr = BX.create('tr'),
					tdName = BX.create('td', {props:{className:"adm-detail-content-cell-l"}, html: BX.util.htmlspecialchars(property.getName())+":"}),
					tdControl = BX.create('td', {props:{className:"adm-detail-content-cell-r"}});

				tdName.style.verticalAlign = 'top';
				tdName.style.paddingTop = '0.8em';

				if(property.isRequired())
					BX.addClass(tdName, "fwb");

				tdName.width = "40%";
				property.appendTo(tdControl);
				tr.appendChild(tdName);
				tr.appendChild(tdControl);
				table.appendChild(tr);
			}

			div1.appendChild(divName);
			div1.appendChild(table);
			container.appendChild(div1);
		}
	},

	setOrderRelPropsArray: function(props)
	{
		var personTypeId = BX.Sale.Admin.OrderBuyer.getBuyerTypeId();

		BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId + '_rel'] = new BX.Sale.PropertyCollection(props);

		BX.Sale.Admin.OrderBuyer.propertyLocCollection = BX.Sale.Admin.OrderBuyer.savedPropsCollections[personTypeId+'_rel'];

		if(!BX.Sale.Admin.OrderBuyer.propertyLocCollection)
		{
			BX.debug('Error! Can\'t initialize property collection!');
			return;
		}

		var container = BX("order_properties_container_add"),
			group, property,
			groupIterator = BX.Sale.Admin.OrderBuyer.propertyLocCollection.getGroupIterator();


		for(var i= 0, l = container.children.length; i < l; i++)
			container.removeChild(container.children[0]);

		var parent = BX.findParent(container, {'attr': 'data-id'}, true);
		var navElement = BX('nav_relprops').parentNode;

		if (props.properties.length > 0)
		{
			BX.show(parent);
			BX.style(navElement, 'display', 'inline-block');
		}
		else
		{
			BX.hide(parent);
			BX.hide(navElement);
		}

		while (group = groupIterator())
		{
			var div1 = BX.create('DIV',{props:{className:"adm-bus-table-container caption border sale-order-props-group"}}),
				divName = BX.create('DIV',{props:{className:"adm-bus-table-caption-title"}, html:BX.util.htmlspecialchars(group.getName())}),
				table = BX.create('TABLE',{props:{className:"adm-detail-content-table edit-table"}}),
				propsIterator =  group.getIterator();

			table.border = 0;
			table.cellspacing = 0;
			table.cellpadding = 0;
			table.width = "100%";

			while (property = propsIterator())
			{
				var tr = BX.create('tr'),
					tdName = BX.create('td', {props:{className:"adm-detail-content-cell-l"}, html: BX.util.htmlspecialchars(property.getName())+":"}),
					tdControl = BX.create('td', {props:{className:"adm-detail-content-cell-r"}});

				if(property.isRequired())
					BX.addClass(tdName, "fwb");

				tdName.width = "40%";
				property.appendTo(tdControl);
				tr.appendChild(tdName);
				tr.appendChild(tdControl);
				table.appendChild(tr);
			}

			div1.appendChild(divName);
			div1.appendChild(table);
			container.appendChild(div1);
		}
	},

	callPropertiesUpdaters: function()
	{
		var prop;

		if(prop = BX.Sale.Admin.OrderBuyer.propertyCollection.getPhone())
			BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater("BUYER_PHONE", prop.getValue());

		if(prop = BX.Sale.Admin.OrderBuyer.propertyCollection.getUserEmail())
			BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater("BUYER_EMAIL", prop.getValue());

		if(prop = BX.Sale.Admin.OrderBuyer.propertyCollection.getDeliveryLocation())
			BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater("LOCATION", prop.getValue());

		if(prop = BX.Sale.Admin.OrderBuyer.propertyCollection.getPayerName())
			BX.Sale.Admin.OrderEditPage.callConcreteFieldUpdater("BUYER_FIO", prop.getValue());
	}
};
