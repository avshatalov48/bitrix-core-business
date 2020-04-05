<?
class CBlogSmile
{
	static $smiles = array();
	static $sets = array();

	function CheckFields()
	{
		return false;
	}

	function Add()
	{
		return false;
	}

	function Update()
	{
		return false;
	}

	function Delete()
	{
		return false;
	}

	function GetList()
	{
		return false;
	}

	function GetByID()
	{
		return false;
	}

	function GetByIDEx()
	{
		return false;
	}

	function GetLangByID()
	{
		return false;
	}

	public static function GetSmilesList()
	{
		$type = CSmile::TYPE_SMILE;
		$lang = LANGUAGE_ID;

		if (COption::GetOptionInt("blog", "smile_native_gallery_id", 0) <= 0)
			return self::getSmiles($type, $lang);

		$key = "old_".$type."_".$lang;
		if (!array_key_exists($key, self::$smiles))
		{
			$smiles = CSmile::getByGalleryId($type, COption::GetOptionInt("blog", "smile_native_gallery_id", 0), $lang);
			$result = array();
			foreach ($smiles as $smile)
			{
				if ($smile['HIDDEN'] == 'Y')
					continue;

				$result[] = array(
					'ID' => $smile['ID'],
					'SMILE_TYPE' => $type,
					'TYPING' => $smile['TYPING'],
					'IMAGE' => $smile["IMAGE"],
					'DESCRIPTION' => '',
					'CLICKABLE' => 'Y',
					'SORT' => $smile['SORT'],
					'IMAGE_WIDTH' => $smile['IMAGE_WIDTH'],
					'IMAGE_HEIGHT' => $smile['IMAGE_HEIGHT'],
					'SET_ID' => $smile['SET_ID'],
					'NAME' => $smile['NAME'],
					'WIDTH' => $smile['IMAGE_WIDTH'],
					'HEIGHT' => $smile['IMAGE_HEIGHT'],
				);
			}
			self::$smiles[$key] = $result;
		}
		return self::$smiles[$key];
	}

	public static function getSmiles($type, $lang)
	{
		$type = ($type == "I" ? CSmile::TYPE_ICON : CSmile::TYPE_SMILE);
		$key = "new_".$type."_".$lang;

		if (!array_key_exists($key, self::$smiles))
		{
			$smiles = CSmile::getByGalleryId($type, COption::GetOptionInt("blog", "smile_gallery_id", 0), $lang);
			$result = array();
			foreach ($smiles as $smile)
			{
				if ($smile['HIDDEN'] == 'Y')
					continue;

				$result[] = array(
					'SET_ID' => $smile['SET_ID'],
					'NAME' => $smile['NAME'],
					'IMAGE' => ($smile['TYPE'] == CSmile::TYPE_SMILE ? CSmile::PATH_TO_SMILE : CSmile::PATH_TO_ICON).$smile["SET_ID"]."/".$smile["IMAGE"],
					'TYPING' => $smile['TYPING'],
					'WIDTH' => $smile['IMAGE_WIDTH'],
					'HEIGHT' => $smile['IMAGE_HEIGHT'],
				);
			}
			self::$smiles[$key] = $result;
		}
		return self::$smiles[$key];
	}
}
?>