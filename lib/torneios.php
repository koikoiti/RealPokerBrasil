<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
	
	#busca torneios
	$torneios = $banco->ListaTorneiosCompleta();
	
    #data de hj
    $data = date("d/m/Y");
    
	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('torneios');
    $Conteudo = str_replace('<%DATA%>',$data,$Conteudo);
	$Conteudo = str_replace('<%TORNEIOS%>',$torneios,$Conteudo);
?>