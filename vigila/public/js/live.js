document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-live-player]').forEach((container) => {
        const video = container.querySelector('.live-video');
        const image = container.querySelector('.live-mjpeg');
        const placeholder = container.querySelector('.live-placeholder');
        const status = container.querySelector('[data-live-status]');
        const endpoint = container.dataset.sessionUrl;
        let hlsPlayer = null;
        let renewalTimer = null;

        const showMessage = (title, detail, isError = false) => {
            placeholder.hidden = false;
            placeholder.classList.toggle('error', isError);
            placeholder.querySelector('strong').textContent = title;
            placeholder.querySelector('small').textContent = detail;
        };

        const setStatus = (text, connected = false) => {
            status.textContent = text;
            status.classList.toggle('connected', connected);
        };

        const connect = async () => {
            if (hlsPlayer) { hlsPlayer.destroy(); hlsPlayer = null; }
            if (renewalTimer) window.clearTimeout(renewalTimer);
            video.hidden = true;
            image.hidden = true;
            setStatus('Conectando...');

            try {
                const response = await fetch(endpoint, { cache: 'no-store', headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo obtener la señal de la cámara.');

                placeholder.hidden = true;
                if (data.type === 'mjpeg') {
                    image.hidden = false;
                    image.src = data.url;
                    setStatus('Señal recibida', true);
                    return;
                }

                video.hidden = false;
                video.onplaying = () => setStatus('Transmitiendo', true);
                video.onerror = () => {
                    showMessage('No se pudo reproducir la transmisión', 'Verifica el formato de la señal, CORS y que la cámara esté enviando video.', true);
                    setStatus('Sin señal');
                };

                if (data.type === 'hls' && window.Hls?.isSupported()) {
                    hlsPlayer = new window.Hls({ lowLatencyMode: true });
                    hlsPlayer.loadSource(data.url);
                    hlsPlayer.attachMedia(video);
                    hlsPlayer.on(window.Hls.Events.MANIFEST_PARSED, () => {
                        video.play().catch(() => {});
                        setStatus('Señal recibida', true);
                    });
                    hlsPlayer.on(window.Hls.Events.ERROR, (_event, error) => {
                        if (error.fatal) {
                            showMessage('Se interrumpió la transmisión', 'Reintentando conexión con la cámara...', true);
                            setStatus('Reconectando');
                            window.setTimeout(connect, 3000);
                        }
                    });
                } else if (data.type === 'hls' && video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = data.url;
                    video.play().catch(() => {});
                } else if (data.type === 'hls') {
                    throw new Error('Este navegador no puede reproducir HLS. Intenta con un navegador compatible.');
                } else {
                    video.src = data.url;
                    video.play().catch(() => {});
                }

                if (data.expires_in) renewalTimer = window.setTimeout(connect, Math.max(60, data.expires_in - 45) * 1000);
            } catch (error) {
                showMessage('Transmisión no disponible', error.message, true);
                setStatus('Sin señal');
            }
        };

        connect();
        container.querySelector('[data-live-retry]')?.addEventListener('click', connect);
    });
});
