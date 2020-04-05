(function() {
var BX = window.BX;
if(BX.PhoneAuth)
	return;

BX.PhoneAuth = function(params)
{
	this.containerId = params.containerId;
	this.errorContainerId = params.errorContainerId;
	this.data = params.data;
	this.interval = 60;
	if(params.interval)
	{
		this.interval = params.interval;
	}
	if(params.onError)
	{
		this.onError = params.onError;
	}

	BX.ready(BX.delegate(this.createLink, this));
};

BX.PhoneAuth.prototype.createLink = function()
{
	var container = BX(this.containerId);
	if(container)
	{
		var note = BX.message('phone_auth_resend').replace(/#INTERVAL#/, '<span id="' + this.containerId + '_counter">' + this.interval + '</span>');
		container.innerHTML =
			'<div id="' + this.containerId + '_text">' + note + '</div>\n' +
			'<div id="' + this.containerId + '_action" style="display:none"><a href="javascript:void(0)" id="' + this.containerId + '_link">' + BX.message('phone_auth_resend_link') + '</a></div>';
		BX(this.containerId + '_link').onclick = BX.delegate(this.resendCode, this);
		this.startTimer();
	}
};

BX.PhoneAuth.prototype.startTimer = function()
{
	BX(this.containerId + '_counter').textContent = this.interval;
	BX(this.containerId + '_text').style.display = '';
	BX(this.containerId + '_action').style.display = 'none';
	BX(this.errorContainerId).style.display = 'none';

	var timerId = setInterval(
		BX.delegate(function()
		{
			var span = BX(this.containerId + '_counter');
			var counter = parseInt(span.textContent);
			if(counter > 0)
			{
				counter--;
			}
			span.textContent = counter;
		}, this),
		1000
	);

	setTimeout(
		BX.delegate(function()
		{
			clearInterval(timerId);
			BX(this.containerId + '_text').style.display = 'none';
			BX(this.containerId + '_action').style.display = '';
		}, this),
		this.interval*1000
	);
};

BX.PhoneAuth.prototype.resendCode = function()
{
	BX.ajax.runAction(
		'main.phoneauth.resendCode',
		{
			data: this.data
	 	}
	).then(
		this.startTimer(),
		BX.delegate(function (response)
		{
			this.startTimer();

			if(this.onError)
			{
				this.onError(response);
			}
		}, this)
	);
};

})();
