# Conceitos Elixir

## Processos

Um processo executa código isolado de todos os outros processos.

### Criando um processo

Para criar um processo usa-se spawn/1 que retorna um identificador para o processo criado (PID):

```
pid = spawn(fn -> 1+2 end)

situacao_do_processo = Process.alive?(pid)
```

Para obter o PID do processo atual, usa-se self/0:

```
Process.alive?( self() )
```

### Enviando e recebendo mensagens

Cada processo possui uma caixa de correio, onde pode receber mensagens.

Para enviar uma mensagem de um processo para outro, usa-se send/2:

```
pid_destinatario = spawn( ... )

send( pid_destinatario, {:oi, "processo"} )
```

Para receber uma mensagem, usa-se receive/1, que suporte guardas e múltiplas cláusulas:

```
receive do
	{:oi, msg} -> msg
after
	1_000 -> "Depois de 1s, ainda não encontrei o que espero!"
end
```
Note que 'after ...' pode ser usado para timeout.  
'after 0' pode ser usado quando já se espera a mensagem na caixa de correio.

### Mostrando mensagens no shell

Usa-se flush/0:

```
flush()
```

### Criando processo com link

Ao criarmos um processo com spawn/1, se ele gerar algum erro, sua execução é interrompida e seu erro registrado, mas o processo pai continua em execução.

Caso isso seja indesejado, usa-se spawn_link/1, que propaga erros de processos filhos para seus pais.

```
pid = spawn_link( self(), fn -> raise "erro" end )
```

### Tasks

Tasks criam processos usando spawn/1 e spawn_link/1 como base, oferecendo mais recursos.  
Elas retornam uma tupla {:situação, PID}, por exemplo.

```
Task.start(fn -> raise "erro" end)

Task.start_link(fn -> raise "erro" end)
```
