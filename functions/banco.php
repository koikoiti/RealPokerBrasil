<?php
	class banco{
		
		#Funcao que inicia conexao com banco
		function Conecta(){	
			$link = mysql_connect(DB_Host,DB_User,DB_Pass);
			if (!$link) {
				$this->ChamaManutencao();
			}
			$db_selected = mysql_select_db(DB_Database, $link);
			if (!$db_selected) {
				$this->ChamaManutencao();
			}
		}	
		
        #Valida Campos da TEla de Suporte
        function ValidaTudo($arr){
            foreach($arr as $key => $value){
                if($value == ''){
                    $retorno[] = $key;
                    }
                if($key == 'email'){
                    $result = $this->validaEmail($value);
                    if ($result === false){
                        $retorno[] = $key;
                    }
                }
            }
            return $retorno;
        }
        
        function validaEmail($email) {
			if (preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $email)){
				return true;
			}else{
				return false;
			}
		}
        
		#Busca os usuarios do site jogobrasil.net
		function BuscaUsuariosSite(){
			$raw = file_get_contents("http://jogobrasil.net/www/");
			$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
			#Retira os caracteres de identa��o html
			$content = str_replace($newlines, "", html_entity_decode($raw));
			#Seta onde ir� come�ar
			$start = strpos($content,'Online');
			#Define final
			$end = strpos($content,'id="wrap_content">',$start) + 18;
			$table = substr($content,$start,$end-$start);
			$aux = explode(">", $table);
			$usuarios = rtrim($aux[1], " </span");
			return $usuarios;
		}
		
		#funcao imprime conteudo
		function Imprime($Conteudo){
			$SaidaHtml = $this->CarregaHtml('modelo');
			
			#Busca os usuarios online do jogobrasil.net
			$usuarios = $this->BuscaUsuariosSite();
			
			$SaidaHtml = str_replace('<%USUARIOS%>',$usuarios,$SaidaHtml);
			$SaidaHtml = str_replace('<%CONTEUDO%>',$Conteudo,$SaidaHtml);
			$SaidaHtml = str_replace('<%URLPADRAO%>',UrlPadrao,$SaidaHtml);
			echo utf8_encode($SaidaHtml);
		}
		
		#funcao que chama manutencao
		function ChamaManutencao(){
			$filename = 'html/manutencao.html';
			$handle = fopen($filename,"r");
			$Html = fread($handle,filesize($filename));
			fclose($handle);
			$SaidaHtml = $this->CarregaHtml('modelo');
			$SaidaHtml = str_replace('<%CONTEUDO%>',$Html,$SaidaHtml);
			$SaidaHtml = str_replace('<%URLPADRAO%>',UrlPadrao,$SaidaHtml);
			echo $SaidaHtml;
		}
		
		#funcao que monta o conteudo
		function MontaConteudo(){
			#verifica se nao tem nada do lado da URLPADRAO
			if(!isset($this->Pagina)){
				return $Conteudo = $this->ChamaPhp('inicio');
			#verifica se a pagina existe e chama ela
			}elseif($this->BuscaPagina()){
				return $Conteudo = $this->ChamaPhp($this->Pagina);
			#Se nao tiver pagina chama 404
			}else{
				return $Conteudo = $this->CarregaHtml('404');
			}
		} 
		
		#Busca a pagina e verifica se existe
		function BuscaPagina(){
			$Sql = "Select * from g_paginas where nome = '".$this->Pagina."'";
			$result = $this->Execute($Sql);
			$num_rows = $this->Linha($result);
			if($num_rows){
				return true;
			}else{
				return false;
			}
		}
		
		#Fun��o que chama a pagina.php desejada.
		public function ChamaPhp($Nome){
			@require_once('lib/'.$Nome.'.php');
			return $Conteudo;
		}
	
		#Fun��o que monta o html da pagina
		public function CarregaHtml($Nome){
			$filename = 'html/'.$Nome.".html";
			$handle = fopen($filename,"r");
			$Html = fread($handle,filesize($filename));
			fclose($handle);
			return $Html;
		}
		
		#Funcao que executa uma Sql e retorna.
		static function Execute($Sql){
			$result = mysql_query($Sql);
			return $result;
		}
		
		#Funcao que retorna o numero de linhas 
		static function Linha($result){
			$num_rows = mysql_num_rows($result);
			return $num_rows;
		}
		
		#Funcao que redireciona para pagina solicitada
		function RedirecionaPara($nome){
			header("Location: ".UrlPadrao.$nome);
		}
		
		#Funcao que carrega as p�ginas
		function CarregaPaginas(){
			$urlDesenvolve = 'RealPokerBrasil';
			$primeiraBol = true;
			$uri = $_SERVER["REQUEST_URI"];
			$exUrls = explode('/',$uri);
			$SizeUrls = count($exUrls)-1;

			$p = 0;
			foreach( $exUrls as $chave => $valor ){
				if( $valor != '' && $valor != $urlDesenvolve ){
					$valorUri = $valor;
					$valorUri = strip_tags($valorUri);
					$valorUri = trim($valorUri);
					$valorUri = addslashes($valorUri);
					
					if( $primeiraBol ){
						$this->Pagina = $valorUri;
						$primeiraBol = false;
					}else{
						$this->PaginaAux[$p] = $valorUri;
						$p++;
					}
				}
			}
		}
	}
?>