<?php
IncludeModuleLangFile(__FILE__);

class CSearchStemTable extends CSearchFullText
{
	public function connect($connectionIndex, $indexName = '')
	{
	}

	public function truncate()
	{
		$DB = CDatabase::GetModuleConnection('search');

		$DB->Query('TRUNCATE TABLE b_search_stem');
		$DB->Query('TRUNCATE TABLE b_search_content_text');
		$DB->Query('TRUNCATE TABLE b_search_content_stem');
	}

	public function deleteById($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$DB->Query('DELETE FROM b_search_content_text WHERE SEARCH_CONTENT_ID = ' . $ID);
		$DB->Query('DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ' . $ID);
	}

	public function replace($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$helper = $DB->getConnection()->getSqlHelper();

		if (array_key_exists('SEARCHABLE_CONTENT', $arFields))
		{
			$text_md5 = md5($arFields['SEARCHABLE_CONTENT']);
			$rsText = $DB->Query('SELECT SEARCH_CONTENT_MD5 FROM b_search_content_text WHERE SEARCH_CONTENT_ID = ' . $ID);
			$arText = $rsText->Fetch();
			if (!$arText || $arText['SEARCH_CONTENT_MD5'] !== $text_md5)
			{
				CSearch::CleanFreqCache($ID);
				$DB->Query('DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ' . $ID);
				if (COption::GetOptionString('search', 'agent_stemming') === 'Y')
				{
					CSearchStemTable::DelayStemIndex($ID);
				}
				else
				{
					CSearch::StemIndex($arFields['SITE_ID'], $ID, $arFields['SEARCHABLE_CONTENT']);
				}

				$merge = $helper->prepareMerge('b_search_content_text', ['SEARCH_CONTENT_ID'], [
					'SEARCH_CONTENT_ID' => $ID,
					'SEARCH_CONTENT_MD5' => $text_md5,
					'SEARCHABLE_CONTENT' => $arFields['SEARCHABLE_CONTENT']
				], [
					'SEARCH_CONTENT_MD5' => $text_md5,
					'SEARCHABLE_CONTENT' => $arFields['SEARCHABLE_CONTENT']
				]);
				if ($merge)
				{
					$DB->Query($merge[0]);
				}
			}
		}
	}

	public static function DelayStemIndex($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);

		$DB->Query('
			delete from b_search_content_stem
			where SEARCH_CONTENT_ID = -' . $ID
		);
		$DB->Query('
			insert into b_search_content_stem
			(SEARCH_CONTENT_ID, LANGUAGE_ID, STEM, TF, PS)
			values
			(-' . $ID . ', \'en\', 0, 0, 0)
		');

		CSearchStemTable::_addAgent();
	}

	private static function _addAgent()
	{
		global $APPLICATION;

		static $bAgentAdded = false;
		if (!$bAgentAdded)
		{
			$bAgentAdded = true;
			$rsAgents = CAgent::GetList(['ID' => 'DESC'], ['NAME' => 'CSearchStemTable::DelayedStemIndex(%']);
			if (!$rsAgents->Fetch())
			{
				$res = CAgent::AddAgent(
					'CSearchStemTable::DelayedStemIndex();',
					'search', //module
					'N', //period
					1 //interval
				);

				if (!$res)
				{
					$APPLICATION->ResetException();
				}
			}
		}
	}

	public static function DelayedStemIndex()
	{
		$DB = CDatabase::GetModuleConnection('search');
		$etime = time() + intval(COption::GetOptionString('search', 'agent_duration'));
		do {
			$stemQueue = $DB->Query($DB->TopSql('
				SELECT SEARCH_CONTENT_ID ID
				FROM b_search_content_stem
				WHERE SEARCH_CONTENT_ID < 0
			', 1));
			if ($stemTask = $stemQueue->Fetch())
			{
				$ID = -$stemTask['ID'];

				$sites = [];
				$rsSite = $DB->Query('
					SELECT SITE_ID, URL
					FROM b_search_content_site
					WHERE SEARCH_CONTENT_ID = ' . $ID . '
				');
				while ($arSite = $rsSite->Fetch())
				{
					$sites[$arSite['SITE_ID']] = $arSite['URL'];
				}

				$rsContent = $DB->Query('SELECT SEARCHABLE_CONTENT from b_search_content_text WHERE SEARCH_CONTENT_ID = ' . $ID);
				if ($arContent = $rsContent->Fetch())
				{
					$DB->Query('DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ' . $ID);
					CSearch::StemIndex($sites, $ID, $arContent['SEARCHABLE_CONTENT']);
				}
				$DB->Query('DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ' . $stemTask['ID']);
			}
			else
			{
				//Cancel the agent
				return '';
			}
		} while ($etime >= time());
		return 'CSearchStemTable::DelayedStemIndex();';
	}
}
