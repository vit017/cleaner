<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Cashbox\Internals\CashboxConnectTable;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\Cashbox\Internals\CashboxZReportTable;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Result;

Loc::loadMessages(__FILE__);

final class Manager
{
	/* ignored all errors, warnings */
	const LEVEL_TRACE_E_IGNORED = 0;
	/* trace only errors */
	const LEVEL_TRACE_E_ERROR = Errors\Error::LEVEL_TRACE;
	/* trace only errors, warnings */
	const LEVEL_TRACE_E_WARNING = Errors\Warning::LEVEL_TRACE;

	const DEBUG_MODE = false;
	const CACHE_ID = 'BITRIX_CASHBOX_LIST';
	const TTL = 31536000;

	/**
	 * @param CollectableEntity $entity
	 * @return array
	 */
	public static function getListWithRestrictions(CollectableEntity $entity)
	{
		$result = array();

		$dbRes = CashboxTable::getList(array(
			'select' => array('*'),
			'filter' => array('ACTIVE' => 'Y', 'ENABLED' => 'Y'),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));

		while ($cashbox = $dbRes->fetch())
		{
			if (Restrictions\Manager::checkService($cashbox['ID'], $entity) === Restrictions\Manager::SEVERITY_NONE)
				$result[$cashbox['ID']] = $cashbox;
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return Cashbox|null
	 */
	public static function getObjectById($id)
	{
		static $cashboxObjects = array();

		if ((int)$id <= 0)
			return null;

		if (!isset($cashboxObjects[$id]))
		{
			$data = static::getCashboxFromCache($id);
			if ($data)
			{
				$cashbox = Cashbox::create($data);
				if ($cashbox === null)
					return null;

				$cashboxObjects[$id] = $cashbox;
			}
		}

		return $cashboxObjects[$id];
	}

	/**
	 * @param array $cashbox
	 * @return void
	 */
	public static function saveCashbox(array $cashbox)
	{
		if (isset($cashbox['ID']) && (int)$cashbox['ID'] > 0)
		{
			if ($cashbox['ENABLED'] !== $cashbox['PRESENTLY_ENABLED'])
				static::update($cashbox['ID'], array('ENABLED' => $cashbox['PRESENTLY_ENABLED']));

			CashboxTable::update($cashbox['ID'], array('DATE_LAST_CHECK' => new DateTime()));
		}
		else
		{
			$result = static::add(
				array(
					'ACTIVE' => 'N',
					'DATE_CREATE' => new DateTime(),
					'NAME' => CashboxBitrix::getName(),
					'NUMBER_KKM' => $cashbox['NUMBER_KKM'],
					'HANDLER' => $cashbox['HANDLER'],
					'ENABLED' => $cashbox['PRESENTLY_ENABLED'],
					'DATE_LAST_CHECK' => new DateTime(),
				)
			);

			if ($result->isSuccess())
			{
				CashboxZReportTable::add(array(
					'STATUS' => 'Y',
					'CASHBOX_ID' => $result->getId(),
					'DATE_CREATE' => new DateTime(),
					'DATE_PRINT_START' => new DateTime(),
					'LINK_PARAMS' => '',
					'CASH_SUM' => $cashbox['CACHE'],
					'CASHLESS_SUM' => $cashbox['INCOME']-$cashbox['CACHE'],
					'CUMULATIVE_SUM' => $cashbox['NZ_SUM'],
					'RETURNED_SUM' => 0,
					'CURRENCY' => 'RUB',
					'DATE_PRINT_END' => new DateTime()
				));
			}
		}
	}

	/**
	 * @param $cashboxList
	 * @return mixed
	 */
	public static function chooseCashbox($cashboxList)
	{
		$index = rand(0, count($cashboxList)-1);

		return $cashboxList[$index];
	}

	/**
	 * @return string
	 */
	public static function getConnectionLink()
	{
		$context = Main\Application::getInstance()->getContext();
		$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
		$server = $context->getServer();
		$domain = $server->getServerName();

		if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domain, $matches))
		{
			$domain = $matches['domain'];
			$port   = $matches['port'];
		}
		else
		{
			$port = $server->getServerPort();
		}
		$port = in_array($port, array(80, 443)) ? '' : ':'.$port;

		return sprintf('%s://%s%s/bitrix/tools/sale_check_print.php?hash=%s', $scheme, $domain, $port, static::generateHash());
	}

