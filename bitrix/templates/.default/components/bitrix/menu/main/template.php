<nav class="main-nav__menu">
    <ul class="main-nav__menu-list">
        <?
        if (defined('MODAL_MENU') && MODAL_MENU) {
            $yaMetricMarks = array(
                'Помощь' => 'help_button_menu',
                'О нас' => 'button_about_menu',
                'Клинерам' => 'button_cleaner_menu',
                'Для бизнеса' => 'button_for_buisenes_menu',
            );
        } else {
            $yaMetricMarks = array(
                'Помощь' => 'help_button_footer',
                'О нас' => 'button_about_footer',
                'Клинерам' => 'cleaner_button_footer',
                'Для бизнеса' => 'for_buisines_button_footer',
            );
        }
        foreach ($arResult as $item) :
            $yamark = isset($yaMetricMarks[$item['TEXT']]) ? $yaMetricMarks[$item['TEXT']] : '';
        ?>
            <li class="main-nav__menu-item"><a onclick="yaCounter38469730.reachGoal('<?=$yamark;?>');" href="<?=$item['LINK'];?>"><?=$item['TEXT'];?></a></li>
        <? endforeach;?>
    </ul>
</nav>