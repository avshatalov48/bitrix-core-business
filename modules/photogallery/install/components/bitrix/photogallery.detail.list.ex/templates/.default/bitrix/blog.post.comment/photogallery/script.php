<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>
function onLightEditorShow(content)
{
	if (!window.oBlogComLHE)
		return BX.addCustomEvent(window, 'LHE_OnInit', function(){setTimeout(function(){onLightEditorShow(content);},	500);});

	oBlogComLHE.ReInit(content || '');
}

function showComment(key, error, userName, userEmail, needData)
{
	<?
	if($arResult["use_captcha"]===true)
	{
		?>
		BX.ajax.getCaptcha(function(data) {
			BX("captcha_word").value = "";
			BX("captcha_code").value = data["captcha_sid"];
			BX("captcha").src = '/bitrix/tools/captcha.php?captcha_code=' + data["captcha_sid"];
			BX("captcha").style.display = "";
		});
		<?
	}
	?>

	subject = '';
	comment = '';

	if(needData == "Y")
	{
		subject = window["title"+key];
		comment = window["text"+key];
	}

	var pFormCont = BX('form_c_del');
	BX('form_comment_' + key).appendChild(pFormCont); // Move form
	pFormCont.style.display = "block";

	document.form_comment.parentId.value = key;
	document.form_comment.edit_id.value = '';
	document.form_comment.act.value = 'add';
	document.form_comment.post.value = '<?=GetMessage("B_B_MS_SEND")?>';
	document.form_comment.action = document.form_comment.action + "#" + key;

	if(error == "Y")
	{
		if(comment.length > 0)
		{
			comment = comment.replace(/\/</gi, '<');
			comment = comment.replace(/\/>/gi, '>');
		}
		if(userName.length > 0)
		{
			userName = userName.replace(/\/</gi, '<');
			userName = userName.replace(/\/>/gi, '>');
			document.form_comment.user_name.value = userName;
		}
		if(userEmail.length > 0)
		{
			userEmail = userEmail.replace(/\/</gi, '<');
			userEmail = userEmail.replace(/\/>/gi, '>');
			document.form_comment.user_email.value = userEmail;
		}
		if(subject && subject.length>0 && document.form_comment.subject)
		{
			subject = subject.replace(/\/</gi, '<');
			subject = subject.replace(/\/>/gi, '>');
			document.form_comment.subject.value = subject;
		}
	}

	onLightEditorShow(comment);
	BX.onCustomEvent('onShowPhotoBlogComment');
	return false;
}

function editComment(key)
{
	subject = window["title"+key];
	comment = window["text"+key];

	if(comment.length > 0)
	{
		comment = comment.replace(/\/</gi, '<');
		comment = comment.replace(/\/>/gi, '>');
	}

	var pFormCont = BX('form_c_del');
	BX('form_comment_' + key).appendChild(pFormCont); // Move form
	pFormCont.style.display = "block";

	onLightEditorShow(comment);

	document.form_comment.parentId.value = '';
	document.form_comment.edit_id.value = key;
	document.form_comment.act.value = 'edit';
	document.form_comment.post.value = '<?=GetMessage("B_B_MS_SAVE")?>';
	document.form_comment.action = document.form_comment.action + "#" + key;

	if(subject && subject.length > 0 && document.form_comment.subject)
	{
		subject = subject.replace(/\/</gi, '<');
		subject = subject.replace(/\/>/gi, '>');
		document.form_comment.subject.value = subject;
	}
	return false;
}

