<script>
    document.addEventListener('alpine:init', () => {
        const storageKey = 'assistant-sidebar-open';

        const readOpenState = () => {
            try {
                return sessionStorage.getItem(storageKey) === '1';
            } catch (error) {
                return false;
            }
        };

        const saveOpenState = (open) => {
            try {
                sessionStorage.setItem(storageKey, open ? '1' : '0');
            } catch (error) {
                // The sidebar works normally when session storage is unavailable.
            }
        };

        window.Alpine.data('assistantSidebar', () => ({
            open: false,
            draft: '',
            pendingMessage: '',
            busy: false,

            init() {
                this.open = readOpenState();
                this.$watch('open', (open) => this.handleOpenChange(open));

                if (this.open) {
                    this.scrollAndFocusInput();
                }
            },

            handleOpenChange(open) {
                saveOpenState(open);

                if (open) {
                    this.scrollAndFocusInput();
                }
            },

            scrollAndFocusInput() {
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.input?.focus();
                });
            },

            scrollToBottom() {
                this.$refs.history.scrollTop = this.$refs.history.scrollHeight;
            },

            restoreDraft() {
                if (!this.draft) {
                    this.draft = this.pendingMessage;
                }
            },

            async submitMessage() {
                const message = this.draft.trim();

                if (this.busy || !message) {
                    return;
                }

                this.pendingMessage = message;
                this.draft = '';
                this.busy = true;
                this.$nextTick(() => this.scrollToBottom());

                try {
                    this.$wire.$set('message', message, false);
                    await this.$wire.send();

                    if (this.$wire.error) {
                        this.restoreDraft();
                    } else {
                        this.pendingMessage = '';
                    }
                } catch (error) {
                    this.restoreDraft();
                } finally {
                    this.busy = false;
                    this.$nextTick(() => this.scrollToBottom());
                }
            },
        }));
    });
</script>
