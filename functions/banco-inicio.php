<?php
	class bancoinicio extends banco{
		
		#NAO ESTA USANDO ! ESTA SENDO USADA NO CRON DO HOSTINGER
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
            $horaAgora = date('H:i:s');
            $dataAgora = date('Y-m-d');
			$max = 25;
			$Auxilio = parent::CarregaHtml('torneios-inicio');
			$Sql = "SELECT * FROM g_torneios WHERE data >= '$dataAgora'
					AND status <> 'Completed'
					AND inscritos <> '0'
                    AND time_to_sec(hora) > time_to_sec('$horaAgora')
					ORDER BY inscritos ASC, premiacao DESC
					LIMIT 0, 3";
			$result = parent::Execute($Sql);
			$num_rows = parent::Linha($result);
			if($num_rows){
				
				while($rs = mysql_fetch_array($result, MYSQL_ASSOC)){
					$Linha = $Auxilio;
					$tamanho = strlen($rs['nome']);
					if($tamanho > $max){
						$nome = substr_replace($rs['nome'],'(...)',$max-5,$tamanho-20);
					}else{
						$nome = $rs['nome'];
					}
					$Linha = str_replace("<%NOME%>", ucfirst(strtolower($nome)), $Linha);
					$Linha = str_replace("<%ID%>", $rs['idtorneio'], $Linha);
					$Linha = str_replace("<%INSCRITOS%>", $rs['inscritos'], $Linha);
					$Linha = str_replace("<%PREMIACAO%>", $rs['premiacao'], $Linha);
					$Linha = str_replace("<%DATA%>", date('d/m/Y',strtotime($rs['data'])).' - '.substr($rs['hora'],'0',-3), $Linha);
					
					#Traduz para PT-BR
					switch ($rs['status']){
							case 'Registering':
							$status = 'Registrando';
							break;
							case 'Running':
							$status = 'Rolando';
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
		
	function ListaTorneiosCompleta(){
			$max = 60;
            $count = 0;
			$Auxilio = parent::CarregaHtml('torneios-itens');
			$Sql = "SELECT * FROM g_torneios WHERE data >= CURDATE() - 1
					ORDER BY data DESC, hora DESC
					";
			$result = parent::Execute($Sql);
			$num_rows = parent::Linha($result);
			if($num_rows){
				while($rs = mysql_fetch_array($result, MYSQL_ASSOC)){
					$Linha = $Auxilio;
					$tamanho = strlen($rs['nome']);
					if($tamanho > $max){
						$nome = substr_replace($rs['nome'],'(...)',$max-5,$tamanho-55);
					}else{
						$nome = $rs['nome'];
					}
                    if($count % 2 == 0){
                        $classe = "linha1";
                    }else{
                        $classe = "linha2";
                    }
					$Linha = str_replace("<%NOME%>", ucfirst(strtolower($nome)), $Linha);
					$Linha = str_replace("<%ID%>", $rs['idtorneio'], $Linha);
                    $Linha = str_replace("<%CLASSE%>", $classe, $Linha);
					$Linha = str_replace("<%INSCRITOS%>", $rs['inscritos'], $Linha);
					$Linha = str_replace("<%PREMIACAO%>", $rs['premiacao'], $Linha);
					$Linha = str_replace("<%STATUS%>", $rs['status'], $Linha);
					$Linha = str_replace("<%DATA%>", date('d/m/Y',strtotime($rs['data'])).' - '.substr($rs['hora'],'0',-3), $Linha);
					
					#Traduz para PT-BR
					switch ($rs['status']){
							case 'Registering':
							$status = 'Registrando';
							break;
							case 'Running':
							$status = 'Rolando';
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
                    $count++;
				}
			}
			return $Torneios;
		}
	}#Fim da classe
?>