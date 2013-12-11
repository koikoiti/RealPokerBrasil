<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
	
	#busca torneios
	$torneios = $banco->ListaTorneiosCompleta();

    #data de hj
    $data1 = date("d/m/Y");
    $data2 = date("d/m/Y", strtotime("+1 day"));
    
	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('torneios');
    $Conteudo = str_replace('<%DATA1%>',$data1,$Conteudo);
    $Conteudo = str_replace('<%DATA2%>',$data2,$Conteudo);
	$Conteudo = str_replace('<%TORNEIOS%>',$torneios,$Conteudo);
?>