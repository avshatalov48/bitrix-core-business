import {MenuManager} from 'main.popup';
import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';

export class BaseMenu
{
	menuInstance: Object = null;
	context: Object = null;
	target: HTMLElement = null;
	store: Object = null;
	restClient: Object = null;
	id: String = 'im-base-context-menu';

	constructor($Bitrix: Object)
	{
		this.$Bitrix = $Bitrix;
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();

		this.onClosePopupHandler = this.onClosePopup.bind(this);
		EventEmitter.subscribe(EventType.dialog.closePopup, this.onClosePopupHandler);
	}

	// public
	openMenu(context: Object, target: HTMLElement)
	{
		if (this.menuInstance)
		{
			this.menuInstance.destroy();
			this.menuInstance = null;
		}
		this.context = context;
		this.target = target;
		this.menuInstance = this.getMenuInstance();
		this.menuInstance.show();
	}

	getMenuInstance()
	{
		return MenuManager.create(this.getMenuOptions());
	}

	getMenuOptions(): Object
	{
		return {
			id: this.id,
			bindOptions: {forceBindPosition: true, position: 'bottom'},
			targetContainer: document.body,
			bindElement: this.target,
			cacheable: false,
			className: this.getMenuClassName(),
			items: this.getMenuItems()
		};
	}

	getMenuItems(): Array
	{
		return [];
	}

	getMenuClassName(): string
	{
		return this.isDarkMode() ? 'im-context-menu-dark' : '';
	}

	isDarkMode(): boolean
	{
		return this.store.state.application.options.darkTheme;
	}

	onClosePopup()
	{
		this.destroy();
	}

	close()
	{
		if (!this.menuInstance)
		{
			return;
		}

		this.menuInstance.destroy();
		this.menuInstance = null;
	}

	destroy()
	{
		this.close();
		EventEmitter.unsubscribe(EventType.dialog.closePopup, this.onClosePopupHandler);
	}
}