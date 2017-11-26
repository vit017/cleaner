<?
$aMenuLinks = Array(
	Array(
		"Новые (".bhTools::getAvailOrders().')',
		"/cleaners/",
		Array(),
		Array(),
		""
	),
	Array(
		"Взятые (".bhTools::getActiveOrders().')',
		"/cleaners/my/",
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Выполненные",
		"/cleaners/history/",
		Array(), 
		Array(), 
		"" 
	),
    Array(
        "Нерабочие дни",
        "/cleaners/no_work/",
        Array(),
        Array(),
        ""
    )
);
?>