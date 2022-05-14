<?php

use Bitrix\Mail;
use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Context;
use Bitrix\Mail\Internals;
use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('mail');


class CMailClientMessageListComponent extends CBitrixComponent implements Controllerable, Main\Errorable
{
	public function configureActions()
	{
		$this->errorCollection = new Main\ErrorCollection();

		return [];
	}

	protected $componentId;
	protected $mailbox;
	protected $foldersItems;
	/** @var Mailbox */
	protected $mailboxHelper;
	/** @var Main\ErrorCollection */
	private $errorCollection;

	private function getDirsMd5WithCountOfUnseenMails($mailboxId)
	{
		$foldersWithCounter = Internals\MailCounterTable::getList([
			'runtime' => array(
				new ORM\Fields\Relations\Reference(
					'DIRECTORY',
					'Bitrix\Mail\Internals\MailboxDirectoryTable',
					[
						'=this.ENTITY_ID' => 'ref.ID',
					],
					[
						'join_type' => 'INNER',
					]
				),
			),
			'select' => [
				'UNSEEN' => 'VALUE',
				'DIR_MD5' => 'DIRECTORY.DIR_MD5'
			],
			'filter' => [
				'=DIRECTORY.MAILBOX_ID' => $mailboxId,
				'=ENTITY_TYPE' => 'DIR',
			],
		]);

		$foldersWithUnseenMails = [];

		while ($folderTable = $foldersWithCounter->fetch())
		{
			$foldersWithUnseenMails[$folderTable['DIR_MD5']] = $folderTable;
		}

		return $foldersWithUnseenMails;
	}

	public function getDirsWithUnseenMailCountersAction($mailboxId)
	{
		global $USER;
		if (!Mail\Helper\Message::isMailboxOwner($mailboxId, $USER->GetID()))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('access denied');
			return false;
		}

		$mailboxHelper = Mailbox::createInstance($mailboxId);
		$syncDirs = $mailboxHelper->getDirsHelper()->getSyncDirs();
		$defaultDirPath = $mailboxHelper->getDirsHelper()->getDefaultDirPath();
		$dirs = [];

		$dirsMd5WithCountOfUnseenMails = $this->getDirsMd5WithCountOfUnseenMails($mailboxId);

		$defaultDirPathId = null;

		foreach ($syncDirs as $dir)
		{
			$newDir = [];
			$newDir['path'] = $dir->getPath();
			$newDir['name'] = $dir->getName();
			$newDir['count'] = 0;
			$currentDirMd5WithCountsOfUnseenMails = $dirsMd5WithCountOfUnseenMails[$dir->getDirMd5()];

			if ($currentDirMd5WithCountsOfUnseenMails !== null)
			{
				$newDir['count'] = $currentDirMd5WithCountsOfUnseenMails['UNSEEN'];
			}

			if($newDir['path'] === $defaultDirPath)
			{
				$defaultDirPathId = count($dirs);
			}

			$dirs[] = $newDir;
		}

		if (empty($dirs))
		{
			$dirs = [
				[
					'count' => 0,
					'path' => $defaultDirPath,
					'name' => $defaultDirPath,
				],
			];
		}

		//inbox always on top
		array_unshift( $dirs, array_splice($dirs, $defaultDirPathId, 1)[0] );

