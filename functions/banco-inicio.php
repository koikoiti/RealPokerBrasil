<?php
	class bancoinicio extends banco{
		
		#Busca os torneios
		function BuscaTorneios(){
			#faz a busca no site
			$dia = date("d");
			$mes = date("m");
			$ano = date("Y");
			$url = "http://jogobrasil.net/www/sections/tourney_history.php?start_time%5BDate_Day%5D=$dia&start_time%5BDate_Month%5D=$mes&start_time%5BDate_Year%5D=$ano&end_time%5BDate_Day%5D=$dia&end_time%5BDate_Month%5D=$mes&end_time%5BDate_Year%5D=$ano";
			$raw = file_get_contents($url);
			$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
			#Retira os caracteres de identa��o html
			$content = str_replace($newlines, "", html_entity_decode($raw));
			#Seta onde ir� come�ar				
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
					#premia��o
					$premiacao = explode(" ", $linhaArray[5]);
					$premiacao = rtrim($premiacao[1], "</td>");
					#Status
					$status = explode("<", $linhaArray[6]);
					$status = $status[0];
					
					$SqlInsert = "INSERT INTO g_torneios (idtorneio, nome, data, hora, inscritos, premiacao, status)
								 VALUES ('$id', '$nome', '$data', '$hora', '$inscritos', '$premiacao', '$status')";
					$result = parent::Execute($SqlInsert);
				}
			}
		}#Fim function BuscaTorneios
		
		function ListaTorneios(){
			$max = 20;
			$Auxilio = parent::CarregaHtml('torneios-inicio');
			$Sql = "SELECT * FROM g_torneios WHERE data >= CURDATE() - 1
					AND status <> 'Completed'
					AND inscritos <> '0'
					ORDER BY inscritos ASC, premiacao DESC
					LIMIT 0, 3";
			$result = parent::Execute($Sql);
			$num_rows = parent::Linha($result);
			if($num_rows){
				while($rs = mysql_fetch_array($result, MYSQL_ASSOC)){
					$Linha = $Auxilio;
					$tamanho = strlen($rs['nome']);
					if($tamanho > $max){
						$nome = substr_replace($rs['nome'],'(...)',$max,$tamanho-$max);
					}else{
						$nome = $rs['nome'];
					}
					$Linha = str_replace("<%NOME%>", ucfirst(strtolower($nome)), $Linha);
					$Linha = str_replace("<%ID%>", $rs['idtorneio'], $Linha);
					$Linha = str_replace("<%INSCRITOS%>", $rs['inscritos'], $Linha);
					$Linha = str_replace("<%PREMIACAO%>", $rs['premiacao'], $Linha);
					$Linha = str_replace("<%DATA%>", date('d/m/Y',strtotime($rs['data'])).' - '.rtrim($rs['hora'],':00'), $Linha);
					
					#Traduz para PT-BR
					switch ($rs['status']){
							case 'Registering':
							$status = 'Registrando';
							break;
							case 'Running':
							$status = 'Running';
							break;
							case 'Completed':
							$status = 'Conclu�do';
							break;
							case 'Announced':
							$status = 'Anunciado';
							break;
					}
					$Linha = str_replace("<%STATUS%>", $status, $Linha);
					$Torneios .= $Linha;
				}
			}
			return $Torneios;
		}
	}#Fim da classe
?>