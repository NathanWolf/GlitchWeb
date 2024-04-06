class Glitch {
    #loaded = false;
    #tab = 'characters';
    #history = new History();
    #characters = new Characters(this, document.getElementById('characters'));
    #profile = new Profile(this, document.getElementById('profile'), document.getElementById('profileButton'));
    #characterEditor = new CharacterEditor(this, document.getElementById('characterEditor'));
    #home = new Home(this, document.getElementById('home'));
    #tabs = {
        characters: this.#characters,
        characterEditor: this.#characterEditor,
        profile: this.#profile,
        home: this.#home
    };

    constructor() {
        let controller = this;
        this.#history.setDefault('tab', 'characters');
        this.#history.onChange(function() {
            controller.onHistoryChange();
        });
        this.#history.autoUpdate();
    }

    getCharacters() {
        return this.#characters;
    }

    getProfile() {
        return this.#profile;
    }

    getHistory() {
        return this.#history;
    }

    register() {
        let love = this;
        Utilities.addHandlerToClass('tabButton', function() {
            love.selectTab(this.dataset.tab);
        });
        Utilities.addHandlerToClass('navigation', function() {
            love.selectTab(this.dataset.tab);
        });

        // Try to make the virtual keyboard on iOS not break the entire layout
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', () => {
                love.forceViewport();
            });
        }

        this.#profile.check();
    }

    forceViewport() {
        let container = document.getElementById('mainContainer');
        container.style.height = window.visualViewport.height + 'px';
        container.scrollTop = 0;
        document.body.scrollTop = 0;
        window.scrollTop = 0;
    }

    selectTab(tabId) {
        if (!this.#tabs.hasOwnProperty(tabId)) {
            throw new Error("Selecting unknown tab: " + tabId);
        }
        let tabButtons = document.getElementsByClassName('tabButton');
        for (let i = 0; i < tabButtons.length; i++) {
            let tabButton = tabButtons[i];
            if (tabButton.dataset.tab === tabId) {
                Utilities.addClass(tabButton, 'active');
            } else {
                Utilities.removeClass(tabButton, 'active');
            }
        }
        let tabs = document.getElementsByClassName('tab');
        for (let i = 0; i < tabs.length; i++) {
            let tab = tabs[i];
            if (tab.id === tabId) {
                tab.style.display = 'flex';
            } else {
                tab.style.display = 'none';
            }
        }

        let tab = this.#tabs[tabId];
        let title = 'Glitch';
        let tabTitle = tab.getTitle();
        if (tabTitle != null) {
            title += ' (' + tabTitle + ')';
        }
        document.title = title;
        this.#tab = tabId;
        this.#history.set('tab', this.#tab);

        // Don't activate the tab until data is loaded
        if (!this.#loaded) {
            return;
        }

        let previousTab = this.#tabs.hasOwnProperty(this.#tab) ? this.#tabs[this.#tab] : null;
        if (previousTab != null) {
            previousTab.deactivate();
        }
        tab.activate();
    }

    #request(url, callback) {
        const request = new XMLHttpRequest();
        request.onload = callback;
        request.responseType = 'json';
        request.onerror = function() { alert("Failed to load data, sorry!"); };
        request.open("GET", url, true);
        request.send();
    }

    load() {
        const glitch = this;
        this.#request('data/glitch.php', function() {
            glitch.#processData(this.response);
        });
    }

    #processData(data) {
        if (!data.success) {
            alert("Failed to load data: " + data.message);
            return;
        }

        this.#characters.addProperties(data.properties);
        this.#characters.addCharacters(data.characters);

        this.#loaded = true;
        document.getElementById('loading').style.display = 'none';
        this.#profile.loaded();

        // Activate current tab
        let tab = this.#tabs[this.#tab];
        tab.activate();
        // We also skip having tabs process history until data is loaded
        tab.onHistoryChange();
    }

    onHistoryChange() {
        let tab = this.#history.get('tab');
        if (tab !== this.#tab) {
            this.selectTab(tab);
        }
        if (this.#loaded) {
            this.#tabs[tab].onHistoryChange();
        }
    }
}
