BXHTMLEditor.prototype.SetCodeEditorContent = function(sContent)
{
	this.pSourceFrame.value = this.SetCodeEditorContent_ex(sContent);
};

BXHTMLEditor.prototype.GetCodeEditorContent = function()
{
	return this.pSourceFrame.value;
	if(this.pSourceDocument.body.innerText)
		return this.pSourceDocument.body.innerText;

	var html = this.pSourceWindow.document.body.ownerDocument.createRange();
	html.selectNodeContents(this.pSourceWindow.document.body);
	return html.toString().replace(/\xA0+/g, ' ');
};


BXHTMLEditor.prototype.SetView = function(sType)
{
	//console.info('BXHTMLEditor.prototype.SetView = ' + sType);
	if (this.sEditorMode == sType)
		return;
	
	this.SaveContent();
	switch(sType)
	{
		case 'code':
			this.pSourceFrame.style.height = "99%";
			this.pEditorFrame.style.display = "none";
			this.pSourceFrame.style.display = "inline";
			if (IEplusDoctype)
			{
				this.pSourceFrame.rows = "50";
				this.pSourceDiv.style.height = "99%";
				this.pSourceDiv.style.display = "block";
			}
			//console.info('code SetView > this.GetContent() = '+ this.GetContent());
			this.SetCodeEditorContent(this.GetContent());
			break;
		case 'split':
			this.pEditorFrame.style.height = "50%";
			if (IEplusDoctype)
			{
				this.pSourceFrame.rows = "40";
				this.pSourceDiv.style.height = "49%";
				this.pSourceDiv.style.display = "block";
			}
			else
			{
				this.pSourceFrame.style.height = "49%";
			}
			
			this.pSourceFrame.style.display = "inline";
			this.pEditorFrame.style.display = "block";
			if(this.sEditorMode == 'code')
				this.SetEditorContent(this.GetContent());
			else if(this.sEditorMode == 'html')
				this.SetCodeEditorContent(this.GetContent());
			break;
		default:
			this.pEditorFrame.style.height = "100%";
			this.pSourceFrame.style.display = "none";
			this.pEditorFrame.style.display = "block";
			if (IEplusDoctype)
				this.pSourceDiv.style.display = "none";
			//console.info('html SetView > this.GetContent() = '+ this.GetContent());
			this.SetEditorContent(this.GetContent());
			sType = "html";
	}
	
	//console.info("SPLIT >> \n this.sEditorMode= " + this.sEditorMode + "\n this.sEditorSplitMode = "+ this.sEditorSplitMode + "\n");
	
	this.sEditorMode = sType;
	this.OnEvent("OnChangeView", [this.sEditorMode, this.sEditorSplitMode]);
};