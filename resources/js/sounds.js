/**
 * Sons (Web Audio) + música de fundo.
 *
 * Nível criança → sempre música sintética (Web Audio).
 * Adolescente / adulto → trechos MP3 em:
 *   /audio/adolescente/audio1.mp3 … audio10.mp3
 *   /audio/adulto/audio1.mp3 … audio10.mp3
 * (troca a cada pergunta; começa num ponto aleatório do arquivo)
 *
 * preset: 'quiz' | 'live'
 * nivel: 'crianca' | 'adolescente' | 'adulto'
 */
export function createSounds({ preset = 'quiz', nivel = 'crianca' } = {}) {
    let ctx = null;
    let ligado = localStorage.getItem('quiz-som') !== 'off';
    let musicGain = null;
    let musicTimer = null;
    let nextNoteAt = 0;
    let musicPlaying = false;

    /** @type {HTMLAudioElement | null} */
    let fileAudio = null;
    /** @type {boolean | null} null = ainda não testou */
    let filesAvailable = null;
    let fileTrackIndex = 0;

    const nivelSlug = ['adolescente', 'adulto'].includes(nivel) ? nivel : 'crianca';
    const useLevelFiles = nivelSlug !== 'crianca';
    const TRACK_COUNT = 10;
    const FILE_VOL = 0.38;

    const quizTrack = {
        beat: 0.28,
        volume: 0.07,
        loop: [
            523.25, 659.25, 783.99, 659.25,
            587.33, 698.46, 880.00, 698.46,
            659.25, 783.99, 987.77, 783.99,
            523.25, 659.25, 783.99, 1046.5,
        ],
        bass: [
            130.81, null, 130.81, null,
            146.83, null, 146.83, null,
            164.81, null, 164.81, null,
            130.81, null, 196.00, null,
        ],
    };

    // Várias músicas de festa; no Ao Vivo troca a cada pergunta.
    const liveTracks = [
        // 0 · animada em Dó maior
        {
            beat: 0.22, volume: 0.085,
            loop: [
                523.25, 659.25, 783.99, 1046.5, 987.77, 783.99, 659.25, 523.25,
                587.33, 698.46, 880.00, 1174.7, 1046.5, 880.00, 698.46, 587.33,
            ],
            bass: [
                130.81, null, 196.00, null, 146.83, null, 220.00, null,
                164.81, null, 246.94, null, 174.61, null, 261.63, null,
            ],
        },
        // 1 · saltitante em Sol maior
        {
            beat: 0.2, volume: 0.085,
            loop: [
                783.99, 987.77, 1174.7, 987.77, 880.00, 1046.5, 1318.5, 1046.5,
                783.99, 659.25, 587.33, 659.25, 783.99, 987.77, 783.99, 587.33,
            ],
            bass: [
                196.00, null, 196.00, 293.66, 220.00, null, 220.00, null,
                246.94, null, 174.61, null, 196.00, null, 293.66, null,
            ],
        },
        // 2 · marchinha alegre em Fá maior
        {
            beat: 0.24, volume: 0.08,
            loop: [
                698.46, 698.46, 880.00, 1046.5, 880.00, 698.46, 587.33, 523.25,
                587.33, 698.46, 880.00, 698.46, 1046.5, 880.00, 698.46, 523.25,
            ],
            bass: [
                174.61, null, 261.63, null, 220.00, null, 174.61, null,
                146.83, null, 220.00, null, 174.61, null, 261.63, null,
            ],
        },
        // 3 · balada bouncy em Ré maior
        {
            beat: 0.21, volume: 0.085,
            loop: [
                587.33, 739.99, 880.00, 1108.7, 880.00, 739.99, 587.33, 493.88,
                659.25, 739.99, 880.00, 739.99, 1108.7, 880.00, 739.99, 587.33,
            ],
            bass: [
                146.83, null, 220.00, null, 185.00, null, 146.83, null,
                164.81, null, 246.94, null, 185.00, null, 220.00, null,
            ],
        },
        // 4 · funky em Lá menor
        {
            beat: 0.19, volume: 0.085,
            loop: [
                440.00, 523.25, 659.25, 523.25, 587.33, 659.25, 783.99, 659.25,
                440.00, 659.25, 880.00, 659.25, 783.99, 659.25, 587.33, 523.25,
            ],
            bass: [
                110.00, null, 164.81, null, 146.83, null, 196.00, null,
                110.00, null, 164.81, null, 130.81, null, 196.00, null,
            ],
        },
        // 5 · valsinha divertida em Mi maior
        {
            beat: 0.26, volume: 0.08,
            loop: [
                659.25, 830.61, 987.77, 830.61, 659.25, 987.77, 1318.5, 987.77,
                739.99, 880.00, 1108.7, 880.00, 659.25, 830.61, 987.77, 659.25,
            ],
            bass: [
                164.81, null, 246.94, null, 207.65, null, 164.81, null,
                185.00, null, 277.18, null, 207.65, null, 246.94, null,
            ],
        },
        // 6 · Ode à Alegria (Beethoven) — alegre e famosa
        {
            beat: 0.24, volume: 0.085,
            loop: [
                659.25, 659.25, 698.46, 783.99, 783.99, 698.46, 659.25, 587.33,
                523.25, 523.25, 587.33, 659.25, 659.25, 587.33, 587.33, 587.33,
            ],
            bass: [
                130.81, null, null, null, 196.00, null, null, null,
                130.81, null, null, null, 196.00, null, 196.00, null,
            ],
        },
        // 7 · Galope do William Tell (Rossini) — corrida animada
        {
            beat: 0.15, volume: 0.085,
            loop: [
                392.00, 392.00, 392.00, 392.00, 392.00, 392.00, 523.25, 523.25,
                392.00, 392.00, 392.00, 587.33, 587.33, 587.33, 523.25, 392.00,
            ],
            bass: [
                130.81, null, 130.81, null, 130.81, null, 196.00, null,
                130.81, null, 130.81, null, 196.00, null, 196.00, null,
            ],
        },
        // 8 · Na Caverna do Rei da Montanha (Grieg) — misteriosa e crescente
        {
            beat: 0.17, volume: 0.085,
            loop: [
                440.00, 493.88, 523.25, 587.33, 659.25, 523.25, 659.25, 587.33,
                440.00, 493.88, 523.25, 587.33, 659.25, 783.99, 659.25, 523.25,
            ],
            bass: [
                110.00, null, 164.81, null, 130.81, null, 164.81, null,
                110.00, null, 164.81, null, 130.81, 164.81, 196.00, null,
            ],
        },
        // 9 · disco pop animado em Dó maior
        {
            beat: 0.2, volume: 0.085,
            loop: [
                523.25, 659.25, 587.33, 523.25, 659.25, 783.99, 880.00, 783.99,
                659.25, 587.33, 523.25, 587.33, 659.25, 783.99, 1046.5, 783.99,
            ],
            bass: [
                130.81, null, 196.00, null, 174.61, null, 196.00, null,
                130.81, null, 196.00, null, 220.00, null, 196.00, null,
            ],
        },
    ];

    let currentTrack = preset === 'live' ? liveTracks[0] : quizTrack;
    let trackIndex = 0;
    let BEAT = currentTrack.beat;
    let LOOP = currentTrack.loop;
    let BASS = currentTrack.bass;
    let MUSIC_VOL = currentTrack.volume;

    function applyTrack(track) {
        currentTrack = track;
        BEAT = track.beat;
        LOOP = track.loop;
        BASS = track.bass;
        MUSIC_VOL = track.volume;
    }

    function fileSrcFor(index = 0) {
        const i = ((Number(index) || 0) % TRACK_COUNT + TRACK_COUNT) % TRACK_COUNT;
        return `/audio/${nivelSlug}/audio${i + 1}.mp3`;
    }

    function probeAudio(src) {
        return new Promise((resolve) => {
            const a = new Audio();
            let done = false;
            const finish = (ok) => {
                if (done) {
                    return;
                }
                done = true;
                a.removeAttribute('src');
                a.load();
                resolve(ok);
            };
            a.preload = 'metadata';
            a.addEventListener('loadeddata', () => finish(true), { once: true });
            a.addEventListener('error', () => finish(false), { once: true });
            a.src = src;
            setTimeout(() => finish(false), 2500);
        });
    }

    async function ensureFileMode() {
        if (!useLevelFiles) {
            filesAvailable = false;
            return false;
        }
        if (filesAvailable !== null) {
            return filesAvailable;
        }
        filesAvailable = await probeAudio(fileSrcFor(0));
        return filesAvailable;
    }

    function pickClipStart(audio) {
        const duration = Number.isFinite(audio.duration) ? audio.duration : 0;
        if (duration <= 15) {
            return 0;
        }
        // Trecho: começa em ponto aleatório nos primeiros ~70% (evita o final curto).
        const maxStart = Math.max(0, duration * 0.7 - 12);
        return Math.random() * maxStart;
    }

    function ensureFileAudio(src, { freshClip = false } = {}) {
        if (!fileAudio) {
            fileAudio = new Audio();
            fileAudio.loop = true;
            fileAudio.preload = 'auto';
            fileAudio.volume = ligado ? FILE_VOL : 0;
        }
        const changed = fileAudio.dataset.src !== src;
        if (changed) {
            fileAudio.dataset.src = src;
            fileAudio.src = src;
            fileAudio.load();
        }
        if (changed || freshClip) {
            const applyStart = () => {
                try {
                    fileAudio.currentTime = pickClipStart(fileAudio);
                } catch (e) {
                    /* ignore */
                }
            };
            if (fileAudio.readyState >= 1) {
                applyStart();
            } else {
                fileAudio.addEventListener('loadedmetadata', applyStart, { once: true });
            }
        }
        return fileAudio;
    }

    async function playFileMusic(index = fileTrackIndex, { freshClip = true } = {}) {
        const ok = await ensureFileMode();
        if (!ok) {
            return false;
        }
        fileTrackIndex = index;
        const audio = ensureFileAudio(fileSrcFor(index), { freshClip });
        audio.volume = ligado ? FILE_VOL : 0;
        try {
            await audio.play();
            musicPlaying = true;
            return true;
        } catch (e) {
            return false;
        }
    }

    function stopFileMusic({ fade = true } = {}) {
        if (!fileAudio) {
            return;
        }
        if (!fade) {
            fileAudio.pause();
            fileAudio.currentTime = 0;
            return;
        }
        const startVol = fileAudio.volume;
        const steps = 8;
        let i = 0;
        const timer = setInterval(() => {
            i += 1;
            fileAudio.volume = Math.max(0, startVol * (1 - i / steps));
            if (i >= steps) {
                clearInterval(timer);
                fileAudio.pause();
                fileAudio.currentTime = 0;
                fileAudio.volume = ligado ? FILE_VOL : 0;
            }
        }, 60);
    }

    /**
     * Seleciona a música pelo índice da pergunta (quiz e ao vivo).
     * Em criança só troca a trilha sintética no Ao Vivo.
     */
    function selecionarMusica(index) {
        const i = ((Number(index) || 0) % liveTracks.length + liveTracks.length) % liveTracks.length;

        if (useLevelFiles) {
            if (i === trackIndex) {
                return;
            }
            trackIndex = i;
            applyTrack(liveTracks[i % liveTracks.length]);
            if (musicPlaying && ligado) {
                playFileMusic(i, { freshClip: true });
            }
            return;
        }

        if (preset !== 'live') {
            return;
        }
        if (i === trackIndex) {
            return;
        }
        trackIndex = i;
        applyTrack(liveTracks[i]);
        if (musicPlaying && ctx && ligado) {
            musicGain.gain.setTargetAtTime(MUSIC_VOL, ctx.currentTime, 0.12);
        }
    }

    function ensureCtx() {
        if (!ctx) {
            ctx = new (window.AudioContext || window.webkitAudioContext)();
            musicGain = ctx.createGain();
            musicGain.gain.value = ligado ? MUSIC_VOL : 0;
            musicGain.connect(ctx.destination);
        }
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        return ctx;
    }

    function tocar(notas) {
        if (!ligado) {
            return;
        }
        try {
            ensureCtx();
            notas.forEach(({ freq, start, dur, tipo = 'sine', vol = 0.22 }) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = tipo;
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, ctx.currentTime + start);
                gain.gain.exponentialRampToValueAtTime(vol, ctx.currentTime + start + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + dur);
                osc.connect(gain).connect(ctx.destination);
                osc.start(ctx.currentTime + start);
                osc.stop(ctx.currentTime + start + dur + 0.02);
            });
        } catch (e) {
            /* navegador sem suporte */
        }
    }

    function scheduleNote(freq, when, dur, tipo, vol) {
        if (!freq) {
            return;
        }
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = tipo;
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, when);
        gain.gain.exponentialRampToValueAtTime(vol, when + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, when + dur);
        osc.connect(gain).connect(musicGain);
        osc.start(when);
        osc.stop(when + dur + 0.02);
    }

    function scheduleLoop() {
        if (!musicPlaying || !ctx) {
            return;
        }
        const horizon = ctx.currentTime + 1.2;
        while (nextNoteAt < horizon) {
            const absStep = Math.round(nextNoteAt / BEAT) % LOOP.length;
            scheduleNote(LOOP[absStep], nextNoteAt, BEAT * 0.8, 'triangle', 0.55);
            scheduleNote(BASS[absStep], nextNoteAt, BEAT * 0.9, 'sine', 0.38);
            if (absStep % 2 === 0) {
                scheduleNote(190, nextNoteAt, 0.05, 'square', 0.11);
            }
            if (preset === 'live' && absStep % 4 === 0) {
                scheduleNote(240, nextNoteAt + BEAT * 0.5, 0.04, 'square', 0.08);
            }
            nextNoteAt += BEAT;
        }
    }

    function iniciarMusica() {
        try {
            ensureCtx();
            if (!ligado) {
                if (fileAudio) {
                    fileAudio.volume = 0;
                    fileAudio.pause();
                }
                if (musicGain) {
                    musicGain.gain.setTargetAtTime(0, ctx.currentTime, 0.05);
                }
                musicPlaying = false;
                return;
            }

            // Prefere MP3 em /audio/; cai no sintético se não houver arquivo.
            playFileMusic(trackIndex).then((usedFile) => {
                if (usedFile) {
                    clearInterval(musicTimer);
                    musicTimer = null;
                    return;
                }
                if (musicPlaying && musicGain) {
                    musicGain.gain.setTargetAtTime(MUSIC_VOL, ctx.currentTime, 0.15);
                    return;
                }
                musicPlaying = true;
                nextNoteAt = ctx.currentTime + 0.05;
                musicGain.gain.setValueAtTime(0.0001, ctx.currentTime);
                musicGain.gain.exponentialRampToValueAtTime(MUSIC_VOL, ctx.currentTime + 0.5);
                scheduleLoop();
                clearInterval(musicTimer);
                musicTimer = setInterval(scheduleLoop, 350);
            });
        } catch (e) {
            /* ignore */
        }
    }

    function pararMusica({ fade = true } = {}) {
        stopFileMusic({ fade });
        if (!ctx || !musicGain) {
            musicPlaying = false;
            clearInterval(musicTimer);
            musicTimer = null;
            return;
        }
        musicPlaying = false;
        clearInterval(musicTimer);
        musicTimer = null;
        const now = ctx.currentTime;
        if (fade) {
            musicGain.gain.cancelScheduledValues(now);
            musicGain.gain.setValueAtTime(Math.max(musicGain.gain.value, 0.0001), now);
            musicGain.gain.exponentialRampToValueAtTime(0.0001, now + 0.8);
        } else {
            musicGain.gain.setValueAtTime(0, now);
        }
    }

    return {
        acerto: () => tocar([
            { freq: 660, start: 0, dur: 0.12 },
            { freq: 880, start: 0.1, dur: 0.18 },
            { freq: 1180, start: 0.22, dur: 0.2 },
        ]),
        erro: () => tocar([
            { freq: 220, start: 0, dur: 0.22, tipo: 'square', vol: 0.16 },
            { freq: 140, start: 0.16, dur: 0.3, tipo: 'square', vol: 0.14 },
        ]),
        fim: () => tocar([
            { freq: 523, start: 0, dur: 0.16 },
            { freq: 659, start: 0.14, dur: 0.16 },
            { freq: 784, start: 0.28, dur: 0.16 },
            { freq: 1046, start: 0.42, dur: 0.3 },
        ]),
        // Tempo esgotado / todos responderam — revelação da resposta.
        revelar: () => tocar([
            { freq: 392, start: 0, dur: 0.12, tipo: 'triangle', vol: 0.2 },
            { freq: 523.25, start: 0.1, dur: 0.14, tipo: 'triangle', vol: 0.22 },
            { freq: 659.25, start: 0.22, dur: 0.16, tipo: 'triangle', vol: 0.24 },
            { freq: 784, start: 0.36, dur: 0.28, tipo: 'sine', vol: 0.26 },
            { freq: 1046.5, start: 0.42, dur: 0.35, tipo: 'sine', vol: 0.18 },
        ]),
        iniciarMusica,
        pararMusica,
        selecionarMusica,
        unlock() {
            try {
                ensureCtx();
            } catch (e) {
                /* ignore */
            }
        },
        alternar() {
            ligado = !ligado;
            localStorage.setItem('quiz-som', ligado ? 'on' : 'off');
            try {
                ensureCtx();
                if (fileAudio) {
                    fileAudio.volume = ligado ? FILE_VOL : 0;
                    if (!ligado) {
                        fileAudio.pause();
                    } else if (musicPlaying || filesAvailable) {
                        playFileMusic(fileTrackIndex);
                    }
                }
                if (musicGain) {
                    musicGain.gain.cancelScheduledValues(ctx.currentTime);
                    if (ligado && musicPlaying && filesAvailable !== true) {
                        musicGain.gain.setTargetAtTime(MUSIC_VOL, ctx.currentTime, 0.1);
                        scheduleLoop();
                        clearInterval(musicTimer);
                        musicTimer = setInterval(scheduleLoop, 350);
                    } else if (ligado && filesAvailable !== true) {
                        iniciarMusica();
                    } else if (!ligado) {
                        musicGain.gain.setTargetAtTime(0, ctx.currentTime, 0.08);
                        clearInterval(musicTimer);
                        musicTimer = null;
                    }
                } else if (ligado) {
                    iniciarMusica();
                }
            } catch (e) {
                /* ignore */
            }
            return ligado;
        },
        get ativo() {
            return ligado;
        },
        get tocando() {
            return musicPlaying;
        },
    };
}
