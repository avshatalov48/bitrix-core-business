ForumFormCaptcha = function(params)
{
	if (params == null)
		return false;
	this.div = params.div || null;
	this.input = params.input || null;
	this.image = params.image || null;
	this.hidden = params.hidden || null;
	if ( ! (
		this.div &&
		this.input &&
		this.image &&
		this.hidden
	)) return false;

	setTimeout(BX.proxy(this.BindLHE, this), 200);
}

ForumFormCaptcha.prototype.BindLHEEvents = function()
{
	BX.bind(window.oLHE.pEditorDocument, 'keydown', BX.proxy(this.Show, this));
	BX.bind(window.oLHE.pTextarea, 'keydown', BX.proxy(this.Show, this));
	BX.bind(window.oLHE.pButtonsCont, 'click', BX.proxy(this.Show, this));
}

ForumFormCaptcha.prototype.BindLHE = function()
{
	this.BindLHEEvents();
	window.oLHE.forumFormCaptcha = this;
	window.oLHE.ffcSetEditorContent = window.oLHE.SetEditorContent;
	window.oLHE.SetEditorContent = function(sContent)
	{
		var result = this.ffcSetEditorContent(sContent);
		this.forumFormCaptcha.BindLHEEvents();
		return result;
	}
	if (window.oLHE.GetContent().length > 0)
		this.Show();
}

ForumFormCaptcha.prototype.Show = function() {
	function _checkDisplay(ob) {
		var d = ob.style.display || BX.style(ob, 'display');
		return (d != 'none');
	}

	if (! _checkDisplay(this.div)) {
		BX.show(this.div);
		this.Update();
	}
}

ForumFormCaptcha.prototype.UpdateControls = function(data) {
	this.input.value = '';
	this.hidden.value = data.captcha_sid;
	this.image.src = '/bitrix/tools/captcha.php?captcha_code='+data.captcha_sid;
}

ForumFormCaptcha.prototype.Update = function() {
	BX.ajax.getCaptcha(BX.proxy(this.UpdateControls, this));
}