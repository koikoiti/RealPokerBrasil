<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();

	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('inicio');
?>