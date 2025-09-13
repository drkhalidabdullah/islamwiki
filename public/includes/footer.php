    </main>
    
    <footer class="footer">
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
</body>
</html>
