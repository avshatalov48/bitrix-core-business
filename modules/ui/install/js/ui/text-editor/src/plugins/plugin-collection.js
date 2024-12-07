import { Type } from 'main.core';

import { type TextEditor } from '../text-editor';
import type { PluginConstructor } from './base-plugin';
import BasePlugin from './base-plugin';

export default class PluginCollection
{
	#pluginConstructors: Map<string, PluginConstructor> = new Map();
	#plugins: Map<string, BasePlugin> = new Map();
	#availablePlugins: Map<string, PluginConstructor> = new Map();

	constructor(
		builtinPlugins: PluginConstructor[] = [],
		plugins: Array<string | PluginConstructor> = [],
		pluginsToRemove: Array<string | PluginConstructor> = [],
	)
	{
		for (const pluginConstructor of builtinPlugins)
		{
			if (pluginConstructor.getName())
			{
				this.#availablePlugins.set(pluginConstructor.getName(), pluginConstructor);
			}
		}

		for (const plugin: string | PluginConstructor of plugins)
		{
			if (Type.isFunction(plugin) && plugin.getName() && !this.#availablePlugins.has(plugin.getName()))
			{
				this.#availablePlugins.set(plugin.getName(), plugin);
			}
		}

		const pluginsToLoad = plugins.filter((plugin: string | PluginConstructor) => {
			if (pluginsToRemove.includes(plugin))
			{
				return false;
			}

			if (Type.isFunction(plugin) && pluginsToRemove.includes(plugin.getName()))
			{
				return false;
			}

			return !pluginsToRemove.includes(this.#availablePlugins.get(plugin));
		});

		pluginsToLoad
			.map((plugin: PluginConstructor | string) => {
				return Type.isFunction(plugin) ? plugin : this.#availablePlugins.get(plugin);
			})
			.forEach((pluginConstructor: PluginConstructor) => {
				if (Type.isFunction(pluginConstructor))
				{
					this.#pluginConstructors.set(pluginConstructor.getName(), pluginConstructor);
				}
			})
		;
	}

	init(textEditor: TextEditor): void
	{
		const instances = [];
		for (const [, PluginConstruct] of this.#pluginConstructors)
		{
			const plugin = new PluginConstruct(textEditor);
			if (!(plugin instanceof BasePlugin))
			{
				throw new TypeError('TextEditor: a plugin must be an instance of TextEditor.BasePlugin.');
			}

			this.#plugins.set(PluginConstruct.getName(), plugin);
			instances.push(plugin);
		}

		instances.forEach((instance: BasePlugin) => {
			instance.afterInit();
		});
	}

	getConstructors(): PluginConstructor[]
	{
		return [...this.#pluginConstructors.values()];
	}

	getPlugins(): Map<string, BasePlugin>
	{
		return this.#plugins;
	}

	[Symbol.iterator](): IterableIterator<[string, BasePlugin]>
	{
		return this.#plugins[Symbol.iterator]();
	}

	get(key: PluginConstructor | string): BasePlugin | null
	{
		const name: string = Type.isFunction(key) ? key.getName() : key;

		return this.#plugins.get(name) || null;
	}

	has(key: PluginConstructor | string): boolean
	{
		const name: string = Type.isFunction(key) ? key.getName() : key;

		return this.#plugins.has(name);
	}
}
