<?php

class Lesson {

	public $start;
	public $end;
	public $teacher;
	public $classroom;
	public $notes;
	public $homework;
	public $subject;
	public $summary;
	public $desc;
	public $url;

	function __construct($title_string,$url="") {
		
		if (ord(substr($title_string, 0, 1))==195) {//Remove "Ændret!"
			$x = preg_replace('/^.+\n/', '', $title_string);
		} elseif (substr($title_string, 0, 6)=="Aflyst") {
			$x = preg_replace('/^.+\n/', '', $title_string);
			$this->status="CANCELLED";
		} else {
			$x = $title_string;
		}

		$x = explode("\n",$x); //Make array "entry" for each line of string

		// Remove first line if note date
		if (!@preg_match('/[0-9]?[0-9]\/[0-9]?[0-9]-[0-9]{4}/', $x[0])) {
			$this->summary = $x[0];
			array_shift($x);
		}

	##TIME
		$time = explode(" ",$x[0]); //As the date is always on line 0
		$date = $time[0]; //date as dd/mm-yyyy
		$start = $time[1]; //time as hh:mm
		$end = $time[3];

		$this->start = gmdate("Ymd\THis\Z",strtotime(preg_replace("/\//", "-", $date." ".$start)));
		$this->end = gmdate("Ymd\THis\Z",strtotime(preg_replace("/\//", "-", $date." ".$end)));

	
	##TEACHER
		if (search($x,"L&#230;rer:")) {
			$row = explode(" ",$x[search($x,"L&#230;rer:")]);
			$this->teacher=str_replace(array(chr(13),"(",")"),"",end($row));
		} elseif(search($x,"L&#230;rere:")) {
			$row = explode(" ",$x[search($x,"L&#230;rere:")],2);
			$this->teacher=str_replace(array(chr(13),","),"",end($row));
		} else {
			$this->teacher=NULL;
		}


	##ROOM
		if(search($x,"Lokale:")) {
			$row = explode(" ",$x[search($x,"Lokale:")],2);
			$this->classroom=str_replace(array(chr(13),","),"",end($row));
		} elseif(search($x,"Lokaler:")) {
			$row = explode(" ",$x[search($x,"Lokaler:")],2);
			$this->classroom=str_replace(array(chr(13),","),"",end($row));
		} else {
			$this->classroom=NULL;
		}


	##SUBJECT
		if (search($x,"Hold:")) {
			$row = explode(" ",$x[search($x,"Hold:")],2);
			$this->subject=str_replace(array(chr(13),","),"",end($row));
			if (search($x,"Hold:")!=1) {
				$this->subject=substr($x[1],0,-1)." - ".$this->subject;
			}
		}
	

	##HOMEWORK
		if (search($x,"Lektier:")) {
			$y=$x; //make sure not to break $x
			$n=0;
			while ($n<=search($x,"Lektier:")) {
				array_shift($y);
				$n++;
			}
			$n=0;
			while (search($y,"Note:")) { //pop until note is removed
				array_pop($y);
			}
			while (search($y,chr(13))) { //pop until linebreak lines are removed
				array_pop($y);
			}
			while (search($y,"")) { //pop until empty lines are removed
				array_pop($y);
			}
			/*foreach ($y as $key => $value) { //remove the linebreak at end of note // No longer necessary
				$y[$key]=substr($value, 0,-1);
			}*/

			$this->homework = $y;
		}


	##NOTES
		if (search($x,"Note:")) {
			$y=$x; //make sure not to break $x
			while (search($y,"Note:")) { // Shift until just before note is removed (leaving only the actual note)
				array_shift($y);
			}
			array_shift($y); // Remove "Note:"
			while (search($y,chr(13))) { // pop until empty lines are removed
				array_pop($y);
			}
			while (search($y,"")) { // pop until empty lines are removed
				array_pop($y);
			}
			/*foreach ($y as $key => $value) { //remove the linebreak at end of note ONLY if it exists // Not necessary
				if(substr($value, 0,-1).chr(13)==$value){
					$y[$key]=substr($value, 0,-1);
				}
			}*/
			$this->notes = $y;
		}


	##SUMMARY ADDITION
		
		if ($this->teacher!=NULL) { # 
			$teacherinfo = " - ".$this->teacher;
		} else {
			$teacherinfo = "";
		}


		if (search($x,"Note:")) {
			$this->summary .= $this->subject.$teacherinfo." - N";
		}
		if (search($x,"Lektier:")) {
			$this->summary .= $this->subject.$teacherinfo." - L";
		}
		if (search($x,"Note:") && search($x,"Lektier:")) {
			$this->summary .= $this->subject.$teacherinfo." - L:N";
		}
		if (!(search($x,"Note:") || search($x,"Lektier:"))) {
			$this->summary .= $this->subject.$teacherinfo;
		}


	##BUILD DESC
		$this->desc="";
		
		if (search($x,"Note:") && search($x,"Lektier:")) { // Both present
			foreach ($this->notes as $key => $value) {
				$this->desc.="$value\\n\\n";
			}
			$this->desc.="\\n\\n";
			foreach ($this->homework as $key => $value) {
				if (@$this->homework[$key+1]) {
					$this->desc.="$value\\n\\n";
				} else {
					$this->desc.="$value";
				}
			}
		} elseif(!(search($x,"Note:") OR search($x,"Lektier:"))){ // None present
			$this->desc=NULL;
		} elseif (search($x,"Lektier:")) {
			foreach ($this->homework as $key => $value) {
				if (@$this->homework[$key+1]) {
					$this->desc.="$value\\n\\n";
				} else {
					$this->desc.="$value";
				}
			}
		} elseif (search($x,"Note:")) {
			foreach ($this->notes as $key => $value) {
				if(@$this->notes[$key+1]){
					$this->desc.="$value\\n\\n";
				}else{
					$this->desc.="$value";
				}
			}
		}

	##UID
		$this->uid = md5($this->teacher.$this->start.$this->end);

		
	##URL
		$this->url = $url;


	}

}

?>
