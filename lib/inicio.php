<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
		
	#Busca os torneios do dia
	#$banco->BuscaTorneios();
	
	$torneios = $banco->ListaTorneios();
	
	#Imprime valores
	$Conteudo = $banco->CarregaHtml('inicio');
	$Conteudo = str_replace('<%TORNEIOS%>',$torneios,$Conteudo);
?>
