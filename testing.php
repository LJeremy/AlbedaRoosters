<?php
    
    global $week;
    $week = $_GET['week'];
    require('schools.php'); 

    if(!isset($_GET['school']) || !isset($schools[strtolower(@$_GET['school'])])){
        die('[err] School niet bekend.');
    }
    if(!isset($_GET['class'])){
        die('[err] Geef een klas op.');
    }
    if(!isset($_GET['week'])){
        die('[err] Geef een week op.');
    }
    if(!isset($_GET['format']) || !in_array(strtolower(@$_GET['format']),array('json','html'))){
        $_GET['format'] = 'json';
    }
    $school = $schools[$_GET['school']];
    if($_GET['class'] !== 'classes'){
        $filename = 'temp/rooster_'. strtolower(@$_GET['school']) . '_'. trim(@$_GET['class']) . '_' . $week . '.' . strtolower(@$_GET['format']);
        $c = @file_get_contents($filename);
        if($c != '' && !isset($_GET['reset'])){
            // Rooster seems to be in cache. Let's show it and quit.
            switch(strtolower(@$_GET['format'])){
                case 'json':
                    header('Content-type: application/json');
                    if(isset($_GET['callback'])){
	                    $c = $_GET['callback'] . '(' . $c . ')';
	                }
                    break;
                case 'xml':
                    header('Content-type: text/xml; charset=UTF-8');
                    break;       
            }
            echo $c; exit;
        }
        $data = array(
       	  'timestamp' => time(),
          'expires' => time()+86400,
          'date_readable' => date('d-m-Y H:i'),
          'school_name' => $school['full_name'],
          'school_town' => $school['town'],
          'original_url' => str_replace('%class%',trim(@$_GET['class']),$school['rooster_url']),
          'rooster' => array()
        );

        switch($school['rooster_system']){
            case 'untis2011':
            case 'untis2011-r1':
            case 'untis2012':
            case 'untis2012-r1':
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,str_replace('%class%',trim(@$_GET['class']),$school['rooster_url']));
                @curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
                @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                @curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                $cd = curl_exec($ch);
                if(empty($cd)){ die('[err] Kon het rooster niet ophalen. Server error.'); }
                curl_close($ch);
                $dom = new domDocument;
                @$dom->loadHTML($cd); 
                if($school['rooster_system'] == 'untis2012-r1' || $school['rooster_system'] == 'untis2012'){
                	$hours = $dom->getElementsByTagName('table')->item(1)->getElementsByTagName('tr'); 
                }else{
	                $hours = $dom->getElementsByTagName('table')->item(0)->getElementsByTagName('tr');
                }
                $hour = 0;
                foreach($hours as $h => $f){
                	if($h > 0 && @$f->getElementsByTagName('table')->item(0)){
                        $hour++;
                        $e = $f->getElementsByTagName('td');
                        $day = 0;
                        foreach($e as $d => $g){
                        	if($d > 0 && @$g->getElementsByTagName('table')->item(0)){
                                $day++;
                                $i = $g->getElementsByTagName('tr');
                                if($school['rooster_system'] == 'untis2011-r1' || $school['rooster_system'] == 'untis2012-r1'){
                                    $l = 0;
                                    $n = 0;
                                    $data['rooster'][$day][$hour][$l] = array();
                                    $k = $i->item(0)->getElementsByTagName('td');
                                    foreach($k as $m){
                                        $o = trim($m->nodeValue);
                                        if($n == 3){ $n = 0; $l++; }
                                        if($n == 0){ $data['rooster'][$day][$hour][$l]['subject'] = $o; }
                                        if($n == 1){ $data['rooster'][$day][$hour][$l]['teacher'] = $o; }
                                        if($n == 2){ $data['rooster'][$day][$hour][$l]['room'] = $o; }
                                        $n++;
                                    }   
                                }else{
                                    if(isset($data['rooster'][$day][$hour])){
                                        $day++;
                                    }
                                    foreach($i as $j){
                                        $k = $j->getElementsByTagName('td');
                                        $data['rooster']['weekdag'][$day]['uur'][$hour] = array(
                                        'teacher' => trim($k->item(1)->nodeValue),
                                        'subject' => trim($k->item(0)->nodeValue),
                                        'room' => trim($k->item(2)->nodeValue)
                                        );
                                    }
                                    $p = $g->getAttribute('rowspan')/2;
                                    if($p > 1){
                                        for($q=0;$q<$p;$q++){
                                            $data['rooster'][$day][($hour+$q)] = $data['rooster'][$day][$hour];
                                        }
                                        $day+$p;
                                    }
                                }
                            }
                        }
                    }
                }
                if($hour == 0){
	                die('[err] Er is geen rooster beschikbaar voor deze klas.');
                }
                break;
        }
    }
            
    switch(strtolower(@$_GET['format'])){
        case 'json':
            header('Content-type: application/json');
            $output = json_encode($data);
            break;
        case 'txt':
            $output = print_r($data,true);
            break;      
    }
    
    $outputraw = $output;
    if(isset($_GET['callback'])){
    	$output = $_GET['callback'] . '(' . $output . ')';
    }
            
    echo $output;
    @file_put_contents($filename,$outputraw);
    exit;
?>