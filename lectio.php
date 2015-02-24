<?php
require_once('class.lesson.php');
date_default_timezone_set("Europe/Copenhagen");
#header('Content-type: text/calendar, charset=UTF-8');
header('Cache-Control: max-age=7200, private, must-revalidate');
if($_GET['elev']=="3324951786"){
	exit();
}
function search($arr,$attr){
	foreach ($arr as $key=>$line) {
		$word=explode(" ",$line,2);
		if($word[0]==$attr){
			return $key;
		}
	}
	return false;
}

if(isset($_GET["elev"])) $id=$_GET["elev"];
else $id=$_GET["laerer"];
if(!isset($_GET["type"])) $_GET["type"]="elev";
$school=$_GET["skole"];
$weeks=$_GET["uger"];
$w=0;
while($w<$weeks){
	$week=time()+$w*604800;
	$week=date("WY",$week);
	$dom = new DomDocument();
	
	if($_GET['type']=='elev') $dom->loadHTMLFile("http://www.lectio.dk/lectio/$school/SkemaNy.aspx?type=elev&elevid=$id&week=$week");
	else $dom->loadHTMLFile("http://www.lectio.dk/lectio/$school/SkemaNy.aspx?type=laerer&laererid=$id&week=$week");
	
	$finder = new DomXPath($dom);
	$classname="s2bgbox";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	unset($elements);
	$elements=array();
	for($i=0; $i < $nodes->length; $i++){
		$elements[]=$nodes->item($i);
	}
	foreach($elements as $el){
		$lesson[]=new lesson($el->getAttribute('title'),$el->getAttribute('href'));
	}
	$w++;
}
$nodes = $dom->getElementsByTagName('title');
$title = $nodes->item(0)->nodeValue;
$ext = explode(",", $title);
if(substr($ext[1], 7, 1)=="("){print "error"; exit();}
include "mysql.php";
print "BEGIN:VCALENDAR\n";
print "VERSION:2.0\n";
print "PRODID:-//EMILBA.CH//LECTIO//EN//"."\n";
# print "X-PUBLISHED-TTL:PT1H";
# print floor(date("i")/15)."\n";
if(floor(date("i")/15)==0){
	$min = "00";
}else{
	$min = floor(date("i")/15)*15;
}
#print date("Y/m/d H:".$min)."\n";x
print "LAST-MODIFIED:".date("Ymd\THis\Z",strtotime(date("Y/m/d H:".$min))-3600)."\n";
print "X-PUBLISHED-TTL:PT15M"."\n";
print "X-WR-CALNAME:Lectio skema"."\n";
foreach($lesson as $l){
	if(isset($_GET["cancelled"]) OR !(isset($l->status))){
		print "BEGIN:VEVENT"."\n";
		print "UID:".$l->uid."@emilba.ch"."\n";
		print "SEQUENCE:".$sequence."\n";
		if(isset($l->status)) print "STATUS:".$l->status."\n";
		print "DTSTAMP:".gmdate('Ymd').'T'. gmdate('His')."Z"."\n";
		print "DTSTART:".$l->start."\n";
		print "DTEND:".$l->end."\n";
		print "SUMMARY:".html_entity_decode($l->summary)."\n";
		print "LOCATION:".$l->classroom."\n";
		if($l->desc!=NULL) print "DESCRIPTION:".html_entity_decode($l->desc)."\n";
		//print "URL:".$l->url."\n";
		print "END:VEVENT"."\n";
	}
}
print "END:VCALENDAR"."\n";
?>
