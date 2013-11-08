<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
    
    #trabalha com post
    if(isset($_POST['acao']) && $_POST['acao'] != ''){
       $nome = strip_tags(trim(addslashes($_POST["nome"])));
	   $email = strip_tags(trim(addslashes($_POST["email"])));
       $mensagem = strip_tags(trim(addslashes($_POST["mensagem"])));
       
       $valida = $banco->ValidaTudo($_POST);
       if($valida){
            $msg = 'campo em branco';
       }else{
            echo "<script>alert('Mensagem Enviada com Sucesso! Aguarde Nosso Retorno.');</script>";
       }
    }
    
	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('suporte');
?>