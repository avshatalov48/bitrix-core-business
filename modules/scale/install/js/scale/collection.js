	/**
 * Class BX.Scale.Collection
 * Prototype for collections class
 */
;(function(window) {

	if (BX.Scale.Collection) return;

	/**
	 * Class BX.Scale.Collection{paramsList}
	 * @constructor
	 */
	BX.Scale.Collection = function (objClass, paramsList)
	{
		this.objectClass = objClass;
		this.objectsList = {};

		if(paramsList)
			for(var objectId in paramsList)
				this.addObject(objectId, paramsList[objectId]);
	};

	BX.Scale.Collection.prototype.addObject = function(objectId, objectParams)
	{
		this.objectsList[objectId] = new this.objectClass(objectId, objectParams);
		return this.objectsList[objectId];
	};

	BX.Scale.Collection.prototype.getObject = function(objectId)
	{
		var result = false;

		if(this.objectsList[objectId] !== undefined)
			result = this.objectsList[objectId];

		return result;
	};

	BX.Scale.Collection.prototype.deleteObject = function(objectId)
	{
		delete this.objectsList[objectId];
	};

	BX.Scale.Collection.prototype.getObjectsIds = function()
	{
		var result = [];

		for(var objectId in this.objectsList)
			result.push(objectId);

		return result;
	};

	BX.Scale.Collection.prototype.getObjectsList = function()
	{
		var result = {};

		for(var objectId in this.objectsList)
			result[objectId] = this.objectsList[objectId];

		return result;
	};

})(window);


