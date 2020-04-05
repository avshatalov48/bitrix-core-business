var voteScript = {
	trace_vote: function(div, flag)
	{
		var my_div = div;
		while (true)
		{
			if (my_div && my_div.nodeType == 1)
			{
				if (flag)
				{
					if(!my_div.saved_className)
						my_div.saved_className = my_div.className;
					BX.addClass(my_div, 'photo-rating-star-select');
				}
				else
				{
					if(my_div.saved_className && my_div.className != my_div.saved_className)
						my_div.className = my_div.saved_className;
				}
			}

			if (!my_div || !my_div.previousSibling)
				break;
			my_div = my_div.previousSibling;
		}
	},

	do_vote: function(div, parent_id, arParams)
	{
		var pVoteCont = BX('bx-photo-rating-cont');
		var counter = 1;
		BX.addClass(pVoteCont, 'photo-rating-wait');
		pVoteCont.innerHTML = '...';
		var loadingInterval = setInterval(
			function()
			{
				var html = '.';
				if (counter == 2)
				{
					html = '..';
				}
				else if (counter == 3)
				{
					html = '...';
					counter = 0;
				}
				pVoteCont.innerHTML = html;
				counter++;
			},
			300
		);
		var r = div.id.match(/^vote_(\d+)_(\d+)$/);

		arParams.vote = 'Y';
		arParams.vote_id = r[1];
		arParams.rating = r[2];

		BX.ajax.post(
			'/bitrix/components/bitrix/iblock.vote/component.php',
			arParams,
			function (data)
			{
				if (loadingInterval)
				{
					clearInterval(loadingInterval);
					loadingInterval = null;
				}
				BX.removeClass(pVoteCont, 'photo-rating-wait');
				pVoteCont.innerHTML = data;
			}
		);
	}
}