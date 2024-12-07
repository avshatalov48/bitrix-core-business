<?
class CAdminContextMenuML extends CAdminContextMenu
{
	function GetClassByID($icon_id)
	{
		if (mb_substr($icon_id, 0, 7) == 'btn_new')
			return 'adm-btn-save adm-btn-add';
		else
			return parent::GetClassByID($icon_id);

		return '';
	}

	function BeginRightBar()
	{
		?><div><?
		return false;
	}

	function Button($item, $hkInst)
	{

		if(isset($item['LEFT_FLOAT_BEGIN']) || isset($item['RIGHT_FLOAT_BEGIN']) || isset($item['FLOAT_END']))
		{
			if(isset($item['LEFT_FLOAT_BEGIN']) && $item['LEFT_FLOAT_BEGIN'] == true)
				echo '<span class="ml-menu-float-left">';

			if(isset($item['RIGHT_FLOAT_BEGIN']) && $item['RIGHT_FLOAT_BEGIN'] == true)
				echo '<span class="ml-menu-float-right">';

			if(isset($item['FLOAT_END']) && $item['FLOAT_END'] == true)
				echo '</span>';

			return true;
		}


		parent::Button($item, $hkInst);
	}
}
?>