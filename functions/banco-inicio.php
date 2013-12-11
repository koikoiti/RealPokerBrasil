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
					$result = parent::Execute($SqlInsert);
				}
			}
		}#Fim function BuscaTorneios
		
		function ListaTorneios(){
            $horaAgora = date('H:i:s');
            $dataAgora = date('Y-m-d');
			$max = 25;
            #AND time_to_sec(hora) > time_to_sec('$horaAgora')
			$Auxilio = parent::CarregaHtml('torneios-inicio');
			$Sql = "SELECT * FROM g_torneios WHERE data >= '$dataAgora'
					AND status <> 'Completed'
					AND inscritos >= '0'
                    AND hora > '$horaAgora'
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
							$status = 'Concluído';
							break;
							case 'Announced':
							$status = 'Anunciado';
							break;
					}
					$Linha = str_replace("<%STATUS%>", $status, $Linha);
					$Torneios .= $Linha;
				}
			}else{
			     $Torneios = '
                 <div class="torneios">
                    <center style="padding-top: 60px;">Nenhum Registro Encontrado.</center>
                 </div>
                 
                 ';
			}
			return $Torneios;
		}
		
	function ListaTorneiosCompleta(){
			$max = 60;
            $count = 0;
            $data = date("Y-m-d");
			$Auxilio = parent::CarregaHtml('torneios-itens');
			$Sql = "SELECT * FROM g_torneios WHERE data >= '$data'
					ORDER BY data ASC, hora ASC
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
					$Linha = str_replace("<%DATA%>", date('d/m/Y',strtotime($rs['data'])).' - '.substr($rs['hora'],'0',-3), $Linha);
					
					#Traduz para PT-BR
					switch ($rs['status']){
							case 'Registering':
							$status = '<span style="color: #33CC00">Registrando</span>';
							break;
							case 'Running':
							$status = 'Rolando';
							break;
							case 'Completed':
							$status = 'Concluído';
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
        
        function EnviaEmailContato($nome,$email,$mensagem){
            #Carrega classe MAILER
			include_once("./app/PHPMailer/class.phpmailer.php");
			include("./app/PHPMailer/class.smtp.php");

			$mail = new PHPMailer();
			// Charset para evitar erros de caracteres
			$mail->Charset = 'UTF-8';
			// Dados de quem está enviando o email
			$mail->From =  $email;
			$mail->FromName = $nome;
				
			// Setando o conteudo
			$mail->IsHTML(true);
			$mail->Subject = 'Contato gerado atraves do site RealPokerBrasil';
			$mail->Body = parent::CarregaHtml('EmailEmpresa');
            $mail->Body = str_replace("<%NOME%>", $nome, $mail->Body);
            $mail->Body = str_replace("<%EMAIL%>", $email, $mail->Body);
            $mail->Body = str_replace("<%MENSAGEM%>", $mensagem, $mail->Body);
            
            // Validando a autenticação
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			$mail->Host     = "ssl://smtp.gmail.com";
			$mail->Port     = 465;
			$mail->Username = 'realpokerbrasil@gmail.com';
			$mail->Password = '15052010';

			// Setando o endereço de recebimento
			$mail->AddAddress('realpokerbrasil@gmail.com','realpokerbrasil@gmail.com');
            //var_dump($mail);die;
			// Enviando o e-mail para o usuário
            if($mail->Send()){
                echo "<script>alert('Mensagem Enviada com Sucesso! Aguarde Nosso Retorno.');</script>";
            }else{
                echo "<script>alert('Falha ao enviar o email! Tente novamente mais tarde.');</script>";
            }
        }
	}#Fim da classe
?>