<?namespace MaxClean\Sale;

\Bitrix\Main\Loader::includeModule('catalog');

class ProductProvider extends \CCatalogProductProvider {
    private static $_priceGroupId;

    public static function onGetOptimalPrice($productID, $quantity, $userGroups, $renewal, $prices, $siteId, $discountCoupons)
    {
        if (!$_SESSION["period"]) {
            return true;
        }

        $price = self::_getProductPrice($productID, $_SESSION["period"] == 'once' ? 'base' : $_SESSION["period"]);
        if (empty($price)) {
            return true;
        }

        $result = \CCatalogDiscount::applyDiscountList($price['PRICE'], $price['CURRENCY'], $discounts);

        return [
            'PRICE' => $price,
            'DISCOUNT_LIST' => $result['DISCOUNT_LIST']
        ];
    }

    private static function _getProductPrice($productId, $priceGroupName) {
        $price = null;

        if (strlen($priceGroupName) < 1)
            return $price;

        if (empty(self::$_priceGroupId)) {
            $priceGroups = \CCatalogGroup::GetList(
                ['ID' => 'DESC'],
                ['=CAN_ACCESS' => 'Y', '=CAN_BUY' => 'Y'],
                false,
                false,
                ['ID', 'NAME']
            );
            while ($priceGroup = $priceGroups->Fetch()) {
                self::$_priceGroupId[$priceGroup['NAME']] = $priceGroup['ID'];
            }
        }

        if (self::$_priceGroupId[$priceGroupName]) {
            $price = \CPrice::GetList(
                [],
                [
                    'PRODUCT_ID' => $productId,
                    'CATALOG_GROUP_ID' => self::$_priceGroupId[$priceGroupName],
                    'CURRENCY' => 'RUB'
                ],
                false,
                ['nTopCount' => 1],
                ['ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY']
            )->Fetch();
        }

        return $price;
    }
}