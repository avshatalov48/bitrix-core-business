(function (window)
{
	if (!!window.JCFlatVote)
	{
		return;
	}

	window.JCFlatVote = {
		trace_vote: function (div, flag)
		{
			var my_div;
			//Left from current
			my_div = div;
			while (my_div = my_div.previousSibling)
			{
				if (flag)
					BX.addClass(my_div, 'bx-star-active');
				else
					BX.removeClass(my_div, 'bx-star-active');
			}
			//current
			if (flag)
				BX.addClass(div, 'bx-star-active');
			else
				BX.removeClass(div, 'bx-star-active');
			//Right from the current
			my_div = div;
			while (my_div = my_div.nextSibling)
			{
				BX.removeClass(my_div, 'bx-star-active');
			}
		},

		do_vote: function (div, parent_id, arParams)
		{
			var r = div.id.match(/^vote_(\d+)_(\d+)$/);

			var vote_id = r[1];
			var vote_value = r[2];

			arParams['vote'] = 'Y';
			arParams['vote_id'] = vote_id;
			arParams['rating'] = vote_value;
			BX.ajax.post(
				'/bitrix/components/bitrix/iblock.vote/component.php',
				arParams,
				function (data)
				{
					var obContainer = BX(parent_id);
					if (obContainer)
					{
						var obResult = BX.create('DIV');
						obResult.innerHTML = data;
						obContainer.parentNode.replaceChild(obResult.firstChild, obContainer);
					}
				}
			);
		}
	}
}
)(window);
