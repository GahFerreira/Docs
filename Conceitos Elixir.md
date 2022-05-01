# Conceitos Elixir

## Estruturas de Dados Chave-Valor

### Keyword Lists

Keyword Lists, ou Listas de Palavras-Chave, são listas em que cada elemento é uma tupla {chave: valor}.

O primeiro elemento da tupla é a chave, e o segundo é o valor (key-value pair).

A sintaxe da Keyword List é bastante permissiva, além de possuir todos os recursos de listas.

```
[trim: true] == [{trim: true}]  # São equivalentes

# Pode-se acessar o elemento de uma Keyword List por [:chave]
minha_lista = [a: 1]
minha_lista[:a]
```

São comumente usadas para passar opções para funções.

```
# Exemplo de Keyword List passada para função
String.split("1  2  3", " ", [trim: true])

# Não precisa de [] quando é o último parâmetro de uma função
String.split("1  2  3", " ", trim: true)
```

Informações notáveis sobre Keyword Lists:

- Chaves têm de ser átomos
- Chaves são ordenadas
- Chaves não precisam ser únicas

Em caso de chaves duplicadas, ao buscar-se por uma chave `minha_lista[:chave]`, retorna-se o primeiro elemento com aquela chave.

### Mapas

Mapas são estruturas de dados de Chave-Valor em que cada chave é única.

Chaves em mapas podem ser de qualquer tipo e não são ordenadas, facilitando casamento de padrões que ocorrem fora de ordem também.

A sintaxe de um mapa é `%{chave => valor, chave => valor, ...}`, e há diversas maneiras de acessar elementos.

```
meu_mapa = %{:chave => 1, 2 => :valor}

%{:chave => valor} = meu_mapa  # 'valor' recebe 1

outro_valor = meu_mapa[:chave]

mais_outro_valor = meu_mapa.chave  # Válido só se 'chave' for um átomo

ainda_outro_valor = Map.get(meu_mapa, :chave)  # Usando o módulo Map
```

Um mapa só não casa padrão caso a chave a ser casada não esteja presente no mapa.  
Ou seja, um mapa vazio `%{}` casa sempre.

```
%{:c => 1} = %{:a => 1, :b => 1}  # Dispara erro!
```

Para atualizar um valor no mapa, a sintaxe é `%{meu_mapa | chave_existente => novo_valor}`. Note que não é possível adicionar itens ao mapa nessa sintaxe.

```
meu_mapa = %{:chave => 1, 2 => :valor}

%{meu_mapa | :chave => :novo_valor}
```

Mapas funcionam muito bem quando as suas chaves são átomos.

1. Se todas forem átomos, na declaração, pode-se usar a sintaxe `atomo:` na chave, invés de `chave =>`.
2. Para acessar uma chave que é átomo, pode-se usar `mapa.chave`.

```
meu_mapa = %{chave1: :valor1, chave2: :valor2}

meu_valor = meu_mapa.chave1
```

### Structs

Structs são construídas baseadas em mapas, provendo valores padrão, checagens em tempo de compilação e número fixo de campos.

Uma struct é definida dentro de um módulo usando `defstruct`, obtendo para si o nome do módulo.

Para usar uma struct, usa-se `%nome_modulo{}`.

```
defmodule Usuario do
	defstruct nome: "Lynn", idade: 25
end

lynn = %Usuario{}
felipe = %Usuario{nome: "Felipe"}

# 'lynn' é igual a %Usuario{idade: 25, nome: "Lynn"}
# 'felipe' é igual a %Usuario{idade: 25, nome: "Felipe"}
```

Para modificar um campo da struct, usa-se a sintaxe de atualização `|`, igual nos mapas.

Como não há mudança na estrutura da struct usando essa sintaxe, pode-se referenciar uma outra struct do mesmo módulo ao usar o operador.

```
%Usuario{lynn | idade: 26}

# Atualizando a partir dos valores de outra struct
vinicius = {lynn | nome: "Vinicius"}
```

