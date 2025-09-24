    </main>
    
    <footer class="footer <?php echo !is_logged_in() ? 'logged-out' : ''; ?>">
        <div class="footer-content">
            <div class="footer-text">
                <p><?php echo get_copyright_text(); ?></p>
                <p>Version <?php echo SITE_VERSION; ?></p>
            </div>
            <a href="/" class="footer-powered">Powered by IslamWiki</a>
        </div>
    </footer>
    
    <script src="/skins/bismillah/assets/js/main.js"></script>
    
    <!-- Load extension scripts -->
    <?php if (isset($extension_manager)): ?>
        <?php $extension_manager->loadExtensionScripts(); ?>
    <?php endif; ?>
    
    <style>
    /* COMPLETE REDESIGN - SIMPLE LAYOUT */
    body.logged-out {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
    }
    
    /* Main content area */
    body.logged-out main {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    /* Form container - SIMPLE AND CLEAN */
    body.logged-out .form-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        width: 100%;
        max-width: 400px;
        border: 1px solid #e9ecef;
    }
    
    /* Footer - SIMPLE STICKY */
    .footer.logged-out {
        background: #2c3e50;
        color: white;
        padding: 1rem;
        text-align: center;
        margin-top: auto;
    }
    
    .footer.logged-out .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .footer.logged-out .footer-text {
        text-align: left;
    }
    
    .footer.logged-out .footer-powered {
        color: white;
        text-decoration: none;
    }
    </style>
</body>
</html>
