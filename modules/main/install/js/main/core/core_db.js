/**
 * Class for Web SQL Database
 * @param params
 * @constructor
 */

;
(function (window)
{
	if (window.BX.dataBase) return;

	var BX = window.BX;

	/**
	 * Parameters description:
	 * version - version of the database
	 * name - name of the database
	 * displayName - display name of the database
	 * capacity - size of the database in bytes.
	 * @param params
	 */
	BX.dataBase = function (params)
	{
		this.tableList = [];
		this.jsonFields = {};
		
		if(typeof window.SQLitePlugin != 'undefined' && typeof window.SQLitePlugin.openDatabase == 'function')
		{
			this.dbObject = window.SQLitePlugin.openDatabase(params.name, params.version, params.displayName, params.capacity);
			this.dbBandle = 'SQLitePlugin';
		}
		else if(typeof window.openDatabase != 'undefined')
		{
			this.dbBandle = 'openDatabase';
			this.dbObject = window.openDatabase(params.name, params.version, params.displayName, params.capacity);
		}
		else 
		{
			this.dbBandle = 'undefined';
			this.dbObject = null;
		}
	};

	BX.dataBase.create = function(params)
	{
		if (
			typeof window.openDatabase != 'undefined'
			|| typeof window.SQLitePlugin != 'undefined' && typeof window.SQLitePlugin.openDatabase == 'function'
		)
		{
			return new BX.dataBase(params);
		}
		else 
		{
			return null;
		}
	};

	BX.dataBase.prototype.setJsonFields = function (tableName, fields)
	{
		if (typeof fields == 'string')
		{
			if (fields == '')
			{
				fields = [];
			}
			else 
			{
				fields = [fields];
			}
		}
		
		if (tableName && BX.type.isArray(fields))
		{
			tableName = tableName.toString().toUpperCase();
			
			this.jsonFields[tableName] = [];
			if (fields.length > 0)
			{
				for (var i = 0; i < fields.length; i++)
				{
					this.jsonFields[tableName].push(
						fields[i].toString().toUpperCase()
					);
				}
			}
			else 
			{
				delete this.jsonFields[tableName];
			}
		}
		
		return true;
	}
	
	BX.dataBase.prototype.isTableExists = function (tableName, callback)
	{
		tableName = tableName.toUpperCase();
		var promise = new BX.Promise();
		if (typeof callback != 'function')
		{
			callback = function(){};
		}
		
		var tableListCallback = function (tableList)
		{
			if (tableList.indexOf(tableName) > -1)
			{
				callback(true, tableName);
				promise.fulfill(tableName);
			}
			else 
			{
				callback(false, tableName);
				promise.reject(tableName);
			}
		};

		if (this.tableList.length <= 0)
		{
			this.getTableList().then(tableListCallback);
		}
		else
		{
			tableListCallback(this.tableList);
		}

		return promise;
	};

	/**
	 * Takes the list of existing tables from the database
	 * @param callback The callback handler will be invoked with boolean parameter as a first argument
	 * @example
	 */
	BX.dataBase.prototype.getTableList = function (callback)
	{
		var promise = new BX.Promise();
		if (typeof callback != 'function')
		{
			callback = function(){};
		}
		
		var callbackFunc = callback;
		this.query({
			query: "SELECT tbl_name from sqlite_master WHERE type = 'table'",
			values: []
		}).then(function (success) {
			this.tableList = [];
			if (success.result.count > 0)
			{
				for (var i = 0; i < success.result.items.length; i++)
				{
					this.tableList[this.tableList.length] = success.result.items[i].tbl_name.toString().toUpperCase();
				}
			}
			callbackFunc(this.tableList);
			promise.fulfill(this.tableList);
		}.bind(this)).catch(function (error){
			promise.reject(error);
		});
		
		return promise;
	};

	/**
	 * Creates the table in the database
	 * @param params
	 */
	BX.dataBase.prototype.createTable = function (params)
	{
		var promise = new BX.Promise();
		
		params = params || {};
		if (typeof params.success != 'function')
		{
			params.success = function(result, transaction){};
		}
		if (typeof params.fail != 'function')
		{
			params.fail = function(result, transaction, query){};
		}
		
		params.action = 'create';
		
		this.query(
			this.getQuery(params)
		).then(function (success) {
			this.getTableList();
			success.result.tableName = params.tableName;
			params.success(success.result, success.transaction);
			promise.fulfill(success);
		}.bind(this)).catch(function (error){
			params.fail(error.result, error.transaction, error.query, params);
			error.queryParams = params;
			promise.reject(error);
		});
		
		return promise;
	};

	/**
	 * Drops the table from the database
	 * @param params
	 */
	BX.dataBase.prototype.dropTable = function (params)
	{
		var promise = new BX.Promise();
		
		params = params || {};
		if (typeof params.success != 'function')
		{
			params.success = function(result, transaction){};
		}
		if (typeof params.fail != 'function')
		{
			params.fail = function(result, transaction, query){};
		}
		
		params.action = "drop";
		
		this.query(
			this.getQuery(params)
		).then(function (success) {
			this.getTableList();
			success.result.tableName = params.tableName;
			params.success(success.result, success.transaction);
			promise.fulfill(success);
		}.bind(this)).catch(function (error){
			params.fail(error.result, error.transaction, error.query, params);
			error.queryParams = params;
			promise.reject(error);
		});
		
		return promise;
	};

	/**
	 * Drops the table from the database
	 * @param params
	 */
	BX.dataBase.prototype.addRow = function (params)
	{
		var promise = new BX.Promise();
		
		params = params || {};
		if (typeof params.success != 'function')
		{
			params.success = function(result, transaction){};
		}
		if (typeof params.fail != 'function')
		{
			params.fail = function(result, transaction, query){};
		}
		
		params.action = "insert";
		
		this.query(
			this.getQuery(params)
		).then(function (success) {
			params.success(success.result, success.transaction);
			success.result.tableName = params.tableName;
			promise.fulfill(success);
		}).catch(function (error){
			params.fail(error.result, error.transaction, error.query, params);
			error.queryParams = params;
			promise.reject(error);
		});
		
		return promise;
	};

	/**
	 * Gets the data from the table
	 * @param params
	 */
	BX.dataBase.prototype.getRows = function (params)
	{
		var promise = new BX.Promise();
		
		params = params || {};
		if (typeof params.success != 'function')
		{
			params.success = function(result, transaction){};
		}
		if (typeof params.fail != 'function')
		{
			params.fail = function(result, transaction, query){};
		}
		
		params.action = "select";
		
		this.query(
			this.getQuery(params)
		).then(function (success) {
			var tableName = params.tableName.toString().toUpperCase();
			if (
				this.jsonFields[tableName] 
				&& this.jsonFields[tableName].length 
				&& success.result.items.length
			)
			{
				for (var i = 0; i < success.result.items.length; i++)
				{
					for (var j = 0; j < this.jsonFields[tableName].length; j++)
					{
						if (success.result.items[i][this.jsonFields[tableName][j]])
						{
							success.result.items[i][this.jsonFields[tableName][j]] = JSON.parse(success.result.items[i][this.jsonFields[tableName][j]]);
						}
					}
				}
			}
			params.success(success.result, success.transaction);
			success.result.tableName = params.tableName;
			promise.fulfill(success);
		}.bind(this)).catch(function (error){
			params.fail(error.result, error.transaction, error.query, params);
			error.queryParams = params;
			promise.reject(error);
		});
		
		return promise;
	};

	/**
	 * Updates the table
	 * @param params
	 */
	BX.dataBase.prototype.updateRows = function (params)
	{
		var promise = new BX.Promise();
		
		params = params || {};
		if (typeof params.success != 'function')
		{
			params.success = function(result, transaction){};
		}
		if (typeof params.fail != 'function')
		{
			params.fail = function(result, transaction, query){};
		}
		
		params.action = "update";
		
		this.query(
			this.getQuery(params)
		).then(function (success) {
			params.success(success.result, success.transaction);
			success.result.tableName = params.tableName;
			promise.fulfill(success);
		}).catch(function (error){
			params.fail(error.result, error.transaction, error.query, params);
			error.queryParams = params;
			promise.reject(error);
		});
		
		return promise;
	};

	/**
	 * Deletes rows from the table
	 * @param params
	 */
	BX.dataBase.prototype.deleteRows = function (params)
	{
		params.action = "delete";
		var str = this.getQuery(params);
		this.query(str, params.success, params.fail);
	};

	/**
	 * Builds the query string and the set of values.
	 * @param params
	 * @returns {{query: string, values: Array}}
	 */
	BX.dataBase.prototype.getQuery = function (params)
	{
		var values = [];
		var where = params.filter;
		var select = params.fields;
		var insert = params.insertFields;
		var set = params.updateFields;
		var tableName = params.tableName;
		var strQuery = "";

		switch (params.action)
		{
			case "create":
			{
				var fieldsString = "";
				if (typeof(select) == "object")
				{
					var field = "";
					var type = "";
					for (var j = 0; j < select.length; j++)
					{
						field = "";
						type = "";
						if (typeof(select[j]) == "object")
						{
							if (select[j].name)
							{
								field = select[j].name;
							}
							if (field && select[j].type)
							{
								if (
									select[j].type.toLowerCase() == 'integer'
									|| select[j].type.toLowerCase() == 'real'
									|| select[j].type.toLowerCase() == 'text'
								)
								{
									field += " "+select[j].type;
								}
							}
							if (field && select[j].unique && select[j].unique == true)
							{
								field += " unique";
							}
						}
						else if (typeof(select[j]) == "string" && select[j].length > 0)
						{
							field = select[j];
						}

						if (field.length > 0)
						{
							if (fieldsString.length > 0)
								fieldsString += "," + field.toUpperCase();
							else
								fieldsString = field.toUpperCase();
						}
					}
				}

				strQuery = "CREATE TABLE IF NOT EXISTS " + tableName.toUpperCase() + " (" + fieldsString + ") ";
				
				break;
			}

			case "drop":
			{
				strQuery = "DROP TABLE IF EXISTS " + tableName.toUpperCase();
				break;
			}
			case "select":
			{
				strQuery = "SELECT " + this.getValueArrayString(select, "*") + " FROM " + tableName.toUpperCase() + " " + this.getFilter(where);
				values = this.getValues([where]);
				break;
			}
			case "insert":
			{
				var groups = 0;
				var groupSize = 0;
				var keyString = "";
				if (BX.type.isArray(insert))
				{
					values = this.getValues(insert, 'insert');
					for (var i in insert[0])
					{
						groupSize++
					}
					groups = insert.length;
					keyString = this.getKeyString(insert[0])
				}
				else 
				{
					values = this.getValues([insert], 'insert');
					groups = 1;
					groupSize = values.length;
					keyString = this.getKeyString(insert)
				}
				
				strQuery = "INSERT INTO " + tableName.toUpperCase() + " (" + keyString + ") VALUES %values%";
				
				var placeholder = [];
				var placeholderGroup = [];
				for (var i = 0; i < groups; i++)
				{
					placeholder = [];
					for (var j = 0; j < groupSize; j++)
					{
						placeholder.push('?');
					}
					placeholderGroup.push(placeholder.join(','));
				}

				strQuery = strQuery.replace("%values%", "("+placeholderGroup.join("), (")+")");
				
				break;
			}
				
			case "delete":
			{
				strQuery = "DELETE FROM " + tableName.toUpperCase() + " " + this.getFilter(where);
				values = this.getValues([where]);
				break;
			}

			case "update":
			{
				strQuery = "UPDATE " + tableName.toUpperCase() + " " + this.getFieldPair(set, "SET ") + " " + this.getFilter(where);
				values = this.getValues([set], 'update').concat(
					this.getValues([where])
				);
				break;
			}	
		}
		return {
			query: strQuery,
			values: values
		}
	};


	/**
	 * Gets pairs for query string
	 * @param {object} fields The object with set of key-value pairs
	 * @param {string} operator The keyword that will be join on the beginning of the string
	 * @returns {string}
	 */
	BX.dataBase.prototype.getFieldPair = function (fields, operator)
	{
		var pairsRow = "";
		var keyWord = operator || "";

		if (typeof(fields) == "object")
		{
			var i = 0;
			for (var key in fields)
			{
				var pair = ((i > 0) ? ", " : "") + (key.toUpperCase() + "=" + "?");
				if (pairsRow.length == 0 && keyWord.length > 0)
					pairsRow = keyWord;
				pairsRow += pair;
				i++;
			}
		}

		return pairsRow;
	};

	BX.dataBase.prototype.getFilter = function (fields)
	{
		var pairsRow = "";
		var keyWord = "WHERE ";

		if (typeof(fields) == "object")
		{
			var i = 0;
			for (var key in fields)
			{
				var pair = "";
				var count = 1;
				if (typeof(fields[key]) == "object")
				{
					count = fields[key].length;
				}
				
				for (var j = 0; j < count; j++)
				{
					pair = ((j > 0) ? pair + " OR " : "(") + (key.toUpperCase() + "=" + "?");
					if ((j + 1) == count)
						pair += ")"
				};

				pairsRow += pair;
				i++;
			}
		}
		else if (typeof fields == "string")
		{
			pairsRow = fields;
		}
		return pairsRow == "" ? "" : "WHERE " + pairsRow;
	};

	/**
	 * Gets the string with keys of fields that have splitted by commas
	 * @param fields
	 * @param defaultResult
	 * @returns {string}
	 */
	BX.dataBase.prototype.getKeyString = function (fields, defaultResult)
	{
		var result = "";
		if (!defaultResult)
			defaultResult = "";
		
		if (BX.type.isArray(fields))
		{
			for (var i = 0; i < fields.length; i++)
			{
				for (var key in fields[i])
				{
					if (result.length > 0)
						result += "," + key.toUpperCase();
					else
						result = key.toUpperCase();
				}
			}
		}
		else if (typeof(fields) == "object")
		{
			for (var key in fields)
			{
				if (result.length > 0)
					result += "," + key.toUpperCase();
				else
					result = key.toUpperCase();
			}
		}

		if (result.length == 0)
			result = defaultResult;

		return result;
	};

	/**
	 * Gets the string with values of the array that have splitted by commas
	 * @param fields
	 * @param defaultResult
	 * @returns {string}
	 */
	BX.dataBase.prototype.getValueArrayString = function (fields, defaultResult)
	{
		var result = "";
		if (!defaultResult)
			defaultResult = "";
		if (typeof(fields) == "object")
		{
			for (var i = 0; i < fields.length; i++)
			{

				if (result.length > 0)
					result += "," + fields[i].toUpperCase();
				else
					result = fields[i].toUpperCase();
			}
		}


		if (result.length == 0)
			result = defaultResult;

		return result;
	};

	/**
	 * Gets the array of values
	 * @param values
	 * @returns {Array}
	 */
	BX.dataBase.prototype.getValues = function (values, type)
	{
		type = type || 'undefined';
		
		var resultValues = [];
		for (var j = 0; j < values.length; j++)
		{
			var valuesItem = values[j];

			if (BX.type.isArray(valuesItem))
			{
				for (var i = 0; i < valuesItem.length; i++)
				{
					if ((type == 'insert' || type == 'update') && typeof(valuesItem[i]) == "object")
					{
						resultValues.push(JSON.stringify(valuesItem[i]));
					}
					else if (typeof(valuesItem[i]) == "object")
					{
						for (var keyField in valuesItem[i])
						{
							if (typeof(valuesItem[i][keyField]) == "object")
							{
								resultValues.push(JSON.stringify(valuesItem[i][keyField]));
							}
							else
							{
								resultValues.push(valuesItem[i][keyField]);
							}
						}
					}
					else 
					{
						resultValues.push(valuesItem[i]);
					}
				}
			}
			else if (typeof(valuesItem) == "object")
			{
				for (var i in valuesItem)
				{
					if ((type == 'insert' || type == 'update') && typeof(valuesItem[i]) == "object")
					{
						resultValues.push(JSON.stringify(valuesItem[i]));
					}
					else if (typeof(valuesItem[i]) == "object")
					{
						for (var keyField in valuesItem[i])
						{
							if (typeof(valuesItem[i][keyField]) == "object")
							{
								resultValues.push(JSON.stringify(valuesItem[i][keyField]));
							}
							else
							{
								resultValues.push(valuesItem[i][keyField]);
							}
						}
					}
					else 
					{
						resultValues.push(valuesItem[i]);
					}
				}
			}
		}

		return resultValues;
	};

	/**
	 * Executes the query
	 * @param success The success callback
	 * @param fail The failture callback
	 * @returns {string}
	 * @param query
	 */
	BX.dataBase.prototype.query = function (query, success, fail)
	{
		var promise = new BX.Promise();
		if (typeof success != 'function')
		{
			success = function(result, transaction){};
		}
		if (typeof fail != 'function')
		{
			fail = function(result, transaction, query){};
		}
		
		if (!this.dbObject)
		{
			fail(null, null, null);
			promise.reject(null, null, null);
			return promise;
		}
		
		this.dbObject.transaction(
			function (tx)
			{
				tx.executeSql(
					query.query,
					query.values,
					function (tx, results)
					{
						var result = {
							originalResult: results
						};

						var len = results.rows.length;
						if (len >= 0)
						{
							result.count = len;
							result.items = [];

							for (var i = 0; i < len; i++)
							{
								var item = {};
								var dbItem = results.rows.item(i);
								for (var key in dbItem)
								{
									if (dbItem.hasOwnProperty(key))
									{
										item[key] = dbItem[key];
									}
								}
								result.items.push(item);
							}
						}

						success(result, tx);
						promise.fulfill({result: result, transaction: tx});
					},
					function (tx, res)
					{
						fail(res, tx, query);
						promise.reject({result: res, transaction: tx, query: query});
					}
				);
			}
		);
		return promise;
	};

	/**
	 * Gets the beautifying result from the query response
	 * @param results
	 * @returns {*}
	 */

	BX.dataBase.prototype.getResponseObject = function (results)
	{

		var len = results.rows.length;

		var result = [];
		for (var i = 0; i < len; i++)
		{
			result[result.length] = results.rows.item(i);
		}

		return result;
	};

})(window);
