function ValidateForm(form)
{
	var editor = (window["BXHtmlEditor"] ? window["BXHtmlEditor"].Get("POST_MESSAGE") : null);
	if (typeof form != "object" || !editor)
		return false;
	editor.SaveContent();

	var
		errors = "",
		Message = editor.GetContent(),
		MessageMax = 64000,
		MessageLength = form.POST_MESSAGE.value.length;

	if (form.POST_SUBJ && (form.POST_SUBJ.value.length < 2))
		errors += window["oErrors"]['no_topic_name'];

	if (MessageLength < 2)
		errors += window["oErrors"]['no_message'];
	else if ((MessageMax !== 0) && (MessageLength > MessageMax))
		errors += window["oErrors"]['max_len'].replace("#MAX_LENGTH#", MessageMax).replace("#LENGTH#", MessageLength);

	if (errors !== "")
	{
		alert(errors);
		return false;
	}
	
	var arr = form.getElementsByTagName("input");
	for (var i=0; i < arr.length; i++)
	{
		var butt = arr[i];
		if (butt.getAttribute("type") == "submit")
			butt.disabled = true;
	}
	return true;
}
