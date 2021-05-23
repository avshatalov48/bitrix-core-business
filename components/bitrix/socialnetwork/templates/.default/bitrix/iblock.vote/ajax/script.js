var voteScript = {
	trace_vote: function(div, flag)
	{
		var my_div = div;
		while (my_div)
		{
			if (flag)
			{
				if(!my_div.saved_className)
					my_div.saved_className = my_div.className;
				if(my_div.className!='star-active star-over')
					my_div.className = 'star-active star-over';
			}
			else
			{
				if(my_div.saved_className && my_div.className != my_div.saved_className)
					my_div.className = my_div.saved_className;
			}
			if (my_div.parentNode.previousSibling)
				my_div = my_div.parentNode.previousSibling.firstChild;
			else
				my_div = false;
		}
	},
	
	do_vote: function(div, parent_id, arParams)
	{
		var r = div.id.match(/^vote_(\d+)_(\d+)$/);

		var vote_id = r[1];
		var vote_value = r[2];

		function __handler(data)
		{
			var obContainer = div.parentNode.parentNode.parentNode.parentNode;
			if (obContainer)
			{
				var obResult = document.createElement("DIV");
				obResult.innerHTML = data;
				obContainer.parentNode.replaceChild(obResult.firstChild, obContainer);
			}
		}
		div.parentNode.parentNode.lastChild.innerHTML = '...';
		
		var url = '/bitrix/components/bitrix/iblock.vote/component.php'

		arParams['vote'] = 'Y';
		arParams['vote_id'] = vote_id;
		arParams['rating'] = vote_value;

		BX.ajax.post(
			'/bitrix/components/bitrix/iblock.vote/component.php',
			arParams,
			__handler
		);
	}
}