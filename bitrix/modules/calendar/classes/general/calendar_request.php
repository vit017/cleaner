<?
class CCalendarRequest
{
	private static
		$reqId,
		$calendar;

	public static function Process($action = '', CCalendar $calendar)
	{
		global $APPLICATION;
		if ($_REQUEST['skip_unescape'] !== 'Y')
			CUtil::JSPostUnEscape();

		self::$calendar = $calendar;

		// Export calendar
		if ($action == 'export')
		{
			// We don't need to check access  couse we will check security SIGN from the URL
			$sectId = intVal($_GET['sec_id']);
			if ($_GET['check'] == 'Y') // Just for access check from calendar interface
			{
				$APPLICATION->RestartBuffer();
				if (CCalendarSect::CheckSign($_GET['sign'], intVal($_GET['user']), $sectId > 0 ? $sectId : 'superposed_calendars'))
					echo 'BEGIN:VCALENDAR';
				CMain::FinalActions();
				die();
			}

			if (CCalendarSect::CheckAuthHash() && $sectId > 0)
			{
				// We don't need any warning in .ics file
				error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
				CCalendarSect::ReturnICal(array(
					'sectId' => $sectId,
					'userId' => intVal($_GET['user']),
					'sign' => $_GET['sign'],
					'type' => $_GET['type'],
					'ownerId' => intVal($_GET['owner'])
				));
			}
		}
		else
		{
			// // Check the access
			if (!CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()) || !check_bitrix_sessid())
			{
				$APPLICATION->ThrowException(GetMessage("EC_ACCESS_DENIED"));
				return false;
			}

			$APPLICATION->ShowAjaxHead();
			$APPLICATION->RestartBuffer();
			self::$reqId = intVal($_REQUEST['reqId']);

			switch ($action)
			{
				case 'edit_event':
					self::EditEvent();
					break;
				case 'move_event_to_date':
					self::MoveEventToDate();
					break;
				case 'delete':
					self::DeleteEvent();
					break;
				case 'load_events':
					self::LoadEvents();
					break;
				case 'section_edit':
					self::EditSection();
					break;
				case 'section_delete':
					self::DeleteSection();
					break;
				case 'section_caldav_hide':
					self::HideCaldavSection();
					break;
				case 'set_superposed':
					self::SetSuperposed();
					break;
				case 'get_superposed':
					self::GetSuperposed();
					break;
				case 'spcal_user_cals':
					self::GetSuperposedUserCalendars();
					break;
				case 'spcal_del_user':
					self::DeleteTrackingUser();
					break;
				case 'save_settings':
					self::SaveUserSettings();
					break;
				case 'set_meeting_status':
					self::SetMeetingStatus();
					break;
				case 'get_group_members':
					self::GetGroupMemberList();
					break;
				case 'get_accessibility':
					self::GetAccessibility();
					break;
				case 'get_mr_accessibility':
					self::GetMeetingRoomAccessibility();
					break;
				case 'check_meeting_room':
					self::CheckMeetingRoom();
					break;
				case 'connections_edit':
					self::EditConnections();
					break;
				case 'disconnect_google':
					self::DisconnectGoogle();
					break;
				case 'clear_sync_info':
					self::ClearSynchronizationInfo();
					break;
				case 'exchange_sync':
					self::SyncExchange();
					break;
				case 'get_view_event_dialog':
					self::GetViewEventDialog();
					break;
				case 'get_edit_event_dialog':
					self::GetEditEventDialog();
					break;
				case 'update_planner':
					self::UpdatePlanner();
					break;
				case 'change_recurcive_event_until':
					self::ChangeRecurciveEventUntil();
					break;
				case 'exclude_recursion_date':
					self::AddExcludeRecursionDate();
					break;
			}
		}

		if($ex = $APPLICATION->GetException())
			ShowError($ex->GetString());

