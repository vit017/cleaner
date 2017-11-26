<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 15.05.14
 * Time: 18:49
 */
xmp($arResult);
/*foreach($arResult["MONTHS"] as $month){?>
    <table border="1" >
        <tr><td><</td><td colspan="5"><?=$month['MONTH']?></td><td>></td></tr>
        <tr>
            <?$days = weekdays();
            foreach($days as $name){?>
                <td><?=$name?></td>
            <?}?>
        </tr>
        <tr>
            <?$i=0;
            foreach($month['PREV_MONTH'] as $date => $arTime){
            if($i>6){
            $i = 0;?>
                </tr><tr>
            <?};
            $i++;?>
            <td style="color:goldenrod"><?=$date?></td>

            <?}
            if(count($month['PREV_MONTH'])<=0){
                while($month['FIRST_DAY']>$i+1){
                    $i++?>
                    <td></td>
                    <?
                }
            }
            foreach($month['DATE'] as $date => $arTime){
            if($i>6){
            $i = 0;?>
        </tr><tr>
            <?};
            $i++;
            ?>
            <?if($arTime['AVAIL']){?>
                <td style="color: green" class="js-selectDate" data-date="<?=$date?>">
            <?}else{?>
                <td style="color: red">
            <?}?>
            <?=$date;?>
                <?
                foreach($arTime['TIME'] as $time){
                    echo '<br>'.$time;
                    //if($arTime['AVAIL'])xmp($arTime);
                }?>
            </td>
            <?}
            foreach($month['NEXT_MONTH'] as $date => $arTime){
            if($i>6){
            $i = 0;?>
        </tr><tr>
            <?};
            $i++;?>
            <td style="color:goldenrod"><?=$date?></td>
            <?}?>
            <?if($i == 6){?>
        </tr>
        <?}?>
    </table>
<?}*/?>
<table>
    <tr>
        <?foreach($arResult["TIME"] as $time){?>
            <td class="js-selectTime" data-time="<?=$time?>"><?=$time?>:00</td>
        <?}?>
    </tr>
    <tr>
        <input type="text" name="ORDER_PROP_<?=$arParams['PROP_DATE_ID']?>" >
        <input type="text" name="ORDER_PROP_<?=$arParams['PROP_TIME_ID']?>" >
    </tr>
</table>