function waitResult(id)
{
	r = 'new_comment_' + id;
	ob = BX(r);
	if(ob.innerHTML.length > 0)
	{
		var obNew = BX.processHTML(ob.innerHTML, true);
		scripts = obNew.SCRIPT;
		BX.ajax.processScripts(scripts, true);
		if(window.commentEr && window.commentEr == "Y")
		{
			BX('err_comment_'+id).innerHTML = ob.innerHTML;
			ob.innerHTML = '';
		}
		else
		{
			if(BX('edit_id').value > 0)
			{
				if(BX('blg-comment-'+id))
				{
					BX('blg-comment-'+id+'old').innerHTML = BX('blg-comment-'+id).innerHTML;
					BX('blg-comment-'+id+'old').id = 'blg-comment-'+id;
				}
				else
					BX('blg-comment-'+id+'old').innerHTML = ob.innerHTML;
			}
			else
				BX('new_comment_cont_'+id).innerHTML += ob.innerHTML;
			ob.innerHTML = '';
			BX('form_c_del').style.display = "none";
		}
		window.commentEr = false;

		BX.closeWait();
		BX('post-button').disabled = false;
	}
	else
		setTimeout("waitResult('"+id+"')", 500);
}

function submitComment()
{
	oBlogComLHE.SaveContent();
	obForm = BX('form_comment');
	<?
	if($arParams["AJAX_POST"] == "Y")
	{
		?>
		if(BX('edit_id').value > 0)
		{
			val = BX('edit_id').value;
			BX('blg-comment-'+val).id = 'blg-comment-'+val+'old';
		}
		else
			val = BX('parentId').value;
		id = 'new_comment_' + val;
		BX('post-button').focus();
		BX('post-button').disabled = true;
		if(BX('err_comment_'+val))
			BX('err_comment_'+val).innerHTML = '';

		BX.showWait('bxlhe_frame_LHEBlogCom');
		BX.ajax.submitComponentForm(obForm, id);
		setTimeout("waitResult('"+val+"')", 100);
		<?
	}
	?>
	BX.submit(obForm);
}

function hideShowComment(url, id)
{
	var bcn = BX('blg-comment-'+id);
	BX.showWait(bcn);
	bcn.id = 'blg-comment-'+id+'old';
	BX('err_comment_'+id).innerHTML = '';

	BX.ajax.get(url, function(data) {
		var obNew = BX.processHTML(data, true);
		scripts = obNew.SCRIPT;
		BX.ajax.processScripts(scripts, true);
		var nc = BX('new_comment_'+id);
		var bc = BX('blg-comment-'+id+'old');
		nc.style.display = "none";
		nc.innerHTML = data;

		if(BX('blg-comment-'+id))
		{
			bc.innerHTML = BX('blg-comment-'+id).innerHTML;
		}
		else
		{
			BX('err_comment_'+id).innerHTML = nc.innerHTML;
		}
		BX('blg-comment-'+id+'old').id = 'blg-comment-'+id;

		BX.closeWait();
	});

	return false;
}

function deleteComment(url, id)
{
	BX.showWait(BX('blg-comment-'+id));

	BX.ajax.get(url, function(data) {
		var obNew = BX.processHTML(data, true);
		scripts = obNew.SCRIPT;
		BX.ajax.processScripts(scripts, true);

		var nc = BX('new_comment_'+id);
		nc.style.display = "none";
		nc.innerHTML = data;

		if(BX('blg-com-err'))
		{
			BX('err_comment_'+id).innerHTML = nc.innerHTML;
		}
		else
		{
			BX('blg-comment-'+id).innerHTML = nc.innerHTML;
		}
		nc.innerHTML = '';

		BX.closeWait();
	});

	return false;
}

function bcShowMoreCom()
{
	var
		pLink = BX('bx-blog-com-show-more-link'),
		pInp = BX('bx-blog-com-cur');

	BX.showWait(pLink);
	setTimeout(function() {
		pInp.value = pInp.value - 1;
		if (pInp.value > 0)
		{
			var pDiv = BX('blog-comment-page-' + pInp.value);
			if (pDiv)
				pDiv.style.display = "";
		}
		else
		{
			BX('bx-blog-com-show-more-link').style.display = "none";
		}
		BX.closeWait();
		BX.onCustomEvent('onShowPhotoBlogComment');
	}, 200);
}
</script>