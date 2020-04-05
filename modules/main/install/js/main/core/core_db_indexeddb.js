;
(function (window)
{
	if (window.BX.indexedDB) return;

	var BX = window.BX;

	/**
	 * IndexedDB driver
	 * 
	 * === Param 'Params' should be contains ===
	 * name - name of the database
	 * scheme - scheme of the database
	 * version - version of the database, default: 1
	 * success - callback function if method successful (you can use BX.promise for alternative)
	 * error - callback function if method errorful (you can use BX.promise for alternative)
	 * 
	 * 
	 * === Usage example ===
	 * BX.indexedDB({
	 *		name: 'BX.Messenger',
	 *		scheme: this.dbGetStores(),
	 *		version: 2
	 *	}).then(function (db) {
	 *		console.log('Open DB', db);
	 *	}).catch(function(error){
	 *		console.log(error);
	 *	});
	 * 
	 *
	 * === Error example ===
	 * JS Object = {
	 * 		errorCode: 0, 
	 * 		errorName: "VersionError", 
	 * 		errorMessage: "The requested version (2) is less than the existing version (3)."
	 * }
	 * 
	 * @param params
	 * @return BX.promise
	 */
	
	BX.indexedDB = function (params)
	{
		var indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
		window.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction || window.msIDBTransaction;
		window.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange;

		params.version = parseInt(params.version);
		params.version = !params.version? 1: params.version;
		
		var result = new BX.Promise();
		var error = {};
		
		if(
			typeof indexedDB == 'undefined'
			|| typeof window.IDBTransaction == 'undefined'
			|| typeof window.IDBKeyRange == 'undefined'
		)
		{
			error = {
				errorCode: 'bxNotSupported', 
				errorName: 'bxNotSupported', 
				errorMessage: 'IndexedDB is not supported in current browser.'
			};
			
			if (typeof params.error == 'function')
			{
				params.error(error);
			}
			
			result.reject(error);
			return result;
		}
		
		if (
			typeof(params) != 'object'
			|| !params.name
			|| !params.scheme
		)
		{
			error = {
				errorCode: 'bxParamsError', 
				errorName: 'bxParamsError', 
				errorMessage: 'Required parameters not specified.'
			};
			
			if (typeof params.error == 'function')
			{
				params.error(error);
			}
			
			result.reject(error);
			return result;
		}

		
		var request = indexedDB.open(params.name, params.version);
		if (!request)
		{
			error = {
				errorCode: 'bxOpenError', 
				errorName: 'bxOpenError', 
				errorMessage: 'An error occurred while opening the database.'
			};
			
			if (typeof params.error == 'function')
			{
				params.error(error);
			}
			
			result.reject(error);
			return result;
		}
		
		request.onsuccess = function(event)
		{
			if (typeof params.success == 'function')
			{
				params.success(event.target.result);
			}
			result.fulfill(event.target.result);
		};
		
		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message
			};
			if (typeof params.error == 'function')
			{
				params.error(error);
			}
			result.reject(error);
		};

		request.onupgradeneeded = function (event)
		{
			/* syncronize database structure */

			if (typeof params.scheme != 'undefined')
			{
				var hDBHandle = event.target.result;
				var ob = null;
				var oStore = null;
				var schemeLength = params.scheme.length;
				var i, j = null;

				for (i = 0; i < schemeLength; i++)
				{
					ob = params.scheme[i];

					if (
						typeof ob == 'object'
						&& !hDBHandle.objectStoreNames.contains(ob.name)
					)
					{
						oStore = hDBHandle.createObjectStore(
							ob.name,
							{
								keyPath : (typeof ob.keyPath != 'undefined' && ob.keyPath ? ob.keyPath : undefined),
								autoIncrement : (typeof ob.autoIncrement != 'undefined' && !!ob.autoIncrement)
							}
						);

						if (typeof ob.indexes != 'undefined')
						{
							for (j = 0; j < ob.indexes.length; j++)
							{
								oStore.createIndex(ob.indexes[j].name, ob.indexes[j].keyPath, { unique: !!ob.indexes[j].unique });
							}
						}
					}
				}

				var bFound = null;
				var length = hDBHandle.objectStoreNames.length;

				for (i = 0; i < length; i++)
				{
					if (!hDBHandle.objectStoreNames[i])
						continue;
					
					bFound = false;

					for (j = 0; j < schemeLength; j++)
					{
						ob = params.scheme[j];
						if (ob.name == hDBHandle.objectStoreNames[i])
						{
							bFound = true;
							break;
						}
					}

					if (!bFound)
					{
						hDBHandle.deleteObjectStore(hDBHandle.objectStoreNames[i]);
					}
				}
			}
		};
		
		return result;
	};

	BX.indexedDB.checkDatabaseObject = function (dataBase)
	{
		var result = {error: null, result: false};
		
		if (
			!dataBase
			|| typeof dataBase != 'object'
			|| !(dataBase instanceof IDBDatabase)
		)
		{
			result.error = {
				errorCode: 'bxDataBaseInvalidFormat', 
				errorName: 'bxDataBaseInvalidFormat', 
				errorMessage: 'The given "dataBase" object is invalid format for IndexedDB.'
			};
		}
		else 
		{
			result.result = true;
		}
		
		return result;
	};

	BX.indexedDB.getObjectStore = function (dataBase, storeName, openMode)
	{
		openMode = openMode || 'readonly';
		
		var result = {error: null, transaction: null};
		
		var checkResult = BX.indexedDB.checkDatabaseObject(dataBase);
		if (checkResult.error)
		{
			return result;
		}

		try
		{
			var transaction = dataBase.transaction(storeName, openMode);
			transaction.onsuccess = function(event){};
			transaction.onerror = function(event){
				console.log('IndexedDB Transaction error', event);
			};
			
			result.transaction = transaction.objectStore(storeName);
			return result;
		}
		catch(err)
		{
			result.error = {
				errorCode: err.code, 
				errorName: err.name, 
				errorMessage: err.message
			};
			
			return result;
		}
	};
	
	BX.indexedDB.addValue = function (dataBase, storeName, value, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName, 'readwrite');
		if (store.error)
		{
			store.error.params = {dataBase: dataBase, storeName: storeName, value: value};
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}

		var request = null;
		try
		{
			request = store.transaction.add(value);
		}
		catch (e)
		{
			var error = {
				errorCode: e.code? e.code: e.name, 
				errorName: e.name, 
				errorMessage: e.message,
				params: {dataBase: dataBase, storeName: storeName, value: value}
			};
			result.reject(error);
			return result;
		}

		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message,
				params: {dataBase: dataBase, storeName: storeName, value: value}
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function(event)
		{
			if (typeof callback.success == 'function')
			{
				callback.success(event.target.result);
			}
			
			result.fulfill(event.target.result);
		};
		
		return result;
	};
	
	BX.indexedDB.updateValue = function (dataBase, storeName, value, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName, 'readwrite');
		if (store.error)
		{
			store.error.params = {dataBase: dataBase, storeName: storeName, value: value};
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}

		var request = null;
		try
		{
			request = store.transaction.put(value);
		}
		catch (e)
		{
			var error = {
				errorCode: e.code? e.code: e.name, 
				errorName: e.name, 
				errorMessage: e.message,
				params: {dataBase: dataBase, storeName: storeName, value: value}
			};
			result.reject(error);
			return result;
		}

		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message,
				params: {dataBase: dataBase, storeName: storeName, value: value}
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function(event)
		{
			if (typeof callback.success == 'function')
			{
				callback.success(event.target.result);
			}
			
			result.fulfill(event.target.result);
		};
		
		return result;
	};

	BX.indexedDB.deleteValue = function (dataBase, storeName, primaryId, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName, 'readwrite');
		if (store.error)
		{
			store.error.params = {dataBase: dataBase, storeName: storeName, primaryId: primaryId};
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}

		var request = null;
		try
		{
			request = store.transaction.delete(primaryId);
		}
		catch (e)
		{
			var error = {
				errorCode: e.code? e.code: e.name, 
				errorName: e.name, 
				errorMessage: e.message,
				params: {dataBase: dataBase, storeName: storeName, primaryId: primaryId}
			};
			result.reject(error);
			return result;
		}

		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message,
				params: {dataBase: dataBase, storeName: storeName, primaryId: primaryId}
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function()
		{
			if (typeof callback.success == 'function')
			{
				callback.success(true);
			}
			
			result.fulfill(true);
		};
		
		return result;
   };

	BX.indexedDB.deleteValueByIndex = function (dataBase, storeName, indexName, indexValue, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		BX.indexedDB.getValueByIndex(dataBase, storeName, indexName, indexValue).then(function(element){
			BX.indexedDB.deleteValue(dataBase, storeName, element.id).then(function(){
				if (typeof callback.success == 'function')
				{
					callback.success(true);
				}
				result.fulfill(true);
			}).catch(function(error){
				error.params = {dataBase: dataBase, storeName: storeName, indexName: indexName, indexValue: indexValue};
				if (typeof callback.error == 'function')
				{
					callback.error(error);
				}
				result.reject(error);
			});
		}).catch(function(error){
			error.params = {dataBase: dataBase, storeName: storeName, indexName: indexName, indexValue: indexValue};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		});
		
		return result;
	};

	BX.indexedDB.getValue = function (dataBase, storeName, primaryId, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName, 'readwrite');
		if (store.error)
		{
			store.error.params = {dataBase: dataBase, storeName: storeName, primaryId: primaryId};
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}

		var request = null;
		try
		{
			request = store.transaction.get(primaryId);
		}
		catch (e)
		{
			var error = {
				errorCode: e.code? e.code: e.name, 
				errorName: e.name, 
				errorMessage: e.message,
				params: {dataBase: dataBase, storeName: storeName, primaryId: primaryId}
			};
			result.reject(error);
			return result;
		}

		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message,
				params: {dataBase: dataBase, storeName: storeName, primaryId: primaryId}
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function(event)
		{
			if (!event.target.result)
			{
				var error = {
					errorCode: 'bxElementNotFound', 
					errorName: 'bxElementNotFound', 
					errorMessage: "Element with id '"+primaryId+"' not found",
					params: {dataBase: dataBase, storeName: storeName, primaryId: primaryId}
				};
				if (typeof callback.error == 'function')
				{
					callback.error(error);
				}
				result.reject(error);
				return result;
			}
			
			if (typeof callback.success == 'function')
			{
				callback.success(event.target.result);
			}
			
			result.fulfill(event.target.result);
		};
		
		return result;
	};

	BX.indexedDB.getValueByIndex = function (dataBase, storeName, indexName, indexValue, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName, 'readwrite');
		if (store.error)
		{
			store.error.params = {dataBase: dataBase, storeName: storeName, indexName: indexName, indexValue: indexValue};
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}

		var request = null;
		try
		{
			request = store.transaction.index(indexName).get(indexValue);
		}
		catch (e)
		{
			var error = {
				errorCode: e.code? e.code: e.name, 
				errorName: e.name, 
				errorMessage: e.message,
				params: {dataBase: dataBase, storeName: storeName, indexName: indexName, indexValue: indexValue}
			};
			result.reject(error);
			return result;
		}

		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message,
				params: {dataBase: dataBase, storeName: storeName, indexName: indexName, indexValue: indexValue}
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function(event)
		{
			if (!event.target.result)
			{
				var error = {
					errorCode: 'bxElementNotFound', 
					errorName: 'bxElementNotFound', 
					errorMessage: "Element with indexName '"+indexName+"' and indexValue '"+indexValue+"' not found",
					params: {dataBase: dataBase, storeName: storeName, indexName: indexName, indexValue: indexValue}
				};
				if (typeof callback.error == 'function')
				{
					callback.error(error);
				}
				result.reject(error);
				return result;
			}
			
			if (typeof callback.success == 'function')
			{
				callback.success(event.target.result);
			}
			
			result.fulfill(event.target.result);
		};
		
		return result;
	};
	
	BX.indexedDB.openCursor = function (dataBase, storeName, keyRange, callback)
	{
		keyRange = keyRange || {};
		callback = callback || {};
		
		var result = new BX.Promise();
		var keyRangeForCursor = null;
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName);
		if (store.error)
		{
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}
		
		if (typeof keyRange.lower != 'undefined')
		{
			if (typeof keyRange.upper != 'undefined')
			{
				keyRangeForCursor = window.IDBKeyRange.bound(keyRange.lower, keyRange.upper, !!keyRange.lowerOpen, !!keyRange.upperOpen);
			}
			else
			{
				keyRangeForCursor = window.IDBKeyRange.lowerBound(keyRange.lower, !!keyRange.lowerOpen);
			}
		}
		else if (typeof keyRange.upper != 'undefined')
		{
			keyRangeForCursor = window.IDBKeyRange.upperBound(keyRange.upper, !!keyRange.upperOpen);
		}
		
		/*
		* Filter (iterator) should be return follow text: 
		* continue - store item and go to next
		* stop - store item and return collection
		* skip - skip item and go to next
		* break - return collection without store last item
		*/
		if (typeof callback.filter != 'function')
		{
			callback.filter = function(key, value){
				return 'continue';	
			};
		}

		var request = store.transaction.openCursor(keyRangeForCursor);
		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
			return result;
		};
		
		var rows = [];
		request.onsuccess = function(event)
		{
			var cursor = event.target.result;
			if (cursor)
			{
				var filterResult = 'continue';
				if (typeof callback.filter == 'function')
				{
					filterResult = callback.filter(cursor.key, cursor.value);
				}
				
				if (filterResult == 'break')
				{
					if (typeof callback.success == 'function')
					{
						callback.callback(rows);
					}
					
					result.fulfill(rows);
					return result;
				}
				
				if (filterResult != 'skip')
				{
					rows.push({key: cursor.key, value: cursor.value});
				}
				
				if (filterResult == 'stop')
				{
					if (typeof callback.success == 'function')
					{
						callback.callback(rows);
					}
					
					result.fulfill(rows);
					return result;
				}
				else 
				{
					cursor['continue']();
				}
			}
			else 
			{
				if (typeof callback.success == 'function')
				{
					callback.callback(rows);
				}
				
				result.fulfill(rows);
				return result;
			}
		};
		
		return result;
	};
	
	BX.indexedDB.count = function (dataBase, storeName, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
	
		var store = BX.indexedDB.getObjectStore(dataBase, storeName);
		if (store.error)
		{
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}
		
		var request = store.transaction.count();
		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function(event)
		{
			if (typeof callback.success == 'function')
			{
				callback.success(event.target.result);
			}
			
			result.fulfill(event.target.result);
		};
		
		return result;
	};
	
	BX.indexedDB.clearObjectStore = function (dataBase, storeName, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var store = BX.indexedDB.getObjectStore(dataBase, storeName, 'readwrite');
		if (store.error)
		{
			if (typeof callback.error == 'function')
			{
				callback.error(store.error);
			}
			result.reject(store.error);
			return result;
		}
		
		try
		{
			var request = store.transaction.clear();
		}
		catch(err)
		{
			var error = {
				errorCode: err.code, 
				errorName: err.name, 
				errorMessage: err.message
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
			return result;
		}
		
		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function()
		{
			if (typeof callback.success == 'function')
			{
				callback.success(true);
			}
			
			result.fulfill(true);
		};
		
		return result;
	};
	
	BX.indexedDB.deleteDatabase = function (dataBase, openedDataBase, callback)
	{
		callback = callback || {};
		var result = new BX.Promise();
		
		var indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
		
		if (openedDataBase !== null)
		{
			var checkResult = BX.indexedDB.checkDatabaseObject(openedDataBase);
			if (checkResult.result)
			{
				openedDataBase.close();
				openedDataBase = null;
			}
			else 
			{
				var error = {
					errorCode: 'bxDataBaseInvalidFormat', 
					errorName: 'bxDataBaseInvalidFormat', 
					errorMessage: 'The given "openedDataBase" object is invalid format for IndexedDB. You need specify link to opened DB for correct delete.'
				};
				if (typeof callback.error == 'function')
				{
					callback.error(error);
				}
				result.reject(error);
				return result;
			}
		}
		
		var request = indexedDB.deleteDatabase(dataBase);
		
		request.onerror = function(event)
		{
			var error = {
				errorCode: event.target.error.code, 
				errorName: event.target.error.name, 
				errorMessage: event.target.error.message
			};
			if (typeof callback.error == 'function')
			{
				callback.error(error);
			}
			result.reject(error);
		};

		request.onsuccess = function()
		{
			if (typeof callback.success == 'function')
			{
				callback.success(true);
			}
			
			result.fulfill(true);
		};
		
		return result;
	}

})(window);