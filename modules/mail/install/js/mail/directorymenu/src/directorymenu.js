import { Tag } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Item } from './item.js';

import 'ui.design-tokens';
import 'ui.fonts.opensans';
import './css/ui-wrappermenu.css';
import './css/style.css';


export class DirectoryMenu
{
	#activeDir = '';
	#menu = Tag.render`<ul class="ui-mail-left-directory-menu"></ul>`;
	#directoryCounters = new Map();
	#items = new Map();
	#systemDirs = [];

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
		this.#directoryCounters = dirsWithUnseenMailCounters;
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

	buildMenu(firstBuild = false)
	{
		for (let i = 0; i < this.#directoryCounters.length; i++)
		{
			const directory = this.#directoryCounters[i];
			const path = directory['path'];
			if(!Item.checkProperties(directory))
			{
				continue;
			}

			if(this.#systemDirs['inbox'] === path && firstBuild)
			{
				BX.Mail.Home.FilterToolbar.setCount(directory['count']);
			}

			new Item(directory,this,0,this.#systemDirs);
		}
	}

	setFilterDir(name)
	{
		const event = new BaseEvent({ data: { directory: name } });
		EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', event);

		name = BX.Mail.Home.Counters.getShortcut(name);

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

	setDirectory(path)
	{
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
		systemDirs :
		{
			spam: 'Spam',
			trash: 'Trash',
			outcome: 'Outcome',
			drafts: 'Drafts',
			inbox: 'Inbox',
		}
	})
	{
		this.filter = BX.Main.filterManager.getById(config['filterId']);
		this.#systemDirs = config['systemDirs'];

		EventEmitter.subscribe('BX.Main.Filter:apply', (event) => {

			let dir = BX.Mail.Home.Counters.getDirPath(this.filter.getFilterFieldsValues()['DIR']);

			EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', new BaseEvent({ data: { directory: dir } }));
			this.setDirectory(dir)
		});

		this.#directoryCounters = config['dirsWithUnseenMailCounters'];

		this.buildMenu(true);
	}

	getNode()
	{
		return this.#menu;
	}
}