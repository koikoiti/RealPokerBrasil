<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
	
	#busca torneios
	$torneios = $banco->ListaTorneiosCompleta();
<<<<<<< HEAD
	
    #data de hj
    $data = date("d/m/Y");
=======
>>>>>>> 43b0381880e9bfbbde32ec04f75413b388736e42
    
	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('torneios');
    $Conteudo = str_replace('<%DATA%>',$data,$Conteudo);
	$Conteudo = str_replace('<%TORNEIOS%>',$torneios,$Conteudo);
?>