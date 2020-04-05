BXHTMLEditor.prototype.SetCodeEditorContent = function(sContent)
{
	sContent = sContent.replace(/^[\s\S]*?<body.*?>/i, "");
	sContent = sContent.replace(/<\/body>[\s\S]*?$/i, "");
	sContent = sContent.replace(/<\/html>[\s\S]*?$/i, "");

	this.pSourceFrame.value = sContent;
	return true;
	////
	var src = bxhtmlspecialchars(sContent).replace(/\r\n/g, '<br>');
	src = src.replace(/([a-z]+)=&quot;(.+?)&quot;/g, "<font color=blue>$1</font>=&quot;<font color=green>$2</font>&quot;");
	src = src.replace(/&lt;([a-z]+)/g, "&lt;<font color=#000099>$1</font>");
	src = src.replace(/&lt;\/([a-z]+)/g, "&lt;/<font color=#000099>$1</font>");

	this.pSourceWindow.document.body.innerHTML	= '<pre id="content">'+src+'</pre>';
	if(!this.pSourceDocument.body.contentEditable)
	{
		var c = this.pSourceDocument.getElementById('content'); // чтобы в FF успел пропарситься контент
		/*
		alert(c);
		var obj = this;
		setTimeout(function()
			{
				c = obj.pSourceDocument.getElementById('content'); // чтобы в FF успел пропарситься контент
				alert('1'+obj.pSourceDocument.innerHTML);
				obj.pSourceDocument.designMode='on';
			}, 10000
		);
		//c = this.pSourceDocument.getElementById('content'); // чтобы в FF успел пропарситься контент
		//alert(c);
		*/
		this.pSourceDocument.designMode='on';
	}
}

BXHTMLEditor.prototype.GetCodeEditorContent = function()
{
	return this.pSourceFrame.value;
	//////////////////
	if(this.pSourceDocument.body.innerText)
		return this.pSourceDocument.body.innerText;

	var html = this.pSourceWindow.document.body.ownerDocument.createRange();
	html.selectNodeContents(this.pSourceWindow.document.body);
    return html.toString().replace(/\xA0+/g, ' ');
}

BXHTMLEditor.prototype.SetView = function(sType)
{
	this.SaveContent();
	switch(sType)
	{
		case 'code':
			this.pSourceFrame.style.height = "100%";
			this.pEditorFrame.style.display = "none";
			this.pSourceFrame.style.display = "inline";
			this.SetCodeEditorContent(this.GetContent());
			break;
		case 'split':
			this.pEditorFrame.style.height = "50%";
			this.pSourceFrame.style.height = "50%";
			this.pSourceFrame.style.display = "inline";
			this.pEditorFrame.style.display = "block";
			if(this.sEditorMode == 'code')
				this.SetEditorContent(this.GetContent());
			else if(this.sEditorMode == 'html')
				this.SetCodeEditorContent(this.GetContent());
			else
			{
				this.SetCodeEditorContent(this.GetContent());
				this.SetEditorContent(this.GetContent());
			}
			break;
		default:
			this.pEditorFrame.style.height = "100%";
			this.pSourceFrame.style.display = "none";
			this.pEditorFrame.style.display = "block";
			this.SetEditorContent(this.GetContent());
			sType = "html";
	}

	this.sEditorMode = sType;
	this.OnEvent("OnChangeView", [this.sEditorMode, this.sEditorSplitMode]);
	//this.LoadContent();
}
