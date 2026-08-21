import lottie from 'lottie-web/build/player/lottie_light';

const container = document.querySelector('[data-auth-lottie]');

if (container) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const animation = lottie.loadAnimation({
        container,
        renderer: 'svg',
        loop: true,
        autoplay: !reduceMotion.matches,
        path: container.dataset.authLottie,
        rendererSettings: {
            preserveAspectRatio: 'xMidYMid meet',
            progressiveLoad: true,
        },
    });

    animation.addEventListener('DOMLoaded', () => {
        container.classList.add('is-ready');
        container.querySelector('svg')?.setAttribute('aria-hidden', 'true');
    });

    animation.addEventListener('data_failed', () => {
        container.classList.add('has-failed');
    });

    const syncMotion = (event) => {
        if (event.matches) {
            animation.goToAndStop(0, true);
        } else {
            animation.play();
        }
    };

    reduceMotion.addEventListener?.('change', syncMotion);
}
