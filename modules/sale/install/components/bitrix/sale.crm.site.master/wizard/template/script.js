function strip_tags(str)
{
	return str.replace(/<\/?[^>]+>/gi, '');
}

function CAjaxWizardForm(formName, target, hiddenField)
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
	this.progressBar = null;
	this.result = null;
	this.description = null;
}

CAjaxWizardForm.prototype.AjaxHandler = function()
{
	//opera triggers onload event even on empty iframe
	if(this.iframe.contentWindow && this.iframe.contentWindow.location.href.indexOf('http') !== 0)
		return;

	var iframeDocument;
	if (this.iframe.contentDocument)
		iframeDocument = this.iframe.contentDocument;
	else
		iframeDocument = this.iframe.contentWindow.document;

	var response = iframeDocument.body.innerHTML;
	if (response.length === 0
		|| iframeDocument.getElementById("bitrix_install_template")
		|| response.indexOf("[Error]") !== -1
	)
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
	if (end === -1)
	{
		this.ShowError(response);
		return;
	}

	response = response.substr(start, end-start);

	window.eval(response);
};

CAjaxWizardForm.prototype.ShowError = function(errorMessage)
{
	var errorContainer = document.getElementById("error_container");
	var errorText = document.getElementById("error_text");
	if (!errorContainer || !errorText)
		return;

	errorContainer.style.display = 'block';
	errorText.innerHTML = errorMessage;

	var retryButton = document.getElementById("error_retry_button");

	var _this = this;
	var nextStep = this.nextStep.value;
	var nextStepStage = this.nextStepStage.value;

	retryButton.onclick = function() {
		_this.HideError();
		_this.Post(nextStep, nextStepStage);
	};
};

CAjaxWizardForm.prototype.HideError = function()
{
	var errorContainer = document.getElementById("error_container");
	var errorText = document.getElementById("error_text");
	if (!errorContainer || !errorText)
		return;

	while (errorText.firstChild)
		errorText.removeChild(errorText.firstChild);

	errorContainer.style.display = 'none';
};

CAjaxWizardForm.prototype.Post = function(nextStep, nextStepStage)
{
	if (nextStep)
		this.nextStep.value = nextStep;

	this.nextStepStage.value = nextStepStage;

	if (nextStep === "finish")
	{
		this.FinishStatus()
	}
	else
	{
		this.form.submit();
	}
};

CAjaxWizardForm.prototype.StopAjax = function()
{
	this.iframe.onload = null;
	this.form.target = "_self";
};

CAjaxWizardForm.prototype.SetStatus = function(percent)
{
	if (!this.percent)
		this.percent = document.getElementById("progressBar_percent");

	this.percent.innerHTML = percent;

	if (!this.progressBar)
		this.progressBar = document.getElementById("progressBar");

	this.progressBar.style.width = percent + "%";
};

CAjaxWizardForm.prototype.FinishStatus = function()
{
	if (!this.result)
		this.result = document.getElementById("result");

	if (!!this.result.classList && !this.result.classList.contains("complete"))
	{
		this.result.classList.add("complete");
	}

	document.getElementById("button_submit_wrap").style.display = "block";

	this.AutoSubmitTimer();
};

CAjaxWizardForm.prototype.AutoSubmitTimer = function()
{
	if (!this.description)
		this.description = document.getElementById("progress_description");

	var descriptionText = BX.message("SALE_CSM_WIZARD_MODULEINSTALLSTEP_INSTALL_WAIT3");
	var seconds = 10;

	var intervalId = setInterval(function(descriptionText, that){
		if(seconds === 0){
			clearInterval(intervalId);
			that.form.submit();
		}

		that.description.innerHTML = descriptionText.replace("#COUNT_TIME#", seconds);

		seconds--;
	}, 1000, descriptionText, this);
};

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
}

CAjaxForm.prototype.AjaxHandler = function()
{
	//opera triggers onload event even on empty iframe
	if(this.iframe.contentWindow && this.iframe.contentWindow.location.href.indexOf('http') !== 0)
		return;

	var iframeDocument;
	if (this.iframe.contentDocument)
		iframeDocument = this.iframe.contentDocument;
	else
		iframeDocument = this.iframe.contentWindow.document;

	var response = iframeDocument.body.innerHTML;
	if (response.length === 0 || iframeDocument.getElementById("bitrix_install_template"))
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
	if (end === -1)
	{
		this.ShowError(response);
		return;
	}

	response = response.substr(start, end-start);

	window.eval(response);
};

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

	if (nextStep === "main")
		skipButton.onclick = function() {_this.HideError(); _this.Post(nextStep, nextStepStage,'');};
	else
		skipButton.onclick = function() {_this.HideError(); _this.Post(nextStep, 'skip','');};
};

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
};

CAjaxForm.prototype.Post = function(nextStep, nextStepStage, status)
{
	if (nextStep)
		this.nextStep.value = nextStep;

	if (nextStepStage)
		this.nextStepStage.value = nextStepStage;

	this.form.submit();
};

CAjaxForm.prototype.StopAjax = function()
{
	this.UnsetEventBeforeUnloadWindow();

	this.iframe.onload = null;
	this.form.target = "_self";
};

CAjaxForm.prototype.SetStatus = function(percent)
{

};

/**
 * @return {string}
 */
CAjaxForm.prototype.OnBeforeUnloadWindow = function(e)
{
	var confirmationMessage = BX.message("SALE_CSM_WIZARD_DATAINSTALLSTEP_CLOSE_CONFIRMATION");

	(e || window.event).returnValue = confirmationMessage;
	return confirmationMessage;
};

CAjaxForm.prototype.SetEventBeforeUnloadWindow = function()
{
	window.addEventListener("beforeunload", this.OnBeforeUnloadWindow);
};

CAjaxForm.prototype.UnsetEventBeforeUnloadWindow = function()
{
	window.removeEventListener("beforeunload", this.OnBeforeUnloadWindow);
};