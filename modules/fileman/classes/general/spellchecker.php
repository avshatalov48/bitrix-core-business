<?
IncludeModuleLangFile(__FILE__);

class CSpellchecker
{
	private
		$lang,
		$skip_len,
		$pspell,
		$pspell_mode,
		$custom_spell,
		$wrongWords,
		$dics_path,
		$custom_dics_path,
		$dic;

	public function __construct($params)
	{
		$this->lang = (isset($params["lang"]) && $params["lang"] != '') ? $params["lang"] : 'en';
		$this->skip_len = $params["skip_length"];

		$this->pspell = (function_exists('pspell_config_create') && ($params["use_pspell"] == "Y"));
		//$this->custom_spell = $params["use_custom_spell"] == "Y";
		$this->custom_spell = false;
		$this->pspell_mode = $params["mode"];

		$this->dics_path = $this->checkDicPath();
		$this->user_dics_path = $this->dics_path."/custom.pws";
		$this->custom_dics_path = $this->dics_path.'/custom_dics/'.$this->lang.'_';

		if($this->custom_spell)
		{
			$this->dic = array();
		}

		if ($this->pspell)
		{
			$pspell_config = pspell_config_create ($this->lang, null, null, 'utf-8');
			pspell_config_ignore($pspell_config, $this->skip_len);
			pspell_config_mode($pspell_config, $params["mode"]);
			pspell_config_personal($pspell_config, $this->user_dics_path);
			$this->pspell_link = pspell_new_config($pspell_config);
		}
	}

	private function checkDicPath()
	{
		global $USER;
		$dics_path = $_SERVER["DOCUMENT_ROOT"].COption::GetOptionString('fileman', "user_dics_path", "/bitrix/modules/fileman/u_dics");

		$custom_path = $dics_path.'/'.$this->lang;

		if (COption::GetOptionString('fileman', "use_separeted_dics", "Y") == "Y")
		{
			$custom_path = $custom_path.'/'.$USER->GetID();
		}

		$io = CBXVirtualIo::GetInstance();
		if(!$io->DirectoryExists($custom_path))
		{
			$io->CreateDirectory($custom_path);
		}

		return $custom_path;
	}

	function codeLetter($letter)
	{
		return (in_array($letter, $this->letters) && $letter != 'ы' && $letter != 'ь' && $letter != 'ъ') ? ord($letter) : 'def';
	}

	function loadDic($letter)
	{
		$path = $this->custom_dics_path.$letter.'.dic';
		if (is_readable($path))
		{
			$dic = file($path);
			foreach ($dic as $dict_word)
			{
				$this->dic[$letter][mb_strtolower(trim($dict_word))] = $dict_word;
			}
		}
		else
			$this->dic[$letter] = array();
	}

	function checkWord($word)
	{
		//pspell
		if ($this->pspell)
		{
			return pspell_check($this->pspell_link, $word);
		}
	}


	function checkWords($words)
	{
		$this->wrongWords = array();

		for ($i = 0; $i < count($words); $i++)
		{
			if (!$this->checkWord($words[$i]))
			{
				$this->wrongWords[] = array(
					0 => $i,
					1 => $this->suggest($words[$i])
				);
			}
		}

		return $this->wrongWords;
	}

	function suggest($word)
	{
		$suggestions = array();
		//pspell
		if ($this->pspell)
		{
			$suggestions = pspell_suggest($this->pspell_link, $word);
		}
		return array_unique($suggestions);
	}

	function addWord($word = '')
	{
		//pspell
		if ($this->pspell)
		{
			if (!pspell_add_to_personal($this->pspell_link, $word) || !pspell_save_wordlist($this->pspell_link))
			{
				return false;
			}
		}
	}
}