Structs também permitem casamento de padrão igual a mapas. O casamento de padrão só ocorre caso sejam ambas structs e, quando convém, do mesmo módulo.

Para obter o módulo de uma struct, usa-se `minha_struct.__struct__`

```
defmodule Usuario do
	defstruct nome: "Lynn", idade: 25
end

defmodule User do
	defstruct nome: "Lynn", idade: 25
end

%User{} = %{}  # Struct não casa com mapa

%User{} = %Usuario{}  # Apesar de iguais internamente, não casam

%User{}.__struct__  # Retorna 'User'
```

Caso não seja desejável declarar valor padrão, declara-se os campos entre colchetes, com os que não tiverem valor padrão primeiro. O valor padrão deles será assumido como nil.

Já para forçar a declaração de um valor padrão, pode-se usar o atributo de módulo `@enforce_keys`.

```
defmodule Usuario do
	@enforce_keys [:nome]

	defstruct [:idade, nome: "Lynn"]
end
```

Tentar acessar, adicionar ou modificar um campo não existente em uma struct não é permitido e dispara erro.

Enquanto struct são construídas baseadas em mapas (logo funções do módulo Map funcionam em structs), protocolos de mapas não são implementados para structs (não dá para acessar um campo com `struct[:campo]`, por exemplo).

### Palavras-Chaves e Blocos

Há algumas palavras chaves em Elixir que permitem sintaxe de bloco:

1. do
2. else
3. catch
4. rescue
5. after

```
if true, do: "This", else: "That"

# é equivalente a

if true do
	"This"
else
	"That"
end
```

### Aninhamento de Estruturas

Há alguns recursos prontos para lidar com aninhamento de estruturas em Elixir.

Macros como put_in/2 e update_in/2 são muito úteis para atualizar estruturas aninhadas.

```
meu_mapa = %{a: [:alfa, :beta, :gama], b: 2}

put_in meu_mapa.b, 3

update_in meu_mapa.a, fn lista_interna -> List.delete(lista_interna, :gama) end

# meu_mapa agora é igual a %{a: [:alfa, :beta], b: 3}
```

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

Para receber uma mensagem, usa-se receive/1, que suporta guardas e múltiplas cláusulas:

```
receive do
	{:oi, msg} -> msg
after
	1_000 -> "Depois de 1s, ainda não encontrei o que espero!"
end
```
Note que `after ...` pode ser usado para timeout.  
`after 0` pode ser usado quando já se espera a mensagem na caixa de correio.

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

Pode-se definir o tipo de abertura do arquivo. Por exemplo, usando :utf8, o módulo interpreta os bytes lidos como codificados em UTF-8.

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
Caso hajam múltiplos módulos com submódulo 'SubModulo', usa-se o termo completo para referenciar o módulo adequado (`ModuloCerto.SubModulo`).

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

## Atributos de Módulo

Atributos de módulo se iniciam com `@` e auxiliam no uso dos módulos.

### Documentação

A documentação de um módulo é feita através dos seguintes atributos:

- `@moduledoc`: Documentação sobre o módulo
- `@doc`: Documentação sobre a função ou macro a seguir
- `@spec`: Especifica os tipos da função a seguir
- `@behaviour`: Especifica um protocolo ou comportamento definido pelo usuário

O Elixir trabalha com Here docs para documentação, que mantém a formatação do texto dentro. É usado com três aspas duplas.

```
defmodule MeuModulo do
	@moduledoc """
		Provê funções para o funcionamento do programa.
	"""

	@doc """
		Calcula a soma de dois números.
	"""
	def soma(x, y), do: x + y
end
```
### Constantes

É possível definir uma constante com a sintaxe de atributo de módulo.

Pode-se usar funções para definir o valor da constante, mas note que a função será chamada em tempo de compilação.

```
defmodule MeuModulo do
	@minha_constante 10

	def soma(x), do: x + @minha_constante
end
```

Note que a constante precisa ser acompanhada do seu valor na mesma linha. Se houver quebra de linha, o Elixir vai interpretar como tentativa de leitura da constante.  
Uma constante não definida gera erro ao tentar ser lida.