		CMain::FinalActions();
		die();
	}

	public static function OutputJSRes($reqId = false, $res = false)
	{
		if ($res === false)
			return;
		if ($reqId === false)
			$reqId = intVal($_REQUEST['reqId']);
		if (!$reqId)
			return;
		?>
		<script>top.BXCRES['<?= $reqId?>'] = <?= CUtil::PhpToJSObject($res)?>;</script>
		<?
	}

	public static function EditEvent()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$id = intVal($_POST['id']);
		if (isset($_POST['section']))
		{
			$sectId = intVal($_POST['section']);
			$_POST['sections'] = array($sectId);
		}
		else
		{
			$sectId = intVal($_POST['sections'][0]);
		}

		if (CCalendar::GetType() != 'user' || CCalendar::GetOwnerId() != CCalendar::GetUserId()) // Personal user's calendar
		{
			if (!$id && !CCalendarSect::CanDo('calendar_add', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

			if ($id && !CCalendarSect::CanDo('calendar_edit', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		// Default name for events
		$_POST['name'] = trim($_POST['name']);
		if ($_POST['name'] == '')
			$_POST['name'] = GetMessage('EC_DEFAULT_EVENT_NAME');

		$remind = array();
		if (isset($_POST['remind']['checked']) && $_POST['remind']['checked'] == 'Y')
			$remind[] = array('type' => $_POST['remind']['type'], 'count' => intval($_POST['remind']['count']));

		$rrule = isset($_POST['rrule_enabled']) ? $_POST['rrule'] : false;

		if ($_POST['rrule_endson'] == 'never')
		{
			unset($rrule['COUNT']);
			unset($rrule['UNTIL']);
		}
		elseif ($_POST['rrule_endson'] == 'count')
		{
			unset($rrule['UNTIL']);
		}
		elseif ($_POST['rrule_endson'] == 'until')
		{
			unset($rrule['COUNT']);
		}

		// Date & Time
		$dateFrom = $_POST['date_from'];
		$dateTo = $_POST['date_to'];
		$skipTime = isset($_POST['skip_time']) && $_POST['skip_time'] == 'Y';

		if (!$skipTime)
		{
			$dateFrom .= ' '.$_POST['time_from'];
			$dateTo .= ' '.$_POST['time_to'];
		}

		// Timezone
		$tzFrom = $_POST['tz_from'];
		$tzTo = $_POST['tz_to'];
		if (!$tzFrom && isset($_POST['default_tz']))
		{
			$tzFrom = $_POST['default_tz'];
		}
		if (!$tzTo && isset($_POST['default_tz']))
		{
			$tzTo = $_POST['default_tz'];
		}

		if (isset($_POST['default_tz']) && $_POST['default_tz'] != '')
		{
			CCalendar::SaveUserTimezoneName(CCalendar::GetUserId(), $_POST['default_tz']);
		}

		$arFields = array(
			"ID" => $id,
			"DATE_FROM" => $dateFrom,
			"DATE_TO" => $dateTo,
			'TZ_FROM' => $tzFrom,
			'TZ_TO' => $tzTo,
			'NAME' => $_POST['name'],
			'DESCRIPTION' => trim($_POST['desc']),
			'SECTIONS' => $_POST['sections'],
			'COLOR' => $_POST['color'],
			'TEXT_COLOR' => $_POST['text_color'],
			'ACCESSIBILITY' => $_POST['accessibility'],
			'IMPORTANCE' => $_POST['importance'],
			'PRIVATE_EVENT' => $_POST['private_event'] == 'Y',
			'RRULE' => $rrule,
			'LOCATION' => is_array($_POST['location']) ? $_POST['location'] : array(),
			"REMIND" => $remind,
			"IS_MEETING" => !!$_POST['is_meeting'],
			"SKIP_TIME" => $skipTime
		);

		$arAccessCodes = array();
		if (isset($_POST['EVENT_DESTINATION']))
		{
			foreach($_POST["EVENT_DESTINATION"] as $v => $k)
			{
				if(strlen($v) > 0 && is_array($k) && !empty($k))
				{
					foreach($k as $vv)
					{
						if(strlen($vv) > 0)
						{
							$arAccessCodes[] = $vv;
						}
					}
				}
			}
			$arAccessCodes[] = 'U'.CCalendar::GetUserId();

			if(!CCalendar::IsPersonal() && CCalendar::GetType() == 'user')
				$arAccessCodes[] = 'U'.CCalendar::GetOwnerId();

			$arAccessCodes = array_unique($arAccessCodes);
		}

		$arFields['IS_MEETING'] = !empty($arAccessCodes) && $arAccessCodes != array('U'.CCalendar::GetUserId());
		if ($arFields['IS_MEETING'])
		{
			$arFields['ATTENDEES_CODES'] = $arAccessCodes;
			$arFields['ATTENDEES'] = CCalendar::GetDestinationUsers($arAccessCodes);
			$arFields['MEETING_HOST'] = CCalendar::GetUserId();
			$arFields['MEETING'] = array(
					'HOST_NAME' => CCalendar::GetUserName($arFields['MEETING_HOST']),
					'TEXT' => isset($_POST['meeting_text']) ? $_POST['meeting_text'] : '',
					'OPEN' => $_POST['open_meeting'] === 'Y',
					'NOTIFY' => $_POST['meeting_notify'] === 'Y',
					'REINVITE' => $_POST['meeting_reinvite'] === 'Y'
			);
		}

		// Userfields for event
		$arUFFields = array();
		foreach ($_POST as $field => $value)
		{
			if (substr($field, 0, 3) == "UF_")
			{
				$arUFFields[$field] = $value;
			}
		}

		$newId = CCalendar::SaveEvent(array(
			'arFields' => $arFields,
			'UF' => $arUFFields,
			'silentErrorMode' => false,
			'recursionEditMode' => $_REQUEST['rec_edit_mode'],
			'currentEventDateFrom' => CCalendar::Date(CCalendar::Timestamp($_POST['current_date_from']), false)
		));

		$arEvents = array();
		$arAttendees = array();
		$eventIds = array($newId);

		if ($newId)
		{
			$arFilter = array("ID" => $newId);
			$month = intVal($_REQUEST['month']);
			$year = intVal($_REQUEST['year']);
			$arFilter["FROM_LIMIT"] = CCalendar::Date(mktime(0, 0, 0, $month - 1, 20, $year), false);
			$arFilter["TO_LIMIT"] = CCalendar::Date(mktime(0, 0, 0, $month + 1, 10, $year), false);

			$arEvents = CCalendarEvent::GetList(
				array(
					'arFilter' => $arFilter,
					'parseRecursion' => true,
					'fetchAttendees' => true,
					'userId' => CCalendar::GetUserId()
				)
			);

			if ($arFields['IS_MEETING'])
			{
				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "CALENDAR",
					"CODE" => \Bitrix\Main\FinderDestTable::convertRights($arAccessCodes, array('U'.CCalendar::GetUserId()))
				));
			}

			if ($arEvents && $arFields['IS_MEETING'])
				$arAttendees = CCalendarEvent::GetLastAttendees();

			if (in_array($_REQUEST['rec_edit_mode'], array('this', 'next')))
			{
				unset($arFilter['ID']);
				$arFilter['RECURRENCE_ID'] = ($arEvents && $arEvents[0] && $arEvents[0]['RECURRENCE_ID']) ? $arEvents[0]['RECURRENCE_ID'] : $newId;

				$resRelatedEvents = CCalendarEvent::GetList(
					array(
						'arFilter' => $arFilter,
						'parseRecursion' => true,
						'fetchAttendees' => true,
						'userId' => CCalendar::GetUserId()
					)
				);

				foreach ($resRelatedEvents as $ev)
				{
					$eventIds[] = $ev['ID'];
				}
				$arEvents = array_merge($arEvents, $resRelatedEvents);
			}
			elseif ($id && $arEvents && $arEvents[0] && CCalendarEvent::CheckRecurcion($arEvents[0]))
			{
				$recId = $arEvents[0]['RECURRENCE_ID'] ? $arEvents[0]['RECURRENCE_ID'] : $arEvents[0]['ID'];
				if ($arEvents[0]['RECURRENCE_ID'] && $arEvents[0]['RECURRENCE_ID'] !== $arEvents[0]['ID'])
				{
					unset($arFilter['RECURRENCE_ID']);
					$arFilter['ID'] = $arEvents[0]['RECURRENCE_ID'];
					$resRelatedEvents = CCalendarEvent::GetList(
						array(
							'arFilter' => $arFilter,
							'parseRecursion' => true,
							'fetchAttendees' => true,
							'userId' => CCalendar::GetUserId()
						)
					);
					$eventIds[] = $arEvents[0]['RECURRENCE_ID'];
					$arEvents = array_merge($arEvents, $resRelatedEvents);
				}

				if ($recId)
				{
					unset($arFilter['ID']);
					$arFilter['RECURRENCE_ID'] = $recId;
					$resRelatedEvents = CCalendarEvent::GetList(
						array(
							'arFilter' => $arFilter,
							'parseRecursion' => true,
							'fetchAttendees' => true,
							'userId' => CCalendar::GetUserId()
						)
					);

					foreach ($resRelatedEvents as $ev)
					{
						$eventIds[] = $ev['ID'];
					}
					$arEvents = array_merge($arEvents, $resRelatedEvents);
				}
			}
		}

		self::OutputJSRes(self::$reqId, array(
				'id' => $newId,
				'events' => $arEvents,
				'attendees' => $arAttendees,
				'eventIds' => array_unique($eventIds)
		));
	}

	public static function MoveEventToDate()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$id = intVal($_POST['id']);
		$sectId = intVal($_POST['section']);
		$reload = $_POST['recursive'] === 'Y';
		$busyWarning = false;

		if (CCalendar::GetType() != 'user' || CCalendar::GetOwnerId() != CCalendar::GetUserId()) // Personal user's calendar
		{
			if (!$id && !CCalendarSect::CanDo('calendar_add', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

			if ($id && !CCalendarSect::CanDo('calendar_edit', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		$skipTime = isset($_POST['skip_time']) && $_POST['skip_time'] == 'Y';
		$arFields = array(
			"ID" => $id,
			"DATE_FROM" => CCalendar::Date(CCalendar::Timestamp($_POST['date_from']), !$skipTime),
			"SKIP_TIME" => $skipTime
		);

		if (isset($_POST['date_to']))
			$arFields["DATE_TO"] = CCalendar::Date(CCalendar::Timestamp($_POST['date_to']), !$skipTime);
		$timezone = $_POST['timezone'];
		if (!$skipTime && $_POST['set_timezone'] == 'Y' && $_POST['timezone'])
		{
			$arFields["TZ_FROM"] = $_POST['timezone'];
			$arFields["TZ_TO"] = $_POST['timezone'];
		}

		if ($_POST['is_meeting'] === 'Y')
		{
			$usersToCheck = array();

			if (is_array($_POST['attendees']))
			{
				foreach ($_POST['attendees'] as $attId)
				{
					$userSettings = CCalendarUserSettings::Get(intval($attId));
					if ($userSettings && $userSettings['denyBusyInvitation'])
					{
						$usersToCheck[] = intval($attId);
					}
				}
			}

			if (count($usersToCheck) > 0)
			{
				$fromTs = CCalendar::Timestamp($arFields["DATE_FROM"]);
				$toTs = CCalendar::Timestamp($arFields["DATE_TO"]);
				$fromTs = $fromTs - CCalendar::GetTimezoneOffset($timezone, $fromTs);
				$toTs = $toTs - CCalendar::GetTimezoneOffset($timezone, $toTs);
				$dateFromUtc = CCalendar::Date($fromTs);
				$dateToUtc = CCalendar::Date($toTs);

				$accessibility = CCalendar::GetAccessibilityForUsers(array(
					'users' => $usersToCheck,
					'from' => $dateFromUtc, // date or datetime in UTC
					'to' => $dateToUtc, // date or datetime in UTC
					'curEventId' => $id,
					'getFromHR' => true,
					'checkPermissions' => false
				));

				foreach($accessibility as $userId => $entries)
				{
					foreach($entries as $entry)
					{
						$entFromTs = CCalendar::Timestamp($entry["DATE_FROM"]);
						$entToTs = CCalendar::Timestamp($entry["DATE_TO"]);

						$entFromTs -= CCalendar::GetTimezoneOffset($entry['TZ_FROM'], $entFromTs);
						$entToTs -= CCalendar::GetTimezoneOffset($entry['TZ_TO'], $entToTs);

						if ($entFromTs < $toTs && $entToTs > $fromTs)
						{
							$busyWarning = true;
							$reload = true;
							break;
						}
					}

					if ($busyWarning)
						break;
				}
			}
		}

		if (!$busyWarning)
		{
			if ($_POST['recursive'] === 'Y')
			{
				CCalendar::SaveEventEx(array(
					'arFields' => $arFields,
					'silentErrorMode' => false,
					'recursionEditMode' => 'this',
					'currentEventDateFrom' => CCalendar::Date(CCalendar::Timestamp($_POST['current_date_from']), false)
				));
			}
			else
			{
				//SaveEvent
				$id = CCalendar::SaveEvent(array(
					'arFields' => $arFields,
					'silentErrorMode' => false
				));
			}
		}

		self::OutputJSRes(self::$reqId, array(
			'id' => $id,
			'reload' => $reload,
			'busy_warning' => $busyWarning
		));
	}


	public static function DeleteEvent()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$res = CCalendar::DeleteEvent(intVal($_POST['id']), true, array('recursionMode' => $_REQUEST['rec_mode']));

		if ($res !== true)
			return CCalendar::ThrowError(strlen($res) > 0 ? $res : GetMessage('EC_EVENT_DEL_ERROR'));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function LoadEvents()
	{
		$arSect = array();
		$arHiddenSect = array();
		$month = intVal($_REQUEST['month']);
		$year = intVal($_REQUEST['year']);
		$fromLimit = CCalendar::Date(mktime(0, 0, 0, $month - 1, 20, $year), false);
		$toLimit = CCalendar::Date(mktime(0, 0, 0, $month + 1, 10, $year), false);
		$connections = false;

		if ($_REQUEST['cal_dav_data_sync'] == 'Y' && CCalendar::IsCalDAVEnabled())
		{
			CDavGroupdavClientCalendar::DataSync("user", CCalendar::GetOwnerId());

			$JSConfig = array();
			CCalendar::InitCalDavParams($JSConfig);
			if ($JSConfig['connections'])
				$connections = $JSConfig['connections'];
		}

		$bGetTask = false;
		if (is_array($_REQUEST['active_sect']))
		{
			foreach($_REQUEST['active_sect'] as $sectId)
			{
				if ($sectId == 'tasks')
					$bGetTask = true;
				elseif (intval($sectId) > 0)
					$arSect[] = intval($sectId);
			}
		}

		if (is_array($_REQUEST['hidden_sect']))
		{
			foreach($_REQUEST['hidden_sect'] as $sectId)
			{
				if ($sectId == 'tasks')
					$arHiddenSect[] = 'tasks';
				elseif(intval($sectId) > 0)
					$arHiddenSect[] = intval($sectId);
			}
		}

		$arAttendees = array(); // List of attendees for each event Array([ID] => Array(), ..,);
		$arEvents = array();

		if (count($arSect) > 0)
		{
			// NOTICE: Attendees for meetings selected inside this method and returns as array by link '$arAttendees'
			$arEvents = CCalendar::GetEventList(array(
					'type' => CCalendar::GetType(),
					'section' => $arSect,
					'fromLimit' => $fromLimit,
					'toLimit' => $toLimit
			), $arAttendees);
		}

		if (is_array($_REQUEST['sup_sect']))
		{
			$arDisplayedSPSections = array();
			foreach($_REQUEST['sup_sect'] as $sectId)
			{
				$arDisplayedSPSections[] = intval($sectId);
			}

			if (count($arDisplayedSPSections) > 0)
			{
				$arSuperposedEvents = CCalendarEvent::GetList(
						array(
								'arFilter' => array(
										"FROM_LIMIT" => $fromLimit,
										"TO_LIMIT" => $toLimit,
										"SECTION" => $arDisplayedSPSections
								),
								'parseRecursion' => true,
								'fetchAttendees' => true,
								'userId' => CCalendar::GetUserId()
						)
				);

				$arEvents = array_merge($arEvents, $arSuperposedEvents);
			}
		}

		//  **** GET TASKS ****
		$arTaskIds = array();
		if ($bGetTask)
		{
			$arTasks = CCalendar::GetTaskList(array(
				'fromLimit' => $fromLimit,
				'toLimit' => $toLimit
			), $arTaskIds);

			if (count($arTasks) > 0)
				$arEvents = array_merge($arEvents, $arTasks);
		}

		// Save hidden calendars
		CCalendarSect::Hidden(CCalendar::GetUserId(), $arHiddenSect);

		self::OutputJSRes(self::$reqId, array(
				'events' => $arEvents,
				'attendees' => $arAttendees,
				'connections' => $connections
		));
	}

	public static function EditSection()
	{
		$id = intVal($_POST['id']);
		$bNew = (!isset($id) || $id == 0);

		if ($bNew) // For new sections
		{
			if (CCalendar::GetType() == 'group')
			{
				// It's for groups
				if (!CCalendarType::CanDo('calendar_type_edit_section', 'group'))
					return CCalendar::ThrowError('[se01]'.GetMessage('EC_ACCESS_DENIED'));
			}
			else if (CCalendar::GetType() == 'user')
			{
				if (!CCalendar::IsPersonal()) // If it's not owner of the group.
					return CCalendar::ThrowError('[se02]'.GetMessage('EC_ACCESS_DENIED'));
			}
			else // other types
			{
				if (!CCalendarType::CanDo('calendar_type_edit_section', CCalendar::GetType()))
					return CCalendar::ThrowError('[se03]'.GetMessage('EC_ACCESS_DENIED'));
			}
		}
		// For existent sections
		elseif (!CCalendar::IsPersonal() && !$bNew && !CCalendarSect::CanDo('calendar_edit_section', $id, CCalendar::GetUserId()))
		{
			return CCalendar::ThrowError(GetMessage('[se02]EC_ACCESS_DENIED'));
		}

		$type = CCalendar::GetType();
		$arFields = Array(
				'CAL_TYPE' => $type,
				'ID' => $id,
				'NAME' => trim($_POST['name']),
				'DESCRIPTION' => trim($_POST['desc']),
				'COLOR' => $_POST['color'],
				'TEXT_COLOR' => $_POST['text_color'],
				'OWNER_ID' => ($type == 'user' || $type == 'group') ? CCalendar::GetOwnerId() : '',
				'EXPORT' => array(
						'ALLOW' => isset($_POST['export']) && $_POST['export'] == 'Y',
						'SET' => $_POST['exp_set']
				),
				'ACCESS' => is_array($_POST['access']) ? $_POST['access'] : array()
		);

		if ($bNew)
		{
			$arFields['IS_EXCHANGE'] = $_POST['is_exchange'] == 'Y';
		}

		$id = intVal(CCalendar::SaveSection(array('arFields' => $arFields)));

		if ($id > 0)
		{
			CCalendarSect::SetClearOperationCache(true);
			$oSect = CCalendarSect::GetById($id, true, true);
			if (!$oSect)
				return CCalendar::ThrowError(GetMessage('EC_CALENDAR_SAVE_ERROR'));

			if (CCalendar::GetType() == 'user' && isset($_POST['is_def_meet_calendar']) && $_POST['is_def_meet_calendar'] == 'Y')
			{
				$set = CCalendarUserSettings::Get(CCalendar::GetOwnerId());
				$set['meetSection'] = $id;
				CCalendarUserSettings::Set($set, CCalendar::GetOwnerId());
			}

			self::OutputJSRes(self::$reqId, array('calendar' => $oSect, 'accessNames' => CCalendar::GetAccessNames()));
		}

		if ($id <= 0)
			return CCalendar::ThrowError(GetMessage('EC_CALENDAR_SAVE_ERROR'));
	}

	public static function DeleteSection()
	{
		$sectId = intVal($_REQUEST['id']);

		if (!CCalendar::IsPersonal() && !CCalendarSect::CanDo('calendar_edit_section', $sectId, CCalendar::GetUserId()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		CCalendar::DeleteSection($sectId);

		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function HideCaldavSection()
	{
		$sectId = intVal($_REQUEST['id']);

		if (!CCalendar::IsPersonal() && !CCalendarSect::CanDo('calendar_edit_section', $sectId, CCalendar::GetUserId()))
		{
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		$connectionRemoved = false;
		$oSect = CCalendarSect::GetById($sectId);
		// For exchange we change only calendar name
		if ($oSect && $oSect['CAL_DAV_CON'])
		{
			CCalendarSect::Edit(array(
					'arFields' => array(
							"ID" => $sectId,
							"ACTIVE" => "N"
					)));

			// Check if it's last section from connection - remove it
			$sections = CCalendarSect::GetList(
					array('arFilter' => array(
							'CAL_DAV_CON' => $oSect['CAL_DAV_CON'],
							'ACTIVE' => 'Y'
					)));

			if(!$sections || count($sections) == 0)
			{
				CCalendar::RemoveConnection(array('id' => intval($oSect['CAL_DAV_CON']), 'del_calendars' => 'Y'));
				$connectionRemoved = true;
			}
		}

		self::OutputJSRes(self::$reqId, array(
				'result' => true,
				'refreshView' => $connectionRemoved
		));
	}

	public static function SetSuperposed()
	{
		$trackedUser = intVal($_REQUEST['trackedUser']);
		if ($trackedUser > 0)
		{
			$arUserIds = CCalendar::TrackingUsers(CCalendar::GetUserId());
			if (!in_array($trackedUser, $arUserIds))
			{
				$arUserIds[] = $trackedUser;
				CCalendar::TrackingUsers(CCalendar::GetUserId(), $arUserIds);
			}
		}

		if (CCalendar::SetDisplayedSuperposed(CCalendar::GetUserId(), $_REQUEST['sect']))
			self::OutputJSRes(self::$reqId, array('result' => true));
		else
			CCalendar::ThrowError('Error! Cant save displayed superposed calendars');
	}

	public static function GetSuperposed()
	{
		self::OutputJSRes(self::$reqId, array('sections' => CCalendar::GetSuperposed()));
	}

	public static function GetSuperposedUserCalendars()
	{
		self::OutputJSRes(self::$reqId, array('sections' => CCalendar::GetSuperposedForUsers($_REQUEST['users'])));
	}

	public static function DeleteTrackingUser()
	{
		self::OutputJSRes(self::$reqId, array('result' => CCalendar::DeleteTrackingUser(intVal($_REQUEST['userId']))));
	}

	public static function SaveUserSettings()
	{
		if (isset($_POST['clear_all']) && $_POST['clear_all'] == true)
		{
			// Clear personal settings
			CCalendarUserSettings::Set(false);
		}
		else
		{
			// Personal
			CCalendarUserSettings::Set($_REQUEST['user_settings']);

			// Save access for type
			if (CCalendarType::CanDo('calendar_type_edit_access', CCalendar::GetType()))
			{
				// General
				$_REQUEST['settings']['week_holidays'] = implode('|',$_REQUEST['settings']['week_holidays']);
				CCalendar::SetSettings($_REQUEST['settings']);
				CCalendarType::Edit(array(
						'arFields' => array(
								'XML_ID' => CCalendar::GetType(),
								'ACCESS' => $_REQUEST['type_access']
						)
				));
			}
		}
		if (isset($_POST['user_timezone_name']))
		{
			CCalendar::SaveUserTimezoneName(CCalendar::GetUserId(), $_POST['user_timezone_name']);
		}

		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function SetMeetingStatus()
	{
		CCalendarEvent::SetMeetingStatusEx(array(
			'attendeeId' => CCalendar::GetUserId(),
			'eventId' => intVal($_REQUEST['event_id']),
			'parentId' => intVal($_REQUEST['parent_id']),
			'status' => in_array($_REQUEST['status'], array('Q', 'Y', 'N')) ? $_REQUEST['status'] : 'Q',
			'reccurentMode' => in_array($_REQUEST['reccurent_mode'], array('this', 'next', 'all')) ? $_REQUEST['reccurent_mode'] : false,
			'currentDateFrom' => CCalendar::Date(CCalendar::Timestamp($_REQUEST['current_date_from']), false)
		));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function SetMeetingParams()
	{
		CCalendarEvent::SetMeetingParams(
				CCalendar::GetUserId(),
				intVal($_REQUEST['event_id']),
				array(
						'ACCESSIBILITY' => $_REQUEST['accessibility'],
						'REMIND' =>  $_REQUEST['remind']
				)
		);
		self::OutputJSRes(self::$reqId, true);
	}

	public static function GetGroupMemberList()
	{
		if (CCalendar::GetType() == 'group')
			self::OutputJSRes(self::$reqId, array('users' => CCalendar::GetGroupMembers(CCalendar::GetOwnerId())));
	}

	public static function GetAccessibility()
	{
		$res = CCalendar::GetAccessibilityForUsers(array(
				'users' => $_POST['users'],
				'from' => CCalendar::Date(CCalendar::Timestamp($_POST['from'])),
				'to' => CCalendar::Date(CCalendar::Timestamp($_POST['to'])),
				'curEventId' => intVal($_POST['cur_event_id']),
				'getFromHR' => true
		));
		self::OutputJSRes(self::$reqId, array('data' => $res));
	}

	public static function GetMeetingRoomAccessibility()
	{
		$res = CCalendar::GetAccessibilityForMeetingRoom(array(
				'id' => intVal($_POST['id']),
				'from' => CCalendar::Date(CCalendar::Timestamp($_POST['from'])),
				'to' => CCalendar::Date(CCalendar::Timestamp($_POST['to'])),
				'curEventId' => intVal($_POST['cur_event_id'])
		));

		self::OutputJSRes(self::$reqId, array('data' => $res));
	}

	public static function CheckMeetingRoom()
	{
		$from = CCalendar::Date(CCalendar::Timestamp($_POST['from']));
		$to = CCalendar::Date(CCalendar::Timestamp($_POST['to']));
		$loc_old = $_POST['location_old'] ? CCalendar::ParseLocation(trim($_POST['location_old'])) : false;
		$loc_new = CCalendar::ParseLocation(trim($_POST['location_new']));

		$Params = array(
				'dateFrom' => $from,
				'dateTo' => $to,
				'regularity' => 'NONE',
				'members' => isset($_POST['guest']) ? $_POST['guest'] : false,
		);

		if (intVal($_POST['id']) > 0)
			$Params['ID'] = intVal($_POST['id']);

		$settings = CCalendar::GetSettings(array('request' => false));
		if ($loc_new['mrid'] == $settings['vr_iblock_id'])
		{
			$Params['VMiblockId'] = $settings['vr_iblock_id'];
			if ($loc_old['mrevid'] > 0)
				$Params['ID'] = $loc_old['mrevid'];
			$check = CCalendar::CheckVideoRoom($Params);
		}
		else
		{
			$Params['RMiblockId'] = $settings['rm_iblock_id'];
			$Params['mrid'] = $loc_new['mrid'];
			$Params['mrevid_old'] = $loc_old ? $loc_old['mrevid'] : 0;
			$check = CCalendar::CheckMeetingRoom($Params);
		}

		self::OutputJSRes(self::$reqId, array('check' => $check));
	}

	public static function EditConnections()
	{
		if (CCalendar::GetType() == 'user' && CCalendar::IsCalDAVEnabled())
		{
			$res = CCalendar::ManageConnections($_POST['connections']);
			if ($res !== true)
				CCalendar::ThrowError($res == '' ? 'Edit connections error' : $res);
			else
				self::OutputJSRes(self::$reqId, array('result' => true));
		}
	}
	public static function DisconnectGoogle()
	{
		if (CCalendar::GetType() == 'user' && CCalendar::IsCalDAVEnabled())
		{
			CCalendar::RemoveConnection(array('id' => intval($_POST['connectionId']), 'del_calendars' => 'Y'));
			self::OutputJSRes(self::$reqId, array('result' => true));
		}
	}

	public static function ClearSynchronizationInfo()
	{
		CCalendar::ClearSyncInfo(CCalendar::GetUserId(), $_POST['sync_type']);
		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function SyncExchange()
	{
		if (CCalendar::GetType() == 'user' && CCalendar::IsExchangeEnabled(CCalendar::GetOwnerId()))
		{
			$error = "";
			$res = CDavExchangeCalendar::DoDataSync(CCalendar::GetOwnerId(), $error);
			if ($res === true || $res === false)
				self::OutputJSRes(self::$reqId, array('result' => true));
			else
				CCalendar::ThrowError($error);
		}
	}

	public static function GetViewEventDialog()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$jsId = $color = preg_replace('/[^\d|\w]/', '', $_REQUEST['js_id']);
		$eventId = intval($_REQUEST['event_id']);
		$fromTs = CCalendar::Timestamp($_REQUEST['date_from']) - $_REQUEST['date_from_offset'];

		$event = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
					"DELETED" => "N",
					"FROM_LIMIT" => CCalendar::Date($fromTs),
					"TO_LIMIT" => CCalendar::Date($fromTs)
				),
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'preciseLimits' => true,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			)
		);

		if (!$event || !is_array($event[0]))
		{
			$event = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N"
					),
					'parseRecursion' => true,
					'maxInstanceCount' => 1,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				)
			);
		}

		if ($event && is_array($event[0]))
		{
			$event = $event[0];
			if ($event['IS_MEETING'] && $event['PARENT_ID'] != $event['ID'])
			{
				$parentEvent = CCalendarEvent::GetById(intval($event['PARENT_ID']));
				if($parentEvent['DELETED'] == 'Y')
				{
					CCalendarEvent::CleanEventsWithDeadParents();
					$event = false;
				}
			}
		}

		if ($event)
		{
			CCalendarSceleton::DialogViewEvent(array(
				'id' => $jsId,
				'event' => $event,
				'sectionName' => $_REQUEST['section_name'],
				'bIntranet' => CCalendar::IsIntranetEnabled(),
				'bSocNet' => CCalendar::IsSocNet(),
				'AVATAR_SIZE' => 21
			));
		}

		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	}

	public static function GetEditEventDialog()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$jsId = $color = preg_replace('/[^\d|\w]/', '', $_REQUEST['js_id']);
		$event_id = intval($_REQUEST['event_id']);

		if ($event_id > 0)
		{
			$Event = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $event_id
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				)
			);

			$Event = $Event && is_array($Event[0]) ? $Event[0] : false;
		}
		else
		{
			$Event = array();
		}

		if (!$event_id || !empty($Event))
		{
			CCalendarSceleton::DialogEditEvent(array(
				'id' => $jsId,
				'event' => $Event,
				'type' => CCalendar::GetType(),
				'bIntranet' => CCalendar::IsIntranetEnabled(),
				'bSocNet' => CCalendar::IsSocNet(),
				'AVATAR_SIZE' => 21
			));
		}

		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	}

	public static function UpdatePlanner()
	{
		$curEventId = intVal($_REQUEST['cur_event_id']);

		$result = array(
			'users' => array(),
			'entries' => array(),
			'accessibility' => array()
		);
		$userIds = array();
		$curUserId = CCalendar::GetCurUserId();

		if (isset($_REQUEST['codes']) && is_array($_REQUEST['codes']))
		{
			$codes = array();
			foreach($_REQUEST['codes'] as $permCode)
			{
				if($permCode)
					$codes[] = $permCode;
			}

			// mantis: 80733
			if(count($codes) > 0 && (!$curEventId || !isset($_REQUEST['add_cur_user_to_list']) || $_REQUEST['add_cur_user_to_list'] === 'Y'))
			{
				$codes[] = 'U'.$curUserId;
			}

			if(!CCalendar::IsPersonal() && CCalendar::GetType() == 'user')
				$codes[] = 'U'.CCalendar::GetOwnerId();

			$users = CCalendar::GetDestinationUsers($codes, true);

			foreach($users as $user)
			{
				$userIds[] = $user['USER_ID'];
				$status = '';
				if ($curUserId == $user['USER_ID'])
					$status = 'h';

				$userSettings = CCalendarUserSettings::Get($user['USER_ID']);
				$result['entries'][] = array(
						'type' => 'user',
						'id' => $user['USER_ID'],
						'name' => CCalendar::GetUserName($user),
						'status' => $status,
						'url' => CCalendar::GetUserUrl($user['USER_ID']),
						'avatar' => CCalendar::GetUserAvatarSrc($user),
						'strictStatus' => $userSettings['denyBusyInvitation']
				);
			}
		}
		elseif(isset($_REQUEST['entries']) && is_array($_REQUEST['entries']))
		{
			foreach($_REQUEST['entries'] as $userId)
			{
				$userIds[] = intval($userId);
			}
		}

		$from = CCalendar::Date(CCalendar::Timestamp($_REQUEST['date_from']), false);
		$to = CCalendar::Date(CCalendar::Timestamp($_REQUEST['date_to']), false);

		$accessibility = CCalendar::GetAccessibilityForUsers(array(
				'users' => $userIds,
				'from' => $from, // date or datetime in UTC
				'to' => $to, // date or datetime in UTC
				'curEventId' => $curEventId,
				'getFromHR' => true,
				'checkPermissions' => false
		));

		$result['accessibility'] = array();
		$deltaOffset = isset($_REQUEST['timezone']) ? (CCalendar::GetTimezoneOffset($_REQUEST['timezone']) - CCalendar::GetCurrentOffsetUTC($curUserId)) : 0;

		foreach($accessibility as $userId => $entries)
		{
			$result['accessibility'][$userId] = array();

			foreach($entries as $entry)
			{
				if (isset($entry['DT_FROM']) && !isset($entry['DATE_FROM']))
				{
					$result['accessibility'][$userId][] = array(
							'id' => $entry['ID'],
							'dateFrom' => $entry['DT_FROM'],
							'dateTo' => $entry['DT_TO'],
							'type' => $entry['FROM_HR'] ? 'hr' : 'event'
					);
				}
				else
				{
					$fromTs = CCalendar::Timestamp($entry['DATE_FROM']);
					$toTs = CCalendar::Timestamp($entry['DATE_TO']);
					if ($entry['DT_SKIP_TIME'] !== "Y")
					{
						$fromTs -= $entry['~USER_OFFSET_FROM'];
						$toTs -= $entry['~USER_OFFSET_TO'];
						$fromTs += $deltaOffset;
						$toTs += $deltaOffset;
					}
					$result['accessibility'][$userId][] = array(
							'id' => $entry['ID'],
							'dateFrom' => CCalendar::Date($fromTs, $entry['DT_SKIP_TIME'] != 'Y'),
							'dateTo' => CCalendar::Date($toTs, $entry['DT_SKIP_TIME'] != 'Y'),
							'type' => $entry['FROM_HR'] ? 'hr' : 'event'
					);
				}
			}
		}

		$location = CCalendar::ParseLocation(trim($_REQUEST['location']));

		if($location['mrid'])
		{
			$mrid = 'MR_'.$location['mrid'];
			$roomEventId = intval($_REQUEST['roomEventId']);
			$entry = array(
					'type' => 'room',
					'id' => $mrid,
					'name' => 'meeting room'
			);

			$roomList = CCalendar::GetMeetingRoomList();
			foreach($roomList as $room)
			{
				if ($room['ID'] == $location['mrid'])
				{
					$entry['name'] = $room['NAME'];
					$entry['url'] = $room['URL'];
					break;
				}
			}

			$result['entries'][] = $entry;
			$result['accessibility'][$mrid] = array();

			$meetingRoomRes = CCalendar::GetAccessibilityForMeetingRoom(array(
					'allowReserveMeeting' => true,
					'id' => $location['mrid'],
					'from' => $from,
					'to' => $to,
					'curEventId' => $roomEventId
			));

			foreach($meetingRoomRes as $entry)
			{
				$result['accessibility'][$mrid][] = array(
						'id' => $entry['ID'],
						'dateFrom' => $entry['DT_FROM'],
						'dateTo' => $entry['DT_TO']
				);
			}
		}

		self::OutputJSRes(self::$reqId, $result);
	}

	public static function ChangeRecurciveEventUntil()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$res = array('result' => false);
		$event = CCalendarEvent::GetById(intval($_POST['event_id']));
		$untilTimestamp = CCalendar::Timestamp($_POST['until_date']);
		$recId = false;

		if ($event)
		{
			if (CCalendarEvent::CheckRecurcion($event))
			{
				$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);
				$event['RRULE']['UNTIL'] = CCalendar::Date($untilTimestamp, false);
				$id = CCalendar::SaveEvent(array(
					'arFields' => array(
						"ID" => $event["ID"],
						"RRULE" => $event['RRULE']
					),
					'silentErrorMode' => false,
					'recursionEditMode' => 'skip'
				));
				$recId = $event["ID"];
				$res['id'] = $id;
			}

			if($event["RECURRENCE_ID"] > 0)
			{
				$recParentEvent = CCalendarEvent::GetById($event["RECURRENCE_ID"]);
				if ($recParentEvent && CCalendarEvent::CheckRecurcion($recParentEvent))
				{
					$recParentEvent['RRULE'] = CCalendarEvent::ParseRRULE($recParentEvent['RRULE']);

					if ($recParentEvent['RRULE']['UNTIL'] && CCalendar::Timestamp($recParentEvent['RRULE']['UNTIL']) > $untilTimestamp)
					{
						$recParentEvent['RRULE']['UNTIL'] = CCalendar::Date($untilTimestamp, false);
						$id = CCalendar::SaveEvent(array(
							'arFields' => array(
								"ID" => $recParentEvent["ID"],
								"RRULE" => $recParentEvent['RRULE']
							),
							'silentErrorMode' => false,
							'recursionEditMode' => 'skip'
						));
						$res['id'] = $id;
					}
				}

				$recId = $event["RECURRENCE_ID"];
			}

			if ($recId)
			{
				$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recId, false);
				foreach($recRelatedEvents as $ev)
				{
					if(CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp)
					{
						CCalendar::DeleteEvent(intVal($ev['ID']), true, array('recursionMode' => 'this'));
					}
				}
			}

			$res['result'] = true;
		}

		self::OutputJSRes(self::$reqId, $res);
	}

	public static function AddExcludeRecursionDate()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		CCalendarEvent::ExcludeInstance($_POST['event_id'], $_POST['exclude_date']);

		self::OutputJSRes(self::$reqId, array('result' => true));
	}
}
?>