	/**
	 * @return string
	 */
	private static function generateHash()
	{
		$hash = md5(base64_encode(time()));
		CashboxConnectTable::add(array('HASH' => $hash, 'ACTIVE' => 'Y'));

		return $hash;
	}

	/**
	 * @return mixed
	 */
	public static function getListFromCache()
	{
		$cacheManager = Main\Application::getInstance()->getManagedCache();

		if($cacheManager->read(self::TTL, self::CACHE_ID))
			$cashboxList = $cacheManager->get(self::CACHE_ID);

		if (empty($cashboxList))
		{
			$cashboxList = array();

			$dbRes = CashboxTable::getList();
			while ($data = $dbRes->fetch())
				$cashboxList[$data['ID']] = $data;

			$cacheManager->set(self::CACHE_ID, $cashboxList);
		}

		return $cashboxList;
	}

	/**
	 * @param $cashboxId
	 * @return array
	 */
	public static function getCashboxFromCache($cashboxId)
	{
		$cashboxList = static::getListFromCache();
		if (isset($cashboxList[$cashboxId]))
			return $cashboxList[$cashboxId];

		return array();
	}

	/**
	 * @param $cashboxId
	 * @param $id
	 * @return array
	 */
	public static function buildZReportQuery($cashboxId, $id)
	{
		$cashbox = Manager::getObjectById($cashboxId);
		if ($cashbox->getField('USE_OFFLINE') === 'Y')
			return array();

		return $cashbox->buildZReportQuery($id);
	}

	/**
	 * @param $cashboxIds
	 * @return array
	 */
	public static function buildChecksQuery($cashboxIds)
	{
		$result = array();
		$checks = CheckManager::getPrintableChecks($cashboxIds);
		foreach ($checks as $item)
		{
			$check = CheckManager::create($item);
			if ($check !== null)
			{
				$printResult = static::buildConcreteCheckQuery($check->getField('CASHBOX_ID'), $check);
				if ($printResult)
					$result[] = $printResult;
			}
		}

		return $result;
	}

	/**
	 * @param $cashboxId
	 * @param Check $check
	 * @return Result
	 */
	public static function buildConcreteCheckQuery($cashboxId, Check $check)
	{
		$result = new Result();

		$cashbox = static::getObjectById($cashboxId);
		if ($cashbox)
			return $cashbox->buildCheckQuery($check);

		return $result;
	}

	/**
	 * @param array $data
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	public static function add(array $data)
	{
		$cacheManager = Main\Application::getInstance()->getManagedCache();
		$cacheManager->clean(Manager::CACHE_ID);

		return CashboxTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return \Bitrix\Main\Entity\UpdateResult
	 */
	public static function update($primary, array $data)
	{
		$cacheManager = Main\Application::getInstance()->getManagedCache();
		$cacheManager->clean(Manager::CACHE_ID);

		return CashboxTable::update($primary, $data);
	}

	/**
	 * @param $primary
	 * @return \Bitrix\Main\Entity\DeleteResult
	 */
	public static function delete($primary)
	{
		if ($primary == Cashbox1C::getId())
		{
			$cacheManager = Main\Application::getInstance()->getManagedCache();
			$cacheManager->clean(Cashbox1C::CACHE_ID);
		}

		$cacheManager = Main\Application::getInstance()->getManagedCache();
		$cacheManager->clean(Manager::CACHE_ID);

		return CashboxTable::delete($primary);
	}

	/**
	 * @param $cashboxId
	 * @param Main\Error $error
	 * @return void
	 */
	public static function writeToLog($cashboxId, Main\Error $error)
	{
		if (static::getTraceErrorLevel() === static::LEVEL_TRACE_E_IGNORED)
			return;

		if ($error instanceof Errors\Error || $error instanceof Errors\Warning)
		{
			if (static::DEBUG_MODE === true || $error::LEVEL_TRACE <= static::getTraceErrorLevel())
			{
				$data = array(
					'CASHBOX_ID' => $cashboxId,
					'MESSAGE' => $error->getMessage(),
					'DATE_INSERT' => new DateTime()
				);

				Internals\CashboxErrLogTable::add($data);
			}
		}
	}

	/**
	 * @return int
	 */
	private static function getTraceErrorLevel()
	{
		return static::LEVEL_TRACE_E_ERROR;
	}
}