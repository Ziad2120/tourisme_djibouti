// ============================================================
//  TOURISME DJIBOUTI — JavaScript principal
// ============================================================

// ---- AUDIO PLAYER ----
function initAudioPlayer(playerId) {
    const player = document.getElementById(playerId);
    if (!player) return;

    const btn     = player.querySelector('.audio-btn');
    const progress= player.querySelector('.audio-progress');
    const timeEl  = player.querySelector('.audio-time');
    const audio   = player.querySelector('audio');

    if (!audio) {
        // Mode démo sans fichier réel
        let playing = false, fakeTime = 0, total = 240, interval;

        btn.addEventListener('click', () => {
            playing = !playing;
            btn.innerHTML = playing
                ? '<svg viewBox="0 0 24 24" fill="white"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>'
                : '<svg viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>';

            if (playing) {
                interval = setInterval(() => {
                    fakeTime = Math.min(fakeTime + 1, total);
                    progress.value = (fakeTime / total) * 100;
                    timeEl.textContent = formatTime(fakeTime) + ' / ' + formatTime(total);
                    if (fakeTime >= total) { clearInterval(interval); playing = false; }
                }, 1000);
            } else {
                clearInterval(interval);
            }
        });

        progress.addEventListener('input', () => {
            fakeTime = (progress.value / 100) * total;
            timeEl.textContent = formatTime(fakeTime) + ' / ' + formatTime(total);
        });

        timeEl.textContent = '0:00 / ' + formatTime(total);
        return;
    }

    btn.addEventListener('click', () => {
        if (audio.paused) {
            audio.play();
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="white"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
        } else {
            audio.pause();
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>';
        }
    });

    audio.addEventListener('timeupdate', () => {
        if (audio.duration) {
            progress.value = (audio.currentTime / audio.duration) * 100;
            timeEl.textContent = formatTime(audio.currentTime) + ' / ' + formatTime(audio.duration);
        }
    });

    progress.addEventListener('input', () => {
        audio.currentTime = (progress.value / 100) * audio.duration;
    });
}

function formatTime(s) {
    s = Math.floor(s);
    return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
}

// ---- RÉALITÉ AUGMENTÉE ----
function lancerRA(siteId, typeOverlay) {
    const viewer = document.getElementById('ra-viewer');
    if (!viewer) return;

    viewer.classList.add('ra-active');

    const descriptions = {
        'panorama':       '🌐 Vue panoramique 360° activée — Faites glisser pour explorer',
        'reconstitution': '🏛️ Reconstitution historique chargée — Découvrez le passé',
        'faune':          '🦅 Détection de faune activée — Pointez vers la végétation',
        'annotation':     '📍 Annotations architecturales — Touchez les éléments',
    };

    viewer.innerHTML = `
        <div style="text-align:center;color:white;padding:20px;">
            <div style="font-size:2.5rem;margin-bottom:16px;">📱</div>
            <div style="font-size:1rem;font-weight:500;margin-bottom:8px;">
                ${descriptions[typeOverlay] || '🔮 RA Activée'}
            </div>
            <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);">
                Mode démo — Dans l'application mobile, la caméra s'active ici
            </div>
            <div style="margin-top:20px;padding:10px 20px;background:rgba(200,155,60,0.2);border:1px solid rgba(200,155,60,0.4);border-radius:4px;display:inline-block;font-size:0.78rem;color:#F0C060;">
                ✦ Réalité Augmentée — ${typeOverlay.toUpperCase()}
            </div>
        </div>
        <div class="ra-tag">RA ACTIVE</div>
    `;

    // Animation de scan
    let scanLine = document.createElement('div');
    scanLine.style.cssText = 'position:absolute;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,rgba(200,155,60,0.8),transparent);animation:scan 2s linear infinite;';
    viewer.appendChild(scanLine);

    const style = document.createElement('style');
    style.textContent = '@keyframes scan{0%{top:0}100%{top:100%}}';
    document.head.appendChild(style);
}

// ---- STAR RATING ----
document.querySelectorAll('.star-rating').forEach(container => {
    const labels = container.querySelectorAll('label');
    labels.forEach((label, i) => {
        label.addEventListener('mouseover', () => {
            labels.forEach((l, j) => {
                l.style.color = j >= labels.length - 1 - i ? 'var(--or)' : 'var(--gris-pale)';
            });
        });
        label.addEventListener('mouseleave', () => {
            const checked = container.querySelector('input:checked');
            labels.forEach((l, j) => {
                const val = checked ? parseInt(checked.value) : 0;
                l.style.color = '';
            });
        });
    });
});

// ---- FILTRES ----
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
    initAudioPlayer('audio-player-1');
    initAudioPlayer('audio-player-2');
});
