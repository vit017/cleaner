<?
$MESS["PROVIDE_LINK_BY_TIP"] = "Gibt an, ob der CODE oder die ID an Input-Control bei der Wahl eines Standortes geliefert wird.";
$MESS["JS_CONTROL_GLOBAL_ID_TIP"] = "Die ID der Zeile zum Abruf des JavaScript-Objektes des Schalters von extern aus, indem das Objekt window.BX.locationSelectors benutzt wird.";
$MESS["JS_CALLBACK_TIP"] = "JavaScript-Funktion, die jedes Mal abgerufen wird, wenn ein Schalterwert geändert wird. Die Funktion muss im window-Objekt definiert werden, z.B.:<br />
window.locationUpdated = function(id)<br />
{<br />
&nbsp;console.log(arguments);<br />
&nbsp;console.log(this.getNodeByLocationId(id));<br />
}
";
$MESS["PRESELECT_TREE_TRUNK_TIP"] = "Wenn der Standort-Baum eine Kette hat, die vom Root kommt aber keine Verzweigung hat, wird eben diese Kette ursprünglich ausgewählt.";
$MESS["PRECACHE_LAST_LEVEL_TIP"] = "Ist diese Option aktiviert, wird die zuletzt ausgewählte Ebene vorher geladen, wenn die Komponente angezeigt wird. Anderenfalls werden die Daten geladen, wenn man zuerst auf die Auswahlliste zugreift.";
?>