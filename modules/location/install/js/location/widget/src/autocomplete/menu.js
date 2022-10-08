import {Menu as MainMenu} from 'main.popup';
import './css/menu.css';
import MenuBottom from './menubottom';

export default class Menu extends MainMenu
{
	choseItemIdx = -1;
	#bottom;

	constructor(options)
	{
		super(options);

		const elRect = options.bindElement.getBoundingClientRect();
		this.popupWindow.setMaxWidth(elRect.width);
		this.#bottom = new MenuBottom();
		this.layout.menuContainer.appendChild(
			this.#bottom.render()
		);
	}

	isMenuEmpty(): boolean
	{
		return this.menuItems.length <= 0;
	}

	isChoseLastItem(): boolean
	{
		return this.choseItemIdx >= this.menuItems.length - 1;
	}

	isChoseFirstItem(): boolean
	{
		return this.choseItemIdx === 0;
	}

	isItemChosen(): boolean
	{
		return this.choseItemIdx >= 0;
	}

	isDestroyed(): boolean
	{
		return this.getPopupWindow().isDestroyed();
	}

	isItemExist(index: number): boolean
	{
		return typeof this.menuItems[index] !== 'undefined';
	}

	getChosenItem()
	{
		let result = null;

		if(this.isItemChosen() && this.isItemExist(this.choseItemIdx))
		{
			result = this.menuItems[this.choseItemIdx];
		}

		return result;
	}

	chooseNextItem(): void
	{
		if(!this.isMenuEmpty() && !this.isChoseLastItem())
		{
			this.chooseItem(this.choseItemIdx + 1);
		}

		return this.getChosenItem();
	}

	choosePrevItem(): void
	{
		if(!this.isMenuEmpty() && !this.isChoseFirstItem())
		{
			this.chooseItem(this.choseItemIdx - 1);
		}

		return this.getChosenItem();
	}

	highlightItem(index: number): void
	{
		if(this.isItemExist(index))
		{
			const item = this.getChosenItem();

			if(item && item.layout.item)
			{
				item.layout.item.classList.add('highlighted');
			}
		}
	}

	unHighlightItem(index: number): void
	{
		if(this.isItemExist(index))
		{
			const item = this.getChosenItem();

			if(item && item.layout.item)
			{
				item.layout.item.classList.remove('highlighted');
			}
		}
	}

	chooseItem(index: number)
	{
		let idx = index;

		if(idx < 0)
		{
			idx = this.menuItems.length - 1;
		}
		else if(idx > this.menuItems.length - 1)
		{
			idx = 0;
		}

		this.unHighlightItem(this.choseItemIdx);
		this.choseItemIdx = idx;
		this.highlightItem(this.choseItemIdx);
	}

	clearItems()
	{
		while(this.menuItems.length > 0)
		{
			this.removeMenuItem(this.menuItems[0].id);
		}
	}

	isShown(): boolean
	{
		return this.getPopupWindow().isShown();
	}

	setBottomRightItemNode(node: Element): void
	{
		this.#bottom.setRightItemNode(node);
	}

	setBottomLeftItemNode(node: Element): void
	{
		this.#bottom.setLeftItemNode(node);
	}
}
