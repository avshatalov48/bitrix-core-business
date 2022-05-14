(function () {

	let modules = {};
	let requireStack = [];
	let inProgressModules = {};
	let SEPARATOR = '.';

	function build(module)
	{
		let factory = module.factory;
		let localRequire = function (id) {
			let resultantId = id;
			if (id.charAt(0) === '.')
			{
				resultantId = module.id.slice(0, module.id.lastIndexOf(SEPARATOR)) + SEPARATOR + id.slice(2);
			}
			return require(resultantId);
		};
		module.exports = {};
		delete module.factory;
		factory(localRequire, module.exports, module);
		return module.exports;
	}

	let require = function (id) {
		if (!modules[id])
		{
			throw new Error('extension ' + id + ' not found');
		}
		else if (id in inProgressModules)
		{
			let cycle = requireStack.slice(inProgressModules[id]).join(' -> ') + ' -> ' + id;
			throw new Error('Cycle in require graph: ' + cycle);
		}
		if (modules[id].factory)
		{
			try
			{
				inProgressModules[id] = requireStack.length;
				requireStack.push(id);
				return build(modules[id]);
			} finally
			{
				delete inProgressModules[id];
				requireStack.pop();
			}
		}
		return modules[id].exports;
	};

	/**
	 *
	 * @param {String} id
	 * @param {DefineFactory} factory
	 */
	let define = function (id, factory) {
		if (Object.prototype.hasOwnProperty.call(modules, id))
		{
			throw new Error('module ' + id + ' already defined');
		}

		modules[id] = {
			id: id,
			factory: factory
		};
	};

	define.remove = function (id) {
		delete modules[id];
	};

	define.moduleMap = modules;

	let data = {}
	this.jnExtensionData = {
		set:function (name, value) {
			data[name] = value
		},
		get:function(name) {
			if (typeof data[name] !== "undefined") {
				return data[name];
			}
			return {};
		}
	}

	let jnexport = this.jnexport = (...exportData) => {
		exportData.forEach(exportItem=>{
			if(exportItem instanceof Array)
			{
				if(exportItem.length === 2)
				{
					this[exportItem[1]] = exportItem[0]
				}
			}
			else
			{
				this[exportItem.name] = exportItem
			}
		})
	};


	this.jn = {
		define, require, export: jnexport
	}

})();
