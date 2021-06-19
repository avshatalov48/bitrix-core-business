import { Tag } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Item } from './item.js';
import './css/ui-wrappermenu.css';
import './css/style.css';

export class DirectoryMenu
{
	#activeDir = '';
	#menu = Tag.render`<ul class="ui-mail-left-directory-menu"></ul>`;
	#dirsWithUnseenMailCounters = new Map();
	#items = new Map();

	getActiveDir()
	{
		return this.#activeDir;
	}

	setActiveDir(path)
	{
		this.#activeDir = path;
	}

	clearActiveMenuButtons()
	{
		for (let item of this.#items.values())
		{
			item.disableActivity();
		}
	}

	rebuildMenu(dirsWithUnseenMailCounters)
	{
		this.#dirsWithUnseenMailCounters = dirsWithUnseenMailCounters;
		this.cleanItems();
		this.buildMenu();
		this.setDirectory(this.getActiveDir());
	}

	cleanItems()
	{
		for (let item of this.#items.values())
		{
			this.#menu.removeChild(item);
		}
		this.#items.clear();
	}

	includeItem(domItem, directoryPath)
	{
		this.#items.set(directoryPath, domItem);
		this.#menu.append(domItem);
	}

	chooseFunction(path)
	{
		this.clearActiveMenuButtons();
		this.setActiveDir(path);
		this.setFilterDir(path);
	}

	buildMenu()
	{
		for (let i = 0; i < this.#dirsWithUnseenMailCounters.length; i++)
		{
			const directory = this.#dirsWithUnseenMailCounters[i];
			const path = directory['path'];
			if(!Item.checkProperties(directory))
			{
				continue;
			}
			const itemElement = new Item(directory,this);
		}
	}

	setFilterDir(name)
	{
		const event = new BaseEvent({ data: { directory: name } });
		EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', event);

		name = this.convertPathForFilter(name);

		const filter = this.filter;
		if (!!filter && (filter instanceof BX.Main.Filter))
		{
			const FilterApi = filter.getApi();
			FilterApi.setFields({
				'DIR': name,
			});
			FilterApi.apply();
		}
	}

	changeCounter(dirPath, number, mode)
	{
		const item = this.#items.get(dirPath);

		if(item === undefined) return;

		if (mode !== 'set')
		{
			item.setCount(item.getCount() + Number(number));
		}
		else
		{
			item.setCount(Number(number));
		}
	}

	setCounters(counters)
	{
		for (let path in counters)
		{
			if (counters.hasOwnProperty(path))
			{
				this.changeCounter(path, counters[path], 'set');
			}
		}
	}

	convertPathForFilter(path)
	{
		if (path === 'INBOX' || path === undefined)
		{
			path = '';
		}

		return path;
	}

	convertPathForMenu(path)
	{
		if (path === '' || path === undefined)
		{
			path = 'INBOX';
		}

		return path;
	}

	setDirectory(path)
	{
		path = this.convertPathForMenu(path);

		this.clearActiveMenuButtons();
		if(path === undefined) return;
		const item = this.#items.get(path);
		if(item)
		{
			this.setActiveDir(path)
			item.enableActivity();
		}
	}

	constructor(config = {
		dirsWithUnseenMailCounters: {},
		filterId: '',
	})
	{
		this.filter = BX.Main.filterManager.getById(config['filterId']);

		EventEmitter.subscribe('BX.Main.Filter:apply', (event) => {
			let dir = this.filter.getFilterFieldsValues()['DIR'];
			dir = this.convertPathForMenu(dir);
			EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', new BaseEvent({ data: { directory: dir } }));
			this.setDirectory(dir)
		});

		this.#dirsWithUnseenMailCounters = config['dirsWithUnseenMailCounters'];

		this.buildMenu();
	}

	getNode()
	{
		return this.#menu;
	}
}