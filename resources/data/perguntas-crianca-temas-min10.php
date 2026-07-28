<?php

/**
 * Completa categorias infantis com menos de 10 perguntas (meta: mínimo 10 cada).
 */
function criancaTemasMin10Rows(): array
{
    return [
        // --- Boas maneiras (+6) ---
        ['Boas maneiras', '🍽️', 'Na fila do lanche, esperamos a nossa...', ['Vez', 'Corrida'], ['🧍', '🏃'], 0],
        ['Boas maneiras', '🤫', 'Na biblioteca falamos...', ['Baixinho', 'Gritando'], ['🤫', '📢'], 0],
        ['Boas maneiras', '🤝', 'Quando encontramos alguém, podemos dar um...', ['Oi', 'Chute'], ['👋', '🦵'], 0],
        ['Boas maneiras', '🙏', 'Pedimos "por favor" quando...', ['Queremos algo', 'Estamos bravos'], ['🙏', '😠'], 0],
        ['Boas maneiras', '🪑', 'Na mesa, esperamos todo mundo sentar. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Boas maneiras', '🚪', 'Batemos na porta antes de entrar. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Boas maneiras', '🤗', 'Se esbarramos em alguém, pedimos...', ['Desculpa', 'Sorvete'], ['🙏', '🍦'], 0],

        // --- Cocomelon (+9) ---
        ['Cocomelon', '🚌', 'No Cocomelon, as crianças cantam sobre o ônibus escolar. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Cocomelon', '🍼', 'Cocomelon tem músicas sobre a hora da...', ['Mamadeira', 'Faculdade'], ['🍼', '🎓'], 0],
        ['Cocomelon', '🧸', 'No Cocomelon aparecem brinquedos e amigos. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Cocomelon', '🛁', 'Tem música do Cocomelon sobre a hora do...', ['Banho', 'Trabalho'], ['🛁', '💼'], 0],
        ['Cocomelon', '😴', 'Cocomelon também canta sobre dormir. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Cocomelon', '🦷', 'Existe música do Cocomelon sobre escovar os...', ['Dentes', 'Sapatos'], ['🦷', '👟'], 0],
        ['Cocomelon', '🍎', 'No Cocomelon aprendemos a comer bem. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Cocomelon', '👨‍👩‍👧', 'Cocomelon mostra uma família feliz. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Cocomelon', '🎵', 'Cocomelon é famoso pelas...', ['Músicas', 'Corridas de carro'], ['🎵', '🏎'], 0],

        // --- Dias (+7) ---
        ['Dias', '☀️', 'O dia começa de...', ['Manhã', 'Madrugada escura só'], ['🌅', '🌌'], 0],
        ['Dias', '🌙', 'Depois da tarde vem a...', ['Noite', 'Manhã'], ['🌙', '🌅'], 0],
        ['Dias', '📅', 'Segunda-feira vem depois do...', ['Domingo', 'Sábado só'], ['📅', '🎉'], 0],
        ['Dias', '🏫', 'Nos dias de semana as crianças vão à...', ['Escola', 'Lua'], ['🏫', '🌙'], 0],
        ['Dias', '🎉', 'Sábado e domingo são dias de...', ['Descanso', 'Só chorar'], ['😌', '😢'], 0],
        ['Dias', '🎃', 'No Dia das Bruxas as crianças usam...', ['Fantasia', 'Casaco de gelo'], ['🎃', '🧥'], 0],
        ['Dias', '❤️', 'No Dia das Mães damos carinho para a...', ['Mamãe', 'Geladeira'], ['👩', '🧊'], 0],

        // --- Diversão (+8) ---
        ['Diversão', '🛝', 'No parque a gente pode brincar no...', ['Escorregador', 'Fogão'], ['🛝', '🔥'], 0],
        ['Diversão', '⚽', 'Chutar a bola é brincar de...', ['Futebol', 'Dormir'], ['⚽', '😴'], 0],
        ['Diversão', '🧩', 'Montar peças coloridas é um...', ['Quebra-cabeça', 'Sapato'], ['🧩', '👟'], 0],
        ['Diversão', '🎨', 'Pintar com tinta é uma...', ['Diversão', 'Bronca'], ['🎨', '😠'], 0],
        ['Diversão', '📚', 'Ouvir historinha também é...', ['Divertido', 'Chato sempre'], ['📖', '😴'], 0],
        ['Diversão', '🕺', 'Dançar música faz a gente...', ['Sorrir', 'Dormir na hora'], ['😄', '😴'], 0],
        ['Diversão', '🧸', 'Brincar de faz de conta com bonecos. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Diversão', '🚴', 'Andar de bicicleta pode ser...', ['Diversão', 'Comida'], ['🚲', '🍕'], 0],

        // --- Encanto (+9) ---
        ['Encanto', '🦋', 'Em Encanto, Mirabel não tem poder mágico. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Encanto', '💪', 'Luisa é a irmã muito...', ['Forte', 'Pequena'], ['💪', '🐣'], 0],
        ['Encanto', '🌸', 'Isabela faz flores aparecerem. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Encanto', '🍲', 'Julieta cura as pessoas com...', ['Comida', 'Pedras'], ['🍲', '🪨'], 0],
        ['Encanto', '🌦️', 'Pepa controla o...', ['Clima', 'Carro'], ['⛅', '🚗'], 0],
        ['Encanto', '🐐', 'Antonio fala com os...', ['Animais', 'Robôs'], ['🦜', '🤖'], 0],
        ['Encanto', '👵', 'A avó da família se chama Abuela. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Encanto', '🕯️', 'A vela mágica cuida da família Madrigal. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Encanto', '🎵', 'Em Encanto tem a música "Não Falamos do Bruno". Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],

        // --- Estações (+8) ---
        ['Estações', '🍂', 'No outono as árvores ficam com folhas...', ['Coloridas', 'De gelo'], ['🍂', '🧊'], 0],
        ['Estações', '🌸', 'Na primavera nascem muitas...', ['Flores', 'Nevadas'], ['🌸', '❄️'], 0],
        ['Estações', '🏖️', 'No verão muita gente vai à...', ['Praia', 'Geladeira'], ['🏖️', '🧊'], 0],
        ['Estações', '🧣', 'No inverno usamos...', ['Cachecol', 'Bermuda só'], ['🧣', '🩳'], 0],
        ['Estações', '☔', 'Quando chove usamos...', ['Guarda-chuva', 'Óculos de sol'], ['☔', '🕶'], 0],
        ['Estações', '🌞', 'O sol brilha mais forte no...', ['Verão', 'Inverno'], ['☀️', '❄️'], 0],
        ['Estações', '🍃', 'Ventania forte balança as...', ['Árvores', 'Nuvens de algodão'], ['🌳', '☁'], 0],
        ['Estações', '📅', 'O ano tem quatro estações. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Estações', '🧥', 'No frio vestimos casaco. Em qual estação isso é comum?', ['Inverno', 'Verão'], ['❄️', '☀️'], 0],

        // --- Frozen (+7) ---
        ['Frozen', '🧡', 'A irmã da Elsa se chama...', ['Anna', 'Moana'], ['🧡', '🌊'], 0],
        ['Frozen', '🏰', 'Elsa vira rainha de Arendelle. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Frozen', '🎶', 'Elsa canta "Liberdade". Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Frozen', '🧊', 'O poder da Elsa é de...', ['Gelo', 'Fogo'], ['❄️', '🔥'], 0],
        ['Frozen', '👨', 'Kristoff é amigo da Anna. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Frozen', '⛄', 'Olaf gosta de...', ['Abraços quentes', 'Ficar sozinho sempre'], ['🤗', '🚫'], 0],
        ['Frozen', '👗', 'O vestido mágico da Elsa é de...', ['Gelo brilhante', 'Areia'], ['✨', '🏖'], 0],

        // --- Galinha Pintadinha (+7) ---
        ['Galinha Pintadinha', '🎶', 'A Galinha Pintadinha canta para as crianças. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Galinha Pintadinha', '🥚', 'A galinha bota...', ['Ovos', 'Pedras'], ['🥚', '🪨'], 0],
        ['Galinha Pintadinha', '🐥', 'Os pintinhos são filhotes da...', ['Galinha', 'Vaca'], ['🐔', '🐄'], 0],
        ['Galinha Pintadinha', '🔵', 'A Galinha Pintadinha é azulzinha. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Galinha Pintadinha', '💃', 'Nas músicas, todo mundo pode...', ['Dançar', 'Dormir na rua'], ['💃', '🛣'], 0],
        ['Galinha Pintadinha', '🎤', 'As crianças cantam junto com a Galinha. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Galinha Pintadinha', '👶', 'As músicas da Galinha Pintadinha são para...', ['Crianças', 'Motoristas'], ['👶', '🚕'], 0],

        // --- Moana (+8) ---
        ['Moana', '🐷', 'O porquinho amigo da Moana se chama...', ['Pua', 'Hei Hei'], ['🐷', '🐔'], 0],
        ['Moana', '🏝️', 'Moana mora numa...', ['Ilha', 'Montanha de neve'], ['🏝️', '🏔'], 0],
        ['Moana', '🛶', 'Moana viaja de...', ['Canoa', 'Trem'], ['🛶', '🚂'], 0],
        ['Moana', '🌋', 'Maui é um semideus amigo da Moana. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Moana', '🪝', 'Maui usa um anzol mágico. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Moana', '🌊', 'Moana ama o...', ['Oceano', 'Deserto'], ['🌊', '🏜'], 0],
        ['Moana', '👗', 'Moana usa roupa tipicamente da ilha. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Moana', '❤️', 'Moana ajuda sua família e o povo. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],

        // --- Mundo Bita (+9) ---
        ['Mundo Bita', '🎵', 'Bita canta músicas para crianças. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '🌈', 'No Mundo Bita tem cores e diversão. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '👧', 'Dan canta junto com o Bita. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '👦', 'Tito também é amigo do Bita. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '🎭', 'No Mundo Bita tem brincadeiras e teatro. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '🧡', 'As músicas do Bita ensinam carinho. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '🎤', 'Bita aparece cantando no palco. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Mundo Bita', '👶', 'Mundo Bita é feito para...', ['Crianças', 'Caminhões'], ['👶', '🚚'], 0],
        ['Mundo Bita', '🎉', 'Dançar com o Bita é...', ['Divertido', 'Chato'], ['🎉', '😐'], 0],

        // --- Patati Patatá (+9) ---
        ['Patati Patatá', '🎪', 'Patati e Patatá fazem show de...', ['Circo', 'Cozinha só'], ['🎪', '🍳'], 0],
        ['Patati Patatá', '😄', 'Os palhaços fazem a gente...', ['Rir', 'Chorar'], ['😂', '😢'], 0],
        ['Patati Patatá', '🎵', 'Patati e Patatá também cantam. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Patati Patatá', '🎩', 'Palhaço usa chapéu engraçado. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Patati Patatá', '👟', 'Patati e Patatá dançam bastante. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Patati Patatá', '🧒', 'As crianças gostam dos palhaços. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Patati Patatá', '🎤', 'No show eles falam com o público. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Patati Patatá', '💛', 'Patati e Patatá são dois amigos. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],
        ['Patati Patatá', '🎉', 'Ir ao circo dos palhaços é...', ['Festa', 'Prova difícil'], ['🎉', '📝'], 0],

        // --- Rotina (+8) ---
        ['Rotina', '🪥', 'De manhã escovamos os...', ['Dentes', 'Livros'], ['🦷', '📚'], 0],
        ['Rotina', '👕', 'Antes de sair, vestimos a...', ['Roupa', 'Cama'], ['👕', '🛏'], 0],
        ['Rotina', '🎒', 'Para a escola levamos a...', ['Mochila', 'Geladeira'], ['🎒', '🧊'], 0],
        ['Rotina', '🍽️', 'Almoço é a refeição do...', ['Meio-dia', 'Meia-noite'], ['☀️', '🌙'], 0],
        ['Rotina', '🧼', 'Depois de brincar no parque, lavamos as...', ['Mãos', 'Nuvens'], ['🙌', '☁'], 0],
        ['Rotina', '📚', 'À noite podemos ouvir uma...', ['História', 'Sirene só'], ['📖', '🚨'], 0],
        ['Rotina', '🛏️', 'Na hora de dormir vamos para a...', ['Cama', 'Cozinha'], ['🛏', '🍳'], 0],
        ['Rotina', '⏰', 'De manhã o despertador toca para a gente...', ['Levantar', 'Voar'], ['⏰', '🕊'], 0],
        ['Rotina', '🛁', 'Antes de dormir, muitas crianças tomam...', ['Banho', 'Sol'], ['🛁', '☀️'], 0],

        // --- Toy Story (+6) ---
        ['Toy Story', '👧', 'A dona dos brinquedos se chama...', ['Bonnie ou Andy', 'Elsa'], ['👧', '❄'], 0],
        ['Toy Story', '🐕', 'Slinky é o cachorro de...', ['Mola', 'Plástico duro só'], ['🐕', '🧱'], 0],
        ['Toy Story', '🥔', 'Sr. Cabeça de Batata é um...', ['Brinquedo', 'Fruta de verdade'], ['🥔', '🍎'], 0],
        ['Toy Story', '👽', 'Os alienígeninhas vivem na máquina de...', ['Garra', 'Sorvete'], ['👾', '🍦'], 0],
        ['Toy Story', '🐴', 'Bullseye é o cavalo do...', ['Woody', 'Buzz'], ['🐴', '🚀'], 0],
        ['Toy Story', '❤️', 'Os brinquedos são amigos. Verdadeiro?', ['Sim', 'Não'], ['✅', '❌'], 0],

        // --- José Comilão (10) ---
        ['José Comilão', '🍪', 'José Comilão adora comer...', ['Lanchinhos', 'Pedras'], ['🍪', '🪨'], 0],
        ['José Comilão', '🍩', 'José Comilão faz rosquinhas e...', ['Bolos', 'Aviões'], ['🎂', '✈'], 0],
        ['José Comilão', '🧑‍🍳', 'José Comilão cozinha com ingredientes...', ['Mágicos', 'De ferro'], ['✨', '🔩'], 0],
        ['José Comilão', '😋', 'O apelido "Comilão" é porque ele...', ['Come bastante', 'Corre rápido'], ['😋', '🏃'], 0],
        ['José Comilão', '📺', 'José Comilão também é chamado de José...', ['Totoy', 'Mickey'], ['👦', '🐭'], 0],
        ['José Comilão', '🧁', 'Nas histórias, José Comilão prepara...', ['Doces', 'Carros'], ['🧁', '🚗'], 0],
        ['José Comilão', '🧒', 'José Comilão é um desenho para...', ['Crianças', 'Pilotos'], ['🧒', '✈'], 0],
        ['José Comilão', '🤝', 'Nas aventuras, José Comilão aprende com...', ['Amigos', 'Tempestades'], ['🤝', '⛈'], 0],
        ['José Comilão', '🎉', 'Assistir José Comilão pode ser...', ['Divertido', 'Chato sempre'], ['🎉', '😐'], 0],
        ['José Comilão', '🍽️', 'José Comilão quase nunca dispensa um...', ['Lanche', 'Exame difícil'], ['🥪', '📝'], 0],
    ];
}
