<?
class CCalendarSync
{
	public static function ModifyEvent($calendarId, $arFields, $params = array())
	{
		list($sectionId, $entityType, $entityId) = $calendarId;
		$userId = $entityType == 'user' ? $entityId : 0;
		$eventId = false;

		$bExchange = CCalendar::IsExchangeEnabled($userId) && $entityType == 'user';
		$saveEvent = true;

		CCalendar::SetSilentErrorMode();
		if ($sectionId && CCalendarSect::GetById($sectionId, false))
		{
			CCalendar::SetOffset(false, CCalendar::GetOffset($userId));
			$entityType = strtolower($entityType);
			$eventId = ((isset($arFields["ID"]) && (intval($arFields["ID"]) > 0)) ? intval($arFields["ID"]) : 0);
			$arNewFields = array(
				"DAV_XML_ID" => $arFields['XML_ID'],
				"CAL_DAV_LABEL" => (isset($arFields['PROPERTY_BXDAVCD_LABEL']) && strlen($arFields['PROPERTY_BXDAVCD_LABEL']) > 0) ? $arFields['PROPERTY_BXDAVCD_LABEL'] : '',
				"DAV_EXCH_LABEL" => (isset($arFields['PROPERTY_BXDAVEX_LABEL']) && strlen($arFields['PROPERTY_BXDAVEX_LABEL']) > 0) ? $arFields['PROPERTY_BXDAVEX_LABEL'] : '',
				"ID" => $eventId,
				'NAME' => $arFields["NAME"] ? $arFields["NAME"] : GetMessage('EC_NONAME_EVENT'),
				'CAL_TYPE' => $entityType,
				'OWNER_ID' => $entityId,
				'DESCRIPTION' => isset($arFields['DESCRIPTION']) ? $arFields['DESCRIPTION'] : '',
				'SECTIONS' => $sectionId,
				'ACCESSIBILITY' => isset($arFields['ACCESSIBILITY']) ? $arFields['ACCESSIBILITY'] : 'busy',
				'IMPORTANCE' => isset($arFields['IMPORTANCE']) ? $arFields['IMPORTANCE'] : 'normal',
				"REMIND" => is_array($arFields['REMIND']) ? $arFields['REMIND'] : array(),
				"RRULE" => is_array($arFields['RRULE']) ? is_array($arFields['RRULE']) : array(),
				"VERSION" => isset($arFields['VERSION']) ? intVal($arFields['VERSION']) : 1,
				"PRIVATE_EVENT" => !!$arFields['PRIVATE_EVENT']
			);

			if (isset($arFields['ATTENDEE_EMAIL_LIST']) && $entityType == 'user' && false)
			{
				$arNewFields['IS_MEETING'] = count($arFields['ATTENDEE_EMAIL_LIST']) > 0;
				$arNewFields['ATTENDEES'] = self::GetUsersByEmailList($arFields['ATTENDEE_EMAIL_LIST']);

				if (!empty($arNewFields['ATTENDEES']))
				{
					$arNewFields['ATTENDEES_CODES'] = array();
					foreach($arNewFields['ATTENDEES'] as $attendee)
					{
						if(intval($attendee['USER_ID']) > 0)
						{
							$arNewFields['ATTENDEES_CODES'][] = 'U'.IntVal($attendee['USER_ID']);
						}
					}
					$arNewFields['ATTENDEES_CODES'] = array_unique($arNewFields['ATTENDEES_CODES']);
				}

				$arNewFields['MEETING_HOST'] = $entityId;
				$arNewFields['MEETING'] = array(
					'HOST_NAME' => CCalendar::GetUserName($entityId)
				);
			}

			$arNewFields["DATE_FROM"] = $arFields['DATE_FROM'];
			$arNewFields["DATE_TO"] = $arFields['DATE_TO'];
			$arNewFields["TZ_FROM"] = $arFields['TZ_FROM'];
			$arNewFields["TZ_TO"] = $arFields['TZ_TO'];
			$arNewFields["SKIP_TIME"] = $arFields['SKIP_TIME'];

			if (isset($arFields['RECURRENCE_ID']))
				$arNewFields['RECURRENCE_ID'] = $arFields['RECURRENCE_ID'];

			if ($arNewFields["SKIP_TIME"])
			{
				$arNewFields["DATE_FROM"] = CCalendar::Date(CCalendar::Timestamp($arNewFields['DATE_FROM']), false);
				$arNewFields["DATE_TO"] = CCalendar::Date(CCalendar::Timestamp($arNewFields['DATE_TO']) - CCalendar::GetDayLen(), false);
			}

			if (!empty($arFields['PROPERTY_REMIND_SETTINGS']))
			{
				$ar = explode("_", $arFields["PROPERTY_REMIND_SETTINGS"]);
				if(count($ar) == 2)
					$arNewFields["REMIND"][] = array('type' => $ar[1],'count' => floatVal($ar[0]));
			}

			if (!empty($arFields['PROPERTY_ACCESSIBILITY']))
				$arNewFields["ACCESSIBILITY"] = $arFields['PROPERTY_ACCESSIBILITY'];
			if (!empty($arFields['PROPERTY_IMPORTANCE']))
				$arNewFields["IMPORTANCE"] = $arFields['PROPERTY_IMPORTANCE'];
			if (!empty($arFields['PROPERTY_LOCATION']))
				$arNewFields["LOCATION"] = CCalendar::UnParseTextLocation($arFields['PROPERTY_LOCATION']);
			if (!empty($arFields['DETAIL_TEXT']))
				$arNewFields["DESCRIPTION"] = $arFields['DETAIL_TEXT'];

			$arNewFields["DESCRIPTION"] = CCalendar::ClearExchangeHtml($arNewFields["DESCRIPTION"]);
			if (isset($arFields["PROPERTY_PERIOD_TYPE"]) && in_array($arFields["PROPERTY_PERIOD_TYPE"], array("DAILY", "WEEKLY", "MONTHLY", "YEARLY")))
			{
				$arNewFields['RRULE']['FREQ'] = $arFields["PROPERTY_PERIOD_TYPE"];
				$arNewFields['RRULE']['INTERVAL'] = $arFields["PROPERTY_PERIOD_COUNT"];

				if (!isset($arNewFields['DT_LENGTH']) && !empty($arFields['PROPERTY_EVENT_LENGTH']))
				{
					$arNewFields['DT_LENGTH'] = intval($arFields['PROPERTY_EVENT_LENGTH']);
				}
				else
				{
					$arNewFields['DT_LENGTH'] = $arFields['DT_TO_TS'] - $arFields['DT_FROM_TS'];
				}

				if ($arNewFields['RRULE']['FREQ'] == "WEEKLY" && !empty($arFields['PROPERTY_PERIOD_ADDITIONAL']))
				{
					$arNewFields['RRULE']['BYDAY'] = array();
					$bydays = explode(',',$arFields['PROPERTY_PERIOD_ADDITIONAL']);
					foreach($bydays as $day)
					{
						$day = CCalendar::WeekDayByInd($day, false);
						if ($day !== false)
							$arNewFields['RRULE']['BYDAY'][] = $day;
					}
					$arNewFields['RRULE']['BYDAY'] = implode(',',$arNewFields['RRULE']['BYDAY']);
				}

				if (isset($arFields['PROPERTY_RRULE_COUNT']))
					$arNewFields['RRULE']['COUNT'] = $arFields['PROPERTY_RRULE_COUNT'];
				elseif (isset($arFields['PROPERTY_PERIOD_UNTIL']))
					$arNewFields['RRULE']['UNTIL'] = $arFields['PROPERTY_PERIOD_UNTIL'];
				else
					$arNewFields['RRULE']['UNTIL'] = $arFields['DT_TO_TS'];

				if (isset($arFields['EXDATE']))
					$arNewFields['EXDATE'] = $arFields["EXDATE"];
			}

			if ($eventId > 0 && $arNewFields['IS_MEETING'] && $bExchange && false)
			{
				$curEvent = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $eventId,
							"DELETED" => "N"
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'fetchMeetings' => false,
						'userId' => $userId
					)
				);
				if ($curEvent)
					$curEvent = $curEvent[0];

				if ($curEvent['IS_MEETING'] && $curEvent['MEETING_HOST'] !== $userId)
				{
					$arFields = $curEvent;
					$arFields['SKIP_TIME'] = $curEvent['DT_SKIP_TIME'] == 'Y';
					$arFields['SECTIONS'] = array($curEvent['SECT_ID']);
					$arFields['ATTENDEES'] = array();
					foreach($curEvent['~ATTENDEES'] as $att)
					{
						$arFields['ATTENDEES'][] = $att['USER_ID'];
					}
					$arFields['LOCATION'] = Array(
						"OLD" => $curEvent['LOCATION'],
						"NEW" => $curEvent['LOCATION']
					);
					if ($curEvent["RRULE"] != '')
					{
						$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent["RRULE"]);
					}

					$res = self::DoSaveToDav(array(
						'bExchange' => true,
						'sectionId' => $curEvent['SECT_ID']
					), $arFields, $curEvent);

					$saveEvent = false;
				}
			}

			if ($saveEvent)
			{
				$eventId = CCalendar::SaveEvent(
					array(
						'arFields' => $arNewFields,
						'userId' => $userId,
						'bAffectToDav' => false, // Used to prevent syncro with calDav again
						'bSilentAccessMeeting' => true,
						'autoDetectSection' => false
					)
				);
			}
		}

		CCalendar::SetSilentErrorMode(false);

		return $eventId;
	}

	public static function ModifyReccurentInstances($params = array())
	{
		CCalendar::SetSilentErrorMode();
		$parentEvent = CCalendarEvent::GetById($params['parentId']);

		if ($parentEvent && CCalendarEvent::CheckRecurcion($parentEvent))
		{
			$excludeDates = CCalendarEvent::GetExDate($parentEvent['EXDATE']);

			foreach ($params['events'] as $arFields)
			{
				$arFields['RECURRENCE_ID'] = $parentEvent['ID'];
				self::ModifyEvent($params['calendarId'], $arFields);

				if ($arFields['RECURRENCE_ID_DATE'])
				{
					$excludeDates[] = CCalendar::Date(CCalendar::Timestamp($arFields['RECURRENCE_ID_DATE']), false);
				}
			}

			$res = CCalendar::SaveEventEx(array(
				'arFields' => array(
					'ID' => $parentEvent['ID'],
					'EXDATE' => CCalendarEvent::SetExDate($excludeDates)
				),
				'bSilentAccessMeeting' => true,
				'recursionEditMode' => 'skip',
				'silentErrorMode' => true,
				'sendInvitations' => false,
				'bAffectToDav' => false,
				'sendEditNotification' => false
			));
		}

		CCalendar::SetSilentErrorMode(false);
	}

	public static function DoSaveToDav($params = array(), &$arFields, $event = false)
	{
		$sectionId = $params['sectionId'];
		$bExchange = $params['bExchange'];
		$bCalDav = $params['bCalDav'];

		if (isset($event['DAV_XML_ID']))
			$arFields['DAV_XML_ID'] = $event['DAV_XML_ID'];
		if (isset($event['DAV_EXCH_LABEL']))
			$arFields['DAV_EXCH_LABEL'] = $event['DAV_EXCH_LABEL'];
		if (isset($event['CAL_DAV_LABEL']))
			$arFields['CAL_DAV_LABEL'] = $event['CAL_DAV_LABEL'];
		if (!isset($arFields['DATE_CREATE']) && isset($event['DATE_CREATE']))
			$arFields['DATE_CREATE'] = $event['DATE_CREATE'];

		$section = CCalendarSect::GetById($sectionId, false);

		if ($event)
		{
			if ($event['SECT_ID'] != $sectionId)
			{
				$bCalDavCur = CCalendar::IsCalDAVEnabled() && $event['CAL_TYPE'] == 'user' && strlen($event['CAL_DAV_LABEL']) > 0;
				$bExchangeEnabledCur = CCalendar::IsExchangeEnabled() && $event['CAL_TYPE'] == 'user';

				if ($bExchangeEnabledCur || $bCalDavCur)
				{
					$res = CCalendarSync::DoDeleteToDav(array(
						'bCalDav' => $bCalDavCur,
						'bExchangeEnabled' => $bExchangeEnabledCur,
						'sectionId' => $event['SECT_ID']
					), $event);

					if ($event['DAV_EXCH_LABEL'])
						$event['DAV_EXCH_LABEL'] = '';

					if ($res !== true)
						return CCalendar::ThrowError($res);
				}
			}
		}

		$arDavFields = $arFields;
		CCalendarEvent::CheckFields($arDavFields);
		if ($arDavFields['RRULE'] != '')
			$arDavFields['RRULE'] = $arFields['RRULE'];

		if ($arDavFields['LOCATION']['NEW'] !== '')
			$arDavFields['LOCATION']['NEW'] = CCalendar::GetTextLocation($arDavFields['LOCATION']['NEW']);
		$arDavFields['PROPERTY_IMPORTANCE'] = $arDavFields['IMPORTANCE'];
		$arDavFields['PROPERTY_LOCATION'] = $arDavFields['LOCATION']['NEW'];

		$arDavFields['REMIND_SETTINGS'] = '';
		if ($arFields['REMIND'] && is_array($arFields['REMIND']) && is_array($arFields['REMIND'][0]))
			$arDavFields['REMIND_SETTINGS'] = floatVal($arFields['REMIND'][0]['count']).'_'.$arFields['REMIND'][0]['type'];

		if (isset($arDavFields['RRULE'], $arDavFields['RRULE']['BYDAY']) && is_array($arDavFields['RRULE']['BYDAY']))
			$arDavFields['RRULE']['BYDAY'] = implode(',',$arDavFields['RRULE']['BYDAY']);

		// **** Synchronize with CalDav ****
		if ($bCalDav && $section['CAL_DAV_CON'] > 0)
		{
			// New event or move existent event to DAV calendar
			if($arFields['ID'] <= 0 || ($event && !$event['CAL_DAV_LABEL']))
			{
				$DAVRes = CDavGroupdavClientCalendar::DoAddItem($section['CAL_DAV_CON'], $section['CAL_DAV_CAL'], $arDavFields);
			}
			else // Edit existent event
			{
				$DAVRes = CDavGroupdavClientCalendar::DoUpdateItem($section['CAL_DAV_CON'], $section['CAL_DAV_CAL'], $event['DAV_XML_ID'], $event['CAL_DAV_LABEL'], $arDavFields);
			}

			if (!is_array($DAVRes) || !array_key_exists("XML_ID", $DAVRes))
				return CCalendar::CollectCalDAVErros($DAVRes);

			// // It's ok, we successfuly save event to caldav calendar - and save it to DB
			$arFields['DAV_XML_ID'] = $DAVRes['XML_ID'];
			$arFields['CAL_DAV_LABEL'] = $DAVRes['MODIFICATION_LABEL'];
		}
		// **** Synchronize with Exchange ****
		elseif ($bExchange && $section['IS_EXCHANGE'] && strlen($section['DAV_EXCH_CAL']) > 0 && $section['DAV_EXCH_CAL'] !== 0)
		{
			$ownerId = $arFields['OWNER_ID'];

			$fromTo = CCalendarEvent::GetEventFromToForUser($arDavFields, $ownerId);
			$arDavFields["DATE_FROM"] = $fromTo['DATE_FROM'];
			$arDavFields["DATE_TO"] = $fromTo['DATE_TO'];

			// Convert BBcode to HTML for exchange
			$arDavFields["DESCRIPTION"] = CCalendarEvent::ParseText($arDavFields['DESCRIPTION']);

			if ($arFields['IS_MEETING'] && count($arFields['ATTENDEES']) > 0 && false)
			{
				$arDavFields['REQUIRED_ATTENDEES'] = self::GetExchangeEmailForUser($arFields['ATTENDEES']);
			}

			// New event  or move existent event to Exchange calendar
			if ($arFields['ID'] <= 0 || ($event && !$event['DAV_EXCH_LABEL']))
				$exchRes = CDavExchangeCalendar::DoAddItem($ownerId, $section['DAV_EXCH_CAL'], $arDavFields);
			else
				$exchRes = CDavExchangeCalendar::DoUpdateItem($ownerId, $event['DAV_XML_ID'], $event['DAV_EXCH_LABEL'], $arDavFields);

			if (!is_array($exchRes) || !array_key_exists("XML_ID", $exchRes))
				return CCalendar::CollectExchangeErrors($exchRes);

			// It's ok, we successfuly save event to exchange calendar - and save it to DB
			$arFields['DAV_XML_ID'] = $exchRes['XML_ID'];
			$arFields['DAV_EXCH_LABEL'] = $exchRes['MODIFICATION_LABEL'];
		}

		return true;
	}

	public static function DoDeleteToDav($params, $event)
	{
		$sectionId = $params['sectionId'];
		$bExchangeEnabled = $params['bExchangeEnabled'];
		$bCalDav = $params['bCalDav'];
		$sect = CCalendarSect::GetById($sectionId, false);

		// Google and other caldav
		if ($bCalDav && $sect['CAL_DAV_CON'] > 0)
		{
			$DAVRes = CDavGroupdavClientCalendar::DoDeleteItem($sect['CAL_DAV_CON'], $sect['CAL_DAV_CAL'], $event['DAV_XML_ID']);

			if ($DAVRes !== true)
				return CCalendar::CollectCalDAVErros($DAVRes);
		}
		// Exchange
		if ($bExchangeEnabled && $sect['IS_EXCHANGE'])
		{
			$exchRes = CDavExchangeCalendar::DoDeleteItem($event['OWNER_ID'], $event['DAV_XML_ID']);
			if ($exchRes !== true)
				return CCalendar::CollectExchangeErrors($exchRes);
		}

		return true;
	}

	public static function SyncCalendarSections($connectionType, $arCalendars, $entityType, $entityId, $connectionId = null)
	{
		CCalendar::SetSilentErrorMode();
		//Array(
		//	[0] => Array(
		//		[XML_ID] => calendar
		//		[NAME] => calendar
		//	)
		//	[1] => Array(
		//		[XML_ID] => AQATAGFud...
		//		[NAME] => geewgvwe 1
		//		[DESCRIPTION] => gewgvewgvw
		//		[COLOR] => #FF0000
		//		[MODIFICATION_LABEL] => af720e7c7b6a
		//	)
		//)

		$entityType = strtolower($entityType);
		$entityId = intVal($entityId);

		$tempUser = CCalendar::TempUser(false, true);

		$calendarNames = array();
		foreach ($arCalendars as $value)
			$calendarNames[$value["XML_ID"]] = $value;

		if ($connectionType == 'exchange')
		{
			$xmlIdField = "DAV_EXCH_CAL";
			$xmlIdModLabel = "DAV_EXCH_MOD";
		}
		elseif ($connectionType == 'caldav')
		{
			$xmlIdField = "CAL_DAV_CAL";
			$xmlIdModLabel = "CAL_DAV_MOD";
		}
		else
			return array();

		$arFilter = array(
			'CAL_TYPE' => $entityType,
			'OWNER_ID' => $entityId,
			'!'.$xmlIdField => false
		);

		if ($connectionType == 'caldav')
			$arFilter["CAL_DAV_CON"] = $connectionId;
		if ($connectionType == 'exchange')
			$arFilter["IS_EXCHANGE"] = 1;

		$arResult = array();
		$res = CCalendarSect::GetList(array('arFilter' => $arFilter, 'checkPermissions' => false, 'getPermissions' => false));

		foreach($res as $section)
		{
			$xmlId = $section[$xmlIdField];
			$modificationLabel = $section[$xmlIdModLabel];

			if ($connectionType == 'caldav' && $section['DAV_EXCH_CAL'])
				continue;

			if (empty($xmlId))
				continue;

			if (!array_key_exists($xmlId, $calendarNames))
			{
				CCalendarSect::Delete($section["ID"]);
			}
			else
			{
				if ($modificationLabel != $calendarNames[$xmlId]["MODIFICATION_LABEL"])
				{
					CCalendarSect::Edit(array(
						'arFields' => array(
							"ID" => $section["ID"],
							"NAME" => $calendarNames[$xmlId]["NAME"],
							"OWNER_ID" => $entityType == 'user' ? $entityId : 0,
							"CREATED_BY" => $entityType == 'user' ? $entityId : 0,
							"DESCRIPTION" => $calendarNames[$xmlId]["DESCRIPTION"],
							"COLOR" => $calendarNames[$xmlId]["COLOR"],
							$xmlIdModLabel => $calendarNames[$xmlId]["MODIFICATION_LABEL"],
						)
					));
				}

				if (empty($modificationLabel) || ($modificationLabel != $calendarNames[$xmlId]["MODIFICATION_LABEL"]))
				{
					$arResult[] = array(
						"XML_ID" => $xmlId,
						"CALENDAR_ID" => array($section["ID"], $entityType, $entityId)
					);
				}

				unset($calendarNames[$xmlId]);
			}
		}

		foreach($calendarNames as $key => $value)
		{
			$arFields = Array(
				'CAL_TYPE' => $entityType,
				'OWNER_ID' => $entityId,
				'NAME' => $value["NAME"],
				'DESCRIPTION' => $value["DESCRIPTION"],
				'COLOR' => $value["COLOR"],
				'EXPORT' => array('ALLOW' => false),
				"CREATED_BY" => $entityType == 'user' ? $entityId : 0,
				'ACCESS' => array(),
				$xmlIdField => $key,
				$xmlIdModLabel => $value["MODIFICATION_LABEL"]
			);

			if ($connectionType == 'caldav')
				$arFields["CAL_DAV_CON"] = $connectionId;
			if ($entityType == 'user')
				$arFields["CREATED_BY"] = $entityId;
			if ($connectionType == 'exchange')
				$arFields["IS_EXCHANGE"] = 1;

			$id = intVal(CCalendar::SaveSection(array('arFields' => $arFields, 'bAffectToDav' => false)));
			if ($id)
				$arResult[] = array("XML_ID" => $key, "CALENDAR_ID" => array($id, $entityType, $entityId));
		}

		CCalendar::TempUser($tempUser, false);
		CCalendar::SetSilentErrorMode(false);

		return $arResult;
	}

	public static function GetGoogleCalendarConnection()
	{
		$userId = CCalendar::GetCurUserId();
		$result = array();
		if (\Bitrix\Main\Loader::includeModule('socialservices'))
		{
			$client = new CSocServGoogleOAuth($userId);
			$client->getEntityOAuth()->addScope(array('https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly'));

			$id = false;
			if($client->getEntityOAuth()->GetAccessToken())
			{
				$url = "https://www.googleapis.com/calendar/v3/users/me/calendarList";
				$h = new \Bitrix\Main\Web\HttpClient();
				$h->setHeader('Authorization', 'Bearer '.$client->getEntityOAuth()->getToken());
				$response = \Bitrix\Main\Web\Json::decode($h->get($url));
				$id = self::GetGoogleOauthPrimaryId($response);
				$result['googleCalendarPrimaryId'] = $id;
			}

			if(!$id)
			{
				$curPath = CCalendar::GetPath();
				if($curPath)
					$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
				$result['authLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
			}
		}

		return $result;
	}

	private static function GetGoogleOauthPrimaryId($data = array())
	{
		$id = false;
		if (is_array($data['items']) && count($data['items']) > 0)
		{
			foreach($data['items'] as $item)
			{
				if (is_array($item) && $item['primary'] && $item['accessRole'] == 'owner')
				{
					$id = $item['id'];
					break;
				}
			}
		}
		return $id;
	}

	public static function GetExchangeEmailForUser($idList = array())
	{
		global $DB;

		$users = array();

		if (CCalendar::IsSocNet())
		{
			if(is_array($idList))
			{
				$idList = array_unique($idList);
			}
			else
			{
				$idList = array($idList);
			}

			$strIdList = "";
			foreach($idList as $id)
			{
				if(intVal($id) > 0)
				{
					$strIdList .= ','.intVal($id);
				}
			}
			$strIdList = trim($strIdList, ', ');

			if($strIdList != '')
			{
				$exchangeMailbox = COption::GetOptionString("dav", "exchange_mailbox", "");
				$exchangeUseLogin = COption::GetOptionString("dav", "exchange_use_login", "Y");

				$strSql = "SELECT U.ID, U.LOGIN, U.EMAIL, BUF.UF_BXDAVEX_MAILBOX
					FROM b_user U
					LEFT JOIN b_uts_user BUF ON (BUF.VALUE_ID = U.ID)
					WHERE
						U.ACTIVE = 'Y' AND
						U.ID in (".$strIdList.")";

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($entry = $res->Fetch())
				{
					$users[$entry['ID']] = (($exchangeUseLogin == "Y") ? $entry["LOGIN"].$exchangeMailbox : $entry["UF_BXDAVEX_MAILBOX"]);
					if (empty($users[$entry['ID']]))
						$users[$entry['ID']] = $entry['EMAIL'];
				}
			}
		}

		return $users;
	}

	public static function GetUsersByEmailList($emailList = array())
	{
		global $DB;
		$exchangeMailbox = COption::GetOptionString("dav", "exchange_mailbox", "");
		$exchangeUseLogin = COption::GetOptionString("dav", "exchange_use_login", "Y");
		$exchangeMailboxStrlen = strlen($exchangeMailbox);

		$users = array();
		$strValue = "";
		foreach($emailList as $email)
		{
			$strValue .= ",'".CDatabase::ForSql($email)."'";
		}
		$strValue = trim($strValue, ', ');

		if($strValue != '')
		{
			$strSql = "SELECT U.ID, BUF.UF_BXDAVEX_MAILBOX
					FROM b_user U
					LEFT JOIN b_uts_user BUF ON (BUF.VALUE_ID = U.ID)
					WHERE
						U.ACTIVE = 'Y' AND
						BUF.UF_BXDAVEX_MAILBOX in (".$strValue.")";

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$checkedEmails = array();
			while($entry = $res->Fetch())
			{
				$checkedEmails[] = strtolower($entry["UF_BXDAVEX_MAILBOX"]);
				$users[] = $entry['ID'];
			}

			if ($exchangeUseLogin == "Y")
			{
				$strLogins = '';
				foreach($emailList as $email)
				{
					if(!in_array(strtolower($email), $checkedEmails) && strtolower(substr($email, strlen($email) - $exchangeMailboxStrlen)) == strtolower($exchangeMailbox))
					{
						$value = substr($email, 0, strlen($email) - $exchangeMailboxStrlen);
						$strLogins .= ",'".CDatabase::ForSql($value)."'";
					}
				}
				$strLogins = trim($strLogins, ', ');

				if ($strLogins !== '')
				{
					$res = $DB->Query("SELECT U.ID, U.LOGIN FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LOGIN in (".$strLogins.")", false, "File: ".__FILE__."<br>Line: ".__LINE__);

					while($entry = $res->Fetch())
					{
						$users[] = $entry['ID'];
					}
				}
			}
		}

		return $users;
	}
}
?>