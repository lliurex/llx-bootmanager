<?php
  
header ( "Content-type: text/plain" );

$default_boot=""; // To set default boot when reading
// Include all files in pxemenu.d menu

$MenuList=array();

foreach (glob("pxemenu.d/*.php") as $filename)
{
	$MenuEntryListObject=null;
	// $ filename should define $MenuEntryListObject
    	include $filename;		
	if($MenuEntryListObject!=null){
		foreach($MenuEntryListObject as $entry){
			array_push($MenuList, $entry);
		}	
	}	
}

echo (json_encode($MenuList));





/*  
label ubuntu
kernel pxe/ubuntu/vmlinuz-3.2.0-23-generic
append ro initrd=/pxe/ubuntu/initrd.img-3.2.0-23-generic init=/sbin/init-ltsp quiet splash plymouth:force-splash vt.handoff=7 root=/dev/nbd0 nbdroot=10.2.1.254:/opt/ltsp/ubuntu


LABEL iso
 MENU iso
 MENU LABEL Boot mini iso
 KERNEL memdisk
 APPEND iso ro initrd=isos/mini.iso
*/

?>


