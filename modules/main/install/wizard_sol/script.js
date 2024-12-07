function CancelBubble(event)
{
	if (event.stopPropagation)
	{
		event.preventDefault();
		event.stopPropagation();
	}
	else
	{
		event.cancelBubble = true;
		event.returnValue = false;
	}
}

function htmlspecialchars(str)
{
	if (typeof(str)!='string')
		return str;
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	return str;
}

function strip_tags(str)
{
	return str.replace(/<\/?[^>]+>/gi, '');
}


function CAjaxForm(formName, target, hiddenField)
{
	var form = document.forms[formName];
	if (!form)
		 form = document.getElementById(formName);

	this.nextStep = form.elements[hiddenField];
	this.nextStepStage = form.elements[hiddenField+"Stage"];
	this.iframe = document.getElementById(target);
	this.form = form;
	this.form.target = target;
	var _this = this;

	if (this.iframe.attachEvent) //IE
		this.iframe.attachEvent("onload", function() {_this.AjaxHandler()});
	else
		this.iframe.onload = function() {_this.AjaxHandler()};

	this.percent = null;
	this.percent2 = null;
	this.indicator = null;
	this.status = null;
}

CAjaxForm.prototype.AjaxHandler = function()
{
	//opera triggers onload event even on empty iframe
	if(this.iframe.contentWindow && this.iframe.contentWindow.location.href.indexOf('http') != 0)
		return;

	if (this.iframe.contentDocument)
		var iframeDocument = this.iframe.contentDocument;
	else
		var iframeDocument = this.iframe.contentWindow.document;

	var response = iframeDocument.body.innerHTML;
	if (response.length == 0 || iframeDocument.getElementById("bitrix_install_template"))
	{
		this.ShowError("Connection error. Empty response.");
		return;
	}

	var regexpStart = new RegExp('\\[response\\]', 'i');
	var regexpEnd = new RegExp('\\[\/response\\]', 'i');

	var matchResponse = response.match(regexpStart);

	if (matchResponse === null)
	{
		this.ShowError(response);
		return;
	}

	var start = matchResponse.index + matchResponse[0].length;
	var end = response.search(regexpEnd);
	if (end == -1)
	{
		this.ShowError(response);
		return;
	}

	response = response.substr(start, end-start);

	//if (window.execScript)
	//	window.execScript(response, 'javascript');
	//else
	window.eval(response);
}

CAjaxForm.prototype.ShowError = function(errorMessage)
{
	var errorContainer = document.getElementById("error_container");
	var errorText = document.getElementById("error_text");
	if (!errorContainer || !errorText)
		return;

	var waitWindow = document.getElementById("wait");
	if (waitWindow)
		waitWindow.style.display = "none";

	errorContainer.style.display = 'block';
	errorText.innerHTML = strip_tags(errorMessage);

	var retryButton = document.getElementById("error_retry_button");
	var skipButton = document.getElementById("error_skip_button");

	var _this = this;
	var nextStep = this.nextStep.value;
	var nextStepStage = this.nextStepStage.value;

	retryButton.onclick = function() {_this.HideError(); _this.Post(nextStep, nextStepStage,'');};

	if (nextStep == "main")
		skipButton.onclick = function() {_this.HideError(); _this.Post(nextStep, nextStepStage,'');};
	else
		skipButton.onclick = function() {_this.HideError(); _this.Post(nextStep, 'skip','');};
}

CAjaxForm.prototype.HideError = function()
{
	var errorContainer = document.getElementById("error_container");
	var errorText = document.getElementById("error_text");
	if (!errorContainer || !errorText)
		return;

	while (errorText.firstChild)
		errorText.removeChild(errorText.firstChild);
	
	errorContainer.style.display = 'none';

	var waitWindow = document.getElementById("wait");
	if (waitWindow)
		waitWindow.style.display = "block";
}

CAjaxForm.prototype.Post = function(nextStep, nextStepStage, status)
{
	if (nextStep)
		this.nextStep.value = nextStep;

	if (nextStepStage)
		this.nextStepStage.value = nextStepStage;

	this.form.submit();

	if (!this.status)
		this.status = document.getElementById("status");

	if (status.length > 0)
		this.status.innerHTML = status + "...";
}

CAjaxForm.prototype.StopAjax = function()
{
	this.iframe.onload = null;
	this.form.target = "_self";
}

CAjaxForm.prototype.SetStatus = function(percent)
{
	if (!this.percent)
		this.percent = document.getElementById("percent");

	if (!this.percent2)
		this.percent2 = document.getElementById("percent2");

	if (!this.indicator)
		this.indicator = document.getElementById("indicator");

	this.percent.innerHTML = percent + "%";
	this.percent2.innerHTML = percent + "%";
	this.indicator.style.width = percent + "%";
}


function PreloadImages(path)
{
	var preloadImages = ["prev.gif", "error.gif", "wait.gif", "admin.gif", "public.gif"];

	for (var imageIndex = 0; imageIndex < preloadImages.length; imageIndex++)
	{
		var imageObj = new Image();
		imageObj.src = path + preloadImages[imageIndex];
	}
}

function ImgShw(ID, width, height, alt)
{
	var scroll = "no";
	var top=0, left=0;
	if(width > screen.width-10 || height > screen.height-28) scroll = "yes";
	if(height < screen.height-28) top = Math.floor((screen.height - height)/2-14);
	if(width < screen.width-10) left = Math.floor((screen.width - width)/2-5);
	width = Math.min(width, screen.width-10);
	height = Math.min(height, screen.height-28);
	var wnd = window.open("","","scrollbars="+scroll+",resizable=yes,width="+width+",height="+height+",left="+left+",top="+top);
	wnd.document.write(
		"<html><head>"+
			"<"+"script>"+
			"function KeyPress()"+
			"{"+
			"	if(window.event.keyCode == 27) "+
			"		window.close();"+
			"}"+
			"</"+"script>"+
			"<title></title></head>"+
			"<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onKeyPress=\"KeyPress()\">"+
			"<img src=\""+ID+"\" border=\"0\" alt=\""+alt+"\" />"+
			"</body></html>"
	);
	wnd.document.close();
}

