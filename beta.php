<?php

    require('schools.php'); 

    if(!isset($_GET['school']) || !isset($schools[strtolower(@$_GET['school'])])){
        die('[err] School niet bekend.');
    }
    if(!isset($_GET['class'])){
        die('[err] Geef een klas op.');
    }
    if(!isset($_GET['format']) || !in_array(strtolower(@$_GET['format']),array('json','html'))){
        $_GET['format'] = 'json';
    }

 $school = $schools[$_GET['school']];

    if($_GET['class'] == 'classes'){
        $filename = 'temp/classes_'. strtolower(@$_GET['school']) . '_'. date('Y') . '.' . strtolower(@$_GET['format']);
        if(@filemtime($filename) > (time() - 31*2*86400) && !isset($_GET['reset'])){
            switch(strtolower(@$_GET['format'])){
                case 'json':
                    header('Content-type: application/json');
                    break;
                case 'xml':
                    header('Content-type: text/xml; charset=UTF-8');
                    break;       
            }
            $c = @file_get_contents($filename);
            echo $c; exit;
        }
        if(!is_array($school['rooster_classes'])){
            $school['rooster_classes'] = array($school['rooster_classes']);
        }
        $data = array(
        'timestamp' => time(),
        'expires' => time() + 31*2*86400,
        'date_readable' => date('d-m-Y H:i'),
        'school_name' => $school['full_name'],
        'school_town' => $school['town'],
        'sources' => $school['rooster_classes'],
        'classes' => array()
        );
        switch($school['rooster_system']){
            case 'untis2011':
            case 'untis2011-r1':
            case 'untis2012':
            case 'untis2012-r1':
                foreach($school['rooster_classes'] as $u){
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$u);
                    @curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
                    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    @curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    @curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                    $cd = curl_exec($ch);
                    if(empty($cd)){ die('[err] Kon het klassenoverzicht niet ophalen. Server error.'); }
                    curl_close($ch);
                    $dom = new domDocument;
                    @$dom->loadHTML($cd);
                    $as = $dom->getElementsByTagName('option');
                    foreach($as as $a){
                        $data['classes'][] = $a->nodeValue;
                    }
                }
                break;
        }
    }
            
    switch(strtolower(@$_GET['format'])){
        case 'json':
            header('Content-type: application/json');
            $output = json_encode($data);
            break;
        case 'html':
            header('Content-type: text/html; charset=UTF-8');
            $output = '<pre>'.print_r($data,true).'</pre>';
            break;
        case 'txt':       
    }
    
    $outputraw = $output;
    if(isset($_GET['callback'])){
        $output = $_GET['callback'] . '(' . $output . ')';
    }
            
    echo $output;
    @file_put_contents($filename,$outputraw);
    exit;
?>