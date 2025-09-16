// Comprehensive Messaging System JavaScript

class MessagingSystem {
    constructor() {
        this.currentConversation = null;
        this.lastMessageId = 0;
        this.pollInterval = null;
        this.isPolling = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupMessageInput();
        this.setupConversationList();
        
        // Start polling for new messages if on messages page
        if (window.location.pathname.includes('/messages')) {
            this.startPolling();
        }
    }
    
    setupEventListeners() {
        // Message input enter key
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
        
        // Send button
        const sendButton = document.querySelector('.btn-send');
        if (sendButton) {
            sendButton.addEventListener('click', () => this.sendMessage());
        }
        
        // Conversation items
        document.addEventListener('click', (e) => {
            if (e.target.closest('.conversation-item')) {
                const conversationItem = e.target.closest('.conversation-item');
                const conversationId = conversationItem.dataset.conversationId;
                if (conversationId) {
                    this.loadConversation(conversationId);
                }
            }
        });
        
        // Recipient selection in compose
        document.addEventListener('click', (e) => {
            if (e.target.closest('.recipient-item')) {
                const recipientItem = e.target.closest('.recipient-item');
                const userId = recipientItem.dataset.userId;
                const userName = recipientItem.dataset.userName;
                if (userId) {
                    this.selectRecipient(userId, userName);
                }
            }
        });
    }
    
    setupMessageInput() {
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            // Auto-resize textarea
            messageInput.addEventListener('input', () => {
                messageInput.style.height = 'auto';
                messageInput.style.height = messageInput.scrollHeight + 'px';
            });
        }
    }
    
    setupConversationList() {
        // Add conversation IDs to conversation items
        document.querySelectorAll('.conversation-item').forEach(item => {
            const userId = item.dataset.otherUserId || item.dataset.userId;
            if (userId) {
                item.dataset.conversationId = userId;
            }
        });
    }
    
    async loadConversation(conversationId) {
        this.currentConversation = conversationId;
        this.lastMessageId = 0;
        
        // Update active conversation
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const activeItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
        }
        
        // Load messages
        await this.loadMessages();
        
        // Scroll to bottom
        this.scrollToBottom();
    }
    
    async loadMessages() {
        if (!this.currentConversation) return;
        
        try {
            const response = await fetch(`/api/ajax/get_messages.php?conversation_id=${this.currentConversation}&last_message_id=${this.lastMessageId}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayMessages(data.messages);
                if (data.messages.length > 0) {
                    this.lastMessageId = data.messages[data.messages.length - 1].id;
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
    
    displayMessages(messages) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            chatMessages.appendChild(messageElement);
        });
        
        this.scrollToBottom();
    }
    
    createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender_id == window.currentUserId ? 'sent' : 'received'}`;
        
        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';
        messageContent.textContent = message.message;
        
        const messageTime = document.createElement('div');
        messageTime.className = 'message-time';
        messageTime.textContent = new Date(message.created_at).toLocaleString();
        
        messageDiv.appendChild(messageContent);
        messageDiv.appendChild(messageTime);
        
        return messageDiv;
    }
    
    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        
        if (!message || !this.currentConversation) return;
        
        try {
            const response = await fetch('/api/ajax/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipient_id: this.currentConversation,
                    message: message
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Clear input
                messageInput.value = '';
                messageInput.style.height = 'auto';
                
                // Add message to chat
                const messageElement = this.createMessageElement(data.data);
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    chatMessages.appendChild(messageElement);
                    this.scrollToBottom();
                }
                
                // Update conversation list
                this.updateConversationList(data.data);
            } else {
                alert('Failed to send message: ' + data.message);
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message');
        }
    }
    
    updateConversationList(message) {
        // Update the conversation preview in the sidebar
        const conversationItem = document.querySelector(`[data-conversation-id="${message.recipient_id}"]`);
        if (conversationItem) {
            const preview = conversationItem.querySelector('.conversation-preview');
            if (preview) {
                preview.textContent = message.message;
            }
            
            const time = conversationItem.querySelector('.conversation-time');
            if (time) {
                time.textContent = new Date(message.created_at).toLocaleDateString();
            }
        }
    }
    
    selectRecipient(userId, userName) {
        // Update recipient selection in compose page
        const selectedRecipient = document.getElementById('selectedRecipient');
        const recipientId = document.getElementById('recipient_id');
        
        if (selectedRecipient && recipientId) {
            selectedRecipient.innerHTML = `<span class="selected-name">${userName}</span>`;
            recipientId.value = userId;
            
            // Update visual state
            document.querySelectorAll('.recipient-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            const selectedItem = document.querySelector(`[data-user-id="${userId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('selected');
            }
        }
    }
    
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollInterval = setInterval(() => {
            if (this.currentConversation) {
                this.loadMessages();
            }
        }, 2000); // Poll every 2 seconds
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        this.isPolling = false;
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    // Public methods for external use
    loadConversation(conversationId) {
        this.loadConversation(conversationId);
    }
    
    sendMessage() {
        this.sendMessage();
    }
}

// Initialize messaging system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set current user ID for message display
    window.currentUserId = window.currentUserId || null;
    
    // Initialize messaging system
    window.messagingSystem = new MessagingSystem();
    
    // Global functions for backward compatibility
    window.loadConversation = function(conversationId) {
        window.messagingSystem.loadConversation(conversationId);
    };
    
    window.sendMessage = function() {
        window.messagingSystem.sendMessage();
    };
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.messagingSystem) {
        window.messagingSystem.stopPolling();
    }
});
