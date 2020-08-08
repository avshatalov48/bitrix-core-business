function CBXSession()
{
	var _this = this;
	this.dateInput = new Date();
	this.dateCheck = new Date();
	this.dateHit = new Date();
	this.notifier = null;
	this.checkInterval = 60;

	this.Expand = function(key)
	{
		this.key = key;
		
		BX.ready(function(){
			BX.bind(document, "keypress", _this.OnUserInput);
			BX.bind(document.body, "mousemove", _this.OnUserInput);
			BX.bind(document.body, "click", _this.OnUserInput);

			//check the state once in a minute
			setInterval(_this.CheckSession, _this.checkInterval*1000);
		})
	};
		
	this.OnUserInput = function()
	{
		var curr = new Date();
		_this.dateInput.setTime(curr.valueOf());
	};
	
	this.CheckSession = function()
	{
		var currentDate = new Date();

		if((currentDate - _this.dateCheck) < (_this.checkInterval - 1))
		{
			//storm protection, e.g. after PC wake-up
			return;
		}

		_this.dateCheck.setTime(currentDate.valueOf());

		if(_this.dateInput > _this.dateHit)
		{
			//there was input after the last hit, expand/check the session
			var config = {
				'method': 'GET',
				'headers': [
					{'name': 'X-Bitrix-Csrf-Token', 'value': BX.bitrix_sessid()}
				],
				'dataType': 'html',
				'url': '/bitrix/tools/public_session.php?k='+_this.key,
				'data':  '',
				'onsuccess': function(data){_this.CheckResult(data)},
				'lsId': 'sess_expand', //caching the result in the local storage for multiple tabs
				'lsTimeout': _this.checkInterval - 5 //some delta for response time
			};
			BX.ajax(config);
		}
	};
	
	this.CheckResult = function(data)
	{
		var currentDate = new Date();
		_this.dateHit.setTime(currentDate.valueOf());

		if(data == 'SESSION_EXPIRED')
		{
			if(BX.message("SessExpired"))
			{
				if(!_this.notifier)
				{
					_this.notifier = document.body.appendChild(BX.create('DIV', {
						props: {className: 'bx-session-message'},
						style: {
							top: '0',
							backgroundColor: '#FFEB41',
							border: '1px solid #EDDA3C',
							width: '630px',
							fontFamily: 'Arial,Helvetica,sans-serif',
							fontSize: '13px',
							fontWeight: 'bold',
							textAlign: 'center',
							color: 'black',
							position: 'absolute',
							zIndex: '10000',
							padding: '10px'
						},
						html: '<a class="bx-session-message-close" ' +
							'style="display:block; width:12px; height:12px; background:url(/bitrix/js/main/core/images/close.gif) center no-repeat; float:right;" ' +
							'href="javascript:bxSession.Close()"></a>' +
							BX.message("SessExpired")
					}));

					var windowScroll = BX.GetWindowScrollPos();
					var windowSize = BX.GetWindowInnerSize();

					_this.notifier.style.left = parseInt(windowScroll.scrollLeft + (windowSize.innerWidth / 2) - (parseInt(_this.notifier.clientWidth) / 2)) + 'px';

					if(BX.browser.IsIE())
					{
						_this.notifier.style.top = windowScroll.scrollTop + 'px';

						BX.bind(window, 'scroll', function()
						{
							var windowScroll = BX.GetWindowScrollPos();
							_this.notifier.style.top = windowScroll.scrollTop + 'px';
						});
					}
					else
					{
						_this.notifier.style.position='fixed';
					}
				}

				_this.notifier.style.display = '';
			}
		}
	};
	
	this.Close = function()
	{
		this.notifier.style.display = 'none';
	}
}

var bxSession = new CBXSession();
