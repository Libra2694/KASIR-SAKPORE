            </main>
        </div>
    </div>
    <?php if (!isset($assetPath)) { require_once __DIR__ . '/../config/paths.php'; $assetPath = getAssetPath(''); } ?>
    <script src="<?php echo $assetPath; ?>js/main.js"></script>
</body>
</html>

