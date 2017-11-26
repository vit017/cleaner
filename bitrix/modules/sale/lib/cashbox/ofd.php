<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\NotImplementedException;

abstract class Ofd
{
	protected $testMode = false;

	/**
	 * @return array
	 */
	public static function getHandlerList()
	{
		return array(
			'\Bitrix\Sale\Cashbox\FirstOfd' => FirstOfd::getName(),
			'\Bitrix\Sale\Cashbox\PlatformaOfd' => PlatformaOfd::getName(),
			'\Bitrix\Sale\Cashbox\YarusOfd' => YarusOfd::getName(),
			'\Bitrix\Sale\Cashbox\TaxcomOfd' => TaxcomOfd::getName(),
			'\Bitrix\Sale\Cashbox\OfdruOfd' => OfdruOfd::getName(),
		);
	}

	/**
	 * @param $handler
	 * @param bool $testMode
	 * @return null
	 */
	public static function create($handler, $testMode = false)
	{
		if (class_exists($handler))
			return new $handler($testMode);

		return null;
	}

	/**
	 * Ofd constructor.
	 * @param $testMode
	 */
	private function __construct($testMode)
	{
		$this->testMode = $testMode;
	}

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return '';
	}

	/**
	 * @return array
	 */
	protected function getLinkParamsMap()
	{
		return array();
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function generateCheckLink($data)
	{
		$queryParams = array();

		$map = $this->getLinkParamsMap();
		foreach ($map as $queryKey => $checkKey)
		{
			if ($data[$checkKey])
				$queryParams[] = $queryKey.'='.$data[$checkKey];
		}

		if (empty($queryParams))
			return '';

		$url = $this->getUrl();
		return $url.implode('&', $queryParams);
	}

	/**
	 * @throws NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		throw new NotImplementedException();
	}
}