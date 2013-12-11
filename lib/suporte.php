<?php
	#include das funcoes da tela inico
	include('functions/banco-inicio.php');

	#Instancia o objeto
	$banco = new bancoinicio();
    
    #declara variavel
    $msg = '';
    
    #trabalha com post
    if(isset($_POST['acao']) && $_POST['acao'] != ''){
       $nome = strip_tags(trim(addslashes($_POST["nome"])));
	   $email = strip_tags(trim(addslashes($_POST["email"])));
       $mensagem = strip_tags(trim(addslashes($_POST["mensagem"])));
       
       $valida = $banco->ValidaTudo($_POST);
       if($valida){
            $msg = "<tr><td colspan='2'><div class='alert alert-info'><center><strong>Erro! </strong>Preencha todos os campos corretamente.</center></div></td></tr>";
       }else{
            #enviar email
            $banco->EnviaEmailContato($nome,$email,$mensagem);
       }
    }
    
	#Imprimi valores
	$Conteudo = $banco->CarregaHtml('suporte');
    $Conteudo = str_replace('<%MSG%>',$msg,$Conteudo);
?>