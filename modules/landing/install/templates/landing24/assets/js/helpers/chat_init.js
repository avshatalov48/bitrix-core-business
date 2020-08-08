;(function ()
{
	'use strict';

	BX.ready(function() {
		if (!top.BXIM)
		{
			return;
		}

		var clickedChat = null;
		var selectors = [].slice.call(document.querySelectorAll('a.landing-chat-message'));

		if (selectors.length)
		{
			for (var i = 0, c = selectors.length; i < c; i++)
			{
				var link = selectors[i];
				var href = link.getAttribute('href');
				if (href)
				{
					link.addEventListener(
						'click',
						function()
						{
							var chatType = this.substr(0, 5);
							var chatId = this.substr(5);
							if (chatType === '#chat')
							{
								top.BXIM.openMessenger(chatId);
							}
							else if (chatType === '#join')
							{
								clickedChat = chatId;
								BX.ajax({
									url: '/bitrix/tools/landing/ajax.php',
									method: 'POST',
									data: {
										action: 'Chat::joinChat',
										data: {
											internalId: chatId
										},
										sessid: BX.message('bitrix_sessid')
									},
									dataType: 'json',
									onsuccess: function(data)
									{
										if (data.result)
										{
											top.BXIM.openMessenger('chat' + data.result);
										}
									}
								});
							}
							BX.PreventDefault();
						}.bind(href)
					);
				}
			}
		}

		BX.addCustomEvent(
			'onPullEvent-im',
			function(command, params)
			{
				if (command === 'chatUserAdd')
				{
					if (clickedChat)
					{
						// you should find data-chatid="<clickedChat>"
						console.log(params.users);
					}
				}
			}
		);
	});

})();
