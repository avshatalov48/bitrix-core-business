import {EventEmitter} from 'main.core.events'

export default class BindingToCrmElementController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
	}

	rollback()
	{
		super.rollback();
		if (this._isChanged)
		{
			this._isChanged = false;
		}
		EventEmitter.unsubscribeAll('BX.Main.User.SelectorController::open');
	}

	onBeforeSubmit()
	{
		super.onBeforeSubmit();
		EventEmitter.unsubscribeAll('BX.Main.User.SelectorController::open');
	}
}