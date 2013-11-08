<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
	
	#busca torneios
	$torneios = $banco->ListaTorneiosCompleta();
    
	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('torneios');
	$Conteudo = str_replace('<%TORNEIOS%>',$torneios,$Conteudo);
?>