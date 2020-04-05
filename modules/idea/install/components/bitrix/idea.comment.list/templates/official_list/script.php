<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>
function onLightEditorShow(content)
{
//	todo: need?
	if (!window.oBlogComLHE)
		return BX.addCustomEvent(window, 'LHE_OnInit', function(){setTimeout(function(){onLightEditorShow(content);},	500);});

	window.LHEPostForm.reinitData('LHEBlogCom', content);
}

function showComment(key, subject, error, comment, userName, userEmail)
{
	<?
	if($arResult["use_captcha"]===true)
	{
		?>
		var im = BX('captcha');
		BX('captcha_del').appendChild(im);
		<?
	}
	?>

	var pFormCont = BX('form_c_del');
	BX('form_comment_' + key).appendChild(pFormCont); // Move form
	pFormCont.style.display = "block";

	document.form_comment.parentId.value = key;
	document.form_comment.edit_id.value = '';
	document.form_comment.act.value = 'add';
	document.form_comment.post.value = '<?=GetMessage("B_B_MS_SEND")?>';
	document.form_comment.action = document.form_comment.action + "#" + key;

	<?
	if($arResult["use_captcha"]===true)
	{
		?>
		var im = BX('captcha');
		BX('div_captcha').appendChild(im);
		im.style.display = "block";
		<?
	}
	?>

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
		if(subject.length>0 && document.form_comment.subject)
		{
			subject = subject.replace(/\/</gi, '<');
			subject = subject.replace(/\/>/gi, '>');
			document.form_comment.subject.value = subject;
		}
	}

	onLightEditorShow(comment);

	return false;
}

function editComment(key, subject, comment)
{
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

	if(subject.length > 0 && document.form_comment.subject)
	{
		subject = subject.replace(/\/</gi, '<');
		subject = subject.replace(/\/>/gi, '>');
		document.form_comment.subject.value = subject;
	}
	return false;
}
</script>