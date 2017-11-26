<?php

namespace Bitrix\Conversion;

use Bitrix\Main\SiteTable;
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Date;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;

final class DayContext extends Internals\BaseContext
{
	/**
	 * Add value to counter. If counter not exists set counter to value.
	 *
	 * @param Date $day Counter date.
	 * @param string $name Counter name.
	 * @param int|float $value Number to add.
	 * @return void
	 */
	public function addCounter($day, $name, $value = null)
	{
		if (func_num_args() == 2)
		{
			$value = $name;
			$name  = $day;
			$day   = new Date();
		}

		parent::addCounter($day, $name, $value);
	}

	/**
	 * Add value to counter (once a day per person). If counter not exists set counter to value.
	 *
	 * @param string $name Counter name.
	 * @param int|float $value Number to add.
	 * @return void
	 */
	public function addDayCounter($name, $value)
	{
		$instance =& self::$instance;
		$session  =& self::$session;

		$unique =& $session['UNIQUE'];

		if ($this !== $instance || !in_array($name, $unique, true))
		{
			$this->addCounter($name, $value);

			if ($this === $instance)
			{
				$unique[] = $name;

				$this->setCookie(); // TODO HACK save to database into session
			}
		}
	}

	/**
	 * Add currency value to counter. If counter not exists set counter to value.
	 *
	 * @param string $name Counter name.
	 * @param int|float|string $value Numeric value.
	 * @param string $currency Currency code (eg: RUB).
	 * @return void
	 */
	public function addCurrencyCounter($name, $value, $currency)
	{
		$this->addCounter($name, Utils::convertToBaseCurrency($value, $currency));
	}

	/**
	 * Attach entity item to context.
	 *
	 * @param string $entity Entity type.
	 * @param string|int $item Entity item ID.
	 * @throws ArgumentTypeException
	 * @return void
	 */
	public function attachEntityItem($entity, $item)
	{
		if (! is_string($entity))
			throw new ArgumentTypeException('entity', 'string');

		if (! is_scalar($item))
			throw new ArgumentTypeException('item', 'scalar');

		try
		{
			$result = Internals\ContextEntityItemTable::add(array(
				'CONTEXT_ID' => $this->id,
				'ENTITY'     => $entity,
				'ITEM'       => $item,
			));

			$result->isSuccess(); // TODO
		}
		catch (\Bitrix\Main\DB\SqlQueryException $e)
		{
			// TODO log??
		}
	}

	/**
	 * Get context of attached entity item.
	 *
	 * @param string $entity Entity type.
	 * @param string|int $item Entity item ID.
	 * @return self
	 */
	public static function getEntityItemInstance($entity, $item)
	{
		$instance = self::getInstance();

		$context = Internals\ContextEntityItemTable::getList(array(
			'select' => array('CONTEXT_ID'),
			'filter' => array('=ENTITY' => $entity, '=ITEM' => $item),
			'limit'  => 1,
		))->fetch();

		$contextId = !empty($context['CONTEXT_ID']) ? $context['CONTEXT_ID'] : self::EMPTY_CONTEXT_ID;
		if ($contextId !== $instance->id)
		{
			$instance = new self;
			$instance->id = $contextId;
		}

		return $instance;
	}

	/**
	 * Returns context for given Site
	 *
	 * @param string $siteId Site ID.
	 * @return self
	 */
	public static function getSiteInstance($siteId)
	{
		$instance = self::getInstance();

		if (preg_match('/[a-z0-9_]{2}/i', $siteId) && self::getSiteId() != $siteId && \CSite::getById($siteId)->fetch())
		{
			$instance = new self;

			$instance->saveInstance();

			$instance->id = null;

			$instance->setAttribute('conversion_site', $siteId);
			$instance->save();
		}

		return $instance;
	}

	/** @var self $instance */
	private static $instance;
	private static $session;

	/**
	 * Get day context singleton instance.
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		if (! self::$instance)
		{
			$instance = new self;
			$varName  = self::getVarName();
			$session  =& $_SESSION[$varName];
			$expire   = strtotime('today 23:59');

			if (! (is_array($session) && is_int($session['ID']) && $session['EXPIRE'] === $expire))
			{
				$session = array('ID' => null, 'EXPIRE' => $expire, 'UNIQUE' => array());

				if ($cookie = $_COOKIE[$varName])
				{
					try
					{
						$cookie = Json::decode($cookie);
					}
					catch (ArgumentException $e)
					{
					}
				}

				// check if cookie is valid
				if (   is_array($cookie)
					&& is_array($cookie['UNIQUE'])
					&& $cookie['EXPIRE'] === $expire
					&& ($id = $cookie['ID']) !== null
					&& is_int($id)
					&& ($id === self::EMPTY_CONTEXT_ID || Internals\ContextTable::getByPrimary($id)->fetch())
				)
				{
					$session['ID'    ] = $id;
					$session['UNIQUE'] = $cookie['UNIQUE'];
				}
			}

			$instance->id = $session['ID'];
			self::$session =& $session;
			self::$instance = $instance;

			if ($instance->id === null)
				$instance->saveInstance();

			$instance->setCookie();
		}

		return self::$instance;
	}

	/** @internal */
	private function setCookie()
	{
		$session = self::$session;

		@setcookie(self::getVarName(), Json::encode(array(
			'ID'     => $session['ID'    ],
			'EXPIRE' => $session['EXPIRE'],
			'UNIQUE' => $session['UNIQUE'],
		)), strtotime('+1 year'), '/');
	}

	/** @internal */
	private function saveInstance()
	{
		$instance =& self::$instance;
		$session  =& self::$session;

		// save day context

		if ($this->id === null)
		{
			foreach (EventManager::getInstance()->findEventHandlers('conversion', 'OnSetDayContextAttributes') as $handler)
			{
				ExecuteModuleEventEx($handler, array($this));
			}

			$this->save();

			if ($this === $instance)
				$session['ID'] = $instance->id;
		}
	}

	/** @internal */
	public static function getVarName()
	{
		static $name;

		if (! $name)
		{
			$name = 'BITRIX_CONVERSION_CONTEXT_'.self::getSiteId();
		}

		return $name;
	}

	/** @internal */
	public static function getSiteId()
	{
		static $siteId = null;

		if ($siteId === null)
		{
			$siteId = '';

			if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
			{
				if ($row = SiteTable::getList(array(
					'select' => array('LID'),
					'order'  => array('DEF' => 'DESC', 'SORT' => 'ASC'),
					'limit'  => 1,
				))->fetch())
				{
					$siteId = $row['LID'];
				}
			}
			else
			{
				$siteId = SITE_ID;
			}
		}

		return $siteId;
	}
}
