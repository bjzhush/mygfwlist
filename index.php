<?php

function success($msg) {
   echo '<font color=green>'.$msg.'</font>';
}

function fail($msg) {
   echo '<font color=red>'.$msg.'</font>';
    
}

function getGfwList()
{
   $html = file_get_contents(base64_decode('aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL2dmd2xpc3QvZ2Z3bGlzdC9tYXN0ZXIvZ2Z3bGlzdC50eHQ='));
    if ($html === false) {
       fail('get origin gfwlist failed');
    }
    $res = file_put_contents('gfwlist.txt', $html );
    if (!$res) {
       fail('write gfwlist.txt failed') ;
    } else {
       success('write gfwlist.txt succeed') ;
    }
}

function showList()
{
    $all = file_get_contents('list.json');
    if ($all === false) {
        exit('no list.json file error');
    } else {
        $allDomains = json_decode($all, TRUE);
    }
    foreach ($allDomains as $domain) {
        echo $domain['d'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$domain['t']."<br>";
    }

}

function addDomain($get)
{
    $domainNew = $get['d'] ? $get['d'] : null;
    if (is_null($domainNew)) {
       exit('no domain found from get');
    }
    $all = file_get_contents('list.json');
    if ($all === false) {
       $allDomains = [];
    } else {
       $allDomains = json_decode($all, TRUE);
    }
    foreach ($allDomains as $domain) {
       if ($domain['d'] == $domainNew) {
          exit('existed domain '.$domainNew);
       }
    }
    
    $allDomains[] = [
        'd' => $domainNew,
        't' => date('Y-m-d H:i:s'),
    ];
    
    $writeRes = file_put_contents('list.json', json_encode($allDomains));
    if ($writeRes === false) {
       exit('write list.json failed');
    } else {
       exit('domain '.$domainNew.' added');
    }
}

function delDomain($get)
{

    $domainNew = $get['d'] ? $get['d'] : null;
    if (is_null($domainNew)) {
        exit('no domain found from get');
    }
    $all = file_get_contents('list.json');
    if ($all === false) {
        $allDomains = [];
    } else {
        $allDomains = json_decode($all, TRUE);
    }
    $delFlag = false;
    foreach ($allDomains as $k => $domain) {
        if ($domain['d'] == $domainNew) {
            unset($allDomains[$k]);
            $delFlag = true;
        }
    }
    if ($delFlag === false) {
       exit('found none match record to delete');
    }

    $writeRes = file_put_contents('list.json', json_encode($allDomains));
    if ($writeRes === false) {
        exit('write list.json failed');
    } else {
        exit('domain '.$domainNew.' deled');
    }
    
}


function generate()
{
    getGfwList();
    $baseList = file_get_contents('gfwlist.txt');
    $baseDe = base64_decode($baseList);
    $rows = explode(PHP_EOL, $baseDe);

    $rows[]  = '!-----zs custom rule start----';
    
    //get my domains
    $all = file_get_contents('list.json');
    if ($all === false) {
        exit('no list.json found');
    } else {
        $allDomains = json_decode($all, TRUE);
    }
    //put into
    foreach ($allDomains as $domain) {
        $rows[] = '|'.$domain;
        echo $domain.'<br>';
    }
    $rows[]  = '!-----zs custom rule start----';
    $all = implode(PHP_EOL, $rows);
    $str = base64_encode($all);
    $res = file_put_contents('list.txt',$str );
    if (!$res) {
       fail('save to list.txt failed');
    } else {
       success('gene succeed') ;
    }
    
}

//auth
if (is_null($_COOKIE['user'])) {
   exit('auth failed');
}

//help 
if(isset($_GET['help'])) {
   echo '
        /?a=list      : list domains <br>
        /?a=add&d=xxx : add domain   <br>
        /?a=del&d=xxx : del domain   <br>
        /?a=gene      : gene list
   ';
   exit;
}

//get action
$action = $_GET['a'] ? $_GET['a'] : null;
if (is_null($action)) {
   exit('no action found');
}

$get = $_GET;

//run
try {

    if($action == 'list') {
        showList();
    } elseif($action == 'getgfw') {
        getGfwList();
    } elseif($action == 'add') {
        addDomain($get);
    } elseif($action == 'del') {
        delDomain($get);
    } elseif($action == 'gene') {
        generate();
    } else {
        exit('unknown action name');
    }
    
} catch (Exception $e) {
   echo $e->getMessage(); 
}

