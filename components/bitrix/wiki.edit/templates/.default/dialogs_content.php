<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/template" id="template-insert-image">
	<table>
		<tr>
			<td width="30%"><?=GetMessage('WIKI_IMAGE_URL')?>:</td>
			<td width="70%"><input type="text" id="image_url" name="image_url" value="" /></td>
		</tr>
	</table>
</script>

<script type="text/template" id="template-insert-category">
	<table>
		<tr>
			<td width="30%"><?=GetMessage('WIKI_CATEGORY_NAME')?>:</td>
			<td width="70%"><input type="text" id="category_name" name="category_name" value="" /></td>
		</tr>
		<?if(count($arResult['TREE']) > 1):?>
			<tr>
				<td width="30%"><?=GetMessage('WIKI_CATEGORY_SELECT')?>:</td>
				<td width="70%">
					<select id="category_select" onchange="if(this.options[this.selectedIndex].value != -1) BX('category_name').value = this.options[this.selectedIndex].value" style="width: 240px;">
						<?foreach ($arResult['TREE'] as $key => $value):?>
							<option value="<?=CUtil::JSEscape($key)?>" title="<?=CUtil::JSEscape(htmlspecialcharsbx($value, ENT_QUOTES))?>"><?=CUtil::JSEscape(htmlspecialcharsbx($value, ENT_QUOTES))?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
		<?endif;?>
	</table>
</script>

<script type="text/template" id="template-insert-internal-link">
	<table>
		<tr>
			<td width="30%"><?=GetMessage('WIKI_LINK_URL')?>:</td>
			<td width="70%">
				<input type="text" id="link_url" name="link_url" value="" />
			</td>
		</tr>
		<tr>
			<td width="30%"><?=GetMessage('WIKI_LINK_NAME')?>:</td>
			<td width="70%">
				<input type="text" id="link_name" name="link_name" value="#linkName#" />
			</td>
		</tr>
	</table>
</script>

<script type="text/template" id="template-insert-external-link">
	<table>
		<tr>
			<td width="30%"><?=GetMessage('WIKI_LINK_URL')?>:</td>
			<td width="70%">
				<select id="bx_url_type">
					<option value="http://" SELECTED>http://</option>
					<option value="ftp://" >ftp://</option>
					<option value="https://">https://</option>
				</select>
				<input type="text" id="link_url" name="link_url" value="" />
			</td>
		</tr>
		<tr>
			<td width="30%"><?=GetMessage('WIKI_LINK_NAME')?>:</td>
			<td width="70%"><input type="text" id="link_name" name="link_name" value="#linkName#" size="30" /></td>
		</tr>
	</table>
</script>

<script type="text/template" id="template-image-upload">
	<form action="<?=POST_FORM_ACTION_URI?>" name="load_form" method="post" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="do_upload" value="1" />
		<input type="hidden" name="image_upload" value="Y" />
		<table>
			<tr>
				<td width="30%"><?=GetMessage('WIKI_IMAGE')?>:</td>
				<td width="70%"><?=CFile::InputFile('FILE_ID', 20, 0)?></td>
			</tr>
		</table>
	</form>
</script>

<script type="text/template" id="template-image-item">
	<div class="blog-post-image-item-border">#html#</div>
	<div class="wiki-post-image-item-input">
		<div>
			<input type="checkbox" name="IMAGE_ID_del[#id#]" id="img_del_#id#"/>
			<label for="img_del_#id#"><?=GetMessage('WIKI_IMAGE_DELETE')?></label>
		</div>
	</div>
</script>
