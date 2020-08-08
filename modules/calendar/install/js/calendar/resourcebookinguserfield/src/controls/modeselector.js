import {Type, Dom, Loc, Event} from "calendar.resourcebooking";

export class ModeSelector
{
	constructor(params)
	{
		this.params = params;
		this.outerWrap = this.create();
	}

	create()
	{
		let
			wrapNode = Dom.create("span",
				{
					props:{className: "calendar-resourcebook-content-block-select calendar-resourcebook-mode-selector"}
				}
			),
			menuItems = [
				{
					text: Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES'),
					onclick: function(e, item){
						if (Type.isFunction(this.params.showResources))
						{
							this.params.showResources();
						}
						wrapNode.innerHTML = item.text;
						this.modeSwitcherPopup.close();
					}.bind(this)
				},
				{
					text: Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_USERS'),
					onclick: function(e, item){
						if (Type.isFunction(this.params.showUsers))
						{
							this.params.showUsers();
						}
						wrapNode.innerHTML = item.text;
						this.modeSwitcherPopup.close();
					}.bind(this)
				},
				{
					text: Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES_AND_USERS'),
					onclick: function(e, item){
						if (Type.isFunction(this.params.showResourcesAndUsers))
						{
							this.params.showResourcesAndUsers();
						}
						wrapNode.innerHTML = item.text;
						this.modeSwitcherPopup.close();
					}.bind(this)
				}
			],
			switcherId = 'mode-switcher-' + Math.round(Math.random() * 100000);


		Event.bind(wrapNode, 'click', function(){
			if (this.modeSwitcherPopup && this.modeSwitcherPopup.popupWindow && this.modeSwitcherPopup.popupWindow.isShown())
			{
				return this.modeSwitcherPopup.close();
			}

			this.modeSwitcherPopup = BX.PopupMenu.create(
				switcherId,
				wrapNode,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					offsetTop: 0,
					offsetLeft: 20,
					angle: true
				}
			);
			this.modeSwitcherPopup.show();

			BX.addCustomEvent(this.modeSwitcherPopup.popupWindow, 'onPopupClose', function()
			{
				BX.PopupMenu.destroy(switcherId);
				this.modeSwitcherPopup = null;
			}.bind(this));
		}.bind(this));

		if (this.params.useUsers && !this.params.useResources)
		{
			wrapNode.innerHTML = Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_USERS');
		}
		else if (this.params.useUsers && this.params.useResources)
		{
			wrapNode.innerHTML = Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES_AND_USERS');
		}
		else
		{
			wrapNode.innerHTML = Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES');
		}

		return wrapNode;
	}

	getOuterWrap()
	{
		return this.outerWrap;
	}
}
