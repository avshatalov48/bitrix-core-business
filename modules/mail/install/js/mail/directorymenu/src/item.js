import { Tag } from 'main.core';

export class Item
{
	#count = 0;
	#nameOriginal = '';
	#name = '';
	#counterElement: null;
	#itemElement: null;
	#isActive: false;
	#path : '';
	#shiftWidthInPixels = 10;
	#zeroLevelShiftWidth = 29;

	setCount(number)
	{
		this.#count = number;
		this.#counterElement.textContent = number;

		if (number === 0)
		{
			this.#counterElement.classList.add('ui-sidepanel-menu-link-text-counter-hidden');
		}
		else
		{
			this.#counterElement.classList.remove('ui-sidepanel-menu-link-text-counter-hidden');
		}
	}

	getCount()
	{
		return Number(this.#count);
	}

	disableActivity()
	{
		this.#isActive = false;
		this.#itemElement.classList.remove('ui-sidepanel-menu-active');
	}

	getPath()
	{
		return this.#path;
	}

	enableActivity()
	{
		this.#isActive = true;
		this.#itemElement.classList.add('ui-sidepanel-menu-active');
	}

	isActive()
	{
		return this.#isActive;
	}

	/**
	 * So as not to break the menu with incorrectly synchronized directories.
	 *
	 * @param directory (directory structure).
	 * @returns {boolean}
	 */
	static checkProperties(directory) {
		if(directory['path'] === undefined || directory['name'] === undefined || directory['name'] === undefined)
		{
			return false;
		}
		return true;
	}

	constructor(directory, menu, nestingLevel = 0, systemDirs)
	{
		this.#path = directory['path'];

		let iconClass = 'default';
		if(systemDirs['inbox'] === this.#path)
		{
			iconClass = 'inbox';
		}
		else if(systemDirs['spam'] === this.#path)
		{
			iconClass = 'spam';
		}
		else if(systemDirs['outcome'] === this.#path)
		{
			iconClass = 'outcome';
		}
		else if(systemDirs['trash'] === this.#path)
		{
			iconClass = 'trash';
		}
		else if(systemDirs['drafts'] === this.#path)
		{
			iconClass = 'drafts';
		}

		this.#nameOriginal = directory['name'];

		this.#name = this.#nameOriginal.charAt(0).toUpperCase() + this.#nameOriginal.slice(1);

		const itemContainer = Tag.render`<div title="${this.#name}" class="mail-menu-directory-item-container"></div>`;
		const itemElement = Tag.render`<li class="ui-sidepanel-menu-item ui-sidepanel-menu-counter-white mail-menu-directory-item-${iconClass}">
				<a style="padding-left: ${this.#zeroLevelShiftWidth + (this.#shiftWidthInPixels*nestingLevel)}px" class="ui-sidepanel-menu-link">
					<div class="ui-sidepanel-menu-link-text">
						<span class="ui-sidepanel-menu-link-text-item">${this.#name}</span>
					</div>
					<span class="ui-sidepanel-menu-link-text-counter">${directory['count']}</span>
				</a>
			</li>`;
		itemContainer.append(itemElement);

		itemElement.onclick = ()=>
		{
			if(!itemContainer.isActive())
			{
				menu.chooseFunction(directory['path']);
				itemContainer.enableActivity();
			}
		}

		const counterElement = itemElement.querySelector(".ui-sidepanel-menu-link-text-counter");

		this.#counterElement = counterElement;
		this.#itemElement = itemElement;

		itemContainer.getCount = () => this.getCount();
		itemContainer.setCount = number => this.setCount(number);
		itemContainer.enableActivity = () => this.enableActivity();
		itemContainer.disableActivity = () => this.disableActivity();
		itemContainer.isActive = () => this.isActive();
		itemContainer.setIconClass = name => this.setIconClass(name);

		this.setCount(directory['count']);

		for(let i=0; i<directory['items'].length; i++)
		{
			if(!Item.checkProperties(directory['items'][i]))
			{
				continue;
			}
			const subdirectory = new Item(directory['items'][i],menu,nestingLevel+1,systemDirs);
			itemContainer.append(subdirectory);
		}

		menu.includeItem(itemContainer, this.#path);

		return itemContainer;
	}
}