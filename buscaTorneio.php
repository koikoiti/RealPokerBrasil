<?php
    #Busca os torneios
    $bancoDados = 'mysql1328.netcetera.co.uk';
    $user = 'rpb';
    $senha = 's1stem@';
    $baseDados = 'realpokerbrasil';
    
    #conecta no banco
	$link = mysql_connect($bancoDados,$user,$senha);
	if (!$link) {
		echo 'erro';
	}
	$db_selected = mysql_select_db($baseDados, $link);
	if (!$db_selected) {
		echo 'erro';
    }
	
    $SqlTruncate = "DELETE FROM g_torneios";
	$result = mysql_query($SqlTruncate);
	
	#faz a busca no site
    $dataInicio = date("d/m/Y");
    $dataFinal = date("d/m/Y", strtotime('+1 day'));
    $dtIni = explode("/", $dataInicio);
    $dtFim = explode("/", $dataFinal);
	$url = "http://jogobrasil.net/www/sections/tourney_history.php?start_time%5BDate_Day%5D=".$dtIni[0]."&start_time%5BDate_Month%5D=".$dtIni[1]."&start_time%5BDate_Year%5D=".$dtIni[2]."&end_time%5BDate_Day%5D=".$dtFim[0]."&end_time%5BDate_Month%5D=".$dtFim[1]."&end_time%5BDate_Year%5D=".$dtFim[2];
    $raw = file_get_contents($url);
	$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
	#Retira os caracteres de identação html
	$content = str_replace($newlines, "", html_entity_decode($raw));
	#Seta onde irá começar				
	$start = strpos($content,'<table class="tableTourney');
	#Define final
	$end = strpos($content,'</table>',$start) + 8;
	$table = substr($content,$start,$end-$start);
	preg_match_all("|<tr>(.*)</tr>|U",$table,$rows);
	foreach($rows[0] as $key=>$value){
		if($key != 0){
			$linhaArray = explode("<td>", $value);
			#pega id
			$id = explode("#", $linhaArray[1]);
			$id = rtrim($id[1], "</a></td>");
			#Pega a data e hora
			$auxDataHora = explode(" ", $linhaArray[2]);
			$auxData = explode("/",$auxDataHora[0]);
			#data
			$data = date("Y") . "-" . $auxData[1] . "-" . $auxData[0];
			#hora
			$hora = rtrim($auxDataHora[1], "</td>");
			#pega nome torneio
			$nome = explode("_", $linhaArray[3]);
			$nome = rtrim($nome[2], "</a></td>");
			#Inscritos
			$inscritos = rtrim($linhaArray[4], "</td>");
			#premiação
			$premiacao = explode(" ", $linhaArray[5]);
			$premiacao = rtrim($premiacao[1], "</td>");
			#Status
			$status = explode("<", $linhaArray[6]);
			$status = $status[0];
			
			$SqlInsert = "INSERT INTO g_torneios (idtorneio, nome, data, hora, inscritos, premiacao, status)
						 VALUES ('$id', '$nome', '$data', '$hora', '$inscritos', '$premiacao', '$status')";
			$result = mysql_query($SqlInsert);
		}
	}
	mysql_close($link);
?>