BX.Forum = (!!BX.Forum ? BX.Forum : {});
BX.Forum.transliterate = function(node)
{
	if (!BX.translit)
		return false;
	node.onblur = function(){ clearInterval(node.bxfInterval); };
	node.bxfInterval = setInterval(function(){
		if (node.value != node.bxValue)
		{
			node.bxValue = node.value;
			BX.translit(node.value, {
				'max_len' : 70,
				'change_case' : 'L',
				'replace_space' : '-',
				'replace_other' : '',
				'delete_repeat_replace' : true,
				'use_google' : true,
				'callback' : function(result){ node.nextSibling.value = result; }
			});
		}
	}, 500);
};
function ForumSearchTopic(oObj, bSetControl)
{
	BX.Forum['topic_search']['object'] = oObj = (typeof(BX.Forum['topic_search']['object']) == "object" ? BX.Forum['topic_search']['object'] : oObj);
	if (typeof(oObj) != "object" || oObj == null)
		return false;
	bSetControl = (bSetControl == "Y" || bSetControl == "N" ? bSetControl : "U");
	BX.Forum['topic_search']['action'] = (bSetControl == "N" ? "dont_search" : (bSetControl == "Y" ? "search" : BX.Forum['topic_search']['action']));

	var res = parseInt(oObj.value);
	if (res <= 0 || !parseInt(res))
		BX('TOPIC_INFO').innerHTML = BX.message('topic_bad');
	else if (parseInt(BX.Forum['topic_search']['value']) != res)
	{
		BX('TOPIC_INFO').innerHTML = BX.message('topic_wait');
		BX.Forum['topic_search']['value'] = oObj.value;
		ForumSendMessage(oObj.value, BX.Forum['topic_search']['url']);
	}
	if (BX.Forum['topic_search']['action'] == "search")
		setTimeout(ForumSearchTopic, 1000);
	return false;
}

function ForumSendMessage(id, url)
{
	id = (parseInt(id) > 0 ? parseInt(id) : false);
	url = (typeof url == "string" && url.length > 0 ? url : false);
	if (!id || !url)
		return false;
	BX.ajax.get(url, {AJAX_CALL : "Y", TID : id}, function(data)
		{
			var result = false;
			try { eval('result = ' + data + ';'); } catch(e) { result = false; }
			BX('TOPIC_INFO').innerHTML = ((typeof(result) == "object" && result != null) ? result['TOPIC_TITLE'] : BX.message('topic_not_found'));
		});
}