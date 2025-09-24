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
    /* Footer positioning for logged-out users */
    .footer.logged-out {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        height: 60px;
    }
    
    /* Simple layout - no flexbox complications */
    body.logged-out {
        margin: 0;
        padding: 0;
    }
    
    /* Center the form container */
    body.logged-out .form-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        max-width: 400px;
        padding: 2rem;
        box-sizing: border-box;
    }
    
    /* Ensure content doesn't go below footer */
    body.logged-out .form-container {
        margin-bottom: 60px; /* Space for footer */
    }
    </style>
</body>
</html>
