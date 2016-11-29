Para a aplicação funcionar, após instalada, deve ser agendado tarefas para executar o envio de SMS.
Abaixo exemplo do agendamento feito no crontab em plataforma UNIX:

```
//para preparar o banco e criar os sms pra enviar
* * * * * curl -s http://www.lembrefacil.com.br/default/schedule/update  

//para enviar os sms pendentes que estavam na tal tabela do banco
* * * * * curl -s http://www.lembrefacil.com.br/default/schedule/send    
```