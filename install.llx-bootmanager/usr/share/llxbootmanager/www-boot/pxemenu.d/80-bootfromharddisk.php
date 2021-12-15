<?php
if (!isset($PLATFORM) || trim(strtolower($PLATFORM)) != "efi"){
$MenuEntryList=array();
$MenuEntry=new stdClass();
$MenuEntry->id="bootfromhd";
$MenuEntry->label="Arranca des del disc dur (hd0 0)";
$MenuEntry->menuString="\nLABEL HD
MENU LABEL Arranca des del disc dur
KERNEL chain.c32
APPEND hd0 0\n";
array_push($MenuEntryList, $MenuEntry);

// "Return" MenuEntryListObject
$MenuEntryListObject=$MenuEntryList;
}
?>
