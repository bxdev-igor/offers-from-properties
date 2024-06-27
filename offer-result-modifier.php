<?
class OfferResultModifier
{

    static public function prepareDetailArResult(&$arResult, $component)
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        $offers = OfferResultModifier::getOffers([$arResult], $component);
        foreach($offers as &$arItems)
        {
            foreach($arItems as &$arItem)
            {
                $arItem["DISPLAY_PROPERTIES"] = array();
                foreach ($arItem["PROPERTIES"] as $pid => &$arProp)
                {
                    if (!in_array($arProp["CODE"], $component->arParams['OFFER_TREE_PROPS']))
                        continue;

                    if((is_array($arProp["VALUE"]) && count($arProp["VALUE"])>0) ||
                        (!is_array($arProp["VALUE"]) && strlen($arProp["VALUE"])>0))
                    {
                        $arItem["DISPLAY_PROPERTIES"][$pid] = \CIBlockFormatProperties::GetDisplayValue($arItem, $arProp);
                    }
                }
            }
            unset($arItem);
        }
        unset($arItems);

        if(array_key_exists($arResult['ID'], $offers))
        {
            $arResult['OFFERS'] = array_values($offers[$arResult['ID']]);
            $arResult['OFFER_ID_SELECTED'] = $arResult['ID'];
        }
        unset($arItem);
        unset($offers);
    }

    static public function getOfferIds($arItem,$component)
    {

        $result = [];
        $objResultElements = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $component->arParams['IBLOCK_ID'], 'CATALOG_AVAILABLE' => 'Y', 'IBLOCK_SECTION_ID' => $arItem['IBLOCK_SECTION_ID'] ],
            false,
            false,
            ['IBLOCK_ID', 'ID']
        );
        while ($res = $objResultElements->GetNext()) {
            $result[$res['ID']] = $res['ID'];
        }
        return $result;
    }

    static public function getOffers($items, $component)
    {

        $allIds = $allOfferIds = [];
        $curIds = [];

        foreach($items as $arItem)
        {
            $curIds = self::getOfferIds($arItem,$component);
            if(count($curIds) > 1)
            {

                $allIds = array_merge($allIds, $curIds);

                $allOfferIds[$arItem['ID']] = $curIds;

            }
        }


        if(count($allIds) > 0)
        {
            global $APPLICATION, $arrModelOffers;
            $arrModelOffers = ['ID' => $allIds];
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.top",
                "offer_models",
                array(
                    "COMPONENT_TEMPLATE" => ".default",
                    "IBLOCK_TYPE" => "aspro_max_catalog",
                    "IBLOCK_ID" => $component->arParams['IBLOCK_ID'],
                    "FILTER_NAME" => "arrModelOffers",
                    "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                    "HIDE_NOT_AVAILABLE" => "N",
                    "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                    "ELEMENT_SORT_FIELD" => "sort",
                    "ELEMENT_SORT_ORDER" => "asc",
                    "ELEMENT_SORT_FIELD2" => "id",
                    "ELEMENT_SORT_ORDER2" => "desc",
                    "OFFERS_SORT_FIELD" => "sort",
                    "OFFERS_SORT_ORDER" => "asc",
                    "OFFERS_SORT_FIELD2" => "id",
                    "OFFERS_SORT_ORDER2" => "desc",
                    "ELEMENT_COUNT" => count($allIds),
                    "LINE_ELEMENT_COUNT" => "3",
                    "OFFERS_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "OFFERS_LIMIT" => "0",
                    "SECTION_URL" => "",
                    "DETAIL_URL" => "",
                    "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                    "SEF_MODE" => "N",
                    "CACHE_TYPE" => "N",
                    "CACHE_TIME" => "36000000",
                    "CACHE_GROUPS" => "Y",
                    "CACHE_FILTER" => "N",
                    "ACTION_VARIABLE" => "action",
                    "PRODUCT_ID_VARIABLE" => "id",
                    "PRICE_CODE" => $component->arParams['PRICE_CODE'],
                    "USE_PRICE_COUNT" => "N",
                    "SHOW_PRICE_COUNT" => "1",
                    "PRICE_VAT_INCLUDE" => "N",
                    "CONVERT_CURRENCY" => "N",
                    "BASKET_URL" => "/personal/basket.php",
                    "USE_PRODUCT_QUANTITY" => "N",
                    "ADD_PROPERTIES_TO_BASKET" => "N",
                    "PRODUCT_PROPS_VARIABLE" => "prop",
                    "PARTIAL_PRODUCT_PROPERTIES" => "N",
                    "DISPLAY_COMPARE" => "N",
                    "COMPATIBLE_MODE" => "Y"
                ),
                $component
            );
            unset($arrModelOffers);
            if(!empty($component->arResult['CUSTOM_OFFERS']))
            {
                foreach($allOfferIds as &$arOffers)
                {
                    foreach($arOffers as $key=>$id)
                    {
                        if(empty($component->arResult['CUSTOM_OFFERS'][$id]))
                        {
                            unset($arOffers[$key]);
                        }
                        else
                        {
                            $arOffers[$key] = $component->arResult['CUSTOM_OFFERS'][$id];
                            //print_R($arOffers[$key]);
                            $arOffers[$key] = array_merge($arOffers[$key], \CNext::formatPriceMatrix($arOffers[$key]));
                        }
                    }
                }
                unset($arOffers);

                return $allOfferIds;
            }
            return [];
        }
        return [];
    }
}
?>