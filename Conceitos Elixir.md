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

## Entrada e Saída

### Módulo IO

O módulo IO possui operações de leitura e escrita para várias entradas e saídas.

#### Escrita

Para escrever na saída padrão, usa-se IO.puts/1:

```
IO.puts("Olá mundo!")
```

Por padrão, a saída é a saída padrão (:stdout).  
Para definir a saída, como a saída de erro padrão, usa-se IO.puts/2:

```
IO.puts(:stderr, "Erro!")
```

#### Leitura

Para ler da entrada padrão, usa-se IO.gets/1:

```
IO.gets("Digite um número: ")
```

### Módulo File

O módulo File possui operações para trabalhar com arquivos.

#### Abrir e Fechar

Para abrir um arquivo, usa-se File.open/1 ou File.open/2, que retorna uma tupla {:estado, PID_arquivo}.  
Para fechar um arquivo, usa-se File.close/1.

```
{:ok, arquivo} = File.open("meu_arquivo");

File.close(arquivo);
```
Elixir trabalha com arquivos como processos, logo o nosso "ponteiro" para o arquivo é na verdade um PID.

#### Ler e Escrever

Por padrão um arquivo é aberto em modo binário, necessitando das funções corretas para interagir com ele: IO.binread/2 e IO.bitwrite/2.

Pode se definir o tipo de abertura do arquivo. Por exemplo, usando :utf8, o módulo interpreta os bytes lidos como codificados em UTF-8.

```
{:ok, arquivo} = File.open("meu_arquivo", [:write]);

IO.binwrite(arquivo, "Nova informação!")

File.close(arquivo);

# 'entrada' é igual a "Nova Informação!"
{:ok, entrada} = File.read("meu_arquivo")
```

As funções de File têm duas variantes, uma normal e uma com '!' no final, por exemplo: File.read/1 e File.read!/1.

As normais retornam a tupla {:estado, PID_arquivo}, enquanto as com '!' retornam o conteúdo do arquivo.  

Usar as normais é bom para casamento de padrões, enquanto as com '!' são mais diretas e geram erros descritivos se o arquivo não é encontrado.

```
# Se não encontrar "meu_arquivo" dispara erro descritivo
conteudo_arquivo = File.read!("meu_arquivo")


tupla_estado_arquivo = File.read("meu_arquivo")

case tupla_estado_arquivo do
	{:ok, conteudo} -> ...
	{:error, razao} -> ...
end
```

#### Módulo Path

As funções de manipulação de arquivos do módulo File precisam trabalhar com o caminho do arquivo. O módulo Path facilita isso.

```
# 'caminho' guarda algo como: "/Users/usuario/meu_arquivo"
caminho = Path.expand("~/meu_arquivo")

File.open(caminho, [:write])
```

## Importando e Renomeando módulos

Elixir possui ferramentas para reusar código de outros módulos, até mesmo com outros nomes.

### alias

Permite a renomeação de módulos.

Um `alias` só é válido no escopo em que foi definido.

```
defmodule MeuModulo do
	alias Modulo.SubModulo, as: ModuloLegal

	# Agora, ao usar 'ModuloLegal', na verdade uso 'Modulo.SubModulo'
end
```

Note que ainda pode-se usar o nome verdadeiro do módulo.  
Caso hajam múltiplos módulos com submódulo 'SubModulo', usa-se o termo completo para referenciar o módulo adequado (ModuloCerto.SubModulo).

```
alias Modulo.SubModulo

# é equivalente a

alias Modulo.SubModulo, as: SubModulo
```

### require

Permite a importação de macros de um módulo.

Um `require` só é válido no escopo em que foi definido.

```
# 'is_odd' não é uma função, mas sim um macro de Integer

# Isso dispara erro
Integer.is_odd(5)


require(Integer)

# Agora isso é ok
Integer.is_odd(5)
```

Funções públicas de um módulo disponível ficam automaticamente disponíveis, mas seus macros precisam do `require`.

### import

Permite o acesso a funções e macros de outro módulo sem precisar referenciar o módulo.

Um `import` só é válido no escopo em que foi definido.

```
defmodule MeuModulo do
	import Integer

	def eh_impar?(x) do
		is_odd(x)
	end
end
```

Ao usar um `import`, automaticamente usa-se `require` também, logo macros do módulo ficam disponíveis.

É boa prática usar `only` e `except` para definir melhor o que se quer importar.

```
import Integer, only: [is_odd: 1]

import Integer, except: [is_odd: 1]
```

### use

Permite a injeção do código de um módulo.

```
defmodule MeuModulo do
	use Ferramenta, opcao: :valor

	# Agora, todo o código de ferramenta foi injetado aqui e posso fazer uso dele
end
```

Ao usar um `use`, automaticamente é usado um `require` também.

Como use permite que qualquer código seja executado, precisa-se ter cuidado e entender o módulo importado.

### Referenciando vários módulos

É possível usar quaisquer de `alias`, `require`, `import` e `use` para referenciar vários módulos.

```
alias MeuAplicativo.{Modulo1, Modulo2, Modulo3}
```

### Boas Práticas

Para simplificar e manter o código organizado, é melhor usar o primeiro que resolver o problema:

1. alias
2. require
3. import
4. use

Ou seja, se `alias` resolve o problema, não use `import`.
