
(function () {

    const themes = {

        classic: {
            name: "Classic",
            description: "Original Vlast colours",
            background: "#f3f5f7",
            surface: "#ffffff",
            accent: "#171717",
            text: "#222222",
            muted: "#888888",
            border: "#eeeeee",
            soft: "#f1f2f4",
            sidebar: "#e4e6e9"
        },

        ocean: {
            name: "Ocean Blue",
            description: "Cool blue appearance",
            background: "#eaf3fa",
            surface: "#ffffff",
            accent: "#1677c8",
            text: "#18334b",
            muted: "#71879a",
            border: "#dce8f1",
            soft: "#e0edf7",
            sidebar: "#c9e1f3"
        },

        forest: {
            name: "Forest Green",
            description: "Natural green colours",
            background: "#edf5ef",
            surface: "#ffffff",
            accent: "#24784a",
            text: "#203b2c",
            muted: "#819487",
            border: "#dce9df",
            soft: "#e0eee4",
            sidebar: "#cce2d2"
        },

        rose: {
            name: "Rose Pink",
            description: "Soft pink appearance",
            background: "#fff0f4",
            surface: "#ffffff",
            accent: "#d6336c",
            text: "#492332",
            muted: "#a17c8a",
            border: "#f4dce5",
            soft: "#fce3ec",
            sidebar: "#f4cad9"
        },

        violet: {
            name: "Royal Purple",
            description: "Elegant purple colours",
            background: "#f2effb",
            surface: "#ffffff",
            accent: "#7045bd",
            text: "#33254f",
            muted: "#8a7ba4",
            border: "#e4dcf2",
            soft: "#e9e0f7",
            sidebar: "#d8c9ef"
        },

        sunset: {
            name: "Sunset Orange",
            description: "Warm orange appearance",
            background: "#fff3e9",
            surface: "#ffffff",
            accent: "#e87527",
            text: "#49301f",
            muted: "#a68b78",
            border: "#f3e1d2",
            soft: "#fce6d5",
            sidebar: "#f5d4ba"
        },

        emerald: {
            name: "Emerald",
            description: "Fresh emerald colours",
            background: "#eaf7f4",
            surface: "#ffffff",
            accent: "#07866c",
            text: "#193d35",
            muted: "#7d9b93",
            border: "#d7ebe5",
            soft: "#dff1eb",
            sidebar: "#c4e5da"
        },

        red: {
            name: "Crimson Red",
            description: "Bold red appearance",
            background: "#fff0ef",
            surface: "#ffffff",
            accent: "#c62828",
            text: "#432323",
            muted: "#a47e7e",
            border: "#f1dddd",
            soft: "#f9e1e1",
            sidebar: "#f0caca"
        },

        midnight: {
            name: "Midnight",
            description: "Deep dark appearance",
            background: "#171923",
            surface: "#222633",
            accent: "#8b9cff",
            text: "#f0f1f8",
            muted: "#a0a6b8",
            border: "#353b4d",
            soft: "#2c3242",
            sidebar: "#30374b"
        },

        slate: {
            name: "Slate Grey",
            description: "Modern grey appearance",
            background: "#eceff3",
            surface: "#ffffff",
            accent: "#52657d",
            text: "#283442",
            muted: "#84909d",
            border: "#dce1e7",
            soft: "#e2e7ed",
            sidebar: "#cfd7e0"
        }

    };

    // Each logged-in user gets a separate storage key.
    const userId = window.VLAST_USER_ID;

    const storageKey = userId
        ? "vlastAppearance_user_" + String(userId)
        : "vlastAppearance_guest";

    function getSettings() {

        try {

            const saved = JSON.parse(
                localStorage.getItem(storageKey)
            );

            if (
                saved &&
                themes[saved.theme] &&
                ["light", "dark"].includes(saved.mode)
            ) {
                return saved;
            }

        } catch (error) {
            console.error("Could not load Vlast appearance:", error);
        }

        return {
            theme: "classic",
            mode: "light"
        };

    }

    function apply(themeName, mode) {

        const theme = themes[themeName] || themes.classic;

        if (!["light", "dark"].includes(mode)) {
            mode = "light";
        }

        const dark = mode === "dark";

        const root = document.documentElement;

        const values = {

            "--vlast-bg": dark ? "#171923" : theme.background,

            "--vlast-surface": dark ? "#222633" : theme.surface,

            "--vlast-accent": theme.accent,

            "--vlast-text": dark ? "#f0f1f8" : theme.text,

            "--vlast-muted": dark ? "#a0a6b8" : theme.muted,

            "--vlast-border": dark ? "#353b4d" : theme.border,

            "--vlast-soft": dark ? "#2c3242" : theme.soft,

            "--vlast-sidebar": dark ? "#30374b" : theme.sidebar

        };

        Object.keys(values).forEach(function (property) {

            root.style.setProperty(property, values[property]);

        });

        root.dataset.vlastMode = mode;
        root.dataset.vlastTheme = themeName;

        let style = document.getElementById("vlast-global-theme");

        if (!style) {

            style = document.createElement("style");
            style.id = "vlast-global-theme";

            document.head.appendChild(style);

        }

        style.textContent = `

            body {
                background: var(--vlast-bg) !important;
                color: var(--vlast-text) !important;
            }

            header,
            .header,
            .navbar,
            .topbar,
            .mobile-nav,
            .settings-content,
            .messages-container,
            .conversations,
            .chat-header,
            .message-form,
            .card,
            .post,
            .post-card,
            .profile-card,
            .sidebar-card,
            .suggestion-card,
            .story,
            .create-post,
            .composer,
            .modal-content {
                background-color: var(--vlast-surface) !important;
                color: var(--vlast-text) !important;
                border-color: var(--vlast-border) !important;
            }

            .sidebar,
            .settings-sidebar,
            .left-sidebar,
            .right-sidebar {
                background-color: var(--vlast-sidebar) !important;
                color: var(--vlast-text) !important;
                border-color: var(--vlast-border) !important;
            }

            .chat-area,
            .chat-messages,
            .page,
            .main-content,
            .feed,
            .content-area {
                background-color: var(--vlast-bg) !important;
                color: var(--vlast-text) !important;
            }

            .search-box,
            .message-input,
            .message-extra,
            .chat-actions button,
            .settings-nav a:hover,
            .settings-nav a.active,
            .chat-item.active,
            .comment-input,
            .input-box {
                background-color: var(--vlast-soft) !important;
                color: var(--vlast-text) !important;
            }

            input,
            textarea,
            select {
                background-color: var(--vlast-surface);
                color: var(--vlast-text) !important;
                border-color: var(--vlast-border) !important;
            }

            input::placeholder,
            textarea::placeholder {
                color: var(--vlast-muted) !important;
            }

            .message.received .message-bubble {
                background: var(--vlast-surface) !important;
                color: var(--vlast-text) !important;
            }

            .message.sent .message-bubble {
                background: var(--vlast-accent) !important;
                color: #ffffff !important;
            }

            .send-button,
            .preview-button,
            .follow-button,
            .primary-button,
            .settings-nav .active-button {
                background: var(--vlast-accent) !important;
                color: #ffffff !important;
            }

            .header-icons a.active,
            .header-icons a:hover,
            .chat-name,
            .chat-user-info h3,
            .settings-content h2,
            .settings-content h3 {
                color: var(--vlast-text);
            }

            a {
                transition: color .2s ease;
            }

            button {
                border-color: var(--vlast-border);
            }

            ${dark ? `

                .chat-messages {
                    background: #1b202d !important;
                }

                .chat-item {
                    background: #222633 !important;
                    border-color: #353b4d !important;
                    color: #f0f1f8 !important;
                }

                .chat-item:hover {
                    background: #2c3242 !important;
                }

                .chat-item h3,
                .chat-item p {
                    color: #f0f1f8 !important;
                }

            ` : ""}

        `;

    }

    function save(themeName, mode) {

        if (!themes[themeName]) {
            themeName = "classic";
        }

        if (!["light", "dark"].includes(mode)) {
            mode = "light";
        }

        const settings = {
            theme: themeName,
            mode: mode
        };

        try {

            localStorage.setItem(
                storageKey,
                JSON.stringify(settings)
            );

        } catch (error) {

            console.error("Could not save Vlast appearance:", error);

        }

        apply(themeName, mode);

    }

    const current = getSettings();

    apply(current.theme, current.mode);

    window.VlastTheme = {

        themes: themes,

        getSettings: getSettings,

        apply: apply,

        save: save

    };

})();
