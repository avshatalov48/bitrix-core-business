'use strict';

BX.namespace('BX.Sale.PropertyCollection');

BX.Sale.PropertyCollection = (function () {

	// Iterator

	var iterator = function (array)
	{
		var i = 0;
		return function () {return array[i++];};
	};

	// Group

	var Group = function (group, properties)
	{
		this.getId           = function () {return group.ID;};
		this.getName         = function () {return group.NAME;};
		this.getPersonTypeId = function () {return group.PERSON_TYPE_ID;};
		this.getIterator     = function () {return iterator(properties);};
	};

	// Property

	var Editor = BX.Sale.Input.Manager.Editor;

	var newProperty = function (property, publicMode)
	{
		var name = !!publicMode ? 'ORDER_PROP_' + property.ID : 'PROPERTIES[' + property.ID + ']';
		var me = (property.TYPE == 'LOCATION' && !!publicMode) ? {} : new Editor(name, property);
		me.getId           = function () {return property.ID;};
		me.getName         = function () {return property.NAME;};
		me.getType         = function () {return property.TYPE;};
		me.isRequired      = function () {return property.REQUIRED === 'Y';};
		me.isMultiple      = function () {return property.MULTIPLE === 'Y';};
		me.getGroupId      = function () {return property.PROPS_GROUP_ID;};
		me.getDescription  = function () {return property.DESCRIPTION;};
		me.getPersonTypeId = function () {return property.PERSON_TYPE_ID;};
		me.getAltLocation  = function () {return property.INPUT_FIELD_LOCATION;};
		me.getSettings	   = function () {return property};
		return me;
	};

	// Collection

	var	bizFields = ['IS_EMAIL', 'IS_PAYER', 'IS_LOCATION', 'IS_LOCATION4TAX', 'IS_PROFILE_NAME', 'IS_ZIP', 'IS_PHONE', 'IS_ADDRESS'],
		bizLength = bizFields.length;

	return function (data)
	{
		// private

		var	groups = [],
			properties = [],
			propertyIndex = {};

		// temporary

		var	list, length, i, item,
			groupId, props, groupedProperties = {}, altLocations = [],
			propertyId, property,
			bizI, bizName,
			publicMode = !!data.publicMode;

		// create groups

		list = data.groups;

		for (i in list)
		{
			if(!list.hasOwnProperty(i))
				continue;

			item = list[i];
			groupId = item.ID;

			props = [];
			groupedProperties[groupId] = props;
			groups.push(new Group(item, props));
		}

		// create properties

		list = data.properties;

		for (i in list)
		{
			if(!list.hasOwnProperty(i))
				continue;

			item = list[i];
			propertyId = item.ID;

			groupId = item.PROPS_GROUP_ID;
			property = newProperty(item, publicMode);

			propertyIndex[propertyId] = property;
			properties.push(property);

			if (groupedProperties.hasOwnProperty(groupId))
			{
				groupedProperties[groupId].push(property);
			}
			else
			{
				throw 'undefined group';
			}

			if (item.TYPE == 'LOCATION' && item.INPUT_FIELD_LOCATION && !publicMode)
				altLocations.push(property);

			for (bizI = 0; bizI < bizLength; bizI++)
			{
				bizName = bizFields[bizI];
				if (item[bizName] == 'Y')
					propertyIndex[bizName] = property;
			}
		}

		// assign alternative location field callback

		length = altLocations.length;

		for (i = 0; i < length; i++)
		{
			altLocations[i].addEvent('change', function (event, input)
			{
				var hasCity = false,
					valuePath = input.getValuePath(),
					i = 0, length = valuePath.length;

				for (; i < length; i++)
				{
					if (valuePath[i].TYPE == 'CITY')
					{
						hasCity = true;
						break;
					}
				}	

				if (!!propertyIndex[input.getAltLocation()])
					propertyIndex[input.getAltLocation()].setDisabled(hasCity);
			});
		}

		// collect garbage

		data = list = item = props = groupedProperties = altLocations = property = null;

		// public interface

		this.getIterator      = function () {return iterator(properties);};
		this.getGroupIterator = function () {return iterator(groups);};

		this.getById = function (propertyId) {return propertyIndex[propertyId];};

		this.getUserEmail           = function () {return propertyIndex.IS_EMAIL;};
		this.getPayerName           = function () {return propertyIndex.IS_PAYER;};
		this.getDeliveryLocation    = function () {return propertyIndex.IS_LOCATION;};
		this.getTaxLocation         = function () {return propertyIndex.IS_LOCATION4TAX;};
		this.getProfileName         = function () {return propertyIndex.IS_PROFILE_NAME;};
		this.getDeliveryLocationZip = function () {return propertyIndex.IS_ZIP;};
		this.getPhone               = function () {return propertyIndex.IS_PHONE;};
		this.getAddress             = function () {return propertyIndex.IS_ADDRESS;};
	};

})();
