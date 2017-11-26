<?

namespace Bitrix\Seo\Retargeting;

class Factory
{
	public static function create($object, $type, $parameters = null)
	{
		$spaceList = explode('\\', $object);
		$objectClassName = array_pop($spaceList);
		array_push($spaceList, 'Services', $objectClassName);
		$className = implode('\\', $spaceList) . strtoupper(substr($type, 0, 1)) . strtolower(substr($type, 1));

		if (!class_exists($object))
		{
			throw new \Bitrix\Main\ArgumentOutOfRangeException('Object');
		}

		if (!class_exists($className))
		{
			throw new \Bitrix\Main\ArgumentOutOfRangeException('Type');
		}

		$instance = new $className($parameters);

		return $instance;
	}
}