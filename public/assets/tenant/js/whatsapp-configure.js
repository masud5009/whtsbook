(function ($) {
    "use strict";

    const copyButtons = document.querySelectorAll('.callback-copy-btn');

    if (copyButtons.length === 0) {
        return;
    }

    const defaultClass = 'btn-outline-primary';
    const activeClass = 'btn-success';

    const fallbackCopy = function (text) {
        const tempInput = document.createElement('textarea');
        tempInput.value = text;
        tempInput.setAttribute('readonly', '');
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
    };

    copyButtons.forEach(function (copyButton) {
        const defaultLabel = copyButton.querySelector('span').textContent;

        copyButton.addEventListener('click', async function () {
            const target = document.querySelector(this.dataset.copyTarget);

            if (!target) {
                return;
            }

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(target.value);
                } else {
                    fallbackCopy(target.value);
                }

                // Show success feedback on button
                this.classList.remove(defaultClass);
                this.classList.add(activeClass);
                this.querySelector('span').textContent = __copyUrlBtn__;

                // Revert button after 1.5 seconds
                setTimeout(() => {
                    this.classList.remove(activeClass);
                    this.classList.add(defaultClass);
                    this.querySelector('span').textContent = defaultLabel;
                }, 1500);
            } catch (e) {
                console.error('Copy failed:', e);
            }
        });
    });

})(jQuery);
