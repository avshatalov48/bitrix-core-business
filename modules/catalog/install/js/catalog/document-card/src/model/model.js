export default class DocumentModel extends BX.UI.EntityModel
{
	constructor(id, settings) {
		super();
		this.initialize(id, settings);
	}

	isCaptionEditable()
	{
		return true;
	}

	getCaption()
	{
		var title = this.getField("TITLE");
		return BX.type.isString(title) ? title : "";
	}

	setCaption(caption)
	{
		this.setField("TITLE", caption);
	}

	prepareCaptionData(data)
	{
		data["TITLE"] = this.getField("TITLE", "");
	}
}
