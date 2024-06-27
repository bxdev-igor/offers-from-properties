<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!empty($component->__parent))
{
    $component->__parent->arResult['CUSTOM_OFFERS'] = [];
    foreach($templateData['ITEMS'] as $arItem)
    {
        if(!empty($arItem['ID']))
            $component->__parent->arResult['CUSTOM_OFFERS'][$arItem['ID']] = $arItem;

        if ($arItem['ID'] == $_REQUEST['oid']){
            $component->__parent->arResult['CURRENT_OFFER'] = $arItem;
        }elseif($arItem['ID'] == $component->__parent->arResult['ID']){
            $component->__parent->arResult['CURRENT_OFFER'] = $arItem;

        }

    }
}