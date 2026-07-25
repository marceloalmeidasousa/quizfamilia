# Prompt para gerar perguntas do Quiz em Família

Use este prompt no ChatGPT, Gemini, Claude ou outra IA para expandir a base
`resources/data/perguntas.json`. Rode uma vez para cada nível trocando os campos
entre `<< >>`.

---

## Prompt (copie e cole)

```text
Você é um gerador de perguntas de quiz em português do Brasil.

Gere << QUANTIDADE >> perguntas de múltipla escolha para o nível "<< NÍVEL >>".

Público-alvo do nível:
- criança: 3 a 6 anos (NÍVEL FÁCIL) — frases bem curtas. TEMAS PRINCIPAIS:
  desenhos animados infantis. Inclua Disney (Mickey, Frozen, Moana, Rei Leão,
  Toy Story, princesas, Encanto, etc.) MAS NÃO só Disney: misture também Peppa Pig,
  Bluey, Patrulha Canina (Paw Patrol), Galinha Pintadinha, Bob Esponja, PJ Masks,
  Turma da Mônica, Masha e o Urso e outros desenhos adequados a 3–6 anos.
  Meta aproximada: cerca de metade Disney e metade outros desenhos.
  Nada de violência, terror ou temas adultos.
  EXATAMENTE 2 opções por pergunta (não 4!).
- adolescente: 7 a 14 anos (NÍVEL MÉDIO) — misture conhecimentos gerais do ensino
  fundamental (geografia, ciências, matemática, esportes) COM cultura teen e
  influencers: TikTok, YouTube, Twitch, MrBeast, Khaby Lame, Charli D'Amelio,
  Felipe Neto, Whindersson Nunes, Virginia Fonseca e outros creators adequados
  à idade. Sem temas adultos, fofoca pesada ou conteúdo impróprio.
  EXATAMENTE 4 opções por pergunta.
- adulto: 15 anos ou mais (NÍVEL FÁCIL A MODERADO) — cultura geral acessível:
  história, ciências, arte, literatura, geografia, esportes e tecnologia.
  A maioria deve ser fácil ou moderada; mantenha poucas perguntas difíceis.
  Para cada 10 perguntas, gere 5 fáceis, 3 moderadas e apenas 2 difíceis.
  Inclua o campo "dificuldade" com "facil", "moderada" ou "dificil".
  No meio do baralho, inclua algumas PEGADINHAS (categoria "Pegadinha"):
  perguntas com raciocínio que levam a erro comum (ex.: "quantos meses têm
  28 dias?", "onde se enterra os sobreviventes?"). Sem conteúdo ofensivo.
  EXATAMENTE 4 opções por pergunta.

Regras obrigatórias:
1. Quantidade de opções:
   - criança: EXATAMENTE 2 opções. Campo "correta" = 0 ou 1.
   - adolescente e adulto: EXATAMENTE 4 opções. Campo "correta" = 0 a 3.
2. Apenas UMA opção correta.
3. As opções erradas devem ser plausíveis, mas claramente incorretas.
4. Sem perguntas de opinião: a resposta deve ser um fato verificável.
5. Varie a posição da resposta correta (não deixe sempre no índice 0).
6. Evite repetir perguntas ou temas já usados.
7. Português do Brasil, sem erros de ortografia.
8. Sempre inclua o campo "emoji": UM emoji que ilustre o tema da pergunta
   SEM revelar a resposta.
9. No nível criança é OBRIGATÓRIO incluir "opcoesEmoji": um emoji para cada
   uma das 2 opções, na mesma ordem (jogo pelas figuras).
10. Nos níveis adolescente e adulto NÃO use "opcoesEmoji".
11. Responda SOMENTE com um array JSON válido, sem texto extra, sem markdown.

Formato criança (2 opções):
{
  "id": "cNN",
  "categoria": "Peppa Pig",
  "emoji": "🐷",
  "pergunta": "Qual porquinha rosa mora com a família Pig?",
  "opcoes": ["George", "Peppa"],
  "opcoesEmoji": ["👦", "🐷"],
  "correta": 1
}

Formato adolescente / adulto (4 opções):
{
  "id": "<< PREFIXO >>NN",
  "categoria": "Influencers",
  "emoji": "📱",
  "pergunta": "Qual plataforma é famosa por vídeos curtos teens?",
  "opcoes": ["TikTok", "Wikipedia", "Excel", "Outlook"],
  "dificuldade": "facil",
  "correta": 0
}

Onde:
- << PREFIXO >> = "c" para criança, "a" para adolescente, "d" para adulto.
- NN = número sequencial com 2 dígitos (01, 02, 03, ...).

Comece a numeração em << NÚMERO_INICIAL >>.
```

---

## Como preencher

| Campo               | Criança | Adolescente | Adulto |
|---------------------|---------|-------------|--------|
| `<< NÍVEL >>`       | criança | adolescente | adulto |
| `<< PREFIXO >>`     | c       | a           | d      |
| `<< QUANTIDADE >>`  | 30      | 30          | 30     |
| Opções por pergunta | **2**   | **4**       | **4**  |

- `<< NÚMERO_INICIAL >>`: continue de onde a base parou. Hoje temos até `c34`,
  `a33` e `d33`, então comece em 35 / 34 / 34 para não repetir ids.

## Como usar o resultado

1. A IA devolve um array JSON (algo como `[ {...}, {...} ]`).
2. Cole esse array dentro da chave do nível correspondente em
   `resources/data/perguntas.json` (`crianca`, `adolescente` ou `adulto`).
3. Confira se o JSON continua válido (vírgulas entre os itens) e recarregue a página.

## Campos aceitos em cada pergunta

| Campo         | Obrigatório | Descrição |
|---------------|-------------|-----------|
| `id`          | sim         | Identificador único (`c01`, `a01`, `d01`) |
| `categoria`   | sim         | Tema curto (ex.: Disney, Influencers) |
| `pergunta`    | sim         | Texto da pergunta |
| `opcoes`      | sim         | 2 (criança) ou 4 (outros) alternativas |
| `correta`     | sim         | Índice da alternativa correta |
| `dificuldade` | adulto      | `facil`, `moderada` ou `dificil` |
| `emoji`       | recomendado | Figura grande acima da pergunta |
| `opcoesEmoji` | só criança  | Um emoji por alternativa |
| `imagem`      | opcional    | Caminho/URL de imagem real |

### Usando imagens reais em vez de emoji

Coloque os arquivos em `public/images/perguntas/` e referencie assim:

```json
{ "id": "c35", "emoji": "🏰", "imagem": "images/perguntas/mickey.jpg", "pergunta": "Quem é este personagem?", "opcoes": ["Mickey", "Donald"], "opcoesEmoji": ["🐭", "🦆"], "correta": 0 }
```

Também aceita URL completa (`https://...`). Mantenha o `emoji` como reserva
caso a imagem não carregue.

## Dica de validação rápida

```bash
./vendor/bin/sail php -r "json_decode(file_get_contents('resources/data/perguntas.json'), true) ?: exit('JSON inválido'); echo 'JSON OK';"
```
