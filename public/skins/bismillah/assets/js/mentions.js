/**
 * Mentions System - Handles @username autocomplete and linking
 */
class MentionsSystem {
    constructor() {
        this.isActive = false;
        this.currentQuery = '';
        this.selectedIndex = -1;
        this.users = [];
        this.dropdown = null;
        this.textarea = null;
        this.cursorPosition = 0;
        this.mentionStart = -1;
        
        this.init();
    }
    
    init() {
        // Initialize for all textareas with mention support
        this.initializeTextareas();
        
        // Create global dropdown
        this.createDropdown();
        
        // Bind events
        this.bindEvents();
    }
    
    initializeTextareas() {
        const textareas = document.querySelectorAll('textarea[data-mentions="true"], .post-input, .comment-input');
        console.log('Found textareas for mentions:', textareas.length, textareas);
        textareas.forEach(textarea => {
            console.log('Setting up textarea:', textarea);
            this.setupTextarea(textarea);
        });
    }
    
    setupTextarea(textarea) {
        console.log('Setting up textarea with ID:', textarea.id, 'Class:', textarea.className);
        
        // Add mention support attributes
        textarea.setAttribute('data-mentions', 'true');
        textarea.setAttribute('autocomplete', 'off');
        textarea.setAttribute('spellcheck', 'false');
        
        // Add event listeners
        textarea.addEventListener('input', (e) => this.handleInput(e));
        textarea.addEventListener('keydown', (e) => this.handleKeydown(e));
        textarea.addEventListener('blur', (e) => this.handleBlur(e));
        textarea.addEventListener('focus', (e) => this.handleFocus(e));
        
        console.log('Textarea setup complete for:', textarea.id);
    }
    
    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'mentions-dropdown';
        this.dropdown.style.display = 'none';
        document.body.appendChild(this.dropdown);
    }
    
    bindEvents() {
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.mentions-dropdown') && !e.target.closest('textarea[data-mentions="true"]')) {
                this.hideDropdown();
            }
        });
    }
    
    handleInput(e) {
        console.log('Input event triggered on:', e.target);
        this.textarea = e.target;
        const value = this.textarea.value;
        const cursorPos = this.textarea.selectionStart;
        
        console.log('Input value:', value, 'Cursor position:', cursorPos);
        
        // Find @mention at cursor position
        const mentionMatch = this.findMentionAtPosition(value, cursorPos);
        console.log('Mention match:', mentionMatch);
        
        if (mentionMatch) {
            this.mentionStart = mentionMatch.start;
            this.currentQuery = mentionMatch.query;
            this.cursorPosition = cursorPos;
            
            if (this.currentQuery.length >= 1) {
                this.searchUsers(this.currentQuery);
            } else {
                this.hideDropdown();
            }
        } else {
            this.hideDropdown();
        }
    }
    
    handleKeydown(e) {
        if (!this.isActive) return;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.navigateDropdown(1);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.navigateDropdown(-1);
                break;
            case 'Enter':
            case 'Tab':
                e.preventDefault();
                this.selectUser();
                break;
            case 'Escape':
                this.hideDropdown();
                break;
        }
    }
    
    handleBlur(e) {
        // Delay hiding to allow for dropdown clicks
        setTimeout(() => {
            if (!document.activeElement || !document.activeElement.closest('.mentions-dropdown')) {
                this.hideDropdown();
            }
        }, 150);
    }
    
    handleFocus(e) {
        // Check if there's an active mention when focusing
        const value = e.target.value;
        const cursorPos = e.target.selectionStart;
        const mentionMatch = this.findMentionAtPosition(value, cursorPos);
        
        if (mentionMatch && mentionMatch.query.length >= 1) {
            this.mentionStart = mentionMatch.start;
            this.currentQuery = mentionMatch.query;
            this.cursorPosition = cursorPos;
            this.searchUsers(this.currentQuery);
        }
    }
    
    findMentionAtPosition(text, position) {
        // Look backwards from cursor position for @mention
        let start = position - 1;
        
        // Find the start of the current word
        while (start >= 0 && /\S/.test(text[start])) {
            start--;
        }
        start++;
        
        // Check if this word starts with @
        if (start < text.length && text[start] === '@') {
            // Find the end of the mention
            let end = start + 1;
            while (end < text.length && /[a-zA-Z0-9_]/.test(text[end])) {
                end++;
            }
            
            const query = text.substring(start + 1, end);
            return {
                start: start,
                end: end,
                query: query
            };
        }
        
        return null;
    }
    
    async searchUsers(query) {
        try {
            console.log('Searching users for query:', query);
            const response = await fetch(`/api/ajax/search_users.php?q=${encodeURIComponent(query)}&limit=10`);
            console.log('Search response status:', response.status);
            const data = await response.json();
            console.log('Search response data:', data);
            
            if (data.users) {
                this.users = data.users;
                this.selectedIndex = -1;
                this.showDropdown();
            } else {
                this.hideDropdown();
            }
        } catch (error) {
            console.error('Error searching users:', error);
            this.hideDropdown();
        }
    }
    
    showDropdown() {
        if (this.users.length === 0) {
            this.hideDropdown();
            return;
        }
        
        this.isActive = true;
        this.dropdown.innerHTML = '';
        
        this.users.forEach((user, index) => {
            const item = document.createElement('div');
            item.className = 'mentions-dropdown-item';
            if (index === this.selectedIndex) {
                item.classList.add('selected');
            }
            
            item.innerHTML = `
                <div class="mention-user-avatar">
                    ${user.avatar ? 
                        `<img src="${user.avatar}" alt="${user.username}">` : 
                        `<div class="avatar-circle">${(user.display_name || user.username).substring(0, 2).toUpperCase()}</div>`
                    }
                </div>
                <div class="mention-user-info">
                    <div class="mention-username">@${user.username}</div>
                    ${user.display_name ? `<div class="mention-display-name">${user.display_name}</div>` : ''}
                </div>
            `;
            
            item.addEventListener('click', () => {
                this.selectedIndex = index;
                this.selectUser();
            });
            
            this.dropdown.appendChild(item);
        });
        
        this.positionDropdown();
        this.dropdown.style.display = 'block';
    }
    
    hideDropdown() {
        this.isActive = false;
        this.dropdown.style.display = 'none';
        this.users = [];
        this.selectedIndex = -1;
    }
    
    positionDropdown() {
        if (!this.textarea) return;
        
        const rect = this.textarea.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Position dropdown below textarea
        this.dropdown.style.position = 'absolute';
        this.dropdown.style.top = (rect.bottom + scrollTop + 5) + 'px';
        this.dropdown.style.left = rect.left + 'px';
        this.dropdown.style.width = Math.min(300, rect.width) + 'px';
        this.dropdown.style.zIndex = '1000';
    }
    
    navigateDropdown(direction) {
        if (!this.isActive || this.users.length === 0) return;
        
        this.selectedIndex += direction;
        
        if (this.selectedIndex < 0) {
            this.selectedIndex = this.users.length - 1;
        } else if (this.selectedIndex >= this.users.length) {
            this.selectedIndex = 0;
        }
        
        // Update visual selection
        const items = this.dropdown.querySelectorAll('.mentions-dropdown-item');
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === this.selectedIndex);
        });
    }
    
    selectUser() {
        if (!this.isActive || this.selectedIndex < 0 || this.selectedIndex >= this.users.length) {
            return;
        }
        
        const selectedUser = this.users[this.selectedIndex];
        const beforeMention = this.textarea.value.substring(0, this.mentionStart);
        const afterMention = this.textarea.value.substring(this.cursorPosition);
        
        // Insert the mention
        const mention = `@${selectedUser.username} `;
        this.textarea.value = beforeMention + mention + afterMention;
        
        // Position cursor after the mention
        const newCursorPos = this.mentionStart + mention.length;
        this.textarea.setSelectionRange(newCursorPos, newCursorPos);
        
        // Trigger input event for any other handlers
        this.textarea.dispatchEvent(new Event('input', { bubbles: true }));
        
        this.hideDropdown();
    }
}

// Initialize mentions system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('Mentions system initializing...');
    window.mentionsSystem = new MentionsSystem();
    console.log('Mentions system initialized:', window.mentionsSystem);
});

// Re-initialize for dynamically added textareas
document.addEventListener('DOMNodeInserted', (e) => {
    if (e.target.tagName === 'TEXTAREA' && e.target.hasAttribute('data-mentions')) {
        window.mentionsSystem.setupTextarea(e.target);
    }
});