### Armazenamento Temporário

Atributos de módulo podem ainda ser usados como armazenamento temporário para definir como o resto do módulo será compilado.

Pode-se, por exemplo, acumular vários em um atributo usando `accumulate: true`

```
defmodule MeuModulo do
	Module.register_attribute __MODULE__, :acumulador, accumulate: true

	@acumulador :valor1
	@acumulador :valor2    # Aqui @acumulador é [:valor1, :valor2]
end
```

## Protocolos

Protocolos funcionam como um contrato. Para um módulo ter acesso às funcionalidades de um protocolo, ele deve implementar suas funções.

Pode-se pensar em protocolos como classes abstratas ou interfaces de orientação a objetos.

Protocolos resolvem o problema de uma função ter que tratar os vários tipos de dados que podem ser passados para ela.

### Declaração e Implementação

Um protocolo é declarado com `defprotocol`, e a implemetação de um protocolo por um tipo com `defimpl protocolo, for: nome_classe`.

```
defprotocol Utilidade do
	def meu_tipo(valor)
end

defimpl Utilidade, for: Tuple do
	def meu_tipo(tupla), do: "tupla"
end

minha_tupla = {1, 2}

meu_tipo(minha_tupla)  # Retorna "tupla"
```

Uma função de protocolo pode ter mais de 1 parâmetro. Nesse caso, a implementação do protocolo a ser executada depende do tipo do primeiro parâmetro da função.

Caso não haja um protocolo para aquele tipo, um erro é disparado.

### Any

Pode-se definir uma implementação padrão de um protocolo para qualquer tipo de dados usando `Any`.

Dessa forma, qualquer módulo que deseja usar aquele protocolo com a implementação pode apenas usar `@derive [protocolo]`.

```
defimpl Utilidade, for: Any do
	def meu_tipo(_), do: "tipo qualquer"
end

defmodule Usuario do
	@derive [Utilidade]
	defstruct [:nome]
end

Utilidade.meu_tipo(%Usuario{})  # Retorna "tipo qualquer"
```

No entanto, se um módulo que não usa `@derive [protocolo]` tentar usar o protocolo, um erro ainda é disparado.

Caso seja desejável que todos os módulos usem a implementação padrão de um protocolo, usa-se `@fallback_to_any true` na definição do protocolo.

```
defprotocol Utilidade do
	@fallback_to_any true
	def meu_tipo(valor)
end
```

## Compreensões

Para iterar sobre uma estrutura de dados, existe a sintaxe `for <- ..., do: ...`.

```
lista = for n <- [1, 2, 3, 4], do: n * n

# lista é igual a [1, 4, 9, 16]
```

Ela é dividida em 3 partes:

1. Gerador: as estruturas de dados geradas que serão iteradas sobre.
2. Filtros: alguma função ou casamento de padrão que defina os itens da estrutura que serão considerados.
3. Coletável: onde e como será guardado o resultado.

```
lista = for n <- 1..5, n <= 3, do: n - 1

# lista é igual a [0, 1, 2]

# '<- 1..5' é o gerador
# 'n <= 3' é o filtro
# [0, 1, 2] é o coletável, colocado dentro de lista
```

É possível colocar múltiplos geradores e filtros.

Note que as variáveis atribuídas dentro da compreensão não são refletidas fora dela, semelhante a serem declaradas dentro de um bloco.

```
# Compreensão que retorna os números positivos de 'm'

lista = for n <- -10..1, m <- -100..2, n == 0, m > 0, do: n + m

# lista é igual a [1, 2]

# 'n <- -10..1' e 'm <- -100..2' são geradores
# 'n == 0' e 'm > 0' são filtros
# [1, 2] é o coletável
```

Para mudar onde o coletável será colocado, usa-se `into`.

```
mapa = for {chave, valor} <- %{a: 1, b: 2}, into: %{}, do: {chave, valor + 1}

# mapa é igual a %{a: 2, b: 3}
```
