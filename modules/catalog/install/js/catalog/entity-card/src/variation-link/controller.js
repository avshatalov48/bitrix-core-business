import {EventEmitter} from 'main.core.events'


export default class VariationLinkController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		EventEmitter.subscribe('onChangeVariationLink', this.markAsChanged.bind(this));
	}

	rollback()
	{
		super.rollback();
		if (this._isChanged)
		{
			this._isChanged = false;
		}
	}
}