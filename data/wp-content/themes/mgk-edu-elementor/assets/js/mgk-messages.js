(function () {
    function initComposer(form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var input = form.querySelector('textarea');
            if (input) input.value = '';
        });
    }

    function setModalOpen(modal, open) {
        if (!modal) return;
        if (open) {
            modal.hidden = false;
            document.documentElement.classList.add('mgk-message-modal-open');
        } else {
            modal.hidden = true;
            document.documentElement.classList.remove('mgk-message-modal-open');
        }
    }

    function initComposeModal(scope) {
        var modal = scope.querySelector('[data-mgk-message-compose-modal]');
        if (!modal) return;

        scope.querySelectorAll('[data-mgk-message-compose-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                setModalOpen(modal, true);
            });
        });

        modal.querySelectorAll('[data-mgk-message-compose-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                setModalOpen(modal, false);
            });
        });
    }

    window.mgkInitMessageComposer = function () {
        document.querySelectorAll('[data-mgk-message-composer]').forEach(initComposer);
        document.querySelectorAll('.mgk-parent-messages').forEach(initComposeModal);
    };

    window.mgkSendMessage = function () {};
    window.mgkAttachFile = function () {};
    window.mgkAttachLessonReference = function () {};
    window.mgkValidateMessage = function () { return true; };

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[data-mgk-message-compose-modal]:not([hidden])').forEach(function (modal) {
            setModalOpen(modal, false);
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.mgkInitMessageComposer);
    } else {
        window.mgkInitMessageComposer();
    }
})();
