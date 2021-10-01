export default function showUrlPreview(htmlEditor, editorParams)
{
	if (!(editorParams.urlPreviewId && window['BXUrlPreview'] && BX(editorParams.urlPreviewId)))
	{
		return;
	}

	const urlPreview =  new BXUrlPreview(BX(editorParams.urlPreviewId));
	const OnAfterUrlConvert = function(url)
	{
		urlPreview.attachUrlPreview({url: url});
	};
	const OnBeforeCommandExec = function(isContentAction, action, oAction, value)
	{
		if (action === 'createLink'
			&& BX.type.isPlainObject(value)
			&& value.hasOwnProperty('href')
		)
		{
			urlPreview.attachUrlPreview({url: value.href});
		}
	}
	BX.addCustomEvent(htmlEditor, 'OnAfterUrlConvert', OnAfterUrlConvert);
	BX.addCustomEvent(htmlEditor, 'OnAfterLinkInserted', OnAfterUrlConvert);
	BX.addCustomEvent(htmlEditor, 'OnBeforeCommandExec', OnBeforeCommandExec);

	BX.addCustomEvent(htmlEditor, 'OnReinitialize', (text, data) => {
		urlPreview.detachUrlPreview();
		let urlPreviewId;
		for (let uf in data)
		{
			if (data.hasOwnProperty(uf)
				&& data[uf].hasOwnProperty('USER_TYPE_ID')
				&& data[uf]['USER_TYPE_ID'] === 'url_preview')
			{
				urlPreviewId = data[uf]['VALUE'];
				break;
			}
		}
		if (urlPreviewId)
		{
			urlPreview.attachUrlPreview({id: urlPreviewId});
		}
	});
}