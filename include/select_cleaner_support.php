<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 09.04.15
 * Time: 18:13
 */
?>
<script>
    $('body').on('click', '.js-show_detail', function(){
        $('.js-order_detail').fadeOut();
        $(this).find('.js-order_detail').fadeIn();
    })


</script>
<style>
    .select_cleaner .selectize-input,
    .select_cleaner .selectize-dropdown
    {
        border:none !important;


    }
    .select_cleaner .selectize-input {
        height:50px;
        background: #EFF1F1 !important;
    }
    .select_cleaner .selectize-dropdown {
        box-shadow: rgba(0, 0, 0, 0.0470588) 0px 5px 10px 0px;
    }
    .select_cleaner .selectize-dropdown {
        box-shadow: rgba(0, 0, 0, 0.0470588) 0px 5px 10px 0px;
    }

    .select_cleaner.selectize-control.single .selectize-input:after,
    .select_cleaner.selectize-control.single .selectize-input.dropdown-active:after{
        background-color:#23C8AC;
        background-image:url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiCgkgd2lkdGg9IjE3LjIyOHB4IiBoZWlnaHQ9IjEwLjk3OHB4IiB2aWV3Qm94PSIwIDAgMTcuMjI4IDEwLjk3OCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTcuMjI4IDEwLjk3ODsiCgkgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxwYXRoIHN0eWxlPSJmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiNmZmZmZmY7IiBkPSJNMTcuMjI4LDIuNjM0bC04LjM0NCw4LjM0NGwtMC4yNy0wLjI3bC0wLjI3LDAuMjdMMCwyLjYzNAoJTDIuNjM0LDBsNS45OCw1Ljk4TDE0LjU5NCwwTDE3LjIyOCwyLjYzNHoiLz4KPC9zdmc+");
        margin:0;
    }
    .select_cleaner.selectize-control.single:hover .selectize-input:after,
    .select_cleaner.selectize-control.single .selectize-input:after:hover {
        background-color:#07B19A;
    }

    .select_cleaner.selectize-control.single .selectize-input.dropdown-active:after {
        background-color:#059A86;
        transform: scaleY(-1);
    }

    .cleaner-face{
        height:32px;
        width: 32px;
        margin-right:15px;
        float: left;
    }
</style>