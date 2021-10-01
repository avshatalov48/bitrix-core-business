export default function bindAutoSave(htmlEditor, formNode)
{
	if (!formNode)
	{
		return;
	}
	BX.addCustomEvent(formNode, 'onAutoSavePrepare', function (ob) {
		ob.FORM.setAttribute("bx-lhe-autosave-prepared", "Y");
		setTimeout(function() {
			BX.addCustomEvent(htmlEditor, 'OnContentChanged', function(text) {
				ob["mpfTextContent"] = text;
				ob.Init();
			});
		},1500);
	});

	BX.addCustomEvent(formNode, 'onAutoSave', function(ob, form_data)
	{
		if (BX.type.isNotEmptyString(ob['mpfTextContent']))
			form_data['text'] = ob['mpfTextContent'];
	});

	BX.addCustomEvent(formNode, 'onAutoSaveRestore', function(ob, form_data) {
		if (form_data['text'] && /[^\s]+/gi.test(form_data['text']))
		{
			htmlEditor.CheckAndReInit(form_data['text']);
		}
	});

	if (formNode.hasAttribute("bx-lhe-autosave-prepared") && formNode.BXAUTOSAVE)
	{
		formNode.removeAttribute("bx-lhe-autosave-prepared");
		setTimeout(formNode.BXAUTOSAVE.Prepare, 100);
	}
}