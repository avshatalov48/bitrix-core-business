import {Type, Loc} from 'main.core';

export class SonetGroupMenu
{
	static instance = null;

	constructor()
	{
		this.menuPopup = null;
		this.menuItem = null;
		this.favoritesValue = null;
	}

	static getInstance()
	{
		if (Type.isNull(this.instance))
		{
			this.instance = new SonetGroupMenu();

			BX.addCustomEvent('SidePanel.Slider:onClose', () => {
				if (this.instance.menuPopup)
				{
					this.instance.menuPopup.close();
				}
			});

			BX.addCustomEvent('BX.Socialnetwork.WorkgroupMenuIcon:onSetFavorites', (params) => {
				this.getInstance().setItemTitle(params.value);
			});
		}

		return this.instance;
	}

	setItemTitle(value)
	{
		if (!Type.isDomNode(this.menuItem))
		{
			return;
		}

		this.menuItem.innerHTML = (value ? Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE') : Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD'));
	}
}