		return $dirs;
	}

	public function getComponentId()
	{
		if ($this->componentId === null)
		{
			$this->componentId = 'mail-client-list-manager';
		}

		return $this->componentId;
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$APPLICATION->setTitle(Loc::getMessage('MAIL_CLIENT_HOME_TITLE'));

		if (!is_object($USER) || !$USER->isAuthorized())
		{
			$APPLICATION->authForm('');

			return;
		}

		$vars = $this->arParams['VARIABLES'];

		$this->arResult['MAILBOXES'] = Mail\MailboxTable::getUserMailboxes();
		$this->arResult['MAILBOX'] = [];
		$this->arResult['USER_OWNED_MAILBOXES_COUNT'] = 0;

		foreach ($this->arResult['MAILBOXES'] as $k => $item)
		{
			if (empty($item['NAME']))
			{
				$item['NAME'] = $item['EMAIL'] ? : $item['LOGIN'] ? : sprintf('#%u', $item['ID']);
			}

			$this->arResult['MAILBOXES'][$k] = $item;

			if (empty($vars['id']) && empty($this->arResult['MAILBOX']) || $vars['id'] == $item['ID'])
			{
				$this->mailbox = $this->arResult['MAILBOX'] = $item;
			}

			if ($item['USER_ID'] == $USER->getId())
			{
				$this->arResult['USER_OWNED_MAILBOXES_COUNT']++;
			}
		}

		if (empty($this->mailbox))
		{
			if (isset($_REQUEST['strict']) && 'N' == $_REQUEST['strict'])
			{
				localRedirect($this->arParams['PATH_TO_MAIL_HOME'], true);
			}
			else
			{
				showError(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));

				return;
			}
		}

		$this->mailboxHelper = Mailbox::createInstance($this->mailbox['ID']);

		if (empty($this->mailboxHelper->getDirsHelper()->getDirs()))
		{
			$this->mailboxHelper->cacheDirs();
		}

		$this->rememberCurrentMailboxId($this->mailbox['ID']);

		$this->arResult['CONFIG_SYNC_DIRS'] = $this->mailboxHelper->getDirsHelper()->getSyncDirs();

		if(empty($this->arResult['CONFIG_SYNC_DIRS']))
		{
			Mail\Helper::setMailboxUnseenCounter($this->mailbox['ID'],0);
		}

		$this->arResult['userHasCrmActivityPermission'] = Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();

		$mailboxesUnseen = \Bitrix\Mail\Helper\Message::getTotalUnseenForMailboxes(
			Main\Engine\CurrentUser::get()->getId()
		);
		foreach ($mailboxesUnseen as $mailboxId => $mailboxData)
		{
			$this->arResult['MAILBOXES'][$mailboxId]['__unseen'] = $mailboxData['UNSEEN'];
		}

		$this->arResult['GRID_ID'] = 'mail-message-list-'.$this->mailbox['ID'];
		$this->arResult['FILTER_ID'] = 'mail-message-list-'.$this->mailbox['ID'];

		$this->setFilterSettings($this->getDirsForFilter());
		$this->setFilterPresets();

		$gridOptions = new \Bitrix\Main\Grid\Options($this->arResult['GRID_ID'], $this->arResult['FILTER_PRESETS']);

		$navData = $gridOptions->getNavParams(['nPageSize' => 25]);
		$navigation = new \Bitrix\Main\UI\PageNavigation('mail-message-list');
		$navigation->setPageSize($navData['nPageSize'])->initFromUri();

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		if (preg_match('/^\s*(\d+)\s*$/', $request->getQuery($navigation->getId()), $matches))
		{
			$navigation->setCurrentPage($matches[1]);
		}

		$filterOption = new Main\UI\Filter\Options($this->arResult['FILTER_ID'], $this->arResult['FILTER_PRESETS']);

		//reset the filter when opening the page so that the "Inbox" folder is always opened
		if(!$this->request->isAjaxRequest())
		{
			$filterOption->setFilterSettings(
				\Bitrix\Main\UI\Filter\Options::TMP_FILTER,
			  [
				  'name' => '',
				  'fields' => []
			  ],
			  true,
			  false
			);

			$filterOption->save();
		}

		$filterData = $filterOption->getFilter($this->arResult['FILTER']);

		$this->arResult['currentDir'] = '';

		if (isset($filterData['DIR']) && is_scalar($filterData['DIR']))
		{
			$this->arResult['currentDir'] = $filterData['DIR'];
		}

		$filter = [
			'=MAILBOX_ID' => $this->mailbox['ID'],
		];
		$filter1 = $filter2 = [];

		$uidSubquery = new ORM\Query\Query(Mail\MailMessageUidTable::getEntity());
		$uidSubquery->addFilter('=MAILBOX_ID', new Main\DB\SqlExpression('%s'));
		$uidSubquery->addFilter('=MESSAGE_ID', new Main\DB\SqlExpression('%s'));
		$uidSubquery->addFilter('==DELETE_TIME', 0);

		$accessSubquery = new ORM\Query\Query(MessageAccessTable::getEntity());
		$accessSubquery->addFilter('=MAILBOX_ID', new Main\DB\SqlExpression('%s'));
		$accessSubquery->addFilter('=MESSAGE_ID', new Main\DB\SqlExpression('%s'));

		$closureSubquery = new ORM\Query\Query(Mail\Internals\MessageClosureTable::getEntity());
		$closureSubquery->addFilter('=PARENT_ID', new Main\DB\SqlExpression('%s'));
		$closureSubquery->addFilter('!=MESSAGE_ID', new Main\DB\SqlExpression('%s'));

		if (!empty($filterData['FILTER_APPLIED']))
		{
			if (isset($filterData['BIND']))
			{
				if ($filterData['BIND'] == MessageAccessTable::ENTITY_TYPE_NO_BIND)
				{
					$filter1['==MESSAGE_ACCESS'] = false;
					//$filter2['=MESSAGE_ACCESS.ENTITY_TYPE'] = false;
				}
				else
				{
					$accessSubquery->addFilter('=ENTITY_TYPE', $filterData['BIND']);
					$filter1['==MESSAGE_ACCESS'] = true;
					$filter2['=MESSAGE_ACCESS.ENTITY_TYPE'] = $filterData['BIND'];
				}
			}

			if (isset($filterData['ATTACHMENTS']))
			{
				if ($filterData['ATTACHMENTS'] == 'Y')
				{
					$filter2['!=ATTACHMENTS'] = '0';
					$filter1['!=ATTACHMENTS'] = '0';
				}
				elseif ($filterData['ATTACHMENTS'] == 'N')
				{
					$filter2['=ATTACHMENTS'] = '0';
					$filter1['=ATTACHMENTS'] = '0';
				}
			}

			if (isset($filterData['IS_SEEN']))
			{
				if ($filterData['IS_SEEN'] == 'Y')
				{
					$uidSubquery->addFilter('@IS_SEEN', ['Y', 'S']);
					$filter2['@MESSAGE_UID.IS_SEEN'] = ['Y', 'S'];
					$filter1['@MESSAGE_UID.IS_SEEN'] = ['Y', 'S'];
				}
				elseif ($filterData['IS_SEEN'] == 'N')
				{
					$uidSubquery->addFilter('!@IS_SEEN', ['Y', 'S']);
					$filter2['!@MESSAGE_UID.IS_SEEN'] = ['Y', 'S'];
					$filter1['!@MESSAGE_UID.IS_SEEN'] = ['Y', 'S'];
				}
			}

			if (isset($filterData['DIR']) && is_scalar($filterData['DIR']))
			{
				if ($filterData['DIR'] != '')
				{
					$uidSubquery->addFilter('=DIR_MD5', md5($filterData['DIR']));
					$filter2['=MESSAGE_UID.DIR_MD5'] = md5($filterData['DIR']);
					$filter1['=MESSAGE_UID.DIR_MD5'] = md5($filterData['DIR']);
				}
			}

			try
			{
				if (!empty($filterData['DATE_from']))
				{
					$filter['>=FIELD_DATE'] = new Main\Type\DateTime($filterData['DATE_from']);
				}

			}
			catch (\Exception $e)
			{
			}

			try
			{
				if (!empty($filterData['DATE_to']))
				{
					$filter['<=FIELD_DATE'] = new Main\Type\DateTime($filterData['DATE_to']);
				}
			}
			catch (\Exception $e)
			{
			}

			if (!empty($filterData['FIND']))
			{
				$filterKey = sprintf(
					'%sSEARCH_CONTENT',
					Mail\MailMessageTable::getEntity()->fullTextIndexEnabled('SEARCH_CONTENT') ? '*' : '*%'
				);
				$filter[$filterKey] = Mail\Helper\Message::prepareSearchString($filterData['FIND']);
			}
		}

		if (empty($filter2['=MESSAGE_UID.DIR_MD5']))
		{
			$uidSubquery->addFilter('=DIR_MD5', md5($this->mailboxHelper->getDirsHelper()->getDefaultDirPath()));
			$filter2['=MESSAGE_UID.DIR_MD5'] = md5($this->mailboxHelper->getDirsHelper()->getDefaultDirPath());
			$filter1['=MESSAGE_UID.DIR_MD5'] = md5($this->mailboxHelper->getDirsHelper()->getDefaultDirPath());
		}

		$items = Mail\MailMessageTable::getList(
			[
				'runtime' => [
					new ORM\Fields\Relations\Reference(
						'MESSAGE_UID', Bitrix\Mail\MailMessageUidTable::class, [
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					], [
							'join_type' => 'INNER',
						]
					),
					new ORM\Fields\ExpressionField(
						'MESSAGE_ACCESS', sprintf('EXISTS(%s)', $accessSubquery->getQuery()), ['MAILBOX_ID', 'ID']
					),
					new ORM\Fields\ExpressionField(
						'MESSAGE_CLOSURE', sprintf('EXISTS(%s)', $closureSubquery->getQuery()), ['ID', 'ID']
					),
				],
				'select' => ['ID'],
				'filter' => array_merge(
					$filter,
					$filter1,
					[
						'==MESSAGE_UID.DELETE_TIME' => 0,
					]
				),
				'order' => [
					'FIELD_DATE' => 'DESC',
					'ID' => 'DESC',
				],
				'offset' => $navigation->getOffset(),
				// todo delete this hack
				/* '10' - stock limit for selections of gluing duplicate messages
				(can be formed when moving letters). if you take it without a stock,
				 then in case of gluing, the "show more" button disappears */
				'limit' => $navigation->getLimit() + 1 + 10,
			]
		)->fetchAll();

		if (!empty($items))
		{
			$select = [
				'MID' => 'ID',
				'SUBJECT',
				'FIELD_FROM',
				'FIELD_TO',
				'FIELD_DATE',
				'ATTACHMENTS',
				'OPTIONS',
				'RID' => 'MESSAGE_UID.ID',
				'IS_SEEN' => 'MESSAGE_UID.IS_SEEN',
				'IS_OLD' => 'MESSAGE_UID.IS_OLD',
				'DIR_MD5' => 'MESSAGE_UID.DIR_MD5',
				'MSG_UID' => 'MESSAGE_UID.MSG_UID',
				new ORM\Fields\ExpressionField(
					'BIND', 'CONCAT(%s, "-", %s)', [
						'MESSAGE_ACCESS.ENTITY_TYPE',
						'MESSAGE_ACCESS.ENTITY_ID',
					]
				),
			];

			if (Main\Loader::includeModule('crm'))
			{
				$select['CRM_ACTIVITY_OWNER'] = new ORM\Fields\ExpressionField(
					'CRM_ACTIVITY_OWNER', 'CONCAT(%s, "-", %s)', [
											'MESSAGE_ACCESS.CRM_ACTIVITY.OWNER_TYPE_ID',
											'MESSAGE_ACCESS.CRM_ACTIVITY.OWNER_ID',
										]
				);
				$select['CRM_ACTIVITY_OWNER_TYPE_ID'] = 'MESSAGE_ACCESS.CRM_ACTIVITY.OWNER_TYPE_ID';
				$select['CRM_ACTIVITY_OWNER_ID'] = 'MESSAGE_ACCESS.CRM_ACTIVITY.OWNER_ID';
			}

			$res = Mail\MailMessageTable::getList(
				[
					'runtime' => [
						new ORM\Fields\Relations\Reference(
							'MESSAGE_UID', Mail\MailMessageUidTable::class, [
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
							'=this.ID' => 'ref.MESSAGE_ID',
						], [
								'join_type' => 'INNER',
							]
						),
						new ORM\Fields\Relations\Reference(
							'MESSAGE_ACCESS', MessageAccessTable::class, [
												'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
												'=this.ID' => 'ref.MESSAGE_ID',
											]
						),
					],
					'select' => $select,
					'filter' => array_merge(
						[
							'@ID' => array_column($items, 'ID'),
						],
						$filter,
						$filter2
					),
					'order' => [
						'FIELD_DATE' => 'DESC',
						'MID' => 'DESC',
						'MSG_UID' => 'ASC',
					],
				]
			);

			$items = [];
			while ($item = $res->fetch())
			{
				$item['BIND'] = (array)$item['BIND'];
				$item['CRM_ACTIVITY_OWNER'] = (array)@$item['CRM_ACTIVITY_OWNER'];

				if (array_key_exists($item['MID'], $items))
				{
					$item['IS_SEEN'] = max($items[$item['MID']]['IS_SEEN'], $item['IS_SEEN']);
					$item['BIND'] = array_unique(
						array_filter(
							array_merge(
								$items[$item['MID']]['BIND'],
								$item['BIND']
							)
						)
					);
					$item['CRM_ACTIVITY_OWNER'] = array_unique(
						array_filter(
							array_merge(
								$items[$item['MID']]['CRM_ACTIVITY_OWNER'],
								$item['CRM_ACTIVITY_OWNER']
							)
						)
					);
				}
				$items[$item['MID']] = $item;
			}
		}

		$this->arResult['gridActionsData'] = $this->getGridActionsData();

		$this->arResult['ROWS'] = $this->getRows($items, $navigation);
		$this->arResult['NAV_OBJECT'] = $navigation;
		$this->arResult['DIRECTORY_HIERARCHY_WITH_UNSEEN_MAIL_COUNTERS'] = $this->getDirectoryHierarchyForContextMenuAction($this->mailbox['ID']);
		$this->arResult['DIRS_WITH_UNSEEN_MAIL_COUNTERS'] = $this->getDirsWithUnseenMailCountersAction($this->mailbox['ID']);

		if ($this->request->getPost('errorMessage'))
		{
			$this->arResult["MESSAGES"][] = [
				"TYPE" => \Bitrix\Main\Grid\MessageType::ERROR,
				"TITLE" => Loc::getMessage('MAIL_CLIENT_AJAX_ERROR'),
				"TEXT" => \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($this->request->getPost('errorMessage')),
			];
		}

		$this->arResult['defaultDir'] = $this->mailboxHelper->getDirsHelper()->getDefaultDirPath();
		$this->arResult['spamDir'] = $this->mailboxHelper->getDirsHelper()->getSpamPath();
		$this->arResult['trashDir'] = $this->mailboxHelper->getDirsHelper()->getTrashPath();
		$this->arResult['outcomeDir'] = $this->mailboxHelper->getDirsHelper()->getOutcomePath();
		$this->arResult['draftsDir'] = $this->mailboxHelper->getDirsHelper()->getDraftsPath();

		$this->arResult['foldersItems'] = $this->getDirectoryHierarchyForContextMenuAction($this->mailbox['ID']);


		$email = $this->mailbox['NAME'];
		$pieces = explode("@", $email);
		$name = $pieces[0];
		$domain ='';

		if(count($pieces)>1)
		{
			$domain = '@'.$pieces[1];
		}

		$this->arResult['MAILBOX_NAME'] = $name;
		$this->arResult['MAILBOX_DOMAIN'] = $domain;

		$this->arResult['invisibleDirsToCounters'] = [
			$this->arResult['spamDir'],
			$this->arResult['trashDir'],
			$this->arResult['outcomeDir'],
			$this->arResult['draftsDir']
		];

		$this->arResult['MAX_ALLOWED_CONNECTED_MAILBOXES'] = Mail\Helper\LicenseManager::getUserMailboxesLimit();

		$this->includeComponentTemplate();
	}

	/**
	 * @param $items
	 * @param \Bitrix\Main\UI\PageNavigation $navigation
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws Main\LoaderException
	 */
	private function getRows($items, $navigation)
	{
		$rows = [];
		$avatarConfigs = $this->getAvatarConfigs($items);

		foreach ($items as $index => $item)
		{
			$onclickOpenMessageViewMethod = 'top.BX.SidePanel.Instance.open("'.
			 \CComponentEngine::makePathFromTemplate(
				 $this->arParams['PATH_TO_MAIL_MSG_VIEW'],
				 ['id' => $item['MID']]
			 ).'",{printable: true})';

			$onclickEventOpenMessageMethod = 'BX.onCustomEvent(
			`mail:openMessageForView`,
			[{
				id: `'.htmlspecialcharsbx($item['MID']).'`
			}]); ';

			if (count($rows) >= $navigation->getLimit())
			{
				$this->arResult['ENABLE_NEXT_PAGE'] = true;
				break;
			}

			$item['ID'] = $item['RID'].'-'.$this->mailbox['ID'];

			$columns = [];
			$dataNow = localtime((time() + \CTimeZone::getOffset()),true);
			$today = mktime(0, 0, 0, $dataNow['tm_mon']+1, $dataNow['tm_mday'], $dataNow['tm_year']+1900);
			$fieldDateInTimeStamp = makeTimeStamp($item['FIELD_DATE']);
			$dateDisplayFormat = false;

			$titleDateFormat = Context::getCurrent()->getCulture()->getFullDateFormat()."&#013;H:i:s";

			if($fieldDateInTimeStamp >= $today )
			{
				$dateDisplayFormat = Context::getCurrent()->getCulture()->getShortTimeFormat();
			}
			else
			{
				$dateDisplayFormat = Context::getCurrent()->getCulture()->getDayShortMonthFormat();
			}

			$columns['DATE'] = sprintf(
				'<span class="mail-msg-list-cell-%u %s" title="%s">%s</span>',
				$item['ID'],
				!in_array($item['IS_SEEN'], ['Y', 'S']) ? 'mail-msg-list-cell-unseen' : '',
				FormatDate($titleDateFormat, $fieldDateInTimeStamp, (time() + \CTimeZone::getOffset())),
				('<span class="mail-msg-from-title">'.FormatDate($dateDisplayFormat, $fieldDateInTimeStamp, (time() + \CTimeZone::getOffset())).'</span>')
			);

			$columns['SUBJECT'] = htmlspecialcharsbx(
				$item['SUBJECT'] ? : Loc::getMessage('MAIL_MESSAGE_EMPTY_SUBJECT_PLACEHOLDER')
			);

			$from = new \Bitrix\Main\Mail\Address($item['FIELD_FROM']);
			if ($from->validate())
			{
				//Outcome message
				if ($from->getEmail() == $this->mailbox['EMAIL'] && !empty($item['FIELD_TO']))
				{
					$columns['FROM'] = htmlspecialcharsbx($item['FIELD_TO']);

					//'email@email' => 'email@domain'
					//'Name <email@domain>, Name2 <email2@domain2>' => 'Name <email@domain'
					$fromString = current(preg_split("/>,?/", $item['FIELD_TO']));

					//'email@email' => 'email@domain'
					//'Name <email@domain' => 'Name <email@domain>'
					if(preg_match("/</", $fromString))
					{
						$fromString.='>';
					}
					$from = new \Bitrix\Main\Mail\Address($fromString);
				}
			}

			$avatarParams = !empty($from->getEmail()) && !empty($avatarConfigs[$from->getEmail()])
				? $avatarConfigs[$from->getEmail()] : [];

			if ($from->validate())
			{
				$columns['FROM'] = sprintf(
					$this->getSenderColumnCell($avatarParams).'<a onclick=\''.$onclickEventOpenMessageMethod.$onclickOpenMessageViewMethod.'\' class="mail-msg-from-title" title="%s">%s</a>',
					htmlspecialcharsbx((!empty($from->getName())?$from->getName().' / ':'').$from->getEmail()),
					htmlspecialcharsbx($from->getName() ? $from->getName() : $from->getEmail())
				);
			}

			$columns['SUBJECT'] = sprintf(
				'<a class="mail-msg-list-subject" onclick=\''.$onclickEventOpenMessageMethod.$onclickOpenMessageViewMethod.'\' title="%s">%s</a>',
				$columns['SUBJECT'],
				$columns['SUBJECT']
			);

			if ($item['OPTIONS']['attachments'] > 0 || $item['ATTACHMENTS'] > 0)
			{
				$columns['SUBJECT'] .= '<span class="mail-msg-list-attach-icon" title="'.
					Loc::getMessage('MAIL_MESSAGE_LIST_ATTACH_ICON_HINT').
				'"></span>';
			}

			$dir = $this->mailboxHelper->getDirsHelper()->getDirByHash($item['DIR_MD5']);

			$isDisabled = ($item['MSG_UID'] == 0);
			$jsFromClassNames = $dir && $dir->isSpam() ? 'js-spam ' : '';
			$jsFromClassNames .= $isDisabled ? 'js-disabled ' : '';
			$columns['FROM'] = sprintf(
				'<span data-message-id="%u" class="'.
				$jsFromClassNames.
				'mail-name-block mail-msg-list-cell-%u mail-msg-list-cell-nowrap mail-msg-list-cell-flex %s">%s</span>',
				$item['MID'],
				$item['MID'],
				!in_array($item['IS_SEEN'], ['Y', 'S']) ? 'mail-msg-list-cell-unseen' : '',
				$columns['FROM']
			);

			$columns['SUBJECT'] = sprintf(
				'<span class="mail-title-block mail-msg-list-cell-%u %s %s mail-msg-list-cell-flex">%s</span>',
				$item['ID'],
				!in_array($item['IS_SEEN'], ['Y', 'S']) ? 'mail-msg-list-cell-unseen' : '',
				$item['IS_OLD'] === 'Y' ? 'mail-msg-list-cell-old' : '',
				$columns['SUBJECT']
			);

			$taskHref = \CHTTP::urlAddParams(
				\CComponentEngine::makePathFromTemplate(
					$this->arParams['PATH_TO_USER_TASKS_TASK'],
					[
						'action' => 'edit',
						'task_id' => '0',
					]
				),
				[
					'TITLE' => rawurlencode(
						Loc::getMessage('MAIL_MESSAGE_TASK_TITLE', ['#SUBJECT#' => $item['SUBJECT']])
					),
					'UF_MAIL_MESSAGE' => (int)$item['MID'],
				]
			);

			$postHref = \CHTTP::urlAddParams(
				\CComponentEngine::makePathFromTemplate(
					$this->arParams['PATH_TO_USER_BLOG_POST_EDIT'],
					[
						'post_id' => '0',
					]
				),
				[
					'TITLE' => rawurlencode(
						Loc::getMessage(
							'MAIL_MESSAGE_POST_TITLE',
							['#SUBJECT#' => $item['SUBJECT']]
						)
					),
					'UF_MAIL_MESSAGE' => (int)$item['MID'],
				]
			);

			$bind = '<span class="mail-ui-binding-data js-bind-'.$item['MID'].'" message-id="'.$item['ID'].'" message-simple-id="'.$item['MID'].'" ';
			$bindClose ='></span>';

			$columns['CRM_BIND'] = 	$bind;
			$columns['TASK_BIND'] = $bind.'create-href="'.\CUtil::jsEscape($taskHref).'" ';
			$columns['CHAT_BIND'] = $bind;
			$columns['POST_BIND'] = $bind.'create-href="'. \CUtil::jsEscape($postHref).'" ';
			$columns['MEETING_BIND'] = $bind;

			if ($item['BIND'])
			{
				foreach ((array)$item['BIND'] as $bindWithId)
				{
					[$bindEntityType, $bindEntityId] = explode('-', $bindWithId);
					$bindId = $bind.'bind-id ="'.$bindEntityId.'" ';

					switch ($bindEntityType)
					{
						case MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT:
							$bindId .= 'bind-href ="'.\CComponentEngine::makePathFromTemplate(
								$this->arParams['PATH_TO_USER_CALENDAR_EVENT'],
								[
									'event_id' => $bindEntityId,
								]
							).'"';
							$columns['MEETING_BIND'] = $bindId;
							break;
						case MessageAccessTable::ENTITY_TYPE_IM_CHAT:
							$bindId .= 'bind-href ="'.\CComponentEngine::makePathFromTemplate(
								$this->arParams['PATH_TO_USER_IM_CHAT'],
								[
									'chat_id' => $bindEntityId,
								]
							).'"';
							$columns['CHAT_BIND'] = $bindId;
							break;
						case MessageAccessTable::ENTITY_TYPE_TASKS_TASK:
							$bindId .= 'bind-href ="'.\CComponentEngine::makePathFromTemplate(
								$this->arParams['PATH_TO_USER_TASKS_TASK'],
								[
									'action' => 'view',
									'task_id' => $bindEntityId,
								]
							).'"';
							$columns['TASK_BIND'] = $bindId;
							break;
						case MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY:
							[$ownerTypeId, $ownerId] = explode('-', end($item['CRM_ACTIVITY_OWNER']));
							$bindId .= (Main\Loader::includeModule('crm')) ? ('bind-href ="'.\CCrmOwnerType::getEntityShowPath($ownerTypeId, $ownerId).'"') : '';
							$columns['CRM_BIND'] = $bindId;
							break;
						case MessageAccessTable::ENTITY_TYPE_BLOG_POST:
							$bindId .= 'bind-href ="'.\CComponentEngine::makePathFromTemplate(
								$this->arParams['PATH_TO_USER_BLOG_POST'],
								[
									'post_id' => $bindEntityId,
								]
							).'"';
							$columns['POST_BIND'] = $bindId;
							break;
					}
				}
			}

			$this->arResult['ERRORS']=[];
			$this->arResult['ERRORS']['CRM']=[];
			$this->arResult['ERRORS']['CALENDAR']=[];

			if(!ModuleManager::isModuleInstalled('crm'))
			{
				$columns['CRM_BIND'] .= 'error-type="crm-install-error" ';
				$this->arResult['ERRORS']['CRM'][] = "crm-install-error";
			}
			elseif(!$this->arResult['userHasCrmActivityPermission'])
			{
				$columns['CRM_BIND'] .= 'error-type="crm-install-permission-error" ';
			}

			if(!ModuleManager::isModuleInstalled('calendar'))
			{
				$columns['MEETING_BIND'] .= 'error-type="calendar-install-error" ';
			}

			if(!ModuleManager::isModuleInstalled('tasks'))
			{
				$columns['TASK_BIND'] .= 'error-type="tasks-install-error" ';
			}

			if(!ModuleManager::isModuleInstalled('im'))
			{
				$columns['CHAT_BIND'] .= 'error-type="chat-install-error" ';
			}

			if(!ModuleManager::isModuleInstalled('socialnetwork'))
			{
				$columns['POST_BIND'] .= 'error-type="socialnetwork-install-error" ';
			}

			$columns['CRM_BIND'] .= ' bind-type ="crm" '.$bindClose;
			$columns['TASK_BIND'] .= ' bind-type ="task" '.$bindClose;
			$columns['CHAT_BIND'] .= ' bind-type ="chat" '.$bindClose;
			$columns['POST_BIND'] .= ' bind-type ="post" '.$bindClose;
			$columns['MEETING_BIND'] .= ' bind-type ="meeting" '.$bindClose;

			$rows[$item['ID']] = [
				'id' => $item['ID'],
				'data' => $item,
				'columns' => $columns,
				'attrs' => [
					'unseen' => !in_array($item['IS_SEEN'], ['Y', 'S']) ? 'true' : 'false',
				],
			];

			$rows[$item['ID']]['actions'] = [
				[
					'id' => $this->arResult['gridActionsData']['view']['id'],
					'text' => $this->arResult['gridActionsData']['view']['text'],
					'title' => $this->arResult['gridActionsData']['view']['title'],
					'icon' => $this->arResult['gridActionsData']['view']['icon'],
					'default' => true,
					'onclick' => $onclickOpenMessageViewMethod,
					'hideInActionPanel' => true,
					'iconOnly' => true,
				],
				[
					'id' => $this->arResult['gridActionsData']['notRead']['id'],
					'html' => '<span data-role="not-read-action">'.
						$this->arResult['gridActionsData']['notRead']['text'].
						'</span>',
					'text' => '<span data-role="not-read-action">'.
						$this->arResult['gridActionsData']['notRead']['text'].
						'</span>',
					'title' => $this->arResult['gridActionsData']['notRead']['title'],
					'icon' => $this->arResult['gridActionsData']['notRead']['icon'],
					'disabled' => $isDisabled,
					'className' => "menu-popup-no-icon",
					'onclick' => "BX.Mail.Client.Message.List['".
						CUtil::JSEscape($this->getComponentId()).
						"'].onReadClick('{$item['ID']}');",
					'iconOnly' => true,
				],
				[
					'id' => $this->arResult['gridActionsData']['read']['id'],
					'html' =>'<span data-role="read-action">'.
						$this->arResult['gridActionsData']['read']['text'].
						'</span>',
					'text' =>'<span data-role="read-action">'.
						$this->arResult['gridActionsData']['read']['text'].
						'</span>',
					'title' => $this->arResult['gridActionsData']['read']['title'],
					'icon' => $this->arResult['gridActionsData']['read']['icon'],
					'disabled' => $isDisabled,
					'className' => "menu-popup-no-icon",
					'onclick' => "BX.Mail.Client.Message.List['".
						CUtil::JSEscape($this->getComponentId()).
						"'].onReadClick('{$item['ID']}');",
					'iconOnly' => true,
				],
				[
					'id' => $this->arResult['gridActionsData']['move']['id'].$item['ID'],
					'icon' => $this->arResult['gridActionsData']['move']['icon'],
					'disabled' => $isDisabled,
					'text' => $this->arResult['gridActionsData']['move']['text'],
					'title' => $this->arResult['gridActionsData']['move']['title'],
					'items' => $this->getDirectoryHierarchyForContextMenuAction($this->mailbox['ID']),
					'gridRowId' => $item['ID'],
					'iconOnly' => true,
				],
				[
					'id' => $this->arResult['gridActionsData']['notSpam']['id'],
					'icon' => $this->arResult['gridActionsData']['notSpam']['icon'],
					'html' => '<span data-role="not-spam-action">'.
						$this->arResult['gridActionsData']['notSpam']['text'].
					'</span>',
					'text' => '<span data-role="not-spam-action">'.
						$this->arResult['gridActionsData']['notSpam']['text'].
					'</span>',
					'title' => $this->arResult['gridActionsData']['notSpam']['title'],
					'disabled' => $isDisabled,
					'onclick' => "BX.Mail.Client.Message.List['".
						CUtil::JSEscape($this->getComponentId()).
					"'].onSpamClick('{$item['ID']}');",
					'iconOnly' => true,
				],
				[
					'id' => $this->arResult['gridActionsData']['spam']['id'],
					'icon' => $this->arResult['gridActionsData']['spam']['icon'],
					'html' => '<span data-role="spam-action">'.
						$this->arResult['gridActionsData']['spam']['text'].
					'</span>',
					'text' => '<span data-role="spam-action">'.
						$this->arResult['gridActionsData']['spam']['text'].
					'</span>',
					'title' => $this->arResult['gridActionsData']['spam']['title'],
					'disabled' => $isDisabled,
					'onclick' => "BX.Mail.Client.Message.List['".
						CUtil::JSEscape($this->getComponentId()).
					"'].onSpamClick('{$item['ID']}');",
					'iconOnly' => true,
				],
				[
					'id' => $this->arResult['gridActionsData']['delete']['id'],
					'icon' => $this->arResult['gridActionsData']['delete']['icon'],
					'text' => $this->arResult['gridActionsData']['delete']['text'],
					'title' => $this->arResult['gridActionsData']['delete']['title'],
					'disabled' => $isDisabled,
					'onclick' => "BX.Mail.Client.Message.List['".
						CUtil::JSEscape($this->getComponentId()).
						"'].onDeleteClick('{$item['ID']}');",
					'iconOnly' => true,
				],
				[
					'id' => 'separator',
					'additionalClassForPanel' => 'mail-separator',
				],
			];

			$crmOnClickAction = "";

			if(!ModuleManager::isModuleInstalled('crm'))
			{
				$crmOnClickAction = "BX.Mail.Client.Item.showError('crm-install-error');";
			}
			else if(!$this->arResult['userHasCrmActivityPermission'])
			{
				$crmOnClickAction = "BX.Mail.Client.Item.showError('crm-install-permission-working-error');";
			}
			else
			{
				$crmOnClickAction = "BX.Mail.Client.Message.List['".
				CUtil::JSEscape($this->getComponentId()).
				"'].onCrmClick('{$item['ID']}');";
			}

			$rows[$item['ID']]['actions'] = array_merge(
				$rows[$item['ID']]['actions'],
				[
					[
						'id' => $this->arResult['gridActionsData']['addToCrm']['id'],
						'html' => '<span data-role="crm-action">'.
							$this->arResult['gridActionsData']['addToCrm']['text'].
						'</span>',
						'text' => '<span data-role="crm-action">'.
							$this->arResult['gridActionsData']['addToCrm']['text'].
						'</span>',
						'title' => $this->arResult['gridActionsData']['addToCrm']['title'],

						'onclick' => $crmOnClickAction,
						'additionalClassForPanel' => 'mail-crm-action',
					],
					[
						'id' => $this->arResult['gridActionsData']['excludeFromCrm']['id'],
						'html' => '<span data-role="not-crm-action">'.
							$this->arResult['gridActionsData']['excludeFromCrm']['text'].
						'</span>',
						'text' => '<span data-role="not-crm-action">'.
							$this->arResult['gridActionsData']['excludeFromCrm']['text'].
						'</span>',
						'title' => $this->arResult['gridActionsData']['excludeFromCrm']['title'],

						'onclick' => $crmOnClickAction,

						'additionalClassForPanel' => 'mail-not-crm-action',
					],
				]
			);

			$rows[$item['ID']]['actions'] = array_merge(
				$rows[$item['ID']]['actions'],
				[
					[
						'id' => $this->arResult['gridActionsData']['task']['id'],
						'text' => $this->arResult['gridActionsData']['task']['text'],
						'title' => $this->arResult['gridActionsData']['task']['title'],

						'href' => !ModuleManager::isModuleInstalled('tasks') ? '' : $taskHref,

						'onclick' => !ModuleManager::isModuleInstalled('tasks') ?
							"BX.Mail.Client.Item.showError('tasks-install-error');" :
							"top.BX.SidePanel.Instance.open('".
							\CUtil::jsEscape($taskHref).
							"', {'cacheable': false, 'loader': 'task-new-loader'}); if (event = event || window.event) event.preventDefault(); ",

						'dataset' => ['sliderIgnoreAutobinding' => true],
						'additionalClassForPanel' => 'mail-task',
					],
					[
						'id' => $this->arResult['gridActionsData']['discuss']['id'],
						'text' => $this->arResult['gridActionsData']['discuss']['text'],
						'title' => $this->arResult['gridActionsData']['discuss']['title'],
						'additionalClassForPanel' => 'mail-discuss',
						'items' => [
								[
									'id' => $this->arResult['gridActionsData']['chat']['id'],
									'text' => $this->arResult['gridActionsData']['chat']['text'],
									'title' => $this->arResult['gridActionsData']['chat']['title'],
									'onclick' => !ModuleManager::isModuleInstalled('im') ?
										"BX.Mail.Client.Item.showError('chat-install-error');" :
										'BX.Mail.Secretary.getInstance('.htmlspecialcharsbx($item['MID']).').openChat()',
								],
							[
								'id' => $this->arResult['gridActionsData']['liveFeed']['id'],
								'text' => $this->arResult['gridActionsData']['liveFeed']['text'],
								'title' => $this->arResult['gridActionsData']['liveFeed']['title'],
								'href' => !ModuleManager::isModuleInstalled('socialnetwork') ? '' : $postHref,

								'onclick' => !ModuleManager::isModuleInstalled('socialnetwork') ?
									"BX.Mail.Client.Item.showError('socialnetwork-install-error');" :
									"top.BX.SidePanel.Instance.open('".
									\CUtil::jsEscape($postHref).
									"', {'cacheable': false, 'loader': 'socialnetwork:userblogposteditex'}); if (event = event || window.event) event.preventDefault(); ",

								'dataset' => ['sliderIgnoreAutobinding' => true],
							],
						]
					],
					[
						'id' => $this->arResult['gridActionsData']['event']['id'],
						'text' => $this->arResult['gridActionsData']['event']['text'],
						'additionalClassForPanel' => 'mail-meeting',
						'title' => $this->arResult['gridActionsData']['event']['title'],

						'onclick' => !ModuleManager::isModuleInstalled('calendar') ?
							"BX.Mail.Client.Item.showError('calendar-install-error');" :
							'BX.Mail.Secretary.getInstance('.htmlspecialcharsbx($item['MID']).').openCalendarEvent()',
					],
					[
						'id' => $this->arResult['gridActionsData']['deleteImmediately']['id'],
						'text' => $this->arResult['gridActionsData']['deleteImmediately']['text'],
						'title' => $this->arResult['gridActionsData']['deleteImmediately']['title'],
						'disabled' => ($this->arResult['currentDir'] !== '[Gmail]/All Mail') ? $isDisabled : true,
						'onclick' => "BX.Mail.Client.Message.List['".
									 CUtil::JSEscape($this->getComponentId()).
									 "'].onDeleteImmediately('{$item['ID']}');",
						'hiddenInPanel' => true,
					],
				]
			);
		}

		return $rows;
	}

	/**
	 * @param $emails
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getAvatarConfigs($items)
	{
		$emails = [];
		foreach ($items as $key => $element)
		{
			foreach (['FIELD_FROM', 'FIELD_TO'] as $column)
			{
				if ((isset($element[$column]) || $element[$column]))
				{
					$emails[$element[$column]] = $element[$column];
				}
			}
		}
		$emails = array_values($emails);
		$configs = (new Mail\MessageView\AvatarManager(
			Main\Engine\CurrentUser::get()->getId()
		))->getAvatarParamsFromEmails($emails);

		return $configs;
	}

	private function getGridActionsData()
	{
		return [
			'view' => [
				'id' => 'view',
				'icon' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_open_mail.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_VIEW'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_VIEW'),
			],
			'delete' => [
				'id' => 'delete',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-actionpanel_remove.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_DELETE'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_DELETE'),
			],
			'deleteImmediately' => [
				'id' => 'deleteImmediately',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-actionpanel_remove.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_DELETE_IMMEDIATELY'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_DELETE_IMMEDIATELY'),
			],
			'spam' => [
				'id' => 'spam',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-actionpanel_lock.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_SPAM'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_SPAM'),
			],
			'notSpam' => [
				'id' => 'notSpam',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-actionpanel_not_spam.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_NOT_SPAM'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_NOT_SPAM'),
			],
			'addToCrm' => [
				'id' => 'addToCrm',
				'icon' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_save_to_crm.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_CREATE_CRM_BTN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_CREATE_CRM_BTN_TITLE'),
			],
			'excludeFromCrm' => [
				'id' => 'excludeFromCrm',
				'icon' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_exclude.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_CREATE_CRM_EXCLUDE_BTN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_CREATE_CRM_EXCLUDE_BTN'),
			],
			'task' => [
				'id' => 'task',
				'icon' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_create.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_CREATE_TASK_BTN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_CREATE_TASK_BTN_TITLE'),
			],
			'event' => [
				'id' => 'event',
				'icon' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_event.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_CREATE_EVENT_BTN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_CREATE_EVENT_BTN_TITLE'),
			],
			'liveFeed' => [
				'id' => 'liveFeed',
				'text' => Loc::getMessage('MAIL_MESSAGE_CREATE_FEED_POST'),
				'title' => Loc::getMessage('MAIL_MESSAGE_CREATE_FEED_POST_TITLE'),
			],
			'discuss' => [
				'id' => 'discuss',
				'icon' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_discuss_in_chat.svg',
				'text' => Loc::getMessage('MAIL_PANEL_DISCUSS_BTN'),
				'title' => Loc::getMessage('MAIL_PANEL_DISCUSS_BTN_TITLE'),
			],
			'chat' => [
				'id' => 'chat',
				'text' => Loc::getMessage('MAIL_MESSAGE_CREATE_IM_BTN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_CREATE_IM_BTN_TITLE'),
			],
			'read' => [
				'id' => 'read',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-open-envelope.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_SEEN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_SEEN_TITLE'),
			],
			'notRead' => [
				'id' => 'notRead',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-closed-envelope.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_UNSEEN'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_UNSEEN_TITLE'),
			],
			'move' => [
				'id' => ':move:',
				'icon' => '/bitrix/images/mail/mailservice-icon/mail-actionpanel_move_to_folder.svg',
				'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_MOVE'),
				'title' => Loc::getMessage('MAIL_MESSAGE_LIST_BTN_MOVE'),
			],
		];
	}

	private function getSenderColumnCell($avatarParams)
	{
		global $APPLICATION;
		static $contactAvatars = [];

		$email = !empty($avatarParams['email']) ? $avatarParams['email'] : 'default';
		$name = !empty($avatarParams['name']) ? $avatarParams['name'] : 'default';
		$key = md5($email.$name);

		if (!array_key_exists($key, $contactAvatars))
		{
			ob_start();
			$APPLICATION->includeComponent(
				'bitrix:mail.contact.avatar',
				'',
				$avatarParams,
				null,
				[
					'HIDE_ICONS' => 'Y',
				]
			);
			$contactAvatars[$key] = ob_get_clean();
		}

		return $contactAvatars[$key];

	}

	private function getDirsForFilter()
	{
		$syncDirs = $this->mailboxHelper->getDirsHelper()->getSyncDirs();
		$defaultDirPath = $this->mailboxHelper->getDirsHelper()->getDefaultDirPath();
		$dirs = [];

		foreach ($syncDirs as $dir)
		{
			if($dir->isHiddenSystemFolder())
			{
				continue;
			}
			
			$dirPath = '';

			if ($dir->getPath() !== $defaultDirPath)
			{
				$dirPath = $dir->getPath();
			}

			$dirs[$dirPath] = $dir->getName();
		}

		if (empty($dirs))
		{
			$dirs = [
				'' => '',
			];
		}

		return $dirs;
	}

	private function setFilterSettings($dirsForFilter)
	{
		$dirsForFilter = array('' => $dirsForFilter['']) + $dirsForFilter;

		$this->arResult['FILTER'] = [
			[
				'id' => 'DIR',
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_DIR'),
				'type' => 'list',
				'params' => ['multiple' => 'N'],
				'items' => $dirsForFilter,
				'default' => true,
				'strict' => true,
			],
			[
				'id' => 'DATE',
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_DATE'),
				'type' => 'date',
				'default' => true,
				'exclude' => [
					\Bitrix\Main\UI\Filter\DateType::TOMORROW,
					\Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
					\Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
					\Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
				],
			],
			[
				'id' => 'IS_SEEN',
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_IS_SEEN'),
				'type' => 'list',
				'params' => ['multiple' => 'N'],
				'items' => [
					'Y' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_OPTION_Y'),
					'N' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_OPTION_N'),
				],
				'default' => true,
			],
			[
				'id' => 'BIND',
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_BIND'),
				'type' => 'list',
				'default' => true,
				'params' => ['multiple' => 'N'],
				'items' => [
					MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY => Loc::getMessage(
						'MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_CRM'
					),
					MessageAccessTable::ENTITY_TYPE_TASKS_TASK => Loc::getMessage(
						'MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_TASK'
					),
					MessageAccessTable::ENTITY_TYPE_IM_CHAT => Loc::getMessage(
						'MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_CHAT'
					),
					MessageAccessTable::ENTITY_TYPE_BLOG_POST => Loc::getMessage(
						'MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_POST'
					),
					MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT => Loc::getMessage(
						'MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_CALENDAR_EVENT'
					),
					MessageAccessTable::ENTITY_TYPE_NO_BIND => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_OPTION_N'),
				],
			],
			[
				'id' => 'ATTACHMENTS',
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_ATTACHMENTS'),
				'type' => 'list',
				'default' => true,
				'params' => ['multiple' => 'N'],
				'items' => [
					'Y' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_OPTION_Y'),
					'N' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_OPTION_N'),
				]
			]
		];
	}

	private function setFilterPresets()
	{
		$presetBindings = [
			'bindTask' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_TASK'),
				'fields' => [
					'BIND' => MessageAccessTable::ENTITY_TYPE_TASKS_TASK,
				],
			],
			'bindCrm' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_CRM'),
				'fields' => [
					'BIND' => MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY,
				],
			],
			'bindPost' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_BIND_POST'),
				'fields' => [
					'BIND' => MessageAccessTable::ENTITY_TYPE_BLOG_POST,
				],
			],
		];
		$presetDirs = [
			'income' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_INCOME'),
				'fields' => [
					'DIR' => $this->mailboxHelper->getDirsHelper()->getIncomePath(),
				],
			],
			'outcome' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_OUTCOME'),
				'fields' => [
					'DIR' => $this->mailboxHelper->getDirsHelper()->getOutcomePath(),
				],
			],
			'spam' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_SPAM'),
				'fields' => [
					'DIR' => $this->mailboxHelper->getDirsHelper()->getSpamPath(),
				],
			],
			'trash' => [
				'name' => Loc::getMessage('MAIL_MESSAGE_LIST_FILTER_PRESET_TRASH'),
				'fields' => [
					'DIR' => $this->mailboxHelper->getDirsHelper()->getTrashPath(),
				],
			],
		];
		$defaultPresetKeys = array_keys(array_merge($presetDirs, $presetBindings));
		$defaultPresetKeys[] = '';
		$this->arResult['FILTER_PRESETS'] = [];
		$defaultPreset = [];
		$defaultDirPath = $this->mailboxHelper->getDirsHelper()->getDefaultDirPath();
		foreach ($presetDirs as $presetKey => $preset)
		{
			$dirPath = $preset['fields']['DIR'];
			$dir = $this->mailboxHelper->getDirsHelper()->getDirByPath($dirPath);

			if ('' == $dirPath || $dir === null)
			{
				continue;
			}

			if ($dir->isSync())
			{
				if ($dir->getPath() === $defaultDirPath)
				{
					if (empty($defaultPreset))
					{
						$preset['fields']['DIR'] = '';
						$preset['default'] = true;
						$defaultPreset[$presetKey] = $preset;
					}

					continue;
				}

				$this->arResult['FILTER_PRESETS'][$presetKey] = $preset;
			}
		}
		if (!empty($defaultPreset))
		{
			$keys = array_keys($defaultPreset);
			$values = array_values($defaultPreset);
			$this->arResult['FILTER_PRESETS'] = array_merge(
				[array_pop($keys) => array_pop($values)],
				$this->arResult['FILTER_PRESETS']
			);
		}
		$this->arResult['FILTER_PRESETS'] = $this->arResult['FILTER_PRESETS'] + $presetBindings;
		$currentAllowedPresetKeys = array_keys($this->arResult['FILTER_PRESETS']);
		$filterOptions = new \Bitrix\Main\UI\Filter\Options(
			$this->arResult['FILTER_ID'], $this->arResult['FILTER_PRESETS']
		);
		$userPresets = $filterOptions->getPresets();
		foreach ($userPresets as $presetUserKey => $userPreset)
		{
			if (in_array($presetUserKey, $defaultPresetKeys, true))
			{
				$userPresets[$presetUserKey]['fields']['DIR'] = $this->arResult['FILTER_PRESETS'][$presetUserKey]['fields']['DIR'];
				$userPresets[$presetUserKey]['name'] = $this->arResult['FILTER_PRESETS'][$presetUserKey]['name'];
				if (!in_array($presetUserKey, $currentAllowedPresetKeys, true))
				{
					unset($userPresets[$presetUserKey]);
				}
			}
			elseif ('' != $userPreset['fields']['DIR'])
			{
				$dir = $this->mailboxHelper->getDirsHelper()->getDirByPath($userPreset['fields']['DIR']);

				if (!$dir)
				{
					unset($userPresets[$presetUserKey]);
				}
				elseif ($dir && !$dir->isSync())
				{
					unset($userPresets[$presetUserKey]);
				}
			}
		}
		$curPresets = $filterOptions->getPresets();
		if ($this->arrayDiffRecursive($curPresets, $userPresets))
		{
			$filterOptions->setPresets($userPresets);
			$filterOptions->save();
		}
	}

	public function getDirectoryHierarchyForContextMenuAction($mailboxId)
	{
		//so as not to rebuild twice on one hit
		//called repeatedly during component build

		if (empty($this->foldersItems))
		{
			$directoriesWithNumberOfUnreadMessages = $this->getDirsMd5WithCountOfUnseenMails($mailboxId);

			$this->foldersItems = $this->buildDirectoryTreeForContextMenu($directoriesWithNumberOfUnreadMessages);
		}

		return $this->foldersItems;
	}

	private function buildDirectoryTreeForContextMenu($directoriesWithNumberOfUnreadMessages)
	{
		$flat = [];
		$list = [];
		$dirs = $this->mailboxHelper->getDirsHelper()->getSyncDirs();

		foreach ($dirs as $dir)
		{
			$path = $dir->getPath();
			$hasChild = (bool)preg_match('/(HasChildren)/ix', $dir->getFlags());
			$isCounted = ($dir->isTrash() || $dir->isSpam()) ? false : true;

			if($dir->isHiddenSystemFolder())
			{
				continue;
			}

			$flat[$dir->getId()] = [
				'id' => $path,
				'path' => $path,
				'order' => $this->mailboxHelper->getDirsHelper()->getOrderByDefault($dir),
				'delimiter' => $dir->getDelimiter(),
				'name' => htmlspecialcharsbx($dir->getName()),
				// @TODO: transfer to template
				'html' => sprintf('<span class="mail-msg-list-menu-item">%s</span>', htmlspecialcharsbx($dir->getName())),
				'dataset' => [
					'path' => $path,
					'dirMd5' => $dir->getDirMd5(),
					'isDisabled' => $dir->isDisabled(),
					'hasChild' => $hasChild,
					'isCounted' => $isCounted
				],
				// @TODO: lead to one key 'unseenCount'
				'count' => isset($directoriesWithNumberOfUnreadMessages[$dir->getDirMd5()]['UNSEEN']) ? (int)$directoriesWithNumberOfUnreadMessages[$dir->getDirMd5()]['UNSEEN'] : 0,
				'unseen' => isset($directoriesWithNumberOfUnreadMessages[$dir->getDirMd5()]['UNSEEN']) ? (int)$directoriesWithNumberOfUnreadMessages[$dir->getDirMd5()]['UNSEEN'] : 0,
				'onclick' => "BX.Mail.Client.Message.List['".
					CUtil::JSEscape($this->getComponentId()).
				"'].onMoveToFolderClick(event)",
				'items' => $hasChild ? [
					[
						'id' => 'loading',
						'text' => Loc::getMessage('MAIL_CLIENT_BUTTON_LOADING'),
						'disabled' => true,
						'items' => []
					]
				] : []
			];

			if (!empty($flat[$dir->getParentId()]))
			{
				foreach ($flat[$dir->getParentId()]['items'] as $k => $item)
				{
					if (!empty($item['id']) && $item['id'] === 'loading')
					{
						array_splice($flat[$dir->getParentId()]['items'], $k, 1);
					}
				}

				$flat[$dir->getParentId()]['items'][] = &$flat[$dir->getId()];
			}
			else
			{
				$list[] = &$flat[$dir->getId()];
			}
		}

		usort(
			$list,
			function($a, $b)
			{
				$aSort = $a['order'];
				$bSort = $b['order'];

				if ($aSort === $bSort)
				{
					return 0;
				}

				return $aSort > $bSort ? 1 : -1;
			}
		);

		return $list;
	}

	private function arrayDiffRecursive($arr1, $arr2)
	{
		$modified = [];
		foreach ($arr1 as $key => $value)
		{
			if (array_key_exists($key, $arr2))
			{
				if (is_array($value) && is_array($arr2[$key]))
				{
					$arDiff = $this->arrayDiffRecursive($value, $arr2[$key]);
					if (!empty($arDiff))
					{
						$modified[$key] = $arDiff;
					}
				}
				elseif ($value != $arr2[$key])
				{
					$modified[$key] = $value;
				}
			}
			else
			{
				$modified[$key] = $value;
			}
		}

		return $modified;
	}

	private function rememberCurrentMailboxId($mailboxId)
	{
		$previousSeenMailboxId = CUserOptions::GetOption('mail', 'previous_seen_mailbox_id', null);

		if ((int)$previousSeenMailboxId !== (int)$mailboxId)
		{
			CUserOptions::SetOption('mail', 'previous_seen_mailbox_id', $mailboxId);
		}
	}

	/**
	 * Getting array of errors.
	 * @return Main\Error[]
	 */
	final public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Main\Error|null
	 */
	final public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}
