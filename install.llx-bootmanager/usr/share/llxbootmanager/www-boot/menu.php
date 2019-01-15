<?php

$MAC=$_GET["mac"];
  
header ( "Content-type: text/plain" );

$default_boot=""; // To set default boot when reading

function write_header($time){
echo "
MENU TITLE Arrencada per xarxa de LliureX
MENU BACKGROUND pxe/lliurex-pxe.png

MENU WIDTH 80
MENU MARGIN 10
MENU ROWS 12
MENU TABMSGROW 18
MENU CMDLINEROW 12
MENU ENDROW 24
MENU TIMEOUTROW 20
menu color title  1;36;44   #ff8bc2ff #00000000 std
menu color unsel  37;44     #ff1069c5 #00000000 std
menu color sel    7;37;40   #ff000000 #ffff7518 all
menu color hotkey 1;37;44   #ffffffff #00000000 std
menu color hotsel 1;7;37;40 #ff000431 #ffff7518 all
menu color border 0 #00ffffff #00ffffff none
TIMEOUT ".$time."
";
}

function getBootTimeOut(){
	$cfgpath="/etc/llxbootmanager/bootcfg.json";
	$data=file_get_contents($cfgpath);
	$json_data=json_decode($data, true);
	return $json_data["timeout"];
}

function getBootOrder(){
	$cfgpath="/etc/llxbootmanager/bootcfg.json";
	$data=file_get_contents($cfgpath);
	$json_data=json_decode($data, true);
	return $json_data["bootorder"];
}

function getClientSpecificBoot($MAC){

	$defboot=null;
	try{
        	$cfgpath="/etc/llxbootmanager/clients.json";
	        $data=file_get_contents($cfgpath);
	        $json_data=json_decode($data, true);
		foreach ($json_data["clients"] as $client){
			if ($MAC==$client["mac"])  {
				// IDEA: Can check if it is bootable?
				return $client["boot"];
				
				}
		}
	}catch (Exception $e){
		return null;			
	}
	return $defboot;
}


function findMenuEntry($menuList, $option){
        $MenuEntry=new stdClass();
	$MenuEntry->arraypos=-1;
	$MenuEntry->menuitem=null;

	$pos=0;
	foreach($menuList as $menuItem){
		if($option==$menuItem->id){
			$MenuEntry->arraypos=$pos;
			$MenuEntry->menuitem=$menuItem;
			echo ("_pos_".$pos."\n");
		}
		$pos=$pos+1;
	}
	return $MenuEntry;
}

// Is there any specific boot for MAC?
$specificBoot=getClientSpecificBoot($MAC);
if ($specificBoot==null) error_log("Mac unregistered");
else error_log($specificBoot);

// Write Menu
$timeout=getBootTimeOut();
if ($specificBoot!=null) $timeout="1"; // Set to minimum
write_header($timeout);

// Getting boot order
$bootorder=getBootOrder();

// Setting bootlist
$bootlist=array();

// Setting menu entries
$menuList=array();

// Setting rescue menu entries
$rescueMenu=array();

// Include all files in pxemenu.d menu to $menulist
foreach (glob("pxemenu.d/*.php") as $filename)
{
     $MenuEntryListObject=null;
	// $ filename should define $MenuEntryListObject
     include $filename;
	
	if($MenuEntryListObject!=null){
		foreach($MenuEntryListObject as $entry){
			$current_label=explode("\n", str_replace("label ", "",$entry->menuString))[0];
			// If there's not default boot or it exists and is current, let's add.
			error_log("specific boot:".$specificBoot."*");
			error_log("Current Label:".$current_label."*");
			array_push($rescueMenu, $entry); // Filling rescue menu
			if (($specificBoot==null)||($specificBoot==$current_label)) 
				array_push($menuList, $entry);
		}
		// If menulist is empty, overwrite by rescuemenu
		if (count($menuList)==0) {
			error_log("[LLXBootmanager Warning] Entry ".$specificBoot." does not exists.");
			$menuList=$rescueMenu;
			}
	}
	
}

// Ordering menu list
foreach ($bootorder as $option){
	$entry=findMenuEntry($menuList, $option);  // find option in menu list
	if ($entry->menuitem!=null){
		//echo "Adding: ".$entry->menuitem->id." Removing: ".$entry->arraypos."\n";
		array_push($bootlist, $entry->menuitem); // Adding menu entry to boot list
		unset($menuList[$entry->arraypos]);
		$menuList=array_values($menuList);
	}
}

/* REMOVED:
 * If menu entries are not in list them won't be shown.
 * 
 // Adding options that are not in the list 
foreach ($menuList as $entry)
	array_push($bootlist, $entry); // Adding menu entry to boot list
*/

foreach ($bootlist as $opt){
        //echo $opt->id;
	echo $opt->menuString."\n";
 }

?>


