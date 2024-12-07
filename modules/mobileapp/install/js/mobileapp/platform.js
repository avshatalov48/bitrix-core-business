(function() {
	const modules = {};
	const requireStack = [];
	const inProgressModules = {};
	const SEPARATOR = '.';


	class ModulesList {
		#modules = {}

		addModule(moduleName, module) {
			this.#modules[moduleName] = module
		}

		getModuleExports(moduleName) {
			return this.#modules[moduleName]?.exports
		}
	}
	if (typeof globalThis.nativeModules === 'undefined') {
		Object.defineProperty(globalThis, 'nativeModules', {
			enumerable: false,
			writable: false,
			configurable: true,
			value: new ModulesList(),
		})
	}


	function build(module)
	{
		const factory = module.factory;
		const localRequire = function(id) {
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

	let require = function(id) {
		if (id.startsWith('native/'))
		{
			if (typeof nativeRequire !== 'function') {
				return {}
			}

			const moduleName = id.replace('native/', '');
			let exports = globalThis.nativeModules.getModuleExports(moduleName)
			if (typeof exports === 'undefined' && exports !== null) {
				exports = nativeRequire(moduleName)
				globalThis.nativeModules.addModule(moduleName, { exports })
			}

			return exports
		}

		if (!modules[id])
		{
			throw new Error(`extension ${id} not found`);
		}
		else if (id in inProgressModules)
		{
			const cycle = `${requireStack.slice(inProgressModules[id]).join(' -> ')} -> ${id}`;
			throw new Error(`Cycle in require graph: ${cycle}`);
		}

		if (modules[id].factory)
		{
			try
			{
				inProgressModules[id] = requireStack.length;
				requireStack.push(id);

				return build(modules[id]);
			}
			finally
			{
				delete inProgressModules[id];
				requireStack.pop();
			}
		}

		return modules[id].exports;
	};

	const moduleUsage = () => {
		const defined = Object.keys(modules);
		const definedCount = defined.length;
		const used = defined.filter((id) => typeof modules[id].factory === 'undefined');
		const usedCount = used.length;
		const emptyUsage = defined.filter((id) => !used.includes(id));
		const findUsage = (moduleName) => Object.keys(modules).filter((moduleKey) => moduleKey.startsWith(moduleName));

		return {
			defined,
			definedCount,
			used,
			usedCount,
			emptyUsage,
			findUsage,
		};
	};

	/**
	 *
	 * @param {String} id
	 * @param {DefineFactory} factory
	 */
	const define = function(id, factory) {
		modules[id] = {
			id,
			factory,
		};
	};

	define.remove = function(id) {
		delete modules[id];
	};

	define.moduleMap = modules;

	const data = {};
	this.jnExtensionData = {
		set(name, value)
		{
			data[name] = value;
		},
		get(name)
		{
			if (typeof data[name] !== 'undefined')
			{
				return data[name];
			}

			return {};
		},
	};

	const jnexport = this.jnexport = (...exportData) => {
		exportData.forEach((exportItem) => {
			if (Array.isArray(exportItem))
			{
				if (exportItem.length === 2)
				{
					this[exportItem[1]] = exportItem[0];
				}
			}
			else
			{
				this[exportItem.name] = exportItem;
			}
		});
	};

	const loadedExtensions = {};
	let loadingExtension = null;
	const delayedCallback = {};
	const jnImport = (ext, force = false) => {
		if (loadingExtension == null)
		{
			loadingExtension = {};
			this.loadedExtensions.forEach((ext) => {
				loadedExtensions[ext] = true;
			});
		}

		return new Promise((resolve, reject) => {
			if (Application.getApiVersion() < 45)
			{
				reject({ error: 'API_VERSION is lower then 45' });

				return;
			}

			if (loadedExtensions[ext] && force === false)
			{
				resolve();

				return;
			}

			if (!delayedCallback[ext])
			{
				delayedCallback[ext] = [];
			}

			delayedCallback[ext].push({
				fail: (e) => {
					reject(e);
				},
				success: () => {
					resolve();
				},
			});

			if (loadingExtension[ext])
			{
				return;
			}
			loadingExtension[ext] = true;
			const params = {
				headers: {
					'Content-Type': 'application/json',
				},
				data: Object.keys(loadedExtensions),
			};
			dynamicLoad(`/mobileapp/jn/${ext}/?type=extension`, params)
				.then((result) => {
					delete loadingExtension[ext];
					loadedExtensions[ext] = true;
					if (delayedCallback[ext])
					{
						delayedCallback[ext].forEach((callback) => callback.success.call());
					}
				}).catch((e) => {
				delete loadingExtension[ext];
				if (delayedCallback[ext])
				{
					delayedCallback[ext].forEach((callback) => callback.fail.call(null, e));
				}
			});
		});
	};

	const getExtensionCodeText = (moduleId) => {
		return new Promise((resolve, reject) => {
			if (typeof moduleId !== 'string')
			{
				reject(new Error(`Expected argument 'ext' with value ${moduleId} to be a string`));
			}

			BX.ajax({
				url: `/mobileapp/jn/${moduleId}/?type=extension&onlyTextOfExt=true`,
				method: 'POST',
				dataType: 'html',
				onsuccess: resolve,
				onfailure: reject,
			});
		});
	};

	this.jn = {
		moduleUsage, define, require, export: jnexport, import: jnImport, getExtensionCodeText,
	};
})();
